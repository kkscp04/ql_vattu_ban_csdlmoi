<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$rs = $conn->query("SELECT * FROM VatTu WHERE maVatTu='$id'");
$row = $rs ? $rs->fetch_assoc() : null;
if (!$row) { echo "Không tìm thấy vật tư"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = $conn->real_escape_string(trim($_POST['tenVatTu'] ?? ''));
    $mota = $conn->real_escape_string(trim($_POST['mota'] ?? ''));
    $trongluong = (float) ($_POST['trongluong'] ?? 0);
    $gianhap = (float) ($_POST['gianhap'] ?? 0);
    $giaban = (float) ($_POST['giaban'] ?? 0);
    $soluong = (int) ($_POST['soluong'] ?? 0);
    $maLoai = $conn->real_escape_string(trim($_POST['maLoai'] ?? ''));
    $maDVT = $conn->real_escape_string(trim($_POST['maDVT'] ?? ''));

    if ($ten === '' || $maLoai === '' || $maDVT === '') {
        $error = "Vui lòng nhập đủ thông tin bắt buộc.";
    } else {
        $sql = "UPDATE VatTu
                SET tenVatTu='$ten',
                    mota='$mota',
                    trongluong=$trongluong,
                    gianhap=$gianhap,
                    soluong=$soluong,
                    giaban=$giaban,
                    maLoai='$maLoai',
                    maDVT='$maDVT',
                    maNV_QuanLy=NULL
                WHERE maVatTu='$id'";
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
    <h4 class="fw-bold mb-3">Sửa vật tư</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Mã vật tư</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($row['maVatTu']) ?>" readonly>
            </div>
            <div class="col-md-8 mb-3">
                <label>Tên vật tư</label>
                <input type="text" name="tenVatTu" class="form-control" value="<?= htmlspecialchars($row['tenVatTu']) ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Loại vật tư</label>
                <select name="maLoai" class="form-select" required>
                    <?php
                    $loai = $conn->query("SELECT maLoai, tenLoai FROM LoaiVatTu ORDER BY tenLoai");
                    while ($r = $loai->fetch_assoc()) {
                        $sel = ($r['maLoai'] === $row['maLoai']) ? 'selected' : '';
                        echo "<option value='{$r['maLoai']}' $sel>" . htmlspecialchars($r['tenLoai']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Đơn vị tính</label>
                <select name="maDVT" class="form-select" required>
                    <?php
                    $dvt = $conn->query("SELECT maDVT, tenDVT FROM DonViTinh ORDER BY tenDVT");
                    while ($r = $dvt->fetch_assoc()) {
                        $sel = ($r['maDVT'] === $row['maDVT']) ? 'selected' : '';
                        echo "<option value='{$r['maDVT']}' $sel>" . htmlspecialchars($r['tenDVT']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3 mb-3"><label>Giá nhập</label><input type="number" name="gianhap" class="form-control" min="0" step="0.01" value="<?= (float) $row['gianhap'] ?>"></div>
            <div class="col-md-3 mb-3"><label>Giá bán</label><input type="number" name="giaban" class="form-control" min="0" step="0.01" value="<?= (float) $row['giaban'] ?>"></div>
            <div class="col-md-3 mb-3"><label>Số lượng</label><input type="number" name="soluong" class="form-control" min="0" value="<?= (int) $row['soluong'] ?>"></div>
            <div class="col-md-3 mb-3"><label>Trọng lượng</label><input type="number" name="trongluong" class="form-control" min="0" step="0.01" value="<?= (float) $row['trongluong'] ?>"></div>

            <div class="col-12 mb-3">
                <label>Mô tả</label>
                <input type="text" name="mota" class="form-control" value="<?= htmlspecialchars($row['mota']) ?>">
            </div>
        </div>

        <button class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

</div>
</body>
</html>
