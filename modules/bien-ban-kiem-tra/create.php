<?php require_once __DIR__ . '/../../bootstrap.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maBB = $conn->real_escape_string(trim($_POST['maBB'] ?? ''));
    $maNV = $conn->real_escape_string(trim($_POST['maNV'] ?? ''));
    $maNCC = $conn->real_escape_string(trim($_POST['maNCC'] ?? ''));
    $daiDienNCC = $conn->real_escape_string(trim($_POST['daidienNCC'] ?? ''));
    $thoiGianKTInput = $_POST['thoigianKT'] ?? date('Y-m-d\TH:i');
    $thoiGianKT = $conn->real_escape_string(str_replace('T', ' ', $thoiGianKTInput) . (strlen($thoiGianKTInput) === 16 ? ':00' : ''));
    $diaDiem = $conn->real_escape_string(trim($_POST['diadiem'] ?? ''));
    $trangThai = $conn->real_escape_string(trim($_POST['trangthai'] ?? 'Đang kiểm tra'));
    $ghiChu = $conn->real_escape_string(trim($_POST['ghichu'] ?? ''));

    $vt = $_POST['maVatTu'] ?? [];
    $slGiao = $_POST['slGiao'] ?? [];
    $slDat = $_POST['slDat'] ?? [];
    $slLoi = $_POST['slLoi'] ?? [];
    $ketQua = $_POST['ketqua'] ?? [];
    $phuongAn = $_POST['phuonganxuly'] ?? [];
    $ghiChuLoi = $_POST['ghichuloi'] ?? [];

    if ($maBB === '' || $maNV === '' || $maNCC === '') {
        $error = "Vui lòng nhập mã biên bản, nhân viên và nhà cung cấp.";
    } elseif (count(array_filter($vt)) !== count(array_unique(array_filter($vt)))) {
        $error = "Vật tư bị trùng trong chi tiết kiểm tra.";
    } else {
        $conn->begin_transaction();
        try {
            $conn->query("INSERT INTO BienBanKiemTra(maBB, maNV, maNCC, daidienNCC, thoigianKT, diadiem, trangthai, ghichu)
                          VALUES('$maBB', '$maNV', '$maNCC', '$daiDienNCC', '$thoiGianKT', '$diaDiem', '$trangThai', '$ghiChu')");

            for ($i = 0; $i < count($vt); $i++) {
                $maVatTu = $conn->real_escape_string($vt[$i] ?? '');
                $soLuongGiao = (int) ($slGiao[$i] ?? 0);
                $soLuongDat = (int) ($slDat[$i] ?? 0);
                $soLuongLoi = (int) ($slLoi[$i] ?? 0);
                $ketQuaRow = (isset($ketQua[$i]) && $ketQua[$i] !== '') ? (int) $ketQua[$i] : 0;
                $phuongAnRow = $conn->real_escape_string(trim($phuongAn[$i] ?? ''));
                $ghiChuLoiRow = $conn->real_escape_string(trim($ghiChuLoi[$i] ?? ''));

                if ($maVatTu !== '') {
                    $q = $conn->query("SELECT maDVT FROM VatTu WHERE maVatTu='$maVatTu' LIMIT 1");
                    $rowDVT = $q ? $q->fetch_assoc() : null;
                    $maDVT = $rowDVT['maDVT'] ?? '';
                    if ($maDVT === '') {
                        throw new Exception("Không tìm thấy đơn vị tính của vật tư $maVatTu.");
                    }

                    $conn->query("INSERT INTO ChiTietKiemTra(maBB, maVatTu, maDVT, slGiao, slDat, slLoi, ketqua, phuonganxuly, ghichuloi)
                                  VALUES('$maBB', '$maVatTu', '$maDVT', $soLuongGiao, $soLuongDat, $soLuongLoi, $ketQuaRow, '$phuongAnRow', '$ghiChuLoiRow')");
                }
            }

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
    <h4 class="fw-bold mb-3">Tạo Biên Bản Kiểm Tra</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3 mb-3"><label>Mã BB *</label><input type="text" name="maBB" class="form-control" required></div>
            <div class="col-md-3 mb-3"><label>Nhân viên *</label><select name="maNV" class="form-select" required><option value="">-- Chọn --</option><?php $r = $conn->query("SELECT maNV, hoten FROM NhanVien ORDER BY hoten"); while ($x = $r->fetch_assoc()) echo "<option value='{$x['maNV']}'>" . htmlspecialchars($x['maNV'] . ' - ' . $x['hoten']) . "</option>"; ?></select></div>
            <div class="col-md-3 mb-3"><label>Nhà cung cấp *</label><select name="maNCC" class="form-select" required><option value="">-- Chọn --</option><?php $r = $conn->query("SELECT maNCC, tenNCC FROM NhaCungCap ORDER BY tenNCC"); while ($x = $r->fetch_assoc()) echo "<option value='{$x['maNCC']}'>" . htmlspecialchars($x['tenNCC']) . "</option>"; ?></select></div>
            <div class="col-md-3 mb-3"><label>Đại diện NCC</label><input type="text" name="daidienNCC" class="form-control"></div>

            <div class="col-md-4 mb-3"><label>Thời gian kiểm tra</label><input type="datetime-local" name="thoigianKT" class="form-control" value="<?= date('Y-m-d\TH:i') ?>"></div>
            <div class="col-md-4 mb-3"><label>Địa điểm</label><input type="text" name="diadiem" class="form-control"></div>
            <div class="col-md-4 mb-3"><label>Trạng thái</label><input type="text" name="trangthai" class="form-control" value="Đang kiểm tra"></div>
            <div class="col-md-12"><label>Ghi chú</label><input type="text" name="ghichu" class="form-control"></div>
        </div>

        <table class="table table-bordered align-middle" id="tbl">
            <thead>
                <tr>
                    <th>Vật tư</th>
                    <th>ĐVT</th>
                    <th>SL giao</th>
                    <th>SL đạt</th>
                    <th>SL lỗi</th>
                    <th>Kết quả</th>
                    <th>Phương án xử lý</th>
                    <th>Ghi chú lỗi</th>
                    <th class="text-center" style="width:90px;">Xóa</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><select name="maVatTu[]" class="form-select" required><option value="">-- Chọn --</option><?php $r = $conn->query("SELECT v.maVatTu, v.tenVatTu, d.tenDVT FROM VatTu v LEFT JOIN DonViTinh d ON v.maDVT = d.maDVT ORDER BY v.tenVatTu"); while ($x = $r->fetch_assoc()) echo "<option value='{$x['maVatTu']}' data-unit='" . htmlspecialchars($x['tenDVT'] ?? '') . "'>" . htmlspecialchars($x['tenVatTu']) . "</option>"; ?></select></td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="slGiao[]" class="form-control" min="0" value="0"></td>
                    <td><input type="number" name="slDat[]" class="form-control" min="0" value="0"></td>
                    <td><input type="number" name="slLoi[]" class="form-control" min="0" value="0"></td>
                    <td><select name="ketqua[]" class="form-select"><option value="1">Đạt</option><option value="0">Không đạt</option></select></td>
                    <td><input type="text" name="phuonganxuly[]" class="form-control"></td>
                    <td><input type="text" name="ghichuloi[]" class="form-control"></td>
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
const tb = document.querySelector('#tbl tbody');

function fillUnit(row) {
    const s = row.querySelector('select[name="maVatTu[]"]');
    row.querySelector('.unit').value = s.options[s.selectedIndex]?.dataset.unit || '';
}

tb.addEventListener('change', e => {
    if (e.target.matches('select[name="maVatTu[]"]')) {
        fillUnit(e.target.closest('tr'));
    }
});

function addRow() {
    const r = tb.rows[0].cloneNode(true);
    r.querySelectorAll('input').forEach(i => i.value = '');
    r.querySelectorAll('input[type="number"]').forEach(i => i.value = 0);
    r.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    tb.appendChild(r);
}

function delRow(b) {
    if (tb.rows.length > 1) b.closest('tr').remove();
}

document.querySelectorAll('#tbl tbody tr').forEach(fillUnit);
</script>

</div></body></html>
