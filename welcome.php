<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/welcome.php,v 1.17 2004/10/24 23:51:49 uugdave Exp $
 
	include './common.php'; 
	include './header.php'; 
    
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
if ( ! hide_configuration_management() ) {
?>
<a href="documentation.php?view=credits"><?php echo $lang['credits']; ?></a> |
<a href="documentation.php?view=changelog"><?php echo $lang['changelog']; ?></a> |
<a href="<?php echo get_href( 'donate' ); ?>"><?php echo $lang['donate']; ?></a>
<?php
}
?>

</center>

</body>

</html>
