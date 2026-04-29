<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Danh sách đơn vị tính</h4>
        <a href="create.php" class="btn btn-success">+ Thêm</a>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã DVT</th>
                <th>Tên đơn vị</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $rs = $conn->query("SELECT * FROM DonViTinh ORDER BY maDVT DESC");
        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
            <tr>
                <td><?= htmlspecialchars($r['maDVT']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['tenDVT']) ?></td>
                <td>
                    <a href="delete.php?id=<?= urlencode($r['maDVT']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa đơn vị tính này?')">Xóa</a>
                </td>
            </tr>
        <?php
            }
        } else {
        ?>
            <tr><td colspan="3" class="text-muted">Chưa có đơn vị tính nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>