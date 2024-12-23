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

// Oturum başlatma
session_start();

// Kullanıcı oturum kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    die("<p>Lütfen bilet almak için giriş yapın.</p>");
}

$kullanici_id = $_SESSION['kullanici_id'];

// Kullanıcının telefon numarasını al
$userQuery = "SELECT kullanici_tel FROM kullanici WHERE kullanici_id = '$kullanici_id'";
$userResult = $conn->query($userQuery);
if ($userResult->num_rows > 0) {
    $user = $userResult->fetch_assoc();
    $kullanici_tel = $user['kullanici_tel'];
} else {
    die("<p>Kullanıcı bilgileri bulunamadı.</p>");
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

// Koltuk ve Bilet İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_ticket'])) {
    $sefer_id = isset($_POST['sefer_id']) ? $conn->real_escape_string($_POST['sefer_id']) : null;
    $koltuk_no = isset($_POST['koltuk_no']) ? $conn->real_escape_string($_POST['koltuk_no']) : null;

    if (empty($sefer_id) || empty($koltuk_no)) {
        echo "<p>Lütfen tüm alanları doldurun.</p>";
    } else {
        // Koltuğun daha önce alınmadığını kontrol et
        $checkQuery = "SELECT * FROM bilet WHERE sefer_id = '$sefer_id' AND koltuk_no = '$koltuk_no'";
        $checkResult = $conn->query($checkQuery);

        if ($checkResult->num_rows > 0) {
            echo "<p>Bu koltuk zaten rezerve edilmiş.</p>";
        } else {
            // Daha önce aynı kullanici_tel ve sefer_id eklenmiş mi kontrol et
            $checkYolcuQuery = "SELECT * FROM yolcu WHERE sefer_id = '$sefer_id' AND yolcu_id = '$kullanici_tel'";
            $checkYolcuResult = $conn->query($checkYolcuQuery);

            if ($checkYolcuResult->num_rows == 0) {
                // Yolcu tablosuna ekle
                $insertYolcuQuery = "INSERT INTO yolcu (sefer_id, yolcu_id) VALUES ('$sefer_id', '$kullanici_tel')";
                if ($conn->query($insertYolcuQuery) === TRUE) {
                    // Bilet tablosuna ekle
                    $insertBiletQuery = "INSERT INTO bilet (yolcu_id, sefer_id, koltuk_no, durum, fiyat) 
                                         VALUES ('$kullanici_tel', '$sefer_id', '$koltuk_no', 1, 50)";
                    if ($conn->query($insertBiletQuery) === TRUE) {
                        echo "<p>Bilet başarıyla satın alındı!</p>";
                    } else {
                        echo "<p>Bilet kaydedilemedi: " . $conn->error . "</p>";
                    }
                } else {
                    echo "<p>Yolcu kaydedilemedi: " . $conn->error . "</p>";
                }
            } else {
                echo "<p>Bu sefer için zaten kaydınız var.</p>";
            }
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
    <title>Bilet Al</title>
    <style>
        .seat-map { display: grid; grid-template-columns: repeat(5, 50px); gap: 10px; margin: 20px 0; }
        .seat { width: 50px; height: 50px; text-align: center; line-height: 50px; border: 1px solid #ccc; cursor: pointer; }
        .available { background-color: #4CAF50; color: white; }
        .selected { background-color: #2196F3; color: white; }
        .occupied { background-color: #f44336; color: white; cursor: not-allowed; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seats = document.querySelectorAll('.seat');
            seats.forEach(seat => {
                seat.addEventListener('click', function() {
                    if (this.classList.contains('available')) {
                        document.querySelectorAll('.selected').forEach(selected => selected.classList.remove('selected'));
                        this.classList.add('selected');
                        document.getElementById('koltuk_no').value = this.dataset.seatNumber;
                    }
                });
            });
        });
    </script>
</head>
<body>
    <h1>Bilet Al</h1>
    <form method="POST" action="">
        <label for="sefer_id">Sefer:</label>
        <select id="sefer_id" name="sefer_id" required>
            <option value="">Sefer Seçin</option>
            <?php foreach ($seferler as $sefer): ?>
                <option value="<?= $sefer['sefer_id'] ?>"><?= $sefer['sefer_ad'] ?></option>
            <?php endforeach; ?>
        </select><br><br>
        
        <h2>Koltuk Seçimi</h2>
        <div class="seat-map">
            <?php for ($i = 1; $i <= 20; $i++): ?>
                <div class="seat available" data-seat-number="<?= $i ?>"><?= $i ?></div>
            <?php endfor; ?>
        </div>
        <input type="hidden" id="koltuk_no" name="koltuk_no">
        <button type="submit" name="buy_ticket">Bilet Al</button>
    </form>
</body>
</html>
