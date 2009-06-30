<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/tree.php,v 1.76 2004/12/22 14:12:15 uugdave Exp $

/* 
 * tree.php
 * This script displays the LDAP tree for all the servers that you have
 * in config.php. We read the session variable 'tree' to know which
 * dns are expanded or collapsed. No query string parameters are expected,
 * however, you can use a '#' offset to scroll to a given dn. The syntax is
 * tree.php#<server_id>_<rawurlencoded dn>, so if I wanted to scroll to
 * dc=example,dc=com for server 3, the URL would be: 
 *	tree.php#3_dc%3Dexample%2Cdc%3Dcom
 */

require './common.php';

// no expire header stuff
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// This allows us to display large sub-trees without running out of time.
@set_time_limit( 0 );

// do we not have a tree and tree icons yet? Build a new ones.
initialize_session_tree();

// get the tree and tree icons.
$tree = $_SESSION['tree'];
$tree_icons = $_SESSION['tree_icons'];

// Close the session for faster page loading (we're done with session data anyway).
pla_session_close();

include './header.php';
?>

<body>

<?php
	$bug_href = get_href( 'add_bug' );
	$feature_href = get_href( 'add_rfe' );
	$donate_href = get_href( 'donate' );
	$help_href = get_href( 'help' );
?>

<h3 class="subtitle" style="margin:0px">phpLDAPadmin - <?php echo pla_version(); ?></h3>

<!-- Links at the top of the tree viewer -->
<table class="edit_dn_menu">
<?php if ( ! hide_configuration_management() ) { ?>
<tr>
	<td><img src="images/light.png" alt="<?php echo $lang['light']; ?>" /></td>
	<td><nobr><a href="<?php echo $feature_href; ?>" target="new"><?php echo $lang['request_new_feature']; ?></a></nobr></td>
	<td><img src="images/bug.png" alt="<?php echo $lang['bug']; ?>" /></td>
	<td><nobr><a href="<?php echo $bug_href; ?>" target="new"><?php echo $lang['report_bug']; ?></a></nobr></td>
</tr>
<?php } ?>
<tr>	
	<td><img src="images/smile.png" alt="<?php echo $lang['donate']; ?>" /></td>
	<td><nobr><a href="<?php echo $donate_href; ?>" target="right_frame"><?php echo $lang['donate']; ?></a></nobr></td>
	<td><img src="images/trash.png" alt="<?php echo $lang['purge_cache']; ?>" /></td>
	<td><nobr><a href="purge_cache.php" target="right_frame" title="<?php echo $lang['purge_cache_tooltip']; ?>"><?php echo $lang['purge_cache']; ?></a></nobr></td>
</tr>
<tr>	
	<td><img src="images/home.png" alt="<?php echo $lang['home']; ?>" /></td>
	<td><nobr><a href="welcome.php" target="right_frame"><?php echo $lang['home']; ?></a></nobr></td>
	<td><img src="images/help.png" alt="<?php echo $lang['help']; ?>" /></td>
	<td><nobr><a href="help.php" target="right_frame"><?php echo $lang['help']; ?></a></nobr></td>
</tr>
</table>

<table class="tree" cellspacing="0">

<?php 

// We want the std tree function as a fallback
require_once( 'tree_functions.php' );

// For each of the configured servers
foreach( $servers as $server_id => $server_tree ) { 

	$is_visible = ( ! isset( $servers[ $server_id ][ 'visible' ] ) 
		|| ( $servers[ $server_id ][ 'visible' ] === true  ) );
	if( isset( $servers[ $server_id ] )
		&& trim( $servers[ $server_id ][ 'host' ] ) != ''
		&& $is_visible )
	{
		$filename = get_custom_file( $server_id, 'tree_functions.php' );
		require_once( $filename );

		call_custom_function( $server_id, 'draw_server_tree' );
	}
}

?>

</table>
<?php 
//	echo "<pre>"; print_r( $tree ); 
?>

</body>
</html>

<?php
exit;

/**
 * Recursively descend on the given dn and draw the tree in html
 */
function draw_tree_html( $dn, $server_id, $level = 0 )
{
	global $servers, $tree, $tree_icons, $lang, $search_result_size_limit;
	$id = $server_id;

	$encoded_dn = rawurlencode( $dn );
	$expand_href = "expand.php?server_id=$id&amp;dn=$encoded_dn";
	$collapse_href = "collapse.php?server_id=$id&amp;dn=$encoded_dn";
	$edit_href = "edit.php?server_id=$id&amp;dn=$encoded_dn";

	// should never happen, but just in case
	if( ! isset( $tree_icons[ $server_id ][ $dn ] ) )
		$tree_icons[ $server_id ][ $dn ] = get_icon( $server_id, $dn );
	$img_src = 'images/' . $tree_icons[ $server_id ][ $dn ];

	$rdn = get_rdn( $dn );

	echo '<tr>';

	for( $i=0; $i<=$level; $i++ ) {
		echo '<td class="spacer"></td>' . "\n";
	}

	// Shall we draw the "mass-delete" checkbox?
	if( mass_delete_enabled( $server_id ) ) {
		echo "<td>
			<input 
			type=\"checkbox\" 
			name=\"mass_delete[" . htmlspecialchars($dn) . "]\" />
			</td>\n";
	}

	// is this node expanded? (deciding whether to draw "+" or "-")
	if( isset( $tree[$server_id][$dn] ) ) { ?>
		<td class="expander">
			<nobr>
			<a href="<?php echo $collapse_href; ?>"><img src="images/minus.png" alt="-" /></a>
			</nobr>
		</td>
		<?php  $child_count = number_format( count( $tree[$server_id][$dn] ) );
	} else { ?>	
		<td class="expander">
			<nobr>
			<a href="<?php echo $expand_href; ?>"><img src="images/plus.png" alt="+" /></a>
			</nobr>
		</td>
		<?php  	$limit = isset( $search_result_size_limit ) ? $search_result_size_limit : 50;
            if( is_server_low_bandwidth( $server_id ) ) {
                $child_count = null;
            } else {
                $child_count = count( get_container_contents( $server_id, $dn, $limit+1, 
                                      '(objectClass=*)', get_tree_deref_setting() ) );
                if( $child_count > $limit )
                    $child_count = $limit . '+';
            }
	} ?>	

	<td class="icon">
		<a href="<?php echo $edit_href; ?>"
		   target="right_frame"
		   name="<?php echo $server_id; ?>_<?php echo $encoded_dn; ?>"><img src="<?php echo $img_src; ?>" alt="img" /></a>
	</td>
	<td class="rdn" colspan="<?php echo (97-$level); ?>">
		<nobr>
			<a href="<?php echo $edit_href; ?>"
				target="right_frame"><?php echo ( draw_formatted_dn( $server_id, $dn ) ); /*pretty_print_dn( $rdn ) );*/ ?></a>
				<?php if( $child_count ) { ?>
					<span class="count">(<?php echo $child_count; ?>)</span>
				<?php } ?>
		</nobr>
	</td>
	</tr>

	<?php 

	if( isset( $tree[$server_id][$dn] ) && is_array( $tree[$server_id][$dn] ) )	{
        // Draw the "create new" link at the top of the tree list if there are more than 10
        // entries in the listing for this node.
        if( count( $tree[$server_id][$dn] ) > 10 )
            if( show_create_enabled( $server_id ) )
                draw_create_link( $server_id, $rdn, $level, $encoded_dn );
		foreach( $tree[$server_id][$dn] as $dn )
			draw_tree_html( $dn, $server_id, $level+1 );
        // Always draw the "create new" link at the bottom of the listing
		if( show_create_enabled( $server_id ) )
            draw_create_link( $server_id, $rdn, $level, $encoded_dn );
	}
}

function draw_create_link( $server_id, $rdn, $level, $encoded_dn )
{
    global $lang;
    // print the "Create New object" link.
    $create_html = "";
    $create_href = "create_form.php?server_id=$server_id&amp;container=$encoded_dn";
    $create_html .= '<tr>';
    for( $i=0; $i<=$level; $i++ ) {
        $create_html .= '<td class="spacer"></td>';
    }
    $create_html .= '<td class="spacer"></td>';
    $create_html .= '<td class="icon"><a href="' . $create_href .
        '" target="right_frame"><img src="images/star.png" alt="' . $lang['new'] . '" /></a></td>';
    $create_html .= '<td class="create" colspan="' . (97-$level) . '"><a href="' . $create_href . 
        '" target="right_frame" title="' . $lang['create_new_entry_in'] . ' ' . $rdn.'">' . 
        $lang['create_new'] . '</a></td>';
    $create_html .= '</tr>';
    echo $create_html;
}

?>
