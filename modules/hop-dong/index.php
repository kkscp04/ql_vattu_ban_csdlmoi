<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-file-contract"></i> Danh sách hợp đồng</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Hợp Đồng</a>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã HĐ</th>
                <th>Khách hàng</th>
                <th>Nhân viên lập</th>
                <th>Ngày ký</th>
                <th>Tổng giá trị</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT h.*, k.tenKH, n.hoten
                FROM HopDong h
                LEFT JOIN KhachHang k ON h.maKH = k.maKH
                LEFT JOIN NhanVien n ON h.maNV_Lap = n.maNV
                ORDER BY h.maHDong DESC";
        $rs = $conn->query($sql);

        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
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
        <?php
            }
        } else {
        ?>
            <tr><td colspan="7" class="text-muted">Chưa có hợp đồng nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>