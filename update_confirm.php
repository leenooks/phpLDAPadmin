<?php

/*
 * udpate_confirm.php
 * Takes the results of clicking "Save" in edit.php and determines which 
 * attributes need to be updated (ie, which ones actually changed). Then,
 * we present a confirmation table to the user outlining the changes they
 * are about to make. That form submits directly to update.php, which 
 * makes the change.
 *
 */

require 'common.php';

include 'header.php';

$server_id = $_POST['server_id'];
$encoded_dn = $_POST['dn'];
$dn = rawurldecode( $encoded_dn );
$rdn = get_rdn( $dn );
$old_values = $_POST['old_values'];
$new_values = $_POST['new_values'];
$update_array = array();
if( is_server_read_only( $server_id ) )
	pla_error( "You cannot perform updates while server is in read-only mode" );
?>

<body>

<h3 class="title"><?php echo htmlspecialchars( utf8_decode( $rdn ) ); ?></h3>
<h3 class="subtitle">Server: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; Distinguished Name: <b><?php echo htmlspecialchars( utf8_decode( $dn ) ); ?></b></h3>

<?php
foreach( $new_values as $attr => $new_val )
{
	// did the user change the field?
	if( $new_val != $old_values[ $attr ] ) {

		// special case for userPassword attributes
		if( 0 == strcasecmp( $attr, 'userPassword' ) && $new_val != '' )
			$new_val = password_hash( $new_val, $_POST['enc_type'] );

		$update_array[ $attr ] = $new_val;
	}
}

// special case check for a new enc_type for userPassword (not otherwise detected)
if( $_POST['enc_type'] != $_POST['old_enc_type'] && $_POST['new_values']['userpassword'] != '' ) {
	$new_password = password_hash( $_POST['new_values']['userpassword'], $_POST['enc_type'] );
	$update_array[ 'userpassword' ] = $new_password;
}

// strip empty vals from update_array and ensure consecutive indices for each attribute
foreach( $update_array as $attr => $val ) {
	if( is_array( $val ) ) {
		foreach( $val as $i => $v )
			if( null == $v || 0 == strlen( $v ) )
				unset( $update_array[$attr][$i] );
		$update_array[$attr] = array_values( $update_array[$attr] );
	}
}

// at this point, the update_array should look like this (example):
// Array (
//    cn => Array( 
//           [0] => 'Dave',
//           [1] => 'Bob' )
//    sn => 'Smith',
//    telephoneNumber => '555-1234' )
//  This array should be ready to be passed to ldap_modify()

?>
<?php if( count( $update_array ) > 0 ) { ?>

	<br />
	<center>
	Do you want to make these changes?
	<br />
	<br />

	<table class="confirm">
	<tr><th>Attribute</th><th>Old Value</th><th>New Value</th></tr>
	<?php $counter=0; foreach( $update_array as $attr => $new_val ) { $counter++ ?>
	
		<tr class="<?php echo $counter%2 ? 'even' : 'odd'; ?>">
		<td><b><?php echo htmlspecialchars( $attr ); ?></b></td>
		<td><nobr>
		<?php
		if( is_array( $old_values[ $attr ] ) ) 
			foreach( $old_values[ $attr ] as $v )
				echo htmlspecialchars( utf8_encode( $v ) ) . "<br />";
		else  
			echo htmlspecialchars( utf8_encode( $old_values[ $attr ] ) ) . "<br />";
		echo "</nobr></td><td><nobr>";

		// is this a multi-valued attribute?
		if( is_array( $new_val ) ) {
			foreach( $new_val as $i => $v ) {
				if( $v == '' ) {
					// remove it from the update array if it's empty
					unset( $update_array[ $attr ][ $i ] );
					$update_array[ $attr ] = array_values( $update_array[ $attr ] );
				} else {
					echo htmlspecialchars( utf8_encode( $v ) ) . "<br />";
				}
			}

			// was this a multi-valued attribute deletion? If so,
			// fix the $update_array to reflect that per update_confirm.php's
			// expectations
			if( $update_array[ $attr ] == array( 0 => '' ) || $update_array[ $attr ] == array() ) {
				$update_array[ $attr ] = '';
				echo '<span style="color: red">[attribute deleted]</span>';
			}
		}
		else 
			if( $new_val != '' ) 
				echo htmlspecialchars( $new_val ) . "<br />";
			else 
				echo '<span style="color: red">[attribute deleted]</span>';
		echo "</nobr></td></tr>\n\n";
	}

	?>

	</table>
	<br />

	<table>
	<tr>
		<td>
			<!-- Commit button and acompanying form -->
			<form action="update.php" method="post">
			<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
			<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
			<?php foreach( $update_array as $attr => $val ) { ?>
				<?php if( is_array( $val ) ) { ?>				
					<?php foreach( $val as $i => $v ) { ?>
						<input  type="hidden"
							name="update_array[<?php echo htmlspecialchars( utf8_encode( $attr ) ); ?>][<?php echo $i; ?>]"
							value="<?php echo htmlspecialchars( utf8_encode( $v ) ); ?>" />
					<?php } ?> 
				<?php } else { ?>				
					<input  type="hidden"
						name="update_array[<?php echo htmlspecialchars( utf8_encode( $attr ) ); ?>]"
						value="<?php echo htmlspecialchars( utf8_encode( $val ) ); ?>" />
				<?php } ?>				
			<?php } ?>
			<input type="submit" value="Commit" class="happy" />
			</form>
		</td>
		<td>
			<!-- Cancel button -->
			<form action="edit.php" method="get">
			<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
			<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
			<input type="submit" value="Cancel" class="scary" />
			</form>
		</td>
	</tr>
	</table>		
	</center>
	</body>

	<?php

} else { ?>
	
	<center>
	You made no changes. 
	<a href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>">Go back</a>.
	</center>

<?php } ?>

</form>



