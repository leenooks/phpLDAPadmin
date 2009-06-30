<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/welcome.php,v 1.19 2005/02/25 13:44:06 wurley Exp $
 
/**
 * @package phpLDAPadmin
 */
/**
 */
	include './common.php'; 
	include './header.php'; 
//	include './config.php'; 
    
if ( isset($custom_welcome_page) && $custom_welcome_page ) {
    include_once $custom_welcome_page;

} else {
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

<?php }
?>
