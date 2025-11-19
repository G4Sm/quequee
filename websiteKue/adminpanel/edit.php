<?php
require 'session.php';
include '../koneksi.php';

// --- LOGIKA UTAMA: Cek ID dan Ambil Data Lama ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_artikel = intval($_GET['id']);
$artikel_data = null;
$message = '';
$uploadDir = 'gambar/'; 

// 1. Ambil Data Artikel dari Database
$stmt = $con->prepare("SELECT * FROM artikel WHERE id_artikel = ?");
$stmt->bind_param("i", $id_artikel);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $artikel_data = $result->fetch_assoc();
} else {
    header("Location: index.php?status=error&msg=not_found");
    exit();
}
$stmt->close();

// --- LOGIKA UPDATE DATA ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $judul = $con->real_escape_string($_POST['judul']);
    $kategori = $con->real_escape_string($_POST['kategori']);
    $crop_y = intval($_POST['crop_position_y']);
    $old_gambar = $artikel_data['gambar'];
    $fileName = $old_gambar;

    // 1. Proses Upload Gambar Baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['gambar']['tmp_name'];
        $fileExtension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $newFileName = uniqid() . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            if ($old_gambar && file_exists($uploadDir . $old_gambar)) {
                unlink($uploadDir . $old_gambar);
            }
            $fileName = $newFileName;
        } else {
            $message = '<div class="alert alert-error">❌ Error: Gagal menyimpan file gambar baru.</div>';
            goto end_update;
        }
    }

    // 2. Menggabungkan Paragraf dengan format konsisten
    $fullContent = "";
    if (isset($_POST['paragraf']) && is_array($_POST['paragraf'])) {
        foreach ($_POST['paragraf'] as $paragraf) {
            $sanitized_paragraf = trim($paragraf);
            if (!empty($sanitized_paragraf)) {
                $sanitized_paragraf = htmlspecialchars($sanitized_paragraf, ENT_QUOTES, 'UTF-8');
                $fullContent .= "<p>" . $sanitized_paragraf . "</p>\n";
            }
        }
    } else {
        $message = '<div class="alert alert-error">❌ Error: Konten artikel tidak boleh kosong.</div>';
        goto end_update;
    }

    // 3. Query UPDATE
    $stmt_update = $con->prepare("UPDATE artikel SET judul=?, gambar=?, isi=?, kategori=?, crop_y=? WHERE id_artikel=?");
    $stmt_update->bind_param("ssssii", $judul, $fileName, $fullContent, $kategori, $crop_y, $id_artikel);

    if ($stmt_update->execute()) {
        // Update data lokal untuk tampilan form
        $artikel_data['judul'] = $judul;
        $artikel_data['gambar'] = $fileName;
        $artikel_data['isi'] = $fullContent;
        $artikel_data['kategori'] = $kategori;
        $artikel_data['crop_y'] = $crop_y;
        
        $message = '<div class="alert alert-success">✅ Artikel berhasil diupdate! <a href="index.php" style="color: #fff; text-decoration: underline;">Kembali ke Dashboard</a></div>';
    } else {
        $message = '<div class="alert alert-error">❌ Error: Gagal mengupdate artikel. ' . $stmt_update->error . '</div>';
    }

    $stmt_update->close();
}

end_update:
$con->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel #<?php echo $id_artikel; ?> - Admin Rumah Que Que</title>
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
            padding: 20px;
        }

        /* Header */
        .admin-header {
            max-width: 1200px;
            margin: 0 auto 2rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .admin-header h1 {
            font-size: 2rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-back-header {
            padding: 0.8rem 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 140, 66, 0.5);
            border-radius: 50px;
            color: #ff8c42;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-back-header:hover {
            background: rgba(255, 140, 66, 0.1);
            transform: translateY(-2px);
        }

        /* Alert Messages */
        .alert {
            max-width: 1200px;
            margin: 0 auto 2rem;
            padding: 1.2rem 1.5rem;
            border-radius: 15px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 2px solid rgba(40, 167, 69, 0.5);
            color: #4ade80;
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.2);
            border: 2px solid rgba(220, 53, 69, 0.5);
            color: #f87171;
        }

        /* Form Container */
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: #ffffff;
            font-size: 1.1rem;
        }

        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 1rem 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input[type="text"]:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff8c42;
            background: rgba(255, 255, 255, 0.08);
        }

        .form-group select option {
            background: #1a3a1f;
            color: white;
        }

        /* Image Upload Section */
        .image-upload-section {
            background: rgba(255, 140, 66, 0.05);
            padding: 2rem;
            border-radius: 15px;
            border: 2px dashed rgba(255, 140, 66, 0.3);
        }

        .file-input-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: block;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            color: white;
            text-align: center;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .file-input-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 66, 0.5);
        }

        .current-image-info {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .current-image-info strong {
            color: #ff8c42;
        }

        .image-preview-area {
            margin-top: 1.5rem;
        }

        .preview-info {
            color: #b8b8b8;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            text-align: center;
        }

        .image-preview-box {
            width: 100%;
            height: 500px;
            background-color: rgba(0, 0, 0, 0.3);
            background-size: cover;
            background-repeat: no-repeat;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            cursor: grab;
            border: 2px solid rgba(255, 255, 255, 0.1);
            transition: border-color 0.3s;
        }

        .image-preview-box:hover {
            border-color: rgba(255, 140, 66, 0.5);
        }

        .image-preview-box.dragging {
            cursor: grabbing;
        }

        #drag-hint {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #666;
            font-size: 1.1rem;
            text-align: center;
            pointer-events: none;
        }

        .position-indicator {
            margin-top: 1rem;
            padding: 0.8rem;
            background: rgba(255, 140, 66, 0.1);
            border-radius: 10px;
            text-align: center;
            color: #ff8c42;
            font-weight: 600;
        }

        /* Paragraf Section */
        .paragraf-section {
            background: rgba(255, 255, 255, 0.02);
            padding: 2rem;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h3 {
            color: #ffffff;
            font-size: 1.2rem;
        }

        .btn-add-paragraf {
            padding: 0.6rem 1.2rem;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-add-paragraf:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.4);
        }

        #paragraf-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .paragraf-input-group {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            background: rgba(255, 255, 255, 0.03);
            padding: 1rem;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .paragraf-input-group textarea {
            flex: 1;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            resize: vertical;
            min-height: 120px;
            line-height: 1.6;
        }

        .paragraf-input-group textarea:focus {
            outline: none;
            border-color: #ff8c42;
            background: rgba(255, 255, 255, 0.08);
        }

        .paragraf-input-group textarea::placeholder {
            color: #666;
        }

        .btn-remove-paragraf {
            padding: 0.8rem 1.2rem;
            background: rgba(220, 53, 69, 0.2);
            border: 2px solid rgba(220, 53, 69, 0.5);
            color: #f87171;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            white-space: nowrap;
            border: none;
        }

        .btn-remove-paragraf:hover {
            background: rgba(220, 53, 69, 0.3);
            transform: scale(1.05);
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            flex-wrap: wrap;
        }

        .btn-submit {
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(255, 140, 66, 0.5);
        }

        .btn-cancel {
            padding: 1rem 2.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: #e0e0e0;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* Helper Text */
        .helper-text {
            color: #888;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            font-style: italic;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-container {
                padding: 1.5rem;
            }

            .admin-header {
                padding: 1.5rem;
                flex-direction: column;
                text-align: center;
            }

            .admin-header h1 {
                font-size: 1.5rem;
            }

            .image-preview-box {
                height: 300px;
            }

            .paragraf-input-group {
                flex-direction: column;
            }

            .btn-remove-paragraf {
                width: 100%;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-submit,
            .btn-cancel {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="admin-header">
        <h1>✏️ Edit Artikel #<?php echo $id_artikel; ?></h1>
        <a href="index.php" class="btn-back-header">← Kembali ke Dashboard</a>
    </div>

    <!-- Alert Message -->
    <?php if (!empty($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="form-container">
        <form action="edit.php?id=<?php echo $id_artikel; ?>" method="POST" enctype="multipart/form-data">
            
            <!-- Judul -->
            <div class="form-group">
                <label for="judul">📝 Judul Artikel</label>
                <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($artikel_data['judul']); ?>" required>
                <p class="helper-text">Edit judul artikel sesuai kebutuhan</p>
            </div>

            <!-- Kategori -->
            <div class="form-group">
                <label for="kategori">📁 Kategori</label>
                <select id="kategori" name="kategori" required>
                    <?php $kategori_db = $artikel_data['kategori']; ?>
                    <option value="Teknologi" <?php echo ($kategori_db == 'Teknologi' ? 'selected' : ''); ?>>Teknologi</option>
                    <option value="Edukasi" <?php echo ($kategori_db == 'Edukasi' ? 'selected' : ''); ?>>Edukasi</option>
                    <option value="Politik" <?php echo ($kategori_db == 'Politik' ? 'selected' : ''); ?>>Politik</option>
                    <option value="Kesehatan" <?php echo ($kategori_db == 'Kesehatan' ? 'selected' : ''); ?>>Kesehatan</option>
                </select>
            </div>

            <!-- Upload Gambar -->
            <?php 
                $current_image_url = !empty($artikel_data['gambar']) ? 'gambar/' . htmlspecialchars($artikel_data['gambar']) : ''; 
                $current_crop_y = $artikel_data['crop_y'] ?? 50;
            ?>
            <div class="form-group">
                <label>🖼️ Gambar Utama Artikel</label>
                <div class="image-upload-section">
                    
                    <?php if (!empty($current_image_url)): ?>
                    <div class="current-image-info">
                        <strong>📷 Gambar Saat Ini:</strong> <?php echo basename($artikel_data['gambar']); ?>
                    </div>
                    <?php endif; ?>

                    <div class="file-input-wrapper">
                        <input type="file" id="gambar" name="gambar" accept="image/*" onchange="setupImageDrag(event)">
                        <label for="gambar" class="file-input-label">
                            📤 Upload Gambar Baru (Opsional)
                        </label>
                    </div>

                    <input type="hidden" id="crop-position-y" name="crop_position_y" value="<?php echo $current_crop_y; ?>">

                    <div class="image-preview-area">
                        <p class="preview-info">💡 Geser gambar untuk mengatur posisi fokus. Upload gambar baru untuk mengganti gambar lama.</p>
                        <div 
                            class="image-preview-box" 
                            id="image-preview-box"
                            style="<?php echo !empty($current_image_url) ? "background-image: url('{$current_image_url}'); background-position: center {$current_crop_y}%;" : ''; ?>"
                        >
                            <p id="drag-hint" style="display: <?php echo !empty($current_image_url) ? 'none' : 'block'; ?>">Belum ada gambar. Upload gambar terlebih dahulu.</p>
                        </div>
                        <div class="position-indicator">
                            Posisi Vertikal: <span id="position-value"><?php echo $current_crop_y; ?></span>%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Isi Artikel (Paragraf) -->
            <div class="form-group">
                <div class="paragraf-section">
                    <div class="section-header">
                        <label style="margin-bottom: 0;">📄 Isi Artikel</label>
                        <button type="button" class="btn-add-paragraf" onclick="addParagraf()">
                            ➕ Tambah Paragraf
                        </button>
                    </div>
                    <p class="helper-text" style="margin-bottom: 1.5rem;">Edit atau tambahkan paragraf baru sesuai kebutuhan. Setiap paragraf akan diformat dengan jarak yang sesuai.</p>
                    
                    <div id="paragraf-container">
                        <?php
                        $isi_db = $artikel_data['isi'];
                        preg_match_all('/<p>(.*?)<\/p>/s', $isi_db, $matches);
                        $paragraf_array = $matches[1] ?? [''];

                        $count = 0;
                        foreach ($paragraf_array as $paragraf_text) {
                            $count++;
                        ?>
                        <div class="paragraf-input-group">
                            <textarea name="paragraf[]" placeholder="Masukkan Paragraf <?php echo $count; ?>..." required><?php echo htmlspecialchars($paragraf_text); ?></textarea>
                            <button type="button" class="btn-remove-paragraf" onclick="removeParagraf(this)">🗑️ Hapus</button>
                        </div>
                        <?php
                        }
                        if ($count == 0) {
                        ?>
                        <div class="paragraf-input-group">
                            <textarea name="paragraf[]" placeholder="Masukkan Paragraf 1..." required></textarea>
                            <button type="button" class="btn-remove-paragraf" onclick="removeParagraf(this)">🗑️ Hapus</button>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">✅ Update Artikel</button>
                <a href="index.php" class="btn-cancel">❌ Batal</a>
            </div>

        </form>
    </div>

<script>
    // --- Fungsionalitas Geser Gambar ---
    let isDragging = false;
    let startY = 0;
    let startPos = 0;
    const previewBox = document.getElementById('image-preview-box');
    const cropInput = document.getElementById('crop-position-y');
    const positionValueSpan = document.getElementById('position-value');
    
    // Inisialisasi drag pada gambar lama
    if (previewBox.style.backgroundImage) {
        previewBox.addEventListener('mousedown', dragStart);
        previewBox.addEventListener('mouseup', dragEnd);
        previewBox.addEventListener('mouseleave', dragEnd);
        previewBox.addEventListener('mousemove', dragMove);
        
        // Touch events
        previewBox.addEventListener('touchstart', touchStart);
        previewBox.addEventListener('touchend', dragEnd);
        previewBox.addEventListener('touchmove', touchMove);
    }
    
    function setupImageDrag(event) {
        const dragHint = document.getElementById('drag-hint');
        dragHint.style.display = 'none';

        const reader = new FileReader();
        reader.onload = function(){
            const imageUrl = reader.result;
            previewBox.style.backgroundImage = `url('${imageUrl}')`;
            previewBox.style.backgroundPosition = 'center 50%';
            cropInput.value = 50;
            positionValueSpan.textContent = 50;

            if (!previewBox._listenersAdded) {
                previewBox.addEventListener('mousedown', dragStart);
                previewBox.addEventListener('mouseup', dragEnd);
                previewBox.addEventListener('mouseleave', dragEnd);
                previewBox.addEventListener('mousemove', dragMove);
                
                previewBox.addEventListener('touchstart', touchStart);
                previewBox.addEventListener('touchend', dragEnd);
                previewBox.addEventListener('touchmove', touchMove);
                
                previewBox._listenersAdded = true;
            }
        };
        
        if (event.target.files.length > 0) {
            reader.readAsDataURL(event.target.files[0]);
            const fileName = event.target.files[0].name;
            document.querySelector('.file-input-label').textContent = `✅ ${fileName}`;
        }
    }

    function dragStart(e) {
        if (!previewBox.style.backgroundImage) return;
        e.preventDefault();
        isDragging = true;
        previewBox.classList.add('dragging');
        startY = e.clientY;
        startPos = parseFloat(cropInput.value || 50);
    }

    function touchStart(e) {
        if (!previewBox.style.backgroundImage) return;
        isDragging = true;
        previewBox.classList.add('dragging');
        startY = e.touches[0].clientY;
        startPos = parseFloat(cropInput.value || 50);
    }

    function dragEnd() {
        if (!isDragging) return;
        isDragging = false;
        previewBox.classList.remove('dragging');
    }

    function dragMove(e) {
        if (!isDragging) return;
        
        const deltaY = e.clientY - startY;
        const boxHeight = previewBox.clientHeight;
        const deltaPercent = (deltaY / boxHeight) * 100 * 0.5;
        
        let newPos = startPos - deltaPercent;
        newPos = Math.max(0, Math.min(100, newPos));
        
        previewBox.style.backgroundPosition = `center ${newPos.toFixed(0)}%`;
        cropInput.value = newPos.toFixed(0);
        positionValueSpan.textContent = newPos.toFixed(0);
    }

    function touchMove(e) {
        if (!isDragging) return;
        
        const deltaY = e.touches[0].clientY - startY;
        const boxHeight = previewBox.clientHeight;
        const deltaPercent = (deltaY / boxHeight) * 100 * 0.5;
        
        let newPos = startPos - deltaPercent;
        newPos = Math.max(0, Math.min(100, newPos));
        
        previewBox.style.backgroundPosition = `center ${newPos.toFixed(0)}%`;
        cropInput.value = newPos.toFixed(0);
        positionValueSpan.textContent = newPos.toFixed(0);
    }

    // --- Fungsionalitas Tambah/Hapus Paragraf ---
    function addParagraf() {
        const container = document.getElementById('paragraf-container');
        const count = container.children.length + 1;

        const newGroup = document.createElement('div');
        newGroup.classList.add('paragraf-input-group');
        
        newGroup.innerHTML = `
            <textarea name="paragraf[]" placeholder="Masukkan paragraf ${count}..." required></textarea>
            <button type="button" class="btn-remove-paragraf" onclick="removeParagraf(this)">🗑️ Hapus</button>
        `;
        
        container.appendChild(newGroup);
        newGroup.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function removeParagraf(button) {
        const container = document.getElementById('paragraf-container');
        if (container.children.length > 1) {
            button.closest('.paragraf-input-group').remove();
        } else {
            alert("⚠️ Minimal harus ada satu paragraf dalam artikel.");
        }
    }

    // Validasi form
    document.querySelector('form').addEventListener('submit', function(e) {
        const paragraphs = document.querySelectorAll('textarea[name="paragraf[]"]');
        let hasContent = false;
        
        paragraphs.forEach(p => {
            if (p.value.trim() !== '') {
                hasContent = true;
            }
        });

        if (!hasContent) {
            e.preventDefault();
            alert('Artikel harus memiliki setidaknya satu paragraf yang terisi!');
        }
    });
</script>

</body>
</html>