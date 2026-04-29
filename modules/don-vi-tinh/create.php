<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = $conn->real_escape_string(trim($_POST['maDVT'] ?? ''));
    $ten = $conn->real_escape_string(trim($_POST['tenDVT'] ?? ''));

    if ($ma === '' || $ten === '') {
        $error = "Vui lòng nhập đầy đủ mã và tên đơn vị.";
    } else {
        if ($conn->query("INSERT INTO DonViTinh(maDVT, tenDVT) VALUES('$ma', '$ten')")) {
            header("Location: index.php");
            exit;
        }
        $error = "Lỗi: " . $conn->error;
    }
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4" style="max-width:700px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Thêm đơn vị tính</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Mã DVT</label>
            <input type="text" name="maDVT" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tên đơn vị</label>
            <input type="text" name="tenDVT" class="form-control" required>
        </div>

        <button class="btn btn-success">Lưu</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

</div>
</body>
</html>