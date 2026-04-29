<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$rs = $conn->query("SELECT * FROM HopDong WHERE maHDong='$id'");
$row = $rs ? $rs->fetch_assoc() : null;
if (!$row) { echo "Khong tim thay hop dong"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maKH = $conn->real_escape_string(trim($_POST['maKH'] ?? ''));
    $maNV = $conn->real_escape_string(trim($_POST['maNV_Lap'] ?? ''));
    $ngayky = $conn->real_escape_string($_POST['ngayky'] ?? date('Y-m-d'));
    $ngayhieuluc = $conn->real_escape_string($_POST['ngayhieuluc'] ?? '');
    $ngayhethan = $conn->real_escape_string($_POST['ngayhethan'] ?? '');
    $thoigiangiaohang = $conn->real_escape_string($_POST['thoigiangiaohang'] ?? '');
    $thoihanthanhtoan = (int) ($_POST['thoihanthanhtoan'] ?? 0);
    $tongtruocthue = (float) ($_POST['tongtruocthue'] ?? 0);
    $thue = (float) ($_POST['thue'] ?? 0);
    $tonggiatri = $tongtruocthue + $thue;
    $pttt = $conn->real_escape_string(trim($_POST['phuongthucthanhtoan'] ?? ''));
    $trangthai = $conn->real_escape_string(trim($_POST['trangthai'] ?? ''));

    if ($maKH === '' || $maNV === '') {
        $error = "Vui long chon khach hang va nhan vien.";
    } else {
        $sql = "UPDATE HopDong SET
                maKH='$maKH',
                maNV_Lap='$maNV',
                thoigiangiaohang=" . ($thoigiangiaohang !== '' ? "'$thoigiangiaohang 00:00:00'" : "NULL") . ",
                ngayky='$ngayky 00:00:00',
                thoihanthanhtoan=$thoihanthanhtoan,
                tongtruocthue=$tongtruocthue,
                thue=$thue,
                tonggiatriHopDong=$tonggiatri,
                ngayhieuluc=" . ($ngayhieuluc !== '' ? "'$ngayhieuluc 00:00:00'" : "NULL") . ",
                trangthai='$trangthai',
                ngayhethan=" . ($ngayhethan !== '' ? "'$ngayhethan 00:00:00'" : "NULL") . ",
                phuongthucthanhtoan='$pttt'
                WHERE maHDong='$id'";
        if ($conn->query($sql)) { header("Location: index.php"); exit; }
        $error = "Loi: " . $conn->error;
    }
}

$form = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $row;
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4" style="max-width:1000px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Sua Hop Dong</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-4 mb-3"><label>Ma HD</label><input type="text" class="form-control" value="<?= htmlspecialchars($row['maHDong']) ?>" readonly></div>
            <div class="col-md-4 mb-3"><label>Khach hang *</label><select name="maKH" class="form-select" required><?php $r=$conn->query("SELECT maKH,tenKH FROM KhachHang ORDER BY tenKH"); while($x=$r->fetch_assoc()){ $s=(($x['maKH'])===($form['maKH'] ?? ''))?'selected':''; echo "<option value='{$x['maKH']}' $s>".htmlspecialchars($x['tenKH'])."</option>"; } ?></select></div>
            <div class="col-md-4 mb-3"><label>Nhan vien lap *</label><select name="maNV_Lap" class="form-select" required><?php $r=$conn->query("SELECT maNV,hoten FROM NhanVien ORDER BY hoten"); while($x=$r->fetch_assoc()){ $s=(($x['maNV'])===($form['maNV_Lap'] ?? ''))?'selected':''; echo "<option value='{$x['maNV']}' $s>".htmlspecialchars($x['maNV'].' - '.$x['hoten'])."</option>"; } ?></select></div>

            <div class="col-md-3 mb-3"><label>Ngay ky</label><input type="date" name="ngayky" class="form-control" value="<?= !empty($form['ngayky']) ? date('Y-m-d', strtotime($form['ngayky'])) : '' ?>"></div>
            <div class="col-md-3 mb-3"><label>Ngay hieu luc</label><input type="date" name="ngayhieuluc" class="form-control" value="<?= !empty($form['ngayhieuluc']) ? date('Y-m-d', strtotime($form['ngayhieuluc'])) : '' ?>"></div>
            <div class="col-md-3 mb-3"><label>Ngay het han</label><input type="date" name="ngayhethan" class="form-control" value="<?= !empty($form['ngayhethan']) ? date('Y-m-d', strtotime($form['ngayhethan'])) : '' ?>"></div>
            <div class="col-md-3 mb-3"><label>Thoi gian giao hang</label><input type="date" name="thoigiangiaohang" class="form-control" value="<?= !empty($form['thoigiangiaohang']) ? date('Y-m-d', strtotime($form['thoigiangiaohang'])) : '' ?>"></div>

            <div class="col-md-3 mb-3"><label>Thoi han thanh toan</label><input type="number" name="thoihanthanhtoan" class="form-control" min="0" value="<?= htmlspecialchars((string) ($form['thoihanthanhtoan'] ?? 0)) ?>"></div>
            <div class="col-md-3 mb-3"><label>Tong truoc thue</label><input type="number" id="tongTruocThue" name="tongtruocthue" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars((string) ($form['tongtruocthue'] ?? 0)) ?>"></div>
            <div class="col-md-3 mb-3"><label>Tien thue</label><input type="number" id="thueHopDong" name="thue" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars((string) ($form['thue'] ?? 0)) ?>"></div>
            <div class="col-md-3 mb-3"><label>Tong gia tri HD</label><input type="number" id="tongGiaTriHopDong" name="tonggiatriHopDong" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars((string) ($form['tonggiatriHopDong'] ?? 0)) ?>" readonly></div>

            <div class="col-md-6 mb-3">
                <label>Phuong thuc thanh toan</label>
                <?php $ptttValue = $form['phuongthucthanhtoan'] ?? 'Tien mat'; ?>
                <select name="phuongthucthanhtoan" class="form-select">
                    <option <?= $ptttValue === 'Tien mat' ? 'selected' : '' ?>>Tien mat</option>
                    <option <?= $ptttValue === 'Chuyen khoan' ? 'selected' : '' ?>>Chuyen khoan</option>
                    <option <?= $ptttValue === 'Cong no' ? 'selected' : '' ?>>Cong no</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label>Trang thai</label>
                <?php $trangThaiValue = $form['trangthai'] ?? 'Moi tao'; ?>
                <select name="trangthai" class="form-select">
                    <option <?= $trangThaiValue === 'Moi tao' ? 'selected' : '' ?>>Moi tao</option>
                    <option <?= $trangThaiValue === 'Dang thuc hien' ? 'selected' : '' ?>>Dang thuc hien</option>
                    <option <?= $trangThaiValue === 'Hoan thanh' ? 'selected' : '' ?>>Hoan thanh</option>
                    <option <?= $trangThaiValue === 'Tam dung' ? 'selected' : '' ?>>Tam dung</option>
                    <option <?= $trangThaiValue === 'Da huy' ? 'selected' : '' ?>>Da huy</option>
                </select>
            </div>
        </div>

        <button class="btn btn-warning">Cap nhat</button>
        <a href="index.php" class="btn btn-secondary">Huy</a>
    </form>
</div>

<script>
function calcContractTotal() {
    const truoc = parseFloat(document.getElementById('tongTruocThue').value) || 0;
    const thue = parseFloat(document.getElementById('thueHopDong').value) || 0;
    document.getElementById('tongGiaTriHopDong').value = (truoc + thue).toFixed(2);
}

document.getElementById('tongTruocThue').addEventListener('input', calcContractTotal);
document.getElementById('thueHopDong').addEventListener('input', calcContractTotal);
calcContractTotal();
</script>

</div>
</body>
</html>
