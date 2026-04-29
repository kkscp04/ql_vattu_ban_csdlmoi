<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý vật tư</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
body {
    background: #f4f6f9;
    display: flex;
    min-height: 100vh;
    margin: 0;
}
.sidebar {
    width: 260px;
    background: #343a40;
    color: white;
    min-height: 100vh;
    padding-top: 20px;
}
.sidebar a,
.sidebar summary {
    color: #c2c7d0;
    padding: 12px 20px;
    display: block;
    text-decoration: none;
    font-size: 15px;
    transition: 0.3s;
    width: 100%;
    background: transparent;
    border: 0;
    text-align: left;
}
.sidebar a:hover,
.sidebar summary:hover,
.sidebar details[open] > summary {
    color: white;
    background: #494e53;
}
.sidebar .nav-header {
    padding: 10px 20px;
    font-size: 12px;
    font-weight: bold;
    color: #6c757d;
    text-transform: uppercase;
    margin-top: 15px;
    letter-spacing: 1px;
}
.sidebar .sidebar-brand {
    font-size: 20px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 20px;
    color: white;
    text-decoration: none;
    display: block;
    border-bottom: 1px solid #4f5962;
    padding-bottom: 15px;
}
.sidebar summary {
    list-style: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
}
.sidebar summary::-webkit-details-marker {
    display: none;
}
.sidebar .menu-toggle .left {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}
.sidebar .menu-toggle .arrow {
    transition: transform 0.3s ease;
}
.sidebar details[open] .menu-toggle .arrow {
    transform: rotate(180deg);
}
.sidebar .submenu a {
    padding-left: 48px;
    font-size: 14px;
    background: rgba(255,255,255,0.02);
}
.main-content {
    flex-grow: 1;
    padding: 20px;
    background: #f4f6f9;
    height: 100vh;
    overflow-y: auto;
}
.card {
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    border: none;
}
</style>

</head>

<body>

<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$isVatTuGroup = str_contains($currentPath, '/modules/vat-tu/')
    || str_contains($currentPath, '/modules/loai-vat-tu/')
    || str_contains($currentPath, '/modules/don-vi-tinh/')
    || str_contains($currentPath, '/modules/danh-muc/')
    || str_ends_with($currentPath, '/vattu.php')
    || str_ends_with($currentPath, '/them_vattu.php')
    || str_ends_with($currentPath, '/sua_vattu.php')
    || str_ends_with($currentPath, '/xoa_vattu.php')
    || str_ends_with($currentPath, '/danhmuc.php')
    || str_ends_with($currentPath, '/themdanhmuc.php')
    || str_ends_with($currentPath, '/sua_danhmuc.php')
    || str_ends_with($currentPath, '/xoa_danhmuc.php')
    || str_contains($currentPath, '/loaivt/')
    || str_contains($currentPath, '/donvitinh/');

$isKhoGroup = str_contains($currentPath, '/modules/phieu-nhap/')
    || str_contains($currentPath, '/modules/bien-ban-kiem-tra/')
    || str_ends_with($currentPath, '/phieunhap.php')
    || str_ends_with($currentPath, '/them_phieunhap.php')
    || str_ends_with($currentPath, '/sua_phieunhap.php')
    || str_ends_with($currentPath, '/xoa_phieunhap.php')
    || str_ends_with($currentPath, '/chi_tiet_phieunhap.php');

$isNhaCungCap = str_contains($currentPath, '/modules/nha-cung-cap/')
    || str_ends_with($currentPath, '/nhacungcap.php')
    || str_ends_with($currentPath, '/them_nhacungcap.php')
    || str_ends_with($currentPath, '/sua_nhacungcap.php')
    || str_ends_with($currentPath, '/xoa_nhacungcap.php');

$isNhanVien = str_contains($currentPath, '/modules/nhan-vien/')
    || str_ends_with($currentPath, '/nhanvien.php')
    || str_ends_with($currentPath, '/them_nhanvien.php')
    || str_ends_with($currentPath, '/sua_nhanvien.php')
    || str_ends_with($currentPath, '/xoa_nhanvien.php');

$isKhachHang = str_contains($currentPath, '/modules/khach-hang/')
    || str_ends_with($currentPath, '/khachhang.php')
    || str_ends_with($currentPath, '/them_khachhang.php')
    || str_ends_with($currentPath, '/sua_khachhang.php')
    || str_ends_with($currentPath, '/xoa_khachhang.php');

$isHopDong = str_contains($currentPath, '/modules/hop-dong/')
    || str_ends_with($currentPath, '/hopdong.php')
    || str_ends_with($currentPath, '/them_hopdong.php')
    || str_ends_with($currentPath, '/sua_hopdong.php')
    || str_ends_with($currentPath, '/xoa_hopdong.php')
    || str_ends_with($currentPath, '/chi_tiet_hopdong.php');

$isDonHang = str_contains($currentPath, '/modules/don-hang/')
    || str_ends_with($currentPath, '/donhang.php')
    || str_ends_with($currentPath, '/them_donhang.php')
    || str_ends_with($currentPath, '/sua_donhang.php')
    || str_ends_with($currentPath, '/xoa_donhang.php')
    || str_ends_with($currentPath, '/chi_tiet_donhang.php');

$isHoaDon = str_contains($currentPath, '/modules/hoa-don/')
    || str_ends_with($currentPath, '/hoadon.php')
    || str_ends_with($currentPath, '/them_hoadon.php')
    || str_ends_with($currentPath, '/sua_hoadon.php')
    || str_ends_with($currentPath, '/xoa_hoadon.php')
    || str_ends_with($currentPath, '/chi_tiet_hoadon.php');
?>

<div class="sidebar">
    <a href="<?= app_url() ?>" class="sidebar-brand"><i class="fas fa-boxes"></i> QL Vật Tư</a>

    <div class="nav-header">Menu</div>

    <details <?= $isVatTuGroup ? 'open' : '' ?>>
        <summary class="menu-toggle">
            <span class="left">
                <i class="fas fa-box"></i>
                <span>Quản lý vật tư</span>
            </span>
            <i class="fas fa-chevron-down arrow"></i>
        </summary>
        <div class="submenu">
            <a href="<?= app_url('modules/vat-tu/index.php') ?>">Vật tư</a>
            <a href="<?= app_url('modules/loai-vat-tu/index.php') ?>">Loại vật tư</a>
            <a href="<?= app_url('modules/don-vi-tinh/index.php') ?>">Đơn vị tính</a>
            <a href="<?= app_url('modules/danh-muc/index.php') ?>">Nhóm danh mục</a>
        </div>
    </details>

    <details <?= $isKhoGroup ? 'open' : '' ?>>
        <summary class="menu-toggle">
            <span class="left">
                <i class="fas fa-warehouse"></i>
                <span>Kho bãi</span>
            </span>
            <i class="fas fa-chevron-down arrow"></i>
        </summary>
        <div class="submenu">
            <a href="<?= app_url('modules/bien-ban-kiem-tra/index.php') ?>">Biên bản kiểm tra</a>
            <a href="<?= app_url('modules/phieu-nhap/index.php') ?>">Phiếu nhập</a>
        </div>
    </details>

    <a href="<?= app_url('modules/nha-cung-cap/index.php') ?>"<?= $isNhaCungCap ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-truck"></i> Nhà cung cấp</a>
    <a href="<?= app_url('modules/nhan-vien/index.php') ?>"<?= $isNhanVien ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-user-tie"></i> Nhân viên</a>
    <a href="<?= app_url('modules/khach-hang/index.php') ?>"<?= $isKhachHang ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-users"></i> Khách hàng</a>
    <a href="<?= app_url('modules/hop-dong/index.php') ?>"<?= $isHopDong ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-file-contract"></i> Hợp đồng</a>
    <a href="<?= app_url('modules/don-hang/index.php') ?>"<?= $isDonHang ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-shopping-cart"></i> Đơn hàng</a>
    <a href="<?= app_url('modules/hoa-don/index.php') ?>"<?= $isHoaDon ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-file-invoice-dollar"></i> Hóa đơn</a>
</div>

<div class="main-content">
    <div class="container-fluid">
