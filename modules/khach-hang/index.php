<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<?php
$q = trim($_GET['q'] ?? '');
$whereSql = '';
$params = [];
$types = '';

if ($q !== '') {
    $whereSql = "WHERE maKH LIKE ? OR tenKH LIKE ? OR sdt LIKE ? OR diachi LIKE ? OR loaiKH LIKE ?";
    $like = "%$q%";
    $params = [$like, $like, $like, $like, $like];
    $types = 'sssss';
}

$sql = "SELECT * FROM KhachHang $whereSql ORDER BY maKH DESC";

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
        <h4 class="fw-bold"><i class="fas fa-users"></i> Danh sách khách hàng</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Khách Hàng</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Tìm mã KH, tên, SĐT, địa chỉ, loại KH..." value="<?= htmlspecialchars($q) ?>">
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
                <th>Mã KH</th>
                <th>Tên khách hàng</th>
                <th>Loại KH</th>
                <th>Điện thoại</th>
                <th>Địa chỉ</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rs && $rs->num_rows > 0): while ($r = $rs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['maKH']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['tenKH']) ?></td>
                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['loaiKH']) ?></span></td>
                <td><?= htmlspecialchars($r['sdt']) ?></td>
                <td><?= htmlspecialchars($r['diachi']) ?></td>
                <td>
                    <a href="edit.php?id=<?= urlencode($r['maKH']) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maKH']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa khách hàng này?')"><i class="fas fa-trash"></i> Xóa</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-muted">Không tìm thấy khách hàng nào</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div></body></html>