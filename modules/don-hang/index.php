<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-shopping-cart"></i> Danh sách đơn hàng</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Đơn Hàng</a>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã DH</th>
                <th>Khách hàng</th>
                <th>Hợp đồng</th>
                <th>Nhân viên lập</th>
                <th>Ngày đặt</th>
                <th>Tiền cọc</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT d.*, k.tenKH, h.maHDong AS ma_hd, n.hoten
                FROM DonHang d
                LEFT JOIN KhachHang k ON d.maKH = k.maKH
                LEFT JOIN HopDong h ON d.maHDong = h.maHDong
                LEFT JOIN NhanVien n ON d.maNV_Lap = n.maNV
                ORDER BY d.maDH DESC";
        $rs = $conn->query($sql);

        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
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
        <?php
            }
        } else {
        ?>
            <tr><td colspan="9" class="text-muted">Chưa có đơn hàng nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>