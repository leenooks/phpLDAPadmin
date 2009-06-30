<form action="search.php" method="get" class="search">
<input type="hidden" name="search" value="true" />
<input type="hidden" name="form" value="simple" />
<input type="hidden" name="scope" value="sub" />

<table>
<td>	
<center><b><?php echo $lang['simple_search_form_str']; ?></b><br />
<small>(<a href="search.php?server_id=<?php echo $server_id; ?>&amp;form=advanced"><?php echo $lang['advanced_search_form_str']; ?></a>)</small><br />
<br />
</center>

		<small>Server</small><br /> <?php echo $server_menu_html; ?><br />
		<br />	
		<small>Search for entries whose:</small><br />

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
			$search_criteria_options = array( "equals", "starts with", "contains", "ends with", "sounds like" );
			foreach( $search_criteria_options as $c ) { ?>
			<option value="<?php echo $c; ?>"<?php echo $c==$criterion?' selected':''; ?>>
				<?php echo htmlspecialchars($c); ?>
			</option>
		<?php  } ?>
		</select>
			
		<input type="text" name="filter" size="20" value="<?php echo htmlspecialchars(utf8_decode($filter)); ?>" /><br />
		<br />

		<center><input type="submit" value="Search" /></center>
		</nobr>
</td>
</table>
</form>

