<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$rs = $conn->query("SELECT * FROM DanhMuc WHERE maDM='$id'");
$row = $rs ? $rs->fetch_assoc() : null;
if (!$row) { echo "Không tìm thấy danh mục"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = $conn->real_escape_string(trim($_POST['tenDM'] ?? ''));
    $mota = $conn->real_escape_string(trim($_POST['mota'] ?? ''));

    if ($ten === '') {
        $error = "Tên danh mục không được để trống.";
    } else {
        if ($conn->query("UPDATE DanhMuc SET tenDM='$ten', mota='$mota' WHERE maDM='$id'")) {
            header("Location: index.php");
            exit;
        }
        $error = "Lỗi: " . $conn->error;
    }
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4" style="max-width:700px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Sửa danh mục</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Mã DM</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($row['maDM']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label>Tên danh mục</label>
            <input type="text" name="tenDM" class="form-control" value="<?= htmlspecialchars($row['tenDM']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Mô tả</label>
            <input type="text" name="mota" class="form-control" value="<?= htmlspecialchars($row['mota']) ?>">
        </div>

        <button class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

</div>
</body>
</html>