<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Danh sách loại vật tư</h4>
        <a href="create.php" class="btn btn-success">+ Thêm</a>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã loại</th>
                <th>Tên loại</th>
                <th>Danh mục</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT l.*, d.tenDM
                FROM LoaiVatTu l
                LEFT JOIN DanhMuc d ON l.maDM = d.maDM
                ORDER BY l.maLoai DESC";
        $rs = $conn->query($sql);

        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
            <tr>
                <td><?= htmlspecialchars($r['maLoai']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['tenLoai']) ?></td>
                <td><?= htmlspecialchars($r['tenDM'] ?? '') ?></td>
                <td>
                    <a href="edit.php?id=<?= urlencode($r['maLoai']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maLoai']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa loại vật tư này?')">Xóa</a>
                </td>
            </tr>
        <?php
            }
        } else {
        ?>
            <tr><td colspan="4" class="text-muted">Chưa có loại vật tư nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>