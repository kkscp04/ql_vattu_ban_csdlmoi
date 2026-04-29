<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$chucVuList = [];
$cvRs = $conn->query("SELECT maCV, tenCV FROM ChucVu ORDER BY tenCV");
if ($cvRs) {
    while ($r = $cvRs->fetch_assoc()) $chucVuList[] = $r;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = $conn->real_escape_string(trim($_POST['maNV'] ?? ''));
    $ten = $conn->real_escape_string(trim($_POST['hoten'] ?? ''));
    $sdt = $conn->real_escape_string(trim($_POST['sdt'] ?? ''));
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $trangThai = $conn->real_escape_string(trim($_POST['trangthai'] ?? 'Đang làm việc'));
    $maCV = $conn->real_escape_string(trim($_POST['maCV'] ?? ''));

    if ($ma === '' || $ten === '' || $maCV === '') {
        $error = "Vui lòng nhập mã nhân viên, họ tên và mã chức vụ.";
    } elseif (!$chucVuList) {
        $error = "Chưa có chức vụ nào trong hệ thống. Bạn cần tạo dữ liệu bảng Chức vụ trước khi thêm nhân viên.";
    } else {
        $checkCV = $conn->query("SELECT maCV FROM ChucVu WHERE maCV='$maCV' LIMIT 1");
        if (!$checkCV || $checkCV->num_rows === 0) {
            $error = "Mã chức vụ `$maCV` không tồn tại. Hãy nhập mã chức vụ có trong bảng Chức vụ.";
        } else {
            $sql = "INSERT INTO NhanVien(maNV, hoten, sdt, email, trangthai, maCV)
                    VALUES('$ma', '$ten', '$sdt', '$email', '$trangThai', '$maCV')";
            if ($conn->query($sql)) {
                header("Location: index.php");
                exit;
            }
            $error = "Lỗi: " . $conn->error;
        }
    }
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4" style="max-width:900px; margin:0 auto;">
    <h4 class="fw-bold mb-4"><i class="fas fa-user-plus"></i> Thêm Nhân Viên</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Mã NV <span class="text-danger">*</span></label>
                <input type="text" name="maNV" class="form-control" value="<?= htmlspecialchars($_POST['maNV'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Họ tên <span class="text-danger">*</span></label>
                <input type="text" name="hoten" class="form-control" value="<?= htmlspecialchars($_POST['hoten'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Số điện thoại</label>
                <input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($_POST['sdt'] ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>Mã chức vụ <span class="text-danger">*</span></label>
                <input type="text" name="maCV" class="form-control" list="danhSachChucVu" value="<?= htmlspecialchars($_POST['maCV'] ?? '') ?>" required>
                <datalist id="danhSachChucVu">
                    <?php foreach ($chucVuList as $cv) { ?>
                        <option value="<?= htmlspecialchars($cv['maCV']) ?>"><?= htmlspecialchars($cv['tenCV']) ?></option>
                    <?php } ?>
                </datalist>
                <?php if (!$chucVuList) { ?>
                    <small class="text-muted">Hiện chưa có dữ liệu chức vụ trong bảng `ChucVu` để gợi ý.</small>
                <?php } ?>
            </div>
            <div class="col-md-6 mb-3">
                <label>Trạng thái</label>
                <select name="trangthai" class="form-select">
                    <option <?= ($_POST['trangthai'] ?? 'Đang làm việc') === 'Đang làm việc' ? 'selected' : '' ?>>Đang làm việc</option>
                    <option <?= ($_POST['trangthai'] ?? '') === 'Tạm nghỉ' ? 'selected' : '' ?>>Tạm nghỉ</option>
                    <option <?= ($_POST['trangthai'] ?? '') === 'Đã nghỉ' ? 'selected' : '' ?>>Đã nghỉ</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu</button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
    </form>
</div>

</div>
</body>
</html>
