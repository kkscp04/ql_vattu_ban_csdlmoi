<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-file-invoice-dollar"></i> Danh sách hóa đơn</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Hóa Đơn</a>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã HĐơn</th>
                <th>Số hóa đơn</th>
                <th>Đơn hàng</th>
                <th>Công nợ KH</th>
                <th>Nhân viên lập</th>
                <th>Ngày tạo</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT h.*, n.hoten
                FROM HoaDon h
                LEFT JOIN NhanVien n ON h.maNV_Lap = n.maNV
                ORDER BY h.maHDon DESC";
        $rs = $conn->query($sql);

        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
            <tr>
                <td><?= htmlspecialchars($r['maHDon']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['sohoadon']) ?></td>
                <td><?= htmlspecialchars($r['maDH'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['maCNKH'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['hoten'] ?? '') ?></td>
                <td><?= !empty($r['ngaytao']) ? date('d/m/Y', strtotime($r['ngaytao'])) : '' ?></td>
                <td class="text-danger fw-bold"><?= number_format((float)$r['tongtien']) ?> đ</td>
                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['trangthai']) ?></span></td>
                <td>
                    <a href="detail.php?id=<?= urlencode($r['maHDon']) ?>" class="btn btn-info btn-sm text-white">Xem</a>
                    <a href="edit.php?id=<?= urlencode($r['maHDon']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maHDon']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa hóa đơn này?')">Xóa</a>
                </td>
            </tr>
        <?php
            }
        } else {
        ?>
            <tr><td colspan="9" class="text-muted">Chưa có hóa đơn nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>