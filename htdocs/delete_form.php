<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/delete_form.php,v 1.22 2005/12/17 00:00:11 wurley Exp $

/**
 * delete_form.php
 * Displays a last chance confirmation form to delete a dn.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));
if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$dn = $_GET['dn'];
$children = $ldapserver->getContainerContents($dn,0,'(objectClass=*)',LDAP_DEREF_NEVER);
$has_children = count($children) > 0 ? true : false;

include './header.php';

echo '<body>';
printf('<h3 class="title">'._('Delete %s').'</h3>',htmlspecialchars(get_rdn($dn)));
printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
	_('Server'),$ldapserver->name,_('Distinguished Name'),htmlspecialchars(($dn)));

if ($has_children) {
	echo '<center>';
	printf('<b>%s</b><br /><br />',_('Permanently delete all children also?'));
	flush();

	# get the total number of child objects (whole sub-tree)
	$s = $ldapserver->search(null,dn_escape($dn),'objectClass=*',array('dn'));
	$sub_tree_count = count($s);
?>

<table class="delete_confirm">
<tr>
	<td>
	<p>
	<?php printf(_('This entry is the root of a sub-tree containing %s entries.'),$sub_tree_count); ?>
	<small>(<a href="search.php?search=true&amp;server_id=<?php echo $ldapserver->server_id; ?>&amp;filter=<?php echo rawurlencode('objectClass=*'); ?>&amp;base_dn=<?php echo rawurlencode($dn); ?>&amp;form=advanced&amp;scope=sub"><?php echo _('view entries'); ?></a>)</small>
	<br />
	<br />

	<?php printf(_('phpLDAPadmin can recursively delete this entry and all %s of its children. See below for a list of all the entries that this action will delete. Do you want to do this?'),($sub_tree_count-1)); ?><br />
	<br />
	<small><?php echo _('Note: this is potentially very dangerous and you do this at your own risk. This operation cannot be undone. Take into consideration aliases, referrals, and other things that may cause problems.'); ?></small>

	<br />
	<br />
	<table width="100%">
	<tr>
		<td>
		<center>
			<form action="rdelete.php" method="post">
			<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
			<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
			<input type="submit" class="scary" value="<?php printf(_('Delete all %s objects'),$sub_tree_count); ?>" />
			</form>
		</center>
		</td>

		<td>
		<center>
			<form action="template_engine.php" method="get">
			<input type="hidden" name="dn" value="<?php echo htmlspecialchars($dn); ?>" />
			<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
			<input type="submit" name="submit" value="<?php echo _('Cancel'); ?>" class="cancel" />
			</form>
		</center>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>

<?php flush(); ?>
<br />
<br />
<?php echo _('List of entries to be deleted:'); ?><br />

<select size="<?php echo min(10,$sub_tree_count);?>" multiple disabled style="background:white; color:black;width:500px" >
	<?php $i=0;
	foreach ($s as $dn => $junk) {
		$i++; ?>

	<option><?php echo $i; ?>. <?php echo htmlspecialchars((dn_unescape($dn))); ?></option>
	<?php } ?>
</select>
</center>

<br />

<?php } else { ?>

<center>
<table class="delete_confirm">
<tr>
	<td>
	<?php echo _('Are you sure you want to permanently delete this object?'); ?><br />
	<br />
	<nobr><acronym title="<?php echo _('Distinguished Name'); ?>"><?php echo _('DN'); ?></acronym>:  <b><?php echo pretty_print_dn($dn); ?></b></nobr><br />
	<nobr><?php echo _('Server'); ?>: <b><?php echo htmlspecialchars($ldapserver->name); ?></b></nobr><br />
	<br />

	<table width="100%">
	<tr>
		<td>
			<center>
			<form action="delete.php" method="post">
			<input type="hidden" name="dn" value="<?php echo htmlspecialchars($dn); ?>" />
			<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
			<input type="submit" name="submit" value="<?php echo _('Delete'); ?>" class="scary" />
			</form>
			</center>
		</td>

		<td>
			<center>
			<form action="template_engine.php" method="get">
			<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
			<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
			<input type="submit" name="submit" value="<?php echo _('Cancel'); ?>" class="cancel" />
			</form>
			</center>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</center>

<?php } ?>

</body>
</html>
