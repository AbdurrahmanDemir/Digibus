<?php
// Veritabanı bağlantı bilgileri
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'digibus');

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Admin Dashboard</h1>

    <!-- Tarih Seçim Formu -->
    <form method="POST" action="">
        <label for="year">Yıl:</label>
        <select id="year" name="year">
            <?php foreach ($years as $year): ?>
                <option value="<?= $year ?>" <?= $year == $selectedYear ? 'selected' : '' ?>><?= $year ?></option>
            <?php endforeach; ?>
        </select>
        <label for="month">Ay:</label>
        <select id="month" name="month">
            <?php foreach ($months as $month): ?>
                <option value="<?= $month ?>" <?= $month == $selectedMonth ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $month, 10)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filtrele</button>
    </form>

    <!-- Grafik -->
    <canvas id="dashboardChart" width="400" height="200"></canvas>
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
</body>
</html>
