<?php
/**
 * Imports an LDIF file to the specified LDAP server.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';
require LIBDIR.'import_functions.php';

$request = array();
$request['importer'] = new Importer($app['server']->getIndex(),'LDIF');
$request['import'] = $request['importer']->getTemplate();

$request['continuous_mode'] = get_request('continuous_mode') ? true : false;

$type = $request['import']->getType();

# Set our timelimit in case we have a lot of importing to do
@set_time_limit(0);

# String associated to the operation on the ldap server
$actionString = array(
	'add' => _('Adding'),
	'delete' => _('Deleting'),
	'modrdn' => _('Renaming'),
	'moddn' => _('Renaming'),
	'modify' => _('Modifying')
	);

# String associated with error
$actionErrorMsg = array(
	'add' => _('Could not add object'),
	'delete' => _('Could not delete object'),
	'modrdn' => _('Could not rename object'),
	'moddn' => _('Could not rename object'),
	'modify' => _('Could not modify object')
	);

$request['page'] = new PageRender($app['server']->getIndex(),get_request('template','REQUEST',false,'none'));
$request['page']->drawTitle(sprintf('<b>%s</b>',_('Import')));
$request['page']->drawSubTitle(sprintf('%s: <b>%s</b> %s: <b>%s %s %s (%s)</b>',
	_('Server'),$app['server']->getName(),
	_('File'),$request['import']->getSource('name'),number_format($request['import']->getSource('size')),_('bytes'),$type['description']));

echo '<br />';

# @todo When renaming DNs, the hotlink should point to the new entry on success, or the old entry on failure.
while (! $request['import']->eof()) {
	while ($request['template'] = $request['import']->readEntry()) {

		$edit_href = sprintf('cmd.php?cmd=template_engine&amp;server_id=%s&amp;dn=%s',$app['server']->getIndex(),
			rawurlencode($request['template']->getDN()));

		$changetype = $request['template']->getType();
		printf('<small>%s <a href="%s">%s</a>',$actionString[$changetype],$edit_href,$request['template']->getDN());

		if ($request['import']->LDAPimport())
			printf(' <span style="color:green;">%s</span></small><br />',_('Success'));

		else {
			printf(' <span style="color:red;">%s</span></small><br /><br />',_('Failed'));
			$errormsg = sprintf('%s <b>%s</b>',$actionErrorMsg[$changetype],$request['template']->getDN());
			$errormsg .= ldap_error_msg($app['server']->getErrorMessage(null),$app['server']->getErrorNum(null));

			system_message(array(
				'title'=>_('LDIF text import'),
				'body'=>$errormsg,
				'type'=>'warn'));
		}
	}

	if ($request['import']->error) {
		printf('<small><span style="color:red;">%s: %s</span></small><br />',
			_('Error'),$request['import']->error['message']);

		echo '<br/>';

		display_pla_parse_error($request['import']);
	}

	if (! $request['continuous_mode'])
		break;
}

function display_pla_parse_error($request) {
	$type = $request->getType();

	echo '<center>';
	echo '<table class="error">';
	echo '<tr>';
	printf('<td class="img"><img src="%s/%s" /></td>',IMGDIR,'error-big.png');

	printf('<td><h2>%s %s</h2></td>',$type['description'],_('Parse Error'));
	echo '</tr>';

	printf('<tr><td><b>%s</b>:</td><td>%s</td></tr>',_('Description'),$request->error['message']);
	printf('<tr><td><b>%s</b>:</td><td>%s</td></tr>',_('Line'),$request->error['line']);
	printf('<tr><td colspan=2><b>%s</b>:</td></tr>',_('Data'));

	foreach ($request->error['data'] as $line)
		printf('<tr><td>&nbsp;</td><td>%s</td></tr>',$line);

	echo '</table>';
	echo '</center>';
}
?>
