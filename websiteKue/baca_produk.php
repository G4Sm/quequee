<?php
include 'koneksi.php'; // Koneksi database

// --- 1. Validasi ID dari URL ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $error_message = "ID Produk tidak valid.";
    goto display_error;
}

$kategori_sql = "SELECT DISTINCT kategori FROM products WHERE status='published' AND kategori IS NOT NULL AND kategori != ''";
$kategori_result = $con->query($kategori_sql);
$kategori_options = [];
while ($row = $kategori_result->fetch_assoc()) {
    $kategori_options[] = $row['kategori'];
}

$id_product = intval($_GET['id']);
$product_data = null;
$error_message = null;

// --- 2. Logika Update Pelihat (View Counter) ---
$stmt_update = $con->prepare("UPDATE products SET pelihat = pelihat + 1 WHERE id_product = ?");
$stmt_update->bind_param("i", $id_product);
$stmt_update->execute();
$stmt_update->close();


// --- 3. Ambil Data Produk (Detail) ---
$stmt_select = $con->prepare("SELECT nama, harga, gambar, deskripsi, kategori, pelihat, rata_rating, total_ratings, crop_y FROM products WHERE id_product = ? AND status = 'published'");
$stmt_select->bind_param("i", $id_product);
$stmt_select->execute();
$result = $stmt_select->get_result();

if ($result->num_rows === 1) {
    $product_data = $result->fetch_assoc();
    
    $judul = htmlspecialchars($product_data['nama']);
    $harga = number_format($product_data['harga'], 0, ',', '.');
    $deskripsi_html = nl2br($product_data['deskripsi']);
    $kategori = htmlspecialchars($product_data['kategori']);
    $pelihat = number_format($product_data['pelihat']);
    $rata_rating = round($product_data['rata_rating'], 1);
    $total_ratings = number_format($product_data['total_ratings']);
    $gambar_url = !empty($product_data['gambar']) ? 'adminpanel/gambar/' . htmlspecialchars($product_data['gambar']) : '';
    $crop_position = $product_data['crop_y'] . '%';
} else {
    $error_message = "Produk tidak ditemukan atau belum dipublikasikan.";
    goto display_error;
}
$stmt_select->close();

// --- 4. Ambil Ulasan (Ratings) yang Sudah Ada ---
$ratings_list = [];
$stmt_ratings = $con->prepare("SELECT rating, komentar, tanggal FROM ratings WHERE product_id = ? ORDER BY tanggal DESC");
$stmt_ratings->bind_param("i", $id_product);
$stmt_ratings->execute();
$result_ratings = $stmt_ratings->get_result();
while ($row = $result_ratings->fetch_assoc()) {
    $ratings_list[] = $row;
}
$stmt_ratings->close();

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
    <title>Error</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(255, 140, 66, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 166, 98, 0.06) 0%, transparent 50%);
            z-index: -1;
            animation: gradient-shift 15s ease infinite;
        }

        @keyframes gradient-shift {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(5%, 5%) rotate(180deg); }
        }

        .error-box {
            max-width: 500px;
            padding: 3rem;
            background: rgba(10, 31, 15, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 140, 66, 0.3);
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .error-box h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #ff8c42;
        }

        .error-box p {
            margin-bottom: 2rem;
            color: #e0e0e0;
            line-height: 1.6;
        }

        .btn-back {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.4);
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 66, 0.6);
        }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>Terjadi Kesalahan!</h1>
        <p><?php echo $error_message; ?></p>
        <a href="produk.php" class="btn-back">← Kembali ke Katalog Produk</a>
    </div>
</body>
</html>
<?php exit(); ?>


<?php
display_content:
$con->close(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $judul; ?> - Detail Produk</title>
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
            line-height: 1.6;
            padding: 2rem;
            overflow-x: hidden;
        }

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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(10, 31, 15, 0.95);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            color: #ff8c42;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .back-link:hover {
            background: rgba(255, 140, 66, 0.1);
            transform: translateX(-5px);
        }

        .product-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .featured-image {
            width: 100%;
            height: 500px;
            background-color: rgba(0, 0, 0, 0.3);
            background-image: url('<?php echo $gambar_url; ?>');
            background-position: center <?php echo $crop_position; ?>;
            background-size: cover;
            background-repeat: no-repeat;
            border-radius: 15px;
            border: 2px solid rgba(255, 140, 66, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .info-wrapper h1 {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .price-tag {
            font-size: 2.5rem;
            color: #ff8c42;
            font-weight: 700;
            margin: 1rem 0;
            text-shadow: 0 2px 10px rgba(255, 140, 66, 0.3);
        }

        .rating-summary {
            background: rgba(255, 140, 66, 0.1);
            border: 1px solid rgba(255, 140, 66, 0.3);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            display: inline-block;
        }

        .star-rating {
            color: #ffa662;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .meta-info {
            font-size: 0.95rem;
            color: #b8b8b8;
            margin: 1.5rem 0;
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .meta-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .meta-info .material-icons {
            font-size: 1.2rem;
            color: #ff8c42;
        }

        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            padding: 1.2rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 1.5rem;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
        }

        .whatsapp-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(37, 211, 102, 0.6);
        }

        .description-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .description-box h2 {
            font-size: 1.8rem;
            color: #ff8c42;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .description-box p {
            color: #e0e0e0;
            line-height: 1.8;
        }

        .rating-form-box {
            background: rgba(255, 140, 66, 0.05);
            border: 1px solid rgba(255, 140, 66, 0.2);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .rating-form-box h3 {
            color: #ff8c42;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .rating-form-box p {
            color: #b8b8b8;
            margin-bottom: 1.5rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .rating-form-box label {
            display: block;
            margin-bottom: 0.5rem;
            color: #e0e0e0;
            font-weight: 600;
        }

        .rating-form-box input[type="text"],
        .rating-form-box select,
        .rating-form-box textarea {
            width: 100%;
            padding: 0.9rem;
            margin-bottom: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .rating-form-box input[type="text"]:focus,
        .rating-form-box select:focus,
        .rating-form-box textarea:focus {
            outline: none;
            border-color: #ff8c42;
            background: rgba(255, 140, 66, 0.1);
        }

        .rating-form-box textarea {
            resize: vertical;
            min-height: 100px;
        }

        .rating-form-box button {
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.4);
        }

        .rating-form-box button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 66, 0.6);
        }

        .reviews-section h2 {
            font-size: 1.8rem;
            color: #ff8c42;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .review-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .review-item:hover {
            background: rgba(255, 140, 66, 0.05);
            border-color: rgba(255, 140, 66, 0.3);
        }

        .review-rating {
            color: #ffa662;
            font-weight: 700;
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }

        .review-item p {
            color: #e0e0e0;
            margin-bottom: 0.8rem;
            line-height: 1.6;
        }

        .review-date {
            font-size: 0.85rem;
            color: #b8b8b8;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #b8b8b8;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            border: 1px dashed rgba(255, 255, 255, 0.1);
        }

        /* Responsive */
        @media (max-width: 968px) {
            .product-header {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .featured-image {
                height: 400px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .container {
                padding: 1.5rem;
            }

            .info-wrapper h1 {
                font-size: 1.8rem;
            }

            .price-tag {
                font-size: 2rem;
            }

            .featured-image {
                height: 300px;
            }

            .whatsapp-btn {
                width: 100%;
                justify-content: center;
            }

            .meta-info {
                flex-direction: column;
                gap: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .info-wrapper h1 {
                font-size: 1.5rem;
            }

            .price-tag {
                font-size: 1.8rem;
            }

            .description-box h2,
            .reviews-section h2 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="produk.php" class="back-link">
        <span class="material-icons">arrow_back</span>
        Kembali ke Katalog
    </a>
    
    <div class="product-header">
        <div class="image-wrapper">
            <?php if (!empty($gambar_url)): ?>
                <div class="featured-image"></div>
            <?php endif; ?>
        </div>
        
        <div class="info-wrapper">
            <h1><?php echo $judul; ?></h1>
            <div class="price-tag">Rp<?php echo $harga; ?></div>
            
            <div class="rating-summary">
                <span class="star-rating"><?php echo $rata_rating; ?> ⭐</span>
                <span style="color: #e0e0e0;"> (<?php echo $total_ratings; ?> ulasan)</span>
            </div>
            
            <div class="meta-info">
                <div class="meta-info-item">
                    <span class="material-icons">category</span>
                    <span><strong>Kategori:</strong> <?php echo $kategori; ?></span>
                </div>
                <div class="meta-info-item">
                    <span class="material-icons">visibility</span>
                    <span><strong>Dilihat:</strong> <?php echo $pelihat; ?> kali</span>
                </div>
            </div>
            
            <a href="https://wa.me/NOMOR_WHATSAPP_ANDA?text=Halo%2C%20saya%20tertarik%20dengan%20produk%20%2A<?php echo urlencode($judul); ?>%2A%20(ID%20<?php echo $id_product; ?>)%20yang%20saya%20lihat%20di%20website." 
               class="whatsapp-btn"
               target="_blank">
               <span class="material-icons">shopping_cart</span>
               Order via WhatsApp
            </a>
        </div>
    </div>
    
    <div class="description-box">
        <h2>
            <span class="material-icons">description</span>
            Deskripsi Produk
        </h2>
        <p><?php echo $deskripsi_html; ?></p>
    </div>

    <div class="rating-form-box">
        <h3>Berikan Ulasan Anda</h3>
        <p>Silakan masukkan kode unik yang Anda terima dari staf kami setelah pembelian untuk memberikan rating.</p>
        
        <?php 
        if (isset($_GET['rating_status'])) {
            $status = $_GET['rating_status'];
            if ($status == 'success') {
                echo '<div class="alert alert-success">✅ Terima kasih! Ulasan Anda berhasil disimpan.</div>';
            } elseif ($status == 'invalid_code') {
                echo '<div class="alert alert-error">❌ Kode ulasan tidak valid, sudah digunakan, atau tidak cocok dengan produk ini.</div>';
            } elseif ($status == 'missing_data') {
                 echo '<div class="alert alert-error">❌ Mohon lengkapi semua data (kode, rating, dan komentar).</div>';
            }
        }
        ?>
        
        <form action="submit_rating.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $id_product; ?>">
            
            <label for="review_code">Kode Ulasan Unik:</label>
            <input type="text" id="review_code" name="review_code" placeholder="Contoh: B3S7-22" required>
            
            <label for="rating_value">Beri Nilai:</label>
            <select id="rating_value" name="rating_value" required>
                <option value="">-- Pilih Bintang --</option>
                <option value="5">5 Bintang (Sangat Baik)</option>
                <option value="4">4 Bintang (Baik)</option>
                <option value="3">3 Bintang (Cukup)</option>
                <option value="2">2 Bintang (Kurang)</option>
                <option value="1">1 Bintang (Buruk)</option>
            </select>
            
            <label for="komentar">Komentar (Opsional):</label>
            <textarea id="komentar" name="komentar" rows="4" placeholder="Bagaimana pengalaman Anda dengan produk ini?"></textarea>
            
            <button type="submit">Kirim Ulasan</button>
        </form>
    </div>

    <div class="reviews-section">
        <h2>
            <span class="material-icons">rate_review</span>
            Ulasan Pembeli (<?php echo $total_ratings; ?>)
        </h2>
        <?php if (!empty($ratings_list)) {
            foreach ($ratings_list as $review) {
        ?>
        <div class="review-item">
            <div class="review-rating">
                <?php echo str_repeat('⭐', $review['rating']); ?> (<?php echo $review['rating']; ?>/5)
            </div>
            <p><?php echo htmlspecialchars($review['komentar']); ?></p>
            <div class="review-date">
                <span class="material-icons" style="font-size: 1rem;">schedule</span>
                Diulas pada: <?php echo date('d M Y', strtotime($review['tanggal'])); ?>
            </div>
        </div>
        <?php }
        } else { ?>
        <div class="empty-state">
            <span class="material-icons" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;">chat_bubble_outline</span>
            <p>Belum ada ulasan untuk produk ini. Jadilah yang pertama!</p>
        </div>
        <?php } ?>
    </div>
</div>

</body>
</html>