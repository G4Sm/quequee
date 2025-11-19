<?php
// File: edit_event.php (SETELAH PERBAIKAN SINTAKS ARRAY)

require 'session.php';
// PANGGIL FILE PBO BARU
require_once 'Event.php'; 
require_once 'EventManager.php'; 
require_once 'Database.php'; 

// --- INISIALISASI ---
$manager = new EventManager();
$message = '';

// --- LOGIKA UTAMA: Cek ID dan Ambil Data Lama (READ) ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_event = intval($_GET['id']);
$event_data = null; // Inisialisasi variabel yang diperlukan di View

try {
    // PBO: Ambil data event dari Manager
    // getEventById sekarang mengembalikan ARRAY
    $event_data = $manager->getEventById($id_event); // BARIS INI SEKITAR LINE 27
    
    if (!$event_data) {
        header("Location: index.php?status=error&msg=not_found");
        exit();
    }
    
} catch (Exception $e) {
    // Gagal mengambil data awal
    die("Error mengambil data event: " . $e->getMessage());
}

// --- LOGIKA UPDATE DATA (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $judul = $_POST['judul'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $crop_y = intval($_POST['crop_position_y'] ?? 50);
    
    $fileData = $_FILES['gambar'] ?? [];
    $paragrafData = $_POST['paragraf'] ?? [];

    // Mengakses gambar lama dari ARRAY $event_data
    $old_gambar = $event_data['gambar'] ?? null; 

    try {
        // PBO: Panggil metode UPDATE di Manager
        $updated_data = $manager->updateEvent(
            id_event: $id_event,
            judul: $judul,
            fileData: $fileData,
            paragrafData: $paragrafData,
            kategori: $kategori,
            crop_y: $crop_y,
            old_gambar: $old_gambar
        );
        
        // Update variabel $event_data dengan data yang baru di-update
        $event_data = $updated_data;
        
        $message = '<div class="alert alert-success">✅ Event berhasil diupdate! <a href="index.php" style="color: #fff; text-decoration: underline;">Kembali ke Dashboard</a></div>';

    } catch (Exception $e) {
        $message = '<div class="alert alert-error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// --- BAGIAN VIEW (HTML) ---
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link href="../css/adm.css" rel="stylesheet">
</head>
<body class="editEvent">

    <div class="admin-header">
        <h1>✏️ Edit Event #<?php echo $id_event; ?></h1>
        <a href="index.php" class="btn-back-header">← Kembali ke Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <div class="form-container">
        <form action="edit_event.php?id=<?php echo $id_event; ?>" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label for="judul">📝 Judul Event</label>
                <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($event_data['judul']); ?>" required> 
                <p class="helper-text">Edit judul event sesuai kebutuhan</p>
            </div>

            <div class="form-group">
                <label for="kategori">📁 Kategori</label>
                <select id="kategori" name="kategori" required>
                    <?php $kategori_db = $event_data['kategori']; ?>
                    <option value="Internal" <?php echo ($kategori_db == 'Internal' ? 'selected' : ''); ?>>Internal</option>
                    <option value="Eksternal" <?php echo ($kategori_db == 'Eksternal' ? 'selected' : ''); ?>>Eksternal</option>
                    <option value="Lainnya" <?php echo ($kategori_db == 'Lainnya' ? 'selected' : ''); ?>>Lainnya</option>
                </select>
            </div>

            <?php 
                // PERUBAHAN SINTAKS: ->gambar menjadi ['gambar']
                $current_image_url = !empty($event_data['gambar']) ? 'gambar/' . htmlspecialchars($event_data['gambar']) : ''; 
                // PERUBAHAN SINTAKS: ->crop_y menjadi ['crop_y']
                $current_crop_y = $event_data['crop_y'] ?? 50;
            ?>
            <div class="form-group">
                <label>🖼️ Gambar Utama Event</label>
                <div class="image-upload-section">
                    
                    <?php 
                        // Ambil URL dan Crop Position saat ini (sudah menggunakan array access)
                        $current_image_url = !empty($event_data['gambar']) ? 'gambar/' . htmlspecialchars($event_data['gambar']) : ''; 
                        $current_crop_y = $event_data['crop_y'] ?? 50;
                    ?>
            
                    <?php if (!empty($current_image_url)): ?>
                    <div class="current-image-info">
                        <strong>📷 Gambar Saat Ini:</strong> <?php echo basename($event_data['gambar']); ?>
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

            <div class="form-group">
                <div class="paragraf-section">
                    <div id="paragraf-container">
                        <?php
                        // PERUBAHAN SINTAKS: ->isi menjadi ['isi']
                        $isi_db = $event_data['isi'];
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

            <div class="form-actions">
                <button type="submit" class="btn-submit">✅ Update Event</button>
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
            alert("⚠️ Minimal harus ada satu paragraf dalam event.");
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
            alert('Event harus memiliki setidaknya satu paragraf yang terisi!');
        }
    });
</script>

</body>
</html>