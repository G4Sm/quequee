<?php
// File: tambah_event.php (SETELAH REFACTORING PBO)

require 'session.php';
// include '../koneksi.php'; // <--- HAPUS koneksi MySQLi lama

// PANGGIL FILE PBO BARU
require_once 'Event.php'; 
require_once 'EventManager.php'; 
require_once 'Database.php'; 

// --- INISIALISASI ---
$manager = new EventManager();
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data POST dan FILES
    $judul = $_POST['judul'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $crop_y = intval($_POST['crop_position_y'] ?? 50);
    
    $fileData = $_FILES['gambar'] ?? [];
    $paragrafData = $_POST['paragraf'] ?? [];

    try {
        // PBO: Panggil metode CREATE di Manager
        $manager->createEvent(
            judul: $judul,
            fileData: $fileData,
            paragrafData: $paragrafData,
            kategori: $kategori,
            crop_y: $crop_y
        );
        
        $message = '<div class="alert alert-success">✅ Event berhasil ditambahkan! Kembali ke <a href="index.php" style="color: #fff; text-decoration: underline;">Dashboard</a></div>';

    } catch (Exception $e) {
        // Tangani semua error (validasi, upload, atau database) yang dilempar dari Manager
        $message = '<div class="alert alert-error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// end_process: <--- Label ini tidak diperlukan lagi dalam struktur PBO
// $con->close(); // <--- TIDAK ADA $con lagi

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Event Baru - Admin Rumah Que Que</title>
    <link href="../css/adm.css" rel="stylesheet">
</head>
<body class="tambahEvent">

    <div class="admin-header">
        <h1>✍️ Tambah Event Baru</h1>
        <a href="index.php" class="btn-back-header">← Kembali ke Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <div class="form-container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label for="judul">📝 Judul Event</label>
                <input type="text" id="judul" name="judul" placeholder="Masukkan judul event yang menarik..." required>
                <p class="helper-text">Buatlah judul yang menarik dan deskriptif (maksimal 100 karakter)</p>
            </div>

            <div class="form-group">
                <label for="kategori">📁 Kategori</label>
                <select id="kategori" name="kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Internal">Internal</option>
                    <option value="Eksternal">Eksternal</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label>🖼️ Gambar Utama Event</label>
                <div class="image-upload-section">
                    <div class="file-input-wrapper">
                        <input type="file" id="gambar" name="gambar" accept="image/*" onchange="setupImageDrag(event)" required>
                        <label for="gambar" class="file-input-label">
                            📤 Klik untuk Upload Gambar
                        </label>
                    </div>

                    <input type="hidden" id="crop-position-y" name="crop_position_y" value="50">

                    <div class="image-preview-area">
                        <p class="preview-info">💡 Setelah upload, geser gambar untuk menentukan area fokus yang akan ditampilkan</p>
                        <div class="image-preview-box" id="image-preview-box">
                            <p id="drag-hint">Belum ada gambar. Upload gambar terlebih dahulu.</p>
                        </div>
                        <div class="position-indicator">
                            Posisi Vertikal: <span id="position-value">50</span>%
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="paragraf-section">
                    <div class="section-header">
                        <label style="margin-bottom: 0;">📄 Isi Event</label>
                        <button type="button" class="btn-add-paragraf" onclick="addParagraf()">
                            ➕ Tambah Paragraf
                        </button>
                    </div>
                    <p class="helper-text" style="margin-bottom: 1.5rem;">Pisahkan event menjadi beberapa paragraf untuk keterbacaan yang lebih baik. Setiap paragraf akan otomatis diformat dengan jarak yang sesuai.</p>
                    
                    <div id="paragraf-container">
                        <div class="paragraf-input-group">
                            <textarea name="paragraf[]" placeholder="Masukkan paragraf pertama event Anda di sini. Tulislah dengan gaya yang mudah dipahami dan menarik..." required></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">✅ Simpan & Publikasikan Event</button>
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
        
        function setupImageDrag(event) {
            const dragHint = document.getElementById('drag-hint');
            dragHint.style.display = 'none';

            const reader = new FileReader();
            reader.onload = function(){
                const imageUrl = reader.result;
                previewBox.style.backgroundImage = `url('${imageUrl}')`;
                
                // Set posisi awal
                previewBox.style.backgroundPosition = 'center 50%';
                cropInput.value = 50;
                positionValueSpan.textContent = 50;

                // Event listeners
                if (!previewBox._listenersAdded) {
                    previewBox.addEventListener('mousedown', dragStart);
                    previewBox.addEventListener('mouseup', dragEnd);
                    previewBox.addEventListener('mouseleave', dragEnd);
                    previewBox.addEventListener('mousemove', dragMove);

                    // Touch events untuk mobile
                    previewBox.addEventListener('touchstart', touchStart);
                    previewBox.addEventListener('touchend', dragEnd);
                    previewBox.addEventListener('touchmove', touchMove);
                    previewBox._listenersAdded = true;
                }
            };
            
            if (event.target.files.length > 0) {
                reader.readAsDataURL(event.target.files[0]);
                // Update label
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
            
            const currentPos = previewBox.style.backgroundPosition.split(' ')[1];
            startPos = parseFloat(currentPos || '50%');
        }

        function touchStart(e) {
             if (!previewBox.style.backgroundImage) return;
            isDragging = true;
            previewBox.classList.add('dragging');
            startY = e.touches[0].clientY;
            
            const currentPos = previewBox.style.backgroundPosition.split(' ')[1];
            startPos = parseFloat(currentPos || '50%');
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
                alert("⚠️ Minimal harus ada satu paragraf dalam event.");
            }
        }

        // Validasi form sebelum submit
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
                alert('⚠️ Event harus memiliki setidaknya satu paragraf yang terisi!');
            }
        });
    </script>

</body>
</html>