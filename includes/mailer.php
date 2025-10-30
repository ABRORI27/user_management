<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // gunakan __DIR__ agar path pasti benar

function sendActivationEmail($email, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '15anasanjiabrori@gmail.com'; // ganti dengan email kamu
        $mail->Password   = 'bpqw upgd bdji iogz'; // App Password dari Gmail, bukan password login Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('15anasanjiabrori@gmail.com', 'Admin Gudang');
        $mail->addAddress($email);

        $activationLink = "http://localhost/user_management/auth/activate.php?token=" . $token;

        $mail->isHTML(true);
        $mail->Subject = 'Aktivasi Akun Gudang';
        $mail->Body    = "
            <h2>Aktivasi Akun Anda</h2>
            <p>Terima kasih sudah mendaftar, silakan klik tautan berikut untuk mengaktifkan akun Anda:</p>
            <p><a href='$activationLink' style='color:#4CAF50;font-weight:bold;'>Aktifkan Akun</a></p>
            <hr>
            <p>Jika Anda tidak merasa mendaftar, abaikan email ini.</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
