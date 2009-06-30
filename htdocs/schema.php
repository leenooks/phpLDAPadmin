<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/schema.php,v 1.67.2.7 2008/12/12 12:20:22 wurley Exp $

/**
 * Displays the schema for the specified server_id
 *
 * Variables that come in as GET vars:
 *  - view (optional: can be 'attr' or empty. If 'attr', show that attribute)
 *  - attr (optional)
 *  - highlight_oid (optional)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if (! $_SESSION[APPCONFIG]->isCommandAvailable('schema'))
	error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('view schema')),'error','index.php');

$entry = array();
$entry['view'] = get_request('view','GET','false','objectClasses');
$entry['value'] = get_request('viewvalue','GET');

if (! is_null($entry['value'])) {
	$entry['viewed'] = false;
	$entry['value'] = strtolower($entry['value']);
}

$schema_error_str = sprintf('%s <b>%s</b>.<br /><br /></center>%s<ul><li>%s</li><li>%s</li><li>%s</li><li>%s</li></ul>',
	_('Could not retrieve schema from'),htmlspecialchars($ldapserver->name),
	_('This could happen for several reasons, the most probable of which are:'),_('The server does not fully support the LDAP protocol.'),
	_('Your version of PHP does not correctly perform the query.'),_('phpLDAPadmin doesn\'t know how to fetch the schema for your server.'),
	_('Or lastly, your LDAP server doesnt provide this information.'));

printf('<h3 class="title">%s <b>%s</b></h3>',
	_('Schema for server'),
	htmlspecialchars($ldapserver->name));

$entry['schema_types'] = array(
	'objectClasses'=>_('ObjectClasses'),
	'attributes'=>_('Attribute Types'),
	'syntaxes'=>_('Syntaxes'),
	'matching_rules'=>_('Matching Rules'));

echo '<br />';
echo '<center>';

$counter = 0;
foreach ($entry['schema_types'] as $item => $value) {
	if ($counter++)
		echo ' | ';

	$entry['href'][$item] = sprintf('?cmd=schema&server_id=%s&view=%s&viewvalue=%%s',$ldapserver->server_id,$item);

	$href = htmlspecialchars(sprintf($entry['href'][$item],''));
	echo ($entry['view'] == $item ? _($value) : sprintf('<a href="%s">%s</a>',$href,_($value)));
}

echo '</center>';
echo '<br />';

switch($entry['view']) {

	case 'syntaxes':
		$highlight_oid = get_request('highlight_oid','GET',false,false);

		echo '<center>';
		print '<table class="result_table" border=0>';
		printf('<tr class="heading"><td>%s</td><td>%s</td></tr>',_('Syntax OID'),_('Description'));

		$counter = 1;

		$schema_syntaxes = $ldapserver->SchemaSyntaxes(null,true);
		if (! $schema_syntaxes)
			error($schema_error_str,'error','index.php');

		foreach ($schema_syntaxes as $syntax) {
			$counter++;
			$oid = htmlspecialchars($syntax->getOID());
			$desc = htmlspecialchars($syntax->getDescription());

			if ($highlight_oid && $highlight_oid == $oid)
				echo '<tr class="highlight">';

			else
				printf('<tr class="%s">',$counter%2==0?'even':'odd');

			printf('<td>%s</td><td>%s</td></tr>',$oid,$desc);
		}

		print '</table>';
		echo '</center>';
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

		$schema_attrs = $ldapserver->SchemaAttributes();
		$schema_object_classes = $ldapserver->SchemaObjectClasses();

		if (! $schema_attrs || ! $schema_object_classes)
			error($schema_error_str,'error','index.php');

		printf('<small>%s:</small>',_('Jump to an attribute type'));
		echo '<form action="cmd.php" method="get">';
		echo '<input type="hidden" name="cmd" value="schema" />';
		printf('<input type="hidden" name="view" value="%s" />',$entry['view']);
		printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);

		echo '<select name="viewvalue" onChange="submit()">';
		echo '<option value=""> - all -</option>';
		foreach ($schema_attrs as $name => $attr)
			printf('<option value="%s" %s>%s</option>',
				$name,$name == $entry['value'] ? 'selected ': '',$attr->getName());
		echo '</select>';

		printf('<input type="submit" value="%s" /></form>',_('Go'));

		echo '<br />';

		foreach ($schema_attrs as $attr) {
			if (is_null($entry['value']) || ! strcasecmp($entry['value'],$attr->getName())) {
				if (! is_null($entry['value']))
					$entry['viewed'] = true;

				echo '<table class="result_table" width=100% border=0>';
				printf('<tr class="heading"><td colspan=2><a name="%s">%s</a></td></tr>',
					strtolower($attr->getName()),$attr->getName());

				$counter = 0;

				foreach ($entry['attr_types'] as $item => $value) {

					printf('<tr class="%s">',++$counter%2 ? 'odd' : 'even');
					printf('<td class="title">%s</td>',$value);

					switch ($item) {
						case 'desc':
							printf('<td>%s</td>',
								is_null($attr->getDescription()) ?
									'('._('no description').')' : $attr->getDescription());

							print '</tr>';
							printf('<tr class="%s">',++$counter%2 ? 'odd' : 'even');
							echo '<td class="title"><acronym title="Object Identier">OID</acronym></td>';
							printf('<td>%s</td>',$attr->getOID());

							break;

						case 'obsolete':
							printf('<td>%s</td>',$attr->getIsObsolete() ? '<b>'._('Yes').'</b>' : _('No'));
							break;

						case 'inherits':
							print '<td>';

							if (is_null($attr->getSupAttribute()))
								printf('(%s)',_('none'));

							else {
								$href = htmlspecialchars(sprintf($entry['href']['attributes'],strtolower($attr->getSupAttribute())));
								printf('<a href="%s">%s</a>',$href,$attr->getSupAttribute());
							}

							print '</td>';
							break;

						case 'equality':
							print '<td>';

							if (is_null($attr->getEquality()))
								printf('(%s)',_('not specified'));

							else {
								$href = htmlspecialchars(sprintf($entry['href']['matching_rules'],$attr->getEquality()));
								printf('<a href="%s">%s</a>',$href,$attr->getEquality());
							}

							print '</td>';
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
							print '<td>';

							if (is_null($attr->getType())) {
								echo $attr->getSyntaxOID();

							} else {
								$href = htmlspecialchars(sprintf($entry['href']['syntaxes'].'&highlight_oid=%s#%s','',
									$attr->getSyntaxOID(),$attr->getSyntaxOID()));
								printf('<a href="%s">%s (%s)</a>',$href,$attr->getType(),$attr->getSyntaxOID());
							}

							print '</td>';
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
							print '<td>';

							if ( is_null($attr->getMaxLength()))
								echo '('._('not applicable').')';

							else
								printf('%s %s',number_format($attr->getMaxLength()),
									$attr->getMaxLength()>1 ? _('characters') : _('character'));

							print '</td>';
							break;

						case 'aliases':
							print '<td>';

							if (count($attr->getAliases()) == 0)
								printf('(%s)',_('none'));

							else
								foreach ($attr->getAliases() as $alias_attr_name) {
									$href = htmlspecialchars(sprintf($entry['href']['attributes'],strtolower($alias_attr_name)));
									printf('<a href="%s">%s</a>',$href,$alias_attr_name);
								}

							print '</td>';
							break;

						case 'used_by_objectclasses':
							print '<td>';

							if (count($attr->getUsedInObjectClasses()) == 0)
								printf('(%s)',_('none'));

							else
								foreach ($attr->getUsedInObjectClasses() as $used_in_oclass) {
									$href = htmlspecialchars(sprintf($entry['href']['objectClasses'],strtolower($used_in_oclass)));
									printf('<a href="%s">%s</a> ',$href,$used_in_oclass);
								}

							print '</td>';
							break;

						case 'force_as_may':
							printf('<td>%s</td>',$attr->forced_as_may ? _('Yes') : _('No'));
							break;

					}
					print '</tr>';
				}
				print '</table>';
				echo '<br />';
			}
		}

		break;

	case 'matching_rules':
		$schema_matching_rules = $ldapserver->MatchingRules(null,true);
		if (! $schema_matching_rules)
			error($schema_error_str,'error','index.php');

		printf('<small>%s</small><br />',_('Jump to a matching rule'));

		print '<form action="cmd.php" method="get">';
		print '<input type="hidden" name="cmd" value="schema" />';
		printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
		print '<input type="hidden" name="view" value="matching_rules" />';

		print '<select name="viewvalue" onChange="submit()">';
		print '<option value=""> - all -</option>';
		foreach ($schema_matching_rules as $rule)
			printf('<option value="%s" %s>%s</option>',
				$rule->getName(),
				($rule->getName() == $entry['value'] ? 'selected': ''),
				$rule->getName());
		print '</select>';

		printf('<input type="submit" value="%s" />',_('Go'));
		print '</form>';

		print '<table class="result_table" width=100% border=0>';
		printf('<tr class="heading"><td>%s</td><td>%s</td><td>%s</td></tr>',
			_('Matching Rule OID'),_('Name'),_('Used by Attributes'));

		$counter = 1;

		foreach ($schema_matching_rules as $rule) {
			$counter++;
			$oid = htmlspecialchars($rule->getOID());
			$desc = htmlspecialchars($rule->getName());

			if ( is_null($entry['value']) || $entry['value'] == strtolower($rule->getName())) {

				if (! is_null($entry['value']))
					$entry['viewed'] = true;

				if (null != $rule->getDescription())
					$desc .= sprintf(' (%s)',$rule->getDescription());

				if ( $rule->getIsObsolete())
					$desc .= sprintf(' <span style="color:red">%s</span>',_('Obsolete'));

				printf('<tr class="%s">',$counter%2 ? 'odd' : 'even');
				printf('<td>%s</td>',$oid);
				printf('<td>%s</td>',$desc);

				print '<td>';

				if (count($rule->getUsedByAttrs()) == 0) {
					printf('<center>(%s)</center><br /><br />',_('none'));

				} else {
					print '<table width=100% border=0><tr><td>';
					print '<form action="cmd.php" method="get">';
					print '<input type="hidden" name="cmd" value="schema" />';
					printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
					print '<input type="hidden" name="view" value="attributes" />';

					print '<select size="4" name="viewvalue">';
					foreach ($rule->getUsedByAttrs() as $attr)
						printf('<option>%s</option>',$attr);
					print '</select><br />';

					printf('<input type="submit" value="%s" />',_('Go'));
					print '</form>';
					print '</td></tr></table>';
				}
				print '</td>';
				print '</tr>';
			}
		}

		print '</table>';
		break;

	case 'objectClasses':
		$schema_oclasses = $ldapserver->SchemaObjectClasses();
		if (! $schema_oclasses)
			error($schema_error_str,'error','index.php');

		printf('<small>%s:</small>',_('Jump to an objectClass'));

		echo '<form action="cmd.php" method="get">';
		echo '<input type="hidden" name="cmd" value="schema" />';
		printf('<input type="hidden" name="view" value="%s" />',$entry['view']);
		printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);

		echo '<select name="viewvalue" onChange="submit()">';
		echo '<option value=""> - all - </option>';
		foreach ($schema_oclasses as $name => $oclass)
			printf('<option value="%s" %s>%s</option>',
				$name,$name == $entry['value'] ? 'selected ': '',$oclass->getName());
		echo '</select>';

		printf('<input type="submit" value="%s" />',_('Go'));
		echo '</form>';

		echo '<br />';

		foreach ($schema_oclasses as $name => $oclass) {
			if (is_null($entry['value']) || ($entry['value'] == $name)) {
				if (! is_null($entry['value']))
					$entry['viewed'] = true;

				echo '<table class="result_table" width=100% border=0>';
				printf('<tr class="heading"><td colspan=4><a name="%s">%s</a></td></tr>',$name,$oclass->getName());
				printf('<tr class="odd"><td colspan=4>%s: <b>%s</b></td></tr>',_('OID'),$oclass->getOID());

				if ($oclass->getDescription())
					printf('<tr class="odd"><td colspan=4>%s: <b>%s</b></td></tr>',_('Description'),$oclass->getDescription());

				printf('<tr class="odd"><td colspan=4>%s: <b>%s</b></td></tr>',_('Type'),$oclass->getType());

				if ($oclass->getIsObsolete())
					printf('<tr class="odd"><td colspan=4>%s</td></tr>',_('This objectClass is obsolete.'));

				printf('<tr class="odd"><td colspan=4>%s: <b>',_('Inherits from'));
				if (count($oclass->getSupClasses()) == 0)
					printf('(%s)',_('none'));

				else
					foreach ($oclass->getSupClasses() as $i => $object_class) {
						$href = htmlspecialchars(sprintf($entry['href']['objectClasses'],strtolower($object_class)));

						printf('<a title="%s" href="%s">%s</a>',
							_('Jump to this objectClass definition'),$href,$object_class);

						if ($i < count($oclass->getSupClasses()) - 1)
							echo ', ';
					}
				echo '</b></td></tr>';

				printf('<tr class="odd"><td colspan=4>%s: <b>',_('Parent to'));
				if (strcasecmp($oclass->getName(),'top') == 0) {
					$href = htmlspecialchars(sprintf($entry['href']['objectClasses'],''));
					printf('(<a href="%s">all</a>)',$href);

				} elseif (count($oclass->getChildObjectClasses()) == 0)
					printf('(%s)',_('none'));

				else
					foreach ($oclass->getChildObjectClasses() as $i => $object_class) {
						$href = htmlspecialchars(sprintf($entry['href']['objectClasses'],strtolower($object_class)));
						printf('<a title="%s" href="%s">%s</a>',_('Jump to this objectClass definition'),$href,$object_class);

						if ( $i < count($oclass->getChildObjectClasses()) - 1)
							echo ', ';
					}
				echo '</b></td></tr>';

				printf('<tr class="even"><td class="blank" rowspan=2>&nbsp;</td><td><b>%s</b></td><td><b>%s</b></td><td class="blank" rowspan=2>&nbsp;</td></tr>',
					_('Required Attributes'),_('Optional Attributes'));

				echo '<tr class="odd">';
				echo '<td>';

				if (count($oclass->getMustAttrs($schema_oclasses)) > 0) {

					echo '<ul class="list">';
					foreach ($oclass->getMustAttrs($schema_oclasses) as $attr) {
						echo '<li>';
						$href = htmlspecialchars(sprintf($entry['href']['attributes'],strtolower($attr->getName())));
						printf('<a href="%s">%s</a>',$href,$attr->getName());

						if ($attr->getSource() != $oclass->getName()) {
							echo '<br />';
							$href = htmlspecialchars(sprintf($entry['href']['objectClasses'],strtolower($attr->getSource())));
							printf('<small>(%s <a href="%s">%s</a>)</small>',_('Inherited from'),$href,$attr->getSource());
						}
						echo '</li>';
					}
					echo '</ul>';

				} else
					printf('(%s)',_('none'));

				echo '</td>';
				echo '<td>';

				if (count($oclass->getMayAttrs($schema_oclasses)) > 0) {

					echo '<ul class="list">';
					foreach ($oclass->getMayAttrs($schema_oclasses) as $attr) {
						echo '<li>';
						$href = htmlspecialchars(sprintf($entry['href']['attributes'],strtolower($attr->getName())));
						printf('<a href="%s">%s</a>',$href,$attr->getName());

						if ($attr->getSource() != $oclass->getName()) {
							echo '<br />';
							$href = htmlspecialchars(sprintf($entry['href']['objectClasses'],strtolower($attr->getSource())));
							printf('<small>(%s <a href="%s">%s</a>)</small>',_('Inherited from'),$href,$attr->getSource());
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
			}
		} /* End foreach objectClass */
		break;
}

if (! is_null($entry['value']) && ! $entry['viewed'])
	error(sprintf(_('No such schema item: "%s"'),htmlspecialchars($entry['value'])),'error','index.php');
?>
