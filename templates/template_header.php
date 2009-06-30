<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/Attic/template_header.php,v 1.6.4.7 2007/03/18 03:23:26 wurley Exp $

/**
 * Header page for engine.
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 */

include './header.php';

$url_base = sprintf('server_id=%s&amp;dn=%s',$ldapserver->server_id,$encoded_dn);

$export_href_base = sprintf('export_form.php?%s&amp;scope=%s',$url_base,'base');
$export_href_sub = sprintf('export_form.php?%s&amp;scope=%s',$url_base,'sub');
$refresh_href = sprintf('template_engine.php?%s&amp;random=%s',$url_base,random_junk());
$copy_href = sprintf('copy_form.php?%s',$url_base);
$intattr_href = sprintf('template_engine.php?%s&amp;show_internal_attrs=true',$url_base);
$delete_href = sprintf('delete_form.php?%s',$url_base);
$rename_href = sprintf('rename_form.php?%s',$url_base);
$compare_href = sprintf('compare_form.php?%s',$url_base);
$create_href = sprintf('create_form.php?server_id=%s&amp;container=%s',$ldapserver->server_id,$encoded_dn);
$addattr_href = sprintf('add_attr_form.php?%s',$url_base);

echo '<body>';

if ($dn) {
	$actionlayout = '<td class="icon"><img src="images/%s" alt="%s" /></td><td><a href="%s" title="%s">%s</a></td>';
	$hintlayout = '<td class="icon"><img src="images/light.png" alt="Hint" /></td><td colspan="3"><span class="hint">%s</span></td>';

	printf('<h3 class="title">%s</h3>',htmlspecialchars($rdn));
	printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
		_('Server'),$ldapserver->name,_('Distinguished Name'),htmlspecialchars($dn));

	echo "\n";

	echo '<table class="edit_dn_menu"><tr>';
	printf($actionlayout,'refresh.png','Refresh',$refresh_href,_('Refresh this entry'),_('Refresh'));
	printf($actionlayout,'save.png','Save',$export_href_base,_('Save a dump of this object'),_('Export'));
	echo '</tr><tr>';

	printf($actionlayout,'cut.png','Cut',$copy_href,_('Copy this object to another location, a new DN, or another server'),_('Copy or move this entry'));

	if ($show_internal_attrs)
		printf($actionlayout,'tools-no.png','Hide',$refresh_href,'',_('Hide internal attributes'));

	else
		printf($actionlayout,'tools.png','Show',$intattr_href,'',_('Show internal attributes'));

	echo '</tr>';

	if (! $ldapserver->isReadOnly()) {
		echo '<tr>';
		printf($actionlayout,'trash.png','Trash',$delete_href,_('You will be prompted to confirm this decision'),_('Delete this entry'));
		printf($actionlayout,'rename.png','Rename',$rename_href,'',_('Rename'));
		echo '</tr>';

		if ($config->GetValue('appearance','show_hints')) {
			echo '<tr>';
			printf($hintlayout,_('Hint: To delete an attribute, empty the text field and click save.'));
			echo '</tr>';
		}

		echo '<tr>';
		printf($actionlayout,'compare.png','Compare',$compare_href,'',_('Compare with another entry'));
		echo '</tr>';

		echo '<tr>';
		printf($actionlayout,'star.png','Create',$create_href,'',_('Create a child entry'));
		printf($actionlayout,'add.png','Add',$addattr_href,'',_('Add new attribute'));
		echo '</tr>';
	}

	flush();
	$children = $ldapserver->getContainerContents($dn,$max_children,'(objectClass=*)',$config->GetValue('deref','view'));

	if (($children_count = count($children)) > 0) {
		if ($children_count == $max_children)
			$children_count = $children_count.'+';

		$child_href = sprintf('search.php?server_id=%s&amp;search=true&amp;filter=%s&amp;base_dn=%s&amp;form=advanced&amp;scope=one',
			$ldapserver->server_id,rawurlencode('objectClass=*'),$encoded_dn);

		echo '<tr>';
		printf($actionlayout,'children.png','Children',$child_href,'',($children_count == 1) ? _('View 1 child') : sprintf(_('View %s children'),$children_count));
		printf($actionlayout,'save.png','Save',$export_href_sub,_('Save a dump of this object and all of its children'),_('Export subtree'));
		echo '</tr>';
	}

	if ($config->GetValue('appearance','show_hints')) {
		echo '<tr>';
		printf($hintlayout,_('Hint: To view the schema for an attribute, click the attribute name.'));
		echo '</tr>';
	}

	if ($ldapserver->isReadOnly()) {
		echo '<tr>';
		printf($hintlayout,_('Viewing entry in read-only mode.'));
		echo '</tr>';
	}

	if ($modified_attrs) {
		echo '<tr>';
		printf($hintlayout,
			($children_count == 1) ? sprintf(_('An attribute (%s) was modified and is highlighted below.'),implode('',$modified_attrs)) :
				sprintf(_('Some attributes (%s) were modified and are highlighted below.'),implode(', ',$modified_attrs)));
		echo '</tr>';
	}

	echo '</table>';

	flush();

	if (! $ldapserver->isReadOnly()) {
		echo '<form action="update_confirm.php" method="post" name="edit_form">';
		printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
		printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($dn));
	}

	echo '<br />'."\n\n";
	echo '<table class="edit_dn">';

	if ($show_internal_attrs) {
		$counter = 0;

		foreach ($ldapserver->getDNSysAttrs($dn) as $attr => $vals) {
			$counter++;
			$schema_href = sprintf('schema.php?server_id=%s&amp;view=attributes&amp;viewvalue=%s',
				$ldapserver->server_id,real_attr_name($attr));

			printf('<tr><td colspan="2" class="attr"><b><a title="'._('Click to view the schema definition for attribute type \'%s\'').'" href="%s" />%s</b></td></tr>',
				$attr,$schema_href,htmlspecialchars($attr));

			echo '<tr><td class="val"><small>';

			if ($ldapserver->isAttrBinary($attr)) {
				$href = sprintf('download_binary_attr.php?server_id=%s&amp;dn=%s&amp;attr=%s',$server_id,$encoded_dn,$attr);

				echo _('Binary value');
				echo '<br />';

				if (count($vals) > 1) {
					for ($i=1;$i<=count($vals);$i++)
						printf('<a href="%s&amp;value_num=%s"><img src="images/save.png" /> %s(%s)</a><br />',
							$href,$i,_('download value'),$i);

				} else {
					printf('<a href="%s"><img src="images/save.png" /> %s</a><br />',
						$href,_('download value'));
				}

			} else {
				foreach ($vals as $v)
					printf('%s<br />',htmlspecialchars($v));
			}

			echo '</small></td></tr>';
		}

		if ($counter == 0)
			printf('<tr><td colspan="2">(%s)</td></tr>',_('No internal attributes'));

		echo "\n\n";
	}

	flush();

} else {
	printf('<h3 class="title">%s</h3>',_('Create Object'));
	printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
		_('Server'),$ldapserver->name,_('using template'),htmlspecialchars($_REQUEST['template']));
}
?>
