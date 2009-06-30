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
 
require 'config.php';
require_once 'functions.php';

$server_id = $_GET['server_id'];
$view = isset( $_GET['view'] ) ? $_GET['view'] : 'objectClasses';

check_server_id( $server_id ) or 
	pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or 
	pla_error( "Not enough information to login to server. Please check your configuration." );
pla_ldap_connect( $server_id ) or 
	pla_error( "Coult not connect to LDAP server." );

$schema = get_schema( $server_id );

include 'header.php';

?>

<body>

<?php  if( ! $schema ) {

	pla_error( "Could not retrieve schema from <b>" . htmlspecialchars($servers[$server_id]['name']) . "</b>.<br />
		<br />
		This could happen for several reasons, the most probable of which are:
		<ul>
			<li>The server does not fully support the LDAP protocol.</li>
			<li>Your version of PHP does not correctly perform the query.</li>
			<li>Or lastly, phpLDAPAdmin doesn't know how to fetch the schema for your server.</li>
		</ul>
		Please <a href=\"http://sourceforge.net/tracker/?func=add&group_id=80217&atid=559082\">report this</a>
		as a bug.
		" );

} else { ?>

	<h3 class="title">Schema for server <b><?php echo htmlspecialchars($servers[$server_id]['name']); ?></b></h3>

	<br />
	<center>
		<?php echo ( $view=='objectClasses' ?
			'objectClasses' :
			'<a href="schema.php?server_id=' . $server_id . '&amp;view=objectClasses">objectClasses</a>' ); ?>
			|
		<?php echo ( $view=='syntaxes' ?
			'Syntaxes' :
			'<a href="schema.php?server_id=' . $server_id . '&amp;view=syntaxes">Syntaxes</a>' ); ?>
			|
		<?php echo ( $view=='attributes' ?
			'Attributes' :
			'<a href="schema.php?server_id=' . $server_id . '&amp;view=attributes">Attributes</a>' ); ?>
			|
		<?php echo ( $view=='matching_rules' ?
			'Matching Rules' :
			'<a href="schema.php?server_id=' . $server_id . '&amp;view=matching_rules">Matching Rules</a>' ); ?>
	</center>
	<br />

	<?php  flush(); ?>

	
	<?php
		
	if( $view == 'attr' )
	{
		$attr = $_GET['attr'];
		if( ! isset( $_GET['attr'] ) )
			pla_error( "No attribute specified in the query string." );

		//echo '<pre>'; print_r( $schema['attrs'] ); 	
		?>

		<center>
		Attribute definition for <b><?php echo htmlspecialchars( $attr ); ?></b><br /><br />
		<table class="schema_attr">

		<?php
		if( is_array( $schema['attrs'][strtolower($attr)] ) )
		{
			$counter = 0;
			foreach( $schema['attrs'][strtolower($attr)] as $key => $val )
			{
				if( $key != 'val' && $val != null )  {
					$counter++;
					echo "<tr class=\"" . ($counter%2==0 ? 'even' : 'odd') . "\"><td>$key</td><td>$val</td></tr>\n";
				}
			}
		}
		else 
			pla_error( "Bad schema entry for attribute: " . htmlspecialchars( $attr ) );

		?>

		</table>
		</center>
		
		<?php

	} elseif( $view == 'syntaxes' ) {
		echo "<center>The following <b>syntaxes</b> are supported by this LDAP server</center><br />\n\n";
		echo "\n\n<table class=\"schema_attr\" width=\"100%\">\n";
		echo "<tr><th>Syntax OID</th><th>Description</th></tr>\n";
		$counter=1;
		foreach( get_schema_syntaxes( $server_id ) as $oid => $desc ) {
			$counter++;
			$oid = htmlspecialchars( $oid );
			$desc = htmlspecialchars( $desc['description'] );
			echo "<tr class=\"" . ($counter%2==0?'even':'odd'). "\"><td>$oid</td><td>$desc</td></tr>\n";
		}
		echo "</table>\n";

	} elseif( $view == 'attributes' ) {
		echo "<center>The following <b>attributes</b> are supported by this LDAP server</center><br />\n\n";
		echo "<table class=\"schema_attr\" width=\"100%\">\n";
		foreach( get_schema_attributes( $server_id ) as $attr ) {
			echo "<tr><th colspan=\"2\">" . $attr['name'] . "</th></tr>\n";
			$counter = 0;
			foreach( $attr as $key => $val )
			{
				if( $key != 'val' && $val != null )  {
					$counter++;
					echo "<tr class=\"" . ($counter%2==0 ? 'even' : 'odd') . "\">";
					echo "<td>$key</td><td>$val</td></tr>\n";
				}
			}
		}
		echo "</table>\n";
	} elseif( $view == 'matching_rules' ) {
		echo "<center>The following <b>matching rules</b> are supported by this LDAP server</center><br />\n\n";
		echo "\n\n<table class=\"schema_attr\" width=\"100%\">\n";
		echo "<tr><th>Matching Rule OID</th><th>Description</th></tr>\n";
		$counter=1;
		foreach( get_schema_matching_rules( $server_id ) as $oid => $attr ) {
			$counter++;
			$oid = htmlspecialchars( $oid );
			$desc = htmlspecialchars( $attr );
			echo "<tr class=\"" . ($counter%2==0?'even':'odd'). "\"><td>$oid</td><td>$attr</td></tr>\n";
		}
		echo "</table>\n";


		
	} else { ?>
		
		<small>Jump to an objectClass:</small>
		<select name="oclass_jumper"
			onChange="window.location.href='schema.php?server_id=<?php echo $server_id; ?>#'+this.value">
				
			<?php foreach( $schema['oclasses'] as $oclass => $desc ) { ?>
				<option value="<?php echo $oclass; ?>"><?php echo $desc['name']; ?></option>
			<?php } ?>
			
		</select>
		<br />

		<?php foreach( $schema['oclasses'] as $oclass => $attrs ) { ?>
			
			<small>[<a name="<?php echo $oclass; ?>" href="#" title="Head on up to the top.">top</a>]</small>
			<h4 class="oclass">objectClass <b><?php echo $attrs['name']; ?></b></h4>
			<h4 class="oclass_sub">OID <b><?php echo $attrs['oid']; ?></b></h4>
			<?php if( $attrs['description'] ) { ?>
				<h4 class="oclass_sub">Description <b><?php echo $attrs['description']; ?></b></h4>
			<?php } ?>
			<h4 class="oclass_sub">Inherits <b><?php echo $attrs['sup']; ?></b></h4>
			<br />
			<table width="100%" class="schema_oclasses">
			<tr>
			<th width="50%"><b>Required Attributes</b></th>
			<th width="50%"><b>Optional Attributes</b></th>
			</tr>
			<tr>
			<td>
			<?php 
				
			if( count( $attrs['must_attrs'] ) > 0 ) {
				echo '<ul class="schema">';
				foreach( $attrs['must_attrs'] as $attr ) 
					echo "<li><a href=\"schema.php?server_id=$server_id&amp;view=attr&amp;attr=" .
						rawurlencode( $attr ) . "\">" . htmlspecialchars($attr) . "</a></li>\n";
			}
			else				
				echo "<center>(none)</center>\n";

			?>

			</ul>
			</td>
			<td width="50%">
			<?php 
				
			if( count( $attrs['may_attrs'] ) > 0 ) {
				echo '<ul class="schema">';
				foreach( $attrs['may_attrs'] as $attr ) 
					echo "<li><a href=\"schema.php?server_id=$server_id&amp;view=attr&amp;attr=" .
						rawurlencode( $attr ) . "\">" . htmlspecialchars($attr) . "</a></li>\n";
			}
			else				
				echo "<center>(none)</center>\n";

			?>
				
			</ul>
			</td>
			</tr>
			</table>
			
		<?php  } /* End foreach objectClass */ ?>
	<?php } /* End else (displaying objectClasses */ ?>
<?php } /* End else (schema _is_ available) */ ?>

</body>

</html>
