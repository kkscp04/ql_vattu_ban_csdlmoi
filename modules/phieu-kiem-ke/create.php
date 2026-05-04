<?php
require_once __DIR__ . '/_common.php';

$lotOptions = kk_load_available_lots($conn);
$rows = kk_rows_from_lots($lotOptions);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maPKK = trim($_POST['maPKK'] ?? '');
    $maNV = trim($_POST['maNV_Lap'] ?? '');
    $status = trim($_POST['trangthai'] ?? KK_STATUS_DANG_KIEM_KE);
    $ghichu = trim($_POST['ghichu'] ?? '');
    $rows = kk_collect_post_rows($conn);
    $errors = kk_validate($conn, $maPKK, $maNV, $status, $rows, true);

    if (empty($errors)) {
        try {
            kk_save($conn, $maPKK, $maNV, $status, $ghichu, $rows);
            flash_set('success', "Da tao phieu kiem ke '$maPKK'.");
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            error_log('[KiemKe-Create] ' . $e->getMessage());
            $errors[] = 'Loi khi luu phieu kiem ke. Vui long thu lai.';
        }
    }
    $error = implode('<br>', $errors);
}

$lotJson = json_encode($lotOptions, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$rowsJson = json_encode(array_values($rows), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
require_once APP_ROOT . '/shared/layout.php';
?>

<div class="card shadow p-4">
    <h4 class="fw-bold mb-3">Tao phieu kiem ke</h4>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Ma PKK <span class="text-danger">*</span></label>
                <input type="text" name="maPKK" class="form-control" value="<?= htmlspecialchars($_POST['maPKK'] ?? '') ?>" required>
            </div>
            <div class="col-md-3">
                <label>Nhan vien lap <span class="text-danger">*</span></label>
                <select name="maNV_Lap" class="form-select" required>
                    <option value="">-- Chon --</option>
                    <?php
                    $nvRs = $conn->query("SELECT maNV, hoten FROM NhanVien ORDER BY hoten");
                    $selNV = $_POST['maNV_Lap'] ?? '';
                    while ($x = $nvRs->fetch_assoc()) {
                        $s = ($x['maNV'] === $selNV) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($x['maNV']) . "' $s>"
                            . htmlspecialchars($x['maNV'] . ' - ' . $x['hoten']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Trang thai</label>
                <select name="trangthai" class="form-select">
                    <?php $currentStatus = $_POST['trangthai'] ?? KK_STATUS_DANG_KIEM_KE; ?>
                    <?php foreach (kk_statuses() as $status): ?>
                        <option value="<?= htmlspecialchars($status) ?>"<?= $currentStatus === $status ? ' selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Thoi gian</label>
                <input type="text" class="form-control" value="<?= date('d/m/Y H:i') ?>" readonly>
            </div>
            <div class="col-12 mt-3">
                <label>Ghi chu</label>
                <input type="text" name="ghichu" class="form-control" value="<?= htmlspecialchars($_POST['ghichu'] ?? '') ?>">
            </div>
        </div>

        <table class="table table-bordered align-middle" id="tbl">
            <thead>
                <tr>
                    <th>Ma lo</th>
                    <th>Ten vat tu</th>
                    <th>DVT</th>
                    <th>So luong he thong</th>
                    <th>So luong thuc te</th>
                    <th>Chenh lech</th>
                    <th>Ly do/Ghi chu</th>
                    <th class="text-center" style="width:90px;">Xoa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <select name="maLo[]" class="form-select lot-select" required></select>
                        </td>
                        <td><input class="form-control ten-vt" readonly></td>
                        <td><input class="form-control ten-dvt" readonly></td>
                        <td><input type="number" name="soLuongHeThong[]" class="form-control so-he-thong" step="0.01" readonly></td>
                        <td><input type="number" name="soLuongThucTe[]" class="form-control so-thuc-te" min="0" step="0.01" value="<?= htmlspecialchars((string) $row['soLuongThucTe']) ?>" required></td>
                        <td><input type="number" name="chenhLech_view[]" class="form-control chenh-lech" step="0.01" readonly></td>
                        <td><input type="text" name="lydo[]" class="form-control" value="<?= htmlspecialchars($row['lydo']) ?>"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($rows === []): ?>
                    <tr>
                        <td>
                            <select name="maLo[]" class="form-select lot-select" required></select>
                        </td>
                        <td><input class="form-control ten-vt" readonly></td>
                        <td><input class="form-control ten-dvt" readonly></td>
                        <td><input type="number" name="soLuongHeThong[]" class="form-control so-he-thong" step="0.01" readonly></td>
                        <td><input type="number" name="soLuongThucTe[]" class="form-control so-thuc-te" min="0" step="0.01" value="0" required></td>
                        <td><input type="number" name="chenhLech_view[]" class="form-control chenh-lech" step="0.01" readonly></td>
                        <td><input type="text" name="lydo[]" class="form-control"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm" onclick="delRow(this)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <button type="button" class="btn btn-info btn-sm text-white mb-3" onclick="addRow()">
            <i class="fas fa-plus"></i> Them dong
        </button>
        <div>
            <button class="btn btn-success"><i class="fas fa-save"></i> Luu</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Huy</a>
        </div>
    </form>
</div>

<script>
const lotOptions = <?= $lotJson ?>;
const lotMap = Object.fromEntries(lotOptions.map((item) => [item.maLo, item]));
const tableBody = document.querySelector('#tbl tbody');
const initialRows = <?= $rowsJson ?>;

function buildLotOptions(select, selectedValue = '') {
    select.innerHTML = '<option value="">-- Chon lo --</option>';
    lotOptions.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.maLo;
        option.textContent = `${item.maLo} - ${item.tenVatTu}`;
        if (item.maLo === selectedValue) option.selected = true;
        select.appendChild(option);
    });
}

function fillRow(row) {
    const select = row.querySelector('.lot-select');
    const lot = lotMap[select.value] || null;
    row.querySelector('.ten-vt').value = lot ? lot.tenVatTu : '';
    row.querySelector('.ten-dvt').value = lot ? lot.tenDVT : '';
    row.querySelector('.so-he-thong').value = lot ? Number(lot.soLuongHeThong).toFixed(2) : '';
    if (lot && row.querySelector('.so-thuc-te').value === '') {
        row.querySelector('.so-thuc-te').value = Number(lot.soLuongHeThong).toFixed(2);
    }
    calcDiff(row);
}

function calcDiff(row) {
    const systemQty = parseFloat(row.querySelector('.so-he-thong').value) || 0;
    const actualQty = parseFloat(row.querySelector('.so-thuc-te').value) || 0;
    row.querySelector('.chenh-lech').value = (actualQty - systemQty).toFixed(2);
}

function rowHasDuplicateLot(currentRow) {
    const selectedLot = currentRow.querySelector('.lot-select').value;
    if (!selectedLot) return false;
    let found = false;
    tableBody.querySelectorAll('tr').forEach((row) => {
        if (row === currentRow) return;
        if (row.querySelector('.lot-select').value === selectedLot) {
            found = true;
        }
    });
    return found;
}

function addRow() {
    const row = tableBody.rows[0].cloneNode(true);
    row.querySelectorAll('input').forEach((input) => {
        if (!input.classList.contains('so-thuc-te')) input.value = '';
    });
    row.querySelector('.so-thuc-te').value = '0';
    row.querySelector('.lot-select').selectedIndex = 0;
    buildLotOptions(row.querySelector('.lot-select'));
    tableBody.appendChild(row);
    fillRow(row);
}

function delRow(button) {
    if (tableBody.rows.length > 1) {
        button.closest('tr').remove();
    }
}

tableBody.addEventListener('change', (event) => {
    if (event.target.matches('.lot-select')) {
        const row = event.target.closest('tr');
        if (rowHasDuplicateLot(row)) {
            alert('Ma lo da ton tai trong phieu. Neu kiem dem lai, hay cap nhat so luong tren dong hien co.');
            event.target.value = '';
        }
        fillRow(row);
    }
});

tableBody.addEventListener('input', (event) => {
    if (event.target.matches('.so-thuc-te')) {
        calcDiff(event.target.closest('tr'));
    }
});

tableBody.querySelectorAll('tr').forEach((row, index) => {
    const select = row.querySelector('.lot-select');
    const selectedLot = initialRows[index]?.maLo || '';
    buildLotOptions(select, selectedLot);
    if (selectedLot) {
        select.value = selectedLot;
    }
    fillRow(row);
});
</script>

</div></body></html>
