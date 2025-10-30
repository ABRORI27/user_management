<?php
// dashboard/profile.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
$page_title = 'Profil Pengguna';
include __DIR__ . '/../includes/header.php';

$uid = $_SESSION['user_id'];
$msg = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['full_name'] ?? '');
        if ($name === '') {
            $errors[] = "Nama tidak boleh kosong.";
        } else {
            $upd = $conn->prepare("UPDATE users SET full_name = ?, updated_at = NOW() WHERE id = ?");
            $upd->bind_param("si", $name, $uid);
            if ($upd->execute()) {
                $msg = "Profil berhasil diperbarui.";
                $_SESSION['user_name'] = $name;
            } else {
                $errors[] = "Gagal update profil: " . $conn->error;
            }
            $upd->close();
        }
    } elseif (isset($_POST['change_pass'])) {
        $old = $_POST['old_password'] ?? '';
        $p1 = $_POST['password'] ?? '';
        $p2 = $_POST['cpassword'] ?? '';
        
        if (strlen($p1) < 6) {
            $errors[] = "Password baru minimal 6 karakter.";
        }
        if ($p1 !== $p2) {
            $errors[] = "Konfirmasi password tidak cocok.";
        }
        if (!$errors) {
            $st = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $st->bind_param("i", $uid);
            $st->execute();
            $res = $st->get_result()->fetch_assoc();
            
            if (!password_verify($old, $res['password'])) {
                $errors[] = "Password lama salah.";
            } else {
                $hash = password_hash($p1, PASSWORD_BCRYPT);
                $up = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                $up->bind_param("si", $hash, $uid);
                if ($up->execute()) {
                    $msg = "Password berhasil diubah.";
                } else {
                    $errors[] = "Gagal mengubah password: " . $conn->error;
                }
                $up->close();
            }
            $st->close();
        }
    }
}

// Fetch profile data
$stmt = $conn->prepare("SELECT id, full_name, email, created_at, updated_at FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Query untuk statistik
$product_count = $conn->query("SELECT COUNT(*) as total FROM products WHERE created_by = $uid")->fetch_assoc()['total'];
$recent_products = $conn->query("SELECT product_name, created_at FROM products WHERE created_by = $uid ORDER BY created_at DESC LIMIT 3");
?>

<div class="main-content">
    <!-- HEADER -->
    <header class="topbar">
        <div class="header-content">
            <h1 class="header-title">👤 Profil Pengguna</h1>
            <div class="header-user">
                <span>Selamat datang, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</span>
                        <a href="../dashboard/index.php" class="btn btn-secondary">
                        🏠 Kembali ke Dashboard
                        </a>
            </div>
        </div>
    </header>

    <!-- ALERTS -->
    <?php if($msg): ?>
        <div class="alert alert-success">
            <strong>✅ Success:</strong> <?= $msg ?>
        </div>
    <?php endif; ?>
    
    <?php if($errors): ?>
        <div class="alert alert-danger">
            <strong>❌ Error:</strong><br>
            <?= implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>

    <!-- PROFILE CONTENT GRID -->
    <div class="profile-content-grid">
        
        <!-- INFORMASI AKUN -->
        <div class="profile-section">
            <div class="section-header">
                <h3>📊 Informasi Akun</h3>
            </div>
            <div class="table-responsive">
                <table class="profile-info-table">
                    <tbody>
                        <tr>
                            <td class="label-cell"><strong>🆔 ID Pengguna</strong></td>
                            <td class="value-cell">
                                <span class="user-id"><?= htmlspecialchars($user['id']) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-cell"><strong>👤 Nama Lengkap</strong></td>
                            <td class="value-cell">
                                <span class="user-name"><?= htmlspecialchars($user['full_name']) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-cell"><strong>📧 Email</strong></td>
                            <td class="value-cell">
                                <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-cell"><strong>📅 Tanggal Daftar</strong></td>
                            <td class="value-cell">
                                <span class="user-date"><?= date('d F Y H:i', strtotime($user['created_at'])) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-cell"><strong>🕒 Terakhir Diupdate</strong></td>
                            <td class="value-cell">
                                <span class="user-updated <?= $user['updated_at'] ? 'active' : 'inactive' ?>">
                                    <?= $user['updated_at'] ? date('d F Y H:i', strtotime($user['updated_at'])) : 'Belum pernah diupdate' ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- STATISTIK AKTIVITAS -->
        <div class="profile-section">
            <div class="section-header">
                <h3>📈 Statistik Aktivitas</h3>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">📦</div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $product_count ?></div>
                        <div class="stat-label">Total Produk</div>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $user['updated_at'] ? 'Aktif' : 'Baru' ?></div>
                        <div class="stat-label">Status Akun</div>
                    </div>
                </div>
            </div>

            <?php if($product_count > 0): ?>
            <div class="recent-activity">
                <h4 class="recent-title">📝 Produk Terbaru</h4>
                <div class="table-responsive">
                    <table class="recent-products-table">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th width="100" class="text-center">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($product = $recent_products->fetch_assoc()): ?>
                                <tr>
                                    <td class="product-name-cell"><?= htmlspecialchars($product['product_name']) ?></td>
                                    <td class="text-center date-cell"><?= date('d/m/Y', strtotime($product['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
                <div class="empty-recent">
                    <p>📭 Belum ada produk yang dibuat</p>
                    <a href="products.php?action=add" class="btn btn-primary btn-sm">➕ Buat Produk Pertama</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- UPDATE PROFIL -->
        <div class="profile-section">
            <div class="section-header">
                <h3>✏️ Update Profil</h3>
            </div>
            
            <form method="post" action="" class="form-standard">
                <div class="form-group">
                    <label for="full_name">Nama Lengkap *</label>
                    <input id="full_name" name="full_name" type="text" 
                           value="<?= htmlspecialchars($user['full_name']) ?>" 
                           placeholder="Masukkan nama lengkap" required 
                           class="form-input" />
                </div>
                
                <div class="form-group">
                    <label for="email_display">Email</label>
                    <input id="email_display" type="text" 
                           value="<?= htmlspecialchars($user['email']) ?>" 
                           disabled class="form-input disabled-field" />
                    <small class="form-help">Email tidak dapat diubah</small>
                </div>
                
                <button type="submit" name="update_profile" class="btn btn-primary btn-full">
                    💾 Simpan Perubahan Profil
                </button>
            </form>
        </div>

        <!-- UBAH PASSWORD -->
        <div class="profile-section">
            <div class="section-header">
                <h3>🔒 Ubah Password</h3>
            </div>
            
            <form method="post" action="" class="form-standard">
                <div class="form-group">
                    <label for="old_password">Password Lama *</label>
                    <input id="old_password" name="old_password" type="password" 
                           placeholder="Masukkan password lama" required 
                           class="form-input" />
                </div>
                
                <div class="form-group">
                    <label for="password">Password Baru *</label>
                    <input id="password" name="password" type="password" 
                           placeholder="Minimal 6 karakter" required 
                           class="form-input" />
                    <small class="form-help">Password minimal 6 karakter</small>
                </div>
                
                <div class="form-group">
                    <label for="cpassword">Konfirmasi Password Baru *</label>
                    <input id="cpassword" name="cpassword" type="password" 
                           placeholder="Ulangi password baru" required 
                           class="form-input" />
                </div>
                
                <button type="submit" name="change_pass" class="btn btn-success btn-full">
                    🔑 Ubah Password
                </button>
            </form>
        </div>
    </div>


    <!-- FOOTER -->
    <footer class="main-footer">
        <div class="footer-content">
            <p>&copy; 2025 <strong>Gudang Asset</strong> | Profil Pengguna - <?= htmlspecialchars($user['full_name']) ?></p>
        </div>
    </footer>
</div>

<script>
// Theme toggle functionality
document.getElementById('themeToggle').addEventListener('click', () => {
    const html = document.documentElement;
    const newTheme = html.dataset.theme === 'dark' ? 'light' : 'dark';
    html.dataset.theme = newTheme;
    localStorage.setItem('theme', newTheme);
});

// Keep theme saved
const savedTheme = localStorage.getItem('theme');
if (savedTheme) document.documentElement.dataset.theme = savedTheme;
</script>
