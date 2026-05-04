<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<?php
$q = trim($_GET['q'] ?? '');
$whereSql = '';
$params = [];
$types = '';

if ($q !== '') {
    $whereSql = "WHERE b.maBB LIKE ? OR n.hoten LIKE ? OR nc.tenNCC LIKE ? OR b.diadiem LIKE ?";
    $like = "%$q%";
    $params = [$like, $like, $like, $like];
    $types = 'ssss';
}

$sql = "SELECT b.*, n.hoten, nc.tenNCC
        FROM BienBanKiemTra b
        LEFT JOIN NhanVien n ON b.maNV = n.maNV
        LEFT JOIN NhaCungCap nc ON b.maNCC = nc.maNCC
        $whereSql
        ORDER BY b.maBB DESC";

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
        <h4 class="fw-bold"><i class="fas fa-clipboard-check"></i> Danh sách Biên Bản Kiểm Tra</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Tạo Biên Bản KT</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Tìm mã BB, nhân viên, NCC, địa điểm..." value="<?= htmlspecialchars($q) ?>">
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
                <th>Mã BB</th>
                <th>Nhân viên</th>
                <th>Nhà cung cấp</th>
                <th>Đại diện NCC</th>
                <th>Thời gian KT</th>
                <th>Địa điểm</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rs && $rs->num_rows > 0): while ($r = $rs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['maBB']) ?></td>
                <td><?= htmlspecialchars($r['hoten'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['tenNCC'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['daidienNCC'] ?? '') ?></td>
                <td><?= !empty($r['thoigianKT']) ? date('d/m/Y H:i', strtotime($r['thoigianKT'])) : '' ?></td>
                <td><?= htmlspecialchars($r['diadiem'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['trangthai'] ?? '') ?></td>
                <td>
                    <a href="detail.php?id=<?= urlencode($r['maBB']) ?>" class="btn btn-info btn-sm text-white">Xem</a>
                    <a href="edit.php?id=<?= urlencode($r['maBB']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maBB']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa biên bản kiểm tra này?')">Xóa</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="8" class="text-muted">Không tìm thấy biên bản kiểm tra nào</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div></body></html>
