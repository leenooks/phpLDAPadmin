<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/search.php,v 1.78.2.4 2007/12/29 08:24:10 wurley Exp $

/**
 * Perform LDAP searches and draw the advanced/simple search forms
 *
 * Variables that come in as GET vars:
 *  - search (true if performing a search, empty to just draw form)
 *  For simple searches:
 *  - attribute, criterion, filter
 *  For advanced searches:
 *  - base_dn, scope, filter
 *
 * @package phpLDAPadmin
 * @todo Search is probably broken, since base_dn is now an array
 * @todo Order doesnt Order properly - probably case related.
 */
/**
 */

define('SIZE_LIMIT_EXCEEDED',4);

# Output format, table or list?
$result_formats = array('list','table');

# Our incoming variables
$entry['format'] = get_request('format','GET','false',$_SESSION[APPCONFIG]->GetValue('search','display'));
$entry['form'] = get_request('form','GET',false,get_request('form','SESSION'));

$entry['orderby']['raw'] = get_request('orderby','GET');
$entry['orderby']['string'] = rawurldecode($entry['orderby']['raw']);
$entry['orderby']['array'] = $entry['orderby']['raw'] ? explode(',',preg_replace('/\s+/','',$entry['orderby']['string'])) : true;

$entry['display']['raw'] = get_request('display_attrs','GET');
$entry['display']['string'] = rawurldecode($entry['display']['raw']);
$entry['display']['array'] = $entry['display']['raw'] ? explode(',',preg_replace('/\s+/','',$entry['display']['string'])) : array();

$entry['filter']['string'] = get_request('filter','GET');
$entry['filter']['clean'] = $entry['filter']['string'] ? clean_search_vals($entry['filter']['string']) : null;

$entry['attr'] = get_request('attribute','GET');
$entry['base_dn']['string'] = get_request('base_dn','GET');
$entry['criterion'] = get_request('criterion','GET');
$entry['scope'] = get_request('scope','GET','false','sub');
$entry['search'] = get_request('search','GET');
$entry['predefined'] = get_request('predefined','GET');
$entry['page'] = get_request('page','GET',false,0);

if (! in_array($entry['format'],$result_formats))
	$entry['format'] = 'list';

$_SESSION['form'] = $entry['form'];

# build the server drop-down html and JavaScript array (for base_dns)
$js_on_change_string = '';
if ($entry['form'] == 'advanced')
	$js_on_change_string = 'onChange="document.forms[0].base_dn.value=servers[document.forms[0].server_id.value].base_dn"';

if (isset($ldapserver)) {
	$server_menu_html = server_select_list($ldapserver->server_id,true,'server_id',$js_on_change_string);
	$server_info_list = server_info_list();
}

# grab the base dn for the search
if ($entry['base_dn']['string']) {
	$entry['base_dn']['invalid'] = false;
	$entry['base_dn']['exist'] = false;

	if (! is_dn_string($entry['base_dn']['string']))
		$entry['base_dn']['invalid'] = true;

	elseif (! $ldapserver->dnExists($entry['base_dn']['string']))
		$entry['base_dn']['exist'] = true;

	$base_dns = array($entry['base_dn']['string']);

} else
	if (isset($ldapserver))
		$base_dns = $ldapserver->getBaseDN();

printf('<script type="text/javascript" src="%ssearch_util.js"></script>','../htdocs/'.JSDIR);
echo '<center>';

$entry['command']['as'] = $_SESSION[APPCONFIG]->isCommandAvailable('search','advanced_search');
$entry['command']['ps'] = $_SESSION[APPCONFIG]->isCommandAvailable('search','predefined_search');
$entry['command']['ss'] = $_SESSION[APPCONFIG]->isCommandAvailable('search','simple_search');

if ($entry['form'] == 'advanced') {
	if ($entry['command']['as'])
		require LIBDIR.'search_form_advanced.php';

} elseif ($entry['form'] == 'predefined') {
	if ($entry['command']['ps'])
		require LIBDIR.'search_form_predefined.php';

} elseif ($entry['form'] == 'simple') {
	# Draw simple search form
	if ($entry['command']['ss'])
		require LIBDIR.'search_form_simple.php';

} elseif ($entry['command']['as']) {
	require LIBDIR.'search_form_advanced.php';

} elseif ($entry['command']['ss']) {
	require LIBDIR.'search_form_simple.php';

} elseif ($entry['command']['ps']) {
	require LIBDIR.'search_form_predefined.php';

} else {
	echo _('Searching is not permitted');
}

echo '</center>';
echo '<br />';

if ($entry['search']) {
	if ($entry['form'] == 'advanced') {
		if (! $_SESSION[APPCONFIG]->isCommandAvailable('search','advanced_search'))
			pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('advanced search')));

	} elseif ($entry['form'] == 'predefined') {
		if (! $_SESSION[APPCONFIG]->isCommandAvailable('search','predefined_search'))
			pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('predefined search')));

	} elseif ($entry['form'] == 'simple') {
		if (! $_SESSION[APPCONFIG]->isCommandAvailable('search','simple_search'))
			pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('simple search')));
	}

	if ($entry['form'] == 'advanced') {
		if ($entry['display'])
			$search_result_attributes = $entry['display']['array'];
		else
			$search_result_attributes = $_SESSION[APPCONFIG]->GetValue('search','result_attributes');

	} else {
		$search_result_attributes = $_SESSION[APPCONFIG]->GetValue('search','result_attributes');
	}

	# do we have enough authentication information for the specified server_id
	# @todo: redirect isnt working
	if (! $ldapserver->haveAuthInfo()) {
		$login_url = sprintf('cmd.php?cmd=login_form&amp;server_id=%s&amp;redirect=%s',
			$ldapserver->server_id,rawurlencode($_SERVER['REQUEST_URI']));

		system_message(array(
			'title'=>_('Search'),
			'body'=>_('You have not logged into the selected server yet, so you cannot perform searches on it.'),
			'type'=>'warn'),
			$login_url);
	}

	if (is_numeric($entry['predefined'])) {
		$query = get_cleaned_up_predefined_search($entry['predefined']);

		$search_result_attributes = explode(', ',$query['attributes']);
		$search_attributes_display = $search_result_attributes;
		$search_attributes = $search_result_attributes;

		$entry['filter']['clean'] = $query['filter'];
		$entry['scope'] = $query['scope'];

		if (! trim($query['base']))
			$query['base'] = $ldapserver->getBaseDN();

		elseif (is_array($query['base']))
			$base_dns = $query['base'];

		else
			$base_dns = array($query['base']);

	} else {
		$entry['predefined'] = '';
	}

	if ($entry['filter']['clean']) {
		# if they are using the simple search form, build an LDAP search filter from their input
		if ($entry['form'] == 'simple' & ! is_numeric($entry['predefined'])) {

			switch ($entry['criterion']) {
				case 'starts with':
					if ($entry['filter']['clean'] == '*')
						$entry['filter']['clean'] = '';

					$entry['filter']['clean'] = sprintf('(%s=%s*)',$entry['attr'],$entry['filter']['clean']);
					break;

				case 'contains':
					if ($entry['filter']['clean'] == '*')
						$entry['filter']['clean'] = sprintf('(%s=*)',$entry['attr']);
					else
						$entry['filter']['clean'] = sprintf('(%s=*%s*)',$entry['attr'],$entry['filter']['clean']);

					break;

				case 'ends with':
					if ($entry['filter']['clean'] == '*')
						$entry['filter']['clean'] = '';

					$entry['filter']['clean'] = sprintf('(%s=*%s)',$entry['attr'],$entry['filter']['clean']);
					break;

				case 'equals':
					$entry['filter']['clean'] = sprintf('(%s=%s)',$entry['attr'],$entry['filter']['clean']);
					break;

				case 'sounds like':
					$entry['filter']['clean'] = sprintf('(%s~=%s)',$entry['attr'],$entry['filter']['clean']);
					break;

				default:
					system_message(array(
						'title'=>_('Unrecognized criteria option: ').htmlspecialchars($entry['criterion']),
						'body'=>_('If you want to add your own criteria to the list. Be sure to edit search.php to handle them. Quitting.'),
						'type'=>'warn'),
						sprintf('cmd.php?cmd=search&server_id=%s',$ldapserver->server_id));
			}
		}

		# prevent script from bailing early on a long delete
		@set_time_limit(0);

		$size_limit = $_SESSION[APPCONFIG]->GetValue('search','size_limit');

		# Sanity check
		if ($size_limit < 1)
			$size_limit = 1;

		$time_start = utime();
		$time_elapsed = 0;

		foreach ($base_dns as $base_dn) {
			if (! $ldapserver->dnExists($base_dn)) {
				if (DEBUG_ENABLED)
					debug_log('BaseDN [%s] skipped as it doesnt exist in [%s].',64,__FILE__,__LINE__,__METHOD__,
						$base_dn,$ldapserver->server_id);

				continue;

			} else {
				if (DEBUG_ENABLED)
					debug_log('Search with base DN [%s]',64,__FILE__,__LINE__,__METHOD__,$base_dn);
			}

			$results = $ldapserver->search(null,dn_escape($base_dn),$entry['filter']['clean'],$search_result_attributes,
				$entry['scope'],$entry['orderby']['array'],$_SESSION[APPCONFIG]->GetValue('deref','search'));

			if ((! $results) && $ldapserver->errno())
				pla_error(_('Encountered an error while performing search.'),$ldapserver->error(),$ldapserver->errno());

			$errno = $ldapserver->errno();

			$time_end = utime();
			$time_elapsed += round($time_end-$time_start,2);
			$count = count($results);

			$start_entry = $entry['page'] * $size_limit;
			$end_entry = min($start_entry+$size_limit+1,$count+1);

			# Search Results Table
			echo '<table class="search_result" border=0>';

			echo '<tr class="header">';
			printf('<td>%s%s <b>%s</b> <small>(%s %s)</small></td>',_('Entries found'),_(':'),
				number_format($count),$time_elapsed,_('seconds'));

			if ($_SESSION[APPCONFIG]->isCommandAvailable('export')) {
				$href = htmlspecialchars(sprintf('cmd.php?cmd=export_form&server_id=%s&scope=%s&dn=%s&filter=%s&attributes=%s',
					$ldapserver->server_id,$entry['scope'],$base_dn,rawurlencode($entry['filter']['clean']),rawurlencode(join(', ',$search_result_attributes))));

				printf('<td style="text-align: right"><small>[ <a href="%s"><img src="images/save.png" alt="Save" /> %s</a> ]',
					$href,_('export results'));
			}

			printf('[ <img src="images/rename.png" alt="rename" /> %s%s',_('Format'),_(':'));

			foreach ($result_formats as $f) {
				echo '&nbsp;';

				if ($entry['format'] == $f) {
					printf('<b>%s</b>',_($f));

				} else {
					$query_string = array_to_query_string($_GET,array('format','cmd'));
					$query_string .= sprintf('&amp;format=%s',$f);
					printf('<a href="cmd.php?cmd=search&amp;%s">%s</a>',$query_string,_($f));
				}
			}

			echo ' ]</small>';

			if ($_SESSION[APPCONFIG]->isCommandAvailable('schema')) {
				echo '<br />';
				printf('<small>%s%s <b>%s</b></small>',_('Base DN'),_(':'),htmlspecialchars($base_dn));

				if ($entry['form'] == 'simple' || $entry['form'] == 'predefined') {
					echo '<br />';
					printf('<small>%s%s <b>%s</b></small>',_('Filter performed'),_(':'),htmlspecialchars($entry['filter']['clean']));
				}
			}

			echo '</td>';
			echo '</tr>';
			echo '</table>';

			# The LDAP error code for the size limit exceeded error.
			# @todo: test this with system_message
			if ($errno && $errno == SIZE_LIMIT_EXCEEDED)
				printf('<br /><center><small style="color:red; white-space: nowrap">%s</small></center><br />',_('Notice, search size limit exceeded.'));

			# Draw the paging links
			$pager_html = '';
			$total_pages = $count / $size_limit;
			$results_per_page = $size_limit;

			if ($count > $size_limit) {
				printf(_('Showing results %s through %s.').'<br />',
					'<b>'.number_format($start_entry+1).'</b>','<b>'.number_format($end_entry-1).'</b>');

				if ($entry['page'] != 0) {
					$query_string = array_to_query_string($_GET,array('page','cmd'));
					$query_string .= '&page=' . ($entry['page']-1);
					$pager_html .= sprintf('<a title="'._('Page %d').'" href="cmd.php?cmd=search&amp;%s">&lsaquo;&lsaquo;</a>',$entry['page'],$query_string);

				} else {
					$pager_html .= '&lsaquo;&lsaquo;';
				}

				$pager_html .= '&nbsp;&nbsp;';

				/* For large search results where we page beyond the first 20 pages,
				 * print elipsis instead of making the pager be super wide. */
				$elipsis_printed = false;
				for ($i=0;$i<$count;$i+=$size_limit) {
					$page_num = $i/$size_limit;

					if ($count > $size_limit * 20 && abs($page_num-$entry['page']) > 10) {

						if (! $elipsis_printed) {
							$pager_html .= '...&nbsp;&nbsp;';
							$elipsis_printed = true;
						}

					} elseif ($entry['page'] == $page_num) {
						$pager_html .= '<b>' . ($page_num + 1) . '</b>';
						$pager_html .= '&nbsp;&nbsp;';
						$elipsis_printed = false;

					} else {
						$query_string = array_to_query_string($_GET,array('page','cmd'));
						$query_string .= '&page=' . $page_num;
						$pager_html .= "<a href=\"cmd.php?cmd=search&amp;$query_string\">" . ($page_num+1) . "</a>";
						$pager_html .= '&nbsp;&nbsp;';
						$elipsis_printed = false;
					}
				}

				if ($entry['page']+1 < $total_pages) {
					$query_string = array_to_query_string($_GET,array('page','cmd'));
					$query_string .= '&page=' . ($entry['page']+1);
					$pager_html .= "<a title=\"" . sprintf(_('Page %d'),($entry['page']+2))."\" href=\"cmd.php?cmd=search&amp;$query_string\">&rsaquo;&rsaquo;</a>";

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

				if ($entry['format'] == 'list')
					require LIBDIR.'search_results_list.php';
				elseif ($entry['format'] == 'table')
					require LIBDIR.'search_results_table.php';
				else
					pla_error(sprintf(_('Unrecognized search result format: %s'),htmlspecialchars($entry['format'])));

				echo '<br />';
				if (trim($pager_html))
					printf('<center>%s</center>',$pager_html);
			}
		}

		printf('<br /><br /><div class="search_result"><center><small><span style="font-weight:normal;font-size:75%%;">%s <b>%s</b> %s.</span></small></center></div>',
			_('Search performed by phpLDAPadmin in'),$time_elapsed,_('seconds'));

	}
}
?>
