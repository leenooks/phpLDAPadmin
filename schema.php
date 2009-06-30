<?php

/* 
 * schema.php
 * Displays the schema for the specified server_id
 * 
 * Variables that come in as GET vars:
 *  - server_id
 *  - view (optional: can be 'attr' or empty. If 'attr', show that attribute)
 *  - attr (optional)
 */
 
require 'common.php';

$server_id = $_GET['server_id'];
$view = isset( $_GET['view'] ) ? $_GET['view'] : 'objectClasses';

check_server_id( $server_id ) or 
	pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or 
	pla_error( "Not enough information to login to server. Please check your configuration." );
pla_ldap_connect( $server_id ) or 
	pla_error( "Coult not connect to LDAP server." );

include 'header.php';

$schema_error_str = "Could not retrieve schema from <b>" . htmlspecialchars($servers[$server_id]['name']) . "</b>.<br />
		<br />
		</center>
		This could happen for several reasons, the most probable of which are:
		<ul>
			<li>The server does not fully support the LDAP protocol.</li>
			<li>Your version of PHP does not correctly perform the query.</li>
			<li>Or lastly, phpLDAPadmin doesn't know how to fetch the schema for your server.</li>
		</ul>
		Please <a href=\"https://sourceforge.net/tracker/?func=add&group_id=61828&atid=498546\" target=\"new\">
		report this as a bug</a>";

?>

<body>

<h3 class="title"><?php echo $lang['schema_for_server']; ?> 
	<b><?php echo htmlspecialchars($servers[$server_id]['name']); ?></b></h3>

<br />
<center>
	<?php echo ( $view=='objectClasses' ?
		'objectClasses' :
		'<a href="schema.php?server_id=' . $server_id . '&amp;view=objectClasses">objectClasses</a>' ); ?>
		|
	<?php echo ( $view=='attributes' ?
		'Attributes' :
		'<a href="schema.php?server_id=' . $server_id . '&amp;view=attributes">Attributes</a>' ); ?>
		|
	<?php echo ( $view=='syntaxes' ?
		'Syntaxes' :
		'<a href="schema.php?server_id=' . $server_id . '&amp;view=syntaxes">Syntaxes</a>' ); ?>
		|
	<?php echo ( $view=='matching_rules' ?
		'Matching Rules' :
		'<a href="schema.php?server_id=' . $server_id . '&amp;view=matching_rules">Matching Rules</a>' ); ?>
</center>
<br />

<?php flush(); ?>

<?php

if( $view == 'syntaxes' ) {
	$highlight_oid = isset( $_GET['highlight_oid'] ) ? $_GET['highlight_oid'] : false;
	echo "<center>" . $lang['the_following_syntaxes'] . "</center><br />\n\n";
	echo "\n\n<table class=\"schema_attr\" width=\"100%\">\n";
	echo "<tr><th>" . $lang['syntax_oid'] . "</th><th>" . $lang['desc'] . "</th></tr>\n";
	flush();
	$counter=1;
	$schema_syntaxes = get_schema_syntaxes( $server_id ); 
	if( ! $schema_syntaxes ) pla_error( $schema_error_str );
	foreach( $schema_syntaxes as $oid => $desc ) {
		$counter++;
		$oid = htmlspecialchars( $oid );
		$desc = htmlspecialchars( $desc['description'] );
		if( $highlight_oid && $highlight_oid == $oid )
			echo "<tr class=\"highlight\">";
		else
			echo "<tr class=\"" . ($counter%2==0?'even':'odd'). "\">";
		echo "<td><a name=\"$oid\">$oid</a></td><td>$desc</td></tr>\n\n";
	}
	echo "</table>\n";

} elseif( $view == 'attributes' ) {
	echo "<center>" . $lang['the_following_attributes'] . "</center><br />\n";
	flush();
	$schema_attrs = get_schema_attributes( $server_id );
	$schema_object_classes = get_schema_objectclasses( $server_id );
	if( ! $schema_attrs || ! $schema_object_classes ) 
		pla_error( $schema_error_str );

	// do a reverse-mapping to add in which objectClasses each attributeType is used
	foreach( $schema_object_classes as $object_class ) {
		if( isset( $object_class['must_attrs'] ) && is_array( $object_class['must_attrs'] ) )
			$must_attrs = $object_class['must_attrs'];
		else
			$must_attrs = array();
		if( isset( $object_class['may_attrs'] ) && is_array( $object_class['may_attrs'] ) )
			$may_attrs = $object_class['may_attrs'];
		else
			$may_attrs = array();
		$attrs = array_unique( array_merge( $must_attrs, $may_attrs ) );
		foreach( $attrs as $attr_name )
			if( isset( $schema_attrs[ strtolower( $attr_name ) ] ) )
				$schema_attrs[ strtolower( $attr_name ) ]->addUsedInObjectClass( $object_class['name'] );
	}
	
	//echo "<pre>";
	//print_r( $schema_attrs );
	//echo "</pre>";

	?>
	<small><?php echo $lang['jump_to_attr']; ?>:</small>
	<select name="oclass_jumper"
	onChange="window.location.href='schema.php?server_id=<?php echo $server_id; ?>&amp;view=attributes#'+this.value">

	<?php foreach( $schema_attrs as $attr ) { ?>
		<option value="<?php echo strtolower( $attr->getName() ); ?>"><?php echo $attr->getName(); ?></option>
	<?php } ?>
	</select>

	<br />
	<table class="schema_attr" width="100%">
	<?php foreach( $schema_attrs  as $attr ) {
		echo "<tr><th colspan=\"2\"><a name=\"" . strtolower( $attr->getName() ) . "\">";
		echo $attr->getName() . "</a></th></tr>\n\n";
		$counter = 0;

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Description</td>\n";
		echo "<td>" . ( $attr->getDescription() == null ? '(no description)' : $attr->getDescription() ). "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td><acronym title=\"Object Identier\">OID</acronym></td>\n";
		echo "<td>" .  $attr->getOID() . "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Obsolete?</td>\n";
		echo "<td>" . ( $attr->getIsObsolete() ? '<b>Yes</b>' : 'No' ) . "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Inherits</td>\n";
		echo "<td>" .  ( $attr->getSupAttribute()==null? '(none)' : $attr->getSupAttribute() )  . "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Equality</td>\n";
		echo "<td>" .  ( $attr->getEquality() == null ? '(not specified)' : $attr->getEquality() ) . "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Ordering</td>\n";
		echo "<td>" .  ( $attr->getOrdering()==null? '(not specified)' : $attr->getOrdering() ) . "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Substring Rule</td>\n";
		echo "<td>" .  ( $attr->getSubstr()==null? '(not specified)' : $attr->getSubstr() ) . "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Syntax</td>\n";
		echo "<td>";
		if( null != $attr->getType() ) {
			echo "<a href=\"schema.php?server_id=$server_id&amp;view=syntaxes&amp;highlight_oid=";
			echo $attr->getSyntaxOID() . "#" .  $attr->getSyntaxOID();
			echo "\">" . $attr->getType() . " (" . $attr->getSyntaxOID() . ")</a>";
		} else {
			echo $attr->getSyntaxOID();
		}
		echo "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Single Valued</td>\n";
		echo "<td>" .  ( $attr->getIsSingleValue() ? 'Yes' : 'No' ) . "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Collective?</td>\n";
		echo "<td>" .  ( $attr->getIsCollective() ? 'Yes' : 'No' ) . "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>User Modification</td>\n";
		echo "<td>" . ( $attr->getIsNoUserModification() ? 'No' : 'Yes' ) . "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Usage</td>\n";
		echo "<td>" .  ( $attr->getUsage() ? $attr->getUsage() : '(not specified)' ) . "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Maximum Length</td>\n";
		echo "<td>" .  ( $attr->getMaxLength() === null ? 
					'(not applicable)' : 
					number_format( $attr->getMaxLength() ) . ' characters' ) . "</td>\n";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Aliases</td>\n";
		echo "<td>"; 
		if( count( $attr->getAliases() ) == 0 )
			echo "(none)";
		else
			foreach( $attr->getAliases() as $alias_attr_name )
				echo "<a href=\"#" . strtolower($alias_attr_name) . "\">$alias_attr_name</a> ";
		echo "</td>";
		echo "</tr>\n\n";

		echo "<tr class=\"" . (++$counter%2==0?'even':'odd') . "\">\n";
		echo "<td>Used by objectClasses</td>\n";
		echo "<td>";
		if( count( $attr->getUsedInObjectClasses() ) == 0 )
			echo "(none)";
		else
			foreach( $attr->getUsedInObjectClasses() as $used_in_oclass)
				echo "<a href=\"schema.php?server_id=$server_id&amp;view=objectClasses#" . 
					strtolower($used_in_oclass) . "\">$used_in_oclass</a> ";
		echo "</td>";
		echo "</tr>\n\n";

		flush();
	}
	echo "</table>\n";

} elseif( $view == 'matching_rules' ) {
	echo "<center>" . $lang['the_following_matching'] . "</center><br />\n\n";
	echo "\n\n<table class=\"schema_attr\" width=\"100%\">\n";
	echo "<tr><th>" . $lang['matching_rule_oid'] . "</th><th>" . $lang['desc'] . "</th></tr>\n";
	flush();
	$counter=1;
	$schema_matching_rules = get_schema_matching_rules( $server_id ); 
	if( ! $schema_matching_rules ) pla_error( $schema_error_str );
	foreach( $schema_matching_rules as $oid => $attr ) {
		$counter++;
		$oid = htmlspecialchars( $oid );
		$desc = htmlspecialchars( $attr );
		echo "<tr class=\"" . ($counter%2==0?'even':'odd'). "\"><td>$oid</td><td>$attr</td></tr>\n";
	}
	echo "</table>\n";

} else { 
	echo "<center>" . $lang['the_following_objectclasses'] . "</center><br />\n";
	flush();
	$schema_oclasses = get_schema_objectclasses( $server_id );
	if( ! $schema_oclasses ) pla_error( $schema_error_str );
	?>
	<small><?php echo $lang['jump_to_objectclass']; ?>:</small>
	<select name="oclass_jumper"
	onChange="window.location.href='schema.php?server_id=<?php echo $server_id; ?>#'+this.value">
	<?php foreach( $schema_oclasses as $oclass => $desc ) { ?>
		<option value="<?php echo $oclass; ?>"><?php echo $desc['name']; ?></option>
	<?php } ?>
	</select>
	<br />
	<?php foreach( $schema_oclasses as $oclass => $attrs ) { ?>
		<!--<small>[<a name="<?php echo $oclass; ?>" href="#" title="Head on up to the top.">top</a>]</small>-->
		<h4 class="oclass"><a name="<?php echo $oclass; ?>"><?php echo $attrs['name']; ?></a></h4>
		<h4 class="oclass_sub"><?php echo $lang['OID']; ?>: <b><?php echo $attrs['oid']; ?></b></h4>
		<?php if( $attrs['description'] ) { ?>
			<h4 class="oclass_sub"><?php echo $lang['desc']; ?>: <b><?php echo $attrs['description']; ?></b></h4>
		<?php } ?>
		<h4 class="oclass_sub">Type: <b><?php echo $attrs['type']; ?></b></h4>
		<?php if( isset( $attrs['is_obsolete'] ) && $attrs['is_obsolete'] == true ) { ?>
			<h4 class="oclass_sub"><?php echo $lang['is_obsolete']; ?></h4>
		<?php } ?>

		<h4 class="oclass_sub"><?php echo $lang['inherits']; ?>: <b><?php 
		if( count( $attrs['sup'] ) == 0 )
			echo "(" . $lang['none'] . ")";
		else
			foreach( $attrs['sup'] as $i => $object_class ) {
				echo '<a title="' . $lang['jump_to_this_oclass'] . ' " 
					href="#' . strtolower( htmlspecialchars( $object_class ) );
				echo '">' . htmlspecialchars( $object_class ) . '</a>';
				if( $i < count( $attrs['sup'] ) - 1 )
					echo ', ';
		}
		?></b></h4>

		<table width="100%" class="schema_oclasses">
		<tr>
			<th width="50%"><b><?php echo $lang['required_attrs']; ?></b></th>
			<th width="50%"><b><?php echo $lang['optional_attrs']; ?></b></th>
		</tr>
		<tr>
			<td>
			<?php if( count( $attrs['must_attrs'] ) > 0 ) {
				echo '<ul class="schema">';
				foreach( $attrs['must_attrs'] as $attr ) 
				echo "<li><a href=\"schema.php?server_id=$server_id&amp;view=attributes#" .
					strtolower( rawurlencode( $attr ) ). "\">" . htmlspecialchars($attr) . "</a></li>\n";
			} else				
				echo "<center>(" . $lang['none'] . ")</center>\n";
			?>
		</ul>
		</td>
		<td width="50%">
		<?php 
		if( count( $attrs['may_attrs'] ) > 0 ) {
		echo '<ul class="schema">';
		foreach( $attrs['may_attrs'] as $attr ) 
			echo "<li><a href=\"schema.php?server_id=$server_id&amp;view=attributes#" .
				strtolower( rawurlencode( $attr ) ) . "\">" . htmlspecialchars($attr) . "</a></li>\n";
		}
		else				
			echo "<center>(" . $lang['none'] . ")</center>\n";
	?>

	</ul>
	</td>
	</tr>
	</table>

	<?php  } /* End foreach objectClass */ ?>
<?php } /* End else (displaying objectClasses */ ?>

</body>

</html>
