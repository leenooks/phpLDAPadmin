<?php

return [
	'disks' => [
		'templates' => [
			'driver' => 'local',
			'root' => base_path(env('LDAP_TEMPLATE_DIR','templates')),
		],
	],
];
