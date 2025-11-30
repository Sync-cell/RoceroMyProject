
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Under Maintenance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .maintenance-container {
            text-align: center;
            background: #ffffff;
            padding: 60px 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .maintenance-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        h1 {
            color: #2c3e50;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .subtitle {
            color: #7f8c8d;
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .maintenance-info {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
            border-left: 4px solid #667eea;
            text-align: left;
        }

        .info-item {
            margin: 10px 0;
            color: #2c3e50;
            font-size: 14px;
        }

        .info-label {
            font-weight: 600;
            color: #667eea;
        }

        .countdown {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }

        .countdown-item {
            text-align: center;
        }

        .countdown-number {
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
        }

        .countdown-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-top: 6px;
        }

        .message {
            color: #555;
            font-size: 15px;
            line-height: 1.8;
            margin: 20px 0;
        }

        .contact-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #ffc107;
        }

        .contact-text {
            color: #856404;
            font-size: 14px;
        }

        .ip-info {
            background: #f0f4ff;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 12px;
            color: #667eea;
            border: 1px solid #667eea;
        }

        .denied-banner {
            background: #ffcdd2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #c62828;
            font-weight: 600;
            border: 1px solid #ef5350;
        }

        @media (max-width: 480px) {
            .maintenance-container {
                padding: 40px 25px;
            }

            h1 {
                font-size: 26px;
            }

            .maintenance-icon {
                font-size: 60px;
            }

            .countdown {
                gap: 10px;
            }

            .countdown-number {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="maintenance-container">
    <div class="denied-banner">
        🚫 Access Denied - System Under Maintenance
    </div>

    <div class="maintenance-icon">🔧</div>
    <h1>System Under Maintenance</h1>
    <p class="subtitle">We're making things better for you!</p>

    <p class="message">
        Our Task Manager system is currently undergoing scheduled maintenance. 
        We apologize for any inconvenience and appreciate your patience.
    </p>

    <?php if (!empty($status['toggled_at'])): ?>
        <div class="maintenance-info">
            <div class="info-item">
                <span class="info-label">🕐 Maintenance Started:</span>
                <br><?= esc($status['toggled_at']) ?>
            </div>
            <?php if (!empty($status['toggled_by'])): ?>
                <div class="info-item">
                    <span class="info-label">👤 Initiated By:</span>
                    <br><?= esc($status['toggled_by']) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="countdown">
        <div class="countdown-item">
            <div class="countdown-number" id="hours">00</div>
            <div class="countdown-label">Hours</div>
        </div>
        <div class="countdown-item">
            <div class="countdown-number" id="minutes">00</div>
            <div class="countdown-label">Minutes</div>
        </div>
        <div class="countdown-item">
            <div class="countdown-number" id="seconds">00</div>
            <div class="countdown-label">Seconds</div>
        </div>
    </div>

    <?php if (!empty($userIp)): ?>
        <div class="ip-info">
            🔍 Your IP Address: <strong><?= esc($userIp) ?></strong><br>
            <span style="font-style:italic">(This IP is not authorized during maintenance)</span>
        </div>
    <?php endif; ?>

    <div class="contact-info">
        <p class="contact-text">
            📧 For urgent assistance, please contact our support team.
        </p>
    </div>
</div>

<script>
    function updateCountdown() {
        const now = new Date();
        const maintenanceTime = new Date();
        maintenanceTime.setHours(maintenanceTime.getHours() + 2);

        const diff = maintenanceTime - now;

        if (diff > 0) {
            const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const minutes = Math.floor((diff / (1000 * 60)) % 60);
            const seconds = Math.floor((diff / 1000) % 60);

            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);

    setTimeout(() => {
        location.reload();
    }, 10000);
</script>

</body>
</html>