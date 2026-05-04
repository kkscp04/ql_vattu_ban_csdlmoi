<?php
require_once __DIR__ . '/../../bootstrap.php';

$VALID_TT = ['Moi tao', 'Dang thuc hien', 'Hoan thanh', 'Huy'];
$VALID_PTTT = ['Tien mat', 'Chuyen khoan', 'Cong no'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = trim($_POST['maHDong'] ?? '');
    $maKH = trim($_POST['maKH'] ?? '');
    $maNV = trim($_POST['maNV_Lap'] ?? '');
    $ngayKy = $_POST['ngayky'] ?? '';
    $ngayHL = $_POST['ngayhieuluc'] ?? '';
    $ngayHH = $_POST['ngayhethan'] ?? '';
    $truoc = (float) ($_POST['tongtruocthue'] ?? 0);
    $thuePercent = (float) ($_POST['thue'] ?? 0);
    $pttt = trim($_POST['phuongthucthanhtoan'] ?? 'Tien mat');
    $trangthai = trim($_POST['trangthai'] ?? 'Moi tao');
    $errors = [];

    if ($ma === '') $errors[] = '[R01] Ma hop dong khong duoc de trong.';
    if ($maKH === '') $errors[] = '[R01] Khach hang khong duoc de trong.';
    if ($maNV === '') $errors[] = '[R01] Nhan vien khong duoc de trong.';

    if ($ma !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)) {
        $errors[] = '[R02] Ma hop dong chi gom chu, so, dau . _ - va toi da 50 ky tu.';
    }

    if ($ma !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)) {
        if (db_exists($conn, "SELECT maHDong FROM HopDong WHERE maHDong = ? LIMIT 1", 's', [$ma])) {
            $errors[] = "[R04] Ma hop dong '$ma' da ton tai.";
        }
    }

    if ($maKH !== '' && !db_exists($conn, "SELECT maKH FROM KhachHang WHERE maKH = ? LIMIT 1", 's', [$maKH])) {
        $errors[] = "[R06] Khach hang '$maKH' khong ton tai.";
    }
    if ($maNV !== '' && !db_exists($conn, "SELECT maNV FROM NhanVien WHERE maNV = ? LIMIT 1", 's', [$maNV])) {
        $errors[] = "[R06] Nhan vien '$maNV' khong ton tai.";
    }

    if (!in_array($trangthai, $VALID_TT, true)) $errors[] = '[R07] Trang thai khong hop le.';
    if (!in_array($pttt, $VALID_PTTT, true)) $errors[] = '[R07] Phuong thuc thanh toan khong hop le.';

    if ($truoc < 0) $errors[] = '[R09] Tong truoc thue phai >= 0.';
    if ($thuePercent < 0 || $thuePercent > 100) $errors[] = '[R09] Thue (%) phai trong [0..100].';

    if ($ngayKy !== '' && strtotime($ngayKy) === false) $errors[] = '[R10] Ngay ky khong hop le.';
    if ($ngayHL !== '' && strtotime($ngayHL) === false) $errors[] = '[R10] Ngay hieu luc khong hop le.';
    if ($ngayHH !== '' && strtotime($ngayHH) === false) $errors[] = '[R10] Ngay het han khong hop le.';

    if ($ngayHL !== '' && $ngayHH !== '' && strtotime($ngayHL) !== false && strtotime($ngayHH) !== false) {
        if (strtotime($ngayHH) < strtotime($ngayHL)) {
            $errors[] = '[R11] Ngay het han phai >= ngay hieu luc.';
        }
    }

    if (empty($errors)) {
        $thue = round($truoc * $thuePercent / 100, 2);
        $tong = $truoc + $thue;
        $ngayKyFmt = $ngayKy !== '' ? date('Y-m-d H:i:s', strtotime($ngayKy)) : null;
        $ngayHLFmt = $ngayHL !== '' ? date('Y-m-d H:i:s', strtotime($ngayHL)) : null;
        $ngayHHFmt = $ngayHH !== '' ? date('Y-m-d H:i:s', strtotime($ngayHH)) : null;

        try {
            $stmt = $conn->prepare(
                "INSERT INTO HopDong
                 (maHDong, maKH, maNV_Lap, ngayky, ngayhieuluc, ngayhethan,
                  tongtruocthue, thue, tonggiatriHopDong, phuongthucthanhtoan, trangthai)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)"
            );
            $stmt->bind_param(
                'ssssssdddss',
                $ma,
                $maKH,
                $maNV,
                $ngayKyFmt,
                $ngayHLFmt,
                $ngayHHFmt,
                $truoc,
                $thue,
                $tong,
                $pttt,
                $trangthai
            );
            $stmt->execute();
            $stmt->close();
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            error_log('[HopDong-Create] ' . $e->getMessage());
            $errors[] = 'Loi khi luu. Vui long thu lai.';
        }
    }

    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4" style="max-width:1000px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Them Hop Dong</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-3 mb-3"><label>Ma HD *</label><input type="text" name="maHDong" class="form-control" value="<?= htmlspecialchars($_POST['maHDong'] ?? '') ?>" required></div>
            <div class="col-md-4 mb-3"><label>Khach hang *</label><select name="maKH" class="form-select" required><option value="">-- Chon --</option><?php $r=$conn->query("SELECT maKH,tenKH FROM KhachHang ORDER BY tenKH"); $sel=$_POST['maKH']??''; while($x=$r->fetch_assoc()){ $s=($x['maKH']===$sel)?'selected':''; echo "<option value='" . htmlspecialchars($x['maKH']) . "' $s>" . htmlspecialchars($x['tenKH']) . "</option>"; } ?></select></div>
            <div class="col-md-5 mb-3"><label>Nhan vien lap *</label><select name="maNV_Lap" class="form-select" required><option value="">-- Chon --</option><?php $r=$conn->query("SELECT maNV,hoten FROM NhanVien ORDER BY hoten"); $sel=$_POST['maNV_Lap']??''; while($x=$r->fetch_assoc()){ $s=($x['maNV']===$sel)?'selected':''; echo "<option value='" . htmlspecialchars($x['maNV']) . "' $s>" . htmlspecialchars($x['maNV'].' - '.$x['hoten']) . "</option>"; } ?></select></div>

            <div class="col-md-3 mb-3"><label>Ngay ky</label><input type="date" name="ngayky" class="form-control" value="<?= htmlspecialchars($_POST['ngayky'] ?? '') ?>"></div>
            <div class="col-md-3 mb-3"><label>Ngay hieu luc</label><input type="date" name="ngayhieuluc" class="form-control" value="<?= htmlspecialchars($_POST['ngayhieuluc'] ?? '') ?>"></div>
            <div class="col-md-3 mb-3"><label>Ngay het han</label><input type="date" name="ngayhethan" class="form-control" value="<?= htmlspecialchars($_POST['ngayhethan'] ?? '') ?>"></div>

            <div class="col-md-3 mb-3"><label>Tong truoc thue</label><input type="number" id="ttr" name="tongtruocthue" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($_POST['tongtruocthue'] ?? '0') ?>"></div>
            <div class="col-md-3 mb-3"><label>Thue (%)</label><input type="number" id="thue" name="thue" class="form-control" min="0" max="100" step="0.01" value="<?= htmlspecialchars($_POST['thue'] ?? '0') ?>"></div>
            <div class="col-md-3 mb-3"><label>Tong gia tri HD</label><input type="number" id="tong" class="form-control" readonly></div>

            <div class="col-md-3 mb-3"><label>Phuong thuc TT</label><select name="phuongthucthanhtoan" class="form-select"><?php foreach($VALID_PTTT as $x){$s=(($_POST['phuongthucthanhtoan'] ?? 'Tien mat')===$x)?'selected':''; echo "<option $s>$x</option>";} ?></select></div>
            <div class="col-md-3 mb-3"><label>Trang thai</label><select name="trangthai" class="form-select"><?php foreach($VALID_TT as $x){$s=(($_POST['trangthai'] ?? 'Moi tao')===$x)?'selected':''; echo "<option $s>$x</option>";} ?></select></div>
        </div>

        <button class="btn btn-success">Luu</button>
        <a href="index.php" class="btn btn-secondary">Huy</a>
    </form>
</div>

<script>
function calcContractTotal() {
    const truoc = parseFloat(document.getElementById('ttr').value) || 0;
    const thuePct = parseFloat(document.getElementById('thue').value) || 0;
    const tienThue = truoc * thuePct / 100;
    document.getElementById('tong').value = (truoc + tienThue).toFixed(2);
}
document.getElementById('ttr').addEventListener('input', calcContractTotal);
document.getElementById('thue').addEventListener('input', calcContractTotal);
calcContractTotal();
</script>

</div>
</body>
</html>
