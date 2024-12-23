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
        // `rol` tablosunda `rol_id` değerinin var olup olmadığını kontrol et
        if (!in_array($rol_id, [1, 2, 3])) {
            echo "<p>Geçersiz rol ID. Lütfen geçerli bir rol seçin (Admin, Muavin/Şoför, Yolcu).</p>";
        } else {
            // Kullanıcı telefon numarası veya TC Kimlik No kontrolü
            $checkQuery = "SELECT kullanici_id FROM kullanici WHERE kullanici_id = '$kullanici_id' OR kullanici_tel = '$kullanici_tel'";
            $result = $conn->query($checkQuery);

            if ($result->num_rows > 0) {
                echo "<p>Bu TC Kimlik No veya telefon numarası zaten kayıtlı.</p>";
            } else {
                // Yeni kullanıcı kaydet
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
}

// Kullanıcı Giriş
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $kullanici_tel = $conn->real_escape_string($_POST['kullanici_tel']);
    $kullanici_sifre = $_POST['kullanici_sifre'];

    $sql = "SELECT * FROM kullanici WHERE kullanici_tel = '$kullanici_tel'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($kullanici_sifre, $user['kullanici_sifre'])) {
            $_SESSION['kullanici_ad'] = $user['kullanici_ad'];
            $_SESSION['kullanici_soyad'] = $user['kullanici_soyad'];
            $_SESSION['rol_id'] = $user['rol_id'];
            echo "<p>Giriş başarılı. Hoşgeldiniz, " . $user['kullanici_ad'] . " " . $user['kullanici_soyad'] . "!</p>";
        } else {
            echo "<p>Şifre yanlış!</p>";
        }
    } else {
        echo "<p>Telefon numarası bulunamadı.</p>";
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
</head>
<body>
    <?php if (isset($_SESSION['kullanici_ad'])): ?>
        <h1>Hoşgeldiniz, <?= $_SESSION['kullanici_ad'] ?> <?= $_SESSION['kullanici_soyad'] ?>!</h1>
        <form method="POST" action="">
            <button type="submit" name="logout">Çıkış Yap</button>
        </form>
    <?php else: ?>
        <h1>Kayıt Ol</h1>
        <form method="POST" action="">
            <label for="kullanici_id">TC Kimlik No:</label>
            <input type="text" id="kullanici_id" name="kullanici_id" required><br>
            <label for="kullanici_ad">Ad:</label>
            <input type="text" id="kullanici_ad" name="kullanici_ad" required><br>
            <label for="kullanici_soyad">Soyad:</label>
            <input type="text" id="kullanici_soyad" name="kullanici_soyad" required><br>
            <label for="kullanici_tel">Telefon:</label>
            <input type="text" id="kullanici_tel" name="kullanici_tel" required><br>
            <label for="kullanici_sifre">Şifre:</label>
            <input type="password" id="kullanici_sifre" name="kullanici_sifre" required><br>
            <label for="rol_id">Rol:</label>
            <select id="rol_id" name="rol_id" required>
                <option value="1">Admin</option>
                <option value="2">Muavin</option>
                <option value="3">Şoför</option>
            </select><br>
            <button type="submit" name="register">Kayıt Ol</button>
        </form>

        <h1>Giriş Yap</h1>
        <form method="POST" action="">
            <label for="kullanici_tel">Telefon:</label>
            <input type="text" id="kullanici_tel" name="kullanici_tel" required><br>
            <label for="kullanici_sifre">Şifre:</label>
            <input type="password" id="kullanici_sifre" name="kullanici_sifre" required><br>
            <button type="submit" name="login">Giriş Yap</button>
        </form>
    <?php endif; ?>
</body>
</html>
