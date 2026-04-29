<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$sql = "SELECT h.*, k.tenKH, n.hoten
        FROM HopDong h
        LEFT JOIN KhachHang k ON h.maKH = k.maKH
        LEFT JOIN NhanVien n ON h.maNV_Lap = n.maNV
        WHERE h.maHDong='$id'";
$row = $conn->query($sql)->fetch_assoc();
if (!$row) { echo "Khong tim thay hop dong"; exit; }
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Chi tiet Hop Dong #<?= htmlspecialchars($row['maHDong']) ?></h4>
        <a href="index.php" class="btn btn-secondary">Quay lai</a>
    </div>

    <p><strong>Khach hang:</strong> <?= htmlspecialchars($row['tenKH'] ?? '') ?></p>
    <p><strong>Nhan vien lap:</strong> <?= htmlspecialchars($row['hoten'] ?? '') ?></p>
    <p><strong>Ngay ky:</strong> <?= !empty($row['ngayky']) ? date('d/m/Y', strtotime($row['ngayky'])) : '' ?></p>
    <p><strong>Ngay hieu luc:</strong> <?= !empty($row['ngayhieuluc']) ? date('d/m/Y', strtotime($row['ngayhieuluc'])) : '' ?></p>
    <p><strong>Ngay het han:</strong> <?= !empty($row['ngayhethan']) ? date('d/m/Y', strtotime($row['ngayhethan'])) : '' ?></p>
    <p><strong>Thoi gian giao hang:</strong> <?= !empty($row['thoigiangiaohang']) ? date('d/m/Y', strtotime($row['thoigiangiaohang'])) : '' ?></p>
    <p><strong>Thoi han thanh toan:</strong> <?= (int)$row['thoihanthanhtoan'] ?> ngay</p>
    <p><strong>Tong truoc thue:</strong> <?= number_format((float)$row['tongtruocthue']) ?> d</p>
    <p><strong>Tien thue:</strong> <?= number_format((float)$row['thue']) ?> d</p>
    <p><strong>Tong gia tri hop dong:</strong> <span class="text-danger fw-bold"><?= number_format((float)$row['tonggiatriHopDong']) ?> d</span></p>
    <p><strong>Phuong thuc thanh toan:</strong> <?= htmlspecialchars($row['phuongthucthanhtoan']) ?></p>
    <p><strong>Trang thai:</strong> <?= htmlspecialchars($row['trangthai']) ?></p>
</div>

</div>
</body>
</html>
