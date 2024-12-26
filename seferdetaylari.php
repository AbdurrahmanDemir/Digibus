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
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kullanıcı oturum kontrolü
if (!isset($_GET['sefer_id'])) {
    die("<p>Sefer ID belirtilmedi.</p>");
}

$sefer_id = $conn->real_escape_string($_GET['sefer_id']);
$seferDetaylari = [];
$molalar = [];

// Sefer bilgileri
$seferQuery = "
    SELECT s.sefer_ad, i1.il_adi AS baslangic_il, i1.latitude AS baslangic_lat, i1.longitude AS baslangic_lng, 
           i2.il_adi AS varis_il, i2.latitude AS varis_lat, i2.longitude AS varis_lng, s.tarih, s.saat
    FROM seferler AS s
    JOIN iller AS i1 ON s.baslangic_noktasi = i1.il_id
    JOIN iller AS i2 ON s.varis_noktasi = i2.il_id
    WHERE s.sefer_id = '$sefer_id'
";
$seferResult = $conn->query($seferQuery);
if ($seferResult && $seferResult->num_rows > 0) {
    $seferDetaylari = $seferResult->fetch_assoc();
}

// Mola bilgileri
$molaQuery = "
    SELECT mola_ad, latitude, longitude, baslangic, bitis
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

function kalanSure($molaSaat) {
    $now = new DateTime();
    $molaZamani = new DateTime($molaSaat);

    if ($molaZamani > $now) {
        $interval = $now->diff($molaZamani);
        return $interval->format('%h saat %i dakika');
    } else {
        return 'Geçti';
    }
}

function molaSuresi($baslangic, $bitis) {
    $baslangicZamani = new DateTime($baslangic);
    $bitisZamani = new DateTime($bitis);
    $interval = $baslangicZamani->diff($bitisZamani);
    return $interval->format('%h saat %i dakika');
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Detayları</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map {
            height: 500px;
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h1 class="text-center">Sefer Detayları</h1>
            </div>
            <div class="card-body">
                <?php if (!empty($seferDetaylari)): ?>
                    <h2 class="text-center mb-4">Sefer Bilgileri</h2>
                    <table class="table table-bordered">
                        <tr>
                            <th>Sefer Adı</th>
                            <td><?= $seferDetaylari['sefer_ad'] ?></td>
                        </tr>
                        <tr>
                            <th>Başlangıç Noktası</th>
                            <td><?= $seferDetaylari['baslangic_il'] ?></td>
                        </tr>
                        <tr>
                            <th>Varış Noktası</th>
                            <td><?= $seferDetaylari['varis_il'] ?></td>
                        </tr>
                        <tr>
                            <th>Tarih</th>
                            <td><?= $seferDetaylari['tarih'] ?></td>
                        </tr>
                        <tr>
                            <th>Saat</th>
                            <td><?= $seferDetaylari['saat'] ?></td>
                        </tr>
                    </table>
                <?php endif; ?>

                <?php if (!empty($molalar)): ?>
                    <h2 class="text-center mt-5 mb-4">Mola Bilgileri</h2>
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Mola Adı</th>
                                <th>Başlangıç</th>
                                <th>Bitiş</th>
                                <th>Kalan Süre</th>
                                <th>Mola Süresi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($molalar as $mola): ?>
                                <tr>
                                    <td><?= $mola['mola_ad'] ?></td>
                                    <td><?= $mola['baslangic'] ?></td>
                                    <td><?= $mola['bitis'] ?></td>
                                    <td><?= kalanSure($mola['baslangic']) ?></td>
                                    <td><?= molaSuresi($mola['baslangic'], $mola['bitis']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning text-center mt-4">
                        Bu sefere ait mola bilgisi bulunamadı.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div id="map" class="mt-5"></div>
    </div>

    <script>
        const map = L.map('map').setView([<?= $seferDetaylari['baslangic_lat'] ?>, <?= $seferDetaylari['baslangic_lng'] ?>], 7);

        // OpenStreetMap Tile Layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        // Başlangıç Noktası Marker
        L.marker([<?= $seferDetaylari['baslangic_lat'] ?>, <?= $seferDetaylari['baslangic_lng'] ?>])
            .addTo(map)
            .bindPopup("<b>Başlangıç:</b> <?= $seferDetaylari['baslangic_il'] ?>");

        // Varış Noktası Marker
        L.marker([<?= $seferDetaylari['varis_lat'] ?>, <?= $seferDetaylari['varis_lng'] ?>])
            .addTo(map)
            .bindPopup("<b>Varış:</b> <?= $seferDetaylari['varis_il'] ?>");

        // Molalar Marker
        <?php foreach ($molalar as $mola): ?>
        L.marker([<?= $mola['latitude'] ?>, <?= $mola['longitude'] ?>])
            .addTo(map)
            .bindPopup("<b>Mola:</b> <?= $mola['mola_ad'] ?>");
        <?php endforeach; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

