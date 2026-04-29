<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Danh sách vật tư</h4>
        <a href="create.php" class="btn btn-success">+ Thêm</a>
    </div>

    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-primary">
            <tr>
                <th>Mã VT</th>
                <th>Tên vật tư</th>
                <th>Danh mục</th>
                <th>Loại</th>
                <th>ĐVT</th>
                <th>Giá bán</th>
                <th>Số lượng</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT v.*, l.tenLoai, d.tenDM, dv.tenDVT
                FROM VatTu v
                LEFT JOIN LoaiVatTu l ON v.maLoai = l.maLoai
                LEFT JOIN DanhMuc d ON l.maDM = d.maDM
                LEFT JOIN DonViTinh dv ON v.maDVT = dv.maDVT
                ORDER BY v.maVatTu DESC";
        $rs = $conn->query($sql);

        if ($rs && $rs->num_rows > 0) {
            while ($r = $rs->fetch_assoc()) {
        ?>
            <tr>
                <td><?= htmlspecialchars($r['maVatTu']) ?></td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($r['tenVatTu']) ?></td>
                <td><?= htmlspecialchars($r['tenDM'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['tenLoai'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['tenDVT'] ?? '') ?></td>
                <td class="text-danger fw-bold"><?= number_format((float)$r['giaban']) ?> đ</td>
                <td><?= (int)$r['soluong'] ?></td>
                <td>
                    <a href="edit.php?id=<?= urlencode($r['maVatTu']) ?>" class="btn btn-warning btn-sm">Sửa</a>
                    <a href="delete.php?id=<?= urlencode($r['maVatTu']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa vật tư này?')">Xóa</a>
                </td>
            </tr>
        <?php
            }
        } else {
        ?>
            <tr><td colspan="8" class="text-muted">Chưa có vật tư nào</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>