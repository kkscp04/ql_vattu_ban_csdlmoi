<?php
require_once __DIR__ . '/../../bootstrap.php';

$ma = $conn->real_escape_string($_GET['id'] ?? '');
if ($ma !== '') {
    $conn->query("DELETE FROM DonViTinh WHERE maDVT = '$ma'");
}

header("Location: index.php");
exit;