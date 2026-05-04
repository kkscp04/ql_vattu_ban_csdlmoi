<?php
require_once __DIR__ . '/../../bootstrap.php';

$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id !== '') {
    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM ChiTietDonHang WHERE maDH='$id'");
        $conn->query("DELETE FROM DonHang WHERE maDH='$id'");
        $conn->commit();
        flash_set('success', "Xoa don hang '$id' thanh cong.");
    } catch (Throwable $e) {
        $conn->rollback();
        error_log("[DonHang-Delete] maDH={$id} :: " . $e->getMessage());
        flash_set('danger', "Xoa don hang '$id' that bai. Vui long thu lai.");
    }
} else {
    flash_set('warning', 'Khong tim thay ma don hang can xoa.');
}

header("Location: index.php");
exit;
