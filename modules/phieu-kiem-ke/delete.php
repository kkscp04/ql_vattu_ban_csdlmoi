<?php
require_once __DIR__ . '/_common.php';

$id = trim($_GET['id'] ?? '');
$header = kk_fetch_header($conn, $id);
if (!$header) {
    flash_set('warning', 'Khong tim thay phieu kiem ke can xoa.');
    header('Location: index.php');
    exit;
}

if (($header['trangthai'] ?? '') === KK_STATUS_HOAN_THANH) {
    flash_set('danger', "Phieu kiem ke '$id' da hoan thanh, khong duoc xoa.");
    header('Location: index.php');
    exit;
}

$rows = kk_fetch_detail_rows($conn, $id);
$lots = array_column($rows, 'maLo');

$conn->begin_transaction();
try {
    if (($header['trangthai'] ?? '') === KK_STATUS_DANG_KIEM_KE) {
        inventory_unlock_kiemke_lots($conn, $lots);
    }
    db_prepare_execute($conn, "DELETE FROM ChiTietPhieuKiemKe WHERE maPKK = ?", 's', [$id])->close();
    db_prepare_execute($conn, "DELETE FROM PhieuKiemKe WHERE maPKK = ?", 's', [$id])->close();
    $conn->commit();
    flash_set('success', "Da xoa phieu kiem ke '$id'.");
} catch (Throwable $e) {
    $conn->rollback();
    error_log('[KiemKe-Delete] ' . $e->getMessage());
    flash_set('danger', "Xoa phieu kiem ke '$id' that bai.");
}

header('Location: index.php');
exit;

