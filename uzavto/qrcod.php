<?php
$csvFile = './data/clients_log.csv';

function processClientLog($data) {
    $serviceCounts = array();
    foreach ($data as $row) {
        if (isset($row['Xizmat turi']) && isset($row['Status']) && $row['Status'] === 'active') {
            $service = $row['Xizmat turi'];
            $serviceCounts[$service] = isset($serviceCounts[$service]) ? $serviceCounts[$service] + 1 : 1;
        }
    }
    ksort($serviceCounts); // Sort by service name
    return $serviceCounts;
}

$clientData = array();
if (file_exists($csvFile)) {
    if (($handle = fopen($csvFile, 'r')) !== false) {
        $header = fgetcsv($handle, 1000, ","); // Adjust delimiter if needed (e.g., comma)
        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            if (count($row) === count($header)) {
                $clientData[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
} else {
    error_log("Error: CSV file '$csvFile' not found.");
    $clientData = array();
}

$serviceCounts = processClientLog($clientData);
$serviceLabels = array_keys($serviceCounts);
$serviceData = array_values($serviceCounts);

// Calculate differences between consecutive service counts
$differences = [];
for ($i = 1; $i < count($serviceData); $i++) {
    $diff = $serviceData[$i] - $serviceData[$i - 1];
    $differences[$serviceLabels[$i]] = $diff;
}

// Fallback data if CSV is empty or not found (based on your image)
if (empty($serviceLabels)) {
    $serviceLabels = ['Avtomabil xarid qilish', 'Maslahat olish', 'Texnik ko\'rik'];
    $serviceData = [9, 3, 10]; // Counts from the image
    $differences = ['', -6, 7]; // Differences calculated: 9-3=-6, 10-3=7
}

$colors = array(
    'rgba(0, 123, 255, 0.7)',  // Blue
    'rgba(255, 99, 132, 0.7)', // Red
    'rgba(75, 192, 192, 0.7)', // Teal
);
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Activity Chart</title>
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

        .chart-container-medium {
            background: linear-gradient(90deg, #00C4FF, #00FFF7);
            color: #fff;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: calc(50% - 2rem);
            max-width: 600px;
            text-align: center;
        }

        .chart-title {
            font-size: 1.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        }

        canvas {
            max-width: 100%;
        }

        .difference-table {
            margin-top: 1rem;
            font-size: 1rem;
            color: #fff;
        }

        .difference-table p {
            margin: 0.5rem 0;
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
                margin: 1rem;
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
                    <li><a href="companies.php">Korxonalar</a></li>
                    <li><a href="statistics.php">Statistika</a></li>
                    <li><a href="tahlil.php">Bashorat</a></li>
                    <li><a href="qrcod.php" class="active">Davr</a></li>
                    <li><a href="#"><img src="rasm/bot.png" alt="Telegram Bot" width="40" height="40" style="vertical-align: middle;"></a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-32 pb-8 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="chart-container-medium">
                <h2 class="chart-title">Xizmatlar Faolligi (25-May 2025)</h2>
                <?php if (empty($serviceLabels) && empty($serviceData)): ?>
                    <p class="text-lg text-red-200">Xatolik: Ma'lumotlar topilmadi.</p>
                <?php else: ?>
                    <canvas id="serviceActivityChart"></canvas>
                    <div class="difference-table">
                        <h3>Farqlar</h3>
                        <?php 
                        $prevLabel = reset($serviceLabels);
                        foreach ($differences as $service => $diff) {
                            if ($diff !== '') {
                                echo "<p>" . htmlspecialchars($service) . ": " . ($diff > 0 ? '+' : '') . $diff . "</p>";
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>
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
        // Logo carousel
        const logos = document.querySelectorAll('.logo-img');
        let currentLogo = 0;

        function rotateLogos() {
            logos[currentLogo].classList.remove('active');
            currentLogo = (currentLogo + 1) % logos.length;
            logos[currentLogo].classList.add('active');
        }

        setInterval(rotateLogos, 3000);

        // Chart JS Configuration
        document.addEventListener('DOMContentLoaded', function() {
            var serviceActivityChart = document.getElementById('serviceActivityChart');
            if (serviceActivityChart) {
                new Chart(serviceActivityChart, <?php echo json_encode(array(
                    'type' => 'bar',
                    'data' => array(
                        'labels' => $serviceLabels,
                        'datasets' => array(
                            array(
                                'label' => 'Xizmatlar soni',
                                'data' => $serviceData,
                                'backgroundColor' => $colors,
                                'borderColor' => $colors,
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
                            'x' => array('title' => array('display' => true, 'text' => 'Xizmat turi', 'color' => '#fff'), 'ticks' => array('color' => '#fff')),
                            'y' => array('title' => array('display' => true, 'text' => 'Foydalanuvchilar soni', 'color' => '#fff'), 'ticks' => array('color' => '#fff'))
                        )
                    )
                )); ?>);
            }
        });
    </script>
</body>
</html>