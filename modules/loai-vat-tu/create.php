<?php
require_once __DIR__ . '/../../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma = trim($_POST['maLoai'] ?? '');
    $ten = trim($_POST['tenLoai'] ?? '');
    $maDM = trim($_POST['maDM'] ?? '');
    $errors = [];

    if ($ma === '') $errors[] = '[R01] Ma loai khong duoc de trong.';
    if ($ten === '') $errors[] = '[R01] Ten loai khong duoc de trong.';
    if ($maDM === '') $errors[] = '[R01] Danh muc khong duoc de trong.';

    if ($ma !== '' && !preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)) {
        $errors[] = '[R02] Ma loai chi gom chu, so, dau . _ - va toi da 50 ky tu.';
    }

    if ($ma !== '' && preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $ma)) {
        if (db_exists($conn, "SELECT maLoai FROM LoaiVatTu WHERE maLoai = ? LIMIT 1", 's', [$ma])) {
            $errors[] = "[R04] Ma loai '$ma' da ton tai.";
        }
    }

    if ($maDM !== '' && !db_exists($conn, "SELECT maDM FROM DanhMuc WHERE maDM = ? LIMIT 1", 's', [$maDM])) {
        $errors[] = "[R06] Danh muc '$maDM' khong ton tai.";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO LoaiVatTu (maLoai, tenLoai, maDM) VALUES (?,?,?)");
            $stmt->bind_param('sss', $ma, $ten, $maDM);
            $stmt->execute();
            $stmt->close();
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            error_log('[LoaiVatTu-Create] ' . $e->getMessage());
            $errors[] = 'Loi khi luu. Vui long thu lai.';
        }
    }

    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<div class="card shadow p-4" style="max-width:700px; margin:0 auto;">
    <h4 class="fw-bold mb-3">Them Loai Vat Tu</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3"><label>Ma loai *</label><input type="text" name="maLoai" class="form-control" value="<?= htmlspecialchars($_POST['maLoai'] ?? '') ?>" required></div>
        <div class="mb-3"><label>Ten loai *</label><input type="text" name="tenLoai" class="form-control" value="<?= htmlspecialchars($_POST['tenLoai'] ?? '') ?>" required></div>
        <div class="mb-3">
            <label>Danh muc *</label>
            <select name="maDM" class="form-select" required>
                <option value="">-- Chon --</option>
                <?php $rs = $conn->query("SELECT maDM, tenDM FROM DanhMuc ORDER BY tenDM"); $sel = $_POST['maDM'] ?? ''; while ($x = $rs->fetch_assoc()) { $s = ($x['maDM'] === $sel) ? 'selected' : ''; echo "<option value='" . htmlspecialchars($x['maDM']) . "' $s>" . htmlspecialchars($x['tenDM']) . "</option>"; } ?>
            </select>
        </div>

        <button class="btn btn-success">Luu</button>
        <a href="index.php" class="btn btn-secondary">Huy</a>
    </form>
</div>

</div></body></html>
