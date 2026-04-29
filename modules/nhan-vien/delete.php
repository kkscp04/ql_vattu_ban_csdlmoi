<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id !== '') {
    $conn->query("DELETE FROM NhanVien WHERE maNV='$id'");
}
header("Location: index.php");
exit;
