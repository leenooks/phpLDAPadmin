<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/template_header.php,v 1.6 2005/09/04 18:41:34 wurley Exp $
 
/**
 * Header page for engine.
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 */

include './header.php';

$time = gettimeofday();
$random_junk = md5(strtotime('now').$time['usec']);
$url_base = sprintf('server_id=%s&amp;dn=%s',$ldapserver->server_id,$encoded_dn);

$export_href_base = sprintf('export_form.php?%s&amp;scope=%s',$url_base,'base');
$export_href_sub = sprintf('export_form.php?%s&amp;scope=%s',$url_base,'sub');
$refresh_href = sprintf('edit.php?%s&amp;random=%s',$url_base,$random_junk);
$copy_href = sprintf('copy_form.php?%s',$url_base);
$intattr_href = sprintf('edit.php?%s&amp;show_internal_attrs=true',$url_base);
$delete_href = sprintf('delete_form.php?%s',$url_base);
$rename_href = sprintf('rename_form.php?%s',$url_base);
$compare_href = sprintf('compare_form.php?%s',$url_base);
$create_href = sprintf('create_form.php?server_id=%s&amp;container=%s',$ldapserver->server_id,$encoded_dn);
$addattr_href = sprintf('add_attr_form.php?%s',$url_base);
?>

<body>

<?php if ($dn) { ?>
<h3 class="title"><?php echo htmlspecialchars($rdn); ?></h3>
<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $ldapserver->name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name'];?>: <b><?php echo htmlspecialchars( ( $dn ) ); ?></b></h3>

<table class="edit_dn_menu">
	<tr>
		<td class="icon"><img src="images/refresh.png" /></td>
		<td><a href="<?php echo $refresh_href; ?>" title="<?php echo $lang['refresh_this_entry']; ?>"><?php echo $lang['refresh_entry']; ?></a></td>
		<td class="icon"><img src="images/save.png" /></td>
		<td><a href="<?php echo $export_href_base; ?>" title="<?php echo $lang['export_tooltip']; ?>"><?php echo $lang['export']; ?></a></td>
	</tr>

	<tr>
		<td class="icon"><img src="images/cut.png" /></td>
		<td><a href="<?php echo $copy_href; ?>" title="<?php echo $lang['copy_this_entry_tooltip']; ?>"><?php echo $lang['copy_this_entry']; ?></a></td>

	<?php if ($show_internal_attrs) { ?>
	    <td class="icon"><img src="images/tools-no.png" /></td>
		<td><a href="<?php echo $refresh_href; ?>"><?php echo $lang['hide_internal_attrs']; ?></a></td>

	<?php } else { ?>
		<td class="icon"><img src="images/tools.png" /></td>
		<td><a href="<?php echo $intattr_href; ?>"><?php echo $lang['show_internal_attrs']; ?></a></td>
	<?php } ?>

	</tr>

	<?php if (! $ldapserver->isReadOnly()) { ?>
	<tr>
		<td class="icon"><img src="images/trash.png" /></td>
		<td><a style="color: red" href="<?php echo $delete_href; ?>" title="<?php echo $lang['delete_this_entry_tooltip']; ?>"><?php echo $lang['delete_this_entry']; ?></a></td>
		<td class="icon"><img src="images/rename.png" /></td>
		<td><a href="<?php echo $rename_href; ?>"><?php echo $lang['rename']; ?></a></td>
	</tr>

	    <?php if ($config->GetValue('appearance','show_hints')) { ?>
	<tr>
		<td class="icon"><img src="images/light.png" /></td>
		<td colspan="3"><span class="hint"><?php echo $lang['delete_hint']; ?></span></td>
	</tr>
		<?php } ?>

	<tr>
		<td class="icon"><img src="images/compare.png" /></td>
		<td><a href="<?php echo $compare_href; ?>"><?php echo $lang['compare_with']; ?></a></td>
	</tr>

	<tr>
		<td class="icon"><img src="images/star.png" /></td>
		<td><a href="<?php echo $create_href; ?>"><?php echo $lang['create_a_child_entry']; ?></a></td>
		<td class="icon"><img src="images/add.png" /></td>
		<td><a href="<?php echo $addattr_href; ?>"><?php echo $lang['add_new_attribute']; ?></a></td>
	</tr>
	<?php }

	flush();
	$children = get_container_contents($ldapserver,$dn,$max_children,'(objectClass=*)',$config->GetValue('deref','view')); 

	if (($children_count = count($children)) > 0) {
		if ($children_count == $max_children)
			$children_count = $children_count.'+';

		$child_href = sprintf('search.php?server_id=%s&amp;search=true&amp;filter=%s&amp;base_dn=%s&amp;form=advanced&amp;scope=one',
			$ldapserver->server_id,rawurlencode('objectClass=*'),$encoded_dn);
?>

	<tr>
		<td class="icon"><img src="images/children.png" /></td>
		<td><a href="<?php echo $child_href; ?>"><?php if( $children_count == 1 ) echo $lang['view_one_child']; else echo sprintf( $lang['view_children'], $children_count ); ?></a></td>
		<td class="icon"><img src="images/save.png" /></td>
		<td><a href="<?php echo $export_href_sub; ?>" title="<?php echo $lang['export_subtree_tooltip']; ?>"><?php echo $lang['export_subtree']; ?></a></td>
	</tr> 

	<?php } ?>

	<?php if ($config->GetValue('appearance','show_hints')) { ?> 
	<tr> 
		<td class="icon"><img src="images/light.png" /></td>
		<td colspan="3"><span class="hint"><?php echo $lang['attr_schema_hint']; ?></span></td>
	</tr>
	<?php } ?>

	<?php if ($ldapserver->isReadOnly()) { ?>
	<tr>
		<td class="icon"><img src="images/light.png" /></td>
		<td><?php echo $lang['viewing_read_only']; ?></td>
	</tr>
	<?php } ?>

	<?php if ($modified_attrs) { ?>
	<tr>
		<td class="icon"><img src="images/light.png" /></td>

		<?php if (count($modified_attrs) > 1) { ?>
		<td colspan="3"><?php echo sprintf($lang['attrs_modified'],implode(', ',$modified_attrs)); ?></td>

		<?php } else { ?>
		<td colspan="3"><?php echo sprintf($lang['attr_modified'],implode('',$modified_attrs)); ?></td>
		<?php } ?>

	</tr>
	<?php } ?>

</table>

	<?php flush(); ?>

<br />
<table class="edit_dn">

	<?php if ($show_internal_attrs) {
		$counter = 0;

		foreach (get_entry_system_attrs($ldapserver,$dn) as $attr => $vals) {
			$counter++;
			$schema_href = sprintf('schema.php?server_id=%s&amp;view=attributes&amp;viewvalue=%s',
				$ldapserver->server_id,real_attr_name($attr));
?>

	<tr>
		<td colspan="2" class="attr"><b><a title="<?php echo sprintf( $lang['attr_name_tooltip'], $attr ); ?>" href="<?php echo $schema_href; ?>"><?php echo htmlspecialchars( $attr ); ?></b></td>
	</tr>

	<tr>
		<td class="val">

			<?php if (is_attr_binary($ldapserver,$attr)) {
				$href = "download_binary_attr.php?server_id=$server_id&amp;dn=$encoded_dn&amp;attr=$attr";
?>

			<small>
        		<?php echo $lang['binary_value']; ?><br />
        		<?php if (count($vals) > 1) {
					for ($i = 1; $i <= count($vals); $i++) { ?>

				<a href="<?php sprintf('%s&amp;value_num=%s',$href,$i); ?>"><img src="images/save.png" /> <?php echo $lang['download_value']; ?>(<?php echo $i; ?>)</a>
				<br />

<?php
					}
				} else { ?>
				<a href="<?php echo $href; ?>"><img src="images/save.png" /> <?php echo $lang['download_value']; ?></a><br />
				<?php }

			} else {

				foreach ($vals as $v) {
					echo htmlspecialchars($v);
					echo "<br />\n";
				}
			}
?>
			</small>
		</td>
	</tr>

<?php
		}

		if ($counter == 0)
			echo "<tr><td colspan=\"2\">(" . $lang['no_internal_attributes'] . ")</td></tr>\n";
	}

	flush(); ?>

<!-- Table of attributes/values to edit -->

	<?php if (! $ldapserver->isReadOnly()) { ?>
	<form action="update_confirm.php" method="post" name="edit_form">
	<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
	<?php }

} else {

?>

<h3 class="title"><?php echo $lang['createf_create_object']; ?></h3>
<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $ldapserver->name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['using_template'];?>: <b><?php echo htmlspecialchars($_REQUEST['template']); ?></b></h3>

<?php
}
?>
