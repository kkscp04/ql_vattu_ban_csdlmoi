<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maDH = $conn->real_escape_string(trim($_POST['maDH'] ?? ''));
    $maKH = $conn->real_escape_string(trim($_POST['maKH'] ?? ''));
    $maHD = $conn->real_escape_string(trim($_POST['maHDong'] ?? ''));
    $maNV = $conn->real_escape_string(trim($_POST['maNV_Lap'] ?? ''));
    $ngay = $conn->real_escape_string($_POST['ngaydathang'] ?? date('Y-m-d'));
    $coc = (float)($_POST['tiendatcoc'] ?? 0);
    $ghichu = $conn->real_escape_string(trim($_POST['ghichu'] ?? ''));
    $trangthai = $conn->real_escape_string(trim($_POST['trangthai'] ?? 'Mới tạo'));

    $vt = $_POST['maVatTu'] ?? [];
    $sl = $_POST['soluong'] ?? [];
    $dg = $_POST['dongia'] ?? [];
    $ghict = $_POST['ghichu_ct'] ?? [];

    if ($maDH === '' || $maKH === '' || $maNV === '') {
        $error = "Vui lòng nhập mã đơn hàng, khách hàng, nhân viên lập.";
    } elseif (count(array_filter($vt)) !== count(array_unique(array_filter($vt)))) {
        $error = "Vật tư bị trùng trong chi tiết đơn hàng.";
    } else {
        $conn->begin_transaction();
        try {
            $conn->query("INSERT INTO DonHang(maDH, maKH, maHDong, maNV_Lap, ngaydathang, tiendatcoc, tongtien, ghichu, trangthai)
                          VALUES('$maDH', '$maKH', " . ($maHD !== '' ? "'$maHD'" : "NULL") . ", '$maNV', '$ngay', $coc, 0, '$ghichu', '$trangthai')");

            $tong = 0;
            for ($i = 0; $i < count($vt); $i++) {
                $maVatTu = $conn->real_escape_string($vt[$i] ?? '');
                $soLuong = (int)($sl[$i] ?? 0);
                $donGia = (float)($dg[$i] ?? 0);
                $ghiChuCT = $conn->real_escape_string(trim($ghict[$i] ?? ''));

                if ($maVatTu !== '' && $soLuong > 0) {
                    $thanhTien = $soLuong * $donGia;
                    $tong += $thanhTien;

                    $conn->query("INSERT INTO ChiTietDonHang(maDH, maVatTu, maLo, soluong, dongia, thanhtien, ghichu)
                                  VALUES('$maDH', '$maVatTu', NULL, $soLuong, $donGia, $thanhTien, '$ghiChuCT')");
                }
            }

            $conn->query("UPDATE DonHang SET tongtien=$tong WHERE maDH='$maDH'");
            $conn->commit();
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Thêm Đơn Hàng</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3 mb-3"><label>Mã DH *</label><input type="text" name="maDH" class="form-control" required></div>
            <div class="col-md-3 mb-3"><label>Khách hàng *</label><select name="maKH" class="form-select" required><option value="">-- Chọn --</option><?php $r=$conn->query("SELECT maKH,tenKH FROM KhachHang ORDER BY tenKH"); while($x=$r->fetch_assoc()) echo "<option value='{$x['maKH']}'>".htmlspecialchars($x['tenKH'])."</option>"; ?></select></div>
            <div class="col-md-3 mb-3"><label>Hợp đồng</label><select name="maHDong" class="form-select"><option value="">-- Không chọn --</option><?php $r=$conn->query("SELECT maHDong FROM HopDong ORDER BY maHDong DESC"); while($x=$r->fetch_assoc()) echo "<option value='{$x['maHDong']}'>".htmlspecialchars($x['maHDong'])."</option>"; ?></select></div>
            <div class="col-md-3 mb-3"><label>Nhân viên lập *</label><select name="maNV_Lap" class="form-select" required><option value="">-- Chọn --</option><?php $r=$conn->query("SELECT maNV,hoten FROM NhanVien ORDER BY hoten"); while($x=$r->fetch_assoc()) echo "<option value='{$x['maNV']}'>".htmlspecialchars($x['maNV'].' - '.$x['hoten'])."</option>"; ?></select></div>

            <div class="col-md-3"><label>Ngày đặt</label><input type="date" name="ngaydathang" class="form-control" value="<?= date('Y-m-d') ?>"></div>
            <div class="col-md-3"><label>Tiền đặt cọc</label><input type="number" name="tiendatcoc" class="form-control" min="0" step="0.01" value="0"></div>
            <div class="col-md-3"><label>Trạng thái</label><input type="text" name="trangthai" class="form-control" value="Mới tạo"></div>
            <div class="col-md-3"><label>Ghi chú</label><input type="text" name="ghichu" class="form-control"></div>
        </div>

        <table class="table table-bordered align-middle" id="tbl">
            <thead>
                <tr>
                    <th>Vật tư</th>
                    <th>ĐVT</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Ghi chú CT</th>
                    <th class="text-center" style="width:90px;">Xóa</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><select name="maVatTu[]" class="form-select" required><option value="">-- Chọn --</option><?php $r=$conn->query("SELECT v.maVatTu,v.tenVatTu,d.tenDVT FROM VatTu v LEFT JOIN DonViTinh d ON v.maDVT=d.maDVT ORDER BY v.tenVatTu"); while($x=$r->fetch_assoc()) echo "<option value='{$x['maVatTu']}' data-unit='".htmlspecialchars($x['tenDVT'] ?? '')."'>".htmlspecialchars($x['tenVatTu'])."</option>"; ?></select></td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="soluong[]" class="form-control" min="1" required></td>
                    <td><input type="number" name="dongia[]" class="form-control" min="0" step="0.01" required></td>
                    <td><input type="text" name="ghichu_ct[]" class="form-control"></td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)"><i class="fas fa-trash"></i></button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-info btn-sm text-white mb-3" onclick="addRow()">+ Thêm dòng</button><br>
        <button class="btn btn-success">Lưu</button>
        <a href="index.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

<script>
const tb=document.querySelector('#tbl tbody');
function fillUnit(row){const s=row.querySelector('select[name="maVatTu[]"]'); row.querySelector('.unit').value=s.options[s.selectedIndex]?.dataset.unit||'';}
tb.addEventListener('change',e=>{if(e.target.matches('select[name="maVatTu[]"]')) fillUnit(e.target.closest('tr'));});
function addRow(){const r=tb.rows[0].cloneNode(true);r.querySelectorAll('input').forEach(i=>i.value='');r.querySelector('select').selectedIndex=0;tb.appendChild(r);}
function delRow(b){if(tb.rows.length>1)b.closest('tr').remove();}
document.querySelectorAll('#tbl tbody tr').forEach(fillUnit);
</script>

</div>
</body>
</html>