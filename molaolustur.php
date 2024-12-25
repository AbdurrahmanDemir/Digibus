<?php
// Veritabanı bağlantı bilgileri
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'digibus');

// Veritabanına bağlan
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

session_start();

// Sefer listesini getir
$seferler = [];
$seferlerQuery = "SELECT sefer_id, sefer_ad FROM seferler";
$result = $conn->query($seferlerQuery);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $seferler[] = $row;
    }
}

// Mola Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_mola'])) {
    $sefer_id = isset($_POST['sefer_id']) ? $conn->real_escape_string($_POST['sefer_id']) : null;
    $mola_ad = isset($_POST['mola_ad']) ? $conn->real_escape_string($_POST['mola_ad']) : null;
    $baslangic = isset($_POST['baslangic']) ? $conn->real_escape_string($_POST['baslangic']) : null;
    $bitis = isset($_POST['bitis']) ? $conn->real_escape_string($_POST['bitis']) : null;
    $latitude = isset($_POST['latitude']) ? $conn->real_escape_string($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) ? $conn->real_escape_string($_POST['longitude']) : null;

    if (empty($sefer_id) || empty($mola_ad) || empty($baslangic) || empty($bitis) || empty($latitude) || empty($longitude)) {
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
</head>
<body>
    <h1>Mola Oluştur</h1>
    <form method="POST" action="">
        <label for="sefer_id">Sefer:</label>
        <select id="sefer_id" name="sefer_id" required>
            <option value="">Sefer Seçin</option>
            <?php foreach ($seferler as $sefer): ?>
                <option value="<?= $sefer['sefer_id'] ?>"><?= $sefer['sefer_ad'] ?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="mola_ad">Mola Adı:</label>
        <input type="text" id="mola_ad" name="mola_ad" required><br>
        <label for="baslangic">Başlangıç Zamanı:</label>
        <input type="time" id="baslangic" name="baslangic" required><br>
        <label for="bitis">Bitiş Zamanı:</label>
        <input type="time" id="bitis" name="bitis" required><br>
        <label for="latitude">Enlem:</label>
        <input type="text" id="latitude" name="latitude" placeholder="Örn: 39.92077" required><br>
        <label for="longitude">Boylam:</label>
        <input type="text" id="longitude" name="longitude" placeholder="Örn: 32.85411" required><br>
        <button type="submit" name="create_mola">Mola Kaydet</button>
    </form>
</body>
</html>
