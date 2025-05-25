<?php
$salesJsonFile = './data/monthly_summary.json';
$visitsJsonFile = './data/future_visits_week.json';
$csvFile = './data/uzauto_dataset_mantiqiy.csv';

$productIdToModel = array(
    '1' => 'Cobalt',
    '2' => 'Nexia 3',
    '3' => 'Malibu 2',
    '4' => 'Malibu',
    '5' => 'Damas',
    '6' => 'Labo',
    '7' => 'Tracker',
    '8' => 'Equinox',
    '9' => 'Onix',
    '10' => 'Gentra',
    '11' => 'Spark',
);

function processMonthlyData($data, $productIdToModel) {
    $result = array();
    foreach ($data as $month => $models) {
        $entry = array('month' => $month);
        foreach ($productIdToModel as $model) {
            $entry[$model] = isset($models[$model]) ? (int)$models[$model] : 0;
        }
        $result[] = $entry;
    }
    usort($result, function($a, $b) {
        return strcmp($a['month'], $b['month']);
    });
    return $result;
}

function processWeeklyVisits($data) {
    $result = array();
    foreach ($data as $day => $hours) {
        $hourlyData = array();
        foreach ($hours as $time => $visits) {
            $hourlyData[] = array(
                'time' => explode('–', $time)[0],
                'visits' => (int)$visits,
            );
        }
        usort($hourlyData, function($a, $b) {
            return strcmp($a['time'], $b['time']);
        });
        $result[] = array('day' => $day, 'hours' => $hourlyData);
    }
    usort($result, function($a, $b) {
        return strcmp($a['day'], $b['day']);
    });
    return $result;
}

function processCsvData($data) {
    $result = array();
    foreach ($data as $row) {
        $quantity = isset($row['quantity']) ? (int)$row['quantity'] : 0;
        $date = isset($row['date']) ? date('Y-m-d', strtotime($row['date'])) : null;
        $product_id = isset($row['product_id']) ? trim($row['product_id']) : '';
        $model = isset($row['model']) ? trim($row['model']) : '';
        $color = isset($row['color']) ? trim($row['color']) : '';
        $variant = isset($row['variant']) ? trim($row['variant']) : '';

        if ($quantity > 0 && $model !== '' && $color !== '' && $date !== null) {
            $result[] = array(
                'date' => $date,
                'product_id' => $product_id,
                'model' => $model,
                'color' => $color,
                'variant' => $variant,
                'quantity' => $quantity,
            );
        }
    }
    return $result;
}

$monthlyData = array();
if (file_exists($salesJsonFile)) {
    $monthlySalesJson = file_get_contents($salesJsonFile);
    $monthlySummary = json_decode($monthlySalesJson, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($monthlySummary)) {
        $monthlyData = processMonthlyData($monthlySummary, $productIdToModel);
    } else {
        error_log("Error: Invalid JSON in '$salesJsonFile'. Error code: " . json_last_error());
        die("Error: Invalid sales JSON data in '$salesJsonFile'.");
    }
} else {
    error_log("Error: Sales JSON file '$salesJsonFile' not found.");
    die("Error: Sales JSON file '$salesJsonFile' not found.");
}

$weeklyVisits = array();
if (file_exists($visitsJsonFile)) {
    $visitsJson = file_get_contents($visitsJsonFile);
    $weeklyVisitsSample = json_decode($visitsJson, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($weeklyVisitsSample)) {
        $weeklyVisits = processWeeklyVisits($weeklyVisitsSample);
    } else {
        $weeklyVisits = array();
        error_log("Error: Invalid JSON in '$visitsJsonFile'. Error code: " . json_last_error());
    }
} else {
    $weeklyVisits = array();
    error_log("Error: Visits JSON file '$visitsJsonFile' not found.");
}

$csvData = array();
if (file_exists($csvFile)) {
    if (($handle = fopen($csvFile, 'r')) !== false) {
        $header = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                $csvData[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    $csvData = processCsvData($csvData);
} else {
    $csvData = array();
    error_log("Error: CSV file '$csvFile' not found.");
}

$salesByModel = array();
foreach ($monthlyData as $month) {
    foreach ($productIdToModel as $model) {
        $salesByModel[$model] = (isset($salesByModel[$model]) ? $salesByModel[$model] : 0) + (isset($month[$model]) ? $month[$model] : 0);
    }
}
arsort($salesByModel);
$modelLabels = array_keys($salesByModel);
$modelData = array_values($salesByModel);

$salesByColor = array();
foreach ($csvData as $row) {
    $color = $row['color'];
    $salesByColor[$color] = (isset($salesByColor[$color]) ? $salesByColor[$color] : 0) + $row['quantity'];
}
arsort($salesByColor);
$colorLabels = array_keys($salesByColor);
$colorData = array_values($salesByColor);

// Calculate daily and monthly visits from weekly data
$dailyVisits = array();
$monthlyVisitsFromWeekly = array();
foreach ($weeklyVisits as $visit) {
    $date = $visit['day'];
    $month = date('Y-m', strtotime($date)); // Extract year-month (e.g., "2025-05")
    $totalDailyVisits = 0;
    foreach ($visit['hours'] as $hour) {
        $totalDailyVisits += $hour['visits'];
    }
    $dailyVisits[$date] = $totalDailyVisits;
    $monthlyVisitsFromWeekly[$month] = (isset($monthlyVisitsFromWeekly[$month]) ? $monthlyVisitsFromWeekly[$month] : 0) + $totalDailyVisits;
}

// Prepare data for the overall visits chart
$visitLabels = array_keys($dailyVisits); // Use daily labels instead of monthly
sort($visitLabels); // Sort days chronologically
$visitData = array();
foreach ($visitLabels as $date) {
    $visitData[] = isset($dailyVisits[$date]) ? $dailyVisits[$date] : 0;
}

$selectedDayVisits = array();
foreach ($weeklyVisits as $visit) {
    if ($visit['day'] === '2025-05-25') {
        $selectedDayVisits = $visit['hours'];
        break;
    }
}
if (empty($selectedDayVisits) && !empty($weeklyVisits)) {
    $selectedDayVisits = $weeklyVisits[0]['hours'];
} elseif (empty($selectedDayVisits)) {
    $selectedDayVisits = array();
    error_log("Warning: No visitor data for May 25, 2025 in '$visitsJsonFile'.");
}

$mostPopularModel = !empty($modelLabels) ? array_reduce($modelLabels, function($a, $b) use ($salesByModel) {
    return (isset($salesByModel[$a]) && isset($salesByModel[$b]) && $salesByModel[$a] > $salesByModel[$b]) ? $a : $b;
}, $modelLabels[0]) : 'N/A';
$interestingFact = !empty($mostPopularModel) && $mostPopularModel !== 'N/A' ? "Eng ko'p sotilgan model \"$mostPopularModel\" bo'lib, umumiy {$salesByModel[$mostPopularModel]} ta mashina sotilgan." : 'Ma\'lumot topilmadi.';

$colors = array(
    'rgba(0, 123, 255, 0.7)',
    'rgba(255, 99, 132, 0.7)',
    'rgba(75, 192, 192, 0.7)',
    'rgba(255, 159, 64, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 205, 86, 0.7)',
    'rgba(54, 162, 235, 0.7)',
    'rgba(201, 203, 207, 0.7)',
    'rgba(255, 99, 71, 0.7)',
    'rgba(147, 112, 219, 0.7)',
    'rgba(60, 179, 113, 0.7)',
);
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UzAuto Motors Sales and Visits Forecast</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #e6f0fa,rgb(179, 241, 252));
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

        .chart-container-large {
            background: linear-gradient(90deg, #00C4FF,rgb(0, 21, 255));
            color: #fff;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
        }

        .chart-container-medium {
            background: linear-gradient(90deg, #00C4FF,rgb(0, 255, 225));
            color: #fff;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: calc(50% - 2rem);
        }

        .chart-title {
            font-size: 1.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        canvas {
            max-width: 100%;
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

            .chart-container-medium {
                width: 100%;
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
                    <li><a href="index.php">Bosh sahifa</a></li>
                    <li><a href="companies.php">Dilerlar</a></li>
                    <li><a href="statistics.php">Statistika</a></li>
                    <li><a href="tahlil.php" class="active">Bashorat</a></li>
                    <li><a href="qrcod.php">Davr</a></li>

                    <li><a href=""><img src="rasm/bot.png" alt="Telegram Bot" width="40" height="40" style="vertical-align: middle;"></a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-32 pb-8 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="chart-container-large">
                <h2 class="chart-title">Umumiy tahlil</h2>
                <p class="text-lg mb-4">Bu hisobot 2025 yilning yanvar-mart oylari uchun avtomobil sotuvlari, rang bo'yicha sotuvlar va tashriflar bashorati haqida ma'lumot beradi.</p>
                <p class="text-lg font-bold"><?php echo htmlspecialchars($interestingFact); ?></p>
            </div>

            <div class="flex flex-wrap -mx-2">
                <div class="chart-container-medium">
                    <h2 class="chart-title">Kelajakdagi sotuvlar</h2>
                    <?php if (empty($monthlyData)): ?>
                        <p class="text-lg text-red-200">Xatolik: Sotuv ma'lumotlari topilmadi.</p>
                    <?php else: ?>
                        <canvas id="monthlySalesChart"></canvas>
                    <?php endif; ?>
                </div>
                <div class="chart-container-medium">
                    <h2 class="chart-title">Model bo'yicha umumiy sotuvlar</h2>
                    <?php if (empty($modelLabels)): ?>
                        <p class="text-lg text-red-200">Xatolik: Model bo'yicha sotuv ma'lumotlari topilmadi.</p>
                    <?php else: ?>
                        <canvas id="salesByModelChart"></canvas>
                    <?php endif; ?>
                </div>
                <div class="chart-container-medium">
                    <h2 class="chart-title">Umumiy Tashriflar Bashorati</h2>
                    <?php if (empty($visitLabels)): ?>
                        <p class="text-lg text-red-200">Xatolik: Tashriflar bashorati ma'lumotlari topilmadi.</p>
                    <?php else: ?>
                        <canvas id="overallVisitsChart"></canvas>
                    <?php endif; ?>
                </div>
                <div class="chart-container-medium">
                    <h2 class="chart-title">25-May 2025 uchun soatlik tashriflar bashorati</h2>
                    <?php if (empty($selectedDayVisits)): ?>
                        <p class="text-lg text-red-200">Xatolik: 25-May 2025 uchun tashriflar ma'lumotlari topilmadi.</p>
                    <?php else: ?>
                        <canvas id="hourlyVisitsChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

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

    <!-- JavaScript -->
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

        // Chart JS Configurations
        document.addEventListener('DOMContentLoaded', function() {
            var monthlySalesChart = document.getElementById('monthlySalesChart');
            if (monthlySalesChart) {
                new Chart(monthlySalesChart, <?php
                    $datasets = array();
                    foreach (array_keys($productIdToModel) as $index => $modelId) {
                        $modelName = $productIdToModel[$modelId];
                        $data = array();
                        foreach ($monthlyData as $month) {
                            $data[] = isset($month[$modelName]) ? $month[$modelName] : 0;
                        }
                        if (array_sum($data) > 0) {
                            $datasets[] = array(
                                'label' => $modelName,
                                'data' => $data,
                                'backgroundColor' => $colors[$index % count($colors)],
                                'borderColor' => $colors[$index % count($colors)],
                                'borderWidth' => 1
                            );
                        }
                    }
                    echo json_encode(array(
                        'type' => 'bar',
                        'data' => array(
                            'labels' => array_column($monthlyData, 'month'),
                            'datasets' => $datasets
                        ),
                        'options' => array(
                            'responsive' => true,
                            'plugins' => array(
                                'legend' => array('position' => 'top', 'labels' => array('color' => '#fff')),
                                'tooltip' => array('backgroundColor' => '#fff', 'titleColor' => '#000', 'bodyColor' => '#000')
                            ),
                            'scales' => array(
                                'x' => array('title' => array('display' => true, 'text' => 'Oy', 'color' => '#fff'), 'ticks' => array('color' => '#fff')),
                                'y' => array('title' => array('display' => true, 'text' => 'Sotuvlar soni', 'color' => '#fff'), 'ticks' => array('color' => '#fff'))
                            )
                        )
                    ));
                ?>);
            }

            var salesByModelChart = document.getElementById('salesByModelChart');
            if (salesByModelChart) {
                new Chart(salesByModelChart, <?php echo json_encode(array(
                    'type' => 'bar',
                    'data' => array(
                        'labels' => $modelLabels,
                        'datasets' => array(array(
                            'label' => 'Sotuvlar',
                            'data' => $modelData,
                            'backgroundColor' => $colors[0],
                            'borderColor' => $colors[0],
                            'borderWidth' => 1
                        ))
                    ),
                    'options' => array(
                        'responsive' => true,
                        'plugins' => array(
                            'legend' => array('position' => 'top', 'labels' => array('color' => '#fff')),
                            'tooltip' => array('backgroundColor' => '#fff', 'titleColor' => '#000', 'bodyColor' => '#000')
                        ),
                        'scales' => array(
                            'x' => array('title' => array('display' => true, 'text' => 'Model', 'color' => '#fff'), 'ticks' => array('color' => '#fff')),
                            'y' => array('title' => array('display' => true, 'text' => 'Sotuvlar soni', 'color' => '#fff'), 'ticks' => array('color' => '#fff'))
                        )
                    )
                )); ?>);
            }

            var overallVisitsChart = document.getElementById('overallVisitsChart');
            if (overallVisitsChart) {
                new Chart(overallVisitsChart, <?php echo json_encode(array(
                    'type' => 'bar',
                    'data' => array(
                        'labels' => $visitLabels,
                        'datasets' => array(
                            array(
                                'label' => 'Kunlik tashriflar',
                                'data' => $visitData,
                                'backgroundColor' => $colors[0],
                                'borderColor' => $colors[0],
                                'borderWidth' => 1
                            )
                        )
                    ),
                    'options' => array(
                        'responsive' => true,
                        'plugins' => array(
                            'legend' => array('position' => 'top', 'labels' => array('color' => '#fff')),
                            'tooltip' => array('backgroundColor' => '#fff', 'titleColor' => '#000', 'bodyColor' => '#000')
                        ),
                        'scales' => array(
                            'x' => array('title' => array('display' => true, 'text' => 'Kun', 'color' => '#fff'), 'ticks' => array('color' => '#fff')),
                            'y' => array('title' => array('display' => true, 'text' => 'Tashriflar soni', 'color' => '#fff'), 'ticks' => array('color' => '#fff'))
                        )
                    )
                )); ?>);
            }

            var hourlyVisitsChart = document.getElementById('hourlyVisitsChart');
            if (hourlyVisitsChart) {
                new Chart(hourlyVisitsChart, <?php echo json_encode(array(
                    'type' => 'line',
                    'data' => array(
                        'labels' => array_column($selectedDayVisits, 'time'),
                        'datasets' => array(array(
                            'label' => 'Tashriflar',
                            'data' => array_column($selectedDayVisits, 'visits'),
                            'backgroundColor' => $colors[0],
                            'borderColor' => $colors[0],
                            'fill' => true,
                            'tension' => 0.4
                        ))
                    ),
                    'options' => array(
                        'responsive' => true,
                        'plugins' => array(
                            'legend' => array('position' => 'top', 'labels' => array('color' => '#fff')),
                            'tooltip' => array('backgroundColor' => '#fff', 'titleColor' => '#000', 'bodyColor' => '#000')
                        ),
                        'scales' => array(
                            'x' => array('title' => array('display' => true, 'text' => 'Soat', 'color' => '#fff'), 'ticks' => array('color' => '#fff')),
                            'y' => array('title' => array('display' => true, 'text' => 'Tashriflar soni', 'color' => '#fff'), 'ticks' => array('color' => '#fff'))
                        )
                    )
                )); ?>);
            }
        });
    </script>
</body>
</html>