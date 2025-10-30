<?php
// auth/activate.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$token = $_GET['token'] ?? '';
$message = '';
$message_type = 'error';

if (!$token) {
    $message = "Token tidak ditemukan.";
} else {
    // cek token dan aktifkan jika masih pending
    $stmt = $conn->prepare("SELECT id, created_at, status FROM users WHERE activation_token = ? AND status = 'pending' LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            
            // Cek expiry berdasarkan created_at (24 jam)
            $created = new DateTime($user['created_at']);
            $now = new DateTime();
            $interval = $now->getTimestamp() - $created->getTimestamp();
            
            if ($interval > 86400) { // 24 jam dalam detik
                $message = "Token aktivasi sudah kedaluwarsa. Silakan daftar ulang.";
            } else {
                $upd = $conn->prepare("UPDATE users SET status = 'active', activation_token = NULL, updated_at = NOW() WHERE id = ?");
                if ($upd) {
                    $upd->bind_param("i", $user['id']);
                    if ($upd->execute()) {
                        $message = "Akun berhasil diaktivasi. Silakan <a href='login.php' style='color: #4a90e2; text-decoration: underline;'>login</a>.";
                        $message_type = 'success';
                    } else {
                        $message = "Gagal aktivasi akun. Silakan coba lagi.";
                    }
                    $upd->close();
                } else {
                    $message = "Error preparing update statement.";
                }
            }
        } else {
            // Cek apakah akun sudah aktif
            $check_stmt = $conn->prepare("SELECT status FROM users WHERE activation_token = ? LIMIT 1");
            if ($check_stmt) {
                $check_stmt->bind_param("s", $token);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows === 1) {
                    $user_data = $check_result->fetch_assoc();
                    if ($user_data['status'] === 'active') {
                        $message = "Akun sudah aktif sebelumnya. Silakan <a href='login.php' style='color: #4a90e2; text-decoration: underline;'>login</a>.";
                        $message_type = 'info';
                    } else {
                        $message = "Token tidak valid.";
                    }
                } else {
                    $message = "Token tidak valid.";
                }
                $check_stmt->close();
            } else {
                $message = "Token tidak valid.";
            }
        }
        $stmt->close();
    } else {
        $message = "Error preparing statement.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun - Gudang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
            color: #eaeaea;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .activation-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .activation-box {
            background: #161616;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.6);
            border: 1px solid #2b2b2b;
            text-align: center;
        }

        .activation-title {
            color: #4a90e2;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .activation-message {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 16px;
            line-height: 1.5;
        }

        .message-success {
            background: rgba(39, 174, 96, 0.1);
            border: 1px solid rgba(39, 174, 96, 0.3);
            color: #27ae60;
        }

        .message-error {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
        }

        .message-info {
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.3);
            color: #3498db;
        }

        .activation-actions {
            margin-top: 25px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: #fff;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5ba5ff, #3b88d3);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #2b2b2b;
            color: #eaeaea;
            border: 1px solid #3a3a3a;
        }

        .btn-secondary:hover {
            background: #3a3a3a;
            transform: translateY(-2px);
        }

        .activation-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #2b2b2b;
            color: #b0b0b0;
            font-size: 12px;
        }

        @media (max-width: 480px) {
            .activation-box {
                padding: 30px 25px;
            }
            
            .activation-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="activation-container">
        <div class="activation-box">
            <h1 class="activation-title">Aktivasi Akun</h1>
            
            <div class="activation-message <?= 
                $message_type === 'success' ? 'message-success' : 
                ($message_type === 'info' ? 'message-info' : 'message-error') 
            ?>">
                <?= $message ?>
            </div>

            <div class="activation-actions">
                <?php if ($message_type === 'success' || $message_type === 'info'): ?>
                    <a href="login.php" class="btn btn-primary">
                        🔐 Login Sekarang
                    </a>
                <?php endif; ?>
                
                <a href="register.php" class="btn btn-secondary">
                    📝 Daftar Akun Baru
                </a>
                
                <a href="../index.php" class="btn btn-secondary">
                    🏠 Halaman Utama
                </a>
            </div>

            <div class="activation-footer">
                <p>&copy; 2025 <strong>Gudang Asset</strong> | Dibuat oleh <?= htmlspecialchars($_SESSION['user_name'] ?? 'System') ?></p>
            </div>
        </div>
    </div>
</body>
</html>