<?php
require 'session.php';
include '../koneksi.php';

$message = '';
$message_type = '';

// --- Fungsionalitas Tambah Kode ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_kode'])) {
    $raw_code = trim($_POST['code_input']);
    $product_id = intval($_POST['product_id']);

    if (empty($raw_code) || $product_id <= 0) {
        $message = 'Kode dan Produk wajib diisi.';
        $message_type = 'error';
    } else {
        // Hash kode sebelum disimpan untuk keamanan
        $code_hash = hash('sha256', $raw_code); 

        // 1. Cek duplikasi hash kode
        $stmt_check = $con->prepare("SELECT id_code FROM rating_codes WHERE code_hash = ?");
        $stmt_check->bind_param("s", $code_hash);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $message = 'Kode ini sudah pernah ditambahkan!';
            $message_type = 'error';
        } else {
            // 2. Insert kode hash ke database
            $stmt_insert = $con->prepare("INSERT INTO rating_codes (code_hash, product_id, used_status) VALUES (?, ?, FALSE)");
            $stmt_insert->bind_param("si", $code_hash, $product_id);

            if ($stmt_insert->execute()) {
                $message = 'Kode "' . htmlspecialchars($raw_code) . '" berhasil ditambahkan untuk Produk ID ' . $product_id;
                $message_type = 'success';
            } else {
                $message = 'Error: Gagal menyimpan kode. ' . $stmt_insert->error;
                $message_type = 'error';
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

// --- Fungsionalitas Hapus Kode ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_code = intval($_GET['id']);
    $stmt_delete = $con->prepare("DELETE FROM rating_codes WHERE id_code = ?");
    $stmt_delete->bind_param("i", $id_code);
    
    if ($stmt_delete->execute()) {
        header("Location: code.php?status=deleted");
        exit();
    } else {
        header("Location: code.php?status=error_delete");
        exit();
    }
    $stmt_delete->close();
}

// Tampilkan pesan status
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'deleted') {
        $message = 'Kode rating berhasil dihapus.';
        $message_type = 'success';
    } elseif ($_GET['status'] == 'error_delete') {
        $message = 'Gagal menghapus kode.';
        $message_type = 'error';
    }
}

// --- Pagination Setup ---
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $items_per_page;

// Hitung total kode
$count_sql = "SELECT COUNT(*) as total FROM rating_codes";
$count_result = $con->query($count_sql);
$total_items = 0;
if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    $total_items = $count_row['total'];
}
$total_pages = ceil($total_items / $items_per_page);

// --- Ambil Data ---
// 1. Daftar produk untuk dropdown
$product_list_sql = "SELECT id_product, nama FROM products WHERE status = 'published' ORDER BY nama ASC";
$product_list_result = $con->query($product_list_sql);

// 2. Semua kode rating dengan pagination
$codes_sql = "
    SELECT rc.id_code, rc.used_status, p.nama AS product_name, p.id_product 
    FROM rating_codes rc
    JOIN products p ON rc.product_id = p.id_product
    ORDER BY rc.id_code DESC
    LIMIT $items_per_page OFFSET $offset";
$codes_result = $con->query($codes_sql);
$has_codes = ($codes_result && $codes_result->num_rows > 0);

$con->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kode Rating - Rumah Que Que</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a1f0f;
            color: #fff;
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
            padding: 2rem;
        }

        /* Background Gradient Effects */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(255, 140, 66, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 166, 98, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(255, 140, 66, 0.05) 0%, transparent 40%);
            z-index: -1;
            animation: gradient-shift 15s ease infinite;
        }

        @keyframes gradient-shift {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(5%, 5%) rotate(120deg); }
            66% { transform: translate(-5%, 5%) rotate(240deg); }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Back Button */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #ff8c42;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            margin-bottom: 2rem;
        }

        .back-button:hover {
            background: rgba(255, 140, 66, 0.1);
            border-color: rgba(255, 140, 66, 0.3);
            transform: translateX(-5px);
        }

        /* Header */
        .page-header {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #b8b8b8;
            font-size: 0.95rem;
        }

        /* Alert */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        /* Form Container */
        .form-container {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-container h3 {
            font-size: 1.3rem;
            color: #ff8c42;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 2fr 2fr 1fr;
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            color: #e0e0e0;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group select {
            padding: 0.8rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: rgba(255, 140, 66, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }

        .form-group select option {
            background: #1a3320;
            color: #fff;
        }

        .btn-submit {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 140, 66, 0.4);
        }

        /* Table Container */
        .table-container {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
        }

        .table-container h3 {
            font-size: 1.3rem;
            color: #ff8c42;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .code-list {
            display: flex;
            flex-direction: column;
        }

        .code-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }

        .code-item:last-child {
            border-bottom: none;
        }

        .code-item:hover {
            background: rgba(255, 140, 66, 0.05);
        }

        .code-id {
            width: 60px;
            font-weight: 600;
            color: #b8b8b8;
            font-size: 0.9rem;
        }

        .code-product {
            flex: 1;
            color: #e0e0e0;
        }

        .code-product strong {
            color: #fff;
        }

        .code-status {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-used {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .status-available {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .btn-delete {
            padding: 0.5rem 1rem;
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-delete:hover {
            background: rgba(231, 76, 60, 0.3);
            transform: translateY(-2px);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 0.7rem 1.2rem;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #b8b8b8;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .pagination a:hover {
            background: rgba(255, 140, 66, 0.1);
            border-color: rgba(255, 140, 66, 0.3);
            color: #ff8c42;
            transform: translateY(-2px);
        }

        .pagination .active {
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            border-color: #ff8c42;
            color: white;
        }

        .pagination .disabled {
            opacity: 0.3;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #b8b8b8;
        }

        .empty-state .material-icons {
            font-size: 4rem;
            color: #ff8c42;
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .code-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.8rem;
            }

            .code-id {
                width: auto;
            }

            .btn-delete {
                width: 100%;
                justify-content: center;
            }

            .pagination a, .pagination span {
                padding: 0.5rem 0.8rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .page-header {
                padding: 1.5rem;
            }

            .form-container,
            .table-container {
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.3rem;
            }

            .pagination a, .pagination span {
                padding: 0.4rem 0.6rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <a href="produk_dashboard.php" class="back-button">
            <span class="material-icons">arrow_back</span>
            Kembali ke Dashboard Produk
        </a>

        <!-- Page Header -->
        <div class="page-header">
            <h1>Kelola Kode Rating</h1>
            <p>Buat dan kelola kode unik untuk sistem rating produk one-time use</p>
        </div>

        <!-- Alert -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <span class="material-icons">
                    <?php echo $message_type == 'success' ? 'check_circle' : 'error'; ?>
                </span>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Form Input Kode -->
        <div class="form-container">
            <h3>
                <span class="material-icons">add_circle</span>
                Input Kode Baru
            </h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="code_input">Kode Unik</label>
                        <input 
                            type="text" 
                            id="code_input" 
                            name="code_input" 
                            placeholder="Contoh: B3S7-22" 
                            required 
                            maxlength="100"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="product_id">Tujuan Produk</label>
                        <select id="product_id" name="product_id" required>
                            <option value="">-- Pilih Produk --</option>
                            <?php 
                            if ($product_list_result && $product_list_result->num_rows > 0) {
                                while($product = $product_list_result->fetch_assoc()) {
                                    echo '<option value="' . $product['id_product'] . '">' . htmlspecialchars($product['nama']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="tambah_kode" class="btn-submit">
                        <span class="material-icons" style="font-size: 1.2rem;">save</span>
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Kode -->
        <div class="table-container">
            <h3>
                <span class="material-icons">list</span>
                Daftar Kode Rating Aktif
            </h3>

            <?php if (!$has_codes): ?>
                <div class="empty-state">
                    <span class="material-icons">qr_code_2</span>
                    <p>Belum ada kode rating yang diinput</p>
                </div>
            <?php else: ?>
                <div class="code-list">
                    <?php while($row = $codes_result->fetch_assoc()): ?>
                        <div class="code-item">
                            <div class="code-id">ID: <?php echo htmlspecialchars($row['id_code']); ?></div>
                            <div class="code-product">
                                <strong><?php echo htmlspecialchars($row['product_name']); ?></strong>
                                <span style="color: #888; font-size: 0.9rem;"> (ID: <?php echo htmlspecialchars($row['id_product']); ?>)</span>
                            </div>
                            <span class="code-status <?php echo $row['used_status'] ? 'status-used' : 'status-available'; ?>">
                                <?php echo $row['used_status'] ? 'Digunakan' : 'Tersedia'; ?>
                            </span>
                            <a 
                                href="code.php?action=delete&id=<?php echo $row['id_code']; ?>" 
                                class="btn-delete"
                                onclick="return confirm('Yakin ingin menghapus kode ID <?php echo $row['id_code']; ?>?')"
                            >
                                <span class="material-icons" style="font-size: 1rem;">delete</span>
                                Hapus
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>">
                                <span class="material-icons" style="font-size: 1.2rem;">chevron_left</span>
                            </a>
                        <?php else: ?>
                            <span class="disabled">
                                <span class="material-icons" style="font-size: 1.2rem;">chevron_left</span>
                            </span>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        if ($start_page > 1) {
                            echo '<a href="?page=1">1</a>';
                            if ($start_page > 2) {
                                echo '<span>...</span>';
                            }
                        }

                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $current_page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor;

                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<span>...</span>';
                            }
                            echo '<a href="?page=' . $total_pages . '">' . $total_pages . '</a>';
                        }
                        ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>">
                                <span class="material-icons" style="font-size: 1.2rem;">chevron_right</span>
                            </a>
                        <?php else: ?>
                            <span class="disabled">
                                <span class="material-icons" style="font-size: 1.2rem;">chevron_right</span>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto hide alert after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            }
        });
    </script>
</body>
</html>