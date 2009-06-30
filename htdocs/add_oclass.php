<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_oclass.php,v 1.17.2.5 2005/12/09 23:32:37 wurley Exp $

/**
 * Adds an objectClass to the specified dn.
 *
 * Note, this does not do any schema violation checking. That is
 * performed in add_oclass_form.php.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - new_oclass
 *  - new_attrs (array, if any)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if( $ldapserver->isReadOnly() )
	pla_error( _('You cannot perform updates while server is in read-only mode') );
if( ! $ldapserver->haveAuthInfo())
	pla_error( _('Not enough information to login to server. Please check your configuration.') );

$dn = rawurldecode( $_POST['dn'] );
$new_oclass = unserialize( rawurldecode( $_POST['new_oclass'] ) );
$new_attrs = $_POST['new_attrs'];

$encoded_dn = rawurlencode( $dn );

if ($ldapserver->isAttrReadOnly('objectClass'))
	pla_error( "ObjectClasses are flagged as read only in the phpLDAPadmin configuration." );

$new_entry = array();
$new_entry['objectClass'] = $new_oclass;

$new_attrs_entry = array();
$new_oclass_entry = array( 'objectClass' => $new_oclass );

if( is_array( $new_attrs ) && count( $new_attrs ) > 0 )
	foreach( $new_attrs as $attr => $val ) {

		// Check to see if this is a unique Attribute
		if ($badattr = $ldapserver->checkUniqueAttr($dn,$attr,array($val))) {
			$search_href = sprintf('search.php?search=true&form=advanced&server_id=%s&filter=%s=%s',
				$ldapserver->server_id,$attr,$badattr);
			pla_error(sprintf( _('Your attempt to add <b>%s</b> (<i>%s</i>) to <br><b>%s</b><br> is NOT allowed. That attribute/value belongs to another entry.<p>You might like to <a href=\'%s\'>search</a> for that entry.'),$attr,$badattr,$dn,$search_href ) );
		}

		$new_entry[ $attr ] = $val;
	}

$add_res = $ldapserver->attrModify($dn,$new_entry);

if (! $add_res)
	pla_error(_('Could not perform ldap_mod_add operation.'),$ldapserver->error(),$ldapserver->errno());

else
	header(sprintf('Location: template_engine.php?server_id=%s&dn=%s&modified_attrs[]=objectclass',$ldapserver->server_id,$encoded_dn));
?>
