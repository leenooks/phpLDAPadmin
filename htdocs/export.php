<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/export.php,v 1.15.4.6 2005/12/10 12:03:44 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

# Fix a bug with IE:
ini_set('session.cache_limiter','');

require './common.php';
require LIBDIR.'export_functions.php';

if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$base_dn = isset($_POST['dn']) ? $_POST['dn']:NULL;
$format = isset($_POST['format']) ? $_POST['format'] : 'unix';
$scope = isset($_POST['scope']) ? $_POST['scope'] : 'base';
$filter = isset($_POST['filter']) ? $_POST['filter'] : 'objectclass=*';
$target = isset($_POST['target']) ? $_POST['target'] : 'display';
$save_as_file = isset($_POST['save_as_file']) && $_POST['save_as_file'] == 'on';

if (isset($_POST['filter'])) {
	preg_replace('/\s+/','',$_POST['filter']);
	$attributes = split(',',preg_replace('/\s+/','',$_POST['attributes']));

} else {
	$attributes = array();
}

# add system attributes if needed
if (isset($_POST['sys_attr'])) {
	array_push($attributes,'*');
	array_push($attributes,'+');
}

isset($_POST['exporter_id']) or pla_error(_('You must choose an export format.'));
$exporter_id = $_POST['exporter_id'];
isset($exporters[$exporter_id]) or pla_error(_('Invalid export format'));

# Initialisation of other variables
$friendly_rdn = get_rdn($base_dn,1);
$extension = $exporters[$exporter_id]['extension'];

# default case not really needed
switch ($format) {
	case 'win':
		$br = "\r\n";
		break;
	case 'mac':
		$br = "\r";
		break;
	case 'unix':
	default:
		$br = "\n";
}

# get the decoree,ie the source
$plaLdapExporter = new PlaLdapExporter($ldapserver->server_id,$filter,$base_dn,$scope,$attributes);

# the decorator do it that way for the moment
$exporter = null;

switch ($exporter_id) {
	case 0:
		$exporter = new PlaLdifExporter($plaLdapExporter);
		break;

	case 1:
		$exporter = new PlaDsmlExporter($plaLdapExporter);
		break;

	case 2:
		$exporter = new PlaVcardExporter($plaLdapExporter);
		break;

	case 3:
		$exporter = new PlaCSVExporter($plaLdapExporter);
		break;

	default:
		# truly speaking,this default case will never be reached. See check at the bottom.
		pla_error(_('No available exporter found.'));
}

# set the CLRN
$exporter->setOutputFormat($br);

if (isset($_REQUEST['compress']) && $_REQUEST['compress'] = 'on')
	$exporter->compress(true);

# prevent script from bailing early for long search
@set_time_limit(0);

# send the header
if ($save_as_file)
	header('Content-type: application/download');
else
	header('Content-type: text/plain');

header(sprintf('Content-Disposition: filename="%s.%s"',$friendly_rdn,$exporters[$exporter_id]['extension'].($exporter->isCompressed()?'.gz':'')));
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);

# and export
$exporter->export();
?>
