<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/index.php,v 1.39.2.3 2005/10/17 10:03:38 wurley Exp $

/**
 * @package phpLDAPadmin
 * @todo: Move config.php syntax error processing to earlier.
 */

/*******************************************
<pre>

If you are seeing this in your browser,
PHP is not installed on your web server!!!

</pre>
*******************************************/

/**
 * We will perform some sanity checking here, since this file is normally loaded first when users
 * first setup PLA.
 */
define('LIBDIR','../lib/');
ini_set('display_errors',1);
error_reporting(E_ALL);

# General functions needed to proceed (pla_ldap_search(), pla_error(), get_object_attrs(), etc.)
ob_start();
if (! file_exists(LIBDIR.'functions.php')) {
	ob_end_clean();
	die("Fatal error: Required file 'functions.php' does not exist.");
}

if (! is_readable(LIBDIR.'functions.php')) {
	ob_end_clean();
	die("Cannot read the file 'functions.php' its permissions are too strict.");
}
require LIBDIR.'functions.php';
$config_file = CONFDIR.'config.php';
ob_end_clean();

/* Helper functions.
 * Our required helper functions are defined in functions.php
 */
foreach ($pla_function_files as $file_name ) {
	if (! file_exists($file_name))
		pla_error("Fatal error: Required file '$file_name' does not exist.");

	if (! is_readable($file_name))
		pla_error( "Fatal error: Cannot read the file '$file_name', its permissions are too strict." );

	ob_start();
	require $file_name;
	ob_end_clean();
}

# Configuration File check
if (! file_exists($config_file)) {
?>

<html>
<head>
	<title>phpLDAPadmin - <?php echo pla_version(); ?></title>
	<link rel="stylesheet" href="style.css" />
</head>

<body>
<h3 class="title">Configure phpLDAPadmin</h1>
<br />
<br />

<center><?php echo $lang['need_to_configure']; ?></center>
</body>
</html>

<?php
	die();

} elseif (! is_readable($config_file)) {
	pla_error(sprintf('Fatal error: Cannot read your configuration file "%s", its permissions are too strict.',$config_file));
}

# Now read in config_default.php, which also reads in config.php
require LIBDIR.'config_default.php';

if (check_config()) {
	print '<?xml version="1.0" encoding="utf-8"?>';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN"
	"http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="no-NO">

<?php if ($pagetitle = $config->GetValue('appearance','page_title')) { ?>
<head><title>phpLDAPadmin (<?php echo pla_version(); ?>) - <?php echo $pagetitle; ?></title></head>
<?php } else { ?>
<head><title>phpLDAPadmin - <?php echo pla_version(); ?></title></head>
<?php } ?>

<frameset cols="<?php echo $config->GetValue('appearance','tree_width'); ?>,*">
	<frame src="tree.php" name="left_frame" id="left_frame" />
	<frame src="welcome.php" name="right_frame" id="right_frame" />
</frameset>

</html>

<?php }

/*
 * Makes sure that the config file is properly setup and
 * that your install of PHP can handle LDAP stuff.
 */
function check_config() {
	global $lang, $config_file;

	# Make sure their PHP version is current enough
	if (strcmp(phpversion(),REQUIRED_PHP_VERSION) < 0) {
		pla_error(sprintf('phpLDAPadmin requires PHP version %s or greater. You are using %s',
			REQUIRED_PHP_VERSION,phpversion()));
	}

	# Make sure this PHP install has all our required extensions
	if (! extension_loaded('ldap')) {
		pla_error( "Your install of PHP appears to be missing LDAP support. Please install " .
			"LDAP support before using phpLDAPadmin. (Don't forget to restart your web server afterwards)");
		return false;
	}

	# Make sure that we have php-xml loaded.
	if (! function_exists('xml_parser_create')) {
		pla_error( "Your install of PHP appears to be missing XML support. Please install " .
			"XML support before using phpLDAPadmin. (Don't forget to restart your web server afterwards)");
		return false;
	}
 
	# Make sure their session save path is writable, if they are using a file system session module, that is.
	if ( ! strcasecmp("Files",session_module_name() && ! is_writable(realpath(session_save_path())))) {
		pla_error( "Your PHP session configuration is incorrect. Please check the value of session.save_path
			in your php.ini to ensure that the directory specified there exists and is writable.
			The current setting of \"". session_save_path() . "\" is un-writable by the web server.");
		return false;
	}

	/* check for syntax errors in config.php */
	# capture the result of including the file with output buffering
	ob_start();
	include $config_file;
	$str = ob_get_contents();
	ob_end_clean();

	if( $str && false !== strpos( $str, 'error' ) ) {
		$str = strip_tags( $str );
		$matches = array();
		preg_match( "/on line (\d+)/", $str, $matches );
		$line_num = $matches[1];
		$file = file($config_file);
		?>

	<html>
	<head>
	<title>phpLDAPadmin Config File Error</title>
	<link rel="stylesheet" href="style.css" />
	</head>

	<body>
	<h3 class="title">Config file error</h3>
	<h3 class="subtitle">Syntax error on line <?php echo $line_num; ?></h3>

	<center>
	Looks like your config file has a syntax error on line <?php echo $line_num; ?>.
	Here is a snippet around that line
	<br />
	<br />
	<div style="text-align: left; margin-left: 80px; margin-right: 80px; border: 1px solid black; padding: 10px;">
	<tt>

		<?php for( $i=$line_num-9; $i<$line_num+5; $i++ ) {
			if( $i+1 == $line_num )
				echo "<div style=\"color:red;background:#fdd\">";

			if( $i < 0 )
				continue;
			echo "<b>" . ($i+1) . "</b>: " . htmlspecialchars($file[ $i ]) . "<br />";

			if( $i+1 == $line_num )
				echo "</div>";
		}
		?>

	</tt>

	</div>
	<br />
	Hint: Sometimes these errors are caused by lines <b>preceding</b> the line reported.
	</body>
	</html>

		<?php return false;
	}

	/* check the existence of the servers array */
	require $config_file;

	if( ! isset($ldapservers) || count($ldapservers->GetServerList()) == 0) {
		pla_error( "Your config.php is missing Server Definitions
			Please see the sample file config.php.example ", false );
		return false;
	}

	# @todo: Implement this and fix all the tests.
/*
	if ( ! count($ldapservers->GetServerList())) {
		pla_error( "None of the " . count($servers) . " servers in your \$servers configuration is
		active in config.php. At least one of your servers must set the 'host' directive.
		Example: <br><pre>\$servers['host'] = \"ldap.example.com\";<br></pre>
		phpLDAPadmin cannot proceed util you correct this.", false );
		return false;
	}

	// Check that 'base' is present on all serve entries
	foreach( $servers as $id => $server ) {
		if( isset( $server['host'] ) && isset( $server['name'] ) )
			isset( $server['base'] )
				or pla_error ( "Your configuration has an error. You omitted the 'base' directive
					on server number $id. Your server entry must have a 'base' directive
					even if it's empty ('')." );
	}

	// Check each of the servers in the servers array
	foreach( $servers as $id => $server ) {
		if( isset( $server['host'] ) ) {

			// Make sure they specified an auth_type
			if( ! isset( $server['auth_type'] ) ) {
				pla_error( "Your configuration has an error. You omitted the 'auth_type' directive on server number $id
				'auth_type' must be set, and it must be one of 'config', 'cookie', or 'session'.", false );
				return false;
			}

			// Make sure they specified a correct auth_type
			if( ! in_array( $server['auth_type'], array( 'config', 'cookie', 'session' ) ) ) {
				global $lang;
				pla_error( sprintf( $lang['error_auth_type_config'], htmlspecialchars( $server['auth_type'] ) ) );
				return false;
			}
		}
	}
*/

	return true;
}
?>
