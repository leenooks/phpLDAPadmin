<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/template_config.php,v 1.17 2004/05/08 11:14:55 xrenard Exp $

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
        array(  'desc'    => 'Kolab User Entry',
                'icon'    => 'images/user.png',
                'handler' => 'new_kolab_template.php' );

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
##  2 - $default_samba3_domains : the domain name and the domain sid.                 ##   
##                                                                                    ##
######################################################################################*/

// path 2 the mkntpwd utility (Customize)
$mkntpwdCommand = "./templates/creation/mkntpwd";

// Default domains definition (Customize)
//   (use `net getlocalsid` on samba server)
$default_samba3_domains = array();
$default_samba3_domains[] =
        array(  'name'   => 'My Samba domain Name',
                'sid' => 'S-1-5-21-479559372-1547523452-3818884970' );

// The base dn of samba group. (CUSTOMIZE)
//$samba_base_groups = "ou=Groups,ou=samba,dc=example,dc=org";


//Definition of built-in local groups
$built_in_local_groups = array( "S-1-5-32-544" => "Administrators",
			        "S-1-5-32-545" => "Users",
			        "S-1-5-32-546" => "Guests",
			        "S-1-5-32-547" => "Power Users",
				"S-1-5-32-548" => "Account Operators",
				"S-1-5-32-549" => "Server Operators",
				"S-1-5-32-550" => "Print Operators",
				"S-1-5-32-551" => "backup Operators",
				"S-1-5-32-552" => "Replicator" );


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
        // fetch and lowercase all the objectClasses in an array
        $object_classes = get_object_attr( $server_id, $dn, 'objectClass', true );

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


/**
 * Utily class to get the samba passwords.
 */

class MkntPasswdUtil{


  var $clearPassword = NULL;
  var $sambaPassword ;
  function MkntPasswdUtil(){
    $sambaPassword = array("sambaLMPassword" => NULL,
			   "sambaNTPassword" => NULL);
  }
  
  function createSambaPasswords($password){
    global $mkntpwdCommand;
    $this->clearPassword = $password;
    file_exists ( $mkntpwdCommand ) && is_executable ( $mkntpwdCommand ) or pla_error(' Unable to create the Samba passwords. Please, check the configuration in template_config.php');
    $sambaPassCommand = $mkntpwdCommand . " " . $password;
    if($sambaPassCommandOutput = shell_exec($sambaPassCommand)){
      $this->sambaPassword['sambaLMPassword'] = trim( substr( $sambaPassCommandOutput , 0 , strPos( $sambaPassCommandOutput,':' ) ) );
      $this->sambaPassword['sambaNTPassword'] = trim( substr( $sambaPassCommandOutput, strPos( $sambaPassCommandOutput ,':' ) +1 ) );
      return true;
    }
    else{
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
    return  $this->sambaPassword[$key];
  }

}


/**
 * Return posix group entries
 *
 */

function get_posix_groups( $server_id , $base_dn = NULL ){
  global $servers;
  if( is_null( $base_dn ) )
    $base_dn = $servers[$server_id]['base'];  
  
  $results = pla_ldap_search( $server_id, "objectclass=posixGroup", $base_dn, array() );
  if( !$results )
    return false;
  else
    return $results;
}
?>
