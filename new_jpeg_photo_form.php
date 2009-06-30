<?php 

/* 
 * new_jpeg_photo_form.php
 * Displays a form to allow the user to a jpegPhoto to an object.
 *  - dn (rawurlencoded)
 *  - server_id
 */

require 'config.php';
require_once 'functions.php';

$dn = stripslashes( rawurldecode( $_GET['dn'] ) );
$encoded_dn = rawurlencode( $dn );
$server_id = $_GET['server_id'];
$rdn = ldap_explode_dn( $dn, 0 );
$rdn = $rdn[0];
$server_name = $servers[$server_id]['name'];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

include 'header.php'; ?>

<body>

<h3 class="title">Add a <b>jpegPhoto</b> to <b><?php echo htmlspecialchars($rdn); ?></b></h3>
<h3 class="subtitle">Server2: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; Distinguished Name: <b><?php echo $dn; ?>
</b></h3>


Select a jpeg file:<br />
<br />

<form action="new_attr.php" method="post" class="new_value" enctype="multipart/form-data">
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
<input type="hidden" name="attr" value="jpegPhoto" />
<input type="file" name="jpeg_photo_file" /><br />
<br />
<input type="submit" value="Proceed &gt;&gt;" />
</form>

</body>
</html>

