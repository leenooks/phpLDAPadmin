<?php

/*
 * Represents an LDAP objectClass
 */
class ObjectClass
{
	/* This objectClass' OID, ie "2.16.840.1.113730.3.2.2" */
	var $oid;
	/* This objectClass' name, ie "inetOrgPerson" */
	var $name;
	/* This objectClass' description */
	var $description;
	/* array of objectClass names from which this objectClass inherits */
	var $sup_classes;
	/* one of STRUCTURAL, ABSTRACT, or AUXILIARY */
	var $type;
	/* arrays of attribute names that this objectClass requires */
	var $must_attrs;
	/* arrays of attribute names that this objectClass allows, but does not require */
	var $may_attrs;
	/* boolean value indicating whether this objectClass is obsolete */
	var $is_obsolete;

	/* Initialize the class' member variables */
	function initVars()
	{
		$this->oid = null;
		$this->name = null;
		$this->description = null;
		$this->sup_classes = array();
		$this->type = null;
		$this->must_attrs = array();
		$this->may_attrs = array();
		$this->is_obsolete = false;
	}

	/*
	 * Parses a raw LDAP objectClass string into this object's $this vars
	 */
	function ObjectClass( $raw_ldap_schema_string )
	{
		$this->initVars();
		$class = $raw_ldap_schema_string;
		$strings = preg_split ("/[\s,]+/", $class, -1,PREG_SPLIT_DELIM_CAPTURE);
		for($i=0; $i<count($strings); $i++) {
			switch($strings[$i]) {
				case '(':
					break;
				case 'NAME':
					if($strings[$i+1]!="(") {
						do {
							$i++;
							if(strlen($this->name)==0)
								$this->name = $strings[$i];
							else
								$this->name .= " " . $strings[$i];
						}while(!preg_match("/\'$/s", $strings[$i]));
					} else {
						$i++;
						do {
							$i++;
							if(strlen($this->name) == 0)
 								$this->name = $strings[$i];
							else
								$this->name .= " " . $strings[$i];
						} while(!preg_match("/\'$/s", $strings[$i]));
						do {
							$i++;
						}while($strings[$i]!=")");
					}
					break;
				case 'DESC':
					do {
						$i++;
						if(strlen($this->description)==0)
							$this->description=$this->description . $strings[$i];
						else
							$this->description=$this->description . " " . $strings[$i];
					}while(!preg_match("/\'$/s", $strings[$i]));
					break;
				case 'OBSOLETE':
					$this->is_obsolete = TRUE;
					break;
				case 'SUP':
					if($strings[$i+1]!="(") {
						$i++;
						array_push ($this->sup_classes, $strings[$i]);
					}else{
						$i++;
						do {
							$i++;
							if($strings[$i]!="$")
								array_push( $this->sup_classes, $strings[$i] );
						}while($strings[$i+1]!=")");
					}
					break;
				case 'ABSTRACT':
					$this->type='abstract';
					break;
				case 'STRUCTURAL':
					$this->type='structural';
					break;
				case 'AUXILIARY':
					$this->type='auxiliary';
					break;
				case 'MUST':
					if($strings[$i+1]!="(") {
						$i++;
						array_push ($this->must_attrs, $strings[$i]);
					}else{
						$i++;
						do {
							$i++;
							if($strings[$i]!="$")
								array_push ($this->must_attrs, $strings[$i]);
						}while($strings[$i+1]!=")");
					}
					sort($this->must_attrs);
					break;
				case 'MAY':
					if($strings[$i+1]!="(") {
						$i++;
						array_push ($this->may_attrs, $strings[$i]);
					}else{
						$i++;
						do
						{
							$i++;
							if($strings[$i]!="$")
								array_push ($this->may_attrs, $strings[$i]);
						}while($strings[$i+1]!=")");
					}
					sort($this->may_attrs);
					break;
				default:
					if(preg_match ("/[\d\.]+/i",$strings[$i]))
						$this->oid = $strings[$i];
			}
		}

		$this->name =        preg_replace("/^\'/", "", $this->name);
		$this->name =        preg_replace("/\'$/", "", $this->name);
		$this->description = preg_replace("/^\'/", "", $this->description);
		$this->description = preg_replace("/\'$/", "", $this->description);
	}

	/* Getters */
	function getMustAttrs()
	{
		return $this->must_attrs;
	}

	function getMayAttrs() 
	{
		return $this->may_attrs;
	}

	function getName()
	{
		return $this->name;
	}

	function getDescription()
	{
		return $this->description;
	}

	function getSupClasses()
	{
		return $this->sup_classes;
	}

	function getType()
	{
		return $this->type;
	}

	function getIsObsolete()
	{
		return $this->is_obsolete;
	}

	/*
	 * Adds the specified array of attributes to this objectClass' list of 
	 * MUST attributes. The resulting array of must attributes will contain
	 * unique members.
	 */
	function addMustAttrs( $new_must_attrs )
	{
		if( ! is_array( $new_must_attrs ) )
			return;
		if( 0 == count( $new_must_attrs ) )
			return;
		$this->must_attrs = array_values( array_unique( array_merge( $this->must_attrs, $new_must_attrs  ) ) );
	}

	/*
	 * Behaves identically to addMustAttrs, but it operates on the MAY
	 * attributes of this objectClass.
	 */
	function addMayAttrs( $new_may_attrs )
	{
		if( ! is_array( $new_may_attrs ) )
			return;
		if( 0 == count( $new_may_attrs ) )
			return;
		$this->may_attrs = array_values( array_unique( array_merge( $this->may_attrs, $new_may_attrs  ) ) );
	}

	/*
	 * Returns an associative array of this objectClass.
	 * This exists for backwards compatibility for portions of PLA
	 * that have not yet been made aware of the new object oriented
	 * ObjectClass code.
	 */
	function toAssoc()
	{
		return array (
			'oid' =>         $this->oid,
			'name' =>        $this->name,
			'description' => $this->description,
			'sup' =>         $this->sup_classes,
			'type' =>        $this->type,
			'must_attrs' =>  $this->must_attrs,
			'may_attrs' =>   $this->may_attrs,
			'is_obsolete' => $this->is_obsolete );
	}
}

/*
 * Represents an LDAP AttributeType 
 */
class AttributeType
{
	/* The OID of this attributeType: ie, 1.2.3.4*/
	var $oid;
	/* The name of this attributeType */
	var $name;
	/* string: the description */
	var $description;
	/* boolean: is it obsoloete */
	var $is_obsolete;
	/* The attribute from which this attribute inherits (if any) */
	var $sup_attribute;
	/* The equality rule used */
	var $equality;
	/* The ordering of the attributeType */
	var $ordering;
	/* Boolean: supports substring matching? */
	var $sub_str;
	/* The full syntax string, ie 1.2.3.4{16} */
	var $syntax;
	/* boolean: is single valued only? */
	var $is_single_value;
	/* boolean: is collective? */
	var $is_collective;
	/* boolean: can use modify? */
	var $is_no_user_modification;
	/* The usage string set by the LDAP schema */
	var $usage;
	/* An array of alias attribute names, strings */
	var $aliases;
	/* The max number of characters this attribute can be */
	var $max_length;
	/* A string description of the syntax type (taken from the LDAPSyntaxes) */
	var $type;
	/* An array of objectClasses which use this attributeType (must be set by caller) */
	var $used_in_object_classes;

	/* 
	 * Initialize the class' member variables 
	 */
	function initVars()
	{
		$this->oid = null;
		$this->name = null;
		$this->description = null;
		$this->is_obsolete = false;
		$this->sup_attribute = null;
		$this->equality = null;
		$this->ordering = null;
		$this->sub_str = null;
		$this->syntax_oid = null;
		$this->syntax = null;
		$this->max_length = null;
		$this->is_single_value= false;
		$this->is_collective = false;
		$this->is_no_user_modification = false;
		$this->usage = null;
		$this->aliases = array();
		$this->type = null;
		$this->used_in_object_classes = array();
	}

	/*
	 * Parses a raw LDAP objectClass string into this object's $this vars
	 */
	function AttributeType( $raw_ldap_attr_string )
	{
		//echo "<nobr>$raw_ldap_attr_string</nobr><Br />";
		$this->initVars();
		$attr = $raw_ldap_attr_string;
		$strings = preg_split ("/[\s,]+/", $attr, -1,PREG_SPLIT_DELIM_CAPTURE);
		for($i=0; $i<count($strings); $i++) {
			switch($strings[$i]) {
				case '(':
					break;
				case 'NAME':
					if($strings[$i+1]!="(") {
						do {
							$i++;
							if(strlen($this->name)==0)
								$this->name = $strings[$i];
							else
								$this->name .= " " . $strings[$i];
						}while(!preg_match("/\'$/s", $strings[$i]));
						// this attribute has no aliases
						$this->aliases = array();
					} else {
						$i++;
						do {
							$i++;
							if(strlen($this->name) == 0)
 								$this->name = $strings[$i];
							else
								$this->name .= " " . $strings[$i];
						} while(!preg_match("/\'$/s", $strings[$i]));
						// add alias names for this attribute
						while($strings[++$i]!=")") {
							$alias = $strings[$i];
							$alias = preg_replace("/^\'/", "", $alias );
							$alias = preg_replace("/\'$/", "", $alias );
							$this->aliases[] = $alias;
						}
					}
					break;
				case 'DESC':
					do {
						$i++;
						if(strlen($this->description)==0)
							$this->description=$this->description . $strings[$i];
						else
							$this->description=$this->description . " " . $strings[$i];
					}while(!preg_match("/\'$/s", $strings[$i]));
					break;
				case 'OBSOLETE':
					$this->is_obsolete = TRUE;
					break;
				case 'SUP':
					$i++;
					$this->sup_attribute = $strings[$i];
					break;
				case 'EQUALITY':
					$i++;
					$this->equality = $strings[$i];
					break;
				case 'ORDERING':
					$i++;
					$this->ordering = $strings[$i];
					break;
				case 'SUBSTR':
					$i++;
					$this->sub_str = $strings[$i];
					break;
				case 'SYNTAX':
					$i++;
					$this->syntax = $strings[$i];
					$this->syntax_oid = preg_replace( "/{\d+}$/", "", $this->syntax );
					// does this SYNTAX string specify a max length (ie, 1.2.3.4{16})
					if( preg_match( "/{(\d+)}$/", $this->syntax, $this->max_length ) )
						$this->max_length = $this->max_length[1];
					else 
						$this->max_length = null;
					if($strings[$i+1]=="{")	{
						do {
							$i++;
							$this->name .= " " . $strings[$i];
						} while($strings[$i]!="}");
					}
					break;
				case 'SINGLE-VALUE':
					$this->is_single_value = TRUE;
					break;
				case 'COLLECTIVE':
					$this->is_collective = TRUE;
					break;
				case 'NO-USER-MODIFICATION':
					$this->is_no_user_modification = TRUE;
					break;
				case 'USAGE':
					$i++;
					$this->usage = $strings[$i];
					break;
				default:
					if(preg_match ("/[\d\.]+/i",$strings[$i]))
						$this->oid = $strings[$i];
			}
		}

		$this->name =        preg_replace("/^\'/", "", $this->name);
		$this->name =        preg_replace("/\'$/", "", $this->name);
		$this->description = preg_replace("/^\'/", "", $this->description);
		$this->description = preg_replace("/\'$/", "", $this->description);
	}

	/* Getters */
	function getOID()
	{
		return $this->oid;
	}

	function getName()
	{
		return $this->name;
	}

	function getDescription()
	{
		return $this->description;
	}

	function getIsObsolete()
	{
		return $this->is_obsolete;
	}

	function getUsage()
	{
		return $this->usage;
	}

	function getSupAttribute()
	{
		return $this->sup_attribute;
	}

	function getEquality()
	{
		return $this->equality;
	}

	function getOrdering()
	{
		return $this->ordering;
	}

	function getSubstr()
	{
		return $this->sub_str;
	}

	function getAliases()
	{
		return $this->aliases;
	}

	/*
	 * Returns the entire raw syntax string for this attr, for example: 1.2.3.4{16}
	 */
	function getSyntaxString()
	{
		return $this->syntax;
	}

	/*
	 * Differs from getSyntaxString() in that it only returns the actual OID with any length
	 * specification removed. Ie, if the syntax string is 1.2.3.4{16}, this retruns
	 * 1.2.3.4. 
	 */
	function getSyntaxOID()
	{
		return $this->syntax_oid;
	}

	/*
	 * Returns the maximum length specified by this attribute (ie, "16" in 1.2.3.4{16})
	 */
	function getMaxLength()
	{
		return $this->max_length;
	}

	function getIsSingleValue()
	{
		return $this->is_single_value;
	}

	function getIsCollective()
	{
		return $this->is_collective;
	}

	function getIsNoUserModification()
	{
		return $this->is_no_user_modification;
	}

	function getType()
	{
		return $this->type;
	}

	/*
	 * Removes an attribute name from the alias array.
	 */
	function removeAlias( $remove_alias_name )
	{
		foreach( $this->aliases as $i => $alias_name ) {
			if( 0 == strcasecmp( $alias_name, $remove_alias_name ) ) {
				unset( $this->aliases[ $i ] );
				$this->aliases = array_values( $this->aliases );
				return true;
			}
		}
		return false;
	}

	/*
	 * Adds an attribute name to the alias array.
	 */
	function addAlias( $new_alias_name )
	{
		$this->aliases[] = $new_alias_name;
	}

	function setName( $new_name )
	{
		$this->name = $new_name;
	}

	function setDescription( $new_desc )
	{
		$this->description = $new_desc;
	}
	
	function setSupAttribute( $new_sup_attr )
	{
		$this->sup_attribute = $new_sup_attr;
	}

	function setAliases( $new_aliases )
	{
		$this->aliases = $new_aliases;
	}

	function setType( $new_type )
	{
		$this->type = $new_type;
	}

	function addUsedInObjectClass( $object_class_name )
	{
		if( ! in_array( $object_class_name, $this->used_in_object_classes ) ) {
			$this->used_in_object_classes[] = $object_class_name;
		}
	}

	function getUsedInObjectClasses()
	{
		return $this->used_in_object_classes;
	}
}

/* 
 * Returns an associative array of objectClasses for the specified 
 * $server_id. Each array entry's key is the name of the objectClass
 * in lower-case. 
 * The sub-entries consist of sub-arrays called 'must_attrs' and 
 * 'may_attrs', and sub-entries called 'oid', 'name' and 'description'.
 *
 * The bulk of this function came from the good code in the 
 * GPL'ed LDAP Explorer project. Thank you.
 */
function get_schema_objectclasses( $server_id )
{
	// cache the schema to prevent multiple schema fetches from LDAP server
	static $cache = array();
	if( isset( $cache[$server_id] ) ) {
		//echo "Using oclass cache.<br />";
		return $cache[$server_id];
	}

	$ds = pla_ldap_connect( $server_id );
	if( ! $ds )
		return false;
	
	// try with the standard DN
	$result = @ldap_read($ds, 'cn=subschema', '(objectClass=*)',
				array( 'objectclasses' ), 0, 200, 0, LDAP_DEREF_ALWAYS );
	if( ! $result )
		// try again, with a different schema DN
		$result = @ldap_read($ds, 'cn=schema', '(objectClass=*)',
				array( 'objectclasses' ), 0, 200, 0, LDAP_DEREF_ALWAYS );
	if( ! $result ) 
		// give up
		return false;

	$raw_oclasses = @ldap_get_entries($ds,$result );
	
	// build the array of objectClasses
	$object_classes = array();
	for( $i=0; $i < $raw_oclasses[0]['objectclasses']['count']; $i++ ) {
		$class_string = $raw_oclasses[0]["objectclasses"][$i];
		if( $class_string == null || 0 == strlen( $class_string ) )
			continue;
		$object_class = new ObjectClass( $class_string );
		$name = $object_class->getName();
		$key = strtolower( $name );
		$object_classes[ $key ] = $object_class->toAssoc();
	}

	// go back and add any inherited MUST/MAY attrs to each objectClass
	foreach( $object_classes as $name => $object_class ) {
		$sup_classes = $object_class['sup'];
		$must = $object_class['must_attrs'];
		$may = $object_class['may_attrs'];

		foreach( $sup_classes as $sup_class )
			add_sup_class_attrs( $name, $sup_class, $object_classes, $must, $may );
		$object_classes[ $name ][ 'must_attrs' ] = $must;
		$object_classes[ $name ][ 'may_attrs' ] = $may;
	}

	ksort( $object_classes );

	// cache the schema to prevent multiple schema fetches from LDAP server
	$cache[ $server_id ] = $object_classes;
	return( $object_classes );
}

/*
 * Helper function for get_schema_objectclasses. This is a recursive function that 
 * will add MUST and MAY attributes based on an objectclas' inherited objectclasses.
 */
function add_sup_class_attrs( $oclass, $sup_class, &$oclasses, &$must_attrs, &$may_attrs )
{
	//echo "add_sup_class_attrs( $oclass, $sup_class )<br />";
	// base cases
	if( 0 == strcasecmp( $sup_class, 'top' ) ) return;
	if( ! isset( $oclasses[ strtolower( $sup_class ) ] ) ) return;

	// recursive case
	$new_must = $oclasses[ strtolower( $sup_class ) ]['must_attrs'];
	$new_may =  $oclasses[ strtolower( $sup_class ) ]['may_attrs'];
	$must_attrs = array_unique( array_merge( $new_must, $must_attrs ) );
	$may_attrs = array_unique( array_merge( $new_may, $may_attrs ) );

	$sup_classes = $oclasses[ strtolower( $sup_class ) ]['sup'];
	if( is_array( $sup_classes ) && count( $sup_classes ) > 0 )
		foreach( $sup_classes as $sup_sup_class )
			add_sup_class_attrs( $sup_class, $sup_sup_class, $oclasses, $must_attrs, $may_attrs );
}

/*
 * Retrieves the schema for a single attribute.
 */
function get_schema_attribute( $server_id, $attr_name ) 
{
	$attr_name = preg_replace( "/;.*$/U", "", $attr_name );
	$schema_attrs = get_schema_attributes( $server_id );
	$attr_name = strtolower( $attr_name );
	$schema_attr = isset( $schema_attrs[ $attr_name ] ) ?
				$schema_attrs[ $attr_name ] :
				null;
	return $schema_attr;
}

/* 
 * Returns an associative array of attributes for the specified 
 * $server_id. Each array entry's key is the name of the attribute,
 * in lower-case.
 * The sub-entries are 'oid', 'syntax', 'equality', 'substr', 'name',
 * and 'single_value'.
 *
 * The bulk of this function came from the good code in the 
 * GPL'ed LDAP Explorer project. Thank you. It was extended
 * considerably for application here.
 */
function get_schema_attributes( $server_id, $lower_case_keys = false )
{
	// Cache gets filled in later (bottom). each subsequent call uses
	// the cache which has the attributes already fetched and parsed
	static $cache = null;
	if( isset( $cache[ $server_id ] ) ) { 
		//echo "Using attr cache<br />";
		return $cache[ $server_id ];
	}
	
	$ds = pla_ldap_connect( $server_id );
	if( ! $ds )
		return false;

	// get all the attributeTypes
	$result = @ldap_read($ds, 'cn=subschema', '(objectClass=*)',
				array( 'attributeTypes' ), 0, 200, 0, LDAP_DEREF_ALWAYS );
	if( ! $result )
		$result = @ldap_read($ds, 'cn=schema', '(objectClass=*)',
				array( 'attributeTypes' ), 0, 200, 0, LDAP_DEREF_ALWAYS );

	if( $result )
		$raw_attrs = ldap_get_entries( $ds, $result );
	else
		$raw_attrs = array();
	
	$syntaxes = get_schema_syntaxes( $server_id );

	// build the array of attribueTypes
	$attrs = array();
	for( $i=0; $i < $raw_attrs[0]['attributetypes']['count']; $i++ ) {
		$attr_string = $raw_attrs[0]['attributetypes'][$i];
		if( $attr_string == null || 0 == strlen( $attr_string ) )
			continue;
		$attr = new AttributeType( $attr_string );
		if( isset( $syntaxes[ $attr->getSyntaxOID() ] ) )
			$attr->setType( $syntaxes[ $attr->getSyntaxOID() ]['description'] );
		$name = $attr->getName();
		$key = strtolower( $name );
		$attrs[ $key ] = $attr;
	}

	add_aliases_to_attrs( $attrs );
	add_sup_to_attrs( $attrs );

	ksort( $attrs );

	// cache the schema to prevent multiple schema fetches from LDAP server
	$cache[ $server_id ] = $attrs;
	return( $attrs );
}

/*
 * For each attribute that has multiple names, this function adds unique entries to 
 * the attrs array for those names. Ie, attributeType has name 'gn' and 'givenName'.
 * This function will create a unique entry for 'gn' and 'givenName'.
 */
function add_aliases_to_attrs( &$attrs )
{
	// go back and add data from aliased attributeTypes
	foreach( $attrs as $name => $attr ) {
		$aliases = $attr->getAliases();
		if( is_array( $aliases ) &&  count( $aliases ) > 0 ) {
			// foreach of the attribute's aliases, create a new entry in the attrs array
			// with its name set to the alias name, and all other data copied
			foreach( $aliases as $i => $alias_attr_name ) {
				$new_attr = $attr;
				$new_attr->setName( $alias_attr_name );
				$new_attr->addAlias( $attr->getName() );
				$new_attr->removeAlias( $alias_attr_name );
				$new_attr_key = strtolower( $alias_attr_name );
				$attrs[ $new_attr_key ] = $new_attr;
			}
		}
	}
}

/*
 * Adds inherited values to each attributeType specified by the SUP directive. 
 * Supports infinite levels of inheritance.
 */
function add_sup_to_attrs( &$attrs )
{
	// go back and add any inherited descriptions from parent attributes (ie, cn inherits name)
	foreach( $attrs as $key => $attr ) {
		$sup_attr = $attr->getSupAttribute();
		while( $sup_attr != null ) {
			if( ! isset( $attrs[ strtolower( $sup_attr ) ] ) ){ 
				pla_error( "Warning: attributeType '" . $attr->getName() . "' inherits from 
						'" . $sup_attr . "', but attributeType '" . $sup_attr . "' does not
						exist." );
				return;
			}

			$sup_attr = $attrs[ strtolower( $sup_attr ) ];
			// if the inhertied attriute does not inherit any furth attributes,
			// copy its values and move on to the next attributeType
			if( null == $sup_attr->getSupAttribute() )  {
				// only three values are allowed to be set when an attributeType SUPs another 
				// attributeType: NAME, DESC, and SUP
				$tmp_name = $attr->getName();
				$tmp_desc = $attr->getDescription();
				$tmp_sup = $attr->getSupAttribute();
				$tmp_aliases = $attr->getAliases();

				$attr = $sup_attr;

				$attr->setName( $tmp_name );
				$attr->setDescription( $tmp_desc );
				$attr->setSupAttribute( $tmp_sup);
				$attr->setAliases( $tmp_aliases );
				// replace this attribute in the attrs array now that we have populated
				// new values therein
				$attrs[$key] = $attr;
				$sup_attr = null;
			} else {
				// set the sup_attr to the name of the attributeType from which
				// this attributeType inherits and move up the inheritance chain.
				$sup_attr = $sup_attr->getSupAttribute();
			}
		}
	}
}

/* 
 * Returns an associate array of the server's schema matching rules
 */
function get_schema_matching_rules( $server_id )
{
	static $cache;

	// cache the schema to prevent multiple schema fetches from LDAP server
	if( isset( $cache[$server_id] ) ) {
		//echo "Using matching rules cache.<br />";
		return $cache[$server_id];
	}

	$ds = pla_ldap_connect( $server_id );

	if( ! $ds )
		return false;

	// get all the attributeTypes
	$result = @ldap_read($ds, 'cn=subschema', '(objectClass=*)',
				array( 'matchingRules', 'matchingRuleUse' ), 0, 200, 0, LDAP_DEREF_ALWAYS );
	if( ! $result )
		$result = @ldap_read($ds, 'cn=schema', '(objectClass=*)',
				array( 'matchingRules', 'matchingRuleUse' ), 0, 200, 0, LDAP_DEREF_ALWAYS );

	if( $result )
		$raw = ldap_get_entries( $ds, $result );
	else
		return( array() );

	// build the array of attributes
	$rules = array();
	for( $i=0; $i < $raw[0]['matchingrules']['count']; $i++ )
	{
		$rule = $raw[0]['matchingrules'][$i];
		preg_match( "/[\s]+([\d\.]+)[\s]+/", $rule, $oid);
		preg_match( "/[\s]+NAME[\s]+'([\)\(:?\.a-zA-Z0-9\-_ ]+)'/", $rule, $name );

		$key = strtolower( trim( $oid[1] ) );
		if( ! $key ) continue;

		$rules[$key] = $name[1];
		//$rules[$key]['name'] = $name[1];
	}

	ksort( $rules );

	// cache the schema to prevent multiple schema fetches from LDAP server
	$cache[$server_id] = $rules;
	return $rules;
}

/* 
 * Returns an associate array of the syntax OIDs that this LDAP server uses mapped to
 * their descriptions.
 */
function get_schema_syntaxes( $server_id )
{
	static $cache;

	// cache the schema to prevent multiple schema fetches from LDAP server
	if( isset( $cache[$server_id] ) ) {
		//echo "Using syntax cache.<br />";
		return $cache[$server_id];
	}

	$ds = pla_ldap_connect( $server_id );

	if( ! $ds )
		return false;

	// get all the attributeTypes
	$result = @ldap_read($ds, 'cn=subschema', '(objectClass=*)',
				array( 'ldapSyntaxes' ), 0, 200, 0, LDAP_DEREF_ALWAYS );
	if( ! $result )
		$result = @ldap_read($ds, 'cn=schema', '(objectClass=*)',
				array( 'ldapSyntaxes' ), 0, 200, 0, LDAP_DEREF_ALWAYS );

	if( $result )
		$raw = ldap_get_entries( $ds, $result );
	else
		return( array() );

	// build the array of attributes
	$syntaxes = array();
	for( $i=0; $i < $raw[0]['ldapsyntaxes']['count']; $i++ )
	{
		$syntax = $raw[0]['ldapsyntaxes'][$i];
		preg_match( "/[\s]+([\d\.]+)[\s]+/", $syntax, $oid);
		preg_match( "/[\s]+DESC[\s]+'([\)\(:?\.a-zA-Z0-9\-_ ]+)'/", $syntax, $description );

		$key = strtolower( trim( $oid[1] ) );
		if( ! $key ) continue;

		$syntaxes[$key] = array();
		$syntaxes[$key]['description'] = $description[1];
	}

	ksort( $syntaxes );

	// cache the schema to prevent multiple schema fetches from LDAP server
	$cache[$server_id] = $syntaxes;

	return $syntaxes;
}

?>
