<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/config.php'; // ✅ path benar
$user_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin Gudang</title>
  <link rel="stylesheet" href="../assets/css/style.css"> <!-- ✅ path benar -->
</head>
<body class="dashboard-layout">

  <!-- Sidebar -->
  <aside class="sidebar">
    <h2>🧿 GUDANG</h2>
    <a href="index.php" class="active">🏠 Dashboard</a>
    <a href="products.php">📦 Produk</a>
    <a href="profile.php">👤 Profil</a>
    <a href="../auth/logout.php" class="logout">🚪 Logout</a>
  </aside>

  <!-- Content -->
  <main class="main-content">
    <header class="topbar">
      <span>Selamat datang, <?= htmlspecialchars($user_name) ?> 👋</span>
    </header>

    <section class="content-grid">
      <div class="card">
        <h3>Data Produk</h3>
        <p>Kelola daftar produk di gudang.</p>
        <a href="products.php" class="btn">Buka</a>
      </div>

      <div class="card">
        <h3>Profil Pengguna</h3>
        <p>Lihat dan ubah informasi akun Anda.</p>
        <a href="profile.php" class="btn">Buka</a>
      </div>
    </section>
  </main>

<script>
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

</body>
</html>
