<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$dh = $conn->query("
    SELECT d.*, k.tenKH, h.maHDong AS ma_hd, n.hoten
    FROM DonHang d
    LEFT JOIN KhachHang k ON d.maKH = k.maKH
    LEFT JOIN HopDong h ON d.maHDong = h.maHDong
    LEFT JOIN NhanVien n ON d.maNV_Lap = n.maNV
    WHERE d.maDH='$id'
")->fetch_assoc();

if (!$dh) { echo "Không tìm thấy đơn hàng"; exit; }

$ct = $conn->query("
    SELECT c.*, v.tenVatTu, dv.tenDVT
    FROM ChiTietDonHang c
    JOIN VatTu v ON c.maVatTu = v.maVatTu
    LEFT JOIN DonViTinh dv ON v.maDVT = dv.maDVT
    WHERE c.maDH='$id'
");
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Chi tiết Đơn Hàng #<?= htmlspecialchars($id) ?></h4>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </div>

    <p><strong>Khách hàng:</strong> <?= htmlspecialchars($dh['tenKH'] ?? '') ?></p>
    <p><strong>Hợp đồng:</strong> <?= htmlspecialchars($dh['ma_hd'] ?? '') ?></p>
    <p><strong>Nhân viên lập:</strong> <?= htmlspecialchars($dh['hoten'] ?? '') ?></p>
    <p><strong>Ngày đặt:</strong> <?= !empty($dh['ngaydathang']) ? date('d/m/Y', strtotime($dh['ngaydathang'])) : '' ?></p>
    <p><strong>Tiền cọc:</strong> <?= number_format((float)$dh['tiendatcoc']) ?> đ</p>
    <p><strong>Tổng tiền:</strong> <span class="text-danger fw-bold"><?= number_format((float)$dh['tongtien']) ?> đ</span></p>
    <p><strong>Trạng thái:</strong> <?= htmlspecialchars($dh['trangthai']) ?></p>
    <p><strong>Ghi chú:</strong> <?= htmlspecialchars($dh['ghichu']) ?></p>

    <table class="table table-bordered text-center align-middle mt-3">
        <thead class="table-light">
            <tr><th>STT</th><th>Vật tư</th><th>ĐVT</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th><th>Ghi chú</th></tr>
        </thead>
        <tbody>
            <?php $i=1; while($r=$ct->fetch_assoc()){ ?>
            <tr>
                <td><?= $i++ ?></td>
                <td class="text-start"><?= htmlspecialchars($r['tenVatTu']) ?></td>
                <td><?= htmlspecialchars($r['tenDVT'] ?? '') ?></td>
                <td><?= (int)$r['soluong'] ?></td>
                <td><?= number_format((float)$r['dongia']) ?> đ</td>
                <td class="text-danger fw-bold"><?= number_format((float)$r['thanhtien']) ?> đ</td>
                <td><?= htmlspecialchars($r['ghichu']) ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>