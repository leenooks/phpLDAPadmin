<?php
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

if (! isset($ldapserver))
	header("Location: index.php");

include './header.php';

global $lang;

# If $session_timeout not defined, use ( session_cache_expire() - 1 )
$session_timeout = $ldapserver->session_timeout ? $ldapserver->session_timeout : session_cache_expire()-1;
?>

<h3 class="title"><?php echo $ldapserver->name; ?></h3>
<br />
<br />
<center>
	<b><?php printf('%s %s %s',$lang['session_timed_out_1'],$session_timeout,$lang['session_timed_out_2']); ?></b>
	<br />
	<br />
	<?php echo $lang['log_back_in']; ?><br />
	<a href="login_form.php?server_id=<?php echo $ldapserver->server_id; ?>"><?php echo $lang['login_link']; ?></a>
</center>

</body>
</html>
