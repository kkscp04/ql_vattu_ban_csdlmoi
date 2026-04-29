<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-file-import"></i> Danh sách phiếu nhập</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Tạo Phiếu Nhập</a>
    </div>

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
        <?php
        $sql = "SELECT p.*, n.hoten
                FROM PhieuNhap p
                LEFT JOIN NhanVien n ON p.maNV_Lap = n.maNV
                ORDER BY p.maPN DESC";
        $rs = $conn->query($sql);

        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
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
        <?php }} else { ?>
            <tr><td colspan="7" class="text-muted">Chưa có phiếu nhập nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div></body></html>
