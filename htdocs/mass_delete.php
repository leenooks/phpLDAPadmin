<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/mass_delete.php,v 1.14.2.3 2005/12/16 10:21:12 wurley Exp $

/**
 * Enables user to mass delete multiple entries using checkboxes.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - mass_delete - an array of DNs to delete in this form:
 *      Array (
 *          [o=myorg,dc=example,dc=com] => on
 *          [cn=bob,dc=example,dc=com] => on
 *          etc.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if( $ldapserver->isReadOnly() )
	pla_error(_('Unable to delete, server is in READY-ONLY mode.'));
if( ! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$confirmed = isset($_POST['confirmed']) ? true : false;
isset($_POST['mass_delete']) or
	pla_error(_('Error calling mass_delete.php. Missing mass_delete in POST vars.'));

$mass_delete = $_POST['mass_delete'];

is_array($mass_delete) or
	pla_error(_('mass_delete POST var is not an array.'));

$ldapserver->isMassDeleteEnabled() or
	pla_error(_('Mass deletion is not enabled. Please enable it in config.php before proceeding.'));

require './header.php';

echo '<body>';
printf('<h3 class="title">%s</h3>',_('Mass Deleting'));

if ($confirmed == true) {
	printf('<h3 class="subtitle">'._('Deletion progress on server "%s"').'</h3>',$ldapserver->name);
	echo '<blockquote>';
	echo '<small>';

	$successfully_delete_dns = array();
	$failed_dns = array();

	if (! is_array($mass_delete))
		pla_error(_('Malformed mass_delete array.'));

	if (count($mass_delete) == 0) {
		echo '<br />';
		printf('<center>%s</center>',_('You did not select any entries to delete.'));
		die();
	}

	// @todo: Should sort these entries, so that they are deleted in order, if a user selects children.
	foreach ($mass_delete as $dn => $junk) {
		printf(_('Deleting %s'),htmlspecialchars($dn));
		flush();

		if(run_hook('pre_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn))) {
			$success = $ldapserver->delete($dn);

			if ($success) {
		                run_hook('post_entry_delete',array('server_id'=>$ldapserver->server_id,'dn'=>$dn));

				printf(' <span style="color:green">%s</span>.<br />',_('Success'));
				$successfully_delete_dns[] = $dn;

			} else {
				printf(' <span style="color:red">%s</span>.<br /> (%s)<br />',_('Failed'),$ldapserver->error());
				$failed_dns[] = $dn;
			}
		}

		flush();
	}

	echo '<blockquote>';
	echo '</small>';

	$failed_count = count($failed_dns);
	$total_count = count($mass_delete);

	if ($failed_count > 0)
		printf('<span style="color: red; font-weight: bold;">'._('%s of %s entries failed to be deleted.').'</span>',$failed_count,$total_count);
	else
		printf('<span style="color: green; font-weight: bold;">%s</span>',_('All entries deleted successfully.'));

	echo '<script language="javascript">parent.left_frame.location.reload();</script>';

} else {
	$n = count($mass_delete);
	printf('<h3 class="subtitle">'._('Confirm mass delete of %s entries on server %s').'</h3>',$n,$ldapserver->name);

	echo'<center>';
	printf(_('Do you really want to delete %s %s %s'),
		($n == 1? _('this') : _('these')),$n,($n == 1 ? _('entry') : _('entries')));

	echo '<form action="mass_delete.php" method="post">';
	echo '<input type="hidden" name="confirmed" value="true" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);

	echo '<table><tr><td><ol>';
	foreach ($mass_delete as $dn => $junk)
		printf('<input type="hidden" name="mass_delete[%s]" value="on" /><li>%s</li>',htmlspecialchars($dn),htmlspecialchars($dn));
	echo '</ol></td></tr></table>';

	printf('<input class="scary" type="submit" value="%s" /></center>',_('Yes, delete!'));
	echo '</form>';
}
?>
