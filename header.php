<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/header.php,v 1.10 2004/03/19 20:13:08 i18phpldapadmin Exp $
 
	// We want to get $language into scope in case we were included
	// from within a function
	global $language; 
	@header( "Content-type: text/html; charset=\"UTF-8\"" );

	// XML version and encoding for well-behaved browsers
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"; 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language; ?>" lang="<?php echo $language; ?>" dir="ltr">
<head>
	<title>phpLDAPadmin</title>
	<link rel="stylesheet" href="style.css" media="screen" />
	<script src="entry_chooser.js" type="text/javascript"></script>
	<script src="search_util.js" type="text/javascript"></script>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
