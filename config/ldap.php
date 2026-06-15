<?php

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
			'name' => env('LDAP_NAME','LDAP Server'),
			'hosts' => [env('LDAP_HOST', '127.0.0.1')],
			'username' => env('LDAP_USERNAME', ''),
			'password' => env('LDAP_PASSWORD', ''),
			'port' => env('LDAP_PORT', 389),
			'timeout' => env('LDAP_TIMEOUT', 5),
			'use_ssl' => env('LDAP_SSL', false),
			'use_tls' => env('LDAP_TLS', false),
			'use_sasl' => env('LDAP_SASL', false),
			'sasl_options' => [
				// 'mech' => 'GSSAPI',
			],
		],

		'ldaps' => [
			'name' => env('LDAP_NAME','LDAPS Server'),
			'hosts' => [env('LDAP_HOST', '127.0.0.1')],
			'username' => env('LDAP_USERNAME', ''),
			'password' => env('LDAP_PASSWORD', ''),
			'port' => env('LDAP_PORT', 636),
			'timeout' => env('LDAP_TIMEOUT', 5),
			'use_ssl' => env('LDAP_SSL', true),
			'use_tls' => env('LDAP_TLS', false),
			'use_sasl' => env('LDAP_SASL', false),
			'sasl_options' => [
				// 'mech' => 'GSSAPI',
			],
		],

		'starttls' => [
			'name' => env('LDAP_NAME','LDAP-TLS Server'),
			'hosts' => [env('LDAP_HOST', '127.0.0.1')],
			'username' => env('LDAP_USERNAME', ''),
			'password' => env('LDAP_PASSWORD', ''),
			'port' => env('LDAP_PORT', 389),
			'timeout' => env('LDAP_TIMEOUT', 5),
			'use_ssl' => env('LDAP_SSL', false),
			'use_tls' => env('LDAP_TLS', true),
			'use_sasl' => env('LDAP_SASL', false),
			'sasl_options' => [
				// 'mech' => 'GSSAPI',
			],
		],

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

	'logging' => [
		'enabled' => env('LDAP_LOGGING', false),
		'channel' => env('LOG_CHANNEL', 'stack'),
		'level' => env('LOG_LEVEL', 'info'),
	],

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

	'attrtags' => [
		'only_binary' => explode(',',strtolower(env('LDAP_ATTRTAG_BINARY_ONLY', 'userCertificate'))),
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
		'cacertificate' => [
			'cacertificate.*'=> [
				'sometimes',
				'max:1'
			],
			'cacertificate.binary.*' => [
				'required',
				new \App\Rules\CertificateIsBinary,
			],
		],
		'gidnumber' => [
			'gidnumber.*' => [
				'sometimes',
				'max:1'
			],
			'gidnumber.*.*' => [
				'nullable',
				'integer',
				'min:1'
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
		'objectclass' => [
			'objectclass.*'=>[
				new \App\Rules\HasStructuralObjectClass,
			]
		],
		'sambaacctflags' => [
			'sambaacctflags.'.\App\Ldap\Entry::TAG_INTERNAL.'.0' => [
				'sometimes',
				'array',
				'max:11',
				new \App\Rules\SambaAcctFlags,
			],
		],
		'uidnumber' => [
			'uidnumber.*' => [
				'sometimes',
				'max:1'
			],
			'uidnumber.*.*' => [
				'nullable',
				'integer',
				'min:1'
			]
		],
		'userpassword' => [
			'userpassword.*' => [
				'sometimes',
				'min:1'
			],
			sprintf('userpassword.%s%s.*',\App\Ldap\Entry::TAG_NOTAG,\App\Ldap\Entry::TAG_HELPER) => [
				'nullable',
				'min:3'
			],
			sprintf('userpassword.%s.*',\App\Ldap\Entry::TAG_NOTAG) => [
				'nullable',
				'min:8'
			]
		],
		'usercertificate' => [
			'usercertificate.*'=> [
				'sometimes',
				'max:1'
			],
			'usercertificate.binary.*' => [
				new \App\Rules\CertificateIsBinary,
			],
		],
	],
];
