<?php

define('APP_ROOT', __DIR__);
define('BASE_URL', '/ql_vattu_ban_csdlmoi');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once APP_ROOT . '/config/connect.php';

// ─────────────────────────────────────────────────────────────────────────────
// URL & redirect helpers
// ─────────────────────────────────────────────────────────────────────────────
if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        $base = rtrim(BASE_URL, '/');
        if ($path === '') {
            return $base . '/';
        }
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect_to')) {
    function redirect_to(string $path): void
    {
        header('Location: ' . app_url($path));
        exit;
    }
}

if (!function_exists('flash_set')) {
    function flash_set(string $type, string $message): void
    {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('flash_get')) {
    function flash_get(): ?array
    {
        if (!isset($_SESSION['flash_message'])) {
            return null;
        }
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return is_array($flash) ? $flash : null;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Prepared-statement helpers
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Prepare, bind, execute a statement and return the stmt object.
 *
 * Usage:
 *   $stmt = db_prepare_execute($conn, "INSERT INTO t (a,b) VALUES (?,?)", "si", [$strVal, $intVal]);
 *
 * @param mysqli  $conn
 * @param string  $sql
 * @param string  $types  e.g. "ssidd" – one char per param (s=string, i=int, d=double, b=blob)
 * @param array   $params list of values in the same order as '?'
 * @return mysqli_stmt
 * @throws RuntimeException on prepare/execute failure (already handled by MYSQLI_REPORT_STRICT)
 */
function db_prepare_execute(mysqli $conn, string $sql, string $types, array $params): mysqli_stmt
{
    $stmt = $conn->prepare($sql);
    if ($params !== []) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

/**
 * Execute a SELECT prepared statement and return all rows as an associative array
 * indexed by a chosen key column.
 *
 * Usage:
 *   $map = db_fetch_keyed($conn, "SELECT maVatTu, maDVT FROM VatTu WHERE maVatTu IN ($ph)", "sss", $vtList, 'maVatTu');
 *
 * @param mysqli   $conn
 * @param string   $sql
 * @param string   $types
 * @param array    $params
 * @param string   $keyCol   Column to use as array key
 * @return array<string, array>
 */
function db_fetch_keyed(mysqli $conn, string $sql, string $types, array $params, string $keyCol): array
{
    $stmt = db_prepare_execute($conn, $sql, $types, $params);
    $result = $stmt->get_result();
    $map = [];
    while ($row = $result->fetch_assoc()) {
        $map[$row[$keyCol]] = $row;
    }
    $stmt->close();
    return $map;
}

/**
 * Build a placeholder string for IN-clause: "?,?,?"
 */
function db_placeholders(array $items): string
{
    return implode(',', array_fill(0, count($items), '?'));
}

/**
 * Build the types string for bind_param from an array of values.
 * Strings get 's', ints get 'i', floats get 'd'.
 */
function db_types(array $values): string
{
    $t = '';
    foreach ($values as $v) {
        if (is_int($v))
            $t .= 'i';
        elseif (is_float($v))
            $t .= 'd';
        else
            $t .= 's';
    }
    return $t;
}

/**
 * Fetch a single row via prepared SELECT. Returns assoc array or null.
 */
function db_fetch_one(mysqli $conn, string $sql, string $types, array $params): ?array
{
    $stmt = db_prepare_execute($conn, $sql, $types, $params);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/**
 * Check existence. Returns true if at least 1 row matches.
 */
function db_exists(mysqli $conn, string $sql, string $types, array $params): bool
{
    $stmt = db_prepare_execute($conn, $sql, $types, $params);
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

function db_table_has_column(mysqli $conn, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND LOWER(TABLE_NAME) = LOWER(?)
           AND LOWER(COLUMN_NAME) = LOWER(?)"
    );
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $cache[$key] = ((int) ($row['total'] ?? 0)) > 0;
    return $cache[$key];
}

function inventory_update_legacy_stock(mysqli $conn, string $maVatTu): void
{
    $stmt = $conn->prepare("SELECT COALESCE(SUM(soluong), 0) AS tong FROM LoHang WHERE maVatTu = ?");
    $stmt->bind_param('s', $maVatTu);
    $stmt->execute();
    $tong = (float) ($stmt->get_result()->fetch_assoc()['tong'] ?? 0);
    $stmt->close();

    $stmt = $conn->prepare("UPDATE VatTu SET soluong = ? WHERE maVatTu = ?");
    $stmt->bind_param('ds', $tong, $maVatTu);
    $stmt->execute();
    $stmt->close();
}

function inventory_update_lot_status(mysqli $conn, string $maLo): void
{
    $stmt = $conn->prepare("SELECT COALESCE(soluong, 0) AS soluong FROM LoHang WHERE maLo = ?");
    $stmt->bind_param('s', $maLo);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return;
    }

    $trangThai = ((float) $row['soluong'] > 0) ? 'CON_HANG' : 'HET_HANG';
    $stmt = $conn->prepare("UPDATE LoHang SET trangthai = ? WHERE maLo = ?");
    $stmt->bind_param('ss', $trangThai, $maLo);
    $stmt->execute();
    $stmt->close();
}

function inventory_recalculate_order_status(mysqli $conn, string $maDH): void
{
    $stmt = $conn->prepare(
        "SELECT
            COALESCE((SELECT SUM(soluong) FROM ChiTietDonHang WHERE maDH = ?), 0) AS tongDat,
            COALESCE((
                SELECT SUM(ctx.soluong)
                FROM ChiTietPhieuXuat ctx
                INNER JOIN PhieuXuat px ON px.maPX = ctx.maPX
                WHERE px.maDH = ?
            ), 0) AS tongXuat"
    );
    $stmt->bind_param('ss', $maDH, $maDH);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return;
    }

    $tongDat = (float) $row['tongDat'];
    $tongXuat = (float) $row['tongXuat'];

    if ($tongXuat <= 0) {
        $trangThai = 'DUYET';
    } elseif ($tongXuat < $tongDat) {
        $trangThai = 'XUAT_MOT_PHAN';
    } else {
        $trangThai = 'HOAN_TAT_XUAT';
    }

    $stmt = $conn->prepare("UPDATE DonHang SET trangthai = ? WHERE maDH = ?");
    $stmt->bind_param('ss', $trangThai, $maDH);
    $stmt->execute();
    $stmt->close();
}

function inventory_ensure_kiemke_schema(mysqli $conn): void
{
    static $done = false;
    if ($done) {
        return;
    }

    $changes = [
        ['ChiTietPhieuKiemKe', 'maLo', "ALTER TABLE ChiTietPhieuKiemKe ADD COLUMN maLo VARCHAR(50) NULL AFTER maPKK"],
        ['ChiTietPhieuKiemKe', 'soLuongHeThong', "ALTER TABLE ChiTietPhieuKiemKe ADD COLUMN soLuongHeThong DECIMAL(15,2) NULL AFTER maDVT"],
        ['ChiTietPhieuKiemKe', 'soLuongThucTe', "ALTER TABLE ChiTietPhieuKiemKe ADD COLUMN soLuongThucTe DECIMAL(15,2) NULL AFTER soLuongHeThong"],
        ['ChiTietPhieuKiemKe', 'chenhLech', "ALTER TABLE ChiTietPhieuKiemKe ADD COLUMN chenhLech DECIMAL(15,2) NULL AFTER soLuongThucTe"],
        ['ChiTietPhieuKiemKe', 'lydo', "ALTER TABLE ChiTietPhieuKiemKe ADD COLUMN lydo TEXT NULL AFTER chenhLech"],
        ['PhieuKiemKe', 'ngayhoanthanh', "ALTER TABLE PhieuKiemKe ADD COLUMN ngayhoanthanh DATETIME NULL AFTER trangthai"],
        ['LoHang', 'biKhoaKiemKe', "ALTER TABLE LoHang ADD COLUMN biKhoaKiemKe TINYINT(1) NOT NULL DEFAULT 0 AFTER trangthai"],
    ];

    foreach ($changes as [$table, $column, $sql]) {
        if (!db_table_has_column($conn, $table, $column)) {
            try {
                $conn->query($sql);
            } catch (mysqli_sql_exception $e) {
                if (stripos($e->getMessage(), 'Duplicate column name') === false) {
                    throw $e;
                }
            }
        }
    }

    // Bỏ khóa chính (maPKK, maVatTu) để cho phép cùng 1 phiếu kiểm kê có thể chứa nhiều lô của cùng 1 vật tư
    try {
        $conn->query("ALTER TABLE ChiTietPhieuKiemKe DROP PRIMARY KEY");
    } catch (Throwable $e) {}

    // Sửa kiểu dữ liệu cột soluong sang số thập phân để tránh lỗi data truncated in strict mode
    try {
        $conn->query("ALTER TABLE ChiTietPhieuKiemKe MODIFY COLUMN soluong DECIMAL(15,2) NULL");
    } catch (Throwable $e) {}

    $done = true;
}

function inventory_kiemke_status_options(): array
{
    return ['Dang kiem ke', 'Cho duyet', 'Hoan thanh'];
}

function inventory_lot_is_available_for_export(mysqli $conn): string
{
    inventory_ensure_kiemke_schema($conn);
    return db_table_has_column($conn, 'LoHang', 'biKhoaKiemKe')
        ? " AND COALESCE(l.biKhoaKiemKe, 0) = 0"
        : '';
}

function inventory_load_kiemke_lots(mysqli $conn): array
{
    inventory_ensure_kiemke_schema($conn);

    $sql = "SELECT l.maLo, l.maVatTu, l.soluong, l.dongia, l.trangthai,
                   COALESCE(l.biKhoaKiemKe, 0) AS biKhoaKiemKe,
                   v.tenVatTu, v.maDVT, dv.tenDVT
            FROM LoHang l
            INNER JOIN VatTu v ON v.maVatTu = l.maVatTu
            LEFT JOIN DonViTinh dv ON dv.maDVT = v.maDVT
            WHERE l.soluong >= 0
            ORDER BY l.maLo";
    $rs = $conn->query($sql);

    $rows = [];
    while ($row = $rs->fetch_assoc()) {
        $rows[] = [
            'maLo' => $row['maLo'],
            'maVatTu' => $row['maVatTu'],
            'tenVatTu' => $row['tenVatTu'] ?? '',
            'maDVT' => $row['maDVT'] ?? '',
            'tenDVT' => $row['tenDVT'] ?? '',
            'soLuongHeThong' => (float) ($row['soluong'] ?? 0),
            'donGia' => (float) ($row['dongia'] ?? 0),
            'trangthai' => $row['trangthai'] ?? '',
            'biKhoaKiemKe' => (int) ($row['biKhoaKiemKe'] ?? 0),
        ];
    }
    return $rows;
}

function inventory_lock_kiemke_lots(mysqli $conn, array $lotCodes): void
{
    inventory_ensure_kiemke_schema($conn);
    $lotCodes = array_values(array_unique(array_filter(array_map('trim', $lotCodes))));
    if ($lotCodes === [] || !db_table_has_column($conn, 'LoHang', 'biKhoaKiemKe')) {
        return;
    }

    $ph = db_placeholders($lotCodes);
    $types = str_repeat('s', count($lotCodes));
    $stmt = $conn->prepare("UPDATE LoHang SET biKhoaKiemKe = 1 WHERE maLo IN ($ph)");
    $stmt->bind_param($types, ...$lotCodes);
    $stmt->execute();
    $stmt->close();
}

function inventory_unlock_kiemke_lots(mysqli $conn, array $lotCodes): void
{
    inventory_ensure_kiemke_schema($conn);
    $lotCodes = array_values(array_unique(array_filter(array_map('trim', $lotCodes))));
    if ($lotCodes === [] || !db_table_has_column($conn, 'LoHang', 'biKhoaKiemKe')) {
        return;
    }

    $ph = db_placeholders($lotCodes);
    $types = str_repeat('s', count($lotCodes));
    $stmt = $conn->prepare("UPDATE LoHang SET biKhoaKiemKe = 0 WHERE maLo IN ($ph)");
    $stmt->bind_param($types, ...$lotCodes);
    $stmt->execute();
    $stmt->close();
}
