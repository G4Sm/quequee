<?php
// File: eventadmin.php

require 'session.php';
// require "../koneksi.php"; // <--- HAPUS KONEKSI MYSQLI LAMA

// PANGGIL FILE PBO BARU
require_once 'Database.php';
require_once 'Event.php'; 
require_once 'EventManager.php'; 
require_once 'User.php'; 
require_once 'UserManager.php'; 

// --- INISIALISASI MANAGERS ---
$eventManager = new EventManager();
$userManager = new UserManager();

// Definisikan nilai default (masih diperlukan untuk view)
$currentImage = 'uploads/profiles/default.png';
$currentCropY = 50;
$userId = 0;
$userRole = 'Pengguna';
$session = $_SESSION['username'] ?? '';
$user = null; // Variabel $user harus ada untuk UI lama

// 1. PBO: Ambil data pengguna untuk Sidebar
try {
    $userObject = $userManager->getUserByUsername($session);
    
    if ($userObject) {
        $user = $userObject->toArray(); // Konversi ke array agar $user['key'] di HTML tetap jalan
        
        $userId = $userObject->id;
        $userRole = $userObject->role;
        $currentImage = $userObject->profile_image;
        $currentCropY = $userObject->profile_crop_y;
    }
} catch (Exception $e) {
    // Penanganan error pengambilan user
    error_log("Error fetching user data: " . $e->getMessage());
}


// 2. Pagination Setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $items_per_page;

$total_items = 0;
$total_pages = 0;
$result = null; // Variabel $result harus ada untuk loop while($row = $result->fetch_assoc())
$has_events = false;

// 3. PBO: Ambil total event
try {
    $total_items = $eventManager->getTotalEvents();
    $total_pages = ceil($total_items / $items_per_page);
    
    // 4. PBO: Query untuk mengambil event dengan pagination
    $eventObjects = $eventManager->getPaginatedEvents($items_per_page, $offset);
    
    // Untuk mempertahankan UI lama: Buat wrapper yang meniru hasil query MySQLi
    if (!empty($eventObjects)) {
        $has_events = true;
        // Kita membuat kelas MockResult untuk meniru objek MySQLi Result.
        // Ini adalah JEMBATAN agar kode HTML Anda (View) tidak perlu diubah.
        
        class MockResult {
            private $data = [];
            private $index = 0;
            public $num_rows = 0;

            public function __construct(array $data) {
                $this->data = $data;
                $this->num_rows = count($data);
            }

            // Meniru metode fetch_assoc()
            public function fetch_assoc(): ?array {
                if ($this->index < $this->num_rows) {
                    $row = $this->data[$this->index];
                    $this->index++;
                    return $row;
                }
                return null;
            }
        }
        
        // Konversi Event Object menjadi array $row untuk MockResult
        $eventArrays = array_map(function($event) {
            // Kita harus mengembalikan array dengan keys SAMA PERSIS 
            // seperti yang dibutuhkan di HTML: id_event, judul, tanggal, pelihat, kategori, gambar, crop_y
            return [
                'id_event' => $event->id_event,
                'judul' => $event->judul,
                'tanggal' => $event->tanggal,
                'pelihat' => $event->pelihat,
                'kategori' => $event->kategori,
                'gambar' => $event->gambar,
                'crop_y' => $event->crop_y,
            ];
        }, $eventObjects);
        
        $result = new MockResult($eventArrays);

    } else {
         $has_events = false;
    }

} catch (Exception $e) {
    // Penanganan error pengambilan event
    error_log("Error fetching event list: " . $e->getMessage());
    $has_events = false;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Event - Rumah Que Que</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="../css/adm.css" rel="stylesheet">

</head>
<body class="dashEvent">
    <button class="mobile-toggle" onclick="toggleMenu()">
        <span class="material-icons">menu</span>
    </button>

    <div class="mobile-overlay" onclick="toggleMenu()"></div>

    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <h1>Dashboard</h1>
            </div>

            <div class="profile-card">
                <a href="editpp.php?id=<?php echo $userId; ?>" class="profile-pic-link">
                    <div 
                        class="profile-pic" 
                        style="
                            background-image: url('<?php echo $currentImage; ?>?t=<?php echo time(); ?>'); 
                            background-position: center <?php echo $currentCropY; ?>%;
                            background-size: cover;
                            background-repeat: no-repeat;
                        "
                    ></div>
                    <div class="edit-overlay">Edit Foto</div>
                </a>

                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($session); ?></h3>
                    <span class="status"><?php echo htmlspecialchars($userRole); ?></span>
                </div>
            </div>

            <nav class="navigation-menu">
                <ul>
                    <li>
                        <a href="index.php">
                            <span class="material-icons">dashboard</span>
                            Beranda
                        </a>
                    </li>
                    <li>
                        <a href="produk_dashboard.php">
                            <span class="material-icons">shopping_bag</span>
                            Produk
                        </a>
                    </li>
                    <li>
                        <a href="artikeladmin.php">
                            <span class="material-icons">article</span>
                            Artikel
                        </a>
                    </li>
                    <li class="active">
                        <a href="eventadmin.php">
                            <span class="material-icons">event</span>
                            Event
                        </a>
                    </li>
                    <li>
                        <a href="pengumuman.php">
                            <span class="material-icons">campaign</span>
                            Konten Depan
                        </a>
                    </li>
                    <li>
                        <a href="logout.php">
                            <span class="material-icons">logout</span>
                            Keluar
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h2>Kelola Event</h2>
                <a href="tambah_event.php" class="btn-tambah">
                    <span class="material-icons">add_circle</span>
                    Tambah Event
                </a>
            </header>

            <?php if (!$has_events): ?>
                <div class="empty-state">
                    <span class="material-icons">event</span>
                    <h3>Belum Ada Event</h3>
                    <p>Klik tombol "Tambah Event" untuk membuat event pertama Anda</p>
                </div>
            <?php else: ?>
                <div class="event-list-container">
                    <div class="event-list">
                        <?php while($row = $result->fetch_assoc()): 
                            $kategori_label = htmlspecialchars($row['kategori']);
                            $image_url = !empty($row['gambar']) ? 'gambar/' . htmlspecialchars($row['gambar']) : '';
                            $crop_position = $row['crop_y'] . '%';
                        ?>
                            <div class="event-item">
                                <div 
                                    class="event-thumbnail"
                                    style="
                                        <?php if (!empty($image_url)): ?>
                                            background-image: url('<?php echo $image_url; ?>');
                                            background-position: center <?php echo $crop_position; ?>;
                                        <?php else: ?>
                                            background: linear-gradient(135deg, rgba(46, 204, 113, 0.2), rgba(46, 204, 113, 0.1));
                                        <?php endif; ?>
                                    "
                                ></div>
                                
                                <div class="event-info">
                                    <div class="event-header">
                                        <h3 class="event-title"><?php echo htmlspecialchars($row['judul']); ?></h3>
                                        <span class="kategori-badge"><?php echo $kategori_label; ?></span>
                                    </div>
                                    
                                    <div class="event-meta">
                                        <div class="meta-item">
                                            <span class="material-icons">calendar_today</span>
                                            <span><?php echo date('d M Y', strtotime($row['tanggal'])); ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="material-icons">visibility</span>
                                            <span><?php echo number_format($row['pelihat']); ?> views</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="event-actions">
                                    <a href="edit_event.php?id=<?php echo $row['id_event']; ?>" class="btn-edit">
                                        <span class="material-icons" style="font-size: 1rem;">edit</span>
                                        Edit
                                    </a>
                                    <button 
                                        class="btn-hapus" 
                                        onclick="confirmDelete(<?php echo $row['id_event']; ?>)"
                                    >
                                        <span class="material-icons" style="font-size: 1rem;">delete</span>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

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
            
            <?php // Tidak ada lagi $con->close() karena koneksi dikelola oleh Database::class ?>
        </main>
    </div>

    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.mobile-overlay');
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // Close sidebar when clicking on menu items in mobile
        document.addEventListener('DOMContentLoaded', function() {
            const menuLinks = document.querySelectorAll('.navigation-menu a');
            
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        toggleMenu();
                    }
                });
            });
        });

        function confirmDelete(id) {
            if (confirm("Apakah Anda yakin ingin menghapus event ini?")) {
                window.location.href = 'hapus_event.php?id=' + id;
            }
        }
    </script>
</body>
</html>