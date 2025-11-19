<?php
session_start();
include '../koneksi.php'; 

// Cek apakah ID pengguna tersedia
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$userId = intval($_GET['id']);
$message = '';
$uploadDir = 'uploads/profiles/';
$profile_data = null;

// 1. Ambil Data Pengguna Saat Ini
$stmt = $con->prepare("SELECT id, profile_image, profile_crop_y FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $profile_data = $result->fetch_assoc();
} else {
    header("Location: index.php");
    exit();
}
$stmt->close();

$currentImage = $profile_data['profile_image'] ?? 'uploads/profiles/default.png';
$currentCropY = $profile_data['profile_crop_y'] ?? 50;


// 2. Logika Update Data (Jika form disubmit)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $crop_y = intval($_POST['crop_position_y']);
    $fileName = $currentImage;

    $isNewUpload = false;

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $isNewUpload = true;
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            if ($currentImage != 'uploads/profiles/default.png' && file_exists($currentImage)) {
                unlink($currentImage);
            }
            $fileName = $destPath;
        } else {
            $message = '<div class="alert alert-error">❌ Error: Gagal menyimpan file gambar baru.</div>';
            goto end_update;
        }
    }
    
    if ($isNewUpload) {
        $stmt_update = $con->prepare("UPDATE users SET profile_image = ?, profile_crop_y = ? WHERE id = ?");
        if (!$stmt_update) { 
             $message = '<div class="alert alert-error">❌ Error Prepare (Upload): ' . $con->error . '</div>'; 
             goto end_update;
        }
        $stmt_update->bind_param("sii", $fileName, $crop_y, $userId);

    } else {
        $stmt_update = $con->prepare("UPDATE users SET profile_crop_y = ? WHERE id = ?");
        if (!$stmt_update) { 
             $message = '<div class="alert alert-error">❌ Error Prepare (Geser): ' . $con->error . '</div>'; 
             goto end_update;
        }
        $stmt_update->bind_param("ii", $crop_y, $userId);
    }
    
    if ($stmt_update->execute()) {
        $message = '<div class="alert alert-success">✅ Foto profil berhasil diupdate! <a href="index.php">Kembali ke Dashboard</a></div>';
        
        $currentImage = $fileName;
        $currentCropY = $crop_y;
        
    } else {
        $message = '<div class="alert alert-error">❌ Error: Gagal mengupdate profil. ' . $stmt_update->error . '</div>';
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
    <title>Edit Foto Profil - Rumah Que Que</title>
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
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
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
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(5%, 5%) rotate(120deg); }
            66% { transform: translate(-5%, 5%) rotate(240deg); }
        }

        /* Form Container */
        .form-container {
            max-width: 700px;
            width: 100%;
            background: rgba(10, 31, 15, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        h2 {
            font-size: 2rem;
            background: linear-gradient(135deg, #ffffff, #ff8c42);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
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

        .alert a {
            color: #ff8c42;
            text-decoration: underline;
            font-weight: 600;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: #e0e0e0;
            font-size: 1.1rem;
        }

        .form-group input[type="file"] {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 140, 66, 0.3);
            border-radius: 12px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s;
        }

        .form-group input[type="file"]:hover {
            border-color: #ff8c42;
            background: rgba(255, 140, 66, 0.1);
        }

        .meta-info {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #b8b8b8;
        }

        /* Image Preview Area */
        .image-preview-area {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
        }

        .image-preview-area > p {
            text-align: center;
            margin-bottom: 1rem;
            color: #e0e0e0;
            font-weight: 500;
        }

        .image-preview-box {
            width: 100%;
            height: 400px;
            margin: 0 auto;
            overflow: hidden;
            position: relative;
            background-color: rgba(0, 0, 0, 0.3);
            cursor: grab;
            background-size: cover;
            background-repeat: no-repeat;
            border-radius: 12px;
            border: 2px solid rgba(255, 140, 66, 0.3);
        }

        .image-preview-box:active {
            cursor: grabbing;
        }

        .profile-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border: 4px solid rgba(255, 140, 66, 0.8);
            border-radius: 50%;
            pointer-events: none;
            box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.5);
        }

        .position-info {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.95rem;
            color: #ff8c42;
            font-weight: 600;
        }

        #position-value {
            font-size: 1.2rem;
            font-weight: 700;
        }

        /* Buttons */
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn-submit,
        .btn-back {
            flex: 1;
            min-width: 150px;
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .btn-submit {
            background: linear-gradient(135deg, #ff8c42, #ffa662);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 66, 0.6);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            color: #e0e0e0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .form-container {
                padding: 2rem 1.5rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .image-preview-box {
                height: 300px;
            }

            .profile-frame {
                width: 150px;
                height: 150px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn-submit,
            .btn-back {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 1.5rem 1rem;
            }

            h2 {
                font-size: 1.3rem;
            }

            .image-preview-box {
                height: 250px;
            }

            .profile-frame {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Foto Profil</h2>
    <?php echo $message; ?>

    <form action="editpp.php?id=<?php echo $userId; ?>" method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label for="profile_image">
                <span class="material-icons" style="vertical-align: middle; margin-right: 0.5rem;">add_photo_alternate</span>
                Upload/Ganti Foto Profil
            </label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="setupImageDrag(event)">
            <p class="meta-info">Upload foto baru atau geser foto yang ada di bawah untuk menyesuaikan posisi.</p>
        </div>
        
        <input type="hidden" id="crop-position-y" name="crop_position_y" value="<?php echo $currentCropY; ?>"> 

        <div class="image-preview-area">
            <p>Geser Foto untuk Menentukan Area Crop</p>
            <div 
                class="image-preview-box" 
                id="image-preview-box"
                style="background-image: url('<?php echo $currentImage; ?>?t=<?php echo time(); ?>'); background-position: center <?php echo $currentCropY; ?>%;"
            >
                <div class="profile-frame"></div>
            </div>
            <p class="position-info">Posisi Vertikal: <span id="position-value"><?php echo $currentCropY; ?></span>%</p>
        </div>
        
        <div class="button-group">
            <button type="submit" class="btn-submit">
                <span class="material-icons" style="vertical-align: middle; margin-right: 0.5rem; font-size: 1.2rem;">save</span>
                Simpan Perubahan
            </button>
            <a href="index.php" class="btn-back">
                <span class="material-icons" style="vertical-align: middle; margin-right: 0.5rem; font-size: 1.2rem;">arrow_back</span>
                Batal & Kembali
            </a>
        </div>

    </form>
</div>

<script>
    let isDragging = false;
    let startY = 0;
    let startPos = 0;
    const previewBox = document.getElementById('image-preview-box');
    const cropInput = document.getElementById('crop-position-y');
    const positionValueSpan = document.getElementById('position-value');
    
    function dragStart(e) {
        e.preventDefault();
        isDragging = true;
        previewBox.style.cursor = 'grabbing';
        startY = e.clientY;
        startPos = parseFloat(cropInput.value || 50); 
    }

    function dragEnd() {
        if (!isDragging) return;
        isDragging = false;
        previewBox.style.cursor = 'grab';
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
    
    function setupImageDrag(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const imageUrl = reader.result;
            previewBox.style.backgroundImage = `url('${imageUrl}')`;
            
            previewBox.style.backgroundPosition = 'center 50%';
            cropInput.value = 50;
            positionValueSpan.textContent = 50;
        };
        
        if (event.target.files.length > 0) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        previewBox.addEventListener('mousedown', dragStart);
        document.addEventListener('mouseup', dragEnd);
        previewBox.addEventListener('mousemove', dragMove);
        previewBox.addEventListener('mouseleave', dragEnd);
    });
</script>
</body>
</html>