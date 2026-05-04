<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<?php
$q       = trim($_GET['q'] ?? '');
$tuNgay  = trim($_GET['tu_ngay'] ?? '');
$denNgay = trim($_GET['den_ngay'] ?? '');
$tthai   = trim($_GET['trangthai'] ?? '');

$where = []; $params = []; $types = '';
if ($q !== '') {
    $where[] = "(h.maHDong LIKE ? OR k.tenKH LIKE ? OR n.hoten LIKE ?)";
    $like = "%$q%"; $params = array_merge($params, [$like, $like, $like]); $types .= 'sss';
}
if ($tuNgay !== '') { $where[] = "DATE(h.ngayky) >= ?"; $params[] = $tuNgay; $types .= 's'; }
if ($denNgay !== '') { $where[] = "DATE(h.ngayky) <= ?"; $params[] = $denNgay; $types .= 's'; }
if ($tthai !== '') { $where[] = "h.trangthai = ?"; $params[] = $tthai; $types .= 's'; }

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT h.*, k.tenKH, n.hoten FROM HopDong h LEFT JOIN KhachHang k ON h.maKH = k.maKH LEFT JOIN NhanVien n ON h.maNV_Lap = n.maNV $whereSql ORDER BY h.maHDong DESC";

if ($params) { $stmt = $conn->prepare($sql); $stmt->bind_param($types, ...$params); $stmt->execute(); $rs = $stmt->get_result(); }
else { $rs = $conn->query($sql); }
?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-file-contract"></i> Danh sách hợp đồng</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Hợp Đồng</a>
    </div>

    <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Tìm kiếm</label>
            <input type="text" name="q" class="form-control" placeholder="Mã HĐ, khách hàng, nhân viên..." value="<?= htmlspecialchars($q) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Trạng thái</label>
            <select name="trangthai" class="form-select">
                <option value="">-- Tất cả --</option>
                <option value="Đang hiệu lực" <?= $tthai === 'Đang hiệu lực' ? 'selected' : '' ?>>Đang hiệu lực</option>
                <option value="Hết hạn" <?= $tthai === 'Hết hạn' ? 'selected' : '' ?>>Hết hạn</option>
                <option value="Thanh lý" <?= $tthai === 'Thanh lý' ? 'selected' : '' ?>>Thanh lý</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Từ ngày ký</label>
            <input type="date" name="tu_ngay" class="form-control" value="<?= htmlspecialchars($tuNgay) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Đến ngày ký</label>
            <input type="date" name="den_ngay" class="form-control" value="<?= htmlspecialchars($denNgay) ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Lọc</button>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        </div>
    </form>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr><th>Mã HĐ</th><th>Khách hàng</th><th>Nhân viên lập</th><th>Ngày ký</th><th>Tổng giá trị</th><th>Trạng thái</th><th>Hành động</th></tr>
        </thead>
        <tbody>
        <?php if ($rs && $rs->num_rows > 0): while ($r = $rs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['maHDong']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['tenKH'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['hoten'] ?? '') ?></td>
                <td><?= !empty($r['ngayky']) ? date('d/m/Y', strtotime($r['ngayky'])) : '' ?></td>
                <td class="text-danger fw-bold"><?= number_format((float)$r['tonggiatriHopDong']) ?> đ</td>
                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['trangthai']) ?></span></td>
                <td>
                    <a href="detail.php?id=<?= urlencode($r['maHDong']) ?>" class="btn btn-info btn-sm text-white">Xem</a>
                    <a href="edit.php?id=<?= urlencode($r['maHDong']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maHDong']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa hợp đồng này?')">Xóa</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-muted">Không tìm thấy hợp đồng nào</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div></body></html>