<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/welcome.php,v 1.24.4.2 2005/12/08 11:58:14 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */
require './common.php';
include './header.php';

# Close the session for faster page loading
pla_session_close();
?>

<body>
<center>
<br />
<br />
<br />
<img src="images/logo.jpg" title="<?php echo _('phpLDAPadmin logo'); ?>" alt="<?php echo _('phpLDAPadmin logo'); ?>" />
<br />
<br />
<?php echo _('Use the menu to the left to navigate'); ?>
<br />
<br />
<br />
<br />

<?php if (! $config->GetValue('appearance','hide_configuration_management')) { ?>
<a href="<?php echo get_href('credits'); ?>"><?php echo _('Credits'); ?></a> |
<a href="<?php echo get_href('documentation'); ?>"><?php echo _('Documentation'); ?></a> |
<a href="<?php echo get_href('donate'); ?>"><?php echo _('Donate'); ?></a>
<?php } ?>

</center>
</body>
</html>
