<?php
// Veritabanı bağlantı bilgileri
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'digibus');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

session_start();

// Sefer bilgilerini al
$sefer_id = $_GET['sefer_id'];
$seferDetaylari = [];
$molalar = [];

$seferQuery = "
    SELECT baslangic_noktasi, varis_noktasi
    FROM seferler
    WHERE sefer_id = '$sefer_id'
";
$seferResult = $conn->query($seferQuery);
if ($seferResult && $seferResult->num_rows > 0) {
    $seferDetaylari = $seferResult->fetch_assoc();
}

$molaQuery = "
    SELECT mola_ad, latitude, longitude
    FROM mola
    WHERE sefer_id = '$sefer_id'
";
$molaResult = $conn->query($molaQuery);
if ($molaResult && $molaResult->num_rows > 0) {
    while ($row = $molaResult->fetch_assoc()) {
        $molalar[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Detayları</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY"></script>
    <script>
        function initMap() {
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 6,
                center: { lat: 39.92077, lng: 32.85411 }, // Örnek merkez (Türkiye)
            });

            // Başlangıç noktası
            const startPoint = {
                lat: <?= $seferDetaylari['baslangic_noktasi_lat'] ?>,
                lng: <?= $seferDetaylari['baslangic_noktasi_lng'] ?>
            };
            new google.maps.Marker({
                position: startPoint,
                map,
                label: "Başlangıç",
            });

            // Varış noktası
            const endPoint = {
                lat: <?= $seferDetaylari['varis_noktasi_lat'] ?>,
                lng: <?= $seferDetaylari['varis_noktasi_lng'] ?>
            };
            new google.maps.Marker({
                position: endPoint,
                map,
                label: "Varış",
            });

            // Molalar
            <?php foreach ($molalar as $mola): ?>
            new google.maps.Marker({
                position: { lat: <?= $mola['latitude'] ?>, lng: <?= $mola['longitude'] ?> },
                map,
                label: "<?= $mola['mola_ad'] ?>",
            });
            <?php endforeach; ?>
        }
    </script>
</head>
<body onload="initMap()">
    <h1>Sefer Detayları</h1>
    <div id="map" style="height: 500px; width: 100%;"></div>
</body>
</html>
