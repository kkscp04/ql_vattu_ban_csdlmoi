<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quan ly vat tu</title>

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
    || str_contains($currentPath, '/modules/danh-muc/');

$isKhoGroup = str_contains($currentPath, '/modules/phieu-nhap/')
    || str_contains($currentPath, '/modules/phieu-xuat/')
    || str_contains($currentPath, '/modules/ton-kho/')
    || str_contains($currentPath, '/modules/bien-ban-kiem-tra/')
    || str_contains($currentPath, '/modules/phieu-kiem-ke/');

$isNhaCungCap = str_contains($currentPath, '/modules/nha-cung-cap/');
$isNhanVien = str_contains($currentPath, '/modules/nhan-vien/');
$isKhachHang = str_contains($currentPath, '/modules/khach-hang/');
$isHopDong = str_contains($currentPath, '/modules/hop-dong/');
$isDonHang = str_contains($currentPath, '/modules/don-hang/');
$isHoaDon = str_contains($currentPath, '/modules/hoa-don/');

$isCongNoGroup = str_contains($currentPath, '/modules/cong-no-khach-hang/')
    || str_contains($currentPath, '/modules/cong-no-nha-cung-cap/');

$isBaoCao = str_contains($currentPath, '/modules/bao-cao-thong-ke/');
$isHome = str_ends_with($currentPath, '/home.php') || $currentPath === '/' || $currentPath === '';
?>

<div class="sidebar">
    <a href="<?= app_url('home.php') ?>" class="sidebar-brand"><i class="fas fa-boxes"></i> QL Vat Tu</a>

    <div class="nav-header">Menu</div>

    <details <?= $isVatTuGroup ? 'open' : '' ?>>
        <summary class="menu-toggle">
            <span class="left">
                <i class="fas fa-box"></i>
                <span>Quan ly vat tu</span>
            </span>
            <i class="fas fa-chevron-down arrow"></i>
        </summary>
        <div class="submenu">
            <a href="<?= app_url('modules/vat-tu/index.php') ?>">Vat tu</a>
            <a href="<?= app_url('modules/loai-vat-tu/index.php') ?>">Loai vat tu</a>
            <a href="<?= app_url('modules/don-vi-tinh/index.php') ?>">Don vi tinh</a>
            <a href="<?= app_url('modules/danh-muc/index.php') ?>">Nhom danh muc</a>
        </div>
    </details>

    <details <?= $isKhoGroup ? 'open' : '' ?>>
        <summary class="menu-toggle">
            <span class="left">
                <i class="fas fa-warehouse"></i>
                <span>Kho bai</span>
            </span>
            <i class="fas fa-chevron-down arrow"></i>
        </summary>
        <div class="submenu">
            <a href="<?= app_url('modules/bien-ban-kiem-tra/index.php') ?>">Bien ban kiem tra</a>
            <a href="<?= app_url('modules/phieu-kiem-ke/index.php') ?>">Phieu kiem kho</a>
            <a href="<?= app_url('modules/phieu-nhap/index.php') ?>">Phieu nhap</a>
            <a href="<?= app_url('modules/phieu-xuat/index.php') ?>">Phieu xuat</a>
            <a href="<?= app_url('modules/ton-kho/index.php') ?>">Ton kho</a>
        </div>
    </details>

    <a href="<?= app_url('modules/nha-cung-cap/index.php') ?>"<?= $isNhaCungCap ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-truck"></i> Nha cung cap</a>
    <a href="<?= app_url('modules/nhan-vien/index.php') ?>"<?= $isNhanVien ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-user-tie"></i> Nhan vien</a>
    <a href="<?= app_url('modules/khach-hang/index.php') ?>"<?= $isKhachHang ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-users"></i> Khach hang</a>
    <a href="<?= app_url('modules/hop-dong/index.php') ?>"<?= $isHopDong ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-file-contract"></i> Hop dong</a>
    <a href="<?= app_url('modules/don-hang/index.php') ?>"<?= $isDonHang ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-shopping-cart"></i> Don hang</a>
    <a href="<?= app_url('modules/hoa-don/index.php') ?>"<?= $isHoaDon ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-file-invoice"></i> Hoa don</a>

    <details <?= $isCongNoGroup ? 'open' : '' ?>>
        <summary class="menu-toggle">
            <span class="left">
                <i class="fas fa-hand-holding-usd"></i>
                <span>Quan ly cong no</span>
            </span>
            <i class="fas fa-chevron-down arrow"></i>
        </summary>
        <div class="submenu">
            <a href="<?= app_url('modules/cong-no-khach-hang/index.php') ?>">Cong no Khach hang</a>
            <a href="<?= app_url('modules/cong-no-nha-cung-cap/index.php') ?>">Cong no Nha cung cap</a>
        </div>
    </details>

    <a href="<?= app_url('modules/bao-cao-thong-ke/index.php') ?>"<?= $isBaoCao ? ' style="color: white; background: #494e53;"' : '' ?>><i class="fas fa-chart-pie"></i> Bao cao thong ke</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <?php $flash = flash_get(); ?>
        <?php if ($flash && !empty($flash['message'])): ?>
            <?php
                $type = strtolower((string) ($flash['type'] ?? 'info'));
                $alertClass = match ($type) {
                    'success' => 'alert-success',
                    'warning' => 'alert-warning',
                    'danger', 'error' => 'alert-danger',
                    default => 'alert-info',
                };
            ?>
            <div class="alert <?= $alertClass ?>" role="alert">
                <?= htmlspecialchars((string) $flash['message']) ?>
            </div>
        <?php endif; ?>

