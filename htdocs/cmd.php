<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/cmd.php,v 1.3.2.1 2007/12/21 12:11:55 wurley Exp $

/**
 * @package phpLDAPadmin
 */

require_once './common.php';

$body = new block();
$file = '';
$cmd = get_request('cmd','REQUEST');
$meth = get_request('meth','REQUEST');

ob_start();

if (is_null($cmd)) {
	$cmd = 'welcome';
}

switch ($cmd) {
	case '_debug' :
		debug_dump($_REQUEST,1);
		$file = '';
		break;

	default :
		if (file_exists(HOOKSDIR.$cmd.'.php'))
			$file = HOOKSDIR.$cmd.'.php';
		elseif (file_exists(HTDOCDIR.$cmd.'.php'))
			$file = HTDOCDIR.$cmd.'.php';
		else
			$file = 'welcome.php';
}

if ($file) {
	include $file;
}

$body->SetBody(ob_get_contents());
ob_end_clean();

if (DEBUG_ENABLED)
   debug_log('Ready to render page for command [%s,%s].',128,__FILE__,__LINE__,__METHOD__,$cmd,$file);

$www = new page($ldapserver->server_id);
$www->block_add('body',$body);

if ($meth == 'get_body')
	$www->body(true);
else
	$www->display();
?>
