<?php 

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

$server_id = $_GET['server_id'];

// try to get an available server_id if one is not provided
if( ! isset( $server_id ) )
{
	$server_id = get_avail_server_id();
}
else
{
	check_server_id( $server_id ) or pla_error( "Bad server_id: " . var_dump( htmlspecialchars( $server_id ) ) );
}
$js_on_change_string ='';
  if( $_GET['form'] == 'advanced' ) { 
    $js_on_change_string = 'onChange="document.forms[0].base_dn.value=servers[document.forms[0].server_id.value].getBaseDn()"';
  }

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

$filter = $_GET['filter'];
$filter = utf8_encode($filter);
$attr = $_GET['attribute'];

// grab the base dn for the search
if( isset( $_GET['base_dn'] ) )
	$base_dn = $_GET['base_dn'];
elseif( '' != $servers[$server_id]['base'] )
	$base_dn = $servers[$server_id]['base'];
else 
	$base_dn = try_to_get_root_dn( $server_id );
	
$criterion = $_GET['criterion'];
$form = $_GET['form'];
$scope = $_GET['scope'] ? $_GET['scope'] : 'sub';
//echo "<PRE>";print_r( $_GET );echo "</pre>";
?>

<?php include 'header.php'; ?>
<body>

<center>

<?php  if( $form == 'advanced' ) { 

	include 'search_form_advanced.php';

} else /* Draw simple search form */ {

	process_config();
	include 'search_form_simple.php';

} ?>

</center>

<?php flush(); ?>

<?php 

if( $_GET['search'] )
{

	if( $form == 'advanced'  ) {
		$search_result_attributes = isset( $_GET['display_attrs'] ) ? 
						$_GET['display_attrs'] :
						$search_result_attributes;
		process_config();
	} 

	// do we have enough authentication information for the specified server_id
	if( ! have_auth_info( $server_id ) )
	{
		$login_url = "login_form.php?server_id=$server_id&amp;redirect=" . rawurlencode( $_SERVER['REQUEST_URI'] );
		?>
		<center>
		<br />
		You haven't logged into server <b><?php echo htmlspecialchars( $servers[$server_id]['name'] ); ?></b>
		yet. Go to the <a href="<?php echo $login_url; ?>">login form</a> to do so.
		</center>
		<?php
		exit;
	}

	pla_ldap_connect( $server_id ) or pla_error( "Could not connect to LDAP server." );
	
	if( $filter )
	{

		// if they are using the simple search form, build an LDAP search filter from their input
		if( $form == 'simple' )
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
					pla_error( "Unrecognized criteria option: " . htmlspecialchars( $criterion ) .
						"If you want to add your own criteria to the list. Be sure to edit " .
						"search.php to handle them. Quitting." );
			}
		}
		
		$time_start = utime();
		$results = pla_ldap_search( $server_id, $filter, $base_dn, $search_result_attributes, $scope );
		$time_end = utime();
		$time_elapsed = round( $time_end - $time_start, 2 );
		$count = count( $results );

		?>

		<br />
		<center>Found <b><?php echo $count; ?></b> <?php echo $count==1?'entry':'entries'; ?>.

		<?php  if( $form == 'simple' ) { ?>
			<center><small>Filter performed: <?php echo htmlspecialchars( $filter ); ?></small></center>
		<?php  } ?>

		</center>

		<?php flush(); ?>	

		<?php if( $results ) foreach( $results as $dn => $attrs ) { ?>
			<?php  $encoded_dn = rawurlencode( $dn ); ?>
			<?php  $rdn = utf8_decode( get_rdn( $dn ) ); ?>
			<div class="search_result">
			<a href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>">
				<?php echo htmlspecialchars($rdn); ?>
			</a>
			</div>
			<table class="attrs">
				<?php foreach( $attrs as $attr => $values ) { ?>

					<tr>
						<td class="attr" valign="top"><?php echo htmlspecialchars( $attr ); ?></td>
						<td class="val">
							<?php  if( is_array( $values ) ) { ?>
								<?php  foreach( $values as $value ) { ?>
								<?php echo str_replace( ' ', '&nbsp;',
								htmlspecialchars( utf8_decode( $value ) ) ); ?><br />
								<?php  } ?>
							<?php  } else { ?>
								<?php echo str_replace( ' ', '&nbsp;',
								htmlspecialchars( utf8_decode( $values ) ) ); ?>
							<?php  } ?>
						</td>
					</tr>

				<?php  } ?>
			</table>

		<?php  } ?>

			<br /><br />
			<div class="search_result"><center><span style="font-weight:normal;font-size:75%;">Search happily performed by phpLDAPadmin in 
				<b><?php echo $time_elapsed; ?></b> seconds.</small></center></div>
		<?php 
	}
}

?>

</body>
</html>

<?php 

function utime ()
{
	$time = explode( " ", microtime());
	$usec = (double)$time[0];
	$sec = (double)$time[1];
	return $sec + $usec;
}

?>
