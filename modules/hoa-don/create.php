<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = $conn->real_escape_string(trim($_POST['maHDon'] ?? ''));
    $maDH = $conn->real_escape_string(trim($_POST['maDH'] ?? ''));
    $maCNKH = $conn->real_escape_string(trim($_POST['maCNKH'] ?? ''));
    $maNV = $conn->real_escape_string(trim($_POST['maNV_Lap'] ?? ''));
    $ngaytao = $conn->real_escape_string($_POST['ngaytao'] ?? date('Y-m-d'));
    $truoc = (float) ($_POST['tongtientruocthue'] ?? 0);
    $pttt = $conn->real_escape_string(trim($_POST['phuongthucthanhtoan'] ?? 'Tiền mặt'));
    $trangthai = $conn->real_escape_string(trim($_POST['trangthai'] ?? 'Chưa thanh toán'));
    $diachi = $conn->real_escape_string(trim($_POST['diachi'] ?? ''));
    $vat = (float) ($_POST['thuevat'] ?? 0);
    $tienthue = round($truoc * $vat / 100, 2);
    $tong = $truoc + $tienthue;
    $ngaytt = $conn->real_escape_string($_POST['ngaythanhtoan'] ?? '');
    $so = $conn->real_escape_string(trim($_POST['sohoadon'] ?? ''));
    $loai = $conn->real_escape_string(trim($_POST['loaihoadon'] ?? 'Bán hàng'));
    $ghichu = $conn->real_escape_string(trim($_POST['ghichu'] ?? ''));

    if ($ma === '' || $maDH === '' || $maCNKH === '' || $maNV === '' || $so === '') {
        $error = "Vui lòng nhập đầy đủ mã hóa đơn, số hóa đơn, đơn hàng, công nợ KH, nhân viên lập.";
    } else {
        $sql = "INSERT INTO HoaDon(maHDon, maDH, maCNKH, maNV_Lap, ngaytao, tongtientruocthue, phuongthucthanhtoan, trangthai, diachi, thuevat, tienthue, tongtien, ngaythanhtoan, sohoadon, loaihoadon, ghichu)
                VALUES('$ma', '$maDH', '$maCNKH', '$maNV', '$ngaytao 00:00:00', $truoc, '$pttt', '$trangthai', '$diachi', $vat, $tienthue, $tong,
                       " . ($ngaytt !== '' ? "'$ngaytt 00:00:00'" : "NULL") . ",
                       '$so', '$loai', '$ghichu')";
        if ($conn->query($sql)) {
            header("Location: index.php");
            exit;
        }
        $error = "Lỗi: " . $conn->error;
    }
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4" style="max-width:1000px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Thêm Hóa Đơn</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-3 mb-3"><label>Mã HĐơn *</label><input type="text" name="maHDon" class="form-control" value="<?= htmlspecialchars($_POST['maHDon'] ?? '') ?>" required></div>
            <div class="col-md-3 mb-3"><label>Số hóa đơn *</label><input type="text" name="sohoadon" class="form-control" value="<?= htmlspecialchars($_POST['sohoadon'] ?? '') ?>" required></div>
            <div class="col-md-3 mb-3"><label>Đơn hàng *</label><select name="maDH" class="form-select" required><option value="">-- Chọn --</option><?php $r = $conn->query("SELECT maDH FROM DonHang ORDER BY maDH DESC"); while ($x = $r->fetch_assoc()) { $s = (($x['maDH']) === ($_POST['maDH'] ?? '')) ? 'selected' : ''; echo "<option value='{$x['maDH']}' $s>" . htmlspecialchars($x['maDH']) . "</option>"; } ?></select></div>
            <div class="col-md-3 mb-3"><label>Công nợ KH *</label><select name="maCNKH" class="form-select" required><option value="">-- Chọn --</option><?php $r = $conn->query("SELECT maCNKH FROM CongNoKH ORDER BY maCNKH DESC"); while ($x = $r->fetch_assoc()) { $s = (($x['maCNKH']) === ($_POST['maCNKH'] ?? '')) ? 'selected' : ''; echo "<option value='{$x['maCNKH']}' $s>" . htmlspecialchars($x['maCNKH']) . "</option>"; } ?></select></div>

            <div class="col-md-3 mb-3"><label>Nhân viên lập *</label><select name="maNV_Lap" class="form-select" required><option value="">-- Chọn --</option><?php $r = $conn->query("SELECT maNV,hoten FROM NhanVien ORDER BY hoten"); while ($x = $r->fetch_assoc()) { $s = (($x['maNV']) === ($_POST['maNV_Lap'] ?? '')) ? 'selected' : ''; echo "<option value='{$x['maNV']}' $s>" . htmlspecialchars($x['maNV'] . ' - ' . $x['hoten']) . "</option>"; } ?></select></div>
            <div class="col-md-3 mb-3"><label>Ngày tạo</label><input type="date" name="ngaytao" class="form-control" value="<?= htmlspecialchars($_POST['ngaytao'] ?? date('Y-m-d')) ?>"></div>
            <div class="col-md-3 mb-3"><label>Loại hóa đơn</label><input type="text" name="loaihoadon" class="form-control" value="<?= htmlspecialchars($_POST['loaihoadon'] ?? 'Bán hàng') ?>"></div>
            <div class="col-md-3 mb-3"><label>Địa chỉ</label><input type="text" name="diachi" class="form-control" value="<?= htmlspecialchars($_POST['diachi'] ?? '') ?>"></div>

            <div class="col-md-3 mb-3"><label>Tổng trước thuế</label><input type="number" id="tongTruocThue" name="tongtientruocthue" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($_POST['tongtientruocthue'] ?? '0') ?>"></div>
            <div class="col-md-3 mb-3"><label>VAT (%)</label><input type="number" id="thueVat" name="thuevat" class="form-control" min="0" max="100" step="0.01" value="<?= htmlspecialchars($_POST['thuevat'] ?? '0') ?>"></div>
            <div class="col-md-3 mb-3"><label>Tiền thuế</label><input type="number" id="tienThue" name="tienthue" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($_POST['tienthue'] ?? '0') ?>" readonly></div>
            <div class="col-md-3 mb-3"><label>Tổng tiền</label><input type="number" id="tongTien" name="tongtien" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($_POST['tongtien'] ?? '0') ?>" readonly></div>

            <div class="col-md-4 mb-3">
                <label>Phương thức thanh toán</label>
                <?php $ptttValue = $_POST['phuongthucthanhtoan'] ?? 'Tiền mặt'; ?>
                <select name="phuongthucthanhtoan" class="form-select">
                    <option <?= $ptttValue === 'Tiền mặt' ? 'selected' : '' ?>>Tiền mặt</option>
                    <option <?= $ptttValue === 'Chuyển khoản' ? 'selected' : '' ?>>Chuyển khoản</option>
                    <option <?= $ptttValue === 'Công nợ' ? 'selected' : '' ?>>Công nợ</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label>Trạng thái</label>
                <?php $trangThaiValue = $_POST['trangthai'] ?? 'Chưa thanh toán'; ?>
                <select name="trangthai" class="form-select">
                    <option <?= $trangThaiValue === 'Chưa thanh toán' ? 'selected' : '' ?>>Chưa thanh toán</option>
                    <option <?= $trangThaiValue === 'Thanh toán một phần' ? 'selected' : '' ?>>Thanh toán một phần</option>
                    <option <?= $trangThaiValue === 'Đã thanh toán' ? 'selected' : '' ?>>Đã thanh toán</option>
                </select>
            </div>
            <div class="col-md-4 mb-3"><label>Ngày thanh toán</label><input type="date" name="ngaythanhtoan" class="form-control" value="<?= htmlspecialchars($_POST['ngaythanhtoan'] ?? '') ?>"></div>

            <div class="col-12 mb-3"><label>Ghi chú</label><input type="text" name="ghichu" class="form-control" value="<?= htmlspecialchars($_POST['ghichu'] ?? '') ?>"></div>
        </div>

        <button class="btn btn-success">Lưu</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

<script>
function calcInvoiceTotals() {
    const truoc = parseFloat(document.getElementById('tongTruocThue').value) || 0;
    const vat = parseFloat(document.getElementById('thueVat').value) || 0;
    const tienThue = truoc * vat / 100;
    const tongTien = truoc + tienThue;

    document.getElementById('tienThue').value = tienThue.toFixed(2);
    document.getElementById('tongTien').value = tongTien.toFixed(2);
}

document.getElementById('tongTruocThue').addEventListener('input', calcInvoiceTotals);
document.getElementById('thueVat').addEventListener('input', calcInvoiceTotals);
calcInvoiceTotals();
</script>

</div>
</body>
</html>
