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

session_start();


// Otobüs listesini getir
$otobüsler = [];
$otobüsQuery = "SELECT otobüs_id, otobüs_plaka FROM otobüs";
$result = $conn->query($otobüsQuery);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $otobüsler[] = $row;
    }
} else {
    echo "<p>Hiç kayıtlı otobüs bulunamadı.</p>";
}

// Sefer Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_sefer'])) {
    $sefer_id = isset($_POST['sefer_id']) ? $conn->real_escape_string($_POST['sefer_id']) : null;
    $otobüs_id = isset($_POST['otobüs_id']) ? $conn->real_escape_string($_POST['otobüs_id']) : null;
    $baslangic_noktasi = isset($_POST['baslangic_noktasi']) ? $conn->real_escape_string($_POST['baslangic_noktasi']) : null;
    $varis_noktasi = isset($_POST['varis_noktasi']) ? $conn->real_escape_string($_POST['varis_noktasi']) : null;
    $tarih = isset($_POST['tarih']) ? $conn->real_escape_string($_POST['tarih']) : null;
    $saat = isset($_POST['saat']) ? $conn->real_escape_string($_POST['saat']) : null;
    $sefer_ad = isset($_POST['sefer_ad']) ? $conn->real_escape_string($_POST['sefer_ad']) : null;

    if (empty($sefer_id) || empty($otobüs_id) || empty($baslangic_noktasi) || empty($varis_noktasi) || empty($tarih) || empty($saat) || empty($sefer_ad)) {
        echo "<p>Tüm alanları doldurmanız gerekiyor.</p>";
    } else {
        // Yeni sefer kaydı
        $sql = "INSERT INTO seferler (sefer_id, otobüs_id, baslangic_noktasi, varis_noktasi, tarih, saat, sefer_ad) 
                VALUES ('$sefer_id', '$otobüs_id', '$baslangic_noktasi', '$varis_noktasi', '$tarih', '$saat', '$sefer_ad')";

        if ($conn->query($sql) === TRUE) {
            echo "<p>Sefer başarıyla kaydedildi!</p>";
        } else {
            echo "<p>Sefer kaydedilemedi: " . $conn->error . "</p>";
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
    <title>Sefer Oluştur</title>
</head>
<body>
    <h1>Sefer Oluştur</h1>
    <form method="POST" action="">
        <label for="sefer_id">Sefer ID:</label>
        <input type="text" id="sefer_id" name="sefer_id" required><br>
        <label for="otobüs_id">Otobüs:</label>
        <select id="otobüs_id" name="otobüs_id" required>
            <option value="">Otobüs Seçin</option>
            <?php foreach ($otobüsler as $otobüs): ?>
                <option value="<?= $otobüs['otobüs_id'] ?>"><?= $otobüs['otobüs_plaka'] ?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="baslangic_noktasi">Başlangıç Noktası:</label>
        <input type="text" id="baslangic_noktasi" name="baslangic_noktasi" required><br>
        <label for="varis_noktasi">Varış Noktası:</label>
        <input type="text" id="varis_noktasi" name="varis_noktasi" required><br>
        <label for="tarih">Tarih:</label>
        <input type="date" id="tarih" name="tarih" required><br>
        <label for="saat">Saat:</label>
        <input type="time" id="saat" name="saat" required><br>
        <label for="sefer_ad">Sefer Adı:</label>
        <input type="text" id="sefer_ad" name="sefer_ad" required><br>
        <button type="submit" name="create_sefer">Sefer Kaydet</button>
    </form>
</body>
</html>
