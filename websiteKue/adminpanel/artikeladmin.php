<?php
require 'session.php';
require "../koneksi.php";

// Definisikan nilai default
$currentImage = 'uploads/profiles/default.png';
$currentCropY = 50;

// 1. Ambil data pengguna
$session = $_SESSION['username'];
$stmt = $con->prepare("SELECT id, profile_image, profile_crop_y, role FROM users WHERE username = ?");

if ($stmt !== false) {
    $stmt->bind_param("s", $session);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $userId = $user['id'];
        $userRole = $user['role'] ?? 'Pengguna';

        if (!empty($user['profile_image'])) {
            $currentImage = htmlspecialchars($user['profile_image']);
        }
        $currentCropY = intval($user['profile_crop_y']);

        if ($currentCropY < 0 || $currentCropY > 100) {
            $currentCropY = 50; 
        }
    }
}

// 2. Pagination Setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Minimal halaman 1
$offset = ($current_page - 1) * $items_per_page;

// Hitung total artikel
$count_sql = "SELECT COUNT(*) as total FROM artikel";
$count_result = $con->query($count_sql);
$total_items = 0;
if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    $total_items = $count_row['total'];
}
$total_pages = ceil($total_items / $items_per_page);

// 3. Query untuk mengambil artikel dengan pagination
$sql = "SELECT id_artikel, judul, tanggal, pelihat, kategori, gambar, crop_y
        FROM artikel 
        ORDER BY tanggal DESC
        LIMIT $items_per_page OFFSET $offset";

$result = $con->query($sql); 
$has_articles = ($result && $result->num_rows > 0);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel - Rumah Que Que</title>
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

        /* Dashboard Container */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: rgba(10, 31, 15, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .sidebar::-webkit-scrollbar {
            display: none;
        }

        .logo {
            padding: 0 2rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 2rem;
        }

        .logo h1 {
            font-size: 1.8rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Profile Card */
        .profile-card {
            padding: 0 2rem;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-pic-link {
            display: block;
            width: 100px;
            height: 100px;
            margin-bottom: 1rem;
            position: relative;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid rgba(255, 140, 66, 0.3);
            transition: all 0.3s;
        }

        .profile-pic-link:hover {
            border-color: #ff8c42;
            transform: scale(1.05);
        }

        .profile-pic {
            width: 100%;
            height: 100%;
        }

        .edit-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .profile-pic-link:hover .edit-overlay {
            opacity: 1;
        }

        .profile-info {
            text-align: center;
        }

        .profile-info h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }

        .status {
            display: inline-block;
            padding: 0.4rem 1rem;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Navigation Menu */
        .navigation-menu {
            padding: 0 1rem;
        }

        .navigation-menu ul {
            list-style: none;
        }

        .navigation-menu li {
            margin-bottom: 0.5rem;
        }

        .navigation-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            color: #b8b8b8;
            text-decoration: none;
            border-radius: 15px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .navigation-menu a:hover {
            background: rgba(255, 140, 66, 0.1);
            color: #ff8c42;
            transform: translateX(5px);
        }

        .navigation-menu li.active a {
            background: linear-gradient(135deg, rgba(255, 140, 66, 0.2), rgba(255, 166, 98, 0.1));
            color: #ff8c42;
            border-left: 3px solid #ff8c42;
        }

        .navigation-menu .material-icons {
            font-size: 1.3rem;
        }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1100;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.4);
            transition: all 0.3s;
        }

        .mobile-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(255, 140, 66, 0.6);
        }

        .mobile-toggle .material-icons {
            color: white;
            font-size: 1.5rem;
            display: block;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
            backdrop-filter: blur(5px);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2rem;
        }

        /* Header */
        .main-header {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .main-header h2 {
            font-size: 2rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-tambah {
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 15px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-tambah:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 140, 66, 0.4);
        }

        /* Article List */
        .artikel-list-container {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
        }

        .artikel-list {
            display: flex;
            flex-direction: column;
        }

        .artikel-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }

        .artikel-item:last-child {
            border-bottom: none;
        }

        .artikel-item:hover {
            background: rgba(255, 140, 66, 0.05);
        }

        .artikel-thumbnail {
            width: 120px;
            height: 80px;
            border-radius: 12px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .artikel-info {
            flex: 1;
            min-width: 0;
        }

        .artikel-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
        }

        .artikel-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #ffffff;
            margin: 0;
        }

        .kategori-badge {
            background: rgba(255, 140, 66, 0.2);
            color: #ff8c42;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid rgba(255, 140, 66, 0.3);
        }

        .artikel-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            color: #b8b8b8;
            font-size: 0.9rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .meta-item .material-icons {
            font-size: 1rem;
            color: #ff8c42;
        }

        .artikel-actions {
            display: flex;
            gap: 0.8rem;
            flex-shrink: 0;
        }

        .btn-edit, .btn-hapus {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-edit {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
            border: 1px solid rgba(52, 152, 219, 0.3);
            text-decoration: none;
        }

        .btn-edit:hover {
            background: rgba(52, 152, 219, 0.3);
            transform: translateY(-2px);
        }

        .btn-hapus {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .btn-hapus:hover {
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
            padding: 4rem 2rem;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
        }

        .empty-state .material-icons {
            font-size: 5rem;
            color: #ff8c42;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #b8b8b8;
        }

        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 250px;
            }

            .main-content {
                margin-left: 250px;
            }
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }

            .sidebar {
                width: 280px;
                position: fixed;
                left: -280px;
                top: 0;
                height: 100vh;
                transition: left 0.3s ease;
                z-index: 1001;
                border-right: 1px solid rgba(255, 255, 255, 0.1);
            }

            .sidebar.active {
                left: 0;
            }

            .mobile-overlay.active {
                display: block;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
                padding-top: 4rem;
                width: 100%;
            }

            .logo {
                padding: 0 2rem 1.5rem;
                margin-bottom: 1.5rem;
            }

            .logo h1 {
                font-size: 1.5rem;
            }

            .profile-card {
                flex-direction: column;
                align-items: center;
                padding: 0 1.5rem;
                margin-bottom: 1.5rem;
            }

            .profile-pic-link {
                width: 80px;
                height: 80px;
                margin-bottom: 1rem;
            }

            .profile-info {
                text-align: center;
            }

            .profile-info h3 {
                font-size: 1.1rem;
            }

            .status {
                font-size: 0.8rem;
                padding: 0.3rem 0.8rem;
            }

            .navigation-menu {
                padding: 0 1rem;
            }

            .navigation-menu ul {
                display: block;
            }

            .navigation-menu li {
                margin-bottom: 0.3rem;
            }

            .navigation-menu a {
                padding: 0.9rem 1.2rem;
                font-size: 0.95rem;
            }

            .main-header {
                flex-direction: column;
                align-items: stretch;
                padding: 1.5rem;
            }

            .main-header h2 {
                font-size: 1.5rem;
                text-align: center;
            }

            .btn-tambah {
                width: 100%;
                justify-content: center;
            }

            .artikel-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1.2rem;
            }

            .artikel-thumbnail {
                width: 100%;
                height: 150px;
            }

            .artikel-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .artikel-title {
                font-size: 1rem;
            }

            .artikel-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .artikel-actions {
                width: 100%;
                flex-direction: column;
            }

            .btn-edit, .btn-hapus {
                width: 100%;
                justify-content: center;
            }

            .pagination {
                gap: 0.3rem;
            }

            .pagination a, .pagination span {
                padding: 0.5rem 0.8rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 0.8rem;
                padding-top: 4rem;
            }

            .main-header {
                padding: 1.2rem;
            }

            .main-header h2 {
                font-size: 1.3rem;
            }

            .artikel-item {
                padding: 1rem;
            }

            .artikel-thumbnail {
                height: 120px;
            }

            .artikel-title {
                font-size: 0.95rem;
            }

            .artikel-meta {
                font-size: 0.85rem;
            }

            .pagination a, .pagination span {
                padding: 0.4rem 0.6rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-toggle" onclick="toggleMenu()">
        <span class="material-icons">menu</span>
    </button>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" onclick="toggleMenu()"></div>

    <div class="dashboard-container">
        <!-- Sidebar -->
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
                    <li class="active">
                        <a href="artikeladmin.php">
                            <span class="material-icons">article</span>
                            Artikel
                        </a>
                    </li>
                    <li>
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

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <h2>Kelola Artikel</h2>
                <a href="tambah.php" class="btn-tambah">
                    <span class="material-icons">add_circle</span>
                    Tambah Artikel
                </a>
            </header>

            <!-- Article List -->
            <?php if (!$has_articles): ?>
                <div class="empty-state">
                    <span class="material-icons">article</span>
                    <h3>Belum Ada Artikel</h3>
                    <p>Klik tombol "Tambah Artikel" untuk membuat artikel pertama Anda</p>
                </div>
            <?php else: ?>
                <div class="artikel-list-container">
                    <div class="artikel-list">
                        <?php while($row = $result->fetch_assoc()): 
                            $kategori_label = htmlspecialchars($row['kategori']);
                            $image_url = !empty($row['gambar']) ? 'gambar/' . htmlspecialchars($row['gambar']) : '';
                            $crop_position = $row['crop_y'] . '%';
                        ?>
                            <div class="artikel-item">
                                <div 
                                    class="artikel-thumbnail"
                                    style="
                                        <?php if (!empty($image_url)): ?>
                                            background-image: url('<?php echo $image_url; ?>');
                                            background-position: center <?php echo $crop_position; ?>;
                                        <?php else: ?>
                                            background: linear-gradient(135deg, rgba(255, 140, 66, 0.2), rgba(255, 166, 98, 0.1));
                                        <?php endif; ?>
                                    "
                                ></div>
                                
                                <div class="artikel-info">
                                    <div class="artikel-header">
                                        <h3 class="artikel-title"><?php echo htmlspecialchars($row['judul']); ?></h3>
                                        <span class="kategori-badge"><?php echo $kategori_label; ?></span>
                                    </div>
                                    
                                    <div class="artikel-meta">
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

                                <div class="artikel-actions">
                                    <a href="edit.php?id=<?php echo $row['id_artikel']; ?>" class="btn-edit">
                                        <span class="material-icons" style="font-size: 1rem;">edit</span>
                                        Edit
                                    </a>
                                    <button 
                                        class="btn-hapus" 
                                        onclick="confirmDelete(<?php echo $row['id_artikel']; ?>)"
                                    >
                                        <span class="material-icons" style="font-size: 1rem;">delete</span>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
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
                        // Show page numbers
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
            <?php $con->close(); ?>
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
            if (confirm("Apakah Anda yakin ingin menghapus artikel ini?")) {
                window.location.href = 'hapus.php?id=' + id;
            }
        }
    </script>
</body>
</html>