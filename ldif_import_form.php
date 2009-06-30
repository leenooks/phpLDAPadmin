<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/ldif_import_form.php,v 1.11 2004/04/20 12:36:35 uugdave Exp $
 

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

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

include 'header.php'; ?>

<body>

<h3 class="title"><?php echo $lang['import_ldif_file_title']?></h3>
<h3 class="subtitle"><?php echo $lang['server']?>: <b><?php echo htmlspecialchars( $server_name ); ?></b></h3>

<br />
<br />
<?php // check if file_uploads is enabled in php_ini file
ini_get("file_uploads") == 1 or pla_error("File uploads seem to have been disabled in your php configuration. Please, make sure that the option <em>file_uploads</em> has the value <em>On</em> in your  php_ini file."); ?>

<?php echo $lang['select_ldif_file']?><br />
<br />

<form action="ldif_import.php" method="post" class="new_value" enctype="multipart/form-data">

<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />

<input type="file" name="ldif_file" /> <br />
<div style="margin-top: 5px;"><input type="checkbox" name="continuous_mode" value="1" /><span style="font-size: 90%;"><?php echo $lang['dont_stop_on_errors']; ?></span></div>
<br />
<input type="submit" value="<?php echo $lang['select_ldif_file_proceed']?>" />
</form>

</body>
</html>
