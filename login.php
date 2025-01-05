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

// Oturum başlatma

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Çıkış yapma işlemi
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Kullanıcı Kaydolma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $kullanici_id = $conn->real_escape_string($_POST['kullanici_id']); // TC Kimlik No
    $kullanici_ad = $conn->real_escape_string($_POST['kullanici_ad']);
    $kullanici_soyad = $conn->real_escape_string($_POST['kullanici_soyad']);
    $kullanici_tel = $conn->real_escape_string($_POST['kullanici_tel']);
    $kullanici_sifre = password_hash($_POST['kullanici_sifre'], PASSWORD_BCRYPT);
    $rol_id = intval($_POST['rol_id']);

    // TC Kimlik No doğrulama
    if (strlen($kullanici_id) != 11 || !ctype_digit($kullanici_id)) {
        echo "<p>Geçersiz TC Kimlik No. Lütfen 11 haneli bir sayı girin.</p>";
    } else {
        // Kullanıcı telefon numarası veya TC Kimlik No kontrolü
        $checkQuery = "SELECT kullanici_id FROM kullanici WHERE kullanici_id = '$kullanici_id' OR kullanici_tel = '$kullanici_tel'";
        $result = $conn->query($checkQuery);

        $rolCheckQuery = "SELECT rol_id FROM rol WHERE rol_id = $rol_id";
$rolCheckResult = $conn->query($rolCheckQuery);

if ($rolCheckResult->num_rows === 0) {
    echo "<p>Geçersiz rol seçimi. Lütfen geçerli bir rol seçin.</p>";
} else {
    // Kullanıcı ekleme işlemi
    $sql = "INSERT INTO kullanici (kullanici_id, kullanici_ad, kullanici_soyad, kullanici_tel, kullanici_sifre, rol_id) 
            VALUES ('$kullanici_id', '$kullanici_ad', '$kullanici_soyad', '$kullanici_tel', '$kullanici_sifre', $rol_id)";
    if ($conn->query($sql) === TRUE) {
        echo "<p>Kayıt başarılı! Şimdi giriş yapabilirsiniz.</p>";
    } else {
        echo "<p>Kayıt başarısız: " . $conn->error . "</p>";
    }
}

    }
}

// Kullanıcı Giriş
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $kullanici_tel = $conn->real_escape_string($_POST['kullanici_tel']);
    $kullanici_sifre = $_POST['kullanici_sifre'];

    // Kullanıcı bilgilerini sorgula
    $sql = "SELECT * FROM kullanici WHERE kullanici_tel = '$kullanici_tel'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Şifre doğrulama
        if (password_verify($kullanici_sifre, $user['kullanici_sifre'])) {
            // Kullanıcı bilgilerini oturuma kaydet
            $_SESSION['kullanici_id'] = $user['kullanici_id'];
            $_SESSION['kullanici_ad'] = $user['kullanici_ad'];
            $_SESSION['kullanici_soyad'] = $user['kullanici_soyad'];
            $_SESSION['rol_id'] = $user['rol_id']; // **Rol bilgisi eklendi**

            // Başarı durumunda yönlendirme
            header("Location: login.php");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Şifre yanlış!</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Telefon numarası bulunamadı.</div>";
    }
}



// Kullanıcı bilet bilgilerini al
$biletBilgileri = [];
if (isset($_SESSION['kullanici_id'])) {
    $kullanici_id = $_SESSION['kullanici_id'];
    $biletQuery = "
    SELECT b.koltuk_no, b.bilet_fiyat, y.durum, s.sefer_ad, s.tarih, s.saat, b.sefer_id, b.bilet_id
    FROM bilet AS b
    JOIN yolcu AS y ON b.yolcu_id = y.yolcu_id
    JOIN seferler AS s ON b.sefer_id = s.sefer_id
    WHERE y.kullanici_id = '$kullanici_id'
";

    $biletResult = $conn->query($biletQuery);

    if ($biletResult && $biletResult->num_rows > 0) {
        while ($row = $biletResult->fetch_assoc()) {
            $biletBilgileri[] = $row;
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
    <title>Kayıt ve Giriş Sayfası</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, h1, p { margin: 0; padding: 0; font-family: 'Open Sans', sans-serif; }
        .container { margin-top: 30px; }
        .ticket { max-width: 300px; margin: 20px auto; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); border-radius: 10px; }
        .ticket .top { background: #ffcc05; padding: 20px; text-align: center; border-top-left-radius: 10px; border-top-right-radius: 10px; }
        .ticket .top h1 { margin: 0; font-size: 24px; }
        .ticket .top .big { font-size: 16px; margin-top: 10px; }
        .ticket .top .big .from { color: #333; font-weight: bold; }
        .ticket .top .big .to { color: #555; }
        .ticket .bottom { background: #fff; padding: 20px; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; }
        .ticket .bottom .info { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .ticket .bottom .bar--code { height: 50px; background: repeating-linear-gradient(90deg, #000, #000 5px, #fff 5px, #fff 10px); }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['kullanici_ad'])): ?>
            <h1 class="text-center">Hoşgeldiniz, <?= $_SESSION['kullanici_ad'] ?> <?= $_SESSION['kullanici_soyad'] ?>!</h1>
            <form method="POST" action="" class="text-center mt-3">
                <button type="submit" name="logout" class="btn btn-danger">Çıkış Yap</button>
            </form>

            <div class="mt-4">
                <h2>Bilet Bilgileriniz</h2>
                <?php if (!empty($biletBilgileri)): ?>
                    <?php foreach ($biletBilgileri as $bilet): ?>
                        <div class="ticket">
                            <div class="top">
                                <h1><?= $bilet['sefer_ad'] ?></h1>
                                <div class="big">
                                    <p class="from"><?= $bilet['tarih'] ?> - <?= $bilet['saat'] ?></p>
                                    <p class="to"><i class="fas fa-arrow-right"></i> <?= $bilet['durum'] ?></p>
                                </div>
                            </div>
                            <div class="bottom">
                                <div class="info">
                                    <span><strong>Koltuk:</strong> <?= $bilet['koltuk_no'] ?></span>
                                    <span><strong>Fiyat:</strong> <?= $bilet['bilet_fiyat'] ?> TL</span>
                                </div>
                                <div class="info">
                                    <span><strong>Durum:</strong> <?= $bilet['durum'] ?></span>
                                    <span><strong>Sefer:</strong> <?= $bilet['sefer_ad'] ?></span>
                                </div>
                                <form method="GET" action="seferdetaylari.php" style="display: inline-block;">
                                    <input type="hidden" name="sefer_id" value="<?= $bilet['sefer_id'] ?>">
                                    <button type="submit" class="btn btn-info btn-sm">Detay Gör</button>
                                </form>
                                <form method="POST" action="bilet_sil.php" style="display: inline-block;">
                                    <input type="hidden" name="bilet_id" value="<?= $bilet['bilet_id'] ?>">
                                    <a href="bilet_sil.php?bilet_id=<?= $bilet['bilet_id'] ?>" class="btn btn-danger btn-sm">Biletini Sil</a>

                                </form>
                                <div class="bar--code"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">Henüz bir bilet satın almadınız.</div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-6">
                    <h1>Kayıt Ol</h1>
                    <form method="POST" action="" class="p-3 border rounded">
                        <div class="mb-3">
                            <label for="kullanici_id" class="form-label">TC Kimlik No:</label>
                            <input type="text" id="kullanici_id" name="kullanici_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="kullanici_ad" class="form-label">Ad:</label>
                            <input type="text" id="kullanici_ad" name="kullanici_ad" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="kullanici_soyad" class="form-label">Soyad:</label>
                            <input type="text" id="kullanici_soyad" name="kullanici_soyad" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="kullanici_tel" class="form-label">Telefon:</label>
                            <input type="text" id="kullanici_tel" name="kullanici_tel" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="kullanici_sifre" class="form-label">Şifre:</label>
                            <input type="password" id="kullanici_sifre" name="kullanici_sifre" class="form-control" required>
                        </div>
                        <label for="rol_id">Rol:</label>
            <select id="rol_id" name="rol_id" required>
                <option value="1">Admin</option>
                <option value="2">Muavin/Şoför</option>
                <option value="3">Yolcu</option>
            </select><br>
                        <button type="submit" name="register" class="btn btn-success">Kayıt Ol</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <h1>Giriş Yap</h1>
                    <form method="POST" action="" class="p-3 border rounded">
                        <div class="mb-3">
                            <label for="kullanici_tel" class="form-label">Telefon:</label>
                            <input type="text" id="kullanici_tel" name="kullanici_tel" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="kullanici_sifre" class="form-label">Şifre:</label>
                            <input type="password" id="kullanici_sifre" name="kullanici_sifre" class="form-control" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">Giriş Yap</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
