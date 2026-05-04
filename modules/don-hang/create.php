<?php
require_once __DIR__ . '/../../bootstrap.php';

// ─────────────────────────────────────────────
// Validation (R01 R02 R04 R06 R09 R10 R12 R13 R14 R15 R16)
// ─────────────────────────────────────────────
function validate_donhang_create(
    mysqli $conn, string $maDH, string $maKH, string $maNV, string $maHD,
    string $ngay, float $coc, array $vt, array $sl, array $dg
): array {
    $errors = [];

    if ($maDH === '') $errors[] = '[R01] Mã đơn hàng không được để trống.';
    if ($maKH === '') $errors[] = '[R01] Khách hàng không được để trống.';
    if ($maNV === '') $errors[] = '[R01] Nhân viên lập không được để trống.';

    if ($maDH !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $maDH))
        $errors[] = '[R02] Mã đơn hàng chỉ gồm chữ, số, dấu . _ - và tối đa 50 ký tự.';

    // R04
    if ($maDH !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $maDH)) {
        if (db_exists($conn, "SELECT maDH FROM DonHang WHERE maDH = ? LIMIT 1", 's', [$maDH]))
            $errors[] = "[R04] Mã đơn hàng '$maDH' đã tồn tại.";
    }

    // R06 – FK
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

    // R12 / R13 / R14 / R09
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
// POST handler
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maDH      = trim($_POST['maDH']        ?? '');
    $maKH      = trim($_POST['maKH']        ?? '');
    $maHD      = trim($_POST['maHDong']     ?? '');
    $maNV      = trim($_POST['maNV_Lap']    ?? '');
    $ngay      = $_POST['ngaydathang']      ?? date('Y-m-d');
    $coc       = (float) ($_POST['tiendatcoc'] ?? 0);
    $ghichu    = trim($_POST['ghichu']      ?? '');
    $trangthai = trim($_POST['trangthai']   ?? 'Mới tạo');

    $vtArr    = $_POST['maVatTu']   ?? [];
    $slArr    = $_POST['soluong']   ?? [];
    $dgArr    = $_POST['dongia']    ?? [];
    $ghiCtArr = $_POST['ghichu_ct'] ?? [];

    $errors = validate_donhang_create($conn, $maDH, $maKH, $maNV, $maHD, $ngay, $coc, $vtArr, $slArr, $dgArr);

    if (empty($errors)) {
        // Build valid items
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

            // 1) INSERT header (tongtien = 0, sẽ UPDATE sau)
            $stmtDH = $conn->prepare(
                "INSERT INTO DonHang (maDH, maKH, maHDong, maNV_Lap, ngaydathang, tiendatcoc, tongtien, ghichu, trangthai)
                 VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)"
            );
            $stmtDH->bind_param('sssssdss',
                $maDH, $maKH, $maHDParam, $maNV, $ngayFmt, $coc, $ghichu, $trangthai
            );
            $stmtDH->execute();
            $stmtDH->close();

            // 2) INSERT chi tiết; R15: tính tổng server-side
            $stmtCT = $conn->prepare(
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
                $stmtCT->bind_param('ssidds',
                    $maDH, $maVTBind, $soLuong, $donGia, $thanhTien, $ghiCTBind
                );
                $stmtCT->execute();
            }
            $stmtCT->close();

            // R16 – tiendatcoc <= tongtien
            if ($coc > $tong) {
                throw new RuntimeException(
                    "[R16] Tiền đặt cọc ($coc) không được vượt quá tổng tiền ($tong)."
                );
            }

            // 3) UPDATE tổng tiền
            $stmtTong = $conn->prepare("UPDATE DonHang SET tongtien = ? WHERE maDH = ?");
            $stmtTong->bind_param('ds', $tong, $maDH);
            $stmtTong->execute();
            $stmtTong->close();

            $conn->commit();
            header("Location: index.php");
            exit;
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('[DonHang-Create] ' . $e->getMessage());
            // Expose R16 message to user (it's business logic, not a raw SQL error)
            $userMsg = str_starts_with($e->getMessage(), '[R16]')
                ? $e->getMessage()
                : 'Lỗi khi lưu đơn hàng. Vui lòng kiểm tra lại dữ liệu.';
            $errors[] = $userMsg;
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Thêm Đơn Hàng</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3 mb-3">
                <label>Mã DH <span class="text-danger">*</span></label>
                <input type="text" name="maDH" class="form-control"
                       value="<?= htmlspecialchars($_POST['maDH'] ?? '') ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label>Khách hàng <span class="text-danger">*</span></label>
                <select name="maKH" class="form-select" required>
                    <option value="">-- Chọn --</option>
                    <?php
                    $rs = $conn->query("SELECT maKH, tenKH FROM KhachHang ORDER BY tenKH");
                    $sel = $_POST['maKH'] ?? '';
                    while ($x = $rs->fetch_assoc()) {
                        $s = ($x['maKH'] === $sel) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maKH']) . "' $s>"
                            . htmlspecialchars($x['tenKH']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label>Hợp đồng</label>
                <select name="maHDong" class="form-select">
                    <option value="">-- Không chọn --</option>
                    <?php
                    $rs = $conn->query("SELECT maHDong FROM HopDong ORDER BY maHDong DESC");
                    $sel = $_POST['maHDong'] ?? '';
                    while ($x = $rs->fetch_assoc()) {
                        $s = ($x['maHDong'] === $sel) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maHDong']) . "' $s>"
                            . htmlspecialchars($x['maHDong']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label>Nhân viên lập <span class="text-danger">*</span></label>
                <select name="maNV_Lap" class="form-select" required>
                    <option value="">-- Chọn --</option>
                    <?php
                    $rs = $conn->query("SELECT maNV, hoten FROM NhanVien ORDER BY hoten");
                    $sel = $_POST['maNV_Lap'] ?? '';
                    while ($x = $rs->fetch_assoc()) {
                        $s = ($x['maNV'] === $sel) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maNV']) . "' $s>"
                            . htmlspecialchars($x['maNV'] . ' – ' . $x['hoten']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Ngày đặt</label>
                <input type="date" name="ngaydathang" class="form-control"
                       value="<?= htmlspecialchars($_POST['ngaydathang'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-3">
                <label>Tiền đặt cọc</label>
                <input type="number" name="tiendatcoc" class="form-control"
                       min="0" step="0.01" value="<?= htmlspecialchars($_POST['tiendatcoc'] ?? '0') ?>">
            </div>
            <div class="col-md-3">
                <label>Trạng thái</label>
                <input type="text" name="trangthai" class="form-control"
                       value="<?= htmlspecialchars($_POST['trangthai'] ?? 'Mới tạo') ?>">
            </div>
            <div class="col-md-3">
                <label>Ghi chú</label>
                <input type="text" name="ghichu" class="form-control"
                       value="<?= htmlspecialchars($_POST['ghichu'] ?? '') ?>">
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
                <tr>
                    <td>
                        <select name="maVatTu[]" class="form-select" required>
                            <option value="">-- Chọn --</option>
                            <?php
                            $vtRs = $conn->query(
                                "SELECT v.maVatTu, v.tenVatTu, d.tenDVT
                                 FROM VatTu v LEFT JOIN DonViTinh d ON v.maDVT = d.maDVT
                                 ORDER BY v.tenVatTu"
                            );
                            while ($x = $vtRs->fetch_assoc()) {
                                $unit = htmlspecialchars($x['tenDVT'] ?? '');
                                echo "<option value='" . htmlspecialchars($x['maVatTu']) . "'"
                                    . " data-unit='$unit'>"
                                    . htmlspecialchars($x['tenVatTu']) . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="soluong[]" class="form-control sl" min="1" required></td>
                    <td><input type="number" name="dongia[]" class="form-control dg" min="0" step="0.01" required></td>
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
        <button class="btn btn-success"><i class="fas fa-save"></i> Lưu</button>
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