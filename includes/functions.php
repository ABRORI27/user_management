<?php
// includes/functions.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

function generateToken($len = 64) {
    return bin2hex(random_bytes(intval($len/2)));
}

function sendActivationEmail($toEmail, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Aktivasi Akun - Gudang';
        $link = BASE_URL . "auth/activate.php?token=" . urlencode($token);
        $mail->Body = "<p>Terima kasih telah mendaftar. Klik link berikut untuk mengaktivasi akun:</p>
                       <p><a href='{$link}'>{$link}</a></p>
                       <p>Link berlaku 24 jam.</p>";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

function sendResetEmail($toEmail, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Reset Password - Gudang';
        $link = BASE_URL . "auth/reset_password.php?token=" . urlencode($token);
        $mail->Body = "<p>Permintaan reset password diterima. Klik link berikut untuk mengganti password:</p>
                       <p><a href='{$link}'>{$link}</a></p>
                       <p>Link berlaku 1 jam.</p>";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
