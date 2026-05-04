<?php
require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma     = trim($_POST['maKH']   ?? '');
    $ten    = trim($_POST['tenKH']  ?? '');
    $loai   = trim($_POST['loaiKH'] ?? '');
    $diachi = trim($_POST['diachi'] ?? '');
    $sdt    = trim($_POST['sdt']    ?? '');
    $errors = [];

    if ($ma === '')  $errors[] = '[R01] Mã KH không được để trống.';
    if ($ten === '') $errors[] = '[R01] Tên khách hàng không được để trống.';
    if ($ma !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma))
        $errors[] = '[R02] Mã KH chỉ gồm chữ, số, dấu . _ - và tối đa 50 ký tự.';
    if (mb_strlen($ten) > 255)    $errors[] = '[R03] Tên KH tối đa 255 ký tự.';
    if (mb_strlen($diachi) > 255) $errors[] = '[R03] Địa chỉ tối đa 255 ký tự.';
    if ($ma !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)) {
        if (db_exists($conn, "SELECT maKH FROM KhachHang WHERE maKH = ? LIMIT 1", 's', [$ma]))
            $errors[] = "[R04] Mã KH '$ma' đã tồn tại.";
    }
    if ($sdt !== '' && !preg_match('/^\d{1,10}$/', $sdt))
        $errors[] = '[R08] Số điện thoại chỉ gồm chữ số, tối đa 10 ký tự.';

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO KhachHang (maKH, tenKH, loaiKH, diachi, sdt) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('sssss', $ma, $ten, $loai, $diachi, $sdt);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php"); exit;
        } catch (Throwable $e) {
            error_log('[KhachHang-Create] ' . $e->getMessage());
            $errors[] = 'Lỗi khi lưu. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:800px; margin:0 auto;">
    <h4 class="fw-bold mb-4">Thêm Khách Hàng</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3"><label>Mã KH <span class="text-danger">*</span></label><input type="text" name="maKH" class="form-control" value="<?= htmlspecialchars($_POST['maKH'] ?? '') ?>" required></div>
            <div class="col-md-6 mb-3"><label>Tên khách hàng <span class="text-danger">*</span></label><input type="text" name="tenKH" class="form-control" value="<?= htmlspecialchars($_POST['tenKH'] ?? '') ?>" required></div>
            <div class="col-md-6 mb-3"><label>Loại khách hàng</label><input type="text" name="loaiKH" class="form-control" value="<?= htmlspecialchars($_POST['loaiKH'] ?? '') ?>"></div>
            <div class="col-md-6 mb-3"><label>Số điện thoại</label><input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($_POST['sdt'] ?? '') ?>"></div>
            <div class="col-12 mb-3"><label>Địa chỉ</label><input type="text" name="diachi" class="form-control" value="<?= htmlspecialchars($_POST['diachi'] ?? '') ?>"></div>
        </div>
        <button type="submit" class="btn btn-success">Lưu</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
</div></body></html>