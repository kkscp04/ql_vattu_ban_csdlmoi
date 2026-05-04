<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$px = $conn->query("
    SELECT px.*, nv.hoten
    FROM PhieuXuat px
    LEFT JOIN NhanVien nv ON nv.maNV = px.maNV_Lap
    WHERE px.maPX = '$id'
")->fetch_assoc();

if (!$px) { echo "Không tìm thấy phiếu xuất"; exit; }

$ct = $conn->query("
    SELECT c.*, v.tenVatTu, dv.tenDVT
    FROM ChiTietPhieuXuat c
    INNER JOIN VatTu v ON v.maVatTu = c.maVatTu
    LEFT JOIN DonViTinh dv ON dv.maDVT = c.maDVT
    WHERE c.maPX = '$id'
    ORDER BY c.maLo
");

$hoaDon = $conn->query("SELECT * FROM HoaDon WHERE maPX = '$id' LIMIT 1")->fetch_assoc();
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Chi tiết Phiếu Xuất #<?= htmlspecialchars($id) ?></h4>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </div>

    <p><strong>Loại xuất:</strong> <?= htmlspecialchars($px['loaiXuat'] ?? '') ?></p>
    <p><strong>Đơn hàng:</strong> <?= htmlspecialchars($px['maDH'] ?? '') ?></p>
    <p><strong>Nhân viên lập:</strong> <?= htmlspecialchars($px['hoten'] ?? '') ?></p>
    <p><strong>Ngày xuất:</strong> <?= !empty($px['ngayxuat']) ? date('d/m/Y', strtotime($px['ngayxuat'])) : '' ?></p>
    <p><strong>Tổng tiền:</strong> <span class="text-danger fw-bold"><?= number_format((float) $px['tongtien']) ?> đ</span></p>
    <p><strong>Ghi chú:</strong> <?= htmlspecialchars($px['ghichu']) ?></p>

    <?php if ($hoaDon) { ?>
        <div class="alert alert-warning">
            <strong>Hóa đơn thanh lý:</strong>
            <?= htmlspecialchars($hoaDon['maHDon']) ?> / <?= htmlspecialchars($hoaDon['sohoadon']) ?>
        </div>
    <?php } ?>

    <table class="table table-bordered text-center align-middle mt-3">
        <thead class="table-light">
            <tr>
                <th>STT</th>
                <th>Vật tư</th>
                <th>Mã lô</th>
                <th>ĐVT</th>
                <th>Số lượng</th>
                <th>Đơn giá xuất</th>
                <th>Thành tiền</th>
                <th>Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; while ($r = $ct->fetch_assoc()) { ?>
            <tr>
                <td><?= $i++ ?></td>
                <td class="text-start"><?= htmlspecialchars($r['tenVatTu']) ?></td>
                <td><?= htmlspecialchars($r['maLo'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['tenDVT'] ?? '') ?></td>
                <td><?= rtrim(rtrim(number_format((float) $r['soluong'], 2, '.', ''), '0'), '.') ?></td>
                <td><?= number_format((float) $r['dongiaxuat']) ?> đ</td>
                <td class="text-danger fw-bold"><?= number_format((float) $r['thanhtien']) ?> đ</td>
                <td><?= htmlspecialchars($r['ghichu']) ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</div></body></html>
