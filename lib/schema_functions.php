<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/schema_functions.php,v 1.85 2005/09/17 20:37:04 wurley Exp $

/**
 * Classes and functions for fetching and parsing schema from an LDAP server.
 *
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 */

/**
 * Generic parent class for all schema items. A schema item is
 * an ObjectClass, an AttributeBype, a MatchingRule, or a Syntax.
 * All schema items have at least two things in common: An OID
 * and a description. This class provides an implementation for
 * these two data.
 * @package phpLDAPadmin
 */
class SchemaItem {
	/** The OID of this schema item. */
	var $oid;

	/** The description of this schema item. */
	var $description;

	/** Initialize class members to default values. */
	function initVars() {
		$this->oid = null;
		$this->description = null;
	}

	/** Default constructor. */
	function SchemaItem() {
		$this->initVars();
	}

	function setOID( $new_oid ) {
		$this->oid = $new_oid;
	}

	function setDescription( $new_desc ) {
		$this->description = $new_desc;
	}

	function getOID() {
		return $this->oid;
	}

	function getDescription() {
		return $this->description;
	}
}

/**
 * Represents an LDAP objectClass
 * @package phpLDAPadmin
 */
class ObjectClass extends SchemaItem {
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
	function initVars() {
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
	function ObjectClass( $raw_ldap_schema_string ) {
		debug_log(sprintf('%s::ObjectClass(): Entered with (%s)',get_class($this),$raw_ldap_schema_string),2);

		$this->initVars();
		$class = $raw_ldap_schema_string;
		$strings = preg_split("/[\s,]+/",$class,-1,PREG_SPLIT_DELIM_CAPTURE);

		for ($i=0; $i < count($strings); $i++) {

			switch ($strings[$i]) {
				case '(':
					break;

				case 'NAME':
					if ($strings[$i+1]!="(") {
						do {
							$i++;
							if(strlen($this->name)==0)
								$this->name = $strings[$i];
							else
								$this->name .= " ".$strings[$i];

						} while (!preg_match("/\'$/s", $strings[$i]));

					} else {
						$i++;

						do {
							$i++;
							if(strlen($this->name) == 0)
								$this->name = $strings[$i];
							else
								$this->name .= " " . $strings[$i];

						} while (!preg_match("/\'$/s", $strings[$i]));

						do {
							$i++;
						} while (! preg_match('/\)+\)?/',$strings[$i]));
					}

					$this->name = preg_replace("/^\'/", "", $this->name);
					$this->name = preg_replace("/\'$/", "", $this->name);

					debug_log(sprintf('%s::ObjectClass(): Case NAME returned (%s)',get_class($this),$this->name),9);
					break;

				case 'DESC':
					do {
						$i++;
						if (strlen($this->description)==0)
							$this->description=$this->description . $strings[$i];
						else
							$this->description=$this->description . " " . $strings[$i];

					} while (!preg_match("/\'$/s", $strings[$i]));

					debug_log(sprintf('%s::ObjectClass(): Case DESC returned (%s)',get_class($this),$this->description),9);
					break;

				case 'OBSOLETE':
					$this->is_obsolete = TRUE;

					debug_log(sprintf('%s::ObjectClass(): Case OBSOLETE returned (%s)',get_class($this),$this->is_obsolete),9);
					break;

				case 'SUP':
					if ($strings[$i+1]!="(") {
						$i++;
						array_push($this->sup_classes, preg_replace("/'/","",$strings[$i]));

					} else {
						$i++;
						do {
							$i++;
							if ($strings[$i]!="$")
								array_push($this->sup_classes,preg_replace("/'/","",$strings[$i]));

						} while (! preg_match('/\)+\)?/',$strings[$i+1]));
					}

					debug_log(sprintf('%s::ObjectClass(): Case SUP returned (%s)',get_class($this),serialize($this->sup_classes)),9);
					break;

				case 'ABSTRACT':
					$this->type='abstract';

					debug_log(sprintf('%s::ObjectClass(): Case ABSTRACT returned (%s)',get_class($this),$this->type),9);
					break;

				case 'STRUCTURAL':
					$this->type='structural';

					debug_log(sprintf('%s::ObjectClass(): Case STRUCTURAL returned (%s)',get_class($this),$this->type),9);
					break;

				case 'AUXILIARY':
					$this->type='auxiliary';

					debug_log(sprintf('%s::ObjectClass(): Case AUXILIARY returned (%s)',get_class($this),$this->type),9);
					break;

				case 'MUST':
					if (preg_match("/^\(./",$strings[$i+1])) {
						$i++;
						$attr = new ObjectClassAttribute(preg_replace("/^\(/","",$strings[$i]), $this->name);
						array_push ($this->must_attrs, $attr);

					} elseif ($strings[$i+1]!="(") {
						$i++;
						$attr = new ObjectClassAttribute($strings[$i], $this->name);
						array_push ($this->must_attrs, $attr);

					} else {
						$i++;
						do {
							$i++;
							if ($strings[$i]!="$") {
								$attr = new ObjectClassAttribute($strings[$i], $this->name);
								array_push ($this->must_attrs, $attr);
							}

						} while (! preg_match('/\)+\)?/',$strings[$i+1]));
					}
					sort($this->must_attrs);

					debug_log(sprintf('%s::ObjectClass(): Case MUST returned (%s)',get_class($this),serialize($this->must_attrs)),9);
					break;

				case 'MAY':
					if (preg_match("/^\(./",$strings[$i+1])) {
						$i++;
						$attr = new ObjectClassAttribute(preg_replace("/^\(/","",$strings[$i]), $this->name);
						array_push ($this->may_attrs, $attr);

					} elseif ($strings[$i+1]!="(") {
						$i++;
						$attr = new ObjectClassAttribute($strings[$i], $this->name);
						array_push ($this->may_attrs, $attr);

					} else {
						$i++;
						do {
							$i++;
							if ($strings[$i]!="$") {
								$attr = new ObjectClassAttribute($strings[$i], $this->name);
								array_push ($this->may_attrs, $attr);
							}

						} while (! preg_match('/\)+\)?/',$strings[$i+1]));
					}
					sort($this->may_attrs);

					debug_log(sprintf('%s::ObjectClass(): Case MUST returned (%s)',get_class($this),serialize($this->may_attrs)),9);
					break;

				default:
					if(preg_match ("/[\d\.]+/i",$strings[$i]) && $i == 1)
						$this->oid = $strings[$i];

					debug_log(sprintf('%s::ObjectClass(): Case default returned (%s)',get_class($this),$this->oid),9);
			}
		}

		$this->description = preg_replace("/^\'/", "", $this->description);
		$this->description = preg_replace("/\'$/", "", $this->description);

		debug_log(sprintf('%s::ObjectClass(): Returning ()',get_class($this)),1);
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
	function getMustAttrs($oclasses = NULL) {
		debug_log(sprintf('%s::getMustAttrs(): Entered with (%s)',get_class($this),serialize($oclasses)),2);

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
	function getMayAttrs($oclasses = NULL) {
		debug_log(sprintf('%s::getMayAttrs(): Entered with (%s)',get_class($this),serialize($oclasses)),2);

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
	function getMustAttrNames( $oclasses = null ) {
		debug_log(sprintf('%s::getMustAttrNames(): Entered with (%s)',get_class($this),serialize($oclasses)),2);

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
	function getMayAttrNames( $oclasses = null ) {
		debug_log(sprintf('%s::getMayAttrNames(): Entered with (%s)',get_class($this),serialize($oclasses)),2);

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
	function addChildObjectClass( $object_class_name ) {
		debug_log(sprintf('%s::addChildObjectClass(): Entered with (%s)',get_class($this),$object_class_name),2);

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
	function getChildObjectClasses() {
		return $this->children_objectclasses;
	}

	/**
	 * Gets the name of this objectClass (ie, "inetOrgPerson")
	 * @return string The name of the objectClass
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * Gets the objectClass names from which this objectClass inherits.
	 *
	 * @return array An array of objectClass names (strings)
	 */
	function getSupClasses() {
		return $this->sup_classes;
	}

	/**
	 * Gets the type of this objectClass: STRUCTURAL, ABSTRACT, or AUXILIARY.
	 */
	function getType() {
		return $this->type;
	}

	/**
	 * Gets whether this objectClass is flagged as obsolete by the LDAP server.
	 */
	function getIsObsolete() {
		return $this->is_obsolete;
	}

	/**
	 * Adds the specified array of attributes to this objectClass' list of
	 * MUST attributes. The resulting array of must attributes will contain
	 * unique members.
	 *
	 * @param array $new_must_attrs An array of attribute names (strings) to add.
	 */
	function addMustAttrs( $new_must_attrs ) {
		debug_log(sprintf('%s::addMustAttrs(): Entered with (%s)',get_class($this),$new_must_attrs),2);

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
	function addMayAttrs( $new_may_attrs ) {
		debug_log(sprintf('%s::addMayAttrs(): Entered with (%s)',get_class($this),$new_may_attrs),2);

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
 * @package phpLDAPadmin
 */
class ObjectClassAttribute {
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
	function ObjectClassAttribute ($name, $source) {
		$this->name=$name;
		$this->source=$source;
	}

	/** Gets this attribute's name */
	function getName () {
		return $this->name;
	}

	/** Gets the name of the ObjectClass which originally specified this attribute. */
	function getSource () {
		return $this->source;
	}
}

/**
 * Represents an LDAP AttributeType
 * @package phpLDAPadmin
 */
class AttributeType extends SchemaItem {
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
	/** A list of object class names that require this attribute type. */
	var $required_by_object_classes = array();

	/**
	 * Initialize the class' member variables
	 */
	function initVars() {
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
		$this->required_by_object_classes = array();
	}

	/**
	 * Creates a new AttributeType objcet from a raw LDAP AttributeType string.
	 */
	function AttributeType( $raw_ldap_attr_string ) {
		debug_log(sprintf('%s::AttributeType(): Entered with (%s)',get_class($this),$raw_ldap_attr_string),2);

		$this->initVars();
		$attr = $raw_ldap_attr_string;
		$strings = preg_split("/[\s,]+/",$attr,-1,PREG_SPLIT_DELIM_CAPTURE);

		for($i=0; $i<count($strings); $i++) {

			switch($strings[$i]) {
				case '(':
					break;

				case 'NAME':
					if ($strings[$i+1]!="(") {
						do {
							$i++;
							if (strlen($this->name)==0)
								$this->name = $strings[$i];
							else
								$this->name .= " " . $strings[$i];

						} while (!preg_match("/\'$/s", $strings[$i]));
						// this attribute has no aliases
						$this->aliases = array();

					} else {
						$i++;
						do {
							$i++;
							if (strlen($this->name) == 0)
 								$this->name = $strings[$i];
							else
								$this->name .= " " . $strings[$i];

						} while (!preg_match("/\'$/s", $strings[$i]));
						// add alias names for this attribute

						while ($strings[++$i]!=")") {
							$alias = $strings[$i];
							$alias = preg_replace("/^\'/", "", $alias );
							$alias = preg_replace("/\'$/", "", $alias );
							$this->aliases[] = $alias;
						}
					}

					debug_log(sprintf('%s::AttributeType(): Case NAME returned (%s) (%s)',get_class($this),$this->name,serialize($this->aliases)),9);
					break;

				case 'DESC':
					do {
						$i++;
						if (strlen($this->description)==0)
							$this->description=$this->description . $strings[$i];
						else
							$this->description=$this->description . " " . $strings[$i];
					} while (!preg_match("/\'$/s", $strings[$i]));

					debug_log(sprintf('%s::AttributeType(): Case DESC returned (%s)',get_class($this),$this->description),9);
					break;

				case 'OBSOLETE':
					$this->is_obsolete = TRUE;

					debug_log(sprintf('%s::AttributeType(): Case OBSOLETE returned (%s)',get_class($this),$this->is_obsolete),9);
					break;

				case 'SUP':
					$i++;
					$this->sup_attribute = $strings[$i];

					debug_log(sprintf('%s::AttributeType(): Case SUP returned (%s)',get_class($this),$this->sup_attribute),9);
					break;

				case 'EQUALITY':
					$i++;
					$this->equality = $strings[$i];

					debug_log(sprintf('%s::AttributeType(): Case EQUALITY returned (%s)',get_class($this),$this->equality),9);
					break;

				case 'ORDERING':
					$i++;
					$this->ordering = $strings[$i];

					debug_log(sprintf('%s::AttributeType(): Case ORDERING returned (%s)',get_class($this),$this->ordering),9);
					break;

				case 'SUBSTR':
					$i++;
					$this->sub_str = $strings[$i];

					debug_log(sprintf('%s::AttributeType(): Case SUBSTR returned (%s)',get_class($this),$this->sub_str),9);
					break;

				case 'SYNTAX':
					$i++;
					$this->syntax = $strings[$i];
					$this->syntax_oid = preg_replace("/{\d+}$/", "", $this->syntax);

					// does this SYNTAX string specify a max length (ie, 1.2.3.4{16})
					if (preg_match( "/{(\d+)}$/", $this->syntax, $this->max_length))
						$this->max_length = $this->max_length[1];
					else
						$this->max_length = null;

					if ($i < count($strings) - 1 && $strings[$i+1]=="{") {
						do {
							$i++;
							$this->name .= " " . $strings[$i];
						} while ($strings[$i]!="}");
					}

					debug_log(sprintf('%s::AttributeType(): Case SYNTAX returned (%s) (%s) (%s)',
						get_class($this),$this->syntax,$this->syntax_oid,$this->max_length),9);
					break;

				case 'SINGLE-VALUE':
					$this->is_single_value = TRUE;
					debug_log(sprintf('%s::AttributeType(): Case SINGLE-VALUE returned (%s)',get_class($this),$this->is_single_value),9);
					break;

				case 'COLLECTIVE':
					$this->is_collective = TRUE;

					debug_log(sprintf('%s::AttributeType(): Case COLLECTIVE returned (%s)',get_class($this),$this->is_collective),9);
					break;

				case 'NO-USER-MODIFICATION':
					$this->is_no_user_modification = TRUE;

					debug_log(sprintf('%s::AttributeType(): Case NO-USER-MODIFICATION returned (%s)',get_class($this),$this->is_no_user_modification),9);
					break;

				case 'USAGE':
					$i++;
					$this->usage = $strings[$i];

					debug_log(sprintf('%s::AttributeType(): Case USAGE returned (%s)',get_class($this),$this->usage),9);
					break;

				default:
					if(preg_match ("/[\d\.]+/i",$strings[$i]) && $i == 1)
						$this->oid = $strings[$i];

					debug_log(sprintf('%s::AttributeType(): Case default returned (%s)',get_class($this),$this->oid),9);
			}
		}

		$this->name = preg_replace("/^\'/", "", $this->name);
		$this->name = preg_replace("/\'$/", "", $this->name);
		$this->description = preg_replace("/^\'/", "", $this->description);
		$this->description = preg_replace("/\'$/", "", $this->description);
		$this->syntax = preg_replace("/^\'/", "", $this->syntax );
		$this->syntax = preg_replace("/\'$/", "", $this->syntax );
		$this->syntax_oid = preg_replace("/^\'/", "", $this->syntax_oid );
		$this->syntax_oid = preg_replace("/\'$/", "", $this->syntax_oid );
		$this->sup_attribute = preg_replace("/^\'/", "", $this->sup_attribute );
		$this->sup_attribute = preg_replace("/\'$/", "", $this->sup_attribute );

		debug_log(sprintf('%s::AttributeType(): Returning ()',get_class($this)),1);
	}

	/**
	 * Gets this attribute's name
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * Gets whether this attribute has been flagged as obsolete by the LDAP server
	 * @return bool
	 */
	function getIsObsolete() {
		return $this->is_obsolete;
	}

	/**
	 * Gets this attribute's usage string as defined by the LDAP server
	 * @return string
	 */
	function getUsage() {
		return $this->usage;
	}

	/**
	 * Gets this attribute's parent attribute (if any). If this attribute does not
	 * inherit from another attribute, null is returned.
	 * @return string
	 */
	function getSupAttribute() {
		return $this->sup_attribute;
	}

	/**
	 * Gets this attribute's equality string
	 * @return string
	 */
	function getEquality() {
		return $this->equality;
	}

	/**
	 * Gets this attribute's ordering specification.
	 * @return string
	 */
	function getOrdering() {
		return $this->ordering;
	}

	/**
	 * Gets this attribute's substring matching specification
	 * @return string
	 */
	function getSubstr() {
		return $this->sub_str;
	}

	/**
	 * Gets the names of attributes that are an alias for this attribute (if any).
	 * @return array An array of names of attributes which alias this attribute or
	 *          an empty array if no attribute aliases this object.
	 */
	function getAliases() {
		return $this->aliases;
	}

	/**
	 * Returns whether the specified attribute is an alias for this one (based on this attribute's alias list).
	 * @param string $attr_name The name of the attribute to check.
	 * @return bool True if the specified attribute is an alias for this one, or false otherwise.
	 */
	function isAliasFor( $attr_name ) {
		debug_log(sprintf('%s::isAliasFor(): Entered with (%s)',get_class($this),$attr_name),2);

		foreach( $this->aliases as $alias_attr_name )
			if( 0 == strcasecmp( $alias_attr_name, $attr_name ) )
				return true;
		return false;
	}

	/**
	 * Gets this attribute's raw syntax string (ie: "1.2.3.4{16}").
	 * @return string The raw syntax string
	 */
	function getSyntaxString() {
		return $this->syntax;
	}

	/**
	 * Gets this attribute's syntax OID. Differs from getSyntaxString() in that this
	 * function only returns the actual OID with any length specification removed.
	 * Ie, if the syntax string is "1.2.3.4{16}", this function only retruns
	 * "1.2.3.4".
	 * @return string The syntax OID string.
	 */
	function getSyntaxOID() {
		return $this->syntax_oid;
	}

	/**
	 * Gets this attribute's the maximum length. If no maximum is defined by the LDAP server, null is returned.
	 * @return int The maximum length (in characters) of this attribute or null if no maximum is specified.
	 */
	function getMaxLength() {
		return $this->max_length;
	}

	/**
	 * Gets whether this attribute is single-valued. If this attribute only supports single values, true
	 * is returned. If this attribute supports multiple values, false is returned.
	 * @return bool Returns true if this attribute is single-valued or false otherwise.
	 */
	function getIsSingleValue() {
		return $this->is_single_value;
	}

	/**
	 * Sets whether this attribute is single-valued.
	 * @param bool $is_single_value
	 */
	function setIsSingleValue( $is_single_value ) {
		$this->is_single_value = $is_single_value;
	}

	/**
	 * Gets whether this attribute is collective.
	 * @return bool Returns true if this attribute is collective and false otherwise.
	 */
	function getIsCollective() {
		return $this->is_collective;
	}

	/**
	 * Gets whether this attribute is not modifiable by users.
	 * @return bool Returns true if this attribute is not modifiable by users.
	 */
	function getIsNoUserModification() {
		return $this->is_no_user_modification;
	}

	/**
	 * Gets this attribute's type
	 * @return string The attribute's type.
	 */
	function getType() {
		return $this->type;
	}

	/**
	 * Removes an attribute name from this attribute's alias array.
	 * @param string $remove_alias_name The name of the attribute to remove.
	 * @return bool true on success or false on failure (ie, if the specified
	 *           attribute name is not found in this attribute's list of aliases)
	 */
	function removeAlias( $remove_alias_name ) {
		debug_log(sprintf('%s::removeAlias(): Entered with (%s)',get_class($this),$remove_alias_name),2);

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
	function addAlias( $new_alias_name ) {
		$this->aliases[] = $new_alias_name;
	}

	/**
	 * Sets this attriute's name.
	 * @param string $new_name The new name to give this attribute.
	 */
	function setName( $new_name ) {
		$this->name = $new_name;
	}

	/**
	 * Sets this attriute's SUP attribute (ie, the attribute from which this attribute inherits).
	 * @param string $new_sup_attr The name of the new parent (SUP) attribute
	 */
	function setSupAttribute( $new_sup_attr ) {
		$this->sup_attribute = $new_sup_attr;
	}

	/**
	 * Sets this attribute's list of aliases.
	 * @param array $new_aliases The array of alias names (strings)
	 */
	function setAliases( $new_aliases ) {
		$this->aliases = $new_aliases;
	}

	/**
	 * Sets this attribute's type.
	 * @param string $new_type The new type.
	 */
	function setType( $new_type ) {
		$this->type = $new_type;
	}

	/**
	 * Adds an objectClass name to this attribute's list of "used in" objectClasses,
	 * that is the list of objectClasses which provide this attribute.
	 * @param string $object_class_name The name of the objectClass to add.
	 */
	function addUsedInObjectClass( $object_class_name ) {
		debug_log(sprintf('%s::addUsedInObjectClass(): Entered with (%s)',get_class($this),$object_class_name),2);

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
	function getUsedInObjectClasses() {
		return $this->used_in_object_classes;
	}

	/**
	 * Adds an objectClass name to this attribute's list of "required by" objectClasses,
	 * that is the list of objectClasses which must have this attribute.
	 * @param string $object_class_name The name of the objectClass to add.
	 */
	function addRequiredByObjectClass( $object_class_name ) {
		debug_log(sprintf('%s::addRequiredByObjectClass(): Entered with (%s)',get_class($this),$object_class_name),2);

		foreach( $this->required_by_object_classes as $required_by_object_class )
			if( 0 == strcasecmp( $required_by_object_class, $object_class_name ) )
				return false;
		$this->required_by_object_classes[] = $object_class_name;
		return true;
	}

	/**
	 * Gets the list of "required by" objectClasses, that is the list of objectClasses
	 * which provide must have attribute.
	 * @return array An array of names of objectclasses (strings) which provide this attribute
	 */
	function getRequiredByObjectClasses() {
		return $this->required_by_object_classes;
	}
}

/**
 * Represents an LDAP Syntax
 * @package phpLDAPadmin
 */
class Syntax extends SchemaItem {
	/** Initializes the class' member variables */
	function initVars() {
		parent::initVars();

		$this->oid = null;
		$this->description = null;
	}

	/**
	 * Creates a new Syntax object from a raw LDAP syntax string.
	 */
	function Syntax( $raw_ldap_syntax_string ) {
		debug_log(sprintf('%s::Syntax(): Entered with (%s)',get_class($this),$raw_ldap_syntax_string),2);

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
 * @package phpLDAPadmin
 */
class MatchingRule extends SchemaItem {
	/** This rule's name */
	var $name;
	/** This rule's syntax OID */
	var $syntax;
	/** Boolean value indicating whether this MatchingRule is obsolete */
	var $is_obsolete;
	/** An array of attribute names who use this MatchingRule */
	var $used_by_attrs;

	/** Initialize the class' member variables */
	function initVars() {
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
	function MatchingRule( $raw_ldap_matching_rule_string ) {
		debug_log(sprintf('%s::MatchingRule(): Entered with (%s)',get_class($this),$raw_ldap_matching_rule_string),2);

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
						} while (! preg_match('/\)+\)?/',$strings[$i]));
					}
					$this->name = preg_replace("/^\'/", "", $this->name);
					$this->name = preg_replace("/\'$/", "", $this->name);
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
	function setUsedByAttrs( $attrs ) {
		$this->used_by_attrs = $attrs;
	}

	/**
	 * Adds an attribute name to the list of attributes who use this MatchingRule
	 * @return true if the attribute was added and false otherwise (already in the list)
	 */
	function addUsedByAttr( $new_attr_name ) {
		debug_log(sprintf('%s::addUsedByAttr(): Entered with (%s)',get_class($this),$new_attr_name),2);

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
	function getName() {
		return $this->name;
	}

	/**
	 * Gets whether this MatchingRule is flagged as obsolete by the LDAP server.
	 * @return bool True if this MatchingRule is obsolete and false otherwise.
	 */
	function getIsObsolete() {
		return $this->is_obsolete;
	}

	/**
	 * Gets this MatchingRule's syntax string (an OID).
	 */
	function getSyntax() {
		return $this->description;
	}

	/**
	 * Gets an array of attribute names (strings) which use this MatchingRule
	 * @return array The array of attribute names (strings).
	 */
	function getUsedByAttrs() {
		return $this->used_by_attrs;
	}
}

/**
 * Represents an LDAP schema matchingRuleUse entry
 * @package phpLDAPadmin
 */
class MatchingRuleUse extends SchemaItem {
	/** The name of the MathingRule this applies to */
	var $name;

	/** An array of attributeType names who make use of the mathingRule
	 * identified by $this->oid and $this->name */
	var $used_by_attrs;

	/** Initialize the class' member variables */
	function initVars() {
		parent::initVars();

		$this->oid = null;
		$this->name = null;
		$this->used_by_attrs = array();
	}

	function MatchingRuleUse( $raw_matching_rule_use_string ) {
		debug_log(sprintf('%s::MatchingRuleUse(): Entered with (%s)',get_class($this),$raw_matching_rule_use_string),2);

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
						} while (! preg_match('/\)+\)?/',$strings[$i]));
					}
					$this->name = preg_replace("/^\'/", "", $this->name);
					$this->name = preg_replace("/\'$/", "", $this->name);
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
	function getName() {
		return $this->name;
	}

	/**
	 * Gets an array of attribute names (strings) which use this MatchingRuleUse object.
	 * @return array The array of attribute names (strings).
	 */
	function getUsedByAttrs() {
		return $this->used_by_attrs;
	}
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
function get_schema_objectclasses($ldapserver,$dn=null) {
	debug_log(sprintf('get_schema_objectclasses(): Entered with (%s,%s)',$ldapserver->server_id,$dn),2);

	# Set default return
	$return = null;

	if ($return = get_cached_item($ldapserver->server_id,'schema','objectclasses')) {
		debug_log(sprintf('get_schema_objectclasses(): Returning CACHED [%s] (%s)',$ldapserver->server_id,'objectclasses'),3);
		return $return;
	}

	$raw_oclasses = $ldapserver->getRawSchema('objectclasses', $dn);
	if ($raw_oclasses) {

		# build the array of objectClasses
		$return = array();

		foreach ($raw_oclasses as $class_string) {
			if ($class_string == null || ! strlen($class_string))
				continue;

			$object_class = new ObjectClass($class_string);
			$return[strtolower($object_class->getName())] = $object_class;
		}

		ksort($return);

		# cache the schema to prevent multiple schema fetches from LDAP server
		set_cached_item($ldapserver->server_id,'schema','objectclasses',$return);
	}

	debug_log(sprintf('get_schema_objectclasses(): Returning (%s)',serialize($return)),1);
	return $return;
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
function get_schema_objectclass( $ldapserver,$oclass_name,$dn=null) {
	debug_log(sprintf('get_schema_objectclass(): Entered with (%s,%s,%s)',$ldapserver->server_id,$oclass_name,$dn),2);

	$oclass_name = strtolower( $oclass_name );
	$oclasses = get_schema_objectclasses($ldapserver,$dn);

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
function get_schema_attribute($ldapserver,$attr_name,$dn=null) {
	debug_log(sprintf('get_schema_attribute(): Entered with (%s,%s,%s)',$ldapserver->server_id,$attr_name,$dn),2);

	$attr_name = real_attr_name($attr_name);
	$schema_attrs = get_schema_attributes($ldapserver,$dn);
	$attr_name = strtolower($attr_name);
	$schema_attr = isset($schema_attrs[$attr_name]) ? $schema_attrs[$attr_name] : false;

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
function get_schema_attributes($ldapserver,$dn=null) {
	debug_log(sprintf('get_schema_attributes(): Entered with (%s,%s)',$ldapserver->server_id,$dn),2);

	# Set default return
	$return = null;

	if ($return = get_cached_item($ldapserver->server_id,'schema','attributes')) {
		debug_log(sprintf('get_schema_attributes(): Returning CACHED [%s] (%s)',$ldapserver->server_id,'attributes'),3);
		return $return;
	}

	$raw_attrs = $ldapserver->getRawSchema('attributeTypes', $dn);
	if ($raw_attrs) {

		# build the array of attribueTypes
		$syntaxes = get_schema_syntaxes($ldapserver,$dn);
		$attrs = array();

		/**
		 * bug 856832: create two arrays - one indexed by name (the standard
		 * $attrs array above) and one indexed by oid (the new $attrs_oid array
		 * below). This will help for directory servers, like IBM's, that use OIDs
		 * in their attribute definitions of SUP, etc
		 */
		$attrs_oid = array();
		foreach ($raw_attrs as $attr_string) {
			if (is_null($attr_string) || strlen($attr_string) == 0 )
				continue;

			$attr = new AttributeType( $attr_string );
			if (isset($syntaxes[$attr->getSyntaxOID()])) {
				$syntax = $syntaxes[$attr->getSyntaxOID()];
				$attr->setType($syntax->getDescription());
			}
			$attrs[strtolower($attr->getName())] = $attr;

			/**
			 * bug 856832: create an entry in the $attrs_oid array too. This
			 * will be a ref to the $attrs entry for maintenance and performance
			 * reasons
			 */
			$attrs_oid[$attr->getOID()] = &$attrs[strtolower($attr->getName())];
		}

		add_aliases_to_attrs($attrs);
		/**
		 * bug 856832: pass the $attrs_oid array as a second (new) parameter
		 * to add_sup_to_attrs. This will allow lookups by either name or oid.
		 */
		add_sup_to_attrs($attrs,$attrs_oid);

		ksort($attrs);

		# Add the used in and required_by values.
		$schema_object_classes = get_schema_objectclasses($ldapserver);
		if (! is_array($schema_object_classes))
			return array();

		foreach ($schema_object_classes as $object_class) {
			$must_attrs = $object_class->getMustAttrNames($schema_object_classes);
			$may_attrs = $object_class->getMayAttrNames($schema_object_classes);
			$oclass_attrs = array_unique(array_merge($must_attrs,$may_attrs));

			# Add Used In.
			foreach ($oclass_attrs as $attr_name) {
				if (isset($attrs[strtolower($attr_name)]))
					$attrs[strtolower($attr_name)]->addUsedInObjectClass($object_class->getName());

				else {
					#echo "Warning, attr not set: $attr_name<br />";
				}
			}

			# Add Required By.
			foreach ($must_attrs as $attr_name) {
				if (isset($attrs[strtolower($attr_name)]))
					$attrs[strtolower($attr_name)]->addRequiredByObjectClass($object_class->getName());

				else {
					#echo "Warning, attr not set: $attr_name<br />";
				}
			}

		}

		$return = $attrs;
		# cache the schema to prevent multiple schema fetches from LDAP server
		set_cached_item($ldapserver->server_id,'schema','attributes',$return);
	}

	debug_log(sprintf('get_schema_attributes(): Returning (%s)',serialize($return)),1);
	return $return;
}

/**
 * For each attribute that has multiple names, this function adds unique entries to
 * the attrs array for those names. Ie, attributeType has name 'gn' and 'givenName'.
 * This function will create a unique entry for 'gn' and 'givenName'.
 */
function add_aliases_to_attrs( &$attrs ) {
	debug_log(sprintf('add_aliases_to_attrs(): Entered with (%s)',serialize($attrs)),2);

	// go back and add data from aliased attributeTypes
	foreach( $attrs as $name => $attr ) {
		$aliases = $attr->getAliases();
		if( is_array( $aliases ) && count( $aliases ) > 0 ) {
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
function add_sup_to_attrs( &$attrs, &$attrs_oid ) {
	debug_log(sprintf('add_sup_to_attrs(): Entered with (%s,%s)',serialize($attrs),serialize($attrs_oid)),2);

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
function get_schema_matching_rules($ldapserver,$dn=null) {
	debug_log(sprintf('get_schema_matching_rules(): Entered with (%s,%s)',$ldapserver->server_id,$dn),2);

	# Set default return
	$return = null;

	if ($return = get_cached_item($ldapserver->server_id,'schema','matchingrules')) {
		debug_log(sprintf('get_schema_matching_rules(): Returning CACHED [%s] (%s)',
			$ldapserver->server_id,'matchingrules'),3);
		return $return;
	}

	# build the array of MatchingRule objects
	$raw_matching_rules = $ldapserver->getRawSchema('matchingRules', $dn);
	if ($raw_matching_rules) {

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
		$raw_matching_rule_use = $ldapserver->getRawSchema('matchingRuleUse');
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
			$attrs = get_schema_attributes( $ldapserver, $dn );
			if( is_array( $attrs ) )
				foreach( $attrs as $attr ) {
					$rule_key = strtolower( $attr->getEquality() );
					if( isset( $rules[ $rule_key ] ) )
						$rules[ $rule_key ]->addUsedByAttr( $attr->getName() );
				}
		}

		$return = $rules;
		# cache the schema to prevent multiple schema fetches from LDAP server
		set_cached_item($ldapserver->server_id,'schema','matchingrules',$return);
	}

	debug_log(sprintf('get_schema_attributes(): Returning (%s)',serialize($return)),1);
	return $return;
}

/**
 * Returns an array of Syntax objects that this LDAP server uses mapped to
 * their descriptions. The key of each entry is the OID of the Syntax.
 */
function get_schema_syntaxes($ldapserver,$dn=null) {
	debug_log(sprintf('get_schema_syntaxes(): Entered with (%s,%s)',$ldapserver->server_id,$dn),2);

	# Set default return
	$return = null;

	if ($return = get_cached_item($ldapserver->server_id,'schema','syntaxes')) {
		debug_log(sprintf('get_schema_syntaxes(): Returning CACHED [%s] (%s)',$ldapserver->server_id,'syntaxes'),3);
		return $return;
	}

	$raw_syntaxes = $ldapserver->getRawSchema('ldapSyntaxes', $dn);
	if ($raw_syntaxes) {

		# build the array of attributes
		$return = array();
		foreach ($raw_syntaxes as $syntax_string) {
			$syntax = new Syntax($syntax_string);
			$key = strtolower(trim($syntax->getOID()));
			if (! $key) continue;
			$return[$key] = $syntax;
		}

		ksort($return);

		# cache the schema to prevent multiple schema fetches from LDAP server
		set_cached_item($ldapserver->server_id,'schema','syntaxes',$return);
	}

	debug_log(sprintf('get_schema_syntaxes(): Returning (%s)',serialize($return)),1);
	return $return;
}
?>
