<?php
// Oturum başlatılmışsa yeniden başlatmaya çalışma
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kullanıcının rolünü kontrol et
$rol_id = isset($_SESSION['rol_id']) ? intval($_SESSION['rol_id']) : null;

// Admin menü öğeleri
$admin_menu = [
    'Ana Sayfa' => 'login.php',
    'Dashboard' => 'dashboard.php',
    'Otobüs Oluştur' => 'otobus.php',
    'Sefer Oluştur' => 'seferolustur.php',
    'Mola Oluştur' => 'molaolustur.php',
    'Bilet Al' => 'biletal.php',
];

// Yolcu menü öğeleri
$yolcu_menu = [
    'Ana Sayfa' => 'login.php',
    'Bilet Al' => 'biletal.php',
];

// Menü seçimi
$menu_items = ($rol_id === 1) ? $admin_menu : (($rol_id === 3) ? $yolcu_menu : []);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Tasarımı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 240px;
            background-color: #343a40;
            color: #fff;
            padding: 15px;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        .sidebar .nav-link {
            color: #ddd;
            font-weight: 500;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: #495057;
            border-radius: 5px;
        }
        .content {
            margin-left: 240px; /* Sidebar genişliğine eşit olmalı */
            padding: 20px;
            transition: margin-left 0.3s;
        }
        .toggle-sidebar {
            display: none;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .toggle-sidebar {
                display: block;
                position: absolute;
                top: 15px;
                left: 15px;
                z-index: 1000;
                background-color: #007bff;
                color: #fff;
                border: none;
                border-radius: 5px;
                padding: 10px 15px;
                cursor: pointer;
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php if (!empty($menu_items)): ?>
        <!-- Toggle Button for Mobile -->
        <button class="toggle-sidebar" id="toggleSidebar">Menü</button>

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <h5 class="text-center">Menü</h5>
            <hr>
            <ul class="nav flex-column">
                <?php foreach ($menu_items as $title => $link): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == $link ? 'active' : '' ?>" href="<?= $link ?>">
                            <?= $title ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

  <!--   İçerik Alanı
    <div class="content">
        <h1>Sayfa İçeriği</h1>
        <p>Bu alanda ana içerik yer alır.</p>
    </div> -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function () {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        });
    </script>
</body>
</html>
