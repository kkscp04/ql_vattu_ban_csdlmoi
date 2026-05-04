<?php
require_once __DIR__ . '/../../bootstrap.php';

$VALID_TT = ['Chưa thanh toán', 'Thanh toán một phần', 'Đã thanh toán'];
$VALID_PTTT = ['Tiền mặt', 'Chuyển khoản', 'Công nợ'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = trim($_POST['maHDon'] ?? '');
    $maDH = trim($_POST['maDH'] ?? '');
    $maPX = trim($_POST['maPX'] ?? '');
    $maCNKH = trim($_POST['maCNKH'] ?? '');
    $maNV = trim($_POST['maNV_Lap'] ?? '');
    $ngaytao = $_POST['ngaytao'] ?? date('Y-m-d');
    $truoc = (float) ($_POST['tongtientruocthue'] ?? 0);
    $pttt = trim($_POST['phuongthucthanhtoan'] ?? 'Tiền mặt');
    $tt = trim($_POST['trangthai'] ?? 'Chưa thanh toán');
    $diachi = trim($_POST['diachi'] ?? '');
    $vat = (float) ($_POST['thuevat'] ?? 0);
    $ngaytt = trim($_POST['ngaythanhtoan'] ?? '');
    $so = trim($_POST['sohoadon'] ?? '');
    $loai = trim($_POST['loaihoadon'] ?? 'Bán hàng');
    $ghichu = trim($_POST['ghichu'] ?? '');
    $errors = [];

    if ($ma === '') $errors[] = '[R01] Mã hóa đơn không được để trống.';
    if ($maCNKH === '') $errors[] = '[R01] Công nợ KH không được để trống.';
    if ($maNV === '') $errors[] = '[R01] Nhân viên không được để trống.';
    if ($so === '') $errors[] = '[R01] Số hóa đơn không được để trống.';
    if ($maDH === '' && $maPX === '') $errors[] = '[R01] Hóa đơn phải gắn đơn hàng hoặc phiếu xuất.';

    if ($ma !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)) $errors[] = '[R02] Mã hóa đơn không hợp lệ.';
    if ($ma !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)
        && db_exists($conn, "SELECT maHDon FROM HoaDon WHERE maHDon = ? LIMIT 1", 's', [$ma])) {
        $errors[] = "[R04] Mã hóa đơn '$ma' đã tồn tại.";
    }
    if ($so !== '' && db_exists($conn, "SELECT maHDon FROM HoaDon WHERE sohoadon = ? LIMIT 1", 's', [$so])) {
        $errors[] = "[R05] Số hóa đơn '$so' đã tồn tại.";
    }
    if ($maDH !== '' && !db_exists($conn, "SELECT maDH FROM DonHang WHERE maDH = ? LIMIT 1", 's', [$maDH])) {
        $errors[] = "[R06] Đơn hàng '$maDH' không tồn tại.";
    }
    if ($maPX !== '' && !db_exists($conn, "SELECT maPX FROM PhieuXuat WHERE maPX = ? LIMIT 1", 's', [$maPX])) {
        $errors[] = "[R06] Phiếu xuất '$maPX' không tồn tại.";
    }
    if ($maCNKH !== '' && !db_exists($conn, "SELECT maCNKH FROM CongNoKH WHERE maCNKH = ? LIMIT 1", 's', [$maCNKH])) {
        $errors[] = "[R06] Công nợ KH '$maCNKH' không tồn tại.";
    }
    if ($maNV !== '' && !db_exists($conn, "SELECT maNV FROM NhanVien WHERE maNV = ? LIMIT 1", 's', [$maNV])) {
        $errors[] = "[R06] Nhân viên '$maNV' không tồn tại.";
    }

    if (!in_array($tt, $VALID_TT, true)) $errors[] = '[R07] Trạng thái không hợp lệ.';
    if (!in_array($pttt, $VALID_PTTT, true)) $errors[] = '[R07] Phương thức thanh toán không hợp lệ.';
    if ($truoc < 0) $errors[] = '[R09] Tổng trước thuế phải >= 0.';
    if ($vat < 0 || $vat > 100) $errors[] = '[R09] VAT phải trong khoảng 0..100.';
    if ($ngaytao !== '' && strtotime($ngaytao) === false) $errors[] = '[R10] Ngày tạo không hợp lệ.';
    if ($ngaytt !== '' && strtotime($ngaytt) === false) $errors[] = '[R10] Ngày thanh toán không hợp lệ.';

    if (empty($errors)) {
        $tienthue = round($truoc * $vat / 100, 2);
        $tong = $truoc + $tienthue;
        $ngaytaoFmt = date('Y-m-d', strtotime($ngaytao));
        $ngayttParam = $ngaytt !== '' ? date('Y-m-d', strtotime($ngaytt)) : null;
        $maDHParam = $maDH !== '' ? $maDH : null;
        $maPXParam = $maPX !== '' ? $maPX : null;

        try {
            $stmt = $conn->prepare(
                "INSERT INTO HoaDon
                 (maHDon, maDH, maPX, maCNKH, maNV_Lap, ngaytao, tongtientruocthue,
                  phuongthucthanhtoan, trangthai, diachi, thuevat, tienthue, tongtien,
                  ngaythanhtoan, sohoadon, loaihoadon, ghichu)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'ssssssdsssdddssss',
                $ma,
                $maDHParam,
                $maPXParam,
                $maCNKH,
                $maNV,
                $ngaytaoFmt,
                $truoc,
                $pttt,
                $tt,
                $diachi,
                $vat,
                $tienthue,
                $tong,
                $ngayttParam,
                $so,
                $loai,
                $ghichu
            );
            $stmt->execute();
            $stmt->close();
            header("Location: index.php");
            exit;
        } catch (Throwable $e) {
            error_log('[HoaDon-Create] ' . $e->getMessage());
            $errors[] = 'Lỗi khi lưu. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:1000px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Thêm Hóa Đơn</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="row">
            <div class="col-md-3 mb-3"><label>Mã HĐơn <span class="text-danger">*</span></label><input type="text" name="maHDon" class="form-control" value="<?= htmlspecialchars($_POST['maHDon'] ?? '') ?>" required></div>
            <div class="col-md-3 mb-3"><label>Số HĐ <span class="text-danger">*</span></label><input type="text" name="sohoadon" class="form-control" value="<?= htmlspecialchars($_POST['sohoadon'] ?? '') ?>" required></div>
            <div class="col-md-3 mb-3"><label>Đơn hàng</label><select name="maDH" class="form-select"><option value="">-- Không chọn --</option><?php $rs = $conn->query("SELECT maDH FROM DonHang ORDER BY maDH DESC"); $sel = $_POST['maDH'] ?? ''; while ($x = $rs->fetch_assoc()) { $s = ($x['maDH'] === $sel) ? 'selected' : ''; echo "<option value='" . htmlspecialchars($x['maDH']) . "' $s>" . htmlspecialchars($x['maDH']) . "</option>"; } ?></select></div>
            <div class="col-md-3 mb-3"><label>Phiếu xuất</label><select name="maPX" class="form-select"><option value="">-- Không chọn --</option><?php $rs = $conn->query("SELECT maPX FROM PhieuXuat ORDER BY maPX DESC"); $sel = $_POST['maPX'] ?? ''; while ($x = $rs->fetch_assoc()) { $s = ($x['maPX'] === $sel) ? 'selected' : ''; echo "<option value='" . htmlspecialchars($x['maPX']) . "' $s>" . htmlspecialchars($x['maPX']) . "</option>"; } ?></select></div>
            <div class="col-md-3 mb-3"><label>Công nợ KH <span class="text-danger">*</span></label><select name="maCNKH" class="form-select" required><option value="">-- Chọn --</option><?php $rs = $conn->query("SELECT maCNKH FROM CongNoKH ORDER BY maCNKH DESC"); $sel = $_POST['maCNKH'] ?? ''; while ($x = $rs->fetch_assoc()) { $s = ($x['maCNKH'] === $sel) ? 'selected' : ''; echo "<option value='" . htmlspecialchars($x['maCNKH']) . "' $s>" . htmlspecialchars($x['maCNKH']) . "</option>"; } ?></select></div>
            <div class="col-md-3 mb-3"><label>NV lập <span class="text-danger">*</span></label><select name="maNV_Lap" class="form-select" required><option value="">-- Chọn --</option><?php $rs = $conn->query("SELECT maNV, hoten FROM NhanVien ORDER BY hoten"); $sel = $_POST['maNV_Lap'] ?? ''; while ($x = $rs->fetch_assoc()) { $s = ($x['maNV'] === $sel) ? 'selected' : ''; echo "<option value='" . htmlspecialchars($x['maNV']) . "' $s>" . htmlspecialchars($x['maNV'] . ' - ' . $x['hoten']) . "</option>"; } ?></select></div>
            <div class="col-md-2 mb-3"><label>Ngày tạo</label><input type="date" name="ngaytao" class="form-control" value="<?= htmlspecialchars($_POST['ngaytao'] ?? date('Y-m-d')) ?>"></div>
            <div class="col-md-3 mb-3"><label>Loại HĐ</label><input type="text" name="loaihoadon" class="form-control" value="<?= htmlspecialchars($_POST['loaihoadon'] ?? 'Bán hàng') ?>"></div>
            <div class="col-md-4 mb-3"><label>Địa chỉ</label><input type="text" name="diachi" class="form-control" value="<?= htmlspecialchars($_POST['diachi'] ?? '') ?>"></div>
            <div class="col-md-3 mb-3"><label>Tổng trước thuế</label><input type="number" id="ttr" name="tongtientruocthue" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($_POST['tongtientruocthue'] ?? '0') ?>"></div>
            <div class="col-md-2 mb-3"><label>VAT (%)</label><input type="number" id="vatv" name="thuevat" class="form-control" min="0" max="100" step="0.01" value="<?= htmlspecialchars($_POST['thuevat'] ?? '0') ?>"></div>
            <div class="col-md-2 mb-3"><label>Tiền thuế</label><input type="number" id="thv" class="form-control" readonly></div>
            <div class="col-md-2 mb-3"><label>Tổng tiền</label><input type="number" id="tov" class="form-control" readonly></div>
            <div class="col-md-3 mb-3"><label>PT Thanh toán</label><select name="phuongthucthanhtoan" class="form-select"><?php foreach ($VALID_PTTT as $x) { $s = (($_POST['phuongthucthanhtoan'] ?? 'Tiền mặt') === $x) ? 'selected' : ''; echo "<option $s>" . htmlspecialchars($x) . "</option>"; } ?></select></div>
            <div class="col-md-3 mb-3"><label>Trạng thái</label><select name="trangthai" class="form-select"><?php foreach ($VALID_TT as $x) { $s = (($_POST['trangthai'] ?? 'Chưa thanh toán') === $x) ? 'selected' : ''; echo "<option $s>" . htmlspecialchars($x) . "</option>"; } ?></select></div>
            <div class="col-md-3 mb-3"><label>Ngày TT</label><input type="date" name="ngaythanhtoan" class="form-control" value="<?= htmlspecialchars($_POST['ngaythanhtoan'] ?? '') ?>"></div>
            <div class="col-12 mb-3"><label>Ghi chú</label><input type="text" name="ghichu" class="form-control" value="<?= htmlspecialchars($_POST['ghichu'] ?? '') ?>"></div>
        </div>
        <button class="btn btn-success">Lưu</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
<script>
function calc(){const t=parseFloat(document.getElementById('ttr').value)||0;const v=parseFloat(document.getElementById('vatv').value)||0;const th=t*v/100;document.getElementById('thv').value=th.toFixed(2);document.getElementById('tov').value=(t+th).toFixed(2);}
document.getElementById('ttr').addEventListener('input',calc);document.getElementById('vatv').addEventListener('input',calc);calc();
</script>
</div></body></html>
