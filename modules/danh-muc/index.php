<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<?php
$q = trim($_GET['q'] ?? '');
$whereSql = '';
$params = [];
$types = '';

if ($q !== '') {
    $whereSql = "WHERE maDM LIKE ? OR tenDM LIKE ? OR mota LIKE ?";
    $like = "%$q%";
    $params = [$like, $like, $like];
    $types = 'sss';
}

$sql = "SELECT * FROM DanhMuc $whereSql ORDER BY maDM DESC";

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
        <h4 class="fw-bold">Danh sách danh mục</h4>
        <a href="create.php" class="btn btn-success">+ Thêm</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control" placeholder="Tìm mã, tên hoặc mô tả..." value="<?= htmlspecialchars($q) ?>">
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
                <th>Mã DM</th>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rs && $rs->num_rows > 0): while ($r = $rs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['maDM']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['tenDM']) ?></td>
                <td><?= htmlspecialchars($r['mota']) ?></td>
                <td>
                    <a href="edit.php?id=<?= urlencode($r['maDM']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maDM']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa danh mục này?')">Xóa</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="4" class="text-muted">Không tìm thấy danh mục nào</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div></body></html>