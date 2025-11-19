<?php
include 'koneksi.php'; // Koneksi database

$kategori_sql = "SELECT DISTINCT kategori FROM products WHERE status='published' AND kategori IS NOT NULL AND kategori != ''";
$kategori_result = $con->query($kategori_sql);
$kategori_options = [];
while ($row = $kategori_result->fetch_assoc()) {
    $kategori_options[] = $row['kategori'];
}

// --- 1. Validasi ID dari URL ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $error_message = "ID artikel tidak valid.";
    goto display_error;
}


$q = mysqli_query($con, "SELECT text FROM announcement WHERE id = 1");
$data = mysqli_fetch_assoc($q);
$pengumuman = $data ? $data['text'] : "Tidak ada pengumuman";


$id_artikel = intval($_GET['id']);
$artikel_data = null;
$error_message = null;

// --- 2. Logika Update Pelihat (View Counter) ---
$stmt_update = $con->prepare("UPDATE artikel SET pelihat = pelihat + 1 WHERE id_artikel = ?");
$stmt_update->bind_param("i", $id_artikel);
$stmt_update->execute();
$stmt_update->close();

// --- 3. Ambil Data Artikel ---
$stmt_select = $con->prepare("SELECT judul, gambar, isi, tanggal, kategori, pelihat, crop_y FROM artikel WHERE id_artikel = ?");
$stmt_select->bind_param("i", $id_artikel);
$stmt_select->execute();
$result = $stmt_select->get_result();

if ($result->num_rows === 1) {
    $artikel_data = $result->fetch_assoc();
    
    $judul = htmlspecialchars($artikel_data['judul']);
    $isi_html = $artikel_data['isi'];
    
    // Format tanggal Indonesia
    $bulan_indonesia = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $tanggal_obj = date_create($artikel_data['tanggal']);
    $tanggal = date_format($tanggal_obj, 'd') . ' ' . 
               $bulan_indonesia[date_format($tanggal_obj, 'n')] . ' ' . 
               date_format($tanggal_obj, 'Y');
    
    $kategori = htmlspecialchars($artikel_data['kategori']);
    $pelihat = number_format($artikel_data['pelihat']);
    $gambar_url = !empty($artikel_data['gambar']) ? 'adminpanel/gambar/' . htmlspecialchars($artikel_data['gambar']) : '';
    $crop_position = $artikel_data['crop_y'] . '%';
} else {
    $error_message = "Artikel tidak ditemukan.";
    goto display_error;
}
$stmt_select->close();

// --- 4. Ambil Artikel Terkait (6 artikel random) ---
$stmt_related = $con->prepare("SELECT id_artikel, judul, tanggal, kategori, pelihat, gambar, crop_y 
                                FROM artikel 
                                WHERE id_artikel != ? AND status = 'published'
                                ORDER BY RAND() 
                                LIMIT 6");
$stmt_related->bind_param("i", $id_artikel);
$stmt_related->execute();
$related_articles = $stmt_related->get_result();
$stmt_related->close();

goto display_content;

// --- Logika Tampilan Error ---
display_error:
$con->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Rumah Que Que</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a1f0f;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .error-box {
            max-width: 500px;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            text-align: center;
        }
        .error-icon { font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5; }
        h1 { font-size: 2rem; color: #ff8c42; margin-bottom: 1rem; }
        p { color: #b8b8b8; margin-bottom: 2rem; font-size: 1.1rem; }
        .btn-back {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.3);
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 66, 0.5);
        }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="error-icon">⚠️</div>
        <h1>Terjadi Kesalahan!</h1>
        <p><?php echo $error_message; ?></p>
        <a href="artikel.php" class="btn-back">← Kembali ke Daftar Artikel</a>
    </div>
</body>
</html>
<?php exit(); ?>

<?php
// --- Logika Tampilan Konten Normal ---
display_content:
$con->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $judul; ?> - Rumah Que Que</title>
    <style>

                :root {
            --bg-color: #100E16;
            --text-color: #F8F7F9;
            --text-muted: #A3A1A8;
            --border-color: rgba(255, 255, 255, 0.1);
            --font-family: 'Inter', sans-serif;
            --border-color: #DDDDDD;
            --bg-light: #F9F9F9;
            --text-dark: #333333;
            --text-light: #FFFFFF;
            --green: #15491B;
        }
        
        /* ======== ORNAMENT BACKGROUND GRID ======== */
        .site-wrapper {
            position: relative;
            overflow: hidden;
        }
        
        .site-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at center, rgba(124, 58, 237, 0.15) 0%, transparent 40%),
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 100% 100%, 40px 40px, 40px 40px;
            z-index: -1;
        }
        
        /* ======== TOP BANNER ======== */
        .top-banner {
            width: 100%;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(10, 31, 15, 0.95) 0%, rgba(21, 73, 27, 0.95) 100%);
            backdrop-filter: blur(15px);
            color: white;
            position: relative;
            padding: 0.8rem;
            border-bottom: 2px solid rgba(255, 140, 66, 0.3);
            box-shadow: 0 4px 20px rgba(255, 140, 66, 0.15);
        }

        .top-banner a {
            color: #ffa662;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
            margin: 0 auto;
            font-weight: 500;
            text-shadow: 0 2px 10px rgba(255, 140, 66, 0.3);
        }
        .top-banner a:hover { 
            color: #ffffff;
            text-shadow: 0 0 20px rgba(255, 140, 66, 0.8);
        }
        .marquee {
            display: flex;
            text-align: center;
        }
        
        /* Animasi panah */
        .arrow-left {
            display: inline-block;
            opacity: 0;
            transform: translateX(-10px);
            animation: arrowLeftMove 1.2s ease-in-out infinite;
            font-size: large;
            color: #ff8c42;
        }

        .arrow-right {
            display: inline-block;
            opacity: 0;
            transform: translateX(10px);
            animation: arrowRightMove 1.2s ease-in-out infinite;
            font-size: large;
            color: #ff8c42;
        }

                        /* ======== RESPONSIVE ======== */
        @media(max-width: 992px) {
            .nav-link { 
                display: none;
            }
            .nav-logo-container { 
                text-align: left !important;
            }
            .hamburger { 
                display: block !important;
            }
            .mobile-menu,
            .mobile-overlay {
                display: block !important;
                top: 3.3rem !important;
            }

            .story-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .values-grid {
                grid-template-columns: 1fr;
            }

            .team-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .about-hero {
                padding: 120px 5% 60px;
                min-height: 500px;
            }

            .about-hero-content h1 {
                font-size: 3rem;
            }

            .about-hero-content p {
                font-size: 1.1rem;
            }

            .story-content h2 {
                font-size: 2.2rem;
            }

            .story-image img {
                height: 350px;
            }

            .section-title h2 {
                font-size: 2.2rem;
            }

            .footer-content {
                grid-template-columns: 1fr !important;
            }

            .mobile-menu {
                width: 80%;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .stat-number {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 480px) {
            .about-hero-content h1 {
                font-size: 2.5rem !important;
            }

            .mobile-menu {
                width: 85%;
            }

            .nav-logo-container img {
                width: 70px;
                height: 55px;
            }

            .value-card,
            .team-card {
                padding: 2rem 1.5rem;
            }
        }

        @keyframes arrowLeftMove {
            0% {
                opacity: 0;
                transform: translateX(-10px);
            }
            50% {
                opacity: 1;
                transform: translateX(0);
            }
            100% {
                opacity: 0;
                transform: translateX(10px);
            }
        }
        
        @keyframes arrowRightMove {
            0% {
                opacity: 0;
                transform: translateX(10px);
            }
            50% {
                opacity: 1;
                transform: translateX(0);
            }
            100% {
                opacity: 0;
                transform: translateX(-10px);
            }
        }
        
        /* ======== NAVBAR ======== */
        .header {
            width: 100%;
            position: fixed;
            top: 40px;
            left: 0;
            z-index: 100;
            transition: background-color 0.3s ease, top 0.3s ease;
            border-bottom: 1px solid transparent;
        }
        
        .header.scrolled {
            top: 0;
            background-color: rgb(18 27 20 / 98%);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 2rem;
            height: 60px;
        }
        
        .nav-group {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .nav-logo-container {
            flex-grow: 1;
            text-align: center;
        }
        
        .nav-logo-container img{
            background-color: none;
            backdrop-filter: blur(10px);
            transition: border-bottom 0.3s ease;
        }
        
        .nav-logo-container.scrolled-logo img {
            border-bottom: 2px solid var(--border-color);
            border-radius: 50%;
            backdrop-filter: blur(10px);
        }
        
        .nav-logo {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -2px;
        }
        
        .nav-link {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
            transition: color 0.2s;
            text-decoration: none;
        }
        .nav-link:hover { color: var(--text-color); }
        
        .hamburger { 
            display: none;
            flex-direction: column;
            cursor: pointer;
            z-index: 102;
        }

        .bar {
            display: block;
            width: 25px; 
            height: 2px;
            margin: 5px auto;
            background-color: var(--text-color);
            transition: all 0.3s ease;
        }

        /* Mobile Menu */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            right: -100%;
            width: 70%;
            max-width: 300px;
            height: 100vh;
            background-color: rgb(18 27 20 / 98%);
            backdrop-filter: blur(20px);
            padding: 100px 2rem 2rem;
            transition: right 0.4s ease;
            z-index: 99;
            overflow-y: auto;
        }

        .mobile-menu.active {
            right: 0;
        }

        .mobile-menu .nav-link {
            display: block;
            padding: 1rem 0;
            font-size: 1.1rem;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.7);
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s ease;
            z-index: 98;
        }

        .mobile-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            background: #0a1f0f;
            color: #fff;
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
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            33% {
                transform: translate(5%, 5%) rotate(120deg);
            }
            66% {
                transform: translate(-5%, 5%) rotate(240deg);
            }
        }

        /* Navigation */
        

        /* Back Button */
        .back-button {
            position: fixed;
            top: 120px;
            left: 5%;
            z-index: 999;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 140, 66, 0.5);
            border-radius: 50px;
            color: #ff8c42;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }

        .back-button:hover {
            background: rgba(255, 140, 66, 0.1);
            transform: translateX(-5px);
        }

        /* Article Container */
        .article-container {
            margin-top: 100px;
            padding: 3rem 5% 6rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Featured Image */
        .featured-image-wrapper {
            width: 100%;
            height: 500px;
            border-radius: 30px;
            overflow: hidden;
            margin-bottom: 3rem;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .featured-image {
            width: 100%;
            height: 100%;
            background-size: cover;
            background-repeat: no-repeat;
            position: relative;
        }

        .featured-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.4) 100%);
        }

        .kategori-badge-large {
            position: absolute;
            bottom: 30px;
            left: 30px;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            color: white;
            padding: 0.8rem 1.8rem;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 700;
            z-index: 10;
            box-shadow: 0 8px 25px rgba(255, 140, 66, 0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Article Header */
        .article-header {
            max-width: 900px;
            margin: 0 auto 3rem;
            padding: 0 2rem;
        }

        .article-title {
            font-size: 3rem;
            line-height: 1.3;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .article-meta {
            display: flex;
            gap: 2rem;
            align-items: center;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #b8b8b8;
            font-size: 1rem;
        }

        .meta-icon {
            color: #ff8c42;
            font-size: 1.2rem;
        }

        .meta-item strong {
            color: #ffffff;
        }

        /* Article Content */
        .article-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .article-content :is(p, ul, ol, blockquote) {
            margin-bottom: 1.8rem;
            line-height: 1.9;
            font-size: 1.15rem;
            color: #d0d0d0;
            text-align: justify;
        }

        .article-content p:first-letter {
            font-size: 3.5rem;
            font-weight: bold;
            float: left;
            line-height: 1;
            margin: 0.1rem 0.15rem 0 0;
            color: #ff8c42;
        }

        .article-content h1,
        .article-content h2,
        .article-content h3,
        .article-content h4 {
            color: #ffffff;
            margin: 2.5rem 0 1.2rem;
            font-weight: 600;
        }

        .article-content h2 {
            font-size: 2rem;
            border-left: 5px solid #ff8c42;
            padding-left: 1rem;
        }

        .article-content h3 {
            font-size: 1.5rem;
            color: #ff8c42;
        }

        .article-content strong {
            color: #ffffff;
            font-weight: 600;
        }

        .article-content a {
            color: #ff8c42;
            text-decoration: underline;
            transition: color 0.3s;
        }

        .article-content a:hover {
            color: #ffa662;
        }

        .article-content ul,
        .article-content ol {
            padding-left: 2rem;
        }

        .article-content li {
            margin-bottom: 0.8rem;
            color: #d0d0d0;
        }

        .article-content blockquote {
            border-left: 4px solid #ff8c42;
            padding: 1.5rem 1.5rem 1.5rem 2rem;
            background: rgba(255, 140, 66, 0.05);
            border-radius: 0 15px 15px 0;
            font-style: italic;
            color: #e0e0e0;
        }

        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 15px;
            margin: 2rem 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .article-content code {
            background: rgba(255, 140, 66, 0.1);
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            color: #ff8c42;
            font-size: 0.95em;
        }

        .article-content pre {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 15px;
            overflow-x: auto;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin: 2rem 0;
        }

        .article-content pre code {
            background: none;
            padding: 0;
            color: #d0d0d0;
        }

        /* Related Articles */
        .related-section {
            max-width: 1400px;
            margin: 6rem auto 0;
            padding: 0 5%;
        }

        .related-title {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 3rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .related-item {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.4s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .related-item:hover {
            transform: translateY(-8px);
            border-color: rgba(255, 140, 66, 0.5);
            box-shadow: 0 15px 40px rgba(255, 140, 66, 0.3);
        }

        .related-image {
            width: 100%;
            height: 150px;
            background-size: cover;
            background-repeat: no-repeat;
            position: relative;
        }

        .related-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.4) 100%);
        }

        .kategori-badge-small {
            position: absolute;
            bottom: 8px;
            left: 8px;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 10;
            box-shadow: 0 2px 10px rgba(255, 140, 66, 0.4);
        }

        .related-content {
            padding: 1rem;
        }

        .related-content h3 {
            font-size: 1rem;
            color: #ffffff;
            margin-bottom: 0.8rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.8em;
        }

        .related-meta {
            display: flex;
            gap: 0.8rem;
            font-size: 0.75rem;
            color: #888;
            align-items: center;
        }

        .related-meta-item {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.3);
            padding: 3rem 8%;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 6rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-section h4 {
            color: #ffffff;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
        }

        .footer-section p {
            color: #888;
            line-height: 1.8;
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: #888;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: #ff8c42;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: #666;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .back-button {
                display: none;
            }

            .featured-image-wrapper {
                height: 300px;
                border-radius: 20px;
            }

            .article-title {
                font-size: 2rem;
            }

            .article-header,
            .article-content {
                padding: 0 1rem;
            }

            .article-content p {
                font-size: 1.05rem;
            }

            .article-content p:first-letter {
                font-size: 2.5rem;
            }

            .article-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .related-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .related-image {
                height: 120px;
            }

            .related-content h3 {
                font-size: 0.9rem;
            }

            .related-meta {
                font-size: 0.7rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->

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

    <!-- Back Button -->
    <a href="artikel.php" class="back-button">
        ← Kembali ke Artikel
    </a>

    <!-- Article Container -->
    <article class="article-container">
        
        <!-- Featured Image -->
        <?php if (!empty($gambar_url)): ?>
        <div class="featured-image-wrapper">
            <div 
                class="featured-image"
                style="
                    background-image: url('<?php echo $gambar_url; ?>');
                    background-position: center <?php echo $crop_position; ?>;
                "
            >
                <span class="kategori-badge-large"><?php echo $kategori; ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Article Header -->
        <header class="article-header">
            <h1 class="article-title"><?php echo $judul; ?></h1>
            
            <div class="article-meta">
                <div class="meta-item">
                    <span class="meta-icon">Date :</span>
                    <span><?php echo $tanggal; ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-icon"></span>
                    <strong><?php echo $kategori; ?></strong>
                </div>
                <div class="meta-item">
                    <span class="meta-icon"></span>
                    <span><?php echo $pelihat; ?> Views</span>
                </div>
            </div>
        </header>

        <!-- Article Content -->
        <div class="article-content">
            <?php echo $isi_html; ?>
        </div>

    </article>

    <!-- Related Articles -->
    <?php if ($related_articles && $related_articles->num_rows > 0): ?>
    <section class="related-section">
        <h2 class="related-title">Artikel Lainnya yang Mungkin Anda Suka</h2>
        <div class="related-grid">
            <?php 
            $bulan = array(
                1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
            );
            
            while($related = $related_articles->fetch_assoc()): 
                $rel_image_url = !empty($related['gambar']) ? 'adminpanel/gambar/' . htmlspecialchars($related['gambar']) : '';
                $rel_crop_position = $related['crop_y'] . '%';
                $rel_tanggal_obj = date_create($related['tanggal']);
                $rel_tanggal = date_format($rel_tanggal_obj, 'd') . ' ' . 
                               $bulan[date_format($rel_tanggal_obj, 'n')] . ' ' . 
                               date_format($rel_tanggal_obj, 'Y');
            ?>
            <a href="baca.php?id=<?php echo $related['id_artikel']; ?>" class="related-item">
                <div 
                    class="related-image"
                    style="
                        <?php if (!empty($rel_image_url)): ?>
                            background-image: url('<?php echo $rel_image_url; ?>');
                            background-position: center <?php echo $rel_crop_position; ?>;
                        <?php else: ?>
                            background: linear-gradient(135deg, #1a3a1f 0%, #0a1f0f 100%);
                        <?php endif; ?>
                    "
                >
                    <span class="kategori-badge-small"><?php echo htmlspecialchars($related['kategori']); ?></span>
                </div>
                <div class="related-content">
                    <h3><?php echo htmlspecialchars($related['judul']); ?></h3>
                    <div class="related-meta">
                        <span class="related-meta-item">📅 <?php echo $rel_tanggal; ?></span>
                        <span class="related-meta-item">👁 <?php echo number_format($related['pelihat']); ?></span>
                    </div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

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
<?php exit(); ?>