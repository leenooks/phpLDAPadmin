<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/purge_cache.php,v 1.6 2005/07/22 06:12:51 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';
include './header.php';

$purge_session_keys = array('cache','tree','tree_icons');
?>

<body>
<h3 class="title">Purging Caches</h3>
<br />
<br />
<br />
<center>

<?php
$size = 0;
foreach ($purge_session_keys as $key) {
    if (isset($_SESSION[$key])) {
        $size += strlen(serialize($_SESSION[$key]));
        unset($_SESSION[$key]);
    }
}

session_write_close();

if (! $size)
    echo $lang['no_cache_to_purge'];

else
    echo sprintf($lang['done_purging_caches'],number_format($size));
?>

</center>
</body>
</html>
