<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/search.php,v 1.72.2.13 2006/10/28 05:56:56 wurley Exp $

/**
 * Perform LDAP searches and draw the advanced/simple search forms
 *
 * Variables that come in as GET vars:
 *  - server_id (handled in common.php)
 *  - search (true if performing a search, empty to just draw form)
 *  For simple searches:
 *  - attribute, criterion, filter
 *  For advanced searches:
 *  - base_dn, scope, filter
 *
 * @package phpLDAPadmin
 * @todo Search is probably broken, since base_dn is now an array
 */
/**
 */

define('SIZE_LIMIT_EXCEEDED',4);
require './common.php';

if (isset($ldapserver) && ! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

# Output format, table or list?
$result_formats = array('list','table');
$format = isset($_GET['format']) ? $_GET['format'] : $config->GetValue('search','display');

if (! in_array($format,$result_formats))
	$format = 'list';

# build the server drop-down html and JavaScript array (for base_dns)
$js_on_change_string = '';
if (isset($_GET['form']) && $_GET['form'] == 'advanced')
	$js_on_change_string =
		'onChange="document.forms[0].base_dn.value=servers[document.forms[0].server_id.value].base_dn"';

if (isset($ldapserver)) {
	$server_menu_html = server_select_list($ldapserver->server_id,true,'server_id',$js_on_change_string);
	$server_info_list = server_info_list();
}

$filter = isset($_GET['filter']) ? clean_search_vals($_GET['filter']) : null;
$orderby = isset($_GET['orderby']) ? clean_search_vals($_GET['orderby']) : null;
$attr = isset($_GET['attribute']) ? $_GET['attribute'] : null;

# grab the base dn for the search
if (isset($_GET['base_dn']) && $_GET['base_dn']) {
	$base_dn = $_GET['base_dn'];
	$base_dn_is_invalid = false;
	$base_dn_does_not_exist = false;

	if (trim($base_dn))
		if (! is_dn_string($base_dn))
			$base_dn_is_invalid = true;

	elseif (! $ldapserver->dnExists($base_dn))
		$base_dn_does_not_exist = true;

	$base_dns = array($base_dn);

} else
	if (isset($ldapserver))
		$base_dns = $ldapserver->getBaseDN();

$criterion = isset($_GET['criterion']) ? $_GET['criterion'] : null;

if (isset($_GET['form']))
	$_SESSION['form'] = $_GET['form'];
$form = isset($_SESSION['form']) ? $_SESSION['form']  : null;
$scope = isset($_GET['scope']) ? $_GET['scope'] : 'sub';

include './header.php';

echo '<body><center>';

if ($form == 'advanced')
	require LIBDIR.'search_form_advanced.php';

elseif ($form == 'predefined')
	require LIBDIR.'search_form_predefined.php';

# Draw simple search form
else
	require LIBDIR.'search_form_simple.php';

echo '</center>';

flush();

if (isset($_GET['search'])) {
	if ($form == 'advanced') {
		if (isset($_GET['display_attrs']))
			$search_result_attributes = explode(',',rawurldecode(preg_replace('/\s+/','',rawurldecode($_GET['display_attrs']))));
		else
			$search_result_attributes = $config->GetValue('search','result_attributes');

	} else {
		$search_result_attributes = $config->GetValue('search','result_attributes');
	}

	# do we have enough authentication information for the specified server_id
	if (! $ldapserver->haveAuthInfo()) {
		$login_url = sprintf('login_form.php?server_id=%s&amp;redirect=%s',
			$ldapserver->server_id,rawurlencode($_SERVER['REQUEST_URI']));

		printf('<center><br />%s <br /><a href="%s">%s</a>.</center>',
			_('You have not logged into the selected server yet, so you cannot perform searches on it.'),$login_url,_('Click here to go to the login form'));
		exit;
	}

	if (isset($_GET['predefined'])) {
		$predefined = $_GET['predefined'];

		if (is_numeric($predefined)) {
			$query = get_cleaned_up_predefined_search($predefined);

			$search_result_attributes = explode(', ',$query['attributes']);
			$search_attributes_display = $search_result_attributes;
			$search_attributes = $search_result_attributes;

			$filter = $query['filter'];
			$scope = $query['scope'];

			if (! trim($query['base']))
				$query['base'] = $ldapserver->getBaseDN();

			elseif (is_array($query['base']))
				$base_dns = $query['base'];

			else
				$base_dns = array($query['base']);
		}

	} else {
		$predefined = '';
	}

	if ($filter) {
		# if they are using the simple search form, build an LDAP search filter from their input
		if ($form == 'simple' & ! is_numeric($predefined)) {

			switch ($criterion) {
				case 'starts with':
					if ($filter == '*')
						$filter = '';

					$filter = "($attr=$filter*)";
					break;

				case 'contains':
					if ($filter == '*')
						$filter = "($attr=*)";
					else
						$filter = "($attr=*$filter*)";

					break;

				case 'ends with':
					if ($filter == '*')
						$filter = '';

					$filter = "($attr=*$filter)";
					break;

				case 'equals':
					$filter = "($attr=$filter)";
					break;

				case 'sounds like':
					$filter = "($attr~=$filter)";
					break;

				default:
					pla_error(_('Unrecognized criteria option: ').htmlspecialchars($criterion)._('If you want to add your own criteria to the list. Be sure to edit search.php to handle them. Quitting.'));
			}
		}

		printf('<center>%s</center>',_('Searching...'));
		flush();

		# prevent script from bailing early on a long delete
		@set_time_limit(0);

		$size_limit = $config->GetValue('search','size_limit');

		# Sanity check
		if ($size_limit < 1)
			$size_limit = 1;

		$page = isset($_GET['page']) ? $_GET['page'] : 0;

		$time_start = utime();
		$time_elapsed = 0;

		foreach ($base_dns as $base_dn) {
			if (! $ldapserver->dnExists($base_dn)) {
				if (DEBUG_ENABLED)
					debug_log('BaseDN [%s] skipped as it doesnt exist in [%s].',64,
						$base_dn,$ldapserver->server_id);

				continue;

			} else {
				if (DEBUG_ENABLED)
					debug_log('Search with base DN [%s]',64,$base_dn);
			}

			$results = $ldapserver->search(null,dn_escape($base_dn),$filter,
				$search_result_attributes,$scope,$orderby,$config->GetValue('deref','search'));

			if ((! $results) && $ldapserver->errno())
				pla_error(_('Encountered an error while performing search.'),$ldapserver->error(),$ldapserver->errno());

			$errno = $ldapserver->errno();

			$time_end = utime();
			$time_elapsed += round($time_end-$time_start,2);
			$count = count($results);

			$start_entry = $page * $size_limit;
			$end_entry = min($start_entry+$size_limit+1,$count+1);
?>

        <table class="search_header">
        <tr>
        <td style="vertical-align: top">
			<nobr><?php echo _('Entries found: ') . ' &nbsp;<b>' . number_format($count) ?></b>&nbsp;</nobr>
			<nobr><small>(<?php echo $time_elapsed; ?> <?php echo _('seconds'); ?>)</small></nobr>
        </td>
        <td style="text-align: right">

        <nobr>
        <small>
        <?php
			printf('[ <a href="export_form.php?server_id=%s&amp;scope=%s&amp;dn=%s&amp;filter=%s&amp;attributes=%s"><img src="images/save.png" alt="Save" /> %s</a> ]',
				$ldapserver->server_id,htmlspecialchars($scope),urlencode($base_dn),urlencode($filter),
				urlencode(join(', ',$search_result_attributes)),_('export results'));

			printf('[ <img src="images/rename.png" /> %s:',_('Format'));

			foreach ($result_formats as $f) {
				echo '&nbsp;';

				if ($format == $f) {
					printf('<b>%s</b>',_($f));

				} else {
					$query_string = array_to_query_string($_GET,array('format'));
					$query_string .= "&amp;format=$f";
					printf('<a href="search.php?%s">%s</a>',$query_string,_($f));
				}
			}
?>

        ]
        </small>
        </nobr>
			<?php if ($form == 'simple' || $form == 'predefined') { ?>
				<br /><nobr><small><?php echo _('Base DN: '); ?>
				<b><?php echo htmlspecialchars($base_dn); ?></b></small></nobr>
				<br /><nobr><small><?php echo _('Filter performed: '); ?>
				<b><?php echo htmlspecialchars($filter); ?></b></small></nobr>
			<?php } ?>

        </td>
        </tr>
        </table>

        <?php

			# The LDAP error code for the size limit exceeded error.
			if ($errno && $errno == SIZE_LIMIT_EXCEEDED)
				printf('<br /><center><small style="color:red; white-space: nowrap">%s</small></center><br />',_('Notice, search size limit exceeded.'));

			# Draw the paging links
			$pager_html = '';
			$total_pages = $count / $size_limit;
			$results_per_page = $size_limit;

			if ($count > $size_limit) {
				printf(_('Showing results %s through %s.').'<br />',
					'<b>'.number_format($start_entry+1).'</b>','<b>'.number_format($end_entry-1).'</b>');

				if ($page != 0) {
					$query_string = array_to_query_string($_GET,array('page'));
					$query_string .= '&page=' . ($page-1);
					$pager_html .= sprintf('<a title="'._('Page %d').'" href="search.php?%s">&lsaquo;&lsaquo;</a>',$page,$query_string);

				} else {
					$pager_html .= "&lsaquo;&lsaquo;";
				}

				$pager_html .= '&nbsp;&nbsp;';

				// for large search results where we page beyone the first 20 pages,
				// print elipsis instead of making the pager be super wide.
				$elipsis_printed = false;
				for ($i=0;$i<$count;$i+=$size_limit) {
					$page_num = $i/$size_limit;

					if ($count > $size_limit * 20 && abs($page_num-$page) > 10) {

						if (! $elipsis_printed) {
							$pager_html .= '...&nbsp;&nbsp;';
							$elipsis_printed = true;
						}

					} elseif ($page == $page_num) {
						$pager_html .= '<b>' . ($page_num + 1) . '</b>';
						$pager_html .= '&nbsp;&nbsp;';
						$elipsis_printed = false;

					} else {
						$query_string = array_to_query_string($_GET,array('page'));
						$query_string .= '&page=' . $page_num;
						$pager_html .= "<a href=\"search.php?$query_string\">" . ($page_num+1) . "</a>";
						$pager_html .= '&nbsp;&nbsp;';
						$elipsis_printed = false;
					}
				}

				if ($page+1 < $total_pages) {
					$query_string = array_to_query_string($_GET,array('page'));
					$query_string .= '&page=' . ($page+1);
					$pager_html .= "<a title=\"" . sprintf(_('Page %d'),($page+2))."\" href=\"search.php?$query_string\">&rsaquo;&rsaquo;</a>";

				} else {
					$pager_html .= "&rsaquo;&rsaquo;";
				}

			}

			if (0 == $count)
				printf('<center><h2>%s</h2></center>',_('The search found no results.'));

			else {
				if (trim($pager_html))
					printf('<center>%s</center>',$pager_html);
				echo '<br />';
				flush();

				if ($format == 'list')
					require LIBDIR.'search_results_list.php';
				elseif ($format == 'table')
					require LIBDIR.'search_results_table.php';
				else
					pla_error(sprintf(_('Unrecognized search result format: %s'),htmlspecialchars($format)));

				echo '<br />';
				if (trim($pager_html))
					printf('<center>%s</center>',$pager_html);
			}
		}

		printf('<br /><br /><div class="search_result"><center><small><span style="font-weight:normal;font-size:75%%;">%s <b>%s</b> %s.</span></small></center></div>',
			_('Search performed by phpLDAPadmin in'),$time_elapsed,_('seconds'));

	}
}
echo '</body></html>';
?>
