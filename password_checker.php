<?php

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

<h3 class="subtitle"><?php echo $lang['password_checker_tool']; ?></h3>

<form style="margin: 0" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <input type="hidden" name="action" value="compare" />
    <table style="border-spacing: 10px">
    <tr>
        <td><?php echo $lang['compare']; ?></td>
        <td><input type="<?php echo ( $enc_type ? 'text' : 'password' ); ?>" name="hash" style="width: 300px" value="<?php echo htmlspecialchars($hash); ?>" /></td>
    </tr>
    <tr>
        <td><?php echo $lang['to']; ?></td>
        <td><input type="password" style="width: 300px" name="check_password" value="<?php echo htmlspecialchars($check_password); ?>" /></td>
    </tr>
    <tr>
        <td></td><td><input type="submit" value="Compare" />

        <?php if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'compare' ) { 
            echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>";
            if( password_check( $hash, $check_password) ) 
                echo "<span style=\"color: green\">" . $lang['passwords_match'] . "</span>";
            else
                echo "<span style=\"color: red\">" . $lang['passwords_do_not_match'] . "</span>";
            echo "</b>";
        } ?>
        </td>
    </tr>
</table>
</form>


