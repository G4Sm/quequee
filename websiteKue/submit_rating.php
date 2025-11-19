<?php
include 'koneksi.php'; 

// Cek apakah data POST telah dikirim
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: produk.php");
    exit();
}

$product_id = intval($_POST['product_id'] ?? 0);
$raw_code = trim($_POST['review_code'] ?? '');
$rating_value = intval($_POST['rating_value'] ?? 0);
$komentar = trim($_POST['komentar'] ?? '');

// 1. Validasi Input Dasar
if ($product_id <= 0 || empty($raw_code) || $rating_value < 1 || $rating_value > 5) {
    header("Location: baca_produk.php?id={$product_id}&rating_status=missing_data");
    exit();
}

// Hash kode yang diinput user untuk dibandingkan dengan hash di database
$code_hash = hash('sha256', $raw_code); 

// --- 2. Validasi Kode Unik ---
// Cari kode di tabel rating_codes: harus cocok hash, belum digunakan (used_status=FALSE), dan product_id harus sesuai
$stmt_code = $con->prepare("SELECT id_code FROM rating_codes WHERE code_hash = ? AND product_id = ? AND used_status = FALSE");
$stmt_code->bind_param("si", $code_hash, $product_id);
$stmt_code->execute();
$result_code = $stmt_code->get_result();

if ($result_code->num_rows === 0) {
    // Kode tidak ditemukan, sudah digunakan, atau ID produk salah
    $stmt_code->close();
    $con->close();
    header("Location: baca_produk.php?id={$product_id}&rating_status=invalid_code");
    exit();
}

$row_code = $result_code->fetch_assoc();
$valid_code_id = $row_code['id_code'];
$stmt_code->close();

// --- 3. Memulai Transaksi Database (Penting!) ---
// Gunakan transaksi agar semua query (3 update) berhasil atau gagal bersamaan
$con->begin_transaction();
$success = true;

try {
    // A. Masukkan Rating ke tabel 'ratings'
    $stmt_insert = $con->prepare("INSERT INTO ratings (product_id, rating, komentar) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iis", $product_id, $rating_value, $komentar);
    if (!$stmt_insert->execute()) {
        $success = false;
    }
    $stmt_insert->close();
    
    // B. Tandai Kode sebagai Sudah Digunakan di tabel 'rating_codes'
    if ($success) {
        $stmt_use_code = $con->prepare("UPDATE rating_codes SET used_status = TRUE WHERE id_code = ?");
        $stmt_use_code->bind_param("i", $valid_code_id);
        if (!$stmt_use_code->execute()) {
            $success = false;
        }
        $stmt_use_code->close();
    }
    
    // C. Hitung Ulang Rata-rata Rating di tabel 'products'
    // SQL ini menghitung ulang rata-rata rating (AVG) dan jumlah total rating (COUNT)
    if ($success) {
        $stmt_update_product = $con->prepare("
            UPDATE products p
            SET 
                rata_rating = (SELECT AVG(rating) FROM ratings WHERE product_id = p.id_product),
                total_ratings = (SELECT COUNT(rating) FROM ratings WHERE product_id = p.id_product)
            WHERE p.id_product = ?
        ");
        $stmt_update_product->bind_param("i", $product_id);
        if (!$stmt_update_product->execute()) {
            $success = false;
        }
        $stmt_update_product->close();
    }
    
    // --- 4. Commit atau Rollback Transaksi ---
    if ($success) {
        $con->commit();
        $con->close();
        header("Location: baca_produk.php?id={$product_id}&rating_status=success");
        exit();
    } else {
        $con->rollback(); // Batalkan semua perubahan
        throw new Exception("Database operation failed.");
    }

} catch (Exception $e) {
    // Log error jika diperlukan
    // error_log("Rating submission failed: " . $e->getMessage()); 
    $con->close();
    header("Location: baca_produk.php?id={$product_id}&rating_status=error"); // Status error umum
    exit();
}
?>