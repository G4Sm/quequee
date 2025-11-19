<?php
// File: EventManager.php

require_once 'Database.php'; // Kelas PDO Wrapper yang sudah dibahas
require_once 'Event.php';

// File: EventManager.php

// Pastikan Event.php di-require di sini
// require_once 'Database.php'; 
// require_once 'Event.php'; 

class EventManager {
    
    private const UPLOAD_DIR = 'gambar/';

    // --- READ (Untuk Dashboard) ---

    public function getTotalEvents(): int {
        $sql = "SELECT COUNT(*) as total FROM events";
        $stmt = Database::getConnection()->query($sql);
        return $stmt->fetchColumn(); 
    }

    /**
     * Metode 2: Mengambil daftar event dengan pagination
     * @return Event[]
     */
    public function getPaginatedEvents(int $items_per_page, int $offset): array {
        $sql = "SELECT id_event, judul, tanggal, pelihat, kategori, gambar, crop_y
                FROM events 
                ORDER BY tanggal DESC
                LIMIT :limit OFFSET :offset";
        
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // HANYA MASUKKAN PARAMETER YANG DIJAMIN ADA DI KONSTRUKTOR EVENT ANDA (Tanpa $isi)
            $events[] = new Event(
                id_event: $row['id_event'],
                judul: $row['judul'],
                tanggal: $row['tanggal'],
                pelihat: $row['pelihat'],
                kategori: $row['kategori'],
                gambar: $row['gambar'],
                crop_y: $row['crop_y']
            );
        }
        return $events;
    }

    // --- READ (Untuk Edit Page) ---

    /**
     * Mengambil event berdasarkan ID. Mengembalikan array agar $isi tidak konflik.
     * @param int $id_event
     * @return array|null
     */
    public function getEventById(int $id_event): ?array { 
        $pdo = Database::getConnection();
        $sql = "SELECT id_event, judul, isi, tanggal, pelihat, kategori, gambar, crop_y 
                FROM events 
                WHERE id_event = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_event]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }
        
        // KEMBALIKAN ARRAY LENGKAP UNTUK DIPROSES OLEH edit_event.php
        return [
            'id_event' => $row['id_event'],
            'judul' => $row['judul'],
            'isi' => $row['isi'], // <--- Kolom 'isi' dijamin ada dalam array
            'tanggal' => $row['tanggal'] ?? null,
            'pelihat' => $row['pelihat'] ?? 0,
            'kategori' => $row['kategori'],
            'gambar' => $row['gambar'],
            'crop_y' => $row['crop_y']
        ];
    }
    
    // --- UPDATE ---

    /**
     * Mengupdate event yang sudah ada.
     * @return array Data event yang sudah diperbarui (untuk view lama)
     * @throws Exception
     */
    public function updateEvent(
        int $id_event, 
        string $judul, 
        array $fileData, 
        array $paragrafData, 
        string $kategori, 
        int $crop_y, 
        ?string $old_gambar
    ): array {
        
        $pdo = Database::getConnection();
        
        // 1. Validasi dan Format Isi
        $judul = trim($judul);
        if (empty($judul)) {
            throw new Exception("Judul event tidak boleh kosong.");
        }
        $crop_y = max(0, min(100, $crop_y));

        $isi_html = '';
        foreach ($paragrafData as $p) {
            if (!empty(trim($p))) {
                $isi_html .= '<p>' . htmlspecialchars(trim($p)) . '</p>';
            }
        }
        if (empty($isi_html)) {
            throw new Exception("Isi event harus memiliki setidaknya satu paragraf.");
        }
        
        // 2. File Upload Handling
        $new_gambar_name = $old_gambar; 
        
        if (isset($fileData['error']) && $fileData['error'] === UPLOAD_ERR_OK) {
            
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $file_ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_ext)) {
                throw new Exception("Ekstensi file tidak valid.");
            }
            
            if ($old_gambar && file_exists(self::UPLOAD_DIR . $old_gambar)) {
                @unlink(self::UPLOAD_DIR . $old_gambar); 
            }
            
            $new_gambar_name = time() . '-' . uniqid() . '.' . $file_ext;
            $target_file = self::UPLOAD_DIR . $new_gambar_name;

            if (!move_uploaded_file($fileData['tmp_name'], $target_file)) {
                throw new Exception("Gagal mengupload file baru.");
            }
        }

        // 3. Database Update
        $sql = "UPDATE events SET 
                judul = :judul, 
                isi = :isi, 
                kategori = :kategori, 
                gambar = :gambar, 
                crop_y = :crop_y
                WHERE id_event = :id_event";
                
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([
            ':judul' => $judul,
            ':isi' => $isi_html,
            ':kategori' => $kategori,
            ':gambar' => $new_gambar_name,
            ':crop_y' => $crop_y,
            ':id_event' => $id_event
        ]);

        if (!$success) {
            throw new Exception("Gagal mengupdate event di database.");
        }

        // 4. Kembalikan array lengkap untuk me-refresh form
        return [
            'id_event' => $id_event,
            'judul' => $judul,
            'gambar' => $new_gambar_name,
            'isi' => $isi_html,
            'kategori' => $kategori,
            'crop_y' => $crop_y,
        ];
    }
    
    // --- DELETE ---

    /**
     * Menghapus event dari database dan file gambar terkait.
     * @param int $id_event
     * @return bool
     * @throws Exception
     */
    public function deleteEvent(int $id_event): bool {
        
        $uploadDir = self::UPLOAD_DIR; 
        $pdo = Database::getConnection();

        // 1. Ambil nama file gambar
        $sql_select = "SELECT gambar FROM events WHERE id_event = ?";
        $stmt_select = $pdo->prepare($sql_select);
        $stmt_select->execute([$id_event]);
        $row_select = $stmt_select->fetch(PDO::FETCH_ASSOC);
        $image_to_delete = $row_select['gambar'] ?? null;
        
        if (!$row_select) {
             throw new Exception("Event dengan ID #$id_event tidak ditemukan.");
        }

        // 2. Hapus Event dari Database
        $sql_delete = "DELETE FROM events WHERE id_event = ?";
        $stmt_delete = $pdo->prepare($sql_delete);
        
        if (!$stmt_delete->execute([$id_event])) {
            throw new Exception("Gagal menghapus event dari database.");
        }
        
        // 3. Hapus File Gambar Fisik
        if ($image_to_delete && file_exists($uploadDir . $image_to_delete)) {
            if (!@unlink($uploadDir . $image_to_delete)) { 
                error_log("Gagal menghapus file gambar fisik: " . $uploadDir . $image_to_delete);
            }
        }
        
        return true;
    }


    // Create
    /**
     * Membuat event baru, termasuk upload file dan insert ke database.
     * @param string $judul
     * @param array $fileData Data dari $_FILES['gambar']
     * @param array $paragrafData Data dari $_POST['paragraf']
     * @param string $kategori
     * @param int $crop_y
     * @return bool
     * @throws Exception Jika terjadi kegagalan (validasi atau database).
     */
    public function createEvent(
        string $judul, 
        array $fileData, 
        array $paragrafData, 
        string $kategori, 
        int $crop_y
    ): bool {
        
        $pdo = Database::getConnection();
        
        // 1. Sanitasi dan Validasi Dasar
        $judul = trim($judul);
        $kategori = trim($kategori);
        $crop_y = max(0, min(100, $crop_y)); // Batasi crop_y antara 0 dan 100

        if (empty($judul)) {
            throw new Exception("Judul event tidak boleh kosong.");
        }

        // Gabungkan paragraf menjadi satu string HTML
        $isi_html = '';
        foreach ($paragrafData as $p) {
            if (!empty(trim($p))) {
                // Gunakan htmlspecialchars untuk keamanan, lalu bungkus dengan <p>
                $isi_html .= '<p>' . htmlspecialchars(trim($p)) . '</p>';
            }
        }
        if (empty($isi_html)) {
            throw new Exception("Isi event harus memiliki setidaknya satu paragraf.");
        }
        
        // 2. File Upload Handling
        if (!isset($fileData['error']) || $fileData['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Gambar event wajib diupload.");
        }
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_ext)) {
            throw new Exception("Ekstensi file tidak valid. Hanya JPG, JPEG, PNG, GIF yang diizinkan.");
        }
        
        // Buat nama file baru yang unik
        $new_gambar_name = time() . '-' . uniqid() . '.' . $file_ext;
        $target_file = self::UPLOAD_DIR . $new_gambar_name;

        // Pastikan direktori ada sebelum move
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0777, true);
        }

        // Pindahkan file
        if (!move_uploaded_file($fileData['tmp_name'], $target_file)) {
            throw new Exception("Gagal menyimpan file gambar.");
        }

        // 3. Database Insert
        $tanggal = date('Y-m-d');
        $status = 'published';
        $pelihat = 0;

        $sql = "INSERT INTO events (judul, gambar, isi, tanggal, kategori, pelihat, crop_y, status) 
                VALUES (:judul, :gambar, :isi, :tanggal, :kategori, :pelihat, :crop_y, :status)";
                
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([
            ':judul' => $judul,
            ':gambar' => $new_gambar_name,
            ':isi' => $isi_html,
            ':tanggal' => $tanggal,
            ':kategori' => $kategori,
            ':pelihat' => $pelihat,
            ':crop_y' => $crop_y,
            ':status' => $status,
        ]);

        if (!$success) {
             // Jika gagal insert, coba hapus file yang sudah diupload untuk cleanup
            if (file_exists($target_file)) {
                @unlink($target_file);
            }
            throw new Exception("Gagal menambahkan event ke database.");
        }

        return true;
    }
}?>