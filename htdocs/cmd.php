<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/cmd.php,v 1.3.2.2 2007/12/24 10:45:57 wurley Exp $

/**
 * @package phpLDAPadmin
 */

require_once './common.php';

$www['cmd'] = get_request('cmd','REQUEST');
$www['meth'] = get_request('meth','REQUEST');

ob_start();

if (is_null($www['cmd']))
	$www['cmd'] = 'welcome';

switch ($www['cmd']) {
	case '_debug' :
		debug_dump($_REQUEST,1);
		$file = '';
		break;

	default :
		if (defined('HOOKSDIR') && file_exists(HOOKSDIR.$www['cmd'].'.php'))
			$file = HOOKSDIR.$www['cmd'].'.php';

		elseif (defined('HTDOCDIR') && file_exists(HTDOCDIR.$www['cmd'].'.php'))
			$file = HTDOCDIR.$www['cmd'].'.php';

		else
			$file = 'welcome.php';
}

if (DEBUG_ENABLED)
   debug_log('Ready to render page for command [%s,%s].',128,__FILE__,__LINE__,__METHOD__,$www['cmd'],$file);

# Create page.
$www['page'] = new page($ldapserver->server_id);

if ($file)
	include $file;

# Capture the output and put into the body of the page.
$www['body'] = new block();
$www['body']->SetBody(ob_get_contents());
$www['page']->block_add('body',$www['body']);
ob_end_clean();

if ($www['meth'] == 'get_body')
	$www['page']->body(true);
else
	$www['page']->display();
?>
