<?php
/**
 * This script shows the contents of the cache for debugging purposes
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

$request = array();
$request['key'] = get_request('key','REQUEST');
$request['index'] = get_request('index','REQUEST');

if (get_request('global','REQUEST'))
	$request['dumpvar'] = &$GLOBALS;
else
	$request['dumpvar'] = &$_SESSION;

if (isset($request['index']) && isset($request['key'])) {
	list($request['server'],$request['x']) = explode(':',$request['index']);
	debug_dump($request['dumpvar'][$request['key']][$request['server']][$request['x']],1);
}

if ($request['key'])
	debug_dump($request['dumpvar'][$request['key']],1);

if (! $_SESSION[APPCONFIG]->getValue('appearance','hide_debug_info')) {
	echo '<div style="font-size: 11px"><ul>';

	foreach (array_keys($request['dumpvar']) as $key) {
		if ((in_array($key,array('cache'))) && is_array($request['dumpvar'][$key]))
			foreach (array_keys($request['dumpvar'][$key]) as $server) {
				foreach (array_keys($request['dumpvar'][$key][$server]) as $x) {
					$index = sprintf('%s:%s',$server,$x);

					printf('<li><span id="%s"><a href="javascript:get(\'%s\',\'%s\');">%s</a></span><div id="%sloading" style="display: none" ></div></li>',
						$key.$index,$key,$index,$key.'.'.$index,$key.$index,$key.$index);
				}
			}
		else
			printf('<li><span id="%s"><a href="javascript:get(\'%s\',\'\');">%s</a></span><div id="%sloading" style="display: none" ></div></li>',
				$key,$key,$key,$key);
	}
	echo '</ul></div>';
}
?>

<script type="text/javascript" language="javascript">
	var http_request = false;
	var newtree_div;
	var obj_key = '';
	var obj_ind = '';
	var obj = null;

	function alertCacheContents(result) {
		if (obj) {
			obj.innerHTML = '<a href="javascript:close(\'' + obj_key + '\', \'' + obj_ind + '\')">' + obj_key + '.' + obj_ind + ' [close]<\/a>';
			obj.innerHTML += result;
			obj = null;
			newtree_div.display = 'none';
		}
	}

	function cancelCacheContents() {
		if (obj) {
			obj.innerHTML = '<a href="javascript:get(\'' + obj_key + '\', \'' + obj_ind + '\');">' + obj_key + '.' + obj_ind + '<\/a>';
			newtree_div.display = 'none';
			obj = null;
		}
	}

	function get(key,xx) {
		if (obj) cancelHttpRequest();

		obj_key = key;
		obj_ind = xx;
		obj = document.getElementById(key + xx);

		newtree_div = document.getElementById(key + xx + 'loading').style;
		newtree_div.display = 'block';
		var poststr = "cmd=show_cache&key=" + encodeURI(key);

		if (xx) {
			poststr += "&index=" + encodeURI(xx);
		}

		<?php if (get_request('global','REQUEST')) { ?>
			poststr += "&global=on" + encodeURI(xx);
		<?php } ?>
		obj.innerHTML = '<img src="<?php echo IMGDIR ?>/ajax-spinner.gif" /> <?php echo _('Loading') ?>...';
		makeHttpRequest('cmd.php',poststr,'POST','alertCacheContents','cancelCacheContents');
	}

	function close(key,xx) {
		document.getElementById(key + xx).innerHTML = '<a href="javascript:get(\'' + key + '\', \'' + xx + '\');">' + key + '.' + xx + '<\/a>';
	}
</script>
