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
$month_filter = isset($_GET['month']) ? (int)$_GET['month'] : '';
$jins_filter = isset($_GET['jins']) ? $conn->real_escape_string($_GET['jins']) : '';
$model_filter = isset($_GET['model']) ? $conn->real_escape_string($_GET['model']) : '';
$color_filter = isset($_GET['color']) ? $conn->real_escape_string($_GET['color']) : '';

// Sotuv ma'lumotlarini olish uchun SQL so‘rov
$sql = "SELECT d.name AS dealer_name, d.location, d.latitude, d.longitude, d.economic_index, 
               cm.model_name, c.color_name, s.year, s.month, s.quantity_sold, s.income, s.jins, s.yosh
        FROM sales s
        JOIN dealers d ON s.dealer_id = d.dealer_id
        JOIN car_models cm ON s.model_id = cm.model_id
        JOIN colors c ON s.color_id = c.color_id
        WHERE 1=1";

if ($dealer_filter) {
    $sql .= " AND d.name = '$dealer_filter'";
}
if ($year_filter) {
    $sql .= " AND s.year = $year_filter";
}
if ($month_filter) {
    $sql .= " AND s.month = $month_filter";
}
if ($jins_filter) {
    $sql .= " AND s.jins = '$jins_filter'";
}
if ($model_filter) {
    $sql .= " AND cm.model_name = '$model_filter'";
}
if ($color_filter) {
    $sql .= " AND c.color_name = '$color_filter'";
}

$result = $conn->query($sql);

// Dilerlar, yillar, oylar, jinslar, modellar va ranglar ro‘yxatini olish (filtrlar uchun)
$dealers = $conn->query("SELECT name FROM dealers ORDER BY name");
$years = $conn->query("SELECT DISTINCT year FROM sales ORDER BY year");
$months = $conn->query("SELECT DISTINCT month FROM sales ORDER BY month");
$jinslar = $conn->query("SELECT DISTINCT jins FROM sales ORDER BY jins");
$models = $conn->query("SELECT model_name FROM car_models ORDER BY model_name");
$colors = $conn->query("SELECT color_name FROM colors ORDER BY color_name");

// Xarita uchun dilerlar ma'lumotlarini olish
$map_data = $conn->query("SELECT name, location, latitude, longitude, economic_index FROM dealers");
$map_data_array = [];
while ($row = $map_data->fetch_assoc()) {
    $map_data_array[] = $row;
}

// Statistika uchun ma'lumotlarni tayyorlash
$chart_data_year = [];
$chart_data_color = [];
$chart_data_month = [];
$chart_data_model = [];
$chart_data_age = [];
$chart_data_location = [];
$details_data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Yil bo'yicha
        $year = $row['year'];
        if (!isset($chart_data_year[$year])) {
            $chart_data_year[$year] = 0;
        }
        $chart_data_year[$year] += $row['quantity_sold'];

        // Rang bo'yicha
        $color = $row['color_name'];
        if (!isset($chart_data_color[$color])) {
            $chart_data_color[$color] = 0;
        }
        $chart_data_color[$color] += $row['quantity_sold'];

        // Oy bo'yicha
        $month = $row['month'];
        $month_names = [1 => 'Yanvar', 2 => 'Fevral', 3 => 'Mart', 4 => 'Aprel', 5 => 'May', 6 => 'Iyun', 7 => 'Iyul', 8 => 'Avgust', 9 => 'Sentabr', 10 => 'Oktabr', 11 => 'Noyabr', 12 => 'Dekabr'];
        if (!isset($chart_data_month[$month])) {
            $chart_data_month[$month] = 0;
        }
        $chart_data_month[$month] += $row['quantity_sold'];

        // Model bo'yicha
        $model = $row['model_name'];
        if (!isset($chart_data_model[$model])) {
            $chart_data_model[$model] = 0;
        }
        $chart_data_model[$model] += $row['quantity_sold'];

        // Yosh bo'yicha (guruhlash)
        $age = $row['yosh'];
        if ($age <= 25) {
            $age_group = '18-25';
        } elseif ($age <= 35) {
            $age_group = '26-35';
        } elseif ($age <= 45) {
            $age_group = '36-45';
        } else {
            $age_group = '46+';
        }
        if (!isset($chart_data_age[$age_group])) {
            $chart_data_age[$age_group] = 0;
        }
        $chart_data_age[$age_group] += $row['quantity_sold'];

        // Joylashuv bo'yicha
        $location = $row['dealer_name'];
        if (!isset($chart_data_location[$location])) {
            $chart_data_location[$location] = 0;
        }
        $chart_data_location[$location] += $row['quantity_sold'];

        // Batafsil ma'lumotlar
        $details_data[] = [
            'dealer' => $row['dealer_name'],
            'location' => $row['location'],
            'economic_index' => $row['economic_index'],
            'model' => $row['model_name'],
            'color' => $row['color_name'],
            'year' => $row['year'],
            'month' => $month_names[$row['month']],
            'quantity' => $row['quantity_sold'],
            'income' => $row['income'],
            'jins' => $row['jins'],
            'yosh' => $row['yosh']
        ];
    }
} else {
    $chart_data_year = [];
    $chart_data_color = [];
    $chart_data_month = [];
    $chart_data_model = [];
    $chart_data_age = [];
    $chart_data_location = [];
    $details_data = [];
}
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UzAuto Motors Statistika Tahlili</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

        .hero {
            text-align: center;
            padding: 100px 20px 20px;
            min-height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .hero-card {
            background: linear-gradient(90deg, rgb(30, 233, 255), rgb(0, 125, 209));
            color: #fff;
            border-radius: 15px;
            padding: 80px;
            max-width: 900px;
            width: 100%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            animation: slideDown 1s ease;
        }

        .hero-card h2 {
            font-size: 48px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        }

        .hero-card p {
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .hero-card .buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .hero-card .btn {
            padding: 12px 30px;
            font-size: 18px;
            animation: pulse 2s infinite;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            background: #fff;
            color: #1E3A8A;
            border-radius: 25px;
            text-decoration: none;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .hero-card .btn:hover {
            transform: scale(1.1);
            background: #e0e0e0;
        }

        .filters {
            padding: 20px;
            text-align: center;
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
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filters input, .filters select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            min-width: 150px;
        }

        .filters button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .filters button:hover {
            background-color: #0056b3;
        }

        .chart-map {
            padding: 20px;
            text-align: center;
        }

        .chart-map h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: #1E3A8A;
            animation: slideDown 1s ease;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .charts-row {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-container {
            flex: 1;
            min-width: 300px;
            max-width: 400px;
            margin: 10px;
            padding: 10px;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            animation: slideUp 1s ease;
            position: relative;
        }

        .chart-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background-color: #98FB98;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .chart-container canvas {
            max-width: 100%;
            height: 200px !important;
        }

        .no-data-message {
            flex: 1;
            min-width: 300px;
            max-width: 400px;
            margin: 10px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
            color: #dc3545;
            font-weight: 500;
        }

        .details-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            display: none;
        }

        .details-container table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-container th, .details-container td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .details-container th {
            background-color: #007bff;
            color: white;
        }

        .explanation-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .map-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            height: 400px;
            animation: slideUp 1s ease;
        }

        .map-container > div {
            height: 100%;
            width: 100%;
            border-radius: 10px;
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

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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

            .hero {
                padding: 80px 15px 15px;
            }

            .hero-card {
                padding: 40px 20px;
                max-width: 100%;
            }

            .hero-card h2 {
                font-size: 32px;
            }

            .hero-card p {
                font-size: 16px;
            }

            .hero-card .buttons {
                flex-direction: column;
                gap: 15px;
            }

            .hero-card .btn {
                padding: 10px 25px;
                font-size: 16px;
            }

            .filters form {
                flex-direction: column;
                gap: 10px;
            }

            .charts-row {
                flex-direction: column;
            }

            .chart-container, .no-data-message {
                width: 100%;
                max-width: none;
            }

            .map-container {
                width: 90%;
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

            .hero-card h2 {
                font-size: 24px;
            }

            .hero-card p {
                font-size: 14px;
            }

            .hero-card .btn {
                padding: 8px 20px;
                font-size: 14px;
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
                    <li><a href="index.php" class="active">Bosh sahifa</a></li>
                    <li><a href="companies.php">Dilerlar</a></li>
                    <li><a href="statistics.php">Statistika</a></li>
                    <li><a href="tahlil.php">Bashorat</a></li>
                    <li><a href="qrcod.php">Davr</a></li>
                    <li><a href=""><img src="rasm/bot.png" alt="Telegram Bot" width="40" height="40" style="vertical-align: middle;"></a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-card">
            <h2>UzAuto Motors statistika tahlili</h2>
            <p>O‘zbekiston bo‘ylab UzAuto Motors dilerlarining sotuv ko‘rsatkichlari bo'yicha to'liq statistika va interaktiv xaritalar.</p>
            <div class="buttons">
                <a href="#filters" class="btn">Tahlilni boshlash</a>
                <a href="statistics.php" class="btn">Statistikani ko‘rish</a>
            </div>
        </div>
    </section>

    <!-- Filtrlar -->
    <section class="filters" id="filters">
        <h2>Ma'lumotlarni Filtrlash</h2>
        <form method="GET">
            <select name="dealer" id="dealer">
                <option value="">Barcha Dilerlar</option>
                <?php while ($row = $dealers->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $dealer_filter == $row['name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endwhile; $dealers->data_seek(0); ?>
            </select>
            <select name="year" id="year">
                <option value="">Barcha Yillar</option>
                <?php while ($row = $years->fetch_assoc()): ?>
                    <option value="<?php echo $row['year']; ?>" <?php echo $year_filter == $row['year'] ? 'selected' : ''; ?>>
                        <?php echo $row['year']; ?>
                    </option>
                <?php endwhile; $years->data_seek(0); ?>
            </select>
            <select name="month" id="month">
                <option value="">Barcha Oylar</option>
                <?php 
                $month_names = [1 => 'Yanvar', 2 => 'Fevral', 3 => 'Mart', 4 => 'Aprel', 5 => 'May', 6 => 'Iyun', 7 => 'Iyul', 8 => 'Avgust', 9 => 'Sentabr', 10 => 'Oktabr', 11 => 'Noyabr', 12 => 'Dekabr'];
                while ($row = $months->fetch_assoc()): ?>
                    <option value="<?php echo $row['month']; ?>" <?php echo $month_filter == $row['month'] ? 'selected' : ''; ?>>
                        <?php echo $month_names[$row['month']]; ?>
                    </option>
                <?php endwhile; $months->data_seek(0); ?>
            </select>
            <select name="jins" id="jins">
                <option value="">Barcha Jinslar</option>
                <?php while ($row = $jinslar->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['jins'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $jins_filter == $row['jins'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['jins'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endwhile; $jinslar->data_seek(0); ?>
            </select>
            <select name="model" id="model">
                <option value="">Barcha Modellar</option>
                <?php while ($row = $models->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['model_name'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $model_filter == $row['model_name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['model_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endwhile; $models->data_seek(0); ?>
            </select>
            <select name="color" id="color">
                <option value="">Barcha Ranglar</option>
                <?php while ($row = $colors->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['color_name'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $color_filter == $row['color_name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['color_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endwhile; $colors->data_seek(0); ?>
            </select>
            <button type="submit">Filtrlash</button>
        </form>
    </section>

    <!-- Grafiklar -->
    <section class="chart-map">
        <h2>Interaktiv Statistika</h2>

        <div class="charts-row">
            <?php if (!empty($chart_data_year)): ?>
                <div class="chart-container">
                    <h3>Yil bo'yicha sotuvlar</h3>
                    <canvas id="yearChart"></canvas>
                </div>
            <?php else: ?>
                <div class="no-data-message">
                    Yil bo'yicha ma'lumotlar topilmadi. Iltimos, filtrlaringizni tekshiring yoki ma'lumotlar bazasida ma'lumotlar mavjudligiga ishonch hosil qiling.
                </div>
            <?php endif; ?>

            <?php if (!empty($chart_data_color)): ?>
                <div class="chart-container">
                    <h3>Ranglar bo'yicha sotuvlar </h3>
                    <canvas id="colorChart"></canvas>
                </div>
            <?php else: ?>
                <div class="no-data-message">
                    Ranglar bo'yicha ma'lumotlar topilmadi. Iltimos, filtrlaringizni tekshiring yoki ma'lumotlar bazasida ma'lumotlar mavjudligiga ishonch hosil qiling.
                </div>
            <?php endif; ?>

            <?php if (!empty($chart_data_month)): ?>
                <div class="chart-container">
                    <h3>Oylar bo'yicha sotuvlar</h3>
                    <canvas id="monthChart"></canvas>
                </div>
            <?php else: ?>
                <div class="no-data-message">
                    Oylar bo'yicha ma'lumotlar topilmadi. Iltimos, filtrlaringizni tekshiring yoki ma'lumotlar bazasida ma'lumotlar mavjudligiga ishonch hosil qiling.
                </div>
            <?php endif; ?>
        </div>

        <div class="charts-row">
            <?php if (!empty($chart_data_model)): ?>
                <div class="chart-container">
                    <h3>Modellar bo'yicha sotuvlar </h3>
                    <canvas id="modelChart"></canvas>
                </div>
            <?php else: ?>
                <div class="no-data-message">
                    Modellar bo'yicha ma'lumotlar topilmadi. Iltimos, filtrlaringizni tekshiring yoki ma'lumotlar bazasida ma'lumotlar mavjudligiga ishonch hosil qiling.
                </div>
            <?php endif; ?>

            <?php if (!empty($chart_data_age)): ?>
                <div class="chart-container">
                    <h3>Yosh guruhlari bo'yicha sotuvlar</h3>
                    <canvas id="ageChart"></canvas>
                </div>
            <?php else: ?>
                <div class="no-data-message">
                    Yosh guruhlari bo'yicha ma'lumotlar topilmadi. Iltimos, filtrlaringizni tekshiring yoki ma'lumotlar bazasida ma'lumotlar mavjudligiga ishonch hosil qiling.
                </div>
            <?php endif; ?>

            <?php if (!empty($chart_data_location)): ?>
                <div class="chart-container">
                    <h3>Joylashuv bo'yicha sotuvlar </h3>
                    <canvas id="locationChart"></canvas>
                </div>
            <?php else: ?>
                <div class="no-data-message">
                    Joylashuv bo'yicha ma'lumotlar topilmadi. Iltimos, filtrlaringizni tekshiring yoki ma'lumotlar bazasida ma'lumotlar mavjudligiga ishonch hosil qiling.
                </div>
            <?php endif; ?>
        </div>

        <div class="details-container" id="detailsContainer">
            <h3>Batafsil Ma'lumotlar</h3>
            <table>
                <thead>
                    <tr>
                        <th>Diler</th>
                        <th>Joylashuv</th>
                        <th>Iqtisodiy Ko‘rsatkich</th>
                        <th>Model</th>
                        <th>Rang</th>
                        <th>Yil</th>
                        <th>Oy</th>
                        <th>Jins</th>
                        <th>Yosh</th>
                        <th>Sotilgan Son</th>
                        <th>Daromad (UZS)</th>
                    </tr>
                </thead>
                <tbody id="detailsTable"></tbody>
            </table>
        </div>

        <div class="explanation-container" id="explanationContainer">
            <h3>Statistika Izohi</h3>
            <p id="explanationText"></p>
        </div>

        <div class="map-container">
            <div id="map"></div>
        </div>
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

    <!-- Telegram Link -->
    <a href="telegram.php" class="telegram-link">Telegram Bot</a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Logo karuseli
        const logos = document.querySelectorAll('.logo-img');
        let currentLogo = 0;

        function rotateLogos() {
            logos[currentLogo].classList.remove('active');
            currentLogo = (currentLogo + 1) % logos.length;
            logos[currentLogo].classList.add('active');
        }

        setInterval(rotateLogos, 3000);

        // Xarita sozlamalari
        const map = L.map('map').setView([41.2995, 69.2401], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        // Dilerlarni xaritaga qo‘shish
        const dealers = <?php echo json_encode($map_data_array, JSON_UNESCAPED_UNICODE); ?>;
        dealers.forEach(dealer => {
            const color = dealer.economic_index === 'high' ? 'green' : dealer.economic_index === 'medium' ? 'yellow' : 'red';
            const marker = L.circleMarker([dealer.latitude, dealer.longitude], {
                color: color,
                radius: 10
            }).addTo(map);
            marker.bindPopup(`<b>${dealer.name}</b><br>Joylashuv: ${dealer.location}<br>Iqtisodiy: ${dealer.economic_index}`);
        });

        // Grafik ma'lumotlari
        const yearData = <?php echo json_encode(array_keys($chart_data_year), JSON_UNESCAPED_UNICODE); ?>;
        const yearQuantities = <?php echo json_encode(array_values($chart_data_year)); ?>;
        const colorData = <?php echo json_encode(array_keys($chart_data_color), JSON_UNESCAPED_UNICODE); ?>;
        const colorQuantities = <?php echo json_encode(array_values($chart_data_color)); ?>;
        const monthData = <?php echo json_encode(array_keys($chart_data_month), JSON_UNESCAPED_UNICODE); ?>;
        const monthNames = [1, 'Yanvar', 2, 'Fevral', 3, 'Mart', 4, 'Aprel', 5, 'May', 6, 'Iyun', 7, 'Iyul', 8, 'Avgust', 9, 'Sentabr', 10, 'Oktabr', 11, 'Noyabr', 12, 'Dekabr'];
        const monthLabels = monthData.map(m => monthNames[m]);
        const monthQuantities = <?php echo json_encode(array_values($chart_data_month)); ?>;
        const modelData = <?php echo json_encode(array_keys($chart_data_model), JSON_UNESCAPED_UNICODE); ?>;
        const modelQuantities = <?php echo json_encode(array_values($chart_data_model)); ?>;
        const ageData = <?php echo json_encode(array_keys($chart_data_age), JSON_UNESCAPED_UNICODE); ?>;
        const ageQuantities = <?php echo json_encode(array_values($chart_data_age)); ?>;
        const locationData = <?php echo json_encode(array_keys($chart_data_location), JSON_UNESCAPED_UNICODE); ?>;
        const locationQuantities = <?php echo json_encode(array_values($chart_data_location)); ?>;
        const detailsData = <?php echo json_encode($details_data, JSON_UNESCAPED_UNICODE); ?>;

        // Debugging uchun ma'lumotlarni konsolga chiqarish
        console.log('Year Data:', yearData, yearQuantities);
        console.log('Color Data:', colorData, colorQuantities);
        console.log('Month Data:', monthLabels, monthQuantities);
        console.log('Model Data:', modelData, modelQuantities);
        console.log('Age Data:', ageData, ageQuantities);
        console.log('Location Data:', locationData, locationQuantities);
        console.log('Details Data:', detailsData);

        // Grafik sozlamalari
        function createChart(canvasId, labels, data, title, type, backgroundColor, borderColor) {
            const ctx = document.getElementById(canvasId);
            if (!ctx) {
                console.error(`Canvas elementi topilmadi: ${canvasId}`);
                return;
            }

            if (!labels.length || !data.length) {
                console.warn(`Ma'lumotlar bo'sh: ${canvasId}`);
                return;
            }

            new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: title,
                        data: data,
                        backgroundColor: backgroundColor,
                        borderColor: borderColor,
                        borderWidth: type === 'line' ? 2 : 1,
                        fill: type === 'line' ? true : false,
                        tension: type === 'line' ? 0.4 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: type !== 'pie' ? {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Sotilgan soni'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: title
                            }
                        }
                    } : {}
                }
            });
        }

        // Grafiklarni yaratish
        if (yearData.length > 0 && yearQuantities.length > 0) {
            // Grafik turini avtomatik aniqlash va chiqarish
            let chartType = 'bar';
            if (yearData.length === 1) {
                chartType = 'pie';
            } else if (yearData.length > 1 && yearData.length <= 3) {
                chartType = 'doughnut';
            }
            // Grafik turini konsolga chiqarish
            console.log('Yil bo‘yicha grafik turi:', chartType);
            createChart('yearChart', yearData, yearQuantities, 'Yil bo‘yicha sotuvlar', chartType, ['#FFD700', '#FF4500'], ['#DAA520', '#DC143C']);
        }

  if (colorData.length > 0 && colorQuantities.length > 0) {
    createChart(
        'colorChart',
        colorData,
        colorQuantities,
        'Ranglar bo‘yicha sotuvlar',
        'pie',
        ['#FFFFFF', '#000000', '#C0C0C0', '#FF0000', '#0000FF', '#808080', '#008000', '#FFD700'],
        ['#E0E0E0', '#222222', '#A9A9A9', '#CC0000', '#0000CC', '#666666', '#006400', '#FFC300']
    );
}



        if (monthData.length > 0 && monthQuantities.length > 0) {
            createChart('monthChart', monthLabels, monthQuantities, 'Oylar bo‘yicha sotuvlar', 'line', 'rgba(50, 205, 50, 0.2)', '#32CD32');
        }

        if (modelData.length > 0 && modelQuantities.length > 0) {
            createChart('modelChart', modelData, modelQuantities, 'Modellar bo‘yicha sotuvlar', 'bar', ['#1E90FF', '#00BFFF', '#87CEEB'], ['#1E90FF', '#00BFFF', '#87CEEB']);
        }

        if (ageData.length > 0 && ageQuantities.length > 0) {
            createChart('ageChart', ageData, ageQuantities, 'Yosh guruhlari bo‘yicha sotuvlar', 'bar', ['#8A2BE2', '#9932CC', '#4B0082', '#6A5ACD'], ['#4B0082', '#6A5ACD', '#483D8B', '#7B68EE']);
        }

        if (locationData.length > 0 && locationQuantities.length > 0) {
            createChart('locationChart', locationData, locationQuantities, 'Joylashuv bo‘yicha sotuvlar', 'bar', ['#FF4500', '#FFD700', '#FFA500'], ['#DC143C', '#DAA520', '#FF4500']);
        }

        // Batafsil ma'lumotlarni ko‘rsatish
        function showDetails(key, type) {
            const detailsContainer = document.getElementById('detailsContainer');
            const detailsTable = document.getElementById('detailsTable');
            detailsTable.innerHTML = '';
            const filteredData = detailsData.filter(item => {
                if (type === 'yearChart') return item.year == key;
                if (type === 'colorChart') return item.color === key;
                if (type === 'monthChart') return item.month === key;
                if (type === 'modelChart') return item.model === key;
                if (type === 'ageChart') {
                    const age = item.yosh;
                    let age_group;
                    if (age <= 25) age_group = '18-25';
                    else if (age <= 35) age_group = '26-35';
                    else if (age <= 45) age_group = '36-45';
                    else age_group = '46+';
                    return age_group === key;
                }
                if (type === 'locationChart') return item.dealer === key;
                return false;
            });

            filteredData.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.dealer}</td>
                    <td>${item.location}</td>
                    <td>${item.economic_index}</td>
                    <td>${item.model}</td>
                    <td>${item.color}</td>
                    <td>${item.year}</td>
                    <td>${item.month}</td>
                    <td>${item.jins}</td>
                    <td>${item.yosh}</td>
                    <td>${item.quantity}</td>
                    <td>${Number(item.income).toLocaleString('uz-UZ')}</td>
                `;
                detailsTable.appendChild(row);
            });

            detailsContainer.style.display = 'block';
            detailsContainer.scrollIntoView({ behavior: 'smooth' });

            const explanationContainer = document.getElementById('explanationContainer');
            const explanationText = document.getElementById('explanationText');
            let explanation = '';
            if (type === 'yearChart') {
                explanation = `Ushbu grafik ${key}-yildagi sotuvlarni ko‘rsatadi.`;
            } else if (type === 'colorChart') {
                explanation = `Ushbu grafik ${key} rangi bo'yicha sotuvlarni ko‘rsatadi.`;
            } else if (type === 'monthChart') {
                explanation = `Ushbu grafik ${key} oyidagi sotuvlarni ko‘rsatadi.`;
            } else if (type === 'modelChart') {
                explanation = `Ushbu grafik ${key} modeli bo'yicha sotuvlarni ko‘rsatadi.`;
            } else if (type === 'ageChart') {
                explanation = `Ushbu grafik ${key} yosh guruhidagi sotuvlarni ko‘rsatadi.`;
            } else if (type === 'locationChart') {
                explanation = `Ushbu grafik ${key} dileri bo'yicha sotuvlarni ko‘rsatadi.`;
            }
            explanationText.textContent = explanation;
            explanationContainer.style.display = 'block';
        }
    </script>
</body>
</html>

<?php
// Ulanishni yopish
$conn->close();
?>