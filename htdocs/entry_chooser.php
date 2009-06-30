<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/entry_chooser.php,v 1.26.2.1 2005/10/09 09:07:21 wurley Exp $

/**
 * Display a selection (popup window) to pick a DN.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$container = isset($_GET['container']) ? rawurldecode($_GET['container']) : false;
$return_form_element = isset($_GET['form_element']) ? htmlspecialchars($_GET['form_element']) : null;
$rdn = isset($_GET['rdn']) ? htmlspecialchars($_GET['rdn']) : null;

include "./header.php";

printf('<h3 class="subtitle">%s</h3>',$lang['entry_chooser_title']);
flush();
?>

<script language="javascript">
	function returnDN( dn ) {
		opener.document.<?php echo $return_form_element; ?>.value = dn;
		close();
	}
</script>

<?php
if ($container) {
	printf('%s<b>%s</b>',$lang['server_colon_pare'],htmlspecialchars($ldapserver->name));
	print '<br />';
	printf('%s<b>%s</b>',$lang['look_in'],htmlspecialchars($container));
	print '<br />';
}

/* Has the use already begun to descend into a specific server tree? */
if (isset($ldapserver) && $container !== false) {

	if (! $ldapserver->haveAuthInfo())
		pla_error($lang['not_enough_login_info']);

	$dn_list = get_container_contents($ldapserver,$container,0,'(objectClass=*)',$config->GetValue('deref','tree'));
	sort($dn_list);

	foreach ($ldapserver->getBaseDN() as $base_dn) {
	        if (DEBUG_ENABLED)
			debug_log('entry_chooser.php: Comparing BaseDN [%s] with container [%s]',9,$base_dn,$container);

		if (! pla_compare_dns($container,$base_dn)) {
			$parent_container = false;
			$up_href = sprintf('entry_chooser.php?form_element=%s&rdn=%s',$return_form_element,$rdn);
			break;

		} else {
			$parent_container = get_container($container);
			$up_href = sprintf('entry_chooser.php?form_element=%s&rdn=%s&amp;server_id=%s&amp;container=%s',
				$return_form_element,$rdn,$ldapserver->server_id,rawurlencode($parent_container));
		}
	}

	print '&nbsp;';
	printf('<a href="%s" style="text-decoration:none"><img src="images/up.png"> %s</a>',$up_href,$lang['back_up_p']);
	print '<br />';

	if (! count($dn_list))
		printf('&nbsp;&nbsp;&nbsp;(%s)<br />',$lang['no_entries']);

	else
		foreach ($dn_list as $dn) {
			$href = sprintf("javascript:returnDN('%s%s')",($rdn ? "$rdn," : ''),$dn);
			print '&nbsp;&nbsp;&nbsp;';
			printf('<a href="entry_chooser.php?server_id=%s&amp;form_element=%s&rdn=%s&amp;container=%s"><img src="images/plus.png" /></a>',
				$ldapserver->server_id,$return_form_element,$rdn,rawurlencode($dn));

			printf('<a href="%s">%s</a>',$href,htmlspecialchars($dn));
			print '<br />';
		}

/* draw the root of the selection tree (ie, list all the servers) */
} else {
	foreach ($ldapservers->GetServerList() as $id) {

		$ldapserver = $ldapservers->Instance($id);

		if ($ldapserver->isVisible()) {

			if (! $ldapserver->haveAuthInfo())
				continue;

			else {
				printf('<b>%s</b>',htmlspecialchars($ldapserver->name));
				print '<br />';
				foreach ($ldapserver->getBaseDN() as $dn) {
					if (! $dn) {
						printf('<small>&nbsp;&nbsp;&nbsp;(%s)</small><br />',$lang['could_not_det_base_dn']);

					} else {
						$href = sprintf("javascript:returnDN('%s%s')",($rdn ? "$rdn," : ''),$dn);

						print '&nbsp;&nbsp;&nbsp;';
						printf('<a href="entry_chooser.php?server_id=%s&amp;form_element=%s&rdn=%s&amp;container=%s"><img src="images/plus.png" /></a> ',
							$ldapserver->server_id,$return_form_element,$rdn,rawurlencode($dn));

						printf('<a href="%s">%s</a>',$href,htmlspecialchars($dn));
						print '<br />';
					}
				}
			}
		}
	}
}
?>
