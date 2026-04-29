<?php
require_once __DIR__ . '/../../bootstrap.php';

$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id !== '') {
    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM ChiTietDonHang WHERE maDH='$id'");
        $conn->query("DELETE FROM DonHang WHERE maDH='$id'");
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
    }
}

header("Location: index.php");
exit;