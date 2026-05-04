<?php
require_once __DIR__ . '/../../bootstrap.php';

$VALID_TT = ['Đang hợp tác', 'Ngừng hợp tác'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma      = trim($_POST['maNCC']    ?? '');
    $ten     = trim($_POST['tenNCC']   ?? '');
    $diachi  = trim($_POST['diachi']   ?? '');
    $sdt     = trim($_POST['sdt']      ?? '');
    $email   = trim($_POST['email']    ?? '');
    $masothue = trim($_POST['masothue'] ?? '');
    $stk     = trim($_POST['stk']      ?? '');
    $tt      = trim($_POST['trangthai'] ?? 'Đang hợp tác');
    $errors  = [];

    if ($ma === '')  $errors[] = '[R01] Mã NCC không được để trống.';
    if ($ten === '') $errors[] = '[R01] Tên NCC không được để trống.';
    if ($ma !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma))
        $errors[] = '[R02] Mã NCC chỉ gồm chữ, số, dấu . _ - và tối đa 50 ký tự.';
    if (mb_strlen($ten) > 255)    $errors[] = '[R03] Tên NCC tối đa 255 ký tự.';
    if (mb_strlen($diachi) > 255) $errors[] = '[R03] Địa chỉ tối đa 255 ký tự.';
    if (mb_strlen($email) > 100)  $errors[] = '[R03] Email tối đa 100 ký tự.';
    if ($ma !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)) {
        if (db_exists($conn, "SELECT maNCC FROM NhaCungCap WHERE maNCC = ? LIMIT 1", 's', [$ma]))
            $errors[] = "[R04] Mã NCC '$ma' đã tồn tại.";
    }
    if (!in_array($tt, $VALID_TT, true))
        $errors[] = '[R07] Trạng thái không hợp lệ.';
    if ($sdt !== '' && !preg_match('/^\d{1,10}$/', $sdt))
        $errors[] = '[R08] SĐT chỉ gồm chữ số, tối đa 10 ký tự.';

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO NhaCungCap (maNCC, tenNCC, diachi, sdt, email, masothue, stk, trangthai)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('ssssssss', $ma, $ten, $diachi, $sdt, $email, $masothue, $stk, $tt);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php"); exit;
        } catch (Throwable $e) {
            error_log('[NhaCungCap-Create] ' . $e->getMessage());
            $errors[] = 'Lỗi khi lưu. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:900px; margin:0 auto;">
    <h4 class="fw-bold mb-4">Thêm Nhà Cung Cấp</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="row">
            <div class="col-md-4 mb-3"><label>Mã NCC <span class="text-danger">*</span></label><input type="text" name="maNCC" class="form-control" value="<?= htmlspecialchars($_POST['maNCC'] ?? '') ?>" required></div>
            <div class="col-md-8 mb-3"><label>Tên NCC <span class="text-danger">*</span></label><input type="text" name="tenNCC" class="form-control" value="<?= htmlspecialchars($_POST['tenNCC'] ?? '') ?>" required></div>
            <div class="col-md-6 mb-3"><label>Địa chỉ</label><input type="text" name="diachi" class="form-control" value="<?= htmlspecialchars($_POST['diachi'] ?? '') ?>"></div>
            <div class="col-md-3 mb-3"><label>SĐT</label><input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($_POST['sdt'] ?? '') ?>"></div>
            <div class="col-md-3 mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label>Mã số thuế</label><input type="text" name="masothue" class="form-control" value="<?= htmlspecialchars($_POST['masothue'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label>Số tài khoản</label><input type="text" name="stk" class="form-control" value="<?= htmlspecialchars($_POST['stk'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3">
                <label>Trạng thái</label>
                <select name="trangthai" class="form-select">
                    <?php foreach ($VALID_TT as $x) { $s = (($_POST['trangthai'] ?? 'Đang hợp tác') === $x) ? 'selected' : ''; echo "<option $s>$x</option>"; } ?>
                </select>
            </div>
        </div>
        <button class="btn btn-success">Lưu</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
</div></body></html>