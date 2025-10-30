<?php
// auth/reset_password.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$token = $_GET['token'] ?? '';
$msg = '';
$showForm = true;
$messageType = 'info';

if (!$token) {
    $msg = "Token tidak ditemukan.";
    $showForm = false;
    $messageType = 'error';
} else {
    // check token
    $stmt = $conn->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows !== 1) {
            $msg = "Token tidak valid atau sudah digunakan.";
            $showForm = false;
            $messageType = 'error';
        } else {
            $row = $res->fetch_assoc();
            $current_time = new DateTime();
            $expiry_time = new DateTime($row['reset_expires']);
            
            if ($current_time > $expiry_time) {
                $msg = "Token reset telah kedaluwarsa. Silakan request reset password lagi.";
                $showForm = false;
                $messageType = 'error';
            } else {
                $user_id = $row['id'];
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $p1 = $_POST['password'] ?? '';
                    $p2 = $_POST['cpassword'] ?? '';
                    
                    if ($p1 === '' || strlen($p1) < 6) {
                        $msg = "Password minimal 6 karakter.";
                        $messageType = 'error';
                    } elseif ($p1 !== $p2) {
                        $msg = "Konfirmasi password tidak sama.";
                        $messageType = 'error';
                    } else {
                        $hash = password_hash($p1, PASSWORD_BCRYPT);
                        $upd = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL, updated_at = NOW() WHERE id = ?");
                        
                        if ($upd) {
                            $upd->bind_param("si", $hash, $user_id);
                            if ($upd->execute()) {
                                $msg = "Password berhasil diubah. Silakan <a href='login.php' style='color: #4a90e2; text-decoration: underline;'>login</a>.";
                                $showForm = false;
                                $messageType = 'success';
                            } else {
                                $msg = "Gagal memperbarui password. Silakan coba lagi.";
                                $messageType = 'error';
                            }
                            $upd->close();
                        } else {
                            $msg = "Error preparing statement.";
                            $messageType = 'error';
                        }
                    }
                }
            }
        }
        $stmt->close();
    } else {
        $msg = "Error preparing statement.";
        $showForm = false;
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Gudang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 40px 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 24px;
            font-weight: 600;
        }

        .description {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="password"]:focus {
            border-color: #4a90e2;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            margin-top: 10px;
        }

        button:hover {
            background-color: #3a7bc8;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #666;
            text-decoration: none;
        }

        .back-link:hover {
            color: #333;
            text-decoration: underline;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Reset Password</h2>
    
    <?php if($showForm): ?>
        <p class="description">Masukkan password baru untuk akun Anda.</p>
    <?php endif; ?>

    <?php if($msg): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= $messageType === 'success' ? $msg : htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <?php if($showForm): ?>
    <form method="post" action="">
        <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password baru" required minlength="6">
        </div>

        <div class="form-group">
            <label for="cpassword">Konfirmasi Password</label>
            <input type="password" id="cpassword" name="cpassword" placeholder="Ulangi password baru" required minlength="6">
        </div>

        <button type="submit">Ganti Password</button>
    </form>
    <?php endif; ?>

    <div class="text-center">
        <a href="login.php" class="back-link">← Kembali ke Login</a>
    </div>
</div>

</body>
</html>