<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$rs = $conn->query("SELECT * FROM LoaiVatTu WHERE maLoai='$id'");
$row = $rs ? $rs->fetch_assoc() : null;
if (!$row) { echo "Không tìm thấy loại vật tư"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = $conn->real_escape_string(trim($_POST['tenLoai'] ?? ''));
    $maDM = $conn->real_escape_string(trim($_POST['maDM'] ?? ''));

    if ($ten === '' || $maDM === '') {
        $error = "Vui lòng nhập đủ Tên loại và Danh mục.";
    } else {
        if ($conn->query("UPDATE LoaiVatTu SET tenLoai='$ten', maDM='$maDM' WHERE maLoai='$id'")) {
            header("Location: index.php");
            exit;
        }
        $error = "Lỗi: " . $conn->error;
    }
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4" style="max-width:700px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Sửa loại vật tư</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Mã loại</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($row['maLoai']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label>Tên loại</label>
            <input type="text" name="tenLoai" class="form-control" value="<?= htmlspecialchars($row['tenLoai']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Danh mục</label>
            <select name="maDM" class="form-select" required>
                <?php
                $dm = $conn->query("SELECT maDM, tenDM FROM DanhMuc ORDER BY tenDM");
                while ($r = $dm->fetch_assoc()) {
                    $sel = ($r['maDM'] === $row['maDM']) ? 'selected' : '';
                    echo "<option value='{$r['maDM']}' $sel>" . htmlspecialchars($r['tenDM']) . "</option>";
                }
                ?>
            </select>
        </div>

        <button class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

</div>
</body>
</html>