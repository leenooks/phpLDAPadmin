<?php
/**
 * Classes and functions for fetching and parsing schema from an LDAP server.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * Generic parent class for all schema items. A schema item is
 * an ObjectClass, an AttributeBype, a MatchingRule, or a Syntax.
 * All schema items have at least two things in common: An OID
 * and a description. This class provides an implementation for
 * these two data.
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
abstract class SchemaItem {
	# The schema item's name.
	protected $name = null;
	# The OID of this schema item.
	private $oid = null;
	# The description of this schema item.
	protected $description = null;
	# Boolean value indicating whether this objectClass is obsolete
	private $is_obsolete = false;

	public function setOID($oid) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->oid = $oid;
	}

	public function setDescription($desc) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->description = $desc;
	}

	public function getOID() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->oid);

		return $this->oid;
	}

	public function getDescription() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->description);

		return $this->description;
	}

	/**
	 * Gets whether this objectClass is flagged as obsolete by the LDAP server.
	 */
	public function getIsObsolete() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->is_obsolete);

		return $this->is_obsolete;
	}

	/**
	 * Return the objects name.
	 *
	 * param boolean $lower Return the name in lower case (default)
	 * @return string The name
	 */
	public function getName($lower=true) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->name);

		return $lower ? strtolower($this->name) : $this->name;
	}
}

/**
 * Represents an LDAP objectClass
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
class ObjectClass extends SchemaItem {
	# The server ID that this objectclass belongs to.
	private $server_id = null;
	# Array of objectClass names from which this objectClass inherits
	private $sup_classes = array();
	# One of STRUCTURAL, ABSTRACT, or AUXILIARY
	private $type;
	# Arrays of attribute names that this objectClass requires
	private $must_attrs = array();
	# Arrays of attribute names that this objectClass allows, but does not require
	private $may_attrs = array();
	# Arrays of attribute names that this objectClass has been forced to MAY attrs, due to configuration
	private $force_may = array();
	# Array of objectClasses which inherit from this one (must be set at runtime explicitly by the caller)
	private $children_objectclasses = array();
	# The objectclass hierarchy
	private $hierarchy = array();

	/**
	 * Creates a new ObjectClass object given a raw LDAP objectClass string.
	 */
	public function __construct($class,$server) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->server_id = $server->getIndex();
		$this->type = $server->getValue('server','schema_oclass_default');

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
							if (strlen($this->name) == 0)
								$this->name = $strings[$i];
							else
								$this->name .= ' '.$strings[$i];

						} while (! preg_match('/\'$/s',$strings[$i]));

					} else {
						$i++;
						do {
							$i++;
							if (strlen($this->name) == 0)
								$this->name = $strings[$i];
							else
								$this->name .= ' '.$strings[$i];

						} while (! preg_match('/\'$/s',$strings[$i]));

						do {
							$i++;
						} while (! preg_match('/\)+\)?/',$strings[$i]));
					}

					$this->name = preg_replace('/^\'/','',$this->name);
					$this->name = preg_replace('/\'$/','',$this->name);

					if (DEBUG_ENABLED)
						debug_log('Case NAME returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->name);
					break;

				case 'DESC':
					do {
						$i++;
						if (strlen($this->description) == 0)
							$this->description=$this->description.$strings[$i];
						else
							$this->description=$this->description.' '.$strings[$i];

					} while (! preg_match('/\'$/s',$strings[$i]));

					if (DEBUG_ENABLED)
						debug_log('Case DESC returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->description);
					break;

				case 'OBSOLETE':
					$this->is_obsolete = TRUE;

					if (DEBUG_ENABLED)
						debug_log('Case OBSOLETE returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->is_obsolete);
					break;

				case 'SUP':
					if ($strings[$i+1] != '(') {
						$i++;
						array_push($this->sup_classes,preg_replace("/'/",'',$strings[$i]));

					} else {
						$i++;
						do {
							$i++;
							if ($strings[$i] != '$')
								array_push($this->sup_classes,preg_replace("/'/",'',$strings[$i]));

						} while (! preg_match('/\)+\)?/',$strings[$i+1]));
					}

					if (DEBUG_ENABLED)
						debug_log('Case SUP returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->sup_classes);
					break;

				case 'ABSTRACT':
					$this->type = 'abstract';

					if (DEBUG_ENABLED)
						debug_log('Case ABSTRACT returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->type);
					break;

				case 'STRUCTURAL':
					$this->type = 'structural';

					if (DEBUG_ENABLED)
						debug_log('Case STRUCTURAL returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->type);
					break;

				case 'AUXILIARY':
					$this->type = 'auxiliary';

					if (DEBUG_ENABLED)
						debug_log('Case AUXILIARY returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->type);
					break;

				case 'MUST':
					$attrs = array();

					$i = $this->parseList(++$i,$strings,$attrs);

					if (DEBUG_ENABLED)
						debug_log('parseList returned %d (%s)',8,0,__FILE__,__LINE__,__METHOD__,$i,$attrs);

					foreach ($attrs as $string) {
						$attr = new ObjectClass_ObjectClassAttribute($string,$this->name);

						if ($server->isForceMay($attr->getName())) {
							array_push($this->force_may,$attr);
							array_push($this->may_attrs,$attr);

						} else
							array_push($this->must_attrs,$attr);
					}

					if (DEBUG_ENABLED)
						debug_log('Case MUST returned (%s) (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->must_attrs,$this->force_may);
					break;

				case 'MAY':
					$attrs = array();

					$i = $this->parseList(++$i,$strings,$attrs);

					if (DEBUG_ENABLED)
						debug_log('parseList returned %d (%s)',8,0,__FILE__,__LINE__,__METHOD__,$i,$attrs);

					foreach ($attrs as $string) {
						$attr = new ObjectClass_ObjectClassAttribute($string,$this->name);
						array_push($this->may_attrs,$attr);
					}

					if (DEBUG_ENABLED)
						debug_log('Case MAY returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->may_attrs);
					break;

				default:
					if (preg_match('/[\d\.]+/i',$strings[$i]) && $i == 1) {
						$this->setOID($strings[$i]);

						if (DEBUG_ENABLED)
							debug_log('Case default returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->getOID());
					}
					break;
			}
		}

		$this->description = preg_replace("/^\'/",'',$this->description);
		$this->description = preg_replace("/\'$/",'',$this->description);

		if (DEBUG_ENABLED)
			debug_log('Returning () - NAME (%s), DESCRIPTION (%s), MUST (%s), MAY (%s), FORCE MAY (%s)',9,0,__FILE__,__LINE__,__METHOD__,
				$this->name,$this->description,$this->must_attrs,$this->may_attrs,$this->force_may);
	}

	/**
	 * Parse an LDAP schema list
	 */
	private function parseList($i,$strings,&$attrs) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		/*
		 * A list starts with a ( followed by a list of attributes separated by $ terminated by )
		 * The first token can therefore be a ( or a (NAME or a (NAME)
		 * The last token can therefore be a ) or NAME)
		 * The last token may be terminate by more than one bracket
		 */

		$string = $strings[$i];
		if (! preg_match('/^\(/',$string)) {
			# A bareword only - can be terminated by a ) if the last item
			if (preg_match('/\)+$/',$string))
				$string = preg_replace('/\)+$/','',$string);

			array_push($attrs,$string);

		} elseif (preg_match('/^\(.*\)$/',$string)) {
			$string = preg_replace('/^\(/','',$string);
			$string = preg_replace('/\)+$/','',$string);
			array_push($attrs,$string);

		} else {
			# Handle the opening cases first
			if ($string == '(') {
				$i++;

			} elseif (preg_match('/^\(./',$string)) {
				$string = preg_replace('/^\(/','',$string);
				array_push($attrs,$string);
				$i++;
			}

			# Token is either a name, a $ or a ')'
			# NAME can be terminated by one or more ')'
			while (! preg_match('/\)+$/',$strings[$i])) {
				$string = $strings[$i];
				if ($string == '$') {
					$i++;
					continue;
				}

				if (preg_match('/\)$/',$string))
					$string = preg_replace('/\)+$/','',$string);
				else
					$i++;

				array_push($attrs,$string);
			}
		}

		sort($attrs);

		if (DEBUG_ENABLED)
			debug_log('Returning (%d,[%s],[%s])',9,0,__FILE__,__LINE__,__METHOD__,$i,$strings,$attrs);

		return $i;
	}

	/**
	 * This will return all our parent ObjectClass Objects
	 */
	public function getParents() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ((count($this->sup_classes) == 1) && ($this->sup_classes[0] == 'top'))
			return array();

		$server = $_SESSION[APPCONFIG]->getServer($this->server_id);
		$return = array();

		foreach ($this->sup_classes as $object_class) {
			array_push($return,$object_class);

			$oc = $server->getSchemaObjectClass($object_class);

			if ($oc)
				$return = array_merge($return,$oc->getParents());
		}

		return $return;
	}

	/**
	 * Gets an array of AttributeType objects that entries of this ObjectClass must define.
	 * This differs from getMustAttrNames in that it returns an array of AttributeType objects
	 *
	 * @param array $parents An array of ObjectClass objects to use when traversing
	 *             the inheritance tree. This presents some what of a bootstrapping problem
	 *             as we must fetch all objectClasses to determine through inheritance which
	 *             attributes this objectClass requires.
	 * @return array The array of required AttributeType objects.
	 *
	 * @see getMustAttrNames
	 * @see getMayAttrs
	 * @see getMayAttrNames
	 */
	public function getMustAttrs($parents=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! $parents)
			return $this->must_attrs;

		$server = $_SESSION[APPCONFIG]->getServer($this->server_id);
		$attrs = $this->must_attrs;

		foreach ($this->getParents() as $sup_class) {
			$sc = $server->getSchemaObjectClass($sup_class);
			$attrs = array_merge($attrs,$sc->getMustAttrs());
		}

		masort($attrs,'name,source');

		# Remove any duplicates
		foreach ($attrs as $index => $attr)
			if (isset($allattr[$attr->getName()]))
				unset($attrs[$index]);
			else
				$allattr[$attr->getName()] = 1;

		return $attrs;
	}

	/**
	 * Gets an array of AttributeType objects that entries of this ObjectClass may define.
	 * This differs from getMayAttrNames in that it returns an array of AttributeType objects
	 *
	 * @param array $parents An array of ObjectClass objects to use when traversing
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
	public function getMayAttrs($parents=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! $parents)
			return $this->may_attrs;

		$server = $_SESSION[APPCONFIG]->getServer($this->server_id);
		$attrs = $this->may_attrs;

		foreach ($this->getParents() as $sup_class) {
			$sc = $server->getSchemaObjectClass($sup_class);
			$attrs = array_merge($attrs,$sc->getMayAttrs());
		}

		masort($attrs,'name,source');

		# Remove any duplicates
		foreach ($attrs as $index => $attr)
			if (isset($allattr[$attr->name]))
				unset($attrs[$index]);
			else
				$allattr[$attr->name] = 1;

		return $attrs;
	}

	public function getForceMayAttrs() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->force_may;
	}

	/**
	 * Gets an array of attribute names (strings) that entries of this ObjectClass must define.
	 * This differs from getMustAttrs in that it returns an array of strings rather than
	 * array of AttributeType objects
	 *
	 * @param array $parents An array of ObjectClass objects to use when traversing
	 *             the inheritance tree. This presents some what of a bootstrapping problem
	 *             as we must fetch all objectClasses to determine through inheritance which
	 *             attributes this objectClass provides.
	 * @return array The array of allowed attribute names (strings).
	 *
	 * @see getMustAttrs
	 * @see getMayAttrs
	 * @see getMayAttrNames
	 */
	public function getMustAttrNames($parents=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attr_names = array();

		foreach ($this->getMustAttrs($parents) as $attr)
			array_push($attr_names,$attr->getName());

		return $attr_names;
	}

	/**
	 * Gets an array of attribute names (strings) that entries of this ObjectClass must define.
	 * This differs from getMayAttrs in that it returns an array of strings rather than
	 * array of AttributeType objects
	 *
	 * @param array $parents An array of ObjectClass objects to use when traversing
	 *             the inheritance tree. This presents some what of a bootstrapping problem
	 *             as we must fetch all objectClasses to determine through inheritance which
	 *             attributes this objectClass provides.
	 * @return array The array of allowed attribute names (strings).
	 *
	 * @see getMustAttrs
	 * @see getMayAttrs
	 * @see getMustAttrNames
	 */
	public function getMayAttrNames($parents=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attr_names = array();

		foreach ($this->getMayAttrs($parents) as $attr)
			array_push($attr_names,$attr->getName());

		return $attr_names;
	}

	/**
	 * Adds an objectClass to the list of objectClasses that inherit
	 * from this objectClass.
	 *
	 * @param String $name The name of the objectClass to add
	 * @return boolean Returns true on success or false on failure (objectclass already existed for example)
	 */
	public function addChildObjectClass($name) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$name = trim($name);

		foreach ($this->children_objectclasses as $existing_objectclass)
			if (strcasecmp($name,$existing_objectclass) == 0)
				return false;

		array_push($this->children_objectclasses,$name);
	}

	/**
	 * Returns the array of objectClass names which inherit from this objectClass.
	 *
	 * @return Array Names of objectClasses which inherit from this objectClass.
	 */
	public function getChildObjectClasses() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->children_objectclasses;
	}

	/**
	 * Gets the objectClass names from which this objectClass inherits.
	 *
	 * @return array An array of objectClass names (strings)
	 */
	public function getSupClasses() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->sup_classes;
	}

	/**
	 * Return if this objectClass is related to $oclass
	 *
	 * @param array ObjectClasses that this attribute may be related to
	 */
	public function isRelated($oclass) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# If I am in the array, we'll just return false
		if (in_array_ignore_case($this->name,$oclass))
			return false;

		$server = $_SESSION[APPCONFIG]->getServer($this->server_id);

		foreach ($oclass as $object_class) {
			$oc = $server->getSchemaObjectClass($object_class);

			if ($oc->isStructural() && in_array_ignore_case($this->getName(),$oc->getParents()))
				return true;
		}

		return false;
	}

	/**
	 * Gets the type of this objectClass: STRUCTURAL, ABSTRACT, or AUXILIARY.
	 */
	public function getType() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->type);

		return $this->type;
	}

	/**
	 * Adds the specified array of attributes to this objectClass' list of
	 * MUST attributes. The resulting array of must attributes will contain
	 * unique members.
	 *
	 * @param array $attr An array of attribute names (strings) to add.
	 */
	private function addMustAttrs($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! is_array($attr) || ! count($attr))
			return;

		$this->must_attrs = array_values(array_unique(array_merge($this->must_attrs,$attr)));
	}

	/**
	 * Behaves identically to addMustAttrs, but it operates on the MAY
	 * attributes of this objectClass.
	 *
	 * @param array $attr An array of attribute names (strings) to add.
	 */
	private function addMayAttrs($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! is_array($attr) || ! count($attr))
			return;

		$this->may_attrs = array_values(array_unique(array_merge($this->may_attrs,$attr)));
	}

	/**
	 * Determine if an array is listed in the force_may attrs
	 */
	public function isForceMay($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		foreach ($this->force_may as $forcemay)
			if ($forcemay->getName() == $attr)
				return true;

		return false;
	}

	public function isStructural() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($this->type == 'structural')
			return true;
		else
			return false;
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
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
class ObjectClass_ObjectClassAttribute {
 	# This Attribute's name (needs to be public, as we sort on it with masort).
	public $name;
	# This Attribute's root (needs to be public, as we sort on it with masort).
	public $source;

	/**
	 * Creates a new ObjectClass_ObjectClassAttribute with specified name and source objectClass.
	 *
	 * @param string $name the name of the new attribute.
	 * @param string $source the name of the ObjectClass which specifies this attribute.
	 */
	public function __construct($name,$source) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->name = $name;
		$this->source = $source;
	}

	# Gets this attribute's name
	public function getName($lower=true) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->name);

		return $lower ? strtolower($this->name) : $this->name;
	}

	# Gets the name of the ObjectClass which originally specified this attribute.
	public function getSource() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->source);

		return $this->source;
	}
}

/**
 * Represents an LDAP AttributeType
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
class AttributeType extends SchemaItem {
	# The attribute from which this attribute inherits (if any)
	private $sup_attribute = null;
	# The equality rule used
	private $equality = null;
	# The ordering of the attributeType
	private $ordering = null;
	# Boolean: supports substring matching?
	private $sub_str = null;
	# The full syntax string, ie 1.2.3.4{16}
	private $syntax = null;
	private $syntax_oid = null;
	# boolean: is single valued only?
	private $is_single_value = false;
	# boolean: is collective?
	private $is_collective = false;
	# boolean: can use modify?
	private $is_no_user_modification = false;
	# The usage string set by the LDAP schema
	private $usage = null;
	# An array of alias attribute names, strings
	private $aliases = array();
	# The max number of characters this attribute can be
	private $max_length = null;
	# A string description of the syntax type (taken from the LDAPSyntaxes)
	private $type = null;
	# An array of objectClasses which use this attributeType (must be set by caller)
	private $used_in_object_classes = array();
	# A list of object class names that require this attribute type.
	private $required_by_object_classes = array();
	# This attribute has been forced a MAY attribute by the configuration.
	private $forced_as_may = false;

	/**
	 * Creates a new AttributeType object from a raw LDAP AttributeType string.
	 */
	public function __construct($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$strings = preg_split('/[\s,]+/',$attr,-1,PREG_SPLIT_DELIM_CAPTURE);

		for($i=0; $i<count($strings); $i++) {

			switch($strings[$i]) {
				case '(':
					break;

				case 'NAME':
					# Some schema's return a (' instead of a ( '
					if ($strings[$i+1] != '(' && ! preg_match('/^\(/',$strings[$i+1])) {
						do {
							$i++;
							if (strlen($this->name)==0)
								$this->name = $strings[$i];
							else
								$this->name .= ' '.$strings[$i];

						} while (! preg_match("/\'$/s",$strings[$i]));

						# This attribute has no aliases
						$this->aliases = array();

					} else {
						$i++;
						do {
							# In case we came here becaues of a ('
							if (preg_match('/^\(/',$strings[$i]))
								$strings[$i] = preg_replace('/^\(/','',$strings[$i]);
							else
								$i++;

							if (strlen($this->name) == 0)
 								$this->name = $strings[$i];
							else
								$this->name .= ' '.$strings[$i];

						} while (! preg_match("/\'$/s",$strings[$i]));

						# Add alias names for this attribute
						while ($strings[++$i] != ')') {
							$alias = $strings[$i];
							$alias = preg_replace("/^\'/",'',$alias);
							$alias = preg_replace("/\'$/",'',$alias);
							$this->addAlias($alias);
						}
					}

					if (DEBUG_ENABLED)
						debug_log('Case NAME returned (%s) (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->name,$this->aliases);
					break;

				case 'DESC':
					do {
						$i++;
						if (strlen($this->description)==0)
							$this->description=$this->description.$strings[$i];
						else
							$this->description=$this->description.' '.$strings[$i];
					} while (! preg_match("/\'$/s",$strings[$i]));

					if (DEBUG_ENABLED)
						debug_log('Case DESC returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->description);
					break;

				case 'OBSOLETE':
					$this->is_obsolete = TRUE;

					if (DEBUG_ENABLED)
						debug_log('Case OBSOLETE returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->is_obsolete);
					break;

				case 'SUP':
					$i++;
					$this->sup_attribute = $strings[$i];

					if (DEBUG_ENABLED)
						debug_log('Case SUP returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->sup_attribute);
					break;

				case 'EQUALITY':
					$i++;
					$this->equality = $strings[$i];

					if (DEBUG_ENABLED)
						debug_log('Case EQUALITY returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->equality);
					break;

				case 'ORDERING':
					$i++;
					$this->ordering = $strings[$i];

					if (DEBUG_ENABLED)
						debug_log('Case ORDERING returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->ordering);
					break;

				case 'SUBSTR':
					$i++;
					$this->sub_str = $strings[$i];

					if (DEBUG_ENABLED)
						debug_log('Case SUBSTR returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->sub_str);
					break;

				case 'SYNTAX':
					$i++;
					$this->syntax = $strings[$i];
					$this->syntax_oid = preg_replace('/{\d+}$/','',$this->syntax);

					# Does this SYNTAX string specify a max length (ie, 1.2.3.4{16})
					if (preg_match('/{(\d+)}$/',$this->syntax,$this->max_length))
						$this->max_length = $this->max_length[1];
					else
						$this->max_length = null;

					if ($i < count($strings) - 1 && $strings[$i+1] == '{') {
						do {
							$i++;
							$this->name .= ' '.$strings[$i];
						} while ($strings[$i] != '}');
					}

					if (DEBUG_ENABLED)
						debug_log('Case SYNTAX returned (%s) (%s) (%s)',8,0,__FILE__,__LINE__,__METHOD__,
							$this->syntax,$this->syntax_oid,$this->max_length);
					break;

				case 'SINGLE-VALUE':
					$this->is_single_value = TRUE;
					if (DEBUG_ENABLED)
						debug_log('Case SINGLE-VALUE returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->is_single_value);
					break;

				case 'COLLECTIVE':
					$this->is_collective = TRUE;

					if (DEBUG_ENABLED)
						debug_log('Case COLLECTIVE returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->is_collective);
					break;

				case 'NO-USER-MODIFICATION':
					$this->is_no_user_modification = TRUE;

					if (DEBUG_ENABLED)
						debug_log('Case NO-USER-MODIFICATION returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->is_no_user_modification);
					break;

				case 'USAGE':
					$i++;
					$this->usage = $strings[$i];

					if (DEBUG_ENABLED)
						debug_log('Case USAGE returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->usage);
					break;

				default:
					if (preg_match('/[\d\.]+/i',$strings[$i]) && $i == 1) {
						$this->setOID($strings[$i]);

						if (DEBUG_ENABLED)
							debug_log('Case default returned (%s)',8,0,__FILE__,__LINE__,__METHOD__,$this->getOID());
					}
			}
		}

		$this->name = preg_replace("/^\'/",'',$this->name);
		$this->name = preg_replace("/\'$/",'',$this->name);
		$this->description = preg_replace("/^\'/",'',$this->description);
		$this->description = preg_replace("/\'$/",'',$this->description);
		$this->syntax = preg_replace("/^\'/",'',$this->syntax);
		$this->syntax = preg_replace("/\'$/",'',$this->syntax);
		$this->syntax_oid = preg_replace("/^\'/",'',$this->syntax_oid);
		$this->syntax_oid = preg_replace("/\'$/",'',$this->syntax_oid);
		$this->sup_attribute = preg_replace("/^\'/",'',$this->sup_attribute);
		$this->sup_attribute = preg_replace("/\'$/",'',$this->sup_attribute);

		if (DEBUG_ENABLED)
			debug_log('Returning ()',9,0,__FILE__,__LINE__,__METHOD__);
	}

	/**
	 * Gets this attribute's usage string as defined by the LDAP server
	 *
	 * @return string
	 */
	public function getUsage() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->usage);

		return $this->usage;
	}

	/**
	 * Gets this attribute's parent attribute (if any). If this attribute does not
	 * inherit from another attribute, null is returned.
	 *
	 * @return string
	 */
	public function getSupAttribute() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->sup_attribute);

		return $this->sup_attribute;
	}

	/**
	 * Gets this attribute's equality string
	 *
	 * @return string
	 */
	public function getEquality() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->equality);

		return $this->equality;
	}

	/**
	 * Gets this attribute's ordering specification.
	 *
	 * @return string
	 */
	public function getOrdering() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->ordering);

		return $this->ordering;
	}

	/**
	 * Gets this attribute's substring matching specification
	 *
	 * @return string
	 */
	public function getSubstr() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->sub_str);

		return $this->sub_str;
	}

	/**
	 * Gets the names of attributes that are an alias for this attribute (if any).
	 *
	 * @return array An array of names of attributes which alias this attribute or
	 *          an empty array if no attribute aliases this object.
	 */
	public function getAliases() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->aliases);

		return $this->aliases;
	}

	/**
	 * Returns whether the specified attribute is an alias for this one (based on this attribute's alias list).
	 *
	 * @param string $attr_name The name of the attribute to check.
	 * @return boolean True if the specified attribute is an alias for this one, or false otherwise.
	 */
	public function isAliasFor($attr_name) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		foreach ($this->aliases as $alias_attr_name)
			if (strcasecmp($alias_attr_name,$attr_name) == 0)
				return true;

		return false;
	}

	/**
	 * Gets this attribute's raw syntax string (ie: "1.2.3.4{16}").
	 *
	 * @return string The raw syntax string
	 */
	public function getSyntaxString() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->syntax);

		return $this->syntax;
	}

	/**
	 * Gets this attribute's syntax OID. Differs from getSyntaxString() in that this
	 * function only returns the actual OID with any length specification removed.
	 * Ie, if the syntax string is "1.2.3.4{16}", this function only retruns
	 * "1.2.3.4".
	 *
	 * @return string The syntax OID string.
	 */
	public function getSyntaxOID() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->syntax_oid);

		return $this->syntax_oid;
	}

	/**
	 * Gets this attribute's the maximum length. If no maximum is defined by the LDAP server, null is returned.
	 *
	 * @return int The maximum length (in characters) of this attribute or null if no maximum is specified.
	 */
	public function getMaxLength() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->max_length);

		return $this->max_length;
	}

	/**
	 * Gets whether this attribute is single-valued. If this attribute only supports single values, true
	 * is returned. If this attribute supports multiple values, false is returned.
	 *
	 * @return boolean Returns true if this attribute is single-valued or false otherwise.
	 */
	public function getIsSingleValue() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->is_single_value);

		return $this->is_single_value;
	}

	/**
	 * Sets whether this attribute is single-valued.
	 *
	 * @param boolean $is
	 */
	public function setIsSingleValue($is) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->is_single_value = $is;
	}

	/**
	 * Gets whether this attribute is collective.
	 *
	 * @return boolean Returns true if this attribute is collective and false otherwise.
	 */
	public function getIsCollective() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->is_collective);

		return $this->is_collective;
	}

	/**
	 * Gets whether this attribute is not modifiable by users.
	 *
	 * @return boolean Returns true if this attribute is not modifiable by users.
	 */
	public function getIsNoUserModification() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->is_no_user_modification);

		return $this->is_no_user_modification;
	}

	/**
	 * Gets this attribute's type
	 *
	 * @return string The attribute's type.
	 */
	public function getType() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->type);

		return $this->type;
	}

	/**
	 * Removes an attribute name from this attribute's alias array.
	 *
	 * @param string $remove_alias_name The name of the attribute to remove.
	 * @return boolean true on success or false on failure (ie, if the specified
	 *           attribute name is not found in this attribute's list of aliases)
	 */
	public function removeAlias($remove_alias_name) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		foreach ($this->aliases as $i => $alias_name) {

			if (strcasecmp($alias_name,$remove_alias_name) == 0) {
				unset($this->aliases[$i]);

				$this->aliases = array_values($this->aliases);
				return true;
			}
		}
		return false;
	}

	/**
	 * Adds an attribute name to the alias array.
	 *
	 * @param string $alias The name of a new attribute to add to this attribute's list of aliases.
	 */
	public function addAlias($alias) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		array_push($this->aliases,$alias);
	}

	/**
	 * Sets this attriute's name.
	 *
	 * @param string $name The new name to give this attribute.
	 */
	public function setName($name) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->name = $name;
	}

	/**
	 * Sets this attriute's SUP attribute (ie, the attribute from which this attribute inherits).
	 *
	 * @param string $attr The name of the new parent (SUP) attribute
	 */
	public function setSupAttribute($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->sup_attribute = $attr;
	}

	/**
	 * Sets this attribute's list of aliases.
	 *
	 * @param array $aliases The array of alias names (strings)
	 */
	public function setAliases($aliases) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->aliases = $aliases;
	}

	/**
	 * Sets this attribute's type.
	 *
	 * @param string $type The new type.
	 */
	public function setType($type) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->type = $type;
	}

	/**
	 * Adds an objectClass name to this attribute's list of "used in" objectClasses,
	 * that is the list of objectClasses which provide this attribute.
	 *
	 * @param string $name The name of the objectClass to add.
	 */
	public function addUsedInObjectClass($name) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		foreach ($this->used_in_object_classes as $used_in_object_class) {
			if (DEBUG_ENABLED)
				debug_log('Checking (%s) with (%s)',8,0,__FILE__,__LINE__,__METHOD__,$used_in_object_class,$name);

			if (strcasecmp($used_in_object_class,$name) == 0)
				return false;
		}

		array_push($this->used_in_object_classes,$name);
	}

	/**
	 * Gets the list of "used in" objectClasses, that is the list of objectClasses
	 * which provide this attribute.
	 *
	 * @return array An array of names of objectclasses (strings) which provide this attribute
	 */
	public function getUsedInObjectClasses() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->used_in_object_classes);

		return $this->used_in_object_classes;
	}

	/**
	 * Adds an objectClass name to this attribute's list of "required by" objectClasses,
	 * that is the list of objectClasses which must have this attribute.
	 *
	 * @param string $name The name of the objectClass to add.
	 */
	public function addRequiredByObjectClass($name) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		foreach ($this->required_by_object_classes as $required_by_object_class)
			if (strcasecmp($required_by_object_class,$name) == 0)
				return false;

		array_push($this->required_by_object_classes,$name);
	}

	/**
	 * Gets the list of "required by" objectClasses, that is the list of objectClasses
	 * which provide must have attribute.
	 *
	 * @return array An array of names of objectclasses (strings) which provide this attribute
	 */
	public function getRequiredByObjectClasses() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->required_by_object_classes);

		return $this->required_by_object_classes;
	}

	/**
	 * This function will mark this attribute as a forced MAY attribute
	 */
	public function setForceMay() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->forced_as_may = true;
	}

	public function isForceMay() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->forced_as_may);

		return $this->forced_as_may;
	}
}

/**
 * Represents an LDAP Syntax
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
class Syntax extends SchemaItem {
	/**
	 * Creates a new Syntax object from a raw LDAP syntax string.
	 */
	public function __construct($class) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$strings = preg_split('/[\s,]+/',$class,-1,PREG_SPLIT_DELIM_CAPTURE);

		for($i=0; $i<count($strings); $i++) {
			switch($strings[$i]) {
				case '(':
					break;

				case 'DESC':
					do {
						$i++;
						if (strlen($this->description) == 0)
							$this->description=$this->description.$strings[$i];
						else
							$this->description=$this->description.' '.$strings[$i];
					} while (! preg_match("/\'$/s",$strings[$i]));
					break;

				default:
					if (preg_match('/[\d\.]+/i',$strings[$i]) && $i == 1)
						$this->setOID($strings[$i]);
			}
		}

		$this->description = preg_replace("/^\'/",'',$this->description);
		$this->description = preg_replace("/\'$/",'',$this->description);
	}
}

/**
 * Represents an LDAP MatchingRule
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
class MatchingRule extends SchemaItem {
	# This rule's syntax OID
	private $syntax = null;
	# An array of attribute names who use this MatchingRule
	private $used_by_attrs = array();

	/**
	 * Creates a new MatchingRule object from a raw LDAP MatchingRule string.
	 */
	function __construct($strings) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$strings = preg_split('/[\s,]+/',$strings,-1,PREG_SPLIT_DELIM_CAPTURE);

		for ($i=0; $i<count($strings); $i++) {
			switch($strings[$i]) {
				case '(':
					break;

				case 'NAME':
					if ($strings[$i+1] != '(') {
						do {
							$i++;
							if (strlen($this->name) == 0)
								$this->name = $strings[$i];
						else
								$this->name .= ' '.$strings[$i];
						} while (! preg_match("/\'$/s",$strings[$i]));

					} else {
						$i++;
						do {
							$i++;
							if (strlen($this->name) == 0)
								$this->name = $strings[$i];
							else
								$this->name .= ' '.$strings[$i];
						} while (! preg_match("/\'$/s",$strings[$i]));

						do {
							$i++;
						} while (! preg_match('/\)+\)?/',$strings[$i]));
					}

					$this->name = preg_replace("/^\'/",'',$this->name);
					$this->name = preg_replace("/\'$/",'',$this->name);
					break;

				case 'DESC':
					do {
						$i++;
						if (strlen($this->description)==0)
							$this->description=$this->description.$strings[$i];
						else
							$this->description=$this->description.' '.$strings[$i];
					} while (! preg_match("/\'$/s",$strings[$i]));
					break;

				case 'OBSOLETE':
					$this->is_obsolete = TRUE;
					break;

				case 'SYNTAX':
					$this->syntax = $strings[++$i];
					break;

				default:
					if (preg_match('/[\d\.]+/i',$strings[$i]) && $i == 1)
						$this->setOID($strings[$i]);
			}
		}
		$this->description = preg_replace("/^\'/",'',$this->description);
		$this->description = preg_replace("/\'$/",'',$this->description);
	}

	/**
	 * Sets the list of used_by_attrs to the array specified by $attrs;
	 *
	 * @param array $attrs The array of attribute names (strings) which use this MatchingRule
	 */
	public function setUsedByAttrs($attrs) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->used_by_attrs = $attrs;
	}

	/**
	 * Adds an attribute name to the list of attributes who use this MatchingRule
	 *
	 * @return true if the attribute was added and false otherwise (already in the list)
	 */
	public function addUsedByAttr($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		foreach ($this->used_by_attrs as $attr_name)
			if (strcasecmp($attr_name,$attr) == 0)
				return false;

		array_push($this->used_by_attrs,$attr);

		return true;
	}

	/**
	 * Gets an array of attribute names (strings) which use this MatchingRule
	 *
	 * @return array The array of attribute names (strings).
	 */
	public function getUsedByAttrs() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->used_by_attrs);

		return $this->used_by_attrs;
	}
}

/**
 * Represents an LDAP schema matchingRuleUse entry
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
class MatchingRuleUse extends SchemaItem {
	# An array of attribute names who use this MatchingRule
	private $used_by_attrs = array();

	function __construct($strings) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$strings = preg_split('/[\s,]+/',$strings,-1,PREG_SPLIT_DELIM_CAPTURE);

		for($i=0; $i<count($strings); $i++) {
			switch($strings[$i]) {
				case '(':
					break;

				case 'NAME':
					if ($strings[$i+1] != '(') {
						do {
							$i++;
							if (! isset($this->name) || strlen($this->name) == 0)
								$this->name = $strings[$i];
							else
								$this->name .= ' '.$strings[$i];

						} while (! preg_match("/\'$/s",$strings[$i]));

					} else {
						$i++;
						do {
							$i++;
							if (strlen($this->name) == 0)
								$this->name = $strings[$i];
							else
								$this->name .= ' '.$strings[$i];
						} while (! preg_match("/\'$/s",$strings[$i]));

						do {
							$i++;
						} while (! preg_match('/\)+\)?/',$strings[$i]));
					}

					$this->name = preg_replace("/^\'/",'',$this->name);
					$this->name = preg_replace("/\'$/",'',$this->name);
					break;

				case 'APPLIES':
					if ($strings[$i+1] != '(') {
						# Has a single attribute name
						$i++;
						$this->used_by_attrs = array($strings[$i]);

					} else {
						# Has multiple attribute names
						$i++;
						while ($strings[$i] != ')') {
							$i++;
							$new_attr = $strings[$i];
							$new_attr = preg_replace("/^\'/",'',$new_attr);
							$new_attr = preg_replace("/\'$/",'',$new_attr);
							array_push($this->used_by_attrs,$new_attr);
							$i++;
						}
					}
					break;

				default:
					if (preg_match('/[\d\.]+/i',$strings[$i]) && $i == 1)
						$this->setOID($strings[$i]);
			}
		}

		sort($this->used_by_attrs);
	}

	/**
	 * Gets an array of attribute names (strings) which use this MatchingRuleUse object.
	 *
	 * @return array The array of attribute names (strings).
	 */
	public function getUsedByAttrs() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',9,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->used_by_attrs);

		return $this->used_by_attrs;
	}
}
?>
