<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/welcome.php,v 1.24 2005/09/25 16:11:44 wurley Exp $
 
/**
 * @package phpLDAPadmin
 */
/**
 */
	require './common.php'; 
	include './header.php'; 
//	include './config.php'; 
    
// Close the session for faster page loading
pla_session_close();
?>

<body>
<center>
<br />
<br />
<br />
<img src="images/logo.jpg" title="<?php echo $lang['pla_logo']; ?>" alt="<?php echo $lang['pla_logo']; ?>" /><br />
<br />
<?php echo $lang['welcome_note']; ?>
<br />
<br />
<br />
<br />
<?php
if (! $config->GetValue('appearance','hide_configuration_management')) {
?>
<a href="documentation.php?view=credits"><?php echo $lang['credits']; ?></a> |
<a href="documentation.php?view=changelog"><?php echo $lang['changelog']; ?></a> |
<a href="http://wiki.pldapadmin.com/Documentation"><?php echo $lang['documentation']; ?></a> |
<a href="<?php echo get_href( 'donate' ); ?>"><?php echo $lang['donate']; ?></a>
<?php
}
?>

</center>
</body>
</html>
