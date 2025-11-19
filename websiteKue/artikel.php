<?php
include "koneksi.php";

$q = mysqli_query($con, "SELECT text FROM announcement WHERE id = 1");
$data = mysqli_fetch_assoc($q);
$pengumuman = $data ? $data['text'] : "Tidak ada pengumuman";

$kategori_sql = "SELECT DISTINCT kategori FROM products WHERE status='published' AND kategori IS NOT NULL AND kategori != ''";
$kategori_result = $con->query($kategori_sql);
$kategori_options = [];
while ($row = $kategori_result->fetch_assoc()) {
    $kategori_options[] = $row['kategori'];
}

// --- 1. Konfigurasi Pagination ---
$items_per_page = 6; // Jumlah artikel per halaman
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Minimal halaman 1
$offset = ($current_page - 1) * $items_per_page;

// --- 2. Ambil dan Sanitasi Input GET ---
$search_keyword = $_GET['search'] ?? '';
$filter_kategori = $_GET['kategori'] ?? '';

// Array untuk menyimpan kondisi WHERE
$where_clauses = ["status = 'published'"];
// Array untuk menyimpan parameter binding
$bind_types = '';
$bind_params = [];


// --- 3. Tambahkan Kondisi WHERE Berdasarkan Input ---

// Kondisi Pencarian Judul
if (!empty($search_keyword)) {
    $where_clauses[] = "judul LIKE ?";
    $bind_types .= 's';
    $bind_params[] = '%' . $search_keyword . '%';
}

// Kondisi Filter Kategori
if (!empty($filter_kategori)) {
    $where_clauses[] = "kategori = ?";
    $bind_types .= 's';
    $bind_params[] = $filter_kategori;
}

// Gabungkan semua kondisi WHERE
$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";


// --- 4. Hitung Total Artikel (untuk pagination) ---
$count_sql = "SELECT COUNT(*) as total FROM artikel {$where_sql}";
$count_stmt = $con->prepare($count_sql);

if (!empty($bind_types)) {
    $bind_params_ref = array();
    $bind_params_ref[] = $bind_types;
    for ($i = 0; $i < count($bind_params); $i++) {
        $bind_params_ref[] = &$bind_params[$i];
    }
    call_user_func_array(array($count_stmt, 'bind_param'), $bind_params_ref);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_articles = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Hitung total halaman
$total_pages = ceil($total_articles / $items_per_page);

// --- 5. Buat Query SQL Akhir dengan LIMIT ---
$sql = "SELECT id_artikel, judul, tanggal, kategori, pelihat, gambar, crop_y 
        FROM artikel 
        {$where_sql} 
        ORDER BY tanggal DESC
        LIMIT ? OFFSET ?";
        
$stmt = $con->prepare($sql);

// Binding parameter dengan LIMIT dan OFFSET
$bind_types .= 'ii'; // Tambah tipe integer untuk LIMIT dan OFFSET
$bind_params[] = $items_per_page;
$bind_params[] = $offset;

if (!empty($bind_types)) {
    $bind_params_ref = array();
    $bind_params_ref[] = $bind_types;
    for ($i = 0; $i < count($bind_params); $i++) {
        $bind_params_ref[] = &$bind_params[$i];
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_params_ref);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// --- 6. Fungsi untuk membuat URL pagination ---
function build_pagination_url($page, $search = '', $kategori = '') {
    $params = ['page' => $page];
    if (!empty($search)) $params['search'] = $search;
    if (!empty($kategori)) $params['kategori'] = $kategori;
    return 'artikel.php?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel - Rumah Que Que</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Asimovian&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

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
            <h1>Artikel <span class="highlight">Rumah Que Que</span></h1>
            <p>Temukan inspirasi, tips, dan cerita menarik seputar dunia kue dan berita internasional</p>

        </div>
    </section>

    <!-- Search & Filter Section -->
    <section class="search-filter-section">
        <form method="GET" action="artikel.php" class="search-filter-form">
            <input 
                type="text" 
                name="search" 
                placeholder="Cari berdasarkan Judul..." 
                value="<?php echo htmlspecialchars($search_keyword); ?>"
                class="search-input"
            >
            
            <select name="kategori" class="filter-select">
                <option value="">Semua Kategori</option>
                <?php 
                $kategori_option = ['Teknologi', 'Edukasi', 'Politik', 'Kesehatan'];
                
                foreach ($kategori_option as $option) {
                    $selected = ($filter_kategori == $option) ? 'selected' : '';
                    echo "<option value=\"{$option}\" {$selected}>{$option}</option>";
                }
                ?>
            </select>
            
            <button type="submit" class="btn-search">Cari</button>
            
            <?php if (!empty($search_keyword) || !empty($filter_kategori)): ?>
                <a href="artikel.php" class="btn-reset">✕ Reset</a>
            <?php endif; ?>
        </form>

        <?php if (!empty($search_keyword) || !empty($filter_kategori)): ?>
            <div class="result-info">
                <?php 
                $info_text = "Menampilkan hasil";
                if (!empty($search_keyword)) {
                    $info_text .= " pencarian '<strong>" . htmlspecialchars($search_keyword) . "</strong>'";
                }
                if (!empty($filter_kategori)) {
                    $info_text .= " dalam kategori '<strong>" . htmlspecialchars($filter_kategori) . "</strong>'";
                }
                $info_text .= " - <strong>" . $total_articles . "</strong> artikel ditemukan";
                echo $info_text;
                ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Articles Grid -->
    <section class="articles-section">
        <?php if ($result && $result->num_rows > 0) { ?>
            <div class="artikel-grid">
                <?php while($row = $result->fetch_assoc()) {
                    $image_url = !empty($row['gambar']) ? 'adminpanel/gambar/' . htmlspecialchars($row['gambar']) : '';
                    $crop_position = $row['crop_y'] . '%'; 
                    
                    // Format tanggal Indonesia
                    $bulan = array(
                        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                        'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
                    );
                    $tanggal_obj = date_create($row['tanggal']);
                    $tanggal_format = date_format($tanggal_obj, 'd') . ' ' . 
                                     $bulan[date_format($tanggal_obj, 'n')] . ' ' . 
                                     date_format($tanggal_obj, 'Y');
                ?>
                <a href="baca.php?id=<?php echo $row['id_artikel']; ?>" class="artikel-item">
                    <div 
                        class="artikel-image-placeholder"
                        style="
                            <?php if (!empty($image_url)): ?>
                                background-image: url('<?php echo $image_url; ?>');
                                background-position: center <?php echo $crop_position; ?>;
                            <?php endif; ?>
                        "
                    >
                        <span class="kategori-badge"><?php echo htmlspecialchars($row['kategori']); ?></span>
                    </div>
                    <div class="artikel-content">
                        <h3><?php echo htmlspecialchars($row['judul']); ?></h3>
                        <div class="meta-info">
                            <span class="meta-item">
                                <span class="meta-icon">📅</span>
                                <?php echo $tanggal_format; ?>
                            </span>
                            <span class="meta-item">
                                <span class="meta-icon">👁</span>
                                <?php echo number_format($row['pelihat']); ?>
                            </span>
                        </div>
                    </div>
                </a>
                <?php } ?>
            </div>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <!-- Previous Button -->
                <?php if ($current_page > 1): ?>
                    <a href="<?php echo build_pagination_url($current_page - 1, $search_keyword, $filter_kategori); ?>" class="pagination-btn">
                        ← Prev
                    </a>
                <?php else: ?>
                    <span class="pagination-btn disabled">← Prev</span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php
                $show_pages = 5; // Jumlah tombol halaman yang ditampilkan
                $start_page = max(1, $current_page - floor($show_pages / 2));
                $end_page = min($total_pages, $start_page + $show_pages - 1);
                
                // Adjust start_page jika end_page sudah maksimal
                if ($end_page - $start_page < $show_pages - 1) {
                    $start_page = max(1, $end_page - $show_pages + 1);
                }

                // First page
                if ($start_page > 1) {
                    echo '<a href="' . build_pagination_url(1, $search_keyword, $filter_kategori) . '" class="pagination-btn">1</a>';
                    if ($start_page > 2) {
                        echo '<span class="pagination-dots">...</span>';
                    }
                }

                // Page numbers
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active_class = ($i == $current_page) ? 'active' : '';
                    echo '<a href="' . build_pagination_url($i, $search_keyword, $filter_kategori) . '" class="pagination-btn ' . $active_class . '">' . $i . '</a>';
                }

                // Last page
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="pagination-dots">...</span>';
                    }
                    echo '<a href="' . build_pagination_url($total_pages, $search_keyword, $filter_kategori) . '" class="pagination-btn">' . $total_pages . '</a>';
                }
                ?>

                <!-- Next Button -->
                <?php if ($current_page < $total_pages): ?>
                    <a href="<?php echo build_pagination_url($current_page + 1, $search_keyword, $filter_kategori); ?>" class="pagination-btn">
                        Next →
                    </a>
                <?php else: ?>
                    <span class="pagination-btn disabled">Next →</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php } else { ?>
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h2>Artikel Tidak Ditemukan</h2>
                <p>
                    <?php if (!empty($search_keyword) || !empty($filter_kategori)): ?>
                        Maaf, tidak ada artikel yang sesuai dengan pencarian Anda. Coba kata kunci atau kategori lain.
                    <?php else: ?>
                        Belum ada artikel yang dipublikasikan saat ini.
                    <?php endif; ?>
                </p>
                <?php if (!empty($search_keyword) || !empty($filter_kategori)): ?>
                    <a href="artikel.php" class="btn-search" style="display: inline-flex;">Lihat Semua Artikel</a>
                <?php endif; ?>
            </div>
        <?php } ?>
    </section>

    <!-- Footer -->
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header');
            const topBanner = document.querySelector('.top-banner');
            const logo = document.querySelector('.nav-logo-container');
            const hamburger = document.querySelector('.hamburger');
            const mobileMenu = document.querySelector('.mobile-menu');
            const mobileOverlay = document.querySelector('.mobile-overlay');
            const mobileLinks = document.querySelectorAll('.mobile-menu .nav-link');

            // Header scroll effect
            window.addEventListener('scroll', () => {
                const threshold = topBanner ? topBanner.offsetHeight : 40;
                
                if (window.scrollY > threshold) {
                    header.classList.add('scrolled');
                    logo.classList.add('scrolled-logo');
                } else {
                    header.classList.remove('scrolled');
                    logo.classList.remove('scrolled-logo');
                }
            }, { passive: true });

            // Toggle mobile menu
            function toggleMenu() {
                hamburger.classList.toggle('active');
                mobileMenu.classList.toggle('active');
                mobileOverlay.classList.toggle('active');
                
                // Prevent body scroll when menu is open
                if (mobileMenu.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }

            // Hamburger click
            hamburger.addEventListener('click', toggleMenu);

            // Overlay click to close
            mobileOverlay.addEventListener('click', toggleMenu);

            // Close menu when link clicked
            mobileLinks.forEach(link => {
                link.addEventListener('click', () => {
                    toggleMenu();
                });
            });

            // Smooth scroll for internal links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Animation on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -100px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe elements for animation
            document.querySelectorAll('.value-card, .team-card, .stat-item').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s ease-out';
                observer.observe(el);
            });
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 992) {
                document.body.style.overflow = "";
                document.querySelector(".mobile-menu").classList.remove("active");
                document.querySelector(".mobile-overlay").classList.remove("active");
            }
        });
    </script>
</body>


</html>
<?php $con->close(); ?>
