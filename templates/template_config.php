<?php
/**
 * template_config.php
 * -------------------
 * General configuration file for templates.
 * File Map:
 * 1 - Generic templates configuration
 * 2 - Samba template configuration
 * 3 - method used in template and other files
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
        array(  'desc'    => 'User Account',
                'icon'    => 'images/user.png',
                'handler' => 'new_user_template.php' );
		// You can use the 'regexp' directive to restrict where
		// entries can be created for this template
                //'regexp' => '^ou=People,o=.*,c=.*$'
		//'regexp' => '^ou=People,dc=.*,dc=.*$'

$templates[] =
        array(  'desc'    => 'Address Book Entry (inetOrgPerson)',
                'icon'    => 'images/user.png',
                'handler' => 'new_address_template.php' );

$templates[] =
        array(  'desc'    => 'Organizational Unit',
                'icon'    => 'images/ou.png',
                'handler' => 'new_ou_template.php' );

$templates[] =
        array(  'desc'    => 'Posix Group',
                'icon'    => 'images/ou.png',
                'handler' => 'new_posix_group_template.php' );

$templates[] =
        array(  'desc'    => 'Samba NT Machine',
                'icon'    => 'images/nt_machine.png',
                'handler' => 'new_nt_machine.php' );
$templates[] =
        array(  'desc'    => 'Samba 3 NT Machine',
                'icon'    => 'images/nt_machine.png',
                'handler' => 'new_smb3_nt_machine.php' );
/*$templates[] =
        array(  'desc'    => 'Samba  User',
                'icon'    => 'images/nt_user.png',
                'handler' => 'new_smbuser_template.php' );
*/
$templates[] =
        array(  'desc'    => 'Samba 3 User',
                'icon'    => 'images/nt_user.png',
                'handler' => 'new_smb3_user_template.php' );
$templates[] =
        array(  'desc'    => 'Samba 3 Group Mapping',
                'icon'    => 'images/ou.png',
                'handler' => 'new_smbgroup_template.php' );

$templates[] =
        array(  'desc'    => 'DNS Entry',
                'icon'    => 'images/dc.png',
                'handler' => 'new_dns_entry.php' );

$templates[] =
	array(  'desc'    => 'Simple Security Object',
		'icon'    => 'images/user.png',
		'handler' => 'new_security_object_template.php' ); 

$templates[] =
	array(  'desc'    => 'Custom',
		'icon'    => 'images/object.png',
		'handler' => 'custom.php' ); 


/*######################################################################################
##  SAMBA TEMPLATE CONFIGURATION                                                      ##
##  ----------------------------                                                      ##
##                                                                                    ##
##  In order to use the samba templates, you might edit the following properties:     ##  
##  1 - $mkntpwdCommand : the path to the mkntpwd utility provided with/by Samba.     ##
##  2 - $default_samba3_domains : the domain name and the domain sid.                 ##   
##                                                                                    ##
######################################################################################*/

// path 2 the mkntpwd utility (Customize)
$mkntpwdCommand = "./templates/creation/mkntpwd";

// Default domains definition (Customize)
$default_samba3_domains = array();
$default_samba3_domains[] =
        array(  'name'   => 'My Samba domain Name',
                'sid' => 'S-1-5-21-1234567891-123456789-123456789' );




/*######################################################################################
##  Methods used in/by templates                                                      ## 
##  ----------------------------                                                      ##
######################################################################################*/

/*
 * Returns the name of the template to use based on the DN and
 * objectClasses of an entry. If no specific modification
 * template is available, simply return 'default'. The caller
 * should append '.php' and prepend 'templates/modification/'
 * to the returned string to get the file name.
 */

function get_template( $server_id, $dn )
{

	// For now, just use default. We will add more templates for 0.9.2.
	// If you have custom modification templates, just modify this.
	return 'default';

        // fetch and lowercase all the objectClasses in an array
        $object_classes = get_object_attr( $server_id, $dn, 'objectClass', true );

        if( $object_classes === null || $object_classes === false)
                return 'default';

        foreach( $object_classes as $i => $class )
                $object_classes[$i] = strtolower( $class );

        $rdn = get_rdn( $dn );
        if( in_array( 'person', $object_classes ) &&
            in_array( 'posixaccount', $object_classes ) )
                return 'user';
	// TODO: Write other templates and criteria therefor
	// else if ...
	//    return 'some other template';
	// else if ...
	//    return 'some other template';
	// etc.

	return 'default';
}

/**
 * Return the domains info
 *  
 */

function get_samba3_domains(){
  global $default_samba3_domains;

  // do the search for the sambadomainname object here
  // In the meantime, just return the default domains
  return $default_samba3_domains;
}

?>
