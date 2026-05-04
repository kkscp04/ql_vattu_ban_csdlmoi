<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id !== '') {
    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM ChiTietKiemTra WHERE maBB='$id'");
        $conn->query("DELETE FROM BienBanKiemTra WHERE maBB='$id'");
        $conn->commit();
        flash_set('success', "Xoa bien ban kiem tra '$id' thanh cong.");
    } catch (Throwable $e) {
        $conn->rollback();
        error_log("[BienBanKiemTra-Delete] maBB={$id} :: " . $e->getMessage());
        flash_set('danger', "Xoa bien ban kiem tra '$id' that bai. Vui long thu lai.");
    }
} else {
    flash_set('warning', 'Khong tim thay ma bien ban can xoa.');
}
header("Location: index.php");
exit;
