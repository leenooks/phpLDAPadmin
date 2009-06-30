<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/modification/user.php,v 1.4 2004/03/19 20:13:10 i18phpldapadmin Exp $
 

/*
 * User modification template. All phpLDAPadmin templates can assume that the following
 * variables are defined externally for them:
 *   $server_id
 *   $dn
 * Other variables will need to be manually extracted from the $_GET or $_POST arrays.
 * It may also be assumed that all functions in functions.php and schema_functions.php
 * are available for use.
 */

$rdn = get_rdn( $dn );
$user_name = explode( '=', $rdn );
$user_name = $user_name[1];
$encoded_dn = rawurlencode( $dn );

$server_name = $servers[$server_id]['name'];

include 'header.php';
?>

<body>

<h3 class="title">Editing User: <b><?php echo htmlspecialchars( utf8_decode( $user_name ) ); ?></b></h3>
<h3 class="subtitle">
	Server: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; 
	LDAP <acronym title="Distinguished Name">DN</acronym>: 
		<b><?php echo htmlspecialchars( utf8_decode( $dn ) ); ?></b>
</h3>

<center><small>
	Using the <b>user</b> template. 
	You may switch to the <a href="<?php echo $default_href; ?>">default template</a>
</small></center>


<center><h1><tt>TODO: FinishMe</tt></h1></center>
