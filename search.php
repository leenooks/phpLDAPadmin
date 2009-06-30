<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/search.php,v 1.58 2004/12/10 04:43:17 uugdave Exp $
 

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

require './common.php';


// try to get an available server_id if one is not provided
if( ! isset( $_GET['server_id'] ) )
	$server_id = get_avail_server_id();
else {
	$server_id = $_GET['server_id'];
	check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
}

// Output format, table or list?
$result_formats = array( 'list', 'table' );
$format = isset( $_GET['format'] ) ? $_GET['format'] : get_default_search_display();
if( ! in_array( $format, $result_formats ) )
    $format = 'list';

$js_on_change_string ='';
if( isset( $_GET['form'] ) && $_GET['form'] == 'advanced' ) 
	$js_on_change_string = 
		'onChange="document.forms[0].base_dn.value=servers[document.forms[0].server_id.value].getBaseDn()"';

// build the server drop-down html and JavaScript array (for base_dns)

$server_info_list =array();

$server_menu_html = '';
if (count($servers)>1){
	$server_menu_html = '<select style="width: 200px" name="server_id" '.$js_on_change_string.'>';
	foreach( $servers as $id => $server ) { 
		$base_dn = $server['base'] ? $server['base'] : try_to_get_root_dn( $id );
		$server_info_list[$id]['id'] = $id;
		$server_info_list[$id]['name'] = $server['name'];
		$server_info_list[$id]['base_dn'] = $base_dn;
	
		if( $server['host'] ) { 
			$server_menu_html .= '<option value="'.$id.'"' . ( $id==$server_id? ' selected="true"' : '' ) . '>';
			$server_menu_html .= $server['name'] . '</option>';
		}
	}
	$server_menu_html .= '</select>';
} else {
	$server  = reset($servers);
	$id		   = key($servers);
	$base_dn = $server['base'] ? $server['base'] : try_to_get_root_dn( $id );
	$server_info_list[$id]['id'] = $id;
	$server_info_list[$id]['name'] = $server['name'];
	$server_info_list[$id]['base_dn'] = $base_dn;
	if( $server['host'] )
		$server_menu_html .= '<input type="hidden" name="server_id" value="'.$id.'" />' .
												 '<b>' . $server['name'] . '</b>';
}


$filter = isset( $_GET['filter'] ) ? $_GET['filter'] : null;
$attr = isset( $_GET['attribute'] ) ? $_GET['attribute'] : null;

// grab the base dn for the search
if( isset( $_GET['base_dn'] ) ) {
	$base_dn = $_GET['base_dn'];
    $base_dn_is_invalid = false;
    $base_dn_does_not_exist = false;
    if( trim( $base_dn ) )
        if( ! is_dn_string( $base_dn ) )
            $base_dn_is_invalid = true;
        elseif( ! dn_exists( $server_id, $base_dn ) )
            $base_dn_does_not_exist = true;
} elseif( '' != $servers[$server_id]['base'] )
	$base_dn = $servers[$server_id]['base'];
else 
	$base_dn = try_to_get_root_dn( $server_id );
	
$criterion = isset( $_GET['criterion'] ) ? $_GET['criterion'] : null;
$form = isset( $_GET['form'] ) ? $_GET['form']  : null;
$scope = isset( $_GET['scope'] ) ? $_GET['scope'] : 'sub';

include './header.php'; ?>

<body>

<center>

<?php  if( $form == 'advanced' ) { 

	include 'search_form_advanced.php';

} elseif( $form == 'predefined' ) {
	
	include 'search_form_predefined.php';

} else /* Draw simple search form */ {

	process_config();
    if( count( $search_attributes ) != count( $search_attributes_display ) )
        pla_error( $lang['mismatched_search_attr_config'] );
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

	
	//making $predefined safe for "register_globals off"
	if( isset( $_GET['predefined'] ) )
	{
		$predefined = $_GET['predefined'];
		if( is_numeric( $predefined ) )
		{
			$query = get_cleaned_up_predefined_search( $predefined );

			$search_result_attributes = $query['attributes'];
			$search_attributes_display = $search_result_attributes;
			$search_attributes = $search_result_attributes;

			process_config();
	
			$filter = $query['filter'];
			$scope = $query['scope'];
			$base_dn = $query['base'];
			$server_id = $query['server'];
	    }
	} else { 
        $predefined = '';
	}
	$ds = pla_ldap_connect( $server_id );
	pla_ldap_connection_is_error( $ds );
	
	
	if( $filter )
	{
		// if they are using the simple search form, build an LDAP search filter from their input
		if( $form == 'simple' & ! is_numeric( $predefined ) )
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

        <table class="search_header">
        <tr>
        <td style="vertical-align: top">
			<nobr><?php echo $lang['entries_found'] . ' &nbsp;<b>' . number_format( $count ) ?></b>&nbsp;</nobr>
			<small><nobr>(<?php echo $time_elapsed; ?> <?php echo $lang['seconds']; ?>)</nobr></small>
        </td>
        <td style="text-align: right">

        <small>
        <nobr>
        <?php echo "[ <a href=\"export_form.php?server_id=$server_id&amp;scope=$scope&amp;dn=" . 
                urlencode($base_dn) . "&amp;filter=" . urlencode($filter) . "&amp;attributes=" . 
                urlencode( join( ', ', $search_result_attributes ) ) . "\"><img src=\"images/save.png\" /> " . 
                $lang['export_results'] . "</a> ]"; ?>
        <?php 
        echo '[ <img src="images/rename.png" /> ' . $lang['format'] . ':';
        foreach( $result_formats as $f )  {
            echo '&nbsp;';
            if( $format == $f ) {
                echo '<b>' . $lang[$f] . '</b>';
            } else {
                $php_self = $_SERVER['PHP_SELF'];
                $query_string = array_to_query_string( $_GET, array( 'format' ) );
                $query_string .= "&amp;format=$f";
                echo "<a href=\"$php_self?$query_string\">" . $lang[$f] . "</a>";
            }
        }
        ?>
        ]
        </small>
        </nobr>
		<?php  if( $form == 'simple' || $form == 'predefined' ) { ?>
		    <br /><nobr><small><?php echo $lang['filter_performed']; ?>
				<?php echo htmlspecialchars( $filter ); ?></small></nobr>
		<?php  } ?>


        </td>
        </tr>
        </table>

        <?php 

        // The LDAP error code for the size limit exceeded error.
        define( 'SIZE_LIMIT_EXCEEDED', 4 );
        if( $errno && $errno == SIZE_LIMIT_EXCEEDED ) {
            echo "<br /><center><small>" . $lang['size_limit_exceeded'] . "</small></center><br />\n";
        }

		// Draw the paging links
        $pager_html = '';
        $total_pages = $count / $size_limit;
        $results_per_page = $size_limit;
		if( $count > $size_limit ) {
            echo sprintf( $lang['showing_results_x_through_y'], "<b>" . number_format($start_entry+1) . "</b>", "<b>" . number_format($end_entry-1) . "</b>" ) . "<br />\n";
			$php_self = $_SERVER['PHP_SELF'];
			if( $page != 0 )  {
				$query_string = array_to_query_string( $_GET, array( 'page' ) );
				$query_string .= '&page=' . ($page-1);
				$pager_html .= "<a title=\"" . sprintf( $lang['page_n'],$page ) . "\" href=\"$php_self?$query_string\">&lsaquo;&lsaquo;</a>";
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
					$pager_html .= '<b>' . ($page_num + 1) . '</b>';
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
			if( $page+1 < $total_pages )  {
				$query_string = array_to_query_string( $_GET, array( 'page' ) );
				$query_string .= '&page=' . ($page+1);
				$pager_html .= "<a title=\"" . sprintf( $lang['page_n'],($page+2) ) . "\" href=\"$php_self?$query_string\">&rsaquo;&rsaquo;</a>";
			} else {
				$pager_html .= "&rsaquo;&rsaquo;";
			}

		} 

        if( 0 == $count )
            echo '<center><h2>' . $lang['no_results'] . '</h2></center>';
        else {
            echo '<center>' . $pager_html . '</center><br />';
            flush();
            if( $format == 'list' )
                include realpath( './search_results_list.php' );
            elseif( $format == 'table' )
                include realpath( './search_results_table.php' );
            else
                pla_error( sprintf( $lang['unrecoginzed_search_result_format'], htmlspecialchars( $format ) ) );
            echo '<br /><center>' . $pager_html . '</center>';
        }

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

