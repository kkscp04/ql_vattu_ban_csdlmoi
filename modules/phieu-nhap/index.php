<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<?php
// --- Search & filter ---
$q     = trim($_GET['q'] ?? '');
$tuNgay = trim($_GET['tu_ngay'] ?? '');
$denNgay = trim($_GET['den_ngay'] ?? '');

$where = [];
$params = [];
$types = '';

if ($q !== '') {
    $where[] = "(p.maPN LIKE ? OR n.hoten LIKE ? OR p.maBB LIKE ?)";
    $like = "%$q%";
    $params = array_merge($params, [$like, $like, $like]);
    $types .= 'sss';
}
if ($tuNgay !== '') {
    $where[] = "DATE(p.ngaynhap) >= ?";
    $params[] = $tuNgay; $types .= 's';
}
if ($denNgay !== '') {
    $where[] = "DATE(p.ngaynhap) <= ?";
    $params[] = $denNgay; $types .= 's';
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT p.*, n.hoten
        FROM PhieuNhap p
        LEFT JOIN NhanVien n ON p.maNV_Lap = n.maNV
        $whereSql
        ORDER BY p.maPN DESC";

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
        <h4 class="fw-bold"><i class="fas fa-file-import"></i> Danh sách phiếu nhập</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Tạo Phiếu Nhập</a>
    </div>

    <!-- Search bar -->
    <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Tìm kiếm</label>
            <input type="text" name="q" class="form-control" placeholder="Mã PN, nhân viên, biên bản..." value="<?= htmlspecialchars($q) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Từ ngày</label>
            <input type="date" name="tu_ngay" class="form-control" value="<?= htmlspecialchars($tuNgay) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Đến ngày</label>
            <input type="date" name="den_ngay" class="form-control" value="<?= htmlspecialchars($denNgay) ?>">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Lọc</button>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        </div>
    </form>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã PN</th>
                <th>Nhân viên lập</th>
                <th>Biên bản KT</th>
                <th>Ngày nhập</th>
                <th>Thành tiền</th>
                <th>Ghi chú</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rs && $rs->num_rows > 0): ?>
            <?php while ($r = $rs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['maPN']) ?></td>
                <td><?= htmlspecialchars($r['hoten'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['maBB'] ?? '') ?></td>
                <td><?= !empty($r['ngaynhap']) ? date('d/m/Y', strtotime($r['ngaynhap'])) : '' ?></td>
                <td class="text-danger fw-bold"><?= number_format((float) $r['tongtien']) ?> đ</td>
                <td><?= htmlspecialchars($r['ghichu']) ?></td>
                <td>
                    <a href="detail.php?id=<?= urlencode($r['maPN']) ?>" class="btn btn-info btn-sm text-white">Xem</a>
                    <a href="edit.php?id=<?= urlencode($r['maPN']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maPN']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa phiếu nhập này?')">Xóa</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-muted">Không tìm thấy phiếu nhập nào</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div></body></html>
