<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/show_cache.php,v 1.3.2.3 2008/12/12 12:20:22 wurley Exp $

/**
 * This script shows the contents of the cache for debugging purposes
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$entry = array();
$entry['key'] = get_request('key','REQUEST');
$entry['index'] = get_request('index','REQUEST');

if (isset($entry['index']) && isset($entry['key'])) {
	list($entry['server'],$entry['x']) = split(':',$entry['index']);
	debug_dump($_SESSION[$entry['key']][$entry['server']][$entry['x']],1);
}
if ($entry['key'])
	debug_dump($_SESSION[$entry['key']],1);

if (! $_SESSION[APPCONFIG]->GetValue('appearance','hide_debug_info')) {
	echo '<div style="font-size: 11px"><ul>';
	foreach (array_keys($_SESSION) as $key) {
		if (($key == 'cache') && is_array($_SESSION[$key]))
			foreach (array_keys($_SESSION['cache']) as $server) {
				foreach (array_keys($_SESSION['cache'][$server]) as $x) {
					$index = sprintf('%s:%s',$server,$x);

					printf('<li><span id="%s"><a href="javascript:get(\'%s\',\'%s\');">%s</a></span><div id="%sloading" style="display: none" ></div></li>',
						$key.$index,$key,$index,$key.'.'.$index,$key.$index,$key.$index);
				}
			}
		else
			printf('<li><span id="%s"><a href="javascript:get(\'%s\', \'\');">%s</a></span><div id="%sloading" style="display: none" ></div></li>',
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

		obj.innerHTML = '<img src="<?php echo IMGDIR ?>/ajax-spinner.gif" /> Loading...';
		makePOSTRequest('cmd.php',poststr,'alertCacheContents','cancelCacheContents');
	}

	function close(key,xx) {
		document.getElementById(key + xx).innerHTML = '<a href="javascript:get(\'' + key + '\', \'' + xx + '\');">' + key + '.' + xx + '<\/a>';
	}
</script>
