<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$rs = $conn->query("SELECT * FROM KhachHang WHERE maKH='$id'");
$row = $rs ? $rs->fetch_assoc() : null;
if (!$row) { echo "Khách hàng không tồn tại"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = $conn->real_escape_string(trim($_POST['tenKH'] ?? ''));
    $loai = $conn->real_escape_string(trim($_POST['loaiKH'] ?? ''));
    $diachi = $conn->real_escape_string(trim($_POST['diachi'] ?? ''));
    $sdt = $conn->real_escape_string(trim($_POST['sdt'] ?? ''));

    if ($ten === '') {
        $error = "Tên khách hàng không được để trống.";
    } else {
        $sql = "UPDATE KhachHang
                SET tenKH='$ten', loaiKH='$loai', diachi='$diachi', sdt='$sdt'
                WHERE maKH='$id'";
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
    <h4 class="fw-bold mb-4"><i class="fas fa-user-edit"></i> Sửa Khách Hàng</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Mã KH</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($row['maKH']) ?>" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Tên khách hàng <span class="text-danger">*</span></label>
                <input type="text" name="tenKH" class="form-control" required value="<?= htmlspecialchars($row['tenKH']) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>Loại khách hàng</label>
                <input type="text" name="loaiKH" class="form-control" value="<?= htmlspecialchars($row['loaiKH']) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>Số điện thoại</label>
                <input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($row['sdt']) ?>">
            </div>
            <div class="col-12 mb-3">
                <label>Địa chỉ</label>
                <input type="text" name="diachi" class="form-control" value="<?= htmlspecialchars($row['diachi']) ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Cập nhật</button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
    </form>
</div>

</div>
</body>
</html>