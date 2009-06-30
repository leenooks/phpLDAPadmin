<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/create_form.php,v 1.22 2005/02/25 13:44:05 wurley Exp $

/**
 * The menu where the user chooses an RDN, Container, and Template for creating a new entry.
 * After submitting this form, the user is taken to their chosen Template handler.
 *
 * Variables that come in as GET vars
 *  - server_id (optional)
 *  - container (rawurlencoded) (optional)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';
require 'templates/template_config.php';

$server_id = (isset($_REQUEST['server_id']) ? $_REQUEST['server_id'] : '');
$ldapserver = new LDAPServer($server_id);

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );
if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$step = isset( $_REQUEST['step'] ) ? $_REQUEST['step'] : 1; // defaults to 1
$container = $_REQUEST['container'];

$server_menu_html = server_select_list($server_id,true);

include './header.php'; ?>

<body>

<h3 class="title"><?php echo $lang['createf_create_object']?></h3>
<h3 class="subtitle"><?php echo $lang['createf_choose_temp']?></h3>
<center><h3><?php echo $lang['createf_select_temp']?></h3></center>

<form action="creation_template.php" method="post">
	<input type="hidden" name="container" value="<?php echo htmlspecialchars( $container ); ?>" />
	<table class="create">
	<tr>
		<td class="heading"><?php echo $lang['server']; ?>:</td>
		<td><?php echo $server_menu_html; ?></td>
	</tr>

	<tr>
		<td class="heading"><?php echo $lang['template']; ?>:</td>
		<td>

		<table class="template_display">
		<tr>
			<td>
			<table class="templates">

<?php $count = count( $templates );
$i = -1;

foreach( $templates as $name => $template ) {
	$i++;

	// Balance the columns properly
	if( ( count( $templates ) % 2 == 0 && $i == intval( $count / 2 ) ) ||
		( count( $templates ) % 2 == 1 && $i == intval( $count / 2 ) + 1 ) )

		echo "</table></td><td><table class=\"templates\">";

	// Check and see if this template should be shown in the list
	$isValid = false;

	if( isset($template['regexp'] ) ) {
		if( @preg_match( "/".$template['regexp']."/i", $container ) ) {
			$isValid = true;
		}

	} else {
		$isValid = true;
	} ?>

			</td>
		</tr>

		<tr>
			<td>
			<input type="radio" name="template" value="<?php echo htmlspecialchars($name);?>"
				id="<?php echo htmlspecialchars($name); ?>"

	<?php if( 0 == strcasecmp( 'Custom', $name ) ) echo ' checked';
	if( ! $isValid ) echo ' disabled'; ?> />

			</td>

			<td class="icon">
			<label for="<?php echo htmlspecialchars($name);?>">
			<img src="<?php echo $template['icon']; ?>" />
			</label>
			</td>

			<td>
			<label for="<?php echo htmlspecialchars($name);?>">

	<?php if( 0 == strcasecmp( 'Custom', $template['desc'] ) ) echo '<b>';

	if( ! $isValid )
		echo "<span style=\"color: gray\"><acronym title=\"This template is not allowed in this container\">";

	echo htmlspecialchars( $template['desc'] );

	if( ! $isValid ) echo "</acronym></span>";
	if( 0 == strcasecmp( 'Custom', $template['desc'] ) ) echo '</b>'; ?>

			</label>
			</td>
		</tr>

<?php } // end foreach ?>

		</table>
		</td>
	</tr>
	</table>
	</td>
</tr>

<tr>
	<td colspan="2"><center><input type="submit" name="submit" value="<?php echo $lang['proceed_gt']?>" /></center></td>
</tr>

</table>
</form>
</body>
</html>
