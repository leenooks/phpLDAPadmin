<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/search_results_table.php,v 1.6 2005/03/05 06:27:06 wurley Exp $

/**
 * Incoming variables (among others)
 *  $results: The result of ldap_search(), ldap_list(), or ldap_read().
 *  $ldapserver: LDAP Server Object.
 *  $start_entry: The index of the entry at which to begin displaying
 *  $end_entry: The index of the entry at which to end displaying
 * @package phpLDAPadmin
 */

$friendly_attrs = process_friendly_attr_table();
$entry_id = ldap_first_entry( $ldapserver->connect(), $results );
$all_attrs = array( '' => 1, 'dn' => 1 );

// Iterate over each entry and store the whole dang thing in memory (necessary to extract
// all attribute names and display in table format in a single pass)
$i = 0;
$entries = array();
$entries_display = array();
while( $entry_id ) {
    $i++;
    if( $i <= $start_entry ) {
        $entry_id = ldap_next_entry( $ldapserver->connect(), $entry_id );
        continue;
    }
    if( $i >= $end_entry )
        break;
    $dn = ldap_get_dn( $ldapserver->connect(), $entry_id );
    $dn_display = strlen( $dn ) > 40 ? "<acronym title=\"" . htmlspecialchars( $dn ) . "\">" .
                                            htmlspecialchars( substr( $dn, 0, 40 ) . '...' ) .
                                            "</acronym>"
                                     : htmlspecialchars( $dn );
    $encoded_dn = rawurlencode( $dn );
    $rdn = get_rdn( $dn );
    $icon = get_icon_use_cache( $ldapserver, $dn );
    $attrs = ldap_get_attributes( $ldapserver->connect(), $entry_id );
    $attr = ldap_first_attribute( $ldapserver->connect(), $entry_id, $attrs );
    $attrs_display = array();
    $edit_url = "edit.php?server_id=$server_id&amp;dn=$encoded_dn";
    $attrs_display[''] = "<center><a href=\"$edit_url\"><img src=\"images/$icon\" /></a><center>";
    $attrs_display['dn'] = "<a href=\"$edit_url\">$dn_display</a>";

    // Iterate over each attribute for this entry and store in associative array $attrs_display
    while( $attr ) {
        //echo "getting values for dn $dn, attr $attr\n";

        // Clean up the attr name
        if( isset( $friendly_attrs[ strtolower( $attr ) ] ) )
            $attr_display = "<acronym title=\"Alias for $attr\">" .
                htmlspecialchars( $friendly_attrs[ strtolower($attr) ] ) .
                "</acronym>";
        else
            $attr_display = htmlspecialchars( $attr );

        if( ! isset( $all_attrs[ $attr_display ] ) )
            $all_attrs[ $attr_display ] = 1;

        // Get the values
        $display = '';
        if( is_jpeg_photo( $ldapserver, $attr ) ) {
            ob_start();
            draw_jpeg_photos( $ldapserver, $dn, $attr, false, false, 'align="center"' );
            $display = ob_get_contents();
            ob_end_clean();
        } elseif( is_attr_binary( $ldapserver, $attr ) ) {
            $display = array( "(binary)" );
        } else {
            $values = @ldap_get_values( $ldapserver->connect(), $entry_id, $attr );
            if( ! is_array( $values ) ) {
                $display = 'Error';
            } else {
                if( isset( $values['count'] ) )
                    unset( $values['count'] );
                foreach( $values as $value )
                    $display .= str_replace( ' ', '&nbsp;',
                            htmlspecialchars( $value ) ) . "<br />\n";
            }
        }
        $attrs_display[ $attr_display ] = $display;
        $attr = ldap_next_attribute( $ldapserver->connect(), $entry_id, $attrs );
    } // end while( $attr )

    $entries_display[] = $attrs_display;

    //echo '<pre>';
    //print_r( $attrs_display );
    //echo "\n\n";
    $entry_id = ldap_next_entry( $ldapserver->connect(), $entry_id );

} // end while( $entry_id )

$all_attrs = array_keys( $all_attrs );

/*
echo "<pre>";
print_r( $all_attrs );
print_r( $entries_display );
echo "</pre>";
*/

// Store the header row so it can be repeated later
$header_row = "<tr>";
foreach( $all_attrs as $attr )
    $header_row .= "<th>$attr</th>";
$header_row .= "</tr>\n";

// begin drawing table
echo "<br />";
echo "<center>";
echo "<table class=\"search_result_table\">\n";

for( $i=0; $i<count($entries_display); $i++ ) {
    $entry = $entries_display[$i];
    if( $i %10 == 0 )
        echo $header_row;
    if( $i % 2 == 0 )
        echo "<tr class=\"highlight\">";
    else
        echo "<tr>";
    foreach( $all_attrs as $attr ) {
        echo "<td>";
        if( isset( $entry[ $attr ] ) )
            echo $entry[ $attr ];
        echo "</td>\n";
    }
    echo "</tr>\n";
}

echo "</table>";
echo "</center>";
?>
