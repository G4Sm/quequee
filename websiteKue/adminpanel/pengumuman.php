<?php
include "../koneksi.php"; // berisi $con

// ambil data saat ini
$q = mysqli_query($con, "SELECT * FROM announcement WHERE id = 1");
$data = mysqli_fetch_assoc($q);

// update data
if(isset($_POST['save'])){
    $text = mysqli_real_escape_string($con, $_POST['text']);
    mysqli_query($con, "UPDATE announcement SET text='$text' WHERE id = 1");
    header("Location: index.php?success=1");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Pengumuman</title>
    <style>
        body{font-family:Arial;padding:20px;}
        .box{width:400px;padding:20px;border:1px solid #ccc;border-radius:10px;}
        .msg{padding:10px;background:#d4f4d7;border:1px solid #8bc98e;margin-bottom:10px;border-radius:5px;}
    </style>
</head>
<body>

<h2>Manajemen Pengumuman</h2>

<?php if(isset($_GET['success'])): ?>
<div class="msg">Pengumuman berhasil diperbarui!</div>
<?php endif; ?>

<div class="box">
<form method="POST">
    <label>Isi Pengumuman:</label><br>
    <textarea name="text" style="width:100%;height:80px;"><?= htmlspecialchars($data['text']) ?></textarea>
    <br><br>
    <button type="submit" name="save">Simpan</button>
</form>
</div>

</body>
</html>
