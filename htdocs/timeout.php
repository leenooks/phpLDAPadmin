<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/timeout.php,v 1.5.2.1 2007/12/21 12:11:55 wurley Exp $

/**
 * Time out page to be displayed on the right frame
 *
 * Variables that come in as GET vars:
 *  - server_id
 *
 * @package phpLDAPadmin
 */

/**
 */

require './common.php';

if (! isset($ldapserver)) {
	header("Location: index.php");
	die();
}

include './header.php';

# If $session_timeout not defined, use ( session_cache_expire() - 1 )
$session_timeout = $ldapserver->session_timeout ? $ldapserver->session_timeout : session_cache_expire()-1;
?>

<h3 class="title"><?php echo $ldapserver->name; ?></h3>
<br />
<br />
<center>
	<b><?php printf('%s %s %s',_('Your Session timed out after'),$session_timeout,_('min. of inactivity. You have been automatically logged out.')); ?></b>
	<br />
	<br />
	<?php echo _('To log back in please click on the following link:'); ?><br />
	<a href="cmd.php?cmd=login_form&server_id=<?php echo $ldapserver->server_id; ?>"><?php echo _('Login...'); ?></a>
</center>

</body>
</html>
