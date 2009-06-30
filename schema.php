<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/schema.php,v 1.59 2005/03/07 20:19:32 wurley Exp $

/**
 * Displays the schema for the specified server_id
 *
 * Variables that come in as GET vars:
 *  - server_id
 *  - view (optional: can be 'attr' or empty. If 'attr', show that attribute)
 *  - attr (optional)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$server_id = (isset($_GET['server_id']) ? $_GET['server_id'] : '');
$ldapserver = new LDAPServer($server_id);

if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$view = isset( $_GET['view'] ) ? $_GET['view'] : 'objectClasses';
$viewvalue = isset( $_GET['viewvalue'] ) ? $_GET['viewvalue'] : null;

if( trim( $viewvalue ) == "" )
	$viewvalue = null;
if( ! is_null( $viewvalue ) )
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
		'<a href="?server_id=' . $server_id . '&amp;view=objectClasses">'.$lang['objectclasses'].'</a>' ); ?>
		|
	<?php echo ( $view=='attributes' ?
		$lang['attribute_types']:
		'<a href="?server_id=' . $server_id . '&amp;view=attributes">'.	$lang['attribute_types'].'</a>' ); ?>
		|
	<?php echo ( $view=='syntaxes' ?
		$lang['syntaxes'] :
		'<a href="?server_id=' . $server_id . '&amp;view=syntaxes">'.$lang['syntaxes'].'</a>' ); ?>
		|
	<?php echo ( $view=='matching_rules' ?
		$lang['matchingrules'] :
		'<a href="?server_id=' . $server_id . '&amp;view=matching_rules">'.$lang['matchingrules'].'</a>' ); ?>
</center>
<br />

<?php flush();

if( $view == 'syntaxes' ) {
	$highlight_oid = isset( $_GET['highlight_oid'] ) ? $_GET['highlight_oid'] : false;

	echo "\n\n<table class=\"schema_attr\" width=\"100%\">\n";
	echo "<tr><th>" . $lang['syntax_oid'] . "</th><th>" . $lang['desc'] . "</th></tr>\n";

	flush();

	$counter=1;

	$schema_syntaxes = get_schema_syntaxes( $ldapserver, null, true );
	if( ! $schema_syntaxes ) pla_error( $schema_error_str );

	foreach( $schema_syntaxes as $syntax ) {
		$counter++;
		$oid =  htmlspecialchars( $syntax->getOID() );
		$desc = htmlspecialchars( $syntax->getDescription() );

		if( $highlight_oid && $highlight_oid == $oid )
			echo "<tr class=\"highlight\">";
		else
			echo "<tr class=\"" . ($counter%2==0?'even':'odd'). "\">";

		echo "<td><a name=\"$oid\">$oid</a></td><td>$desc</td></tr>\n\n";
	}

	echo "</table>\n";

} elseif( $view == 'attributes' ) {
	flush();

	$schema_attrs = get_schema_attributes( $ldapserver, null, true );
	$schema_object_classes = get_schema_objectclasses( $ldapserver, null, true );

	if( ! $schema_attrs || ! $schema_object_classes )
		pla_error( $schema_error_str );

	//echo "<pre>";
	//print_r( $schema_attrs );
	//echo "</pre>";
	?>

	<small><?php echo $lang['jump_to_attr']; ?>:</small>
	<form><input type="hidden" name="view" value="<?php echo $view; ?>" />
        <input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
        <select name="viewvalue" onChange="submit()">
	<option value=""> - all -</option>

	<?php foreach( $schema_attrs as $attr ) {
		printf( '<option value="%s" %s>%s</option>',
			$attr->getName(),
			( 0 == strcasecmp( $attr->getName(), $viewvalue ) ? 'selected' : '' ),
			$attr->getName());
	} ?>

	</select><input type="submit" value="<?php echo $lang['go']; ?>" /></form>

	<br />
	<table class="schema_attr" width="100%">

	<?php foreach( $schema_attrs  as $attr ) {
		if ( is_null( $viewvalue ) || 0 == strcasecmp( $viewvalue, $attr->getName() ) ) {
			if( ! is_null( $viewvalue ) )
				$viewed = true;

			flush();

			echo "<tr><th colspan=\"2\"><a name=\"" . strtolower( $attr->getName() ) . "\">";
			echo $attr->getName() . "</a></th></tr>\n\n";
			$counter = 0;

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['desc']."</td>\n";
			echo "<td>" . ( $attr->getDescription() == null ? '('.$lang['no_description'].')' : $attr->getDescription() ). "</td>\n";
			echo "</tr>\n\n";
			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td><acronym title=\"Object Identier\">".$lang['oid']."</acronym></td>\n";
			echo "<td>" .  $attr->getOID() . "</td>\n";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo '<td>'.$lang['obsolete']."?</td>\n";
			echo "<td>" . ( $attr->getIsObsolete() ? '<b>' . $lang['yes'] . '</b>' : $lang['no'] ) . "</td>\n";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['inherits']."</td>\n";
			echo "<td>";

			if( $attr->getSupAttribute()==null )
				echo '('.$lang['none'].')';
			else
				echo "<a href=\"?server_id=".$server_id."&view=$view&amp;viewvalue=" . strtolower( $attr->getSupAttribute() ) . "\">" . $attr->getSupAttribute()  . "</a></td>\n";

			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['equality']."</td>\n";
			echo "<td>" .  ( $attr->getEquality() == null ? '('.$lang['not_specified'].')' : "<a href=\"?server_id=".$server_id."&view=matching_rules&viewvalue=".$attr->getEquality()."\">".$attr->getEquality() ) . "</a></td>\n";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['ordering']."</td>\n";
			echo "<td>" .  ( $attr->getOrdering()==null? '('.$lang['not_specified'].')' : $attr->getOrdering() ) . "</td>\n";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['substring_rule']."</td>\n";
			echo "<td>" .  ( $attr->getSubstr()==null? '('.$lang['not_specified'].')' : $attr->getSubstr() ) . "</td>\n";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['syntax']."</td>\n";
			echo "<td>";

			if( null != $attr->getType() ) {
				echo "<a href=\"?server_id=$server_id&amp;view=syntaxes&amp;highlight_oid=";
				echo $attr->getSyntaxOID() . "#" .  $attr->getSyntaxOID();
				echo "\">" . $attr->getType() . " (" . $attr->getSyntaxOID() . ")</a>";

			} else {
				echo $attr->getSyntaxOID();
			}

			echo "</td>\n";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['single_valued']."</td>\n";
			echo "<td>" .  ( $attr->getIsSingleValue() ? $lang['yes'] : $lang['no'] ) . "</td>\n";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['collective']."?</td>\n";
			echo "<td>" .  ( $attr->getIsCollective() ? $lang['yes'] : $lang['no'] ) . "</td>\n";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['user_modification']."</td>\n";
			echo "<td>" . ( $attr->getIsNoUserModification() ? $lang['no'] : $lang['yes'] ) . "</td>\n";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['usage']."</td>\n";
			echo "<td>" .  ( $attr->getUsage() ? $attr->getUsage() : '('.$lang['not_specified'].')' ) . "</td>\n";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['maximum_length']."</td>\n";
			echo "<td>";

			if ( $attr->getMaxLength() === null )
				echo '('.$lang['not_applicable'].')';

			else {
				echo number_format( $attr->getMaxLength() ) ." ";

				if ( $attr->getMaxLength()>1)
					echo $lang['characters'];
				else
					echo $lang['character'];
			}

			echo "</td>\n";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['aliases']."</td>\n";
			echo "<td>";

			if( count( $attr->getAliases() ) == 0 )
				echo '('.$lang['none'].')';
			else
				foreach( $attr->getAliases() as $alias_attr_name )
					echo "<a href=\"?server_id=$server_id&view=attributes&amp;viewvalue=" . $alias_attr_name. "\">$alias_attr_name</a> ";

			echo "</td>";
			echo "</tr>\n\n";

			echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
			echo "<td>".$lang['used_by_objectclasses']."</td>\n";
			echo "<td>";

			if( count( $attr->getUsedInObjectClasses() ) == 0 )
				echo '('.$lang['none'].')';
			else
				foreach( $attr->getUsedInObjectClasses() as $used_in_oclass)
					echo "<a href=\"?server_id=$server_id&amp;view=objectClasses&amp;viewvalue=" .
						$used_in_oclass. "\">$used_in_oclass</a> ";

			echo "</td>";
			echo "</tr>\n\n";

			flush();
		}
	}
	echo "</table>\n";

} elseif( $view == 'matching_rules' ) {
        $schema_matching_rules = get_schema_matching_rules( $ldapserver, null, true );

	echo '<small>' . $lang['jump_to_matching_rule'].'</small><br />';
	echo '<form get="?">';
        echo '<input type="hidden" name="server_id" value="'.$server_id.'" />';
        echo '<input type="hidden" name="view" value="matching_rules" />';
        echo '<select name="viewvalue" onChange="submit()">';
        echo '<option value=""> - all -</option>';

	foreach( $schema_matching_rules as $rule )
		printf( '<option value="%s" %s>%s</option>',
			$rule->getName(),
			($rule->getName() == $viewvalue ? 'selected': ''),
			$rule->getName());

        echo '</select>';
       	echo '<input type="submit" value="'.$lang['go'].'" />';
	echo '</form>';
	echo "\n\n<table class=\"schema_attr\" width=\"100%\">\n";
	echo "<tr><th>" . $lang['matching_rule_oid'] . "</th><th>" . $lang['name'] . "</th><th>".$lang['used_by_attributes']."</th></tr>\n";

	flush();

	$schema_matching_rules = get_schema_matching_rules( $ldapserver, null, true );
	if( ! $schema_matching_rules ) pla_error( $schema_error_str );

	$counter=1;
	foreach( $schema_matching_rules as $rule ) {
		$counter++;
		$oid = htmlspecialchars( $rule->getOID() );
		$desc = htmlspecialchars( $rule->getName() );

		if ( $viewvalue==null || $viewvalue==($rule->getName() )) {

			if( ! is_null( $viewvalue ) )
				$viewed = true;
			if( null != $rule->getDescription() )
				$desc .= ' (' . $rule->getDescription() . ')';
			if( true === $rule->getIsObsolete() )
				$desc .= ' <span style="color:red">' . $lang['obsolete'] . '</span>';

			echo "<tr class=\"" . ($counter%2==0?'even':'odd'). "\">";
			echo "<td>$oid</td>";
			echo "<td>$desc</td>";
			echo "<td>";

			if( count( $rule->getUsedByAttrs() ) == 0 ) {
				echo "<center>(" . $lang['none'] . ")</center><br /><br />\n";
			} else {
				echo "<table><tr><td style=\"text-align: right\"><form>";
       	                 echo "<input type=\"hidden\" name=\"server_id\" value=\"".$server_id."\" />";
				echo "<input type=\"hidden\" name=\"view\" value=\"attributes\" />";
				echo "<select style=\"width: 150px; color:black; background-color: #eee\" size=\"4\" name=\"viewvalue\">\n";
				foreach( $rule->getUsedByAttrs() as $attr )
					echo "<option>$attr</option>\n";
				echo "</select><br /><input type=\"submit\" value=\"".$lang['go']."\" /></form></td></tr></table>\n";
			}
			echo "</td></tr>\n";
		}
	}
	echo "</table>\n";

} elseif( $view == 'objectClasses' ) {
	flush();

	$schema_oclasses = get_schema_objectclasses( $ldapserver, null, true );
	if( ! $schema_oclasses ) pla_error( $schema_error_str ); ?>

	<small><?php echo $lang['jump_to_objectclass']; ?>:</small>
	<form><input type="hidden" name="view" value="<?php echo $view; ?>" />
        <input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<select name="viewvalue"
	onChange="submit()">
        <option value=""> - all - </option>

	<?php foreach( $schema_oclasses as $name => $oclass ) {
		printf( '<option value="%s" %s>%s</option>',
			$oclass->getName(),
			( 0 == strcasecmp( $oclass->getName(), $viewvalue ) ? ' selected ':''),
			$oclass->getName());

	} ?>

        </select><input type="submit" value="<?php echo $lang['go']; ?>" />
        </form>

        <?php flush();

	foreach( $schema_oclasses as $name => $oclass ) {
		foreach( $oclass->getSupClasses() as $parent_name ) {
			$parent_name = $parent_name;
			if( isset( $schema_oclasses[ $parent_name ] ) ) {
				$schema_oclasses[ $parent_name ]->addChildObjectClass( $oclass->getName() );
			}
		}
	} ?>

	<br />

	<?php foreach( $schema_oclasses as $name => $oclass ) {
		if ( $viewvalue==null || 0 == strcasecmp( $viewvalue, $oclass->getName() ) ) {
			if( ! is_null( $viewvalue ) )
				$viewed = true;
        ?>

		<h4 class="oclass"><a name="<?php echo $name; ?>"><?php echo $oclass->getName(); ?></a></h4>
		<h4 class="oclass_sub"><?php echo $lang['OID']; ?>: <b><?php echo $oclass->getOID(); ?></b></h4>

		<?php if( $oclass->getDescription() ) { ?>
			<h4 class="oclass_sub"><?php echo $lang['desc']; ?>: <b><?php echo $oclass->getDescription(); ?></b></h4>
		<?php } ?>

		<h4 class="oclass_sub"><?php echo $lang['type']; ?>: <b><?php echo $oclass->getType(); ?></b></h4>

		<?php if( $oclass->getIsObsolete() == true ) { ?>
			<h4 class="oclass_sub"><?php echo $lang['is_obsolete']; ?></h4>
		<?php } ?>

		<h4 class="oclass_sub"><?php echo $lang['inherits']; ?>: <b><?php

		if( count( $oclass->getSupClasses() ) == 0 )
			echo "(" . $lang['none'] . ")";

		else
			foreach( $oclass->getSupClasses() as $i => $object_class ) {
				echo '<a title="' . $lang['jump_to_this_oclass'] . ' "
					href="?server_id='.$server_id.'&amp;view='.$view.'&amp;viewvalue='.htmlspecialchars( $object_class ) ;
				echo '">' . htmlspecialchars( $object_class ) . '</a>';

				if( $i < count( $oclass->getSupClasses() ) - 1 )
					echo ', ';
			}
		?>
</b></h4>

		<h4 class="oclass_sub"><?php echo $lang['parent_to']; ?>: <b><?php

		if( 0 == strcasecmp( $oclass->getName(), 'top' ) )
			echo "(<a href=\"schema.php?view=objectClasses&amp;server_id=$server_id\">all</a>)";

		elseif( count( $oclass->getChildObjectClasses() ) == 0 )
			echo "(" . $lang['none'] . ")";

		else
			foreach( $oclass->getChildObjectClasses() as $i => $object_class ) {
				echo '<a title="' . $lang['jump_to_this_oclass'] . ' "
					href="?server_id='.$server_id.'&view='.$view.'&amp;viewvalue='.htmlspecialchars( $object_class ) ;
				echo '">' . htmlspecialchars( $object_class ) . '</a>';

				if( $i < count( $oclass->getChildObjectClasses() ) - 1 )
					echo ', ';
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
			<?php if( count( $oclass->getMustAttrs($schema_oclasses) ) > 0 ) {
				echo '<ul class="schema">';

				foreach( $oclass->getMustAttrs($schema_oclasses) as $attr ) {
					echo "<li><a href=\"?server_id=$server_id&amp;view=attributes&amp;viewvalue=";
					echo rawurlencode( $attr->getName()  ). "\">" . htmlspecialchars($attr->getName());
					echo "</a>";

					if( $attr->getSource() != $oclass->getName() ) {
						echo "<br /><small>&nbsp;&nbsp;(".$lang['inherited_from']." ";
						echo "<a href=\"?server_id=$server_id&amp;view=objectClasses&amp;viewvalue=" . $attr->getSource()  . "\">" . $attr->getSource() . "</a>";
						echo ")</small>";
					}

					echo "</li>\n";
				}
			} else
				echo "<center>(" . $lang['none'] . ")</center>\n";
			?>
		</ul>
		</td>
		<td width="50%">

		<?php if( count( $oclass->getMayAttrs($schema_oclasses) ) > 0 ) {
			echo '<ul class="schema">';

			foreach( $oclass->getMayAttrs($schema_oclasses) as $attr ) {
				echo "<li><a href=\"?server_id=$server_id&amp;view=attributes&amp;viewvalue=";
				echo rawurlencode( $attr->getName() ) . "\">" . htmlspecialchars($attr->getName() );
				echo "</a>\n";

				if( $attr->getSource() != $oclass->getName() ) {
					echo "<br /><small>&nbsp;&nbsp; (".$lang['inherited_from']." ";
					echo "<a href=\"?server_id=$server_id&amp;view=objectClasses&amp;viewvalue=" . $attr->getSource()  . "\">" . $attr->getSource() . "</a>";
					echo ")</small>";
				}

				echo "</li>";
			}
		} else
			echo "<center>(" . $lang['none'] . ")</center>\n";
	?>

	</ul>
	</td>
	</tr>
	</table>

		<?php }
	} /* End foreach objectClass */

} /* End else (displaying objectClasses */

if( ! is_null( $viewvalue ) && ! $viewed )
	pla_error( sprintf( $lang['no_such_schema_item'], htmlspecialchars( $viewvalue ) ) ); ?>

</body>
</html>
