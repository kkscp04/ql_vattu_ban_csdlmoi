<?php
ob_start();

// Bật strict mode: ném Exception thay vì warning ngầm
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli("localhost", "root", "", "banmoi");
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('[DB-CONNECT] ' . $e->getMessage());
    die("Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.");
}
// Removed closing tag to prevent whitespace leakage