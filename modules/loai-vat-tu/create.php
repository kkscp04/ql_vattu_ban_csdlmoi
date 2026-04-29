<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = $conn->real_escape_string(trim($_POST['maLoai'] ?? ''));
    $ten = $conn->real_escape_string(trim($_POST['tenLoai'] ?? ''));
    $maDM = $conn->real_escape_string(trim($_POST['maDM'] ?? ''));

    if ($ma === '' || $ten === '' || $maDM === '') {
        $error = "Vui lòng nhập đủ Mã loại, Tên loại, Danh mục.";
    } else {
        if ($conn->query("INSERT INTO LoaiVatTu(maLoai, tenLoai, maDM) VALUES('$ma', '$ten', '$maDM')")) {
            header("Location: index.php");
            exit;
        }
        $error = "Lỗi: " . $conn->error;
    }
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4" style="max-width:700px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Thêm loại vật tư</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Mã loại</label>
            <input type="text" name="maLoai" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tên loại</label>
            <input type="text" name="tenLoai" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Danh mục</label>
            <select name="maDM" class="form-select" required>
                <option value="">-- Chọn danh mục --</option>
                <?php
                $dm = $conn->query("SELECT maDM, tenDM FROM DanhMuc ORDER BY tenDM");
                while ($r = $dm->fetch_assoc()) {
                    echo "<option value='{$r['maDM']}'>" . htmlspecialchars($r['tenDM']) . "</option>";
                }
                ?>
            </select>
        </div>

        <button class="btn btn-success">Lưu</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

</div>
</body>
</html>