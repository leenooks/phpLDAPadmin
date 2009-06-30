<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/welcome.php,v 1.9 2004/04/24 12:59:17 uugdave Exp $
 
	include 'common.php'; 
	include 'header.php'; 
?>

<center>
<br />
<br />
<br />
<img src="images/logo.jpg" /><br />
<br />
<?php echo $lang['welcome_note']; ?>
<br />
<br />
<br />
<br />
<a href="documentation.php?view=credits"><?php echo $lang['credits']; ?></a> |
<a href="documentation.php?view=changelog"><?php echo $lang['changelog']; ?></a> |
<a href="<?php echo get_href( 'donate' ); ?>" target="new"><?php echo $lang['donate']; ?></a>
</center>

</body>

</html>
