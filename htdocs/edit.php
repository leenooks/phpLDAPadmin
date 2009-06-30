<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/edit.php,v 1.56 2005/09/25 16:11:44 wurley Exp $

/**
 * Displays the specified dn from the specified server for editing
 * in its template as determined by get_template(). This is a simple
 * shell for displaying entries. The real work is done by the templates
 * found in tempaltes/modification/
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - use_default_template (optional) If set, use the default template no matter what
 *  - Other vars may be set and used by the modification templates
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';
require TMPLDIR.'template_config.php';

if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$dn = isset($_GET['dn']) ? $_GET['dn'] : false;
$dn !== false or pla_error($lang['missing_dn_in_query_string']);

$decoded_dn = rawurldecode($dn);
$encoded_dn = rawurlencode($decoded_dn);

/* Template authors may wish to present the user with a link back to the default, generic
   template for editing. They may use this as the target of the href to do so.
   @deprectated
*/
$default_href = sprintf("edit.php?server_id=%s&amp;dn=%s&amp;use_default_template=true",$ldapserver->server_id,$encoded_dn);
$use_default_template = isset( $_GET['use_default_template'] ) || $config->GetValue('template_engine','enable');

if( $use_default_template ) {
	if ($config->GetValue('template_engine','enable'))
		require './template_engine.php';
	else
		require TMPLDIR.'modification/default.php';

} else {
	$template = get_template($ldapserver,$dn);
	$template_file = TMPLDIR."modification/$template.php";

	if (file_exists($template_file))
		require $template_file;

	else {
		printf('%s <b>%s</b> %s<br />',$lang['missing_template_file'],$template_file,$lang['using_default']);
		require TMPLDIR.'modification/default.php';
	}
}
?>
