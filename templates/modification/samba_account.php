<?php

/**
 * samba_account.php
 * Template for displaying a sambasamaccount object.
 * 
 * Variables that come in as GET vars:
 *  - dn
 *  - server_id
 *  - modified_attrs (optional) an array of attributes to highlight as 
 *                              they were changed by the last operation
 *
 * @package phpLDAPadmin
 *
 * @author The phpLDAPadmin development team 
 **/

include 'header.php';
$rdn = get_rdn( $dn );
$sambaAccount = explode( '=', $rdn, 2 );
$sambaAccountRdn = $sambaAccount[1];
$attrs = get_object_attrs( $server_id, $dn, false, get_view_deref_setting());
?>
<body>
<h3 class="title"><?php echo $lang['samba_account'] . ': '; ?> <b><?php echo htmlspecialchars( $sambaAccountRdn ); ?></b></h3>
<center><small>
	<?php echo $lang['using'] . ' <b>' . $lang['samba_account_lcase'] . '</b>' . $lang['template'] . '.'; ?> 
	<?php echo $lang['switch_to'] . '<a href=' . $default_href . '>' . $lang['default_template'] . '</a>'; ?>
</small></center>
<h2 style="text-align:center;color:#016;text-decoration:underline;">TO DO</h2>
</body>
</html>
