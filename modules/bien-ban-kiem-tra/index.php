<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-clipboard-check"></i> Danh sách Biên Bản Kiểm Tra</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Tạo Biên Bản KT</a>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã BB</th>
                <th>Nhân viên</th>
                <th>Nhà cung cấp</th>
                <th>Đại diện NCC</th>
                <th>Thời gian KT</th>
                <th>Địa điểm</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT b.*, n.hoten, nc.tenNCC
                FROM BienBanKiemTra b
                LEFT JOIN NhanVien n ON b.maNV = n.maNV
                LEFT JOIN NhaCungCap nc ON b.maNCC = nc.maNCC
                ORDER BY b.maBB DESC";
        $rs = $conn->query($sql);
        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
            <tr>
                <td><?= htmlspecialchars($r['maBB']) ?></td>
                <td><?= htmlspecialchars($r['hoten'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['tenNCC'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['daidienNCC'] ?? '') ?></td>
                <td><?= !empty($r['thoigianKT']) ? date('d/m/Y H:i', strtotime($r['thoigianKT'])) : '' ?></td>
                <td><?= htmlspecialchars($r['diadiem'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['trangthai'] ?? '') ?></td>
                <td>
                    <a href="detail.php?id=<?= urlencode($r['maBB']) ?>" class="btn btn-info btn-sm text-white">Xem</a>
                    <a href="edit.php?id=<?= urlencode($r['maBB']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maBB']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa biên bản kiểm tra này?')">Xóa</a>
                </td>
            </tr>
        <?php }} else { ?>
            <tr><td colspan="8" class="text-muted">Chưa có biên bản kiểm tra nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div></body></html>
