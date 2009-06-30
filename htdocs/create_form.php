<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/create_form.php,v 1.30.2.1 2005/10/09 09:07:21 wurley Exp $

/**
 * The menu where the user chooses an RDN, Container, and Template for creating a new entry.
 * After submitting this form, the user is taken to their chosen Template handler.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars
 *  - container (rawurlencoded) (optional)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';
require TMPLDIR.'template_config.php';

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );
if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$step = isset( $_REQUEST['step'] ) ? $_REQUEST['step'] : 1; // defaults to 1
$container = $_REQUEST['container'];

$server_menu_html = server_select_list($ldapserver->server_id,true);

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

<?php
$i = -1;

	if ($config->GetValue('template_engine','enable')) {
		$template_xml = new Templates($ldapserver->server_id);

		if ($config->GetValue('template_engine','disable_old'))
		        $templates = $template_xml->getTemplates();

		else
		        $templates = array_merge($template_xml->getTemplates(),$templates);
	}

	# Remove non-visable templates.
	foreach ($templates as $index => $template)
		if (isset($template['visible']) && (! $template['visible']))
			unset ($templates[$index]);

$templates['custom']['title'] = 'Custom';
$templates['custom']['icon'] = 'images/object.png';

$count = count( $templates );
foreach( $templates as $name => $template ) {
	$i++;

	# If the template doesnt have a title, we'll use the desc field.
	$template['desc'] = isset($template['title']) ? $template['title'] : $template['desc'];

	# Balance the columns properly
	if( ( count( $templates ) % 2 == 0 && $i == intval( $count / 2 ) ) ||
		( count( $templates ) % 2 == 1 && $i == intval( $count / 2 ) + 1 ) )

		echo "</table></td><td><table class=\"templates\">";

	# Check and see if this template should be shown in the list
	$isValid = false;

	if( isset($template['regexp'] ) ) {
		if( @preg_match( "/".$template['regexp']."/i", $container ) ) {
			$isValid = true;
		}

	} else {
		$isValid = true;

	if (isset($template['invalid']) && $template['invalid'])
		$isValid = false;
	} ?>

			</td>
		</tr>

		<tr>
<?php
	if (isset($template['invalid']) && $template['invalid'] || (isset($template['handler']) && ! file_exists(TMPLDIR.'creation/'.$template['handler']))) {
?>
			<td class="icon">
			<img src="images/error.png" />
			</td>
<?php
	} else {
?>

			<td>
			<input type="radio" name="template" value="<?php echo htmlspecialchars($name);?>"
				id="<?php echo htmlspecialchars($name); ?>"

	<?php
if( 0 == strcasecmp( 'Custom', $name ) ) echo ' checked';
	if( ! $isValid ) echo ' disabled';
?> />

			</td>
<?php
	}
?>

			<td class="icon">
			<label for="<?php echo htmlspecialchars($name);?>">
			<img src="<?php echo $template['icon']; ?>" />
			</label>
			</td>

			<td>
			<label for="<?php echo htmlspecialchars($name);?>">

	<?php if( 0 == strcasecmp( 'Custom', $template['desc'] ) ) echo '<b>';

	if( ! $isValid )
		if (isset($template['invalid']) && $template['invalid'])
			printf('<span style="color: gray"><acronym title="%s">',$lang['template_invalid']);
		else
			printf('<span style="color: gray"><acronym title="%s">',$lang['template_restricted']);

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
