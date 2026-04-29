<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = $conn->real_escape_string(trim($_POST['maVatTu'] ?? ''));
    $ten = $conn->real_escape_string(trim($_POST['tenVatTu'] ?? ''));
    $mota = $conn->real_escape_string(trim($_POST['mota'] ?? ''));
    $trongluong = (float) ($_POST['trongluong'] ?? 0);
    $gianhap = (float) ($_POST['gianhap'] ?? 0);
    $giaban = (float) ($_POST['giaban'] ?? 0);
    $soluong = (int) ($_POST['soluong'] ?? 0);
    $maLoai = $conn->real_escape_string(trim($_POST['maLoai'] ?? ''));
    $maDVT = $conn->real_escape_string(trim($_POST['maDVT'] ?? ''));

    if ($ma === '' || $ten === '' || $maLoai === '' || $maDVT === '') {
        $error = "Vui lòng nhập đủ mã, tên, loại và đơn vị tính.";
    } else {
        $sql = "INSERT INTO VatTu(maVatTu, tenVatTu, mota, trongluong, gianhap, soluong, giaban, maLoai, maDVT, maNV_QuanLy)
                VALUES('$ma', '$ten', '$mota', $trongluong, $gianhap, $soluong, $giaban, '$maLoai', '$maDVT', NULL)";
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
    <h4 class="fw-bold mb-3">Thêm vật tư</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Mã vật tư</label>
                <input type="text" name="maVatTu" class="form-control" required>
            </div>
            <div class="col-md-8 mb-3">
                <label>Tên vật tư</label>
                <input type="text" name="tenVatTu" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Loại vật tư</label>
                <select name="maLoai" class="form-select" required>
                    <option value="">-- Chọn loại --</option>
                    <?php
                    $loai = $conn->query("SELECT maLoai, tenLoai FROM LoaiVatTu ORDER BY tenLoai");
                    while ($r = $loai->fetch_assoc()) {
                        echo "<option value='{$r['maLoai']}'>" . htmlspecialchars($r['tenLoai']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Đơn vị tính</label>
                <select name="maDVT" class="form-select" required>
                    <option value="">-- Chọn đơn vị --</option>
                    <?php
                    $dvt = $conn->query("SELECT maDVT, tenDVT FROM DonViTinh ORDER BY tenDVT");
                    while ($r = $dvt->fetch_assoc()) {
                        echo "<option value='{$r['maDVT']}'>" . htmlspecialchars($r['tenDVT']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3 mb-3"><label>Giá nhập</label><input type="number" name="gianhap" class="form-control" min="0" step="0.01" value="0"></div>
            <div class="col-md-3 mb-3"><label>Giá bán</label><input type="number" name="giaban" class="form-control" min="0" step="0.01" value="0"></div>
            <div class="col-md-3 mb-3"><label>Số lượng</label><input type="number" name="soluong" class="form-control" min="0" value="0"></div>
            <div class="col-md-3 mb-3"><label>Trọng lượng</label><input type="number" name="trongluong" class="form-control" min="0" step="0.01" value="0"></div>

            <div class="col-12 mb-3">
                <label>Mô tả</label>
                <input type="text" name="mota" class="form-control">
            </div>
        </div>

        <button class="btn btn-success">Lưu</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

</div>
</body>
</html>
