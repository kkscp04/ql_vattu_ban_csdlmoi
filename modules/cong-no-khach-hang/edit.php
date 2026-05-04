<?php
require_once __DIR__ . '/../../bootstrap.php';

$id = trim($_GET['id'] ?? '');
if ($id === '') {
    flash_set('danger', 'Khong tim thay ma cong no.');
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM congnokh WHERE maCNKH = ?");
$stmt->bind_param('s', $id);
$stmt->execute();
$congno = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$congno) {
    flash_set('danger', 'Khong tim thay cong no.');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maKH = trim($_POST['maKH'] ?? '');
    $maNV = trim($_POST['maNV'] ?? '');
    $tongno = (float)($_POST['tongno'] ?? 0);
    $tongtiendatra = (float)($_POST['tongtiendatra'] ?? 0);
    $ghichu = trim($_POST['ghichu'] ?? '');

    $errors = [];
    if ($maKH === '') $errors[] = 'Vui long chon Khach hang.';
    if ($maNV === '') $errors[] = 'Vui long chon Nhan vien lap.';
    if ($tongno < 0) $errors[] = 'Tong no khong duoc am.';
    if ($tongtiendatra < 0) $errors[] = 'So tien da tra khong duoc am.';

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE congnokh SET maKH = ?, maNV = ?, tongno = ?, tongtiendatra = ?, ghichu = ? WHERE maCNKH = ?");
            $stmt->bind_param('ssddss', $maKH, $maNV, $tongno, $tongtiendatra, $ghichu, $id);
            $stmt->execute();
            $stmt->close();
            
            flash_set('success', "Cap nhat cong no khach hang thanh cong.");
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            error_log('[CongNoKH-Edit] ' . $e->getMessage());
            $errors[] = 'Loi he thong khi luu du lieu.';
        }
    }
    $errorMsg = implode('<br>', $errors);
}

require_once APP_ROOT . '/shared/layout.php';
?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Sua cong no khach hang</h4>
    <?php if (!empty($errorMsg)) echo "<div class='alert alert-danger'>$errorMsg</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label>Ma CNKH</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($id) ?>" readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label>Khach hang <span class="text-danger">*</span></label>
                <select name="maKH" class="form-select" required>
                    <option value="">-- Chon Khach Hang --</option>
                    <?php
                    $khRs = $conn->query("SELECT maKH, tenKH FROM khachhang ORDER BY tenKH");
                    $selKH = $_POST['maKH'] ?? $congno['maKH'];
                    while ($x = $khRs->fetch_assoc()) {
                        $s = ($x['maKH'] === $selKH) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maKH']) . "' $s>" . htmlspecialchars($x['maKH'] . ' - ' . $x['tenKH']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label>Nhan vien lap <span class="text-danger">*</span></label>
                <select name="maNV" class="form-select" required>
                    <option value="">-- Chon Nhan Vien --</option>
                    <?php
                    $nvRs = $conn->query("SELECT maNV, hoten FROM nhanvien ORDER BY hoten");
                    $selNV = $_POST['maNV'] ?? $congno['maNV'];
                    while ($x = $nvRs->fetch_assoc()) {
                        $s = ($x['maNV'] === $selNV) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maNV']) . "' $s>" . htmlspecialchars($x['maNV'] . ' - ' . $x['hoten']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label>Ghi chu</label>
                <input type="text" name="ghichu" class="form-control" value="<?= htmlspecialchars($_POST['ghichu'] ?? $congno['ghichu']) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>Tong no</label>
                <input type="number" name="tongno" class="form-control text-danger fw-bold" step="1" min="0" value="<?= htmlspecialchars($_POST['tongno'] ?? (float)$congno['tongno']) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>So tien da tra</label>
                <input type="number" name="tongtiendatra" class="form-control text-success fw-bold" step="1" min="0" value="<?= htmlspecialchars($_POST['tongtiendatra'] ?? (float)$congno['tongtiendatra']) ?>">
            </div>
        </div>
        
        <div>
            <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Cap nhat</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Huy</a>
        </div>
    </form>
</div>

</div></body></html>
