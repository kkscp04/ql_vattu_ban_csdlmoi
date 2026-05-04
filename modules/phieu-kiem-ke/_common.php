<?php
require_once __DIR__ . '/../../bootstrap.php';

inventory_ensure_kiemke_schema($conn);

const KK_STATUS_DANG_KIEM_KE = 'Dang kiem ke';
const KK_STATUS_CHO_DUYET = 'Cho duyet';
const KK_STATUS_HOAN_THANH = 'Hoan thanh';

function kk_statuses(): array
{
    return [
        KK_STATUS_DANG_KIEM_KE,
        KK_STATUS_CHO_DUYET,
        KK_STATUS_HOAN_THANH,
    ];
}

function kk_load_available_lots(mysqli $conn, array $includeLots = []): array
{
    $rows = inventory_load_kiemke_lots($conn);
    $includeMap = array_fill_keys($includeLots, true);

    return array_values(array_filter($rows, static function (array $row) use ($includeMap): bool {
        if (isset($includeMap[$row['maLo']])) {
            return true;
        }
        return (int) ($row['biKhoaKiemKe'] ?? 0) === 0;
    }));
}

function kk_rows_from_lots(array $lots): array
{
    $rows = [];
    foreach ($lots as $lot) {
        $systemQty = (float) ($lot['soLuongHeThong'] ?? 0);
        $rows[] = [
            'maLo' => $lot['maLo'],
            'maVatTu' => $lot['maVatTu'],
            'tenVatTu' => $lot['tenVatTu'],
            'maDVT' => $lot['maDVT'],
            'tenDVT' => $lot['tenDVT'],
            'soLuongHeThong' => $systemQty,
            'soLuongThucTe' => $systemQty,
            'chenhLech' => 0.0,
            'lydo' => '',
        ];
    }
    return $rows;
}

function kk_collect_post_rows(mysqli $conn, array $snapshotMap = []): array
{
    $lotCatalog = [];
    foreach (inventory_load_kiemke_lots($conn) as $lot) {
        $lotCatalog[$lot['maLo']] = $lot;
    }

    $maLoArr = $_POST['maLo'] ?? [];
    $soHeThongArr = $_POST['soLuongHeThong'] ?? [];
    $soThucTeArr = $_POST['soLuongThucTe'] ?? [];
    $lyDoArr = $_POST['lydo'] ?? [];

    $rows = [];
    for ($i = 0; $i < count($maLoArr); $i++) {
        $maLo = trim($maLoArr[$i] ?? '');
        $soThucTe = (float) ($soThucTeArr[$i] ?? 0);
        $lyDo = trim($lyDoArr[$i] ?? '');

        if ($maLo === '') {
            continue;
        }
        $catalog = $lotCatalog[$maLo] ?? null;
        $snapshotQty = array_key_exists($maLo, $snapshotMap)
            ? (float) $snapshotMap[$maLo]
            : (float) ($catalog['soLuongHeThong'] ?? ($soHeThongArr[$i] ?? 0));
        $row = [
            'maLo' => $maLo,
            'maVatTu' => $catalog['maVatTu'] ?? '',
            'tenVatTu' => $catalog['tenVatTu'] ?? '',
            'maDVT' => $catalog['maDVT'] ?? '',
            'tenDVT' => $catalog['tenDVT'] ?? '',
            'soLuongHeThong' => $snapshotQty,
            'soLuongThucTe' => $soThucTe,
            'chenhLech' => $soThucTe - $snapshotQty,
            'lydo' => $lyDo,
        ];

        if (isset($rows[$maLo])) {
            $rows[$maLo]['soLuongThucTe'] += $soThucTe;
            $rows[$maLo]['chenhLech'] = $rows[$maLo]['soLuongThucTe'] - $rows[$maLo]['soLuongHeThong'];
            if ($lyDo !== '') {
                $rows[$maLo]['lydo'] = trim($rows[$maLo]['lydo'] . ' | ' . $lyDo, ' |');
            }
            continue;
        }

        $rows[$maLo] = $row;
    }

    return array_values($rows);
}

function kk_validate(mysqli $conn, string $maPKK, string $maNV, string $status, array $rows, bool $isCreate, array $allowedLockedLots = []): array
{
    $errors = [];

    if ($maPKK === '') $errors[] = '[R01] Ma phieu kiem ke khong duoc de trong.';
    if ($maNV === '') $errors[] = '[R01] Nhan vien lap khong duoc de trong.';
    if (!in_array($status, kk_statuses(), true)) $errors[] = '[R07] Trang thai khong hop le.';

    if ($maPKK !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $maPKK)) {
        $errors[] = '[R02] Ma phieu kiem ke khong hop le.';
    }

    if ($isCreate && $maPKK !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $maPKK)) {
        if (db_exists($conn, "SELECT maPKK FROM PhieuKiemKe WHERE maPKK = ? LIMIT 1", 's', [$maPKK])) {
            $errors[] = "[R04] Ma phieu kiem ke '$maPKK' da ton tai.";
        }
    }

    if ($maNV !== '' && !db_exists($conn, "SELECT maNV FROM NhanVien WHERE maNV = ? LIMIT 1", 's', [$maNV])) {
        $errors[] = "[R06] Nhan vien '$maNV' khong ton tai.";
    }

    if ($rows === []) {
        $errors[] = '[R12] Phieu kiem ke phai co it nhat 1 lo hang.';
    }

    $availableLots = [];
    foreach (inventory_load_kiemke_lots($conn) as $lot) {
        $availableLots[$lot['maLo']] = $lot;
    }
    $allowedLockedMap = array_fill_keys($allowedLockedLots, true);

    foreach ($rows as $index => $row) {
        $line = $index + 1;
        $maLo = $row['maLo'];
        $lot = $availableLots[$maLo] ?? null;
        if ($lot === null) {
            $errors[] = "[R06] Lo '$maLo' khong ton tai.";
            continue;
        }
        if ((int) ($lot['biKhoaKiemKe'] ?? 0) === 1 && !isset($allowedLockedMap[$maLo])) {
            $errors[] = "[R20] Lo '$maLo' dang bi khoa boi phieu kiem ke khac.";
        }
        if ($row['soLuongHeThong'] < 0) {
            $errors[] = "[R09] So luong he thong dong $line phai >= 0.";
        }
        if ($row['soLuongThucTe'] < 0) {
            $errors[] = "[R09] So luong thuc te dong $line phai >= 0.";
        }
    }

    return $errors;
}

function kk_fetch_header(mysqli $conn, string $maPKK): ?array
{
    return db_fetch_one($conn, "SELECT * FROM PhieuKiemKe WHERE maPKK = ? LIMIT 1", 's', [$maPKK]);
}

function kk_fetch_detail_rows(mysqli $conn, string $maPKK): array
{
    $stmt = db_prepare_execute(
        $conn,
        "SELECT ct.*, v.tenVatTu, dv.tenDVT
         FROM ChiTietPhieuKiemKe ct
         LEFT JOIN VatTu v ON v.maVatTu = ct.maVatTu
         LEFT JOIN DonViTinh dv ON dv.maDVT = ct.maDVT
         WHERE ct.maPKK = ?
         ORDER BY ct.maLo, ct.maVatTu",
        's',
        [$maPKK]
    );
    $rows = [];
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $rows[] = [
            'maLo' => $row['maLo'] ?? '',
            'maVatTu' => $row['maVatTu'] ?? '',
            'tenVatTu' => $row['tenVatTu'] ?? '',
            'maDVT' => $row['maDVT'] ?? '',
            'tenDVT' => $row['tenDVT'] ?? '',
            'soLuongHeThong' => (float) ($row['soLuongHeThong'] ?? 0),
            'soLuongThucTe' => (float) ($row['soLuongThucTe'] ?? ($row['soluong'] ?? 0)),
            'chenhLech' => (float) ($row['chenhLech'] ?? 0),
            'lydo' => $row['lydo'] ?? ($row['ghichu'] ?? ''),
        ];
    }
    $stmt->close();
    return $rows;
}

function kk_save(mysqli $conn, string $maPKK, string $maNV, string $status, string $ghichu, array $rows, ?array $oldHeader = null, array $oldRows = []): void
{
    $isCreate = $oldHeader === null;
    $oldStatus = $oldHeader['trangthai'] ?? '';
    $oldLots = array_values(array_filter(array_map(static fn(array $row): string => trim($row['maLo'] ?? ''), $oldRows)));
    $newLots = array_values(array_filter(array_map(static fn(array $row): string => trim($row['maLo'] ?? ''), $rows)));

    $conn->begin_transaction();
    try {
        if ($isCreate) {
            $stmt = $conn->prepare(
                "INSERT INTO PhieuKiemKe (maPKK, maKho, maNV_Lap, thoigiankiemke, ghichu, trangthai, ngayhoanthanh)
                 VALUES (?, NULL, ?, NOW(), ?, ?, ?)"
            );
            $ngayHoanThanh = $status === KK_STATUS_HOAN_THANH ? date('Y-m-d H:i:s') : null;
            $stmt->bind_param('sssss', $maPKK, $maNV, $ghichu, $status, $ngayHoanThanh);
            $stmt->execute();
            $stmt->close();
        } else {
            if ($oldStatus === KK_STATUS_HOAN_THANH) {
                throw new RuntimeException("Phieu kiem ke '$maPKK' da hoan thanh, khong duoc sua.");
            }

            $ngayHoanThanh = $status === KK_STATUS_HOAN_THANH ? date('Y-m-d H:i:s') : null;
            $stmt = $conn->prepare(
                "UPDATE PhieuKiemKe
                 SET maNV_Lap = ?, ghichu = ?, trangthai = ?, ngayhoanthanh = ?
                 WHERE maPKK = ?"
            );
            $stmt->bind_param('sssss', $maNV, $ghichu, $status, $ngayHoanThanh, $maPKK);
            $stmt->execute();
            $stmt->close();

            db_prepare_execute($conn, "DELETE FROM ChiTietPhieuKiemKe WHERE maPKK = ?", 's', [$maPKK])->close();
        }

        $stmtDetail = $conn->prepare(
            "INSERT INTO ChiTietPhieuKiemKe
             (maPKK, maLo, maVatTu, maDVT, soLuongHeThong, soLuongThucTe, chenhLech, lydo, soluong, ghichu)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $uniqueVatTu = [];
        foreach ($rows as $row) {
            $uniqueVatTu[$row['maVatTu']] = true;
            $actualQty = $row['soLuongThucTe'];
            $ghiChu = $row['lydo'];
            $stmtDetail->bind_param(
                'ssssdddsds',
                $maPKK,
                $row['maLo'],
                $row['maVatTu'],
                $row['maDVT'],
                $row['soLuongHeThong'],
                $row['soLuongThucTe'],
                $row['chenhLech'],
                $row['lydo'],
                $actualQty,
                $ghiChu
            );
            $stmtDetail->execute();
        }
        $stmtDetail->close();

        if ($oldStatus === KK_STATUS_DANG_KIEM_KE) {
            inventory_unlock_kiemke_lots($conn, $oldLots);
        }

        if ($status === KK_STATUS_DANG_KIEM_KE) {
            inventory_lock_kiemke_lots($conn, $newLots);
        } else {
            inventory_unlock_kiemke_lots($conn, $newLots);
        }

        if ($status === KK_STATUS_HOAN_THANH) {
            $stmtLot = $conn->prepare("UPDATE LoHang SET soluong = ?, biKhoaKiemKe = 0 WHERE maLo = ?");
            foreach ($rows as $row) {
                $stmtLot->bind_param('ds', $row['soLuongThucTe'], $row['maLo']);
                $stmtLot->execute();
                inventory_update_lot_status($conn, $row['maLo']);
            }
            $stmtLot->close();

            foreach (array_keys($uniqueVatTu) as $maVatTu) {
                inventory_update_legacy_stock($conn, $maVatTu);
            }
        }

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
}
