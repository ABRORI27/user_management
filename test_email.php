<?php
require __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = '15anasanjiabrori@gmail.com';
    $mail->Password = 'MASUKKAN_APP_PASSWORD_DISINI';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('15anasanjiabrori@gmail.com', 'Admin Gudang');
    $mail->addAddress('email_tujuan_kamu@gmail.com', 'Tes Penerima');

    $mail->isHTML(true);
    $mail->Subject = 'Tes Kirim Email dari XAMPP';
    $mail->Body    = '<b>✅ Ini uji coba kirim email menggunakan PHPMailer.</b>';

    $mail->send();
    echo "✅ Email berhasil dikirim!";
} catch (Exception $e) {
    echo "❌ Gagal kirim email: {$mail->ErrorInfo}";
}
