<?php
/**
 * Displays a form for renaming an LDAP entry.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

# The DN we are working with
$request = array();
$request['dn'] = get_request('dn','GET');
$request['template'] = get_request('template','GET');

$request['page'] = new PageRender($app['server']->getIndex(),get_request('template','REQUEST',false,'none'));
$request['page']->setDN($request['dn']);
$request['page']->accept();

# Render the form
$request['page']->drawTitle(sprintf('%s <b>%s</b>',_('Rename'),get_rdn($request['dn'])));
$request['page']->drawSubTitle();

echo '<center>';
printf('%s <b>%s</b> %s:<br /><br />',_('Rename'),get_rdn($request['dn']),_('to a new object'));

echo '<form action="cmd.php?cmd=rename" method="post" />';
printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
printf('<input type="hidden" name="dn" value="%s" />',rawurlencode($request['dn']));
printf('<input type="hidden" name="template" value="%s" />',$request['template']);
printf('<input type="text" name="new_rdn" size="30" value="%s" />',get_rdn($request['dn']));
printf('<input type="submit" value="%s" />',_('Rename'));
echo '</form>';

echo '</center>';
echo "\n";
?>
