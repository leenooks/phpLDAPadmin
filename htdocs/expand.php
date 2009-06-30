<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/expand.php,v 1.22.4.3 2007/03/18 03:16:05 wurley Exp $

/**
 * This script alters the session variable 'tree', expanding it
 * at the dn specified in the query string.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *
 * Note: this script is equal and opposite to collapse.php
 * @package phpLDAPadmin
 * @see collapse.php
 */
/**
 */

require './common.php';
no_expire_header();

if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

# This allows us to display large sub-trees without running out of time.
@set_time_limit(0);

$dn = $_GET['dn'];

# We dont need this result, as we'll use the SESSION value when we call tree.php
$ldapserver->getContainerContents($dn,0,'(objectClass=*)',$config->GetValue('deref','tree'));

$tree = get_cached_item($ldapserver->server_id,'tree');
$tree['browser'][$dn]['open'] = true;
set_cached_item($ldapserver->server_id,'tree','null',$tree);

/* If cookies were disabled, build the url parameter for the session id.
   It will be append to the url to be redirect */
$id_session_param = '';
if (SID != '')
	$id_session_param = sprintf('&%s=%s',session_name(),session_id());

header(sprintf('Location:tree.php?foo=%s#%s_%s%s',random_junk(),$ldapserver->server_id,rawurlencode($dn),$id_session_param));
?>
