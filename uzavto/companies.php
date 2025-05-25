<?php
// Xatolar ko‘rsatilishini yoqish (debug uchun)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ma'lumotlar bazasiga ulanish
$servername = "localhost";
$username = "root"; // MySQL foydalanuvchi nomingiz
$password = ""; // MySQL parolingiz
$dbname = "uzauto";

$conn = new mysqli($servername, $username, $password, $dbname);

// UTF-8 sozlamasi
$conn->set_charset("utf8mb4");

// Ulanishni tekshirish
if ($conn->connect_error) {
    die("Ulanishda xato: " . $conn->connect_error);
}

// Filtrlarni olish
$dealer_filter = isset($_GET['dealer']) ? $conn->real_escape_string($_GET['dealer']) : '';
$year_filter = isset($_GET['year']) ? (int)$_GET['year'] : '';
$model_filter = isset($_GET['model']) ? $conn->real_escape_string($_GET['model']) : '';
$color_filter = isset($_GET['color']) ? $conn->real_escape_string($_GET['color']) : '';

// Dilerlar ma'lumotlarini olish
$sql = "SELECT d.name AS dealer_name, d.location, d.economic_index, 
               cm.model_name, c.color_name, s.year, s.quantity_sold, s.income
        FROM dealers d
        JOIN sales s ON d.dealer_id = s.dealer_id
        JOIN car_models cm ON s.model_id = cm.model_id
        JOIN colors c ON s.color_id = c.color_id
        WHERE 1=1";

if ($dealer_filter) {
    $sql .= " AND d.name = '$dealer_filter'";
}
if ($year_filter) {
    $sql .= " AND s.year = $year_filter";
}
if ($model_filter) {
    $sql .= " AND cm.model_name = '$model_filter'";
}
if ($color_filter) {
    $sql .= " AND c.color_name = '$color_filter'";
}

$result = $conn->query($sql);

// Dilerlar, yillar, modellar va ranglar ro‘yxatini olish (filtrlar uchun)
$dealers = $conn->query("SELECT name FROM dealers ORDER BY name");
$years = $conn->query("SELECT DISTINCT year FROM sales ORDER BY year");
$models = $conn->query("SELECT model_name FROM car_models ORDER BY model_name");
$colors = $conn->query("SELECT color_name FROM colors ORDER BY color_name");
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UzAuto Motors - Dilerlar</title>
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #e6f0fa, #b3e5fc);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: linear-gradient(90deg, rgb(30, 233, 255), rgb(0, 125, 209));
            padding: 35px 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1500px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 10px;
        }

        .header-content .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        h2 {
            margin-top: 50px;
        }

        .header-content .logo-img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            position: absolute;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .header-content .logo-img.active {
            opacity: 1;
        }

        .header-content .logo {
            font-size: 28px;
            font-weight: bold;
            color: black;
            letter-spacing: 1px;
            margin-left: 120px;
        }

        .header-content nav ul {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        .header-content nav ul li a {
            color: black;
            text-decoration: none;
            font-size: 18px;
            font-weight: 500;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .header-content nav ul li a:hover {
            transform: scale(1.1);
            color: #e0e0e0;
        }

        .header-content nav ul li a.active {
            text-decoration: underline;
            color: #e0e0e0;
        }

        .telegram-link {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(90deg, #0088cc, #00b7eb);
            padding: 10px 20px;
            border-radius: 25px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .telegram-link:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .filters {
            padding: 100px 20px 20px;
            text-align: center;
            background-color: #e6f0fa;
            position: relative;
        }

        .filters h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: #1E3A8A;
            animation: slideDown 1s ease;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .filters form {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filters select, .filters button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            min-width: 150px;
        }

        .filters button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .filters button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
            font-size: 18px;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .economic-high {
            color: green;
            font-weight: bold;
        }

        .economic-medium {
            color: #ffc107;
            font-weight: bold;
        }

        .economic-low {
            color: red;
            font-weight: bold;
        }

        .color-cell {
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
        }

        footer {
            background: rgba(0, 0, 0, 0.4);
            padding: 40px 20px;
            text-align: center;
            margin-top: auto;
            color: #fff;
            box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.2);
        }

        footer p {
            font-size: 16px;
            margin-bottom: 20px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        footer .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        footer .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        footer .social-links a:hover {
            transform: scale(1.2);
        }

        footer .social-links img {
            width: 40px;
            height: 40px;
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            body {
                font-size: 14px;
                margin-top: 60px;
            }

            header {
                padding: 20px 0;
            }

            .header-content {
                flex-direction: column;
                padding: 0 15px;
            }

            .header-content .logo-img {
                width: 60px;
                height: 60px;
            }

            .header-content .logo {
                font-size: 22px;
                margin-left: 80px;
            }

            .header-content nav ul {
                flex-wrap: wrap;
                gap: 15px;
                margin-top: 15px;
                justify-content: center;
            }

            .header-content nav ul li a {
                font-size: 16px;
            }

            .telegram-link {
                top: 10px;
                right: 10px;
                padding: 8px 15px;
                font-size: 12px;
            }

            .filters {
                padding: 80px 15px 15px;
            }

            .filters form {
                flex-direction: column;
                gap: 10px;
            }

            table {
                width: 90%;
            }

            th, td {
                font-size: 14px;
                padding: 10px;
            }

            footer {
                padding: 20px 10px;
            }

            footer p {
                font-size: 12px;
            }

            footer .social-links {
                gap: 15px;
            }

            footer .social-links a {
                width: 35px;
                height: 35px;
            }

            footer .social-links img {
                width: 35px;
                height: 35px;
            }
        }

        @media (max-width: 480px) {
            body {
                font-size: 12px;
            }

            .header-content .logo-img {
                width: 50px;
                height: 50px;
            }

            .header-content .logo {
                font-size: 18px;
                margin-left: 70px;
            }

            .telegram-link {
                padding: 6px 12px;
                font-size: 10px;
            }

            .filters h2 {
                font-size: 24px;
            }

            th, td {
                font-size: 12px;
                padding: 8px;
            }

            footer p {
                font-size: 10px;
            }

            footer .social-links a {
                width: 30px;
                height: 30px;
            }

            footer .social-links img {
                width: 30px;
                height: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="/rasm/m1.png" alt="Logo 1" class="logo-img active" loading="eager">
                <img src="/rasm/m2.png" alt="Logo 2" class="logo-img" loading="eager">
                <img src="/rasm/m3.png" alt="Logo 3" class="logo-img" loading="eager">
                <div class="logo">█▓▒░UzAuto Motors░▒▓█</div>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Bosh Sahifa</a></li>
                    <li><a href="companies.php" class="active">Dilerlar</a></li>
                    <li><a href="statistics.php">Statistika</a></li>
                    <li><a href="tahlil.php">Bashorat</a></li>
                    <li><a href="qrcod.php">Davr</a></li>
                    <li><a href=""><img src="rasm/bot.png" alt="Telegram Bot" width="40" height="40" style="vertical-align: middle;"></a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Telegram Link -->
    <a href="telegram.php" class="telegram-link">Telegram Bot</a>

    <!-- Filtrlar -->
    <section class="filters">
        <h2>Ma'lumotlarni Filtrlash</h2>
        <form method="GET">
            <select name="dealer">
                <option value="">Barcha Dilerlar</option>
                <?php while ($row = $dealers->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $dealer_filter == $row['name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endwhile; $dealers->data_seek(0); ?>
            </select>
            <select name="year">
                <option value="">Barcha Yillar</option>
                <?php while ($row = $years->fetch_assoc()): ?>
                    <option value="<?php echo $row['year']; ?>" <?php echo $year_filter == $row['year'] ? 'selected' : ''; ?>>
                        <?php echo $row['year']; ?>
                    </option>
                <?php endwhile; $years->data_seek(0); ?>
            </select>
            <select name="model">
                <option value="">Barcha Modellar</option>
                <?php while ($row = $models->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['model_name'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $model_filter == $row['model_name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['model_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endwhile; $models->data_seek(0); ?>
            </select>
            <select name="color">
                <option value="">Barcha Ranglar</option>
                <?php while ($row = $colors->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['color_name'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $color_filter == $row['color_name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['color_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endwhile; $colors->data_seek(0); ?>
            </select>
            <button type="submit">Filtrlash</button>
        </form>

        <!-- Jadval -->
        <table>
            <thead>
                <tr>
                    <th>Diler Nomi</th>
                    <th>Model</th>
                    <th>Rang</th>
                    <th>Sotilgan Son</th>
                    <th>Daromad (UZS)</th>
                    <th>Joylashuvi</th>
                    <th>Iqtisodiy Ko‘rsatkich</th>
                    <th>Yil</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['dealer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['model_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="color-cell" style="
                                <?php
                                $color = htmlspecialchars($row['color_name'], ENT_QUOTES, 'UTF-8');
                                $bgColor = '';
                                $textColor = '#000000'; // Default text color
                                switch ($color) {
                                    case 'Oq': $bgColor = '#FFFFFF'; break;
                                    case 'Qora': $bgColor = '#000000'; $textColor = '#FFFFFF'; break;
                                    case 'Kumush': $bgColor = '#C0C0C0'; $textColor = '#000000'; break;
                                    case 'Qizil': $bgColor = '#FF0000'; $textColor = '#FFFFFF'; break;
                                    case 'Ko‘k': $bgColor = '#0000FF'; $textColor = '#FFFFFF'; break;
                                    case 'Kulrang': $bgColor = '#808080'; $textColor = '#FFFFFF'; break;
                                    case 'Yashil': $bgColor = '#008000'; $textColor = '#FFFFFF'; break;
                                    case 'Oltin': $bgColor = '#FFD700'; $textColor = '#000000'; break;
                                    default: $bgColor = '#FFFFFF';
                                }
                                echo "background-color: $bgColor; color: $textColor;";
                                ?>
                            "><?php echo $color; ?></td>
                            <td><?php echo htmlspecialchars($row['quantity_sold'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo number_format($row['income'], 2, '.', ' '); ?></td>
                            <td><?php echo htmlspecialchars($row['location'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="economic-<?php echo strtolower($row['economic_index']); ?>">
                                <?php 
                                switch ($row['economic_index']) {
                                    case 'high': echo 'Yuqori'; break;
                                    case 'medium': echo 'O‘rtacha'; break;
                                    case 'low': echo 'Past'; break;
                                    default: echo 'Nomalum';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['year'], ENT_QUOTES, 'UTF-8') ?: 'N/A'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">Ma'lumot topilmadi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- Footer -->
    <footer>
        <p>© 2025 UzAuto Motors. Barcha huquqlar himoyalangan.</p>
        <div class="social-links">
            <a href="telegram.php" title="Telegram Bot" style="background: #0088cc; border-radius: 50%; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px;">
                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/telegram.svg" alt="Telegram" width="28" height="28" style="display: block; filter: invert(0.5) sepia(1) saturate(5) hue-rotate(170deg);">
            </a>
            <a href="https://instagram.com/uzautomotors" target="_blank" title="Instagram" style="background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%); border-radius: 50%; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px;">
                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/instagram.svg" alt="Instagram" width="25" height="20" style="display: block;">
            </a>
            <a href="https://facebook.com/uzautomotors" target="_blank" title="Facebook" style="background: #1877f3; border-radius: 50%; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px;">
                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/facebook.svg" alt="Facebook" width="28" height="28" style="display: block;">
            </a>
        </div>
    </footer>

    <!-- JavaScript for Logo Carousel -->
    <script>
        const logos = document.querySelectorAll('.logo-img');
        let currentLogo = 0;

        function showNextLogo() {
            logos[currentLogo].classList.remove('active');
            currentLogo = (currentLogo + 1) % logos.length;
            logos[currentLogo].classList.add('active');
        }

        setInterval(showNextLogo, 3000);
        logos[currentLogo].classList.add('active');
    </script>
</body>
</html>

<?php
// Ulanishni yopish
$conn->close();
?>