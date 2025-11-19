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

// 2. Hitung statistik
$total_produk = 0;
$total_artikel = 0;
$total_event = 0;
$total_views_artikel = 0;
$total_views_event = 0;
$total_views_produk = 0;

// Count products
$result = mysqli_query($con, "SELECT COUNT(*) as total FROM products");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_produk = $row['total'];
}

// Count articles
$result = mysqli_query($con, "SELECT COUNT(*) as total FROM artikel");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_artikel = $row['total'];
}

// Count events
$result = mysqli_query($con, "SELECT COUNT(*) as total FROM events");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_event = $row['total'];
}

// Total views artikel
$result = mysqli_query($con, "SELECT SUM(pelihat) as total_views FROM artikel");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_views_artikel = $row['total_views'] ?? 0;
}

// Total views event
$result = mysqli_query($con, "SELECT SUM(pelihat) as total_views FROM events");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_views_event = $row['total_views'] ?? 0;
}

// Total views produk
$result = mysqli_query($con, "SELECT SUM(pelihat) as total_views FROM products");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_views_produk = $row['total_views'] ?? 0;
}

// Artikel terpopuler
$popular_articles = [];
$result = mysqli_query($con, "SELECT judul, pelihat FROM artikel ORDER BY pelihat DESC LIMIT 5");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $popular_articles[] = $row;
    }
}

// Event terpopuler
$popular_events = [];
$result = mysqli_query($con, "SELECT judul, pelihat FROM events ORDER BY pelihat DESC LIMIT 5");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $popular_events[] = $row;
    }
}

// Produk terpopuler
$popular_products = [];
$result = mysqli_query($con, "SELECT nama, pelihat FROM products ORDER BY pelihat DESC LIMIT 5");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $popular_products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Rumah Que Que</title>
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
            /* Sembunyikan scrollbar */
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }

        /* Sembunyikan scrollbar untuk Chrome, Safari dan Opera */
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
            text-align: center;
        }

        .main-header h2 {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Info Cards */
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 140, 66, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .card:hover {
            transform: translateY(-10px);
            border-color: rgba(255, 140, 66, 0.5);
            box-shadow: 0 20px 50px rgba(255, 140, 66, 0.3);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card .material-icons {
            font-size: 3rem;
            color: #ff8c42;
            margin-bottom: 1rem;
        }

        .card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }

        .card .count {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Statistics Section */
        .statistics-section {
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.5rem;
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon .material-icons {
            color: white;
            font-size: 1.5rem;
        }

        .stat-info h4 {
            font-size: 1rem;
            color: #b8b8b8;
            margin-bottom: 0.3rem;
        }

        .stat-info .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
        }

        /* Popular Lists */
        .popular-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .popular-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
        }

        .popular-card h3 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: #ff8c42;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .popular-list {
            list-style: none;
        }

        .popular-item {
            padding: 1rem;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
            margin-bottom: 0.8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .popular-item:hover {
            background: rgba(255, 140, 66, 0.1);
            transform: translateX(5px);
        }

        .popular-item-title {
            color: #e0e0e0;
            font-size: 0.95rem;
            flex: 1;
            margin-right: 1rem;
        }

        .popular-item-views {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            color: #ff8c42;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .popular-item-views .material-icons {
            font-size: 1rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #666;
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

            .info-cards {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .card {
                padding: 1.5rem;
            }

            .card .material-icons {
                font-size: 2.5rem;
            }

            .card h3 {
                font-size: 1.1rem;
            }

            .card .count {
                font-size: 2rem;
            }

            .main-header {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .main-header h2 {
                font-size: 1.6rem;
            }

            .section-title {
                font-size: 1.4rem;
                margin-bottom: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-card {
                padding: 1.2rem;
            }

            .stat-icon {
                width: 45px;
                height: 45px;
            }

            .stat-icon .material-icons {
                font-size: 1.3rem;
            }

            .stat-info h4 {
                font-size: 0.9rem;
            }

            .stat-info .stat-value {
                font-size: 1.5rem;
            }

            .popular-section {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .popular-card {
                padding: 1.5rem;
            }

            .popular-card h3 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            .popular-item {
                padding: 0.8rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .popular-item-title {
                font-size: 0.9rem;
                margin-right: 0;
            }

            .popular-item-views {
                font-size: 0.85rem;
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
                font-size: 1.4rem;
            }

            .card .count {
                font-size: 1.8rem;
            }

            .stat-info .stat-value {
                font-size: 1.3rem;
            }

            .popular-card h3 {
                font-size: 1rem;
            }

            .popular-item-title {
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
                    <li class="active">
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
                <h2>Informasi Website</h2>
            </header>

            <!-- Info Cards -->
            <section class="info-cards">
                <a class="card" href="../produk.php" target="_blank">
                    <span class="material-icons">inventory</span>
                    <h3>Total Produk</h3>
                    <p class="count"><?php echo number_format($total_produk); ?></p>
                </a>

                <a class="card" href="../artikel.php" target="_blank">
                    <span class="material-icons">article</span>
                    <h3>Total Artikel</h3>
                    <p class="count"><?php echo number_format($total_artikel); ?></p>
                </a>

                <a class="card" href="../event.php" target="_blank">
                    <span class="material-icons">event</span>
                    <h3>Total Event</h3>
                    <p class="count"><?php echo number_format($total_event); ?></p>
                </a>
            </section>

            <!-- Statistics Section -->
            <section class="statistics-section">
                <h2 class="section-title">Statistik Website</h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <span class="material-icons">visibility</span>
                            </div>
                            <div class="stat-info">
                                <h4>Total Views Artikel</h4>
                                <div class="stat-value"><?php echo number_format($total_views_artikel); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <span class="material-icons">remove_red_eye</span>
                            </div>
                            <div class="stat-info">
                                <h4>Total Views Event</h4>
                                <div class="stat-value"><?php echo number_format($total_views_event); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <span class="material-icons">shopping_cart</span>
                            </div>
                            <div class="stat-info">
                                <h4>Total Views Produk</h4>
                                <div class="stat-value"><?php echo number_format($total_views_produk); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Popular Content -->
                <div class="popular-section">
                    <!-- Popular Products -->
                    <div class="popular-card">
                        <h3>
                            <span class="material-icons">local_fire_department</span>
                            Produk Terpopuler
                        </h3>
                        <?php if (!empty($popular_products)): ?>
                            <ul class="popular-list">
                                <?php foreach ($popular_products as $product): ?>
                                    <li class="popular-item">
                                        <span class="popular-item-title"><?php echo htmlspecialchars($product['nama']); ?></span>
                                        <span class="popular-item-views">
                                            <span class="material-icons">visibility</span>
                                            <?php echo number_format($product['pelihat']); ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">Belum ada data produk</div>
                        <?php endif; ?>
                    </div>

                    <!-- Popular Articles -->
                    <div class="popular-card">
                        <h3>
                            <span class="material-icons">trending_up</span>
                            Artikel Terpopuler
                        </h3>
                        <?php if (!empty($popular_articles)): ?>
                            <ul class="popular-list">
                                <?php foreach ($popular_articles as $article): ?>
                                    <li class="popular-item">
                                        <span class="popular-item-title"><?php echo htmlspecialchars($article['judul']); ?></span>
                                        <span class="popular-item-views">
                                            <span class="material-icons">visibility</span>
                                            <?php echo number_format($article['pelihat']); ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">Belum ada data artikel</div>
                        <?php endif; ?>
                    </div>

                    <!-- Popular Events -->
                    <div class="popular-card">
                        <h3>
                            <span class="material-icons">stars</span>
                            Event Terpopuler
                        </h3>
                        <?php if (!empty($popular_events)): ?>
                            <ul class="popular-list">
                                <?php foreach ($popular_events as $event): ?>
                                    <li class="popular-item">
                                        <span class="popular-item-title"><?php echo htmlspecialchars($event['judul']); ?></span>
                                        <span class="popular-item-views">
                                            <span class="material-icons">visibility</span>
                                            <?php echo number_format($event['pelihat']); ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">Belum ada data event</div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
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
    </script>
</body>
</html>