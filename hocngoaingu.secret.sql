-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2025 at 11:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hocngoaingu`
--

-- --------------------------------------------------------

--
-- Table structure for table `baihoc`
--

CREATE TABLE `baihoc` (
  `MaBaiHoc` int(11) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL,
  `TenBaiHoc` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `TrangThaiBaiHoc` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTaoBaiHoc` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `baihoc`
--

INSERT INTO `baihoc` (`MaBaiHoc`, `MaKhoaHoc`, `TenBaiHoc`, `TrangThaiBaiHoc`, `ThoiGianTaoBaiHoc`) VALUES
(1, 1, 'Traffic ', 1, '2025-08-25 14:45:00'),
(1, 2, 'Get Started', 1, '2025-08-25 14:45:00'),
(2, 1, 'Food ', 1, '2025-08-25 14:45:00'),
(2, 2, 'Survival Kit', 1, '2025-08-25 14:45:00'),
(3, 1, 'Education ', 1, '2025-08-25 14:45:00'),
(3, 2, 'Vocab Booster: Easy as 1, 2, 3!', 1, '2025-08-25 14:45:00'),
(4, 1, 'Family', 1, '2025-08-25 14:45:00'),
(4, 2, 'Small Talk', 1, '2025-08-25 14:45:00'),
(5, 1, 'Work', 1, '2025-08-25 14:45:00'),
(5, 2, 'Vocab Booster: Who Are You?', 1, '2025-08-25 14:45:00'),
(6, 1, 'Hobbie', 1, '2025-08-25 14:45:00'),
(6, 2, 'Each To Their Own', 1, '2025-08-25 14:45:00'),
(7, 1, 'Technology ', 1, '2025-08-25 14:45:00'),
(7, 2, 'Vocab Booster: Day In, Day Out', 1, '2025-08-25 14:45:00'),
(8, 1, 'Activities ', 1, '2025-08-25 14:45:00'),
(8, 2, 'Food For Thought', 1, '2025-08-25 14:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `boquatuvung`
--

CREATE TABLE `boquatuvung` (
  `TaiKhoan` varchar(100) NOT NULL,
  `MaTuVung` int(11) NOT NULL,
  `MaBaiHoc` int(11) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_room`
--

CREATE TABLE `chatbot_room` (
  `MaRoom` int(11) NOT NULL,
  `TaiKhoan` varchar(100) NOT NULL,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_room`
--

INSERT INTO `chatbot_room` (`MaRoom`, `TaiKhoan`, `ThoiGian`) VALUES
(12, 'admin', '2025-08-23 18:45:26');

-- --------------------------------------------------------

--
-- Table structure for table `dangkykhoahoc`
--

CREATE TABLE `dangkykhoahoc` (
  `TaiKhoan` varchar(100) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dangkykhoahoc`
--

INSERT INTO `dangkykhoahoc` (`TaiKhoan`, `MaKhoaHoc`, `ThoiGian`) VALUES
('0411nguyentiendung503462', 1, '2025-08-25 13:00:00'),
('1388duongtuankhang806721', 1, '2025-09-15 11:55:15'),
('admin', 1, '2025-08-25 13:00:00'),
('dung1234', 1, '2025-08-25 13:00:00'),
('dungnguyentien853712', 1, '2025-09-15 16:58:31'),
('khang', 1, '2025-08-25 13:00:00'),
('khangdz', 1, '2025-08-25 13:00:00'),
('khangtuan657132', 1, '2025-09-15 17:00:30');

-- --------------------------------------------------------

--
-- Table structure for table `danhgiakhoahoc`
--

CREATE TABLE `danhgiakhoahoc` (
  `TaiKhoan` varchar(100) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL,
  `NoiDungDanhGia` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Rating` int(11) NOT NULL DEFAULT 0,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `danhgiakhoahoc`
--

INSERT INTO `danhgiakhoahoc` (`TaiKhoan`, `MaKhoaHoc`, `NoiDungDanhGia`, `Rating`, `ThoiGian`) VALUES
('0411nguyentiendung503462', 1, 'Mình rất thích khóa học này.', 3, '2025-09-18 00:14:13'),
('1388duongtuankhang806721', 1, 'Khóa học này rất hữu ích.', 2, '2025-09-18 00:14:56'),
('khangdz', 1, 'Hay lắm', 1, '2025-08-25 15:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `grammar_answers`
--

CREATE TABLE `grammar_answers` (
  `MaTraLoi` int(11) NOT NULL,
  `MaKetQua` int(11) NOT NULL,
  `TaiKhoan` varchar(100) NOT NULL,
  `MaCauHoi` int(11) NOT NULL,
  `DapAnChon` enum('A','B','C','D') NOT NULL,
  `DungSai` tinyint(1) NOT NULL DEFAULT 0,
  `ThoiGianLam` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grammar_answers`
--

INSERT INTO `grammar_answers` (`MaTraLoi`, `MaKetQua`, `TaiKhoan`, `MaCauHoi`, `DapAnChon`, `DungSai`, `ThoiGianLam`) VALUES
(4, 1, 'admin', 16, 'A', 1, '2025-08-23 19:27:02'),
(5, 2, 'admin', 16, 'A', 1, '2025-08-23 19:27:15'),
(6, 3, 'admin', 16, 'A', 1, '2025-08-23 19:27:25'),
(7, 4, 'admin', 16, 'A', 1, '2025-08-23 20:21:45'),
(8, 5, 'admin', 16, 'A', 1, '2025-08-23 23:32:44'),
(9, 6, 'admin', 16, 'A', 1, '2025-08-25 02:01:46'),
(10, 7, 'admin', 16, 'A', 1, '2025-09-15 11:29:28'),
(11, 8, 'admin', 17, 'A', 1, '2025-09-15 11:36:41'),
(12, 9, 'admin', 17, 'A', 1, '2025-09-15 16:49:44');

-- --------------------------------------------------------

--
-- Table structure for table `grammar_questions`
--

CREATE TABLE `grammar_questions` (
  `MaCauHoi` int(11) NOT NULL,
  `MaChuDe` int(11) NOT NULL,
  `CauHoi` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `DapAnA` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `DapAnB` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `DapAnC` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `DapAnD` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `DapAnDung` enum('A','B','C','D') NOT NULL,
  `GiaiThich` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ThuTu` int(11) NOT NULL DEFAULT 0,
  `TrangThai` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grammar_questions`
--

INSERT INTO `grammar_questions` (`MaCauHoi`, `MaChuDe`, `CauHoi`, `DapAnA`, `DapAnB`, `DapAnC`, `DapAnD`, `DapAnDung`, `GiaiThich`, `ThuTu`, `TrangThai`, `ThoiGianTao`) VALUES
(1, 1, 'She _____ to school every day.', 'go', 'goes', 'going', 'gone', 'B', 'Với chủ ngữ số ít (She), động từ phải thêm -s/-es trong thì hiện tại đơn.', 1, 1, '2025-08-23 11:42:10'),
(2, 1, 'They _____ football on weekends.', 'plays', 'playing', 'play', 'played', 'C', 'Với chủ ngữ số nhiều (They), động từ giữ nguyên dạng gốc.', 2, 1, '2025-08-23 11:42:10'),
(3, 1, 'He usually _____ coffee in the morning.', 'drink', 'drinks', 'drinking', 'drank', 'B', 'Với chủ ngữ số ít (He), động từ \"drink\" phải thêm -s thành \"drinks\".', 3, 1, '2025-08-23 11:42:10'),
(4, 1, 'I _____ to work by bus.', 'go', 'goes', 'going', 'went', 'A', 'Với chủ ngữ \"I\", động từ giữ nguyên dạng gốc.', 4, 1, '2025-08-23 11:42:10'),
(5, 1, 'The sun _____ in the east.', 'rise', 'rises', 'rising', 'rose', 'B', 'Đây là sự thật hiển nhiên, dùng thì hiện tại đơn với chủ ngữ số ít.', 5, 1, '2025-08-23 11:42:10'),
(6, 2, 'Yesterday, I _____ to the market.', 'go', 'goes', 'went', 'going', 'C', 'Thì quá khứ đơn của \"go\" là \"went\".', 1, 1, '2025-08-23 11:42:10'),
(7, 2, 'She _____ her homework last night.', 'finish', 'finished', 'finishing', 'finishes', 'B', 'Động từ có quy tắc thêm -ed để tạo thành thì quá khứ đơn.', 2, 1, '2025-08-23 11:42:10'),
(8, 2, 'We _____ at the restaurant yesterday.', 'eat', 'ate', 'eating', 'eaten', 'B', 'Thì quá khứ đơn của động từ bất quy tắc \"eat\" là \"ate\".', 3, 1, '2025-08-23 11:42:10'),
(9, 2, 'Did you _____ the movie last week?', 'see', 'saw', 'seen', 'seeing', 'A', 'Sau \"Did\", động từ phải ở dạng nguyên thể.', 4, 1, '2025-08-23 11:42:10'),
(10, 2, 'They _____ very happy about the news.', 'are', 'was', 'were', 'be', 'C', 'Với chủ ngữ số nhiều \"They\", dùng \"were\" trong quá khứ.', 5, 1, '2025-08-23 11:42:10'),
(11, 3, 'She wants to be _____ doctor.', 'a', 'an', 'the', 'no article', 'A', 'Dùng \"a\" trước từ bắt đầu bằng phụ âm.', 1, 1, '2025-08-23 11:42:10'),
(12, 3, 'I saw _____ elephant at the zoo.', 'a', 'an', 'the', 'no article', 'B', 'Dùng \"an\" trước từ bắt đầu bằng nguyên âm.', 2, 1, '2025-08-23 11:42:10'),
(13, 3, '_____ moon is beautiful tonight.', 'A', 'An', 'The', 'No article', 'C', 'Dùng \"the\" với các danh từ duy nhất như moon, sun.', 3, 1, '2025-08-23 11:42:10'),
(14, 3, 'He is _____ best student in class.', 'a', 'an', 'the', 'no article', 'C', 'Dùng \"the\" với tính từ so sánh nhất.', 4, 1, '2025-08-23 11:42:10'),
(15, 3, 'I love _____ music.', 'a', 'an', 'the', 'no article', 'D', 'Không dùng mạo từ với danh từ không đếm được khi nói chung.', 5, 1, '2025-08-23 11:42:10'),
(16, 12, 'abcdsdasd', 'a', 'b', 'c', 'd', 'A', 'asdasd', 0, 1, '2025-08-23 19:23:21'),
(17, 13, 'A', 'A', 'B', 'C', 'D', 'A', 'VI A', 0, 1, '2025-09-15 11:34:31');

-- --------------------------------------------------------

--
-- Table structure for table `grammar_results`
--

CREATE TABLE `grammar_results` (
  `MaKetQua` int(11) NOT NULL,
  `TaiKhoan` varchar(100) NOT NULL,
  `MaChuDe` int(11) NOT NULL,
  `TongSoCau` int(11) NOT NULL,
  `SoCauDung` int(11) NOT NULL,
  `DiemSo` decimal(5,2) NOT NULL,
  `ThoiGianLam` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grammar_results`
--

INSERT INTO `grammar_results` (`MaKetQua`, `TaiKhoan`, `MaChuDe`, `TongSoCau`, `SoCauDung`, `DiemSo`, `ThoiGianLam`) VALUES
(1, 'admin', 12, 1, 1, 100.00, '2025-08-23 19:27:02'),
(2, 'admin', 12, 1, 1, 100.00, '2025-08-23 19:27:15'),
(3, 'admin', 12, 1, 1, 100.00, '2025-08-23 19:27:25'),
(4, 'admin', 12, 1, 1, 100.00, '2025-08-23 20:21:45'),
(5, 'admin', 12, 1, 1, 100.00, '2025-08-23 23:32:44'),
(6, 'admin', 12, 1, 1, 100.00, '2025-08-25 02:01:46'),
(7, 'admin', 12, 1, 1, 100.00, '2025-09-15 11:29:28'),
(8, 'admin', 13, 1, 1, 100.00, '2025-09-15 11:36:41'),
(9, 'admin', 13, 1, 1, 100.00, '2025-09-15 16:49:44');

-- --------------------------------------------------------

--
-- Table structure for table `grammar_topics`
--

CREATE TABLE `grammar_topics` (
  `MaChuDe` int(11) NOT NULL,
  `MaKhoaHoc` int(11) DEFAULT NULL,
  `MaBaiHoc` int(11) DEFAULT NULL,
  `TenChuDe` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `TenChuDeEng` varchar(100) NOT NULL,
  `MoTa` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `CapDo` enum('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner',
  `ThuTu` int(11) NOT NULL DEFAULT 0,
  `TrangThai` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grammar_topics`
--

INSERT INTO `grammar_topics` (`MaChuDe`, `MaKhoaHoc`, `MaBaiHoc`, `TenChuDe`, `TenChuDeEng`, `MoTa`, `CapDo`, `ThuTu`, `TrangThai`, `ThoiGianTao`) VALUES
(1, NULL, NULL, 'Thì hiện tại đơn', 'Present Simple', 'Thì hiện tại đơn dùng để diễn tả hành động thường xuyên, sự thật hiển nhiên', 'Beginner', 1, 0, '2025-08-23 11:42:10'),
(2, NULL, NULL, 'Thì quá khứ đơn', 'Past Simple', 'Thì quá khứ đơn dùng để diễn tả hành động đã xảy ra và kết thúc trong quá khứ', 'Beginner', 2, 0, '2025-08-23 11:42:10'),
(3, NULL, NULL, 'Mạo từ', 'Articles', 'Cách sử dụng a, an, the trong tiếng Anh', 'Beginner', 3, 0, '2025-08-23 11:42:10'),
(4, NULL, NULL, 'Thì hiện tại hoàn thành', 'Present Perfect', 'Thì hiện tại hoàn thành diễn tả hành động bắt đầu trong quá khứ, kéo dài đến hiện tại', 'Intermediate', 4, 0, '2025-08-23 11:42:10'),
(5, NULL, NULL, 'Động từ khuyết thiếu', 'Modal Verbs', 'Can, could, should, must, might, may...', 'Intermediate', 5, 0, '2025-08-23 11:42:10'),
(6, NULL, NULL, 'Câu bị động', 'Passive Voice', 'Cách chuyển từ câu chủ động sang câu bị động', 'Intermediate', 6, 0, '2025-08-23 11:42:10'),
(7, NULL, NULL, 'Câu điều kiện', 'Conditionals', 'Câu điều kiện loại 1, 2, 3 và cách sử dụng', 'Advanced', 7, 0, '2025-08-23 11:42:10'),
(8, NULL, NULL, 'Câu tường thuật', 'Reported Speech', 'Cách chuyển từ câu trực tiếp sang câu gián tiếp', 'Advanced', 8, 0, '2025-08-23 11:42:10'),
(9, NULL, NULL, 'Liên từ', 'Conjunctions', '', 'Beginner', 5, 0, '2025-08-23 19:02:35'),
(10, 1, 1, 'Câu bị động', 'Passive Voice', '', 'Beginner', 5, 0, '2025-08-23 19:16:24'),
(11, 1, 1, 'Thì hiện tại đơn', 'Present Simple', '', 'Beginner', 5, 0, '2025-08-23 19:16:48'),
(12, 1, 1, 'Giới từ', 'Prepositions', '', 'Advanced', 1, 1, '2025-08-23 19:19:30'),
(13, 1, 8, 'Động từ khuyết thiếu', 'Modal Verbs', '', 'Beginner', 0, 1, '2025-09-15 11:34:10'),
(14, 1, 5, 'Câu điều kiện', 'Conditional Sentences', 'a', 'Beginner', 3, 1, '2025-09-15 16:50:32');

-- --------------------------------------------------------

--
-- Table structure for table `hethong`
--

CREATE TABLE `hethong` (
  `ID` int(11) NOT NULL,
  `TenWeb` text NOT NULL,
  `Email` text NOT NULL,
  `PassEmail` text NOT NULL,
  `DefaultAvatar` text NOT NULL,
  `MoTa` text NOT NULL,
  `TuKhoa` text NOT NULL,
  `Thumbnail` text NOT NULL,
  `Author` text NOT NULL,
  `BaoTri` text NOT NULL,
  `NoiDungBaoTri` text NOT NULL,
  `GOOGLE_APP_ID` text DEFAULT NULL,
  `GOOGLE_APP_SECRET` text DEFAULT NULL,
  `GOOGLE_APP_CALLBACK_URL` text DEFAULT NULL,
  `FACEBOOK_APP_ID` text DEFAULT NULL,
  `FACEBOOK_APP_SECRET` text DEFAULT NULL,
  `FACEBOOK_APP_CALLBACK_URL` text DEFAULT NULL,
  `OPENAI_API_KEY` text DEFAULT NULL,
  `BASE_URL` text DEFAULT NULL,
  `LinkIcon` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hethong`
--

INSERT INTO `hethong` (
    `ID`, `TenWeb`, `Email`, `PassEmail`, `DefaultAvatar`, `MoTa`, 
    `TuKhoa`, `Thumbnail`, `Author`, `BaoTri`, `NoiDungBaoTri`, 
    `GOOGLE_APP_ID`, `GOOGLE_APP_SECRET`, `GOOGLE_APP_CALLBACK_URL`, 
    `FACEBOOK_APP_ID`, `FACEBOOK_APP_SECRET`, `FACEBOOK_APP_CALLBACK_URL`, 
    `OPENAI_API_KEY`, `BASE_URL`, `LinkIcon`
) VALUES (
    1, 
    'KD English', 
    'your-email@example.com', 
    'YOUR_EMAIL_PASSWORD', 
    'https://i.imgur.com/2U2xyPR.png', 
    'KD English - Nền tảng học ngoại ngữ online', 
    'KD English, hoc ngoai ngu, hoc tieng anh, hoc tieng nhat, tieng anh, tieng nhat', 
    'https://i.imgur.com/0zwVcsy.png', 
    'Dương Tuấn Khang, Nguyễn Tiến Dũng', 
    'OFF', 
    'Website bảo trì hệ thống. Vui lòng truy cập sau.', 
    'YOUR_GOOGLE_APP_ID', 
    'YOUR_GOOGLE_APP_SECRET', 
    'http://localhost/webhocngoaingu/public/callback/google_callback.php', 
    'YOUR_FACEBOOK_APP_ID', 
    'YOUR_FACEBOOK_APP_SECRET', 
    'http://localhost/webhocngoaingu/public/callback/facebook_callback.php', 
    'YOUR_OPENAI_API_KEY', 
    'http://localhost/webhocngoaingu', 
    'https://i.imgur.com/TgJcJp9.png'
);


-- --------------------------------------------------------

--
-- Table structure for table `hoatdong`
--

CREATE TABLE `hoatdong` (
  `MaHoatDong` int(11) NOT NULL,
  `TenHoatDong` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `NoiDung` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp(),
  `TaiKhoan` varchar(100) NOT NULL,
  `MaLoaiHoatDong` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoatdong`
--

INSERT INTO `hoatdong` (`MaHoatDong`, `TenHoatDong`, `NoiDung`, `ThoiGian`, `TaiKhoan`, `MaLoaiHoatDong`) VALUES
(1116, 'Học từ vựng', 'Học từ vựng mới \"car\" thuộc bài học \"Traffic - Giao thông\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1117, 'Học từ vựng', 'Học từ vựng mới \"bus\" thuộc bài học \"Traffic - Giao thông\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1118, 'Học từ vựng', 'Học từ vựng mới \"traffic\" thuộc bài học \"Traffic - Giao thông\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1119, 'Học từ vựng', 'Học từ vựng mới \"road\" thuộc bài học \"Traffic - Giao thông\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1120, 'Học từ vựng', 'Học từ vựng mới \"bicycle\" thuộc bài học \"Traffic - Giao thông\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1121, 'Tạo phòng chat bot', 'Tạo phòng chat bot mới', '2025-08-25 16:00:00', 'admin', 1),
(1122, 'Hỏi ChatBot', 'Hỏi chat bot về câu hỏi: \"hello\"', '2025-08-25 16:00:00', 'admin', 1),
(1123, 'Hỏi ChatBot', 'Hỏi chat bot về câu hỏi: \"ielts\"', '2025-08-25 16:00:00', 'admin', 1),
(1124, 'Học từ vựng', 'Học từ vựng mới \"food\" thuộc bài học \"Food - Thức ăn\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1125, 'Học từ vựng', 'Học từ vựng mới \"rice\" thuộc bài học \"Food - Thức ăn\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1126, 'Học từ vựng', 'Học từ vựng mới \"restaurant\" thuộc bài học \"Food - Thức ăn\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1127, 'Học từ vựng', 'Học từ vựng mới \"hungry\" thuộc bài học \"Food - Thức ăn\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1128, 'Học từ vựng', 'Học từ vựng mới \"delicious\" thuộc bài học \"Food - Thức ăn\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1129, 'Ôn tập từ vựng siêu tốc', 'Trả lời đúng câu ôn tập của từ vựng \"food\"', '2025-08-25 16:00:00', 'admin', 2),
(1130, 'Ôn tập từ vựng siêu tốc', 'Trả lời đúng câu ôn tập của từ vựng \"rice\"', '2025-08-25 16:00:00', 'admin', 2),
(1131, 'Ôn tập từ vựng siêu tốc', 'Trả lời đúng câu ôn tập của từ vựng \"restaurant\"', '2025-08-25 16:00:00', 'admin', 2),
(1132, 'Ôn tập từ vựng siêu tốc', 'Trả lời đúng câu ôn tập của từ vựng \"hungry\"', '2025-08-25 16:00:00', 'admin', 2),
(1133, 'Ôn tập từ vựng siêu tốc', 'Trả lời đúng câu ôn tập của từ vựng \"delicious\"', '2025-08-25 16:00:00', 'admin', 2),
(1134, 'Ôn tập từ vựng siêu tốc', 'Trả lời đúng câu ôn tập của từ vựng \"food\"', '2025-08-25 16:00:00', 'admin', 2),
(1135, 'Ôn tập từ vựng siêu tốc', 'Trả lời đúng câu ôn tập của từ vựng \"rice\"', '2025-08-25 16:00:00', 'admin', 2),
(1136, 'Học từ vựng', 'Học từ vựng mới \"school\" thuộc bài học \"Education \" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1137, 'Học từ vựng', 'Học từ vựng mới \"teacher\" thuộc bài học \"Education \" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1138, 'Học từ vựng', 'Học từ vựng mới \"station\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1139, 'Đánh dấu từ khó', 'Đánh dấu từ khó mới: \"station\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1140, 'Học từ vựng', 'Học từ vựng mới \"driver\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1141, 'Ôn tập từ vựng', 'Trả lời đúng câu ôn tập của từ vựng \"car\"', '2025-08-25 16:00:00', 'admin', 2),
(1142, 'Học từ vựng', 'Học từ vựng mới \"car\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'dung1234', 2),
(1143, 'Cập nhật tài khoản', 'Cập nhật thông tin tài khoản', '2025-08-25 16:00:00', 'khang', 3),
(1144, 'Học từ vựng', 'Học từ vựng mới \"computer\" thuộc bài học \"Technology - Công nghệ\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1145, 'Học từ vựng', 'Học từ vựng mới \"internet\" thuộc bài học \"Technology - Công nghệ\" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', 'admin', 2),
(1146, 'Học từ vựng', 'Học từ vựng mới \"car\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', '0411nguyentiendung503462', 2),
(1147, 'Học từ vựng', 'Học từ vựng mới \"bus\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', '0411nguyentiendung503462', 2),
(1148, 'Học từ vựng', 'Học từ vựng mới \"traffic\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', '0411nguyentiendung503462', 2),
(1149, 'Học từ vựng', 'Học từ vựng mới \"road\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', '0411nguyentiendung503462', 2),
(1150, 'Học từ vựng', 'Học từ vựng mới \"bicycle\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-08-25 16:00:00', '0411nguyentiendung503462', 2),
(1151, 'Ôn tập từ vựng siêu tốc', 'Trả lời đúng câu ôn tập của từ vựng \"car\"', '2025-08-25 16:00:00', '0411nguyentiendung503462', 2),
(1152, 'Ôn tập từ vựng siêu tốc', 'Trả lời sai câu ôn tập của từ vựng \"bus\"', '2025-08-25 16:00:00', '0411nguyentiendung503462', 2),
(1153, 'Ôn tập từ vựng siêu tốc', 'Trả lời đúng câu ôn tập của từ vựng \"bus\"', '2025-08-25 16:00:00', '0411nguyentiendung503462', 2),
(1154, 'Ôn tập từ vựng', 'Trả lời đúng câu ôn tập của từ vựng \"traffic\"', '2025-08-25 16:00:00', '0411nguyentiendung503462', 2),
(1155, 'Ôn tập từ vựng', 'Trả lời đúng câu ôn tập của từ vựng \"road\"', '2025-08-25 16:00:00', '0411nguyentiendung503462', 2),
(1156, 'Ôn tập từ vựng', 'Trả lời đúng câu ôn tập của từ vựng \"bicycle\"', '2025-08-25 16:00:00', '0411nguyentiendung503462', 2),
(1157, 'Đánh giá khóa học', 'Đánh giá khóa học \"Tiếng Anh\" với nội dung \"Hay\" thành công', '2025-08-25 16:00:00', 'khangdz', 2),
(1158, 'Đánh giá khóa học', 'Đánh giá khóa học \"Tiếng Anh\" với nội dung \"Hay\" thành công', '2025-08-25 16:00:00', 'khangdz', 2),
(1159, 'Chỉnh sửa đánh giá khóa học', 'Chỉnh sửa đánh giá khóa học \"Tiếng Anh\" với nội dung \"Hay\" thành công', '2025-08-25 16:00:00', 'khangdz', 2),
(1160, 'Chỉnh sửa đánh giá khóa học', 'Chỉnh sửa đánh giá khóa học \"Tiếng Anh\" với nội dung \"Hay lắm\" thành công', '2025-08-25 16:00:00', 'khangdz', 2),
(1161, 'Học từ vựng', 'Học từ vựng mới \"drink\" thuộc bài học \"Food \" của khóa học \"Tiếng Anh\"', '2025-09-04 14:47:51', 'admin', 2),
(1162, 'Học từ vựng', 'Học từ vựng mới \"breakfast\" thuộc bài học \"Food \" của khóa học \"Tiếng Anh\"', '2025-09-04 14:47:57', 'admin', 2),
(1163, 'Ôn tập từ vựng', 'Trả lời đúng câu ôn tập của từ vựng \"food\"', '2025-09-04 14:48:04', 'admin', 2),
(1164, 'Ôn tập từ vựng', 'Trả lời đúng câu ôn tập của từ vựng \"rice\"', '2025-09-04 14:48:06', 'admin', 2),
(1165, 'Đánh giá khóa học', 'Đánh giá khóa học \"Tiếng Anh\" với nội dung \"nhu cc\" thành công', '2025-09-15 10:38:53', '0411nguyentiendung503462', 2),
(1166, 'Chỉnh sửa đánh giá khóa học', 'Chỉnh sửa đánh giá khóa học \"Tiếng Anh\" với nội dung \"nhu cc\" thành công', '2025-09-15 10:39:01', '0411nguyentiendung503462', 2),
(1167, 'Học từ vựng', 'Học từ vựng mới \"student\" thuộc bài học \"Education \" của khóa học \"Tiếng Anh\"', '2025-09-15 11:38:41', 'admin', 2),
(1168, 'Học từ vựng', 'Học từ vựng mới \"car\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-15 16:58:47', 'dungnguyentien853712', 2),
(1169, 'Học từ vựng', 'Học từ vựng mới \"bus\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-15 16:58:49', 'dungnguyentien853712', 2),
(1170, 'Học từ vựng', 'Học từ vựng mới \"traffic\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-15 16:58:49', 'dungnguyentien853712', 2),
(1171, 'Học từ vựng', 'Học từ vựng mới \"road\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-15 16:58:50', 'dungnguyentien853712', 2),
(1172, 'Học từ vựng', 'Học từ vựng mới \"car\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-15 17:13:16', 'khangtuan657132', 2),
(1173, 'Học từ vựng', 'Học từ vựng mới \"bus\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-15 17:13:18', 'khangtuan657132', 2),
(1174, 'Học từ vựng', 'Học từ vựng mới \"traffic\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-15 17:13:19', 'khangtuan657132', 2),
(1175, 'Học từ vựng', 'Học từ vựng mới \"road\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-15 17:13:48', 'khangtuan657132', 2),
(1176, 'Học từ vựng', 'Học từ vựng mới \"book\" thuộc bài học \"Education \" của khóa học \"Tiếng Anh\"', '2025-09-17 00:30:09', 'admin', 2),
(1177, 'Học từ vựng', 'Học từ vựng mới \"homework\" thuộc bài học \"Education \" của khóa học \"Tiếng Anh\"', '2025-09-17 00:30:17', 'admin', 2),
(1178, 'Học từ vựng', 'Học từ vựng mới \"classroom\" thuộc bài học \"Education \" của khóa học \"Tiếng Anh\"', '2025-09-17 00:30:18', 'admin', 2),
(1179, 'Học từ vựng', 'Học từ vựng mới \"study\" thuộc bài học \"Education \" của khóa học \"Tiếng Anh\"', '2025-09-17 00:30:18', 'admin', 2),
(1180, 'Học từ vựng', 'Học từ vựng mới \"learn\" thuộc bài học \"Education \" của khóa học \"Tiếng Anh\"', '2025-09-17 00:30:20', 'admin', 2),
(1181, 'Chỉnh sửa đánh giá khóa học', 'Chỉnh sửa đánh giá khóa học \"Tiếng Anh\" với nội dung \"Mình rất thích khóa học này.\" thành công', '2025-09-18 00:14:13', '0411nguyentiendung503462', 2),
(1182, 'Đánh giá khóa học', 'Đánh giá khóa học \"Tiếng Anh\" với nội dung \"Khóa học này rất hữu ích.\" thành công', '2025-09-18 00:14:56', '1388duongtuankhang806721', 2),
(1183, 'Học từ vựng', 'Học từ vựng mới \"car\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-18 00:15:05', '1388duongtuankhang806721', 2),
(1184, 'Học từ vựng', 'Học từ vựng mới \"bus\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-18 00:15:17', '1388duongtuankhang806721', 2),
(1185, 'Học từ vựng', 'Học từ vựng mới \"traffic\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-18 00:15:20', '1388duongtuankhang806721', 2),
(1186, 'Học từ vựng', 'Học từ vựng mới \"road\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-18 00:15:22', '1388duongtuankhang806721', 2),
(1187, 'Học từ vựng', 'Học từ vựng mới \"bicycle\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-18 00:15:25', '1388duongtuankhang806721', 2),
(1188, 'Học từ vựng', 'Học từ vựng mới \"station\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-18 00:15:48', '1388duongtuankhang806721', 2),
(1189, 'Học từ vựng', 'Học từ vựng mới \"driver\" thuộc bài học \"Traffic \" của khóa học \"Tiếng Anh\"', '2025-09-18 00:15:50', '1388duongtuankhang806721', 2);

-- --------------------------------------------------------

--
-- Table structure for table `hoctumoi`
--

CREATE TABLE `hoctumoi` (
  `TaiKhoan` varchar(100) NOT NULL,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp(),
  `Token` varchar(20) NOT NULL,
  `TienTrinh` int(11) NOT NULL DEFAULT 0,
  `SoCauHienTai` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoctumoi`
--

INSERT INTO `hoctumoi` (`TaiKhoan`, `ThoiGian`, `Token`, `TienTrinh`, `SoCauHienTai`) VALUES
('0411nguyentiendung503462', '2025-08-25 18:57:12', 'OKQBJNH4P2GMSTRZXIA7', 100, 5),
('1388duongtuankhang806721', '2025-09-18 00:15:04', '4PCU5KJMW28B91GTAZRS', 100, 5),
('1388duongtuankhang806721', '2025-09-18 00:15:48', 'HJWUY1DAOVI2T5N3RMXS', 40, 2),
('admin', '2025-08-24 01:10:59', '1B2N7S6ZCQD45TH3EJA9', 40, 2),
('admin', '2025-09-04 14:47:46', '4NHTB6M8O50A32DZWYGL', 0, 0),
('admin', '2025-09-15 11:38:40', '56BAYHGN7W3I218MT9DQ', 20, 1),
('admin', '2025-08-23 18:42:26', '6TMR1SYZELJKNA53WQHI', 100, 5),
('admin', '2025-09-04 14:47:51', '8BO976C4KVP25XGNQAU3', 40, 2),
('admin', '2025-09-17 00:30:03', 'DYZ1MA95JK0SO8L3PW6C', 0, 0),
('admin', '2025-08-23 20:22:02', 'ERX7BSU8GIKAMWLJ4H52', 100, 5),
('admin', '2025-09-17 00:30:09', 'K612EAHMWP0V3XZOISBG', 100, 5),
('admin', '2025-08-25 01:51:54', 'MCBUNWE5KLY8TA2GO9XD', 40, 2),
('admin', '2025-08-24 23:33:18', 'N2WG34M1QHU0JYRS5LIX', 20, 1),
('admin', '2025-08-24 23:29:29', 'PA2QT79N4R610ZVIS35L', 20, 1),
('dung1234', '2025-08-25 00:35:00', 'DKEZSM36YTWLHRXAO5C9', 20, 1),
('dungnguyentien853712', '2025-09-15 16:58:46', 'LRWITVJCGUS5N4QZYDB2', 80, 4),
('khangtuan657132', '2025-09-15 17:13:48', 'JURTEV63M8AP27IHNWSX', 20, 1),
('khangtuan657132', '2025-09-15 17:13:16', 'OGB3Y1M09HUIZWDKT8RA', 60, 3);

-- --------------------------------------------------------

--
-- Table structure for table `hoctuvung`
--

CREATE TABLE `hoctuvung` (
  `TaiKhoan` varchar(100) NOT NULL,
  `MaTuVung` int(11) NOT NULL,
  `MaBaiHoc` int(11) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp(),
  `ThoiGianOnTap` datetime DEFAULT NULL,
  `TuKho` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoctuvung`
--

INSERT INTO `hoctuvung` (`TaiKhoan`, `MaTuVung`, `MaBaiHoc`, `MaKhoaHoc`, `ThoiGian`, `ThoiGianOnTap`, `TuKho`) VALUES
('0411nguyentiendung503462', 1, 1, 1, '2025-08-25 18:57:13', '2025-08-25 18:57:45', 0),
('0411nguyentiendung503462', 2, 1, 1, '2025-08-25 18:57:21', '2025-08-25 18:57:47', 0),
('0411nguyentiendung503462', 3, 1, 1, '2025-08-25 18:57:22', '2025-08-25 18:57:54', 0),
('0411nguyentiendung503462', 4, 1, 1, '2025-08-25 18:57:27', '2025-08-25 18:57:58', 0),
('0411nguyentiendung503462', 5, 1, 1, '2025-08-25 18:57:31', '2025-08-25 18:58:03', 0),
('1388duongtuankhang806721', 1, 1, 1, '2025-09-18 00:15:05', NULL, 0),
('1388duongtuankhang806721', 2, 1, 1, '2025-09-18 00:15:17', NULL, 0),
('1388duongtuankhang806721', 3, 1, 1, '2025-09-18 00:15:20', NULL, 0),
('1388duongtuankhang806721', 4, 1, 1, '2025-09-18 00:15:22', NULL, 0),
('1388duongtuankhang806721', 5, 1, 1, '2025-09-18 00:15:25', NULL, 0),
('1388duongtuankhang806721', 6, 1, 1, '2025-09-18 00:15:48', NULL, 0),
('1388duongtuankhang806721', 7, 1, 1, '2025-09-18 00:15:50', NULL, 0),
('admin', 1, 1, 1, '2025-08-23 18:42:27', '2025-08-24 23:33:25', 0),
('admin', 2, 1, 1, '2025-08-23 18:42:32', NULL, 0),
('admin', 3, 1, 1, '2025-08-23 18:42:33', NULL, 0),
('admin', 4, 1, 1, '2025-08-23 18:42:33', NULL, 0),
('admin', 5, 1, 1, '2025-08-23 18:42:34', NULL, 0),
('admin', 6, 1, 1, '2025-08-24 23:29:30', NULL, 1),
('admin', 7, 1, 1, '2025-08-24 23:33:18', NULL, 0),
('admin', 8, 2, 1, '2025-08-23 20:22:02', '2025-09-04 14:48:04', 0),
('admin', 9, 2, 1, '2025-08-23 20:22:03', '2025-09-04 14:48:06', 0),
('admin', 10, 2, 1, '2025-08-23 20:22:04', '2025-08-23 20:22:11', 0),
('admin', 11, 2, 1, '2025-08-23 20:22:04', '2025-08-23 20:22:13', 0),
('admin', 12, 2, 1, '2025-08-23 20:22:04', '2025-08-23 20:22:15', 0),
('admin', 13, 2, 1, '2025-09-04 14:47:51', NULL, 0),
('admin', 14, 2, 1, '2025-09-04 14:47:57', NULL, 0),
('admin', 15, 3, 1, '2025-08-24 01:11:00', NULL, 0),
('admin', 16, 3, 1, '2025-08-24 01:11:04', NULL, 0),
('admin', 17, 3, 1, '2025-09-15 11:38:41', NULL, 0),
('admin', 18, 3, 1, '2025-09-17 00:30:09', NULL, 0),
('admin', 19, 3, 1, '2025-09-17 00:30:17', NULL, 0),
('admin', 20, 3, 1, '2025-09-17 00:30:18', NULL, 0),
('admin', 21, 3, 1, '2025-09-17 00:30:18', NULL, 0),
('admin', 22, 3, 1, '2025-09-17 00:30:20', NULL, 0),
('admin', 44, 7, 1, '2025-08-25 01:51:54', NULL, 0),
('admin', 45, 7, 1, '2025-08-25 01:51:56', NULL, 0),
('dung1234', 1, 1, 1, '2025-08-25 00:35:01', NULL, 0),
('dungnguyentien853712', 1, 1, 1, '2025-09-15 16:58:47', NULL, 0),
('dungnguyentien853712', 2, 1, 1, '2025-09-15 16:58:49', NULL, 0),
('dungnguyentien853712', 3, 1, 1, '2025-09-15 16:58:49', NULL, 0),
('dungnguyentien853712', 4, 1, 1, '2025-09-15 16:58:50', NULL, 0),
('khangtuan657132', 1, 1, 1, '2025-09-15 17:13:16', NULL, 0),
('khangtuan657132', 2, 1, 1, '2025-09-15 17:13:18', NULL, 0),
('khangtuan657132', 3, 1, 1, '2025-09-15 17:13:19', NULL, 0),
('khangtuan657132', 4, 1, 1, '2025-09-15 17:13:48', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `khoahoc`
--

CREATE TABLE `khoahoc` (
  `MaKhoaHoc` int(11) NOT NULL,
  `TenKhoaHoc` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `LinkAnh` text NOT NULL,
  `NoiDung` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `SoHocVien` int(11) NOT NULL DEFAULT 0,
  `NguoiTao` varchar(100) NOT NULL,
  `TrangThaiKhoaHoc` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTaoKhoaHoc` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `khoahoc`
--

INSERT INTO `khoahoc` (`MaKhoaHoc`, `TenKhoaHoc`, `LinkAnh`, `NoiDung`, `SoHocVien`, `NguoiTao`, `TrangThaiKhoaHoc`, `ThoiGianTaoKhoaHoc`) VALUES
(1, 'Tiếng Anh', 'https://i.imgur.com/Ix8u2gB.jpg', 'Tự giới thiệu bản thân, khám phá xung quanh, và học các câu nói đời thường hữu ích sẽ làm mọi người cười ngạc nhiên.', 26, 'admin', 1, '2025-08-25 14:30:00'),
(2, 'Tiếng Nhật', 'https://i.imgur.com/LpuAoqN.jpg', 'Tự giới thiệu bản thân, khám phá xung quanh, và học các câu nói đời thường hữu ích sẽ làm mọi người cười ngạc nhiên.', 9, 'admin', 1, '2025-08-25 14:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `loaihoatdong`
--

CREATE TABLE `loaihoatdong` (
  `MaLoaiHoatDong` int(11) NOT NULL,
  `TenLoaiHoatDong` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `LinkAnh` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loaihoatdong`
--

INSERT INTO `loaihoatdong` (`MaLoaiHoatDong`, `TenLoaiHoatDong`, `LinkAnh`) VALUES
(1, 'Hệ thống', '/assets/img/active-login.svg'),
(2, 'Học tập', '/assets/img/study-book.svg'),
(3, 'Tài khoản', '/assets/img/file.svg');

-- --------------------------------------------------------

--
-- Table structure for table `message_chatbot_room`
--

CREATE TABLE `message_chatbot_room` (
  `MaTinNhan` int(11) NOT NULL,
  `MaRoom` int(11) NOT NULL,
  `NoiDung` text NOT NULL,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp(),
  `Role` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_chatbot_room`
--

INSERT INTO `message_chatbot_room` (`MaTinNhan`, `MaRoom`, `NoiDung`, `ThoiGian`, `Role`) VALUES
(107, 12, 'Hello! I\'m your English learning assistant. How can I help you today?', '2025-08-23 18:45:30', 'assistant'),
(108, 12, 'ielts', '2025-08-23 18:45:37', 'user'),
(109, 12, 'I\'m your English learning assistant. Feel free to ask me about vocabulary meanings, grammar questions, or study advice!', '2025-08-23 18:45:37', 'assistant');

-- --------------------------------------------------------

--
-- Table structure for table `muctieuhoctap`
--

CREATE TABLE `muctieuhoctap` (
  `MaMucTieu` int(11) NOT NULL,
  `TenMucTieu` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `NoiDungMucTieu` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `SoLuongTuMoi` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `muctieuhoctap`
--

INSERT INTO `muctieuhoctap` (`MaMucTieu`, `TenMucTieu`, `NoiDungMucTieu`, `SoLuongTuMoi`) VALUES
(1, 'Thông thường', '5 từ mới', 5),
(2, 'Đều đặn', '10 từ mới', 10),
(3, 'Nghiêm túc', '15 từ mới', 15),
(4, 'Cao độ', '20 từ mới', 20);

-- --------------------------------------------------------

--
-- Table structure for table `nguoidung`
--

CREATE TABLE `nguoidung` (
  `TaiKhoan` varchar(100) NOT NULL,
  `MatKhau` text NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `KichHoatEmail` tinyint(1) NOT NULL DEFAULT 0,
  `TokenKichHoatEmail` text DEFAULT NULL,
  `ThoiGianTokenKichHoatEmail` datetime DEFAULT NULL,
  `TokenKhoiPhucMatKhau` text DEFAULT NULL,
  `ThoiGianTokenKhoiPhucMatKhau` datetime DEFAULT NULL,
  `FacebookID` text DEFAULT NULL,
  `AnhDaiDien` text DEFAULT NULL,
  `NgayDangKy` datetime NOT NULL DEFAULT current_timestamp(),
  `TenHienThi` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `IPAddress` text NOT NULL,
  `KinhNghiem` int(11) NOT NULL DEFAULT 0,
  `TongKinhNghiem` int(11) NOT NULL DEFAULT 0,
  `CapDo` int(11) NOT NULL DEFAULT 1,
  `MaQuyenHan` int(11) NOT NULL,
  `MaMucTieu` int(11) NOT NULL,
  `TrangThai` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nguoidung`
--

INSERT INTO `nguoidung` (`TaiKhoan`, `MatKhau`, `Email`, `KichHoatEmail`, `TokenKichHoatEmail`, `ThoiGianTokenKichHoatEmail`, `TokenKhoiPhucMatKhau`, `ThoiGianTokenKhoiPhucMatKhau`, `FacebookID`, `AnhDaiDien`, `NgayDangKy`, `TenHienThi`, `IPAddress`, `KinhNghiem`, `TongKinhNghiem`, `CapDo`, `MaQuyenHan`, `MaMucTieu`, `TrangThai`) VALUES
('0411nguyentiendung503462', '871a518d732865703294c29db49db8d0', 'nguyentiendungt123@gmail.com', 1, NULL, NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocL3EmWGC2xvPJKeEjw9y81YdRvAW_qoPV9reEisR4lsApKscg=s96-c', '2025-08-25 01:41:45', '0411_Nguyễn Tiến Dũng', '::1', 0, 100, 2, 1, 4, 1),
('1388duongtuankhang806721', '1e13879f7ca0536f312f9512ce2821ca', 'khangnha0811@gmail.com', 1, NULL, NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocL0EL6ekVWcA7-tIPIyE-61SDcEoU4refnfW_Sxexm1OHAZYPqcoQ=s96-c', '2025-09-15 11:52:52', '1388_Dương Tuấn Khang', '::1', 70, 70, 1, 1, 2, 1),
('admin', '21232f297a57a5a743894a0e4a801fc3', NULL, 0, '', NULL, NULL, NULL, NULL, 'https://i.imgur.com/2U2xyPR.png', '2023-05-26 08:15:24', 'Admin', '::1', 40, 340, 3, 2, 1, 1),
('dung123', '25f9e794323b453885f5181f1b624d0b', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'https://i.imgur.com/2U2xyPR.png', '2025-08-24 23:43:01', 'dung', '::1', 0, 0, 1, 1, 1, 1),
('dung1234', '25f9e794323b453885f5181f1b624d0b', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'https://i.imgur.com/2U2xyPR.png', '2025-08-24 23:50:01', 'dung3333', '::1', 10, 10, 1, 1, 1, 1),
('dungnguyentien853712', 'e7e679b0c2954cdd82f114961102f736', 'nguyentiendung030704@gmail.com', 1, NULL, NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocK_3cnAb9S5fdn0Oz70SY9ftiAByzK-zcEt31sVLoUzMQHutQ=s96-c', '2025-09-15 16:53:13', 'Dũng Nguyễn Tiến', '::1', 40, 40, 1, 1, 4, 1),
('khang', 'c39e2024ef5db5d740027aee5250440b', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'http://localhost/webhocngoaingu/assets/uploads/845344co-so-vat-chat-ht1-2-361.jpg', '2025-08-25 00:35:18', 'khang', '::1', 0, 0, 1, 1, 4, 1),
('khangdz', '25f9e794323b453885f5181f1b624d0b', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'https://i.imgur.com/2U2xyPR.png', '2025-08-25 19:02:08', 'Khangs', '::1', 0, 0, 1, 1, 4, 1),
('khangtuan657132', 'd39d833cfaedcce0ea0ab9e906ac19c8', 'tuankhangabcd1122@gmail.com', 1, NULL, NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocIu9CYWIKhAvggFUqHXJyTfnqYLc54Tm35huYGm65eBzC0DrA=s96-c', '2025-09-15 16:59:49', 'Khang Tuan', '::1', 40, 40, 1, 1, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ontaploai1`
--

CREATE TABLE `ontaploai1` (
  `TaiKhoan` varchar(100) NOT NULL,
  `Token` varchar(20) NOT NULL,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp(),
  `MaTuVung` int(11) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL,
  `MaBaiHoc` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ontaploai1`
--

INSERT INTO `ontaploai1` (`TaiKhoan`, `Token`, `ThoiGian`, `MaTuVung`, `MaKhoaHoc`, `MaBaiHoc`) VALUES
('0411nguyentiendung503462', '5QZ4VRMC3J8YSHA9GIEP', '2025-08-25 18:57:47', 3, 1, 1),
('admin', 'B1HM4VIPYG9NSDTXK85L', '2025-09-04 14:47:28', 6, 1, 1),
('admin', 'DKO34ZBCQA7T8NRW9EPU', '2025-08-23 20:22:15', 8, 1, 2),
('admin', 'WVKZT2SL4BOD389IC5E0', '2025-08-23 20:26:34', 10, 1, 2),
('admin', 'Z8LARMIPO4GBXWN0QKJD', '2025-08-24 23:33:26', 2, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ontapsieutoctuvung`
--

CREATE TABLE `ontapsieutoctuvung` (
  `TaiKhoan` varchar(100) NOT NULL,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp(),
  `Token` varchar(20) NOT NULL,
  `SoMang` int(11) NOT NULL DEFAULT 0,
  `SoCauHienTai` int(11) NOT NULL DEFAULT 0,
  `SoCauDung` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ontapsieutoctuvung`
--

INSERT INTO `ontapsieutoctuvung` (`TaiKhoan`, `ThoiGian`, `Token`, `SoMang`, `SoCauHienTai`, `SoCauDung`) VALUES
('0411nguyentiendung503462', '2025-08-25 18:57:43', 'COI93YPWNK4REAS67VD8', 2, 3, 2),
('admin', '2025-08-23 20:22:06', 'T3ZA579X0SNBEDP4H16M', 3, 7, 7);

-- --------------------------------------------------------

--
-- Table structure for table `ontaptuvung`
--

CREATE TABLE `ontaptuvung` (
  `TaiKhoan` varchar(100) NOT NULL,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp(),
  `Token` varchar(20) NOT NULL,
  `TienTrinh` int(11) NOT NULL DEFAULT 0,
  `SoCauHienTai` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ontaptuvung`
--

INSERT INTO `ontaptuvung` (`TaiKhoan`, `ThoiGian`, `Token`, `TienTrinh`, `SoCauHienTai`) VALUES
('0411nguyentiendung503462', '2025-08-25 18:57:53', '12AJUDVYH6E79WMB8QRI', 60, 3),
('admin', '2025-09-04 14:48:00', '96R5ZTCKDYBJ3S8417LW', 40, 2),
('admin', '2025-08-24 23:33:22', 'GLX3BRP8NCA5KY4JTMHI', 20, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ontaptuvungkho`
--

CREATE TABLE `ontaptuvungkho` (
  `TaiKhoan` varchar(100) NOT NULL,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp(),
  `Token` varchar(20) NOT NULL,
  `TienTrinh` int(11) NOT NULL DEFAULT 0,
  `SoCauHienTai` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ontaptuvungkho`
--

INSERT INTO `ontaptuvungkho` (`TaiKhoan`, `ThoiGian`, `Token`, `TienTrinh`, `SoCauHienTai`) VALUES
('0411nguyentiendung503462', '2025-08-25 18:57:49', 'LY17X0KMOAT62B8GU4VR', 0, 0),
('admin', '2025-09-04 14:47:28', '6LZST2PNGR9KA4M8UVCE', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `quyenhan`
--

CREATE TABLE `quyenhan` (
  `MaQuyenHan` int(11) NOT NULL,
  `TenQuyenHan` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quyenhan`
--

INSERT INTO `quyenhan` (`MaQuyenHan`, `TenQuyenHan`) VALUES
(1, 'Thành viên'),
(2, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `reading_lessons`
--

CREATE TABLE `reading_lessons` (
  `MaBaiDoc` int(11) NOT NULL,
  `MaBaiHoc` int(11) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL,
  `TieuDe` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `NoiDungBaiDoc` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `MucDo` enum('Dễ','Trung bình','Khó') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Dễ',
  `ChuDe` varchar(100) DEFAULT 'Traffic',
  `ThoiGianLam` int(11) NOT NULL DEFAULT 10,
  `ThuTu` int(11) NOT NULL DEFAULT 1,
  `TrangThai` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reading_lessons`
--

INSERT INTO `reading_lessons` (`MaBaiDoc`, `MaBaiHoc`, `MaKhoaHoc`, `TieuDe`, `NoiDungBaiDoc`, `MucDo`, `ChuDe`, `ThoiGianLam`, `ThuTu`, `TrangThai`, `ThoiGianTao`) VALUES
(1, 1, 1, 'Traffic Safety', 'Traffic safety is very important in our daily life. Every day, millions of people use different means of transportation such as cars, buses, motorcycles, and bicycles to go to work, school, or other places. However, traffic accidents happen frequently and cause many injuries and deaths.\r\n\r\nTo ensure traffic safety, we need to follow traffic rules. Drivers must wear seat belts, obey speed limits, and never drink and drive. Motorcyclists should wear helmets to protect their heads. Pedestrians must use crosswalks and look both ways before crossing the street.\r\n\r\nTraffic lights play an important role in controlling traffic flow. Red means stop, yellow means caution, and green means go. Everyone must respect these signals to avoid accidents.\r\n\r\nIn conclusion, traffic safety is everyone\'s responsibility. By following traffic rules and being careful on the road, we can prevent accidents and save lives.', 'Dễ', 'Traffic', 15, 1, 1, '2025-08-25 14:00:00'),
(3, 1, 1, 'Traffic Rules and Regulations', 'Understanding and following traffic rules is essential for road safety. Traffic rules are laws designed to protect drivers, passengers, and pedestrians from accidents and injuries.\n\nThe most basic traffic rule is to drive on the correct side of the road. In most countries, vehicles drive on the right side, while in some countries like the United Kingdom and Australia, they drive on the left side.\n\nSpeed limits are another crucial aspect of traffic safety. Different roads have different speed limits depending on the area. Highways typically have higher speed limits than city streets. School zones and residential areas usually have much lower speed limits to protect children and families.\n\nTraffic signs provide important information to drivers. Stop signs require drivers to come to a complete stop before proceeding. Yield signs tell drivers to slow down and give way to other traffic. Warning signs alert drivers to potential dangers like sharp curves, construction zones, or animal crossings.\n\nParking regulations help maintain order and safety in cities. Drivers must park in designated areas and follow time limits. Illegal parking can block emergency vehicles and create traffic congestion.\n\nBy following these rules, we create a safer environment for everyone on the road. Remember: traffic rules save lives!', 'Trung bình', 'Traffic', 20, 3, 1, '2025-08-25 14:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `reading_questions`
--

CREATE TABLE `reading_questions` (
  `MaCauHoi` int(11) NOT NULL,
  `MaBaiDoc` int(11) NOT NULL,
  `CauHoi` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `DapAnA` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `DapAnB` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `DapAnC` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `DapAnD` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `DapAnDung` enum('A','B','C','D') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `GiaiThich` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ThuTu` int(11) NOT NULL DEFAULT 1,
  `ThoiGianTao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reading_questions`
--

INSERT INTO `reading_questions` (`MaCauHoi`, `MaBaiDoc`, `CauHoi`, `DapAnA`, `DapAnB`, `DapAnC`, `DapAnD`, `DapAnDung`, `GiaiThich`, `ThuTu`, `ThoiGianTao`) VALUES
(7, 3, 'What is the main purpose of traffic rules?', 'To make driving faster', 'To protect people from accidents', 'To increase government revenue', 'To control population', 'B', 'B├ái ─æß╗ìc n├│i r├Á: \"Traffic rules are laws designed to protect drivers, passengers, and pedestrians from accidents and injuries.\"', 1, '2025-08-23 19:47:47'),
(8, 3, 'Which areas typically have lower speed limits?', 'Highways and freeways', 'Rural roads', 'School zones and residential areas', 'Industrial zones', 'C', 'Theo b├ái ─æß╗ìc: \"School zones and residential areas usually have much lower speed limits to protect children and families.\"', 2, '2025-08-23 19:47:47'),
(9, 3, 'What must drivers do when they see a stop sign?', 'Slow down and proceed', 'Come to a complete stop', 'Sound their horn', 'Flash their lights', 'B', 'B├ái ─æß╗ìc cho biß║┐t: \"Stop signs require drivers to come to a complete stop before proceeding.\"', 3, '2025-08-23 19:47:47'),
(10, 3, 'Why are parking regulations important?', 'To generate income for cities', 'To maintain order and safety', 'To reduce car ownership', 'To promote public transport', 'B', 'Theo ─æoß║ín v─ân: \"Parking regulations help maintain order and safety in cities.\"', 4, '2025-08-23 19:47:47'),
(11, 3, 'What can illegal parking cause?', 'Lower fuel prices', 'Better air quality', 'Traffic congestion', 'Faster emergency response', 'C', 'B├ái ─æß╗ìc n├¬u r├Á: \"Illegal parking can block emergency vehicles and create traffic congestion.\"', 5, '2025-08-23 19:47:47'),
(12, 3, 'What is the main purpose of traffic rules?', 'To make driving faster', 'To protect people from accidents', 'To increase government revenue', 'To control population', 'B', 'Bài đọc nói rõ: \"Traffic rules are laws designed to protect drivers, passengers, and pedestrians from accidents and injuries.\"', 1, '2025-08-23 19:48:15'),
(13, 3, 'Which areas typically have lower speed limits?', 'Highways and freeways', 'Rural roads', 'School zones and residential areas', 'Industrial zones', 'C', 'Theo bài đọc: \"School zones and residential areas usually have much lower speed limits to protect children and families.\"', 2, '2025-08-23 19:48:15'),
(14, 3, 'What must drivers do when they see a stop sign?', 'Slow down and proceed', 'Come to a complete stop', 'Sound their horn', 'Flash their lights', 'B', 'Bài đọc cho biết: \"Stop signs require drivers to come to a complete stop before proceeding.\"', 3, '2025-08-23 19:48:15'),
(15, 3, 'Why are parking regulations important?', 'To generate income for cities', 'To maintain order and safety', 'To reduce car ownership', 'To promote public transport', 'B', 'Theo đoạn văn: \"Parking regulations help maintain order and safety in cities.\"', 4, '2025-08-23 19:48:15'),
(16, 3, 'What can illegal parking cause?', 'Lower fuel prices', 'Better air quality', 'Traffic congestion', 'Faster emergency response', 'C', 'Bài đọc nêu rõ: \"Illegal parking can block emergency vehicles and create traffic congestion.\"', 5, '2025-08-23 19:48:15'),
(19, 1, 'What should drivers do to ensure traffic safety?', 'Wear seat belts and obey speed limits', 'Drive as fast as possible', 'Ignore traffic lights', 'Drink before driving', 'A', 'Theo đoạn văn, tài xế phải thắt dây an toàn, tuân thủ giới hạn tốc độ và không được uống rượu khi lái xe.', 1, '2025-08-25 02:04:34'),
(20, 1, 'What do traffic light colors mean?', 'Red means go, yellow means stop', 'Red means stop, yellow means caution, green means go', 'All colors mean the same thing', 'Traffic lights are not important', 'B', 'Đoạn văn nói rõ: \"Red means stop, yellow means caution, and green means go.\"', 2, '2025-08-25 02:04:34'),
(21, 1, 'What should motorcyclists wear for protection?', 'Seat belts', 'Helmets', 'Sunglasses', 'Gloves', 'B', 'Theo đoạn văn: \"Motorcyclists should wear helmets to protect their heads.\"', 3, '2025-08-25 02:04:34');

-- --------------------------------------------------------

--
-- Table structure for table `reading_user_progress`
--

CREATE TABLE `reading_user_progress` (
  `TaiKhoan` varchar(100) NOT NULL,
  `MaBaiDoc` int(11) NOT NULL,
  `MaCauHoi` int(11) NOT NULL,
  `DapAnChon` enum('A','B','C','D') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `KetQua` tinyint(1) NOT NULL DEFAULT 0,
  `ThoiGian` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thongbaoemail`
--

CREATE TABLE `thongbaoemail` (
  `MaThongBao` int(11) NOT NULL,
  `TaiKhoan` varchar(100) NOT NULL,
  `CapNhatMoi` tinyint(1) NOT NULL DEFAULT 1,
  `BaoCaoTienTrinhHocTap` tinyint(1) NOT NULL DEFAULT 1,
  `NhacNhoTienTrinhHocTap` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thongbaoemail`
--

INSERT INTO `thongbaoemail` (`MaThongBao`, `TaiKhoan`, `CapNhatMoi`, `BaoCaoTienTrinhHocTap`, `NhacNhoTienTrinhHocTap`) VALUES
(22, 'admin', 1, 1, 1),
(23, 'dung123', 1, 1, 1),
(24, 'dung1234', 1, 1, 1),
(25, 'khang', 1, 1, 1),
(26, '0411nguyentiendung503462', 1, 1, 1),
(27, 'khangdz', 1, 1, 1),
(28, '1388duongtuankhang806721', 1, 1, 1),
(29, 'dungnguyentien853712', 1, 1, 1),
(30, 'khangtuan657132', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tuvung`
--

CREATE TABLE `tuvung` (
  `MaTuVung` int(11) NOT NULL,
  `MaBaiHoc` int(11) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL,
  `NoiDungTuVung` varchar(100) NOT NULL,
  `DichNghia` text NOT NULL,
  `LoaiTu` varchar(50) DEFAULT NULL,
  `CachPhatAm` varchar(200) DEFAULT NULL,
  `HinhAnh` text NOT NULL,
  `AmThanh` text DEFAULT NULL,
  `Diem` int(11) NOT NULL DEFAULT 0,
  `TrangThaiTuVung` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTaoTuVung` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tuvung`
--

INSERT INTO `tuvung` (`MaTuVung`, `MaBaiHoc`, `MaKhoaHoc`, `NoiDungTuVung`, `DichNghia`, `LoaiTu`, `CachPhatAm`, `HinhAnh`, `AmThanh`, `Diem`, `TrangThaiTuVung`, `ThoiGianTaoTuVung`) VALUES
(1, 1, 1, 'car', 'xe hơi', 'Noun', '/kɑːr/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756120569/vocabulary_images/car_opsyh7.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756120513/vocabulary_audio/car_zsmgik.mp3', 10, 1, '2025-08-25 15:15:00'),
(2, 1, 1, 'bus', 'xe buýt', 'Noun', '/bʌs/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756120591/vocabulary_images/bus_jw95yj.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756120613/vocabulary_audio/bus_l9kih7.mp3', 10, 1, '2025-08-25 15:15:00'),
(3, 1, 1, 'traffic', 'giao thông', 'Noun', '/ˈtræfɪk/', 'http://res.cloudinary.com/dydrox9id/image/upload/v1681568225/traffic.jpg', 'http://res.cloudinary.com/dydrox9id/video/upload/v1681568250/traffic.wav', 10, 1, '2025-08-25 15:15:00'),
(4, 1, 1, 'road', 'con đường', 'Noun', '/roʊd/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756120662/vocabulary_images/road_eka13c.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756120673/vocabulary_audio/road_yjso7m.mp3', 10, 1, '2025-08-25 15:15:00'),
(5, 1, 1, 'bicycle', 'xe đạp', 'Noun', '/ˈbaɪsɪkəl/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756120698/vocabulary_images/bicycle_sfqy8i.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756120719/vocabulary_audio/bicycle_z7nwab.mp3', 10, 1, '2025-08-25 15:15:00'),
(6, 1, 1, 'station', 'ga tàu', 'Noun', '/ˈsteɪʃən/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756120739/vocabulary_images/station_wugwd8.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756120753/vocabulary_audio/station_dnlpci.mp3', 10, 1, '2025-08-25 15:15:00'),
(7, 1, 1, 'driver', 'tài xế', 'Noun', '/ˈdraɪvər/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756120774/vocabulary_images/driver_zwz8w1.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756120789/vocabulary_audio/driver_u78vmc.mp3', 10, 1, '2025-08-25 15:15:00'),
(8, 2, 1, 'food', 'đồ ăn', 'Noun', '/fuːd/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756120844/vocabulary_images/food_qdx9qp.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756120833/vocabulary_audio/food_wj3tc9.mp3', 10, 1, '2025-08-25 15:15:00'),
(9, 2, 1, 'rice', 'cơm', 'Noun', '/raɪs/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756120859/vocabulary_images/rice_jppaow.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756120877/vocabulary_audio/rice_yfucma.mp3', 10, 1, '2025-08-25 15:15:00'),
(10, 2, 1, 'restaurant', 'nhà hàng', 'Noun', '/ˈrestərɒnt/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756120907/vocabulary_images/restaurant_jwqcej.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756120919/vocabulary_audio/restaurant_ajhxjb.mp3', 10, 1, '2025-08-25 15:15:00'),
(11, 2, 1, 'hungry', 'đói', 'Adjective', '/ˈhʌŋɡri/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756120943/vocabulary_images/hungry_yiejrk.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756120956/vocabulary_audio/hungry_b3bvej.mp3', 10, 1, '2025-08-25 15:15:00'),
(12, 2, 1, 'delicious', 'ngon', 'Adjective', '/dɪˈlɪʃəs/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756120998/vocabulary_images/delicious_etyxfn.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121034/vocabulary_audio/delicious_khpbb0.mp3', 10, 1, '2025-08-25 15:15:00'),
(13, 2, 1, 'drink', 'đồ uống', 'Noun', '/drɪŋk/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121060/vocabulary_images/drink_niicab.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121098/vocabulary_audio/drink_ewb0rs.mp3', 10, 1, '2025-08-25 15:15:00'),
(14, 2, 1, 'breakfast', 'bữa sáng', 'Noun', '/ˈbrekfəst/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121129/vocabulary_images/breakfast_bmgfwp.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121146/vocabulary_audio/breakfast_utcrpf.mp3', 10, 1, '2025-08-25 15:15:00'),
(15, 3, 1, 'school', 'trường học', 'Noun', '/skuːl/', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121218/vocabulary_images/school_hihjln.mp3', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121230/vocabulary_audio/school_dedrip.jpg', 10, 1, '2025-08-25 15:15:00'),
(16, 3, 1, 'teacher', 'giáo viên', 'Noun', '/ˈtiːtʃər/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121247/vocabulary_images/teacher_produv.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121254/vocabulary_audio/teacher_rvmjdz.mp3', 10, 1, '2025-08-25 15:15:00'),
(17, 3, 1, 'student', 'học sinh', 'Noun', '/ˈstuːdənt/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121285/vocabulary_images/student_zienb3.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121293/vocabulary_audio/student_ckypgb.mp3', 10, 1, '2025-08-25 15:15:00'),
(18, 3, 1, 'book', 'sách', 'Noun', '/bʊk/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121312/vocabulary_images/book_gwgery.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121319/vocabulary_audio/book_jr9kb4.mp3', 10, 1, '2025-08-25 15:15:00'),
(19, 3, 1, 'homework', 'bài tập về nhà', 'Noun', '/ˈhoʊmwɜːrk/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121365/vocabulary_images/homework_inkyyq.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121373/vocabulary_audio/homework_ftxe3j.mp3', 10, 1, '2025-08-25 15:15:00'),
(20, 3, 1, 'classroom', 'lớp học', 'Noun', '/ˈklæsruːm/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121400/vocabulary_images/classroom_umnm1i.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121407/vocabulary_audio/classroom_bsrwm2.mp3', 10, 1, '2025-08-25 15:15:00'),
(21, 3, 1, 'study', 'học', 'Verb', '/ˈstʌdi/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121443/vocabulary_images/study_m1yp10.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121433/vocabulary_audio/study_jr02nw.mp3', 10, 1, '2025-08-25 15:15:00'),
(22, 3, 1, 'learn', 'học hỏi', 'Verb', '/lɜːrn/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121461/vocabulary_images/study_l4ikey.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121467/vocabulary_audio/learn_iobual.mp3', 10, 1, '2025-08-25 15:15:00'),
(23, 4, 1, 'family', 'gia đình', 'Noun', '/ˈfæməli/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121588/vocabulary_images/family_jkj3wj.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121595/vocabulary_audio/family_qiiugu.mp3', 10, 1, '2025-08-25 15:15:00'),
(24, 4, 1, 'mother', 'mẹ', 'Noun', '/ˈmʌðər/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121637/vocabulary_images/mother_ubffjq.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121629/vocabulary_audio/mother_1_j8yqew.mp3', 10, 1, '2025-08-25 15:15:00'),
(25, 4, 1, 'father', 'bố', 'Noun', '/ˈfɑːðər/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121655/vocabulary_images/father_vumafc.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121663/vocabulary_audio/father_1_udejll.mp3', 10, 1, '2025-08-25 15:15:00'),
(26, 4, 1, 'brother', 'anh trai', 'Noun', '/ˈbrʌðər/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121688/vocabulary_images/brother_nsbvkb.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121676/vocabulary_audio/brother_wnuzqk.mp3', 10, 1, '2025-08-25 15:15:00'),
(27, 4, 1, 'sister', 'chị gái', 'Noun', '/ˈsɪstər/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121701/vocabulary_images/sister_mr0lmb.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121707/vocabulary_audio/sister_rjh1b5.mp3', 10, 1, '2025-08-25 15:15:00'),
(28, 4, 1, 'parents', 'bố mẹ', 'Noun', '/ˈperənts/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121729/vocabulary_images/parents_r88awj.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121719/vocabulary_audio/parents_pmpcff.mp3', 10, 1, '2025-08-25 15:15:00'),
(29, 4, 1, 'children', 'con cái', 'Noun', '/ˈtʃɪldrən/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121745/vocabulary_images/children_jerg6s.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121751/vocabulary_audio/children_kw9zta.mp3', 10, 1, '2025-08-25 15:15:00'),
(30, 4, 1, 'love', 'yêu thương', 'Verb', '/lʌv/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121782/vocabulary_images/children_hg9qbz.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121772/vocabulary_audio/love_eqmzz4.mp3', 10, 1, '2025-08-25 15:15:00'),
(31, 5, 1, 'work', 'công việc', 'Noun', '/wɜːrk/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121854/vocabulary_images/work_slsxha.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121859/vocabulary_audio/work_u8qc2p.mp3', 10, 1, '2025-08-25 15:15:00'),
(32, 5, 1, 'job', 'việc làm', 'Noun', '/dʒɑːb/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121888/vocabulary_images/job_w8kegu.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121878/vocabulary_audio/job_jzumhk.mp3', 10, 1, '2025-08-25 15:15:00'),
(33, 5, 1, 'office', 'văn phòng', 'Noun', '/ˈɔːfɪs/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121910/vocabulary_images/office_q8nr4s.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121916/vocabulary_audio/office_k6tzm0.mp3', 10, 1, '2025-08-25 15:15:00'),
(34, 5, 1, 'boss', 'sếp', 'Noun', '/bɔːs/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121941/vocabulary_images/boss_fpftrr.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121927/vocabulary_audio/boss_uulhbn.mp3', 10, 1, '2025-08-25 15:15:00'),
(35, 5, 1, 'employee', 'nhân viên', 'Noun', '/ɪmˈplɔɪiː/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121954/vocabulary_images/employee_n0ijf8.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121961/vocabulary_audio/employee_jolccb.mp3', 10, 1, '2025-08-25 15:15:00'),
(36, 5, 1, 'salary', 'lương', 'Noun', '/ˈsæləri/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756121996/vocabulary_images/salary_meeo43.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756121981/vocabulary_audio/salary_iioriu.mp3', 10, 1, '2025-08-25 15:15:00'),
(37, 6, 1, 'hobby', 'sở thích', 'Noun', '/ˈhɑːbi/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122076/vocabulary_images/hobby_oxl1ic.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122086/vocabulary_audio/hobby_yjh4oy.mp3', 10, 1, '2025-08-25 15:15:00'),
(38, 6, 1, 'music', 'âm nhạc', 'Noun', '/ˈmjuːzɪk/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122113/vocabulary_images/music_ef6iqr.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122104/vocabulary_audio/music_xvj33e.mp3', 10, 1, '2025-08-25 15:15:00'),
(39, 6, 1, 'reading', 'đọc sách', 'Noun', '/ˈriːdɪŋ/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122130/vocabulary_images/reading_woop0b.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122136/vocabulary_audio/reading_dibvje.mp3', 10, 1, '2025-08-25 15:15:00'),
(40, 6, 1, 'sports', 'thể thao', 'Noun', '/spɔːrts/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122180/vocabulary_images/sports_swxesp.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122154/vocabulary_audio/sports_ygfhhn.mp3', 10, 1, '2025-08-25 15:15:00'),
(41, 6, 1, 'movie', 'phim', 'Noun', '/ˈmuːvi/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122196/vocabulary_images/movie_vnknip.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122201/vocabulary_audio/movie_qcv8vs.mp3', 10, 1, '2025-08-25 15:15:00'),
(42, 6, 1, 'game', 'trò chơi', 'Noun', '/ɡeɪm/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122248/vocabulary_images/game_a5ftfl.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122255/vocabulary_audio/game_brsr0y.mp3', 10, 1, '2025-08-25 15:15:00'),
(43, 6, 1, 'travel', 'du lịch', 'Verb', '/ˈtrævəl/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122281/vocabulary_images/travel_g4t9fg.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122268/vocabulary_audio/travel_l2xlbg.mp3', 10, 1, '2025-08-25 15:15:00'),
(44, 7, 1, 'computer', 'máy tính', 'Noun', '/kəmˈpjuːtər/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122361/vocabulary_images/computer_jenxsi.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122369/vocabulary_audio/computer_sirobl.mp3', 10, 1, '2025-08-25 15:15:00'),
(45, 7, 1, 'internet', 'mạng internet', 'Noun', '/ˈɪntərnet/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122409/vocabulary_images/internet_zdqhjc.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122417/vocabulary_audio/internet_fvtrbi.mp3', 10, 1, '2025-08-25 15:15:00'),
(46, 7, 1, 'phone', 'điện thoại', 'Noun', '/foʊn/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122548/vocabulary_images/phone_ziykuq.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122537/vocabulary_audio/phone_eemkzm.mp3', 10, 1, '2025-08-25 15:15:00'),
(47, 7, 1, 'email', 'thư điện tử', 'Noun', '/ˈiːmeɪl/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122565/vocabulary_images/email_hgnhbv.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122572/vocabulary_audio/email_rxbnxo.mp3', 10, 1, '2025-08-25 15:15:00'),
(48, 7, 1, 'website', 'trang web', 'Noun', '/ˈwebsaɪt/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122642/vocabulary_images/website_t5vnfi.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122648/vocabulary_audio/website_avtpg7.mp3', 10, 1, '2025-08-25 15:15:00'),
(49, 7, 1, 'software', 'phần mềm', 'Noun', '/ˈsɔːftwer/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122614/vocabulary_images/software_zm4vet.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122600/vocabulary_audio/software_fgg0sw.mp3', 10, 1, '2025-08-25 15:15:00'),
(50, 8, 1, 'activity', 'hoạt động', 'Noun', '/ækˈtɪvəti/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122735/vocabulary_images/activity_xwe4f3.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122718/vocabulary_audio/activity_rn84rh.mp3', 10, 1, '2025-08-25 15:15:00'),
(51, 8, 1, 'exercise', 'tập thể dục', 'Noun', '/ˈeksərsaɪz/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122753/vocabulary_images/exercise_hpowsr.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122763/vocabulary_audio/exercise_htne45.mp3', 10, 1, '2025-08-25 15:15:00'),
(52, 8, 1, 'cooking', 'nấu ăn', 'Noun', '/ˈkʊkɪŋ/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122786/vocabulary_images/cooking_zmzypz.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122775/vocabulary_audio/cooking_lj5zwl.mp3', 10, 1, '2025-08-25 15:15:00'),
(53, 8, 1, 'shopping', 'mua sắm', 'Noun', '/ˈʃɑːpɪŋ/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122807/vocabulary_images/shopping_aqk4qv.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122813/vocabulary_audio/shopping_cudcj4.mp3', 10, 1, '2025-08-25 15:15:00'),
(54, 8, 1, 'swimming', 'bơi lội', 'Noun', '/ˈswɪmɪŋ/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122852/vocabulary_images/swimming_frppoi.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122825/vocabulary_audio/swimming_zsqozr.mp3', 10, 1, '2025-08-25 15:15:00'),
(55, 8, 1, 'dancing', 'khiêu vũ', 'Noun', '/ˈdænsɪŋ/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122866/vocabulary_images/dancing_plbk1m.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122872/vocabulary_audio/dancing_gwf9hs.mp3', 10, 1, '2025-08-25 15:15:00'),
(56, 8, 1, 'walking', 'đi bộ', 'Noun', '/ˈwɔːkɪŋ/', 'http://res.cloudinary.com/dy0rox9id/image/upload/v1756122898/vocabulary_images/walking_k1jrdu.jpg', 'http://res.cloudinary.com/dy0rox9id/video/upload/v1756122891/vocabulary_audio/walking_ld7jdq.mp3', 10, 1, '2025-08-25 15:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `vidu`
--

CREATE TABLE `vidu` (
  `MaViDu` int(11) NOT NULL,
  `MaTuVung` int(11) NOT NULL,
  `MaBaiHoc` int(11) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL,
  `CauViDu` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `DichNghia` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `TrangThaiViDu` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTaoViDu` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `writing_drafts`
--

CREATE TABLE `writing_drafts` (
  `id` int(11) NOT NULL,
  `prompt_id` int(11) NOT NULL,
  `user_account` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `word_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `writing_prompts`
--

CREATE TABLE `writing_prompts` (
  `MaDeBai` int(11) NOT NULL,
  `MaChuDe` int(11) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL,
  `TieuDe` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `NoiDungDeBai` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `GioiHanTu` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ThoiGianLamBai` int(11) NOT NULL DEFAULT 30 COMMENT 'Thời gian làm bài tính bằng phút',
  `MucDo` enum('Dễ','Trung bình','Khó') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Trung bình',
  `TrangThai` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTao` datetime NOT NULL DEFAULT current_timestamp(),
  `NguoiTao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `writing_prompts`
--

INSERT INTO `writing_prompts` (`MaDeBai`, `MaChuDe`, `MaKhoaHoc`, `TieuDe`, `NoiDungDeBai`, `GioiHanTu`, `ThoiGianLamBai`, `MucDo`, `TrangThai`, `ThoiGianTao`, `NguoiTao`) VALUES
(2, 1, 1, 'Traffic Safety', 'Describe the traffic problems in your area and suggest solutions to improve road safety.', '120', 30, 'Khó', 1, '2025-08-23 11:42:10', 'admin'),
(3, 2, 1, 'Traditional Food', 'Write about a traditional dish from your country. Describe how it is prepared and why it is special.', '150-200', 30, 'Trung bình', 1, '2025-08-23 11:42:10', 'admin'),
(17, 1, 1, 'My Daily Routine', 'abc', '200', 50, 'Dễ', 1, '2025-08-23 19:33:38', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `writing_submissions`
--

CREATE TABLE `writing_submissions` (
  `MaBaiViet` int(11) NOT NULL,
  `MaDeBai` int(11) NOT NULL,
  `TaiKhoan` varchar(100) NOT NULL,
  `NoiDungBaiViet` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `SoTu` int(11) NOT NULL,
  `ThoiGianNop` datetime NOT NULL DEFAULT current_timestamp(),
  `TrangThaiCham` enum('Chưa chấm','Đã chấm') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Chưa chấm',
  `DiemSo` decimal(3,1) DEFAULT NULL,
  `DiemNguPhap` decimal(3,1) DEFAULT NULL,
  `DiemMachLac` decimal(3,1) DEFAULT NULL,
  `DiemTuVung` decimal(3,1) DEFAULT NULL,
  `NhanXet` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `NguoiCham` varchar(100) DEFAULT NULL,
  `ThoiGianCham` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `writing_submissions`
--

INSERT INTO `writing_submissions` (`MaBaiViet`, `MaDeBai`, `TaiKhoan`, `NoiDungBaiViet`, `SoTu`, `ThoiGianNop`, `TrangThaiCham`, `DiemSo`, `DiemNguPhap`, `DiemMachLac`, `DiemTuVung`, `NhanXet`, `NguoiCham`, `ThoiGianCham`) VALUES
(1, 2, 'admin', 'biến đổi của phương thức sản xuất ✅\n\nQuan hệ huyết thống ✅\n\nBảo đảm về pháp lý trong hôn nhân ✅\n\nKinh tế xã hội ✅\n\nCơ sở kinh tế - xã hội ✅\n\nTăng cường sự lãnh đạo của Đảng, nâng cao nhận thức của xã hội về xây dựng và phát triển gia đình Việt Nam ✅\n\nChủ yếu là đơn vị kinh tế sang chủ yếu là đơn vị tình cảm ✅\n\nMặt tư tưởng của tôn giáo ✅\n\nKinh tế hàng hóa ✅ (lặp lại câu số 2)\n\nTâm lý, tình cảm, kinh tế ✅\n\nTăng lên ✅\n\nPhát triển kinh tế - xã hội miền núi, vùng đồng bào các dân tộc thiểu số nhằm phát huy tiềm năng phát triển, từng bước khắc phục chênh lệch giữa các dân tộc ✅\n\nNguyên tắc giải quyết vấn đề tôn giáo trong thời kỳ quá độ lên chủ nghĩa xã hội ✅\n\nCơ sở chính trị ✅\n\nĐặc điểm tôn giáo ở Việt Nam ✅\n\nChế độ hôn nhân tiến bộ ✅\n\nChế độ tư hữu về tư liệu sản xuất ✅', 187, '2025-08-23 18:49:56', 'Đã chấm', 2.0, NULL, NULL, NULL, 'Test grading', NULL, '2025-08-23 19:42:29'),
(2, 17, 'admin', 'biến đổi của phương thức sản xuất ✅\n\nQuan hệ huyết thống ✅\n\nBảo đảm về pháp lý trong hôn nhân ✅\n\nKinh tế xã hội ✅\n\nCơ sở kinh tế - xã hội ✅\n\nTăng cường sự lãnh đạo của Đảng, nâng cao nhận thức của xã hội về xây dựng và phát triển gia đình Việt Nam ✅\n\nChủ yếu là đơn vị kinh tế sang chủ yếu là đơn vị tình cảm ✅\n\nMặt tư tưởng của tôn giáo ✅\n\nKinh tế hàng hóa ✅ (lặp lại câu số 2)\n\nTâm lý, tình cảm, kinh tế ✅\n\nTăng lên ✅\n\nPhát triển kinh tế - xã hội miền núi, vùng đồng bào các dân tộc thiểu số nhằm phát huy tiềm năng phát triển, từng bước khắc phục chênh lệch giữa các dân tộc ✅\n\nNguyên tắc giải quyết vấn đề tôn giáo trong thời kỳ quá độ lên chủ nghĩa xã hội ✅\n\nCơ sở chính trị ✅\n\nĐặc điểm tôn giáo ở Việt Nam ✅\nbiến đổi của phương thức sản xuất ✅\n\nQuan hệ huyết thống ✅\n\nBảo đảm về pháp lý trong hôn nhân ✅\n\nKinh tế xã hội ✅\n\nCơ sở kinh tế - xã hội ✅\n\nTăng cường sự lãnh đạo của Đảng, nâng cao nhận thức của xã hội về xây dựng và phát triển gia đình Việt Nam ✅\n\nChủ yếu là đơn vị kinh tế sang chủ yếu là đơn vị tình cảm ✅\n\nMặt tư tưởng của tôn giáo ✅\n\nKinh tế hàng hóa ✅ (lặp lại câu số 2)\n\nTâm lý, tình cảm, kinh tế ✅\n\nTăng lên ✅\n\nPhát triển kinh tế - xã hội miền núi, vùng đồng bào các dân tộc thiểu số nhằm phát huy tiềm năng phát triển, từng bước khắc phục chênh lệch giữa các dân tộc ✅\n\nNguyên tắc giải quyết vấn đề tôn giáo trong thời kỳ quá độ lên chủ nghĩa xã hội ✅\n\nCơ sở chính trị ✅\n\nĐặc điểm tôn giáo ở Việt Nam ✅\n', 340, '2025-08-23 19:46:00', 'Đã chấm', 5.0, NULL, NULL, NULL, 'hay lắm', NULL, '2025-08-23 19:46:25');

-- --------------------------------------------------------

--
-- Table structure for table `writing_topics`
--

CREATE TABLE `writing_topics` (
  `MaChuDe` int(11) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL,
  `TenChuDe` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `MoTa` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `TrangThai` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `writing_topics`
--

INSERT INTO `writing_topics` (`MaChuDe`, `MaKhoaHoc`, `TenChuDe`, `MoTa`, `TrangThai`, `ThoiGianTao`) VALUES
(1, 1, 'Traffic', 'Các đề bài viết về giao thông và an toàn giao thông', 1, '2025-08-23 11:42:10'),
(2, 1, 'Food', 'Các đề bài viết về ẩm thực và văn hóa ăn uống', 1, '2025-08-23 11:42:10'),
(3, 1, 'Education', 'Các đề bài viết về giáo dục và học tập', 1, '2025-08-23 11:42:10'),
(4, 1, 'Family', 'Các đề bài viết về gia đình và mối quan hệ gia đình', 1, '2025-08-23 11:42:10'),
(5, 1, 'Work', 'Các đề bài viết về công việc và nghề nghiệp', 1, '2025-08-23 11:42:10'),
(6, 1, 'Hobbie', 'Các đề bài viết về sở thích và thú vui cá nhân', 1, '2025-08-23 11:42:10'),
(7, 1, 'Technology', 'Các đề bài viết về công nghệ và tác động của công nghệ', 1, '2025-08-23 11:42:10'),
(8, 1, 'Activities', 'Các đề bài viết về các hoạt động và sự kiện', 1, '2025-08-23 11:42:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `baihoc`
--
ALTER TABLE `baihoc`
  ADD PRIMARY KEY (`MaBaiHoc`,`MaKhoaHoc`),
  ADD KEY `FK_khoahoc_baihoc` (`MaKhoaHoc`);

--
-- Indexes for table `boquatuvung`
--
ALTER TABLE `boquatuvung`
  ADD PRIMARY KEY (`TaiKhoan`,`MaTuVung`,`MaBaiHoc`,`MaKhoaHoc`),
  ADD KEY `FK_tuvung-baihoc-khoahoc_boquatuvung` (`MaTuVung`,`MaBaiHoc`,`MaKhoaHoc`);

--
-- Indexes for table `chatbot_room`
--
ALTER TABLE `chatbot_room`
  ADD PRIMARY KEY (`MaRoom`),
  ADD KEY `FK_taikhoan_chatbot_room` (`TaiKhoan`);

--
-- Indexes for table `dangkykhoahoc`
--
ALTER TABLE `dangkykhoahoc`
  ADD PRIMARY KEY (`TaiKhoan`,`MaKhoaHoc`),
  ADD KEY `FK_khoahoc_dangkykhoahoc` (`MaKhoaHoc`);

--
-- Indexes for table `danhgiakhoahoc`
--
ALTER TABLE `danhgiakhoahoc`
  ADD PRIMARY KEY (`TaiKhoan`,`MaKhoaHoc`),
  ADD KEY `FK_khoahoc_danhgia` (`MaKhoaHoc`);

--
-- Indexes for table `grammar_answers`
--
ALTER TABLE `grammar_answers`
  ADD PRIMARY KEY (`MaTraLoi`),
  ADD KEY `FK_grammar_answers_user` (`TaiKhoan`),
  ADD KEY `FK_grammar_answers_question` (`MaCauHoi`),
  ADD KEY `FK_grammar_answers_result` (`MaKetQua`);

--
-- Indexes for table `grammar_questions`
--
ALTER TABLE `grammar_questions`
  ADD PRIMARY KEY (`MaCauHoi`),
  ADD KEY `FK_grammar_questions_topic` (`MaChuDe`);

--
-- Indexes for table `grammar_results`
--
ALTER TABLE `grammar_results`
  ADD PRIMARY KEY (`MaKetQua`),
  ADD KEY `FK_grammar_results_user` (`TaiKhoan`),
  ADD KEY `FK_grammar_results_topic` (`MaChuDe`);

--
-- Indexes for table `grammar_topics`
--
ALTER TABLE `grammar_topics`
  ADD PRIMARY KEY (`MaChuDe`);

--
-- Indexes for table `hethong`
--
ALTER TABLE `hethong`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `hoatdong`
--
ALTER TABLE `hoatdong`
  ADD PRIMARY KEY (`MaHoatDong`),
  ADD KEY `FK_taikhoan_hoatdong` (`TaiKhoan`),
  ADD KEY `FK_loaihd_hoatdong` (`MaLoaiHoatDong`);

--
-- Indexes for table `hoctumoi`
--
ALTER TABLE `hoctumoi`
  ADD PRIMARY KEY (`TaiKhoan`,`Token`) USING BTREE;

--
-- Indexes for table `hoctuvung`
--
ALTER TABLE `hoctuvung`
  ADD PRIMARY KEY (`TaiKhoan`,`MaTuVung`,`MaBaiHoc`,`MaKhoaHoc`),
  ADD KEY `FK_tuvung-baihoc-khoahoc_hoctuvung` (`MaBaiHoc`,`MaKhoaHoc`,`MaTuVung`);

--
-- Indexes for table `khoahoc`
--
ALTER TABLE `khoahoc`
  ADD PRIMARY KEY (`MaKhoaHoc`),
  ADD KEY `FK_nguoitao_khoahoc` (`NguoiTao`);

--
-- Indexes for table `loaihoatdong`
--
ALTER TABLE `loaihoatdong`
  ADD PRIMARY KEY (`MaLoaiHoatDong`);

--
-- Indexes for table `message_chatbot_room`
--
ALTER TABLE `message_chatbot_room`
  ADD PRIMARY KEY (`MaTinNhan`),
  ADD KEY `FK_chatbot_room_maroom` (`MaRoom`);

--
-- Indexes for table `muctieuhoctap`
--
ALTER TABLE `muctieuhoctap`
  ADD PRIMARY KEY (`MaMucTieu`);

--
-- Indexes for table `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`TaiKhoan`),
  ADD KEY `FK_quyenhan_nguoidung` (`MaQuyenHan`),
  ADD KEY `FK_muctieu_nguoidung` (`MaMucTieu`);

--
-- Indexes for table `ontaploai1`
--
ALTER TABLE `ontaploai1`
  ADD PRIMARY KEY (`TaiKhoan`,`Token`),
  ADD KEY `fk_tuvung` (`MaTuVung`,`MaBaiHoc`,`MaKhoaHoc`);

--
-- Indexes for table `ontapsieutoctuvung`
--
ALTER TABLE `ontapsieutoctuvung`
  ADD PRIMARY KEY (`TaiKhoan`,`Token`);

--
-- Indexes for table `ontaptuvung`
--
ALTER TABLE `ontaptuvung`
  ADD PRIMARY KEY (`TaiKhoan`,`Token`);

--
-- Indexes for table `ontaptuvungkho`
--
ALTER TABLE `ontaptuvungkho`
  ADD PRIMARY KEY (`TaiKhoan`,`Token`);

--
-- Indexes for table `quyenhan`
--
ALTER TABLE `quyenhan`
  ADD PRIMARY KEY (`MaQuyenHan`);

--
-- Indexes for table `reading_lessons`
--
ALTER TABLE `reading_lessons`
  ADD PRIMARY KEY (`MaBaiDoc`),
  ADD KEY `FK_reading_baihoc` (`MaBaiHoc`,`MaKhoaHoc`);

--
-- Indexes for table `reading_questions`
--
ALTER TABLE `reading_questions`
  ADD PRIMARY KEY (`MaCauHoi`),
  ADD KEY `FK_reading_questions` (`MaBaiDoc`);

--
-- Indexes for table `reading_user_progress`
--
ALTER TABLE `reading_user_progress`
  ADD PRIMARY KEY (`TaiKhoan`,`MaBaiDoc`,`MaCauHoi`),
  ADD KEY `FK_reading_progress_lesson` (`MaBaiDoc`),
  ADD KEY `FK_reading_progress_question` (`MaCauHoi`);

--
-- Indexes for table `thongbaoemail`
--
ALTER TABLE `thongbaoemail`
  ADD PRIMARY KEY (`MaThongBao`),
  ADD KEY `FK_TAIKHOAN_thongbaoemail` (`TaiKhoan`);

--
-- Indexes for table `tuvung`
--
ALTER TABLE `tuvung`
  ADD PRIMARY KEY (`MaTuVung`,`MaBaiHoc`,`MaKhoaHoc`),
  ADD KEY `FK_baihockhoahoc_tuvung` (`MaBaiHoc`,`MaKhoaHoc`);

--
-- Indexes for table `vidu`
--
ALTER TABLE `vidu`
  ADD PRIMARY KEY (`MaViDu`,`MaTuVung`,`MaBaiHoc`,`MaKhoaHoc`),
  ADD KEY `FK_tuvung-baihoc_khoahoc_vidu` (`MaTuVung`,`MaBaiHoc`,`MaKhoaHoc`);

--
-- Indexes for table `writing_drafts`
--
ALTER TABLE `writing_drafts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_draft` (`prompt_id`,`user_account`);

--
-- Indexes for table `writing_prompts`
--
ALTER TABLE `writing_prompts`
  ADD PRIMARY KEY (`MaDeBai`),
  ADD KEY `FK_writing_prompts_topic` (`MaChuDe`),
  ADD KEY `FK_writing_prompts_course` (`MaKhoaHoc`);

--
-- Indexes for table `writing_submissions`
--
ALTER TABLE `writing_submissions`
  ADD PRIMARY KEY (`MaBaiViet`),
  ADD KEY `FK_writing_submissions_prompt` (`MaDeBai`),
  ADD KEY `FK_writing_submissions_user` (`TaiKhoan`);

--
-- Indexes for table `writing_topics`
--
ALTER TABLE `writing_topics`
  ADD PRIMARY KEY (`MaChuDe`),
  ADD KEY `FK_writing_topics_course` (`MaKhoaHoc`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chatbot_room`
--
ALTER TABLE `chatbot_room`
  MODIFY `MaRoom` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `grammar_answers`
--
ALTER TABLE `grammar_answers`
  MODIFY `MaTraLoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `grammar_questions`
--
ALTER TABLE `grammar_questions`
  MODIFY `MaCauHoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `grammar_results`
--
ALTER TABLE `grammar_results`
  MODIFY `MaKetQua` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `grammar_topics`
--
ALTER TABLE `grammar_topics`
  MODIFY `MaChuDe` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `hethong`
--
ALTER TABLE `hethong`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `hoatdong`
--
ALTER TABLE `hoatdong`
  MODIFY `MaHoatDong` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1190;

--
-- AUTO_INCREMENT for table `khoahoc`
--
ALTER TABLE `khoahoc`
  MODIFY `MaKhoaHoc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `loaihoatdong`
--
ALTER TABLE `loaihoatdong`
  MODIFY `MaLoaiHoatDong` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `message_chatbot_room`
--
ALTER TABLE `message_chatbot_room`
  MODIFY `MaTinNhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `muctieuhoctap`
--
ALTER TABLE `muctieuhoctap`
  MODIFY `MaMucTieu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quyenhan`
--
ALTER TABLE `quyenhan`
  MODIFY `MaQuyenHan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reading_lessons`
--
ALTER TABLE `reading_lessons`
  MODIFY `MaBaiDoc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reading_questions`
--
ALTER TABLE `reading_questions`
  MODIFY `MaCauHoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `thongbaoemail`
--
ALTER TABLE `thongbaoemail`
  MODIFY `MaThongBao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `writing_drafts`
--
ALTER TABLE `writing_drafts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `writing_prompts`
--
ALTER TABLE `writing_prompts`
  MODIFY `MaDeBai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `writing_submissions`
--
ALTER TABLE `writing_submissions`
  MODIFY `MaBaiViet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `writing_topics`
--
ALTER TABLE `writing_topics`
  MODIFY `MaChuDe` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `baihoc`
--
ALTER TABLE `baihoc`
  ADD CONSTRAINT `FK_khoahoc_baihoc` FOREIGN KEY (`MaKhoaHoc`) REFERENCES `khoahoc` (`MaKhoaHoc`);

--
-- Constraints for table `boquatuvung`
--
ALTER TABLE `boquatuvung`
  ADD CONSTRAINT `FK_taikhoan_boquatuvung` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`),
  ADD CONSTRAINT `FK_tuvung-baihoc-khoahoc_boquatuvung` FOREIGN KEY (`MaTuVung`,`MaBaiHoc`,`MaKhoaHoc`) REFERENCES `tuvung` (`MaTuVung`, `MaBaiHoc`, `MaKhoaHoc`);

--
-- Constraints for table `chatbot_room`
--
ALTER TABLE `chatbot_room`
  ADD CONSTRAINT `FK_taikhoan_chatbot_room` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`);

--
-- Constraints for table `dangkykhoahoc`
--
ALTER TABLE `dangkykhoahoc`
  ADD CONSTRAINT `FK_khoahoc_dangkykhoahoc` FOREIGN KEY (`MaKhoaHoc`) REFERENCES `khoahoc` (`MaKhoaHoc`),
  ADD CONSTRAINT `FK_taikhoan_dangkykhoahoc` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`);

--
-- Constraints for table `danhgiakhoahoc`
--
ALTER TABLE `danhgiakhoahoc`
  ADD CONSTRAINT `FK_khoahoc_danhgia` FOREIGN KEY (`MaKhoaHoc`) REFERENCES `khoahoc` (`MaKhoaHoc`),
  ADD CONSTRAINT `FK_taikhoan_danhgia` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`);

--
-- Constraints for table `grammar_answers`
--
ALTER TABLE `grammar_answers`
  ADD CONSTRAINT `FK_grammar_answers_question` FOREIGN KEY (`MaCauHoi`) REFERENCES `grammar_questions` (`MaCauHoi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_grammar_answers_result` FOREIGN KEY (`MaKetQua`) REFERENCES `grammar_results` (`MaKetQua`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_grammar_answers_user` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `grammar_questions`
--
ALTER TABLE `grammar_questions`
  ADD CONSTRAINT `FK_grammar_questions_topic` FOREIGN KEY (`MaChuDe`) REFERENCES `grammar_topics` (`MaChuDe`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `grammar_results`
--
ALTER TABLE `grammar_results`
  ADD CONSTRAINT `FK_grammar_results_topic` FOREIGN KEY (`MaChuDe`) REFERENCES `grammar_topics` (`MaChuDe`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_grammar_results_user` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hoatdong`
--
ALTER TABLE `hoatdong`
  ADD CONSTRAINT `FK_loaihd_hoatdong` FOREIGN KEY (`MaLoaiHoatDong`) REFERENCES `loaihoatdong` (`MaLoaiHoatDong`),
  ADD CONSTRAINT `FK_taikhoan_hoatdong` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`);

--
-- Constraints for table `hoctumoi`
--
ALTER TABLE `hoctumoi`
  ADD CONSTRAINT `FK_taikhoan_nguoidung` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`);

--
-- Constraints for table `hoctuvung`
--
ALTER TABLE `hoctuvung`
  ADD CONSTRAINT `FK_taikhoan_hoctuvung` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`),
  ADD CONSTRAINT `FK_tuvung-baihoc-khoahoc_hoctuvung` FOREIGN KEY (`MaBaiHoc`,`MaKhoaHoc`,`MaTuVung`) REFERENCES `tuvung` (`MaBaiHoc`, `MaKhoaHoc`, `MaTuVung`);

--
-- Constraints for table `khoahoc`
--
ALTER TABLE `khoahoc`
  ADD CONSTRAINT `FK_nguoitao_khoahoc` FOREIGN KEY (`NguoiTao`) REFERENCES `nguoidung` (`TaiKhoan`);

--
-- Constraints for table `message_chatbot_room`
--
ALTER TABLE `message_chatbot_room`
  ADD CONSTRAINT `FK_chatbot_room_maroom` FOREIGN KEY (`MaRoom`) REFERENCES `chatbot_room` (`MaRoom`);

--
-- Constraints for table `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD CONSTRAINT `FK_muctieu_nguoidung` FOREIGN KEY (`MaMucTieu`) REFERENCES `muctieuhoctap` (`MaMucTieu`),
  ADD CONSTRAINT `FK_quyenhan_nguoidung` FOREIGN KEY (`MaQuyenHan`) REFERENCES `quyenhan` (`MaQuyenHan`);

--
-- Constraints for table `ontaploai1`
--
ALTER TABLE `ontaploai1`
  ADD CONSTRAINT `fk_taikhoan` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`),
  ADD CONSTRAINT `fk_tuvung` FOREIGN KEY (`MaTuVung`,`MaBaiHoc`,`MaKhoaHoc`) REFERENCES `tuvung` (`MaTuVung`, `MaBaiHoc`, `MaKhoaHoc`);

--
-- Constraints for table `ontapsieutoctuvung`
--
ALTER TABLE `ontapsieutoctuvung`
  ADD CONSTRAINT `FK_taikhoan_ontapsieutoctuvung` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`);

--
-- Constraints for table `ontaptuvung`
--
ALTER TABLE `ontaptuvung`
  ADD CONSTRAINT `FK_taikhoan_ontaptuvung` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`);

--
-- Constraints for table `ontaptuvungkho`
--
ALTER TABLE `ontaptuvungkho`
  ADD CONSTRAINT `FK_taikhoan_ontaptuvungkho` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`);

--
-- Constraints for table `reading_lessons`
--
ALTER TABLE `reading_lessons`
  ADD CONSTRAINT `FK_reading_baihoc` FOREIGN KEY (`MaBaiHoc`,`MaKhoaHoc`) REFERENCES `baihoc` (`MaBaiHoc`, `MaKhoaHoc`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reading_questions`
--
ALTER TABLE `reading_questions`
  ADD CONSTRAINT `FK_reading_questions` FOREIGN KEY (`MaBaiDoc`) REFERENCES `reading_lessons` (`MaBaiDoc`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reading_user_progress`
--
ALTER TABLE `reading_user_progress`
  ADD CONSTRAINT `FK_reading_progress_lesson` FOREIGN KEY (`MaBaiDoc`) REFERENCES `reading_lessons` (`MaBaiDoc`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_reading_progress_question` FOREIGN KEY (`MaCauHoi`) REFERENCES `reading_questions` (`MaCauHoi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_reading_progress_user` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `thongbaoemail`
--
ALTER TABLE `thongbaoemail`
  ADD CONSTRAINT `FK_TAIKHOAN_thongbaoemail` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`);

--
-- Constraints for table `tuvung`
--
ALTER TABLE `tuvung`
  ADD CONSTRAINT `FK_baihockhoahoc_tuvung` FOREIGN KEY (`MaBaiHoc`,`MaKhoaHoc`) REFERENCES `baihoc` (`MaBaiHoc`, `MaKhoaHoc`);

--
-- Constraints for table `vidu`
--
ALTER TABLE `vidu`
  ADD CONSTRAINT `FK_tuvung-baihoc_khoahoc_vidu` FOREIGN KEY (`MaTuVung`,`MaBaiHoc`,`MaKhoaHoc`) REFERENCES `tuvung` (`MaTuVung`, `MaBaiHoc`, `MaKhoaHoc`);

--
-- Constraints for table `writing_prompts`
--
ALTER TABLE `writing_prompts`
  ADD CONSTRAINT `FK_writing_prompts_course` FOREIGN KEY (`MaKhoaHoc`) REFERENCES `khoahoc` (`MaKhoaHoc`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_writing_prompts_topic` FOREIGN KEY (`MaChuDe`) REFERENCES `writing_topics` (`MaChuDe`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `writing_submissions`
--
ALTER TABLE `writing_submissions`
  ADD CONSTRAINT `FK_writing_submissions_prompt` FOREIGN KEY (`MaDeBai`) REFERENCES `writing_prompts` (`MaDeBai`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_writing_submissions_user` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `writing_topics`
--
ALTER TABLE `writing_topics`
  ADD CONSTRAINT `FK_writing_topics_course` FOREIGN KEY (`MaKhoaHoc`) REFERENCES `khoahoc` (`MaKhoaHoc`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
