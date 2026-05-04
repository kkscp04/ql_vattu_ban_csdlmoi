<?php
require_once __DIR__ . '/../../bootstrap.php';

function validate_phieunhap_edit(
    mysqli $conn,
    string $maPN,
    string $maNV,
    string $maBB,
    string $ngay,
    array $maLoArr,
    array $vtArr,
    array $slArr,
    array $dgArr,
    array $oldLots
): array {
    $errors = [];

    if ($maNV === '') $errors[] = '[R01] Nhân viên lập không được để trống.';
    if ($maNV !== '' && !db_exists($conn, "SELECT maNV FROM NhanVien WHERE maNV = ? LIMIT 1", 's', [$maNV])) {
        $errors[] = "[R06] Nhân viên '$maNV' không tồn tại.";
    }
    if ($maBB !== '' && !db_exists($conn, "SELECT maBB FROM BienBanKiemTra WHERE maBB = ? LIMIT 1", 's', [$maBB])) {
        $errors[] = "[R06] Biên bản kiểm tra '$maBB' không tồn tại.";
    }
    if ($ngay !== '' && strtotime($ngay) === false) {
        $errors[] = '[R10] Ngày nhập không hợp lệ.';
    }

    $validRows = 0;
    $seenLo = [];
    $seenVT = [];
    for ($i = 0; $i < count($vtArr); $i++) {
        $maLo = trim($maLoArr[$i] ?? '');
        $maVT = trim($vtArr[$i] ?? '');
        $soLuong = (float) ($slArr[$i] ?? 0);
        $donGia = (float) ($dgArr[$i] ?? 0);
        if ($maVT === '' && $maLo === '' && $soLuong <= 0) {
            continue;
        }

        $validRows++;
        if ($maLo === '') {
            $errors[] = "[R01] Dòng " . ($i + 1) . " chưa nhập mã lô.";
        } elseif (!preg_match('/^[A-Za-z0-9._\-]{1,50}$/', $maLo)) {
            $errors[] = "[R02] Mã lô '$maLo' không hợp lệ.";
        } else {
            if (in_array($maLo, $seenLo, true)) {
                $errors[] = "[R13] Mã lô '$maLo' bị trùng trong chi tiết.";
            } else {
                $seenLo[] = $maLo;
            }
        }

        if ($maVT === '') $errors[] = "[R01] Dòng " . ($i + 1) . " chưa chọn vật tư.";
        else $seenVT[] = $maVT;

        if ($soLuong <= 0) $errors[] = "[R09] Số lượng dòng " . ($i + 1) . " phải > 0.";
        if ($donGia < 0) $errors[] = "[R09] Đơn giá nhập dòng " . ($i + 1) . " phải >= 0.";
    }

    if ($validRows === 0) {
        $errors[] = '[R12] Phiếu nhập phải có ít nhất 1 dòng chi tiết hợp lệ.';
    }

    if (!empty($seenVT)) {
        $seenVT = array_values(array_unique($seenVT));
        $ph = db_placeholders($seenVT);
        $types = str_repeat('s', count($seenVT));
        $stmt = $conn->prepare("SELECT maVatTu FROM VatTu WHERE maVatTu IN ($ph)");
        $stmt->bind_param($types, ...$seenVT);
        $stmt->execute();
        $res = $stmt->get_result();
        $found = [];
        while ($r = $res->fetch_assoc()) $found[] = $r['maVatTu'];
        $stmt->close();
        foreach ($seenVT as $maVT) {
            if (!in_array($maVT, $found, true)) {
                $errors[] = "[R14] Vật tư '$maVT' không tồn tại.";
            }
        }
    }

    foreach ($seenLo as $maLo) {
        if (in_array($maLo, $oldLots, true)) {
            continue;
        }
        if (db_exists($conn, "SELECT maLo FROM LoHang WHERE maLo = ? LIMIT 1", 's', [$maLo])) {
            $errors[] = "[R04] Mã lô '$maLo' đã tồn tại.";
        }
    }

    if (!empty($oldLots)) {
        $ph = db_placeholders($oldLots);
        $types = str_repeat('s', count($oldLots));
        $stmt = $conn->prepare("SELECT maLo FROM ChiTietPhieuXuat WHERE maLo IN ($ph) LIMIT 1");
        $stmt->bind_param($types, ...$oldLots);
        $stmt->execute();
        $used = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($used) {
            $errors[] = "[R20] Không thể sửa phiếu nhập '$maPN' vì đã có lô xuất kho.";
        }
    }

    return $errors;
}

$idRaw = $_GET['id'] ?? '';
$pn = db_fetch_one($conn, "SELECT * FROM PhieuNhap WHERE maPN = ? LIMIT 1", 's', [$idRaw]);
if (!$pn) {
    echo "<div class='alert alert-danger m-4'>Không tìm thấy phiếu nhập.</div>";
    exit;
}
$id = $pn['maPN'];

$stmtCT = db_prepare_execute($conn, "SELECT * FROM ChiTietPhieuNhap WHERE maPN = ? ORDER BY maLo", 's', [$id]);
$ct = [];
$resCT = $stmtCT->get_result();
while ($r = $resCT->fetch_assoc()) $ct[] = $r;
$stmtCT->close();
$oldLots = array_values(array_filter(array_column($ct, 'maLo')));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maNV = trim($_POST['maNV_Lap'] ?? '');
    $maBB = trim($_POST['maBB'] ?? '');
    $ngay = $_POST['ngaynhap'] ?? date('Y-m-d');
    $ghiChu = trim($_POST['ghichu'] ?? '');

    $maLoArr = $_POST['maLo'] ?? [];
    $vtArr = $_POST['maVatTu'] ?? [];
    $slArr = $_POST['soluong'] ?? [];
    $dgArr = $_POST['dongianhap'] ?? [];
    $ghiCtArr = $_POST['ghichu_ct'] ?? [];

    $errors = validate_phieunhap_edit($conn, $id, $maNV, $maBB, $ngay, $maLoArr, $vtArr, $slArr, $dgArr, $oldLots);

    if (empty($errors)) {
        $validItems = [];
        for ($i = 0; $i < count($vtArr); $i++) {
            $maLo = trim($maLoArr[$i] ?? '');
            $maVT = trim($vtArr[$i] ?? '');
            $soLuong = (float) ($slArr[$i] ?? 0);
            $donGia = (float) ($dgArr[$i] ?? 0);
            $ghiCT = trim($ghiCtArr[$i] ?? '');
            if ($maVT !== '' && $maLo !== '' && $soLuong > 0) {
                $validItems[] = compact('maLo', 'maVT', 'soLuong', 'donGia', 'ghiCT');
            }
        }

        $uniqueVT = array_values(array_unique(array_merge(
            array_column($validItems, 'maVT'),
            array_column($ct, 'maVatTu')
        )));
        $ph = db_placeholders($uniqueVT);
        $types = str_repeat('s', count($uniqueVT));
        $dvtMap = db_fetch_keyed(
            $conn,
            "SELECT maVatTu, maDVT FROM VatTu WHERE maVatTu IN ($ph)",
            $types,
            $uniqueVT,
            'maVatTu'
        );

        $conn->begin_transaction();
        try {
            $ngayFmt = date('Y-m-d', strtotime($ngay));
            $maBBParam = $maBB !== '' ? $maBB : null;

            $stmtUpd = $conn->prepare(
                "UPDATE PhieuNhap
                 SET maNV_Lap = ?, maBB = ?, ngaynhap = ?, ghichu = ?
                 WHERE maPN = ?"
            );
            $stmtUpd->bind_param('sssss', $maNV, $maBBParam, $ngayFmt, $ghiChu, $id);
            $stmtUpd->execute();
            $stmtUpd->close();

            db_prepare_execute($conn, "DELETE FROM ChiTietPhieuNhap WHERE maPN = ?", 's', [$id])->close();
            if (!empty($oldLots)) {
                $ph = db_placeholders($oldLots);
                $types = str_repeat('s', count($oldLots));
                $stmtDelLo = $conn->prepare("DELETE FROM LoHang WHERE maLo IN ($ph)");
                $stmtDelLo->bind_param($types, ...$oldLots);
                $stmtDelLo->execute();
                $stmtDelLo->close();
            }

            $stmtLo = $conn->prepare(
                "INSERT INTO LoHang (maLo, maVatTu, ngayNhap, ngaySX, hanSD, soluong, dongia, trangthai)
                 VALUES (?, ?, ?, NULL, NULL, ?, ?, 'CON_HANG')"
            );
            $stmtCT = $conn->prepare(
                "INSERT INTO ChiTietPhieuNhap
                 (maPN, maVatTu, maLo, maDVT, soluong, dongianhap, thanhtien, ghichu)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $tong = 0.0;
            foreach ($validItems as $item) {
                $maDVT = $dvtMap[$item['maVT']]['maDVT'] ?? null;
                if ($maDVT === null) {
                    throw new RuntimeException("Không tìm thấy đơn vị tính của vật tư {$item['maVT']}.");
                }

                $thanhTien = $item['soLuong'] * $item['donGia'];
                $tong += $thanhTien;

                $stmtLo->bind_param(
                    'sssdd',
                    $item['maLo'],
                    $item['maVT'],
                    $ngayFmt,
                    $item['soLuong'],
                    $item['donGia']
                );
                $stmtLo->execute();

                $stmtCT->bind_param(
                    'ssssddds',
                    $id,
                    $item['maVT'],
                    $item['maLo'],
                    $maDVT,
                    $item['soLuong'],
                    $item['donGia'],
                    $thanhTien,
                    $item['ghiCT']
                );
                $stmtCT->execute();
            }
            $stmtLo->close();
            $stmtCT->close();

            foreach ($uniqueVT as $maVT) {
                inventory_update_legacy_stock($conn, $maVT);
            }

            $stmtTong = $conn->prepare("UPDATE PhieuNhap SET tongtien = ? WHERE maPN = ?");
            $stmtTong->bind_param('ds', $tong, $id);
            $stmtTong->execute();
            $stmtTong->close();

            $conn->commit();
            header("Location: index.php");
            exit;
        } catch (Throwable $e) {
            $conn->rollback();
            error_log('[PhieuNhap-Edit] ' . $e->getMessage());
            $errors[] = 'Lỗi khi cập nhật phiếu nhập. Vui lòng kiểm tra lại dữ liệu.';
        }
    }
    $error = implode('<br>', $errors);
}
?>
<?php require_once APP_ROOT . '/shared/layout.php'; ?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Sửa Phiếu Nhập #<?= htmlspecialchars($id) ?></h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Mã PN</label>
                <input class="form-control" value="<?= htmlspecialchars($id) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label>Nhân viên lập <span class="text-danger">*</span></label>
                <select name="maNV_Lap" class="form-select" required>
                    <?php
                    $nvRs = $conn->query("SELECT maNV, hoten FROM NhanVien ORDER BY hoten");
                    $selNV = $_POST['maNV_Lap'] ?? $pn['maNV_Lap'];
                    while ($x = $nvRs->fetch_assoc()) {
                        $s = ($x['maNV'] === $selNV) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maNV']) . "' $s>"
                            . htmlspecialchars($x['maNV'] . ' - ' . $x['hoten']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Biên bản KT</label>
                <select name="maBB" class="form-select">
                    <option value="">-- Không chọn --</option>
                    <?php
                    $bbRs = $conn->query("SELECT maBB FROM BienBanKiemTra ORDER BY maBB DESC");
                    $selBB = $_POST['maBB'] ?? $pn['maBB'];
                    while ($x = $bbRs->fetch_assoc()) {
                        $s = ($x['maBB'] === $selBB) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maBB']) . "' $s>" . htmlspecialchars($x['maBB']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Ngày nhập</label>
                <input type="date" name="ngaynhap" class="form-control" value="<?= htmlspecialchars($_POST['ngaynhap'] ?? (!empty($pn['ngaynhap']) ? date('Y-m-d', strtotime($pn['ngaynhap'])) : date('Y-m-d'))) ?>">
            </div>
            <div class="col-12 mt-3">
                <label>Ghi chú</label>
                <input type="text" name="ghichu" class="form-control" value="<?= htmlspecialchars($_POST['ghichu'] ?? $pn['ghichu']) ?>">
            </div>
        </div>

        <table class="table table-bordered align-middle" id="tbl">
            <thead>
                <tr>
                    <th>Mã lô</th>
                    <th>Vật tư</th>
                    <th>ĐVT</th>
                    <th>Số lượng</th>
                    <th>Đơn giá nhập</th>
                    <th>Thành tiền</th>
                    <th>Ghi chú CT</th>
                    <th class="text-center" style="width:90px;">Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $vtAllRows = $conn->query(
                    "SELECT v.maVatTu, v.tenVatTu, d.tenDVT
                     FROM VatTu v
                     LEFT JOIN DonViTinh d ON v.maDVT = d.maDVT
                     ORDER BY v.tenVatTu"
                );
                $vtAll = [];
                while ($r = $vtAllRows->fetch_assoc()) $vtAll[] = $r;
                $rows = $ct ?: [['maLo' => '', 'maVatTu' => '', 'soluong' => '', 'dongianhap' => '', 'ghichu' => '']];
                foreach ($rows as $row):
                ?>
                <tr>
                    <td><input type="text" name="maLo[]" class="form-control malo" value="<?= htmlspecialchars($row['maLo'] ?? '') ?>" placeholder="Tu sinh neu de trong"></td>
                    <td>
                        <select name="maVatTu[]" class="form-select" required>
                            <option value="">-- Chọn --</option>
                            <?php foreach ($vtAll as $x): ?>
                                <option value="<?= htmlspecialchars($x['maVatTu']) ?>" data-unit="<?= htmlspecialchars($x['tenDVT'] ?? '') ?>"<?= ($x['maVatTu'] === ($row['maVatTu'] ?? '')) ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($x['tenVatTu']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input class="form-control unit" readonly></td>
                    <td><input type="number" name="soluong[]" class="form-control sl" min="0.01" step="0.01" value="<?= htmlspecialchars((string) ($row['soluong'] ?? '')) ?>" required></td>
                    <td><input type="number" name="dongianhap[]" class="form-control dg" min="0" step="0.01" value="<?= htmlspecialchars((string) ($row['dongianhap'] ?? '')) ?>" required></td>
                    <td><input class="form-control thanhtien" readonly></td>
                    <td><input type="text" name="ghichu_ct[]" class="form-control" value="<?= htmlspecialchars($row['ghichu'] ?? '') ?>"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Tổng tiền</th>
                    <th><input id="tongTien" class="form-control fw-bold text-danger" readonly></th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>

        <button type="button" class="btn btn-info btn-sm text-white mb-3" onclick="addRow()">
            <i class="fas fa-plus"></i> Thêm dòng
        </button>
        <br>
        <button class="btn btn-warning"><i class="fas fa-save"></i> Cập nhật</button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
    </form>
</div>

<script>
const tb = document.querySelector('#tbl tbody');
const tongTienEl = document.getElementById('tongTien');
const form = document.querySelector('form');

function genLo() {
    const ts = Date.now().toString(36).toUpperCase();
    const rnd = Math.floor(Math.random() * 1679616).toString(36).toUpperCase().padStart(4, '0');
    return `LS${ts}${rnd}`.slice(0, 50);
}

function ensureMaLo(row) {
    const inp = row.querySelector('input[name="maLo[]"]');
    if (!inp) return;
    if ((inp.value || '').trim() === '') inp.value = genLo();
}

function fillUnit(row) {
    const sel = row.querySelector('select[name="maVatTu[]"]');
    row.querySelector('.unit').value = sel.options[sel.selectedIndex]?.dataset.unit || '';
}

function calcRow(row) {
    const sl = parseFloat(row.querySelector('.sl').value) || 0;
    const dg = parseFloat(row.querySelector('.dg').value) || 0;
    row.querySelector('.thanhtien').value = (sl * dg).toFixed(2);
    calcTotal();
}

function calcTotal() {
    let sum = 0;
    tb.querySelectorAll('.thanhtien').forEach(el => { sum += parseFloat(el.value) || 0; });
    tongTienEl.value = sum.toFixed(2);
}

tb.addEventListener('change', e => {
    if (e.target.matches('select[name="maVatTu[]"]')) fillUnit(e.target.closest('tr'));
});

tb.addEventListener('input', e => {
    if (e.target.matches('.sl, .dg')) calcRow(e.target.closest('tr'));
});

function addRow() {
    const r = tb.rows[0].cloneNode(true);
    r.querySelectorAll('input').forEach(i => i.value = '');
    r.querySelector('select').selectedIndex = 0;
    tb.appendChild(r);
    calcTotal();
}

function delRow(button) {
    if (tb.rows.length > 1) {
        button.closest('tr').remove();
        calcTotal();
    }
}

document.querySelectorAll('#tbl tbody tr').forEach(r => { fillUnit(r); calcRow(r); });

if (form) {
    form.addEventListener('submit', (e) => {
        const seen = new Set();
        let dup = '';
        tb.querySelectorAll('tr').forEach((row) => {
            const maVT = (row.querySelector('select[name=\"maVatTu[]\"]')?.value || '').trim();
            const sl = parseFloat(row.querySelector('input[name=\"soluong[]\"]')?.value) || 0;
            const dg = parseFloat(row.querySelector('input[name=\"dongianhap[]\"]')?.value) || 0;
            const inp = row.querySelector('input[name=\"maLo[]\"]');
            const vRaw = (inp?.value || '').trim();

            if (maVT === '' && vRaw === '' && sl <= 0 && dg <= 0) return;

            if (inp && vRaw === '') inp.value = genLo();
            const v = (inp?.value || '').trim();
            if (v === '') return;
            if (seen.has(v) && dup === '') dup = v;
            seen.add(v);
        });
        if (dup) {
            e.preventDefault();
            alert(`Ma lo '${dup}' bi trung. Moi dong (vat tu) la 1 lo, vui long nhap ma lo khac nhau.`);
        }
    });
}
</script>

</div></body></html>
