<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Drive Eastern - Your Ride Partner</title>

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

  <!-- Font and Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.lordicon.com/lordicon.js"></script>

  <style>
    :root {
      --primary-yellow: #ffcc00;
      --primary-yellow-dark: #e6b800;
      --primary-black: #1a1a1a;
      --primary-gray: #4a4a4a;
      --card-bg: #f9f9f9;
      --border-radius: 14px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
    }

    body {
      background: url('https://images.unsplash.com/photo-1570129477492-45c003edd2be') no-repeat center center fixed;
      background-size: cover;
      padding: 5rem 1.5rem 3rem;
      text-align: center;
      position: relative;
    }

    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background: rgba(255,255,255,0.9);
      z-index: -1;
    }

    h1 {
      font-size: 2.8rem;
      font-weight: 800;
      color: var(--primary-black);
    }

    p.subtitle {
      margin: 1rem auto 2rem;
      color: var(--primary-gray);
      max-width: 600px;
    }

    .btn-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 2rem;
      max-width: 900px;
      margin: 0 auto;
    }

    .card {
      background: var(--card-bg);
      padding: 2rem;
      border-radius: var(--border-radius);
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      text-decoration: none;
      color: inherit;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }

    .card:hover {
      transform: translateY(-6px);
      border-color: var(--primary-yellow);
    }

    .card span {
      display: block;
      font-weight: 600;
      margin-top: 1rem;
    }

    footer {
      margin-top: 4rem;
      color: #666;
      font-size: 0.9rem;
    }

    .flag-btn {
      position: absolute;
      top: 1rem;
      right: 1rem;
      border: 2px solid var(--primary-yellow-dark);
      border-radius: 6px;
      padding: 2px;
      background: white;
      cursor: pointer;
    }

    .flag-btn img {
      width: 40px;
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.7);
      justify-content: center;
      align-items: center;
      z-index: 999;
    }

    .modal.active {
      display: flex;
    }

    .modal-content {
      width: 90%;
      max-width: 800px;
      height: 80%;
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      position: relative;
      display: flex;
      flex-direction: column;
    }

    #map {
      flex: 1;
    }

    .close-btn {
      position: absolute;
      top: 12px;
      right: 12px;
      background: var(--primary-yellow);
      color: #000;
      border: none;
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      transition: background 0.3s ease;
      z-index: 1000;
    }

    .close-btn:hover {
      background: var(--primary-yellow-dark);
    }
  </style>
</head>
<body>

  <!-- Flag Button -->
  <button class="flag-btn" id="openModal" aria-label="Open Eastern Province Map">
    <img src="https://flagcdn.com/w40/lk.png" alt="Sri Lanka Flag">
  </button>

  <!-- Hero Content -->
  <h1>Welcome to Drive Eastern</h1>
  <p class="subtitle">Seamless travel, trusted drivers, and fast booking</p>

  <!-- Navigation -->
  <div class="btn-container">
    <a href="register.php" class="card">
      <lord-icon src="https://cdn.lordicon.com/kthelypq.json" trigger="hover" colors="primary:#121331,secondary:#ffcc00" style="width:70px;height:70px"></lord-icon>
      <span>Register</span>
    </a>
    <a href="login.php" class="card">
      <lord-icon src="https://cdn.lordicon.com/rqqkvjqf.json" trigger="hover" colors="primary:#121331,secondary:#ffcc00" style="width:70px;height:70px"></lord-icon>
      <span>Login</span>
    </a>
    <a href="about.php" class="card">
      <lord-icon src="https://cdn.lordicon.com/wxnxiano.json" trigger="hover" colors="primary:#121331,secondary:#ffcc00" style="width:70px;height:70px"></lord-icon>
      <span>About Us</span>
    </a>
    <a href="help.php" class="card">
      <lord-icon src="https://cdn.lordicon.com/ljvjsnvh.json" trigger="hover" colors="primary:#121331,secondary:#ffcc00" style="width:70px;height:70px"></lord-icon>
      <span>Message</span>
    </a>
  </div>

  <!-- Modal -->
  <div id="mapModal" class="modal">
    <div class="modal-content">
      <button class="close-btn" id="closeModal">Close</button>
      <div id="map"></div>
    </div>
  </div>

  <footer>&copy; <?php echo date("Y"); ?> Drive Eastern. All rights reserved.</footer>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  <script>
    const modal = document.getElementById('mapModal');
    const openBtn = document.getElementById('openModal');
    const closeBtn = document.getElementById('closeModal');

    openBtn.onclick = () => {
      modal.classList.add('active');
      if (!window.mapInitialized) {
        setTimeout(initMap, 100);
        window.mapInitialized = true;
      }
    };

    closeBtn.onclick = () => modal.classList.remove('active');

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        modal.classList.remove('active');
      }
    });

    function initMap() {
      const map = L.map('map').setView([7.8, 81.8], 8);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      const easternBounds = [
        [7.03, 81.19], // SW corner
        [8.41, 82.35]  // NE corner
      ];

      // Yellow border rectangle (Eastern Province)
      L.rectangle(easternBounds, {
        color: "#ffcc00",
        weight: 3,
        fillOpacity: 0
      }).addTo(map);

      map.fitBounds(easternBounds);
    }
  </script>

</body>
</html>
