<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>About Us | Drive Eastern</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }

        :root {
            --primary-yellow: #ffcc00;
            --primary-yellow-dark: #e6b800;
            --primary-black: #1a1a1a;
            --primary-gray: #4a4a4a;
            --background: #ffffff;
            --card-bg: #f9f9f9;
            --shadow-light: rgba(0, 0, 0, 0.05);
            --shadow-hover: rgba(0, 0, 0, 0.15);
            --border-radius: 14px;
            --font-family: 'Inter', sans-serif;
            --transition: 0.3s ease;
        }

        body {
            margin: 0;
            font-family: var(--font-family);
            background: var(--background);
            color: var(--primary-black);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 70px;
        }

        header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 70px;
            background: var(--primary-black);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            padding: 0 2rem;
            z-index: 1000;
            border-bottom: 1px solid #444;
            box-shadow: 0 3px 10px var(--shadow-light);
            color: var(--primary-yellow);
            font-weight: 700;
            font-size: 1.8rem;
            user-select: none;
        }

        header .logo {
            height: 40px;
            width: auto;
            user-select: none;
        }

        .back-button {
            text-align: center;
            margin: 3rem 0 0;
        }

        .back-button a button {
            background: var(--primary-yellow);
            color: var(--primary-black);
            border: none;
            padding: 14px 38px;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: var(--border-radius);
            cursor: pointer;
            box-shadow: 0 6px 14px rgba(255, 204, 0, 0.4);
            transition: background var(--transition), box-shadow var(--transition), transform 0.25s ease;
            letter-spacing: 1.2px;
        }

        .back-button a button:hover,
        .back-button a button:focus-visible {
            background: var(--primary-yellow-dark);
            box-shadow: 0 8px 20px rgba(230, 184, 0, 0.5);
            outline: none;
            transform: translateY(-3px);
        }

        .container {
            max-width: 900px;
            margin: 3rem auto 5rem;
            padding: 2.5rem 3rem;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 3px 10px var(--shadow-light);
        }

        h2 {
            color: var(--primary-yellow);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 6px;
        }

        h2::after {
            content: "";
            position: absolute;
            width: 60px;
            height: 4px;
            background: var(--primary-yellow-dark);
            bottom: 0;
            left: 0;
            border-radius: 4px;
        }

        p {
            font-size: 1.125rem;
            color: var(--primary-black);
        }

        .founder {
            display: flex;
            align-items: center;
            margin-top: 3rem;
            border-top: 1px solid #ddd;
            padding-top: 2rem;
            gap: 1.5rem;
        }

        .founder img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 4px solid var(--primary-yellow);
            box-shadow: 0 4px 14px rgba(255, 204, 0, 0.3);
            transition: transform var(--transition);
        }

        .founder-details {
            max-width: 600px;
        }

        .founder-details h3 {
            margin: 0 0 0.5rem;
            font-size: 1.4rem;
            color: var(--primary-black);
        }

        .founder-details p {
            font-size: 1rem;
            color: var(--primary-gray);
            margin: 0.25rem 0;
        }

        footer {
            background: var(--primary-black);
            color: #d1d1d1;
            text-align: center;
            padding: 1.5rem;
            font-size: 0.95rem;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            header {
                font-size: 1.5rem;
            }
            .container {
                margin: 2rem 1rem 4rem;
                padding: 2rem 1.5rem;
            }
            .founder {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .founder img {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>

<header>
    <img src="https://img.icons8.com/fluency/96/taxi.png" alt="Drive Eastern Logo" class="logo" />
    <span>About Drive Eastern</span>
</header>

<div class="back-button">
    <a href="index.php">
        <button>← Back to Home</button>
    </a>
</div>

<div class="container">
    <section>
        <h2>About Us</h2>
        <p>
            Drive Eastern is your trusted local taxi service in the province. We aim to provide safe, affordable, and timely rides
            for passengers while offering flexible work for our drivers.
        </p>
        <p>
            Our system connects passengers with available drivers quickly and efficiently, making transportation easy and reliable.
        </p>
    </section>

    <section>
        <h2>Founder</h2>
        <div class="founder">
            <img src="aflal.jpg" alt="Founder and CEO - John Doe" />
            <div class="founder-details">
                <h3>Mohamed Aflal</h3>
                <p><strong>Founder & CEO</strong></p>
                <p>Aflal started Drive Eastern in 2025 with the vision to revolutionize local transport services by leveraging
                    technology to connect drivers and passengers in a seamless, reliable way.</p>
            </div>
        </div>
    </section>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> Drive Eastern. All rights reserved.
</footer>

</body>
</html>
