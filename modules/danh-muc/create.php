<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = $conn->real_escape_string(trim($_POST['maDM'] ?? ''));
    $ten = $conn->real_escape_string(trim($_POST['tenDM'] ?? ''));
    $mota = $conn->real_escape_string(trim($_POST['mota'] ?? ''));

    if ($ma === '' || $ten === '') {
        $error = "Vui lòng nhập mã và tên danh mục.";
    } else {
        if ($conn->query("INSERT INTO DanhMuc(maDM, tenDM, mota) VALUES('$ma', '$ten', '$mota')")) {
            header("Location: index.php");
            exit;
        }
        $error = "Lỗi: " . $conn->error;
    }
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4" style="max-width:700px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Thêm danh mục</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Mã DM</label>
            <input type="text" name="maDM" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tên danh mục</label>
            <input type="text" name="tenDM" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Mô tả</label>
            <input type="text" name="mota" class="form-control">
        </div>

        <button class="btn btn-success">Lưu</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

</div>
</body>
</html>