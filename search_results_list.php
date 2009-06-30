<?php 

$friendly_attrs = process_friendly_attr_table();
$entry_id = ldap_first_entry( $ds, $results );

// Iterate over each entry
$i = 0;
while( $entry_id ) {
    $i++;
    if( $i <= $start_entry ) {
        $entry_id = ldap_next_entry( $ds, $entry_id );
        continue;
    }
    if( $i >= $end_entry )
        break;
    $dn = ldap_get_dn( $ds, $entry_id );
    $encoded_dn = rawurlencode( $dn );
    $rdn = get_rdn( $dn );
    ?>

        <div class="search_result">
          <table>
            <tr>
              <td><img src="images/<?php echo get_icon_use_cache( $server_id, $dn ); ?>" /></td>
              <td><a href="edit.php?server_id=<?php 
                 echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>"><?php echo htmlspecialchars($rdn); ?></a>
              </td>
            </tr>
          </table>
        </div>

        <table class="attrs">

    <?php 

    $attrs = ldap_get_attributes( $ds, $entry_id );
    $attr = ldap_first_attribute( $ds, $entry_id, $attrs );

    // Always print out the DN in the attribute list
    echo "<tr><td class=\"attr\" valign=\"top\">dn</td>";
    echo "<td>" . htmlspecialchars($dn) . "</td></tr>\n";

    // Iterate over each attribute for this entry
    while( $attr ) {

        if( is_attr_binary( $server_id, $attr ) )
            $values = array( "(binary)" );
        else
            $values = ldap_get_values( $ds, $entry_id, $attr );
        if( isset( $values['count'] ) )
            unset( $values['count'] );

        if( isset( $friendly_attrs[ strtolower( $attr ) ] ) )
            $attr = "<acronym title=\"Alias for $attr\">" . 
                htmlspecialchars( $friendly_attrs[ strtolower($attr) ] ) .
                "</acronym>";
        else
            $attr = htmlspecialchars( $attr );
        ?>

            <tr>
            <td class="attr" valign="top"><?php echo $attr; ?></td>
            <td class="val">
            <?php 
            if( is_jpeg_photo( $server_id, $attr ) )
                draw_jpeg_photos( $server_id, $dn, $attr, false, false, 'align="left"' );
            else
                foreach( $values as $value )
                    echo str_replace( ' ', '&nbsp;',
                            htmlspecialchars( $value ) ) . "<br />\n";
        ?>
            </td>
            </tr>
            <?php 
            $attr = ldap_next_attribute( $ds, $entry_id, $attrs );
    } // end while( $attr )

    ?>

        </table>

        <?php 

        $entry_id = ldap_next_entry( $ds, $entry_id );

    // flush every 5th entry (sppeds things up a bit)
    if( 0 == $i % 5 )
        flush();

} // end while( $entry_id )
