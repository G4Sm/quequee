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
$pengumuman = $data ? $data['text'] : "Tidak ada pengumuman";
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rumah Que Que - The Best Cake in Town</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Asimovian&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                <a href="#" class="nav-logo"><img src="logo.png" style="width:100px; height:80px;"></img></a>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="wrapperrr">
            <div class="hero-content">
                <h1>THE <span class="highlight">BEST</span><br>IN TOWN</h1>
                <p>Nikmati kelezatan kue premium kami yang dibuat dengan bahan berkualitas dan resep istimewa untuk momen spesial Anda</p>
                <div class="cta-buttons">
                    <a href="https://wa.me/+6285319111612" target="_blank" class="btn btn-primary">Pesan Sekarang</a>
                    <a href="produk.php" class="btn btn-secondary">Lihat Menu</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="cake-showcase">
                    <!-- Ambient Background Glow -->
                    <div class="ambient-glow"></div>
                    
                    <!-- Geometric Rotating Rings -->
                    <div class="geometric-ring ring-1"></div>
                    <div class="geometric-ring ring-2"></div>
                    <div class="geometric-ring ring-3"></div>
                    
                    <!-- Elegant Accent Lines -->
                    <div class="accent-line line-1"></div>
                    <div class="accent-line line-2"></div>
                    
                    <!-- Subtle Light Particles -->
                    <div class="light-particle particle-1"></div>
                    <div class="light-particle particle-2"></div>
                    <div class="light-particle particle-3"></div>
                    <div class="light-particle particle-4"></div>
                    <div class="light-particle particle-5"></div>
                    <div class="light-particle particle-6"></div>
                    
                    <!-- Main Cake Image -->
                    <div class="cake-main">
                        <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800" alt="Premium Artisan Cake">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="maxwidkatalog" id="katalog">
        <div class="section-title">
            <h2>Yuk, tentukan rasa bahagiamu</h2>
            <p>Pilihan terbaik untuk setiap momen istimewa</p>
        </div>
        <div class="products-grid">
            <div class="product-card">
                <img src="https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600" alt="Kue Nastar" class="product-image">
                <div class="product-info">
                    <div class="product-header">
                        <h3>Kue Nastar</h3>
                        <div class="rating">
                            <span>⭐ 4.9</span>
                        </div>
                    </div>
                    <div class="price">Rp90.000</div>
                    <button class="add-to-cart">Tambah ke Keranjang</button>
                </div>
            </div>
            <div class="product-card">
                <img src="https://images.unsplash.com/photo-1571115177098-24ec42ed204d?w=600" alt="Chocolate Cake" class="product-image">
                <div class="product-info">
                    <div class="product-header">
                        <h3>Chocolate Delight</h3>
                        <div class="rating">
                            <span>⭐ 4.9</span>
                        </div>
                    </div>
                    <div class="price">Rp120.000</div>
                    <button class="add-to-cart">Tambah ke Keranjang</button>
                </div>
            </div>
            <div class="product-card">
                <img src="https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=600" alt="Brownies" class="product-image">
                <div class="product-info">
                    <div class="product-header">
                        <h3>Premium Brownies</h3>
                        <div class="rating">
                            <span>⭐ 4.8</span>
                        </div>
                    </div>
                    <div class="price">Rp85.000</div>
                    <button class="add-to-cart">Tambah ke Keranjang</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Special Ramadhan -->
    <section class="special-section" id="galeri">
        <div class="section-title">
            <h2>Special Ramadhan</h2>
            <p>Koleksi istimewa untuk bulan penuh berkah</p>
        </div>
        <div class="special-grid">
            <div class="special-item">
                <img src="https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600" alt="Special 1">
                <div class="special-overlay">
                    <h4>Nastar Premium</h4>
                    <p>Diskon 20%</p>
                </div>
            </div>
            <div class="special-item">
                <img src="https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?w=600" alt="Special 2">
                <div class="special-overlay">
                    <h4>Paket Lebaran</h4>
                    <p>Hemat Rp50.000</p>
                </div>
            </div>
            <div class="special-item">
                <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600" alt="Special 3">
                <div class="special-overlay">
                    <h4>Hampers Spesial</h4>
                    <p>Gratis Ongkir</p>
                </div>
            </div>
            <div class="special-item">
                <img src="https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?w=600" alt="Special 4">
                <div class="special-overlay">
                    <h4>Kue Kering Mix</h4>
                    <p>Beli 2 Gratis 1</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section" id="pemesanan">
        <div class="contact-info">
            <h3>Pesan Sekarang</h3>
            <p>Hubungi kami untuk pemesanan atau kunjungi toko kami. Kami siap melayani Anda dengan sepenuh hati dan menghadirkan kue terbaik untuk setiap momen spesial Anda.</p>
            <div class="social-links">
                <a href="https://wa.me/" class="social-btn" title="WhatsApp">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                        <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                    </svg>
                </a>
                <a href="https://instagram.com" class="social-btn" title="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
                        <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                    </svg>
                </a>
                <a href="https://facebook.com" class="social-btn" title="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                        <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
                    </svg>
                </a>
            </div>
            <a href="https://wa.me/+6285319111612" class="btn btn-primary">Hubungi Kami</a>
        </div>
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.7445!2d106.8!3d-6.4!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMjQnMDAuMCJTIDEwNsKwNDgnMDAuMCJF!5e0!3m2!1sen!2sid!4v1234567890" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </section>

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

    
</body>
</html>