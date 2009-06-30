<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/export.php,v 1.18 2007/12/15 07:50:30 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

# Fix a bug with IE:
ini_set('session.cache_limiter','');

require LIBDIR.'export_functions.php';

if (! $_SESSION['plaConfig']->isCommandAvailable('export'))
	pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('export')));

$entry['base_dn'] = get_request('dn');
$entry['format'] = get_request('format','POST',false,'unix');
$entry['scope'] = get_request('scope','POST',false,'base');
$entry['filter'] = get_request('filter','POST',false,'objectclass=*');
$entry['attr'] = get_request('attributes');
$entry['sys_attr'] = get_request('sys_attr');
$entry['file'] = get_request('save_as_file') ? true : false;
$entry['exporter_id'] = get_request('exporter_id');

if ($entry['filter']) {
	$entry['filter'] = preg_replace('/\s+/','',$entry['filter']);
	$attributes = split(',',preg_replace('/\s+/','',$entry['attr']));

} else {
	$attributes = array();
}

# Add system attributes if needed
if ($entry['sys_attr']) {
	array_push($attributes,'*');
	array_push($attributes,'+');
}

(! is_null($entry['exporter_id'])) or pla_error(_('You must choose an export format.'));
isset($exporters[$entry['exporter_id']]) or pla_error(_('Invalid export format'));

# Initialisation of other variables
$friendly_rdn = get_rdn($entry['base_dn'],1);
$extension = $exporters[$entry['exporter_id']]['extension'];

# default case not really needed
switch ($entry['format']) {
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
$plaLdapExporter = new PlaLdapExporter($ldapserver->server_id,$entry['filter'],$entry['base_dn'],$entry['scope'],$attributes);

# the decorator do it that way for the moment
$exporter = null;

switch ($entry['exporter_id']) {
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
if ($entry['file']) {
	if (ob_get_level()) ob_end_clean();
	header('Content-type: application/download');
	header(sprintf('Content-Disposition: filename="%s.%s"',$friendly_rdn,$exporters[$entry['exporter_id']]['extension'].($exporter->isCompressed()?'.gz':'')));
	$exporter->export();
	die();

} else {
	print '<span style="font-size: 14px; font-family: courier;"><pre>';
	$exporter->export();
	print '</pre></span>';
}
?>
