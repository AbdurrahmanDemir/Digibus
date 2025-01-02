<?php
// Veritabanı bağlantısı
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'digibus');
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Veritabanı bağlantısı başarısız']));
}

if (!isset($_GET['sefer_id'])) {
    die(json_encode(['error' => 'Sefer ID eksik']));
}

$sefer_id = $conn->real_escape_string($_GET['sefer_id']);
$query = "SELECT koltuk_no FROM bilet WHERE sefer_id = '$sefer_id'";
$result = $conn->query($query);

$occupiedSeats = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $occupiedSeats[] = $row['koltuk_no'];
    }
}

echo json_encode(['occupiedSeats' => $occupiedSeats]);
$conn->close();
