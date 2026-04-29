<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = $conn->real_escape_string(trim($_POST['maNCC'] ?? ''));
    $ten = $conn->real_escape_string(trim($_POST['tenNCC'] ?? ''));
    $mst = $conn->real_escape_string(trim($_POST['masothue'] ?? ''));
    $nlh = $conn->real_escape_string(trim($_POST['nguoilienhe'] ?? ''));
    $sdt = $conn->real_escape_string(trim($_POST['sdt'] ?? ''));
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $diachi = $conn->real_escape_string(trim($_POST['diachi'] ?? ''));
    $stk = $conn->real_escape_string(trim($_POST['stk'] ?? ''));
    $trangthai = $conn->real_escape_string(trim($_POST['trangthai'] ?? 'Hoạt động'));

    if ($ma === '' || $ten === '') {
        $error = "Vui lòng nhập mã và tên nhà cung cấp.";
    } else {
        $sql = "INSERT INTO NhaCungCap(maNCC, tenNCC, masothue, nguoilienhe, sdt, email, diachi, stk, trangthai)
                VALUES('$ma', '$ten', '$mst', '$nlh', '$sdt', '$email', '$diachi', '$stk', '$trangthai')";
        if ($conn->query($sql)) {
            header("Location: index.php");
            exit;
        }
        $error = "Lỗi: " . $conn->error;
    }
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4" style="max-width:900px; margin:0 auto;">
    <h4 class="fw-bold mb-4"><i class="fas fa-truck-loading"></i> Thêm Nhà Cung Cấp</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Mã NCC <span class="text-danger">*</span></label>
                <input type="text" name="maNCC" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Tên nhà cung cấp <span class="text-danger">*</span></label>
                <input type="text" name="tenNCC" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3"><label>Mã số thuế</label><input type="text" name="masothue" class="form-control"></div>
            <div class="col-md-6 mb-3"><label>Người liên hệ</label><input type="text" name="nguoilienhe" class="form-control"></div>
            <div class="col-md-6 mb-3"><label>Số điện thoại</label><input type="text" name="sdt" class="form-control"></div>
            <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
            <div class="col-md-6 mb-3"><label>Số tài khoản</label><input type="text" name="stk" class="form-control"></div>
            <div class="col-md-6 mb-3">
                <label>Trạng thái</label>
                <select name="trangthai" class="form-select">
                    <option>Hoạt động</option>
                    <option>Tạm ngưng</option>
                    <option>Ngừng hợp tác</option>
                </select>
            </div>
            <div class="col-12 mb-3"><label>Địa chỉ</label><input type="text" name="diachi" class="form-control"></div>
        </div>

        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu</button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
    </form>
</div>

</div>
</body>
</html>