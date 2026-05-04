<?php
require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $macongnoNCC = trim($_POST['macongnoNCC'] ?? '');
    $maNCC = trim($_POST['maNCC'] ?? '');
    $maNV = trim($_POST['maNV'] ?? '');
    $tongno = (float)($_POST['tongno'] ?? 0);
    $tongtiendatra = (float)($_POST['tongtiendatra'] ?? 0);
    $ghichu = trim($_POST['ghichu'] ?? '');

    $errors = [];
    if ($macongnoNCC === '') $errors[] = 'Vui long nhap Ma CN NCC.';
    if ($maNCC === '') $errors[] = 'Vui long chon Nha cung cap.';
    if ($maNV === '') $errors[] = 'Vui long chon Nhan vien lap.';
    if ($tongno < 0) $errors[] = 'Tong no khong duoc am.';
    if ($tongtiendatra < 0) $errors[] = 'So tien da tra khong duoc am.';

    if (empty($errors)) {
        if (db_exists($conn, "SELECT macongnoNCC FROM congnoncc WHERE macongnoNCC = ?", 's', [$macongnoNCC])) {
            $errors[] = "Ma CN NCC '$macongnoNCC' da ton tai.";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO congnoncc (macongnoNCC, maNCC, maNV, tongno, tongtiendatra, ghichu) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssdds', $macongnoNCC, $maNCC, $maNV, $tongno, $tongtiendatra, $ghichu);
            $stmt->execute();
            $stmt->close();
            
            flash_set('success', "Them cong no nha cung cap thanh cong.");
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            error_log('[CongNoNCC-Create] ' . $e->getMessage());
            $errors[] = 'Loi he thong khi luu du lieu.';
        }
    }
    $errorMsg = implode('<br>', $errors);
}

require_once APP_ROOT . '/shared/layout.php';
?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Them cong no nha cung cap</h4>
    <?php if (!empty($errorMsg)) echo "<div class='alert alert-danger'>$errorMsg</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label>Ma CN NCC <span class="text-danger">*</span></label>
                <input type="text" name="macongnoNCC" class="form-control" value="<?= htmlspecialchars($_POST['macongnoNCC'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Nha cung cap <span class="text-danger">*</span></label>
                <select name="maNCC" class="form-select" required>
                    <option value="">-- Chon Nha cung cap --</option>
                    <?php
                    $nccRs = $conn->query("SELECT maNCC, tenNCC FROM nhacungcap ORDER BY tenNCC");
                    $selNCC = $_POST['maNCC'] ?? '';
                    while ($x = $nccRs->fetch_assoc()) {
                        $s = ($x['maNCC'] === $selNCC) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maNCC']) . "' $s>" . htmlspecialchars($x['maNCC'] . ' - ' . $x['tenNCC']) . "</option>";
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
