<?php
require_once __DIR__ . '/../../bootstrap.php';

$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id !== '') {
    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM ChiTietPhieuNhap WHERE maPN='$id'");
        $conn->query("DELETE FROM PhieuNhap WHERE maPN='$id'");
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
    }
}

header("Location: index.php");
exit;