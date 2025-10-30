<?php
// dashboard/products.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
$page_title = 'Management Product';
include __DIR__ . '/../includes/header.php'; 

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);
$errors = [];
$success = htmlspecialchars($_GET['success'] ?? '');
$row = [];

// LOGIKA CREATE / UPDATE (POST REQUEST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';
    
    $name = trim($_POST['product_name'] ?? '');
    $code = trim($_POST['product_code'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    $unit = trim($_POST['unit'] ?? '');
    $price = floatval($_POST['price'] ?? 0.00);
    $desc = trim($_POST['description'] ?? '');

    if ($name === '' || $code === '') {
        $errors[] = "Nama Produk & Kode Produk harus diisi.";
    }
    
    if (!$errors) {
        if ($mode === 'add') {
            $stmt = $conn->prepare("INSERT INTO products (product_name, product_code, category, stock, unit, price, description, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssisdsi", $name, $code, $category, $stock, $unit, $price, $desc, $_SESSION['user_id']);
            $success_message = "Produk baru berhasil ditambahkan.";
        } else {
            $stmt = $conn->prepare("UPDATE products SET product_name=?, product_code=?, category=?, stock=?, unit=?, price=?, description=?, updated_at=NOW() WHERE id=?");
            $stmt->bind_param("sssisdsi", $name, $code, $category, $stock, $unit, $price, $desc, $id);
            $success_message = "Data produk berhasil diperbarui.";
        }

        if ($stmt->execute()) {
            header("Location: products.php?success=" . urlencode($success_message));
            exit;
        } else {
            $errors[] = "Gagal menyimpan: " . $stmt->error;
        }
        $stmt->close();
    }
}

// LOGIKA DELETE
if ($action === 'delete' && $id > 0) {
    $del = $conn->prepare("DELETE FROM products WHERE id = ?");
    $del->bind_param("i", $id);
    if ($del->execute()) {
        $success = "Produk berhasil dihapus.";
        header("Location: products.php?success=" . urlencode($success));
        exit;
    } else {
        $errors[] = "Gagal menghapus produk: " . $conn->error;
    }
    $del->close();
}

// FETCH DATA UNTUK FORM EDIT
if ($action === 'edit' && $id > 0) {
    $st = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $st->bind_param("i", $id);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    if ($r) {
        $row = $r;
    } else {
        $errors[] = "Produk tidak ditemukan.";
        $action = '';
    }
    $st->close();
}

// FETCH LIST DATA PRODUK
$res = $conn->query("SELECT p.*, u.full_name AS creator 
                     FROM products p 
                     LEFT JOIN users u ON p.created_by = u.id 
                     ORDER BY p.created_at DESC");
?>

    <!-- MODULE SECTION -->
    <div class="module-section">
        <h2 class="module-title">📦 Management Produk</h2>
        
        <div class="module-card">
            <h3 class="module-subtitle">Tambah Produk Baru</h3>
            <p class="module-info">Total: <?= $res->num_rows ?> produk</p>
            
            <!-- ✅ BUTTON ACTION DI LUAR TABLE -->
            <div class="module-actions">
                <a href="products.php?action=add" class="btn btn-primary btn-large">
                    ➕ Tambah Produk Baru
                </a>
                <a href="../dashboard/index.php" class="btn btn-secondary">
                    🏠 Kembali ke Dashboard
                </a>
                <a href="products.php" class="btn btn-outline">
                    🔄 Refresh Data
                </a>
            </div>
        </div>
    </div>

    <!-- ALERTS -->
    <?php if($errors): ?>
        <div class="alert alert-danger">
            <strong>❌ Error:</strong><br>
            <?= implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success">
            <strong>✅ Success:</strong> <?= $success ?>
            
            <!-- ✅ BUTTON ACTION SETELAH SUKSES -->
            <div class="success-actions">
                <a href="products.php?action=add" class="btn btn-primary btn-sm">
                    ➕ Tambah Produk Lain
                </a>
                <a href="products.php" class="btn btn-secondary btn-sm">
                    📋 Lihat Semua Produk
                </a>
                <a href="../dashboard/index.php" class="btn btn-success btn-sm">
                    🏠 Dashboard
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if($action === 'add' || ($action === 'edit' && $id > 0 && !empty($row))): ?>
        
    <!-- FORM TAMBAH/EDIT -->
    <div class="section form-container">
        <h3 class="section-title">
            <?= $action === 'add' ? '➕ Tambah Produk Baru' : '✏️ Edit Produk: ' . htmlspecialchars($row['product_name'] ?? 'ID ' . $id) ?>
        </h3>
        
        <form method="post" action="" class="form-standard">
            <input type="hidden" name="mode" value="<?= $action === 'add' ? 'add' : 'edit' ?>" />
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="product_name">Nama Produk *</label>
                    <input id="product_name" name="product_name" type="text" 
                           value="<?= htmlspecialchars($row['product_name'] ?? '') ?>" 
                           placeholder="Masukkan nama produk" required />
                </div>
                
                <div class="form-group">
                    <label for="product_code">Kode Produk *</label>
                    <input id="product_code" name="product_code" type="text" 
                           value="<?= htmlspecialchars($row['product_code'] ?? '') ?>" 
                           placeholder="Masukkan kode produk" required />
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="category">Kategori</label>
                    <input id="category" name="category" type="text" 
                           value="<?= htmlspecialchars($row['category'] ?? '') ?>" 
                           placeholder="Kategori produk" />
                </div>
                
                <div class="form-group">
                    <label for="unit">Satuan</label>
                    <input id="unit" name="unit" type="text" 
                           value="<?= htmlspecialchars($row['unit'] ?? '') ?>" 
                           placeholder="Pcs, Kg, Unit, dll" />
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="stock">Stok</label>
                    <input id="stock" name="stock" type="number" min="0" 
                           value="<?= intval($row['stock'] ?? 0) ?>" 
                           placeholder="Jumlah stok" />
                </div>
                
                <div class="form-group">
                    <label for="price">Harga (Rp)</label>
                    <input id="price" name="price" type="number" step="0.01" 
                           value="<?= htmlspecialchars($row['price'] ?? 0.00) ?>" 
                           placeholder="Harga produk" />
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi Produk</label>
                <textarea id="description" name="description" 
                          placeholder="Deskripsi lengkap produk"><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-success btn-large">
                    💾 Simpan Data Produk
                </button>
                <a href="products.php" class="btn btn-secondary">
                    ↩️ Kembali ke Daftar
                </a>
                <a href="../dashboard/index.php" class="btn btn-outline">
                    🏠 Dashboard
                </a>
            </div>
        </form>
    </div>

    <?php else: ?>

    <!-- DATA TABLE SECTION -->
    <div class="section">
        <h3 class="section-title">📊 Data: Semua Produk</h3>
        
        <?php if($res->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="data-table product-table">
                <thead>
                    <tr>
                        <th width="60" class="text-center">NO</th>
                        <th>NAMA PRODUK</th>
                        <th width="100" class="text-center">KODE</th>
                        <th width="120">KATEGORI</th>
                        <th width="100" class="text-center">STOK</th>
                        <th width="130" class="text-right">HARGA (RP)</th>
                        <th width="120">DIBUAT OLEH</th>
                        <th width="110" class="text-center">TANGGAL</th>
                        <th width="100" class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while($p = $res->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td>
                                <div class="product-name"><?= htmlspecialchars($p['product_name']) ?></div>
                                <?php if(!empty($p['description'])): ?>
                                    <div class="product-desc"><?= htmlspecialchars(substr($p['description'], 0, 60)) ?>...</div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="product-code"><?= htmlspecialchars($p['product_code']) ?></span>
                            </td>
                            <td>
                                <span class="category-badge"><?= htmlspecialchars($p['category']) ?: '-' ?></span>
                            </td>
                            <td class="text-center">
                                <span class="stock-display <?= $p['stock'] < 10 ? 'low-stock' : 'normal-stock' ?>">
                                    <?= number_format($p['stock'], 0) ?> 
                                    <span class="unit"><?= htmlspecialchars($p['unit']) ?></span>
                                </span>
                            </td>
                            <td class="text-right">
                                <span class="price-value">Rp <?= number_format($p['price'], 0, ',', '.') ?></span>
                            </td>
                            <td>
                                <span class="creator"><?= htmlspecialchars($p['creator']) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="date"><?= date('d/m/Y', strtotime($p['created_at'])) ?></span>
                            </td>
                            <td class="text-center action-buttons">
                                <a href="products.php?action=edit&id=<?= $p['id'] ?>" 
                                   class="btn-icon edit-btn" 
                                   title="Edit Produk">✏️</a>
                                <a href="products.php?action=delete&id=<?= $p['id'] ?>" 
                                   onclick="return confirm('Yakin hapus produk <?= htmlspecialchars(addslashes($p['product_name'])) ?>?')" 
                                   class="btn-icon delete-btn" 
                                   title="Hapus Produk">🗑️</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <p>📭 Belum ada data produk</p>
                <a href="products.php?action=add" class="btn btn-primary">➕ Tambah Produk Pertama</a>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ✅ FOOTER DI TENGAH -->
    <footer class="main-footer">
        <div class="footer-content">
            <p>&copy; 2025 <strong>Gudang Asset</strong> | Dibuat oleh <?= htmlspecialchars($_SESSION['user_name']) ?></p>
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
