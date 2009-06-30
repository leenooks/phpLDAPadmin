<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/Attic/help.php,v 1.5 2005/02/26 12:35:05 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

include './common.php';
include './header.php';

$forum_href = get_href( 'forum' );
?>

<body>

<h3 class="title">Help</h3>
<br />
<center>
<p>Do you have a problem or question?</p>
<p>Perhaps you are new to LDAP and need a little guidance?</p>
<p>Help is only one click away. Visit the online <a href="<?php echo $forum_href; ?>">phpLDAPadmin support forum</a>.</p>
<br />
</center>

</body>
</html>
