<?php
/**
 * file: ldif_functions.php
 * ------------------------
 * Declare the functions for handling ldif file
 * this is considered as beta version, there is some things to improve:
 *  -change some method
 *  -make it more modular 
 *  -review error message
 *  -handle the number version
 *  -review regexp
 *  -make a class ?
 *  -handle base64 for modrdn
 */

//warning flag;
$warning=0;

// warning message if any
$warning_message;

// boolean flag in case of error
$error=0;

// the error mesage if any
$error_message;

// to keep track of the line number while parsing the file
$line_number = 0;

// keep track of the current line while parsing the file
$current_line;



$fp;

function ldif_open_file($file_name){
  global $fp;
  $fp=fopen($file_name,"r");

}


function ldif_eof(){
  global $fp;
  return feof($fp);
}

/**
 * Check if the current line is a comment line
 *
 * @return true if the current line is a ldif comment,false otherwise
 */

function ldif_is_comment_line(){
  global $current_line;
  return substr(trim($current_line),0,1)=="#"?1:0;
}

/**
 * Check if is the curent line is a blank line
 *
 * @return true if it is a blank line,false otherwise
 */

function ldif_is_blank_line(){
 global $current_line;
 return(trim($current_line)=="")?1:0;
}

/**
 * Check if the current line is a line with a dn
 *
 * @return true if the line contains a dn line, false otherwise.
 */

function ldif_is_dn_line(){
  global $current_line;
  return ereg("^dn:",$current_line)?1:0;

}


/**
 * Fetch the next line of the ldif file
 */

function ldif_next_line(){
  global $line_number;
  global $current_line;
  global $fp;
  
  $current_line = fgets($fp,1024);
  while(!ereg("\n|\r\n",$current_line)&&!ldif_eof()){
    $current_line.=fgets($fp,1024);
  }

  $line_number++;

}



/**
 * Get the version of the ldif file
 *
 **/

function ldif_check_version(){
  global $warning;
  global $warning_message;
  global $error_message;
  global $error;
  global $line_number;
  global $current_line;

  // boolean flag to see if any valid ldif line is found
  $ldif_line_found=0;
  
  while(!ldif_eof()&&!$ldif_line_found &&!$warning){

    //get the first line
    ldif_next_line();

    // skip line with comment and blank line
    if(ldif_is_comment_line()||ldif_is_blank_line()){
      //debug usage
      //echo "$line_number -".(ldif_is_comment_line()?"comment":"blank line")."<br/>";
    }
    elseif(ereg("^version",trim($current_line))){
      $ldif_line_found=1;
      ldif_next_line();
      // TODO: handle the version number here
    }
    //    not valid ldif line was found
    else{
      //set the flag warning
      $warning=1;
      $warning_message = "No version found - assuming 1";
    }
    
  }//end while

  $warning?0:1;
}// end get_version() method



/**
 * Return a warning message
 *
 * @return a warning message
 */

function ldif_warning_message(){
  global $warning_message;
  return $warning_message;
}

function ldif_error_message(){
  global $error_message;
  return $error_message;
}

/**
 * Check if the file is valie
 *
 *
 */

function is_valid_file($path_to_file){
  
  return file_exists($path_to_file)&&is_file($path_to_file)&&is_readable($path_to_file);

}

/**
 * Close the file
 *
 *
 */

function ldif_close(){
  global $fp;
  @fclose($fp);
}

/**
 * Return the action that the ldap server should do with an entry
 * The default action is add.
 *
 * @return the action the server should do.
 */

function ldif_get_action(){
  global $current_line;
  
  //  default action is add
  $action ="add";
  if(ereg("changetype:[ ]*(delete|add|modrdn|moddn|modify)",$current_line,$arr)){
    $action = $arr[1];
    ldif_next_line();
  }
  return $action;
}


/**
 * Retrieve the dn of an entry
 
 * @return the dn value of an entry
 */

function ldif_fetch_dn_entry(){

  global $error;
  global $current_line;
  global $error_message;
  global $line_number;
  global $number_of_lines;
  // the value of the dn
  $current_dn_value;

  //boolean flag to see if any dn was found
  $dn_found=0;
  

    while(!ldif_eof()&&!$dn_found&&!$error){

      //skip comment and blank line
      if(ldif_is_blank_line()||ldif_is_comment_line()){
	//debug
	//echo "$line_number - comment\n<br>";
	ldif_next_line();
      }
      // case where the dn is found
      elseif(ldif_is_dn_line()){
	$current_dn=$current_line;
	//debug
	//echo "$line_number - $current_line<br>";
	$dn_found=1;
      }
      else{
	$error=1;
	$error_message = "Error: Line ".$line_number." - a valid dn is required";
	return false;
      }
      
    }//fin while
  

  
    // if mainly to hanlde the case 
    //where a dn was found and is written on several lines
    // could be use to test if any white line appears after
    if($dn_found&&!ldif_eof()){

      //boolean flag to see if the dn is written on more than one line
      $dn_has_next=0;
      
      //keeping track of the line number of the dn when first encountered
      $dn_line_number = $line_number;
      // empty string to store the possible following lines
      while(!$dn_has_next){
	ldif_next_line();

	//if next line begin with a space and is not empty
	// append to the previous line
	if((substr($current_line,0,1)==" ")){
	  ereg_replace("\n|\r|\r\n","",$current_dn =trim($current_dn).trim($current_line));
	}
	//else leaving the while loop
	else{
	  $dn_has_next=1;

	}
	
      }//end while
      
      //debug
      //echo $dn_line_number." - ".$current_dn."\r\n<br>";
      // handle base 64 case here
      if(ereg("^dn::",$current_dn)){

	  $current_dn = base64_decode(trim(substr($current_dn,4,strlen($current_dn)-1)));
	  
      }else{
	  $current_dn = trim(substr($current_dn,3,strlen($current_dn)-1));

	  
      }
      //            echo $dn_line_number."-".$current_dn."<br />";
    }

    return $current_dn;
}


/**
 * functions which will return a array of attributes
 * for this entry
 *
 * @return attrs the array of attributes
 */

function ldif_fetch_attributes_for_entry(){
  global $error;
  global $current_line;
  global $line_number;
  global $error_message;

  global $server_id;
  $attribute_value="";

  //array to store the attribute
  $attrs = array();


  // while we dont find any empty line or any error occur
  while(trim($current_line)!=""&&$error!=1){

    
    //1 - we need to check if it's a valid ldif line.
    //-----------------------------------------------
   
    if(ereg(":",trim($current_line),$arr)&&substr($current_line,0,1)!=" "){
      
      //get the position of the  character  ":"
      $pos = strpos($current_line,":");
      
      //get the name of the attribute
      
      $attribute_name = substr($current_line,0, $pos);
      // get the value part of the attribute
      $attribute_value_part = trim(substr($current_line,$pos+1,strlen($current_line)));
      
      //2 - we get the description and the "value" part
      // now  check if it's split over several lines
      
      $att_has_next = 0;
      $attr_line_number=$line_number;
      
      while(!$att_has_next){
	ldif_next_line();

	//if there is a another line for the attribute value,it should:
	// 1 - begin with a white space
	// 2 - have a length > 1
	// 3 - not have the character :
	if((substr($current_line,0,1)==" ")&&!ereg(":",trim($current_line))){
	  ereg_replace("\n|\r|\r\n","",$attribute_value_part =trim($attribute_value_part).trim($current_line));
	}
	else{
	  $att_has_next=1;
	}

      }

      //3  - need to see if the value is base 64 encoded,an url
      //-------------------------------------------------------
      $attribute_value="";
      //if the next character in the value part is ":";it's it base 64 encoded
      if(substr($attribute_value_part,0,1)==":"){

	// it is binary data,so we just decode it
	  $attribute_value = base64_decode(trim(substr($attribute_value_part,1)));

      }
      //else if the next charachter is <, we have to handle an url
      elseif(substr($attribute_value_part,0,1)=="<"){
	// we need to handle the case for the scheme "file://" as it 
	//doesn't seem to be supported by fopen

	 $file_path_with_scheme= trim(substr($attribute_value_part,1));
	
	 if(ereg("^file://",$file_path_with_scheme)){

	   $file_path = substr(trim($file_path_with_scheme),7);
	   if($handle = @fopen($file_path, "rb")){
	     $attribute_value = @fread($handle,filesize($file_path));
	     @fclose($handle);
	   }
	   else{
	     $error=1;
	     $error_message = "Line: ".$attr_line_number. " - Unable to open file ".
	       $file_path_with_scheme;
	     return false;
	   }
	 }
	 else{
	   $error=1;
	   $error_message = "Line: ".$attr_line_number. " - the URL seems no to be well formed ";
	   return false;
	 }
      }
      //it's a  string
      else{
	$attribute_value = trim($attribute_value_part);
      }

      $attrs[$attribute_name][] = $attribute_value;
      //debug
      // echo $attr_line_number."- ".$attribute_name.":".$attribute_value."\r\n<br>";      

    }
    //  the attribute line contains the charachter ":" or there is 
    // at least a white space at the begining
    else{
      $error_message = "Line ". $line_number. " - Attribute not well formed";
      $error=1;
      return false;
    }
  }
  return $attrs;
}


