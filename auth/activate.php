<?php
// auth/activate.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
$page_title = 'Aktivasi Akun';
include __DIR__ . '/../includes/header.php';

$token = $_GET['token'] ?? '';
$message = '';
if (!$token) {
    $message = "Token tidak ditemukan.";
} else {
    // cek token dan aktifkan jika masih inactive
    $stmt = $conn->prepare("SELECT id, created_at FROM users WHERE activation_token = ? AND status='inactive' LIMIT 1");
    $stmt->bind_param("s",$token);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        // optional: cek expiry based on created_at (24 jam)
        $created = new DateTime($user['created_at']);
        $now = new DateTime();
        $interval = $now->getTimestamp() - $created->getTimestamp();
        if ($interval > 86400) {
            $message = "Token aktivasi sudah kedaluwarsa.";
        } else {
            $upd = $conn->prepare("UPDATE users SET status = 'active', activation_token = NULL, updated_at = NOW() WHERE id = ?");
            $upd->bind_param("i", $user['id']);
            if ($upd->execute()) {
                $message = "Akun berhasil diaktivasi. Silakan <a href='login.php'>login</a>.";
            } else {
                $message = "Gagal aktivasi. Coba lagi.";
            }
        }
    } else {
        $message = "Token tidak valid atau akun sudah aktif.";
    }
    $stmt->close();
}
?>

<h2>Aktivasi Akun</h2>
<p><?= $message ?></p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
