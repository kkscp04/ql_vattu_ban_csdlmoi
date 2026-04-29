<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Danh sách danh mục</h4>
        <a href="create.php" class="btn btn-success">+ Thêm</a>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã DM</th>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $rs = $conn->query("SELECT * FROM DanhMuc ORDER BY maDM DESC");
        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
            <tr>
                <td><?= htmlspecialchars($r['maDM']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['tenDM']) ?></td>
                <td><?= htmlspecialchars($r['mota']) ?></td>
                <td>
                    <a href="edit.php?id=<?= urlencode($r['maDM']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maDM']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa danh mục này?')">Xóa</a>
                </td>
            </tr>
        <?php
            }
        } else {
        ?>
            <tr><td colspan="4" class="text-muted">Chưa có danh mục nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>