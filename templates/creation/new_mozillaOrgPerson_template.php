<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/creation/Attic/new_mozillaOrgPerson_template.php,v 1.2 2004/10/28 13:37:40 uugdave Exp $

/**
 * "Create new" template for Mozilla Address book entry (mozillaOrgPerson)
 * @author Christian Weiske <cweiske@cweiske.de> 
 */

// customize this to your needs
$default_container = "ou=Addresses";

// Common to all templates
$container = $_POST['container'];
$server_id = $_POST['server_id'];

// Unique to this template
$step = isset( $_POST['step'] ) ? $_POST['step'] : 1;

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

/**
*	Data definition incl. group names
*/
$arDataDef	= array(
	'name' => array(
		'givenName' => 'Given name',
		'sn' => 'Last name',
		'cn' => 'Common name',
		'mozillanickname' => 'mozillaNickname'
	),
	'internet' => array(
		'mail' => 'Email',
		'mozillaSecondEmail' => 'Second email',
//		'mozilla_AimScreenName' => 'Screen name',
//		'mozillausehtmlmail' => 'Use HTML mail'
	),
	'Phones' => array(
		'telephoneNumber' => 'Work',
		'homePhone' => 'Home',
		'facsimileTelephoneNumber' => 'Fax',
		'pager' => 'Pager',
		'mobile' => 'Mobile'
	),
	'Home address' => array(
		'homePostalAddress' => 'Address',
		'mozillaHomePostalAddress2' => 'Address 2',
		'mozillaHomeLocalityName' => 'City',
		'mozillaHomeState' => 'State',
		'mozillaHomePostalCode' => 'ZIP',
//		'mozillaHomeFriendlyCountryName' => 'friendly Country',
		'mozillaHomeCountryName' => 'Country',
		'mozillaHomeUrl' => 'Web page'
	),
	'Work address' => array(
		'title' => 'Title',
		'ou' => 'Department',
		'o' => 'Organization',
		'postalAddress' => 'Address',
		'mozillaPostalAddress2' => 'Address 2',
		'l' => 'City',
		'st' => 'State/Province',
		'postalCode' => 'ZIP',
		'c' => 'Country',
		'mozillaWorkUrl' => 'Web page'
	),
	'Other' => array(
//		'custom1' => 'Custom 1',
//		'custom2' => 'Custom 2',
//		'custom3' => 'Custom 3',
//		'custom4' => 'Custom 4',
//		'description' => 'Notes'
	)
/**/
);

?>

<script language="javascript">
<!--

/*
 * Populates the common name field based on the last 
 * name concatenated with the first name, separated
 * by a blank
 */
function autoFillCommonName( form )
{
	var first_name;
	var last_name;
	var common_name;

	first_name = form.givenName.value;
	last_name = form.sn.value;

	if( last_name == '' ) {
		return false;
	}

	common_name = last_name + ' ' + first_name;
	form.cn.value = common_name;
}

-->
</script>

<center><h2>New Address Book Entry<br />
<small>(MozillaOrgPerson)</small></h2>
</center>

<?php if( $step == 1 ) { ?>

<form action="creation_template.php" method="post" id="address_form" name="address_form">
<input type="hidden" name="step" value="2" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="template" value="<?php echo htmlspecialchars( $_POST['template'] ); ?>" />

<center>
<table class="confirm">
<?php
	foreach( $arDataDef as $strGroupName => $arGroup) 
	{
		echo '<tr class="spacer"><td colspan="3">' . htmlspecialchars( $strGroupName) . '</td></tr>' . "\r\n";
		foreach( $arGroup as $strId => $strName) 
		{
			if( $strId == 'sn' || $strId == 'givenName') {
				$strAutoChange	= ' onChange="autoFillCommonName(this.form)"';
			} else {
				$strAutoChange	= '';
			}
			echo '<tr>' . "\r\n";
			echo '<td></td>' . "\r\n";
			echo '<td class="heading">' . htmlspecialchars( $strName) . '</td>' . "\r\n";
			echo '<td><input type="text" name="' . $strId . '" id="' . $strId . '" value=""' . $strAutoChange . ' /></td>' . "\r\n";
			echo '</tr>' . "\r\n";
		}
	}

?>
<tr class="spacer"><td colspan="3"></td></tr>
<tr>
	<td></td>
	<td class="heading">Container:</td>
	<td><input type="text" name="container" size="40"
		value="<?php if( isset( $container ) )
				echo htmlspecialchars( $container );
			     else
				echo htmlspecialchars( $default_container . ',' . $servers[$server_id]['base'] ); ?>" />
		<?php draw_chooser_link( 'address_form.container' ); ?>
	</td>
</tr>
<tr>
	<td colspan="3" style="text-align: center"><br /><input type="submit" value="Proceed &gt;&gt;" /></td>
</tr>
</table>
</center>
</form>

<?php } elseif( $step == 2 ) {

	$arData	= array();
	foreach( $arDataDef as $arGroup) {
		foreach( $arGroup as $strId => $strName) {
			$arData[$strId]	= trim( $_POST[$strId]);
		}
	}
	$container	= $_POST['container'];

	/* Critical assertions */
	0 != strlen( $arData['cn']) or
		pla_error( "You cannot leave the Common Name blank. Please go back and try again." );

	?>
	<center><h3>Confirm entry creation:</h3></center>

	<form action="create.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="new_dn" value="<?php echo htmlspecialchars( 'cn=' . $arData['cn'] . ',' . $container ); ?>" />

	<!-- ObjectClasses  -->
	<?php $object_classes = rawurlencode( serialize( array( 'top', 'inetOrgPerson', 'mozillaOrgPerson' ) ) ); ?>

	<input type="hidden" name="object_classes" value="<?php echo $object_classes; ?>" />

	<!-- The array of attributes/values -->
	<?php 
	foreach( $arDataDef as $arGroup) {
		foreach( $arGroup as $strId => $strName) {
			echo '<input type="hidden" name="attrs[]" value="' . $strId . '" />' . "\r\n";
			echo '<input type="hidden" name="vals[]" value="' . htmlspecialchars( $arData[$strId]) . '" />' . "\r\n";
		}
	}
	?>
	<center>
	<table class="confirm">
	<?php
	$strEven	= 'even';
	foreach( $arDataDef as $strGroupName => $arGroup)
	{
		echo '<tr class=""><th colspan="2">' . $strGroupName . '</th></tr>';
		foreach( $arGroup as $strId => $strName)
		{
			echo '<tr class="' . $strEven . '">' . "\r\n";
			echo '	<td class="heading">' . $strName . ':</td>' . "\r\n";
			echo '	<td><b>' . htmlspecialchars( $arData[$strId] ) . '</b></td>' . "\r\n";
			echo '</tr>' . "\r\n";
			$strEven = $strEven == 'even' ? 'odd' : 'even';
		}
	}
	
	?>
	<tr class="<?php echo $strEven; ?>">
		<td class="heading">Container:</td>
		<td><?php echo htmlspecialchars( $container ); ?></td>
	</tr>
	</table>
	<br /><input type="submit" value="Create Address" />
	</center>
	</form>

<?php } ?>

</body>
</html>
