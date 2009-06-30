<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/search_form_simple.php,v 1.16.2.2 2007/12/26 09:26:33 wurley Exp $

/**
 * @package phpLDAPadmin
 */
?>

<script type="text/javascript" language="javascript">
	function focus_filter() {
		document.simple_search_form.filter.focus();
	}
</script>

<form action="cmd.php" method="get" class="search" name="simple_search_form">
<input type="hidden" name="cmd" value="search" />
<input type="hidden" name="search" value="true" />
<input type="hidden" name="form" value="simple" />
<input type="hidden" name="scope" value="sub" />
<input type="hidden" name="format" value="<?php echo $entry['format']; ?>" />

<table class="search" border=0>
<tr><td class="title"><?php echo _('Simple Search Form'); ?></td></tr>

<?php
	$as = $_SESSION[APPCONFIG]->isCommandAvailable('search', 'advanced_search');
	$ps = $_SESSION[APPCONFIG]->isCommandAvailable('search', 'predefined_search');
	if ($as | $ps) {
		echo '<tr><td class="subtitle">(';
		if ($as) {
			echo '<a href="cmd.php?cmd=search&amp;server_id=';
			echo $ldapserver->server_id;
			echo '&amp;form=advanced">';
			echo _('Advanced Search Form');
			echo '</a>';
			if ($ps) echo '	| ';
		}
		if ($ps) {
			echo '<a href="cmd.php?cmd=search&amp;server_id=';
			echo $ldapserver->server_id;
			echo '&amp;form=predefined">';
			echo _('Predefined Searches');
			echo '</a>';
		}
		echo ')</td></tr>';
	}
?>

<tr><td>&nbsp;</td></tr>
<tr><td><small><b><?php echo _('Server'); ?></b></small><br /> <?php echo $server_menu_html; ?><br /></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td><small><b><?php echo _('Search for entries whose'); ?></b></small></td></tr>
<tr><td>
	<select name="attribute">
<?php foreach ($_SESSION[APPCONFIG]->GetValue('search','attributes') as $id => $attribute) { ?>
	<option value="<?php echo rawurlencode($attribute); ?>"<?php echo $attribute==$entry['attr']?' selected="true"':''; ?>>
<?php echo htmlspecialchars($_SESSION[APPCONFIG]->getFriendlyName($attribute)); ?>
	</option>
<?php } ?>
	</select>

	<select name="criterion">
<?php
foreach ($_SESSION[APPCONFIG]->GetValue('search','criteria_options') as $c) { ?>
	<option value="<?php echo $c; ?>"<?php echo $c==$entry['criterion']?' selected="true"':''; ?>>
	<?php echo htmlspecialchars(_($c)); ?>
	</option>
<?php } ?>
	</select>

	<input type="text" name="filter" id="filter" size="20" value="<?php echo htmlspecialchars($entry['filter']['clean']); ?>" />
</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td><center><input type="submit" value="<?php echo _('Search'); ?>" /></center></td></tr>
</table>
</form>

<script type="text/javascript" language="javascript">
	// Move the cursor to the filter field
	focus_filter();
</script>
