<?php

return [
	/**
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
];