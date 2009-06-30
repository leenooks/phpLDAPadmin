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

<h3 class="title">Import LDIF File</h3>
<h3 class="subtitle">Server: <b><?php echo htmlspecialchars( $server_name ); ?></b></h3>


<br />
<br />
<center><i>This is an experimental and untested feature. Proceed at your own risk.</i>
<br />
<i>The add operation is the only operation currently supported.</i>
</center>
<br />
<br />

Select an LDIF file:<br />
<br />

<form action="ldif_import.php" method="post" class="new_value" enctype="multipart/form-data">
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="file" name="ldif_file" /><br />
<br />
<input type="submit" value="Proceed &gt;&gt;" />
</form>

</body>
</html>
