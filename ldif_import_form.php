<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/ldif_import_form.php,v 1.16 2004/10/24 23:51:49 uugdave Exp $
 

/* 
 * ldif_import_form.php
 * Displays a form to allow the user to upload and import
 * an LDIF file.
 *
 * Variables expected as GET vars:
 *  - server_id
 */

require './common.php';

$server_id = $_GET['server_id'];
$server_name = $servers[$server_id]['name'];

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

include './header.php'; ?>

<body>

<h3 class="title"><?php echo $lang['import_ldif_file_title']?></h3>
<h3 class="subtitle"><?php echo $lang['server']?>: <b><?php echo htmlspecialchars( $server_name ); ?></b></h3>

<br />
<br />
<?php echo $lang['select_ldif_file']?><br />
<br />

<form action="ldif_import.php" method="post" class="new_value" enctype="multipart/form-data">

<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />

<input type="file" name="ldif_file" /> <br />
<div style="margin-top: 5px;"><input type="checkbox" name="continuous_mode" value="1" /><span style="font-size: 90%;"><?php echo $lang['dont_stop_on_errors']; ?></span></div>
<div style="margin-top:10px;">
<input type="submit" value="<?php echo $lang['proceed_gt']?>" />
</div>
<?php
        if( ! ini_get( 'file_uploads' ) )
            echo "<br /><small><b>" . $lang['warning_file_uploads_disabled'] . "</b></small><br />";
        else
            echo "<br /><small><b>" . sprintf( $lang['max_file_size'], ini_get( 'upload_max_filesize' ) ) . "</b></small><br />";
?>


</form>

</body>
</html>
