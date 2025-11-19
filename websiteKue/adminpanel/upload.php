<?php
// Pastikan Anda sudah memulai session dan menyertakan koneksi database ($con atau $conn)
session_start();
include '../koneksi.php'; // Sesuaikan dengan nama file koneksi Anda

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid.']);
    exit();
}

$username = $_SESSION['username'];
$uploadDir = 'uploads/profiles/'; // Direktori penyimpanan foto profil
$newImageUrl = null;
$error = null;

// Ambil ID pengguna
$stmt = $con->prepare("SELECT id, profile_image FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$userId = $user_data['id'] ?? null;
$old_image = $user_data['profile_image'] ?? null;
$stmt->close();

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Pengguna tidak ditemukan.']);
    exit();
}

// 1. Ambil posisi Crop (Selalu ada, baik dari geser atau upload baru)
$crop_y = isset($_POST['profile_crop_y']) ? intval($_POST['profile_crop_y']) : 50;
$update_sql = "UPDATE users SET profile_crop_y = ?, ";
$bind_types = "ii"; // Tipe data: integer (crop_y), integer (userId)
$bind_params = [$crop_y];


// 2. Proses Upload Gambar Baru (Jika ada file yang dikirim)
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_image']['tmp_name'];
    $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
    $destPath = $uploadDir . $newFileName;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // Hapus file lama jika bukan default
        if ($old_image && $old_image != 'uploads/profiles/default.png' && file_exists($old_image)) {
            unlink($old_image);
        }
        $newImageUrl = $destPath;
        
        // Tambahkan update kolom gambar
        $update_sql .= "profile_image = ?, ";
        $bind_types = "s" . $bind_types; // Tambahkan 's' untuk string gambar
        array_unshift($bind_params, $newImageUrl); // Tambahkan URL gambar di depan array
    } else {
        $error = 'Gagal menyimpan file gambar.';
    }
}

// Jika terjadi error, kirim respons gagal
if ($error) {
    echo json_encode(['success' => false, 'message' => $error]);
    exit();
}


// 3. Finalisasi dan Eksekusi Query UPDATE
// Hapus koma terakhir dan tambahkan WHERE clause
$update_sql = rtrim($update_sql, ', ');
$update_sql .= " WHERE id = ?";

// Urutan parameter binding harus sesuai: [Gambar(jika ada), Crop_y, UserId]
$final_params = array_merge([$bind_types], $bind_params, [$userId]);

$stmt_update = $con->prepare($update_sql);

// Panggil bind_param secara dinamis
call_user_func_array([$stmt_update, 'bind_param'], refValues($final_params));

if ($stmt_update->execute()) {
    echo json_encode(['success' => true, 'new_image_url' => $newImageUrl ?? $old_image]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database.']);
}

$stmt_update->close();
$con->close();


// Fungsi bantu untuk call_user_func_array
function refValues($arr){
    if (strnatcmp(phpversion(), '5.3') >= 0) {
        $refs = [];
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}
?>