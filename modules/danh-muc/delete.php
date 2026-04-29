<?php
require_once __DIR__ . '/../../bootstrap.php';

$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id !== '') $conn->query("DELETE FROM DanhMuc WHERE maDM='$id'");

header("Location: index.php");
exit;