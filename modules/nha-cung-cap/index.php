<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-truck"></i> Danh sách nhà cung cấp</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Nhà Cung Cấp</a>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã NCC</th>
                <th>Tên nhà cung cấp</th>
                <th>Mã số thuế</th>
                <th>Người liên hệ</th>
                <th>SĐT</th>
                <th>Email</th>
                <th>Địa chỉ</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $rs = $conn->query("SELECT * FROM NhaCungCap ORDER BY maNCC DESC");
        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
            <tr>
                <td><?= htmlspecialchars($r['maNCC']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['tenNCC']) ?></td>
                <td><?= htmlspecialchars($r['masothue']) ?></td>
                <td><?= htmlspecialchars($r['nguoilienhe']) ?></td>
                <td><?= htmlspecialchars($r['sdt']) ?></td>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td><?= htmlspecialchars($r['diachi']) ?></td>
                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['trangthai']) ?></span></td>
                <td>
                    <a href="edit.php?id=<?= urlencode($r['maNCC']) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maNCC']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa nhà cung cấp này?')"><i class="fas fa-trash"></i> Xóa</a>
                </td>
            </tr>
        <?php
            }
        } else {
        ?>
            <tr><td colspan="9" class="text-muted">Chưa có nhà cung cấp nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>