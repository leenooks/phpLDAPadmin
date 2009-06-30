<?php 

/* 
 * ldif_import_form.php
 * Displays a form to allow the user to upload and import
 * an LDIF file.
 *
 * Variables expected as GET vars:
 *  - server_id
 */

require 'common.php';

$server_id = $_GET['server_id'];
$server_name = $servers[$server_id]['name'];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

include 'header.php'; ?>

<body>

<h3 class="title"><?php echo $lang['import_ldif_file_title']?></h3>
<h3 class="subtitle"><?php echo $lang['server']?>: <b><?php echo htmlspecialchars( $server_name ); ?></b></h3>

<br />
<br />

<?php echo $lang['select_ldif_file']?><br />
<br />

<form action="ldif_import.php" method="post" class="new_value" enctype="multipart/form-data">
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="file" name="ldif_file" /><br />
<br />
<input type="submit" value="<?php echo $lang['select_ldif_file_proceed']?>" />
</form>

</body>
</html>
