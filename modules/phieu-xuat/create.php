<?php
require_once __DIR__ . '/../../bootstrap.php';

const PX_TYPES = ['DON_HANG', 'THANH_LY'];

function px_build_order_map(mysqli $conn): array
{
    $sql = "SELECT d.maDH, d.trangthai, c.maVatTu, c.soluong, c.dongia,
                   v.tenVatTu, dv.tenDVT
            FROM DonHang d
            INNER JOIN ChiTietDonHang c ON c.maDH = d.maDH
            INNER JOIN VatTu v ON v.maVatTu = c.maVatTu
            LEFT JOIN DonViTinh dv ON dv.maDVT = v.maDVT
            WHERE d.trangthai IN ('Đang xử lý')
            ORDER BY d.maDH DESC, v.tenVatTu";
    $rs = $conn->query($sql);

    $exported = [];
    $rsExport = $conn->query("
        SELECT dh.maDH, ctx.maVatTu, COALESCE(SUM(ctx.soluong), 0) AS daXuat
        FROM ChiTietPhieuXuat ctx
        INNER JOIN PhieuXuat px ON px.maPX = ctx.maPX
        INNER JOIN DonHang dh ON dh.maDH = px.maDH
        GROUP BY dh.maDH, ctx.maVatTu
    ");
    if ($rsExport) {
        while ($row = $rsExport->fetch_assoc()) {
            $exported[$row['maDH']][$row['maVatTu']] = (float) $row['daXuat'];
        }
    }

    $orders = [];
    while ($row = $rs->fetch_assoc()) {
        $daXuat = (float) ($exported[$row['maDH']][$row['maVatTu']] ?? 0);
        $conLai = (float) $row['soluong'] - $daXuat;
        if ($conLai <= 0) {
            continue;
        }
        if (!isset($orders[$row['maDH']])) {
            $orders[$row['maDH']] = [
                'maDH' => $row['maDH'],
                'trangthai' => $row['trangthai'],
                'items' => [],
            ];
        }
        $orders[$row['maDH']]['items'][$row['maVatTu']] = [
            'maVatTu' => $row['maVatTu'],
            'tenVatTu' => $row['tenVatTu'],
            'tenDVT' => $row['tenDVT'] ?? '',
            'dongia' => (float) $row['dongia'],
            'conLai' => $conLai,
        ];
    }

    return $orders;
}

function px_build_lot_map(mysqli $conn): array
{
    $lockFilter = inventory_lot_is_available_for_export($conn);
    $sql = "SELECT l.maLo, l.maVatTu, l.soluong, l.dongia, v.tenVatTu, dv.tenDVT
            FROM LoHang l
            INNER JOIN VatTu v ON v.maVatTu = l.maVatTu
            LEFT JOIN DonViTinh dv ON dv.maDVT = v.maDVT
            WHERE l.soluong > 0{$lockFilter}
            ORDER BY l.ngayNhap, l.maLo";
    $rs = $conn->query($sql);

    $lots = [];
    while ($row = $rs->fetch_assoc()) {
        $lots[$row['maVatTu']][] = [
            'maLo' => $row['maLo'],
            'soLuong' => (float) $row['soluong'],
            'donGia' => (float) $row['dongia'],
            'tenVatTu' => $row['tenVatTu'],
            'tenDVT' => $row['tenDVT'] ?? '',
        ];
    }
    return $lots;
}

function px_validate(
    mysqli $conn,
    array $orders,
    array $lots,
    string $maPX,
    string $maNV,
    string $loaiXuat,
    string $maDH,
    string $ngay,
    array $vtArr,
    array $loArr,
    array $slArr,
    array $dgArr
): array {
    $errors = [];

    if ($maPX === '') $errors[] = '[R01] Mã phiếu xuất không được để trống.';
    if ($maNV === '') $errors[] = '[R01] Nhân viên lập không được để trống.';
    if (!in_array($loaiXuat, PX_TYPES, true)) $errors[] = '[R07] Loại xuất không hợp lệ.';
    if ($maPX !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $maPX)) {
        $errors[] = '[R02] Mã phiếu xuất không hợp lệ.';
    }
    if ($maPX !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $maPX)
        && db_exists($conn, "SELECT maPX FROM PhieuXuat WHERE maPX = ? LIMIT 1", 's', [$maPX])) {
        $errors[] = "[R04] Mã phiếu xuất '$maPX' đã tồn tại.";
    }
    if ($maNV !== '' && !db_exists($conn, "SELECT maNV FROM NhanVien WHERE maNV = ? LIMIT 1", 's', [$maNV])) {
        $errors[] = "[R06] Nhân viên '$maNV' không tồn tại.";
    }
    if ($ngay !== '' && strtotime($ngay) === false) {
        $errors[] = '[R10] Ngày xuất không hợp lệ.';
    }

    if ($loaiXuat === 'DON_HANG') {
        if ($maDH === '') {
            $errors[] = '[R01] Xuất theo đơn hàng phải chọn đơn hàng.';
        } elseif (!isset($orders[$maDH])) {
            $errors[] = "[R06] Đơn hàng '$maDH' không tồn tại hoặc chưa được duyệt.";
        }
    }

    $validRows = 0;
    $sumByVatTu = [];
    $sumByLot = [];
    for ($i = 0; $i < count($vtArr); $i++) {
        $maVatTu = trim($vtArr[$i] ?? '');
        $maLo = trim($loArr[$i] ?? '');
        $soLuong = (float) ($slArr[$i] ?? 0);
        $donGia = (float) ($dgArr[$i] ?? 0);

        if ($maVatTu === '' && $maLo === '' && $soLuong <= 0) {
            continue;
        }

        $validRows++;
        if ($maVatTu === '') $errors[] = "[R01] Dòng " . ($i + 1) . " chưa chọn vật tư.";
        if ($maLo === '') $errors[] = "[R01] Dòng " . ($i + 1) . " chưa chọn mã lô.";
        if ($soLuong <= 0) $errors[] = "[R09] Số lượng dòng " . ($i + 1) . " phải > 0.";
        if ($loaiXuat === 'THANH_LY' && $donGia < 0) $errors[] = "[R09] Đơn giá dòng " . ($i + 1) . " phải >= 0.";

        if ($maVatTu !== '') {
            $sumByVatTu[$maVatTu] = ($sumByVatTu[$maVatTu] ?? 0) + $soLuong;
        }
        if ($maLo !== '') {
            $sumByLot[$maLo] = ($sumByLot[$maLo] ?? 0) + $soLuong;
        }
    }

    if ($validRows === 0) {
        $errors[] = '[R12] Phiếu xuất phải có ít nhất 1 dòng chi tiết hợp lệ.';
    }

    foreach ($sumByLot as $maLo => $soLuongXuat) {
        $found = null;
        foreach ($lots as $maVatTu => $rows) {
            foreach ($rows as $row) {
                if ($row['maLo'] === $maLo) {
                    $found = ['maVatTu' => $maVatTu, 'soLuong' => $row['soLuong']];
                    break 2;
                }
            }
        }
        if (!$found) {
            $errors[] = "[R06] Lô '$maLo' không tồn tại hoặc đã hết hàng.";
            continue;
        }
        if ($soLuongXuat > $found['soLuong']) {
            $errors[] = "[R20] Lô '$maLo' không đủ tồn để xuất.";
        }
    }

    for ($i = 0; $i < count($vtArr); $i++) {
        $maVatTu = trim($vtArr[$i] ?? '');
        $maLo = trim($loArr[$i] ?? '');
        if ($maVatTu === '' || $maLo === '') continue;

        $lotOk = false;
        foreach ($lots[$maVatTu] ?? [] as $lot) {
            if ($lot['maLo'] === $maLo) {
                $lotOk = true;
                break;
            }
        }
        if (!$lotOk) {
            $errors[] = "[R20] Lô '$maLo' không thuộc vật tư '$maVatTu'.";
        }
    }

    if ($loaiXuat === 'DON_HANG' && isset($orders[$maDH])) {
        foreach ($sumByVatTu as $maVatTu => $tongXuat) {
            $orderItem = $orders[$maDH]['items'][$maVatTu] ?? null;
            if (!$orderItem) {
                $errors[] = "[R20] Vật tư '$maVatTu' không có trong đơn hàng '$maDH'.";
                continue;
            }
            if ($tongXuat > $orderItem['conLai']) {
                $errors[] = "[R20] Vật tư '$maVatTu' xuất vượt số lượng còn lại của đơn hàng.";
            }
        }
    }

    return $errors;
}

$orders = px_build_order_map($conn);
$lots = px_build_lot_map($conn);
$allVatTus = [];
foreach ($lots as $maVatTu => $rows) {
    $first = $rows[0];
    $allVatTus[$maVatTu] = [
        'maVatTu' => $maVatTu,
        'tenVatTu' => $first['tenVatTu'],
        'tenDVT' => $first['tenDVT'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maPX = trim($_POST['maPX'] ?? '');
    $maNV = trim($_POST['maNV_Lap'] ?? '');
    $loaiXuat = trim($_POST['loaiXuat'] ?? 'DON_HANG');
    $maDH = trim($_POST['maDH'] ?? '');
    $ngay = $_POST['ngayxuat'] ?? date('Y-m-d');
    $ghiChu = trim($_POST['ghichu'] ?? '');

    $vtArr = $_POST['maVatTu'] ?? [];
    $loArr = $_POST['maLo'] ?? [];
    $slArr = $_POST['soluong'] ?? [];
    $dgArr = $_POST['dongiaxuat'] ?? [];
    $ghiCtArr = $_POST['ghichu_ct'] ?? [];

    $errors = px_validate($conn, $orders, $lots, $maPX, $maNV, $loaiXuat, $maDH, $ngay, $vtArr, $loArr, $slArr, $dgArr);

    if (empty($errors)) {
        $validItems = [];
        for ($i = 0; $i < count($vtArr); $i++) {
            $maVatTu = trim($vtArr[$i] ?? '');
            $maLo = trim($loArr[$i] ?? '');
            $soLuong = (float) ($slArr[$i] ?? 0);
            if ($maVatTu === '' || $maLo === '' || $soLuong <= 0) continue;

            $donGia = (float) ($dgArr[$i] ?? 0);
            if ($loaiXuat === 'DON_HANG') {
                $donGia = (float) ($orders[$maDH]['items'][$maVatTu]['dongia'] ?? 0);
            }

            $validItems[] = [
                'maVatTu' => $maVatTu,
                'maLo' => $maLo,
                'soLuong' => $soLuong,
                'donGia' => $donGia,
                'ghiCT' => trim($ghiCtArr[$i] ?? ''),
            ];
        }

        $uniqueVT = array_values(array_unique(array_column($validItems, 'maVatTu')));
        $ph = db_placeholders($uniqueVT);
        $types = str_repeat('s', count($uniqueVT));
        $dvtMap = db_fetch_keyed(
            $conn,
            "SELECT maVatTu, maDVT FROM VatTu WHERE maVatTu IN ($ph)",
            $types,
            $uniqueVT,
            'maVatTu'
        );

        $conn->begin_transaction();
        try {
            $ngayFmt = date('Y-m-d', strtotime($ngay));
            $maDHParam = $loaiXuat === 'DON_HANG' ? $maDH : null;
            $stmtPX = $conn->prepare(
                "INSERT INTO PhieuXuat (maPX, maDH, loaiXuat, maNV_Lap, ngayxuat, ghichu, tongtien)
                 VALUES (?, ?, ?, ?, ?, ?, 0)"
            );
            $stmtPX->bind_param('ssssss', $maPX, $maDHParam, $loaiXuat, $maNV, $ngayFmt, $ghiChu);
            $stmtPX->execute();
            $stmtPX->close();

            $stmtCT = $conn->prepare(
                "INSERT INTO ChiTietPhieuXuat
                 (maPX, maVatTu, maLo, maDVT, soluong, dongiaxuat, thanhtien, ghichu)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmtLot = $conn->prepare("UPDATE LoHang SET soluong = soluong - ? WHERE maLo = ?");

            $tong = 0.0;
            foreach ($validItems as $item) {
                $maDVT = $dvtMap[$item['maVatTu']]['maDVT'] ?? null;
                if ($maDVT === null) {
                    throw new RuntimeException("Không tìm thấy đơn vị tính của vật tư {$item['maVatTu']}.");
                }

                $thanhTien = $item['soLuong'] * $item['donGia'];
                $tong += $thanhTien;

                $stmtCT->bind_param(
                    'ssssddds',
                    $maPX,
                    $item['maVatTu'],
                    $item['maLo'],
                    $maDVT,
                    $item['soLuong'],
                    $item['donGia'],
                    $thanhTien,
                    $item['ghiCT']
                );
                $stmtCT->execute();

                $stmtLot->bind_param('ds', $item['soLuong'], $item['maLo']);
                $stmtLot->execute();
                inventory_update_lot_status($conn, $item['maLo']);
            }
            $stmtCT->close();
            $stmtLot->close();

            foreach ($uniqueVT as $maVatTu) {
                inventory_update_legacy_stock($conn, $maVatTu);
            }

            $stmtTong = $conn->prepare("UPDATE PhieuXuat SET tongtien = ? WHERE maPX = ?");
            $stmtTong->bind_param('ds', $tong, $maPX);
            $stmtTong->execute();
            $stmtTong->close();

            if ($loaiXuat === 'DON_HANG') {
                inventory_recalculate_order_status($conn, $maDH);
            }

            $conn->commit();
            header("Location: index.php");
            exit;
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('[PhieuXuat-Create] ' . $e->getMessage());
            $errors[] = 'Lỗi khi lưu phiếu xuất. Vui lòng kiểm tra lại dữ liệu.';
        }
    }
    $error = implode('<br>', $errors);
}

$orderJson = json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$lotJson = json_encode($lots, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$allVatTuJson = json_encode($allVatTus, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Tạo Phiếu Xuất</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Mã PX <span class="text-danger">*</span></label>
                <input type="text" name="maPX" class="form-control" value="<?= htmlspecialchars($_POST['maPX'] ?? '') ?>" required>
            </div>
            <div class="col-md-3">
                <label>Loại xuất <span class="text-danger">*</span></label>
                <?php $loaiHienTai = $_POST['loaiXuat'] ?? 'DON_HANG'; ?>
                <select name="loaiXuat" id="loaiXuat" class="form-select" required>
                    <?php foreach (PX_TYPES as $type): ?>
                        <option value="<?= $type ?>"<?= $loaiHienTai === $type ? ' selected' : '' ?>><?= $type ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 order-block">
                <label>Đơn hàng</label>
                <select name="maDH" id="maDH" class="form-select">
                    <option value="">-- Chọn --</option>
                    <?php $selDH = $_POST['maDH'] ?? ''; foreach ($orders as $order): ?>
                        <option value="<?= htmlspecialchars($order['maDH']) ?>"<?= $selDH === $order['maDH'] ? ' selected' : '' ?>><?= htmlspecialchars($order['maDH']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Nhân viên lập <span class="text-danger">*</span></label>
                <select name="maNV_Lap" class="form-select" required>
                    <option value="">-- Chọn --</option>
                    <?php
                    $nvRs = $conn->query("SELECT maNV, hoten FROM NhanVien ORDER BY hoten");
                    $selNV = $_POST['maNV_Lap'] ?? '';
                    while ($x = $nvRs->fetch_assoc()) {
                        $s = ($x['maNV'] === $selNV) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maNV']) . "' $s>" . htmlspecialchars($x['maNV'] . ' - ' . $x['hoten']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Ngày xuất</label>
                <input type="date" name="ngayxuat" class="form-control" value="<?= htmlspecialchars($_POST['ngayxuat'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-9">
                <label>Ghi chú</label>
                <input type="text" name="ghichu" class="form-control" value="<?= htmlspecialchars($_POST['ghichu'] ?? '') ?>">
            </div>
        </div>

        <table class="table table-bordered align-middle" id="tbl">
            <thead>
                <tr>
                    <th>Vật tư</th>
                    <th>Mã lô</th>
                    <th>ĐVT</th>
                    <th>Số lượng xuất</th>
                    <th>Đơn giá xuất</th>
                    <th>Thành tiền</th>
                    <th>Ghi chú CT</th>
                    <th class="text-center" style="width:90px;">Xóa</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><select name="maVatTu[]" class="form-select vt-select" required></select></td>
                    <td><select name="maLo[]" class="form-select lot-select" required></select></td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="soluong[]" class="form-control sl" min="0.01" step="0.01" required></td>
                    <td><input type="number" name="dongiaxuat[]" class="form-control dg" min="0" step="0.01" required></td>
                    <td><input class="form-control thanhtien" readonly></td>
                    <td><input type="text" name="ghichu_ct[]" class="form-control"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6" class="text-end">Tổng tiền</th>
                    <th><input id="tongTien" class="form-control fw-bold text-danger" readonly></th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>

        <button type="button" class="btn btn-info btn-sm text-white mb-3" onclick="addRow()">
            <i class="fas fa-plus"></i> Thêm dòng
        </button>



        <div class="mt-3">
            <button class="btn btn-success"><i class="fas fa-save"></i> Lưu</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
        </div>
    </form>
</div>

<script>
const orderData = <?= $orderJson ?>;
const lotData = <?= $lotJson ?>;
const allVatTuData = <?= $allVatTuJson ?>;
const tb = document.querySelector('#tbl tbody');
const tongTienEl = document.getElementById('tongTien');
const loaiXuatEl = document.getElementById('loaiXuat');
const maDHEl = document.getElementById('maDH');

function getCurrentItems() {
    if (loaiXuatEl.value === 'DON_HANG') {
        return orderData[maDHEl.value]?.items || {};
    }
    return allVatTuData;
}

function buildVatTuOptions(row) {
    const select = row.querySelector('.vt-select');
    const current = select.value;
    const items = getCurrentItems();
    select.innerHTML = '<option value="">-- Chọn --</option>';
    Object.values(items).forEach(item => {
        const option = document.createElement('option');
        option.value = item.maVatTu;
        option.textContent = loaiXuatEl.value === 'DON_HANG'
            ? `${item.tenVatTu} (còn ${item.conLai})`
            : item.tenVatTu;
        option.dataset.unit = item.tenDVT || '';
        option.dataset.price = item.dongia || '';
        option.dataset.remain = item.conLai || '';
        if (current === item.maVatTu) option.selected = true;
        select.appendChild(option);
    });
}

function buildLotOptions(row) {
    const vatTu = row.querySelector('.vt-select').value;
    const select = row.querySelector('.lot-select');
    const current = select.value;
    select.innerHTML = '<option value="">-- Chọn --</option>';
    (lotData[vatTu] || []).forEach(item => {
        const option = document.createElement('option');
        option.value = item.maLo;
        option.textContent = `${item.maLo} (tồn ${item.soLuong})`;
        if (current === item.maLo) option.selected = true;
        select.appendChild(option);
    });
}

function syncRow(row) {
    const select = row.querySelector('.vt-select');
    const option = select.options[select.selectedIndex];
    row.querySelector('.unit').value = option?.dataset.unit || '';
    const dg = row.querySelector('.dg');
    if (loaiXuatEl.value === 'DON_HANG') {
        dg.value = option?.dataset.price || '';
        dg.readOnly = true;
    } else {
        dg.readOnly = false;
    }
    buildLotOptions(row);
    calcRow(row);
}

function calcRow(row) {
    const sl = parseFloat(row.querySelector('.sl').value) || 0;
    const dg = parseFloat(row.querySelector('.dg').value) || 0;
    row.querySelector('.thanhtien').value = (sl * dg).toFixed(2);
    calcTotal();
}

function calcTotal() {
    let sum = 0;
    tb.querySelectorAll('.thanhtien').forEach(el => { sum += parseFloat(el.value) || 0; });
    tongTienEl.value = sum.toFixed(2);
}

function refreshRows() {
    document.querySelectorAll('#tbl tbody tr').forEach(row => {
        buildVatTuOptions(row);
        syncRow(row);
    });
}

tb.addEventListener('change', e => {
    if (e.target.matches('.vt-select')) syncRow(e.target.closest('tr'));
});

tb.addEventListener('input', e => {
    if (e.target.matches('.sl, .dg')) calcRow(e.target.closest('tr'));
});

function addRow() {
    const row = tb.rows[0].cloneNode(true);
    row.querySelectorAll('input').forEach(input => input.value = '');
    tb.appendChild(row);
    buildVatTuOptions(row);
    syncRow(row);
}

function delRow(button) {
    if (tb.rows.length > 1) {
        button.closest('tr').remove();
        calcTotal();
    }
}

function toggleBlocks() {
    const isThanhLy = loaiXuatEl.value === 'THANH_LY';
    document.querySelector('.order-block').style.display = isThanhLy ? 'none' : 'block';
    refreshRows();
}

loaiXuatEl.addEventListener('change', toggleBlocks);
maDHEl.addEventListener('change', refreshRows);

toggleBlocks();
</script>

</div></body></html>
