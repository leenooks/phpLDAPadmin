<?php 
    require_once '../header.php';
?>
<body>
<h3 class="title">phpLDAPadmin icons</h3>
<br />
<center>
<?php

$dir = opendir( '.' );
while(  ( $file = readdir( $dir ) ) !== false ) {
    $counter++;
    if( $file == '.' || $file == '..' )
        continue;
    if( ! preg_match( '/\.png$/', $file ) )
        continue;
    if( $file == 'phpLDAPadmin_logo1.png' )
        continue;
    $files[ filesize( $file ) . '_' . $file ] = $file;
}

ksort( $files );

$counter = 0;
foreach( $files as $file ) {
    $counter++;
    echo '<img title="' . htmlspecialchars( $file ) . '" src="' . htmlspecialchars( $file ) . '" />&nbsp;&nbsp;';
    if( $counter % 15 == 0 )
        echo '<br /><br />';
    flush();
}

?>
</center>
</body>
</html>
