<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$pn = $conn->query("SELECT * FROM PhieuNhap WHERE maPN='$id'")->fetch_assoc();
if (!$pn) { echo "Không tìm thấy phiếu nhập"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maNV = $conn->real_escape_string(trim($_POST['maNV_Lap'] ?? ''));
    $maBB = $conn->real_escape_string(trim($_POST['maBB'] ?? ''));
    $ngay = $conn->real_escape_string($_POST['ngaynhap'] ?? date('Y-m-d'));
    $ghiChu = $conn->real_escape_string(trim($_POST['ghichu'] ?? ''));

    $vt = $_POST['maVatTu'] ?? [];
    $sl = $_POST['soluong'] ?? [];
    $dg = $_POST['dongianhap'] ?? [];
    $ghict = $_POST['ghichu_ct'] ?? [];

    if ($maNV === '') {
        $error = "Vui lòng chọn nhân viên lập.";
    } elseif (count(array_filter($vt)) !== count(array_unique(array_filter($vt)))) {
        $error = "Vật tư bị trùng trong chi tiết.";
    } else {
        $conn->begin_transaction();
        try {
            $conn->query("UPDATE PhieuNhap
                          SET maNV_Lap='$maNV',
                              maBB=" . ($maBB !== '' ? "'$maBB'" : "NULL") . ",
                              ngaynhap='$ngay 00:00:00',
                              ghichu='$ghiChu'
                          WHERE maPN='$id'");

            $conn->query("DELETE FROM ChiTietPhieuNhap WHERE maPN='$id'");

            $tong = 0;
            for ($i = 0; $i < count($vt); $i++) {
                $maVatTu = $conn->real_escape_string($vt[$i] ?? '');
                $soLuong = (int) ($sl[$i] ?? 0);
                $donGia = (float) ($dg[$i] ?? 0);
                $ghiChuCT = $conn->real_escape_string(trim($ghict[$i] ?? ''));

                if ($maVatTu !== '' && $soLuong > 0) {
                    $q = $conn->query("SELECT maDVT FROM VatTu WHERE maVatTu='$maVatTu' LIMIT 1");
                    $rowDVT = $q ? $q->fetch_assoc() : null;
                    $maDVT = $rowDVT['maDVT'] ?? '';
                    if ($maDVT === '') throw new Exception("Không tìm thấy đơn vị tính của vật tư $maVatTu.");

                    $thanhTien = $soLuong * $donGia;
                    $tong += $thanhTien;

                    $conn->query("INSERT INTO ChiTietPhieuNhap(maPN, maVatTu, maLo, maDVT, soluong, dongianhap, thanhtien, ghichu)
                                  VALUES('$id', '$maVatTu', NULL, '$maDVT', $soLuong, $donGia, $thanhTien, '$ghiChuCT')");
                }
            }

            $conn->query("UPDATE PhieuNhap SET tongtien=$tong WHERE maPN='$id'");
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
$rs = $conn->query("SELECT * FROM ChiTietPhieuNhap WHERE maPN='$id'");
while ($r = $rs->fetch_assoc()) $ct[] = $r;
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Sửa Phiếu Nhập #<?= htmlspecialchars($id) ?></h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3"><label>Mã PN</label><input class="form-control" value="<?= htmlspecialchars($id) ?>" readonly></div>
            <div class="col-md-3"><label>Nhân viên lập *</label><select name="maNV_Lap" class="form-select" required><?php $r = $conn->query("SELECT maNV,hoten FROM NhanVien ORDER BY hoten"); while ($x = $r->fetch_assoc()) { $s = ($x['maNV'] === $pn['maNV_Lap']) ? 'selected' : ''; echo "<option value='{$x['maNV']}' $s>" . htmlspecialchars($x['maNV'] . ' - ' . $x['hoten']) . "</option>"; } ?></select></div>
            <div class="col-md-3"><label>Biên bản KT</label><select name="maBB" class="form-select"><option value="">-- Không chọn --</option><?php $r = $conn->query("SELECT maBB FROM BienBanKiemTra ORDER BY maBB DESC"); while ($x = $r->fetch_assoc()) { $s = ($x['maBB'] === $pn['maBB']) ? 'selected' : ''; echo "<option value='{$x['maBB']}' $s>" . htmlspecialchars($x['maBB']) . "</option>"; } ?></select></div>
            <div class="col-md-3"><label>Ngày nhập</label><input type="date" name="ngaynhap" class="form-control" value="<?= !empty($pn['ngaynhap']) ? date('Y-m-d', strtotime($pn['ngaynhap'])) : date('Y-m-d') ?>"></div>
            <div class="col-md-12 mt-3"><label>Ghi chú</label><input type="text" name="ghichu" class="form-control" value="<?= htmlspecialchars($pn['ghichu']) ?>"></div>
        </div>

        <table class="table table-bordered align-middle" id="tbl">
            <thead>
                <tr><th>Vật tư</th><th>ĐVT</th><th>Số lượng</th><th>Đơn giá nhập</th><th>Thành tiền</th><th>Ghi chú CT</th><th class="text-center" style="width:90px;">Xóa</th></tr>
            </thead>
            <tbody>
                <?php if ($ct) { foreach ($ct as $r) { ?>
                <tr>
                    <td><select name="maVatTu[]" class="form-select" required><?php $v = $conn->query("SELECT vt.maVatTu,vt.tenVatTu,dv.tenDVT FROM VatTu vt LEFT JOIN DonViTinh dv ON vt.maDVT=dv.maDVT ORDER BY vt.tenVatTu"); while ($x = $v->fetch_assoc()) { $s = ($x['maVatTu'] === $r['maVatTu']) ? 'selected' : ''; echo "<option value='{$x['maVatTu']}' data-unit='" . htmlspecialchars($x['tenDVT'] ?? '') . "' $s>" . htmlspecialchars($x['tenVatTu']) . "</option>"; } ?></select></td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="soluong[]" class="form-control" min="1" value="<?= (int) $r['soluong'] ?>" required></td>
                    <td><input type="number" name="dongianhap[]" class="form-control" min="0" step="0.01" value="<?= (float) $r['dongianhap'] ?>" required></td>
                    <td><input class="form-control thanhtien" readonly></td>
                    <td><input type="text" name="ghichu_ct[]" class="form-control" value="<?= htmlspecialchars($r['ghichu']) ?>"></td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)"><i class="fas fa-trash"></i></button></td>
                </tr>
                <?php }} else { ?>
                <tr>
                    <td><select name="maVatTu[]" class="form-select" required><option value="">-- Chọn --</option><?php $v = $conn->query("SELECT vt.maVatTu,vt.tenVatTu,dv.tenDVT FROM VatTu vt LEFT JOIN DonViTinh dv ON vt.maDVT=dv.maDVT ORDER BY vt.tenVatTu"); while ($x = $v->fetch_assoc()) echo "<option value='{$x['maVatTu']}' data-unit='" . htmlspecialchars($x['tenDVT'] ?? '') . "'>" . htmlspecialchars($x['tenVatTu']) . "</option>"; ?></select></td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="soluong[]" class="form-control" min="1" required></td>
                    <td><input type="number" name="dongianhap[]" class="form-control" min="0" step="0.01" required></td>
                    <td><input class="form-control thanhtien" readonly></td>
                    <td><input type="text" name="ghichu_ct[]" class="form-control"></td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)"><i class="fas fa-trash"></i></button></td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Tổng tiền</th>
                    <th><input id="tongTien" class="form-control fw-bold text-danger" readonly></th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>

        <button type="button" class="btn btn-info btn-sm text-white mb-3" onclick="addRow()">+ Thêm dòng</button><br>
        <button class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

<script>
const tb = document.querySelector('#tbl tbody');
const tongTienInput = document.getElementById('tongTien');

function fillUnit(row) {
    const s = row.querySelector('select[name="maVatTu[]"]');
    row.querySelector('.unit').value = s.options[s.selectedIndex]?.dataset.unit || '';
}

function calcTotal() {
    let tong = 0;
    tb.querySelectorAll('tr').forEach(row => {
        tong += parseFloat(row.querySelector('.thanhtien').value) || 0;
    });
    tongTienInput.value = tong.toFixed(2);
}

function calcRow(row) {
    const sl = parseFloat(row.querySelector('input[name="soluong[]"]').value) || 0;
    const dg = parseFloat(row.querySelector('input[name="dongianhap[]"]').value) || 0;
    row.querySelector('.thanhtien').value = (sl * dg).toFixed(2);
    calcTotal();
}

tb.addEventListener('change', e => {
    if (e.target.matches('select[name="maVatTu[]"]')) {
        fillUnit(e.target.closest('tr'));
    }
});

tb.addEventListener('input', e => {
    if (e.target.matches('input[name="soluong[]"], input[name="dongianhap[]"]')) {
        calcRow(e.target.closest('tr'));
    }
});

function addRow() {
    const r = tb.rows[0].cloneNode(true);
    r.querySelectorAll('input').forEach(i => i.value = '');
    r.querySelector('select').selectedIndex = 0;
    tb.appendChild(r);
    calcTotal();
}

function delRow(b) {
    if (tb.rows.length > 1) {
        b.closest('tr').remove();
        calcTotal();
    }
}

document.querySelectorAll('#tbl tbody tr').forEach(r => {
    fillUnit(r);
    calcRow(r);
});
</script>

</div></body></html>
