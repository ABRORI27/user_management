<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Email dan password wajib diisi!";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email, password, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $emailDB, $hashedPassword, $status);
            $stmt->fetch();

            if ($status !== "active") {
                $error = "Akun belum aktif! Silakan cek email untuk aktivasi.";
            } elseif (password_verify($password, $hashedPassword)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                header("Location: " . BASE_URL . "dashboard/index.php");
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Email tidak terdaftar!";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gudang</title>
    <style>
        /* RESET DAN GLOBAL STYLING KHUSUS AUTH */
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

        /* AUTH CONTAINER */
        .auth-container {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }

        /* AUTH BOX */
        .auth-box {
            background: #161616;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.6);
            border: 1px solid #2b2b2b;
            transition: all 0.3s ease;
        }

        .auth-box:hover {
            box-shadow: 0 0 40px rgba(74, 144, 226, 0.2);
            transform: translateY(-2px);
        }

        /* AUTH TITLE */
        .auth-title {
            text-align: center;
            margin-bottom: 30px;
            color: #4a90e2;
            font-weight: 600;
            font-size: 24px;
            letter-spacing: 0.5px;
        }

        /* AUTH FORM */
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .auth-form-group {
            display: flex;
            flex-direction: column;
        }

        .auth-form-group label {
            margin-bottom: 8px;
            color: #eaeaea;
            font-weight: 500;
            font-size: 14px;
        }

        .auth-form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #333;
            border-radius: 8px;
            background: #222;
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
            font-family: "Poppins", sans-serif;
        }

        .auth-form-control:focus {
            border-color: #4a90e2;
            background: #1b1b1b;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .auth-form-control::placeholder {
            color: #666;
        }

        /* AUTH BUTTON */
        .auth-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            width: 100%;
            font-family: "Poppins", sans-serif;
        }

        .auth-btn-primary {
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: #fff;
        }

        .auth-btn-primary:hover {
            background: linear-gradient(135deg, #5ba5ff, #3b88d3);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.4);
        }

        /* AUTH LINKS */
        .auth-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #2b2b2b;
        }

        .auth-text {
            color: #b0b0b0;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .auth-link {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .auth-link:hover {
            color: #5ba5ff;
            text-decoration: underline;
        }

        .auth-link-divider {
            color: #b0b0b0;
            margin: 0 12px;
        }

        /* AUTH ALERTS */
        .auth-alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .auth-alert-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: #fff;
        }

        .auth-alert-success {
            background: linear-gradient(135deg, #27ae60, #1e8449);
            color: #fff;
        }

        /* AUTH FOOTER */
        .auth-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #2b2b2b;
            color: #b0b0b0;
            font-size: 12px;
            line-height: 1.5;
        }

        .auth-footer strong {
            color: #4a90e2;
        }

        /* RESPONSIVE */
        @media (max-width: 480px) {
            body {
                padding: 15px;
                align-items: flex-start;
                padding-top: 40px;
            }
            
            .auth-box {
                padding: 30px 25px;
                margin: 0;
            }
            
            .auth-title {
                font-size: 22px;
                margin-bottom: 25px;
            }
            
            .auth-form-control {
                padding: 12px 14px;
            }
            
            .auth-btn {
                padding: 13px 20px;
            }
        }

        @media (max-width: 360px) {
            .auth-box {
                padding: 25px 20px;
            }
            
            .auth-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1 class="auth-title">Login Admin Gudang</h1>
            
            <?php if ($error): ?>
                <div class="auth-alert auth-alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="auth-form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="auth-form-control" 
                           placeholder="Masukkan email Anda" required>
                </div>

                <div class="auth-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="auth-form-control" 
                           placeholder="Masukkan password Anda" required>
                </div>

                <button type="submit" class="auth-btn auth-btn-primary">
                    🔐 Login ke Sistem
                </button>
            </form>

            <div class="auth-links">
                <p class="auth-text">Butuh bantuan dengan akun Anda?</p>
                <div>
                    <a href="register.php" class="auth-link">Daftar Akun Baru</a>
                    <span class="auth-link-divider">|</span>
                    <a href="forgot_password.php" class="auth-link">Lupa Password?</a>
                </div>
            </div>

            <div class="auth-footer">
                <p>&copy; 2025 <strong>Gudang Asset</strong> | Sistem Manajemen Gudang</p>
            </div>
        </div>
    </div>
</body>
</html>