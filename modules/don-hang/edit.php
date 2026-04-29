<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
$id = $conn->real_escape_string($_GET['id'] ?? '');
if ($id === '') { header("Location: index.php"); exit; }

$dh = $conn->query("SELECT * FROM DonHang WHERE maDH='$id'")->fetch_assoc();
if (!$dh) { echo "Không tìm thấy đơn hàng"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maKH = $conn->real_escape_string(trim($_POST['maKH'] ?? ''));
    $maHD = $conn->real_escape_string(trim($_POST['maHDong'] ?? ''));
    $maNV = $conn->real_escape_string(trim($_POST['maNV_Lap'] ?? ''));
    $ngay = $conn->real_escape_string($_POST['ngaydathang'] ?? date('Y-m-d'));
    $coc = (float)($_POST['tiendatcoc'] ?? 0);
    $ghichu = $conn->real_escape_string(trim($_POST['ghichu'] ?? ''));
    $trangthai = $conn->real_escape_string(trim($_POST['trangthai'] ?? ''));

    $vt = $_POST['maVatTu'] ?? [];
    $sl = $_POST['soluong'] ?? [];
    $dg = $_POST['dongia'] ?? [];
    $ghict = $_POST['ghichu_ct'] ?? [];

    if ($maKH === '' || $maNV === '') {
        $error = "Vui lòng chọn khách hàng và nhân viên lập.";
    } elseif (count(array_filter($vt)) !== count(array_unique(array_filter($vt)))) {
        $error = "Vật tư bị trùng trong chi tiết đơn hàng.";
    } else {
        $conn->begin_transaction();
        try {
            $conn->query("UPDATE DonHang SET
                          maKH='$maKH',
                          maHDong=" . ($maHD !== '' ? "'$maHD'" : "NULL") . ",
                          maNV_Lap='$maNV',
                          ngaydathang='$ngay',
                          tiendatcoc=$coc,
                          ghichu='$ghichu',
                          trangthai='$trangthai'
                          WHERE maDH='$id'");

            $conn->query("DELETE FROM ChiTietDonHang WHERE maDH='$id'");

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
                                  VALUES('$id', '$maVatTu', NULL, $soLuong, $donGia, $thanhTien, '$ghiChuCT')");
                }
            }

            $conn->query("UPDATE DonHang SET tongtien=$tong WHERE maDH='$id'");
            $conn->commit();
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}

$ct = [];
$rs = $conn->query("SELECT * FROM ChiTietDonHang WHERE maDH='$id'");
while ($r = $rs->fetch_assoc()) $ct[] = $r;
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Sửa Đơn Hàng #<?= htmlspecialchars($id) ?></h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3"><label>Mã DH</label><input class="form-control" value="<?= htmlspecialchars($id) ?>" readonly></div>
            <div class="col-md-3"><label>Khách hàng *</label><select name="maKH" class="form-select" required><?php $r=$conn->query("SELECT maKH,tenKH FROM KhachHang ORDER BY tenKH"); while($x=$r->fetch_assoc()){ $s=($x['maKH']===$dh['maKH'])?'selected':''; echo "<option value='{$x['maKH']}' $s>".htmlspecialchars($x['tenKH'])."</option>"; } ?></select></div>
            <div class="col-md-3"><label>Hợp đồng</label><select name="maHDong" class="form-select"><option value="">-- Không chọn --</option><?php $r=$conn->query("SELECT maHDong FROM HopDong ORDER BY maHDong DESC"); while($x=$r->fetch_assoc()){ $s=($x['maHDong']===$dh['maHDong'])?'selected':''; echo "<option value='{$x['maHDong']}' $s>".htmlspecialchars($x['maHDong'])."</option>"; } ?></select></div>
            <div class="col-md-3"><label>Nhân viên lập *</label><select name="maNV_Lap" class="form-select" required><?php $r=$conn->query("SELECT maNV,hoten FROM NhanVien ORDER BY hoten"); while($x=$r->fetch_assoc()){ $s=($x['maNV']===$dh['maNV_Lap'])?'selected':''; echo "<option value='{$x['maNV']}' $s>".htmlspecialchars($x['maNV'].' - '.$x['hoten'])."</option>"; } ?></select></div>

            <div class="col-md-3 mt-3"><label>Ngày đặt</label><input type="date" name="ngaydathang" class="form-control" value="<?= !empty($dh['ngaydathang']) ? date('Y-m-d', strtotime($dh['ngaydathang'])) : date('Y-m-d') ?>"></div>
            <div class="col-md-3 mt-3"><label>Tiền đặt cọc</label><input type="number" name="tiendatcoc" class="form-control" min="0" step="0.01" value="<?= (float)$dh['tiendatcoc'] ?>"></div>
            <div class="col-md-3 mt-3"><label>Trạng thái</label><input type="text" name="trangthai" class="form-control" value="<?= htmlspecialchars($dh['trangthai']) ?>"></div>
            <div class="col-md-3 mt-3"><label>Ghi chú</label><input type="text" name="ghichu" class="form-control" value="<?= htmlspecialchars($dh['ghichu']) ?>"></div>
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
                <?php if ($ct) { foreach ($ct as $r) { ?>
                <tr>
                    <td><select name="maVatTu[]" class="form-select" required><?php $v=$conn->query("SELECT vt.maVatTu,vt.tenVatTu,dv.tenDVT FROM VatTu vt LEFT JOIN DonViTinh dv ON vt.maDVT=dv.maDVT ORDER BY vt.tenVatTu"); while($x=$v->fetch_assoc()){ $s=($x['maVatTu']===$r['maVatTu'])?'selected':''; echo "<option value='{$x['maVatTu']}' data-unit='".htmlspecialchars($x['tenDVT'] ?? '')."' $s>".htmlspecialchars($x['tenVatTu'])."</option>"; } ?></select></td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="soluong[]" class="form-control" min="1" value="<?= (int)$r['soluong'] ?>" required></td>
                    <td><input type="number" name="dongia[]" class="form-control" min="0" step="0.01" value="<?= (float)$r['dongia'] ?>" required></td>
                    <td><input type="text" name="ghichu_ct[]" class="form-control" value="<?= htmlspecialchars($r['ghichu']) ?>"></td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)"><i class="fas fa-trash"></i></button></td>
                </tr>
                <?php }} else { ?>
                <tr>
                    <td><select name="maVatTu[]" class="form-select" required><option value="">-- Chọn --</option><?php $v=$conn->query("SELECT vt.maVatTu,vt.tenVatTu,dv.tenDVT FROM VatTu vt LEFT JOIN DonViTinh dv ON vt.maDVT=dv.maDVT ORDER BY vt.tenVatTu"); while($x=$v->fetch_assoc()) echo "<option value='{$x['maVatTu']}' data-unit='".htmlspecialchars($x['tenDVT'] ?? '')."'>".htmlspecialchars($x['tenVatTu'])."</option>"; ?></select></td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="soluong[]" class="form-control" min="1" required></td>
                    <td><input type="number" name="dongia[]" class="form-control" min="0" step="0.01" required></td>
                    <td><input type="text" name="ghichu_ct[]" class="form-control"></td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)"><i class="fas fa-trash"></i></button></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <button type="button" class="btn btn-info btn-sm text-white mb-3" onclick="addRow()">+ Thêm dòng</button><br>
        <button class="btn btn-warning">Cập nhật</button>
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