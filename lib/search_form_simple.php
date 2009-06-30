<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/search_form_simple.php,v 1.14.4.4 2005/12/09 14:31:27 wurley Exp $

/**
 * @package phpLDAPadmin
 */
?>

<script type="text/javascript" language="javascript">
  function focus_filter() {
    document.simple_search_form.filter.focus();
  }
</script>

<form action="search.php" method="get" class="search" name="simple_search_form">
<input type="hidden" name="search" value="true" />
<input type="hidden" name="form" value="simple" />
<input type="hidden" name="scope" value="sub" />
<input type="hidden" name="format" value="<?php echo $format; ?>" />

<table>
<tr>
	<td>
	<center><b><?php echo _('Simple Search Form'); ?></b><br />
	<small>(<a href="search.php?server_id=<?php echo $ldapserver->server_id; ?>&amp;form=advanced"><?php echo _('Advanced Search Form'); ?></a> |
	<a href="search.php?server_id=<?php echo $ldapserver->server_id; ?>&amp;form=predefined"><?php echo _('Predefined Searches'); ?></a>)</small><br />
	</center>
	<br />

	<small><?php echo _('Server'); ?></small><br /> <?php echo $server_menu_html; ?><br />
	<br />
	<small><?php echo _('Search for entries whose'); ?></small><br />

	<nobr>
	<select name="attribute">
<?php  foreach( $config->GetValue('search','attributes') as $id => $attribute ) { ?>
	<option value="<?php echo rawurlencode( $attribute ); ?>"<?php echo $attribute==$attr?' selected="true"':''; ?>>
<?php echo htmlspecialchars($ldapserver->showFriendlyAttr($attribute)); ?>
	</option>
<?php } ?>
	</select>
	</nobr>

	<select name="criterion">

<?php
foreach( $config->GetValue('search','criteria_options') as $c ) { ?>
	<option value="<?php echo $c; ?>"<?php echo $c==$criterion?' selected="true"':''; ?>>
	<?php echo htmlspecialchars(_($c)); ?>
	</option>
<?php  } ?>
	</select>

	<input type="text" name="filter" id="filter" size="20" value="<?php echo htmlspecialchars($filter); ?>" /><br />
	<br />

	<center><input type="submit" value="<?php echo _('Search'); ?>" /></center>
	</td>
</tr>
</table>
</form>

<script type="text/javascript" language="javascript">
    // Move the cursor to the filter field
    focus_filter();
</script>
