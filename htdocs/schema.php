<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/schema.php,v 1.61.2.7 2007/03/21 23:33:19 wurley Exp $

/**
 * Displays the schema for the specified server_id
 *
 * Variables that come in via common.php
 *  - server_id
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

if( ! $ldapserver->haveAuthInfo())
	pla_error( _('Not enough information to login to server. Please check your configuration.') );

$view = isset($_GET['view']) ? $_GET['view'] : 'objectClasses';
$viewvalue = isset($_GET['viewvalue']) ? $_GET['viewvalue'] : null;

if (trim($viewvalue) == "")
	$viewvalue = null;
if (! is_null($viewvalue))
	$viewed = false;

include './header.php';

$schema_error_str = sprintf('%s <b>%s</b>.<br /><br /></center>%s<ul><li>%s</li><li>%s</li><li>%s</li><li>%s</li></ul>',
	_('Could not retrieve schema from'),htmlspecialchars($ldapserver->name),
	_('This could happen for several reasons, the most probable of which are:'),_('The server does not fully support the LDAP protocol.'),
	_('Your version of PHP does not correctly perform the query.'),_('phpLDAPadmin doesn\'t know how to fetch the schema for your server.'),
	_('Or lastly, your LDAP server doesnt provide this information.'));
?>

<body>

<h3 class="title"><?php echo _('Schema for server'); ?>
	<b><?php echo htmlspecialchars($ldapserver->name); ?></b></h3>

<br />

<center>
	<?php echo ( $view=='objectClasses' ?
		_('ObjectClasses') :
		sprintf('<a href="?server_id=%s&amp;view=%s">%s</a>',
			$ldapserver->server_id,'objectClasses',_('ObjectClasses'))); ?>
		|
	<?php echo ( $view=='attributes' ?
		_('Attribute Types'):
		sprintf('<a href="?server_id=%s&amp;view=%s">%s</a>',
			$ldapserver->server_id,'attributes',_('Attribute Types'))); ?>
		|
	<?php echo ( $view=='syntaxes' ?
		_('Syntaxes') :
		sprintf('<a href="?server_id=%s&amp;view=%s">%s</a>',
			$ldapserver->server_id,'syntaxes',_('Syntaxes'))); ?>
		|
	<?php echo ( $view=='matching_rules' ?
		_('Matching Rules') :
		sprintf('<a href="?server_id=%s&amp;view=%s">%s</a>',
			$ldapserver->server_id,'matching_rules',_('Matching Rules'))); ?>
</center>
<br />

<?php flush();

switch($view) {

	case 'syntaxes':
		$highlight_oid = isset($_GET['highlight_oid']) ? $_GET['highlight_oid'] : false;

		print '<table class="schema_attr" width="100%">';
		printf('<tr><th>%s</th><th>%s</th></tr>',_('Syntax OID'),_('Description'));

		$counter = 1;

		$schema_syntaxes = $ldapserver->SchemaSyntaxes(null,true);
		if (! $schema_syntaxes)
			pla_error($schema_error_str);

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
		break;

	case 'attributes':
		$schema_attrs = $ldapserver->SchemaAttributes();
		$schema_object_classes = $ldapserver->SchemaObjectClasses();

		if (! $schema_attrs || ! $schema_object_classes)
			pla_error($schema_error_str);

		printf('<small>%s:</small>',_('Jump to an attribute type'));
		echo '<form action="schema.php" method="get">';
		printf('<input type="hidden" name="view" value="%s" />',$view);
		printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);

		echo '<select name="viewvalue" onChange="submit()">';
		echo '<option value=""> - all -</option>';

		foreach ($schema_attrs as $name => $attr)
			printf('<option value="%s" %s>%s</option>',
				$name,$name == $viewvalue ? 'selected ': '',$attr->getName());

		echo '</select>';

		printf('<input type="submit" value="%s" /></form>',_('Go'));

		echo '<br />';
		echo '<table class="schema_attr" width="100%">';

		foreach ($schema_attrs as $attr) {
			if (is_null($viewvalue) || ! strcasecmp($viewvalue,$attr->getName())) {
				if (! is_null($viewvalue))
					$viewed = true;

				printf('<tr><th colspan="2"><a name="%s">%s</a></th></tr>',
					strtolower($attr->getName()),$attr->getName());

				$counter = 0;

				foreach (
					array('desc','obsolete','inherits','equality','ordering','substring_rule','syntax',
						'single_valued','collective','user_modification','usage','maximum_length',
						'aliases','used_by_objectclasses'
					) as $item) {

					printf('<tr class="%s">',++$counter%2 ? 'odd' : 'even');

					switch ($item) {
						case 'desc':
							printf('<td>%s</td>',_('Description'));
							printf('<td>%s</td>',
								is_null($attr->getDescription()) ?
									'('._('no description').')' : $attr->getDescription());

							print '</tr>';
							printf('<tr class="%s">',++$counter%2 ? 'odd' : 'even');
							echo '<td><acronym title="Object Identier">OID</acronym></td>';
							printf('<td>%s</td>',$attr->getOID());

							break;

						case 'obsolete':
							printf('<td>%s</td>',_('Obsolete'));
							printf('<td>%s</td>',$attr->getIsObsolete() ? '<b>'._('Yes').'</b>' : _('No'));
							break;

						case 'inherits':
							printf('<td>%s</td>',_('Inherits from'));
							print '<td>';

							if (is_null($attr->getSupAttribute()))
								printf('(%s)',_('none'));

							else
								printf('<a href="?server_id=%s&amp;view=%s&amp;viewvalue=%s">%s</a>',
									$ldapserver->server_id,$view,
									strtolower($attr->getSupAttribute()),$attr->getSupAttribute());

							print '</td>';
							break;

						case 'equality':
							printf('<td>%s</td>',_('Equality'));
							printf('<td>%s</td>',
								is_null($attr->getEquality()) ? '('._('not specified').')' :
									sprintf('<a href="?server_id=%s&amp;view=matching_rules&amp;viewvalue=%s">%s</a>',
										$ldapserver->server_id,$attr->getEquality(),$attr->getEquality()));
							break;

						case 'ordering':
							printf('<td>%s</td>',_('Ordering'));
							printf('<td>%s</td>',
								is_null($attr->getOrdering()) ? '('._('not specified').')' : $attr->getOrdering());
							break;

						case 'substring_rule':
							printf('<td>%s</td>',_('Substring Rule'));
							printf('<td>%s</td>',
								is_null($attr->getSubstr()) ? '('._('not specified').')' : $attr->getSubstr());
							break;

						case 'syntax':
							printf('<td>%s</td>',_('Syntax'));
							print '<td>';

							if (is_null($attr->getType())) {
								echo $attr->getSyntaxOID();

							} else {
								printf('<a href="?server_id=%s&amp;view=syntaxes&amp;highlight_oid=%s#%s">%s (%s)</a>',
									$ldapserver->server_id,
									$attr->getSyntaxOID(),$attr->getSyntaxOID(),
									$attr->getType(),$attr->getSyntaxOID());
							}

							print '</td>';
							break;

						case 'single_valued':
							printf('<td>%s</td>',_('Single Valued'));
							printf('<td>%s</td>',$attr->getIsSingleValue() ? _('Yes') : _('No'));
							break;

						case 'collective':
							printf('<td>%s</td>',_('Collective'));
							printf('<td>%s</td>',$attr->getIsCollective() ? _('Yes') : _('No'));
							break;

						case 'user_modification':
							printf('<td>%s</td>',_('User Modification'));
							printf('<td>%s</td>',$attr->getIsNoUserModification() ? _('No') : _('Yes'));
							break;

						case 'usage':
							printf('<td>%s</td>',_('Usage'));
							printf('<td>%s</td>',$attr->getUsage() ? $attr->getUsage() : '('._('not specified').')');
							break;

						case 'maximum_length':
							printf('<td>%s</td>',_('Maximum Length'));
							print '<td>';

							if ( is_null($attr->getMaxLength()))
								echo '('._('not applicable').')';

							else
								printf('%s %s',number_format($attr->getMaxLength()),
									$attr->getMaxLength()>1 ? _('characters') : _('character'));

							print '</td>';
							break;

						case 'aliases':
							printf('<td>%s</td>',_('Aliases'));
							print '<td>';

							if (count($attr->getAliases()) == 0)
								echo '('._('none').')';

							else
								foreach ($attr->getAliases() as $alias_attr_name)
									printf('<a href="?server_id=%s&amp;view=attributes&amp;viewvalue=%s">%s</a>',
										$ldapserver->server_id,strtolower($alias_attr_name),$alias_attr_name);

							print '</td>';
							break;

						case 'used_by_objectclasses':
							printf('<td>%s</td>',_('Used by objectClasses'));
							print '<td>';

							if (count($attr->getUsedInObjectClasses()) == 0)
								echo '('._('none').')';

							else
								foreach ($attr->getUsedInObjectClasses() as $used_in_oclass)
									printf('<a href="?server_id=%s&amp;view=objectClasses&amp;viewvalue=%s">%s</a> ',
										$ldapserver->server_id,strtolower($used_in_oclass),$used_in_oclass);

							print '</td>';
							break;

					}
					print '</tr>';
				}

				flush();
			}
		}

		print '</table>';
		break;

	case 'matching_rules':
		$schema_matching_rules = $ldapserver->MatchingRules(null,true);
		if (! $schema_matching_rules)
			pla_error($schema_error_str);

		printf('<small>%s</small><br />',_('Jump to a matching rule'));

		print '<form action="schema.php" method="get">';
		printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
		print '<input type="hidden" name="view" value="matching_rules" />';

		print '<select name="viewvalue" onChange="submit()">';
		print '<option value=""> - all -</option>';

		foreach ($schema_matching_rules as $rule)
			printf('<option value="%s" %s>%s</option>',
				$rule->getName(),
				($rule->getName() == $viewvalue ? 'selected': ''),
				$rule->getName());

		print '</select>';

		printf('<input type="submit" value="%s" />',_('Go'));
		print '</form>';

		print '<table class="schema_attr" width="100%">';
		printf('<tr><th>%s</th><th>%s</th><th>%s</th></tr>',
			_('Matching Rule OID'),_('Name'),_('Used by Attributes'));

		$counter = 1;

		foreach ($schema_matching_rules as $rule) {
			$counter++;
			$oid = htmlspecialchars($rule->getOID());
			$desc = htmlspecialchars($rule->getName());

			if ( is_null($viewvalue) || $viewvalue == ($rule->getName())) {

				if (! is_null($viewvalue))
					$viewed = true;

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
					print '<table><tr><td style="text-align: right">';
					print '<form action="schema.php" method="get">';
					printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
					print '<input type="hidden" name="view" value="attributes" />';

					print '<select style="width: 150px; color:black; background-color: #eee" size="4" name="viewvalue">';
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
			pla_error($schema_error_str);

		printf('<small>%s:</small>',_('Jump to an objectClass'));

		echo '<form action="schema.php" method="get">';
		printf('<input type="hidden" name="view" value="%s" />',$view);
		printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);

		echo '<select name="viewvalue" onChange="submit()">';
		echo '<option value=""> - all - </option>';

		foreach ($schema_oclasses as $name => $oclass)
			printf('<option value="%s" %s>%s</option>',
				$name,$name == $viewvalue ? 'selected ': '',$oclass->getName());

		echo '</select>';

		printf('<input type="submit" value="%s" />',_('Go'));
		echo '</form>';

		echo '<br />';

		flush();

		foreach ($schema_oclasses as $name => $oclass) {
			if (is_null($viewvalue) || ($viewvalue == $name)) {
				if (! is_null($viewvalue))
					$viewed = true;

				printf('<h4 class="oclass"><a name="%s">%s</a></h4>',$name,$oclass->getName());
				printf('<h4 class="oclass_sub">%s: <b>%s</b></h4>',_('OID'),$oclass->getOID());

				if ($oclass->getDescription())
					printf('<h4 class="oclass_sub">%s: <b>%s</b></h4>',_('Description'),$oclass->getDescription());

				printf('<h4 class="oclass_sub">%s: <b>%s</b></h4>',_('Type'),$oclass->getType());

				if ($oclass->getIsObsolete())
					printf('<h4 class="oclass_sub">%s</h4>',_('This objectClass is obsolete.'));

				printf('<h4 class="oclass_sub">%s: <b>',_('Inherits from'));
				if (count($oclass->getSupClasses()) == 0)
					printf('(%s)',_('none'));

				else
					foreach ($oclass->getSupClasses() as $i => $object_class) {
						printf('<a title="%s" href="?server_id=%s&amp;view=%s&amp;viewvalue=%s">%s</a>',
							_('Jump to this objectClass definition'),
							$ldapserver->server_id,$view,strtolower(htmlspecialchars($object_class)),
							htmlspecialchars($object_class));

						if ($i < count($oclass->getSupClasses()) - 1)
							echo ', ';
					}
				echo '</b></h4>';

				printf('<h4 class="oclass_sub">%s: <b>',_('Parent to'));
				if (strcasecmp($oclass->getName(),'top') == 0)
					printf('(<a href="schema.php?view=objectClasses&amp;server_id=%s">all</a>)',$ldapserver->server_id);

				elseif (count($oclass->getChildObjectClasses()) == 0)
					printf('(%s)',_('none'));

				else
					foreach ($oclass->getChildObjectClasses() as $i => $object_class) {
						printf('<a title="%s" href="?server_id=%s&amp;view=%s&amp;viewvalue=%s">%s</a>',
							_('Jump to this objectClass definition'),
							$ldapserver->server_id,$view,strtolower(htmlspecialchars($object_class)),
							htmlspecialchars($object_class));

						if ( $i < count($oclass->getChildObjectClasses()) - 1)
							echo ', ';
					}
				echo '</b></h4>';

				echo '<table width="100%" class="schema_oclasses">';
				echo '<tr>';
				printf('<th width="50%%"><b>%s</b></th>',_('Required Attributes'));
				printf('<th width="50%%"><b>%s</b></th>',_('Optional Attributes'));
				echo '</tr>';

				echo '<tr>';
				echo '<td>';
				if (count($oclass->getMustAttrs($schema_oclasses)) > 0) {
					echo '<ul class="schema">';

					foreach ($oclass->getMustAttrs($schema_oclasses) as $attr) {
						echo '<li>';
						printf('<a href="?server_id=%s&amp;view=attributes&amp;viewvalue=%s">%s</a>',
							$ldapserver->server_id,rawurlencode(strtolower($attr->getName())),htmlspecialchars($attr->getName()));

						if ($attr->getSource() != $oclass->getName()) {
							echo '<br />';
							printf('<small>&nbsp;&nbsp;(%s <a href="?server_id=%s&amp;view=objectClasses&amp;viewvalue=%s">%s</a>)</small>',
								_('Inherited from'),$ldapserver->server_id,strtolower($attr->getSource()),$attr->getSource());
						}
						echo '</li>';
					}

					echo '</ul>';

				} else
					printf('<center>(%s)</center>',_('none'));


				echo '</td>';

				echo '<td width="50%">';

				if (count($oclass->getMayAttrs($schema_oclasses)) > 0) {
					echo '<ul class="schema">';

					foreach ($oclass->getMayAttrs($schema_oclasses) as $attr) {
						echo '<li>';
						printf('<a href="?server_id=%s&amp;view=attributes&amp;viewvalue=%s">%s</a>',
							$ldapserver->server_id,rawurlencode(strtolower($attr->getName())),htmlspecialchars($attr->getName()));

						if ($attr->getSource() != $oclass->getName()) {
							echo '<br />';
							printf('<small>&nbsp;&nbsp; (%s <a href="?server_id=%s&amp;view=objectClasses&amp;viewvalue=%s">%s</a>)</small>',
								_('Inherited from'),$ldapserver->server_id,strtolower($attr->getSource()),$attr->getSource());
						}
						echo '</li>';
					}

					echo '</ul>';

				} else
					printf('<center>(%s)</center>',_('none'));

				echo '</td>';
				echo '</tr>';
				echo '</table>';
			}
		} /* End foreach objectClass */
		break;
}

if (! is_null($viewvalue) && ! $viewed)
	pla_error(sprintf(_('No such schema item: "%s"'),htmlspecialchars($viewvalue)));

echo '</body>';
echo '</html>';
