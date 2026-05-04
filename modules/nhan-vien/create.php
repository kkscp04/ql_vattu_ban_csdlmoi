<?php
require_once __DIR__ . '/../../bootstrap.php';

$VALID_TT = ['Dang lam viec', 'Tam nghi', 'Da nghi'];
$hasMaCV = db_table_has_column($conn, 'NhanVien', 'maCV');
$hasChucVuText = db_table_has_column($conn, 'NhanVien', 'chucvu');

$chucVuMap = [];
$cvRs = $conn->query("SELECT maCV, tenCV FROM ChucVu ORDER BY tenCV");
if ($cvRs) {
    while ($cv = $cvRs->fetch_assoc()) {
        $chucVuMap[$cv['maCV']] = $cv['tenCV'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = trim($_POST['maNV'] ?? '');
    $hoten = trim($_POST['hoten'] ?? '');
    $sdt = trim($_POST['sdt'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tt = trim($_POST['trangthai'] ?? 'Dang lam viec');
    $maCV = trim($_POST['maCV'] ?? '');
    $chucVuText = trim($_POST['chucvu'] ?? '');
    $errors = [];

    if ($ma === '') $errors[] = '[R01] Ma NV khong duoc de trong.';
    if ($hoten === '') $errors[] = '[R01] Ho ten khong duoc de trong.';
    if ($hasMaCV && $maCV === '') $errors[] = '[R01] Ma chuc vu khong duoc de trong.';
    if ($hasChucVuText && $chucVuText === '') $errors[] = '[R01] Chuc vu khong duoc de trong.';

    if ($ma !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)) {
        $errors[] = '[R02] Ma NV chi gom chu, so, dau . _ - va toi da 50 ky tu.';
    }

    if ($ma !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)) {
        if (db_exists($conn, "SELECT maNV FROM NhanVien WHERE maNV = ? LIMIT 1", 's', [$ma])) {
            $errors[] = "[R04] Ma NV '$ma' da ton tai.";
        }
    }

    if (!in_array($tt, $VALID_TT, true)) $errors[] = '[R07] Trang thai khong hop le.';

    if ($hasMaCV && $maCV !== '' && !db_exists($conn, "SELECT maCV FROM ChucVu WHERE maCV = ? LIMIT 1", 's', [$maCV])) {
        $errors[] = "[R06] Ma chuc vu '$maCV' khong ton tai.";
    }

    if ($sdt !== '' && !preg_match('/^\d{1,20}$/', $sdt)) {
        $errors[] = '[R08] SDT chi gom chu so, toi da 20 ky tu.';
    }

    if (empty($errors)) {
        try {
            if ($hasMaCV) {
                $stmt = $conn->prepare("INSERT INTO NhanVien (maNV, hoten, sdt, email, trangthai, maCV) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param('ssssss', $ma, $hoten, $sdt, $email, $tt, $maCV);
            } else {
                $stmt = $conn->prepare("INSERT INTO NhanVien (maNV, hoten, sdt, email, trangthai, chucvu) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param('ssssss', $ma, $hoten, $sdt, $email, $tt, $chucVuText);
            }
            $stmt->execute();
            $stmt->close();
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            error_log('[NhanVien-Create] ' . $e->getMessage());
            $errors[] = 'Loi khi luu. Vui long thu lai.';
        }
    }

    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:900px; margin:0 auto;">
    <h4 class="fw-bold mb-4">Them Nhan Vien</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-4 mb-3"><label>Ma NV *</label><input type="text" name="maNV" class="form-control" value="<?= htmlspecialchars($_POST['maNV'] ?? '') ?>" required></div>
            <div class="col-md-8 mb-3"><label>Ho ten *</label><input type="text" name="hoten" class="form-control" value="<?= htmlspecialchars($_POST['hoten'] ?? '') ?>" required></div>
            <div class="col-md-4 mb-3"><label>SDT</label><input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($_POST['sdt'] ?? '') ?>"></div>
            <div class="col-md-8 mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
            <div class="col-md-6 mb-3">
                <label>Trang thai</label>
                <select name="trangthai" class="form-select">
                    <?php foreach ($VALID_TT as $x) { $s = (($_POST['trangthai'] ?? 'Dang lam viec') === $x) ? 'selected' : ''; echo "<option $s>$x</option>"; } ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <?php if ($hasMaCV): ?>
                    <label>Ma chuc vu *</label>
                    <select name="maCV" class="form-select" required>
                        <option value="">-- Chon --</option>
                        <?php foreach ($chucVuMap as $code => $name) { $s = (($_POST['maCV'] ?? '') === $code) ? 'selected' : ''; echo "<option value='" . htmlspecialchars($code) . "' $s>" . htmlspecialchars($code . ' - ' . $name) . "</option>"; } ?>
                    </select>
                <?php else: ?>
                    <label>Chuc vu *</label>
                    <input type="text" name="chucvu" class="form-control" value="<?= htmlspecialchars($_POST['chucvu'] ?? '') ?>" required>
                <?php endif; ?>
            </div>
        </div>
        <button class="btn btn-success">Luu</button>
        <a href="index.php" class="btn btn-secondary">Huy</a>
    </form>
</div>
</div></body></html>

