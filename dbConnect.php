<?php
// Veritabanı bilgileri
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'testdb');

// Veritabanına bağlan
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Bağlantıyı kontrol et
if ($conn->connect_error) {
    die("Bağlantı başarısız: " . $conn->connect_error);
}

// Veritabanı oluştur
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    echo "Veritabanı başarıyla oluşturuldu.";
} else {
    echo "Veritabanı oluşturulamadı: " . $conn->error;
}

// Veritabanı bağlantısını kapat
$conn->close();

// Veritabanına bağlan ve tablo oluştur
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Bağlantı başarısız: " . $conn->connect_error);
}

// Örnek bir tablo oluşturma
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tablo başarıyla oluşturuldu.";
} else {
    echo "Tablo oluşturulamadı: " . $conn->error;
}

// Kullanıcı Kaydolma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "Kayıt başarılı.";
    } else {
        echo "Kayıt başarısız: " . $conn->error;
    }
}

// Kullanıcı Giriş
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            echo "Giriş başarılı. Hoşgeldiniz, " . $user['name'];
        } else {
            echo "Şifre yanlış.";
        }
    } else {
        echo "E-posta bulunamadı.";
    }
}

// Veritabanı bağlantısını kapat
$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üye Kaydol ve Giriş</title>
</head>
<body>
    <h1>Üye Kaydol</h1>
    <form method="POST" action="">
        <label for="name">Ad:</label>
        <input type="text" id="name" name="name" required><br>
        <label for="email">E-posta:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="password">Şifre:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit" name="register">Kaydol</button>
    </form>

    <h1>Giriş Yap</h1>
    <form method="POST" action="">
        <label for="email">E-posta:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="password">Şifre:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit" name="login">Giriş Yap</button>
    </form>
</body>
</html>
