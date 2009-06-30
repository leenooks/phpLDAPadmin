<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/template_config.php,v 1.34 2005/03/05 06:27:07 wurley Exp $

/**
 * General configuration file for templates.
 *
 * File Map:
 * 1 - Generic templates configuration
 * 2 - Samba template configuration
 * 3 - method used in template and other files
 *
 * @package phpLDAPadmin
 */

/*######################################################################################
## Templates for entry creation                                                       ##
## ----------------------------                                                       ##
##                                                                                    ##
## Fill in this array with templates that you can create to suit your needs.          ##
## Each entry defines a description (to be displayed in the template list) and        ##
## a handler, which is a file that will be executed with certain POST vars set.       ##
## See the templates provided here for examples of how to make your own template.     ##
##                                                                                    ##
######################################################################################*/


$templates = array();

$templates[] =
        array(  'desc'    => $lang['user_account'],		 	// 'User Account (posixAccount)',
                'icon'    => 'images/user.png',
                'handler' => 'new_user_template.php' );
                // You can use the 'regexp' directive to restrict where
                // entries can be created for this template
                //'regexp' => '^ou=People,o=.*,c=.*$'
                //'regexp' => '^ou=People,dc=.*,dc=.*$'

$templates[] =
        array(  'desc'    => $lang['address_book_inet'],	// 'Address Book Entry (inetOrgPerson)',
                'icon'    => 'images/user.png',
                'handler' => 'new_address_template.php' );

$templates[] =
        array(  'desc'    => $lang['address_book_moz'],		// 'Address Book Entry (mozillaOrgPerson)',
                'icon'    => 'images/user.png',
                'handler' => 'new_mozillaOrgPerson_template.php' );

$templates[] =
        array(  'desc'    => $lang['kolab_user'],			// 'Kolab User Entry',
                'icon'    => 'images/user.png',
                'handler' => 'new_kolab_template.php' );

$templates[] =
        array(  'desc'    => $lang['organizational_unit'],	// 'Organizational Unit',
                'icon'    => 'images/ou.png',
                'handler' => 'new_ou_template.php' );

$templates[] =
        array(  'desc'    => $lang['organizational_role'],	// 'Organizational Role',
                'icon'    => 'images/o.png',
                'handler' => 'new_organizationalRole.php' );

$templates[] =
        array(  'desc'    => $lang['posix_group'],			// 'Posix Group',
                'icon'    => 'images/ou.png',
                'handler' => 'new_posix_group_template.php' );

$templates[] =
        array(  'desc'    => $lang['samba_machine'],		// 'Samba NT Machine',
                'icon'    => 'images/nt_machine.png',
                'handler' => 'new_nt_machine.php' );
$templates[] =
        array(  'desc'    => $lang['samba3_machine'],		// 'Samba 3 NT Machine',
                'icon'    => 'images/nt_machine.png',
                'handler' => 'new_smb3_nt_machine.php' );

$templates[] =
        array(  'desc'    => $lang['samba3_user'],			// 'Samba 3 User',
                'icon'    => 'images/nt_user.png',
                'handler' => 'new_smb3_user_template.php' );

$templates[] =
        array(  'desc'    => $lang['samba_user'],			// 'Samba User',
                'icon'    => 'images/nt_user.png',
                'handler' => 'new_smbuser_template.php' );

$templates[] =
        array(  'desc'    => $lang['samba3_group'],			// 'Samba 3 Group Mapping',
                'icon'    => 'images/ou.png',
                'handler' => 'new_smbgroup_template.php' );

$templates[] =
        array(  'desc'    => $lang['dns_entry'],			// 'DNS Entry',
                'icon'    => 'images/dc.png',
                'handler' => 'new_dns_entry.php' );

$templates[] =
        array(  'desc'    => $lang['simple_sec_object'],	// 'Simple Security Object',
                'icon'    => 'images/user.png',
                'handler' => 'new_security_object_template.php' );

$templates[] =
        array(  'desc'    => $lang['courier_mail_account'],	// 'Courier Mail Account',
                'icon'    => 'images/mail_account.png',
                'handler' => 'new_postfix_account_template.php' );

$templates[] =
        array(  'desc'    => $lang['courier_mail_alias'],	// 'Courier Mail Alias',
                'icon'    => 'images/mail_alias.png',
                'handler' => 'new_postfix_alias_template.php' );

$templates[] =
        array(  'desc'    => $lang['ldap_alias'],			// 'LDAP Alias',
                'icon'    => 'images/go.png',
                'handler' => 'new_alias_template.php' );
$templates[] =
         array( 'desc'    => $lang['sendmail_cluster'],		// 'Sendmail Cluster',
                'icon'    => 'images/mail.png',
                'handler' => 'new_sendmail_cluster_template.php' );

 $templates[] =
         array( 'desc'    => $lang['sendmail_domain'],		// 'Sendmail Domain',
                'icon'    => 'images/mail.png',
                'handler' => 'new_sendmail_domain_template.php' );

 $templates[] =
         array( 'desc'    => $lang['sendmail_alias'],		// 'Sendmail Alias',
                'icon'    => 'images/mail.png',
                'handler' => 'new_sendmail_alias_template.php' );

 $templates[] =
         array( 'desc'    => $lang['sendmail_virt_dom'],	// 'Sendmail Virtual Domain',
                'icon'    => 'images/mail.png',
                'handler' => 'new_sendmail_virthost_template.php' );

 $templates[] =
         array( 'desc'    => $lang['sendmail_virt_users'],	// 'Sendmail Virtual Users',
                'icon'    => 'images/mail.png',
                'handler' => 'new_sendmail_virtuser_template.php' );

 $templates[] =
         array( 'desc'    => $lang['sendmail_relays'],		// 'Sendmail Relays',
                'icon'    => 'images/mail.png',
                'handler' => 'new_sendmail_relay_template.php' );

$templates[] =
        array(  'desc'    => $lang['custom'],				// 'Custom',
                'icon'    => 'images/object.png',
                'handler' => 'custom.php' );

/*#####################################################################################
## POSIX GROUP TEMPLATE CONFIGURATION                                                ##
## ----------------------------------                                                ##
##                                                                                   ##
#####################################################################################*/

// uncomment to set the base dn of posix groups
// default is set to the base dn of the server
//$base_posix_groups="ou=People,dc=example,dc=com";


/*######################################################################################
##  SAMBA TEMPLATE CONFIGURATION                                                      ##
##  ----------------------------                                                      ##
##                                                                                    ##
##  In order to use the samba templates, you might edit the following properties:     ##
##  1 - $mkntpwdCommand : the path to the mkntpwd utility provided with/by Samba.     ##
##  2 - $samba3_domains : the domain name and the domain sid.                 ##
##                                                                                    ##
######################################################################################*/

// path 2 the mkntpwd utility (Customize)
$mkntpwdCommand = "/usr/local/bin/mkntpwd";

// Default domains definition (Customize)
// (use `net getlocalsid` on samba server)
$samba3_domains = array();
$samba3_domains[] =
array(
	'name' => $lang['samba_domain_name'],		// 'My Samba domain Name',
	'sid' => 'S-1-5-21-4147564533-719371898-3834029857'
);

// The base dn of samba group. (CUSTOMIZE)
//$samba_base_groups = "ou=Groups,ou=samba,dc=example,dc=org";


//Definition of built-in local groups
$built_in_local_groups = array(
	"S-1-5-32-544" => $lang['administrators'],	// Administrators
	"S-1-5-32-545" => $lang['users'],			// Users
	"S-1-5-32-546" => $lang['guests'],			// Guests
	"S-1-5-32-547" => $lang['power_users'],		// Power Users
	"S-1-5-32-548" => $lang['account_ops'],		// Account Operators
	"S-1-5-32-549" => $lang['server_ops'],		// Server Operators
	"S-1-5-32-550" => $lang['print_ops'],		// Print Operators
	"S-1-5-32-551" => $lang['backup_ops'],		// backup Operators
	"S-1-5-32-552" => $lang['replicator']		// Replicator
);


/*######################################################################################
## Methods used in/by templates                                                      ##
## ----------------------------                                                      ##
######################################################################################*/

/**
 * Return the name of the template to be used based on the object being edited.
 *
 * Returns the name of the template to use based on the DN and objectClasses of
 * an entry. If no specific modification template is available, simply return
 * 'default'. The caller should append '.php' and prepend 'templates/modification/'
 * to the returned string to get the file name.
 *
 * @param object $ldapserver Server Object the entry is in.
 * @param dn $dn Entry we will need a template for.
 */

function get_template( $ldapserver, $dn ) {
	// fetch and lowercase all the objectClasses in an array
	$object_classes = get_object_attr( $ldapserver, $dn, 'objectClass', true );

	if( $object_classes === null || $object_classes === false)
		return 'default';

	foreach( $object_classes as $i => $class )
		$object_classes[$i] = strtolower( $class );

	$rdn = get_rdn( $dn );

	if( in_array( 'groupofnames', $object_classes ) ||
		in_array( 'groupofuniquenames', $object_classes ) )
		return 'group_of_names';

	/*
		if( in_array( 'person', $object_classes ) &&
		in_array( 'posixaccount', $object_classes ) )
		return 'user';
	*/
	// TODO: Write other templates and criteria therefor
	// else if ...
	// return 'some other template';
	// else if ...
	// return 'some other template';
	// etc.

	return 'default';
}

/**
 * Return the domains info
 *
 */

function get_samba3_domains(){
	global $samba3_domains;

	// do the search for the sambadomainname object here
	// In the meantime, just return the domains defined in this config file
	check_samba_setting();
	return $samba3_domains;
}

/**
 * Utily class to get the samba passwords.
 * @package phpLDAPadmin
 */

class MkntPasswdUtil{

	var $clearPassword = NULL;
	var $sambaPassword ;

	function MkntPasswdUtil(){
		$sambaPassword = array(
			"sambaLMPassword" => NULL,
			"sambaNTPassword" => NULL
		);
	}

	function createSambaPasswords($password){
		global $mkntpwdCommand, $lang;

		$this->clearPassword = $password;

		file_exists ( $mkntpwdCommand ) && is_executable ( $mkntpwdCommand ) or pla_error( $lang['unable_smb_passwords'] );
		$sambaPassCommand = $mkntpwdCommand . " " . $password;

		if($sambaPassCommandOutput = shell_exec($sambaPassCommand)){
			$this->sambaPassword['sambaLMPassword'] = trim( substr( $sambaPassCommandOutput , 0 , strPos( $sambaPassCommandOutput,':' ) ) );
			$this->sambaPassword['sambaNTPassword'] = trim( substr( $sambaPassCommandOutput, strPos( $sambaPassCommandOutput ,':' ) +1 ) );
			return true;

		} else {
			return false;
		}
	}

	function getSambaLMPassword(){
		return $this->sambaPassword['sambaLMPassword'];
	}

	function getSambaNTPassword(){
		return $this->sambaPassword['sambaNTPassword'];
	}

	function getSambaClearPassword(){
		return $this->clearPassword;
	}

	function valueOf($key){
		return $this->sambaPassword[$key];
	}
}

function check_samba_setting(){
	global $samba3_domains;

	// check if the samba3_domains exist and is a array
	( isset($samba3_domains ) && is_array( $samba3_domains ) ) or pla_error($lang['err_smb_conf']);

	// no definition for the samba domain
	if(empty($samba3_domains))
		pla_error($lang['err_smb_no_name_sid']);

	else {
		// check if there is name or a sid declared for each domains
		foreach ($samba3_domains as $samba3_domain) {
			isset($samba3_domain['name']) or pla_error($lang['err_smb_no_name']);
			isset($samba3_domain['sid']) or pla_error($lang['err_smb_no_sid']);
		}
	}
}
?>
