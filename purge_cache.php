<?php

require './common.php';
include './header.php';

$purge_session_keys = array( 'schema' );

?>

<body>
<h3 class="title">Purging Caches</h3>
<br />
<br />
<br />
<center>

<?php
flush();
$size = 0;
foreach( $purge_session_keys as $key ) {
    if( isset( $_SESSION[$key] ) ) {
        $size += strlen( serialize( $_SESSION[$key] ) );
        unset( $_SESSION[$key] );
    } 
}

session_write_close();

if( 0 == $size )
    echo $lang['no_cache_to_purge'];
else
    echo sprintf( $lang['done_purging_caches'], number_format( $size ) );
?>

</center>
</body>
</html>
