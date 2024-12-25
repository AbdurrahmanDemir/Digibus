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

// İller listesini getir
$iller = [];
$illerQuery = "SELECT il_id, il_adi FROM iller ORDER BY il_adi";
$illerResult = $conn->query($illerQuery);
if ($illerResult->num_rows > 0) {
    while ($row = $illerResult->fetch_assoc()) {
        $iller[] = $row;
    }
}

// Sefer Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_sefer'])) {
    $sefer_id = $conn->real_escape_string($_POST['sefer_id']);
    $otobüs_id = $conn->real_escape_string($_POST['otobüs_id']);
    $baslangic_noktasi = $conn->real_escape_string($_POST['baslangic_noktasi']);
    $varis_noktasi = $conn->real_escape_string($_POST['varis_noktasi']);
    $tarih = $conn->real_escape_string($_POST['tarih']);
    $saat = $conn->real_escape_string($_POST['saat']);
    $sefer_ad = $conn->real_escape_string($_POST['sefer_ad']);

    if (empty($sefer_id) || empty($otobüs_id) || empty($baslangic_noktasi) || empty($varis_noktasi) || empty($tarih) || empty($saat) || empty($sefer_ad)) {
        echo "<p style='color: red;'>Tüm alanları doldurmanız gerekiyor.</p>";
    } else {
        // Yeni sefer kaydı
        $sql = "INSERT INTO seferler (sefer_id, otobüs_id, baslangic_noktasi, varis_noktasi, tarih, saat, sefer_ad) 
                VALUES ('$sefer_id', '$otobüs_id', '$baslangic_noktasi', '$varis_noktasi', '$tarih', '$saat', '$sefer_ad')";

        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>Sefer başarıyla kaydedildi!</p>";
        } else {
            echo "<p style='color: red;'>Sefer kaydedilemedi: " . $conn->error . "</p>";
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f7f7f7;
        }
        h1 {
            color: #333;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, button {
            display: block;
            margin-bottom: 15px;
            padding: 10px;
            width: 100%;
            max-width: 400px;
            font-size: 16px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        p {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>Sefer Oluştur</h1>
    <form method="POST" action="">
        <label for="sefer_id">Sefer ID:</label>
        <input type="text" id="sefer_id" name="sefer_id" placeholder="Örn: SFR001" required>

        <label for="otobüs_id">Otobüs:</label>
        <select id="otobüs_id" name="otobüs_id" required>
            <option value="">Otobüs Seçin</option>
            <?php foreach ($otobüsler as $otobüs): ?>
                <option value="<?= $otobüs['otobüs_id'] ?>"><?= $otobüs['otobüs_plaka'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="baslangic_noktasi">Başlangıç Noktası:</label>
        <select id="baslangic_noktasi" name="baslangic_noktasi" required>
            <option value="">Başlangıç Noktası Seçin</option>
            <?php foreach ($iller as $il): ?>
                <option value="<?= $il['il_id'] ?>"><?= $il['il_adi'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="varis_noktasi">Varış Noktası:</label>
        <select id="varis_noktasi" name="varis_noktasi" required>
            <option value="">Varış Noktası Seçin</option>
            <?php foreach ($iller as $il): ?>
                <option value="<?= $il['il_id'] ?>"><?= $il['il_adi'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="tarih">Tarih:</label>
        <input type="date" id="tarih" name="tarih" required>

        <label for="saat">Saat:</label>
        <input type="time" id="saat" name="saat" required>

        <label for="sefer_ad">Sefer Adı:</label>
        <input type="text" id="sefer_ad" name="sefer_ad" placeholder="Örn: Sabah Ankara-İstanbul" required>

        <button type="submit" name="create_sefer">Sefer Kaydet</button>
    </form>
</body>
</html>
