<?php
ob_start();
$conn = new mysqli("localhost", "root", "", "banmoi");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
// Removed closing tag to prevent whitespace leakage