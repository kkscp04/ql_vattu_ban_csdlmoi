<?php
require_once __DIR__ . '/../../bootstrap.php';

$idRaw = $_GET['id'] ?? '';
$row = db_fetch_one($conn, "SELECT * FROM KhachHang WHERE maKH = ? LIMIT 1", 's', [$idRaw]);
if (!$row) { echo "Khách hàng không tồn tại"; exit; }
$id = $row['maKH'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten    = trim($_POST['tenKH']  ?? '');
    $loai   = trim($_POST['loaiKH'] ?? '');
    $diachi = trim($_POST['diachi'] ?? '');
    $sdt    = trim($_POST['sdt']    ?? '');
    $errors = [];
    if ($ten === '') $errors[] = '[R01] Tên khách hàng không được để trống.';
    if (mb_strlen($ten) > 255)    $errors[] = '[R03] Tên KH tối đa 255 ký tự.';
    if (mb_strlen($diachi) > 255) $errors[] = '[R03] Địa chỉ tối đa 255 ký tự.';
    if ($sdt !== '' && !preg_match('/^\d{1,10}$/', $sdt))
        $errors[] = '[R08] Số điện thoại chỉ gồm chữ số, tối đa 10 ký tự.';

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare(
                "UPDATE KhachHang SET tenKH = ?, loaiKH = ?, diachi = ?, sdt = ? WHERE maKH = ?"
            );
            $stmt->bind_param('sssss', $ten, $loai, $diachi, $sdt, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php"); exit;
        } catch (Throwable $e) {
            error_log('[KhachHang-Edit] ' . $e->getMessage());
            $errors[] = 'Lỗi khi cập nhật. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:800px; margin:0 auto;">
    <h4 class="fw-bold mb-4">Sửa Khách Hàng</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3"><label>Mã KH</label><input type="text" class="form-control" value="<?= htmlspecialchars($id) ?>" readonly></div>
            <div class="col-md-6 mb-3"><label>Tên KH <span class="text-danger">*</span></label><input type="text" name="tenKH" class="form-control" value="<?= htmlspecialchars($_POST['tenKH'] ?? $row['tenKH']) ?>" required></div>
            <div class="col-md-6 mb-3"><label>Loại KH</label><input type="text" name="loaiKH" class="form-control" value="<?= htmlspecialchars($_POST['loaiKH'] ?? $row['loaiKH']) ?>"></div>
            <div class="col-md-6 mb-3"><label>SĐT</label><input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($_POST['sdt'] ?? ($row['sdt'] ?? '')) ?>"></div>
            <div class="col-12 mb-3"><label>Địa chỉ</label><input type="text" name="diachi" class="form-control" value="<?= htmlspecialchars($_POST['diachi'] ?? ($row['diachi'] ?? '')) ?>"></div>
        </div>
        <button type="submit" class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
</div></body></html>