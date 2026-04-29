<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-users"></i> Danh sách khách hàng</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Khách Hàng</a>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã KH</th>
                <th>Tên khách hàng</th>
                <th>Loại KH</th>
                <th>Điện thoại</th>
                <th>Địa chỉ</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $rs = $conn->query("SELECT * FROM KhachHang ORDER BY maKH DESC");
        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
            <tr>
                <td><?= htmlspecialchars($r['maKH']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['tenKH']) ?></td>
                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['loaiKH']) ?></span></td>
                <td><?= htmlspecialchars($r['sdt']) ?></td>
                <td><?= htmlspecialchars($r['diachi']) ?></td>
                <td>
                    <a href="edit.php?id=<?= urlencode($r['maKH']) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maKH']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa khách hàng này?')"><i class="fas fa-trash"></i> Xóa</a>
                </td>
            </tr>
        <?php
            }
        } else {
        ?>
            <tr><td colspan="6" class="text-muted">Chưa có khách hàng nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>