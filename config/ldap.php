<?php

use App\Rules\HasStructuralObjectClass;

return [

	/*
	|--------------------------------------------------------------------------
	| Default LDAP Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the LDAP connections below you wish
	| to use as your default connection for all LDAP operations. Of
	| course you may add as many connections you'd like below.
	|
	*/

	'default' => env('LDAP_CONNECTION', 'ldap'),

	/*
	|--------------------------------------------------------------------------
	| LDAP Connections
	|--------------------------------------------------------------------------
	|
	| Below you may configure each LDAP connection your application requires
	| access to. Be sure to include a valid base DN - otherwise you may
	| not receive any results when performing LDAP search operations.
	|
	*/

	'connections' => [

		'ldap' => [
			'hosts' => [env('LDAP_HOST', '127.0.0.1')],
			'username' => env('LDAP_USERNAME', 'cn=user,dc=local,dc=com'),
			'password' => env('LDAP_PASSWORD', 'secret'),
			'port' => env('LDAP_PORT', 389),
			'timeout' => env('LDAP_TIMEOUT', 5),
			'use_ssl' => env('LDAP_SSL', false),
			'use_tls' => env('LDAP_TLS', false),
			'name' => env('LDAP_NAME','LDAP Server'),
		],

		'ldaps' => [
			'hosts' => [env('LDAP_HOST', '127.0.0.1')],
			'username' => env('LDAP_USERNAME', 'cn=user,dc=local,dc=com'),
			'password' => env('LDAP_PASSWORD', 'secret'),
			'port' => env('LDAP_PORT', 636),
			'timeout' => env('LDAP_TIMEOUT', 5),
			'use_ssl' => env('LDAP_SSL', true),
			'use_tls' => env('LDAP_TLS', false),
			'name' => env('LDAP_NAME','LDAPS Server'),
		],

		'starttls' => [
			'hosts' => [env('LDAP_HOST', '127.0.0.1')],
			'username' => env('LDAP_USERNAME', 'cn=user,dc=local,dc=com'),
			'password' => env('LDAP_PASSWORD', 'secret'),
			'port' => env('LDAP_PORT', 389),
			'timeout' => env('LDAP_TIMEOUT', 5),
			'use_ssl' => env('LDAP_SSL', false),
			'use_tls' => env('LDAP_TLS', true),
			'name' => env('LDAP_NAME','LDAP-TLS Server'),
		],

		/*
		'opendj' => [
			'hosts' => ['opendj'],
			'username' => 'cn=Directory Manager',
			'password' => 'password',
			'port' => 1389,
			'timeout' => env('LDAP_TIMEOUT', 5),
			'use_ssl' => env('LDAP_SSL', false),
			'use_tls' => env('LDAP_TLS', false),
			'name' => 'OpenDJ Server',
		],
		*/

	],

	/*
	|--------------------------------------------------------------------------
	| LDAP Logging
	|--------------------------------------------------------------------------
	|
	| When LDAP logging is enabled, all LDAP search and authentication
	| operations are logged using the default application logging
	| driver. This can assist in debugging issues and more.
	|
	*/

	'logging' => env('LDAP_LOGGING', true),

	/*
	|--------------------------------------------------------------------------
	| LDAP Cache
	|--------------------------------------------------------------------------
	|
	| LDAP caching enables the ability of caching search results using the
	| query builder. This is great for running expensive operations that
	| may take many seconds to complete, such as a pagination request.
	|
	*/

	'cache' => [
		'enabled' => env('LDAP_CACHE', false),
		'driver' => env('CACHE_DRIVER', 'file'),
		'time' => env('LDAP_CACHE_TIME',5*60),		// Seconds
	],

	/*
	 |--------------------------------------------------------------------------
	 | Validation
	 |--------------------------------------------------------------------------
	 |
	 | Default validation used for data input.
	 |
	 */
	'validation' => [
		'objectclass' => [
			'objectclass.*'=>[
				new HasStructuralObjectClass,
			]
		],
		'gidnumber' => [
			'gidnumber.*'=> [
				'sometimes',
				'max:1'
			],
			'gidnumber.*.*' => [
				'nullable',
				'integer',
				'max:65535'
			]
		],
		'mail' => [
			'mail.*'=>[
				'sometimes',
				'min:1'
			],
			'mail.*.*' => [
				'nullable',
				'email'
			]
		],
		'userpassword' => [
			'userpassword.*' => [
				'sometimes',
				'min:1'
			],
			'userpassword.*.*' => [
				'nullable',
				'min:8'
			]
		],
		'uidnumber' => [
			'uidnumber.*' => [
				'sometimes',
				'max:1'
			],
			'uidnumber.*.*' => [
				'nullable',
				'integer',
				'max:65535'
			]
		],
	],
];
