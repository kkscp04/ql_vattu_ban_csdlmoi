<?php
require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma  = trim($_POST['maDVT']  ?? '');
    $ten = trim($_POST['tenDVT'] ?? '');
    $errors = [];
    if ($ma === '')  $errors[] = '[R01] Mã DVT không được để trống.';
    if ($ten === '') $errors[] = '[R01] Tên đơn vị tính không được để trống.';
    if ($ma !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma))
        $errors[] = '[R02] Mã DVT chỉ gồm chữ, số, dấu . _ - và tối đa 50 ký tự.';
    if (mb_strlen($ten) > 100) $errors[] = '[R03] Tên đơn vị tính tối đa 100 ký tự.';
    if ($ma !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)) {
        if (db_exists($conn, "SELECT maDVT FROM DonViTinh WHERE maDVT = ? LIMIT 1", 's', [$ma]))
            $errors[] = "[R04] Mã DVT '$ma' đã tồn tại.";
    }
    if ($ten !== '' && db_exists($conn, "SELECT maDVT FROM DonViTinh WHERE tenDVT = ? LIMIT 1", 's', [$ten]))
        $errors[] = "[R05] Tên đơn vị '$ten' đã tồn tại.";

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO DonViTinh (maDVT, tenDVT) VALUES (?, ?)");
            $stmt->bind_param('ss', $ma, $ten);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php"); exit;
        } catch (Throwable $e) {
            error_log('[DVT-Create] ' . $e->getMessage());
            $errors[] = 'Lỗi khi lưu. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:700px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Thêm đơn vị tính</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Mã DVT <span class="text-danger">*</span></label>
            <input type="text" name="maDVT" class="form-control" value="<?= htmlspecialchars($_POST['maDVT'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label>Tên đơn vị <span class="text-danger">*</span></label>
            <input type="text" name="tenDVT" class="form-control" value="<?= htmlspecialchars($_POST['tenDVT'] ?? '') ?>" required>
        </div>
        <button class="btn btn-success">Lưu</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
</div></body></html>