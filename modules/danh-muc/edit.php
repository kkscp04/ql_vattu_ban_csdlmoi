<?php
require_once __DIR__ . '/../../bootstrap.php';

$idRaw = $_GET['id'] ?? '';
$row = db_fetch_one($conn, "SELECT * FROM DanhMuc WHERE maDM = ? LIMIT 1", 's', [$idRaw]);
if (!$row) { echo "Không tìm thấy danh mục"; exit; }
$id = $row['maDM'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten  = trim($_POST['tenDM'] ?? '');
    $mota = trim($_POST['mota']  ?? '');
    $errors = [];
    if ($ten === '') $errors[] = '[R01] Tên danh mục không được để trống.';
    if (mb_strlen($ten) > 255) $errors[] = '[R03] Tên danh mục tối đa 255 ký tự.';
    if ($ten !== '') {
        $stmt = $conn->prepare("SELECT maDM FROM DanhMuc WHERE tenDM = ? AND maDM <> ? LIMIT 1");
        $stmt->bind_param('ss', $ten, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0)
            $errors[] = "[R05] Tên danh mục '$ten' đã được dùng bởi mã khác.";
        $stmt->close();
    }
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE DanhMuc SET tenDM = ?, mota = ? WHERE maDM = ?");
            $stmt->bind_param('sss', $ten, $mota, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php"); exit;
        } catch (Throwable $e) {
            error_log('[DanhMuc-Edit] ' . $e->getMessage());
            $errors[] = 'Lỗi khi cập nhật. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:700px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Sửa danh mục</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Mã DM</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($id) ?>" readonly>
        </div>
        <div class="mb-3">
            <label>Tên danh mục <span class="text-danger">*</span></label>
            <input type="text" name="tenDM" class="form-control" value="<?= htmlspecialchars($_POST['tenDM'] ?? $row['tenDM']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Mô tả</label>
            <input type="text" name="mota" class="form-control" value="<?= htmlspecialchars($_POST['mota'] ?? $row['mota']) ?>">
        </div>
        <button class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
</div></body></html>