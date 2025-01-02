
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
        echo "<div class='alert alert-danger'>Lütfen tüm alanları doldurun.</div>";
    } else {
        // Koltuğun daha önce alınmadığını kontrol et
        $checkQuery = "SELECT * FROM bilet WHERE sefer_id = '$sefer_id' AND koltuk_no = '$koltuk_no'";
        $checkResult = $conn->query($checkQuery);

        if ($checkResult->num_rows > 0) {
            echo "<div class='alert alert-warning'>Bu koltuk zaten rezerve edilmiş.</div>";
        } else {
            // Yolcunun aynı sefer için zaten kaydı olup olmadığını kontrol et
            $checkYolcuQuery = "SELECT * FROM yolcu WHERE sefer_id = '$sefer_id' AND yolcu_id = '$kullanici_tel'";
            $checkYolcuResult = $conn->query($checkYolcuQuery);

            if ($checkYolcuResult->num_rows == 0) {
                // Yolcu tablosuna ekle
                $insertYolcuQuery = "INSERT INTO yolcu (yolcu_id, kullanici_id, sefer_id, durum) 
                                     VALUES ('$kullanici_tel', '$kullanici_id', '$sefer_id', 'aktif')";
                if ($conn->query($insertYolcuQuery) === TRUE) {
                    // Bilet tablosuna ekle
                    $insertBiletQuery = "INSERT INTO bilet (bilet_id, koltuk_no, yolcu_id, sefer_id, bilet_fiyat) 
                                         VALUES (UUID(), '$koltuk_no', '$kullanici_tel', '$sefer_id', 50)";
                    if ($conn->query($insertBiletQuery) === TRUE) {
                        echo "<div class='alert alert-success'>Bilet başarıyla satın alındı!</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Bilet kaydedilemedi: " . $conn->error . "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Yolcu kaydedilemedi: " . $conn->error . "</div>";
                }
            } else {
                echo "<div style='display: flex; justify-content: center; align-items: center; height: 100vh;'>
        <div class='alert alert-warning' style='text-align: center;'>
            Bu sefer için zaten kaydınız var.
        </div>
      </div>";

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .seat-map {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
        }
        .row {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .seat {
            width: 50px;
            height: 50px;
            text-align: center;
            line-height: 50px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        .seat.occupied {
            background-color: #f44336;
            cursor: not-allowed;
        }
        .seat.selected {
            background-color: #2196F3;
            color: white;
        }
        .seat.unavailable {
            background-color: #d3d3d3;
            color: black;
            cursor: not-allowed;
        }
        .seat.driver, .seat.muavin {
            background-color: #d3d3d3;
            color: black;
            cursor: not-allowed;
            font-size: 14px;
            text-transform: uppercase;
        }
        .seat-spacer {
            width: 50px;
            height: 50px;
            background-color: transparent;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Bilet Al</h1>
    <form method="POST" action="" class="mt-4">
        <div class="mb-3">
            <label for="sefer_id" class="form-label">Sefer:</label>
            <select id="sefer_id" name="sefer_id" class="form-select" required>
                <option value="">Sefer Seçin</option>
                <?php foreach ($seferler as $sefer): ?>
                    <option value="<?= $sefer['sefer_id'] ?>"><?= $sefer['sefer_ad'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <h2 class="mt-4">Koltuk Seçimi</h2>
        <div id="seat-map" class="seat-map">
            <p>Lütfen önce bir sefer seçiniz.</p>
        </div>

        <input type="hidden" id="koltuk_no" name="koltuk_no">
        <button type="submit" name="buy_ticket" class="btn btn-primary mt-3" disabled id="buy-ticket-btn">Bilet Al</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const seferSelect = document.getElementById('sefer_id');
        const seatMap = document.getElementById('seat-map');
        const buyTicketBtn = document.getElementById('buy-ticket-btn');
        
        seferSelect.addEventListener('change', function () {
            const seferId = this.value;

            if (!seferId) {
                seatMap.innerHTML = '<p>Lütfen bir sefer seçiniz.</p>';
                buyTicketBtn.disabled = true;
                return;
            }

            // AJAX ile koltuk durumlarını al
            fetch(`get_seats.php?sefer_id=${seferId}`)
                .then(response => response.json())
                .then(data => {
                    seatMap.innerHTML = ''; // Eski içeriği temizle
                    buyTicketBtn.disabled = false;

                    // Koltukları oluştur
                    for (let row = 1; row <= 10; row++) {
                        const rowDiv = document.createElement('div');
                        rowDiv.classList.add('row');

                        // İlk satır için özel düzen
                        if (row === 1) {
                            const muavinDiv = document.createElement('div');
                            muavinDiv.classList.add('seat', 'muavin');
                            muavinDiv.textContent = 'Muavin';
                            rowDiv.appendChild(muavinDiv);

                            const surucuDiv = document.createElement('div');
                            surucuDiv.classList.add('seat', 'driver');
                            surucuDiv.textContent = 'Şoför';
                            rowDiv.appendChild(surucuDiv);

                            const driverSpacer = document.createElement('div');
                            driverSpacer.classList.add('seat-spacer');
                            rowDiv.appendChild(driverSpacer);
                        } else {
                            const leftSeat = createSeatDiv(data, (row - 2) * 3 + 3);
                            const rightSeat1 = createSeatDiv(data, (row - 2) * 3 + 4);
                            const rightSeat2 = createSeatDiv(data, (row - 2) * 3 + 5);

                            rowDiv.appendChild(leftSeat);
                            rowDiv.appendChild(createSpacerDiv());
                            rowDiv.appendChild(rightSeat1);
                            rowDiv.appendChild(rightSeat2);
                        }
                        seatMap.appendChild(rowDiv);
                    }

                    addSeatClickEvents();
                })
                .catch(error => {
                    console.error('Hata:', error);
                    seatMap.innerHTML = '<p>Koltuk bilgileri yüklenemedi.</p>';
                });
        });

        function createSeatDiv(data, seatNumber) {
            const seatDiv = document.createElement('div');
            seatDiv.classList.add('seat');

            if (data.occupiedSeats.includes(seatNumber.toString())) {
                seatDiv.classList.add('occupied');
                seatDiv.textContent = seatNumber;
            } else {
                seatDiv.classList.add('available');
                seatDiv.textContent = seatNumber;
                seatDiv.dataset.seatNumber = seatNumber;
            }

            return seatDiv;
        }

        function createSpacerDiv() {
            const spacerDiv = document.createElement('div');
            spacerDiv.classList.add('seat-spacer');
            return spacerDiv;
        }

        function addSeatClickEvents() {
            const seats = document.querySelectorAll('.seat.available');
            seats.forEach(seat => {
                seat.addEventListener('click', function () {
                    document.querySelectorAll('.seat.selected').forEach(selected => selected.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('koltuk_no').value = this.dataset.seatNumber;
                });
            });
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

