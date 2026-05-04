<?php
require_once __DIR__ . '/_common.php';

$id = trim($_GET['id'] ?? '');
$header = kk_fetch_header($conn, $id);
if (!$header) {
    flash_set('danger', 'Khong tim thay phieu kiem ke.');
    header('Location: index.php');
    exit;
}
$rows = kk_fetch_detail_rows($conn, $id);
require_once APP_ROOT . '/shared/layout.php';
?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Chi tiet phieu kiem ke #<?= htmlspecialchars($id) ?></h4>
        <div>
            <?php if (($header['trangthai'] ?? '') !== KK_STATUS_HOAN_THANH): ?>
                <a href="edit.php?id=<?= urlencode($id) ?>" class="btn btn-warning">Sua</a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-secondary">Quay lai</a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3"><strong>Nhan vien:</strong> <?= htmlspecialchars($header['maNV_Lap'] ?? '') ?></div>
        <div class="col-md-3"><strong>Trang thai:</strong> <?= htmlspecialchars($header['trangthai'] ?? '') ?></div>
        <div class="col-md-3"><strong>Thoi gian:</strong> <?= !empty($header['thoigiankiemke']) ? date('d/m/Y H:i', strtotime($header['thoigiankiemke'])) : '' ?></div>
        <div class="col-md-3"><strong>Hoan thanh:</strong> <?= !empty($header['ngayhoanthanh']) ? date('d/m/Y H:i', strtotime($header['ngayhoanthanh'])) : '' ?></div>
        <div class="col-12 mt-2"><strong>Ghi chu:</strong> <?= htmlspecialchars($header['ghichu'] ?? '') ?></div>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Ma lo</th>
                <th>Ten vat tu</th>
                <th>DVT</th>
                <th>So luong he thong</th>
                <th>So luong thuc te</th>
                <th>Chenh lech</th>
                <th>Ly do/Ghi chu</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rows !== []): ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['maLo']) ?></td>
                    <td class="text-start"><?= htmlspecialchars($row['tenVatTu']) ?></td>
                    <td><?= htmlspecialchars($row['tenDVT']) ?></td>
                    <td><?= number_format((float) $row['soLuongHeThong'], 2, '.', '') ?></td>
                    <td><?= number_format((float) $row['soLuongThucTe'], 2, '.', '') ?></td>
                    <td class="<?= ((float) $row['chenhLech']) !== 0.0 ? 'text-danger fw-bold' : '' ?>"><?= number_format((float) $row['chenhLech'], 2, '.', '') ?></td>
                    <td class="text-start"><?= htmlspecialchars($row['lydo']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-muted">Khong co chi tiet</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div></body></html>

