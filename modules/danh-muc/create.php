<?php
require_once __DIR__ . '/../../bootstrap.php';

function validate_dm_create(mysqli $conn, string $ma, string $ten): array {
    $errors = [];
    if ($ma === '')  $errors[] = '[R01] Mã DM không được để trống.';
    if ($ten === '') $errors[] = '[R01] Tên danh mục không được để trống.';
    if ($ma !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma))
        $errors[] = '[R02] Mã DM chỉ gồm chữ, số, dấu . _ - và tối đa 50 ký tự.';
    if (mb_strlen($ten) > 255) $errors[] = '[R03] Tên danh mục tối đa 255 ký tự.';
    if ($ma !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)) {
        if (db_exists($conn, "SELECT maDM FROM DanhMuc WHERE maDM = ? LIMIT 1", 's', [$ma]))
            $errors[] = "[R04] Mã DM '$ma' đã tồn tại.";
    }
    if ($ten !== '' && db_exists($conn, "SELECT maDM FROM DanhMuc WHERE tenDM = ? LIMIT 1", 's', [$ten]))
        $errors[] = "[R05] Tên danh mục '$ten' đã tồn tại.";
    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma   = trim($_POST['maDM']  ?? '');
    $ten  = trim($_POST['tenDM'] ?? '');
    $mota = trim($_POST['mota']  ?? '');

    $errors = validate_dm_create($conn, $ma, $ten);
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO DanhMuc (maDM, tenDM, mota) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $ma, $ten, $mota);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php"); exit;
        } catch (Throwable $e) {
            error_log('[DanhMuc-Create] ' . $e->getMessage());
            $errors[] = 'Lỗi khi lưu. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:700px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Thêm danh mục</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Mã DM <span class="text-danger">*</span></label>
            <input type="text" name="maDM" class="form-control" value="<?= htmlspecialchars($_POST['maDM'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label>Tên danh mục <span class="text-danger">*</span></label>
            <input type="text" name="tenDM" class="form-control" value="<?= htmlspecialchars($_POST['tenDM'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label>Mô tả</label>
            <input type="text" name="mota" class="form-control" value="<?= htmlspecialchars($_POST['mota'] ?? '') ?>">
        </div>
        <button class="btn btn-success">Lưu</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
</div></body></html>