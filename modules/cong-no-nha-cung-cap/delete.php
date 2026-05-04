<?php
require_once __DIR__ . '/../../bootstrap.php';

$id = trim($_GET['id'] ?? '');
if ($id === '') {
    flash_set('danger', 'Khong tim thay ma cong no.');
    header('Location: index.php');
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM congnoncc WHERE macongnoNCC = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        flash_set('success', "Da xoa cong no NCC '$id'.");
    } else {
        flash_set('warning', "Khong the xoa cong no hoac cong no khong ton tai.");
    }
    $stmt->close();
} catch (Throwable $e) {
    error_log('[CongNoNCC-Delete] ' . $e->getMessage());
    if (str_contains($e->getMessage(), 'foreign key constraint')) {
        flash_set('danger', "Khong the xoa cong no '$id' vi dang duoc su dung o bang khac.");
    } else {
        flash_set('danger', "Loi khi xoa cong no '$id'.");
    }
}

header('Location: index.php');
exit;
