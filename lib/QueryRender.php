<?php
/**
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * QueryRender class
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 * @todo need to add paging
 */
class QueryRender extends PageRender {
	/** CORE FUNCTIONS **/

	/**
	 * Intialise and Render the QueryRender
	 */
	public function accept() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);
		if (DEBUGTMP||DEBUGTMPSUB) printf('<font size=-2>* %s [GETquery:%s]</font><br />',__METHOD__,get_request('query','REQUEST'));
		if (DEBUGTMP||DEBUGTMPSUB) printf('<font size=-2>* %s [Page:%s]</font><br />',__METHOD__,get_request('page','REQUEST'));

		$this->template_id = $this->getTemplateChoice();
		$this->page = get_request('page','REQUEST',false,1);

		# If we are the default template, make sure we pressed search
		if ($this->template_id == 'none' && ! get_request('search','REQUEST'))
			$this->drawTemplateChoice();

		elseif ($this->template_id) {
			$templates = $this->getTemplates();
			$this->template = $templates->getTemplate($this->template_id);
			$this->template->accept();

			$this->visitStart();
			$this->visitEnd();
		}
	}

	/**
	 * Get our templates applicable for this object
	 */
	protected function getTemplates() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return new Queries($this->server_id);
	}

	/**
	 * Are default queries enabled?
	 */
	protected function haveDefaultTemplate() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$server = $this->getServer();

		if ($server->getValue('query','disable_default'))
			return false;
		else
			return true;
	}

	protected function drawTemplateChoice() {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$server = $this->getServer();

		$this->drawTitle(_('Search'));
		$this->drawSubTitle();

		echo "\n";

		$baseDNs = $server->getBaseDN();

		printf('<script type="text/javascript" src="%sdnChooserPopup.js"></script>',JSDIR);
		echo '<form action="cmd.php" id="advanced_search_form">';
		echo '<div>';
		echo '<input type="hidden" name="cmd" value="query_engine" />';
		printf('<input type="hidden" name="server_id" value="%s" />',$server->getIndex());
		echo '</div>';

		echo '<table class="forminput" border="0" style="margin-left: auto; margin-right: auto;">';
		echo '<tr><td colspan="2">&nbsp;</td></tr>';

		$templates = $this->getTemplates();

		if (count($templates->getTemplates())) {
			echo '<tr>';
			printf('<td><acronym title="%s">%s</acronym></td>',_('Run a predefined query'),_('Predefined Query'));
			echo '<td>';
			echo '<select name="query">';
			if ($this->haveDefaultTemplate())
				printf('<option value="%s" %s>%s</option>','none','',_('Custom Query'));

			foreach ($templates->getTemplates() as $template)
				printf('<option value="%s" %s>%s</option>',
					$template->getID(),
					($this->template_id == $template->getID() ? 'selected="selected"' : ''),
					$template->getDescription());
			echo '</select>';
			echo '</td>';
			echo '</tr>';
		}

		echo '<tr>';
		printf('<td><acronym title="%s">%s</acronym></td>',_('The format to show the query results'),_('Display Format'));
		echo '<td>';
		echo '<select name="format" style="width: 200px">';

		printf('<option value="list" %s>%s</option>',
			$_SESSION[APPCONFIG]->getValue('search','display') == 'list' ? 'selected="selected"' : '',_('list'));
		printf('<option value="table" %s>%s</option>',
			$_SESSION[APPCONFIG]->getValue('search','display') == 'table' ? 'selected="selected"' : '',_('table'));

		echo '</select>';
		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		printf('<td><acronym title="%s">%s</acronym></td>',_('Entries to show per page'),_('Show Results'));
		echo '<td>';
		echo '<select name="showresults" style="width: 200px">';

		printf('<option value="na" %s>%s</option>',
			'','NA');

		echo '</select>';
		echo '</td>';
		echo '</tr>';

		echo '<tr>';

		echo '<td colspan="2">';
		printf('<div id="customquery" style="display: %s">','block');
		echo '<br/>';
		echo '<fieldset>';
		printf('<legend>%s</legend>',_('Custom Query'));
		echo '<table border="0"><tr>';

		printf('<td>%s</td>',_('Base DN'));
		printf('<td><input type="text" name="base" value="%s" style="width: 200px" id="base" />',count($baseDNs) == 1 ? $baseDNs[0] : '');

		draw_chooser_link('advanced_search_form','base');

		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		printf('<td><acronym title="%s">%s</acronym></td>',_('The scope in which to search'),_('Search Scope'));

		echo '<td>';
		echo '<select name="scope" style="width: 200px">';

		printf('<option value="sub" %s>%s</option>',
			'',_('Sub (entire subtree)'));

		printf('<option value="one" %s>%s</option>',
			'',_('One (one level beneath base)'));

		printf('<option value="base" %s>%s</option>',
			'',_('Base (base dn only)'));

		echo '</select>';
		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		printf('<td><acronym title="%s">%s</acronym></td>',
			htmlspecialchars(_('Standard LDAP search filter. Example: (&(sn=Smith)(givenName=David))')),_('Search Filter'));

		printf('<td><input type="text" name="filter" id="filter" style="width: 200px" value="%s" /></td>',
			'objectClass=*');

		echo '</tr>';

		echo '<tr>';
		printf('<td><acronym title="%s">%s</acronym></td>',
			_('A list of attributes to display in the results (comma-separated)'),_('Show Attributes'));

		printf('<td><input type="text" name="display_attrs" style="width: 200px" value="%s" /></td>',
			implode(', ',$_SESSION[APPCONFIG]->getValue('search','result_attributes')));
		echo '</tr>';

		echo '<tr>';
		printf('<td><acronym title="%s">%s</acronym></td>',_('Order by'),_('Order by'));
		printf('<td><input type="text" name="orderby" id="orderby" style="width: 200px" value="%s" /></td>','');
		echo '</tr>';

		echo '<tr>';
		printf('<td><acronym title="%s">%s</acronym></td>',_('Set the search results to 0 to retrieve all available records'),_('Search Results'));
		printf('<td><input type="text" name="size_limit" id="size_limit" style="width: 200px" value="%s" /></td>',$_SESSION[APPCONFIG]->getValue('search','size_limit'));
		echo '</tr>';

		echo '</table>';
		echo '</fieldset>';
		echo '</div>';
		echo '</td>';
		echo '</tr>';

		printf('<tr><td colspan="2" style="text-align: center;"><br /><input type="submit" name="search" value="%s" /></td></tr>',_('Search'));

		echo '</table>';
		echo '</form>';
	}

	private function visitStart() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->drawTitle(_('Search Results'));
		$this->drawSubTitle();
		echo '<br/>';
	}

	private function visitEnd() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$server = $this->getServer();
		$afattrs = $this->getAFAttrs();

		# If Mass Actions Enabled
		if ($_SESSION[APPCONFIG]->getValue('mass','enabled')) {
			$mass_actions = array(
				_('delete') => 'mass_delete',
				_('edit') => 'mass_edit'
			);
		}

		$this->drawBaseTabs();
		$ado = $this->template->getAttrDisplayOrder();
		$counter = 0;
		$j = 0;

		foreach ($this->template->results as $base => $results) {
			$counter++;

			if (! $show = get_request('show','REQUEST'))
				$show = ($counter === 1 ? $this->getAjaxRef($base) : null);

			printf('<div id="DN%s" style="display: %s">',
				$this->getAjaxRef($base), ($show == $this->getAjaxRef($base) ? 'block' : 'none'));

			echo '<table class="result_box" border="0" width="100%">';
			echo '<tr><td>';
			echo '<br/>';
			echo '<br/>';

			$this->drawResultsTable($base,count($results));

			echo '<br/>';
			echo '<br/>';

			switch(get_request('format','REQUEST',false,$_SESSION[APPCONFIG]->getValue('search','display'))) {
				case 'list':
					foreach ($results as $dndetails) {
						$dndetails = array_change_key_case($dndetails);

						# Temporarily set our DN, for rendering that leverages our DN (eg: JpegPhoto)
						$this->template->setDN($dndetails['dn']);

						echo '<table class="result" border="0">';

						echo '<tr class="list_title">';
						printf('<td class="icon"><img src="%s/%s" alt="icon" /></td>',IMGDIR,get_icon($server->getIndex(),$dndetails['dn']));

						printf('<td colspan="2"><a href="cmd.php?cmd=template_engine&amp;server_id=%s&amp;dn=%s">%s</a></td>',
							$server->getIndex(),$this->template->getDNEncode(),htmlspecialchars(get_rdn($dndetails['dn'])));
						echo '</tr>';

						printf('<tr class="list_item"><td class="blank">&nbsp;</td><td class="heading">dn</td><td class="value">%s</td></tr>',
							htmlspecialchars(dn_unescape($dndetails['dn'])));

						# Iterate over each attribute for this entry
						foreach (explode(',',$ado) as $attr) {
							$attr = strtolower($attr);

							# Ignore DN, we've already displayed it.
							if ($attr == 'dn')
								continue;

							if (! isset($dndetails[$attr]))
								continue;

							# Set our object with our values
							$afattrs[$attr]->clearValue();

							if (is_array($dndetails[$attr]))
								$afattrs[$attr]->initValue($dndetails[$attr]);
							else
								$afattrs[$attr]->initValue(array($dndetails[$attr]));

							echo '<tr class="list_item">';
							echo '<td class="blank">&nbsp;</td>';

							echo '<td class="heading">';
							$this->draw('Name',$afattrs[$attr]);
							echo '</td>';

							echo '<td>';
							$this->draw('CurrentValues',$afattrs[$attr]);
							echo '</td>';
							echo '</tr>';
						}

						echo '</table>';
						echo '<br/>';
					}

					break;

				# Display the results.
				case 'table':
					if (! $results) {
						echo _('Search returned no results');

						continue;
					}

					printf('<form action="cmd.php" method="post" id="massform_%s">',$counter);
					echo '<div>';
					printf('<input type="hidden" name="server_id" value="%s" />',$server->getIndex());

					foreach ($this->template->resultsdata[$base]['attrs'] as $attr)
						printf('<input type="hidden" name="attrs[]" value="%s" />',$attr);

					echo '</div>';

					echo '<table class="result_table" border="0">';

					echo '<thead class="fixheader">';
					echo '<tr class="heading">';
					echo '<td>&nbsp;</td>';
					echo '<td>&nbsp;</td>';

					foreach (explode(',',$ado) as $attr) {
						echo '<td>';
						$this->draw('Name',$afattrs[$attr]);
						echo '</td>';
					}

					echo '</tr>';
					echo '</thead>';

					echo '<tbody class="scroll">';
					foreach ($results as $dndetails) {
						$j++;
						$dndetails = array_change_key_case($dndetails);

						# Temporarily set our DN, for rendering that leverages our DN (eg: JpegPhoto)
						$this->template->setDN($dndetails['dn']);

						printf('<tr class="%s" id="tr_ma_%s" onclick="var cb=document.getElementById(\'ma_%s\'); cb.checked=!cb.checked;">',
							$j%2 ? 'even' : 'odd',$j,$j);

						# Is mass action enabled.
						if ($_SESSION[APPCONFIG]->getValue('mass','enabled'))
							printf('<td><input type="checkbox" id="ma_%s" name="dn[]" value="%s" onclick="this.checked=!this.checked;" /></td>',$j,$dndetails['dn']);

						$href = sprintf('cmd=template_engine&server_id=%s&dn=%s',$server->getIndex(),$this->template->getDNEncode());
						printf('<td class="icon"><a href="cmd.php?%s"><img src="%s/%s" alt="icon" /></a></td>',
							htmlspecialchars($href),
							IMGDIR,get_icon($server->getIndex(),$dndetails['dn']));

						# We'll clone our attribute factory attributes, since we need to add the values to them for rendering.
						foreach (explode(',',$ado) as $attr) {
							# If the entry is blank, we'll draw an empty box and continue.
							if (! isset($dndetails[$attr])) {
								echo '<td>&nbsp;</td>';
								continue;
							}

							# Special case for DNs
							if ($attr == 'dn') {
								$dn_display = strlen($dndetails['dn']) > 40
									? sprintf('<acronym title="%s">%s...</acronym>',htmlspecialchars($dndetails['dn']),htmlspecialchars(substr($dndetails['dn'],0,40)))
									: htmlspecialchars($dndetails['dn']);

								printf('<td><a href="cmd.php?%s">%s</a></td>',htmlspecialchars($href),$dn_display);
								continue;
							}

							# Set our object with our values
							$afattrs[$attr]->clearValue();
							if (is_array($dndetails[$attr]))
								$afattrs[$attr]->initValue($dndetails[$attr]);
							else
								$afattrs[$attr]->initValue(array($dndetails[$attr]));

							echo '<td>';
							$this->draw('CurrentValues',$afattrs[$attr]);
							echo '</td>';
						}

						echo '</tr>';
					}

					# Is mass action enabled.
					if ($_SESSION[APPCONFIG]->getValue('mass','enabled')) {
						printf('<tr class="%s">',++$j%2 ? 'odd' : 'even');
						printf('<td><input type="checkbox" name="allbox" value="1" onclick="CheckAll(1,\'massform_\',%s);" /></td>',$counter);
						printf('<td colspan="%s">',2+count(explode(',',$ado)));

						foreach ($mass_actions as $display => $action)
							printf('<button type="submit" name="cmd" value="%s">%s</button>&nbsp;&nbsp;',$action,$display);

						echo '</td>';
						echo '</tr>';
					}

					echo '</tbody>';
					echo '</table>';
					echo '</form>';
					echo "\n\n";

					break;

				default:
					printf('Have ID [%s], run this query for page [%s]',$this->template_id,$this->page);
			}

			echo '</td></tr>';
			echo '</table>';
			echo '</div>';
			echo "\n\n";
		}

		if (get_request('format','REQUEST',false,'table') == 'table')
			printf('<script type="text/javascript" src="%sCheckAll.js"></script>',JSDIR);
	}

	public function drawSubTitle($subtitle=null) {
		if (is_null($subtitle)) {
			$server = $this->getServer();
	
			$subtitle = sprintf('%s: <b>%s</b>',_('Server'),$server->getName());

			if ($this->template) {
				$subtitle .= '<br />';
				$subtitle .= sprintf('%s: <b>%s</b>',('Query'),$this->template->getID() != 'none' ? $this->template->getTitle() : _('Default'));
				if ($this->template->getName())
					$subtitle .= sprintf(' (<b>%s</b>)',$this->template->getName(false));
			}
		}

		parent::drawSubTitle($subtitle);
	}

	private function getAFattrs() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attribute_factory = new AttributeFactory();
		$results = array();

		foreach (explode(',',$this->template->getAttrDisplayOrder()) as $attr)
			$results[strtolower($attr)] = $attribute_factory->newAttribute($attr,array('values'=>array()),$this->getServerID());

		return $results;
	}

	private function getAjaxRef($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return preg_replace('/=/','.',base64_encode($dn));
	}

	private function drawBaseTabs() {
		# Setup the Javascript to show/hide our DIVs.
		echo '<script type="text/javascript">';
		echo 'function items() {';
		echo 'var $items = new Array();';
		$counter = 0;
		foreach ($this->template->results as $base => $results)
			printf("items[%s] = '%s';",$counter++,$this->getAjaxRef($base));
		echo 'return items;';
		echo '}</script>';
		echo "\n\n";

		echo '<table class="result_table" border="0">';
		echo '<tr>';
		$counter = 0;
		foreach ($this->template->results as $base => $results) {
			if (! $show = get_request('show','REQUEST'))
				$show = ($counter++ === 0 ? $this->getAjaxRef($base) : null);

			printf('<td id="CTL%s" onclick="return ajSHOWTHIS(\'DN\',\'%s\',\'CTL\');" style="background-color: %s;">%s</td>',
				$this->getAjaxRef($base),
				$this->getAjaxRef($base),
				($show == $this->getAjaxRef($base) ? '#F0F0F0' : '#E0E0E0'),
				htmlspecialchars($base));
		}
		echo '</tr>';
		echo '</table>';
		echo "\n\n";
	}

	private function drawResultsTable($base,$results) {
		$server = $this->getServer();

		echo '<table class="result" border="0">';

		echo '<tr>';
		printf('<td>%s: <b>%s</b><br/><br/><div class="execution_time">(%s %s)</div></td>',_('Entries found'),
			number_format($results),$this->template->resultsdata[$base]['time'],_('seconds'));

		if ($_SESSION[APPCONFIG]->isCommandAvailable('script','export') && $_SESSION[APPCONFIG]->isCommandAvailable('script','export_form')) {
			$href = htmlspecialchars(sprintf('cmd.php?cmd=export_form&server_id=%s&scope=%s&dn=%s&filter=%s&attributes=%s',
				$server->getIndex(),$this->template->resultsdata[$base]['scope'],
				$base,rawurlencode($this->template->resultsdata[$base]['filter']),
				rawurlencode(implode(', ',$this->template->resultsdata[$base]['attrs']))));

			printf('<td style="text-align: right; width: 85%%"><small>[ <a href="%s"><img src="%s/save.png" alt="Save" /> %s</a> ]</small>',
				$href,IMGDIR,_('export results'));
		}

		printf('<small>[ <img src="%s/rename.png" alt="rename" /> %s:',IMGDIR,_('Format'));

		foreach (array('list','table') as $f) {
			echo '&nbsp;';

			if (get_request('format','REQUEST',false,$_SESSION[APPCONFIG]->getValue('search','display')) == $f) {
				printf('<b>%s</b>',_($f));

			} else {
				$query_string = htmlspecialchars(sprintf('%s&format=%s&show=%s&focusbase=%s',array_to_query_string($_GET,array('format','meth')),$f,$this->getAjaxRef($base),$base));

				if (isAjaxEnabled())
					printf('<a href="cmd.php?%s" onclick="return ajDISPLAY(\'BODY\',\'%s\',\'%s\');">%s</a>',
						$query_string,$query_string,_('Loading Search'),_($f));
				else
					printf('<a href="cmd.php?%s">%s</a>',$query_string,_($f));
			}
		}

		echo ' ]</small>';

		echo '<br />';
		printf('<small>%s: <b>%s</b></small>',_('Base DN'),htmlspecialchars($base));

		echo '<br />';
		printf('<small>%s: <b>%s</b></small>',_('Filter performed'),htmlspecialchars($this->template->resultsdata[$base]['filter']));

		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}
}
?>
