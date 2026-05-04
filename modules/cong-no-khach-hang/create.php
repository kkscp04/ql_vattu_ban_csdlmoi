<?php
require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maCNKH = trim($_POST['maCNKH'] ?? '');
    $maKH = trim($_POST['maKH'] ?? '');
    $maNV = trim($_POST['maNV'] ?? '');
    $tongno = (float)($_POST['tongno'] ?? 0);
    $tongtiendatra = (float)($_POST['tongtiendatra'] ?? 0);
    $ghichu = trim($_POST['ghichu'] ?? '');

    $errors = [];
    if ($maCNKH === '') $errors[] = 'Vui long nhap Ma CNKH.';
    if ($maKH === '') $errors[] = 'Vui long chon Khach hang.';
    if ($maNV === '') $errors[] = 'Vui long chon Nhan vien lap.';
    if ($tongno < 0) $errors[] = 'Tong no khong duoc am.';
    if ($tongtiendatra < 0) $errors[] = 'So tien da tra khong duoc am.';

    if (empty($errors)) {
        if (db_exists($conn, "SELECT maCNKH FROM congnokh WHERE maCNKH = ?", 's', [$maCNKH])) {
            $errors[] = "Ma CNKH '$maCNKH' da ton tai.";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO congnokh (maCNKH, maKH, maNV, tongno, tongtiendatra, ghichu) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssdds', $maCNKH, $maKH, $maNV, $tongno, $tongtiendatra, $ghichu);
            $stmt->execute();
            $stmt->close();
            
            flash_set('success', "Them cong no khach hang thanh cong.");
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            error_log('[CongNoKH-Create] ' . $e->getMessage());
            $errors[] = 'Loi he thong khi luu du lieu.';
        }
    }
    $errorMsg = implode('<br>', $errors);
}

require_once APP_ROOT . '/shared/layout.php';
?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Them cong no khach hang</h4>
    <?php if (!empty($errorMsg)) echo "<div class='alert alert-danger'>$errorMsg</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label>Ma CNKH <span class="text-danger">*</span></label>
                <input type="text" name="maCNKH" class="form-control" value="<?= htmlspecialchars($_POST['maCNKH'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Khach hang <span class="text-danger">*</span></label>
                <select name="maKH" class="form-select" required>
                    <option value="">-- Chon Khach Hang --</option>
                    <?php
                    $khRs = $conn->query("SELECT maKH, tenKH FROM khachhang ORDER BY tenKH");
                    $selKH = $_POST['maKH'] ?? '';
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
                    $selNV = $_POST['maNV'] ?? '';
                    while ($x = $nvRs->fetch_assoc()) {
                        $s = ($x['maNV'] === $selNV) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maNV']) . "' $s>" . htmlspecialchars($x['maNV'] . ' - ' . $x['hoten']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label>Ghi chu</label>
                <input type="text" name="ghichu" class="form-control" value="<?= htmlspecialchars($_POST['ghichu'] ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>Tong no</label>
                <input type="number" name="tongno" class="form-control text-danger fw-bold" step="1" min="0" value="<?= htmlspecialchars($_POST['tongno'] ?? '0') ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>So tien da tra</label>
                <input type="number" name="tongtiendatra" class="form-control text-success fw-bold" step="1" min="0" value="<?= htmlspecialchars($_POST['tongtiendatra'] ?? '0') ?>">
            </div>
        </div>
        
        <div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Luu</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Huy</a>
        </div>
    </form>
</div>

</div></body></html>
