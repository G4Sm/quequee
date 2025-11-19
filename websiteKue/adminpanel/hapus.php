<?php
include '../koneksi.php'; 

// Cek apakah ID artikel tersedia di URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_artikel = intval($_GET['id']); // Pastikan ID adalah integer

    // PENTING: Ambil nama file gambar sebelum menghapus artikel
    $stmt_select = $con->prepare("SELECT gambar FROM artikel WHERE id_artikel = ?");
    $stmt_select->bind_param("i", $id_artikel);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();
    $row_select = $result_select->fetch_assoc();
    $image_to_delete = $row_select['gambar'] ?? null;
    $stmt_select->close();

    // 1. Hapus Artikel dari Database
    $stmt_delete = $con->prepare("DELETE FROM artikel WHERE id_artikel = ?");
    $stmt_delete->bind_param("i", $id_artikel);
    
    if ($stmt_delete->execute()) {
        
        // 2. Hapus File Gambar Fisik dari server
        if ($image_to_delete && file_exists('uploads/' . $image_to_delete)) {
            unlink('gambar/' . $image_to_delete);
        }

        // Redirect kembali ke dashboard dengan pesan sukses (opsional)
        header("Location: index.php?status=deleted");
        exit();
    } else {
        // Redirect kembali dengan pesan error
        header("Location: index.php?status=error&msg=delete_failed");
        exit();
    }

    $stmt_delete->close();

} else {
    // Jika ID tidak valid, redirect ke dashboard
    header("Location: index.php");
    exit();
}

$con->close();
?>