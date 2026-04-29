<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maPN = $conn->real_escape_string(trim($_POST['maPN'] ?? ''));
    $maNV = $conn->real_escape_string(trim($_POST['maNV_Lap'] ?? ''));
    $maBB = $conn->real_escape_string(trim($_POST['maBB'] ?? ''));
    $ngay = $conn->real_escape_string($_POST['ngaynhap'] ?? date('Y-m-d'));
    $ghiChu = $conn->real_escape_string(trim($_POST['ghichu'] ?? ''));

    $vt = $_POST['maVatTu'] ?? [];
    $sl = $_POST['soluong'] ?? [];
    $dg = $_POST['dongianhap'] ?? [];
    $ghict = $_POST['ghichu_ct'] ?? [];

    if ($maPN === '' || $maNV === '') {
        $error = "Vui lòng nhập mã phiếu nhập và nhân viên lập.";
    } elseif (count(array_filter($vt)) !== count(array_unique(array_filter($vt)))) {
        $error = "Vật tư bị trùng trong chi tiết.";
    } else {
        $conn->begin_transaction();
        try {
            $conn->query("INSERT INTO PhieuNhap(maPN, maNV_Lap, maBB, ngaynhap, ghichu, tongtien)
                          VALUES('$maPN', '$maNV', " . ($maBB !== '' ? "'$maBB'" : "NULL") . ", '$ngay 00:00:00', '$ghiChu', 0)");

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

                    if ($maDVT === '') {
                        throw new Exception("Không tìm thấy đơn vị tính của vật tư $maVatTu.");
                    }

                    $thanhTien = $soLuong * $donGia;
                    $tong += $thanhTien;

                    $conn->query("INSERT INTO ChiTietPhieuNhap(maPN, maVatTu, maLo, maDVT, soluong, dongianhap, thanhtien, ghichu)
                                  VALUES('$maPN', '$maVatTu', NULL, '$maDVT', $soLuong, $donGia, $thanhTien, '$ghiChuCT')");
                }
            }

            $conn->query("UPDATE PhieuNhap SET tongtien=$tong WHERE maPN='$maPN'");
            $conn->commit();
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Tạo Phiếu Nhập</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3"><label>Mã PN *</label><input type="text" name="maPN" class="form-control" required></div>
            <div class="col-md-3"><label>Nhân viên lập *</label><select name="maNV_Lap" class="form-select" required><option value="">-- Chọn --</option><?php $r = $conn->query("SELECT maNV,hoten FROM NhanVien ORDER BY hoten"); while ($x = $r->fetch_assoc()) echo "<option value='{$x['maNV']}'>" . htmlspecialchars($x['maNV'] . ' - ' . $x['hoten']) . "</option>"; ?></select></div>
            <div class="col-md-3"><label>Biên bản KT</label><select name="maBB" class="form-select"><option value="">-- Không chọn --</option><?php $r = $conn->query("SELECT maBB FROM BienBanKiemTra ORDER BY maBB DESC"); while ($x = $r->fetch_assoc()) echo "<option value='{$x['maBB']}'>" . htmlspecialchars($x['maBB']) . "</option>"; ?></select></div>
            <div class="col-md-3"><label>Ngày nhập</label><input type="date" name="ngaynhap" class="form-control" value="<?= date('Y-m-d') ?>"></div>
            <div class="col-md-12 mt-3"><label>Ghi chú</label><input type="text" name="ghichu" class="form-control"></div>
        </div>

        <table class="table table-bordered align-middle" id="tbl">
            <thead>
                <tr>
                    <th>Vật tư</th><th>ĐVT</th><th>Số lượng</th><th>Đơn giá nhập</th><th>Thành tiền</th><th>Ghi chú CT</th><th class="text-center" style="width:90px;">Xóa</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><select name="maVatTu[]" class="form-select" required><option value="">-- Chọn --</option><?php $r = $conn->query("SELECT v.maVatTu,v.tenVatTu,d.tenDVT FROM VatTu v LEFT JOIN DonViTinh d ON v.maDVT=d.maDVT ORDER BY v.tenVatTu"); while ($x = $r->fetch_assoc()) echo "<option value='{$x['maVatTu']}' data-unit='" . htmlspecialchars($x['tenDVT'] ?? '') . "'>" . htmlspecialchars($x['tenVatTu']) . "</option>"; ?></select></td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="soluong[]" class="form-control" min="1" required></td>
                    <td><input type="number" name="dongianhap[]" class="form-control" min="0" step="0.01" required></td>
                    <td><input class="form-control thanhtien" readonly></td>
                    <td><input type="text" name="ghichu_ct[]" class="form-control"></td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)"><i class="fas fa-trash"></i></button></td>
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

        <button type="button" class="btn btn-info btn-sm text-white mb-3" onclick="addRow()">+ Thêm dòng</button><br>
        <button class="btn btn-success">Lưu</button>
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
