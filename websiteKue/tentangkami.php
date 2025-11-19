<?php
include "koneksi.php";

$q = mysqli_query($con, "SELECT text FROM announcement WHERE id = 1");
$data = mysqli_fetch_assoc($q);
$pengumuman = $data ? $data['text'] : "Tidak ada pengumuman";?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Rumah Que Que</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Asimovian&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>

</head>
<body>
    <!-- Top Banner -->
    <div class="top-banner">
        <div class="marquee">
            <a href="#">
                <span class="arrow-left">→</span>
                <span><?php echo htmlspecialchars($pengumuman); ?></span>
                <span class="arrow-right">←</span>
            </a>
        </div>
    </div>
        

    <!-- Header -->
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

    <!-- About Hero Section -->
    <section class="about-hero">
        <div class="about-hero-content">
            <h1>Tentang <span class="highlight">Rumah Que Que</span></h1>
            <p>Perjalanan kami dimulai dari kecintaan terhadap seni membuat kue dan keinginan untuk membawa kebahagiaan melalui setiap gigitan. Kami percaya bahwa setiap momen spesial layak dirayakan dengan kue yang tak terlupakan.</p>
        </div>
    </section>

    <!-- Story Section -->
    <section class="story-section">
        <div class="story-grid">
            <div class="story-image">
                <img src="https://images.unsplash.com/photo-1556910096-6f5e72db6803?w=800" alt="Our Story">
            </div>
            <div class="story-content">
                <h2>Cerita <span class="highlight">Kami</span></h2>
                <p>Rumah Que Que didirikan pada tahun 2015 dengan visi sederhana: menciptakan kue yang tidak hanya lezat, tetapi juga membawa kenangan indah bagi setiap pelanggan.</p>
                <p>Berawal dari dapur kecil dengan resep warisan keluarga, kini kami telah berkembang menjadi salah satu toko kue terpercaya dengan berbagai varian produk berkualitas premium.</p>
                <p>Setiap kue yang kami buat adalah hasil dari dedikasi, kreativitas, dan komitmen kami untuk memberikan yang terbaik. Kami menggunakan bahan-bahan pilihan berkualitas tinggi dan teknik pembuatan yang telah teruji untuk menghasilkan cita rasa yang istimewa.</p>
            </div>
        </div>

        <div class="story-grid" style="margin-top: 4rem;">
            <div class="story-content">
                <h2>Misi <span class="highlight">Kami</span></h2>
                <p>Kami berkomitmen untuk terus berinovasi dan menghadirkan produk-produk kue dengan kualitas terbaik yang dapat dinikmati oleh seluruh keluarga Indonesia.</p>
                <p>Kepuasan pelanggan adalah prioritas utama kami. Oleh karena itu, kami selalu mendengarkan masukan dan terus meningkatkan kualitas produk serta layanan kami.</p>
                <p>Dengan tim yang berpengalaman dan penuh dedikasi, kami siap melayani setiap pesanan Anda dengan sepenuh hati, mulai dari kue untuk acara pribadi hingga pesanan dalam jumlah besar untuk berbagai event.</p>
            </div>
            <div class="story-image">
                <img src="https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=800" alt="Our Mission">
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">10+</div>
                <div class="stat-label">Tahun Berpengalaman</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">50K+</div>
                <div class="stat-label">Pelanggan Puas</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100+</div>
                <div class="stat-label">Varian Produk</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">5000+</div>
                <div class="stat-label">Pesanan Per Bulan</div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="section-title">
            <h2>Nilai-Nilai Kami</h2>
            <p>Prinsip yang kami pegang teguh dalam setiap langkah</p>
        </div>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">🎯</div>
                <h3>Kualitas Premium</h3>
                <p>Kami hanya menggunakan bahan-bahan berkualitas terbaik untuk setiap produk yang kami buat, memastikan cita rasa yang konsisten dan lezat.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">❤️</div>
                <h3>Dibuat dengan Cinta</h3>
                <p>Setiap kue dibuat dengan penuh perhatian dan dedikasi, karena kami percaya bahwa cinta adalah bahan rahasia terpenting dalam setiap resep.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">🤝</div>
                <h3>Kepuasan Pelanggan</h3>
                <p>Kepuasan Anda adalah kebanggaan kami. Kami selalu memberikan pelayanan terbaik dan mendengarkan setiap masukan dengan serius.</p>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="section-title">
            <h2>Tim Kami</h2>
            <p>Orang-orang di balik kelezatan setiap produk kami</p>
        </div>
        <div class="team-grid">
            <div class="team-card">
                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=600" alt="Chef Manager" class="team-image">
                <div class="team-info">
                    <h3>Sarah Wijaya</h3>
                    <div class="role">Head Pastry Chef</div>
                    <p>Dengan pengalaman lebih dari 15 tahun di industri pastry, Sarah memimpin tim kami dalam menciptakan resep-resep istimewa.</p>
                </div>
            </div>
            <div class="team-card">
                <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=600" alt="Operations Manager" class="team-image">
                <div class="team-info">
                    <h3>Budi Santoso</h3>
                    <div class="role">Operations Manager</div>
                    <p>Budi memastikan setiap proses produksi berjalan lancar dan standar kualitas kami tetap terjaga dengan konsisten.</p>
                </div>
            </div>
            <div class="team-card">
                <img src="https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?w=600" alt="Quality Control" class="team-image">
                <div class="team-info">
                    <h3>Linda Kusuma</h3>
                    <div class="role">Quality Control Specialist</div>
                    <p>Linda bertanggung jawab memastikan setiap produk yang keluar dari dapur kami memenuhi standar kualitas tertinggi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Rumah Que Que</h4>
                <p>Kami menghadirkan kue berkualitas premium dengan cita rasa istimewa untuk melengkapi setiap momen bahagia Anda. Dibuat dengan bahan pilihan dan penuh cinta.</p>
            </div>
            <div class="footer-section">
                <h4>Kategori</h4>
                <ul class="footer-links">
                    <li><a href="#">Kue Kering</a></li>
                    <li><a href="#">Kue Basah</a></li>
                    <li><a href="#">Birthday Cake</a></li>
                    <li><a href="#">Hampers</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Informasi</h4>
                <ul class="footer-links">
                    <li><a href="produk.php">Katalog</a></li>
                    <li><a href="galeri.php">Galeri</a></li>
                    <li><a href="artikel.php">Artikel</a></li>
                    <li><a href="event.php">Event</a></li>
                    <li><a href="tentangkami.php">Tentang Kami</a></li>
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


</body>
</html>