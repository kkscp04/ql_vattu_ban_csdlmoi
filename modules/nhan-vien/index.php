<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold"><i class="fas fa-user-tie"></i> Danh sách nhân viên</h4>
        <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Nhân Viên</a>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã NV</th>
                <th>Họ tên</th>
                <th>SĐT</th>
                <th>Email</th>
                <th>Trạng thái</th>
                <th>Mã CV</th>
                <th>Tên chức vụ</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT n.*, c.tenCV
                FROM NhanVien n
                LEFT JOIN ChucVu c ON n.maCV = c.maCV
                ORDER BY n.maNV DESC";
        $rs = $conn->query($sql);
        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
            <tr>
                <td><?= htmlspecialchars($r['maNV']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['hoten']) ?></td>
                <td><?= htmlspecialchars($r['sdt'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['trangthai'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($r['maCV']) ?></td>
                <td><?= htmlspecialchars($r['tenCV'] ?? '') ?></td>
                <td>
                    <a href="edit.php?id=<?= urlencode($r['maNV']) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maNV']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa nhân viên này?')"><i class="fas fa-trash"></i> Xóa</a>
                </td>
            </tr>
        <?php
            }
        } else {
        ?>
            <tr><td colspan="8" class="text-muted">Chưa có nhân viên nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>
