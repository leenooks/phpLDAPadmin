<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/custom_functions.php,v 1.5 2004/03/19 20:13:08 i18phpldapadmin Exp $


/*
 * custom_functions.php: Choose your own adventure.
 *
 * This file is full of functions (callbacks really) that are
 * meant to be filled in by users of phpLDAPadmin (you). These functions
 * are called as the result of a phpLDAPadmin event, like adding
 * a new entry, deleting an entry, changing an attribute value, etc.
 * Consider this concept an attempt to provide something like SQL 
 * triggers for LDAP users.
 *
 * This can be very handy, for example, for system administrators
 * who want to execute custom code after a user is created or deleted.
 *
 * These functions generally have 2 parameters, $server_id and $dn:
 *
 * 1. $server_id.
 * The $server_id can be used to connect to the server using 
 * pla_ldap_connect( $server_id ) to fetch additional information about
 * the entry being deleted. It can also be used to call 
 * get_object_attrs( $server_id, $dn ) to fetch the entry's attributes.
 *
 * 2. $dn
 * The dn is provided so users can determine where in the LDAP tree
 * this entry resides and act accordingly. For example, if the DN
 * contains "ou=development", you may want to act differently than 
 * if it contains "ou=accounting".
 *
 * Types of callback functions:
 *
 * These callbacks generally fall into two categories: "pre" and "post",
 * "pre" callbacks run before an event has occurred and their return 
 * value (true or false) is used to decide whether to allow the event
 * to proceed. "post" callbacks run after an event has occurred and
 * their return value (void) is ignored.
 *
 * NOTE: These custom callbacks are NOT executed for LDIF imports.
 *
 * ALSO NOTE: These callbacks are responsible for printing out error
 * messages. The calling code will die silently without notifying
 * the user why. YOU are responsible for creating output here.
 *
 * TODO: This section outlines events that phpLDAPadmin does not yet
 *       support. This list includes:
 *       - ldap_mod_add (ie, adding a new value to a multi-valued attribute)
 *       - ldap_mod_del (ie, deleting a value from a multi-valued attribute
 *                           or deleting an attribute from an entry)
 *       - ldap_rename  (ie, renaming an entry's RDN)
 *
 * DONE: This section lists events that phpLDAPadmin *does* support. 
 *       This list includes:
 *       - ldap_add     (ie, creating new entries)
 *       - ldap_delete  (ie, removing entries)
 *       - ldap_modify  (ie, changing the value of an attribute, for both
 *                           multi- and single-valued attributes)
 */

/*
 * This function is executed before modifying an entry's
 * attribute. Unlike preAttrModify, this function's
 * return value is ignored. In addition to the standard 
 * $server_id and $dn paramaters, this function also 
 * gives you the attribute name ($attr_name), and the new 
 * value that the attribute will have ($new_value). $new_value
 * may be a string or an array of strings.
 */
function postAttrModify( $server_id, $dn, $attr_name, $new_value )
{
	// Fill me in
	//
	// A very simple (and lame) example:
	// if( 0 == strcasecmp( $attr_name, "userPassword" ) ) {
	//     mail( "user@example.com", "Password change notification", 
	//           "User '$dn' has changed their password." );
	// }
}

/*
 * This function is executed before modifying an entry's
 * attribute. If it returns true, the entry is modified.
 * If it returns false, the entry is not modified.
 * In addition to the standard $server_id and $dn params,
 * this function also gives you the attribute name ($attr_name)
 * and the new value that the attribute will have ($new_value).
 * $new_value may be a string or an array of strings.
 */
function preAttrModify( $server_id, $dn, $attr_name, $new_value )
{
	// Fill me in
	return true;
}

/*
 * This function is executed after an entry is created.
 * Unlike preEntryCreate(), this function's return 
 * value is ignored. This is very handy for executing
 * custom code after creating a user account. For example,
 * one may wish to create the user's home directory.
 * See the documentation for preEntryCreate() below for
 * the description of the $attrs parameter.
 */
function postEntryCreate( $server_id, $dn, $attrs )
{
	// Fill me in
	//
	// A very simple example:
	// if( preg_match( "/^uid=(\w+),/", $dn, $user_name ) ) {
	//     $user_name = $user_name[1];
	//     mkdir( "/home/$user_name" );
	// } else {
	//     // not a user account
	// }
}

/*
 * This function is executed before an entry is created.
 * If it returns true, the entry is created, if false is
 * returned, the entry is not created. This function has
 * the additional parameters, $attrs, which is an assoc-
 * iative array of attribute/vale pairs of the same form
 * expected by ldap_add(), example:
 *
 * Array (
 *  [objectClass] => Array (
 *     [0] => top
 *     [1] => person
 *     [2] => inetOrgPerson
 *  )
 *  [cn] => John
 *  [sn] => Doe
 *   ...
 * )
 *
 */
function preEntryCreate( $server_id, $dn, $attrs )
{
	// Fill me in
	return true;
}

/*
 * This function is executed before an entry is deleted.
 * If it returns true, the entry is deleted, if false
 * is returned, the entry is not deleted.
 */
function preEntryDelete( $server_id, $dn )
{
	// Fill me in
	return true;
}

/*
 * This function is executed after an entry is deleted.
 * Unlike preEntryDelete(), this function's return 
 * value is ignored.
 */
function postEntryDelete( $server_id, $dn )
{
	// Fill me in
}

/**
 * This function is called, after a new session is initilaized
 */
function postSessionInit()
{
	// Fill me in
}

