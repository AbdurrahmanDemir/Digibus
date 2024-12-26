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

// Sefer listesini getir
$seferler = [];
$seferlerQuery = "SELECT sefer_id, sefer_ad FROM seferler";
$result = $conn->query($seferlerQuery);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $seferler[] = $row;
    }
}

// İller listesini getir
$iller = [];
$illerQuery = "SELECT il_id, il_adi FROM iller";
$resultIller = $conn->query($illerQuery);
if ($resultIller->num_rows > 0) {
    while ($row = $resultIller->fetch_assoc()) {
        $iller[] = $row;
    }
}

// Mola Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_mola'])) {
    $sefer_id = $conn->real_escape_string($_POST['sefer_id']);
    $mola_ad = $conn->real_escape_string($_POST['mola_ad']);
    $il_id = $conn->real_escape_string($_POST['il_id']);
    $baslangic = $conn->real_escape_string($_POST['baslangic']);
    $bitis = $conn->real_escape_string($_POST['bitis']);

    // İl bilgilerine göre enlem ve boylamı al
    $ilQuery = "SELECT latitude, longitude FROM iller WHERE il_id = '$il_id'";
    $ilResult = $conn->query($ilQuery);
    $ilData = $ilResult->fetch_assoc();
    $latitude = $ilData['latitude'];
    $longitude = $ilData['longitude'];

    if (empty($sefer_id) || empty($mola_ad) || empty($baslangic) || empty($bitis)) {
        echo "<p style='color: red;'>Tüm alanları doldurmanız gerekiyor.</p>";
    } else {
        $sql = "INSERT INTO mola (sefer_id, mola_ad, baslangic, bitis, latitude, longitude) 
                VALUES ('$sefer_id', '$mola_ad', '$baslangic', '$bitis', '$latitude', '$longitude')";

        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>Mola başarıyla kaydedildi!</p>";
        } else {
            echo "<p style='color: red;'>Mola kaydedilemedi: " . $conn->error . "</p>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mola Oluştur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        const iller = <?= json_encode($iller) ?>;
        function ilSecildi(select) {
            const selectedIlId = select.value;
            const il = iller.find(il => il.il_id == selectedIlId);
            if (il) {
                document.getElementById('latitude').value = il.latitude;
                document.getElementById('longitude').value = il.longitude;
            }
        }
    </script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h1 class="text-center">Mola Oluştur</h1>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="sefer_id" class="form-label">Sefer:</label>
                        <select id="sefer_id" name="sefer_id" class="form-select" required>
                            <option value="">Sefer Seçin</option>
                            <?php foreach ($seferler as $sefer): ?>
                                <option value="<?= $sefer['sefer_id'] ?>"><?= $sefer['sefer_ad'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="mola_ad" class="form-label">Mola Adı:</label>
                        <input type="text" id="mola_ad" name="mola_ad" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="il_id" class="form-label">İl:</label>
                        <select id="il_id" name="il_id" class="form-select" onchange="ilSecildi(this)" required>
                            <option value="">İl Seçin</option>
                            <?php foreach ($iller as $il): ?>
                                <option value="<?= $il['il_id'] ?>"><?= $il['il_adi'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="latitude" class="form-label">Enlem:</label>
                        <input type="text" id="latitude" name="latitude" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="longitude" class="form-label">Boylam:</label>
                        <input type="text" id="longitude" name="longitude" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="baslangic" class="form-label">Başlangıç Zamanı:</label>
                        <input type="time" id="baslangic" name="baslangic" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="bitis" class="form-label">Bitiş Zamanı:</label>
                        <input type="time" id="bitis" name="bitis" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="create_mola" class="btn btn-success">Mola Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
