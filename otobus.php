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


if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h1 class="text-center">Otobüs Oluştur</h1>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="kapasite" class="form-label">Kapasite:</label>
                        <input type="number" id="kapasite" name="kapasite" class="form-control" placeholder="Kapasite Girin" required>
                    </div>
                    <div class="mb-3">
                        <label for="otobüs_plaka" class="form-label">Otobüs Plaka:</label>
                        <input type="text" id="otobüs_plaka" name="otobüs_plaka" class="form-control" placeholder="Plaka Girin" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="add_bus" class="btn btn-success">Otobüs Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
