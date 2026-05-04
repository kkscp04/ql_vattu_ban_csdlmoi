<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once APP_ROOT . '/shared/layout.php';

// 1. Tong so luong & gia tri ton kho
$rsTonKho = $conn->query("SELECT SUM(soluong) AS tongSL, SUM(soluong * gianhap) AS tongGiaTri FROM vattu WHERE soluong > 0");
$tonKho = $rsTonKho->fetch_assoc();
$tongSL = (int)($tonKho['tongSL'] ?? 0);
$tongGiaTri = (float)($tonKho['tongGiaTri'] ?? 0);

// 2. Tong doanh thu (tu Hoa don)
$rsDoanhThu = $conn->query("SELECT SUM(tongtien) AS doanhthu FROM hoadon");
$doanhThu = (float)($rsDoanhThu->fetch_assoc()['doanhthu'] ?? 0);

// 3. Tong cong no Khach hang
$rsCNKH = $conn->query("SELECT SUM(tongno - tongtiendatra) AS noKH FROM congnokh");
$noKH = (float)($rsCNKH->fetch_assoc()['noKH'] ?? 0);

// 4. Tong cong no Nha cung cap
$rsCNNCC = $conn->query("SELECT SUM(tongno - tongtiendatra) AS noNCC FROM congnoncc");
$noNCC = (float)($rsCNNCC->fetch_assoc()['noNCC'] ?? 0);

// 5. Top 5 vat tu ban chay
$rsTop = $conn->query("
    SELECT v.tenVatTu, SUM(c.soluong) AS tongXuat 
    FROM chitietphieuxuat c 
    JOIN vattu v ON c.maVatTu = v.maVatTu 
    GROUP BY c.maVatTu 
    ORDER BY tongXuat DESC 
    LIMIT 5
");
$topVatTu = [];
while ($row = $rsTop->fetch_assoc()) {
    $topVatTu[] = [
        'ten' => $row['tenVatTu'],
        'sl' => (int)$row['tongXuat']
    ];
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h4 class="fw-bold"><i class="fas fa-chart-pie"></i> Bao cao thong ke</h4>
    </div>

    <!-- Cards Overview -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">Tong so luong ton kho</div>
                            <div class="h5 mb-0 fw-bold"><?= number_format($tongSL) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">Tong gia tri ton kho</div>
                            <div class="h5 mb-0 fw-bold"><?= number_format($tongGiaTri) ?> VNĐ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card bg-info text-white shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">Tong doanh thu (Hoa don)</div>
                            <div class="h5 mb-0 fw-bold"><?= number_format($doanhThu) ?> VNĐ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card bg-warning text-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">Cong no Khach hang (KH no)</div>
                            <div class="h5 mb-0 fw-bold"><?= number_format($noKH) ?> VNĐ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card bg-danger text-white shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">Cong no Nha cung cap (No NCC)</div>
                            <div class="h5 mb-0 fw-bold"><?= number_format($noNCC) ?> VNĐ</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <!-- Top 5 Vat tu -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Top 5 vat tu xuat nhieu nhat</h6>
                </div>
                <div class="card-body">
                    <canvas id="topVatTuChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <!-- Cong no overview -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Tong quan cong no</h6>
                </div>
                <div class="card-body">
                    <canvas id="congNoChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Data for Top 5 Vat Tu Bar Chart
const topVatTuData = <?= json_encode($topVatTu) ?>;
const labelsVT = topVatTuData.map(item => item.ten);
const dataVT = topVatTuData.map(item => item.sl);

new Chart(document.getElementById('topVatTuChart'), {
    type: 'bar',
    data: {
        labels: labelsVT,
        datasets: [{
            label: 'So luong xuat',
            data: dataVT,
            backgroundColor: 'rgba(78, 115, 223, 0.8)',
            borderColor: 'rgba(78, 115, 223, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Data for Cong No Pie Chart
const noKH = <?= $noKH ?>;
const noNCC = <?= $noNCC ?>;

new Chart(document.getElementById('congNoChart'), {
    type: 'doughnut',
    data: {
        labels: ['Khach hang no', 'No Nha cung cap'],
        datasets: [{
            data: [noKH, noNCC],
            backgroundColor: ['#f6c23e', '#e74a3b'],
            hoverBackgroundColor: ['#dda20a', '#be2617'],
            borderWidth: 1
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

</div></body></html>
