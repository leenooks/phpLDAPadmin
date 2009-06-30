<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/login.php,v 1.29 2004/04/23 12:34:06 uugdave Exp $
 

/**
 * For servers whose auth_type is set to 'cookie' or 'session'. Pass me the login info
 * and I'll write two cookies, pla_login_dn_X and pla_pass_X
 * where X is the server_id. The cookie_time comes from config.php
 *
 * Note: this file uses ldap_connect() and ldap_bind() only for purposes
 *       of verifying the user-supplied DN and Password.
 *
 * Variables that come in as POST vars:
 *  - login_dn
 *  - login_pass 
 *  - server_id
 */

require 'common.php';

$server_id = $_POST['server_id'];
$dn = isset( $_POST['login_dn'] ) ? $_POST['login_dn'] : null;
$uid = isset( $_POST['uid'] ) ? $_POST['uid'] : null;
$pass = isset( $_POST['login_pass'] ) ? $_POST['login_pass'] : null;
$anon_bind = isset( $_POST['anonymous_bind'] ) && $_POST['anonymous_bind'] == 'on' ? true : false;
check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );

if( ! $anon_bind ) {
	strlen($pass) or pla_error( $lang['password_blank'] );
}

$auth_type = $servers[$server_id]['auth_type'];

if( $anon_bind ) {
	$dn = null;
	$pass = null;
}
// Checks if the login_attr option is enabled for this host,
// which allows users to login with a simple username like 'jdoe' rather
// than the fully qualified DN, 'uid=jdoe,ou=people,,dc=example,dc=com'.
elseif ( login_attr_enabled( $server_id ) ) {

    // Fake the auth_type of config to do searching. This way, the admin can specify
    // the DN to use when searching for the login_attr user.
	$servers[$server_id]['auth_type'] = 'config';

	// search for the "uid" first
    set_error_handler( 'temp_login_error_handler' );
	$ds = pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_bind'] );
    restore_error_handler();
	$search_base = isset( $servers[$server_id]['base'] ) && '' != trim( $servers[$server_id]['base'] ) ?
		$servers[$server_id]['base'] :
		try_to_get_root_dn( $server_id, $ds );
	if (!empty($servers[$server_id]['login_class'])) {
		$filter = '(&(objectClass='.$servers[$server_id]['login_class'].')('.$servers[$server_id]['login_attr'].'='.$uid.'))';
	} else {
		$filter = $servers[$server_id]['login_attr'].'='.$uid;
	}
	$sr = @ldap_search($ds, $search_base, $filter, array('dn'), 0, 1);
	$result = @ldap_get_entries($ds, $sr);
	$dn = isset( $result[0]['dn'] ) ? $result[0]['dn'] : false;
	if( ! $dn ) {
		pla_error( $lang['bad_user_name_or_password'] );
	}

    // restore the original auth_type
	$servers[$server_id]['auth_type'] = $auth_type;
}

// We fake a 'config' server config to omit duplicated code 
$auth_type = $servers[$server_id]['auth_type'];
$servers[$server_id]['auth_type'] = 'config';
$servers[$server_id]['login_dn'] = $dn;
$servers[$server_id]['login_pass'] = $pass;

// verify that the login is good 
$ds = pla_ldap_connect( $server_id );

if ( ! $ds ) {
	if( $anon_bind )
		pla_error( $lang['could_not_bind_anon'] );
	else
		pla_error( $lang['bad_user_name_or_password'] );
} 

$servers[$server_id]['auth_type'] = $auth_type;
set_login_dn( $server_id, $dn, $pass, $anon_bind ) or pla_error( $lang['could_not_set_cookie'] );

initialize_session_tree();
$_SESSION['tree'][$server_id] = array();
$_SESSION['tree_icons'][$server_id] = array();

session_write_close();

include realpath( 'header.php' );
?>

<script language="javascript">
	<?php if( $anon_bind && anon_bind_tree_disabled() )  { ?>
	
		parent.location.href='search.php?server_id=<?php echo $server_id; ?>'

	<?php } else {  ?>

		parent.left_frame.location.reload();

	<?php } ?>
</script>

<center>
<br />
<br />
<br />
<?php echo sprintf( $lang['successfully_logged_in_to_server'], 
    			htmlspecialchars( $servers[$server_id]['name'] ) ); ?><br />
<?php if( $anon_bind ) { ?>
	(<?php echo $lang['anonymous_bind']; ?>)	
<?php } ?>
<br />
</center>

</body>
</html>

<?php
/**
 * Only gets called when we fail to login.
 */
function temp_login_error_handler( $errno, $errstr, $file, $lineno )
{
    global $lang;
	if( 0 == ini_get( 'error_reporting' ) || 0 == error_reporting() )
			return;
    pla_error( $lang['could_not_connect'] . "<br /><br />" . htmlspecialchars( $errstr ) );
}
?>

