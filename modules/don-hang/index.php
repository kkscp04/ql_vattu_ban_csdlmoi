<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<?php
$q       = trim($_GET['q'] ?? '');
$tuNgay  = trim($_GET['tu_ngay'] ?? '');
$denNgay = trim($_GET['den_ngay'] ?? '');
$tthai   = trim($_GET['trangthai'] ?? '');

$where = []; $params = []; $types = '';
if ($q !== '') {
    $where[] = "(d.maDH LIKE ? OR k.tenKH LIKE ? OR n.hoten LIKE ? OR d.maHDong LIKE ?)";
    $like = "%$q%"; $params = array_merge($params, [$like, $like, $like, $like]); $types .= 'ssss';
}
if ($tuNgay !== '') { $where[] = "DATE(d.ngaydathang) >= ?"; $params[] = $tuNgay; $types .= 's'; }
if ($denNgay !== '') { $where[] = "DATE(d.ngaydathang) <= ?"; $params[] = $denNgay; $types .= 's'; }
if ($tthai !== '') { $where[] = "d.trangthai = ?"; $params[] = $tthai; $types .= 's'; }

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT d.*, k.tenKH, d.maHDong AS ma_hd, n.hoten FROM DonHang d LEFT JOIN KhachHang k ON d.maKH = k.maKH LEFT JOIN HopDong h ON d.maHDong = h.maHDong LEFT JOIN NhanVien n ON d.maNV_Lap = n.maNV $whereSql ORDER BY d.maDH DESC";

if ($params) { $stmt = $conn->prepare($sql); $stmt->bind_param($types, ...$params); $stmt->execute(); $rs = $stmt->get_result(); }
else { $rs = $conn->query($sql); }
?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-shopping-cart"></i> Danh sách đơn hàng</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Đơn Hàng</a>
    </div>

    <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Tìm kiếm</label>
            <input type="text" name="q" class="form-control" placeholder="Mã DH, khách hàng, nhân viên..." value="<?= htmlspecialchars($q) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Trạng thái</label>
            <select name="trangthai" class="form-select">
                <option value="">-- Tất cả --</option>
                <option value="Đang xử lý" <?= $tthai === 'Đang xử lý' ? 'selected' : '' ?>>Đang xử lý</option>
                <option value="Hoàn thành" <?= $tthai === 'Hoàn thành' ? 'selected' : '' ?>>Hoàn thành</option>
                <option value="Hủy" <?= $tthai === 'Hủy' ? 'selected' : '' ?>>Hủy</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Từ ngày đặt</label>
            <input type="date" name="tu_ngay" class="form-control" value="<?= htmlspecialchars($tuNgay) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Đến ngày đặt</label>
            <input type="date" name="den_ngay" class="form-control" value="<?= htmlspecialchars($denNgay) ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Lọc</button>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        </div>
    </form>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr><th>Mã DH</th><th>Khách hàng</th><th>Hợp đồng</th><th>Nhân viên</th><th>Ngày đặt</th><th>Tiền cọc</th><th>Tổng tiền</th><th>Trạng thái</th><th>Hành động</th></tr>
        </thead>
        <tbody>
        <?php if ($rs && $rs->num_rows > 0): while ($r = $rs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['maDH']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['tenKH'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['ma_hd'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['hoten'] ?? '') ?></td>
                <td><?= !empty($r['ngaydathang']) ? date('d/m/Y', strtotime($r['ngaydathang'])) : '' ?></td>
                <td><?= number_format((float)$r['tiendatcoc']) ?> đ</td>
                <td class="text-danger fw-bold"><?= number_format((float)$r['tongtien']) ?> đ</td>
                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['trangthai']) ?></span></td>
                <td>
                    <a href="detail.php?id=<?= urlencode($r['maDH']) ?>" class="btn btn-info btn-sm text-white">Xem</a>
                    <a href="edit.php?id=<?= urlencode($r['maDH']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maDH']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa đơn hàng này?')">Xóa</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="9" class="text-muted">Không tìm thấy đơn hàng nào</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div></body></html>