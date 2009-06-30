<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/export_form.php,v 1.7 2004/05/05 23:21:57 xrenard Exp $

/**
 * export_form.php
 * --------------------
 *
 * Html form to choose an export format(ldif,...)
 *
 */

require 'export_functions.php';

$server_id = isset( $_GET['server_id'] ) ? $_GET['server_id']:NULL ;
$format = isset( $_GET['format'] ) ? $_GET['format'] : "unix" ;
$scope = isset( $_GET['scope'] ) ? $_GET['scope'] : 'base' ;
$exporter_id = isset( $_GET['exporter_id'] ) ? $_GET['exporter_id'] : 0 ;
$dn = isset( $_GET['dn'] ) ? $_GET['dn'] : null;

$available_formats = array( 
	'unix' => 'UNIX', 
	'mac'  => 'Macintosh', 
	'win'  => 'Windows'
);

$available_scopes = array(
	'base' => $lang['scope_base'],
	'one' => $lang['scope_one'],
	'sub' => $lang['scope_sub']
);


include 'header.php'; ?>

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
                    <select name="server_id">
<?php
foreach( $servers as $id => $server )
	if( $server['host'] )
		echo "<option value=\"$id\"". ($id==$server_id?" selected":"") .">" . htmlspecialchars($server['name']) . "</option>\n";
?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td><acronym title="<?php echo $lang['distinguished_name'];?>">DN</acronym></td>
	          <td><nobr><input type="text" name="dn" id="dn" style="width:200px" value="<?php echo htmlspecialchars( $dn ); ?>" /> <?php draw_chooser_link( 'export_form.dn' ); ?></nobr></td>
               </tr>
              </table>
            </fieldset>
          </td>
        </tr>
        <tr>
          <td>
            <fieldset>
              <legend><?php echo $lang['export_format']; ?></legend>
<?php foreach($exporters as $index => $exporter){?>
                <input type="radio"  name="exporter_id" value="<?php echo htmlspecialchars($index); ?>" id="<?php echo htmlspecialchars($index); ?>" <?php if($index==$exporter_id) echo ' checked'; ?> />
                <label for="<?php echo htmlspecialchars( $index ); ?>"><?php echo htmlspecialchars( $exporter['desc'] ); ?></label><br />
<?php } ?>
            </fieldset>
          </td>
        </tr>
	<tr>
	  <td>
            <fieldset>
              <legend><?php echo $lang['line_ends']; ?></legend>
<?php foreach( $available_formats as $id => $desc ) { 
	$id = htmlspecialchars( $id );
	$desc = htmlspecialchars( $desc );
	?>	  
            <input type="radio" name="format" value="<?php echo $id; ?>"  id="<?php echo $id; ?>"<?php if($format==$id) echo ' checked'; ?> /><label for="<?php echo $id; ?>"><?php echo $desc; ?></label><br />
<?php } ?>
            </fieldset>
	  </td>
	</tr>
	<tr>
	  <td>
	  <fieldset>
	    <legend><?php echo $lang['search_scope']; ?></legend>
<?php foreach( $available_scopes as $id => $desc ) {
	$id = htmlspecialchars( $id );
	$desc = htmlspecialchars( $desc ); ?>
	     <input type="radio" name="scope" value="<?php echo $id; ?>" id="<?php echo $id; ?>"<?php if($id==$scope) echo ' checked';?> /><label for="<?php echo $id; ?>"><?php echo $desc; ?></label><br />
<?php } ?>
           </fieldset>
          </td>
	</tr>
        <tr>
          <td colspan="2" ><center><input type="submit" value="<?php echo $lang['createf_proceed']; ?>"></center></td>
        </tr>
      </table>
      </center>
    </form>
  </body>
</html>
