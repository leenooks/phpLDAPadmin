<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/schema_functions.php,v 1.59 2004/04/10 15:30:15 uugdave Exp $

/** 
 * Classes and functions for fetching and parsing schema from an LDAP server.
 * 
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 */ 

/** To enable/disable session-based schema caching (1: enabled, 0: disabled). */
define( 'SCHEMA_SESSION_CACHE_ENABLED', 1 );

/**
 * Generic parent class for all schema items. A schema item is
 * an ObjectClass, an AttributeBype, a MatchingRule, or a Syntax.
 * All schema items have at least two things in common: An OID 
 * and a description. This class provides an implementation for 
 * these two data.
 */
 class SchemaItem
 {
    /** The OID of this schema item. */
    var $oid;
    /** The description of this schema item. */
    var $description;

    /** Initialize class members to default values. */
    function initVars()
    {
        $this->oid = null;
        $this->description = null;
    }

    /** Default constructor. */
    function SchemaItem()
    {
        $this->initVars();
    }

	function setOID( $new_oid )
	{
		$this->oid = $new_oid;
	}

	function setDescription( $new_desc )
	{
		$this->description = $new_desc;
	}

    function getOID()
    {
        return $this->oid;
    }

    function getDescription()
    {
        return $this->description;
    }
 }

/**
 * Represents an LDAP objectClass
 */
class ObjectClass extends SchemaItem
{
	/** This objectClass' name, ie "inetOrgPerson" */
	var $name;
	/** array of objectClass names from which this objectClass inherits */
	var $sup_classes;
	/** one of STRUCTURAL, ABSTRACT, or AUXILIARY */
	var $type;
	/** arrays of attribute names that this objectClass requires */
	var $must_attrs;
	/** arrays of attribute names that this objectClass allows, but does not require */
	var $may_attrs;
	/** boolean value indicating whether this objectClass is obsolete */
	var $is_obsolete;
    /** array of objectClasses which inherit from this one (must be set at runtime explicitly by the caller) */
    var $children_objectclasses;

	/** Initialize the class' member variables */
	function initVars()
	{
        parent::initVars();
		$this->oid = null;
		$this->name = null;
		$this->description = null;
		$this->sup_classes = array();
		$this->type = null;
		$this->must_attrs = array();
		$this->may_attrs = array();
		$this->is_obsolete = false;
        $this->children_objectclasses = array();
	}

	/**
	 * Creates a new ObjectClass object given a raw LDAP objectClass string.
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
					$this->name =        preg_replace("/^\'/", "", $this->name);
					$this->name =        preg_replace("/\'$/", "", $this->name);
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
					if($strings[$i+1]!="(")
					{
						$i++;
						$attr = new ObjectClassAttribute($strings[$i], $this->name);
						array_push ($this->must_attrs, $attr);
					}else{
						$i++;
						do {
							$i++;
							if($strings[$i]!="$")
							{
							 $attr = new ObjectClassAttribute($strings[$i], $this->name);
							 array_push ($this->must_attrs, $attr);
							}
						}while($strings[$i+1]!=")");
					}
					sort($this->must_attrs);
					break;
				case 'MAY':
					if($strings[$i+1]!="(")
					{
						$i++;
						$attr = new ObjectClassAttribute($strings[$i], $this->name);
						array_push ($this->may_attrs, $attr);
					}else{
						$i++;
						do
						{
							$i++;
							if($strings[$i]!="$")
							{
							 $attr = new ObjectClassAttribute($strings[$i], $this->name);
							 array_push ($this->may_attrs, $attr);
							}
						}while($strings[$i+1]!=")");
					}
					sort($this->may_attrs);
					break;
				default:
					if(preg_match ("/[\d\.]+/i",$strings[$i]) && $i == 1)
						$this->oid = $strings[$i];
			}
		}

		$this->description = preg_replace("/^\'/", "", $this->description);
		$this->description = preg_replace("/\'$/", "", $this->description);
	}

    /**
     * Gets an array of AttributeType objects that entries of this ObjectClass must define.
     * This differs from getMustAttrNames in that it returns an array of AttributeType objects
     *
     * @param array $oclasses An array of ObjectClass objects to use when traversing
     *             the inheritance tree. This presents some what of a bootstrapping problem
     *             as we must fetch all objectClasses to determine through inheritance which
     *             attributes this objectClass requires.
     * @return array The array of required AttributeType objects.
     *
     * @see getMustAttrNames
     * @see getMayAttrs
     * @see getMayAttrNames
     */
	function getMustAttrs($oclasses = NULL)
	{
		$all_must_attrs = array();
		$all_must_attrs = $this->must_attrs;
		foreach( $this->sup_classes as $sup_class)
		{
			if( $oclasses != null
				&& $sup_class != "top" 
				&& isset( $oclasses[ strtolower($sup_class) ] ) ) {
						$sup_class = $oclasses[ strtolower($sup_class) ]; 
						$sup_class_must_attrs = $sup_class->getMustAttrs( $oclasses );
						$all_must_attrs = array_merge( $sup_class_must_attrs, $all_must_attrs );
			}
		}

		ksort($all_must_attrs);
		return $all_must_attrs;
	}

    /**
     * Gets an array of AttributeType objects that entries of this ObjectClass may define.
     * This differs from getMayAttrNames in that it returns an array of AttributeType objects
     *
     * @param array $oclasses An array of ObjectClass objects to use when traversing
     *             the inheritance tree. This presents some what of a bootstrapping problem
     *             as we must fetch all objectClasses to determine through inheritance which
     *             attributes this objectClass provides.
     * @return array The array of allowed AttributeType objects.
     *
     * @see getMustAttrNames
     * @see getMustAttrs
     * @see getMayAttrNames
     * @see AttributeType
     */
	function getMayAttrs($oclasses = NULL)
	{
		$all_may_attrs = array();
		$all_may_attrs = $this->may_attrs;
		foreach( $this->sup_classes as $sup_class_name )
		{
			if( $oclasses != null 
				&& $sup_class_name != "top" 
				&& isset( $oclasses[ strtolower($sup_class_name) ] ) ) {
					$sup_class = $oclasses[ strtolower($sup_class_name) ]; 
					$sup_class_may_attrs = $sup_class->getMayAttrs( $oclasses );
					$all_may_attrs = array_merge( $sup_class_may_attrs, $all_may_attrs );
			} 
		}

		ksort($all_may_attrs);
		return $all_may_attrs;
	}

    /**
     * Gets an array of attribute names (strings) that entries of this ObjectClass must define.
     * This differs from getMustAttrs in that it returns an array of strings rather than 
     * array of AttributeType objects
     *
     * @param array $oclasses An array of ObjectClass objects to use when traversing
     *             the inheritance tree. This presents some what of a bootstrapping problem
     *             as we must fetch all objectClasses to determine through inheritance which
     *             attributes this objectClass provides.
     * @return array The array of allowed attribute names (strings).
     *
     * @see getMustAttrs
     * @see getMayAttrs
     * @see getMayAttrNames
     */
	function getMustAttrNames( $oclasses = null )
	{
		$attrs = $this->getMustAttrs( $oclasses );
		$attr_names = array();
		foreach( $attrs as $attr )
			$attr_names[] = $attr->getName();
		return $attr_names;
	}

    /**
     * Gets an array of attribute names (strings) that entries of this ObjectClass must define.
     * This differs from getMayAttrs in that it returns an array of strings rather than 
     * array of AttributeType objects
     *
     * @param array $oclasses An array of ObjectClass objects to use when traversing
     *             the inheritance tree. This presents some what of a bootstrapping problem
     *             as we must fetch all objectClasses to determine through inheritance which
     *             attributes this objectClass provides.
     * @return array The array of allowed attribute names (strings).
     *
     * @see getMustAttrs
     * @see getMayAttrs
     * @see getMustAttrNames
     */
	function getMayAttrNames( $oclasses = null )
	{
		$attrs = $this->getMayAttrs( $oclasses );
		$attr_names = array();
		foreach( $attrs as $attr )
			$attr_names[] = $attr->getName();
		return $attr_names;
	}

    /**
     * Adds an objectClass to the list of objectClasses that inherit 
     * from this objectClass.
     * @param String $object_class_name The name of the objectClass to add
     * @return bool Returns true on success or false on failure (objectclass already existed for example)
     */
    function addChildObjectClass( $object_class_name )
    {
        $object_class_name = trim( $object_class_name );
        if( ! is_array( $this->children_objectclasses ) )
            $this->children_objectclasses = array();
        foreach( $this->children_objectclasses as $existing_objectclass )
            if( 0 == strcasecmp( $object_class_name, $existing_objectclass ) )
                return false;
        $this->children_objectclasses[] = $object_class_name;
        return true;
    }

    /**
     * Returns the array of objectClass names which inherit from this objectClass.
     * @return Array Names of objectClasses which inherit from this objectClass.
     */
    function getChildObjectClasses()
    {
        return $this->children_objectclasses;
    }

    /**
     * Gets the name of this objectClass (ie, "inetOrgPerson")
     * @return string The name of the objectClass
     */
	function getName()
	{
		return $this->name;
	}

    /**
     * Gets the objectClass names from which this objectClass inherits.
     *
     * @return array An array of objectClass names (strings)
     */
	function getSupClasses()
	{
		return $this->sup_classes;
	}

    /**
     * Gets the type of this objectClass: STRUCTURAL, ABSTRACT, or AUXILIARY.
     */ 
	function getType()
	{
		return $this->type;
	}

    /** 
     * Gets whether this objectClass is flagged as obsolete by the LDAP server.
     */
	function getIsObsolete()
	{
		return $this->is_obsolete;
	}

	/**
	 * Adds the specified array of attributes to this objectClass' list of 
	 * MUST attributes. The resulting array of must attributes will contain
	 * unique members.
     *
     * @param array $new_must_attrs An array of attribute names (strings) to add.
	 */
	function addMustAttrs( $new_must_attrs )
	{
		if( ! is_array( $new_must_attrs ) )
			return;
		if( 0 == count( $new_must_attrs ) )
			return;
		$this->must_attrs = array_values( array_unique( array_merge( $this->must_attrs, $new_must_attrs  ) ) );
	}

	/**
	 * Behaves identically to addMustAttrs, but it operates on the MAY
	 * attributes of this objectClass.
     *
     * @param array $new_may_attrs An array of attribute names (strings) to add.
	 */
	function addMayAttrs( $new_may_attrs )
	{
		if( ! is_array( $new_may_attrs ) )
			return;
		if( 0 == count( $new_may_attrs ) )
			return;
		$this->may_attrs = array_values( array_unique( array_merge( $this->may_attrs, $new_may_attrs  ) ) );
	}
}

 /**
  * A simple class for representing AttributeTypes used only by the ObjectClass class. 
  * Users should never instantiate this class. It represents an attribute internal to 
  * an ObjectClass. If PHP supported inner-classes and variable permissions, this would 
  * be interior to class ObjectClass and flagged private. The reason this class is used
  * and not the "real" class AttributeType is because this class supports the notion of
  * a "source" objectClass, meaning that it keeps track of which objectClass originally
  * specified it. This class is therefore used by the class ObjectClass to determine
  * inheritance.
  */
 class ObjectClassAttribute
 {
 	/** This Attribute's name */
	var $name;
	/** This Attribute's root */
	var $source;

    /** 
     * Creates a new ObjectClassAttribute with specified name and source objectClass.
     * @param string $name the name of the new attribute.
     * @param string $source the name of the ObjectClass which 
     *           specifies this attribute.
     */
	function ObjectClassAttribute ($name, $source)
	{
	 $this->name=$name;
	 $this->source=$source;
	}

    /** Gets this attribute's name */
	function getName ()
	{
	 return $this->name;
	}

    /** Gets the name of the ObjectClass which originally specified this attribute. */
	function getSource ()
	{
	 return $this->source;
	}
 }


/**
 * Represents an LDAP AttributeType 
 */
class AttributeType extends SchemaItem
{
	/** The name of this attributeType */
	var $name;
	/** string: the description */
	var $is_obsolete;
	/** The attribute from which this attribute inherits (if any) */
	var $sup_attribute;
	/** The equality rule used */
	var $equality;
	/** The ordering of the attributeType */
	var $ordering;
	/** Boolean: supports substring matching? */
	var $sub_str;
	/** The full syntax string, ie 1.2.3.4{16} */
	var $syntax;
	/** boolean: is single valued only? */
	var $is_single_value;
	/** boolean: is collective? */
	var $is_collective;
	/** boolean: can use modify? */
	var $is_no_user_modification;
	/** The usage string set by the LDAP schema */
	var $usage;
	/** An array of alias attribute names, strings */
	var $aliases;
	/** The max number of characters this attribute can be */
	var $max_length;
	/** A string description of the syntax type (taken from the LDAPSyntaxes) */
	var $type;
	/** An array of objectClasses which use this attributeType (must be set by caller) */
	var $used_in_object_classes;

	/** 
	 * Initialize the class' member variables 
	 */
	function initVars()
	{
        parent::initVars();
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
		$this->is_single_value= null;
		$this->is_collective = false;
		$this->is_no_user_modification = false;
		$this->usage = null;
		$this->aliases = array();
		$this->type = null;
		$this->used_in_object_classes = array();
	}

	/**
	 * Creates a new AttributeType objcet from a raw LDAP AttributeType string.
	 */
	function AttributeType( $raw_ldap_attr_string )
	{
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
					if($i < count($strings) - 1 && $strings[$i+1]=="{") {
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
					if(preg_match ("/[\d\.]+/i",$strings[$i]) && $i == 1)
						$this->oid = $strings[$i];
			}
		}

		$this->name =        preg_replace("/^\'/", "", $this->name);
		$this->name =        preg_replace("/\'$/", "", $this->name);
		$this->description = preg_replace("/^\'/", "", $this->description);
		$this->description = preg_replace("/\'$/", "", $this->description);
		$this->syntax_oid  = preg_replace("/^\'/", "", $this->syntax_oid );
		$this->syntax_oid  = preg_replace("/\'$/", "", $this->syntax_oid );
	}

    /** 
     * Gets this attribute's name
     * @return string
     */
	function getName()
	{
		return $this->name;
	}

    /** 
     * Gets whether this attribute has been flagged as obsolete by the LDAP server 
     * @return bool
     */
	function getIsObsolete()
	{
		return $this->is_obsolete;
	}

    /** 
     * Gets this attribute's usage string as defined by the LDAP server
     * @return string
     */
	function getUsage()
	{
		return $this->usage;
	}

    /** 
     * Gets this attribute's parent attribute (if any). If this attribute does not 
     * inherit from another attribute, null is returned.
     * @return string
     */
	function getSupAttribute()
	{
		return $this->sup_attribute;
	}

    /** 
     * Gets this attribute's equality string
     * @return string
     */
	function getEquality()
	{
		return $this->equality;
	}

    /** 
     * Gets this attribute's ordering specification.
     * @return string
     */
	function getOrdering()
	{
		return $this->ordering;
	}

    /** 
     * Gets this attribute's substring matching specification
     * @return string
     */
	function getSubstr()
	{
		return $this->sub_str;
	}

    /** 
     * Gets the names of attributes that are an alias for this attribute (if any).
     * @return array An array of names of attributes which alias this attribute or
     *          an empty array if no attribute aliases this object.
     */
	function getAliases()
	{
		return $this->aliases;
	}

	/**
	 * Gets this attribute's raw syntax string (ie: "1.2.3.4{16}").
     * @return string The raw syntax string
	 */
	function getSyntaxString()
	{
		return $this->syntax;
	}

	/**
	 * Gets this attribute's syntax OID. Differs from getSyntaxString() in that this 
     * function only returns the actual OID with any length specification removed. 
     * Ie, if the syntax string is "1.2.3.4{16}", this function only retruns
	 * "1.2.3.4". 
     * @return string The syntax OID string.
	 */
	function getSyntaxOID()
	{
		return $this->syntax_oid;
	}

	/**
	 * Gets this attribute's the maximum length. If no maximum is defined by the LDAP server, null is returned.
     * @return int The maximum length (in characters) of this attribute or null if no maximum is specified.
	 */
	function getMaxLength()
	{
		return $this->max_length;
	}

	/**
	 * Gets whether this attribute is single-valued. If this attribute only supports single values, true
     * is returned. If this attribute supports multiple values, false is returned.
     * @return bool Returns true if this attribute is single-valued or false otherwise.
	 */
	function getIsSingleValue()
	{
		return $this->is_single_value;
	}

    /**
     * Sets whether this attribute is single-valued.
     * @param bool $is_single_value 
     */
	function setIsSingleValue( $is_single_value )
	{
		$this->is_single_value = $is_single_value;
	}

    /**
     * Gets whether this attribute is collective.
     * @return bool Returns true if this attribute is collective and false otherwise.
     */
	function getIsCollective()
	{
		return $this->is_collective;
	}

    /**
     * Gets whether this attribute is not modifiable by users.
     * @return bool Returns true if this attribute is not modifiable by users.
     */
	function getIsNoUserModification()
	{
		return $this->is_no_user_modification;
	}

    /**
     * Gets this attribute's type
     * @return string The attribute's type.
     */
	function getType()
	{
		return $this->type;
	}

	/**
	 * Removes an attribute name from this attribute's alias array.
     * @param string $remove_alias_name The name of the attribute to remove.
     * @return bool true on success or false on failure (ie, if the specified 
     *           attribute name is not found in this attribute's list of aliases)
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

	/**
	 * Adds an attribute name to the alias array.
     * @param string $new_alias_name The name of a new attribute to add to this attribute's list of aliases.
	 */
	function addAlias( $new_alias_name )
	{
		$this->aliases[] = $new_alias_name;
	}

    /**
     * Sets this attriute's name.
     * @param string $new_name The new name to give this attribute.
     */
	function setName( $new_name )
	{
		$this->name = $new_name;
	}

    /**
     * Sets this attriute's SUP attribute (ie, the attribute from which this attribute inherits).
     * @param string $new_sup_attr The name of the new parent (SUP) attribute
     */
	function setSupAttribute( $new_sup_attr )
	{
		$this->sup_attribute = $new_sup_attr;
	}

    /**
     * Sets this attribute's list of aliases.
     * @param array $new_aliases The array of alias names (strings)
     */
	function setAliases( $new_aliases )
	{
		$this->aliases = $new_aliases;
	}

    /**
     * Sets this attribute's type.
     * @param string $new_type The new type.
     */
	function setType( $new_type )
	{
		$this->type = $new_type;
	}

    /**
     * Adds an objectClass name to this attribute's list of "used in" objectClasses, 
     * that is the list of objectClasses which provide this attribute.
     * @param string $object_class_name The name of the objectClass to add.
     */
	function addUsedInObjectClass( $object_class_name )
	{
		foreach( $this->used_in_object_classes as $used_in_object_class )
			if( 0 == strcasecmp( $used_in_object_class, $object_class_name ) )
				return false;
		$this->used_in_object_classes[] = $object_class_name;
		return true;
	}

    /**
     * Gets the list of "used in" objectClasses, that is the list of objectClasses 
     * which provide this attribute.
     * @return array An array of names of objectclasses (strings) which provide this attribute 
     */
	function getUsedInObjectClasses()
	{
		return $this->used_in_object_classes;
	}
}

/**
 * Represents an LDAP Syntax
 */
class Syntax extends SchemaItem
{
	/** Initializes the class' member variables */
	function initVars()
	{
        parent::initVars();
		$this->oid = null;
		$this->description = null;
	}

	/**
	 * Creates a new Syntax object from a raw LDAP syntax string.
	 */
	function Syntax( $raw_ldap_syntax_string )
	{
		$this->initVars();
		$class = $raw_ldap_syntax_string;
		$strings = preg_split ("/[\s,]+/", $class, -1,PREG_SPLIT_DELIM_CAPTURE);
		for($i=0; $i<count($strings); $i++) {
			switch($strings[$i]) {
				case '(':
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
				default:
					if(preg_match ("/[\d\.]+/i",$strings[$i]) && $i == 1)
						$this->oid = $strings[$i];
			}
		}
		$this->description = preg_replace("/^\'/", "", $this->description);
		$this->description = preg_replace("/\'$/", "", $this->description);
	}
}

/**
 * Represents an LDAP MatchingRule
 */
class MatchingRule extends SchemaItem
{
	/** This rule's name */
	var $name;
	/** This rule's syntax OID */
	var $syntax;
	/** Boolean value indicating whether this MatchingRule is obsolete */
	var $is_obsolete;
	/** An array of attribute names who use this MatchingRule */
	var $used_by_attrs;

	/** Initialize the class' member variables */
	function initVars()
	{
        parent::initVars();
		$this->oid = null;
		$this->name = null;
		$this->description = null;
		$this->is_obsolete = false;
		$this->syntax = null;
		$this->used_by_attrs = array();
	}

	/**
	 * Creates a new MatchingRule object from a raw LDAP MatchingRule string.
	 */
	function MatchingRule( $raw_ldap_matching_rule_string )
	{
		$this->initVars();
		$strings = preg_split ("/[\s,]+/", $raw_ldap_matching_rule_string, -1,PREG_SPLIT_DELIM_CAPTURE);
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
					$this->name =        preg_replace("/^\'/", "", $this->name);
					$this->name =        preg_replace("/\'$/", "", $this->name);
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
				case 'SYNTAX':
					$this->syntax = $strings[++$i];
					break;
				default:
					if(preg_match ("/[\d\.]+/i",$strings[$i]) && $i == 1)
						$this->oid = $strings[$i];
			}
		}
		$this->description = preg_replace("/^\'/", "", $this->description);
		$this->description = preg_replace("/\'$/", "", $this->description);
	}

	/**
	 * Sets the list of used_by_attrs to the array specified by $attrs;
     * @param array $attrs The array of attribute names (strings) which use this MatchingRule
	 */
	function setUsedByAttrs( $attrs )
	{
		$this->used_by_attrs = $attrs;
	}

	/**
	 * Adds an attribute name to the list of attributes who use this MatchingRule
	 * @return true if the attribute was added and false otherwise (already in the list)
	 */
	function addUsedByAttr( $new_attr_name )
	{
		foreach( $this->used_by_attrs as $attr_name )
			if( 0 == strcasecmp( $attr_name, $new_attr_name ) )
				return false;
		$this->used_by_attrs[] = $new_attr_name;
		return true;
	}

    /**
     * Gets this MatchingRule's name.
     * @return string The name.
     */
	function getName()
	{
		return $this->name;
	}

    /**
     * Gets whether this MatchingRule is flagged as obsolete by the LDAP server.
     * @return bool True if this MatchingRule is obsolete and false otherwise.
     */
	function getIsObsolete()
	{
		return $this->is_obsolete;
	}

    /**
     * Gets this MatchingRule's syntax string (an OID).
     * @todo Is this function broken?
     */
	function getSyntax()
	{
		return $this->description;
	}

    /**
     * Gets an array of attribute names (strings) which use this MatchingRule
     * @return array The array of attribute names (strings).
     */
	function getUsedByAttrs()
	{
		return $this->used_by_attrs;
	}
}

/**
 * Represents an LDAP schema matchingRuleUse entry
 */ 
class MatchingRuleUse extends SchemaItem
{
	/** The name of the MathingRule this applies to */
	var $name;
	/** An array of attributeType names who make use of the mathingRule 
	 * identified by $this->oid and $this->name */
	var $used_by_attrs;

	/** Initialize the class' member variables */
	function initVars()
	{
        parent::initVars();
		$this->oid = null;
		$this->name = null;
		$this->used_by_attrs = array();
	}

	function MatchingRuleUse( $raw_matching_rule_use_string )
	{
        $this->initVars();
		$strings = preg_split ("/[\s,]+/", $raw_matching_rule_use_string, -1,PREG_SPLIT_DELIM_CAPTURE);
		for($i=0; $i<count($strings); $i++) {
			switch($strings[$i]) {
				case '(':
					break;
				case 'NAME':
					if($strings[$i+1]!="(") {
						do {
							$i++;
							if( ! isset( $this->name ) || strlen( $this->name ) ==0 )
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
					$this->name =        preg_replace("/^\'/", "", $this->name);
					$this->name =        preg_replace("/\'$/", "", $this->name);
					break;
				case 'APPLIES':
					// TODO
					if($strings[$i+1]!="(") {
						// has a single attribute name
						$i++;
						$this->used_by_attrs = array( $strings[$i] );
						//echo "Adding single: " . $strings[$i] . "<br />";
					} else {
						// has multiple attribute names
						$i++;
						while($strings[$i]!=")") {
							$i++;
							$new_attr = $strings[$i];
							$new_attr = preg_replace("/^\'/", "", $new_attr );
							$new_attr = preg_replace("/\'$/", "", $new_attr );
							$this->used_by_attrs[] = $new_attr;
							//echo "Adding $new_attr<br />";
							$i++;
						}
					}
					break;
				default:
					if(preg_match ("/[\d\.]+/i",$strings[$i]) && $i == 1)
						$this->oid = $strings[$i];
			}
		}
		sort( $this->used_by_attrs );
	}

    /**
     * Gets this MatchingRuleUse's name
     * @return string The name
     */
	function getName()
	{
		return $this->name;
	}

    /**
     * Gets an array of attribute names (strings) which use this MatchingRuleUse object.
     * @return array The array of attribute names (strings).
     */
	function getUsedByAttrs()
	{
		return $this->used_by_attrs;
	}
}

/**
 * Helper for _get_raw_schema() which fetches the DN of the schema object
 * in an LDAP server based on a DN. Entries should set the subSchemaSubEntry
 * attribute pointing to the DN of the server schema. You can specify  the
 * DN whose subSchemaSubEntry you wish to retrieve of specify an empty string
 * to fetch the subScehamSubEntry from the Root DSE.
 *
 * @param int $server_id The ID of the server whose schema DN to fetch.
 * @param string $dn The DN (may be null) which houses the subschemaSubEntry attribute which 
 *                  this function can use to determine the schema entry's DN.
 * @param bool $debug Switch to true to see some nice and copious output. :)
 *
 * @return string The DN of the entry which houses this LDAP server's schema.
 */
function _get_schema_dn( $server_id, $dn, $debug=false )
{
	if( $debug ) echo "<pre>";
	$ds = pla_ldap_connect( $server_id );
	if( ! $ds )
		return false;

	$search = @ldap_read( $ds, $dn, 'objectClass=*', array( 'subschemaSubentry' ) );
	if( $debug ) { echo "Search result (ldap_read): "; var_dump( $search ); echo "\n"; }
	if( ! $search ) {
		if( $debug ) echo "_get_schema_dn() returning false. (search val is false)\n";
		return false;
	}

	if( @ldap_count_entries( $ds, $search ) == 0 ) {
		if( $debug ) echo "_get_schema_dn() returning false (ldap_count_entries() == 0).\n";
		return false;
	}

	$entries = @ldap_get_entries( $ds, $search );
	if( $debug ) { echo "Entries (ldap_get_entries): "; var_dump( $entries ); echo "\n"; }
	if( ! $entries || ! is_array( $entries ) ) {
		if( $debug ) echo "_get_schema_dn() returning false (Bad entries val, false or not array).\n";
		return false;
	}

	$entry = isset( $entries[0] ) ? $entries[0] : false;
	if( ! $entry ) {
		if( $debug ) echo "_get_schema_dn() returning false (entry val is false)\n";
		return false;
	}

	$sub_schema_sub_entry = isset( $entry[0] ) ? $entry[0] : false;
	if( ! $sub_schema_sub_entry ) {
		if( $debug ) echo "_get_schema_dn() returning false (sub_schema_sub_entry val is false)\n";
		return false;
	}

	$schema_dn = isset( $entry[ $sub_schema_sub_entry ][0] ) ?
					$entry[ $sub_schema_sub_entry ][0] :
					false;

	if( $debug ) echo "_get_schema_dn() returning: \"" . $schema_dn . "\"\n";
	return $schema_dn;
}

/**
 * Fetches the raw schema array for the subschemaSubentry of the server. Note,
 * this function has grown many hairs to accomodate more LDAP servers. It is
 * needfully complicated as it now supports many popular LDAP servers that
 * don't necessarily expose their schema "the right way".
 *
 * @param $server_id - The server ID whose server you want to retrieve
 * @param $schema_to_fetch - A string indicating which type of schema to 
 *		fetch. Five valid values: 'objectclasses', 'attributetypes', 
 *		'ldapsyntaxes', 'matchingruleuse', or 'matchingrules'. 
 *		Case insensitive.
 * @param $dn (optional) This paremeter is the DN of the entry whose schema you
 * 		would like to fetch. Entries have the option of specifying
 * 		their own subschemaSubentry that points to the DN of the system
 * 		schema entry which applies to this attribute. If unspecified,
 *		this will try to retrieve the schema from the RootDSE subschemaSubentry.
 *		Failing that, we use some commonly known schema DNs. Default 
 *		value is the Root DSE DN (zero-length string)
 * @return an array of strings of this form:
 *    Array (
 *      [0] => "( 1.3.6.1.4.1.7165.1.2.2.4 NAME 'gidPool' DESC 'Pool ...
 *      [1] => "( 1.3.6.1.4.1.7165.2.2.3 NAME 'sambaAccount' DESC 'Sa ...
 *      etc.
 */
function _get_raw_schema( $server_id, $schema_to_fetch, $dn='' )
{
	global $lang;

	// Swith to true to enable verbose output of schema fetching progress
	$debug = false;

	$ds = pla_ldap_connect( $server_id );
	if( ! $ds )
		return false;

	// error checking
	$schema_to_fetch = strtolower( $schema_to_fetch );
	$valid_schema_to_fetch = array( 'objectclasses', 'attributetypes', 'ldapsyntaxes', 
					'matchingrules', 'matchingruleuse'  );
	if( ! in_array( $schema_to_fetch, $valid_schema_to_fetch ) )
        // This error message is not localized as only developers should ever see it
		pla_error( "Bad parameter provided to function to _get_raw_schema(). '" 
				. htmlspecialchars( $schema_to_fetch ) . "' is 
				not valid for the schema_to_fetch parameter." );
	
	// Try to get the schema DN from the specified entry. 
	$schema_dn = _get_schema_dn( $server_id, $dn, $debug );

	// Do we need to try again with the Root DSE?
	if( ! $schema_dn )
		$schema_dn = _get_schema_dn( $server_id, '', $debug );

	// Store the eventual schema retrieval in $schema_search
	$schema_search = null;

	if( $schema_dn ) {
		if( $debug ) { echo "Found the schema DN: "; var_dump( $schema_dn ); echo "\n"; }
		$schema_search = @ldap_read( $ds, $schema_dn, '(objectClass=*)',
							array( $schema_to_fetch ), 0, 0, 0, 
							LDAP_DEREF_ALWAYS );

        // Were we not able to fetch the schema from the $schema_dn?
        $schema_entries = @ldap_get_entries( $ds, $schema_search );
		if( $schema_search === false || 
            0 == @ldap_count_entries( $ds, $schema_search ) ||
            ! isset( $schema_entries[0][$schema_to_fetch] ) ) {
                if( $debug ) echo "Did not find the schema with (objectClass=*). Attempting with (objetClass=subschema)\n";

                // Try again with a different filter (some servers require (objectClass=subschema) like M-Vault)
                $schema_search = @ldap_read( $ds, $schema_dn, '(objectClass=subschema)',
                        array( $schema_to_fetch ), 0, 0, 0, 
                        LDAP_DEREF_ALWAYS );
                $schema_entries = @ldap_get_entries( $ds, $schema_search );

                // Still didn't get it?
                if( $schema_search === false || 
                        0 == @ldap_count_entries( $ds, $schema_search ) ||
                        ! isset( $schema_entries[0][$schema_to_fetch] ) ) {
                    if( $debug ) echo "Did not find the schema at DN: $schema_dn (with objectClass=* nor objectClass=subschema).\n";
                    unset( $schema_entries );
                    unset( $schema_dn );
                    $schema_search = null;
                } else {
                    if( $debug ) echo "Found the schema at DN: $schema_dn (with objectClass=subschema).\n";
                }
		} else {
			if( $debug ) echo "Found the schema at DN: $schema_dn (with objectClass=*).\n";
		}
	} 

	// Second chance: If the DN or Root DSE didn't give us the subschemaSubentry, ie $schema_search
	// is still null, use some common subSchemaSubentry DNs as a work-around.

	if( $debug && $schema_search == null )
		echo "Attempting work-arounds for 'broken' LDAP servers...\n";

	// cn=subschema for OpenLDAP
	if( $schema_search == null ) {
		if( $debug ) echo "Attempting with cn=subschema (OpenLDAP)...\n";
		// try with the standard DN
		$schema_search = @ldap_read($ds, 'cn=subschema', '(objectClass=*)',
				array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );
	}

	// cn=schema for Novell eDirectory
	if( $schema_search == null ) {
		if( $debug ) echo "Attempting with cn=schema (Novell)...\n";
		// try again, with a different schema DN
		$schema_search = @ldap_read($ds, 'cn=schema', '(objectClass=*)',
				array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );
	}

	// cn=schema,cn=configuration,dc=example,dc=com for ActiveDirectory
	if( $schema_search == null ) {
		// try again, with a different schema DN
		global $servers;
		$base_dn = isset( $servers[ $server_id ][ 'base' ] ) ?
				$servers[ $server_id ][ 'base' ] :
				null;
		if( $debug ) echo "Attempting with cn=schema,cn=configuration,$base_dn (ActiveDirectory)...\n";
		if( $base_dn != null )
			$schema_search = @ldap_read($ds, 'cn=schema,cn=configuration,' . $base_dn, '(objectClass=*)',
				array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );
	}

	// cn=Schema,ou=Admin,dc=example,dc=com for SiteServer
	if( $schema_search == null ) {
		// try again, with a different schema DN
		global $servers;
		$base_dn = isset( $servers[ $server_id ][ 'base' ] ) ?
				$servers[ $server_id ][ 'base' ] :
				null;
		if( $debug ) echo "Attempting with cn=Schema,ou=Admin,$base_dn (ActiveDirectory)...\n";
		if( $base_dn != null )
			$schema_search = @ldap_read($ds, 'cn=Schema,ou=Admin,' . $base_dn, '(objectClass=*)',
				array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );
	}

	// Attempt to pull schema from Root DSE with scope "base"
	if( $schema_search == null ) {
		// try again, with a different schema DN
		if( $debug ) echo "Attempting to pull schema from Root DSE with scope \"base\"...\n";
		if( $base_dn != null )
			$schema_search = @ldap_read($ds, '', '(objectClass=*)',
				array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );
         $schema_entries = @ldap_get_entries( $ds, $schema_search );
         if( ! isset( $schema_entries[0][$schema_to_fetch] ) )
            $schema_search = null;
    }
    
	// Attempt to pull schema from Root DSE with scope "one" (work-around for Isode M-Vault X.500/LDAP)
	if( $schema_search == null ) {
		// try again, with a different schema DN
		if( $debug ) echo "Attempting to pull schema from Root DSE with scope \"one\"...\n";
		if( $base_dn != null )
			$schema_search = @ldap_list($ds, '', '(objectClass=*)',
				array( $schema_to_fetch ), 0, 0, 0, LDAP_DEREF_ALWAYS );
         $schema_entries = @ldap_get_entries( $ds, $schema_search );
         if( ! isset( $schema_entries[0][$schema_to_fetch] ) )
            $schema_search = null;
	}

	// Shall we just give up?
	if( $schema_search == null ) {
        if( $debug ) echo "Returning false since schema_search came back null</pre>\n";
		return false;
    }

	// Did we get something unrecognizable?
	if( 'resource' != gettype( $schema_search ) ) {
		if( $debug ) echo "Returning false since schema_esarch is not of type 'resource'</pre>\n";
		return false;
	}
	
	$schema = @ldap_get_entries( $ds, $schema_search );
	if( $schema == false ) {
		if( $debug ) echo "Returning false since ldap_get_entries() returned false.</pre>\n";
		return false;
	}

	// Make a nice array of this form:
	// Array (
	//    [0] => "( 1.3.6.1.4.1.7165.1.2.2.4 NAME 'gidPool' DESC 'Pool ...
	//    [1] => "( 1.3.6.1.4.1.7165.2.2.3 NAME 'sambaAccount' DESC 'Sa ...
	//    etc.
	if( ! isset( $schema[0][$schema_to_fetch] ) ) {
		if( $debug ) echo "Returning false since '$schema_to_fetch' isn't in the schema array</pre>\n";
		return false;
	}

	$schema = $schema[0][$schema_to_fetch];
	unset( $schema['count'] );

    if( $debug ) echo "</pre>";
	return $schema;
}

/** 
 * Gets an associative array of ObjectClass objects for the specified 
 * server. Each array entry's key is the name of the objectClass
 * in lower-case and the value is an ObjectClass object.
 *
 * @param int $server_id The ID of the server whose objectClasses to fetch
 * @param string $dn (optional) It is easier to fetch schema if a DN is provided
 *             which defines the subschemaSubEntry attribute (all entries should).
 *
 * @return array An array of ObjectClass objects.
 *
 * @see ObjectClass
 * @see get_schema_objectclass
 */
function get_schema_objectclasses( $server_id, $dn=null )
{
    if( cached_schema_available( $server_id, 'objectclasses' ) ) {
        return get_cached_schema( $server_id, 'objectclasses' );   
    }

	$raw_oclasses = _get_raw_schema( $server_id, 'objectclasses', $dn );
	if( ! $raw_oclasses )
		return false;

	// build the array of objectClasses
	$object_classes = array();
	foreach( $raw_oclasses as $class_string  ) {
		if( $class_string == null || 0 == strlen( $class_string ) )
			continue;
		$object_class = new ObjectClass( $class_string );
		$name = $object_class->getName();
		$key = strtolower( $name );
		$object_classes[ $key ] = $object_class;
	}

	ksort( $object_classes );

	// cache the schema to prevent multiple schema fetches from LDAP server
    set_cached_schema( $server_id, 'objectclasses', $object_classes );
	return( $object_classes );
}

/**
 * Gets a single ObjectClass object specified by name.
 *
 * @param int $server_id The ID of the server which houses the objectClass to fetch.
 * @param string $oclass_name The name of the objectClass to fetch.
 * @param string $dn (optional) It is easier to fetch schema if a DN is provided
 *             which defines the subschemaSubEntry attribute (all entries should).
 *
 * @return ObjectClass The specified ObjectClass object or false on error.
 *
 * @see ObjectClass
 * @see get_schema_objectclasses
 */
function get_schema_objectclass( $server_id, $oclass_name, $dn=null )
{
	$oclass_name = strtolower( $oclass_name );
	$oclasses = get_schema_objectclasses( $server_id, $dn );
	if( ! $oclasses )
		return false;
	if( isset( $oclasses[ $oclass_name ] ) )
		return $oclasses[ $oclass_name ];
	else
		return false;
}

/**
 * Gets a single AttributeType object specified by name.
 *
 * @param int $server_id The ID of the server which houses the AttributeType to fetch.
 * @param string $oclass_name The name of the AttributeType to fetch.
 * @param string $dn (optional) It is easier to fetch schema if a DN is provided
 *             which defines the subschemaSubEntry attribute (all entries should).
 *
 * @return AttributeType The specified AttributeType object or false on error.
 *
 * @see AttributeType
 * @see get_schema_attributes
 */
function get_schema_attribute( $server_id, $attr_name, $dn=null ) 
{
	$attr_name = real_attr_name( $attr_name );
	$schema_attrs = get_schema_attributes( $server_id, $dn );
	$attr_name = strtolower( $attr_name );
	$schema_attr = isset( $schema_attrs[ $attr_name ] ) ?
				$schema_attrs[ $attr_name ] :
				false;
	return $schema_attr;
}

/** 
 * Gets an associative array of AttributeType objects for the specified 
 * server. Each array entry's key is the name of the attributeType
 * in lower-case and the value is an AttributeType object.
 *
 * @param int $server_id The ID of the server whose AttributeTypes to fetch
 * @param string $dn (optional) It is easier to fetch schema if a DN is provided
 *             which defines the subschemaSubEntry attribute (all entries should).
 *
 * @return array An array of AttributeType objects.
 */
function get_schema_attributes( $server_id, $dn = null )
{
    if( cached_schema_available( $server_id, 'attributetypes' ) ) {
        return get_cached_schema( $server_id, 'attributetypes' );   
    }

	$raw_attrs = _get_raw_schema( $server_id, 'attributeTypes', $dn );
	if( ! $raw_attrs )
		return false;
	
	// build the array of attribueTypes
	$syntaxes = get_schema_syntaxes( $server_id, $dn );
	$attrs = array();
	/** 
     * bug 856832: create two arrays - one indexed by name (the standard
	 * $attrs array above) and one indexed by oid (the new $attrs_oid array
	 * below). This will help for directory servers, like IBM's, that use OIDs
	 * in their attribute definitions of SUP, etc
	 */
	$attrs_oid = array();
	foreach( $raw_attrs as $attr_string ) {
		if( $attr_string == null || 0 == strlen( $attr_string ) )
			continue;
		$attr = new AttributeType( $attr_string );
		if( isset( $syntaxes[ $attr->getSyntaxOID() ] ) ) {
			$syntax = $syntaxes[ $attr->getSyntaxOID() ];
			$attr->setType( $syntax->getDescription() );
		}
		$name = $attr->getName();
		$key = strtolower( $name );
		$attrs[ $key ] = $attr;
		
		/** 
		 * bug 856832: create an entry in the $attrs_oid array too. This
		 * will be a ref to the $attrs entry for maintenance and performance
		 * reasons 
		 */
		$oid = $attr->getOID();
		$attrs_oid[ $oid ] = &$attrs[ $key ];
	}

	add_aliases_to_attrs( $attrs );
	/** 
	 * bug 856832: pass the $attrs_oid array as a second (new) parameter
	 * to add_sup_to_attrs. This will allow lookups by either name or oid.
	 */
	add_sup_to_attrs( $attrs, $attrs_oid );

	ksort( $attrs );

	// cache the schema to prevent multiple schema fetches from LDAP server
    set_cached_schema( $server_id, 'attributetypes', $attrs );
	return( $attrs );
}

/**
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
			foreach( $aliases as $alias_attr_name ) {
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

/**
 * Adds inherited values to each attributeType specified by the SUP directive. 
 * Supports infinite levels of inheritance.
 * Bug 856832: require a second paramter that has all attributes indexed by OID
 */
function add_sup_to_attrs( &$attrs, &$attrs_oid )
{
	$debug = false;
	if( $debug ) echo "<pre>";

	if( $debug ) print_r( $attrs );
	
	// go back and add any inherited descriptions from parent attributes (ie, cn inherits name)
	foreach( $attrs as $key => $attr ) {
		if( $debug ) echo "Analyzing inheritance for attribute '" . $attr->getName() . "'\n";
		$sup_attr_name = $attr->getSupAttribute();
		$sup_attr = null;

		// Does this attribute have any inheritance happening here?
		if( null != trim( $sup_attr_name ) ) {

			// This loop really should traverse infinite levels of inheritance (SUP) for attributeTypes,
			// but just in case we get carried away, stop at 100. This shouldn't happen, but for
			// some weird reason, we have had someone report that it has happened. Oh well.
			$i = 0;
			while( $i++ < 100 /** 100 == INFINITY ;) */ ) {
				if( $debug ) echo "Top of loop.\n";

				/**
				 * Bug 856832: check if sup is indexed by OID. If it is,
				 * replace the OID with the appropriate name. Then reset
				 * $sup_attr_name to the name instead of the OID. This will
				 * make all the remaining code in this function work as
				 * expected.
				 */
				if( isset( $attrs_oid[$sup_attr_name] ) ) {
					$attr->setSupAttribute( $attrs_oid[$sup_attr_name]->getName() );
					$sup_attr_name = $attr->getSupAttribute();
				}
				
				if( ! isset( $attrs[ strtolower( $sup_attr_name ) ] ) ){ 
					pla_error( "Schema error: attributeType '" . $attr->getName() . "' inherits from 
								'" . $sup_attr_name . "', but attributeType '" . $sup_attr_name . "' does not
								exist." );
						return;
				}

				if( $debug ) echo " sup_attr_name: $sup_attr_name\n";
				$sup_attr = $attrs[ strtolower( $sup_attr_name ) ];
				if( $debug ) echo " Sup attr: " . $sup_attr->getName() . "\n";

				$sup_attr_name = $sup_attr->getSupAttribute();
				if( $debug ) echo " Does the sup attr itself have a sup attr?\n";

				// Does this superior attributeType not have a superior attributeType?
				if( null == $sup_attr_name || strlen( trim( $sup_attr_name ) ) == 0 ) {

					// Since this attribute's superior attribute does not have another superior
					// attribute, clone its properties for this attribute. Then, replace
					// those cloned values with those that can be explicitly set by the child
					// attribute attr). Save those few properties which the child can set here:
					if( $debug ) echo "  nope, this is the end of the inheritance chain after $i iterations.\n";
					$tmp_name = $attr->getName();
					$tmp_oid = $attr->getOID();
					$tmp_sup = $attr->getSupAttribute();
					$tmp_aliases = $attr->getAliases();
					$tmp_single_val = $attr->getIsSingleValue();


					if( $debug ) {
						echo "  populating values into attribute from sup attribute:\n";
						echo "Before: ";
						print_r( $attr );
					}

					// clone the SUP attributeType and populate those values
					// that were set by the child attributeType
					$attr = $sup_attr;
					$attr->setOID( $tmp_oid );
					$attr->setName( $tmp_name );
					$attr->setSupAttribute( $tmp_sup);
					$attr->setAliases( $tmp_aliases );

					if( $debug ) {
						echo "After (name, sup_attr, and aliases should not have changed!: ";
						print_r( $attr );
					}
					// only overwrite the SINGLE-VALUE property if the child explicitly sets it
					// (note: All LDAP attributes default to multi-value if not explicitly set SINGLE-VALUE)
					if( true == $tmp_single_val )
						$attr->setIsSingleValue( true );

					// replace this attribute in the attrs array now that we have populated
					// new values therein
					$attrs[$key] = $attr;

					// very important: break out after we are done with this attribute
					$sup_attr_name = null;
					$sup_attr = null;
					break;

				} else {

					// do nothing, move on down the chain of inheritance...
					if( $debug ) echo "  yup, march down the inheritance chain (iteration $i).\n";
					if( $debug ) { echo "  The sup attr is: "; var_dump( $sup_attr_name ); echo "\n"; }

				}
			}
		}
	}

	if( $debug ) echo "</pre>\n";
}

/** 
 * Returns an array of MatchingRule objects for the specified server.
 * The key of each entry is the OID of the matching rule.
 */
function get_schema_matching_rules( $server_id, $dn=null )
{
    if( cached_schema_available( $server_id, 'matchingrules' ) ) {
        return get_cached_schema( $server_id, 'matchingrules' );   
    }

	// build the array of MatchingRule objects
	$raw_matching_rules = _get_raw_schema( $server_id, 'matchingRules', $dn );
	if( ! $raw_matching_rules )
		return false;
	$rules = array();
	foreach( $raw_matching_rules as $rule_string ) {
		if( $rule_string == null || 0 == strlen( $rule_string ) )
			continue;
		$rule = new MatchingRule( $rule_string );
		$key = strtolower( $rule->getName() );
		$rules[ $key ] = $rule;
	}

	ksort( $rules );

	// For each MatchingRuleUse entry, add the attributes who use it to the
	// MatchingRule in the $rules array.
	$raw_matching_rule_use = _get_raw_schema( $server_id, 'matchingRuleUse' );
	if( $raw_matching_rule_use != false ) {
		foreach( $raw_matching_rule_use as $rule_use_string ) {
			if( $rule_use_string == null || 0 == strlen( $rule_use_string ) )
				continue;
			$rule_use = new MatchingRuleUse( $rule_use_string );
			$key = strtolower( $rule_use->getName() );
			if( isset( $rules[ $key ] ) )
				$rules[ $key ]->setUsedByAttrs( $rule_use->getUsedByAttrs() );
		}
	} else {
		// No MatchingRuleUse entry in the subschema, so brute-forcing
		// the reverse-map for the "$rule->getUsedByAttrs()" data.
		$attrs = get_schema_attributes( $server_id, $dn );
		if( is_array( $attrs ) )
			foreach( $attrs as $attr ) {
				$rule_key = strtolower( $attr->getEquality() );
				if( isset( $rules[ $rule_key ] ) )
					$rules[ $rule_key ]->addUsedByAttr( $attr->getName() );
			}
	}

	// cache the schema to prevent multiple schema fetches from LDAP server
    set_cached_schema( $server_id, 'matchingrules', $rules );
	return $rules;
}

/** 
 * Returns an array of Syntax objects that this LDAP server uses mapped to
 * their descriptions. The key of each entry is the OID of the Syntax.
 */
function get_schema_syntaxes( $server_id, $dn=null )
{
    if( cached_schema_available( $server_id, 'ldapsyntaxes' ) ) {
        return get_cached_schema( $server_id, 'ldapsyntaxes' );   
    }

	$raw_syntaxes = _get_raw_schema( $server_id, 'ldapSyntaxes', $dn );
	if( ! $raw_syntaxes )
		return false;

	// build the array of attributes
	$syntaxes = array();
	foreach( $raw_syntaxes as $syntax_string ) {
		$syntax = new Syntax( $syntax_string );
		$key = strtolower( trim( $syntax->getOID() ) );
		if( ! $key ) continue;
		$syntaxes[$key] = $syntax;
	}

	ksort( $syntaxes );

	// cache the schema to prevent multiple schema fetches from LDAP server
    set_cached_schema( $server_id, 'ldapsyntaxes', $syntaxes );

	return $syntaxes;
}

// --------------------------------------------------------------------
// Schema caching functions
// --------------------------------------------------------------------

/**
 * Returns true if the schema for $schema_type has been cached and 
 * is availble. $schema_type may be one of (lowercase) the following:
 *      objectclasses
 *      attributetypes
 *      ldapsyntaxes
 *      matchingrules
 *      matchingruleuse
 * Note that _get_raw_schema() takes a similar parameter.
 */
function cached_schema_available( $server_id, $schema_type )
{
    // Check config to make sure session-based caching is enabled.
    if( ! SCHEMA_SESSION_CACHE_ENABLED )
        return false;

    // Static memory cache available?
    // (note: this memory cache buys us a 20% speed improvement over strictly 
    //        checking the session, ie 0.05 to 0.04 secs)
    $schema_type = strtolower( $schema_type );
    static $cache_avail;
    if( isset( $cache_avail[ $server_id ][ $schema_type ] ) ) {
        return true;
    }

    // Session cache available?
    if( isset( $_SESSION[ 'schema' ][ $server_id ][ $schema_type ] ) ) {
        $cache_avail[ $server_id ][ $schema_type ] = true;
        return true;
    } else {
        return false;
    }
}

/**
 * Returns the cached array of schemaitem objects for the specified
 * $schema_type. For list of valid $schema_type values, see above
 * schema_cache_available(). Note that internally, this function 
 * utilizes a two-layer cache, one in memory using a static variable
 * for multiple calls within the same page load, and one in a session
 * for multiple calls within the same user session (spanning multiple 
 * page loads).
 *
 * Returns an array of SchemaItem objects on success or false on failure.
 */
function get_cached_schema( $server_id, $schema_type )
{
    // Check config to make sure session-based caching is enabled.
    if( ! SCHEMA_SESSION_CACHE_ENABLED )
        return false;

    static $cache;
    $schema_type = strtolower( $schema_type );
    if( isset( $cache[ $server_id ][ $schema_type ] ) ) {
        //echo "Getting memory-cached schema for \"$schema_type\"...<br />\n";
        return $cache[ $server_id ][ $schema_type ];
    }

    //echo "Getting session-cached schema for \"$schema_type\"...<br />\n";
    if( cached_schema_available( $server_id, $schema_type ) ) {
        $schema = $_SESSION[ 'schema' ][ $server_id ][ $schema_type ];
        $cache[ $server_id ][ $schema_type ] = $schema;
        return $schema;
    } else {
        return false;
    }
}

/**
 * Caches the specified $schema_type for the specified $server_id.
 * $schema_items should be an array of SchemaItem instances (ie,
 * an array of ObjectClass, AttributeType, LDAPSyntax, MatchingRuleUse,
 * or MatchingRule objects.
 *
 * Returns true on success of false on failure.
 */
function set_cached_schema( $server_id, $schema_type, $schema_items )
{
    // Check config to make sure session-based caching is enabled.
    if( ! SCHEMA_SESSION_CACHE_ENABLED )
        return false;

    //echo "Setting cached schema for \"$schema_type\"...<br />\n";
    // Sanity check. The schema must be in the form of an array
    if( ! is_array( $schema_items ) ) {
        die( "While attempting to cache schema, passed a non-array for \$schema_items!" );
        return false;
    }
    // Make sure we are being passed a valid array of schema_items
    foreach( $schema_items as $schema_item ) {
        if( ! is_subclass_of( $schema_item, 'SchemaItem' ) && 
            ! 0 == strcasecmp( 'SchemaItem', get_class( $schema_item ) ) ) {
            die( "While attempting to cache schema, one of the schema items passed is not a true SchemaItem instance!" );
            return false;
        }
    }

    $schema_type = strtolower( $schema_type );
    $_SESSION[ 'schema' ][ $server_id ][ $schema_type ] = $schema_items;
    return true;
}

?>
