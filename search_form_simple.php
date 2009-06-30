<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/search_form_simple.php,v 1.10 2004/04/02 14:44:46 uugdave Exp $
?><script language="javascript">
  function focus_filter() {
    document.simple_search_form.filter.focus();
  }
</script>

<form action="search.php" method="get" class="search" name="simple_search_form">
<input type="hidden" name="search" value="true" />
<input type="hidden" name="form" value="simple" />
<input type="hidden" name="scope" value="sub" />

<table>
<td>	
<center><b><?php echo $lang['simple_search_form_str']; ?></b><br />
<small>(<a href="search.php?server_id=<?php echo $server_id; ?>&amp;form=advanced"><?php echo $lang['advanced_search_form_str']; ?></a> | 
	<a href="search.php?form=predefined"><?php echo $lang['predefined_searches']; ?></a>)</small><br />
</center>
<br />

		<small><?php echo $lang['server']; ?></small><br /> <?php echo $server_menu_html; ?><br />
		<br />
		<small><?php echo $lang['search_for_entries_whose']; ?></small><br />

		<nobr>
		<select name="attribute">
		<?php  foreach( $search_attributes as $id => $attribute ) { ?>
			<option value="<?php echo rawurlencode( $attribute ); ?>"<?php echo $attribute==$attr?' selected':''; ?>>
				<?php echo htmlspecialchars($search_attributes_display[$id]); ?>
			</option>
		<?php  } ?>
		</select>

		<select name="criterion">


		<?php  
			if( ! isset( $search_criteria_options ) || ! is_array( $search_criteria_options ) )
				$search_criteria_options = array( "equals", "starts with", "contains", "ends with", "sounds like" );
			foreach( $search_criteria_options as $c ) { ?>
			<option value="<?php echo $c; ?>"<?php echo $c==$criterion?' selected':''; ?>>
				<?php echo htmlspecialchars( $lang[$c] ); ?>
			</option>
		<?php  } ?>
		</select>
			
		<input type="text" name="filter" id="filter" size="20" value="<?php echo htmlspecialchars($filter); ?>" /><br />
		<br />

		<center><input type="submit" value="<?php echo $lang['Search']; ?>" /></center>
		</nobr>
</td>
</table>
</form>
<script language="javascript">
    // Move the cursor to the filter field
    focus_filter();
</script>
