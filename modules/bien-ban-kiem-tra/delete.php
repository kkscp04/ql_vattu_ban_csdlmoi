<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id !== '') {
    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM ChiTietKiemTra WHERE maBB='$id'");
        $conn->query("DELETE FROM BienBanKiemTra WHERE maBB='$id'");
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
    }
}
header("Location: index.php");
exit;
