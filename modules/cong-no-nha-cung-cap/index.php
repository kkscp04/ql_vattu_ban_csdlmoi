<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once APP_ROOT . '/shared/layout.php';

$q = trim($_GET['q'] ?? '');
$where = []; $params = []; $types = '';
if ($q !== '') {
    $where[] = "(c.macongnoNCC LIKE ? OR ncc.tenNCC LIKE ? OR nv.hoten LIKE ?)";
    $like = "%$q%"; $params = array_merge($params, [$like, $like, $like]); $types .= 'sss';
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT c.*, ncc.tenNCC, nv.hoten FROM congnoncc c LEFT JOIN nhacungcap ncc ON c.maNCC = ncc.maNCC LEFT JOIN nhanvien nv ON c.maNV = nv.maNV $whereSql ORDER BY c.macongnoNCC DESC";
if ($params) { $stmt = $conn->prepare($sql); $stmt->bind_param($types, ...$params); $stmt->execute(); $rs = $stmt->get_result(); }
else { $rs = $conn->query($sql); }
?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-file-invoice-dollar"></i> Công nợ nhà cung cấp</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm công nợ NCC</a>
    </div>

    <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-8">
            <label class="form-label mb-1 fw-semibold" style="font-size:13px;">Tìm kiếm</label>
            <input type="text" name="q" class="form-control" placeholder="Mã CN NCC, tên nhà cung cấp, nhân viên..." value="<?= htmlspecialchars($q) ?>">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Tìm</button>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-primary">
                <tr><th>Mã CN NCC</th><th>Nhà cung cấp</th><th>Nhân viên lập</th><th>Tổng nợ</th><th>Đã trả</th><th>Còn lại</th><th>Ghi chú</th><th>Hành động</th></tr>
            </thead>
            <tbody>
                <?php if ($rs && $rs->num_rows > 0): while ($row = $rs->fetch_assoc()):
                    $tongno = (float)($row['tongno'] ?? 0); $datra = (float)($row['tongtiendatra'] ?? 0); $conlai = $tongno - $datra;
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['macongnoNCC']) ?></td>
                    <td class="text-start"><?= htmlspecialchars($row['tenNCC'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['hoten'] ?? '') ?></td>
                    <td class="text-end text-danger fw-bold"><?= number_format($tongno) ?></td>
                    <td class="text-end text-success fw-bold"><?= number_format($datra) ?></td>
                    <td class="text-end text-primary fw-bold"><?= number_format($conlai) ?></td>
                    <td><?= htmlspecialchars($row['ghichu'] ?? '') ?></td>
                    <td>
                        <a href="edit.php?id=<?= urlencode($row['macongnoNCC']) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <a href="delete.php?id=<?= urlencode($row['macongnoNCC']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa công nợ này?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="8" class="text-muted">Chưa có dữ liệu</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div></body></html>
