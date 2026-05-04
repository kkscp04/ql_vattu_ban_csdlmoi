<?php
require_once __DIR__ . '/../../bootstrap.php';

// ─────────────────────────────────────────────
// Validation (R01 R06 R09 R10 R12 R13 R14)
// ─────────────────────────────────────────────
function validate_donhang_edit(
    mysqli $conn, string $maKH, string $maNV, string $maHD,
    string $ngay, float $coc, array $vt, array $sl, array $dg
): array {
    $errors = [];

    if ($maKH === '') $errors[] = '[R01] Khách hàng không được để trống.';
    if ($maNV === '') $errors[] = '[R01] Nhân viên lập không được để trống.';

    if ($maKH !== '' && !db_exists($conn, "SELECT maKH FROM KhachHang WHERE maKH = ? LIMIT 1", 's', [$maKH]))
        $errors[] = "[R06] Khách hàng '$maKH' không tồn tại.";
    if ($maNV !== '' && !db_exists($conn, "SELECT maNV FROM NhanVien WHERE maNV = ? LIMIT 1", 's', [$maNV]))
        $errors[] = "[R06] Nhân viên '$maNV' không tồn tại.";
    if ($maHD !== '' && !db_exists($conn, "SELECT maHDong FROM HopDong WHERE maHDong = ? LIMIT 1", 's', [$maHD]))
        $errors[] = "[R06] Hợp đồng '$maHD' không tồn tại.";

    if ($ngay !== '' && strtotime($ngay) === false)
        $errors[] = '[R10] Ngày đặt hàng không hợp lệ.';
    if (!is_finite($coc) || $coc < 0)
        $errors[] = '[R09] Tiền đặt cọc phải >= 0.';

    $validRows = 0;
    $seen      = [];
    for ($i = 0; $i < count($vt); $i++) {
        $maVT    = trim($vt[$i] ?? '');
        $soLuong = (int) ($sl[$i] ?? 0);
        $donGia  = (float) ($dg[$i] ?? 0);
        if ($maVT === '' || $soLuong <= 0) continue;

        $validRows++;
        if (in_array($maVT, $seen, true))
            $errors[] = "[R13] Vật tư '$maVT' bị trùng trong chi tiết.";
        else
            $seen[] = $maVT;
        if ($donGia < 0)
            $errors[] = "[R09] Đơn giá dòng $maVT phải >= 0.";
    }

    if ($validRows === 0)
        $errors[] = '[R12] Đơn hàng phải có ít nhất 1 dòng chi tiết hợp lệ.';

    // R14 – batch check
    if (!empty($seen)) {
        $ph    = db_placeholders($seen);
        $types = str_repeat('s', count($seen));
        $stmt  = $conn->prepare("SELECT maVatTu FROM VatTu WHERE maVatTu IN ($ph)");
        $stmt->bind_param($types, ...$seen);
        $stmt->execute();
        $res   = $stmt->get_result();
        $found = [];
        while ($r = $res->fetch_assoc()) $found[] = $r['maVatTu'];
        $stmt->close();
        foreach ($seen as $mv) {
            if (!in_array($mv, $found, true))
                $errors[] = "[R14] Vật tư '$mv' không tồn tại.";
        }
    }

    return $errors;
}

// ─────────────────────────────────────────────
// Load record
// ─────────────────────────────────────────────
$idRaw = $_GET['id'] ?? '';
$dh = db_fetch_one($conn, "SELECT * FROM DonHang WHERE maDH = ? LIMIT 1", 's', [$idRaw]);
if (!$dh) {
    echo "<div class='alert alert-danger m-4'>Không tìm thấy đơn hàng.</div>";
    exit;
}
$id = $dh['maDH'];

// Load detail rows
$stmtCT = db_prepare_execute($conn, "SELECT * FROM ChiTietDonHang WHERE maDH = ?", 's', [$id]);
$ct = [];
$resCT = $stmtCT->get_result();
while ($r = $resCT->fetch_assoc()) $ct[] = $r;
$stmtCT->close();

// ─────────────────────────────────────────────
// POST handler
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maKH      = trim($_POST['maKH']        ?? '');
    $maHD      = trim($_POST['maHDong']     ?? '');
    $maNV      = trim($_POST['maNV_Lap']    ?? '');
    $ngay      = $_POST['ngaydathang']      ?? date('Y-m-d');
    $coc       = (float) ($_POST['tiendatcoc'] ?? 0);
    $ghichu    = trim($_POST['ghichu']      ?? '');
    $trangthai = trim($_POST['trangthai']   ?? '');

    $vtArr    = $_POST['maVatTu']   ?? [];
    $slArr    = $_POST['soluong']   ?? [];
    $dgArr    = $_POST['dongia']    ?? [];
    $ghiCtArr = $_POST['ghichu_ct'] ?? [];

    $errors = validate_donhang_edit($conn, $maKH, $maNV, $maHD, $ngay, $coc, $vtArr, $slArr, $dgArr);

    if (empty($errors)) {
        $validItems = [];
        for ($i = 0; $i < count($vtArr); $i++) {
            $maVT    = trim($vtArr[$i] ?? '');
            $soLuong = (int) ($slArr[$i] ?? 0);
            $donGia  = (float) ($dgArr[$i] ?? 0);
            $ghiCT   = trim($ghiCtArr[$i] ?? '');
            if ($maVT !== '' && $soLuong > 0)
                $validItems[] = compact('maVT', 'soLuong', 'donGia', 'ghiCT');
        }

        $conn->begin_transaction();
        try {
            $ngayFmt   = date('Y-m-d', strtotime($ngay));
            $maHDParam = $maHD !== '' ? $maHD : null;

            // 1) UPDATE header
            $stmtUpd = $conn->prepare(
                "UPDATE DonHang
                 SET maKH = ?, maHDong = ?, maNV_Lap = ?,
                     ngaydathang = ?, tiendatcoc = ?, ghichu = ?, trangthai = ?
                 WHERE maDH = ?"
            );
            $stmtUpd->bind_param('ssssdsss',
                $maKH, $maHDParam, $maNV, $ngayFmt, $coc, $ghichu, $trangthai, $id
            );
            $stmtUpd->execute();
            $stmtUpd->close();

            // 2) Xóa chi tiết cũ
            db_prepare_execute($conn, "DELETE FROM ChiTietDonHang WHERE maDH = ?", 's', [$id]);

            // 3) INSERT chi tiết mới; R15: tính tổng server-side
            $stmtCTIns = $conn->prepare(
                "INSERT INTO ChiTietDonHang (maDH, maVatTu, maLo, soluong, dongia, thanhtien, ghichu)
                 VALUES (?, ?, NULL, ?, ?, ?, ?)"
            );
            $tong = 0.0;
            foreach ($validItems as $item) {
                $thanhTien  = $item['soLuong'] * $item['donGia'];
                $tong      += $thanhTien;
                $soLuong   = $item['soLuong'];
                $donGia    = $item['donGia'];
                $ghiCTBind = $item['ghiCT'];
                $maVTBind  = $item['maVT'];
                $stmtCTIns->bind_param('ssidds',
                    $id, $maVTBind, $soLuong, $donGia, $thanhTien, $ghiCTBind
                );
                $stmtCTIns->execute();
            }
            $stmtCTIns->close();

            // R16
            if ($coc > $tong) {
                throw new RuntimeException(
                    "[R16] Tiền đặt cọc ($coc) không được vượt quá tổng tiền ($tong)."
                );
            }

            // 4) UPDATE tổng tiền
            $stmtTong = $conn->prepare("UPDATE DonHang SET tongtien = ? WHERE maDH = ?");
            $stmtTong->bind_param('ds', $tong, $id);
            $stmtTong->execute();
            $stmtTong->close();

            $conn->commit();
            header("Location: index.php");
            exit;
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('[DonHang-Edit] ' . $e->getMessage());
            $userMsg = str_starts_with($e->getMessage(), '[R16]')
                ? $e->getMessage()
                : 'Lỗi khi cập nhật đơn hàng. Vui lòng kiểm tra lại dữ liệu.';
            $errors[] = $userMsg;
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Sửa Đơn Hàng #<?= htmlspecialchars($id) ?></h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Mã DH</label>
                <input class="form-control" value="<?= htmlspecialchars($id) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label>Khách hàng <span class="text-danger">*</span></label>
                <select name="maKH" class="form-select" required>
                    <?php
                    $rs = $conn->query("SELECT maKH, tenKH FROM KhachHang ORDER BY tenKH");
                    $sel = $_POST['maKH'] ?? $dh['maKH'];
                    while ($x = $rs->fetch_assoc()) {
                        $s = ($x['maKH'] === $sel) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maKH']) . "' $s>"
                            . htmlspecialchars($x['tenKH']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Hợp đồng</label>
                <select name="maHDong" class="form-select">
                    <option value="">-- Không chọn --</option>
                    <?php
                    $rs = $conn->query("SELECT maHDong FROM HopDong ORDER BY maHDong DESC");
                    $sel = $_POST['maHDong'] ?? $dh['maHDong'];
                    while ($x = $rs->fetch_assoc()) {
                        $s = ($x['maHDong'] === $sel) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maHDong']) . "' $s>"
                            . htmlspecialchars($x['maHDong']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Nhân viên lập <span class="text-danger">*</span></label>
                <select name="maNV_Lap" class="form-select" required>
                    <?php
                    $rs = $conn->query("SELECT maNV, hoten FROM NhanVien ORDER BY hoten");
                    $sel = $_POST['maNV_Lap'] ?? $dh['maNV_Lap'];
                    while ($x = $rs->fetch_assoc()) {
                        $s = ($x['maNV'] === $sel) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maNV']) . "' $s>"
                            . htmlspecialchars($x['maNV'] . ' – ' . $x['hoten']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 mt-3">
                <label>Ngày đặt</label>
                <input type="date" name="ngaydathang" class="form-control"
                       value="<?= !empty($dh['ngaydathang']) ? date('Y-m-d', strtotime($dh['ngaydathang'])) : date('Y-m-d') ?>">
            </div>
            <div class="col-md-3 mt-3">
                <label>Tiền đặt cọc</label>
                <input type="number" name="tiendatcoc" class="form-control"
                       min="0" step="0.01" value="<?= (float) $dh['tiendatcoc'] ?>">
            </div>
            <div class="col-md-3 mt-3">
                <label>Trạng thái</label>
                <input type="text" name="trangthai" class="form-control"
                       value="<?= htmlspecialchars($dh['trangthai']) ?>">
            </div>
            <div class="col-md-3 mt-3">
                <label>Ghi chú</label>
                <input type="text" name="ghichu" class="form-control"
                       value="<?= htmlspecialchars($dh['ghichu']) ?>">
            </div>
        </div>

        <table class="table table-bordered align-middle" id="tbl">
            <thead>
                <tr>
                    <th>Vật tư</th><th>ĐVT</th><th>Số lượng</th>
                    <th>Đơn giá</th><th>Thành tiền</th><th>Ghi chú CT</th>
                    <th class="text-center" style="width:90px;">Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Load VatTu list once
                $vtAllRows = $conn->query(
                    "SELECT v.maVatTu, v.tenVatTu, d.tenDVT
                     FROM VatTu v LEFT JOIN DonViTinh d ON v.maDVT = d.maDVT
                     ORDER BY v.tenVatTu"
                );
                $vtAll = [];
                while ($r = $vtAllRows->fetch_assoc()) $vtAll[] = $r;

                $rows = $ct ?: [['maVatTu' => '', 'soluong' => '', 'dongia' => '', 'ghichu' => '']];
                foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <select name="maVatTu[]" class="form-select" required>
                            <option value="">-- Chọn --</option>
                            <?php foreach ($vtAll as $x): ?>
                                <option value="<?= htmlspecialchars($x['maVatTu']) ?>"
                                        data-unit="<?= htmlspecialchars($x['tenDVT'] ?? '') ?>"
                                    <?= ($x['maVatTu'] === ($row['maVatTu'] ?? '')) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($x['tenVatTu']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="soluong[]" class="form-control sl"
                               min="1" value="<?= htmlspecialchars((string) ($row['soluong'] ?? '')) ?>" required></td>
                    <td><input type="number" name="dongia[]" class="form-control dg"
                               min="0" step="0.01" value="<?= htmlspecialchars((string) ($row['dongia'] ?? '')) ?>" required></td>
                    <td><input class="form-control thanhtien" readonly></td>
                    <td><input type="text" name="ghichu_ct[]" class="form-control"
                               value="<?= htmlspecialchars($row['ghichu'] ?? '') ?>"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Tổng tiền</th>
                    <th><input id="tongTien" class="form-control fw-bold text-danger" readonly></th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>

        <button type="button" class="btn btn-info btn-sm text-white mb-3" onclick="addRow()">
            <i class="fas fa-plus"></i> Thêm dòng
        </button>
        <br>
        <button class="btn btn-warning"><i class="fas fa-save"></i> Cập nhật</button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
    </form>
</div>

<script>
const tb = document.querySelector('#tbl tbody');
const tongTienEl = document.getElementById('tongTien');

function fillUnit(row) {
    const sel = row.querySelector('select[name="maVatTu[]"]');
    row.querySelector('.unit').value = sel.options[sel.selectedIndex]?.dataset.unit || '';
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
tb.addEventListener('change', e => {
    if (e.target.matches('select[name="maVatTu[]"]')) fillUnit(e.target.closest('tr'));
});
tb.addEventListener('input', e => {
    if (e.target.matches('.sl, .dg')) calcRow(e.target.closest('tr'));
});
function addRow() {
    const r = tb.rows[0].cloneNode(true);
    r.querySelectorAll('input').forEach(i => i.value = '');
    r.querySelector('select').selectedIndex = 0;
    tb.appendChild(r);
    calcTotal();
}
function delRow(b) {
    if (tb.rows.length > 1) { b.closest('tr').remove(); calcTotal(); }
}
document.querySelectorAll('#tbl tbody tr').forEach(r => { fillUnit(r); calcRow(r); });
</script>

</div></body></html>