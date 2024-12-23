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
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    die("Bu sayfaya erişim yetkiniz yok. Lütfen giriş yapın ve admin yetkisine sahip olduğunuzdan emin olun.");
}

// Otobüs Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bus'])) {
    $kapasite = isset($_POST['kapasite']) ? intval($_POST['kapasite']) : null;
    $otobüs_plaka = isset($_POST['otobüs_plaka']) ? $conn->real_escape_string($_POST['otobüs_plaka']) : null;

    if (empty($kapasite) || $kapasite <= 0) {
        echo "<p>Geçersiz kapasite değeri. Lütfen pozitif bir sayı girin.</p>";
    } elseif (empty($otobüs_plaka)) {
        echo "<p>Plaka boş olamaz. Lütfen geçerli bir plaka girin.</p>";
    } else {
        // Yeni otobüs kaydı, plaka hem otobus_id hem de otobus_plaka olarak kaydedilecek
        $sql = "INSERT INTO otobüs (otobüs_id, kapasite, otobüs_plaka) VALUES ('$otobüs_plaka', $kapasite, '$otobüs_plaka')";

        if ($conn->query($sql) === TRUE) {
            echo "<p>Otobüs başarıyla kaydedildi!</p>";
        } else {
            echo "<p>Otobüs kaydedilemedi: " . $conn->error . "</p>";
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
    <title>Otobüs Oluştur</title>
</head>
<body>
    <h1>Otobüs Oluştur</h1>
    <form method="POST" action="">
        <label for="kapasite">Kapasite:</label>
        <input type="number" id="kapasite" name="kapasite" required><br>
        <label for="otobüs_plaka">Otobüs Plaka:</label>
        <input type="text" id="otobüs_plaka" name="otobüs_plaka" required><br>
        <button type="submit" name="add_bus">Otobüs Kaydet</button>
    </form>
</body>
</html>
