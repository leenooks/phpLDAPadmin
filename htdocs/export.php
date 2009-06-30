<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/export.php,v 1.15 2005/09/25 16:11:44 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

# Fix a bug with IE:
ini_set('session.cache_limiter','');

require './common.php';
require LIBDIR.'export_functions.php';

// get the POST parameters
$server_id = (isset($_POST['server_id']) ? $_POST['server_id'] : '');

if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$base_dn = isset($_POST['dn']) ? $_POST['dn']:NULL;
$format = isset( $_POST['format'] ) ? $_POST['format'] : "unix";
$scope = isset($_POST['scope']) ? $_POST['scope'] : 'base';
$filter = isset($_POST['filter']) ? $_POST['filter'] : 'objectclass=*';
$target = isset($_POST['target']) ? $_POST['target'] : 'display';
$save_as_file = isset( $_POST['save_as_file'] ) &&  $_POST['save_as_file'] == 'on';

// add system attributes if needed
$attributes = array();
if( isset( $_POST['sys_attr'] ) ){
  array_push($attributes,'*');
  array_push($attributes,'+');
}

isset($_POST['exporter_id']) or pla_error( $lang['must_choose_export_format'] );
$exporter_id = $_POST['exporter_id'];
isset($exporters[$exporter_id]) or  pla_error( $lang['invalid_export_format'] );

// Initialisation of other variables
$rdn = get_rdn( $base_dn );
$friendly_rdn = get_rdn( $base_dn, 1 );
$extension = $exporters[$exporter_id]['extension'];

//set the default CRLN to Unix format
$br = "\n";

// default case not really needed
switch( $format ) {
 case 'win': 
   $br = "\r\n"; 
   break;
 case 'mac':
   $br = "\r";
   break;
 case 'unix':
 default:	
   $br = "\n";
}

// get the decoree,ie the source
$plaLdapExporter = new PlaLdapExporter($server_id,$filter,$base_dn,$scope,$attributes);

// the decorator 
// do it that way for the moment
$exporter = NULL;

switch($exporter_id){
 case 0:
   $exporter = new PlaLdifExporter($plaLdapExporter);
   break;
 case 1:
   $exporter = new PlaDsmlExporter($plaLdapExporter);
   break;
 case 2:
   $exporter = new PlaVcardExporter($plaLdapExporter);
   break;
 case 3:
   $exporter = new PlaCSVExporter($plaLdapExporter);
   break;
 default:
   // truly speaking,this default case will never be reached. See check at the bottom.
   $plaLdapExporter->pla_close();
   pla_error( $lang['no_exporter_found'] );
}

// set the CLRN
$exporter->setOutputFormat($br);

// prevent script from bailing early for long search
@set_time_limit( 0 );


// send the header
if( $save_as_file ) 
  header( "Content-type: application/download" );
else
  header( "Content-type: text/plain" );
header( "Content-Disposition: filename=$friendly_rdn.".$exporters[$exporter_id]['extension'] ); 
header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" ); 
header( "Cache-Control: post-check=0, pre-check=0", false );

// and export
$exporter->export();
?>
