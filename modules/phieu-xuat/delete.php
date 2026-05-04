<?php
require_once __DIR__ . '/../../bootstrap.php';

$id = trim($_GET['id'] ?? '');
if ($id !== '') {
    $px = db_fetch_one($conn, "SELECT * FROM PhieuXuat WHERE maPX = ? LIMIT 1", 's', [$id]);
    if ($px) {
        $stmt = $conn->prepare("SELECT maVatTu, maLo, soluong FROM ChiTietPhieuXuat WHERE maPX = ?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        $vatTus = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
            $vatTus[] = $row['maVatTu'];
        }
        $stmt->close();

        $conn->begin_transaction();
        try {
            foreach ($rows as $row) {
                $stmt = $conn->prepare("UPDATE LoHang SET soluong = soluong + ? WHERE maLo = ?");
                $stmt->bind_param('ds', $row['soluong'], $row['maLo']);
                $stmt->execute();
                $stmt->close();
                inventory_update_lot_status($conn, $row['maLo']);
            }

            foreach (array_values(array_unique($vatTus)) as $maVatTu) {
                inventory_update_legacy_stock($conn, $maVatTu);
            }

            $stmt = $conn->prepare("DELETE FROM HoaDon WHERE maPX = ?");
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM ChiTietPhieuXuat WHERE maPX = ?");
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM PhieuXuat WHERE maPX = ?");
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $stmt->close();

            if (!empty($px['maDH'])) {
                inventory_recalculate_order_status($conn, $px['maDH']);
            }

            $conn->commit();
            flash_set('success', "Xoa phieu xuat '$id' thanh cong.");
        } catch (Throwable $e) {
            $conn->rollback();
            error_log("[PhieuXuat-Delete] maPX={$id} :: " . $e->getMessage());
            flash_set('danger', "Xoa phieu xuat '$id' that bai. Vui long thu lai.");
        }
    } else {
        flash_set('warning', "Khong tim thay phieu xuat '$id'.");
    }
} else {
    flash_set('warning', 'Khong tim thay ma phieu xuat can xoa.');
}

header("Location: index.php");
exit;
