<?php
// File: hapus_event.php

require 'session.php';
// include '../koneksi.php'; // <--- HAPUS

// PANGGIL FILE PBO BARU
require_once 'Event.php'; 
require_once 'EventManager.php'; 
require_once 'Database.php'; 

// Cek apakah ID event tersedia di URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_event = intval($_GET['id']); 
    $manager = new EventManager();

    try {
        // PBO: Panggil metode DELETE
        $manager->deleteEvent($id_event);
        
        // Redirect kembali ke dashboard dengan pesan sukses
        header("Location: eventadmin.php?status=event_deleted");
        exit();
        
    } catch (Exception $e) {
        // Tangkap exception jika terjadi kegagalan (misal: Event tidak ditemukan atau gagal delete DB)
        // Redirect kembali dengan pesan error
        error_log("Delete Event Error: " . $e->getMessage()); // Log error untuk debugging
        header("Location: eventadmin.php?status=error&msg=" . urlencode("Gagal menghapus event: " . $e->getMessage()));
        exit();
    }

} else {
    // Jika ID tidak valid, redirect ke dashboard
    header("Location: eventadmin.php");
    exit();
}

// $con->close(); // <--- HAPUS
?>