<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/page.php,v 1.3.2.5 2007/12/21 12:51:28 wurley Exp $

/**
 * Page Rendering Functions
 * @package phpLDAPadmin
 */

class page {
	# pre-HTML headers
	protected $_pageheader;

	# Items to get into the <head>
	protected $_head;

	# Settings for this application
	protected $_app;

	# Default values array.
	protected $_default;

	public function __construct($server_id=null) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with [%s]',129,__FILE__,__LINE__,__METHOD__,$server_id);

		# To be defined in a configuration file.
		$this->_app['title'] = 'phpLDAPadmin';

		# Default Values for configurable items.
		$this->_default['stylecss'] = CSSDIR.'style.css';
		$this->_default['logo'] = 'images/logo_small.jpg';
		$this->_default['sysmsg']['error'] = 'images/warning.png';
		$this->_default['sysmsg']['warn'] = 'images/notice.png';
		$this->_default['sysmsg']['info'] = 'images/light-big.png';

		# Capture any output so far (in case we send some headers below) - there shouldnt be any output anyway.
		$preOutput = '';

		# Try and work around if php compression is on, or the user has set compression in the config.
		# type = 1 for user gzip, 0 for php.ini gzip.
		$obStatus = ob_get_status();
		if ($obStatus['type'] && $obStatus['status']) {
			$preOutput = ob_get_contents();
			ob_end_clean();
		}

		header('Content-type: text/html; charset="UTF-8"');
		if (isset($_SESSION['plaConfig'])
			&& $_SESSION['plaConfig']->GetValue('appearance','compress')
			&& eregi('gzip',$_SERVER['HTTP_ACCEPT_ENCODING'])
			&& ! ini_get('zlib.output_compression')) {

			header('Content-Encoding: gzip');

			if (DEBUG_ENABLED)
				debug_log('Sent COMPRESSED header to browser and discarded (%s)',129,__FILE__,__LINE__,__METHOD__,$preOutput);
		}

		if (isset($_SESSION['plaConfig'])
			&& $_SESSION['plaConfig']->GetValue('appearance','compress')
			&& ini_get('zlib.output_compression'))
				$this->setsysmsg(array(array('title'=>_('Warning'),'body'=>_('WARNING: You cannot have PHP compression and phpLDAPadmin compression enabled at the same time. Please unset zlib.output_compression or set $config->custom->appearance[\'compress\']=false'),'type'=>'warn')));

		# Turn back on output buffering.
		ob_start();

		# Initial Values
		$this->_pageheader[] .= '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$this->_pageheader[] .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN"'."\n";
		$this->_pageheader[] .= '"http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">'."\n";
		$this->_pageheader[] .= "\n";
		$this->_pageheader[] .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="auto" lang="auto" dir="ltr">'."\n";
		$this->_pageheader[] .= "\n";

		$this->_app['logo'] = $this->_default['logo'];

		if (! is_null($server_id))
			$this->_app['urlcss'] = sprintf('%s%s',CSSDIR,$_SESSION['plaConfig']->GetValue('appearance','stylesheet'));
		else
			$this->_app['urlcss'] = sprintf('%s%s',CSSDIR,'style.css');

		$this->server_id = $server_id;
	}

	/* Add to the HTML Header */
	public function head_add($html) {
		$this->_head[] .= $html;
	}

	/* Print out the HTML header */
	private function pageheader_print() {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with ()',129,__FILE__,__LINE__,__METHOD__);

		# HTML prepage requirements.
		foreach ($this->_pageheader as $line)
			echo $line."\n";

		# Page Title
		echo '<head>';

		if (isset($_SESSION['plaConfig']))
			printf('<title>%s (%s) - %s</title>',
				$this->_app['title'],pla_version(),$_SESSION['plaConfig']->GetValue('appearance','page_title'));
		else
			printf('<title>%s - %s</title>',$this->_app['title'],pla_version());

		# Style sheet.
		printf('<link type="text/css" rel="stylesheet" href="%s" />',$this->_app['urlcss']);
		printf('<link type="text/css" rel="stylesheet" media="all" href="%sjscalendar/calendar-blue.css" title="blue" />',JSDIR);
		printf('<link type="text/css" rel="stylesheet" href="%s/phplayersmenu/layerstreemenu.css" />','../htdocs/'.JSDIR);
		printf('<link rel="shortcut icon" href="%s/images/favicon.ico" type="image/vnd.microsoft.icon" />','../htdocs/');
		echo "\n";
		printf('<script type="text/javascript" src="%spla_ajax.js"></script>',JSDIR);
		printf('<script type="text/javascript" src="%stree_hide.js"></script>',JSDIR);
		printf('<script type="text/javascript" src="%sentry_chooser.js"></script>',JSDIR);
		printf('<script type="text/javascript" src="%sto_ascii.js"></script>','../htdocs/'.JSDIR);
		printf('<script type="text/javascript" src="%sjscalendar/calendar.js"></script>','../htdocs/'.JSDIR);
		echo "\n";

		# HTML head requirements.
		if (is_array($this->_head) && count ($this->_head))
			foreach ($this->_head as $line)
				echo $line."\n";

		echo '</head>';
		echo "\n";
	}

	private function head_print() {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with ()',129,__FILE__,__LINE__,__METHOD__);

		if (isset($_SESSION['plaConfig']))
			$pagetitle = $_SESSION['plaConfig']->GetValue('appearance','page_title') ? ' - '.$_SESSION['plaConfig']->GetValue('appearance','page_title') : '';
		else
			$pagetitle = '';

		echo '<tr class="head">';

		if (is_null($this->server_id))
			printf('<td colspan=0>%s</td>','&nbsp;');
		else
			printf('<td colspan=0>%s %s</td>',$this->_app['title'],$pagetitle);

		echo '</tr>';
	}

	private function control_print() {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with ()',129,__FILE__,__LINE__,__METHOD__);

		if (! isset($this->server_id) || is_null($this->server_id)) {
			echo '&nbsp;';
			return;
		}

		echo '<table class="control" border=0>';
		echo '<tr>';

		$empty = true;

		if ($_SESSION['plaConfig']->isCommandAvailable('home')) {
			printf('<td><a href="cmd.php?cmd=welcome" title="%s"><img src="images/home-big.png" alt="%s" /><br />%s</a></td>',
				_('Home'),_('Home'),_('Home'));
			$empty = false;
		}

		if ($_SESSION['plaConfig']->isCommandAvailable('purge')) {
			printf('<td><a href="cmd.php?cmd=purge_cache" title="%s"><img src="images/trash-big.png" alt="%s" /><br />%s</a></td>',
				_('Purge caches'),_('Purge all cached data in phpLDAPadmin, including server schemas.'),_('Purge caches'));
			$empty = false;
		}

		if ($_SESSION['plaConfig']->isCommandAvailable('external_links', 'feature')) {
			printf('<td><a href="%s" target="new" title="%s"><img src="images/request-feature.png" alt="%s" /><br />%s</a></td>',
				get_href('add_rfe'),_('Request feature'),_('light'),_('Request feature'));
			$empty = false;
		}

		if ($_SESSION['plaConfig']->isCommandAvailable('external_links', 'bug')) {
			printf('<td><a href="%s" target="new" title="%s"><img src="images/bug-big.png" alt="%s" /><br />%s</a></td>',
				get_href('add_bug'),_('Report a bug'),_('bug'),_('Report a bug'));
			$empty = false;
		}

		if ($_SESSION['plaConfig']->isCommandAvailable('external_links', 'donation')) {
			printf('<td><a href="%s" target="new" title="%s"><img src="images/smile-big.png" alt="%s" /><br />%s</a></td>',
				get_href('donate'),_('Donate'),_('Donate'),_('Donate'));
			$empty = false;
		}

		if (! $_SESSION['plaConfig']->GetValue('appearance','hide_debug_info')) {
			printf('<td><a href="cmd.php?cmd=show_cache" title="%s"><img src="images/debug-cache.png" alt="%s" /><br />%s</a></td>',
				_('Show Cache'),_('Show Cache'),_('Show Cache'));
			$empty = false;
		}

		if ($_SESSION['plaConfig']->isCommandAvailable('external_links', 'help')) {
			printf('<td><a href="%s" target="new" title="%s"><img src="images/help-big.png" alt="%s" /><br />%s</a></td>',
				get_href('documentation'),_('Help'),_('Help'),_('Help'));
			$empty = false;
		}

		if ($empty) {
			echo '<td></td>';
		}

		printf('<td>&nbsp;</td><td class="logo"><img src="%s" alt="Logo" class="logo" /></td>',$this->_app['logo']);

		echo '</tr>';
		echo '</table>';
	}

	protected function tree() {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with ()',129,__FILE__,__LINE__,__METHOD__);

		if (! isset($_SESSION['plaConfig']))
			return;

		$server_id = is_null($this->server_id) ? min($_SESSION['plaConfig']->ldapservers->GetServerList()) : $this->server_id;

		echo '<td class="tree" colspan=2>';
		if (count(server_info_list(true)) > 1) {
			echo '<form name="server_select" action="cmd.php" method="post">';
			echo '<table class="server_select"><tr><td>';
			printf('%s%s<br />%s',_('Server Select'),_(':'),
				server_select_list($server_id,false,'server_id',sprintf("onchange=\"tree_unhide('server_id',%s)\"",$server_id)));
			echo '</td></tr></table>';
			echo '</form>';
			echo "\n\n";
		}

		foreach ($_SESSION['plaConfig']->ldapservers->GetServerList() as $server_id) {
			printf('<div id="SID_%s" style="display: %s">',$server_id,($server_id == $this->server_id) ? 'block': 'none');
			$ldapserver = $_SESSION['plaConfig']->ldapservers->Instance($server_id);

			$tree = Tree::getInstance($ldapserver->server_id);

			$tree->draw();
			echo '</div>';
		}
		echo '</td>';
	}

	public function block_add($side,$object) {
		if (! is_object($object))
			error('not an object');

		$this->_block[$side][] = $object;
	}

	private function block_print($side) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with ()',129,__FILE__,__LINE__,__METHOD__);

		if (! isset($this->_block[$side]))
			return;

		printf('<td class="%s" colspan=2>',$side);
		foreach ($this->_block[$side] as $object)
			echo $object->draw($side);
		echo '</td>';
	}

	private function sysmsg() {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with ()',129,__FILE__,__LINE__,__METHOD__);

		if (isset($this->sysmsg)) {
			foreach ($this->sysmsg as $index => $details) {
				switch ($details['type']) {
					case 'error':
						$icon = $this->_default['sysmsg']['error'];
						break;

					case 'warn':
						$icon = $this->_default['sysmsg']['warn'];
						break;

					case 'info':
					default:
						$icon = $this->_default['sysmsg']['info'];
						break;
				}

				if (isset($details['title']))
					printf('<tr><td class="icon" rowspan=2 align="right"><img src="%s" alt="%s" /></td><td class="head" align="right">%s</td></tr>',
						$icon,$details['type'],$details['title']);

				if (isset($details['body']))
					if (is_array($details['body'])) {
						echo '<tr><td class="body">';
						foreach ($details['body'] as $line)
							printf('%s<br />',$line);
						echo '</td></tr>';

					} else
						printf('<tr><td class="body">%s</td></tr>',$details['body']);
			}
		}
	}

	public function body($compress=false) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with (%s)',129,__FILE__,__LINE__,__METHOD__,$compress);

		# If the body is called via AJAX, and compression is enable, we need to compress the output
		if ($compress && ob_get_level()) {
			ob_end_clean();
			ob_start();
		}

		# System Message
		if (isset($_SESSION['sysmsg']) && $_SESSION['sysmsg']) {
			$this->setsysmsg($_SESSION['sysmsg']);
			unset($_SESSION['sysmsg']);
		}

		if (isset($this->sysmsg)) {
			echo '<table class="sysmsg">';
			$this->sysmsg();
			echo '</table>';
			echo "\n";
		}

		if (isset($this->_block['body'])) {
			foreach ($this->_block['body'] as $object)
				echo $object->draw('body');
		}

		if ($compress && ob_get_level() && isset($_SESSION['plaConfig'])
			&& $_SESSION['plaConfig']->GetValue('appearance','compress')
			&& ! ini_get('zlib.output_compression')
			&& eregi('gzip',$_SERVER['HTTP_ACCEPT_ENCODING'])) {

			$output = ob_get_contents();
			ob_end_clean();

			if (DEBUG_ENABLED)
				debug_log('Sending COMPRESSED output to browser[(%s),%s]',129,__FILE__,__LINE__,__METHOD__,
					strlen($output),$output);

			print gzencode($output);
		}
	}

	private function footer_print() {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with ()',129,__FILE__,__LINE__,__METHOD__);

		printf('<tr class="foot"><td colspan=3>%s</td></tr>',pla_version());
	}

	public function display($filter=array()) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with [%s]',129,__FILE__,__LINE__,__METHOD__,$filter);

		# Control what is displayed.
		$display = array(
			'tree'=>true
		);

		$display = array_merge($display,$filter);

		# HTML Header
		$this->pageheader_print();
		echo "\n";

		# Start of body
		# Page Header
		echo '<body>';
		echo "\n";
		echo '<table class="page" border=0 width=100%>';

		$this->head_print();
		echo "\n";

		# Control Line
		echo '<tr class="control"><td colspan=3>';
		$this->control_print();
		echo '</td></tr>';
		echo "\n";

		# Left Block
		echo '<tr>';

		if ($display['tree']) {
#			$this->block_print('tree');
			$this->tree();
			echo "\n";
		}

		echo '<td class="body" width=80%>';
		echo '<div id="main_page">';
		echo "\n";
		$this->body();
		echo '</div>';
		echo '</td>';
		echo '</tr>';
		echo "\n";

		# Page Footer
		$this->footer_print();

		# Finish HTML
		echo '</table>';
		echo '</body>';
		echo '</html>';

		# compress output
		if (ob_get_level() && isset($_SESSION['plaConfig'])
			&& $_SESSION['plaConfig']->GetValue('appearance','compress')
			&& ! ini_get('zlib.output_compression')
			&& eregi('gzip',$_SERVER['HTTP_ACCEPT_ENCODING'])) {

			$output = ob_get_contents();
			ob_end_clean();

			if (DEBUG_ENABLED)
				debug_log('Sending COMPRESSED output to browser[(%s),%s]',129,__FILE__,__LINE__,__METHOD__,
					strlen($output),$output);

			print gzencode($output);
		}
	}

	public function setsysmsg($data) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with [%s]',129,__FILE__,__LINE__,__METHOD__,$data);

		if (! is_array($data))
			return;

		if (isset($this->sysmsg))
			$nummsg = count($this->sysmsg);
		else
			$nummsg = 1;

		foreach ($data as $msgno => $msgarray) {
			$msgindex = $msgno + $nummsg;

			foreach (array('title','body','type') as $index)
				if (isset($msgarray[$index]))
					$this->sysmsg[$msgindex][$index] = $msgarray[$index];
		}
	}
}

class block {
	private $title;
	private $body;
	private $foot;

	public function __construct() {
	}

	public function setTitle($html) {
		$this->title = $html;
	}

	public function setBody($html) {
		$this->body = $html;
	}

	public function setFooter($html) {
		$this->foot = $html;
	}

	public function draw($side) {
		$output = '';

		$output .= sprintf('<table class="%s">',$side);
		if (isset($this->body['title']))
			$output .= sprintf('<tr><td class="head">%s</td></tr>',$this->title);

		if (isset($this->body['body']))
			$output .= sprintf('<tr><td>%s</td></tr>',$this->body);

		if (isset($this->body['footer']))
			$output .= sprintf('<tr><td class="foot">%s</td></tr>',$this->foot);
		$output .= '</table>';

		return $output;
	}
}
?>
