<?php
/*
 * Time out page to be displayed on the right frame
 *
 * Variables that come in as GET vars:
 *  - server_id
 */

require realpath( './common.php' );

$server_id = (isset($_GET['server_id']) ? $_GET['server_id'] : '');

include realpath( './header.php' );

global $servers, $lang;

// If $session_timeout not defined, use ( session_cache_expire() - 1 )
if ( ! isset( $servers[ $server_id ][ 'session_timeout' ] ) )
	$session_timeout = session_cache_expire()-1;
else
	$session_timeout = $servers[ $server_id ][ 'session_timeout' ];

?>

<h3 class="title"><?php echo $servers[ $server_id ]['name']; ?></h3>
<br />
<br />
<center>
<b><?php echo $lang['session_timed_out_1'] . " " . $session_timeout . " " . $lang['session_timed_out_2']; ?></b><br /><br />
<?php echo $lang['log_back_in']; ?><br />
<a href="login_form.php?server_id=<?php echo $server_id; ?>"><?php echo $lang['login_link']; ?></a>
</center>
</body>
</html>
