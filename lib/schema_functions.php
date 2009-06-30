<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/schema_functions.php,v 1.88.2.6 2006/03/08 08:22:56 wurley Exp $

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
	function initVars($ldapserver) {
		parent::initVars();

		$this->oid = null;
		$this->name = null;
		$this->description = null;
		$this->sup_classes = array();
		$this->type = $ldapserver->schema_oclass_default;
		$this->must_attrs = array();
		$this->may_attrs = array();
		$this->is_obsolete = false;
		$this->children_objectclasses = array();
	}

	function _parse_list($i, $strings, &$attrs) {
	        /**
		 ** A list starts with a ( followed by a list of attributes separated by $ terminated by )
		 ** The first token can therefore be a ( or a (NAME or a (NAME)
		 ** The last token can therefore be a ) or NAME)
		 ** The last token may be terminate by more than one bracket
		 */
		if (DEBUG_ENABLED)
			debug_log('%s::_parse_list(): Entered with (%d,%s,%s)',9,
				get_class($this),$i,$strings,$attrs);

		$string = $strings[$i];
		if (!preg_match('/^\(/',$string)) {
		        // A bareword only - can be terminated by a ) if the last item
			if (preg_match('/\)+$/',$string))
			        $string = preg_replace('/\)+$/','',$string);

			array_push($attrs, $string);

		} elseif (preg_match('/^\(.*\)$/',$string)) {
		        $string = preg_replace('/^\(/','',$string);
			$string = preg_replace('/\)+$/','',$string);
			array_push($attrs, $string);

		} else {
		        // Handle the opening cases first

		        if ($string == '(') {
			        $i++;

			} elseif (preg_match('/^\(./',$string)) {
			        $string = preg_replace('/^\(/','',$string);
				array_push ($attrs, $string);
				$i++;
			}

			// Token is either a name, a $ or a ')'
			// NAME can be terminated by one or more ')'
			while (! preg_match('/\)+$/',$strings[$i])) {
			        $string = $strings[$i];
				if ($string == '$') {
				        $i++;
					continue;
				}

				if (preg_match('/\)$/',$string)) {
				        $string = preg_replace('/\)+$/','',$string);
				} else {
				        $i++;
				}

				array_push ($attrs, $string);
			}
		}
		sort($attrs);

		if (DEBUG_ENABLED)
			debug_log('%s::_parse_list(): Returning (%d,[%s],[%s])',9,
				get_class($this),$i,$strings,$attrs);
		return $i;
	}

	/**
	 * Creates a new ObjectClass object given a raw LDAP objectClass string.
	 * @todo: Unfortunately, some schemas are not well defined - eg: dNSDomain. Where the schema definition is not case consistent with the attribute definitions. This causes us some problems, which we need to resolve.
	 */
	function ObjectClass($raw_ldap_schema_string,$ldapserver) {
	        if (DEBUG_ENABLED)
			debug_log('%s::__construct(): Entered with (%s)',9,get_class($this),$raw_ldap_schema_string);

		$this->initVars($ldapserver);
		$class = $raw_ldap_schema_string;
		$strings = preg_split('/[\s,]+/',$class,-1,PREG_SPLIT_DELIM_CAPTURE);

		$str_count = count($strings);

		for ($i=0; $i < $str_count; $i++) {

			switch ($strings[$i]) {
				case '(':
					break;

				case 'NAME':
					if ($strings[$i+1]!='(') {
						do {
							$i++;
							if(strlen($this->name)==0)
								$this->name = $strings[$i];
							else
								$this->name .= ' '.$strings[$i];

						} while (!preg_match('/\'$/s', $strings[$i]));

					} else {
						$i++;

						do {
							$i++;
							if(strlen($this->name) == 0)
								$this->name = $strings[$i];
							else
								$this->name .= ' ' . $strings[$i];

						} while (!preg_match('/\'$/s', $strings[$i]));

						do {
							$i++;
						} while (! preg_match('/\)+\)?/',$strings[$i]));
					}

					$this->name = preg_replace('/^\'/', '', $this->name);
					$this->name = preg_replace('/\'$/', '', $this->name);

					if (DEBUG_ENABLED)
						debug_log('%s::__construct(): Case NAME returned (%s)',8,
							get_class($this),$this->name);
					break;

				case 'DESC':
					do {
						$i++;
						if (strlen($this->description)==0)
							$this->description=$this->description . $strings[$i];
						else
							$this->description=$this->description . ' ' . $strings[$i];

					} while (!preg_match('/\'$/s', $strings[$i]));

					if (DEBUG_ENABLED)
						debug_log('%s::__construc(): Case DESC returned (%s)',8,
							get_class($this),$this->description);

					break;

				case 'OBSOLETE':
					$this->is_obsolete = TRUE;

					if (DEBUG_ENABLED)
						debug_log('%s::__construct(): Case OBSOLETE returned (%s)',8,
							get_class($this),$this->is_obsolete);

					break;

				case 'SUP':
					if ($strings[$i+1]!='(') {
						$i++;
						array_push($this->sup_classes,preg_replace("/'/",'',$strings[$i]));

					} else {
						$i++;
						do {
							$i++;
							if ($strings[$i]!='$')
								array_push($this->sup_classes,preg_replace("/'/",'',$strings[$i]));

						} while (! preg_match('/\)+\)?/',$strings[$i+1]));
					}

					if (DEBUG_ENABLED)
						debug_log('%s::__construct(): Case SUP returned (%s)',8,
							get_class($this),$this->sup_classes);

					break;

				case 'ABSTRACT':
					$this->type='abstract';

					if (DEBUG_ENABLED)
						debug_log('%s::__construct(): Case ABSTRACT returned (%s)',8,
							get_class($this),$this->type);

					break;

				case 'STRUCTURAL':
					$this->type='structural';

					if (DEBUG_ENABLED)
						debug_log('%s::__construct(): Case STRUCTURAL returned (%s)',8,
							get_class($this),$this->type);
					break;

				case 'AUXILIARY':
					$this->type='auxiliary';

					if (DEBUG_ENABLED)
						debug_log('%s::__construct(): Case AUXILIARY returned (%s)',8,
							get_class($this),$this->type);
					break;

				case 'MUST':
				        $attrs = array();

					$i = $this->_parse_list(++$i, $strings, $attrs);

					if (DEBUG_ENABLED)
						debug_log('%s::__construct(): _parse_list returned %d (%s)',8,
							get_class($this),$i,$attrs);

					foreach ($attrs as $string) {
					        $attr = new ObjectClassAttribute($string, $this->name);
						array_push ($this->must_attrs, $attr);
					}

					if (DEBUG_ENABLED)
						debug_log('%s::__construct(): Case MUST returned (%s)',8,
							get_class($this),$this->must_attrs);
					break;

				case 'MAY':
				        $attrs = array();

					$i = $this->_parse_list(++$i, $strings, $attrs);

					if (DEBUG_ENABLED)
						debug_log('%s::__construct(): _parse_list returned %d (%s)',8,
							get_class($this),$i,$attrs);

					foreach ($attrs as $string) {
					        $attr = new ObjectClassAttribute($string, $this->name);
						array_push ($this->may_attrs, $attr);
					}

					if (DEBUG_ENABLED)
						debug_log('%s::__construct(): Case MAY returned (%s)',8,
							get_class($this),$this->may_attrs);
					break;

				default:
				        if(preg_match ('/[\d\.]+/i',$strings[$i]) && $i == 1) {
						$this->oid = $strings[$i];

						if (DEBUG_ENABLED)
							debug_log('%s::__construct(): Case default returned (%s)',8,
								get_class($this),$this->oid);
					}
					break;
			}
		}

		$this->description = preg_replace("/^\'/", '', $this->description);
		$this->description = preg_replace("/\'$/", '', $this->description);

		if (DEBUG_ENABLED)
			debug_log('%s::__construct(): Returning () - NAME (%s), DESCRIPTION (%s), MUST (%s), MAY (%s)',9,
				get_class($this),$this->name,$this->description,$this->must_attrs,$this->may_attrs);
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
		if (DEBUG_ENABLED)
			debug_log('%s::getMustAttrs(): Entered with (%s)',9,get_class($this),$oclasses);

		$all_must_attrs = array();
		$all_must_attrs = $this->must_attrs;
		foreach ($this->sup_classes as $sup_class) {
			if (! is_null($oclasses) && $sup_class != 'top'
				&& isset($oclasses[strtolower($sup_class)])) {
					$sup_class = $oclasses[ strtolower($sup_class)];
					$sup_class_must_attrs = $sup_class->getMustAttrs($oclasses);
					$all_must_attrs = array_merge($sup_class_must_attrs,$all_must_attrs);
			}
		}

		masort($all_must_attrs,'name,source',1);

		# Remove any duplicates
		foreach ($all_must_attrs as $index => $attr)
			if (isset($allattr[$attr->name]))
				unset($all_must_attrs[$index]);
			else
				$allattr[$attr->name] = 1;

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
	function getMayAttrs($oclasses=null) {
		if (DEBUG_ENABLED)
			debug_log('%s::getMayAttrs(): Entered with (%s)',9,get_class($this),$oclasses);

		$all_may_attrs = array();
		$all_may_attrs = $this->may_attrs;
		foreach ($this->sup_classes as $sup_class_name) {
			if (! is_null($oclasses) && $sup_class_name != 'top'
				&& isset($oclasses[strtolower($sup_class_name)])) {

					$sup_class = $oclasses[strtolower($sup_class_name)];
					$sup_class_may_attrs = $sup_class->getMayAttrs($oclasses);
					$all_may_attrs = array_merge($sup_class_may_attrs,$all_may_attrs);
			}
		}

		masort($all_may_attrs,'name,source',1);

		# Remove any duplicates
		foreach ($all_may_attrs as $index => $attr)
			if (isset($allattr[$attr->name]))
				unset($all_may_attrs[$index]);
			else
				$allattr[$attr->name] = 1;

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
		if (DEBUG_ENABLED)
			debug_log('%s::getMustAttrNames(): Entered with (%s)',9,get_class($this),$oclasses);

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
		if (DEBUG_ENABLED)
			debug_log('%s::getMayAttrNames(): Entered with (%s)',9,get_class($this),$oclasses);

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
		if (DEBUG_ENABLED)
			debug_log('%s::addChildObjectClass(): Entered with (%s)',9,get_class($this),$object_class_name);

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
		if (DEBUG_ENABLED)
			debug_log('%s::addMustAttrs(): Entered with (%s)',9,get_class($this),$new_must_attrs);

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
		if (DEBUG_ENABLED)
			debug_log('%s::addMayAttrs(): Entered with (%s)',9,get_class($this),$new_may_attrs);

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
	function ObjectClassAttribute($name,$source) {
		if (DEBUG_ENABLED)
			debug_log('%s::__construct(): Entered with name (%s), source (%s)',9,get_class($this),$name,$source);
		$this->name = $name;
		$this->source = $source;
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
	var $required_by_object_classes;

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
		if (DEBUG_ENABLED)
			debug_log('%s::__construct(): Entered with (%s)',9,get_class($this),$raw_ldap_attr_string);

		$this->initVars();
		$attr = $raw_ldap_attr_string;
		$strings = preg_split('/[\s,]+/',$attr,-1,PREG_SPLIT_DELIM_CAPTURE);

		for($i=0; $i<count($strings); $i++) {

			switch($strings[$i]) {
				case '(':
					break;

				case 'NAME':
					if ($strings[$i+1]!='(') {
						do {
							$i++;
							if (strlen($this->name)==0)
								$this->name = $strings[$i];
							else
								$this->name .= ' ' . $strings[$i];

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
								$this->name .= ' ' . $strings[$i];

						} while (!preg_match("/\'$/s", $strings[$i]));
						// add alias names for this attribute

						while ($strings[++$i]!=')') {
							$alias = $strings[$i];
							$alias = preg_replace("/^\'/", '', $alias );
							$alias = preg_replace("/\'$/", '', $alias );
							$this->aliases[] = $alias;
						}
					}

					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case NAME returned (%s) (%s)',8,
							get_class($this),$this->name,$this->aliases);
					break;

				case 'DESC':
					do {
						$i++;
						if (strlen($this->description)==0)
							$this->description=$this->description . $strings[$i];
						else
							$this->description=$this->description . ' ' . $strings[$i];
					} while (!preg_match("/\'$/s", $strings[$i]));

					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case DESC returned (%s)',8,
							get_class($this),$this->description);
					break;

				case 'OBSOLETE':
					$this->is_obsolete = TRUE;

					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case OBSOLETE returned (%s)',8,
							get_class($this),$this->is_obsolete);
					break;

				case 'SUP':
					$i++;
					$this->sup_attribute = $strings[$i];

					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case SUP returned (%s)',8,
							get_class($this),$this->sup_attribute);
					break;

				case 'EQUALITY':
					$i++;
					$this->equality = $strings[$i];

					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case EQUALITY returned (%s)',8,
							get_class($this),$this->equality);
					break;

				case 'ORDERING':
					$i++;
					$this->ordering = $strings[$i];

					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case ORDERING returned (%s)',8,
							get_class($this),$this->ordering);
					break;

				case 'SUBSTR':
					$i++;
					$this->sub_str = $strings[$i];

					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case SUBSTR returned (%s)',8,
							get_class($this),$this->sub_str);
					break;

				case 'SYNTAX':
					$i++;
					$this->syntax = $strings[$i];
					$this->syntax_oid = preg_replace('/{\d+}$/', '', $this->syntax);

					// does this SYNTAX string specify a max length (ie, 1.2.3.4{16})
					if (preg_match( '/{(\d+)}$/', $this->syntax, $this->max_length))
						$this->max_length = $this->max_length[1];
					else
						$this->max_length = null;

					if ($i < count($strings) - 1 && $strings[$i+1]=='{') {
						do {
							$i++;
							$this->name .= ' ' . $strings[$i];
						} while ($strings[$i]!='}');
					}

					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case SYNTAX returned (%s) (%s) (%s)',8,
							get_class($this),$this->syntax,$this->syntax_oid,$this->max_length);
					break;

				case 'SINGLE-VALUE':
					$this->is_single_value = TRUE;
					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case SINGLE-VALUE returned (%s)',8,
							get_class($this),$this->is_single_value);
					break;

				case 'COLLECTIVE':
					$this->is_collective = TRUE;

					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case COLLECTIVE returned (%s)',8,
							get_class($this),$this->is_collective);
					break;

				case 'NO-USER-MODIFICATION':
					$this->is_no_user_modification = TRUE;

					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case NO-USER-MODIFICATION returned (%s)',8,
							get_class($this),$this->is_no_user_modification);
					break;

				case 'USAGE':
					$i++;
					$this->usage = $strings[$i];

					if (DEBUG_ENABLED)
						debug_log('%s::AttributeType(): Case USAGE returned (%s)',8,
							get_class($this),$this->usage);
					break;

				default:
				        if(preg_match ('/[\d\.]+/i',$strings[$i]) && $i == 1) {
						$this->oid = $strings[$i];
						if (DEBUG_ENABLED)
							debug_log('%s::AttributeType(): Case default returned (%s)',8,
								get_class($this),$this->oid);
					}
			}
		}

		$this->name = preg_replace("/^\'/", '', $this->name);
		$this->name = preg_replace("/\'$/", '', $this->name);
		$this->description = preg_replace("/^\'/", '', $this->description);
		$this->description = preg_replace("/\'$/", '', $this->description);
		$this->syntax = preg_replace("/^\'/", '', $this->syntax );
		$this->syntax = preg_replace("/\'$/", '', $this->syntax );
		$this->syntax_oid = preg_replace("/^\'/", '', $this->syntax_oid );
		$this->syntax_oid = preg_replace("/\'$/", '', $this->syntax_oid );
		$this->sup_attribute = preg_replace("/^\'/", '', $this->sup_attribute );
		$this->sup_attribute = preg_replace("/\'$/", '', $this->sup_attribute );

		if (DEBUG_ENABLED)
			debug_log('%s::AttributeType(): Returning ()',9,get_class($this));
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
		if (DEBUG_ENABLED)
			debug_log('%s::isAliasFor(): Entered with (%s)',9,get_class($this),$attr_name);

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
		if (DEBUG_ENABLED)
			debug_log('%s::removeAlias(): Entered with (%s)',9,get_class($this),$remove_alias_name);

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
		if (DEBUG_ENABLED)
			debug_log('%s::addUsedInObjectClass(): Entered with (%s)',9,get_class($this),$object_class_name);

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
		if (DEBUG_ENABLED)
			debug_log('%s::addRequiredByObjectClass(): Entered with (%s)',9,get_class($this),$object_class_name);

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
	function Syntax ( $raw_ldap_syntax_string ) {
		if (DEBUG_ENABLED)
			debug_log('%s::__construct(): Entered with (%s)',9,get_class($this),$raw_ldap_syntax_string);

		$this->initVars();

		$class = $raw_ldap_syntax_string;
		$strings = preg_split ('/[\s,]+/', $class, -1,PREG_SPLIT_DELIM_CAPTURE);
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
							$this->description=$this->description . ' ' . $strings[$i];
					}while(!preg_match("/\'$/s", $strings[$i]));
					break;
				default:
					if(preg_match ('/[\d\.]+/i',$strings[$i]) && $i == 1)
						$this->oid = $strings[$i];
			}
		}
		$this->description = preg_replace("/^\'/", '', $this->description);
		$this->description = preg_replace("/\'$/", '', $this->description);
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
		if (DEBUG_ENABLED)
			debug_log('%s::__construct(): Entered with (%s)',9,get_class($this),$raw_ldap_matching_rule_string);

		$this->initVars();
		$strings = preg_split ('/[\s,]+/', $raw_ldap_matching_rule_string, -1,PREG_SPLIT_DELIM_CAPTURE);
		for($i=0; $i<count($strings); $i++) {
			switch($strings[$i]) {

				case '(':
					break;

				case 'NAME':
					if($strings[$i+1]!='(') {
						do {
							$i++;
							if(strlen($this->name)==0)
								$this->name = $strings[$i];
						else
								$this->name .= ' ' . $strings[$i];
						}while(!preg_match("/\'$/s", $strings[$i]));
					} else {
						$i++;
						do {
							$i++;
							if(strlen($this->name) == 0)
								$this->name = $strings[$i];
							else
								$this->name .= ' ' . $strings[$i];
						} while(!preg_match("/\'$/s", $strings[$i]));
						do {
							$i++;
						} while (! preg_match('/\)+\)?/',$strings[$i]));
					}
					$this->name = preg_replace("/^\'/", '', $this->name);
					$this->name = preg_replace("/\'$/", '', $this->name);
					break;

				case 'DESC':
					do {
						$i++;
						if(strlen($this->description)==0)
							$this->description=$this->description . $strings[$i];
						else
							$this->description=$this->description . ' ' . $strings[$i];
					}while(!preg_match("/\'$/s", $strings[$i]));
					break;

				case 'OBSOLETE':
					$this->is_obsolete = TRUE;
					break;

				case 'SYNTAX':
					$this->syntax = $strings[++$i];
					break;

				default:
					if(preg_match ('/[\d\.]+/i',$strings[$i]) && $i == 1)
						$this->oid = $strings[$i];
			}
		}
		$this->description = preg_replace("/^\'/", '', $this->description);
		$this->description = preg_replace("/\'$/", '', $this->description);
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
		if (DEBUG_ENABLED)
			debug_log('%s::addUsedByAttr(): Entered with (%s)',9,get_class($this),$new_attr_name);

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
		if (DEBUG_ENABLED)
			debug_log('%s::__construct(): Entered with (%s)',9,get_class($this),$raw_matching_rule_use_string);

		$this->initVars();

		$strings = preg_split ('/[\s,]+/', $raw_matching_rule_use_string, -1,PREG_SPLIT_DELIM_CAPTURE);
		for($i=0; $i<count($strings); $i++) {
			switch($strings[$i]) {

				case '(':
					break;

				case 'NAME':
					if($strings[$i+1]!='(') {
						do {
							$i++;
							if( ! isset( $this->name ) || strlen( $this->name ) ==0 )
								$this->name = $strings[$i];
						else
								$this->name .= ' ' . $strings[$i];
						}while(!preg_match("/\'$/s", $strings[$i]));
					} else {
						$i++;
						do {
							$i++;
							if(strlen($this->name) == 0)
								$this->name = $strings[$i];
							else
								$this->name .= ' ' . $strings[$i];
						} while(!preg_match("/\'$/s", $strings[$i]));
						do {
							$i++;
						} while (! preg_match('/\)+\)?/',$strings[$i]));
					}
					$this->name = preg_replace("/^\'/", '', $this->name);
					$this->name = preg_replace("/\'$/", '', $this->name);
					break;

				case 'APPLIES':
					// TODO
					if($strings[$i+1]!='(') {
						// has a single attribute name
						$i++;
						$this->used_by_attrs = array( $strings[$i] );
						//echo "Adding single: " . $strings[$i] . "<br />";
					} else {
						// has multiple attribute names
						$i++;
						while($strings[$i]!=')') {
							$i++;
							$new_attr = $strings[$i];
							$new_attr = preg_replace("/^\'/", '', $new_attr );
							$new_attr = preg_replace("/\'$/", '', $new_attr );
							$this->used_by_attrs[] = $new_attr;
							//echo "Adding $new_attr<br />";
							$i++;
						}
					}
					break;

				default:
					if(preg_match ('/[\d\.]+/i',$strings[$i]) && $i == 1)
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
?>
