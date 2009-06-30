<?php 

/*
 * create_form.php
 * The menu where the user chooses an RDN, Container, and Template for creating a new entry.
 * After submitting this form, the user is taken to their chosen Template handler.
 *
 * Variables that come in as GET vars 
 *  - server_id (optional)
 *  - container (rawurlencoded) (optional)
 */

require 'common.php';

$server_id = $_REQUEST['server_id'];
$step = isset( $_REQUEST['step'] ) ? $_REQUEST['step'] : 1; // defaults to 1
$container = $_REQUEST['container'];

if( is_server_read_only( $server_id ) )
	pla_error( "You cannot perform updates while server is in read-only mode" );

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );
$server_name = $servers[$server_id]['name'];

// build the server drop-down html 
$server_menu_html = '<select name="server_id">';
$js_dn_list = '';
foreach( $servers as $id => $server ) { 
	if( $server['host'] ) { 
		$server_menu_html .= '<option value="'.$id.'"' . ( $id==$server_id? ' selected' : '' ) . '>';
		$server_menu_html .= $server['name'] . '</option>';
	}
}
$server_menu_html .= '</select>';

?>

<?php include 'header.php'; ?>

<body>

<h3 class="title">Create Object</h3>
<h3 class="subtitle">Choose a template</h3>
	<center><h3>Select a template for the creation process</h3></center>
	<form action="creation_template.php" method="post">
	<input type="hidden" name="container" value="<?php echo htmlspecialchars( $container ); ?>" />
	<table class="create">
	<tr>
		<td class="heading">Server:</td>
		<td><?php echo $server_menu_html; ?></td>
	</tr>
	<tr>
		<td class="heading">Template:</td>
		<td>
			<table class="templates">

			<?php foreach( $templates as $name => $template ) { ?>
			<tr>
				<td><input type="radio"
					   name="template"
					   value="<?php echo htmlspecialchars($name);?>"
					   id="<?php echo htmlspecialchars($name); ?>" /></td>
				<td><label for="<?php echo htmlspecialchars($name);?>">
					<img src="<?php echo $template['icon']; ?>" /></label></td>
				<td><label for="<?php echo htmlspecialchars($name);?>">
					<?php echo htmlspecialchars( $template['desc'] ); ?></label></td>
			</tr>
			<?php } ?>
			</table>
		</td>
	</tr>

	<tr>
		<td colspan="2"><center><input type="submit" name="submit" value="Proceed &gt;&gt;" /></center></td>
	</tr>

	</table>

	</form>

</body>
</html>
