-- Database: puyuh_dst
-- Struktur dengan relasi yang baik dan efisien

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+07:00";

-- --------------------------------------------------------
-- Drop tables if exist (untuk reset)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `basis_pengetahuan`;
DROP TABLE IF EXISTS `gejala`;
DROP TABLE IF EXISTS `penyakit`;
DROP TABLE IF EXISTS `admin_user`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------
-- Table: users (Unified Admin & User)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Default Admin (Password: admin123)
-- Catatan: Password tersimpan sebagai PLAIN TEXT (admin123) untuk kemudahan development awal.
-- Logic Login nanti akan handle ini.
INSERT INTO `users` (`nama_lengkap`, `username`, `password`, `role`) VALUES
('Administrator', 'admin', 'admin123', 'admin');

-- --------------------------------------------------------
-- Table: penyakit
-- --------------------------------------------------------
CREATE TABLE `penyakit` (
  `id_penyakit` varchar(10) NOT NULL,
  `nama_penyakit` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `saran_penanganan` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_penyakit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `penyakit` (`id_penyakit`, `nama_penyakit`, `deskripsi`, `saran_penanganan`, `status`) VALUES
('P01', 'Pullorum (Berak Kapur)', 
  'Penyakit bakterial yang disebabkan oleh Salmonella pullorum. Infeksi ini unik karena dapat menular secara vertikal (dari induk ke telur) maupun kontak langsung. Gejala khas pada puyuh muda meliputi kotoran berwarna putih pekat seperti kapur yang lengket di area dubur (pasting), puyuh terlihat lesu, mata tertutup, dan sering bergerombol di dekat pemanas untuk mencari kehangatan.',
  'Segera isolasi puyuh yang terinfeksi. Berikan antibiotik golongan Sulfonamide kepada populasi yang tersisa sesuai dosis anjuran. Serta lakukan desinfeksi kandang secara total sebelum memulai siklus pemeliharaan baru.',
  'aktif'),
('P02', 'Coryza (Snot)', 
  'Infeksi saluran pernapasan akut yang disebabkan oleh bakteri Avibacterium paragallinarum. Pada puyuh, gejala klinis yang muncul meliputi pembengkakan pada area wajah atau sinus (infra-orbital), keluarnya lendir kental dari hidung atau mata, bersin-bersin, serta penurunan nafsu makan dan produksi telur yang signifikan.',
  'Segera pisah puyuh sakit dari yang sehat, bersihkan kandang & tempat minum/pakan tiap hari, pastikan kandang tidak lembab dan ventilasi cukup lakukan pemisahan segera pada puyuh yang menunjukkan gejala klinis dan berikan antibiotik yang tepat, seperti Erythromycin atau Tetracycline, melalui air minum. Bersihkan lendir pada hidung puyuh untuk membantu pernapasan.',
  'aktif'),
('P03', 'Coccidiosis (Berak Darah)', 
  'Penyakit parasit yang menyerang saluran cerna akibat protozoa Eimeria sp. Gejala utamanya adalah diare yang bercampur darah atau berwarna gelap, bulu terlihat kusam/berdiri, dan pertumbuhan terhambat. Penyakit ini sangat dipicu oleh kondisi alas kandang (litter) yang basah atau lembab, yang menjadi tempat berkembang biak parasit.',
  'Tangani penyakit dengan memberikan obat anti-koksidia (koksidiostat) atau golongan Sulfa melalui air minum, serta tambahkan vitamin A dan K untuk mempercepat pemulihan saluran cerna. Kunci pencegahan utama adalah menjaga kondisi litter (alas kandang) agar tetap kering dan bersih, segera ganti litter yang basah karena kelembapan adalah media utama berkembang biaknya protozoa penyebab penyakit ini.',
  'aktif'),
('P04', 'Newcastle Disease (ND / Tetelo)', 
  'Penyakit virus yang sangat menular dan seringkali fatal. Gejala spesifik pada puyuh meliputi gangguan saraf seperti leher terpuntir (torticollis), berjalan memutar, atau kelumpuhan kaki/sayap. Selain itu, sering disertai gangguan pernapasan dan penurunan kualitas serta jumlah produksi telur secara mendadak.',
  'Berikan multivitamin pada puyuh yang masih sehat untuk meningkatkan daya tahan tubuh. Sebagai pencegahan wajib, lakukan vaksinasi ND secara rutin sesuai jadwal dan perketat biosecurity untuk mencegah virus masuk dari luar.',
  'aktif'),
('P05', 'Radang Usus (Quail Enteritis / Ulcerative Enteritis)', 
  'Sering disebut sebagai Quail Disease, penyakit ini disebabkan oleh bakteri Clostridium colinum. Karakteristik utamanya adalah kematian mendadak pada puyuh yang tampak gemuk/sehat. Jika dilakukan bedah bangkai, akan terlihat bercak luka seperti kancing (button ulcers) pada usus/secum. Faktor pemicu utamanya adalah stres dan kepadatan kandang yang tinggi.',
  'Pisahkan puyuh yang terlihat kurus atau memiliki kotoran abnormal, lalu berikan antibiotik spektrum luas (seperti Penicillin atau Bacitracin) melalui pakan atau air minum. Pastikan pakan yang diberikan selalu segar (tidak berjamur) dan air minum selalu bersih. Lakukan pembersihan kotoran di bawah kandang secara rutin untuk mencegah penumpukan gas.',
  'aktif'),
('P06', 'Cacingan (Helminthiasis)', 
  'Infestasi cacing parasit (Nematoda, Cestoda, atau Trematoda) yang menyerap nutrisi dalam saluran cerna. Puyuh akan mengalami penurunan bobot badan meskipun nafsu makan normal, efisiensi pakan memburuk, bulu kusam, dan produksi telur menurun. Sering terjadi pada kandang dengan sanitasi yang kurang baik.',
  'Berikan obat cacing (anthelmintic) spektrum luas kepada seluruh populasi dan ulangi pemberiannya 1-2 minggu kemudian untuk memutus siklus hidup cacing. Setelah pengobatan, berikan suplemen nutrisi untuk membantu mengembalikan bobot badan puyuh. Lakukan pemberian obat cacing rutin setiap 2-3 bulan sekali.',
  'aktif');

-- --------------------------------------------------------
-- Table: gejala
-- --------------------------------------------------------
CREATE TABLE `gejala` (
  `id_gejala` varchar(10) NOT NULL,
  `nama_gejala` varchar(255) NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_gejala`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `gejala` (`id_gejala`, `nama_gejala`, `status`) VALUES
('G01', 'Tinja terdapat darah', 'aktif'),
('G02', 'Tinja berwarna putih', 'aktif'),
('G03', 'Tinja berwarna hijau', 'aktif'),
('G04', 'Nafsu makan menurun', 'aktif'),
('G05', 'Berat badan menurun atau tampak kurus', 'aktif'),
('G06', 'Terjadi kematian mendadak', 'aktif'),
('G07', 'Burung tampak lesu atau menyendiri', 'aktif'),
('G08', 'Bulu berdiri dan tampak kusam', 'aktif'),
('G09', 'Produksi telur menurun', 'aktif'),
('G10', 'Burung batuk atau bersin', 'aktif'),
('G11', 'Napas berbunyi seperti ngorok', 'aktif'),
('G12', 'Sulit bernapas atau megap-megap', 'aktif'),
('G13', 'Keluar lendir dari hidung', 'aktif'),
('G14', 'Mata bengkak dan berair', 'aktif'),
('G15', 'Kepala berputar atau menengok ke belakang', 'aktif'),
('G16', 'Tidak mampu berdiri atau mengalami kelumpuhan', 'aktif'),
('G17', 'Burung mengalami tremor atau gemetar', 'aktif'),
('G18', 'Mulut berbau busuk', 'aktif'),
('G19', 'Tinja berbau sangat busuk', 'aktif'),
('G20', 'Pertumbuhan tubuh lambat', 'aktif'),
('G21', 'Sayap terkulai', 'aktif'),
('G22', 'Kelopak mata lengket sehingga mata menutup', 'aktif'),
('G23', 'Bau kandang sangat menyengat', 'aktif'),
('G24', 'Tinja berlendir', 'aktif'),
('G25', 'Terdapat cacing pada tinja', 'aktif'),
('G26', 'Tinja menempel di dubur', 'aktif'),
('G27', 'Tinja berbuih atau berbusa', 'aktif');

-- --------------------------------------------------------
-- Table: basis_pengetahuan (Tabel Pivot/Relasi)
-- Relasi Many-to-Many antara Gejala dan Penyakit
-- --------------------------------------------------------
CREATE TABLE `basis_pengetahuan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_gejala` varchar(10) NOT NULL,
  `kode_penyakit` varchar(10) NOT NULL,
  `mb` float NOT NULL COMMENT 'Nilai Belief (0-1)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rule` (`kode_gejala`, `kode_penyakit`),
  KEY `fk_gejala` (`kode_gejala`),
  KEY `fk_penyakit` (`kode_penyakit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data basis pengetahuan (1 baris = 1 relasi gejala-penyakit)
INSERT INTO `basis_pengetahuan` (`kode_gejala`, `kode_penyakit`, `mb`) VALUES
-- G01
('G01', 'P03', 0.90),
-- G02
('G02', 'P01', 0.90),
-- G03
('G03', 'P04', 0.85),
('G03', 'P05', 0.85),
-- G04
('G04', 'P01', 0.50),
('G04', 'P02', 0.50),
('G04', 'P03', 0.50),
('G04', 'P04', 0.50),
('G04', 'P05', 0.50),
-- G05
('G05', 'P01', 0.45),
('G05', 'P02', 0.45),
('G05', 'P03', 0.45),
('G05', 'P04', 0.45),
('G05', 'P05', 0.45),
('G05', 'P06', 0.45),
-- G06
('G06', 'P01', 0.70),
('G06', 'P03', 0.70),
('G06', 'P04', 0.70),
('G06', 'P05', 0.70),
-- G07
('G07', 'P01', 0.45),
('G07', 'P02', 0.45),
('G07', 'P03', 0.45),
('G07', 'P04', 0.45),
('G07', 'P05', 0.45),
-- G08
('G08', 'P01', 0.45),
('G08', 'P02', 0.45),
('G08', 'P03', 0.45),
('G08', 'P04', 0.45),
('G08', 'P05', 0.45),
('G08', 'P06', 0.45),
-- G09
('G09', 'P01', 0.45),
('G09', 'P02', 0.45),
('G09', 'P03', 0.45),
('G09', 'P04', 0.45),
('G09', 'P05', 0.45),
('G09', 'P06', 0.45),
-- G10
('G10', 'P02', 0.75),
('G10', 'P04', 0.75),
-- G11
('G11', 'P02', 0.80),
('G11', 'P04', 0.80),
-- G12
('G12', 'P02', 0.80),
('G12', 'P04', 0.80),
-- G13
('G13', 'P02', 0.90),
-- G14
('G14', 'P02', 0.90),
-- G15
('G15', 'P04', 0.90),
-- G16
('G16', 'P04', 0.85),
-- G17
('G17', 'P04', 0.85),
-- G18
('G18', 'P02', 0.85),
-- G19
('G19', 'P05', 0.85),
-- G20
('G20', 'P01', 0.45),
('G20', 'P02', 0.45),
('G20', 'P03', 0.45),
('G20', 'P04', 0.45),
('G20', 'P05', 0.45),
('G20', 'P06', 0.45),
-- G21
('G21', 'P01', 0.75),
('G21', 'P03', 0.75),
('G21', 'P05', 0.75),
-- G22
('G22', 'P02', 0.85),
-- G23
('G23', 'P05', 0.85),
-- G24
('G24', 'P01', 0.70),
('G24', 'P03', 0.70),
('G24', 'P05', 0.70),
('G24', 'P06', 0.70),
-- G25
('G25', 'P06', 0.90),
-- G26
('G26', 'P01', 0.90),
-- G27
('G27', 'P03', 0.85),
('G27', 'P05', 0.85);

-- --------------------------------------------------------
-- Foreign Key Constraints
-- --------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE `basis_pengetahuan`
  ADD CONSTRAINT `fk_bp_gejala` FOREIGN KEY (`kode_gejala`) REFERENCES `gejala` (`id_gejala`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bp_penyakit` FOREIGN KEY (`kode_penyakit`) REFERENCES `penyakit` (`id_penyakit`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
