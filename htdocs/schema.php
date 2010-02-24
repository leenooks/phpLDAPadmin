<?php
/**
 * Displays the schema for the specified server
 *
 * Variables that come in as GET vars:
 * - view (optional)
 *   Shows attribute, objectclass or matching rule
 * - viewvalue (optional)
 *   Shows the attribute, objectclass or matching rule
 * - highlight_oid (optional)
 *   Use to higlight the oid in the syntaxes view.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

$entry = array();
$entry['view'] = get_request('view','GET','false','objectclasses');
$entry['value'] = get_request('viewvalue','GET');

if (! is_null($entry['value'])) {
	$entry['viewed'] = false;
	$entry['value'] = strtolower($entry['value']);
}

$schema_error_str = sprintf('%s <b>%s</b>.<br /><br /></div>%s<ul><li>%s</li><li>%s</li><li>%s</li><li>%s</li></ul>',
	_('Could not retrieve schema from'),$app['server']->getName(),
	_('This could happen for several reasons, the most probable of which are:'),_('The server does not fully support the LDAP protocol.'),
	_('Your version of PHP does not correctly perform the query.'),_('phpLDAPadmin doesn\'t know how to fetch the schema for your server.'),
	_('Or lastly, your LDAP server doesnt provide this information.'));

printf('<h3 class="title">%s <b>%s</b></h3>',_('Schema for server'),$app['server']->getName());

$entry['schema_types'] = array(
	'objectclasses'=>_('ObjectClasses'),
	'attributes'=>_('Attribute Types'),
	'syntaxes'=>_('Syntaxes'),
	'matching_rules'=>_('Matching Rules'));

echo '<br />';
echo '<div style="text-align: center;">';

$counter = 0;
foreach ($entry['schema_types'] as $item => $value) {
	if ($counter++)
		echo ' | ';

	$entry['href'][$item] = sprintf('cmd=schema&server_id=%s&view=%s',$app['server']->getIndex(),$item);

	if ($entry['view'] == $item) {
		echo _($value);

	} else {
		if (isAjaxEnabled())
			printf('<a href="cmd.php?%s" onclick="return ajDISPLAY(\'BODY\',\'%s\',\'Loading %s\');" title="Loading %s">%s</a>',
				htmlspecialchars($entry['href'][$item]),htmlspecialchars($entry['href'][$item]),$value,$value,$value);
		else
			printf('<a href="cmd.php?%s">%s</a>',htmlspecialchars($entry['href'][$item]),_($value));
	}
}

echo '</div>';
echo '<br />';

switch($entry['view']) {
	case 'syntaxes':
		$highlight_oid = get_request('highlight_oid','GET',false,false);

		echo '<table class="result_table" border="0" style="margin-left: auto; margin-right: auto;">';
		printf('<tr class="heading"><td>%s</td><td>%s</td></tr>',_('Syntax OID'),_('Description'));

		$counter = 1;

		$schema_syntaxes = $app['server']->SchemaSyntaxes();
		if (! $schema_syntaxes)
			error($schema_error_str,'error','index.php');

		foreach ($schema_syntaxes as $syntax) {
			$counter++;
			$oid = $syntax->getOID();
			$desc = $syntax->getDescription();

			if ($highlight_oid && $highlight_oid == $oid)
				echo '<tr class="highlight">';

			else
				printf('<tr class="%s">',$counter%2==0?'even':'odd');

			printf('<td>%s</td><td>%s</td></tr>',$oid,$desc);
		}

		echo '</table>';
		break;

	case 'attributes':
		$entry['attr_types'] = array(
			'desc' => _('Description'),
			'obsolete' => _('Obsolete'),
			'inherits' => _('Inherits from'),
			'equality' => _('Equality'),
			'ordering' => _('Ordering'),
			'substring_rule' => _('Substring Rule'),
			'syntax' => _('Syntax'),
			'single_valued' => _('Single Valued'),
			'collective' => _('Collective'),
			'user_modification' => _('User Modification'),
			'usage' => _('Usage'),
			'maximum_length' => _('Maximum Length'),
			'aliases' => _('Aliases'),
			'used_by_objectclasses' => _('Used by objectClasses'),
			'force_as_may' => _('Force as MAY by config')
		);

		$sattrs = $app['server']->SchemaAttributes();

		if (! $sattrs || ! $app['server']->SchemaObjectClasses())
			error($schema_error_str,'error','index.php');

		printf('<small>%s:</small>',_('Jump to an attribute type'));
		echo '<form action="cmd.php" method="get">';
		echo '<div>';
		echo '<input type="hidden" name="cmd" value="schema" />';
		printf('<input type="hidden" name="view" value="%s" />',$entry['view']);
		printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());

		if (isAjaxEnabled()) {
			drawJSItems($sattrs);
			echo '<select name="viewvalue" onchange="ajSHOWSCHEMA(\'attributes\',\'at\')" id="attributes">';
		} else
			echo '<select name="viewvalue" onchange="submit()">';

		echo '<option value=""> - all -</option>';
		foreach ($sattrs as $name => $attr)
			printf('<option value="%s" %s>%s</option>',
				$name,$name == $entry['value'] ? 'selected="selected" ': '',$attr->getName(false));
		echo '</select>';

		if (isAjaxEnabled())
			printf('<input type="button" value="%s" onclick="ajSHOWSCHEMA(\'attributes\',\'at\')"/>',_('Go'));
		else
			printf('<input type="submit" value="%s" />',_('Go'));
		echo '</div>';
		echo '</form>';
		echo '<br />';

		foreach ($sattrs as $attr) {
			if (isAjaxEnabled() || (is_null($entry['value']) || ! trim($entry['value']) || $entry['value']==$attr->getName())) {
				if ((! is_null($entry['value']) && $entry['value']==$attr->getName()) || ! trim($entry['value']))
					$entry['viewed'] = true;

				if (isAjaxEnabled() && $entry['value'])
					printf('<div id="at%s" style="display: %s">',$attr->getName(),strcasecmp($entry['value'],$attr->getName()) ? 'none' : 'block');
				else
					printf('<div id="at%s">',$attr->getName());

				echo '<table class="result_table" width="100%" border="0">';
				printf('<tr class="heading"><td colspan="2"><a name="%s">%s</a></td></tr>',
					$attr->getName(),$attr->getName(false));

				$counter = 0;

				foreach ($entry['attr_types'] as $item => $value) {

					printf('<tr class="%s">',++$counter%2 ? 'odd' : 'even');
					printf('<td class="title" style="width: 30%%;">%s</td>',$value);

					switch ($item) {
						case 'desc':
							printf('<td>%s</td>',
								is_null($attr->getDescription()) ?
									'('._('no description').')' : $attr->getDescription());

							echo '</tr>';
							printf('<tr class="%s">',++$counter%2 ? 'odd' : 'even');
							echo '<td class="title"><acronym title="Object Identier">OID</acronym></td>';
							printf('<td>%s</td>',$attr->getOID());

							break;

						case 'obsolete':
							printf('<td>%s</td>',$attr->getIsObsolete() ? '<b>'._('Yes').'</b>' : _('No'));
							break;

						case 'inherits':
							echo '<td>';

							if (is_null($attr->getSupAttribute()))
								printf('(%s)',_('none'));

							else {
								$href = htmlspecialchars(sprintf('%s&viewvalue=%s',$entry['href']['attributes'],strtolower($attr->getSupAttribute())));
								if (isAjaxEnabled())
									printf('<a href="cmd.php?%s" onclick="return ajSHOWSCHEMA(\'attributes\',\'at\',\'%s\');">%s</a>',
										$href,strtolower($attr->getSupAttribute()),$attr->getSupAttribute());
								else
									printf('<a href="cmd.php?%s">%s</a>',$href,$attr->getSupAttribute());
							}

							echo '</td>';
							break;

						case 'equality':
							echo '<td>';

							if (is_null($attr->getEquality()))
								printf('(%s)',_('not specified'));

							else {
								$href = htmlspecialchars(sprintf('%s&viewvalue=%s',$entry['href']['matching_rules'],$attr->getEquality()));
								if (isAjaxEnabled())
									printf('<a href="cmd.php?%s" onclick="return ajJUMP(\'%s\',\'%s\',\'%s\');">%s</a>',
										$href,$href,_('Matching Rules'),$attr->getEquality(),$attr->getEquality());
								else
									printf('<a href="cmd.php?%s">%s</a>',$href,$attr->getEquality());
							}

							echo '</td>';
							break;

						case 'ordering':
							printf('<td>%s</td>',
								is_null($attr->getOrdering()) ? '('._('not specified').')' : $attr->getOrdering());
							break;

						case 'substring_rule':
							printf('<td>%s</td>',
								is_null($attr->getSubstr()) ? '('._('not specified').')' : $attr->getSubstr());
							break;

						case 'syntax':
							echo '<td>';

							if (is_null($attr->getType())) {
								echo $attr->getSyntaxOID();

							} else {
								$href = htmlspecialchars(sprintf('%s&highlight_oid=%s',$entry['href']['syntaxes'],$attr->getSyntaxOID()));
								if (isAjaxEnabled())
									printf('<a href="cmd.php?%s" onclick="return ajJUMP(\'%s\',\'%s\',\'%s\');">%s (%s)</a>',
										$href,$href,_('Syntaxes'),'',$attr->getType(),$attr->getSyntaxOID());
								else
									printf('<a href="cmd.php?%s">%s (%s)</a>',$href,$attr->getType(),$attr->getSyntaxOID());
							}

							echo '</td>';
							break;

						case 'single_valued':
							printf('<td>%s</td>',$attr->getIsSingleValue() ? _('Yes') : _('No'));
							break;

						case 'collective':
							printf('<td>%s</td>',$attr->getIsCollective() ? _('Yes') : _('No'));
							break;

						case 'user_modification':
							printf('<td>%s</td>',$attr->getIsNoUserModification() ? _('No') : _('Yes'));
							break;

						case 'usage':
							printf('<td>%s</td>',$attr->getUsage() ? $attr->getUsage() : '('._('not specified').')');
							break;

						case 'maximum_length':
							echo '<td>';

							if ( is_null($attr->getMaxLength()))
								echo '('._('not applicable').')';

							else
								printf('%s %s',number_format($attr->getMaxLength()),
									$attr->getMaxLength()>1 ? _('characters') : _('character'));

							echo '</td>';
							break;

						case 'aliases':
							echo '<td>';

							if (count($attr->getAliases()) == 0)
								printf('(%s)',_('none'));

							else
								foreach ($attr->getAliases() as $alias) {
									$href = htmlspecialchars(sprintf('%s&viewvalue=%s',$entry['href']['attributes'],strtolower($alias)));
									if (isAjaxEnabled())
										printf('<a href="cmd.php?%s" onclick="return ajSHOWSCHEMA(\'attributes\',\'at\',\'%s\');">%s</a>',
											$href,strtolower($alias),$alias);
									else
										printf('<a href="cmd.php?%s">%s</a>',$href,$alias);
								}

							echo '</td>';
							break;

						case 'used_by_objectclasses':
							echo '<td>';

							if (count($attr->getUsedInObjectClasses()) == 0)
								printf('(%s)',_('none'));

							else
								foreach ($attr->getUsedInObjectClasses() as $objectclass) {
									$href = htmlspecialchars(sprintf('%s&viewvalue=%s',$entry['href']['objectclasses'],strtolower($objectclass)));
									if (isAjaxEnabled())
										printf('<a href="cmd.php?%s" onclick="return ajJUMP(\'%s\',\'%s\',\'%s\');">%s</a> ',
											$href,$href,_('ObjectClasses'),strtolower($objectclass),$objectclass);
									else
										printf('<a href="cmd.php?%s">%s</a> ',$href,$objectclass);
								}

							echo '</td>';
							break;

						case 'force_as_may':
							printf('<td>%s</td>',$attr->isForceMay() ? _('Yes') : _('No'));
							break;

					}
					echo '</tr>';
				}
				echo '</table>';
				echo '<br />';
				echo '</div>';
			}
		}

		break;

	case 'matching_rules':
		$schema_matching_rules = $app['server']->MatchingRules();
		if (! $schema_matching_rules)
			error($schema_error_str,'error','index.php');

		printf('<small>%s</small><br />',_('Jump to a matching rule'));

		echo '<form action="cmd.php" method="get">';
		echo '<div>';
		echo '<input type="hidden" name="cmd" value="schema" />';
		printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
		echo '<input type="hidden" name="view" value="matching_rules" />';

		if (isAjaxEnabled()) {
			drawJSItems($schema_matching_rules);
			echo '<select name="viewvalue" onchange="ajSHOWSCHEMA(\'matchingrules\',\'mr\')" id="matchingrules">';
		} else
			echo '<select name="viewvalue" onchange="submit()">';

		echo '<option value=""> - all -</option>';
		foreach ($schema_matching_rules as $rule)
			printf('<option value="%s" %s>%s</option>',
				$rule->getName(),
				($rule->getName() == $entry['value'] ? 'selected="selected"': ''),
				$rule->getName(false));

		echo '</select>';

		if (isAjaxEnabled())
			printf('<input type="button" value="%s" onclick="ajSHOWSCHEMA(\'matchingrules\',\'mr\')"/>',_('Go'));
		else
			printf('<input type="submit" value="%s" />',_('Go'));
		echo '</div>';
		echo '</form>';
		echo '<br />';

		echo '<table class="result_table" width="100%" border="0">';
		printf('<tr class="heading"><td>%s</td><td>%s</td><td>%s</td></tr>',
			_('Matching Rule OID'),_('Name'),_('Used by Attributes'));

		$counter = 1;

		foreach ($schema_matching_rules as $rule) {
			$counter++;
			$oid = $rule->getOID();
			$desc = $rule->getName(false);

			if (isAjaxEnabled() || (is_null($entry['value']) || ! trim($entry['value']) || $entry['value']==$rule->getName())) {
				if ((! is_null($entry['value']) && $entry['value']==$rule->getName()) || ! trim($entry['value']))
					$entry['viewed'] = true;

				if (null != $rule->getDescription())
					$desc .= sprintf(' (%s)',$rule->getDescription());

				if ( $rule->getIsObsolete())
					$desc .= sprintf(' <span style="color:red">%s</span>',_('Obsolete'));

				if (isAjaxEnabled() && $entry['value'])
					printf('<tr class="%s" id="mr%s" style="display: %s">',$counter%2 ? 'odd' : 'even',$rule->getName(),
						strcasecmp($entry['value'],$rule->getName()) ? 'none' : '');
				else
					printf('<tr class="%s" id="mr%s">',$counter%2 ? 'odd' : 'even',$rule->getName());
				printf('<td>%s</td>',$oid);
				printf('<td>%s</td>',$desc);

				echo '<td>';

				if (count($rule->getUsedByAttrs()) == 0) {
					printf('<div style="text-align: center;">(%s)</div><br /><br />',_('none'));

				} else {
					echo '<table width="100%" border="0"><tr><td>';
					echo '<form action="cmd.php" method="get">';
					echo '<div>';
					echo '<input type="hidden" name="cmd" value="schema" />';
					printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
					echo '<input type="hidden" name="view" value="attributes" />';

					printf('<select size="4" name="viewvalue" id="vv%s">',$rule->getName());
					foreach ($rule->getUsedByAttrs() as $attr)
						printf('<option>%s</option>',$attr);
					echo '</select><br />';

					if (isAjaxEnabled())
						printf('<input type="button" value="%s" onclick="return ajJUMP(\'cmd=schema&amp;view=attributes&amp;server_id=%s\',\'%s\',\'%s\',\'vv\');"/>',
							_('Go'),$app['server']->getIndex(),_('Attributes'),$rule->getName());
					else
						printf('<input type="submit" value="%s" />',_('Go'));
					echo '</div>';
					echo '</form>';
					echo '</td></tr></table>';
				}
				echo '</td>';
				echo '</tr>';
			}
		}

		echo '</table>';
		break;

	case 'objectclasses':
		$socs = $app['server']->SchemaObjectClasses();
		if (! $socs)
			error($schema_error_str,'error','index.php');

		printf('<small>%s:</small>',_('Jump to an objectClass'));

		echo '<form action="cmd.php" method="get">';
		echo '<div>';
		echo '<input type="hidden" name="cmd" value="schema" />';
		printf('<input type="hidden" name="view" value="%s" />',$entry['view']);
		printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());

		if (isAjaxEnabled()) {
			drawJSItems($socs);
			echo '<select name="viewvalue" onchange="ajSHOWSCHEMA(\'objectclasses\',\'oc\')" id="objectclasses">';
		} else
			echo '<select name="viewvalue" onchange="submit()">';

		echo '<option value=""> - all - </option>';
		foreach ($socs as $name => $oclass)
			printf('<option value="%s" %s>%s</option>',
				$name,$name == $entry['value'] ? 'selected="selected" ': '',$oclass->getName(false));

		echo '</select>';

		if (isAjaxEnabled())
			printf('<input type="button" value="%s" onclick="ajSHOWSCHEMA(\'objectclasses\',\'oc\')"/>',_('Go'));
		else
			printf('<input type="submit" value="%s" />',_('Go'));
		echo '</div>';
		echo '</form>';
		echo '<br />';

		foreach ($socs as $name => $oclass) {
			if (isAjaxEnabled() || (is_null($entry['value']) || ! trim($entry['value']) || $entry['value']==$oclass->getName())) {
				if ((! is_null($entry['value']) && $entry['value']==$oclass->getName()) || ! trim($entry['value']))
					$entry['viewed'] = true;

				if (isAjaxEnabled() && $entry['value'])
					printf('<div id="oc%s" style="display: %s">',$oclass->getName(),strcasecmp($entry['value'],$oclass->getName()) ? 'none' : '');
				else
					printf('<div id="oc%s">',$oclass->getName());

				echo '<table class="result_table" width="100%" border="0">';
				printf('<tr class="heading"><td colspan="4"><a name="%s">%s</a></td></tr>',$name,$oclass->getName(false));
				printf('<tr class="odd"><td colspan="4">%s: <b>%s</b></td></tr>',_('OID'),$oclass->getOID());

				if ($oclass->getDescription())
					printf('<tr class="odd"><td colspan="4">%s: <b>%s</b></td></tr>',_('Description'),$oclass->getDescription());

				printf('<tr class="odd"><td colspan="4">%s: <b>%s</b></td></tr>',_('Type'),$oclass->getType());

				if ($oclass->getIsObsolete())
					printf('<tr class="odd"><td colspan="4">%s</td></tr>',_('This objectClass is obsolete.'));

				printf('<tr class="odd"><td colspan="4">%s: <b>',_('Inherits from'));
				if (count($oclass->getSupClasses()) == 0)
					printf('(%s)',_('none'));

				else
					foreach ($oclass->getSupClasses() as $i => $object_class) {
						$href = htmlspecialchars(sprintf('%s&viewvalue=%s',$entry['href']['objectclasses'],strtolower($object_class)));
						if (isAjaxEnabled())
							printf('<a href="cmd.php?%s" onclick="return ajSHOWSCHEMA(\'objectclasses\',\'oc\',\'%s\');">%s</a>',
								$href,strtolower($object_class),$object_class);
						else
							printf('<a href="cmd.php?%s&viewvalue=%s" title="%s">%s</a>',
								$href,$object_class,_('Jump to this objectClass definition'),$object_class);

						if ($i < count($oclass->getSupClasses()) - 1)
							echo ', ';
					}
				echo '</b></td></tr>';

				printf('<tr class="odd"><td colspan="4">%s: <b>',_('Parent to'));
				if (strcasecmp($oclass->getName(),'top') == 0) {
					$href = htmlspecialchars($entry['href']['objectclasses']);
					if (isAjaxEnabled())
						printf('<a href="cmd.php?%s" onclick="return ajSHOWSCHEMA(\'objectclasses\',\'oc\',\'\');">all</a>',
							$href);
					else
						printf('(<a href="cmd.php?%s">all</a>)',$href);

				} elseif (count($oclass->getChildObjectClasses()) == 0)
					printf('(%s)',_('none'));

				else
					foreach ($oclass->getChildObjectClasses() as $i => $object_class) {
						$href = htmlspecialchars(sprintf('%s&viewvalue=%s',$entry['href']['objectclasses'],strtolower($object_class)));
						if (isAjaxEnabled())
							printf('<a href="cmd.php?%s" title="%s" onclick="return ajSHOWSCHEMA(\'objectclasses\',\'oc\',\'%s\');">%s</a>',
								$href,_('Jump to this objectClass definition'),strtolower($object_class),$object_class);
						else
							printf('<a href="cmd.php?%s" title="%s">%s</a>',$href,_('Jump to this objectClass definition'),$object_class);

						if ( $i < count($oclass->getChildObjectClasses()) - 1)
							echo ', ';
					}
				echo '</b></td></tr>';

				printf('<tr class="even"><td class="blank" rowspan="2" style="width: 5%%;">&nbsp;</td><td style="width: 45%%;"><b>%s</b></td><td style="width: 45%%;"><b>%s</b></td><td class="blank" rowspan="2" style="width: 5%%;">&nbsp;</td></tr>',
					_('Required Attributes'),_('Optional Attributes'));

				echo '<tr class="odd">';
				echo '<td>';

				if ($attrs = $oclass->getMustAttrs(true)) {
					echo '<ul class="list">';

					foreach ($attrs as $attr) {
						echo '<li>';
						$href = htmlspecialchars(sprintf('%s&viewvalue=%s',$entry['href']['attributes'],$attr->getName()));
						if (isAjaxEnabled())
							printf('<a href="cmd.php?%s" onclick="return ajJUMP(\'%s\',\'%s\',\'%s\');">%s</a>',
								$href,$href,_('Attributes'),$attr->getName(),$attr->getName(false));
						else
							printf('<a href="cmd.php?%s">%s</a>',$href,$attr->getName(false));

						if ($attr->getSource() != $oclass->getName(false)) {
							echo '<br />';
							$href = htmlspecialchars(sprintf('%s&viewvalue=%s',$entry['href']['objectclasses'],strtolower($attr->getSource())));
							printf('<small>(%s ',_('Inherited from'));
							if (isAjaxEnabled())
								printf('<a href="cmd.php?%s" title="%s" onclick="return ajSHOWSCHEMA(\'objectclasses\',\'oc\',\'%s\');">%s</a>',
									$href,_('Jump to this objectClass definition'),strtolower($attr->getSource()),$attr->getSource());
							else
								printf('<a href="cmd.php?%s">%s</a>',$href,$attr->getSource());
							echo ')</small>';
						}
						echo '</li>';
					}
					echo '</ul>';

				} else
					printf('(%s)',_('none'));

				echo '</td>';
				echo '<td>';

				if ($attrs = $oclass->getMayAttrs(true)) {
					echo '<ul class="list">';

					foreach ($attrs as $attr) {
						echo '<li>';
						$href = htmlspecialchars(sprintf('%s&viewvalue=%s',$entry['href']['attributes'],$attr->getName()));
						if (isAjaxEnabled())
							printf('<a href="cmd.php?%s" onclick="return ajJUMP(\'%s\',\'%s\',\'%s\');">%s</a>',
								$href,$href,_('Attributes'),$attr->getName(),$attr->getName(false));
						else
							printf('<a href="cmd.php?%s">%s</a>',$href,$attr->getName(false));

						if ($attr->getSource() != $oclass->getName(false)) {
							echo '<br />';
							$href = htmlspecialchars(sprintf('%s&viewvalue=%s',$entry['href']['objectclasses'],strtolower($attr->getSource())));
							printf('<small>(%s ',_('Inherited from'));
							if (isAjaxEnabled())
								printf('<a href="cmd.php?%s" title="%s" onclick="return ajSHOWSCHEMA(\'objectclasses\',\'oc\',\'%s\');">%s</a>',
									$href,_('Jump to this objectClass definition'),strtolower($attr->getSource()),$attr->getSource());
							else
								printf('<a href="cmd.php?%s">%s</a>',$href,$attr->getSource());
							echo ')</small>';
						}

						if ($oclass->isForceMay($attr->getName())) {
							echo '<br />';
							printf('<small>%s</small>',_('This attribute has been forced as a MAY attribute by the configuration'));
						}
						echo '</li>';
					}
					echo '</ul>';

				} else
					printf('(%s)',_('none'));

				echo '</td>';
				echo '</tr>';
				echo '</table>';
				echo '<br />';
				echo '</div>';
			}
		}
		break;
}

if (! is_null($entry['value']) && ! $entry['viewed'])
	error(sprintf(_('No such schema item: "%s"'),$entry['value']),'error','index.php');

function drawJSItems($object) {
	echo '<script type="text/javascript">'."\n";

	echo "
function items() {
	var \$items = new Array();";
	$counter = 0;
	foreach ($object as $attr) {
		printf('	items[%s] = "%s";',$counter++,$attr->getName());
		echo "\n";
	}
	echo '
	return items;
}';

	echo '</script>';
}
?>
