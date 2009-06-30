<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/Attic/tree.php,v 1.88.4.10 2007/03/18 03:16:06 wurley Exp $

/**
 * This script displays the LDAP tree for all the servers that you have
 * in config.php.
 *
 * We read the session variable 'tree' to know which dns are expanded or collapsed.
 * No query string parameters are expected, however, you can use a '#' offset to
 * scroll to a given dn. The syntax is tree.php#<server_id>_<rawurlencoded dn>, so
 * if I wanted to scroll to dc=example,dc=com for server 3, the URL would be:
 *
 *	tree.php#3_dc%3Dexample%2Cdc%3Dcom
 *
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 */
/**
 */

require './common.php';
no_expire_header();

# This allows us to display large sub-trees without running out of time.
@set_time_limit( 0 );

$recently_timed_out_servers = array();
if (isset($_SESSION['activity']['rightframe_server_id']))
	$rightframe_server_id = $_SESSION['activity']['rightframe_server_id'];
$rightframerefresh = false;

foreach ($ldapservers->GetServerList() as $server_id) {
	$ldapserver = $ldapservers->Instance($server_id);

	# Test to see if we should log out the user due to the timeout.
	if ($ldapserver->haveAuthInfo() && $ldapserver->auth_type != 'config') {
		/* If time out value has been reached:
		   - log out user
		   - put $server_id in array of recently timed out servers */
		if (session_timed_out($ldapserver)) {
			array_push($recently_timed_out_servers, $server_id);

		        # If $ldapserver->server_id equal $rightframe_server_id load timeout page on right frame
			if ($ldapserver->server_id == $rightframe_server_id)
				$rightframerefresh = true;

		/* Otherwise calculate a new refresh value. If the timeout value is less than the previous
		    $meta_refresh_variable value set $meta_refresh_variable to $ldapserver->session_timeout */
		} else
			$meta_refresh_variable = $ldapserver->session_timeout*60;
	}
}

include './header.php';

echo '<body>';

if ($rightframerefresh) {
?>
		<script type="text/javascript" language="javascript">
		<!--
		parent.right_frame.location.href = 'timeout.php?server_id=<?php echo $rightframe_server_id; ?>';
		//-->
		</script>
<?php
}
?>

<!-- # PHP layers menu. -->
<script type="text/javascript" language="javascript" src="js/phplayersmenu/libjs/layersmenu-browser_detection.js"></script>
<script type="text/javascript" language="javascript" src="js/phplayersmenu/libjs/layerstreemenu-cookies.js"></script>

<?php
printf('<h3 class="subtitle" style="margin:0px">phpLDAPadmin - %s</h3>',pla_version());

echo "\n\n";
echo '<!-- Links at the top of the tree viewer -->';
echo '<table class="edit_dn_menu" width=100%><tr>';
printf('<td><img src="images/home.png" alt="%s" /></td>',_('Home'));
printf('<td width=50%%><span style="white-space: nowrap;"><a href="welcome.php" target="right_frame">%s</a></span></td>',_('Home'));
printf('<td><img src="images/trash.png" alt="%s" /></td>',_('Purge caches'));
printf('<td width=50%%><span style="white-space: nowrap;"><a href="purge_cache.php" target="right_frame" title="%s">%s</a></span></td>',_('Purge all cached data in phpLDAPadmin, including server schemas.'),_('Purge caches'));
echo '</tr><tr>';

if (! $config->GetValue('appearance','hide_configuration_management')) {
	printf('<td><img src="images/light.png" alt="%s" /></td>',_('light'));
	printf('<td width=50%%><span style="white-space: nowrap;"><a href="%s" target="new">%s</a></span></td>',get_href('add_rfe'),_('Request feature'));
	printf('<td><img src="images/bug.png" alt="%s" /></td>',_('bug'));
	printf('<td width=50%%><span style="white-space: nowrap;"><a href="%s" target="new">%s</a></span></td>',get_href('add_bug'),_('Report a bug'));
	echo '</tr><tr>';

	printf('<td><img src="images/smile.png" alt="%s" /></td>',_('Donate'));
	printf('<td width=50%%><span style="white-space: nowrap;"><a href="%s" target="right_frame">%s</a></span></td>',get_href('donate'),_('Donate'));
}

printf('<td><img src="images/help.png" alt="%s" /></td>',_('Help'));
printf('<td><span style="white-space: nowrap;"><a href="help.php" target="right_frame">%s</a></span></td>',_('Help'));
echo '</tr></table>';

echo "\n\n";

# We want the std tree function as a fallback
require LIBDIR.'tree_functions.php';

# Are we going to use the PLM tree?
if ($config->GetValue('appearance','tree_plm')) {
	require JSDIR.'phplayersmenu/lib/PHPLIB.php';
	require JSDIR.'phplayersmenu/lib/layersmenu-common.inc.php';
	require JSDIR.'phplayersmenu/lib/treemenu.inc.php';
}

# For each of the configured servers
foreach( $ldapservers->GetServerList() as $server_id ) {
	$ldapserver = $ldapservers->Instance($server_id);

	if ($ldapserver->isVisible()) {
		$filename = get_custom_file($server_id,'tree_functions.php',LIBDIR);
		require_once($filename);

		call_custom_function($server_id,'draw_server_tree');
	}
}

echo '</body></html>';
?>
