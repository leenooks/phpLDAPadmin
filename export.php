<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/export.php,v 1.6 2004/05/05 23:22:57 xrenard Exp $

require 'export_functions.php';

// get the POST parameters
$base_dn = isset($_POST['dn']) ? $_POST['dn']:NULL;
$server_id = isset($_POST['server_id']) ? $_POST['server_id']:NULL;
$format = isset( $_POST['format'] ) ? $_POST['format'] : "unix";
$scope = isset($_POST['scope']) ? $_POST['scope'] : 'base';
isset($_POST['exporter_id']) or pla_error( $lang['must_choose_export_format'] );
$exporter_id = $_POST['exporter_id'];
isset($exporters[$exporter_id]) or  pla_error( $lang['invalid_export_format'] );

// do some check
check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

// Initialisation of other varaiables
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
$plaLdapExporter = new PlaLdapExporter($server_id,'objectclass=*',$base_dn,$scope);

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
header( "Content-type: application/download" );
header( "Content-Disposition: filename=$friendly_rdn.".$exporters[$exporter_id]['extension'] ); 
header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" ); 
header( "Cache-Control: post-check=0, pre-check=0", false );

// and export
$exporter->export();
?>
