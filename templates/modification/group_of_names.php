<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/modification/group_of_names.php,v 1.6 2004/04/14 11:30:05 uugdave Exp $


/*
 * group_of_names.php
 * Template for displaying a groupOfNames or groupOfUniqueNames object.
 * 
 * Variables that come in as GET vars:
 *  - dn
 *  - server_id
 *  - modified_attrs (optional) an array of attributes to highlight as 
 *                              they were changed by the last operation
 */

include 'header.php';
$members = get_object_attr( $server_id, $dn, 'uniqueMember' );
$unique = true;
$attr_name = 'uniqueMember';
if( null == $members ) {
	$attr_name = 'member';
	$members = get_object_attr( $server_id, $dn, 'member' );
	$unique = false;
}
$rdn = get_rdn( $dn );
$groupName = explode( '=', $rdn, 2 );
$groupName = $groupName[1];

?>

<script language="javascript">
<!--

// For removing elements from the member list.
// This is overly complicated. Good luck figuring
// out what it does. :) In fact, this thing is sooo
// ugly that I'm not even sure it will work on
// all browsers, but oh well... To understand it,
// you'll have to understand how the old_values
// and new_values array works when submitting 
// a form to update_confirm.php. So start there.
function remove_member( dn ) 
{
	//alert( 'Looking for ' + dn );
	var form = document.remove_member_form;
	for ( x=0; x<form.elements.length; x++ ) {
		var element = form.elements[x];
		if( dn == element.value ) {
			//alert( 'Found it at index: ' + x );
			element.value = '';
			form.submit();
			break;
		}
	}
}

-->
</script>

<form action="update_confirm.php" method="post" name="remove_member_form" style="margin:0">
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="dn" value="<?php echo htmlspecialchars( $dn ); ?>" />
<?php for( $i=0; $i<count($members); $i++) { $member=$members[$i]; ?>
<input type="hidden" name="new_values[<?php echo $attr_name; ?>][<?php echo $i; ?>]" value="<?php echo htmlspecialchars( $member ); ?>" />
<?php } ?>
<?php for( $i=0; $i<count($members); $i++) { $member=$members[$i]; ?>
<input type="hidden" name="old_values[<?php echo $attr_name; ?>][<?php echo $i; ?>]" value="<?php echo htmlspecialchars( $member ); ?>" />
<?php } ?>
</form>

<h3 class="title">Group: <b><?php echo htmlspecialchars( $groupName ); ?></b></h3>

<center><small>
	Using the <b>group of names</b> template. 
	You may switch to the <a href="<?php echo $default_href; ?>">default template</a>
</small></center>

<?php

echo '<h3>List of Members (' . ( $unique ? 'unique' : 'non-unique' ) . ')</h3>';
if( ! is_array( $members ) || 0 == count( $members ) ) {
	echo "(none)";
    echo "<br />";
    echo "<br />";
} else {
    echo "<ol>";
    for( $i=0; $i<count($members); $i++ ) {
        $member = $members[$i];
        echo "<li>"; 
        echo "<a href=\"edit.php?server_id=$server_id&amp;dn=" . rawurlencode( $member );
        echo "&amp;use_default_template=true\" title=\"Jump to this object\">";
        echo htmlspecialchars( $member ) . "</a>";
        echo " <small>(<a style=\"color:red\" href=\"javascript:remove_member( '" . htmlspecialchars( $member ) . "' );\" title=\"Remove this DN from the list\">remove</a>)</small>";

        $member_cn = get_object_attr( $server_id, $member, 'cn' );
        $member_cn = @$member_cn[0];
        $member_sn = get_object_attr( $server_id, $member, 'sn' );
        $member_sn = @$member_sn[0];
        echo '<small>';
        // Don't display the SN if it is a subset of the CN
        if( false !== strpos( $member_cn, $member_sn ) )
            $member_sn = ' ';
        if( $member_sn && $member_cn )
            echo '<br />&nbsp;&nbsp;Name: ' . htmlspecialchars( $member_cn . ' ' . $member_sn );
        $object_classes = get_object_attr( $server_id, $member, 'objectClass' );
        if( is_array( $object_classes ) )
            echo '<br />&nbsp;&nbsp;objectClasses: ' . implode( ', ', $object_classes );
        echo '</small>';
        "</li>";
    }
    echo "</ol>";
}

?>

<form action="add_value.php" method="post" name="add_member_form">
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="dn" value="<?php echo htmlspecialchars( $dn ); ?>" />
<input type="hidden" name="attr" value="<?php echo $attr_name; ?>" />

<div style="margin-left: 20px; border:1px solid gray; background-color: #eef; padding:5px; width: 300px">
	<small>Add a new member:</small><br />
	<input style="margin:0" type="text" name="new_value" size="35" style="font-size: 12px" value="" />
	<?php draw_chooser_link( 'add_member_form.new_value', false ); ?><br />
	<input type="submit" name="submit" value="Add" />
</div>
</form>

