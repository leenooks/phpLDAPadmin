<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/ldif_import_form.php,v 1.19 2005/08/16 09:02:50 wurley Exp $
 
/**
 * Displays a form to allow the user to upload and import
 * an LDIF file.
 *
 * Variables expected as GET vars:
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$server_id = (isset($_GET['server_id']) ? $_GET['server_id'] : '');
$ldapserver = $ldapservers->Instance($server_id);

if( $ldapserver->isReadOnly() )
	pla_error( $lang['no_updates_in_read_only_mode'] );
if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

include './header.php'; ?>

<body>

<h3 class="title"><?php echo $lang['import_ldif_file_title']?></h3>
<h3 class="subtitle"><?php echo $lang['server']?>: <b><?php echo htmlspecialchars( $ldapserver->name ); ?></b></h3>

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
