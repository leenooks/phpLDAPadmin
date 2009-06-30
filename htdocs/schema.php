<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/schema.php,v 1.60 2005/07/22 06:09:50 wurley Exp $

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
	pla_error( $lang['not_enough_login_info'] );

$view = isset($_GET['view']) ? $_GET['view'] : 'objectClasses';
$viewvalue = isset($_GET['viewvalue']) ? $_GET['viewvalue'] : null;

if (trim($viewvalue) == "")
	$viewvalue = null;
if (! is_null($viewvalue))
	$viewed = false;

include './header.php';

$schema_error_str = sprintf('%s <b>%s</b>.<br /><br /></center>%s<ul><li>%s</li><li>%s</li><li>%s</li><li>%s</li></ul>',
	$lang['could_not_retrieve_schema_from'],htmlspecialchars($ldapserver->name),
	$lang['reasons_for_error'],$lang['schema_retrieve_error_1'],
	$lang['schema_retrieve_error_2'],$lang['schema_retrieve_error_3'],
	$lang['schema_retrieve_error_4']);
?>

<body>

<h3 class="title"><?php echo $lang['schema_for_server']; ?>
	<b><?php echo htmlspecialchars($ldapserver->name); ?></b></h3>

<br />

<center>
	<?php echo ( $view=='objectClasses' ?
		$lang['objectclasses'] :
		sprintf('<a href="?server_id=%s&amp;view=%s">%s</a>',
			$ldapserver->server_id,'objectClasses',$lang['objectclasses'])); ?>
		|
	<?php echo ( $view=='attributes' ?
		$lang['attribute_types']:
		sprintf('<a href="?server_id=%s&amp;view=%s">%s</a>',
			$ldapserver->server_id,'attributes',$lang['attribute_types'])); ?>
		|
	<?php echo ( $view=='syntaxes' ?
		$lang['syntaxes'] :
		sprintf('<a href="?server_id=%s&amp;view=%s">%s</a>',
			$ldapserver->server_id,'syntaxes',$lang['syntaxes'])); ?>
		|
	<?php echo ( $view=='matching_rules' ?
		$lang['matchingrules'] :
		sprintf('<a href="?server_id=%s&amp;view=%s">%s</a>',
			$ldapserver->server_id,'matching_rules',$lang['matchingrules'])); ?>
</center>
<br />

<?php flush();

switch($view) {

	case 'syntaxes':
		$highlight_oid = isset($_GET['highlight_oid']) ? $_GET['highlight_oid'] : false;

		print '<table class="schema_attr" width="100%">';
		printf('<tr><th>%s</th><th>%s</th></tr>',$lang['syntax_oid'],$lang['desc']);

		$counter = 1;

		$schema_syntaxes = get_schema_syntaxes($ldapserver,null,true);
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

			printf('<td><a name="%s">%s</a></td><td>%s</td></tr>',$oid,$oid,$desc);
		}

		print '</table>';
		break;

	case 'attributes':
		$schema_attrs = get_schema_attributes($ldapserver,null,true);
		$schema_object_classes = get_schema_objectclasses($ldapserver,null,true);

		if (! $schema_attrs || ! $schema_object_classes)
			pla_error($schema_error_str);
?>

	<small><?php echo $lang['jump_to_attr']; ?>:</small>
	<form>
		<input type="hidden" name="view" value="<?php echo $view; ?>" />
        <input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
        <select name="viewvalue" onChange="submit()">
			<option value=""> - all -</option>

<?php
		foreach ($schema_attrs as $attr) {
			printf('<option value="%s" %s>%s</option>',
				$attr->getName(),
				(! strcasecmp($attr->getName(),$viewvalue) ? 'selected' : ''),$attr->getName());
		}
?>

		</select>
		<input type="submit" value="<?php echo $lang['go']; ?>" /></form>

		<br />
		<table class="schema_attr" width="100%">

<?php
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
					)
				 	as $item) {

					printf('<tr class="%s">',++$counter%2 ? 'odd' : 'even');
					printf('<td>%s</td>',$lang[$item]);

					switch ($item) {
						case 'desc':
							printf('<td>%s</td>',
								is_null($attr->getDescription()) ?
									'('.$lang['no_description'].')' : $attr->getDescription());

							print '</tr>';
							printf('<tr class="%s">',++$counter%2 ? 'odd' : 'even');
							printf('<td><acronym title="Object Identier">%s</acronym></td>',$lang['oid']);
							printf('<td>%s</td>',$attr->getOID());

							break;

						case 'obsolete':
							printf('<td>%s</td>',$attr->getIsObsolete() ? '<b>'.$lang['yes'].'</b>' : $lang['no']);
							break;

						case 'inherits':
							print '<td>';

							if (is_null($attr->getSupAttribute()))
								printf('(%s)',$lang['none']);

							else
								printf('<a href="?server_id=%s&amp;view=%s&amp;viewvalue=%s">%s</a>',
									$ldapserver->server_id,$view,
									strtolower($attr->getSupAttribute()),$attr->getSupAttribute());

							print '</td>';
							break;

						case 'equality':
							printf('<td>%s</td>',
								is_null($attr->getEquality()) ? '('.$lang['not_specified'].')' :
									sprintf('<a href="?server_id=%s&amp;view=matching_rules&amp;viewvalue=%s">%s</a>',
										$ldapserver->server_id,$attr->getEquality(),$attr->getEquality()));
							break;

						case 'ordering':
							printf('<td>%s</td>',
								is_null($attr->getOrdering()) ? '('.$lang['not_specified'].')' : $attr->getOrdering());
							break;

						case 'substring_rule':
							printf('<td>%s</td>',
								is_null($attr->getSubstr()) ? '('.$lang['not_specified'].')' : $attr->getSubstr());
							break;

						case 'syntax':
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
							printf('<td>%s</td>',$attr->getIsSingleValue() ? $lang['yes'] : $lang['no']);
							break;

						case 'collective':
							printf('<td>%s</td>',$attr->getIsCollective() ? $lang['yes'] : $lang['no']);
							break;

						case 'user_modification':
							printf('<td>%s</td>',$attr->getIsNoUserModification() ? $lang['no'] : $lang['yes']);
							break;

						case 'usage':
							printf('<td>%s</td>',$attr->getUsage() ? $attr->getUsage() : '('.$lang['not_specified'].')');
							break;

						case 'maximum_length':
							print '<td>';

							if ( is_null($attr->getMaxLength()))
								echo '('.$lang['not_applicable'].')';

							else
								printf('%s %s',number_format($attr->getMaxLength()),
									$attr->getMaxLength()>1 ? $lang['characters'] : $lang['character']);

							print '</td>';
							break;

						case 'aliases':
							print '<td>';

							if (count($attr->getAliases()) == 0)
								echo '('.$lang['none'].')';

							else
								foreach ($attr->getAliases() as $alias_attr_name)
									printf('<a href="?server_id=%s&amp;view=attributes&amp;viewvalue=%s">%s</a>',
										$ldapserver->server_id,$alias_attr_name,$alias_attr_name);

							print '</td>';
							break;

						case 'used_by_objectclasses':
							print '<td>';

							if (count($attr->getUsedInObjectClasses()) == 0)
								echo '('.$lang['none'].')';

							else
								foreach ($attr->getUsedInObjectClasses() as $used_in_oclass)
									printf('<a href="?server_id=%s&amp;view=objectClasses&amp;viewvalue=%s">%s</a> ',
										$ldapserver->server_id,$used_in_oclass,$used_in_oclass);

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
		$schema_matching_rules = get_schema_matching_rules($ldapserver,null,true);
		if (! $schema_matching_rules)
			pla_error($schema_error_str);

		printf('<small>%s</small><br />',$lang['jump_to_matching_rule']);

		print '<form get="?">';
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

       	printf('<input type="submit" value="%s" />',$lang['go']);
		print '</form>';

		print '<table class="schema_attr" width="100%">';
		printf('<tr><th>%s</th><th>%s</th><th>%s</th></tr>',
			$lang['matching_rule_oid'],$lang['name'],$lang['used_by_attributes']);

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
					$desc .= sprintf(' <span style="color:red">%s</span>',$lang['obsolete']);

				printf('<tr class="%s">',$counter%2 ? 'odd' : 'even');
				printf('<td>%s</td>',$oid);
				printf('<td>%s</td>',$desc);

				print '<td>';

				if (count($rule->getUsedByAttrs()) == 0) {
					printf('<center>(%s)</center><br /><br />',$lang['none']);

				} else {
					print '<table><tr><td style="text-align: right">';
					print '<form>';
					printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
					print '<input type="hidden" name="view" value="attributes" />';

					print '<select style="width: 150px; color:black; background-color: #eee" size="4" name="viewvalue">';
					foreach ($rule->getUsedByAttrs() as $attr)
						printf('<option>%s</option>',$attr);

					print '</select><br />';
					printf('<input type="submit" value="%s" />',$lang['go']);
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
		$schema_oclasses = get_schema_objectclasses($ldapserver,null,true);
		if (! $schema_oclasses)
			pla_error($schema_error_str);
?>

	<small><?php echo $lang['jump_to_objectclass']; ?>:</small>
	<form>
		<input type="hidden" name="view" value="<?php echo $view; ?>" />
        <input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
		<select name="viewvalue" onChange="submit()">
	        <option value=""> - all - </option>

<?php
		foreach( $schema_oclasses as $name => $oclass ) {
			printf('<option value="%s" %s>%s</option>',
				$oclass->getName(),
				(strcasecmp($oclass->getName(),$viewvalue) == 0 ? ' selected ':''),
				$oclass->getName());

		}
?>

        </select>
		<input type="submit" value="<?php echo $lang['go']; ?>" />
	</form>

	<br />

<?php
		flush();

		foreach ($schema_oclasses as $name => $oclass) {
			foreach ($oclass->getSupClasses() as $parent_name) {
				if (isset($schema_oclasses[$parent_name]))
					$schema_oclasses[$parent_name]->addChildObjectClass($oclass->getName());
			}

			if ( is_null($viewvalue) || ! strcasecmp($viewvalue,$oclass->getName())) {
				if ( ! is_null($viewvalue))
					$viewed = true;
?>

		<h4 class="oclass"><a name="<?php echo $name; ?>"><?php echo $oclass->getName(); ?></a></h4>
		<h4 class="oclass_sub"><?php echo $lang['OID']; ?>: <b><?php echo $oclass->getOID(); ?></b></h4>

		<?php if ($oclass->getDescription()) { ?>
			<h4 class="oclass_sub"><?php echo $lang['desc']; ?>: <b><?php echo $oclass->getDescription(); ?></b></h4>
		<?php } ?>

		<h4 class="oclass_sub"><?php echo $lang['type']; ?>: <b><?php echo $oclass->getType(); ?></b></h4>

		<?php if ( $oclass->getIsObsolete()) { ?>
			<h4 class="oclass_sub"><?php echo $lang['is_obsolete']; ?></h4>
		<?php } ?>

		<h4 class="oclass_sub"><?php echo $lang['inherits']; ?>: <b>

<?php

				if (count($oclass->getSupClasses()) == 0)
					printf('(%s)',$lang['none']);

				else
					foreach ($oclass->getSupClasses() as $i => $object_class) {
						printf('<a title="%s" href="?server_id=%s&amp;view=%s&amp;viewvalue=%s">%s</a>',
							$lang['jump_to_this_oclass'],$ldapserver->server_id,$view,htmlspecialchars($object_class),
							htmlspecialchars($object_class));

						if ($i < count($oclass->getSupClasses()) - 1)
							print ', ';
					}
?>

</b></h4>

		<h4 class="oclass_sub"><?php echo $lang['parent_to']; ?>: <b>

<?php

				if (strcasecmp($oclass->getName(),'top') == 0)
					printf('(<a href="schema.php?view=objectClasses&amp;server_id=%s">all</a>)',$ldapserver->server_id);

				elseif (count($oclass->getChildObjectClasses()) == 0)
					printf('(%s)',$lang['none']);

				else
					foreach ($oclass->getChildObjectClasses() as $i => $object_class) {
						printf('<a title="%s" href="?server_id=%s&view=%s&amp;viewvalue=%s">%s</a>',
							$lang['jump_to_this_oclass'],$ldapserver->server_id,$view,htmlspecialchars($object_class),
							htmlspecialchars($object_class));

						if ( $i < count($oclass->getChildObjectClasses()) - 1)
							print ', ';
					}
?>

</b></h4>

		<table width="100%" class="schema_oclasses">
		<tr>
			<th width="50%"><b><?php echo $lang['required_attrs']; ?></b></th>
			<th width="50%"><b><?php echo $lang['optional_attrs']; ?></b></th>
		</tr>

		<tr>
			<td>
<?php
				if (count($oclass->getMustAttrs($schema_oclasses)) > 0) {
					print '<ul class="schema">';

					foreach ($oclass->getMustAttrs($schema_oclasses) as $attr) {
						print '<li>';
						printf('<a href="?server_id=%s&amp;view=attributes&amp;viewvalue=%s">%s</a>',
							$ldapserver->server_id,rawurlencode($attr->getName()),htmlspecialchars($attr->getName()));

						if ($attr->getSource() != $oclass->getName()) {
							printf('<br /><small>&nbsp;&nbsp;(%s ',$lang['inherited_from']);
							printf('<a href="?server_id=%s&amp;view=objectClasses&amp;viewvalue=">%s</a>',
								$ldapserver->server_id,$attr->getSource(),$attr->getSource());
							print ')</small>';
						}

						print '</li>';
					}

					print '</ul>';

				} else
					printf('<center>(%s)</center>',$lang['none']);

?>

		</td>
		<td width="50%">

<?php
				if (count($oclass->getMayAttrs($schema_oclasses)) > 0) {
					print '<ul class="schema">';

					foreach ($oclass->getMayAttrs($schema_oclasses) as $attr) {
						print '<li>';
						printf('<a href="?server_id=%s&amp;view=attributes&amp;viewvalue=%s">%s</a>',
							$ldapserver->server_id,rawurlencode($attr->getName()),htmlspecialchars($attr->getName()));

						if ($attr->getSource() != $oclass->getName()) {
							printf('<br /><small>&nbsp;&nbsp; (%s ',$lang['inherited_from']);
							printf('<a href="?server_id=%s&amp;view=objectClasses&amp;viewvalue=%s">%s</a>',
								$ldapserver->server_id,$attr->getSource(),$attr->getSource());
							print ')</small>';
						}

						print '</li>';
					}

					print '</ul>';

				} else
					printf('<center>(%s)</center>',$lang['none']);
?>

	</ul>
	</td>
	</tr>
	</table>

<?php
			}
		} /* End foreach objectClass */
		break;
}

if (! is_null( $viewvalue ) && ! $viewed)
	pla_error(sprintf($lang['no_such_schema_item'],htmlspecialchars($viewvalue)));

?>

</body>
</html>
