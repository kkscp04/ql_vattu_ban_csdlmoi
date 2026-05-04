<?php
require_once __DIR__ . '/_common.php';
require_once APP_ROOT . '/shared/layout.php';

$q       = trim($_GET['q'] ?? '');
$tuNgay  = trim($_GET['tu_ngay'] ?? '');
$denNgay = trim($_GET['den_ngay'] ?? '');
$tthai   = trim($_GET['trangthai'] ?? '');

$where = []; $params = []; $types = '';
if ($q !== '') {
    $where[] = "(pk.maPKK LIKE ? OR nv.hoten LIKE ?)";
    $like = "%$q%"; $params = array_merge($params, [$like, $like]); $types .= 'ss';
}
if ($tuNgay !== '') { $where[] = "DATE(pk.thoigiankiemke) >= ?"; $params[] = $tuNgay; $types .= 's'; }
if ($denNgay !== '') { $where[] = "DATE(pk.thoigiankiemke) <= ?"; $params[] = $denNgay; $types .= 's'; }
if ($tthai !== '') { $where[] = "pk.trangthai = ?"; $params[] = $tthai; $types .= 's'; }

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT pk.*, nv.hoten,
               (SELECT COUNT(*) FROM ChiTietPhieuKiemKe ct WHERE ct.maPKK = pk.maPKK) AS tongDong
        FROM PhieuKiemKe pk
        LEFT JOIN NhanVien nv ON nv.maNV = pk.maNV_Lap
        $whereSql
        ORDER BY pk.thoigiankiemke DESC, pk.maPKK DESC";

if ($params) {
    $stmt = $conn->prepare($sql); $stmt->bind_param($types, ...$params);
    $stmt->execute(); $rs = $stmt->get_result();
} else { $rs = $conn->query($sql); }
?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-clipboard-check"></i> Danh sách phiếu kiểm kê</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Tạo phiếu kiểm kê</a>
    </div>

    <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Tìm kiếm</label>
            <input type="text" name="q" class="form-control" placeholder="Mã PKK, nhân viên..." value="<?= htmlspecialchars($q) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Trạng thái</label>
            <select name="trangthai" class="form-select">
                <option value="">-- Tất cả --</option>
                <option value="<?= KK_STATUS_DANG_KIEM_KE ?>" <?= $tthai === KK_STATUS_DANG_KIEM_KE ? 'selected' : '' ?>>Đang kiểm kê</option>
                <option value="<?= KK_STATUS_CHO_DUYET ?>" <?= $tthai === KK_STATUS_CHO_DUYET ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="<?= KK_STATUS_HOAN_THANH ?>" <?= $tthai === KK_STATUS_HOAN_THANH ? 'selected' : '' ?>>Hoàn thành</option>
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
            <tr><th>Mã PKK</th><th>Nhân viên</th><th>Thời gian</th><th>Trạng thái</th><th>Số dòng</th><th>Hành động</th></tr>
        </thead>
        <tbody>
        <?php if ($rs && $rs->num_rows > 0): while ($row = $rs->fetch_assoc()):
            $status = $row['trangthai'] ?? '';
            $badge = 'bg-secondary';
            if ($status === KK_STATUS_DANG_KIEM_KE) $badge = 'bg-warning text-dark';
            elseif ($status === KK_STATUS_CHO_DUYET) $badge = 'bg-info text-dark';
            elseif ($status === KK_STATUS_HOAN_THANH) $badge = 'bg-success';
        ?>
            <tr>
                <td><?= htmlspecialchars($row['maPKK']) ?></td>
                <td><?= htmlspecialchars(($row['maNV_Lap'] ?? '') . (($row['hoten'] ?? '') !== '' ? ' - ' . $row['hoten'] : '')) ?></td>
                <td><?= !empty($row['thoigiankiemke']) ? date('d/m/Y H:i', strtotime($row['thoigiankiemke'])) : '' ?></td>
                <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($status) ?></span></td>
                <td><?= (int)($row['tongDong'] ?? 0) ?></td>
                <td>
                    <a href="detail.php?id=<?= urlencode($row['maPKK']) ?>" class="btn btn-info btn-sm text-white">Xem</a>
                    <?php if ($status !== KK_STATUS_HOAN_THANH): ?>
                        <a href="edit.php?id=<?= urlencode($row['maPKK']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                        <a href="delete.php?id=<?= urlencode($row['maPKK']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa phiếu kiểm kê này?')">Xóa</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-muted">Không tìm thấy phiếu kiểm kê nào</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div></body></html>
