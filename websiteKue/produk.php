<?php
include "koneksi.php";

$q = mysqli_query($con, "SELECT text FROM announcement WHERE id = 1");
$data = mysqli_fetch_assoc($q);
$pengumuman = $data ? $data['text'] : "Tidak ada pengumuman";

// --- 1. Pagination Settings ---
$items_per_page = 6;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

// --- 2. Ambil dan Sanitasi Input GET ---
$search_keyword = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'terbaru';
$filter_kategori = $_GET['kategori'] ?? '';

// --- 3. Build WHERE Clauses ---
$where_clauses = ["status = 'published'"];
$bind_types = '';
$bind_params = [];

if (!empty($search_keyword)) {
    $where_clauses[] = "nama LIKE ?";
    $bind_types .= 's';
    $bind_params[] = '%' . $search_keyword . '%';
}

if (!empty($filter_kategori)) {
    $where_clauses[] = "kategori = ?";
    $bind_types .= 's';
    $bind_params[] = $filter_kategori;
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// --- 4. Determine ORDER BY ---
$order_sql = "ORDER BY id_product DESC";
if ($sort_by == 'termurah') {
    $order_sql = "ORDER BY harga ASC";
} elseif ($sort_by == 'termahal') {
    $order_sql = "ORDER BY harga DESC";
} elseif ($sort_by == 'terlaris') {
    $order_sql = "ORDER BY pelihat DESC";
} elseif ($sort_by == 'rating') {
    $order_sql = "ORDER BY rata_rating DESC";
}

// --- 5. Count Total Products for Pagination ---
$count_sql = "SELECT COUNT(*) as total FROM products {$where_sql}";
$stmt_count = $con->prepare($count_sql);
if (!empty($bind_types)) {
    $bind_params_ref = [];
    $bind_params_ref[] = $bind_types;
    for ($i = 0; $i < count($bind_params); $i++) {
        $bind_params_ref[] = &$bind_params[$i];
    }
    call_user_func_array(array($stmt_count, 'bind_param'), $bind_params_ref);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $items_per_page);
$stmt_count->close();

// --- 6. Get Products with Pagination ---
$sql_list = "SELECT id_product, nama, harga, gambar, crop_y, rata_rating, pelihat 
             FROM products 
             {$where_sql} 
             {$order_sql}
             LIMIT ? OFFSET ?";

$stmt_list = $con->prepare($sql_list);
$bind_types .= 'ii';
$bind_params[] = $items_per_page;
$bind_params[] = $offset;

if (!empty($bind_types)) {
    $bind_params_ref = [];
    $bind_params_ref[] = $bind_types;
    for ($i = 0; $i < count($bind_params); $i++) {
        $bind_params_ref[] = &$bind_params[$i];
    }
    call_user_func_array(array($stmt_list, 'bind_param'), $bind_params_ref);
}

$stmt_list->execute();
$result_list = $stmt_list->get_result();
$stmt_list->close();

// --- 7. Get Unique Categories ---
$kategori_sql = "SELECT DISTINCT kategori FROM products WHERE status='published' AND kategori IS NOT NULL AND kategori != ''";
$kategori_result = $con->query($kategori_sql);
$kategori_options = [];
while ($row = $kategori_result->fetch_assoc()) {
    $kategori_options[] = $row['kategori'];
}

$con->close();

// Function to determine badge
function getBadge($pelihat, $rata_rating) {
    if ($pelihat > 100) return ['text' => 'Best Seller', 'class' => 'badge-bestseller'];
    if ($rata_rating >= 4.5) return ['text' => 'Top Rated', 'class' => 'badge-toprated'];
    return null;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk - Rumah Que Que</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Asimovian&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="js/script.js" defer></script>
    <link rel="stylesheet" href="css/style.css">
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

    <!-- About Hero Section -->
    <section class="about-hero">
        <div class="about-hero-content">
            <h1>Katalog <span class="highlight">Rumah Que Que</span></h1>
            <p>Temukan produk berkualitas dengan harga terbaik.</p>

        </div>
    </section>

    <!-- Mobile Filter Toggle -->
    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>

    <!-- Main Content -->
    <div class="catalog-container">
        <!-- Sidebar Filters -->
        <aside class="sidebar" id="sidebar">
            <div class="filter-section">
                <div class="filter-title">Sort By</div>
                <a href="?sort=terlaris<?php echo !empty($search_keyword) ? '&search='.urlencode($search_keyword) : ''; ?><?php echo !empty($filter_kategori) ? '&kategori='.urlencode($filter_kategori) : ''; ?>" 
                   class="filter-link <?php echo $sort_by == 'terlaris' ? 'active' : ''; ?>">
                    Banyak Dilihat
                </a>
                <a href="?sort=termurah<?php echo !empty($search_keyword) ? '&search='.urlencode($search_keyword) : ''; ?><?php echo !empty($filter_kategori) ? '&kategori='.urlencode($filter_kategori) : ''; ?>" 
                   class="filter-link <?php echo $sort_by == 'termurah' ? 'active' : ''; ?>">
                    Paling Murah
                </a>
                <a href="?sort=termahal<?php echo !empty($search_keyword) ? '&search='.urlencode($search_keyword) : ''; ?><?php echo !empty($filter_kategori) ? '&kategori='.urlencode($filter_kategori) : ''; ?>" 
                   class="filter-link <?php echo $sort_by == 'termahal' ? 'active' : ''; ?>">
                    Ekslusif Mahal
                </a>
                <a href="?sort=rating<?php echo !empty($search_keyword) ? '&search='.urlencode($search_keyword) : ''; ?><?php echo !empty($filter_kategori) ? '&kategori='.urlencode($filter_kategori) : ''; ?>" 
                   class="filter-link <?php echo $sort_by == 'rating' ? 'active' : ''; ?>">
                    Rating Terbaik
                </a>
            </div>

            <div class="filter-section">
                <div class="filter-title">Filter by Category</div>
                <a href="?<?php echo !empty($search_keyword) ? 'search='.urlencode($search_keyword).'&' : ''; ?>sort=<?php echo $sort_by; ?>" 
                   class="filter-link <?php echo empty($filter_kategori) ? 'active' : ''; ?>">
                    Semua Produk
                </a>
                <?php if (!empty($kategori_options)): ?>
                    <?php foreach ($kategori_options as $kat): ?>
                        <a href="?kategori=<?php echo urlencode($kat); ?><?php echo !empty($search_keyword) ? '&search='.urlencode($search_keyword) : ''; ?>&sort=<?php echo $sort_by; ?>" 
                           class="filter-link <?php echo $filter_kategori == $kat ? 'active' : ''; ?>">
                            <?php 
                            // Icon berdasarkan kategori
                            $icon = '';
                            if (stripos($kat, 'kering') !== false) $icon = '';
                            elseif (stripos($kat, 'basah') !== false) $icon = '';
                            elseif (stripos($kat, 'lebaran') !== false) $icon = '';
                            echo $icon . ' ' . htmlspecialchars($kat);
                            ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #888; font-size: 0.9rem; padding: 0.5rem;">Belum ada kategori</p>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Products Area -->
        <main class="products-area">
            <div class="products-header">
                <div class="results-count">
                    Menampilkan <span><?php echo $result_list->num_rows; ?></span> dari <span><?php echo $total_products; ?></span> produk
                </div>
            </div>
            <div class="search-container">
                <form method="GET" action="produk.php">
                    <input 
                        type="text" 
                        name="search" 
                        class="search-bar" 
                        placeholder="Cari produk..." 
                        value="<?php echo htmlspecialchars($search_keyword); ?>"
                    >
                    <button type="submit" class="search-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
            <div/>


            <!-- Products Grid -->
            <?php if ($result_list && $result_list->num_rows > 0): ?>
                <div class="products-grid">
                    <?php while($row = $result_list->fetch_assoc()): 
                        $image_url = !empty($row['gambar']) ? 'adminpanel/gambar/' . htmlspecialchars($row['gambar']) : '';
                        $crop_position = 'center ' . $row['crop_y'] . '%';
                        $badge = getBadge($row['pelihat'], $row['rata_rating']);
                        
                        // Build URL with current filters
                        $query_params = http_build_query([
                            'search' => $search_keyword,
                            'kategori' => $filter_kategori,
                            'sort' => $sort_by,
                            'page' => $current_page
                        ]);
                    ?>
                    <a href="baca_produk.php?id=<?php echo $row['id_product']; ?>&<?php echo $query_params; ?>" class="product-card">
                        <div class="product-image-wrapper">
                            <div 
                                class="product-image"
                                style="
                                    <?php if (!empty($image_url)): ?>
                                        background-image: url('<?php echo $image_url; ?>');
                                        background-position: <?php echo $crop_position; ?>;
                                    <?php endif; ?>
                                "
                            ></div>
                            <?php if ($badge): ?>
                                <div class="product-badge <?php echo $badge['class']; ?>">
                                    <?php echo $badge['text']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($row['nama']); ?></h3>
                            <div class="product-meta">
                                <?php if ($row['rata_rating'] > 0): ?>
                                    <div class="product-rating">
                                        <div class="stars">
                                            <?php 
                                            $rating = $row['rata_rating'];
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= floor($rating)) {
                                                    echo '<span class="star filled">★</span>';
                                                } elseif ($i - 0.5 <= $rating) {
                                                    echo '<span class="star filled">★</span>';
                                                } else {
                                                    echo '<span class="star">★</span>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <span class="rating-number"><?php echo number_format($row['rata_rating'], 1); ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="product-rating">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <span style="color: #888; font-size: 0.85rem;">Belum ada rating</span>
                                    </div>
                                <?php endif; ?>
                                <div class="product-views">
                                    <?php echo number_format($row['pelihat']); ?> views
                                </div>
                            </div>
                            <div class="product-price">
                                Rp<?php echo number_format($row['harga'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    </a>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php
                        // Build base URL with filters
                        $base_url = "katalog_produk.php?search=" . urlencode($search_keyword) . 
                                    "&kategori=" . urlencode($filter_kategori) . 
                                    "&sort=" . urlencode($sort_by);
                        ?>
                        
                        <!-- Previous Button -->
                        <a href="<?php echo $base_url; ?>&page=<?php echo max(1, $current_page - 1); ?>" 
                           class="page-btn <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                            ←
                        </a>

                        <?php
                        // Show first page
                        if ($current_page > 3) {
                            echo '<a href="' . $base_url . '&page=1" class="page-btn">1</a>';
                            if ($current_page > 4) {
                                echo '<span class="page-btn disabled">...</span>';
                            }
                        }

                        // Show pages around current page
                        for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
                            $active = $i == $current_page ? 'active' : '';
                            echo '<a href="' . $base_url . '&page=' . $i . '" class="page-btn ' . $active . '">' . $i . '</a>';
                        }

                        // Show last page
                        if ($current_page < $total_pages - 2) {
                            if ($current_page < $total_pages - 3) {
                                echo '<span class="page-btn disabled">...</span>';
                            }
                            echo '<a href="' . $base_url . '&page=' . $total_pages . '" class="page-btn">' . $total_pages . '</a>';
                        }
                        ?>

                        <!-- Next Button -->
                        <a href="<?php echo $base_url; ?>&page=<?php echo min($total_pages, $current_page + 1); ?>" 
                           class="page-btn <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                            →
                        </a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
                    <h2>Produk Tidak Ditemukan</h2>
                    <p>Tidak ada produk yang sesuai dengan filter yang Anda pilih. Coba ubah kriteria pencarian.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

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
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('mobile-active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.querySelector('.menu-toggle');
            
            if (window.innerWidth <= 1024 && 
                sidebar.classList.contains('mobile-active') && 
                !sidebar.contains(e.target) && 
                !menuToggle.contains(e.target)) {
                sidebar.classList.remove('mobile-active');
            }
        });
    </script>

</body>
</html>