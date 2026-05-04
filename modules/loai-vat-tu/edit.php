<?php
require_once __DIR__ . '/../../bootstrap.php';

$idRaw = $_GET['id'] ?? '';
$row = db_fetch_one($conn, "SELECT * FROM LoaiVatTu WHERE maLoai = ? LIMIT 1", 's', [$idRaw]);
if (!$row) { echo "Loại vật tư không tồn tại"; exit; }
$id = $row['maLoai'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten  = trim($_POST['tenLoai'] ?? '');
    $maDM = trim($_POST['maDM']   ?? '');
    $mota = trim($_POST['mota']   ?? '');
    $errors = [];

    if ($ten === '')  $errors[] = '[R01] Tên loại không được để trống.';
    if ($maDM === '') $errors[] = '[R01] Danh mục không được để trống.';
    if ($maDM !== '' && !db_exists($conn, "SELECT maDM FROM DanhMuc WHERE maDM = ? LIMIT 1", 's', [$maDM]))
        $errors[] = "[R06] Danh mục '$maDM' không tồn tại.";

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE LoaiVatTu SET tenLoai=?, maDM=?, mota=? WHERE maLoai=?");
            $stmt->bind_param('ssss', $ten, $maDM, $mota, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php"); exit;
        } catch (Throwable $e) {
            error_log('[LoaiVatTu-Edit] ' . $e->getMessage());
            $errors[] = 'Lỗi khi cập nhật. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:700px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Sửa Loại Vật Tư</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="mb-3"><label>Mã Loại</label><input class="form-control" value="<?= htmlspecialchars($id) ?>" readonly></div>
        <div class="mb-3"><label>Tên Loại <span class="text-danger">*</span></label><input type="text" name="tenLoai" class="form-control" value="<?= htmlspecialchars($_POST['tenLoai'] ?? $row['tenLoai']) ?>" required></div>
        <div class="mb-3">
            <label>Danh mục <span class="text-danger">*</span></label>
            <select name="maDM" class="form-select" required>
                <?php $rs = $conn->query("SELECT maDM, tenDM FROM DanhMuc ORDER BY tenDM"); $sel = $_POST['maDM'] ?? $row['maDM']; while ($x = $rs->fetch_assoc()) { $s = ($x['maDM'] === $sel) ? 'selected' : ''; echo "<option value='" . htmlspecialchars($x['maDM']) . "' $s>" . htmlspecialchars($x['tenDM']) . "</option>"; } ?>
            </select>
        </div>
        <div class="mb-3"><label>Mô tả</label><input type="text" name="mota" class="form-control" value="<?= htmlspecialchars($_POST['mota'] ?? ($row['mota'] ?? '')) ?>"></div>
        <button class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
</div></body></html>