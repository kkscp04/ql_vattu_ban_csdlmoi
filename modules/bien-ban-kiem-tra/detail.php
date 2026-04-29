<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$bb = $conn->query("
    SELECT b.*, n.hoten, nc.tenNCC
    FROM BienBanKiemTra b
    LEFT JOIN NhanVien n ON b.maNV = n.maNV
    LEFT JOIN NhaCungCap nc ON b.maNCC = nc.maNCC
    WHERE b.maBB='$id'
")->fetch_assoc();
if (!$bb) { echo "Không tìm thấy biên bản kiểm tra"; exit; }

$ct = $conn->query("
    SELECT c.*, v.tenVatTu, d.tenDVT
    FROM ChiTietKiemTra c
    JOIN VatTu v ON c.maVatTu = v.maVatTu
    LEFT JOIN DonViTinh d ON c.maDVT = d.maDVT
    WHERE c.maBB='$id'
");
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Chi tiết Biên Bản Kiểm Tra #<?= htmlspecialchars($id) ?></h4>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </div>

    <p><strong>Nhân viên:</strong> <?= htmlspecialchars($bb['hoten'] ?? '') ?></p>
    <p><strong>Nhà cung cấp:</strong> <?= htmlspecialchars($bb['tenNCC'] ?? '') ?></p>
    <p><strong>Đại diện NCC:</strong> <?= htmlspecialchars($bb['daidienNCC'] ?? '') ?></p>
    <p><strong>Thời gian kiểm tra:</strong> <?= !empty($bb['thoigianKT']) ? date('d/m/Y H:i', strtotime($bb['thoigianKT'])) : '' ?></p>
    <p><strong>Địa điểm:</strong> <?= htmlspecialchars($bb['diadiem'] ?? '') ?></p>
    <p><strong>Trạng thái:</strong> <?= htmlspecialchars($bb['trangthai'] ?? '') ?></p>
    <p><strong>Ghi chú:</strong> <?= htmlspecialchars($bb['ghichu'] ?? '') ?></p>

    <table class="table table-bordered text-center align-middle mt-3">
        <thead class="table-light">
            <tr><th>STT</th><th>Vật tư</th><th>ĐVT</th><th>SL giao</th><th>SL đạt</th><th>SL lỗi</th><th>Kết quả</th><th>Phương án xử lý</th><th>Ghi chú lỗi</th></tr>
        </thead>
        <tbody>
            <?php $i = 1; while ($r = $ct->fetch_assoc()) { ?>
            <tr>
                <td><?= $i++ ?></td>
                <td class="text-start"><?= htmlspecialchars($r['tenVatTu']) ?></td>
                <td><?= htmlspecialchars($r['tenDVT'] ?? '') ?></td>
                <td><?= (int) $r['slGiao'] ?></td>
                <td><?= (int) $r['slDat'] ?></td>
                <td><?= (int) $r['slLoi'] ?></td>
                <td><?= !empty($r['ketqua']) ? 'Đạt' : 'Không đạt' ?></td>
                <td><?= htmlspecialchars($r['phuonganxuly'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['ghichuloi'] ?? '') ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</div></body></html>
