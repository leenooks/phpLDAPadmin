<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/export_form.php,v 1.19 2004/10/24 23:51:49 uugdave Exp $

/**
 * export_form.php
 * --------------------
 *
 * Html form to choose an export format(ldif,...)
 *
 */

require 'export_functions.php';

$server_id = isset( $_GET['server_id'] ) ? $_GET['server_id']:NULL ;
$format = isset( $_GET['format'] ) ? $_GET['format'] : get_line_end_format();
$scope = isset( $_GET['scope'] ) ? $_GET['scope'] : 'base' ;
$exporter_id = isset( $_GET['exporter_id'] ) ? $_GET['exporter_id'] : 0 ;
$dn = isset( $_GET['dn'] ) ? $_GET['dn'] : null;
$filter = isset( $_GET['filter'] ) ? $_GET['filter'] : '(objectClass=*)';
$attributes = isset( $_GET['attributes'] ) ? $_GET['attributes'] : '*';
$sys_attr = isset( $_GET['sys_attr'] ) && $_GET['sys_attr'] == 'true' ? true : false;

$available_formats = array( 
	'unix' => 'UNIX (Linux, BSD)', 
	'mac'  => 'Macintosh', 
	'win'  => 'Windows'
);

$available_scopes = array(
	'base' => $lang['scope_base'],
	'one' => $lang['scope_one'],
	'sub' => $lang['scope_sub']
);


include './header.php'; ?>

  <body>
    <h3 class="title"><?php echo $lang['export']; ?></h3>
    <br />
    <center>
    <form name="export_form" action="export.php" method="POST">
      <table class="export_form">
        <tr>
	      <td>
            <fieldset>
	          <legend><?php echo $lang['export']; ?></legend>
              <table>
                <tr>
                  <td><?php echo $lang['server']; ?></td>
                  <td>
                    <?php
                    if( count($servers) > 1 ){
                    	echo '<select name="server_id">';
                    	foreach( $servers as $id => $server )
                    		if( $server['host'] )
                    			echo "<option value=\"$id\"". ($id==$server_id?" selected":"") .">" . htmlspecialchars($server['name']) . "</option>\n";
                    	echo '</select>';
                    } else {
                      $server = reset($servers);
                      if( $server['host'] )
                        echo '<input type="hidden" name="server_id" value="'.key($servers).'" />' .
                                             '<b>' . $server['name'] . '</b>';
                    }
                    ?>
             </td>
          </tr>
          <tr>
            <td style="white-space:nowrap"><?php echo $lang['base_dn']; ?></td>
	        <td><nobr><input type="text" name="dn" id="dn" style="width:230px" value="<?php echo htmlspecialchars( $dn ); ?>" /> <?php draw_chooser_link( 'export_form.dn' ); ?></nobr></td>
          </tr>
	      <tr>
            <td><span style="white-space: nowrap"><?php echo $lang['search_scope']; ?></span></td>
            <td>
            <?php foreach( $available_scopes as $id => $desc ) {
            	$id = htmlspecialchars( $id );
            	$desc = htmlspecialchars( $desc ); ?>

            <input type="radio" name="scope" value="<?php echo $id; ?>" id="<?php echo $id; ?>"<?php if($id==$scope) echo ' checked="true"';?> /><label for="<?php echo $id; ?>"><?php echo $desc; ?></label><br />

            <?php } ?>
            </td>
          </tr>
          <tr>
            <td><?php echo $lang['search_filter']; ?></td>
            <td><input type="text" name="filter" style="width:300px" value="<?php echo htmlspecialchars($filter); ?>" /></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td><input type="checkbox" name="sys_attr" id="sys_attr" <?php if( $sys_attr ) echo 'checked="true" '; ?>/> <label for="sys_attr"><?php echo $lang['include_system_attrs']; ?></label></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
	        <td><input type="checkbox" id="save_as_file" name="save_as_file" /><label for="save_as_file"><?php echo $lang['save_as_file']; ?></label></td>
          </tr>
        </table>
      </fieldset>
      </td>
    </tr>
    <tr>
      <td>
        <table style="width: 100%">
        <tr><td style="width: 50%">
        <fieldset style="height: 100px">
          <legend><?php echo $lang['export_format']; ?></legend>

            <?php foreach($exporters as $index => $exporter){?>

            <input type="radio"  name="exporter_id" value="<?php echo htmlspecialchars($index); ?>" id="<?php echo htmlspecialchars($index); ?>" <?php if($index==$exporter_id) echo ' checked="true"'; ?> />
            <label for="<?php echo htmlspecialchars( $index ); ?>"><?php echo htmlspecialchars( $exporter['desc'] ); ?></label><br />

            <?php } ?>

        </fieldset>
        </td>
        <td style="width: 50%">
        <fieldset style="height: 100px">
          <legend><?php echo $lang['line_ends']; ?></legend>
            <?php foreach( $available_formats as $id => $desc ) { 
            	$id = htmlspecialchars( $id );
            	$desc = htmlspecialchars( $desc );
            ?>	  
    
            <input type="radio" name="format" value="<?php echo $id; ?>"  id="<?php echo $id; ?>"<?php if($format==$id) echo ' checked="true"'; ?> /><label for="<?php echo $id; ?>"><?php echo $desc; ?></label><br />

            <?php } ?>
        </fieldset>
        </td></tr>
        </table>
	  </td>
	</tr>
    <tr>
      <td colspan="2">
	    <center>
          <input type="submit" name="target" value="<?php echo $lang['proceed_gt']; ?>" />
	    </center>
	  </td>
    </tr>
  </table>
</form>
</center>
</body>
</html>

<?php

/**
 * Helper functoin for fetching the line end format.
 * @return String 'win', 'unix', or 'mac' based on the user's browser..
 */
function get_line_end_format()
{
    if( is_browser_os_windows() )
        return 'win';
    elseif( is_browser_os_unix() )
        return 'unix';
    elseif( is_browser_os_mac() )
        return 'mac';
    else
        return 'unix';
}
