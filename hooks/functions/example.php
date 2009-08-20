<?php
/**
 * An example of a hooks implementation.
 *
 * Functions should return true on success and false on failure.
 * If a function returns false it will trigger the rollback to be executed.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * This example hooks implementation will just show system_messages after each hooks is called.
 *
 * @package phpLDAPadmin
 * @subpackage Functions
 */

# If you want to see this example in action, just comment out the return.
return false;

/**
 * The post_session_init function is called after lib/common.php has completed its processing.
 * This can be used to further initialise the session.
 *
 * No arguments are passed to post_session_init.
 */
function example_post_session_init() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Global Vars</i>: <small>%s</small>',join('| ',array_keys($GLOBALS))),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('post_session_init','example_post_session_init');

/**
 * This pre_connect function is called before making a connection to the LDAP server.
 * While PLA makes many calls to connect to the LDAP server, this is called only once
 * when caching is turned on.
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @see post_connect
 */
function example_pre_connect() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li></ul>',$args[0],$args[1]),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('pre_connect','example_pre_connect');

/**
 * This post_connect function is called after making a connection to the LDAP server.
 * While PLA makes many calls to connect to the LDAP server, this is called only once
 * when caching is turned on.
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string User ID of the user who successfully made the connection.
 * @see pre_connect
 */
function example_post_connect() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>User DN: <small>%s</small></li></ul>',$args[0],$args[1],$args[2]),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('post_connect','example_post_connect');

/**
 * This pre_entry_create function is called before an entry is created in ds_ldap_pla::add().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the entry created
 * @param array Attributes for the new DN
 * @see post_entry_create
 */
function example_pre_entry_create() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>Attributes: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],join(',',(array_keys($args[3])))),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('pre_entry_create','example_pre_entry_create');

/**
 * This post_entry_create function is called after an entry is created in ds_ldap_pla::add().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the entry created
 * @param array Attributes for the new DN
 * @see pre_entry_create
 */
function example_post_entry_create() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>Attributes: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],join(',',(array_keys($args[3])))),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('post_entry_create','example_post_entry_create');

/**
 * This pre_entry_delete function is called before an entry is deleted in ds_ldap_pla::delete().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the entry deleted
 * @see post_entry_delete
 */
function example_pre_entry_delete() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li></ul>',$args[0],$args[1],$args[2]),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('pre_entry_delete','example_pre_entry_delete');

/**
 * This post_entry_delete function is called after an entry is deleted in ds_ldap_pla::delete().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the entry deleted
 * @see pre_entry_delete
 */
function example_post_entry_delete() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li></ul>',$args[0],$args[1],$args[2]),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('post_entry_delete','example_post_entry_delete');

/**
 * This pre_entry_rename function is called before an entry is renamed in ds_ldap_pla::rename().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string Old DN of the entry to be renamed
 * @param string New RDN for the new entry
 * @param string Container for the new entry
 * @see post_entry_rename
 */
function example_pre_entry_rename() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>New RDN: <small>%s</small></li><li>New Container: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],$args[3],$args[4]),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('pre_entry_rename','example_pre_entry_rename');

/**
 * This post_entry_rename function is called after an entry is renamed in ds_ldap_pla::rename().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string Old DN of the entry to be renamed
 * @param string New RDN for the new entry
 * @param string Container for the new entry
 * @see pre_entry_rename
 */
function example_post_entry_rename() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>New RDN: <small>%s</small></li><li>New Container: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],$args[3],$args[4]),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('post_entry_rename','example_post_entry_rename');

/**
 * This pre_entry_modify function is called before an entry is modified in ds_ldap_pla::modify().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the entry to be modified
 * @param array Attributes to be modified
 * @see post_entry_modify
 */
function example_pre_entry_modify() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>Attributes: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],join('|',array_keys($args[3]))),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('pre_entry_modify','example_pre_entry_modify');

/**
 * This post_entry_modify function is called after an entry is modified in ds_ldap_pla::modify().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the entry to be modified
 * @param array Attributes to be modified
 * @see pre_entry_modify
 */
function example_post_entry_modify() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>Attributes: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],join('|',array_keys($args[3]))),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('post_entry_modify','example_post_entry_modify');

// pre_attr_add
// post_attr_add
/**
 * This pre_attr_add function is called before an attribute is deleted in ds_ldap_pla::modify().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the attribute to be deleted
 * @param string Attribute to be deleted
 * @param array Old values
 * @see post_attr_add
 */
function example_pre_attr_add() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>Attribute: <small>%s</small></li><li>New Values: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],$args[3],join('|',$args[4])),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('pre_attr_add','example_pre_attr_add');

/**
 * This post_attr_add function is called after an attribute is added in ds_ldap_pla::modify().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the attribute to be added
 * @param string Attribute to be added
 * @param array New values
 * @see pre_attr_add
 */
function example_post_attr_add() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>Attribute: <small>%s</small></li><li>New Values: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],$args[3],join('|',$args[4])),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('post_attr_add','example_post_attr_add');

// pre_attr_modify
// post_attr_modify
/**
 * This pre_attr_modify function is called before an attribute is modified in ds_ldap_pla::modify().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the attribute to be modified
 * @param string Attribute to be modified
 * @param array New values
 * @see post_attr_modify
 */
function example_pre_attr_modify() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>Attribute: <small>%s</small></li><li>Old Values: <small>%s</small></li><li>New Values: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],$args[3],join('|',$args[4]),join('|',$args[5])),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('pre_attr_modify','example_pre_attr_modify');

/**
 * This post_attr_modify function is called after an attribute is deleted in ds_ldap_pla::modify().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the attribute to be deleted
 * @param string Attribute to be deleted
 * @param array Old values
 * @see pre_attr_modify
 */
function example_post_attr_modify() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>Attribute: <small>%s</small></li><li>Old Values: <small>%s</small></li><li>New Values: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],$args[3],join('|',$args[4]),join('|',$args[5])),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('post_attr_modify','example_post_attr_modify');

/**
 * This pre_attr_delete function is called before an attribute is deleted in ds_ldap_pla::modify().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the attribute to be deleted
 * @param string Attribute to be deleted
 * @param array Old values
 * @see post_attr_delete
 */
function example_pre_attr_delete() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>Attribute: <small>%s</small></li><li>Old Values: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],$args[3],join('|',$args[4])),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('pre_attr_delete','example_pre_attr_delete');

/**
 * This post_attr_delete function is called after an attribute is deleted in ds_ldap_pla::modify().
 *
 * Arguments available are:
 * @param int Server ID of the server to be connected to
 * @param string Method. The user connection method, normally 'user'.
 * @param string DN of the attribute to be deleted
 * @param string Attribute to be deleted
 * @param array Old values
 * @see pre_attr_delete
 */
function example_post_attr_delete() {
	$args = func_get_args();

	system_message(array(
		'title'=>sprintf('Hook called [%s]',__METHOD__),
		'body'=>sprintf('<i>Arguments</i>:<ul><li>Server ID: <small>%s</small></li><li>Method: <small>%s</small></li><li>DN: <small>%s</small></li><li>Attribute: <small>%s</small></li><li>Old Values: <small>%s</small></li></ul>',$args[0],$args[1],$args[2],$args[3],join('|',$args[4])),
		'type'=>'info','special'=>true));

	return true;
}
add_hook('post_attr_delete','example_post_attr_delete');
?>
