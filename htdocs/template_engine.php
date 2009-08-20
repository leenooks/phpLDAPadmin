<?php
/**
 * Template render engine.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 * @author The phpLDAPadmin development team
 */

/**
The template engine has the following responsibilities:
* If we are passed a DN, then we are editing an existing entry
* If we are not passed a DN, then we are passed a container (and creating a new entry in that container)

In both cases, we are optionally passed a template ID. 
* If we have a template ID, then we'll render the creation/editing using that template
* If we are not passed a template ID, then we'll either:
	* Present a list of available templates,
	* Present the default template, because there are non available (due to hidden,regexp or non-existant)
	* Present the only template, if there is only one.

Creating and editing entries use two objects:
* A template object which describes how the template should be rendered (and what values should asked for, etc)
* A page object, which is responsible for actually sending out the HTML to the browser.

So:
* we init a new TemplateRender object
* we init a new Template object
* set the DN or container on the template object
	* If setting the DN, this in turn should read the "old values" from the LDAP server
* If we are not on the first page (ie: 2nd, 3rd, 4th step, etc), we should accept the post values that we have obtained thus far

* Finally submit the update to "update_confirm", or the create to "create", when complete.
 */

require './common.php';

$request = array();
$request['dn'] = get_request('dn','REQUEST');
$request['page'] = new TemplateRender($app['server']->getIndex(),get_request('template','REQUEST',false,null));

# If we have a DN, then this is to edit the entry.
if ($request['dn']) {
	$app['server']->dnExists($request['dn'])
		or error(sprintf('%s (%s)',_('No such entry'),pretty_print_dn($request['dn'])),'error','index.php');

	$request['page']->setDN($request['dn']);
	$request['page']->accept();

} else {
	if ($app['server']->isReadOnly())
		error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

	$request['page']->setContainer(get_request('container','REQUEST'));
	$request['page']->accept();
}
?>
