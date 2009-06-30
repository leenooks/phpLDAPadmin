<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/search_form_simple.php,v 1.14 2005/07/16 03:13:54 wurley Exp $

/**
 * @package phpLDAPadmin
 */
?>

<script language="javascript">
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
	<center><b><?php echo $lang['simple_search_form_str']; ?></b><br />
	<small>(<a href="search.php?server_id=<?php echo $ldapserver->server_id; ?>&amp;form=advanced"><?php echo $lang['advanced_search_form_str']; ?></a> |
	<a href="search.php?server_id=<?php echo $ldapserver->server_id; ?>&amp;form=predefined"><?php echo $lang['predefined_searches']; ?></a>)</small><br />
	</center>
	<br />

	<small><?php echo $lang['server']; ?></small><br /> <?php echo $server_menu_html; ?><br />
	<br />
	<small><?php echo $lang['search_for_entries_whose']; ?></small><br />

	<nobr>
	<select name="attribute">
<?php  foreach( $config->GetValue('search','attributes') as $id => $attribute ) { ?>
	<option value="<?php echo rawurlencode( $attribute ); ?>"<?php echo $attribute==$attr?' selected="true"':''; ?>>
<?php echo htmlspecialchars(show_friendly_attribute($attribute)); ?>
	</option>
<?php } ?>
	</select>

	<select name="criterion">

<?php 
foreach( $config->GetValue('search','criteria_options') as $c ) { ?>
	<option value="<?php echo $c; ?>"<?php echo $c==$criterion?' selected="true"':''; ?>>
	<?php echo htmlspecialchars( $lang[$c] ); ?>
	</option>
<?php  } ?>
	</select>

	<input type="text" name="filter" id="filter" size="20" value="<?php echo htmlspecialchars($filter); ?>" /><br />
	<br />

	<center><input type="submit" value="<?php echo $lang['Search']; ?>" /></center>
	</nobr>
	</td>
</tr>
</table>
</form>

<script language="javascript">
    // Move the cursor to the filter field
    focus_filter();
</script>
