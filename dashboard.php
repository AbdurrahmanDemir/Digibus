<?php
// Veritabanı bağlantı bilgileri
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'digibus');

include 'header.php';
include 'sidebar.php';
// Veritabanına bağlan
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Bağlantıyı kontrol et
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Seferlerden yıl ve ay bilgilerini al
$years = [];
$months = [];

$yearQuery = "SELECT DISTINCT YEAR(tarih) AS year FROM seferler ORDER BY year DESC";
$monthQuery = "SELECT DISTINCT MONTH(tarih) AS month FROM seferler ORDER BY month ASC";

$yearResult = $conn->query($yearQuery);
$monthResult = $conn->query($monthQuery);

if ($yearResult && $yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = $row['year'];
    }
}

if ($monthResult && $monthResult->num_rows > 0) {
    while ($row = $monthResult->fetch_assoc()) {
        $months[] = $row['month'];
    }
}

// Varsayılan seçimler
$selectedYear = isset($_POST['year']) ? intval($_POST['year']) : (isset($years[0]) ? $years[0] : date('Y'));
$selectedMonth = isset($_POST['month']) ? intval($_POST['month']) : (isset($months[0]) ? $months[0] : date('m'));

// Veritabanında yıl ve aya göre sorgular
$totalSeferlerQuery = "SELECT COUNT(*) AS total_seferler FROM seferler WHERE YEAR(tarih) = $selectedYear AND MONTH(tarih) = $selectedMonth";
$totalOtobusQuery = "SELECT COUNT(*) AS total_otobus FROM otobüs";
$totalYolcularQuery = "
    SELECT COUNT(*) AS total_yolcular 
    FROM yolcu AS y
    JOIN seferler AS s ON y.sefer_id = s.sefer_id
    WHERE YEAR(s.tarih) = $selectedYear AND MONTH(s.tarih) = $selectedMonth
";

$totalSeferlerResult = $conn->query($totalSeferlerQuery)->fetch_assoc();
$totalOtobusResult = $conn->query($totalOtobusQuery)->fetch_assoc();
$totalYolcularResult = $conn->query($totalYolcularQuery)->fetch_assoc();

$totalSeferler = $totalSeferlerResult['total_seferler'];
$totalOtobus = $totalOtobusResult['total_otobus'];
$totalYolcular = $totalYolcularResult['total_yolcular'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <div class="container my-5">
        <h1 class="text-center mb-4">Admin Dashboard</h1>

        <!-- Tarih Seçim Formu -->
        <div class="card p-4 mb-4 shadow">
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="year" class="form-label">Yıl:</label>
                        <select id="year" name="year" class="form-select">
                            <?php foreach ($years as $year): ?>
                                <option value="<?= $year ?>" <?= $year == $selectedYear ? 'selected' : '' ?>><?= $year ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="month" class="form-label">Ay:</label>
                        <select id="month" name="month" class="form-select">
                            <?php foreach ($months as $month): ?>
                                <option value="<?= $month ?>" <?= $month == $selectedMonth ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $month, 10)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">Filtrele</button>
                </div>
            </form>
        </div>

        <!-- Grafik -->
        <div class="card p-4 shadow">
            <h2 class="text-center mb-3">İstatistikler</h2>
            <canvas id="dashboardChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('dashboardChart').getContext('2d');
        const data = {
            labels: ['Seferler', 'Otobüsler', 'Yolcular'],
            datasets: [{
                label: 'Toplam Sayılar',
                data: [<?= $totalSeferler ?>, <?= $totalOtobus ?>, <?= $totalYolcular ?>],
                backgroundColor: ['blue', 'green', 'orange'],
                borderWidth: 1
            }]
        };
        const config = {
            type: 'bar',
            data: data,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };
        new Chart(ctx, config);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
