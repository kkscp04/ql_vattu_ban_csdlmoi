<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<?php
$q = trim($_GET['q'] ?? '');
$whereSql = "WHERE l.soluong > 0";
$params = [];
$types = '';

if ($q !== '') {
    $whereSql .= " AND (l.maLo LIKE ? OR l.maVatTu LIKE ? OR v.tenVatTu LIKE ?)";
    $like = "%$q%";
    $params = [$like, $like, $like];
    $types = 'sss';
}

$sql = "SELECT l.maLo, l.maVatTu, l.soluong, l.dongia,
               v.tenVatTu, dv.tenDVT,
               (l.soluong * l.dongia) AS giatri
        FROM LoHang l
        INNER JOIN VatTu v ON v.maVatTu = l.maVatTu
        LEFT JOIN DonViTinh dv ON dv.maDVT = v.maDVT
        $whereSql
        ORDER BY v.tenVatTu, l.maLo";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rs = $stmt->get_result();
} else {
    $rs = $conn->query($sql);
}
$tongGiaTri = 0.0;
?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-boxes-stacked"></i> Báo cáo tồn kho</h4>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Tìm mã lô, mã hàng, tên vật tư..." value="<?= htmlspecialchars($q) ?>">
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
                <th>Mã lô</th>
                <th>Mã hàng</th>
                <th>Tên vật tư</th>
                <th>Đơn vị</th>
                <th>Số lượng tồn</th>
                <th>Vốn tồn kho</th>
                <th>Giá trị tồn</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rs && $rs->num_rows > 0):
            while ($r = $rs->fetch_assoc()):
                $tongGiaTri += (float)$r['giatri']; ?>
            <tr>
                <td><?= htmlspecialchars($r['maLo']) ?></td>
                <td><?= htmlspecialchars($r['maVatTu']) ?></td>
                <td class="text-start"><?= htmlspecialchars($r['tenVatTu']) ?></td>
                <td><?= htmlspecialchars($r['tenDVT'] ?? '') ?></td>
                <td><?= rtrim(rtrim(number_format((float)$r['soluong'], 2, '.', ''), '0'), '.') ?></td>
                <td><?= number_format((float)$r['dongia']) ?> đ</td>
                <td class="text-danger fw-bold"><?= number_format((float)$r['giatri']) ?> đ</td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-muted">Không tìm thấy lô hàng tồn kho nào</td></tr>
        <?php endif; ?>
        </tbody>
        <?php if ($tongGiaTri > 0): ?>
        <tfoot>
            <tr>
                <th colspan="6" class="text-end">Tổng giá trị tồn</th>
                <th class="text-danger"><?= number_format($tongGiaTri) ?> đ</th>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

</div></body></html>
