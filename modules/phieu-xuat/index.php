<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<?php
$q       = trim($_GET['q'] ?? '');
$tuNgay  = trim($_GET['tu_ngay'] ?? '');
$denNgay = trim($_GET['den_ngay'] ?? '');
$loai    = trim($_GET['loai'] ?? '');

$where = []; $params = []; $types = '';

if ($q !== '') {
    $where[] = "(px.maPX LIKE ? OR nv.hoten LIKE ? OR px.maDH LIKE ?)";
    $like = "%$q%";
    $params = array_merge($params, [$like, $like, $like]); $types .= 'sss';
}
if ($tuNgay !== '') { $where[] = "DATE(px.ngayxuat) >= ?"; $params[] = $tuNgay; $types .= 's'; }
if ($denNgay !== '') { $where[] = "DATE(px.ngayxuat) <= ?"; $params[] = $denNgay; $types .= 's'; }
if ($loai !== '') { $where[] = "px.loaiXuat = ?"; $params[] = $loai; $types .= 's'; }

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT px.*, nv.hoten FROM PhieuXuat px LEFT JOIN NhanVien nv ON px.maNV_Lap = nv.maNV $whereSql ORDER BY px.maPX DESC";

if ($params) {
    $stmt = $conn->prepare($sql); $stmt->bind_param($types, ...$params);
    $stmt->execute(); $rs = $stmt->get_result();
} else { $rs = $conn->query($sql); }
?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-file-export"></i> Danh sách phiếu xuất</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Tạo Phiếu Xuất</a>
    </div>

    <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Tìm kiếm</label>
            <input type="text" name="q" class="form-control" placeholder="Mã PX, nhân viên, đơn hàng..." value="<?= htmlspecialchars($q) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Loại xuất</label>
            <select name="loai" class="form-select">
                <option value="">-- Tất cả --</option>
                <option value="BAN_HANG" <?= $loai === 'BAN_HANG' ? 'selected' : '' ?>>Bán hàng</option>
                <option value="THANH_LY" <?= $loai === 'THANH_LY' ? 'selected' : '' ?>>Thanh lý</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Từ ngày</label>
            <input type="date" name="tu_ngay" class="form-control" value="<?= htmlspecialchars($tuNgay) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Đến ngày</label>
            <input type="date" name="den_ngay" class="form-control" value="<?= htmlspecialchars($denNgay) ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Lọc</button>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        </div>
    </form>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã PX</th><th>Loại xuất</th><th>Đơn hàng</th><th>Nhân viên lập</th><th>Ngày xuất</th><th>Tổng tiền</th><th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rs && $rs->num_rows > 0): while ($r = $rs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['maPX']) ?></td>
                <td><span class="badge <?= ($r['loaiXuat'] ?? '') === 'THANH_LY' ? 'bg-warning text-dark' : 'bg-info text-dark' ?>"><?= htmlspecialchars($r['loaiXuat'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($r['maDH'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['hoten'] ?? '') ?></td>
                <td><?= !empty($r['ngayxuat']) ? date('d/m/Y', strtotime($r['ngayxuat'])) : '' ?></td>
                <td class="text-danger fw-bold"><?= number_format((float) $r['tongtien']) ?> đ</td>
                <td>
                    <a href="detail.php?id=<?= urlencode($r['maPX']) ?>" class="btn btn-info btn-sm text-white">Xem</a>
                    <a href="delete.php?id=<?= urlencode($r['maPX']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa phiếu xuất này?')">Xóa</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-muted">Không tìm thấy phiếu xuất nào</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div></body></html>
