<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/common.php,v 1.3.2.3 2007/12/26 08:21:10 wurley Exp $

/**
 * @package phpLDAPadmin
 */

# This is just here to provide a convenient link back to the proper common.php
if (! defined('LIBDIR'))
	define('LIBDIR',sprintf('%s/',realpath('../lib/')));
require_once LIBDIR.'common.php';
?>
