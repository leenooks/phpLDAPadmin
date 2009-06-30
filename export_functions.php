<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/export_functions.php,v 1.11 2004/03/19 20:13:08 i18phpldapadmin Exp $

/**
 * Fuctions and classes for exporting ldap entries to others formats
 * (LDIF,DSML,..)
 * An example is provided at the bottom of this file if you want implement yours. *
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @see  export.php and export_form.php
 */ 

// include common configuration definitions
include('common.php');

// registry for the exporters
$exporters = array();

$exporters[] = array('output_type'=>'ldif',
			     'desc' => 'LDIF',
			     'extension' => 'ldif'
			     );
      
$exporters[] = array('output_type'=>'dsml',
			     'desc' => 'DSML V.1',
			     'extension' => 'xml'
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
 * $server_host: the host name of the server.
 * $server_name: the name of the server.
 */

class LdapInfo{

  var $base_dn;
  var $query_filter;
  var $scope;
  var $server_host = NULL;
  var $server_name = NULL;
  var $server_id = NULL;

  /**
   * Create a new LdapInfo object
   *
   * @param int $server_id the server id
   * @param String $base_dn the base_dn for the search in a ldap server
   * @param String $query_filter the query filter for the search
   * @param String scope the scope of the search in a ldap server
   */

  function LdapInfo($server_id,$base_dn = NULL,$query_filter = NULL,$scope = NULL){
    global $servers;
    $this->base_dn = $base_dn;
    $this->query_filter = $query_filter;
    $this->scope = $scope;
    $this->server_name = $servers[ $server_id ][ 'name' ];
    $this->server_host = $servers[ $server_id ][ 'host' ];
    $this->server_id = $server_id;
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
   * May be call when the processing is finished
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
 */

class PlaLdapExporter extends PlaAbstractExporter{
  var $entry_id;
  var $results;
  var $entry_id;
  var $server_id ;
  var $scope;
  var $entry_array;
  var $num_entries;
  var $ldap_info;
  var $queryFilter;
  var $hasNext;
  var $connection_open_state;

  /**
   * Create a PlaLdapExporter object.
   * @param int $server_id the server id
   * @param String $queryFilter the queryFilter for the export
   * @param String $base_dn the base_dn for the data to export
   * @param String $scope the scope for export
   */
  function PlaLdapExporter( $server_id , $queryFilter , $base_dn , $scope){
    global $lang;
    $this->scope = $scope;
    $this->base_dn = $base_dn;
    $this->server_id = $server_id;
    $this->queryFilter = $queryFilter;
    // infos for the server
    $this->ldap_info = new LdapInfo($server_id,$base_dn,$queryFilter,$scope);
    // boolean to check if there is more entries
    $this->hasNext = 0;
    // boolean to check the state of the connection
    $this->connection_open_state = 0;
    
    // connect to the server
    $this->ds = @pla_ldap_connect( $this->server_id );
    if( ! $this->ds ) {
      pla_error( $lang['could_not_connect'] );
    }
    else{
     $this->connection_open_state = 1;
    }
   
    // get the data to be exported
    if( $this->scope == 'base' )
      $this->results = @ldap_read( $this->ds, $this->base_dn, $this->queryFilter,array(), 
				   0, 0, 0, get_export_deref_setting() );
    elseif( $this->scope == 'one' )
      $this->results = @ldap_list( $this->ds, $this->base_dn, $this->queryFilter, array(), 
				   0, 0, 0, get_export_deref_setting() );
    else // scope == 'sub'
      $this->results = @ldap_search( $this->ds, $this->base_dn, $this->queryFilter, array(), 
				     0, 0, 0, get_export_deref_setting() );
    
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
    return $this->ldap_info;
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
	  if( is_attr_binary( $this->server_id,$attr ) ){
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
   * May be call when the processing is finished
   * and to free some ressources.
   * @return bool true or false if any errors is encountered
   */
  function pla_close(){
    if($this->connection_open_state){
      return @ldap_close( $this->ds );
    }
    else{
      return true;
    }
  }
} // end PlaLdapExporter

/**
 * Export entries to ldif format
 * @extends PlaExporter
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
	$this->multi_lines_display("dn:". $entry['dn']);
      else
	$this->multi_lines_display("dn:: " . base64_encode( $entry['dn'] ));
      array_shift($entry);

      // display the attributes
      foreach( $entry as $key => $attr ){
	foreach( $attr as $value ){
	  if(  !$this->is_safe_ascii($value) || is_attr_binary($pla_ldap_info->server_id,$key ) ){
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
    echo "# " . $lang['server'] . ": " .$pla_ldap_info->server_name  . " (" . $pla_ldap_info->server_host . ")" . $this->br;
    echo "# " . $lang['search_scope'] . ": " . $pla_ldap_info->scope . $this->br;
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
     echo "# " . $lang['server'] . ": " .  $pla_ldap_info->server_name  . " (" . $pla_ldap_info->server_host . ")" . $this->br;
     echo "# " . $lang['search_scope'] . ": " . $pla_ldap_info->scope . $this->br;
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
	  $binary_mode = is_attr_binary($pla_ldap_info->server_id,$key)?1:0;
	  
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


class MyCustomExporter{

  function MyCutsomExporter($exporter){
    $this->exporter = $exporer;
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
    $server_name = $ldap_info->server_name;
    $server_host = $ldap_info->server_host;


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
