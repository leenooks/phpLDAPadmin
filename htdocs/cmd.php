<?php
/**
 * Main command page for phpLDAPadmin
 * All pages are rendered through this script.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require_once './common.php';

$www = array();
$www['cmd'] = get_request('cmd','REQUEST');
$www['meth'] = get_request('meth','REQUEST');

ob_start();

switch ($www['cmd']) {
	default:
		if (defined('HOOKSDIR') && file_exists(HOOKSDIR.$www['cmd'].'.php'))
			$app['script_cmd'] = HOOKSDIR.$www['cmd'].'.php';

		elseif (defined('HTDOCDIR') && file_exists(HTDOCDIR.$www['cmd'].'.php'))
			$app['script_cmd'] = HTDOCDIR.$www['cmd'].'.php';

		elseif (file_exists('welcome.php'))
			$app['script_cmd'] = 'welcome.php';

		else
			$app['script_cmd'] = null;
}

if (DEBUG_ENABLED)
	debug_log('Ready to render page for command [%s,%s].',128,0,__FILE__,__LINE__,__METHOD__,$www['cmd'],$app['script_cmd']);

# Create page.
# Set the index so that we render the right server tree.
$www['page'] = new page($app['server']->getIndex());

# See if we can render the command
if (trim($www['cmd'])) {
	# If this is a READ-WRITE operation, the LDAP server must not be in READ-ONLY mode.
	if ($app['server']->isReadOnly() && ! in_array(get_request('cmd','REQUEST'),$app['readwrite_cmds']))
		error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

	# If this command has been disabled by the config.
	if (! $_SESSION[APPCONFIG]->isCommandAvailable('script',$www['cmd'])) {
		system_message(array('title'=>_('Command disabled by the server configuration'),
			_('Error'),'body'=>sprintf('%s: <b>%s</b>.',_('The command could not be run'),htmlspecialchars($www['cmd'])),'type'=>'error'),'index.php');

		$app['script_cmd'] = null;
	}
}

if ($app['script_cmd'])
	include $app['script_cmd'];

# Refresh a frame - this is so that one frame can trigger another frame to be refreshed.
if (isAjaxEnabled() && get_request('refresh','REQUEST') && get_request('refresh','REQUEST') != get_request('frame','REQUEST')) {
	echo '<script type="text/javascript" language="javascript">';
	printf("ajDISPLAY('%s','cmd=refresh&server_id=%s&noheader=%s','%s');",
		get_request('refresh','REQUEST'),$app['server']->getIndex(),get_request('noheader','REQUEST',false,0),_('Auto refresh'));
	echo '</script>';
}

# Capture the output and put into the body of the page.
$www['body'] = new block();
$www['body']->SetBody(ob_get_contents());
$www['page']->block_add('body',$www['body']);
ob_end_clean();

if ($www['meth'] == 'ajax')
	$www['page']->show(get_request('frame','REQUEST',false,'BODY'),true,get_request('raw','REQUEST',false,false));
else
	$www['page']->display();
?>
