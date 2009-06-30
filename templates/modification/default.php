<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/templates/modification/default.php,v 1.70 2004/12/20 14:12:33 uugdave Exp $
 

/*
 * edit.php
 * Displays the specified dn from the specified server for editing
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - server_id
 *  - modified_attrs (optional) an array of attributes to highlight as 
 *                              they were changed by the last operation
 */

/** If an entry has more children than this, stop searching and display this amount with a '+' */
$max_children = 100;

isset( $dn ) or $dn = isset( $_GET['dn'] ) ? $_GET['dn'] : null;
$encoded_dn = rawurlencode( $dn );
$modified_attrs = isset( $_GET['modified_attrs'] ) ? $_GET['modified_attrs'] : false;
isset( $server_id ) or $server_id = $_GET['server_id'];
$show_internal_attrs = isset( $_GET['show_internal_attrs'] ) ? true : false;
if( null != $dn ) {
	$rdn = pla_explode_dn( $dn );
	if( isset( $rdn[0] ) )
		$rdn = $rdn[0];
	else
		$rdn = null;
} else {
	$rdn = null;
}

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );
pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );
$friendly_attrs = process_friendly_attr_table();
if( ! isset( $attrs ) )
	$attrs = get_object_attrs( $server_id, $dn, false, get_view_deref_setting() );

pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );
$system_attrs = get_entry_system_attrs( $server_id, $dn, get_view_deref_setting() );
dn_exists( $server_id, $dn ) or pla_error( sprintf( $lang['no_such_entry'], pretty_print_dn( $dn ) ) );

$server_name = $servers[$server_id]['name'];

include 'header.php'; ?>
<body>

<h3 class="title"><?php echo htmlspecialchars( ( $rdn ) ); ?></h3>
<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name'];?>: <b><?php echo htmlspecialchars( ( $dn ) ); ?></b></h3>

<table class="edit_dn_menu">

<tr>
	<?php  $time = gettimeofday(); $random_junk = md5( strtotime( 'now' ) . $time['usec'] ); ?>
	<td class="icon"><img src="images/refresh.png" /></td>
	<td><a href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>&amp;random=<?php
			echo $random_junk; ?>"
	       title="<?php echo $lang['refresh_this_entry']; ?>"><?php echo $lang['refresh_entry']; ?></a></td>

	<td class="icon"><img src="images/save.png" /></td>
	<?php $export_url = "export_form.php?server_id=$server_id&amp;dn=$encoded_dn&amp;scope=base"; ?>
	<td><a href="<?php echo $export_url; ?>" title="<?php echo $lang['export_tooltip']; ?>"><?php echo $lang['export']; ?></a></td>
</tr>

<tr>
	<td class="icon"><img src="images/cut.png" /></td>
	<td><a href="copy_form.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn?>"
	     title="<?php echo $lang['copy_this_entry_tooltip']; ?>"><?php echo $lang['copy_this_entry']; ?></a></td>
<?php if( $show_internal_attrs ) { ?>
    <td class="icon"><img src="images/tools-no.png" /></td>
    <td><a href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>"><?php echo $lang['hide_internal_attrs']; ?></a></td>
<?php } else { ?>
    <td class="icon"><img src="images/tools.png" /></td>
    <td><a href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>&amp;show_internal_attrs=true"><?php echo $lang['show_internal_attrs']; ?></a></td>
<?php } ?>
</tr>

<?php if( ! is_server_read_only( $server_id ) ) { ?>
<tr>
	<td class="icon"><img src="images/trash.png" /></td>
	<td><a style="color: red" href="delete_form.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>"
	       title="<?php echo $lang['delete_this_entry_tooltip']; ?>"><?php echo $lang['delete_this_entry']; ?></a></td>
	<td class="icon"><img src="images/rename.png" /></td>
	<td><a href="rename_form.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>"><?php echo $lang['rename']; ?></a></td>
    <?php if( show_hints() ) { ?>
    </tr>
    <tr>
    	<td class="icon"><img src="images/light.png" /></td>
    	<td colspan="3"><span class="hint"><?php echo $lang['delete_hint']; ?></span></td>
    </tr>
    <?php } ?>
<tr>
	<td class="icon"><img src="images/star.png" /></td>
	<td><a href="<?php echo "create_form.php?server_id=$server_id&amp;container=$encoded_dn"; ?>"><?php echo $lang['create_a_child_entry']; ?></a></td>
	<td class="icon"><img src="images/add.png" /></td>
	<td><a href="<?php echo "add_attr_form.php?server_id=$server_id&amp;dn=$encoded_dn"; ?>"><?php echo $lang['add_new_attribute']; ?></a></td>
</tr>
<?php } ?>


<?php flush(); ?>
<?php $children = get_container_contents( $server_id, $dn, $max_children, '(objectClass=*)', get_view_deref_setting() ); 

if( ($children_count = count( $children ) ) > 0 ) { 
	if( $children_count == $max_children )
		$children_count = $children_count . '+';

?>

<tr>
	<td class="icon"><img src="images/children.png" /></td>
	<td><a href="search.php?search=true&amp;server_id=<?php echo $server_id; ?>&amp;filter=<?php echo rawurlencode('objectClass=*'); ?>&amp;base_dn=<?php echo $encoded_dn; ?>&amp;form=advanced&amp;scope=one"><?php 
		if( $children_count == 1 ) 
			echo $lang['view_one_child']; 
		else 
			echo sprintf( $lang['view_children'], $children_count ); ?></a></td>
	<td class="icon"><img src="images/save.png" /></td>
	<?php $export_url = "export_form.php?server_id=$server_id&amp;dn=$encoded_dn&amp;scope=sub"; ?>
	<td><a href="<?php echo $export_url; ?>" 
	       title="<?php echo $lang['export_subtree_tooltip']; ?>"><?php echo $lang['export_subtree']; ?></a> 
	</td>
</tr> 

<?php } ?> <?php if( show_hints() ) { ?> 
<tr> 
    <td class="icon"><img src="images/light.png" /></td>
	<td colspan="3"><span class="hint"><?php echo $lang['attr_schema_hint']; ?></span></td>
</tr>
<?php } ?>

<?php if( is_server_read_only( $server_id ) ) { ?>
<tr>
	<td class="icon"><img src="images/light.png" /></td>
	<td><?php echo $lang['viewing_read_only']; ?></td>
</tr>
<?php } ?>

<?php if( $modified_attrs ) { ?>
<tr>
	<td class="icon"><img src="images/light.png" /></td>
	<?php if( count( $modified_attrs ) > 1 ) { ?>
		<td colspan="3"><?php echo sprintf( $lang['attrs_modified'], implode( ', ', $modified_attrs ) ); ?></td>
	<?php } else { ?>
		<td colspan="3"><?php echo sprintf( $lang['attr_modified'], implode( '', $modified_attrs ) ); ?></td>
	<?php } ?>
</tr>
<?php 
    // lower-case all the modified attrs to remove ambiguity when searching the array later
    foreach( $modified_attrs as $i => $attr ) {
        $modified_attrs[$i] = strtolower( $attr );
    }
}
?>

</table>

<?php flush(); ?>

<br />
<table class="edit_dn">

<?php
if( $show_internal_attrs ) {
	$counter = 0;
	foreach( get_entry_system_attrs( $server_id, $dn ) as $attr => $vals ) {
		$counter++;
		$schema_href = "schema.php?server_id=$server_id&amp;view=attributes&amp;viewvalue=" . real_attr_name($attr);
		?>

		<tr>
		<td colspan="2" class="attr"><b><a title="<?php echo sprintf( $lang['attr_name_tooltip'], $attr ); ?>" 
							   href="<?php echo $schema_href; ?>"><?php echo htmlspecialchars( $attr ); ?></b></td>
        </tr>
        <tr>
		<td class="val">
        <?php 
           if( is_attr_binary( $server_id, $attr ) ) {
               $href = "download_binary_attr.php?server_id=$server_id&amp;dn=$encoded_dn&amp;attr=$attr";
               ?>
        		<small>
        		<?php echo $lang['binary_value']; ?><br />
        		<?php if( count( $vals ) > 1 ) { for( $i=1; $i<=count($vals); $i++ ) { ?>
        			<a href="<?php echo $href . "&amp;value_num=$i"; ?>"><img 
        				src="images/save.png" /> <?php echo $lang['download_value']; ?>(<?php echo $i; ?>)</a><br />
        		<?php } } else { ?>
        			<a href="<?php echo $href; ?>"><img src="images/save.png" /> <?php echo $lang['download_value']; ?></a><br />
        		<?php }
           }  else {
               foreach( $vals as $v ) {
                   echo htmlspecialchars( $v );
                   echo "<br />\n";
               }
           } ?>
		</td>
		</tr>
	<?php } 
	if( $counter == 0 )
		echo "<tr><td colspan=\"2\">(" . $lang['no_internal_attributes'] . ")</td></tr>\n";
}

?>

<?php flush(); ?>

<!-- Table of attributes/values to edit -->

<?php if( ! is_server_read_only( $server_id ) ) { ?>
	<form action="update_confirm.php" method="post" name="edit_form">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
<?php } ?>

<?php $counter=0; ?>

<?php

if( ! $attrs || ! is_array( $attrs ) ) {
    echo "<tr><td colspan=\"2\">(" . $lang['no_attributes'] . ")</td></tr>\n";
    echo "</table>";
    echo "</html>";
    die();
}

uksort( $attrs, 'sortAttrs' );
foreach( $attrs as $attr => $vals ) { 

	flush();

    $schema_attr = get_schema_attribute( $server_id, $attr, $dn );
    if( $schema_attr )
        $attr_syntax = $schema_attr->getSyntaxOID();
    else
        $attr_syntax = null;

	if( 0 == strcasecmp( $attr, 'dn' ) )
		continue;

	// has the config.php specified that this attribute is to be hidden or shown?
    if( is_attr_hidden( $server_id, $attr))
        continue;

    // Setup the $attr_note, which will be displayed to the right of the attr name (if any)
    $attr_note = '';

	// is there a user-friendly translation available for this attribute?
	if( isset( $friendly_attrs[ strtolower( $attr ) ] ) ) {
		$attr_display = $friendly_attrs[ strtolower( $attr ) ];
		$attr_note = "<acronym title=\"" . sprintf( $lang['alias_for'], $attr_display, $attr ) . "\">alias</acronym>";
	} else {
		$attr_note = "";
		$attr_display = $attr;
	}

	// is this attribute required by an objectClass?
	$required_by = '';
	if( $schema_attr )
		foreach( $schema_attr->getRequiredByObjectClasses() as $required )
			if( in_array( strtolower( $required ), arrayLower( $attrs['objectClass'] ) ) )
				$required_by .= $required . ' ';
	if( $required_by ) {
		if( trim( $attr_note ) )
			$attr_note .= ', ';
			$attr_note .= "<acronym title=\"" . sprintf( $lang['required_for'], $required_by ) . "\">" . $lang['required'] . "</acronym>&nbsp;";
	}
	?>

	<?php  
    if( is_array( $modified_attrs ) && in_array( strtolower($attr), $modified_attrs ) )
        $is_modified_attr = true;
    else
        $is_modified_attr = false;
    ?>

    <?php if( $is_modified_attr ) { ?>
		<tr class="updated_attr">
	<?php  } else { ?>
        <tr>
	<?php  } ?>

	<td class="attr">
		<?php $schema_href="schema.php?server_id=$server_id&amp;view=attributes&amp;viewvalue=" . real_attr_name($attr); ?>
		<b>
            <a
              title="<?php echo sprintf( $lang['attr_name_tooltip'], $attr ) ?>"
              href="<?php echo $schema_href; ?>"><?php echo $attr_display; ?></a></b>
	</td>
    <td class="attr_note">
		<sup><small><?php echo $attr_note; ?></small></sup>
        <?php if( is_attr_read_only( $server_id, $attr ) ) { ?>
            <small>(<acronym title="<?php echo $lang['read_only_tooltip']; ?>"><?php echo $lang['read_only']; ?></acronym>)</small>
        <?php } ?>
    </td>
    </tr>

    <?php if( $is_modified_attr ) { ?>
		<tr class="updated_attr">
	<?php  } else { ?>
        <tr>
	<?php  } ?>
	<td class="val" colspan="2">

	<?php 
	
	/*
	 * Is this attribute a jpegPhoto? 
	 */
	if( is_jpeg_photo( $server_id, $attr ) ) {
		
		// Don't draw the delete buttons if there is more than one jpegPhoto
		// 	(phpLDAPadmin can't handle this case yet)
		if( is_server_read_only( $server_id ) || is_attr_read_only( $server_id, $attr ) )
			draw_jpeg_photos( $server_id, $dn, $attr, false );
		else
			draw_jpeg_photos( $server_id, $dn, $attr, true );
			
		// proceed to the next attribute
        echo "</td></tr>\n";
        if( $is_modified_attr ) 
            echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';
		continue;
	} 


	/*
	 * Is this attribute binary?
	 */
	if( is_attr_binary( $server_id, $attr ) ) {
		$href = "download_binary_attr.php?server_id=$server_id&amp;dn=$encoded_dn&amp;attr=$attr";
		?>

		<small>
		<?php echo $lang['binary_value']; ?><br />
		<?php if( count( $vals ) > 1 ) { for( $i=1; $i<=count($vals); $i++ ) { ?>
			<a href="<?php echo $href . "&amp;value_num=$i"; ?>"><img 
				src="images/save.png" /> <?php echo $lang['download_value']; ?>(<?php echo $i; ?>)</a><br />
		<?php } } else { ?>
			<a href="<?php echo $href; ?>"><img src="images/save.png" /> <?php echo $lang['download_value']; ?></a><br />
		<?php } ?>

		<?php if( ! is_server_read_only( $server_id ) && ! is_attr_read_only( $server_id, $attr ) ) { ?>
		<a href="javascript:deleteAttribute( '<?php echo $attr; ?>' );"
			style="color:red;"><img src="images/trash.png" /> <?php echo $lang['delete_attribute']; ?></a>
		<?php } ?>

		</small>
		</td>
        </tr>

		<?php 
        if( $is_modified_attr ) 
            echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';
        continue; 
	}


	/*
	 * Note: at this point, the attribute must be text-based (not binary or jpeg)
	 */


	/*
	 * If this server is in read-only mode or this attribute is configured as read_only, 
	 * simply draw the attribute values and continue.
	 */
	if( is_server_read_only( $server_id ) || is_attr_read_only( $server_id, $attr ) ) {
		if( is_array( $vals ) ) { 
			foreach( $vals as $i => $val ) {
				if( trim( $val ) == "" )
					echo "<span style=\"color:red\">[" . $lang['empty'] . "]</span><br />\n";
				elseif( 0 == strcasecmp( $attr, 'userPassword' ) && obfuscate_password_display() )
					echo preg_replace( '/./', '*', $val ) . "<br />";
				else
					echo htmlspecialchars( $val ) . "<br />";
			}
		} else {
			if( 0 == strcasecmp( $attr, 'userPassword' ) && obfuscate_password_display() )
				echo preg_replace( '/./', '*', $vals ) . "<br />";
			else
				echo $vals . "<br />";
		}
		echo "</td>";
		echo "</tr>";
        if( $is_modified_attr ) 
            echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';
		continue;
	}
	
	/*
	 * Is this a userPassword attribute?
	 */
	if( 0 == strcasecmp( $attr, 'userpassword' ) ) { 
		$user_password = $vals[0];
        $enc_type = get_enc_type( $user_password );

        // Set the default hashing type if the password is blank (must be newly created)
        if( $user_password == '' ) {
            $enc_type = get_default_hash( $server_id );
        } 
        ?>

		<input type="hidden"
		       name="old_values[userpassword]" 
		       value="<?php echo htmlspecialchars($user_password); ?>" />

		<!-- Special case of enc_type to detect changes when user changes enc_type but not the password value -->
		<input size="38"
		       type="hidden"
		       name="old_enc_type"
		       value="<?php echo ($enc_type==''?'clear':$enc_type); ?>" />

        <?php if( obfuscate_password_display() || is_null( $enc_type ) )  {
                 echo htmlspecialchars( preg_replace( "/./", "*", $user_password ) );
              } else {
                 echo htmlspecialchars( $user_password );
              }
        ?>
        <br />
		<input style="width: 260px"
		       type="password"
		       name="new_values[userpassword]" 
               value="<?php echo htmlspecialchars( $user_password ); ?>" />

		<select name="enc_type">
			<option>clear</option>
			<option<?php echo $enc_type=='crypt'?' selected="true"':''; ?>>crypt</option>
			<option<?php echo $enc_type=='md5'?' selected="true"':''; ?>>md5</option>
			<option<?php echo $enc_type=='smd5'?' selected="true"':''; ?>>smd5</option>
			<option<?php echo $enc_type=='md5crypt'?' selected="true"':''; ?>>md5crypt</option>
			<option<?php echo $enc_type=='blowfish'?' selected="true"':''; ?>>blowfish</option>
			<option<?php echo $enc_type=='sha'?' selected="true"':''; ?>>sha</option>
			<option<?php echo $enc_type=='ssha'?' selected="true"':''; ?>>ssha</option>
			</select>

            <br />

            <script language="javascript">
            <!--
                function passwordComparePopup()
                {
                    mywindow = open( 'password_checker.php', 'myname', 'resizable=no,width=450,height=200,scrollbars=1' );
                    mywindow.location.href = 'password_checker.php?hash=<?php echo base64_encode($user_password); ?>&base64=true';
                    if( mywindow.opener == null ) 
                        mywindow.opener = self;
                }
            -->
            </script>
            <small><a href="javascript:passwordComparePopup()"><?php echo $lang['t_check_pass']; ?></a></small>

        </td></tr>

		<?php 
        if( $is_modified_attr ) 
            echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';
        continue; 
	} 
	
	/*
	 * Is this a boolean attribute? 
	 */
     if( is_attr_boolean( $server_id, $attr) ) {
		$val = $vals[0];
		?>

		<input type="hidden"
		       name="old_values[<?php echo htmlspecialchars( $attr ); ?>]" 
		       value="<?php echo htmlspecialchars($val); ?>" />

			<select name="new_values[<?php echo htmlspecialchars( $attr ); ?>]">
			<option value="TRUE"<?php echo ($val=='TRUE' ?  ' selected' : ''); ?>>
				<?php echo $lang['true']; ?></option>
			<option value="FALSE"<?php echo ($val=='FALSE' ? ' selected' : ''); ?>>
				<?php echo $lang['false']; ?></option>
			<option value="">(<?php echo $lang['none_remove_value']; ?>)</option>
		</select>
        </td>
        </tr>

		<?php 
        if( $is_modified_attr ) 
            echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';
		continue; 
	}

	/*
	 * End of special case attributes (non plain text).
	 */


	/*
	 * This is a plain text attribute, to be displayed and edited in plain text.
	 */
	foreach( $vals as $i => $val ) {

        $input_name = "new_values[" . htmlspecialchars( $attr ) . "][$i]";
        // We smack an id="..." tag in here that doesn't have [][] in it to allow the 
        // draw_chooser_link() to identify it after the user clicks.
        $input_id =  "new_values_" . htmlspecialchars($attr) . "_" . $i;

        ?>

		<!-- The old_values array will let update.php know if the entry contents changed
		     between the time the user loaded this page and saved their changes. -->
		<input type="hidden"
		       name="old_values[<?php echo htmlspecialchars( $attr ); ?>][<?php echo $i; ?>]" 
		       value="<?php echo htmlspecialchars($val); ?>" />
        <?php

		// Is this value is a structural objectClass, make it read-only
		if( 0 == strcasecmp( $attr, 'objectClass' ) ) {
            ?>
            <a
              title="<?php echo $lang['view_schema_for_oclass']; ?>"
              href="schema.php?server_id=<?php echo $server_id; ?>&amp;view=objectClasses&amp;viewvalue=<?php echo htmlspecialchars( $val ); ?>"><img
                src="images/info.png" /></a>
            <?php
			$schema_object = get_schema_objectclass( $server_id, $val);
			if ($schema_object->type == 'structural') {
				echo "$val <small>(<acronym title=\"" . 
                        sprintf( $lang['structural_object_class_cannot_remove'] ) . "\">" .
                        $lang['structural'] . "</acronym>)</small><br />";
                ?>
        	<input type="hidden"
		       	name="<?php echo $input_name; ?>"
                id="<?php echo $input_id; ?>"
         	    value="<?php echo htmlspecialchars($val); ?>" />
                <?php
                continue;
			}
		}

		?>
			       
        <?php if( is_dn_string( $val ) || is_dn_attr( $server_id, $attr ) ) { ?>
             <a 
                title="<?php echo sprintf( $lang['go_to_dn'], htmlspecialchars($val) ); ?>" 
                href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo rawurlencode($val); ?>"><img 
                        style="vertical-align: top" src="images/go.png" /></a>
        <?php } elseif( is_mail_string( $val ) ) { ?>
             <a 
                href="mailto:<?php echo htmlspecialchars($val); ?>"><img 
                        style="vertical-align: center" src="images/mail.png" /></a>
        <?php } elseif( is_url_string( $val ) ) { ?>
             <a 
                href="<?php echo htmlspecialchars($val); ?>"
                target="new"><img 
                        style="vertical-align: center" src="images/dc.png" /></a>

        <?php } ?>

        <?php if( is_multi_line_attr( $attr, $val, $server_id ) ) { ?>
            <textarea
                class="val"
                rows="3"
         	    cols="50"
		       	name="<?php echo $input_name; ?>"
                id="<?php echo $input_id; ?>"><?php echo htmlspecialchars($val); ?></textarea>
        <?php } else { ?>
        	<input type="text"
                class="val"
		       	name="<?php echo $input_name; ?>"
                id="<?php echo $input_id; ?>"
         	    value="<?php echo htmlspecialchars($val); ?>" />
        <?php } ?>


		<?php 
		// draw a link for popping up the entry browser if this is the type of attribute
		// that houses DNs. 
		if( is_dn_attr( $server_id, $attr ) )
			draw_chooser_link( "edit_form.$input_id", false );

        // If this is a gidNumber on a non-PosixGroup entry, lookup its name and description for convenience
        if( 0 == strcasecmp( $attr, 'gidNumber' ) && 
                ! in_array_ignore_case( 'posixGroup', get_object_attr( $server_id, $dn, 'objectClass' ) ) ) {
            $gid_number = $val;
            $search_group_filter = "(&(objectClass=posixGroup)(gidNumber=$val))";
            $group = pla_ldap_search( $server_id, $search_group_filter, null, array( 'dn', 'description' )  );
            if( count( $group ) > 0 ) {
                echo "<br />";
                $group = array_pop( $group );
                $group_dn = $group['dn'];
                $group_name = explode( '=', get_rdn( $group_dn ) );
                $group_name = $group_name[1];
                $href = "edit.php?server_id=$server_id&amp;dn=" . urlencode( $group_dn );
                echo "<small>";
                echo "<a href=\"$href\">" . htmlspecialchars($group_name) . "</a>";
                $description = isset( $group['description'] ) ? $group['description'] : null;
                if( $description ) echo " (" . htmlspecialchars( $description ) . ")";
                echo "</small>";
            }
        }

        ?>

		<br />
	       
	<?php  } /* end foreach value */ ?>

		<?php 
		/* Draw the "add value" link under the list of values for this attributes */

		if(	! is_server_read_only( $server_id ) && 
			( $schema_attr = get_schema_attribute( $server_id, $attr, $dn ) ) &&
			! $schema_attr->getIsSingleValue() )
		{ 
			$add_href = "add_value_form.php?server_id=$server_id&amp;" .
					"dn=$encoded_dn&amp;attr=" . rawurlencode( $attr ); 
			echo "<div class=\"add_value\">(<a href=\"$add_href\"
				title=\"" . sprintf( $lang['add_value_tooltip'], $attr ) . "\">" . 
				$lang['add_value'] . "</a>)</div>\n";
		} 
			   
		?>

	</td>
	</tr>

    <?php if( $is_modified_attr ) { ?>
		<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>
	<?php  } ?>

	<?php  

	flush();

} /* End foreach( $attrs as $attr => $vals ) */ ?>

<?php if( ! is_server_read_only( $server_id ) ) { ?>
	<tr><td colspan="2"><center><input type="submit" value="<?php echo $lang['save_changes']; ?>" /></center></td></tr></form>
<?php } ?>
	
<?php 
?>


</table>

<?php /* If this entry has a binary attribute, we need to provide a form for it to submit when deleting it. */ ?>
<script language="javascript">
//<!--
function deleteAttribute( attrName )
{
	if( confirm( "<?php echo $lang['really_delete_attribute']; ?> '" + attrName + "'?" ) ) {
		document.delete_attribute_form.attr.value = attrName;
		document.delete_attribute_form.submit();
	}
}
//-->
</script>

<!-- This form is submitted by JavaScript when the user clicks "Delete attribute" on a binary attribute -->
<form name="delete_attribute_form" action="delete_attr.php" method="post">
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
	<input type="hidden" name="attr" value="FILLED IN BY JAVASCRIPT" />
</form>

<?php 

/**
 * Given an attribute $x, this returns true if it is NOT already specified
 * in the current entry, returns false otherwise.
 */
function not_an_attr( $x )
{
	global $attrs;
	//return ! isset( $attrs[ strtolower( $x ) ] );
	foreach( $attrs as $attr => $values )
		if( 0 == strcasecmp( $attr, $x ) )
			return false;
	return true;
}
echo "</body>\n</html>";
?>
