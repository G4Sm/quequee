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

// --- 1. Konfigurasi Pagination ---
$items_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $items_per_page;

// --- 2. Ambil dan Sanitasi Input GET ---
$search_keyword = $_GET['search'] ?? '';
$filter_kategori = $_GET['kategori'] ?? '';

$where_clauses = ["status = 'published'"];
$bind_types = '';
$bind_params = [];

// --- 3. Kondisi WHERE ---
if (!empty($search_keyword)) {
    $where_clauses[] = "judul LIKE ?";
    $bind_types .= 's';
    $bind_params[] = '%' . $search_keyword . '%';
}

if (!empty($filter_kategori)) {
    $where_clauses[] = "kategori = ?";
    $bind_types .= 's';
    $bind_params[] = $filter_kategori;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// --- 4. Hitung Total Events ---
$count_sql = "SELECT COUNT(*) as total FROM events {$where_sql}";
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
$total_events = $count_result->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ceil($total_events / $items_per_page);

// --- 5. Query Events dengan LIMIT ---
$sql = "SELECT id_event, judul, tanggal, kategori, pelihat, gambar, crop_y 
        FROM events 
        {$where_sql} 
        ORDER BY tanggal DESC
        LIMIT ? OFFSET ?";
        
$stmt = $con->prepare($sql);

$bind_types .= 'ii';
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

// --- 6. Fungsi URL Pagination ---
function build_pagination_url($page, $search = '', $kategori = '') {
    $params = ['page' => $page];
    if (!empty($search)) $params['search'] = $search;
    if (!empty($kategori)) $params['kategori'] = $kategori;
    return 'event.php?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Asimovian&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <title>Events - Rumah Que Que</title>

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
            <h1>Events <span class="highlight">Rumah Que Que</span></h1>
            <p>Temukan berbagai acara menarik dan kegiatan spesial dari Rumah Que Que</p>

        </div>
    </section>
    <!-- Search & Filter Section -->
    <section class="search-filter-section">
        <form method="GET" action="event.php" class="search-filter-form">
            <input 
                type="text" 
                name="search" 
                placeholder="Cari Event berdasarkan Judul..." 
                value="<?php echo htmlspecialchars($search_keyword); ?>"
                class="search-input"
            >
            
            <select name="kategori" class="filter-select">
                <option value="">Semua Kategori</option>
                <?php 
                $kategori_options = ['Internal', 'Eksternal', 'Lainnya'];
                
                foreach ($kategori_options as $option) {
                    $selected = ($filter_kategori == $option) ? 'selected' : '';
                    echo "<option value=\"{$option}\" {$selected}>{$option}</option>";
                }
                ?>
            </select>
            
            <button type="submit" class="btn-search">Cari</button>
            
            <?php if (!empty($search_keyword) || !empty($filter_kategori)): ?>
                <a href="event.php" class="btn-reset">Reset</a>
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
                $info_text .= " - <strong>" . $total_events . "</strong> event ditemukan";
                echo $info_text;
                ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Events Grid -->
    <section class="events-section">
        <?php if ($result && $result->num_rows > 0) { ?>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination-info">
                Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $items_per_page, $total_events); ?> dari <?php echo $total_events; ?> event
            </div>
            <?php endif; ?>

            <div class="event-grid">
                <?php while($row = $result->fetch_assoc()) {
                    $image_url = !empty($row['gambar']) ? 'adminpanel/gambar/' . htmlspecialchars($row['gambar']) : '';
                    $crop_position = $row['crop_y'] . '%'; 
                    
                    $bulan = array(
                        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                        'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
                    );
                    $tanggal_obj = date_create($row['tanggal']);
                    $tanggal_format = date_format($tanggal_obj, 'd') . ' ' . 
                                     $bulan[date_format($tanggal_obj, 'n')] . ' ' . 
                                     date_format($tanggal_obj, 'Y');
                ?>
                <a href="baca_event.php?id=<?php echo $row['id_event']; ?>" class="event-item">
                    <div 
                        class="event-image-placeholder"
                        style="
                            <?php if (!empty($image_url)): ?>
                                background-image: url('<?php echo $image_url; ?>');
                                background-position: center <?php echo $crop_position; ?>;
                            <?php endif; ?>
                        "
                    >
                        <span class="kategori-badge"><?php echo htmlspecialchars($row['kategori']); ?></span>
                    </div>
                    <div class="event-content">
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
                <?php if ($current_page > 1): ?>
                    <a href="<?php echo build_pagination_url($current_page - 1, $search_keyword, $filter_kategori); ?>" class="pagination-btn">
                        Prev
                    </a>
                <?php else: ?>
                    <span class="pagination-btn disabled">Prev</span>
                <?php endif; ?>

                <?php
                $show_pages = 5;
                $start_page = max(1, $current_page - floor($show_pages / 2));
                $end_page = min($total_pages, $start_page + $show_pages - 1);
                
                if ($end_page - $start_page < $show_pages - 1) {
                    $start_page = max(1, $end_page - $show_pages + 1);
                }

                if ($start_page > 1) {
                    echo '<a href="' . build_pagination_url(1, $search_keyword, $filter_kategori) . '" class="pagination-btn">1</a>';
                    if ($start_page > 2) {
                        echo '<span class="pagination-dots">...</span>';
                    }
                }

                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active_class = ($i == $current_page) ? 'active' : '';
                    echo '<a href="' . build_pagination_url($i, $search_keyword, $filter_kategori) . '" class="pagination-btn ' . $active_class . '">' . $i . '</a>';
                }

                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="pagination-dots">...</span>';
                    }
                    echo '<a href="' . build_pagination_url($total_pages, $search_keyword, $filter_kategori) . '" class="pagination-btn">' . $total_pages . '</a>';
                }
                ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="<?php echo build_pagination_url($current_page + 1, $search_keyword, $filter_kategori); ?>" class="pagination-btn">
                        Next
                    </a>
                <?php else: ?>
                    <span class="pagination-btn disabled">Next</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php } else { ?>
            <div class="empty-state">
                <div class="empty-icon">📅</div>
                <h2>Event Tidak Ditemukan</h2>
                <p>
                    <?php if (!empty($search_keyword) || !empty($filter_kategori)): ?>
                        Maaf, tidak ada event yang sesuai dengan pencarian Anda. Coba kata kunci atau kategori lain.
                    <?php else: ?>
                        Belum ada event yang dipublikasikan saat ini.
                    <?php endif; ?>
                </p>
                <?php if (!empty($search_keyword) || !empty($filter_kategori)): ?>
                    <a href="event.php" class="btn-search" style="display: inline-flex;">Lihat Semua Event</a>
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
                            <li><a href="event.php?kategori=<?php echo urlencode($kat); ?>"><?php echo htmlspecialchars($kat); ?></a></li>
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
<?php $con->close(); ?>