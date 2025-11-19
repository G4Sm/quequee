-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Nov 2025 pada 12.34
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_online`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `announcement`
--

CREATE TABLE `announcement` (
  `id` int(11) NOT NULL,
  `text` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `announcement`
--

INSERT INTO `announcement` (`id`, `text`, `updated_at`) VALUES
(1, 'Selamat Datang di Rumah Que Que', '2025-11-17 08:57:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `artikel`
--

CREATE TABLE `artikel` (
  `id_artikel` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `crop_y` int(11) DEFAULT 50,
  `isi` text NOT NULL,
  `pelihat` int(11) DEFAULT 0,
  `tanggal` date NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `artikel`
--

INSERT INTO `artikel` (`id_artikel`, `judul`, `gambar`, `crop_y`, `isi`, `pelihat`, `tanggal`, `kategori`, `status`, `created_at`, `updated_at`) VALUES
(10, 'Testing', '68d7108e00fbf.png', 0, '<p>saca</p><p>wederf</p>', 16, '2025-09-27', 'Teknologi', 'published', '2025-09-26 22:15:42', '2025-10-05 22:58:46'),
(15, 'asdA', '68d73a288ffec.jpg', 87, '<p>DAs</p>', 0, '2025-09-27', 'Teknologi', 'published', '2025-09-27 01:13:12', '2025-09-28 07:12:08'),
(17, 'asdc', '68d8ed8f7ba8c.png', 50, '<p>asdas</p>', 0, '2025-09-28', 'Teknologi', 'published', '2025-09-28 08:10:55', '2025-09-28 08:10:55'),
(18, 'Testing', '68e2fd9316244.png', 50, '<p>Di era digital saat ini, informasi dapat diakses dengan sangat cepat melalui berbagai platform. Perubahan gaya hidup masyarakat juga semakin dipengaruhi oleh teknologi yang terus berkembang. Mulai dari pendidikan, pekerjaan, hingga hiburan, hampir semuanya kini terhubung dengan internet dan perangkat pintar. Kondisi ini membuat manusia semakin bergantung pada inovasi yang diciptakan untuk mempermudah aktivitas sehari-hari.</p>\n<p>Namun, di balik kemudahan tersebut, tantangan baru juga ikut muncul. Keamanan data pribadi, kesehatan mental akibat penggunaan berlebihan, serta ketergantungan terhadap teknologi menjadi isu yang sering dibahas. Banyak orang tidak sadar bahwa terlalu lama berinteraksi dengan layar dapat berdampak pada kualitas hidup, baik dari segi fisik maupun psikologis. Karena itu, kesadaran masyarakat dalam mengatur keseimbangan menjadi hal yang penting.</p>\n<p>Melihat fenomena tersebut, perlu ada langkah bijak dalam memanfaatkan teknologi. Penggunaan yang terarah dapat membantu meningkatkan produktivitas, sementara pemakaian berlebihan justru bisa membawa masalah baru. Solusinya adalah menerapkan prinsip moderasi, memilih konten yang bermanfaat, serta selalu memperhatikan aspek keamanan digital. Dengan begitu, perkembangan teknologi bisa tetap memberikan dampak positif tanpa mengorbankan kesejahteraan individu maupun masyarakat.</p>\n', 11, '2025-10-06', 'Edukasi', 'published', '2025-10-05 23:21:55', '2025-11-17 11:11:55'),
(19, 'dssf', '68e2fd9e47b1e.png', 50, '<p>dafs</p>', 2, '2025-10-06', 'Edukasi', 'published', '2025-10-05 23:22:06', '2025-11-17 10:35:52'),
(20, 'sd', '68e2fda729e64.png', 50, '<p>dfsf</p>', 1, '2025-10-06', 'Kesehatan', 'published', '2025-10-05 23:22:15', '2025-10-05 23:34:42'),
(21, 'ddafa', '68e2fdc32ff41.png', 50, '<p>adadfd</p>', 0, '2025-10-06', 'Politik', 'published', '2025-10-05 23:22:43', '2025-10-05 23:22:43'),
(22, 'Testing', '68e2fe9f08026.png', 50, '<p>ssa</p>', 0, '2025-10-06', 'Teknologi', 'published', '2025-10-05 23:26:23', '2025-10-05 23:26:23'),
(23, 'sad', '68e2fea767607.png', 50, '<p>saDA</p>', 2, '2025-10-06', 'Teknologi', 'published', '2025-10-05 23:26:31', '2025-10-05 23:35:06'),
(24, 'SDASD', '68e2feb24a4b5.png', 50, '<p>SDada</p>', 1, '2025-10-06', 'Teknologi', 'published', '2025-10-05 23:26:42', '2025-11-17 11:12:27'),
(25, 'sD', '68e2fec32956d.png', 50, '<p>Sd</p>', 0, '2025-10-06', 'Teknologi', 'published', '2025-10-05 23:26:59', '2025-10-05 23:26:59'),
(26, 'sD', '68e301e3ca779.png', 50, '<p>Sd</p>\n', 0, '2025-10-06', 'Teknologi', 'published', '2025-10-05 23:40:19', '2025-10-05 23:40:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `events`
--

CREATE TABLE `events` (
  `id_event` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `crop_y` int(3) DEFAULT 50,
  `kategori` varchar(50) DEFAULT 'Event',
  `pelihat` int(11) DEFAULT 0,
  `tanggal` date NOT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `events`
--

INSERT INTO `events` (`id_event`, `judul`, `isi`, `gambar`, `crop_y`, `kategori`, `pelihat`, `tanggal`, `status`, `created_at`, `updated_at`) VALUES
(2, 'asdA', '<p>dwffwefdwed</p>', '68d8f88a7f084.jpg', 98, 'Update', 3, '2025-09-28', 'published', '2025-09-28 08:57:46', '2025-10-06 00:29:22'),
(3, 'Testing', '<p>sgef</p>', '68d8fa1473788.jpg', 12, 'Update', 2, '2025-09-28', 'published', '2025-09-28 09:04:20', '2025-10-06 00:40:06'),
(4, 'sdfd', '<p>gfhfdghg</p>', '68d8fa3764828.jpg', 0, 'Event', 1, '2025-09-28', 'published', '2025-09-28 09:04:55', '2025-10-06 00:30:12'),
(5, 'asd', '<p>asdaW</p>', '68d937ffe9463.jpg', 9, 'Lainnya', 0, '2025-09-28', 'published', '2025-09-28 13:28:31', '2025-09-28 13:28:31'),
(6, 'afe', '<p>Melihat fenomena tersebut, perlu ada langkah bijak dalam memanfaatkan teknologi. Penggunaan yang terarah dapat membantu meningkatkan produktivitas, sementara pemakaian berlebihan justru bisa membawa masalah baru. Solusinya adalah menerapkan prinsip moderasi, memilih konten yang bermanfaat, serta selalu memperhatikan aspek keamanan digital. Dengan begitu, perkembangan teknologi bisa tetap memberikan dampak positif tanpa mengorbankan kesejahteraan individu maupun masyarakat.</p>\n<p>Namun, di balik kemudahan tersebut, tantangan baru juga ikut muncul. Keamanan data pribadi, kesehatan mental akibat penggunaan berlebihan, serta ketergantungan terhadap teknologi menjadi isu yang sering dibahas. Banyak orang tidak sadar bahwa terlalu lama berinteraksi dengan layar dapat berdampak pada kualitas hidup, baik dari segi fisik maupun psikologis. Karena itu, kesadaran masyarakat dalam mengatur keseimbangan menjadi hal yang penting.</p>\n<p>Namun, di balik kemudahan tersebut, tantangan baru juga ikut muncul. Keamanan data pribadi, kesehatan mental akibat penggunaan berlebihan, serta ketergantungan terhadap teknologi menjadi isu yang sering dibahas. Banyak orang tidak sadar bahwa terlalu lama berinteraksi dengan layar dapat berdampak pada kualitas hidup, baik dari segi fisik maupun psikologis. Karena itu, kesadaran masyarakat dalam mengatur keseimbangan menjadi hal yang penting.</p>\n', '68e30da766513.png', 50, 'Internal', 11, '2025-10-06', 'published', '2025-10-06 00:30:31', '2025-11-17 10:26:46'),
(7, 'Testing', '<p>sfse</p>', '68e30dafb5c11.png', 50, 'Internal', 1, '2025-10-06', 'published', '2025-10-06 00:30:39', '2025-10-06 00:39:58');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id_product` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `harga` decimal(10,0) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `crop_y` int(11) DEFAULT 50,
  `pelihat` int(11) DEFAULT 0,
  `rata_rating` decimal(2,1) DEFAULT 0.0,
  `total_ratings` int(11) DEFAULT 0,
  `kategori` varchar(50) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'published',
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id_product`, `nama`, `harga`, `deskripsi`, `gambar`, `crop_y`, `pelihat`, `rata_rating`, `total_ratings`, `kategori`, `status`, `tanggal`) VALUES
(5, 'Nastar', 150000, 'ini adalah nastar', 'prod_691aefdbdd452.jpg', 50, 0, 0.0, 0, 'Kue Kering', 'published', '2025-11-17 09:50:19'),
(6, 'Lemper', 20000, 'Ini adalah Lemper', 'prod_691af05d01bcb.jpg', 50, 4, 0.0, 0, 'Kue Basah', 'published', '2025-11-17 09:52:29'),
(7, 'Kue Viral', 200000, 'Viral', 'prod_691af09123850.jpg', 50, 0, 0.0, 0, 'Spesial', 'published', '2025-11-17 09:53:21'),
(8, 'Kue Legit', 90000, 'mantap', 'prod_691af0b733180.jpg', 50, 1, 0.0, 0, 'Lainnya', 'published', '2025-11-17 09:53:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `harga` double DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `ketersediaan_stok` enum('habis','tersedia') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ratings`
--

CREATE TABLE `ratings` (
  `id_rating` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `komentar` text DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rating_codes`
--

CREATE TABLE `rating_codes` (
  `id_code` int(11) NOT NULL,
  `code_hash` varchar(64) NOT NULL,
  `product_id` int(11) NOT NULL,
  `used_status` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('developer','staff','owner') NOT NULL DEFAULT 'staff',
  `profile_crop_y` int(11) DEFAULT 50,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `failed_attempts`, `locked_until`, `profile_image`, `role`, `profile_crop_y`, `updated_at`) VALUES
(1, 'Farel', '$2a$12$tk9CVC.oZhXoW1Hk8PFoju1rYIXodzi/DLBNAePctkh2U5Ev.P52W', 0, NULL, 'uploads/profiles/profile_1_1759121165.png', 'developer', 83, '2025-09-30 02:57:54'),
(2, 'admin', '$2a$12$Esm.MCKuIzz6JjsyE51wSOVZ5I7y0i7a/ks3L29YDVCk44eViu5Fm', 0, NULL, 'uploads/profiles/profile_2_1759072490.jpg', 'staff', 1, '2025-09-28 15:14:50');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `artikel`
--
ALTER TABLE `artikel`
  ADD PRIMARY KEY (`id_artikel`);

--
-- Indeks untuk tabel `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id_event`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id_product`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nama` (`nama`),
  ADD KEY `kategori_produk` (`kategori_id`);

--
-- Indeks untuk tabel `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id_rating`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `rating_codes`
--
ALTER TABLE `rating_codes`
  ADD PRIMARY KEY (`id_code`),
  ADD UNIQUE KEY `code_hash` (`code_hash`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `announcement`
--
ALTER TABLE `announcement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `artikel`
--
ALTER TABLE `artikel`
  MODIFY `id_artikel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `events`
--
ALTER TABLE `events`
  MODIFY `id_event` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id_rating` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `rating_codes`
--
ALTER TABLE `rating_codes`
  MODIFY `id_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `kategori_produk` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`);

--
-- Ketidakleluasaan untuk tabel `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rating_codes`
--
ALTER TABLE `rating_codes`
  ADD CONSTRAINT `rating_codes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
