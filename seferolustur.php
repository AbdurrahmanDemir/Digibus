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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h1>Sefer Oluştur</h1>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="sefer_id" class="form-label">Sefer ID:</label>
                        <input type="text" id="sefer_id" name="sefer_id" class="form-control" placeholder="Örn: SFR001" required>
                    </div>

                    <div class="mb-3">
                        <label for="otobüs_id" class="form-label">Otobüs:</label>
                        <select id="otobüs_id" name="otobüs_id" class="form-select" required>
                            <option value="">Otobüs Seçin</option>
                            <?php foreach ($otobüsler as $otobüs): ?>
                                <option value="<?= $otobüs['otobüs_id'] ?>"><?= $otobüs['otobüs_plaka'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="baslangic_noktasi" class="form-label">Başlangıç Noktası:</label>
                        <select id="baslangic_noktasi" name="baslangic_noktasi" class="form-select" required>
                            <option value="">Başlangıç Noktası Seçin</option>
                            <?php foreach ($iller as $il): ?>
                                <option value="<?= $il['il_id'] ?>"><?= $il['il_adi'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="varis_noktasi" class="form-label">Varış Noktası:</label>
                        <select id="varis_noktasi" name="varis_noktasi" class="form-select" required>
                            <option value="">Varış Noktası Seçin</option>
                            <?php foreach ($iller as $il): ?>
                                <option value="<?= $il['il_id'] ?>"><?= $il['il_adi'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tarih" class="form-label">Tarih:</label>
                        <input type="date" id="tarih" name="tarih" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="saat" class="form-label">Saat:</label>
                        <input type="time" id="saat" name="saat" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="sefer_ad" class="form-label">Sefer Adı:</label>
                        <input type="text" id="sefer_ad" name="sefer_ad" class="form-control" placeholder="Örn: Sabah Ankara-İstanbul" required>
                    </div>

                    <button type="submit" name="create_sefer" class="btn btn-primary w-100">Sefer Kaydet</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
