<?php
require_once __DIR__ . '/../../bootstrap.php';

$idRaw = $_GET['id'] ?? '';
$row = db_fetch_one($conn, "SELECT * FROM VatTu WHERE maVatTu = ? LIMIT 1", 's', [$idRaw]);
if (!$row) { echo "Vật tư không tồn tại"; exit; }
$id = $row['maVatTu'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['tenVatTu'] ?? '');
    $maLoai = trim($_POST['maLoai'] ?? '');
    $maDVT = trim($_POST['maDVT'] ?? '');
    $giaNhap = (float) ($_POST['gianhap'] ?? 0);
    $giaBan = (float) ($_POST['giaban'] ?? 0);
    $mota = trim($_POST['mota'] ?? '');
    $errors = [];

    if ($ten === '') $errors[] = '[R01] Tên vật tư không được để trống.';
    if ($maLoai === '') $errors[] = '[R01] Loại vật tư không được để trống.';
    if ($maDVT === '') $errors[] = '[R01] Đơn vị tính không được để trống.';
    if ($maLoai !== '' && !db_exists($conn, "SELECT maLoai FROM LoaiVatTu WHERE maLoai = ? LIMIT 1", 's', [$maLoai])) {
        $errors[] = "[R06] Loại vật tư '$maLoai' không tồn tại.";
    }
    if ($maDVT !== '' && !db_exists($conn, "SELECT maDVT FROM DonViTinh WHERE maDVT = ? LIMIT 1", 's', [$maDVT])) {
        $errors[] = "[R06] Đơn vị tính '$maDVT' không tồn tại.";
    }
    if ($giaNhap < 0) $errors[] = '[R09] Giá nhập phải >= 0.';
    if ($giaBan < 0) $errors[] = '[R09] Giá bán phải >= 0.';

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare(
                "UPDATE VatTu
                 SET tenVatTu = ?, maLoai = ?, maDVT = ?, gianhap = ?, giaban = ?, mota = ?
                 WHERE maVatTu = ?"
            );
            $stmt->bind_param('sssddss', $ten, $maLoai, $maDVT, $giaNhap, $giaBan, $mota, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: index.php");
            exit;
        } catch (Throwable $e) {
            error_log('[VatTu-Edit] ' . $e->getMessage());
            $errors[] = 'Lỗi khi cập nhật. Vui lòng thử lại.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:900px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Sửa Vật Tư</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="row">
            <div class="col-md-3 mb-3"><label>Mã VT</label><input class="form-control" value="<?= htmlspecialchars($id) ?>" readonly></div>
            <div class="col-md-9 mb-3"><label>Tên VT <span class="text-danger">*</span></label><input type="text" name="tenVatTu" class="form-control" value="<?= htmlspecialchars($_POST['tenVatTu'] ?? $row['tenVatTu']) ?>" required></div>
            <div class="col-md-4 mb-3">
                <label>Loại VT <span class="text-danger">*</span></label>
                <select name="maLoai" class="form-select" required>
                    <?php $rs = $conn->query("SELECT maLoai, tenLoai FROM LoaiVatTu ORDER BY tenLoai"); $sel = $_POST['maLoai'] ?? $row['maLoai']; while ($x = $rs->fetch_assoc()) { $s = ($x['maLoai'] === $sel) ? 'selected' : ''; echo "<option value='" . htmlspecialchars($x['maLoai']) . "' $s>" . htmlspecialchars($x['tenLoai']) . "</option>"; } ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label>ĐVT <span class="text-danger">*</span></label>
                <select name="maDVT" class="form-select" required>
                    <?php $rs = $conn->query("SELECT maDVT, tenDVT FROM DonViTinh ORDER BY tenDVT"); $sel = $_POST['maDVT'] ?? $row['maDVT']; while ($x = $rs->fetch_assoc()) { $s = ($x['maDVT'] === $sel) ? 'selected' : ''; echo "<option value='" . htmlspecialchars($x['maDVT']) . "' $s>" . htmlspecialchars($x['tenDVT']) . "</option>"; } ?>
                </select>
            </div>
            <div class="col-md-2 mb-3"><label>Giá nhập</label><input type="number" name="gianhap" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($_POST['gianhap'] ?? (string) ($row['gianhap'] ?? 0)) ?>"></div>
            <div class="col-md-2 mb-3"><label>Giá bán</label><input type="number" name="giaban" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($_POST['giaban'] ?? (string) ($row['giaban'] ?? 0)) ?>"></div>
            <div class="col-12 mb-3"><label>Mô tả</label><textarea name="mota" class="form-control" rows="2"><?= htmlspecialchars($_POST['mota'] ?? ($row['mota'] ?? '')) ?></textarea></div>
        </div>
        <button class="btn btn-warning">Cập nhật</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
</div></body></html>
