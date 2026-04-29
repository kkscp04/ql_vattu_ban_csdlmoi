<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$bb = $conn->query("SELECT * FROM BienBanKiemTra WHERE maBB='$id'")->fetch_assoc();
if (!$bb) { echo "Không tìm thấy biên bản kiểm tra"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maNV = $conn->real_escape_string(trim($_POST['maNV'] ?? ''));
    $maNCC = $conn->real_escape_string(trim($_POST['maNCC'] ?? ''));
    $daiDienNCC = $conn->real_escape_string(trim($_POST['daidienNCC'] ?? ''));
    $thoiGianKTInput = $_POST['thoigianKT'] ?? date('Y-m-d\TH:i');
    $thoiGianKT = $conn->real_escape_string(str_replace('T', ' ', $thoiGianKTInput) . (strlen($thoiGianKTInput) === 16 ? ':00' : ''));
    $diaDiem = $conn->real_escape_string(trim($_POST['diadiem'] ?? ''));
    $trangThai = $conn->real_escape_string(trim($_POST['trangthai'] ?? ''));
    $ghiChu = $conn->real_escape_string(trim($_POST['ghichu'] ?? ''));

    $vt = $_POST['maVatTu'] ?? [];
    $slGiao = $_POST['slGiao'] ?? [];
    $slDat = $_POST['slDat'] ?? [];
    $slLoi = $_POST['slLoi'] ?? [];
    $ketQua = $_POST['ketqua'] ?? [];
    $phuongAn = $_POST['phuonganxuly'] ?? [];
    $ghiChuLoi = $_POST['ghichuloi'] ?? [];

    if ($maNV === '' || $maNCC === '') {
        $error = "Vui lòng chọn nhân viên và nhà cung cấp.";
    } elseif (count(array_filter($vt)) !== count(array_unique(array_filter($vt)))) {
        $error = "Vật tư bị trùng trong chi tiết kiểm tra.";
    } else {
        $conn->begin_transaction();
        try {
            $conn->query("UPDATE BienBanKiemTra
                          SET maNV='$maNV',
                              maNCC='$maNCC',
                              daidienNCC='$daiDienNCC',
                              thoigianKT='$thoiGianKT',
                              diadiem='$diaDiem',
                              trangthai='$trangThai',
                              ghichu='$ghiChu'
                          WHERE maBB='$id'");

            $conn->query("DELETE FROM ChiTietKiemTra WHERE maBB='$id'");

            for ($i = 0; $i < count($vt); $i++) {
                $maVatTu = $conn->real_escape_string($vt[$i] ?? '');
                $soLuongGiao = (int) ($slGiao[$i] ?? 0);
                $soLuongDat = (int) ($slDat[$i] ?? 0);
                $soLuongLoi = (int) ($slLoi[$i] ?? 0);
                $ketQuaRow = (isset($ketQua[$i]) && $ketQua[$i] !== '') ? (int) $ketQua[$i] : 0;
                $phuongAnRow = $conn->real_escape_string(trim($phuongAn[$i] ?? ''));
                $ghiChuLoiRow = $conn->real_escape_string(trim($ghiChuLoi[$i] ?? ''));

                if ($maVatTu !== '') {
                    $q = $conn->query("SELECT maDVT FROM VatTu WHERE maVatTu='$maVatTu' LIMIT 1");
                    $rowDVT = $q ? $q->fetch_assoc() : null;
                    $maDVT = $rowDVT['maDVT'] ?? '';
                    if ($maDVT === '') throw new Exception("Không tìm thấy đơn vị tính của vật tư $maVatTu.");

                    $conn->query("INSERT INTO ChiTietKiemTra(maBB, maVatTu, maDVT, slGiao, slDat, slLoi, ketqua, phuonganxuly, ghichuloi)
                                  VALUES('$id', '$maVatTu', '$maDVT', $soLuongGiao, $soLuongDat, $soLuongLoi, $ketQuaRow, '$phuongAnRow', '$ghiChuLoiRow')");
                }
            }

            $conn->commit();
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}

$ct = [];
$rs = $conn->query("SELECT * FROM ChiTietKiemTra WHERE maBB='$id'");
while ($r = $rs->fetch_assoc()) $ct[] = $r;
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Sửa Biên Bản Kiểm Tra #<?= htmlspecialchars($id) ?></h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3 mb-3"><label>Mã BB</label><input class="form-control" value="<?= htmlspecialchars($id) ?>" readonly></div>
            <div class="col-md-3 mb-3"><label>Nhân viên *</label><select name="maNV" class="form-select" required><?php $r = $conn->query("SELECT maNV, hoten FROM NhanVien ORDER BY hoten"); while ($x = $r->fetch_assoc()) { $s = ($x['maNV'] === $bb['maNV']) ? 'selected' : ''; echo "<option value='{$x['maNV']}' $s>" . htmlspecialchars($x['maNV'] . ' - ' . $x['hoten']) . "</option>"; } ?></select></div>
            <div class="col-md-3 mb-3"><label>Nhà cung cấp *</label><select name="maNCC" class="form-select" required><?php $r = $conn->query("SELECT maNCC, tenNCC FROM NhaCungCap ORDER BY tenNCC"); while ($x = $r->fetch_assoc()) { $s = ($x['maNCC'] === $bb['maNCC']) ? 'selected' : ''; echo "<option value='{$x['maNCC']}' $s>" . htmlspecialchars($x['tenNCC']) . "</option>"; } ?></select></div>
            <div class="col-md-3 mb-3"><label>Đại diện NCC</label><input type="text" name="daidienNCC" class="form-control" value="<?= htmlspecialchars($bb['daidienNCC'] ?? '') ?>"></div>

            <div class="col-md-4 mb-3"><label>Thời gian kiểm tra</label><input type="datetime-local" name="thoigianKT" class="form-control" value="<?= !empty($bb['thoigianKT']) ? date('Y-m-d\TH:i', strtotime($bb['thoigianKT'])) : date('Y-m-d\TH:i') ?>"></div>
            <div class="col-md-4 mb-3"><label>Địa điểm</label><input type="text" name="diadiem" class="form-control" value="<?= htmlspecialchars($bb['diadiem'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label>Trạng thái</label><input type="text" name="trangthai" class="form-control" value="<?= htmlspecialchars($bb['trangthai'] ?? '') ?>"></div>
            <div class="col-md-12"><label>Ghi chú</label><input type="text" name="ghichu" class="form-control" value="<?= htmlspecialchars($bb['ghichu'] ?? '') ?>"></div>
        </div>

        <table class="table table-bordered align-middle" id="tbl">
            <thead>
                <tr>
                    <th>Vật tư</th>
                    <th>ĐVT</th>
                    <th>SL giao</th>
                    <th>SL đạt</th>
                    <th>SL lỗi</th>
                    <th>Kết quả</th>
                    <th>Phương án xử lý</th>
                    <th>Ghi chú lỗi</th>
                    <th class="text-center" style="width:90px;">Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ct) { foreach ($ct as $r) { ?>
                <tr>
                    <td><select name="maVatTu[]" class="form-select" required><?php $v = $conn->query("SELECT vt.maVatTu, vt.tenVatTu, dv.tenDVT FROM VatTu vt LEFT JOIN DonViTinh dv ON vt.maDVT = dv.maDVT ORDER BY vt.tenVatTu"); while ($x = $v->fetch_assoc()) { $s = ($x['maVatTu'] === $r['maVatTu']) ? 'selected' : ''; echo "<option value='{$x['maVatTu']}' data-unit='" . htmlspecialchars($x['tenDVT'] ?? '') . "' $s>" . htmlspecialchars($x['tenVatTu']) . "</option>"; } ?></select></td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="slGiao[]" class="form-control" min="0" value="<?= (int) $r['slGiao'] ?>"></td>
                    <td><input type="number" name="slDat[]" class="form-control" min="0" value="<?= (int) $r['slDat'] ?>"></td>
                    <td><input type="number" name="slLoi[]" class="form-control" min="0" value="<?= (int) $r['slLoi'] ?>"></td>
                    <td><select name="ketqua[]" class="form-select"><?php $kq = !empty($r['ketqua']) ? 1 : 0; ?><option value="1" <?= $kq === 1 ? 'selected' : '' ?>>Đạt</option><option value="0" <?= $kq === 0 ? 'selected' : '' ?>>Không đạt</option></select></td>
                    <td><input type="text" name="phuonganxuly[]" class="form-control" value="<?= htmlspecialchars($r['phuonganxuly'] ?? '') ?>"></td>
                    <td><input type="text" name="ghichuloi[]" class="form-control" value="<?= htmlspecialchars($r['ghichuloi'] ?? '') ?>"></td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)"><i class="fas fa-trash"></i></button></td>
                </tr>
                <?php }} else { ?>
                <tr>
                    <td><select name="maVatTu[]" class="form-select" required><option value="">-- Chọn --</option><?php $v = $conn->query("SELECT vt.maVatTu, vt.tenVatTu, dv.tenDVT FROM VatTu vt LEFT JOIN DonViTinh dv ON vt.maDVT = dv.maDVT ORDER BY vt.tenVatTu"); while ($x = $v->fetch_assoc()) echo "<option value='{$x['maVatTu']}' data-unit='" . htmlspecialchars($x['tenDVT'] ?? '') . "'>" . htmlspecialchars($x['tenVatTu']) . "</option>"; ?></select></td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="slGiao[]" class="form-control" min="0" value="0"></td>
                    <td><input type="number" name="slDat[]" class="form-control" min="0" value="0"></td>
                    <td><input type="number" name="slLoi[]" class="form-control" min="0" value="0"></td>
                    <td><select name="ketqua[]" class="form-select"><option value="1">Đạt</option><option value="0">Không đạt</option></select></td>
                    <td><input type="text" name="phuonganxuly[]" class="form-control"></td>
                    <td><input type="text" name="ghichuloi[]" class="form-control"></td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)"><i class="fas fa-trash"></i></button></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <button type="button" class="btn btn-info btn-sm text-white mb-3" onclick="addRow()">+ Thêm dòng</button><br>
        <button class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

<script>
const tb = document.querySelector('#tbl tbody');

function fillUnit(row) {
    const s = row.querySelector('select[name="maVatTu[]"]');
    row.querySelector('.unit').value = s.options[s.selectedIndex]?.dataset.unit || '';
}

tb.addEventListener('change', e => {
    if (e.target.matches('select[name="maVatTu[]"]')) {
        fillUnit(e.target.closest('tr'));
    }
});

function addRow() {
    const r = tb.rows[0].cloneNode(true);
    r.querySelectorAll('input').forEach(i => i.value = '');
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
