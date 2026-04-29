<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$pn = $conn->query("
    SELECT p.*, n.hoten
    FROM PhieuNhap p
    LEFT JOIN NhanVien n ON p.maNV_Lap = n.maNV
    WHERE p.maPN='$id'
")->fetch_assoc();
if (!$pn) { echo "Không tìm thấy phiếu nhập"; exit; }

$ct = $conn->query("
    SELECT c.*, v.tenVatTu, d.tenDVT
    FROM ChiTietPhieuNhap c
    JOIN VatTu v ON c.maVatTu = v.maVatTu
    LEFT JOIN DonViTinh d ON c.maDVT = d.maDVT
    WHERE c.maPN='$id'
");
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Chi tiết Phiếu Nhập #<?= htmlspecialchars($id) ?></h4>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </div>

    <p><strong>Nhân viên lập:</strong> <?= htmlspecialchars($pn['hoten'] ?? '') ?></p>
    <p><strong>Biên bản KT:</strong> <?= htmlspecialchars($pn['maBB'] ?? '') ?></p>
    <p><strong>Ngày nhập:</strong> <?= !empty($pn['ngaynhap']) ? date('d/m/Y', strtotime($pn['ngaynhap'])) : '' ?></p>
    <p><strong>Ghi chú:</strong> <?= htmlspecialchars($pn['ghichu']) ?></p>

    <table class="table table-bordered text-center align-middle mt-3">
        <thead class="table-light">
            <tr><th>STT</th><th>Vật tư</th><th>ĐVT</th><th>Số lượng</th><th>Đơn giá nhập</th><th>Thành tiền</th><th>Ghi chú</th></tr>
        </thead>
        <tbody>
            <?php $i = 1; while ($r = $ct->fetch_assoc()) { ?>
            <tr>
                <td><?= $i++ ?></td>
                <td class="text-start"><?= htmlspecialchars($r['tenVatTu']) ?></td>
                <td><?= htmlspecialchars($r['tenDVT'] ?? '') ?></td>
                <td><?= (int) $r['soluong'] ?></td>
                <td><?= number_format((float) $r['dongianhap']) ?> đ</td>
                <td class="text-danger fw-bold"><?= number_format((float) $r['thanhtien']) ?> đ</td>
                <td><?= htmlspecialchars($r['ghichu']) ?></td>
            </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">Tổng tiền</th>
                <th class="text-danger fw-bold"><?= number_format((float) $pn['tongtien']) ?> đ</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>

</div></body></html>
