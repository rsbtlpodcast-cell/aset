-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 15 Nov 2025 pada 04.09
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
-- Database: `siat`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `alat_tulis`
--

CREATE TABLE `alat_tulis` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kode` varchar(100) DEFAULT NULL,
  `kondisi` varchar(100) DEFAULT NULL,
  `tahun` year(4) DEFAULT NULL,
  `jumlah` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `aset`
--

CREATE TABLE `aset` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `merek` varchar(150) DEFAULT NULL,
  `no_registrasi` varchar(100) DEFAULT NULL,
  `no_seri` varchar(100) DEFAULT NULL,
  `tahun_pengadaan` year(4) DEFAULT NULL,
  `lokasi` varchar(150) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `kondisi` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT 1,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipe_aset` enum('aset_kesehatan','aset_non_kesehatan') NOT NULL DEFAULT 'aset_non_kesehatan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `aset_kesehatan`
--

CREATE TABLE `aset_kesehatan` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `merek` varchar(100) DEFAULT NULL,
  `no_registrasi` varchar(50) DEFAULT NULL,
  `tahun_pengadaan` year(4) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT 0,
  `foto_barang` varchar(255) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT 'Kesehatan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `aset_non_kesehatan`
--

CREATE TABLE `aset_non_kesehatan` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `merek` varchar(100) DEFAULT NULL,
  `no_registrasi` varchar(50) DEFAULT NULL,
  `tahun_pengadaan` year(4) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT 0,
  `foto_barang` varchar(255) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT 'Non Kesehatan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `atk_histori`
--

CREATE TABLE `atk_histori` (
  `id` int(11) NOT NULL,
  `id_atk` int(11) NOT NULL,
  `nama_atk` varchar(255) NOT NULL,
  `instalasi` varchar(255) NOT NULL,
  `pemohon` varchar(255) NOT NULL,
  `jumlah_permintaan` int(11) NOT NULL,
  `jumlah_realisasi` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kalibrasi_log`
--

CREATE TABLE `kalibrasi_log` (
  `id` int(11) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `tanggal_upload` date NOT NULL,
  `tanggal_kalibrasi_berikutnya` date NOT NULL,
  `status` enum('aktif','peringatan','kedaluwarsa') DEFAULT 'aktif',
  `notifikasi_terkirim` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kalibrasi_log`
--

INSERT INTO `kalibrasi_log` (`id`, `nama_file`, `tanggal_upload`, `tanggal_kalibrasi_berikutnya`, `status`, `notifikasi_terkirim`) VALUES
(6, 'buku', '2025-11-12', '2026-11-12', 'aktif', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `merek` varchar(255) DEFAULT NULL,
  `no_registrasi` varchar(50) DEFAULT NULL,
  `no_seri` varchar(50) DEFAULT NULL,
  `tahun_pengadaan` year(4) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT 0,
  `kategori` varchar(50) NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'inggi', '$2y$10$EwqIDxgNXQfyXDnTX.NJsOTdm7IG5sn5wup7YTt94dT5rrz58.4CC', 'user', '2025-10-20 01:58:56');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `alat_tulis`
--
ALTER TABLE `alat_tulis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `aset`
--
ALTER TABLE `aset`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `aset_kesehatan`
--
ALTER TABLE `aset_kesehatan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `aset_non_kesehatan`
--
ALTER TABLE `aset_non_kesehatan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `atk_histori`
--
ALTER TABLE `atk_histori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kalibrasi_log`
--
ALTER TABLE `kalibrasi_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `alat_tulis`
--
ALTER TABLE `alat_tulis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `aset`
--
ALTER TABLE `aset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `aset_kesehatan`
--
ALTER TABLE `aset_kesehatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `aset_non_kesehatan`
--
ALTER TABLE `aset_non_kesehatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `atk_histori`
--
ALTER TABLE `atk_histori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `kalibrasi_log`
--
ALTER TABLE `kalibrasi_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
