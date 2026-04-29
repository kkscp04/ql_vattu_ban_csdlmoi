<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = $conn->real_escape_string(trim($_POST['maKH'] ?? ''));
    $ten = $conn->real_escape_string(trim($_POST['tenKH'] ?? ''));
    $loai = $conn->real_escape_string(trim($_POST['loaiKH'] ?? ''));
    $diachi = $conn->real_escape_string(trim($_POST['diachi'] ?? ''));
    $sdt = $conn->real_escape_string(trim($_POST['sdt'] ?? ''));

    if ($ma === '' || $ten === '') {
        $error = "Vui lòng nhập mã và tên khách hàng.";
    } else {
        $sql = "INSERT INTO KhachHang(maKH, tenKH, loaiKH, diachi, sdt)
                VALUES('$ma', '$ten', '$loai', '$diachi', '$sdt')";
        if ($conn->query($sql)) {
            header("Location: index.php");
            exit;
        }
        $error = "Lỗi: " . $conn->error;
    }
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4" style="max-width:800px; margin:0 auto;">
    <h4 class="fw-bold mb-4"><i class="fas fa-user-plus"></i> Thêm Khách Hàng</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Mã KH <span class="text-danger">*</span></label>
                <input type="text" name="maKH" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Tên khách hàng <span class="text-danger">*</span></label>
                <input type="text" name="tenKH" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Loại khách hàng</label>
                <input type="text" name="loaiKH" class="form-control" placeholder="VIP / Đại lý / Lẻ ...">
            </div>
            <div class="col-md-6 mb-3">
                <label>Số điện thoại</label>
                <input type="text" name="sdt" class="form-control">
            </div>
            <div class="col-12 mb-3">
                <label>Địa chỉ</label>
                <input type="text" name="diachi" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu</button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
    </form>
</div>

</div>
</body>
</html>