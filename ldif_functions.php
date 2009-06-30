<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/ldif_functions.php,v 1.23 2004/04/18 19:21:54 xrenard Exp $


/**
 * @todo put the display_parse_error method in ldif_import here 
 * @todo do not allow entry whose number of unfolded lines is 
 *       over 200 for example
 * @todo later: return the entry as an array before returning the entry object
 *       (just make a wrapper method)
 * @todo make a class CSVReader wich extends FileReader  
 *
 * @package phpLDAPadmin
 *
 * @author The phpLDAPadmin development team
 */


/**
 * This class represente an entry in the ldif file
 */
class LdifEntry{

  var $dn;
  var $changeType;
  var $attributes=array();

  /**
   * Creates a new LdifEntry, with optionally specified DN, changeType, and attributes.
   *
   * @param String $dn   the distinguished name of the entry. Default is set to an empty string
   * @param String $changeType the change type associated with the entry. Default is set to add.
   * @param String[]  $atts the attributes of the entry
   */
  function LdifEntry($dn="",$changeType="add",$atts = array()){
    $this->dn=$dn;
    $this->changeType=$changeType;
    $this->attributes=$atts;
  }

  /**
   * Return the dn of the entry
   *
   * @return String the dn of the entry
   */
  function getDn(){
    return $this->dn;
  }

  /**
   * Setter method for the distinguished name
   *
   * @param String $dn the distinguished name of the entry
   */
  function setDn($dn){
    $this->dn = $dn;
  }

  /**
   * Getter method for the change type
   *
   * @return String the change type of the entry
   */
  function getChangeType(){
    return $this->changeType;
  }
  
  /**
   * Setter method for the change type of the entry
   *
   * @param String $changeType the change type of the entry
   */
  function setChangeType($changeType){
    $this->changeType = $changeType;
  }
  
  /**
   * Add the attributes to the entry
   *
   * @param String[][] $atts the attributes of the entry
   */
  function setAttributes($atts){
    $this->attributes = $atts;
  }

  /**
   * Get the attributes of the entry
   *
   * @return String[][] the attributes of the entry
   */
  function getAttributes(){
    return $this->attributes;
    
  }
}

/**
 * This exception is similar to the one in LdifReader
 * Should be remove latter
 * see comment for the class Ldif_LdapEntryReader
 */

class LdifEntryReaderException{
  
  
  var $lineNumber;
  var $currentLine;
  var $message;

  /**
   * Constructor of the exception
   *
   * @param int $lineNumber the number of the line where
   *        the error occured
   * @param String currentLine the line wich raised an exception
   * @param String the message associated the exception
   */

  function LdifEntryReaderException($lineNumber,$currentLine,$message){
    $this->lineNumber = $lineNumber;
    $this->currentLine =$currentLine;
    $this->message = $message;
  }
}


/**
 * Class in charge of reading a paricular entry
 */

class LdifEntryReader{

  //the entry
  var $entry;

  // the lines of the entry fetch from the file
  var $lines;

  // the dn of the entry
  var $dn="";

  // error flag
  var $_error;

  // the current line number of the entry;
  var $_currentLineNumber;

  /**
   * Constructor of the LdifEntryReader
   *
   * @param String[] $lines the line of the entry
   */
  function LdifEntryReader( &$lines ){
    $this->lines = &$lines;
    $this->_currentLineNumber = 1;
    $this->_error = 0;
   }

  /**
   * Read the change type action associated with the entry
   *
   * @return String the change type action of the entry
   */
  function _readChangeType(){
    $changeType ="add";
    $arr = array();
    
    // no lines after the dn one
    if(count($this->lines)==0){
      $this->lines[0] = "";
      $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"Missing attibutes or changetype attribute for entry",new LdifEntry($this->dn)));
    }
    // get the change type of the entry
    elseif(ereg("changetype:[ ]*(delete|add|modrdn|moddn|modify)",$this->lines[0],$arr)){
      $changeType = $arr[1];
      array_shift($this->lines);
      $this->_currentLineNumber++;
    }
    return $changeType;
  }
  
  /**
   * Check if the distinguished name is base 64 encoded
   *
   * @return boolean true if the dn is base 64 encoded, false otherwise
   */
  function _isDnBase64Encoded($dn){
     return  ereg("dn::",$dn)?1:0;
  }
  
  /**
   * Return the base64 decoded value of an attribute
   *
   * @param $attr the attribute to be decoded
   * @return String base64 decoded value of an attribute
   */
  function _getBase64DecodedValue($attr){
    return base64_decode(trim($attr));
  }
  
  /**
   * Fetch the dn value from a line of the ldif file
   * 
   * @param String $currentDnLine  line with a distinguished name
   * @return the value of the distinguished name
   */
  function _getDnValue(){
    $currentDnLine=$this->lines[0];
    if($this->_isDNBase64Encoded($currentDnLine)){
      $currentDnValue = $this->_getBase64DecodedValue(substr($currentDnLine,4,strlen($currentDnLine)-1));
    }else{
      $currentDnValue = substr($currentDnLine,3,strlen($currentDnLine)-1);
    }
    // switch to the next line
    array_shift($this->lines);
    $this->_currentLineNumber++;
    return trim($currentDnValue);
  }

  /**
   * Check if the dn line is valid
   *
   * @return boolean true if the dn is valid, false otherwise.
   */
  function isValidDn(){
    return ereg("^dn:",$this->lines[0])?1:0;
  }

  /**
   * Return the entry read from the ldif lines
   *
   * @return LdifEntry the entry
   */
  function getEntry(){
    global $lang;

    // the dn is not valid, throw the exception and return the entry with the non valid dn
    if (! $this->isValidDn() ){
       $dn = $this->lines[0];
       $changeType = $this->_readChangeType();	 
       //For the moment, overwrite the exception
       $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],$lang['valid_dn_line_required'],new LdifEntry($this->dn)));
       return new LdifEntry( $dn , $changeType );
    }
      
    $dn=$this->_getDnValue();
    $changeType = $this->_readChangeType();	 
    $this->entry = new LdifEntry($dn,$changeType);
    
    if($changeType=="add"){
      $this->_getAddAttributes();
    }
    elseif($changeType=="delete"){
      //do nothing
    }
    elseif($changeType=="modrdn"||$changeType=="moddn"){
      $this->_getModrdnAttributes();
    }
    elseif($changeType=="modify"){
      $this->_getModifyAttributes();
    }
    return $this->entry;
  }

  /**
   * Checked if the parsing of the entry has raised some exception
   *
   * @return bool true if the reading of the entry raised some exceptions, else otherwise.
   */
  function  hasRaisedException(){
    return $this->_error;
  }

  /**
   * Set the exception handler for the entry reader
   *
   * @param  Ldap_ldifEntryReaderException the exception handler associate
   * with the parsing error.
   */

  
  function setLdifEntryReaderException($ldifEntryReaderException){
    $this->_error=1;
    $this->_ldifEntryReaderException= $ldifEntryReaderException;
  }
  

  /**
   * Return the exception handler of the entry Reader
   *
   * @return Ldap_LdifEntryReaderException the exception associate with 
   * the exception wich occur during parsing the file.
   */
  function getLdifEntryReaderException(){
    return $this->_ldifEntryReaderException;
  }

  /**
   * Method to retrieve the attribute value of a ldif line,
   * and get the base 64 decoded value if it is encoded
   */
  function _getAttributeValue($attributeValuePart){
    $attribute_value="";
    if(substr($attributeValuePart,0,1)==":"){
      $attribute_value = $this->_getBase64DecodedValue(trim(substr($attributeValuePart,1)));
    }
    elseif(substr($attributeValuePart,0,1)=="<"){
	// we need to handle the case for the scheme "file://" as it 
	//doesn't seem to be supported by fopen

      $file_path_with_scheme= trim(substr($attributeValuePart,1));
      if(ereg("^file://",$file_path_with_scheme)){

	$file_path = substr(trim($file_path_with_scheme),7);
	if($handle = @fopen($file_path, "rb")){
	  if(!$attribute_value = @fread($handle,filesize($file_path))){
	    	  $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"Unable to read file",$this->entry));
	  }
	  @fclose($handle);
	}
	else{
	  $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"Unable to open file",$this->entry));
	}
      }
      else{
	   $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"The url attribute value should begin with file:///",$this->entry));
	 }
    }
    //it's a  string
    else{
       $attribute_value = $attributeValuePart;
    }
    return trim($attribute_value);
  }
  

  /**
   * Build the attributes array when the change type is add.
   */
  function _getAddAttributes(){

    if(count($this->lines)==0){
      $this->lines[0]="";
         $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"Missing attributes for the entry",$this->entry));
    }
    //$attrs =array();
    while(count($this->lines)!=0 &&$this->_error!=1){
     $currentLine = &$this->lines[0];
     //    echo $this->_currentLineNumber;
      if(ereg(":",$currentLine)){
	
	//get the position of the  character  ":"
	$pos = strpos($currentLine,":");
	
	//get the description of the attribute
	$attributeDescription =  substr($currentLine,0, $pos);
	
	
	// get the value part of the attribute
	$attribute_value_part = trim(substr($currentLine,$pos+1,strlen($currentLine)));
	$attribute_value = $this->_getAttributeValue($attribute_value_part);
	$this->entry->attributes[$attributeDescription][] = trim($attribute_value);
	//echo count($this->entry->attributes);;
	//	$this->entry->add($attrs);
	array_shift($this->lines);
	$this->_currentLineNumber++;
      }
      else{
	$this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"Attribute not well formed",$this->entry));

	//jetter l'exception
      }
    }
  }

  /**
   * Build the attributes array for the entry when the change type is modify
   */
  function _getModifyAttributes(){
    if(count($this->lines)==0){
      $this->lines[0]="";
      $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"Missing attributes for the entry",$this->entry));
    }

    $numberModification=0;

    // while the array is not empty
    while(count($this->lines)!=0 &&$this->_error!=1){
      $new_entry_mod = 0;
      // get the current line with the action
      $currentLine = &$this->lines[0];
      $attribute= explode(":",$currentLine);

      if(count($attribute)==2){
	$action_attribute = trim($attribute[0]);
	$action_attribute_value =trim($attribute[1]);

	if($action_attribute != "add" && $action_attribute != "delete" && $action_attribute !="replace"){
	  $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"The attribute name should be add, delete or replace",$this->entry));
	}

	// put the action attribute in the array
	$this->entry->attributes[$numberModification] = array();
	$this->entry->attributes[$numberModification][$action_attribute] = $this->_getAttributeValue($action_attribute_value);
	$this->entry->attributes[$numberModification][$action_attribute_value] = array();

	// fetching the attribute for the following line
	array_shift($this->lines);
	$currentLine=&$this->lines[0];
	$this->_currentLineNumber++;
	
	while(trim($currentLine)!="-" && $this->_error!=1 && count($this->lines)!=0 ){

	  // if there is a valid line
	  if(ereg(":",$currentLine)){
	    //get the position of the  character  ":"
	    $pos = strpos($currentLine,":");
	    //get the name of the attribute to modify
	    $attribute_name =  substr($currentLine,0, $pos);

	    //check that it correspond to the one specified before
	    if ($attribute_name == $action_attribute_value){
	      
	      // get the value part of the attribute
	      $attribute_value_part = trim(substr($currentLine,$pos+1,strlen($currentLine)));
	      $attribute_value = $this->_getAttributeValue($attribute_value_part);
	      $this->entry->attributes[$numberModification][$attribute_name][]=$attribute_value;
	      array_shift($this->lines);
	      $this->_currentLineNumber++;
	      
	      if(count($this->lines)!=0)
		$currentLine = &$this->lines[0];
	    }
	    else{
	      $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"The attribute to modify doesn't match the one specified by the ".$action_attribute." attribute.",$this->entry));
	    }
	  }
	  else{
	    $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"Attribute is not valid",$this->entry));
	  }
	}// end inner while
	
	// we get a "-" charachter, we remove it from the array
	if ($currentLine == "-"){
	  array_shift($this->lines);
	  $this->_currentLineNumber++;
	}
	$numberModification++;
      }
    }
  }      

  /**
   * Build the attributes for the entry when the change type is modrdn
   */
  
  function _getModrdnAttributes(){

  $attrs = array();
  $numLines = count($this->lines);
  if($numLines != 2 && $numLines !=3){
    if($numLines==0){
      $this->lines[0]="";
    }
    $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"The entry is not valid",$this->entry));
  }    
  else{
  $currentLine = $this->lines[0];

  
  //first we need to check if there is an new rdn specified
  if(ereg("^newrdn:(:?)",$currentLine)){
    $attributeValue = $this->_getAttributeValue(trim(substr($currentLine,7)));
    $attrs['newrdn']=$attributeValue;

    //switch to the deleteoldrdn attribute
    array_shift($this->lines);
    $this->_currentLineNumber++;
$arr=array();
    if(ereg("^deleteoldrdn:[ ]*(0|1)",$this->lines[0],$arr)){
      $attrs['deleteoldrdn'] = $arr[1];
      
      //switch to the possible new superior attribute
      if($numLines>2){
	array_shift($this->lines);
	$this->_currentLineNumber++;
	$currentLine = $this->lines[0];
	
	//then the possible new superior attribute
	//if(trim($currentLine)!=""){
	  
	  if(ereg("^newsuperior:",$currentLine)){
	    $attrs['newsuperior'] = $this->_getAttributeValue(trim(substr($currentLine,12)));
	  }
	  else{
	    $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"the attribute name should be newsuperior",$this->entry));
	  }
	}
	else{
	//as the first character is not ,,we "can write it this way for teh moment"
	  if($pos =  strpos($this->entry->dn,",")){
	    $attrs['newsuperior'] = substr($this->entry->dn,$pos+1);
	  }
	else{
	  $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"Container is null",$this->entry));
	}
      }
    }
    else{
      $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"a valid deleteoldrdn attribute  should be specified",$this->entry));
    }
  }
  else{
    $this->setLdifEntryReaderException(new LdifEntryReaderException($this->_currentLineNumber,$this->lines[0],"a valid newrdn attribute  should be specified",$this->entry));
  }
  $this->entry->attributes = $attrs;
  }
  }
}

/**
 * Exception which can be raised during processing the ldif file
 */

class LdifReaderException{
  
  var $lineNumber;
  var $message;
  var $currentLine;

  /**
   * Constructor of the exception
   *
   * @param int $lineNumber the number of the line where
   *        the error occured
   * @param String currentLine the line wich raised an exception
   * @param String the message associated the exception
   */
  function LdifReaderException($lineNumber,$currentLine,$message){
    $this->lineNumber = $lineNumber;
    $this->currentLine =$currentLine;
    $this->message = $message;
  }
}

/**
 * Helper base class to read file.
 */

class FileReader{
  
  //the file pointer  
  var $_fp;
  
  //the current line number
  var $_currentLineNumber;

  // the current  line
  var $_currentLine;

  /**
   * Constructor of the FileReader class
   * @param $path2File
   */

  function FileReader($path2File){
     $this->_fp = fopen ($path2File, "r");
  }

  /**
   * Returns true if we reached the end of the file.
   *
   * @return bool  true if it's the end of file, false otherwise.
   */
  
  function eof(){
    return @feof($this->_fp);
  }

  /**
   * Helper method to switch to the next line
   */
  
  function _nextLine(){
    
    //$nextLine="";
    $this->_currentLineNumber++;
    $nextLine = fgets($this->_fp, 1024);
    $this->_currentLine = $nextLine;
    
    //need to check if it is a very long line (not folded line for example)
    while(!ereg("\n|\r\n|\r",$nextLine) && !$this->eof()){
      $nextLine = fgets($this->_fp, 1024);
      $this->_currentLine.= trim($nextLine);
    }
  }

  /**
   * Check if is the current line is a blank line.
   *
   * @return bool  if it is a blank line,false otherwise.
   */
  
  function _isBlankLine(){
    return(trim($this->_currentLine)=="")?1:0;
  }

  /**
   * Close the handler
   */
  function close(){
    return @fclose($this->_fp);
  }
}

/**
 * Main parser of the ldif file
 */

class LdifReader extends FileReader{
  
  // the current entry
  var $_currentEntry;
  
  // array containing the lines of the current entry
  var $_currentLines;

  // the reader of the entry
  var $_ldapEntryHandler;

  //boolean flag for error
  var $_error; 
  
  // warning message. Only use for the verion number
  var $_warningMessage;

  // TODO What is this variable? Used below, why?
  var $_warningVersion;

  var $_dnLineNumber;

  // continuous mode operation flag
  var $continuous_mode;

  /**
   * Private constructor of the LDIFReader class.
   * Marked as private as we need to instantiate the class
   * by using other constructor with parameters
   * Use to initialize instance members.
   */
  
  function _LdifReader(){
    $this->_error=0;
    $this->_warning=0;
    $this->_currentLineNumber=0;
    $this->_currentLines = array();
    $this->_warningMessage="";
    $this->_warningVersion="";
    //need to change this one
    $this->_currentEntry = new LdifEntry();
  }
  
  /**
   * Constructor of the class
   *
   * @param String $path2File path of the ldif file to read
   * @param boolean $continuous_mode 1 if continuous mode operation, 0 otherwise
   */
  function  LdifReader( $path2File , $continuous_mode = 0 ){
    parent::FileReader( $path2File );
    $this->continuous_mode = $continuous_mode;
    $this->_LdifReader();
  }
  
  /**
   * Returns the lines that generated the Ldif Entry.
   *
   * @return String[] The lines from the entry.
   */
  function getCurrentLines(){
    return $this->_currentLines;
  }
  
  /**
   * Check if it's a ldif comment line.
   * 
   * @return bool true if it's a comment line,false otherwise
   */
  function _isCommentLine(){
    return substr(trim($this->_currentLine),0,1)=="#"?1:0;
  }
  
  /**
   * Check if the current line is a line containing the distinguished
   * name of an entry.
   *
   * @return bool true if the line contains a dn line, false otherwise.
   */
  function _isDnLine(){
    return ereg("^dn:",$this->_currentLine)?1:0;
  }
  
  /**
   * Return the current entry object 
   *
   * @return Ldap_Ldif_entry the current ldif entry
   */
  function getCurrentEntry(){
    return $this->_currentEntry;
  }
  
  /**
   * Get the lines of the next entry
   *
   * @return String[] the lines (unfolded) of the next entry
   */
  function nextLines(){
    $endEntryFound=0;	
    //free the array (instance member)
    unset($this->_currentLines);
    
    // reset the error state
    $this->_error = 0;

    if( $this->_hasMoreEntries() && !$this->eof() ){
      
      //the first line is the dn one
      $this->_currentLines[0]= trim($this->_currentLine);
      
      $count=0;
      
      // while we end on a blank line, fetch the attribute lines
      while(!$this->eof() && !$endEntryFound ){
	//fetch the next line
	$this->_nextLine();
	
	// if the next line begin with a space,we append it to the current row
	// else we push it into the array (unwrap)
	
	if(substr($this->_currentLine,0,1)==" "){
	  $this->_currentLines[$count].=trim($this->_currentLine);
	}
	elseif(substr($this->_currentLine,0,1)=="#"){
	  //do nothing
	  // echo $this->_currentLine;
	}
	elseif(trim($this->_currentLine)!=""){
	  $this->_currentLines[++$count]=trim($this->_currentLine);
	}
	else{
	  $endEntryFound=1;
	}
      }//end while
      //return the ldif entry array
      return $this->_currentLines;
    }
    else{
      return false;
    }
  }

  /**
   * Check if the ldif version is present in the ldif
   *
   * @return true if a version line was found,false otherwise
   */
  
  function hasVersionNumber(){
    $ldifLineFound = 0;

    while(!$this->eof() && !$ldifLineFound){

      // fetch the first line
      $this->_nextLine();
      
      // if it's a ldif comment line or a blank line,leave it and continue
      if($this->_isCommentLine() || $this->_isBlankLine()){
	//debug usage
	//echo $this->_currentLineNumber." - " .($this->_isCommentLine()?"comment":"blank line\n")."<br>";
      }
      elseif(ereg("^version",trim($this->_currentLine))){
	$ldifLineFound=1;
	$this->_nextLine();
      }
      else{
	$this->_warningVersion=1;
	global $lang;
	$this->_warningMessage = $lang['warning_no_ldif_version_found'];
	$ldifLineFound=1;
      }
    }// end while loop
    return $this->_warningVersion;
  }


  /**
   * Private method to check if there is more entries in the file
   *
   * @return boolean true if an entry was found, false otherwise.
   */
  function _hasMoreEntries(){
    $entry_found = 0;
    while( !$this->eof() && !$entry_found  ){
      //if it's a comment or blank line,switch to the next line
      if( $this->_isCommentLine() || $this->_isBlankLine() ){
	//debug usage
	// echo $this->_currentLineNumber." - " .($this->_isCommentLine()?"comment":"blank line\n")."<br>";
	$this->_nextLine();
      }
      else{
	   $this->_currentDnLine = $this->_currentLine;
	   $this->dnLineNumber = $this->_currentLineNumber;
	  $entry_found=1;
      }
    }
    return $entry_found;
  }
  
  /**
   * Associate the ldif reader with a exception which occurs during
   * proceesing the file.
   * Easier to do exception if we had to switch to php5
   *
   * @param Ldap_LdifReaderException $ldifReaderException 
   */
  function setLdapLdifReaderException($ldifReaderException){
    $this->_ldifReaderException= $ldifReaderException;
    if( !$this->continuous_mode )
      $this->done();
    $this->_error=1;
  }

  /**
   * Return the exception raised during processing the file
   *
   * @return Ldap_ldifReaderException
   */
  function getLdapLdifReaderException(){
    return $this->_ldifReaderException;
  }
  
  /**
   * Helper method which return the value 
   * of the instance member $_error
   *
   * @return true if an error was encountered, false otherwise
   */
  function _ldifHasError(){
    return  $this->_error;
  }

  function hasRaisedException(){
     return  $this->_error;

  }

  /**
   *  Close the ldif file
   *
   * @return void
   */

  
  function done(){
    if (!$this->_ldifHasError()){
      @fclose($this->_fp);
    }

  }

  /**
   * Return a ldif entry object
   *
   * @return LdifEntry the entry object buid from the lines of the ldif file
   */

  function readEntry(){

    if($lines = $this->nextLines()){
      $ldifEntryReader = new LdifEntryReader($lines);
      //fetch entry
      $entry = $ldifEntryReader->getEntry();
      $this->_currentEntry = $entry;
      // if any exception has raised, catch it and throw it to the main reader
      if($ldifEntryReader->hasRaisedException()){
	$exception = $ldifEntryReader->getLdifEntryReaderException();
	$faultyLineNumber = $this->dnLineNumber + $exception->lineNumber - 1;
	$this->setLdapLdifReaderException(new LdifReaderException($faultyLineNumber,$exception->currentLine,$exception->message));

	if ( ! $this->continuous_mode )
	  return 0;
      }
      return $entry;
    }
    else
      return false;
  }

  function getWarningMessage(){
      return $this->_warningMessage;
  }

}

/**
 * Helper class to write entries into the ldap server
 */

class LdapWriter{
  
  var $ldapConnexion;
  var $_writeError=0;

  /**
   * Constructor
   */
  
  function LdapWriter(&$conn){
    $this->ldapConnexion = &$conn;
  }

  /**
   * Add a new entry to the ldap server
   *
   * @param LdifEntry $entry the entry to add
   * @return true in case of success, false otherwise
   */
  function ldapAdd($entry){
    return @ldap_add($this->ldapConnexion,$entry->dn,$entry->attributes);
  }

  
  /**
   * Modify an entry
   *
   * @param LdifEntry $entry the entry to add
   * @return true in case of success, false otherwise
   */

  function ldapModify($entry){
    $changeType = $entry->getChangeType();
    
    switch($changeType){
    case "add":
      $this->_writeError= $this->ldapAdd($entry);
      break;
    case "delete":
      $this->_writeError = @ldap_delete($this->ldapConnexion,$entry->dn);
      break;
    case "modrdn":
      $atts=$entry->getAttributes();
      $this->_writeError = @ldap_rename( $this->ldapConnexion, $entry->dn,$atts['newrdn'], $atts['newsuperior'], $atts['deleteoldrdn'] );
      break;
    case "moddn":
      $atts=$entry->getAttributes();
      $this->_writeError = @ldap_rename( $this->ldapConnexion, $entry->dn,$atts['newrdn'], $atts['newsuperior'], $atts['deleteoldrdn'] );
      break;
    case "modify":
      $attributes = $entry->attributes;
      $num_attributes = count($attributes);
      for($i=0;$i<$num_attributes;$i++)
      {
	$action = key($attributes[$i]);
	array_shift($attributes[$i]);
	switch($action){
	case "add":
	  $this->_writeError =  @ldap_mod_add($this->ldapConnexion,$entry->dn,$attributes[$i]);
	  break;
	case "delete":
	  $this->_writeError =  @ldap_mod_del($this->ldapConnexion,$entry->dn,$attributes[$i]);
	  break;
	case "replace":
	  $this->_writeError =  @ldap_mod_replace($this->ldapConnexion,$entry->dn,$attributes[$i]);
	  break;
	}
      }
    }
    return $this->_writeError;
  }

  /**
   * Close the connection to the ldap server
   * 
   * @return boolean true in case of success, false otherwise
   */
  function ldapClose(){
   return @ldap_close($this->ldapConnexion);
  }
}
?>
