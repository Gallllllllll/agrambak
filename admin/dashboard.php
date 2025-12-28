<?php
require "../middleware/auth.php";
admin_required();
require "../config/database.php";

$nama = $_SESSION['user']['nama'] ?? 'Administrator';


// Statistik
$totalReservasi = $pdo->query("SELECT COUNT(*) FROM reservasi")->fetchColumn();
$totalPendapatan = $pdo->query("
    SELECT SUM(jumlah) 
    FROM pembayaran 
    WHERE status = 'berhasil'
")->fetchColumn();

$reservasiBulanan = $pdo->query("
    SELECT 
        MONTH(waktu_pesan) AS bulan,
        COUNT(*) AS total
    FROM reservasi
    WHERE YEAR(waktu_pesan) = YEAR(CURDATE())
    GROUP BY MONTH(waktu_pesan)
    ORDER BY bulan
")->fetchAll(PDO::FETCH_KEY_PAIR);

$pendapatanBulanan = $pdo->query("
    SELECT 
        MONTH(waktu_bayar) AS bulan,
        SUM(jumlah) AS total
    FROM pembayaran
    WHERE status = 'berhasil'
      AND YEAR(waktu_bayar) = YEAR(CURDATE())
    GROUP BY MONTH(waktu_bayar)
    ORDER BY bulan
")->fetchAll(PDO::FETCH_KEY_PAIR);

$bulanLabel = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

$reservasiChart = [];
$pendapatanChart = [];

for ($i = 1; $i <= 12; $i++) {
    $reservasiChart[]  = (int)($reservasiBulanan[$i] ?? 0);
    $pendapatanChart[] = (int)($pendapatanBulanan[$i] ?? 0);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../aset/css/dashboard_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<body>
<!-- SIDEBAR -->    
<?php include "sidebar.php"; ?>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h1>Dashboard</h1>
            <p>Selamat datang, <strong><?= htmlspecialchars($nama) ?></strong></p>
        </div>
    </div>
    

    <div class="stats-section">

        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-label">Total Reservasi</div>
                <div class="stat-value"><?= $totalReservasi ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-value">
                    Rp<?= number_format($totalPendapatan ?? 0, 0, ',', '.') ?>
                </div>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <div class="chart-title">Reservasi per Bulan</div>
                <div id="chartReservasi"></div>
            </div>

            <div class="chart-card">
                <div class="chart-title">Pendapatan per Bulan</div>
                <div id="chartPendapatan"></div>
            </div>
        </div>


    </div>
</div>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
Highcharts.chart('chartReservasi', {
    chart: {
        type: 'column',
        backgroundColor: 'transparent'
    },

    title: {
        text: null
    },

    xAxis: {
        categories: <?= json_encode($bulanLabel) ?>
    },

    yAxis: {
        title: {
            text: 'Jumlah Reservasi'
        },
        allowDecimals: false
    },

    tooltip: {
        valueSuffix: ' reservasi'
    },

    series: [{
        name: 'Reservasi',
        data: <?= json_encode($reservasiChart) ?>
    }],

    credits: {
        enabled: false
    }
});
</script>
<script>
Highcharts.chart('chartPendapatan', {
    chart: {
        type: 'spline',
        backgroundColor: 'transparent',
        color: '#8bc34a'
    },

    title: {
        text: null
    },

    xAxis: {
        categories: <?= json_encode($bulanLabel) ?>
    },

    yAxis: {
        title: {
            text: 'Pendapatan (Rp)'
        },
        labels: {
            formatter: function () {
                return 'Rp' + Highcharts.numberFormat(this.value, 0, ',', '.');
            }
        }
    },

    tooltip: {
        formatter: function () {
            return '<b>' + this.x + '</b><br>Rp' +
                Highcharts.numberFormat(this.y, 0, ',', '.');
        }
    },

    series: [{
        name: 'Pendapatan',
        data: <?= json_encode($pendapatanChart) ?>
    }],

    credits: {
        enabled: false
    }
});
</script>
</body>
</html>
