<?php
require 'session.php';
include '../koneksi.php';

$uploadDir = 'gambar/';

// --- 1. Validasi ID Produk ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: produk_dashboard.php?status=error&msg=no_id");
    exit();
}

$id_product = intval($_GET['id']);
$image_to_delete = null;

// --- 2. Ambil Nama File Gambar Lama sebelum Menghapus Produk ---
// Ini penting karena data gambar akan hilang setelah baris dihapus dari DB
$stmt_select = $con->prepare("SELECT gambar FROM products WHERE id_product = ?");
$stmt_select->bind_param("i", $id_product);
$stmt_select->execute();
$result_select = $stmt_select->get_result();

if ($result_select->num_rows === 1) {
    $row = $result_select->fetch_assoc();
    $image_to_delete = $row['gambar'];
}
$stmt_select->close();


// --- 3. Hapus Data Produk dari Database ---
$stmt_delete = $con->prepare("DELETE FROM products WHERE id_product = ?");
$stmt_delete->bind_param("i", $id_product);

if ($stmt_delete->execute()) {
    
    // --- 4. Hapus File Gambar dari Server ---
    if (!empty($image_to_delete) && file_exists($uploadDir . $image_to_delete)) {
         unlink($uploadDir . $image_to_delete);
    }

    // Redirect ke dashboard dengan pesan sukses
    header("Location: produk_dashboard.php?status=deleted");
    exit();
} else {
    // Jika gagal menghapus dari DB
    header("Location: produk_dashboard.php?status=error&msg=db_fail");
    exit();
}

$stmt_delete->close();
$con->close();
?>