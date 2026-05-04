<?php
require_once __DIR__ . '/../../bootstrap.php';

$STATUS_OPTIONS = ['Dang lam viec', 'Tam nghi', 'Da nghi', 'Đang làm việc', 'Tạm nghỉ', 'Đã nghỉ'];
$hasMaCV = db_table_has_column($conn, 'NhanVien', 'maCV');
$hasChucVuText = db_table_has_column($conn, 'NhanVien', 'chucvu');

$chucVuMap = [];
$cvRs = $conn->query("SELECT maCV, tenCV FROM ChucVu ORDER BY tenCV");
if ($cvRs) {
    while ($cv = $cvRs->fetch_assoc()) {
        $chucVuMap[$cv['maCV']] = $cv['tenCV'];
    }
}

$idRaw = $_GET['id'] ?? '';
$row = db_fetch_one($conn, "SELECT * FROM NhanVien WHERE maNV = ? LIMIT 1", 's', [$idRaw]);
if (!$row) {
    echo "Nhân viên không tồn tại";
    exit;
}
$id = $row['maNV'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoten  = trim($_POST['hoten'] ?? '');
    $sdt    = trim($_POST['sdt'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $tt     = trim($_POST['trangthai'] ?? '');
    $maCV   = trim($_POST['maCV'] ?? '');
    $chucVuText = trim($_POST['chucvu'] ?? '');
    $errors = [];

    if ($hoten === '') $errors[] = '[R01] Họ tên không được để trống.';
    if ($hasMaCV && $maCV === '') $errors[] = '[R01] Chức vụ không được để trống.';
    if ($hasChucVuText && $chucVuText === '') $errors[] = '[R01] Chức vụ không được để trống.';
    if (mb_strlen($hoten) > 255) $errors[] = '[R03] Họ tên tối đa 255 ký tự.';
    if (mb_strlen($email) > 100) $errors[] = '[R03] Email tối đa 100 ký tự.';
    if ($tt !== '' && !in_array($tt, $STATUS_OPTIONS, true)) $errors[] = '[R07] Trạng thái không hợp lệ.';
    if ($hasMaCV && $maCV !== '' && !db_exists($conn, "SELECT maCV FROM ChucVu WHERE maCV = ? LIMIT 1", 's', [$maCV])) {
        $errors[] = "[R06] Mã chức vụ '$maCV' không tồn tại.";
    }
    if ($sdt !== '' && !preg_match('/^\d{1,20}$/', $sdt)) {
        $errors[] = '[R08] SĐT chỉ gồm chữ số, tối đa 20 ký tự.';
    }

    if (empty($errors)) {
        try {
            if ($hasMaCV) {
                $stmt = $conn->prepare(
                    "UPDATE NhanVien SET hoten=?, sdt=?, email=?, trangthai=?, maCV=? WHERE maNV=?"
                );
                $stmt->bind_param('ssssss', $hoten, $sdt, $email, $tt, $maCV, $id);
            } else {
                $stmt = $conn->prepare(
                    "UPDATE NhanVien SET hoten=?, sdt=?, email=?, trangthai=?, chucvu=? WHERE maNV=?"
                );
                $stmt->bind_param('ssssss', $hoten, $sdt, $email, $tt, $chucVuText, $id);
            }
            $stmt->execute();
            $stmt->close();
            header("Location: index.php");
            exit;
        } catch (Throwable $e) {
            error_log('[NhanVien-Edit] ' . $e->getMessage());
            $errors[] = 'Lỗi khi cập nhật. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:900px; margin:0 auto;">
    <h4 class="fw-bold mb-4">Sửa Nhân Viên</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Mã NV</label>
                <input class="form-control" value="<?= htmlspecialchars($id) ?>" readonly>
            </div>
            <div class="col-md-8 mb-3">
                <label>Họ tên <span class="text-danger">*</span></label>
                <input type="text" name="hoten" class="form-control"
                       value="<?= htmlspecialchars($_POST['hoten'] ?? $row['hoten']) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label>SĐT</label>
                <input type="text" name="sdt" class="form-control"
                       value="<?= htmlspecialchars($_POST['sdt'] ?? ($row['sdt'] ?? '')) ?>">
            </div>
            <div class="col-md-8 mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($_POST['email'] ?? ($row['email'] ?? '')) ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label>Trạng thái</label>
                <select name="trangthai" class="form-select">
                    <?php
                    $cur = $_POST['trangthai'] ?? ($row['trangthai'] ?? '');
                    foreach (['Đang làm việc', 'Tạm nghỉ', 'Đã nghỉ'] as $x) {
                        $s = ($cur === $x) ? 'selected' : '';
                        echo "<option $s>" . htmlspecialchars($x) . "</option>";
                    }
                    if ($cur !== '' && !in_array($cur, ['Đang làm việc', 'Tạm nghỉ', 'Đã nghỉ'], true)) {
                        echo "<option selected>" . htmlspecialchars($cur) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <?php if ($hasMaCV): ?>
                    <label>Chức vụ <span class="text-danger">*</span></label>
                    <select name="maCV" class="form-select" required>
                        <option value="">-- Chọn --</option>
                        <?php
                        $curCV = $_POST['maCV'] ?? ($row['maCV'] ?? '');
                        foreach ($chucVuMap as $code => $name) {
                            $s = ($curCV === $code) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($code) . "' $s>"
                                . htmlspecialchars($code . ' - ' . $name) . "</option>";
                        }
                        ?>
                    </select>
                <?php else: ?>
                    <label>Chức vụ <span class="text-danger">*</span></label>
                    <input type="text" name="chucvu" class="form-control"
                           value="<?= htmlspecialchars($_POST['chucvu'] ?? ($row['chucvu'] ?? '')) ?>" required>
                <?php endif; ?>
            </div>
        </div>
        <button class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
</div></body></html>

