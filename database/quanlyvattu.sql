-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 15, 2026 lúc 09:23 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quanlyvattu`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bienbankiemtra`
--

CREATE TABLE `bienbankiemtra` (
  `maBB` varchar(50) NOT NULL,
  `maNV` varchar(50) NOT NULL,
  `maNCC` varchar(50) NOT NULL,
  `daidienNCC` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `thoigianKT` datetime DEFAULT current_timestamp(),
  `diadiem` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `trangthai` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ghichu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdonhang`
--

CREATE TABLE `chitietdonhang` (
  `maDH` varchar(50) NOT NULL,
  `maVatTu` varchar(50) NOT NULL,
  `maLo` varchar(50) DEFAULT NULL,
  `soluong` int(11) DEFAULT NULL,
  `dongia` decimal(15,2) DEFAULT NULL,
  `thanhtien` decimal(15,2) DEFAULT NULL,
  `ghichu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietkiemtra`
--

CREATE TABLE `chitietkiemtra` (
  `maBB` varchar(50) NOT NULL,
  `maVatTu` varchar(50) NOT NULL,
  `maDVT` varchar(50) NOT NULL,
  `slGiao` int(11) DEFAULT 0,
  `slDat` int(11) DEFAULT 0,
  `slLoi` int(11) DEFAULT 0,
  `ketqua` bit(1) DEFAULT NULL,
  `phuonganxuly` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ghichuloi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietphieukiemke`
--

CREATE TABLE `chitietphieukiemke` (
  `maPKK` varchar(50) NOT NULL,
  `maVatTu` varchar(50) NOT NULL,
  `maDVT` varchar(50) DEFAULT NULL,
  `soluong` int(11) DEFAULT NULL,
  `dongia` decimal(15,2) DEFAULT NULL,
  `ghichu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietphieunhap`
--

CREATE TABLE `chitietphieunhap` (
  `maPN` varchar(50) NOT NULL,
  `maVatTu` varchar(50) NOT NULL,
  `maLo` varchar(50) DEFAULT NULL,
  `maDVT` varchar(50) DEFAULT NULL,
  `soluong` int(11) DEFAULT NULL,
  `dongianhap` decimal(15,2) DEFAULT NULL,
  `thanhtien` decimal(15,2) DEFAULT NULL,
  `ghichu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietphieuthanhly`
--

CREATE TABLE `chitietphieuthanhly` (
  `maPTL` varchar(50) NOT NULL,
  `maVatTu` varchar(50) NOT NULL,
  `maDVT` varchar(50) DEFAULT NULL,
  `soluong` int(11) DEFAULT NULL,
  `dongia` decimal(15,2) DEFAULT NULL,
  `thanhtien` decimal(15,2) DEFAULT NULL,
  `ghichu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietphieuxuat`
--

CREATE TABLE `chitietphieuxuat` (
  `maPX` varchar(50) NOT NULL,
  `maVatTu` varchar(50) NOT NULL,
  `maLo` varchar(50) DEFAULT NULL,
  `maDVT` varchar(50) DEFAULT NULL,
  `soluong` int(11) DEFAULT NULL,
  `dongiaxuat` decimal(15,2) DEFAULT NULL,
  `thanhtien` decimal(15,2) DEFAULT NULL,
  `ghichu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chucvu`
--

CREATE TABLE `chucvu` (
  `maCV` varchar(50) NOT NULL,
  `tenCV` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `nguoitao` varchar(50) DEFAULT NULL,
  `maPB` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `congnokh`
--

CREATE TABLE `congnokh` (
  `maCNKH` varchar(50) NOT NULL,
  `maKH` varchar(50) DEFAULT NULL,
  `maNV` varchar(50) DEFAULT NULL,
  `tongno` decimal(15,2) DEFAULT NULL,
  `tongtiendatra` decimal(15,2) DEFAULT NULL,
  `ghichu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `congnoncc`
--

CREATE TABLE `congnoncc` (
  `macongnoNCC` varchar(50) NOT NULL,
  `maNCC` varchar(50) DEFAULT NULL,
  `maNV` varchar(50) DEFAULT NULL,
  `ghichu` text DEFAULT NULL,
  `tongno` decimal(15,2) DEFAULT NULL,
  `tongtiendatra` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmuc`
--

CREATE TABLE `danhmuc` (
  `maDM` varchar(50) NOT NULL,
  `tenDM` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `mota` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `maDH` varchar(50) NOT NULL,
  `maKH` varchar(50) DEFAULT NULL,
  `maHDong` varchar(50) DEFAULT NULL,
  `maNV_Lap` varchar(50) DEFAULT NULL,
  `ngaydathang` date DEFAULT NULL,
  `tiendatcoc` decimal(15,2) DEFAULT NULL,
  `tongtien` decimal(15,2) DEFAULT NULL,
  `ghichu` text DEFAULT NULL,
  `trangthai` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donvitinh`
--

CREATE TABLE `donvitinh` (
  `maDVT` varchar(50) NOT NULL,
  `tenDVT` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoadon`
--

CREATE TABLE `hoadon` (
  `maHDon` varchar(50) NOT NULL,
  `maDH` varchar(50) DEFAULT NULL,
  `maCNKH` varchar(50) DEFAULT NULL,
  `maNV_Lap` varchar(50) DEFAULT NULL,
  `ngaytao` datetime DEFAULT NULL,
  `tongtientruocthue` decimal(15,2) DEFAULT NULL,
  `phuongthucthanhtoan` varchar(50) DEFAULT NULL,
  `trangthai` varchar(50) DEFAULT NULL,
  `diachi` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `thuevat` int(11) DEFAULT NULL,
  `tienthue` decimal(15,2) DEFAULT NULL,
  `tongtien` decimal(15,2) DEFAULT NULL,
  `ngaythanhtoan` datetime DEFAULT NULL,
  `sohoadon` varchar(50) DEFAULT NULL,
  `loaihoadon` varchar(50) DEFAULT NULL,
  `ghichu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hopdong`
--

CREATE TABLE `hopdong` (
  `maHDong` varchar(50) NOT NULL,
  `maKH` varchar(50) DEFAULT NULL,
  `maNV_Lap` varchar(50) DEFAULT NULL,
  `thoigiangiaohang` datetime DEFAULT NULL,
  `ngayky` datetime DEFAULT NULL,
  `thoihanthanhtoan` int(11) DEFAULT NULL,
  `tongtruocthue` decimal(15,2) DEFAULT NULL,
  `thue` decimal(15,2) DEFAULT NULL,
  `tonggiatriHopDong` decimal(15,2) DEFAULT NULL,
  `ngayhieuluc` datetime DEFAULT NULL,
  `trangthai` varchar(50) DEFAULT NULL,
  `ngayhethan` datetime DEFAULT NULL,
  `phuongthucthanhtoan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khachhang`
--

CREATE TABLE `khachhang` (
  `maKH` varchar(50) NOT NULL,
  `tenKH` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `loaiKH` varchar(50) DEFAULT NULL,
  `diachi` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `sdt` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khobai`
--

CREATE TABLE `khobai` (
  `maKho` varchar(50) NOT NULL,
  `tenKho` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `succhua` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loaivattu`
--

CREATE TABLE `loaivattu` (
  `maLoai` varchar(50) NOT NULL,
  `tenLoai` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `maDM` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lohang`
--

CREATE TABLE `lohang` (
  `maLo` varchar(50) NOT NULL,
  `maVatTu` varchar(50) DEFAULT NULL,
  `ngayNhap` datetime DEFAULT NULL,
  `ngaySX` datetime DEFAULT NULL,
  `hanSD` datetime DEFAULT NULL,
  `soluong` decimal(15,2) DEFAULT NULL,
  `dongia` decimal(15,2) DEFAULT NULL,
  `trangthai` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhacungcap`
--

CREATE TABLE `nhacungcap` (
  `maNCC` varchar(50) NOT NULL,
  `tenNCC` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `masothue` varchar(20) DEFAULT NULL,
  `nguoilienhe` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `diachi` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `stk` varchar(50) DEFAULT NULL,
  `trangthai` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhanvien`
--

CREATE TABLE `nhanvien` (
  `maNV` varchar(50) NOT NULL,
  `hoten` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `trangthai` varchar(50) DEFAULT NULL,
  `maCV` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieukiemke`
--

CREATE TABLE `phieukiemke` (
  `maPKK` varchar(50) NOT NULL,
  `maKho` varchar(50) DEFAULT NULL,
  `maNV_Lap` varchar(50) DEFAULT NULL,
  `thoigiankiemke` datetime DEFAULT NULL,
  `ghichu` text DEFAULT NULL,
  `trangthai` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieunhap`
--

CREATE TABLE `phieunhap` (
  `maPN` varchar(50) NOT NULL,
  `maNV_Lap` varchar(50) DEFAULT NULL,
  `maBB` varchar(50) DEFAULT NULL,
  `ngaynhap` datetime DEFAULT NULL,
  `ghichu` text DEFAULT NULL,
  `tongtien` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieuthanhly`
--

CREATE TABLE `phieuthanhly` (
  `maPTL` varchar(50) NOT NULL,
  `maKho` varchar(50) DEFAULT NULL,
  `maNV_Lap` varchar(50) DEFAULT NULL,
  `thoigianthanhly` datetime DEFAULT NULL,
  `ghichu` text DEFAULT NULL,
  `trangthai` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieuxuat`
--

CREATE TABLE `phieuxuat` (
  `maPX` varchar(50) NOT NULL,
  `maNV_Lap` varchar(50) DEFAULT NULL,
  `ngayxuat` datetime DEFAULT NULL,
  `ghichu` text DEFAULT NULL,
  `tongtien` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phongban`
--

CREATE TABLE `phongban` (
  `maPB` varchar(50) NOT NULL,
  `tenPB` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `soluong` int(11) DEFAULT 0,
  `trangthai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `taikhoan`
--

CREATE TABLE `taikhoan` (
  `maTK` varchar(50) NOT NULL,
  `maNV` varchar(50) NOT NULL,
  `maquyen` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `matkhau` varchar(255) NOT NULL,
  `maxacthuc` varchar(255) DEFAULT NULL,
  `trangthai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vattu`
--

CREATE TABLE `vattu` (
  `maVatTu` varchar(50) NOT NULL,
  `tenVatTu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `mota` text DEFAULT NULL,
  `trongluong` decimal(15,2) DEFAULT NULL,
  `gianhap` decimal(15,2) DEFAULT NULL,
  `soluong` int(11) DEFAULT NULL,
  `giaban` decimal(15,2) DEFAULT NULL,
  `maLoai` varchar(50) DEFAULT NULL,
  `maDVT` varchar(50) DEFAULT NULL,
  `maNV_QuanLy` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bienbankiemtra`
--
ALTER TABLE `bienbankiemtra`
  ADD PRIMARY KEY (`maBB`),
  ADD KEY `FK_BBKT_NhanVien` (`maNV`),
  ADD KEY `FK_BBKT_NhaCungCap` (`maNCC`);

--
-- Chỉ mục cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD PRIMARY KEY (`maDH`,`maVatTu`),
  ADD KEY `FK_CTDH_VT` (`maVatTu`),
  ADD KEY `FK_CTDH_Lo` (`maLo`);

--
-- Chỉ mục cho bảng `chitietkiemtra`
--
ALTER TABLE `chitietkiemtra`
  ADD PRIMARY KEY (`maBB`,`maVatTu`),
  ADD KEY `FK_CTKT_VatTu` (`maVatTu`),
  ADD KEY `FK_CTKT_DVT` (`maDVT`);

--
-- Chỉ mục cho bảng `chitietphieukiemke`
--
ALTER TABLE `chitietphieukiemke`
  ADD PRIMARY KEY (`maPKK`,`maVatTu`),
  ADD KEY `FK_CTPKK_VT` (`maVatTu`),
  ADD KEY `FK_CTPKK_DVT` (`maDVT`);

--
-- Chỉ mục cho bảng `chitietphieunhap`
--
ALTER TABLE `chitietphieunhap`
  ADD PRIMARY KEY (`maPN`,`maVatTu`),
  ADD KEY `FK_CTPN_VT` (`maVatTu`),
  ADD KEY `FK_CTPN_DVT` (`maDVT`),
  ADD KEY `FK_CTPN_Lo` (`maLo`);

--
-- Chỉ mục cho bảng `chitietphieuthanhly`
--
ALTER TABLE `chitietphieuthanhly`
  ADD PRIMARY KEY (`maPTL`,`maVatTu`),
  ADD KEY `FK_CTPTL_VT` (`maVatTu`),
  ADD KEY `FK_CTPTL_DVT` (`maDVT`);

--
-- Chỉ mục cho bảng `chitietphieuxuat`
--
ALTER TABLE `chitietphieuxuat`
  ADD PRIMARY KEY (`maPX`,`maVatTu`),
  ADD KEY `FK_CTPX_VT` (`maVatTu`),
  ADD KEY `FK_CTPX_DVT` (`maDVT`),
  ADD KEY `FK_CTPX_Lo` (`maLo`);

--
-- Chỉ mục cho bảng `chucvu`
--
ALTER TABLE `chucvu`
  ADD PRIMARY KEY (`maCV`),
  ADD KEY `FK_ChucVu_PhongBan` (`maPB`);

--
-- Chỉ mục cho bảng `congnokh`
--
ALTER TABLE `congnokh`
  ADD PRIMARY KEY (`maCNKH`),
  ADD KEY `FK_CNKH_KH` (`maKH`),
  ADD KEY `FK_CNKH_NV` (`maNV`);

--
-- Chỉ mục cho bảng `congnoncc`
--
ALTER TABLE `congnoncc`
  ADD PRIMARY KEY (`macongnoNCC`),
  ADD KEY `FK_CNNCC_NCC` (`maNCC`),
  ADD KEY `FK_CNNCC_NV` (`maNV`);

--
-- Chỉ mục cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  ADD PRIMARY KEY (`maDM`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`maDH`),
  ADD KEY `FK_DH_KhachHang` (`maKH`),
  ADD KEY `FK_DH_HopDong` (`maHDong`),
  ADD KEY `FK_DH_NhanVien` (`maNV_Lap`);

--
-- Chỉ mục cho bảng `donvitinh`
--
ALTER TABLE `donvitinh`
  ADD PRIMARY KEY (`maDVT`);

--
-- Chỉ mục cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  ADD PRIMARY KEY (`maHDon`),
  ADD KEY `FK_HDon_DonHang` (`maDH`),
  ADD KEY `FK_HDon_CongNoKH` (`maCNKH`),
  ADD KEY `FK_HDon_NhanVien` (`maNV_Lap`);

--
-- Chỉ mục cho bảng `hopdong`
--
ALTER TABLE `hopdong`
  ADD PRIMARY KEY (`maHDong`),
  ADD KEY `FK_HD_KhachHang` (`maKH`),
  ADD KEY `FK_HD_NhanVien` (`maNV_Lap`);

--
-- Chỉ mục cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  ADD PRIMARY KEY (`maKH`);

--
-- Chỉ mục cho bảng `khobai`
--
ALTER TABLE `khobai`
  ADD PRIMARY KEY (`maKho`);

--
-- Chỉ mục cho bảng `loaivattu`
--
ALTER TABLE `loaivattu`
  ADD PRIMARY KEY (`maLoai`),
  ADD KEY `FK_LoaiVT_DanhMuc` (`maDM`);

--
-- Chỉ mục cho bảng `lohang`
--
ALTER TABLE `lohang`
  ADD PRIMARY KEY (`maLo`),
  ADD KEY `FK_Lo_VatTu` (`maVatTu`);

--
-- Chỉ mục cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  ADD PRIMARY KEY (`maNCC`);

--
-- Chỉ mục cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD PRIMARY KEY (`maNV`),
  ADD KEY `FK_NhanVien_ChucVu` (`maCV`);

--
-- Chỉ mục cho bảng `phieukiemke`
--
ALTER TABLE `phieukiemke`
  ADD PRIMARY KEY (`maPKK`),
  ADD KEY `FK_PKK_Kho` (`maKho`),
  ADD KEY `FK_PKK_NhanVien` (`maNV_Lap`);

--
-- Chỉ mục cho bảng `phieunhap`
--
ALTER TABLE `phieunhap`
  ADD PRIMARY KEY (`maPN`),
  ADD KEY `FK_PN_NhanVien` (`maNV_Lap`),
  ADD KEY `FK_PN_BBKT` (`maBB`);

--
-- Chỉ mục cho bảng `phieuthanhly`
--
ALTER TABLE `phieuthanhly`
  ADD PRIMARY KEY (`maPTL`),
  ADD KEY `FK_PTL_Kho` (`maKho`),
  ADD KEY `FK_PTL_NhanVien` (`maNV_Lap`);

--
-- Chỉ mục cho bảng `phieuxuat`
--
ALTER TABLE `phieuxuat`
  ADD PRIMARY KEY (`maPX`),
  ADD KEY `FK_PX_NhanVien` (`maNV_Lap`);

--
-- Chỉ mục cho bảng `phongban`
--
ALTER TABLE `phongban`
  ADD PRIMARY KEY (`maPB`);

--
-- Chỉ mục cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD PRIMARY KEY (`maTK`),
  ADD UNIQUE KEY `maNV` (`maNV`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Chỉ mục cho bảng `vattu`
--
ALTER TABLE `vattu`
  ADD PRIMARY KEY (`maVatTu`),
  ADD KEY `FK_VT_Loai` (`maLoai`),
  ADD KEY `FK_VT_DVT` (`maDVT`),
  ADD KEY `FK_VT_NhanVien` (`maNV_QuanLy`);

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bienbankiemtra`
--
ALTER TABLE `bienbankiemtra`
  ADD CONSTRAINT `FK_BBKT_NhaCungCap` FOREIGN KEY (`maNCC`) REFERENCES `nhacungcap` (`maNCC`),
  ADD CONSTRAINT `FK_BBKT_NhanVien` FOREIGN KEY (`maNV`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD CONSTRAINT `FK_CTDH_DH` FOREIGN KEY (`maDH`) REFERENCES `donhang` (`maDH`),
  ADD CONSTRAINT `FK_CTDH_Lo` FOREIGN KEY (`maLo`) REFERENCES `lohang` (`maLo`),
  ADD CONSTRAINT `FK_CTDH_VT` FOREIGN KEY (`maVatTu`) REFERENCES `vattu` (`maVatTu`);

--
-- Các ràng buộc cho bảng `chitietkiemtra`
--
ALTER TABLE `chitietkiemtra`
  ADD CONSTRAINT `FK_CTKT_BienBan` FOREIGN KEY (`maBB`) REFERENCES `bienbankiemtra` (`maBB`),
  ADD CONSTRAINT `FK_CTKT_DVT` FOREIGN KEY (`maDVT`) REFERENCES `donvitinh` (`maDVT`),
  ADD CONSTRAINT `FK_CTKT_VatTu` FOREIGN KEY (`maVatTu`) REFERENCES `vattu` (`maVatTu`);

--
-- Các ràng buộc cho bảng `chitietphieukiemke`
--
ALTER TABLE `chitietphieukiemke`
  ADD CONSTRAINT `FK_CTPKK_DVT` FOREIGN KEY (`maDVT`) REFERENCES `donvitinh` (`maDVT`),
  ADD CONSTRAINT `FK_CTPKK_PKK` FOREIGN KEY (`maPKK`) REFERENCES `phieukiemke` (`maPKK`),
  ADD CONSTRAINT `FK_CTPKK_VT` FOREIGN KEY (`maVatTu`) REFERENCES `vattu` (`maVatTu`);

--
-- Các ràng buộc cho bảng `chitietphieunhap`
--
ALTER TABLE `chitietphieunhap`
  ADD CONSTRAINT `FK_CTPN_DVT` FOREIGN KEY (`maDVT`) REFERENCES `donvitinh` (`maDVT`),
  ADD CONSTRAINT `FK_CTPN_Lo` FOREIGN KEY (`maLo`) REFERENCES `lohang` (`maLo`),
  ADD CONSTRAINT `FK_CTPN_PN` FOREIGN KEY (`maPN`) REFERENCES `phieunhap` (`maPN`),
  ADD CONSTRAINT `FK_CTPN_VT` FOREIGN KEY (`maVatTu`) REFERENCES `vattu` (`maVatTu`);

--
-- Các ràng buộc cho bảng `chitietphieuthanhly`
--
ALTER TABLE `chitietphieuthanhly`
  ADD CONSTRAINT `FK_CTPTL_DVT` FOREIGN KEY (`maDVT`) REFERENCES `donvitinh` (`maDVT`),
  ADD CONSTRAINT `FK_CTPTL_PTL` FOREIGN KEY (`maPTL`) REFERENCES `phieuthanhly` (`maPTL`),
  ADD CONSTRAINT `FK_CTPTL_VT` FOREIGN KEY (`maVatTu`) REFERENCES `vattu` (`maVatTu`);

--
-- Các ràng buộc cho bảng `chitietphieuxuat`
--
ALTER TABLE `chitietphieuxuat`
  ADD CONSTRAINT `FK_CTPX_DVT` FOREIGN KEY (`maDVT`) REFERENCES `donvitinh` (`maDVT`),
  ADD CONSTRAINT `FK_CTPX_Lo` FOREIGN KEY (`maLo`) REFERENCES `lohang` (`maLo`),
  ADD CONSTRAINT `FK_CTPX_PX` FOREIGN KEY (`maPX`) REFERENCES `phieuxuat` (`maPX`),
  ADD CONSTRAINT `FK_CTPX_VT` FOREIGN KEY (`maVatTu`) REFERENCES `vattu` (`maVatTu`);

--
-- Các ràng buộc cho bảng `chucvu`
--
ALTER TABLE `chucvu`
  ADD CONSTRAINT `FK_ChucVu_PhongBan` FOREIGN KEY (`maPB`) REFERENCES `phongban` (`maPB`);

--
-- Các ràng buộc cho bảng `congnokh`
--
ALTER TABLE `congnokh`
  ADD CONSTRAINT `FK_CNKH_KH` FOREIGN KEY (`maKH`) REFERENCES `khachhang` (`maKH`),
  ADD CONSTRAINT `FK_CNKH_NV` FOREIGN KEY (`maNV`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `congnoncc`
--
ALTER TABLE `congnoncc`
  ADD CONSTRAINT `FK_CNNCC_NCC` FOREIGN KEY (`maNCC`) REFERENCES `nhacungcap` (`maNCC`),
  ADD CONSTRAINT `FK_CNNCC_NV` FOREIGN KEY (`maNV`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `FK_DH_HopDong` FOREIGN KEY (`maHDong`) REFERENCES `hopdong` (`maHDong`),
  ADD CONSTRAINT `FK_DH_KhachHang` FOREIGN KEY (`maKH`) REFERENCES `khachhang` (`maKH`),
  ADD CONSTRAINT `FK_DH_NhanVien` FOREIGN KEY (`maNV_Lap`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  ADD CONSTRAINT `FK_HDon_CongNoKH` FOREIGN KEY (`maCNKH`) REFERENCES `congnokh` (`maCNKH`),
  ADD CONSTRAINT `FK_HDon_DonHang` FOREIGN KEY (`maDH`) REFERENCES `donhang` (`maDH`),
  ADD CONSTRAINT `FK_HDon_NhanVien` FOREIGN KEY (`maNV_Lap`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `hopdong`
--
ALTER TABLE `hopdong`
  ADD CONSTRAINT `FK_HD_KhachHang` FOREIGN KEY (`maKH`) REFERENCES `khachhang` (`maKH`),
  ADD CONSTRAINT `FK_HD_NhanVien` FOREIGN KEY (`maNV_Lap`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `loaivattu`
--
ALTER TABLE `loaivattu`
  ADD CONSTRAINT `FK_LoaiVT_DanhMuc` FOREIGN KEY (`maDM`) REFERENCES `danhmuc` (`maDM`);

--
-- Các ràng buộc cho bảng `lohang`
--
ALTER TABLE `lohang`
  ADD CONSTRAINT `FK_Lo_VatTu` FOREIGN KEY (`maVatTu`) REFERENCES `vattu` (`maVatTu`);

--
-- Các ràng buộc cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD CONSTRAINT `FK_NhanVien_ChucVu` FOREIGN KEY (`maCV`) REFERENCES `chucvu` (`maCV`);

--
-- Các ràng buộc cho bảng `phieukiemke`
--
ALTER TABLE `phieukiemke`
  ADD CONSTRAINT `FK_PKK_Kho` FOREIGN KEY (`maKho`) REFERENCES `khobai` (`maKho`),
  ADD CONSTRAINT `FK_PKK_NhanVien` FOREIGN KEY (`maNV_Lap`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `phieunhap`
--
ALTER TABLE `phieunhap`
  ADD CONSTRAINT `FK_PN_BBKT` FOREIGN KEY (`maBB`) REFERENCES `bienbankiemtra` (`maBB`),
  ADD CONSTRAINT `FK_PN_NhanVien` FOREIGN KEY (`maNV_Lap`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `phieuthanhly`
--
ALTER TABLE `phieuthanhly`
  ADD CONSTRAINT `FK_PTL_Kho` FOREIGN KEY (`maKho`) REFERENCES `khobai` (`maKho`),
  ADD CONSTRAINT `FK_PTL_NhanVien` FOREIGN KEY (`maNV_Lap`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `phieuxuat`
--
ALTER TABLE `phieuxuat`
  ADD CONSTRAINT `FK_PX_NhanVien` FOREIGN KEY (`maNV_Lap`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD CONSTRAINT `FK_TaiKhoan_NhanVien` FOREIGN KEY (`maNV`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `vattu`
--
ALTER TABLE `vattu`
  ADD CONSTRAINT `FK_VT_DVT` FOREIGN KEY (`maDVT`) REFERENCES `donvitinh` (`maDVT`),
  ADD CONSTRAINT `FK_VT_Loai` FOREIGN KEY (`maLoai`) REFERENCES `loaivattu` (`maLoai`),
  ADD CONSTRAINT `FK_VT_NhanVien` FOREIGN KEY (`maNV_QuanLy`) REFERENCES `nhanvien` (`maNV`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
