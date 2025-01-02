<?php
// Veritabanı bağlantı bilgileri
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'digibus');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Bağlantıyı kontrol et
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['bilet_id'])) {
    $bilet_id = $conn->real_escape_string($_GET['bilet_id']);

    // Biletin yolcu_id değerini al
    $getYolcuQuery = "SELECT yolcu_id FROM bilet WHERE bilet_id = '$bilet_id'";
    $yolcuResult = $conn->query($getYolcuQuery);

    if ($yolcuResult->num_rows > 0) {
        $row = $yolcuResult->fetch_assoc();
        $yolcu_id = $row['yolcu_id'];

        // Yolcu kaydını sil
        $deleteYolcuQuery = "DELETE FROM yolcu WHERE yolcu_id = '$yolcu_id'";
        if ($conn->query($deleteYolcuQuery) === TRUE) {
            // Bilet kaydını sil
            $deleteBiletQuery = "DELETE FROM bilet WHERE bilet_id = '$bilet_id'";
            if ($conn->query($deleteBiletQuery) === TRUE) {
                echo "<div class='alert alert-success'>Bilet ve yolcu kayıtları başarıyla silindi. Yönlendiriliyorsunuz...</div>";
                // 2 saniye bekle ve yönlendir
                header("Refresh: 2; url=http://localhost/Digibus/biletal.php");
            } else {
                echo "<div class='alert alert-danger'>Bilet silinemedi: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Yolcu kaydı silinemedi: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Geçerli bir bilet bulunamadı.</div>";
    }
} else {
    echo "<div class='alert alert-warning'>Bilet ID belirtilmedi.</div>";
}

$conn->close();
?>
