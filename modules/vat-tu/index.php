<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<?php
$q = trim($_GET['q'] ?? '');
$whereSql = '';
$params = [];
$types = '';

if ($q !== '') {
    $whereSql = "WHERE v.maVatTu LIKE ? OR v.tenVatTu LIKE ? OR l.tenLoai LIKE ? OR d.tenDM LIKE ?";
    $like = "%$q%";
    $params = [$like, $like, $like, $like];
    $types = 'ssss';
}

$sql = "SELECT v.*, l.tenLoai, d.tenDM, dv.tenDVT
        FROM VatTu v
        LEFT JOIN LoaiVatTu l ON v.maLoai = l.maLoai
        LEFT JOIN DanhMuc d ON l.maDM = d.maDM
        LEFT JOIN DonViTinh dv ON v.maDVT = dv.maDVT
        $whereSql
        ORDER BY v.maVatTu DESC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rs = $stmt->get_result();
} else {
    $rs = $conn->query($sql);
}
?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Danh sách vật tư</h4>
        <a href="create.php" class="btn btn-success">+ Thêm</a>
    </div>

    <!-- Thanh tìm kiếm -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control" placeholder="Tìm mã, tên, danh mục..." value="<?= htmlspecialchars($q) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Tìm</button>
        </div>
        <?php if ($q !== ''): ?>
        <div class="col-md-2">
            <a href="index.php" class="btn btn-outline-secondary w-100"><i class="fas fa-times"></i> Xóa</a>
        </div>
        <?php endif; ?>
    </form>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã VT</th>
                <th>Tên vật tư</th>
                <th>Danh mục</th>
                <th>Loại</th>
                <th>ĐVT</th>
                <th>Giá bán</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rs && $rs->num_rows > 0): while ($r = $rs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['maVatTu']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['tenVatTu']) ?></td>
                <td><?= htmlspecialchars($r['tenDM'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['tenLoai'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['tenDVT'] ?? '') ?></td>
                <td class="text-danger fw-bold"><?= number_format((float) $r['giaban']) ?> đ</td>
                <td>
                    <a href="edit.php?id=<?= urlencode($r['maVatTu']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maVatTu']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa vật tư này?')">Xóa</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-muted">Không tìm thấy vật tư nào</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div></body></html>
