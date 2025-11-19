<?php
include "koneksi.php";

$kategori_sql = "SELECT DISTINCT kategori FROM products WHERE status='published' AND kategori IS NOT NULL AND kategori != ''";
$kategori_result = $con->query($kategori_sql);
$kategori_options = [];
while ($row = $kategori_result->fetch_assoc()) {
    $kategori_options[] = $row['kategori'];
}

$q = mysqli_query($con, "SELECT text FROM announcement WHERE id = 1");
$data = mysqli_fetch_assoc($q);
$pengumuman = $data ? $data['text'] : "Tidak ada pengumuman";?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - Rumah Que Que</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Asimovian&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <script src="js/scr.js" defer></script>
</head>
<body>
    <div class="top-banner">
        <div class="marquee">
            <a href="#">
                <span class="arrow-left">→</span>
                <span><?php echo htmlspecialchars($pengumuman); ?></span>
                <span class="arrow-right">←</span>
            </a>
        </div>
    </div>

    <header class="header">
        <nav class="navbar">
            <div class="nav-group nav-left">
                <a href="index.php" class="nav-link">Beranda</a>
                <a href="tentangkami.php" class="nav-link">Tentang</a>
                <a href="produk.php" class="nav-link">Katalog</a>
            </div>
            <div class="nav-logo-container">
                <a href="index.php" class="nav-logo"><img src="logo.png" style="width:100px; height:80px;"></a>
            </div>
            <div class="nav-group nav-right">
                <a href="artikel.php" class="nav-link">Artikel</a>
                <a href="event.php" class="nav-link">Event</a>
                <a href="galeri.php" class="nav-link">Galeri</a>
            </div>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
    </header>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay"></div>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <a href="index.php" class="nav-link">Beranda</a>
        <a href="tentangkami.php" class="nav-link">Tentang</a>
        <a href="produk.php" class="nav-link">Katalog</a>
        <a href="artikel.php" class="nav-link">Artikel</a>
        <a href="event.php" class="nav-link">Event</a>
        <a href="galeri.php" class="nav-link">Galeri</a>
    </div>


    <section class="gallery-hero">
        <div class="gallery-hero-content">
            <h1>Galeri <span class="highlight">Rumah Que Que</span></h1>
            <p>Jelajahi koleksi produk kami dalam pengalaman 3D yang menakjubkan. Klik pada gambar untuk melihat detail lebih lanjut.</p>
        </div>
    </section>

    <section class="globe-section">
        <div class="section-title">
            <h2>Koleksi Produk Kami</h2>
            <p>Klik gambar untuk melihat detail</p>
        </div>

        <div id="globe-container">
            <div class="loading">Memuat galeri 3D...</div>
        </div>

    </section>


    <div class="modal" id="imageModal">
        <div class="modal-content">
            <button class="modal-close" id="closeModal">×</button>
            <img src="" alt="" class="modal-image" id="modalImage">
            <div class="modal-info">
                <h3 id="modalTitle">Product Name</h3>
                <p id="modalDesc">Product Description</p>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Rumah Que Que</h4>
                <p>Kami menghadirkan produk berkualitas premium dengan harga terbaik untuk melengkapi setiap kebutuhan Anda. Dibuat dengan standar kualitas tinggi dan pelayanan terbaik.</p>
            </div>
            <div class="footer-section">
                <h4>Kategori</h4>
                <ul class="footer-links">
                    <?php if (!empty($kategori_options)): ?>
                        <?php foreach ($kategori_options as $kat): ?>
                            <li><a href="produk.php?kategori=<?php echo urlencode($kat); ?>"><?php echo htmlspecialchars($kat); ?></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li style="color: #888;">Belum ada kategori</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Informasi</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="produk.php">Katalog</a></li>
                    <li><a href="galeri.php">Galeri</a></li>
                    <li><a href="artikel.php">Artikel</a></li>
                    <li><a href="tentang.php">Tentang Kami</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Rumah Que Que. All rights reserved.</p>
        </div>
    </footer>

    <a href="https://wa.me/6281234567890" target="_blank" class="whatsapp-float">
        <img src="https://cdn-icons-png.flaticon.com/512/124/124034.png" alt="WhatsApp" class="whatsapp-icon">
    </a>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    

</body>
</html>