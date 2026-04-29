<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$row = $conn->query("
    SELECT h.*, n.hoten
    FROM HoaDon h
    LEFT JOIN NhanVien n ON h.maNV_Lap = n.maNV
    WHERE h.maHDon='$id'
")->fetch_assoc();

if (!$row) { echo "Không tìm thấy hóa đơn"; exit; }
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Chi tiết Hóa Đơn #<?= htmlspecialchars($row['maHDon']) ?></h4>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </div>

    <p><strong>Số hóa đơn:</strong> <?= htmlspecialchars($row['sohoadon']) ?></p>
    <p><strong>Đơn hàng:</strong> <?= htmlspecialchars($row['maDH']) ?></p>
    <p><strong>Công nợ KH:</strong> <?= htmlspecialchars($row['maCNKH']) ?></p>
    <p><strong>Nhân viên lập:</strong> <?= htmlspecialchars($row['hoten'] ?? '') ?></p>
    <p><strong>Ngày tạo:</strong> <?= !empty($row['ngaytao']) ? date('d/m/Y', strtotime($row['ngaytao'])) : '' ?></p>
    <p><strong>Loại hóa đơn:</strong> <?= htmlspecialchars($row['loaihoadon']) ?></p>
    <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($row['diachi']) ?></p>
    <p><strong>Tổng trước thuế:</strong> <?= number_format((float)$row['tongtientruocthue']) ?> đ</p>
    <p><strong>VAT:</strong> <?= (int)$row['thuevat'] ?>%</p>
    <p><strong>Tiền thuế:</strong> <?= number_format((float)$row['tienthue']) ?> đ</p>
    <p><strong>Tổng tiền:</strong> <span class="text-danger fw-bold"><?= number_format((float)$row['tongtien']) ?> đ</span></p>
    <p><strong>Phương thức thanh toán:</strong> <?= htmlspecialchars($row['phuongthucthanhtoan']) ?></p>
    <p><strong>Trạng thái:</strong> <?= htmlspecialchars($row['trangthai']) ?></p>
    <p><strong>Ngày thanh toán:</strong> <?= !empty($row['ngaythanhtoan']) ? date('d/m/Y', strtotime($row['ngaythanhtoan'])) : '' ?></p>
    <p><strong>Ghi chú:</strong> <?= htmlspecialchars($row['ghichu']) ?></p>
</div>

</div>
</body>
</html>