<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UzAuto Motors - Statistika</title>
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

        .main-content {
            padding: 120px 20px 20px;
            text-align: center;
            flex: 1;
        }

        .main-content h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: #333;
        }

        .year-tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            background: #e0f7fa;
            padding: 10px;
            border-radius: 5px;
        }

        .year-tab {
            padding: 10px 20px;
            background: #34495e;
            color: #fff;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .year-tab.active {
            background: #3498db;
        }

        .year-tab:hover {
            background: #2c3e50;
        }

        .powerbi-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: linear-gradient(90deg, #00C4FF, #00FFD1);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .powerbi-image {
            width: 100%;
            max-height: 600px;
            object-fit: contain;
            border-radius: 10px;
        }

        .chart-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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
        }

        footer .social-links a {
            display: inline-block;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        footer .social-links a:hover {
            transform: scale(1.2);
        }

        @media (max-width: 768px) {
            body {
                font-size: 14px;
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

            .main-content {
                padding: 80px 15px 15px;
            }

            .powerbi-container, .chart-container {
                padding: 15px;
                max-width: 100%;
            }

            .year-tabs {
                flex-wrap: wrap;
                justify-content: center;
            }

            .powerbi-image {
                max-height: 400px;
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

            .main-content {
                padding: 60px 15px 15px;
            }

            .main-content h2 {
                font-size: 24px;
            }

            .powerbi-image {
                max-height: 300px;
            }

            .year-tabs {
                flex-direction: column;
                align-items: center;
            }

            .year-tab {
                margin: 5px 0;
            }

            footer p {
                font-size: 10px;
            }

            footer .social-links a {
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
                    <li><a href="companies.php">Dilerlar</a></li>
                    <li><a href="statistics.php" class="active">Statistika</a></li>
                    <li><a href="tahlil.php">Bashorat</a></li>
                    <li><a href="qrcod.php">Davr</a></li>
                    <li><a href=""><img src="rasm/bot.png" alt="Telegram Bot" width="40" height="40" style="vertical-align: middle;"></a></li>

                </ul>
            </nav>
        </div>
    </header>

    <!-- Telegram Link -->
    <a href="telegram.php" class="telegram-link">Telegram Bot</a>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Power BI dagi tahlil</h2>
        <div class="year-tabs" id="year-tabs">
            <div class="year-tab active" data-year="2023">2023</div>
            <div class="year-tab" data-year="2024">2024</div>
            <div class="year-tab" data-year="2025">2025</div>
        </div>
        <div class="powerbi-container" id="powerbi-container">
            <img src="/rasm/power.png" alt="Power BI Report" class="powerbi-image">
        </div>
        <div class="chart-container">
            <h3>Avtomobil sotuvlari statistikasi</h3>
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>© 2025 UzAuto Motors. Barcha huquqlar himoyalangan.</p>
        <div class="social-links">
            <a href="telegram.php" title="Telegram Bot" style="background: #0088cc; border-radius: 50%; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px;">
                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/telegram.svg" alt="Telegram" width="28" height="28" style="display: block; filter: invert(0.5) sepia(1) saturate(5) hue-rotate(170deg);">
            </a>
            <a href="https://instagram.com/uzautomotors" target="_blank" title="Instagram" style="background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%); border-radius: 50%; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px;">
                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/instagram.svg" alt="Instagram" width="25" height="25" style="display: block;">
            </a>
            <a href="https://facebook.com/uzautomotors" target="_blank" title="Facebook" style="background: #1877f3; border-radius: 50%; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px;">
                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/facebook.svg" alt="Facebook" width="28" height="28" style="display: block;">
            </a>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Logo carousel
        const logos = document.querySelectorAll('.logo-img');
        let logoIndex = 0;
        setInterval(() => {
            logos.forEach((img, idx) => img.classList.toggle('active', idx === logoIndex));
            logoIndex = (logoIndex + 1) % logos.length;
        }, 2000);

        // Chart.js data
        const salesData = {
            '2023': [1200, 1500, 1700, 1400, 1800, 2000, 2100, 1900, 2200, 2300, 2100, 2500],
            '2024': [1300, 1600, 1800, 1500, 1900, 2100, 2200, 2000, 2300, 2400, 2200, 2600],
            '2025': [1400, 1700, 1900, 1600, 2000, 2200, 2300, 2100, 2400, 2500, 2300, 2700]
        };

        const ctx = document.getElementById('salesChart').getContext('2d');
        let chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 'Iyul', 'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'],
                datasets: [{
                    label: 'Sotuvlar soni',
                    data: salesData['2023'],
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderColor: '#3498db',
                    borderWidth: 3,
                    pointBackgroundColor: '#00bcd4',
                    pointRadius: 5,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    title: { display: true, text: 'UzAuto Motors sotuv statistikasi (2023)' }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 500 } }
                }
            }
        });

        // Year tabs for chart update
        const yearTabs = document.querySelectorAll('.year-tab');
        yearTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                yearTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const year = tab.getAttribute('data-year');
                chart.data.datasets[0].data = salesData[year];
                chart.options.plugins.title.text = `UzAuto Motors sotuv statistikasi (${year})`;
                chart.update();
            });
        });
    </script>
</body>
</html>