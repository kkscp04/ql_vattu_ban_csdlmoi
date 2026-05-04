<?php
require_once __DIR__ . '/../../bootstrap.php';

$idRaw = $_GET['id'] ?? '';
$row = db_fetch_one($conn, "SELECT * FROM DonViTinh WHERE maDVT = ? LIMIT 1", 's', [$idRaw]);
if (!$row) { echo "Không tìm thấy đơn vị tính"; exit; }
$id = $row['maDVT'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['tenDVT'] ?? '');
    $errors = [];
    if ($ten === '') $errors[] = '[R01] Tên đơn vị tính không được để trống.';
    if (mb_strlen($ten) > 100) $errors[] = '[R03] Tên đơn vị tính tối đa 100 ký tự.';
    if ($ten !== '') {
        $stmt = $conn->prepare("SELECT maDVT FROM DonViTinh WHERE tenDVT = ? AND maDVT <> ? LIMIT 1");
        $stmt->bind_param('ss', $ten, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0)
            $errors[] = "[R05] Tên đơn vị '$ten' đã được dùng bởi mã khác.";
        $stmt->close();
    }
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE DonViTinh SET tenDVT = ? WHERE maDVT = ?");
            $stmt->bind_param('ss', $ten, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php"); exit;
        } catch (Throwable $e) {
            error_log('[DVT-Edit] ' . $e->getMessage());
            $errors[] = 'Lỗi khi cập nhật. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:700px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Sửa đơn vị tính</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Mã DVT</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($id) ?>" readonly>
        </div>
        <div class="mb-3">
            <label>Tên đơn vị <span class="text-danger">*</span></label>
            <input type="text" name="tenDVT" class="form-control" value="<?= htmlspecialchars($_POST['tenDVT'] ?? $row['tenDVT']) ?>" required>
        </div>
        <button class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
</div></body></html>
