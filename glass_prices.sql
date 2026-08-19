-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th8 19, 2026 lúc 03:14 AM
-- Phiên bản máy phục vụ: 10.6.23-MariaDB-log
-- Phiên bản PHP: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `gervin_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `glass_prices`
--

CREATE TABLE `glass_prices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `stt` varchar(255) DEFAULT NULL,
  `product_name` text DEFAULT NULL,
  `aluminum_color` varchar(255) DEFAULT NULL,
  `glass_color` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `price` double NOT NULL DEFAULT 0,
  `code` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `glass_prices`
--

INSERT INTO `glass_prices` (`id`, `category_name`, `stt`, `product_name`, `aluminum_color`, `glass_color`, `unit`, `price`, `code`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM VUÔNG ĐÚC LIỀN D', '1', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay cánh VUÔNG: đúc liền dài 200mm hoặc 1100mm', '1. Màu đen (Đ)', 'Trắng trong, Xanh đen', 'm2', 1200000, 'GK01', '- Đơn giá áp dụng với khổ kính dài dưới 2440mm.\n- Đối khổ kính dài hơn 2440mm sẽ báo giá riêng.\n- Đối với cánh diện tích dưới 0.35(m2)/1 cánh: tính tiền bằng cánh 0.35(m2).', '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(2, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM VUÔNG ĐÚC LIỀN D', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay cánh VUÔNG: đúc liền dài 200mm hoặc 1100mm', '1. Màu đen (Đ)', 'Xám khói, trà nhạt', 'm2', 1250000, 'GK02', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(3, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM VUÔNG ĐÚC LIỀN D', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay cánh VUÔNG: đúc liền dài 200mm hoặc 1100mm', '1. Màu đen (Đ)', 'Kính sóng to', 'm2', 1450000, 'GK03', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(4, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM VUÔNG ĐÚC LIỀN D', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay cánh VUÔNG: đúc liền dài 200mm hoặc 1100mm', '1. Màu đen (Đ)', 'Kính sóng nhỏ', 'm2', 1480000, 'GK04', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(5, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM VUÔNG ĐÚC LIỀN D', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay cánh VUÔNG: đúc liền dài 200mm hoặc 1100mm', '2. Màu vàng xước (V)\n3. Màu ghi xước (G)\n4. Màu coffee\n5. Vàng baby', 'Trắng trong, Xanh đen', 'm2', 1300000, 'GK05', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(6, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM VUÔNG ĐÚC LIỀN D', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay cánh VUÔNG: đúc liền dài 200mm hoặc 1100mm', '2. Màu vàng xước (V)\n3. Màu ghi xước (G)\n4. Màu coffee\n5. Vàng baby', 'Xám khói, trà nhạt', 'm2', 1350000, 'GK06', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(7, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM VUÔNG ĐÚC LIỀN D', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay cánh VUÔNG: đúc liền dài 200mm hoặc 1100mm', '2. Màu vàng xước (V)\n3. Màu ghi xước (G)\n4. Màu coffee\n5. Vàng baby', 'Kính sóng to', 'm2', 1550000, 'GK07', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(8, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM VUÔNG ĐÚC LIỀN D', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay cánh VUÔNG: đúc liền dài 200mm hoặc 1100mm', '2. Màu vàng xước (V)\n3. Màu ghi xước (G)\n4. Màu coffee\n5. Vàng baby', 'Kính sóng nhỏ', 'm2', 1580000, 'GK08', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(9, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 2,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP DÀI 200mm HOẶC 1100mm', '2', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 2.2mm.\nTay nắm VÁT CNC cao cấp: đúc liền dài 200mm hoặc 1100mm', '1. Màu đen (Đ)', 'Trắng trong, Xanh đen', 'm2', 1350000, 'GK09', '\'- Đơn giá áp dụng với khổ kính dài dưới 2440mm.\n- Đối khổ kính dài hơn 2440mm sẽ báo giá riêng.\n- Đối với cánh diện tích dưới 0.35(m2)/1 cánh: tính tiền bằng cánh 0.35(m2).', '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(10, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 2,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP DÀI 200mm HOẶC 1100mm', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 2.2mm.\nTay nắm VÁT CNC cao cấp: đúc liền dài 200mm hoặc 1100mm', '1. Màu đen (Đ)', 'Xám khói, màu trà', 'm2', 1400000, 'GK10', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(11, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 2,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP DÀI 200mm HOẶC 1100mm', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 2.2mm.\nTay nắm VÁT CNC cao cấp: đúc liền dài 200mm hoặc 1100mm', '1. Màu đen (Đ)', 'Kính sóng to', 'm2', 1600000, 'GK11', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(12, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 2,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP DÀI 200mm HOẶC 1100mm', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 2.2mm.\nTay nắm VÁT CNC cao cấp: đúc liền dài 200mm hoặc 1100mm', '1. Màu đen (Đ)', 'Kính sóng nhỏ', 'm2', 1680000, 'GK12', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(13, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 2,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP DÀI 200mm HOẶC 1100mm', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 2.2mm.\nTay nắm VÁT CNC cao cấp: đúc liền dài 200mm hoặc 1100mm', '2. Màu vàng xước (V)\n3. Màu ghi xước (G)\n4. Màu coffee\n5. Vàng baby', 'Trắng trong, Xanh đen', 'm2', 1450000, 'GK13', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(14, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 2,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP DÀI 200mm HOẶC 1100mm', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 2.2mm.\nTay nắm VÁT CNC cao cấp: đúc liền dài 200mm hoặc 1100mm', '2. Màu vàng xước (V)\n3. Màu ghi xước (G)\n4. Màu coffee\n5. Vàng baby', 'Xám khói, màu trà', 'm2', 1500000, 'GK14', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(15, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 2,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP DÀI 200mm HOẶC 1100mm', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 2.2mm.\nTay nắm VÁT CNC cao cấp: đúc liền dài 200mm hoặc 1100mm', '2. Màu vàng xước (V)\n3. Màu ghi xước (G)\n4. Màu coffee\n5. Vàng baby', 'Kính sóng to', 'm2', 1700000, 'GK15', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(16, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 2,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP DÀI 200mm HOẶC 1100mm', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 2.2mm.\nTay nắm VÁT CNC cao cấp: đúc liền dài 200mm hoặc 1100mm', '2. Màu vàng xước (V)\n3. Màu ghi xước (G)\n4. Màu coffee\n5. Vàng baby', 'Kính sóng nhỏ', 'm2', 1780000, 'GK16', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(17, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU - HỘC CẦU THANG DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM ĐÚC LIỀN DÀI SUỐT CÁNH', '3', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay dài liền suốt cánh', '1. Màu đen (Đ)', 'Trắng trong, Xanh đen', 'm2', 1300000, 'GK17', '- Đơn giá áp dụng với khổ kính dài dưới 2440mm.\n- Đối khổ kính dài hơn 2440mm sẽ báo giá riêng.\n- Đối với cánh diện tích dưới 0.35(m2)/1 cánh: tính tiền bằng cánh 0.35(m2).', '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(18, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU - HỘC CẦU THANG DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM ĐÚC LIỀN DÀI SUỐT CÁNH', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay dài liền suốt cánh', '1. Màu đen (Đ)', 'Xám khói, màu trà', 'm2', 1350000, 'GK18', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(19, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU - HỘC CẦU THANG DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM ĐÚC LIỀN DÀI SUỐT CÁNH', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay dài liền suốt cánh', '1. Màu đen (Đ)', 'Kính sóng to', 'm2', 1550000, 'GK19', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(20, 'CÁNH KÍNH TỦ ÁO - TỦ RƯỢU - HỘC CẦU THANG DẠNG MỞ BẢN NHỎ SLIM 21x22mm ĐỘ DÀY NHÔM 1,8mm, TAY NẮM ĐÚC LIỀN DÀI SUỐT CÁNH', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 21x22mm dày 1.8mm.\nTay dài liền suốt cánh', '1. Màu đen (Đ)', 'Kính sóng nhỏ', 'm2', 1580000, 'GK20', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(21, 'CÁNH KÍNH HỆ CỬA LÙA 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM ÂM (MÓC)', '4', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '1. Màu đen (Đ)', 'Trắng trong, Xanh đen', 'm2', 1300000, 'GK21', '- Đơn giá áp dụng với khổ kính dài dưới 2440mm.\n- Đối khổ kính dài hơn 2440mm sẽ báo giá riêng.\n- Đối với cánh diện tích dưới 0.35(m2)/1 cánh: tính tiền bằng cánh 0.35(m2).', '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(22, 'CÁNH KÍNH HỆ CỬA LÙA 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM ÂM (MÓC)', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '1. Màu đen (Đ)', 'Xám khói, màu trà', 'm2', 1350000, 'GK22', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(23, 'CÁNH KÍNH HỆ CỬA LÙA 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM ÂM (MÓC)', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '1. Màu đen (Đ)', 'Kính sóng to', 'm2', 1550000, 'GK23', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(24, 'CÁNH KÍNH HỆ CỬA LÙA 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM ÂM (MÓC)', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '1. Màu đen (Đ)', 'Kính sóng nhỏ', 'm2', 1580000, 'GK24', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(25, 'CÁNH KÍNH HỆ CỬA LÙA 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM ÂM (MÓC)', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Trắng trong, Xanh đen', 'm2', 1400000, 'GK25', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(26, 'CÁNH KÍNH HỆ CỬA LÙA 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM ÂM (MÓC)', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Xám khói, màu trà', 'm2', 1450000, 'GK26', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(27, 'CÁNH KÍNH HỆ CỬA LÙA 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM ÂM (MÓC)', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Kính sóng to', 'm2', 1650000, 'GK27', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(28, 'CÁNH KÍNH HỆ CỬA LÙA 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM ÂM (MÓC)', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Kính sóng nhỏ', 'm2', 1680000, 'GK28', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(29, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '5', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vát CNC cao cấp: đúc liền dài 300mm', '1. Màu đen (Đ)', 'Trắng trong, Xanh đen', 'm2', 1450000, 'GK29', '- Đơn giá áp dụng với khổ kính dài dưới 2440mm.\n- Đối khổ kính dài hơn 2440mm sẽ báo giá riêng.\n- Đối với cánh diện tích dưới 0.35(m2)/1 cánh: tính tiền bằng cánh 0.35(m2).', '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(30, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vát CNC cao cấp: đúc liền dài 300mm', '1. Màu đen (Đ)', 'Xám khói, màu trà', 'm2', 1500000, 'GK30', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(31, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vát CNC cao cấp: đúc liền dài 300mm', '1. Màu đen (Đ)', 'Kính sóng to', 'm2', 1650000, 'GK31', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(32, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vát CNC cao cấp: đúc liền dài 300mm', '1. Màu đen (Đ)', 'Kính sóng nhỏ', 'm2', 1680000, 'GK32', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(33, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vát CNC cao cấp: đúc liền dài 300mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Trắng trong, Xanh đen', 'm2', 1550000, 'GK33', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(34, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vát CNC cao cấp: đúc liền dài 300mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Xám khói, trà nhạt', 'm2', 1600000, 'GK34', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(35, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vát CNC cao cấp: đúc liền dài 300mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Kính sóng to', 'm2', 1750000, 'GK35', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(36, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực: 5mm\nKhung nhôm: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vát CNC cao cấp: đúc liền dài 300mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Kính sóng nhỏ', 'm2', 1780000, 'GK36', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(37, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '6', 'Kính cường lực phủ phim đủ màu dày 5mm\nKhung nhôm tràn viền: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '1. Màu đen (Đ)', 'Sơn kính các màu đơn sắc', 'm2', 1900000, 'GK37', '- Đơn giá áp dụng với khổ kính dài dưới 2440mm.\n- Đối khổ kính dài hơn 2440mm sẽ báo giá riêng.\n- Đối với cánh diện tích dưới 0.35(m2)/1 cánh: tính tiền bằng cánh 0.35(m2).', '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(38, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực phủ phim đủ màu dày 5mm\nKhung nhôm tràn viền: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '1. Màu đen (Đ)', 'Sơn kính các màu nhũ, màu kim sa', 'm2', 2050000, 'GK38', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(39, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực phủ phim đủ màu dày 5mm\nKhung nhôm tràn viền: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Sơn kính các màu đơn sắc', 'm2', 1950000, 'GK39', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(40, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực phủ phim đủ màu dày 5mm\nKhung nhôm tràn viền: Nhập khẩu 100%, công nghệ mạ Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vuông CNC cao cấp: đúc liền dài 200mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Sơn kính các màu nhũ, màu kim sa', 'm2', 2150000, 'GK40', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(41, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực phủ phim đủ màu dày 5mm\nKhung nhôm tràn viền: Nhập khẩu 100%, công nghệ mạ', '1. Màu đen (Đ)', 'Sơn kính các màu đơn sắc', 'm2', 2000000, 'GK41', '- Đơn giá áp dụng với khổ kính dài dưới 2440mm.\n- Đối khổ kính dài hơn 2440mm sẽ báo giá riêng.\n- Đối với cánh diện tích dưới 0.35(m2)/1 cánh: tính tiền bằng cánh 0.35(m2).', '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(42, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Kính cường lực phủ phim đủ màu dày 5mm\nKhung nhôm tràn viền: Nhập khẩu 100%, công nghệ mạ', '1. Màu đen (Đ)', 'Sơn kính các màu nhũ, màu kim sa', 'm2', 2150000, 'GK42', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(43, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '7', 'Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vát CNC cao cấp: đúc liền dài 300mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Sơn kính các màu đơn sắc', 'm2', 2100000, 'GK43', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(44, 'CÁNH KÍNH TỦ BẾP HỆ TRÀN VIỀN 20X50mm, ĐỘ DÀY NHÔM 1,2mm, TAY NẮM VÁT CNC ĐÚC LIỀN CAO CẤP', '', 'Anode hiện đại nhất thị trường hiện nay, định hình bản nhôm 20x50mm dày 1.2mm.\nTay nắm Vát CNC cao cấp: đúc liền dài 300mm', '2. Màu vàng hồng baby\n3. Vàng xước', 'Sơn kính các màu nhũ, màu kim sa', 'm2', 2250000, 'GK44', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(45, 'PHỤ KIỆN & PHỤ PHÍ', '8', 'Phụ phí, vật tư gia công cánh chéo cầu thang', NULL, '', 'cánh', 200000, 'PP1', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(46, 'PHỤ KIỆN & PHỤ PHÍ', '9', 'Bản lề chuyên dụng', 'Thép không rỉ', '', 'cái', 20000, 'PP2', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(47, 'PHỤ KIỆN & PHỤ PHÍ', '10', 'Bản lề âm 3D', 'Thép không rỉ', '', 'bộ', 1000000, 'PP3', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(48, 'PHỤ KIỆN & PHỤ PHÍ', '11', 'Khoá cửa kính bằng chìa cơ', NULL, '', 'bộ', 180000, 'PP4', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(49, 'PHỤ KIỆN & PHỤ PHÍ', '12', 'Khoá cửa kính bằng vân tay', NULL, '', 'bộ', 420000, 'PP5', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(50, 'PHỤ KIỆN & PHỤ PHÍ', '13', 'Bút che khuyết điểm', NULL, '', 'cái', 95000, 'PP6', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(51, 'PHỤ KIỆN & PHỤ PHÍ', '14', 'Bánh xe cửa lùa giảm chấn Hãng Cariny (4 bánh xe + 2\nthanh giảm chấn)', NULL, '', 'bộ', 560000, 'PP7', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(52, 'PHỤ KIỆN & PHỤ PHÍ', '15', 'Ray trên cửa lùa giảm chấn- zay âm Hãng Cariny -\n2m/3m', NULL, '', 'md', 110000, 'PP8', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(53, 'PHỤ KIỆN & PHỤ PHÍ', '16', 'Ray dưới cửa lùa giảm chấn- zay âm Hãng Cariny - 3m', NULL, '', 'md', 70000, 'PP9', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(54, 'PHỤ KIỆN & PHỤ PHÍ', '17', 'Bánh xe cửa lùa KHÔNG giảm chấnHãng Cariny (4\nbánh xe + 2 thanh ray)', NULL, '', 'bộ', 480000, 'PP10', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(55, 'PHỤ KIỆN & PHỤ PHÍ', '18', 'Ray cửa lùa KHÔNG giảm chấn- zay nổi Hãng Cariny -\n2m/3m', NULL, '', 'md', 70000, 'PP11', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02'),
(56, 'PHỤ KIỆN & PHỤ PHÍ', '19', 'Tay nắm móc', NULL, '', 'cái', 30000, 'PP12', NULL, '2026-06-26 04:15:02', '2026-06-26 04:15:02');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `glass_prices`
--
ALTER TABLE `glass_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `glass_prices_code_unique` (`code`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `glass_prices`
--
ALTER TABLE `glass_prices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
