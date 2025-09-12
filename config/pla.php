<?php

return [
	/*
	 |--------------------------------------------------------------------------
	 | Support for attrs display order
	 |--------------------------------------------------------------------------
	 |
	 | Use this array if you want to have your attributes displayed in a specific
	 | order. Case is not important.
	 |
	 | For example, "sn" will be displayed right after "givenName". All the other
	 | attributes that are not specified in this array will be displayed after in
	 | alphabetical order.
	 |
	 */

	'attr_display_order' => [],
	/*
	'attr_display_order' => [
		'givenName',
		'sn',
		'cn',
		'displayName',
		'uid',
		'uidNumber',
		'gidNumber',
		'homeDirectory',
		'mail',
		'userPassword'
	],
	*/

	/*
	 |--------------------------------------------------------------------------
	 | Allow Guest
	 |--------------------------------------------------------------------------
	 |
	 | This will determine whether a user can connect to PLA and show the tree
	 | before they have logged in.
	 |
	 */

	'allow_guest' => env('LDAP_ALLOW_GUEST',FALSE),

	/*
	 |--------------------------------------------------------------------------
	 | Base DNs
	 |--------------------------------------------------------------------------
	 |
	 | Normally PLA will get the base DNs from the rootDSE's namingcontexts
	 | entry. Instead of using that, you can define your own base DNs to use.
	 |
	 */
	'base_dns' => ($x=env('LDAP_BASE_DN', NULL)) ? explode(':',$x) : NULL,

	/*
	 |--------------------------------------------------------------------------
	 | Custom Date Format
	 |--------------------------------------------------------------------------
	 |
	 | Configuration to determine how date fields will be displayed.
	 |
	 */
	'datetime_format' => 'Y-m-d H:i:s',

	/*
	 * These attributes will be forced to MAY attributes and become optional in the
	 * templates. If they are not defined in the templates, then they wont appear
	 * as per normal template processing. You may want to do this because your LDAP
	 * server may automatically calculate a default value.
	 *
	 * In Fedora Directory Server using the DNA Plugin one could ignore uidNumber,
	 * gidNumber and sambaSID.
	 *
	 # 'force_may' => ['uidNumber','gidNumber','sambaSID'],
	 */
	'force_may' => [],

	/*
	 * If 'login,attr' is used above such that phpLDAPadmin will search for your DN
	 * at login, you may restrict the search to a specific objectClasses. EG, set this
	 * to array('posixAccount') or array('inetOrgPerson',..), depending upon your
	 * setup.
	 */
	'login' => [
		// Attribute used to find user for login
		'attr' => [strtolower(env('LDAP_LOGIN_ATTR','uid')) => env('LDAP_LOGIN_ATTR_DESC','User ID')],
		// Objectclass that users must contain to login
		'objectclass' => explode(',',env('LDAP_LOGIN_OBJECTCLASS', 'posixAccount')),
		// Alert if DN is being used, and the login fails, and the the DN doesnt exist
		'alert_rootdn' => env('LDAP_ALERT_ROOTDN',TRUE) && strtolower(env('LDAP_LOGIN_ATTR','uid')) === 'dn',
	],

	'template' => [
		'dir' => env('LDAP_TEMPLATE_DRIVER','templates'),
		'exclude_system' => env('LDAP_TEMPLATE_EXCLUDE_SYSTEM',FALSE),
		'getnextnumber' => [
			'gidnumber' => env('LDAP_TEMPLATE_GIDNUMBER_START', 1000),
			'uidnumber' => env('LDAP_TEMPLATE_UIDNUMBER_START', 1000),
		],
	],

	'update' => [
		'enabled' => env('LDAP_UPDATE_ENABLED',FALSE),
		'proxy' => explode(',',env('LDAP_UPDATE_PROXY','')),
	]
];