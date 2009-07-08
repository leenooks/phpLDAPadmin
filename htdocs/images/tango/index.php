<?php 
/**
 * This will show a nice table of all the icons used by phpLDAPadmin.
 * @package phpLDAPadmin
 */

/**
 */
?>
<html>
<head>
</head>
<body>
<h3 class="title">phpLDAPadmin icons</h3>
<br />
<center>
<?php

$dir = opendir( '.' );
while(  ( $file = readdir( $dir ) ) !== false ) {
    if( $file == '.' || $file == '..' )
        continue;
    if( ! preg_match( '/\.png$/', $file ) )
        continue;
    if( $file == 'phpLDAPadmin_logo1.png' )
        continue;
    $files[ filesize( $file ) . '_' . $file ] = $file;
}

sort( $files );

$cell_style = "color: #888; text-align:center; padding: 10px; padding-bottom: 20px; vertical-align: bottom;";
$counter = 0;
print "<center><b>The " . count( $files ) . " icons used by phpLDAPadmin</b></center>";
echo "<table style=\"font-family: arial; font-size: 12px;\">";
echo "<tr>";
foreach( $files as $file ) {
    if( $counter % 6 == 0 ) {
        echo "</tr>\n"; 
        flush();
        echo "<tr>";
    }
    $counter++;
    echo '<td style="' . $cell_style . '"><img title="' . htmlspecialchars( $file ) . '" src="' . htmlspecialchars( $file ) . '" /><br />';
    echo "$file</td>\n";
}

?>
</center>
</body>
</html>
