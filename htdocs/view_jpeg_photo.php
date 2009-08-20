<?php
/**
 * This script will display the contents of the jpegPhoto attribute to the browser.
 * A server ID and DN must be provided in the GET attributes.
 * Optionally an attr name, index, type and filename can be supplied.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

$request = array();
$request['dn'] = get_request('dn','GET');
$request['attr'] = strtolower(get_request('attr','GET',false,'jpegphoto'));
$request['index'] = get_request('index','GET',false,0);
$request['type'] = get_request('type','GET',false,'image/jpeg');
$request['filename'] = get_request('filename','GET',false,sprintf('%s.jpg',get_rdn($request['dn'],true)));
$request['location'] = get_request('location','GET',false,'ldap');

switch ($request['location']) {
	case 'session':
		if (isset($_SESSION['tmp'][$request['attr']][$request['index']])) {
			$jpeg_data = $_SESSION['tmp'];
			unset($_SESSION['tmp'][$request['attr']][$request['index']]);
		}

		break;

	case 'ldap':
	default:
		$jpeg_data = $app['server']->getDNAttrValues($request['dn'],null,LDAP_DEREF_NEVER,array($request['attr']));

		break;
}

if (! isset($jpeg_data[$request['attr']][$request['index']])) {
	if (function_exists('imagecreate')) {
		$im = imagecreate(160,30);
		if (is_resource($im)) {
			header('Content-type: image/png');

			# Set the background
			imagecolorallocatealpha($im,0xFC,0xFC,0xFE,127);
			$text_color = imagecolorallocate($im,0,0,0);
			imagestring($im,4,3,5,_('Image not available'),$text_color);
			imagepng($im);
			imagedestroy($im);

			die();
		}
	}

	# We cant display an error, but we can set a system message, which will be display on the next page render.
	system_message(array(
		'title'=>_('No image available'),
		'body'=>sprintf(_('Could not fetch jpeg data from LDAP server for attribute [%s].'),$request['attr']),
		'type'=>'warn'));

	die();
}

if (! is_array($jpeg_data[$request['attr']]))
	$jpeg_data[$request['attr']] = array($jpeg_data[$request['attr']]);

$obStatus = ob_get_status();
if (isset($obStatus['type']) && $obStatus['type'] && $obStatus['status'])
	ob_end_clean();

header(sprintf('Content-type: %s',$request['type']));
header(sprintf('Content-disposition: inline; filename="%s"',$request['filename']));
echo $jpeg_data[$request['attr']][$request['index']];
die();
?>
