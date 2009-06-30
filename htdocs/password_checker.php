<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/password_checker.php,v 1.8.4.1 2005/12/08 11:54:00 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';
include './header.php';

$hash = isset( $_REQUEST['hash'] ) ? $_REQUEST['hash'] : null;
$check_password = isset( $_REQUEST['check_password'] ) ? $_REQUEST['check_password'] : null;
//print_r( $_REQUEST );
//var_dump( $check_password );

if( isset( $_REQUEST['base64'] ) ) {
    $hash = base64_decode( $hash );
    $check_password = base64_decode( $check_password );
}

$enc_type = get_enc_type( $hash );
?>

<h3 class="subtitle"><?php echo _('Password Checker Tool'); ?></h3>

<form style="margin: 0" action="password_checker.php" method="post">
    <input type="hidden" name="action" value="compare" />
    <table style="border-spacing: 10px">
    <tr>
        <td><?php echo _('Compare'); ?></td>
        <td><input type="<?php echo ( $enc_type ? 'text' : 'password' ); ?>" name="hash" style="width: 300px" value="<?php echo htmlspecialchars($hash); ?>" /></td>
    </tr>
    <tr>
        <td><?php echo _('To'); ?></td>
        <td><input type="password" style="width: 300px" name="check_password" value="<?php echo htmlspecialchars($check_password); ?>" /></td>
    </tr>
    <tr>
        <td></td><td><input type="submit" value="Compare" />

        <?php if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'compare' ) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>";
            if( password_check( $hash, $check_password) )
                echo "<span style=\"color: green\">" . _('Passwords match!') . "</span>";
            else
                echo "<span style=\"color: red\">" . _('Passwords do not match!') . "</span>";
            echo "</b>";
        } ?>
        </td>
    </tr>
</table>
</form>
