<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>
<?php
$hasMaCV = db_table_has_column($conn, 'NhanVien', 'maCV');
$hasChucVuText = db_table_has_column($conn, 'NhanVien', 'chucvu');

$q = trim($_GET['q'] ?? '');
$whereSql = '';
$params = [];
$types = '';

if ($q !== '') {
    if ($hasMaCV) {
        $whereSql = "WHERE nv.maNV LIKE ? OR nv.hoten LIKE ? OR nv.sdt LIKE ? OR cv.tenCV LIKE ?";
        $like = "%$q%";
        $params = [$like, $like, $like, $like];
        $types = 'ssss';
    } else {
        $whereSql = "WHERE nv.maNV LIKE ? OR nv.hoten LIKE ? OR nv.sdt LIKE ?";
        $like = "%$q%";
        $params = [$like, $like, $like];
        $types = 'sss';
    }
}

if ($hasMaCV) {
    $sql = "SELECT nv.*, cv.tenCV AS tenChucVu
            FROM NhanVien nv
            LEFT JOIN ChucVu cv ON nv.maCV = cv.maCV
            $whereSql
            ORDER BY nv.maNV DESC";
} else {
    $sql = "SELECT nv.*, nv.chucvu AS tenChucVu
            FROM NhanVien nv
            $whereSql
            ORDER BY nv.maNV DESC";
}

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
        <h4 class="fw-bold"><i class="fas fa-user-tie"></i> Danh sách nhân viên</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Nhân Viên</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Tìm mã, tên, SĐT, chức vụ..." value="<?= htmlspecialchars($q) ?>">
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
                <th>Mã NV</th>
                <th>Họ tên</th>
                <th>SĐT</th>
                <th>Email</th>
                <th>Trạng thái</th>
                <th>Chức vụ</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rs && $rs->num_rows > 0): ?>
            <?php while ($r = $rs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['maNV']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['hoten']) ?></td>
                <td><?= htmlspecialchars($r['sdt'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['trangthai'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($r['tenChucVu'] ?? ($hasChucVuText ? ($r['chucvu'] ?? '') : '')) ?></td>
                <td>
                    <a href="edit.php?id=<?= urlencode($r['maNV']) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maNV']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa nhân viên này?')"><i class="fas fa-trash"></i> Xóa</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-muted">Không tìm thấy nhân viên nào</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div></body></html>
