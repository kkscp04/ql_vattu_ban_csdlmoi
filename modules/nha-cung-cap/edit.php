<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$rs = $conn->query("SELECT * FROM NhaCungCap WHERE maNCC='$id'");
$row = $rs ? $rs->fetch_assoc() : null;
if (!$row) { echo "Nhà cung cấp không tồn tại"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = $conn->real_escape_string(trim($_POST['tenNCC'] ?? ''));
    $mst = $conn->real_escape_string(trim($_POST['masothue'] ?? ''));
    $nlh = $conn->real_escape_string(trim($_POST['nguoilienhe'] ?? ''));
    $sdt = $conn->real_escape_string(trim($_POST['sdt'] ?? ''));
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $diachi = $conn->real_escape_string(trim($_POST['diachi'] ?? ''));
    $stk = $conn->real_escape_string(trim($_POST['stk'] ?? ''));
    $trangthai = $conn->real_escape_string(trim($_POST['trangthai'] ?? 'Hoạt động'));

    if ($ten === '') {
        $error = "Tên nhà cung cấp không được để trống.";
    } else {
        $sql = "UPDATE NhaCungCap
                SET tenNCC='$ten',
                    masothue='$mst',
                    nguoilienhe='$nlh',
                    sdt='$sdt',
                    email='$email',
                    diachi='$diachi',
                    stk='$stk',
                    trangthai='$trangthai'
                WHERE maNCC='$id'";
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
    <h4 class="fw-bold mb-4"><i class="fas fa-edit"></i> Sửa Nhà Cung Cấp</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Mã NCC</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($row['maNCC']) ?>" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Tên nhà cung cấp <span class="text-danger">*</span></label>
                <input type="text" name="tenNCC" class="form-control" required value="<?= htmlspecialchars($row['tenNCC']) ?>">
            </div>
            <div class="col-md-6 mb-3"><label>Mã số thuế</label><input type="text" name="masothue" class="form-control" value="<?= htmlspecialchars($row['masothue']) ?>"></div>
            <div class="col-md-6 mb-3"><label>Người liên hệ</label><input type="text" name="nguoilienhe" class="form-control" value="<?= htmlspecialchars($row['nguoilienhe']) ?>"></div>
            <div class="col-md-6 mb-3"><label>Số điện thoại</label><input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($row['sdt']) ?>"></div>
            <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>"></div>
            <div class="col-md-6 mb-3"><label>Số tài khoản</label><input type="text" name="stk" class="form-control" value="<?= htmlspecialchars($row['stk']) ?>"></div>
            <div class="col-md-6 mb-3">
                <label>Trạng thái</label>
                <select name="trangthai" class="form-select">
                    <?php foreach (['Hoạt động','Tạm ngưng','Ngừng hợp tác'] as $x) { $s = ($row['trangthai'] === $x) ? 'selected' : ''; echo "<option $s>$x</option>"; } ?>
                </select>
            </div>
            <div class="col-12 mb-3"><label>Địa chỉ</label><input type="text" name="diachi" class="form-control" value="<?= htmlspecialchars($row['diachi']) ?>"></div>
        </div>

        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Cập nhật</button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
    </form>
</div>

</div>
</body>
</html>