<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/search_form_predefined.php,v 1.5 2004/04/23 13:35:55 uugdave Exp $
?><form action="search.php" method="get" class="search">
<input type="hidden" name="search" value="true" />
<input type="hidden" name="form" value="predefined" />

<table>
<td>	
<?php
if( isset( $_GET['predefined'] ) ) 
	$selected_q_number = intval( $_GET['predefined'] ); 
else
	$selected_q_number = null;
?>
<center><b><?php echo $lang['predefined_searches']; ?></b><br />
<small>(<a href="search.php?server_id=<?php echo $server_id; ?>&amp;form=simple"><?php echo $lang['simple_search_form_str']; ?></a> | 
	<a href="search.php?server_id=<?php echo $server_id; ?>&amp;form=advanced"><?php echo $lang['advanced_search_form_str']; ?></a>)</small><br />
<br />
<?php  
	if( ! isset( $queries ) || ! is_array( $queries ) || 0 == count( $queries ) ) {
        echo "<br />\n";
		echo $lang['no_predefined_queries'];
        echo "<br />\n";
        echo "<br />\n";
        echo "<br />\n";
        echo "</td></table>\n";
        echo "</body>\n";
        echo "</html>\n";
        die();
} else { ?>

<small><?php echo $lang['predefined_search_str']; ?>: </small>
<select name="predefined">
<?php 
	foreach( $queries as $q_number => $q ) {
		if ($selected_q_number === $q_number)
			$selected = " selected";
		else $selected = "";
		print("\t<option value=\"" . $q_number . "\"" . $selected . ">\n");
		print("\t" . htmlspecialchars( $q['name'] ) . "\n");
		print("\t</option>\n");
	}
?>
</select>
<?php } ?>
<br />
<br />
<center><input type="submit" value="<?php echo $lang['Search']; ?>" /></center>
</td>
</table>
</form>
