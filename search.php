<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/search.php,v 1.39 2004/05/08 13:26:27 uugdave Exp $
 

/*
 * search.php
 * Perform LDAP searches and draw the advanced/simple search forms
 *
 * Variables that come in as GET vars:
 *  - server_id
 *  - search (true if performing a search, empty to just draw form)
 *  For simple searches:
 *  - attribute, criterion, filter
 *  For advanced searches:
 *  - base_dn, scope, filter
 */

require 'common.php';

// try to get an available server_id if one is not provided
if( ! isset( $_GET['server_id'] ) )
	$server_id = get_avail_server_id();
else {
	$server_id = $_GET['server_id'];
	check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
}


$js_on_change_string ='';
if( isset( $_GET['form'] ) && $_GET['form'] == 'advanced' ) 
	$js_on_change_string = 
		'onChange="document.forms[0].base_dn.value=servers[document.forms[0].server_id.value].getBaseDn()"';

// build the server drop-down html and JavaScript array (for base_dns)
$server_menu_html = '<select name="server_id" '.$js_on_change_string.'>';

$server_info_list =array();

foreach( $servers as $id => $server ) { 
	$base_dn = $server['base'] ? $server['base'] : try_to_get_root_dn( $id );
	$server_info_list[$id]['id'] = $id;
	$server_info_list[$id]['name'] = $server['name'];
	$server_info_list[$id]['base_dn'] = $base_dn;

	if( $server['host'] ) { 
		$server_menu_html .= '<option value="'.$id.'"' . ( $id==$server_id? ' selected' : '' ) . '>';
		$server_menu_html .= $server['name'] . '</option>';
	}
}

$server_menu_html .= '</select>';

$filter = isset( $_GET['filter'] ) ? $_GET['filter'] : null;
$filter = $filter;
$attr = isset( $_GET['attribute'] ) ? $_GET['attribute'] : null;

// grab the base dn for the search
if( isset( $_GET['base_dn'] ) )
	$base_dn = $_GET['base_dn'];
elseif( '' != $servers[$server_id]['base'] )
	$base_dn = $servers[$server_id]['base'];
else 
	$base_dn = try_to_get_root_dn( $server_id );
	
$criterion = isset( $_GET['criterion'] ) ? $_GET['criterion'] : null;
$form = isset( $_GET['form'] ) ? $_GET['form']  : null;
$scope = isset( $_GET['scope'] ) ? $_GET['scope'] : 'sub';

include 'header.php'; ?>

<body>

<center>

<?php  if( $form == 'advanced' ) { 

	include 'search_form_advanced.php';

} elseif( $form == 'predefined' ) {
	
	include 'search_form_predefined.php';

} else /* Draw simple search form */ {

	process_config();
	include 'search_form_simple.php';

} ?>

</center>

<?php 

flush();

if( isset( $_GET['search'] ) )
{
	if( $form == 'advanced'  ) {
		$search_result_attributes = isset( $_GET['display_attrs'] ) ? 
						rawurldecode( $_GET['display_attrs'] ) :
						( isset( $search_result_attributes ) ?
							$search_result_attributes : 
							"dn, cn" );
		process_config();
	} 

	// do we have enough authentication information for the specified server_id
	if( ! have_auth_info( $server_id ) )
	{
		$login_url = "login_form.php?server_id=$server_id&amp;redirect=" . rawurlencode( $_SERVER['REQUEST_URI'] );
		?>
		<center>
		<br />
		<?php echo $lang['you_have_not_logged_into_server']; ?><br />
		<a href="<?php echo $login_url; ?>"><?php echo $lang['click_to_go_to_login_form']; ?></a>.
		</center>
		<?php
		exit;
	}

	$ds = pla_ldap_connect( $server_id );
	if( ! $ds ) 
			pla_error( $lang['could_not_connect'] );
	
	//making $predefined safe for "register_globals off"
	if( isset( $_GET['predefined'] ) )
	{
		$predefined = $_GET['predefined'];
		if( is_numeric( $predefined ) )
		{
			$query = get_fixed_query( $predefined );

			$search_result_attributes = $query['attributes'];
			$search_attributes_display = $search_result_attributes;
			$search_attributes = $search_result_attributes;

			process_config();
	
			$filter = $query['filter'];
			$scope = $query['scope'];
			$base_dn = $query['base'];
			$server_id = $query['server'];
	}
	} else { $predefined = ''; }
	
	if( $filter )
	{

		// if they are using the simple search form, build an LDAP search filter from their input
		if( $form == 'simple' &! is_numeric( $predefined ) )
		{
			switch( $criterion ) {
				case 'starts with':
					// to fix bug 789113
					if( $filter == "*" )
						$filter = "";
					$filter = "($attr=$filter*)";
					break;
				case 'contains':
					// to fix bug 789113
					if( $filter == "*" )
						$filter = "($attr=*)";
					else
						$filter = "($attr=*$filter*)";
					break;
				case 'ends with':
					// to fix bug 789113
					if( $filter == "*" )
						$filter = "";
					$filter = "($attr=*$filter)";
					break;
				case 'equals':
					$filter = "($attr=$filter)";
					break;
				case 'sounds like':
					$filter = "($attr~=$filter)";
					break;
				default:
					pla_error( $lang['unrecognized_criteria_option'] . 
							htmlspecialchars( $criterion ) .
							$lang['if_you_want_to_add_criteria'] );
			}
		}
		
		echo "<center>" . $lang['searching'] . "</center>\n";
		flush();

		// prevent script from bailing early on a long delete
		@set_time_limit( 0 );

		// grab the size limit set in config.php
		$size_limit = isset ( $search_result_size_limit ) && is_numeric( $search_result_size_limit ) ? 
						$search_result_size_limit :
						50;
        // Sanity check
        if( $size_limit < 1 )
            $size_limit = 1;

		$page = isset( $_GET['page'] ) ? $_GET['page'] : 0;

		$time_start = utime();
		if( $scope == 'base' )
			$results = @ldap_read( $ds, $base_dn, $filter, $search_result_attributes, 
										0, 0, 0, get_search_deref_setting() );
		elseif( $scope == 'one' )
			$results = @ldap_list( $ds, $base_dn, $filter, $search_result_attributes, 
										0, 0, 0, get_search_deref_setting() );
		else // scope == 'sub'
			$results = @ldap_search( $ds, $base_dn, $filter, $search_result_attributes, 
										0, 0, 0, get_search_deref_setting() );
		$errno = @ldap_errno( $ds );
		if( ! $results ) {
			pla_error(  $lang['error_performing_search'], ldap_error( $ds ), ldap_errno( $ds ) );
		}

		$time_end = utime();
		$time_elapsed = round( $time_end - $time_start, 2 );
		$count = @ldap_count_entries( $ds, $results );

		$start_entry = $page * $size_limit;
		$end_entry = min( $start_entry + $size_limit + 1, $count+1 );

		?>

		<center>
			<?php echo $lang['entries_found'] . ' <b>' . number_format( $count ) ?></b>
			(<?php echo $time_elapsed; ?> <?php echo $lang['seconds']; ?>).<br />
			<?php 

			// The LDAP error code for the size limit exceeded error.
			define( 'SIZE_LIMIT_EXCEEDED', 4 );
			if( $errno && $errno == SIZE_LIMIT_EXCEEDED ) {
				echo "<br /><small>" . $lang['size_limit_exceeded'] . "</small><br />\n";
			}

			?>

		<?php  if( $form == 'simple' || $form == 'predefined' ) { ?>
			<center><small><?php echo $lang['filter_performed']; ?>
				<?php echo htmlspecialchars( $filter ); ?></small></center>
		<?php  } ?>

		<?php 
		// Draw the paging links
        $pager_html = '';
		if( $count > $size_limit ) {
            echo sprintf( $lang['showing_results_x_through_y'], "<b>" . number_format($start_entry+1) . "</b>", "<b>" . number_format($end_entry-1) . "</b>" ) . "<br />\n";
			$php_self = $_SERVER['PHP_SELF'];
			if( $page != 0 )  {
				$query_string = array_to_query_string( $_GET, array( 'page' ) );
				$query_string .= '&page=' . ($page-1);
				$pager_html .= "<a href=\"$php_self?$query_string\">&lsaquo;&lsaquo;</a>";
			} else {
				$pager_html .= "&lsaquo;&lsaquo;";
			}
			$pager_html .= '&nbsp;&nbsp;';

            // for large search results where we page beyone the first 20 pages, 
            // print elipsis instead of making the pager be super wide.
            $elipsis_printed = false;
			for( $i=0; $i<$count; $i+=$size_limit ) {
				$page_num = $i/$size_limit;
                if( $count > $size_limit * 20 && abs( $page_num - $page ) > 10 ) {
                    if( ! $elipsis_printed ) {
                        $pager_html .= '...&nbsp;&nbsp;';
                        $elipsis_printed = true;
                    }
                } else if( $page == $page_num ) {
					$pager_html .= ($page_num + 1);
                    $pager_html .= '&nbsp;&nbsp;';
                    $elipsis_printed = false;
				} else {
					$query_string = array_to_query_string( $_GET, array( 'page' ) );
					$query_string .= '&page=' . $page_num;
					$pager_html .= "<a href=\"$php_self?$query_string\">" . ($page_num+1) . "</a>";
                    $pager_html .= '&nbsp;&nbsp;';
                    $elipsis_printed = false;
				}
			}
			if( $page+1 < ( $count / $size_limit ) )  {
				$query_string = array_to_query_string( $_GET, array( 'page' ) );
				$query_string .= '&page=' . ($page+1);
				$pager_html .= "<a href=\"$php_self?$query_string\">&rsaquo;&rsaquo;</a>";
			} else {
				$pager_html .= "&rsaquo;&rsaquo;";
			}

		} 
        echo $pager_html;
        // Done drawing pager
        ?>
		<br />

		</center>

		<?php flush(); ?>

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

        echo '<br />';
        echo '<center>';
        echo $pager_html;
        echo '</center>';

		?>

		<br /><br />
		<div class="search_result"><center><span style="font-weight:normal;font-size:75%;">
			<?php echo $lang['search_duration']; ?>
			<b><?php echo $time_elapsed; ?></b> <?php echo $lang['seconds'];?>.</small></center></div>
		<?php 
	}
}

?>

</body>
</html>

