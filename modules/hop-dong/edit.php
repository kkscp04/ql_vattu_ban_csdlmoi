<?php
require_once __DIR__ . '/../../bootstrap.php';

$VALID_TT   = ['Mới tạo', 'Đang thực hiện', 'Hoàn thành', 'Hủy'];
$VALID_PTTT = ['Tiền mặt', 'Chuyển khoản', 'Công nợ'];

$idRaw = $_GET['id'] ?? '';
$row = db_fetch_one($conn, "SELECT * FROM HopDong WHERE maHDong = ? LIMIT 1", 's', [$idRaw]);
if (!$row) { echo "Hợp đồng không tồn tại"; exit; }
$id = $row['maHDong'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maKH      = trim($_POST['maKH']         ?? '');
    $maNV      = trim($_POST['maNV_Lap']     ?? '');
    $ngayHL    = $_POST['ngayhieuluc']       ?? '';
    $ngayHH    = $_POST['ngayhethan']        ?? '';
    $ngayKy    = $_POST['ngayky']            ?? '';
    $truoc     = (float) ($_POST['tongtruocthue'] ?? 0);
    $thue      = (float) ($_POST['thue']     ?? 0);
    $pttt      = trim($_POST['phuongthucthanhtoan'] ?? '');
    $trangthai = trim($_POST['trangthai']    ?? '');
    $errors    = [];

    if ($maKH === '') $errors[] = '[R01] Khách hàng không được để trống.';
    if ($maNV === '') $errors[] = '[R01] Nhân viên không được để trống.';
    if ($maKH !== '' && !db_exists($conn, "SELECT maKH FROM KhachHang WHERE maKH = ? LIMIT 1", 's', [$maKH]))
        $errors[] = "[R06] Khách hàng '$maKH' không tồn tại.";
    if ($maNV !== '' && !db_exists($conn, "SELECT maNV FROM NhanVien WHERE maNV = ? LIMIT 1", 's', [$maNV]))
        $errors[] = "[R06] Nhân viên '$maNV' không tồn tại.";
    if (!in_array($trangthai, $VALID_TT, true))   $errors[] = '[R07] Trạng thái không hợp lệ.';
    if (!in_array($pttt, $VALID_PTTT, true))      $errors[] = '[R07] Phương thức thanh toán không hợp lệ.';
    if ($truoc < 0) $errors[] = '[R09] Tổng trước thuế phải >= 0.';
    if ($thue < 0)  $errors[] = '[R09] Thuế phải >= 0.';
    if ($ngayHL !== '' && strtotime($ngayHL) === false) $errors[] = '[R10] Ngày hiệu lực không hợp lệ.';
    if ($ngayHH !== '' && strtotime($ngayHH) === false) $errors[] = '[R10] Ngày hết hạn không hợp lệ.';
    if ($ngayHL !== '' && $ngayHH !== '' && strtotime($ngayHL) !== false && strtotime($ngayHH) !== false) {
        if (strtotime($ngayHH) < strtotime($ngayHL))
            $errors[] = '[R11] Ngày hết hạn phải >= ngày hiệu lực.';
    }

    if (empty($errors)) {
        $tong      = $truoc + $thue;
        $ngayHLFmt = $ngayHL !== '' ? date('Y-m-d H:i:s', strtotime($ngayHL)) : null;
        $ngayHHFmt = $ngayHH !== '' ? date('Y-m-d H:i:s', strtotime($ngayHH)) : null;
        $ngayKyFmt = $ngayKy !== '' ? date('Y-m-d H:i:s', strtotime($ngayKy))  : null;

        try {
            $stmt = $conn->prepare(
                "UPDATE HopDong
                 SET maKH=?, maNV_Lap=?, ngayky=?, ngayhieuluc=?, ngayhethan=?,
                     tongtruocthue=?, thue=?, tonggiatriHopDong=?,
                     phuongthucthanhtoan=?, trangthai=?
                 WHERE maHDong=?"
            );
            $stmt->bind_param('sssssdddsss',
                $maKH, $maNV, $ngayKyFmt, $ngayHLFmt, $ngayHHFmt,
                $truoc, $thue, $tong,
                $pttt, $trangthai, $id
            );
            $stmt->execute();
            $stmt->close();
            header("Location: index.php"); exit;
        } catch (Throwable $e) {
            error_log('[HopDong-Edit] ' . $e->getMessage());
            $errors[] = 'Lỗi khi cập nhật. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
$form = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $row;
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:1000px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Sửa Hợp Đồng #<?= htmlspecialchars($id) ?></h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="row">
            <div class="col-md-3 mb-3"><label>Mã HĐ</label><input class="form-control" value="<?= htmlspecialchars($id) ?>" readonly></div>
            <div class="col-md-4 mb-3">
                <label>Khách hàng <span class="text-danger">*</span></label>
                <select name="maKH" class="form-select" required>
                    <?php $rs=$conn->query("SELECT maKH,tenKH FROM KhachHang ORDER BY tenKH"); $sel=$form['maKH']??''; while($x=$rs->fetch_assoc()){$s=($x['maKH']===$sel)?'selected':''; echo "<option value='".htmlspecialchars($x['maKH'])."' $s>".htmlspecialchars($x['tenKH'])."</option>";} ?>
                </select>
            </div>
            <div class="col-md-5 mb-3">
                <label>Nhân viên lập <span class="text-danger">*</span></label>
                <select name="maNV_Lap" class="form-select" required>
                    <?php $rs=$conn->query("SELECT maNV,hoten FROM NhanVien ORDER BY hoten"); $sel=$form['maNV_Lap']??''; while($x=$rs->fetch_assoc()){$s=($x['maNV']===$sel)?'selected':''; echo "<option value='".htmlspecialchars($x['maNV'])."' $s>".htmlspecialchars($x['maNV'].' – '.$x['hoten'])."</option>";} ?>
                </select>
            </div>
            <div class="col-md-3 mb-3"><label>Ngày ký</label><input type="date" name="ngayky" class="form-control" value="<?= !empty($row['ngayky']) ? date('Y-m-d', strtotime($row['ngayky'])) : '' ?>"></div>
            <div class="col-md-3 mb-3"><label>Ngày hiệu lực</label><input type="date" name="ngayhieuluc" class="form-control" value="<?= !empty($row['ngayhieuluc']) ? date('Y-m-d', strtotime($row['ngayhieuluc'])) : '' ?>"></div>
            <div class="col-md-3 mb-3"><label>Ngày hết hạn</label><input type="date" name="ngayhethan" class="form-control" value="<?= !empty($row['ngayhethan']) ? date('Y-m-d', strtotime($row['ngayhethan'])) : '' ?>"></div>
            <div class="col-md-3 mb-3"><label>Tổng trước thuế</label><input type="number" id="ttr" name="tongtruocthue" class="form-control" min="0" step="0.01" value="<?= (float)($form['tongtruocthue'] ?? 0) ?>"></div>
            <div class="col-md-3 mb-3"><label>Thuế</label><input type="number" id="thue" name="thue" class="form-control" min="0" step="0.01" value="<?= (float)($form['thue'] ?? 0) ?>"></div>
            <div class="col-md-3 mb-3"><label>Tổng giá trị HĐ</label><input type="number" id="tong" class="form-control" readonly value="<?= (float)($form['tonggiatriHopDong'] ?? 0) ?>"></div>
            <div class="col-md-3 mb-3">
                <label>Phương thức TT</label>
                <select name="phuongthucthanhtoan" class="form-select">
                    <?php $cur=$form['phuongthucthanhtoan']??'Tiền mặt'; foreach($VALID_PTTT as $x){$s=($cur===$x)?'selected':''; echo "<option $s>$x</option>";} ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label>Trạng thái</label>
                <select name="trangthai" class="form-select">
                    <?php $cur=$form['trangthai']??'Mới tạo'; foreach($VALID_TT as $x){$s=($cur===$x)?'selected':''; echo "<option $s>$x</option>";} ?>
                </select>
            </div>
        </div>
        <button class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
<script>
function calc(){const t=parseFloat(document.getElementById('ttr').value)||0;const th=parseFloat(document.getElementById('thue').value)||0;document.getElementById('tong').value=(t+th).toFixed(2);}
document.getElementById('ttr').addEventListener('input',calc);document.getElementById('thue').addEventListener('input',calc);calc();
</script>
</div></body></html>
