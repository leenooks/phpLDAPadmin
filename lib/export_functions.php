<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/export_functions.php,v 1.30.2.2 2005/10/23 01:19:27 wurley Exp $

/**
 * Fuctions and classes for exporting ldap entries to others formats
 * (LDIF,DSML,..)
 * An example is provided at the bottom of this file if you want implement yours. *
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @see  export.php and export_form.php
 */ 
/**
 */

// include common configuration definitions
include('./common.php');

// registry for the exporters
$exporters = array();

$exporters[] = array(
	'output_type'=>'ldif',
	'desc' => 'LDIF',
	'extension' => 'ldif'
);
      
$exporters[] = array(
	'output_type'=>'dsml',
	'desc' => 'DSML V.1',
	'extension' => 'xml'
);

$exporters[] = array(
	'output_type'=>'vcard',
	'desc' => 'VCARD 2.1',
	'extension' => 'vcf'
);

$exporters[] = array(
	'output_type'=>'csv',
	'desc' => $lang['csv_spreadsheet'],
	'extension' => 'csv'
);

/**
 * This class encapsulate informations about the ldap server 
 * from which the export is done.
 * The following info are provided within this class: 
 *
 * $server_id:  the id of the server.
 * $base_dn:  if the source of the export is the ldap server, 
 *            it indicates the base dn of the search.
 * $query_filter: if the source of the export is the ldap server,
 *                 it indicates the query filter for the search.
 * $scope: if the source of the export is the ldap server,
 *         it indicates the scope of the search.
 *
 * @package phpLDAPadmin
 */

class LdapExportInfo {

  var $base_dn;
  var $query_filter;
  var $scope;

  /**
   * Create a new LdapExportInfo object
   *
   * @param int $server_id the server id
   * @param String $base_dn the base_dn for the search in a ldap server
   * @param String $query_filter the query filter for the search
   * @param String $scope the scope of the search in a ldap server
   */

  function LdapExportInfo($server_id,$base_dn = NULL,$query_filter = NULL,$scope = NULL){
	global $ldapservers;

	$this->ldapserver = $ldapservers->Instance($server_id);

	$this->ldapserver->base_dn = $base_dn;
	$this->ldapserver->query_filter = $query_filter;
	$this->ldapserver->scope = $scope;
  }
}


/**
 * This class represents the base class of all exporters
 * It can be subclassed directly if your intend is to write
 * a source exporter(ie. it will act only as a decoree 
 * which will be wrapped by an another exporter.)
 * If you consider writting an exporter for filtering data 
 * or directly display entries, please consider subclass
 * the PlaExporter
 *
 * @see PlaExporter
 * @package phpLDAPadmin
 */

class PlaAbstractExporter{

  /**
   * Return the number of entries
   * @return int the number of entries to be exported
   */
  function pla_num_entries(){}

  /**
   * Return true if there is some more entries to be processed
   * @return bool if there is some more entries to be processed
   */
  function pla_has_entry(){}

  /**
   * Return the entry as an array
   * @return array  an entry as an array
   */
  function pla_fetch_entry_array(){}

  /**
   * Return the entry as an Entry object
   * @return Entry an entry  as an Entry Object
   */
  function pla_fetch_entry_object(){}

  /**
   * Return a PlaLdapInfo Object
   * @return LdapInfo Object with info from the ldap serveur
   */
  function pla_get_ldap_info(){}

  /**
   * May be call when the processing is finished
   * and to free some ressources.
   * @return bool true or false if any errors is encountered
   */
  function pla_close(){}

}// end PlaAbstractExporter



/**
 * PlaExporter acts a wrapper around another exporter.
 * In other words, it will act as a decorator for another decorator
 *
 * @package phpLDAPadmin
 */

class PlaExporter extends PlaAbstractExporter{
  // the default CRLN 
  var $br="\n";
  // the wrapped $exporter
  var $exporter;

  /**
   * Constructor
   * @param source $source the decoree for this exporter
   */
  function PlaExporter( $source ){
    $this->exporter = $source;
  }
  
  /**
   * Return the number of entries
   * @return int the number of entries to be exported
   */
  function pla_num_entries(){
    return $this->exporter->pla_num_entries();
  }

  /**
   * Return true if there is some more entries to be processed
   * @return bool if there is some more entries to be processed
   */
  function pla_has_entry(){
    return $this->exporter->pla_has_entry();
  }

  /**
   * Return the entry as an array
   * @return array  an entry as an array
   */  
  function pla_fetch_entry_array(){
    return $this->exporter->pla_fetch_entry_array();
  }

  /**
   * Return the entry as an Entry object
   * @return Entry an entry  as an Entry Object
   */
  function pla_fetch_entry_object(){
      return $this->exporter->pla_fetch_entry_object();
  }

  /**
   * Return a PlaLdapInfo Object
   * @return LdapInfo Object with info from the ldap serveur
   */
  function pla_get_ldap_info(){
    return $this->exporter->pla_get_ldap_info();
  }

  /**
   * May be called when the processing is finished
   * and to free some ressources.
   * @return bool false if any errors are encountered,false otherwise
   */
  function pla_close(){
    return $this->exporter->pla_close();
  }

  /**
   * Helper method to check if the attribute value should be base 64 encoded.
   * @param String $str the string to check.
   * @return bool true if the string is safe ascii, false otherwise.
   */
  function is_safe_ascii( $str ){
    for( $i=0; $i<strlen($str); $i++ )
      if( ord( $str{$i} ) < 32 || ord( $str{$i} ) > 127 )
	return false;
    return true;
  }
    
  /**
   * Abstract method use to export data. 
   * Must be implemented in a sub-class if you write an exporter 
   * which export data.
   * Leave it empty if you write a sub-class which do only some filtering.
   */
  function export(){}
  
  /**
   * Set the carriage return /linefeed for the export
   * @param String $br the CRLF to be set
   */
  function setOutputFormat( $br ){
    $this->br = $br;
  }

}// end PlaExporter


/**
 * Export data from a ldap server
 * @extends PlaAbstractExporter
 * @package phpLDAPadmin
 */

class PlaLdapExporter extends PlaAbstractExporter{
  var $entry_id;
  var $results;
  var $server_id;
  var $scope;
  var $entry_array;
  var $num_entries;
  var $ldap_info;
  var $queryFilter;
  var $hasNext;
  var $attributes;
  /**
   * Create a PlaLdapExporter object.
   * @param int $server_id the server id
   * @param String $queryFilter the queryFilter for the export
   * @param String $base_dn the base_dn for the data to export
   * @param String $scope the scope for export
   */
  function PlaLdapExporter( $server_id , $queryFilter , $base_dn , $scope, $attributes){
    global $lang, $config;
    $this->scope = $scope;
    $this->base_dn = $base_dn;
    $this->server_id = $server_id;
    $this->queryFilter = $queryFilter;
    // infos for the server
    $this->ldap_info = new LdapExportInfo($server_id,$base_dn,$queryFilter,$scope);
    // boolean to check if there is more entries
    $this->hasNext = 0;
    // boolean to check the state of the connection

    $this->attributes = $attributes;
    // connect to the server
    $this->ds = $this->ldap_info->ldapserver->connect();
    // @todo test whether we need to call pla_ldap_connection_is_error here.
    //pla_ldap_connection_is_error( $this->ds );

    // get the data to be exported
    if( $this->scope == 'base' )
      $this->results = @ldap_read( $this->ds, $this->base_dn, $this->queryFilter,$this->attributes, 
				   0, 0, 0, $config->GetValue('deref','export'));
    elseif( $this->scope == 'one' )
      $this->results = @ldap_list( $this->ds, $this->base_dn, $this->queryFilter, $this->attributes, 
				   0, 0, 0, $config->GetValue('deref','export'));
    else // scope == 'sub'
      $this->results = @ldap_search( $this->ds, $this->base_dn, $this->queryFilter, $this->attributes, 
				     0, 0, 0, $config->GetValue('deref','export'));
    
    // if no result, there is a something wrong
    if( ! $this->results ) 
      pla_error(  $lang['error_performing_search'], ldap_error( $this->ds ), ldap_errno( $this->ds ) );
    
    // get the number of entries to be exported
    $this->num_entries = @ldap_count_entries( $this->ds,$this->results );

    if( $this->entry_id = @ldap_first_entry( $this->ds,$this->results ) ){
      $this->hasNext = 1;
    }
  }//end constructor

  /**
   * Return the entry as an array
   * @return array  an entry as an array
   */
  function pla_fetch_entry_array(){
    return $this->entry_array;
  }

  /**
   * Return the entry as an Entry object
   * @return Entry an entry  as an Entry Object
   */
  function pla_fetch_entry_object(){
    // to do
  }
  
  /**
   * Return a PlaLdapInfo Object
   * @return LdapInfo Object with info from the ldap serveur
   */
  function pla_get_ldap_info(){
    return $this->ldap_info->ldapserver;
  }

  /**
   * Return the number of entries
   * @return int the number of entries to be exported
   */
  function pla_num_entries(){
    return $this->num_entries;
  }

  /**
   * Return true if there is some more entries to be processed
   * @return bool if there is some more entries to be processed
   */
  function pla_has_entry(){
    if( $this->hasNext ){
      unset( $this->entry_array );
      $dn = @ldap_get_dn( $this->ds,$this->entry_id );
      $this->entry_array['dn'] = $dn;
      
      //get the attributes of the entry
      $attrs = @ldap_get_attributes($this->ds,$this->entry_id);
      if( $attr = @ldap_first_attribute( $this->ds,$this->entry_id,$attrs ) ){
	
	//iterate over the attributes
	while( $attr ){
	  if( is_attr_binary( $this->ldap_info->ldapserver,$attr ) ){
	    $this->entry_array[$attr] = @ldap_get_values_len( $this->ds,$this->entry_id,$attr );
	  }
	  else{
	    $this->entry_array[$attr] = @ldap_get_values( $this->ds,$this->entry_id,$attr );
	  }
	  unset( $this->entry_array[$attr]['count'] );
	  $attr = @ldap_next_attribute( $this->ds,$this->entry_id,$attrs );
	}// end while attr
	
	if(!$this->entry_id = @ldap_next_entry( $this->ds,$this->entry_id ) ){
	  $this->hasNext = 0;
	}
      }// end if attr
      return true;
    }
    else{
      $this->pla_close();
      return false;
    }
  }
 
  /**
   * May be called when the processing is finished
   * and to free some ressources.
   * @return bool true or false if any errors is encountered
   * @todo This could break something, so need to add a method to LDAPServer to close connection and reset $connected.
   */
  function pla_close(){
//    if($this->ldap_info->connected){
//      return @ldap_close( $this->ds );
//    }
//    else{
//      return true;
//    }
  }
} // end PlaLdapExporter

/**
 * Export entries to ldif format
 * @extends PlaExporter
 * @package phpLDAPadmin
 */

class PlaLdifExporter extends PlaExporter{

  // variable to keep the count of the entries
  var $counter = 0;

  // the maximum length of the ldif line
  var $MAX_LDIF_LINE_LENGTH = 76;

  /**
   * Create a PlaLdifExporter object
   * @param PlaAbstractExporter $exporter the source exporter
   */
  function PlaLdifExporter( $exporter ){
    $this->exporter = $exporter;
  }

  /**
   * Export entries to ldif format
   */
  function export(){
    $pla_ldap_info = $this->pla_get_ldap_info();
    $this->displayExportInfo($pla_ldap_info);

    //While there is an entry, fecth the entry as an array 
    while($this->pla_has_entry()){
      $entry = $this->pla_fetch_entry_array();
      $this->counter++;
      
      // display comment before each entry
      global $lang;
      $title_string = "# " . $lang['entry'] . " " . $this->counter . ": " . $entry['dn'] ; 
      if( strlen( $title_string ) > $this->MAX_LDIF_LINE_LENGTH-3 )
	$title_string = substr( $title_string, 0, $this->MAX_LDIF_LINE_LENGTH-3 ) . "...";
      echo "$title_string$this->br";

      // display dn
      if( $this->is_safe_ascii( $entry['dn'] ))
	$this->multi_lines_display("dn: ". $entry['dn']);
      else
	$this->multi_lines_display("dn:: " . base64_encode( $entry['dn'] ));
      array_shift($entry);

      // display the attributes
      foreach( $entry as $key => $attr ){
	foreach( $attr as $value ){
	  if(  !$this->is_safe_ascii($value) || is_attr_binary($pla_ldap_info,$key ) ){
	    $this->multi_lines_display( $key.":: " . base64_encode( $value ) );
	  }
	  else{
	    $this->multi_lines_display( $key.": ".$value );
	  }
	}
      }// end foreach $entry

      echo $this->br;
      // flush every 5th entry (sppeds things up a bit)
      if( 0 == $this->counter % 5 )
	flush();
    }
  }
  
  // display info related to this export
  function displayExportInfo($pla_ldap_info){
    global $lang;
    echo "version: 1$this->br$this->br";
    echo "# " . sprintf( $lang['ldif_export_for_dn'],  $pla_ldap_info->base_dn  ) . $this->br;
    echo "# " . sprintf( $lang['generated_on_date'], date("F j, Y g:i a") ) . $this->br;
    echo "# " . $lang['server'] . ": " .$pla_ldap_info->name  . " (" . $pla_ldap_info->host . ")" . $this->br;
    echo "# " . $lang['search_scope'] . ": " . $pla_ldap_info->scope . $this->br;
    echo "# " . $lang['search_filter'] . ": " . $pla_ldap_info->query_filter . $this->br;
    echo "# " . $lang['total_entries'] . ": " . $this->pla_num_entries() . $this->br; 
    echo $this->br;
  }

  /**
   * Helper method to wrap ldif lines
   * @param String $str the line to be wrapped if needed.
   */
  function multi_lines_display( $str ){
    
    $length_string = strlen($str);
    $max_length = $this->MAX_LDIF_LINE_LENGTH;
    
    while ($length_string > $max_length){
      echo substr($str,0,$max_length).$this->br." ";
      $str= substr($str,$max_length,$length_string);
      $length_string = strlen($str);
      
      // need to do minus one to align on the right
      // the first line with the possible following lines 
      // as these will have an extra space
      $max_length = $this->MAX_LDIF_LINE_LENGTH-1;
    }
    echo $str."".$this->br;
  }

}

/**
 * Export entries to DSML v.1
 * @extends PlaExporter
 * @package phpLDAPadmin
 */

class PlaDsmlExporter extends PlaExporter{

  //not in use
  var $indent_step = 2;
  var $counter = 0;

  /**
   * Create a PlaDsmlExporter object
   * @param PlaAbstractExporter $exporter the decoree exporter
   */
  function PlaDsmlExporter( $exporter ){
    $this->exporter = $exporter;
  }

  /**
   * Export the entries to DSML
   */
  function export(){
     global $lang;
     $pla_ldap_info = $this->pla_get_ldap_info();
     // not very elegant, but do the job for the moment as we have just 4 level
     $directory_entries_indent = "  ";
     $entry_indent= "    ";
     $attr_indent = "      ";
     $attr_value_indent = "        ";

     // print declaration
     echo "<?xml version=\"1.0\"?>$this->br";
     
     // print root element     
     echo "<dsml>$this->br";
     
     // print info related to this export
     echo "<!-- " . $this->br;
     echo "# " . sprintf( $lang['dsml_export_for_dn'],  $pla_ldap_info->base_dn  ) . $this->br;
     echo "# " . sprintf( $lang['generated_on_date'], date("F j, Y g:i a") ) . $this->br;
     echo "# " . $lang['server'] . ": " .  $pla_ldap_info->name  . " (" . $pla_ldap_info->host . ")" . $this->br;
     echo "# " . $lang['search_scope'] . ": " . $pla_ldap_info->scope . $this->br;
     echo "# " . $lang['search_filter'] . ": " . $pla_ldap_info->query_filter . $this->br;
     echo "# " . $lang['total_entries'] . ": " . $this->pla_num_entries() . $this->br; 
     echo "-->" . $this->br;


     echo $directory_entries_indent."<directory-entries>$this->br";
     //While there is an entry, fetch the entry as an array 
      while($this->pla_has_entry()){
	$entry = $this->pla_fetch_entry_array();
	$this->counter++;
	// display dn
	echo $entry_indent."<entry dn=\"". htmlspecialchars( $entry['dn'] ) ."\">".$this->br;
	array_shift($entry);
	
	// echo the objectclass attributes first
	if(isset($entry['objectClass'])){
	  echo $attr_indent."<objectClass>".$this->br;
	  foreach($entry['objectClass'] as $ocValue){
	    echo $attr_value_indent."<oc-value>$ocValue</oc-value>".$this->br;
	  }
	  echo $attr_indent."</objectClass>".$this->br;
	  unset($entry['objectClass']);
	}
	
	$binary_mode = 0;
	// display the attributes
	foreach($entry as $key=>$attr){
	  echo $attr_indent."<attr name=\"$key\">".$this->br;
	  
	  // if the attribute is binary, set the flag $binary_mode to true
	  $binary_mode = is_attr_binary($pla_ldap_info,$key)?1:0;
	  
	  foreach($attr as $value){
	    echo $attr_value_indent."<value>".($binary_mode?base64_encode( $value):  htmlspecialchars( $value ) )."</value>".$this->br;
	  }
	  echo $attr_indent."</attr>".$this->br;
	}// end foreach $entry
	echo $entry_indent."</entry>".$this->br;
	
	// flush every 5th entry (speeds things up a bit)
	if( 0 == $this->counter % 5 )
	  flush();
      }
      echo $directory_entries_indent."</directory-entries>$this->br";
      echo "</dsml>".$this->br;
   }
}


/**
 * @package phpLDAPadmin
 */
class PlaVcardExporter extends PlaExporter{

  // mappping one to one attribute
  var $vcardMapping = array('cn' => 'FN',
				'title' => 'TITLE',
				'homePhone' => 'TEL;HOME',
				'mobile' => 'TEL;CELL',
				'mail' => 'EMAIL;Internet',
				'labeledURI' =>'URL',
				'o' => 'ORG',
				'audio' => 'SOUND',
				'facsmileTelephoneNumber' =>'TEL;WORK;HOME;VOICE;FAX',
				'jpegPhoto' => 'PHOTO;ENCODING=BASE64',
				'businessCategory' => 'ROLE',
				'description' => 'NOTE'
				);
  
  var $deliveryAddress  = array("postOfficeBox",
				"street",
				"l",
				"st",
				"postalCode",
				"c");

  function PlaVcardExporter($exporter){
    $this->exporter = $exporter;
  }

  /**
   * When doing an exporter, the method export need to be overriden.
   * A basic implementation is provided here. Customize to your need
   **/

  function export(){

    // With the method pla->get_ldap_info,
    // you have access to some values related
    // to you ldap server
    $ldap_info = $this->pla_get_ldap_info();
    $base_dn = $ldap_info->base_dn;
    $server_id = $ldap_info->server_id;
    $scope = $ldap_info->scope;
    $server_name = $ldap_info->name;
    $server_host = $ldap_info->host;

    while( $this->pla_has_entry() ){
      $entry = $this->pla_fetch_entry_array();

      //fetch the dn
      $dn = $entry['dn'];
      unset( $entry['dn'] );

      // check the attributes needed for the delivery address
      // field
      $addr = "ADR:";
      foreach( $this->deliveryAddress as $attr_name ){
	if( isset( $entry[$attr_name] ) ){
	  $addr .= $entry[$attr_name][0];
	  unset($entry[$attr_name]);
	}
	$addr .= ';';
      }
      echo "BEGIN:VCARD$this->br";

      // loop for the attributes
      foreach( $entry as $attr_name=>$attr_values ){

	// if an attribute of the ldap entry exist
	// in the mapping array for vcard
	if( isset( $this->vcardMapping[$attr_name] ) ){

	  // case of organisation. Need to append the
	  // possible ou attribute
	  if( 0 == strcasecmp( $attr_name , 'o' )){
	    echo $this->vcardMapping[$attr_name].":$attr_values[0]";
	    if( isset($entry['ou'] ) )
	      foreach( $entry['ou'] as $ou_value ){
		echo ";$ou_value";
	      }
	  }
	  // the attribute is binary. (to do : need to fold the line)
	  else if( 0 == strcasecmp( $attr_name,'audio') || 0 == strcasecmp( $attr_name,'jpegPhoto') ){
	    echo $this->vcardMapping[$attr_name].":$this->br";
	    echo " ".base64_encode( $attr_values[0]);
	  }
	  /*	  else if( $attr_name == "sn"){
	    echo $this->vcardMapping[$attr_name].":$attr_values[0]";
	  }
	  elseif( $attr_name == "homePostalAddress"){
          }*/
	  // else just print the value with the relevant attribute name
	  else{
	    echo $this->vcardMapping[$attr_name].":$attr_values[0]";
	  }
	  echo $this->br;
	}
      }
      // need to check
      echo "UID:$dn";
      echo $this->br;
      echo "VERSION:2.1";
      echo $this->br;
      echo $addr;
      echo $this->br;
      echo "END:VCARD";
      echo $this->br;
    }// end while
  }
}




/**
 * Export to cvs format
 *
 * @author Glen Ogilvie
 * @package phpLDAPadmin
 */

class PlaCSVExporter extends PlaExporter{

  function PlaCSVExporter($exporter){
    $this->exporter = $exporter;
  }
  
  /**
   * When doing an exporter, the method export need to be overriden.
   * A basic implementation is provided here. Customize to your need
   **/
  
  var $separator = ",";
  var $qualifier = '"';
  var $multivalue_separator = " | ";
  var $escapeCode = '"';
  
  function export(){
    
    // With the method pla->get_ldap_info,
    // you have access to some values related
    // to you ldap server
    $ldap_info = $this->pla_get_ldap_info();
    $base_dn = $ldap_info->base_dn;
    $server_id = $ldap_info->server_id;
    $scope = $ldap_info->scope;
    $server_name = $ldap_info->name;
    $server_host = $ldap_info->host;
    
    $entries = array();
    $headers = array();
		
    // go thru and find all the attribute names first.  This is needed, because, otherwise we have
    // no idea as to which search attributes were actually populated with data
    while( $this->pla_has_entry() ) {
      $entry = $this->pla_fetch_entry_array();
      foreach (array_keys($entry) as $key) {
	if (!in_array($key, $headers)) 
	  array_push($headers,$key);
      }
      array_push($entries, $entry);
    }
    
    $num_headers = count($headers);
    
    // print out the headers
    for ($i = 0; $i < $num_headers; $i++) {
      echo $this->qualifier. $headers[$i].$this->qualifier;
      if ($i < $num_headers-1) 
	echo $this->separator;
    }
    
    array_shift($headers);
    $num_headers--;
    
    echo $this->br;
	
    // loop on every entry
    foreach ($entries as $entry) {
      
      //print the dn
      $dn = $entry['dn'];
      unset( $entry['dn'] );
      echo $this->qualifier. $this->LdapEscape($dn).$this->qualifier.$this->separator;
      
      // print the attributes
      for($j=0;$j<$num_headers;$j++){

	$attr_name = $headers[$j];

	echo $this->qualifier;
	if (key_exists($attr_name, $entry)) {
	  $binary_attribute = is_attr_binary( $ldap_info, $attr_name )?1:0;
	  
	  $attr_values = $entry[$attr_name];
	  
	  $num_attr_values = count( $attr_values );
	  for( $i=0 ; $i<$num_attr_values; $i++){
	    if($binary_attribute)
	      echo base64_encode($attr_values[$i]);
	    else
	      echo $this->LdapEscape($attr_values[$i]);
	    
	    if($i < $num_attr_values - 1)
	      echo $this->multivalue_separator;
	    
	  }
	}// end if key
	echo $this->qualifier;
	if( $j < $num_headers - 1 )
	  echo $this->separator;
      }
          echo $this->br;
    }
  }//end export
  
  // function to escape data, where the qualifier happens to also 
  // be in the data.
  function LdapEscape ($var) {
    return str_replace($this->qualifier, $this->escapeCode.$this->qualifier, $var);
  }
}




/**
 * @package phpLDAPadmin
 */

class MyCustomExporter extends PlaExporter{

  function MyCustomExporter($exporter){
    $this->exporter = $exporter;
  }

  /**
   * When doing an exporter, the method export need to be overriden.
   * A basic implementation is provided here. Customize to your need
   **/


  function export(){

    // With the method pla->get_ldap_info,
    // you have access to some values related
    // to you ldap server
    $ldap_info = $this->pla_get_ldap_info();
    $base_dn = $ldap_info->base_dn;
    $server_id = $ldap_info->server_id;
    $scope = $ldap_info->scope;
    $server_name = $ldap_info->name;
    $server_host = $ldap_info->host;


    // Just a simple loop. For each entry 
    // do your custom export
    // see PlaLdifExporter or PlaDsmlExporter as an example 
    while( $this->pla_has_entry() ){
      $entry = $this->pla_fetch_entry_array();
      
      //fetch the dn
      $dn = $entry['dn'];
      unset( $entry['dn'] );
	    
      // loop for the attributes
      foreach( $entry as $attr_name=>$attr_values ){
	foreach( $attr_values as $value ){
	  
	  // simple example
	  // echo "Attribute Name:".$attr_name;
	  //  echo " - value:".$value;
	  // echo $this->br;
	  }
      }
      
    }// end while
  }

}
?>
