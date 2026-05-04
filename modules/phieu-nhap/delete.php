<?php
require_once __DIR__ . '/../../bootstrap.php';

$id = trim($_GET['id'] ?? '');
if ($id !== '') {
    $stmt = $conn->prepare("SELECT maLo, maVatTu FROM ChiTietPhieuNhap WHERE maPN = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $lots = [];
    $vatTus = [];
    while ($row = $res->fetch_assoc()) {
        if (!empty($row['maLo'])) $lots[] = $row['maLo'];
        if (!empty($row['maVatTu'])) $vatTus[] = $row['maVatTu'];
    }
    $stmt->close();

    if (!empty($lots)) {
        $ph = db_placeholders($lots);
        $types = str_repeat('s', count($lots));
        $stmt = $conn->prepare("SELECT maLo FROM ChiTietPhieuXuat WHERE maLo IN ($ph) LIMIT 1");
        $stmt->bind_param($types, ...$lots);
        $stmt->execute();
        $used = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($used) {
            flash_set('warning', "Phieu nhap '$id' da phat sinh xuat kho, khong the xoa.");
            header("Location: index.php");
            exit;
        }
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM ChiTietPhieuNhap WHERE maPN = ?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $stmt->close();

        if (!empty($lots)) {
            $ph = db_placeholders($lots);
            $types = str_repeat('s', count($lots));
            $stmt = $conn->prepare("DELETE FROM LoHang WHERE maLo IN ($ph)");
            $stmt->bind_param($types, ...$lots);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conn->prepare("DELETE FROM PhieuNhap WHERE maPN = ?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $stmt->close();

        foreach (array_values(array_unique($vatTus)) as $maVatTu) {
            inventory_update_legacy_stock($conn, $maVatTu);
        }

        $conn->commit();
        flash_set('success', "Xoa phieu nhap '$id' thanh cong.");
    } catch (Throwable $e) {
        $conn->rollback();
        error_log("[PhieuNhap-Delete] maPN={$id} :: " . $e->getMessage());
        flash_set('danger', "Xoa phieu nhap '$id' that bai. Vui long thu lai.");
    }
} else {
    flash_set('warning', 'Khong tim thay ma phieu nhap can xoa.');
}

header("Location: index.php");
exit;
