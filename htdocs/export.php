<?php
/**
 * Performs the export of data from the LDAP server
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';
require LIBDIR.'export_functions.php';

# Prevent script from bailing early for long search
@set_time_limit(0);

$request = array();
$request['file'] = get_request('save_as_file') ? true : false;
$request['exporter'] = new Exporter($app['server']->getIndex(),get_request('exporter_id','REQUEST'));
$request['export'] = $request['exporter']->getTemplate();
$types = $request['export']->getType();

# send the header
if ($request['file']) {
	$obStatus = ob_get_status();
	if (isset($obStatus['type']) && $obStatus['type'] && $obStatus['status']) 
		ob_end_clean();

	header('Content-type: application/download');
	header(sprintf('Content-Disposition: inline; filename="%s.%s"','export',$types['extension'].($request['export']->isCompressed() ? '.gz' : '')));
	echo $request['export']->export();
	die();

} else {
	print '<span style="font-size: 14px; font-family: courier;"><pre>';
	echo htmlspecialchars($request['export']->export());
	print '</pre></span>';
}
?>
