<?php
require_once __DIR__ . '/../../bootstrap.php';

// ─────────────────────────────────────────────
// Validation (R01 R02 R04 R06 R10 R12 R13 R14 R17)
// ─────────────────────────────────────────────
function validate_bbkt_create(
    mysqli $conn, string $maBB, string $maNV, string $maNCC,
    string $thoigianKT, array $vt, array $slGiao, array $slDat, array $slLoi
): array {
    $errors = [];

    if ($maBB === '')  $errors[] = '[R01] Mã biên bản không được để trống.';
    if ($maNV === '')  $errors[] = '[R01] Nhân viên không được để trống.';
    if ($maNCC === '') $errors[] = '[R01] Nhà cung cấp không được để trống.';

    if ($maBB !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $maBB))
        $errors[] = '[R02] Mã biên bản chỉ gồm chữ, số, dấu . _ - và tối đa 50 ký tự.';

    // R04
    if ($maBB !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $maBB)) {
        if (db_exists($conn, "SELECT maBB FROM BienBanKiemTra WHERE maBB = ? LIMIT 1", 's', [$maBB]))
            $errors[] = "[R04] Mã biên bản '$maBB' đã tồn tại.";
    }

    // R06
    if ($maNV !== '' && !db_exists($conn, "SELECT maNV FROM NhanVien WHERE maNV = ? LIMIT 1", 's', [$maNV]))
        $errors[] = "[R06] Nhân viên '$maNV' không tồn tại.";
    if ($maNCC !== '' && !db_exists($conn, "SELECT maNCC FROM NhaCungCap WHERE maNCC = ? LIMIT 1", 's', [$maNCC]))
        $errors[] = "[R06] Nhà cung cấp '$maNCC' không tồn tại.";

    // R10
    if ($thoigianKT !== '' && strtotime($thoigianKT) === false)
        $errors[] = '[R10] Thời gian kiểm tra không hợp lệ.';

    // R12 / R13 / R17
    $validRows = 0;
    $seen      = [];
    for ($i = 0; $i < count($vt); $i++) {
        $maVT = trim($vt[$i] ?? '');
        if ($maVT === '') continue;

        $validRows++;
        if (in_array($maVT, $seen, true))
            $errors[] = "[R13] Vật tư '$maVT' bị trùng trong chi tiết.";
        else
            $seen[] = $maVT;

        $sg = (int) ($slGiao[$i] ?? 0);
        $sd = (int) ($slDat[$i]  ?? 0);
        $sl = (int) ($slLoi[$i]  ?? 0);
        if ($sg < 0) $errors[] = "[R17] SL giao dòng $maVT phải >= 0.";
        if ($sd < 0) $errors[] = "[R17] SL đạt dòng $maVT phải >= 0.";
        if ($sl < 0) $errors[] = "[R17] SL lỗi dòng $maVT phải >= 0.";
        if (($sd + $sl) > $sg)
            $errors[] = "[R17] Dòng $maVT: (SL đạt + SL lỗi) vượt quá SL giao ($sg).";
    }
    if ($validRows === 0)
        $errors[] = '[R12] Biên bản phải có ít nhất 1 dòng chi tiết.';

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
    $maBB        = trim($_POST['maBB']        ?? '');
    $maNV        = trim($_POST['maNV']         ?? '');
    $maNCC       = trim($_POST['maNCC']        ?? '');
    $daiDienNCC  = trim($_POST['daidienNCC']   ?? '');
    $tgInput     = $_POST['thoigianKT']        ?? date('Y-m-d\TH:i');
    $thoiGianKT  = str_replace('T', ' ', $tgInput) . (strlen($tgInput) === 16 ? ':00' : '');
    $diaDiem     = trim($_POST['diadiem']      ?? '');
    $trangThai   = trim($_POST['trangthai']    ?? '');
    $ghiChu      = trim($_POST['ghichu']       ?? '');

    $vtArr       = $_POST['maVatTu']       ?? [];
    $slGiaoArr   = $_POST['slGiao']        ?? [];
    $slDatArr    = $_POST['slDat']         ?? [];
    $slLoiArr    = $_POST['slLoi']         ?? [];
    $ketQuaArr   = $_POST['ketqua']        ?? [];
    $phuongAnArr = $_POST['phuonganxuly']  ?? [];
    $ghiChuLoiArr = $_POST['ghichuloi']   ?? [];

    $errors = validate_bbkt_create(
        $conn, $maBB, $maNV, $maNCC, $tgInput,
        $vtArr, $slGiaoArr, $slDatArr, $slLoiArr
    );

    if (empty($errors)) {
        // Build valid items list (maVT non-empty)
        $validItems = [];
        for ($i = 0; $i < count($vtArr); $i++) {
            $maVT = trim($vtArr[$i] ?? '');
            if ($maVT === '') continue;
            $validItems[] = [
                'maVT'       => $maVT,
                'slGiao'     => (int) ($slGiaoArr[$i]   ?? 0),
                'slDat'      => (int) ($slDatArr[$i]    ?? 0),
                'slLoi'      => (int) ($slLoiArr[$i]    ?? 0),
                'ketQua'     => (int) ($ketQuaArr[$i]   ?? 0),
                'phuongAn'   => trim($phuongAnArr[$i]   ?? ''),
                'ghiChuLoi'  => trim($ghiChuLoiArr[$i]  ?? ''),
            ];
        }

        // N+1 fix: batch-load maDVT for all unique maVT
        $uniqueVT = array_unique(array_column($validItems, 'maVT'));
        $ph       = db_placeholders($uniqueVT);
        $types    = str_repeat('s', count($uniqueVT));
        $dvtMap   = db_fetch_keyed(
            $conn,
            "SELECT maVatTu, maDVT FROM VatTu WHERE maVatTu IN ($ph)",
            $types,
            $uniqueVT,
            'maVatTu'
        );

        $conn->begin_transaction();
        try {
            // 1) INSERT header
            $stmtBB = $conn->prepare(
                "INSERT INTO BienBanKiemTra
                 (maBB, maNV, maNCC, daidienNCC, thoigianKT, diadiem, trangthai, ghichu)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmtBB->bind_param('ssssssss',
                $maBB, $maNV, $maNCC, $daiDienNCC, $thoiGianKT, $diaDiem, $trangThai, $ghiChu
            );
            $stmtBB->execute();
            $stmtBB->close();

            // 2) INSERT chi tiết
            $stmtCT = $conn->prepare(
                "INSERT INTO ChiTietKiemTra
                 (maBB, maVatTu, maDVT, slGiao, slDat, slLoi, ketqua, phuonganxuly, ghichuloi)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            foreach ($validItems as $item) {
                $maDVT = $dvtMap[$item['maVT']]['maDVT'] ?? null;
                if ($maDVT === null)
                    throw new RuntimeException("Không tìm thấy đơn vị tính của vật tư {$item['maVT']}.");

                $maVTBind   = $item['maVT'];
                $sg         = $item['slGiao'];
                $sd         = $item['slDat'];
                $sl         = $item['slLoi'];
                $kq         = $item['ketQua'];
                $pa         = $item['phuongAn'];
                $gcl        = $item['ghiChuLoi'];

                $stmtCT->bind_param('sssiiiiss',
                    $maBB, $maVTBind, $maDVT, $sg, $sd, $sl, $kq, $pa, $gcl
                );
                $stmtCT->execute();
            }
            $stmtCT->close();

            $conn->commit();
            header("Location: index.php");
            exit;
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('[BBKT-Create] ' . $e->getMessage());
            $errors[] = 'Lỗi khi lưu biên bản kiểm tra. Vui lòng kiểm tra lại dữ liệu.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Tạo Biên Bản Kiểm Tra</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3 mb-3">
                <label>Mã BB <span class="text-danger">*</span></label>
                <input type="text" name="maBB" class="form-control"
                       value="<?= htmlspecialchars($_POST['maBB'] ?? '') ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label>Nhân viên <span class="text-danger">*</span></label>
                <select name="maNV" class="form-select" required>
                    <option value="">-- Chọn --</option>
                    <?php
                    $rs = $conn->query("SELECT maNV, hoten FROM NhanVien ORDER BY hoten");
                    $sel = $_POST['maNV'] ?? '';
                    while ($x = $rs->fetch_assoc()) {
                        $s = ($x['maNV'] === $sel) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maNV']) . "' $s>"
                            . htmlspecialchars($x['maNV'] . ' – ' . $x['hoten']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label>Nhà cung cấp <span class="text-danger">*</span></label>
                <select name="maNCC" class="form-select" required>
                    <option value="">-- Chọn --</option>
                    <?php
                    $rs = $conn->query("SELECT maNCC, tenNCC FROM NhaCungCap ORDER BY tenNCC");
                    $sel = $_POST['maNCC'] ?? '';
                    while ($x = $rs->fetch_assoc()) {
                        $s = ($x['maNCC'] === $sel) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maNCC']) . "' $s>"
                            . htmlspecialchars($x['tenNCC']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label>Đại diện NCC</label>
                <input type="text" name="daidienNCC" class="form-control"
                       value="<?= htmlspecialchars($_POST['daidienNCC'] ?? '') ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Thời gian kiểm tra</label>
                <input type="datetime-local" name="thoigianKT" class="form-control"
                       value="<?= htmlspecialchars($_POST['thoigianKT'] ?? date('Y-m-d\TH:i')) ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Địa điểm</label>
                <input type="text" name="diadiem" class="form-control"
                       value="<?= htmlspecialchars($_POST['diadiem'] ?? '') ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Trạng thái</label>
                <input type="text" name="trangthai" class="form-control"
                       value="<?= htmlspecialchars($_POST['trangthai'] ?? 'Đang kiểm tra') ?>">
            </div>
            <div class="col-12">
                <label>Ghi chú</label>
                <input type="text" name="ghichu" class="form-control"
                       value="<?= htmlspecialchars($_POST['ghichu'] ?? '') ?>">
            </div>
        </div>

        <table class="table table-bordered align-middle" id="tbl">
            <thead>
                <tr>
                    <th>Vật tư</th><th>ĐVT</th><th>SL giao</th><th>SL đạt</th><th>SL lỗi</th>
                    <th>Kết quả</th><th>Phương án xử lý</th><th>Ghi chú lỗi</th>
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
                    <td><input type="number" name="slGiao[]" class="form-control" min="0" value="0"></td>
                    <td><input type="number" name="slDat[]"  class="form-control" min="0" value="0"></td>
                    <td><input type="number" name="slLoi[]"  class="form-control" min="0" value="0"></td>
                    <td>
                        <select name="ketqua[]" class="form-select">
                            <option value="1">Đạt</option>
                            <option value="0">Không đạt</option>
                        </select>
                    </td>
                    <td><input type="text" name="phuonganxuly[]" class="form-control"></td>
                    <td><input type="text" name="ghichuloi[]"    class="form-control"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
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

function fillUnit(row) {
    const sel = row.querySelector('select[name="maVatTu[]"]');
    row.querySelector('.unit').value = sel.options[sel.selectedIndex]?.dataset.unit || '';
}
tb.addEventListener('change', e => {
    if (e.target.matches('select[name="maVatTu[]"]')) fillUnit(e.target.closest('tr'));
});
function addRow() {
    const r = tb.rows[0].cloneNode(true);
    r.querySelectorAll('input[type="text"]').forEach(i => i.value = '');
    r.querySelectorAll('input[type="number"]').forEach(i => i.value = 0);
    r.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    tb.appendChild(r);
}
function delRow(b) {
    if (tb.rows.length > 1) b.closest('tr').remove();
}
document.querySelectorAll('#tbl tbody tr').forEach(fillUnit);
</script>

</div></body></html>
