<?php
/**
 * Configuration processing and defaults.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 * @todo Add validation of set variables to enforce limits or particular values.
 */

/** The minimum version of PHP required to run phpLDAPadmin. */
define('REQUIRED_PHP_VERSION','5.0.0');

/**
 * The config class contains all our configuration settings for a session.
 *
 * An instance of this class should be stored in $_SESSION to maintain state, and to avoid
 * rebuilding/rereading it at the state of each page output.
 *
 * @package phpLDAPadmin
 * @subpackage Tree
 */
class Config {
	public $custom;
	protected $default;
	protected $servers = array();

	public $hooks = array();

	public function __construct() {
		$this->custom = new stdClass;
		$this->default = new stdClass;

		/*
		 * What to do after entry creation :
		 * 2 : display the creation form again
		 * 1 : display the new created entry
		 * 0 : display the choice between 1 and 2
		 */
		$this->default->appearance['action_after_creation'] = array(
			'desc'=>'Display the new created entry',
			'default'=>1);

		## Appearance Attributes
		/** Anonymous implies read only
		 * Set to true if you want LDAP data to be displayed read-only (without input fields)
		 * when a user logs in to a server anonymously
		 */
		$this->default->appearance['anonymous_bind_implies_read_only'] = array(
			'desc'=>'Display as read only if user logs in with anonymous bind',
			'default'=>true);

		$this->default->appearance['attr_display_order'] = array(
			'desc'=>'Custom order to display attributes',
			'default'=>array());

		/*
		* @todo Compression is not working,
		* purge_cache shows blank,
		* tree refresh shows blank - and if view_tree_node is modified to compress output, then previously opened items show up as compressed data.
		*/
		$this->default->appearance['compress'] = array(
			'desc'=>'Compress Output',
			'untested'=>true,
			'default'=>false);

		$this->default->appearance['control_icons'] = array(
			'desc'=>'Show the control as icons or text',
			'default'=>false);

		$this->default->appearance['custom_templates_only'] = array(
			'desc'=>'Only display the custom templates.',
			'default'=>false);

		$this->default->appearance['date'] = array(
			'desc'=>'Date format whenever dates are shown',
			'default'=>'%A %e %B %Y');

		$this->default->appearance['date_attrs'] = array(
			'desc'=>'Array of attributes that should show a jscalendar',
			'default'=>array('shadowExpire'=>'%es','shadowLastChange'=>'%es'));

		$this->default->appearance['date_attrs_showtime'] = array(
			'desc'=>'Array of attributes that should show a the time when showing the jscalendar',
			'default'=>array(''));

		$this->default->appearance['disable_default_template'] = array(
			'desc'=>'Disabled the Default Template',
			'default'=>false);

		$this->default->appearance['disable_default_leaf'] = array(
			'desc'=>'Disabled creating leaf entries in the Default Template',
			'default'=>false);

		$this->default->appearance['friendly_attrs'] = array(
			'desc'=>'Friendly names for attributes',
			'default'=>array());

		$this->default->appearance['hide_attrs'] = array(
			'desc'=>'Hide attributes from display',
			'default'=>array());

		$this->default->appearance['hide_attrs_exempt'] = array(
			'desc'=>'Group DN, where membership will exempt the users from hide_attrs',
			'default'=>null);

		$this->default->appearance['hide_debug_info'] = array(
			'desc'=>'Hide the features that may provide sensitive debugging information to the browser',
			'default'=>true);

		$this->default->appearance['hide_template_regexp'] = array(
			'desc'=>'Templates that are disabled by their regex are not shown',
			'default'=>false);

		$this->default->appearance['hide_template_warning'] = array(
			'desc'=>'Hide template errors from being displayed',
			'default'=>false);

		/** Language
		 * The language setting. If you set this to 'auto', phpLDAPadmin will
		 * attempt to determine your language automatically. Otherwise, set
		 * this to your applicable language in xx_XX format.
		 * Localization is not complete yet, but most strings have been translated.
		 * Please help by writing language files.
		 */
		$this->default->appearance['language'] = array(
			'desc'=>'Language',
			'default'=>'auto');

		$this->default->appearance['max_add_attrs'] = array(
			'desc'=>'Maximum number of attrs to show in the add attr form',
			'default'=>10);

		/**
		 * If you want certain attributes to be editable as multi-line, include them in this list
		 * A multi-line textarea will be drawn instead of a single-line text field
		 */
		$this->default->appearance['multi_line_attributes'] = array(
			'desc'=>'Attributes to show as multiline attributes',
			'default'=>array('postalAddress','homePostalAddress','personalSignature','description','mailReplyText'));

		/**
		 * A list of syntax OIDs which support multi-line attribute values:
		 */
		$this->default->appearance['multi_line_syntax_oids'] = array(
			'desc'=>'Attributes to show as multiline attributes',
			'default'=>array(
				// octet string syntax OID:
				'1.3.6.1.4.1.1466.115.121.1.40',
				// postal address syntax OID:
				'1.3.6.1.4.1.1466.115.121.1.41'));

		/** Obfuscate Password
		 * If true, display all password hash values as "******". Note that clear-text
		 * passwords will always be displayed as "******", regardless of this setting.
		 */
		$this->default->appearance['obfuscate_password_display'] = array(
			'desc'=>'Obfuscate the display of passwords',
			'default'=>true);

		$this->default->appearance['page_title'] = array(
			'desc'=>'Change the page title to this text',
			'default'=>'');

		$this->default->appearance['rdn_all_attrs'] = array(
			'desc'=>'Whether to show all attributes in the RDN chooser, or just the required ones',
			'default'=>true);

		$this->default->appearance['readonly_attrs'] = array(
			'desc'=>'Mark these attributes as readonly',
			'default'=>array());

		$this->default->appearance['readonly_attrs_exempt'] = array(
			'desc'=>'Group DN, where membership will exempt the users from readonly attrs',
			'default'=>null);

		$this->default->appearance['remoteurls'] = array(
			'desc'=>'Whether to include renders for remote URLs',
			'default'=>true);

		$this->default->appearance['show_clear_password'] = array(
			'desc'=>'Whether to show clear passwords if we dont obfuscate them',
			'default'=>false);

		$this->default->appearance['show_hints'] = array(
			'desc'=>'Show helpful hints',
			'default'=>true);

		$this->default->appearance['show_top_create'] = array(
			'desc'=>'Show a additional create link on the top of the list if there are more than 10 entries',
			'default'=>true);

		$this->default->appearance['show_schema_link'] = array(
			'desc'=>'Show the schema link for each attribute',
			'default'=>true);

		$this->default->appearance['show_attribute_notes'] = array(
			'desc'=>'Show notes for each attribute',
			'default'=>true);

		$this->default->appearance['stylesheet'] = array(
			'desc'=>'Style sheet to use',
			'default'=>'style.css');

		$this->default->appearance['theme'] = array(
			'desc'=>'Which theme to use',
			'default'=>'default');

		$this->default->appearance['timezone'] = array(
			'desc'=>'Define our timezone, if not defined in php.ini',
			'default'=>null);

		$this->default->appearance['tree'] = array(
			'desc'=>'Class name which inherits from Tree class and implements the draw() method',
			'default'=>'AJAXTree');

		/** Tree display
		 * An array of format strings used to display enties in the 
		 * tree viewer (left-hand side). The first format string that 
		 * is completely defined (i.e., does not reference attributes 
		 * that are not defined the object). If there is no format 
		 * string that is completely defined, the last one is used. 
		 * 
		 * You can use special tokens to draw the entries as you wish. 
		 * You can even mix in HTML to format the string. 
		 * Here are all the tokens you can use:
		 *	%rdn - draw the RDN of the entry (ie, "cn=Dave")
		 *	%dn - draw the DN of the entry (ie, "cn=Dave,ou=People,dc=example,dc=com"
		 *	%rdnValue - draw the value of the RDN (ie, instead of "cn=Dave", just draw "Dave")
		 *	%[attrname]- draw the value (or values) of the specified attribute.
		 *	 example: %gidNumber
		 *
		 * Any multivalued attributes will be displayed as a comma separated list.
		 *
		 * Examples:
		 *
		 * To draw the gidNumber and uidNumber to the right of the RDN in a small, gray font:
		 *	'%rdn <small style="color:gray">( %gidNumber / %uidNumber )</small>'
		 * To draw the full DN of each entry:
		 *	'%dn'
		 * To draw the objectClasses to the right in parenthesis:
		 *	'%rdn <small style="color: gray">( %objectClass )</small>'
		 * To draw the user-friendly RDN value (ie, instead of "cn=Dave", just draw "Dave"):
		 *	'%rdnValue'
		 */
		$this->default->appearance['tree_display_format'] = array(
			'desc'=>'LDAP attribute to show in the tree',
			'default'=>array('%rdn'));

		$this->default->appearance['tree_height'] = array(
			'desc'=>'Pixel height of the tree browser',
			'default'=>null);

		$this->default->appearance['tree_width'] = array(
			'desc'=>'Pixel width of the tree browser',
			'default'=>null);

		/** Tree display filter
		 * LDAP filter used to search entries for the tree viewer (left-hand side)
		 */
		$this->default->appearance['tree_filter'] = array(
			'desc'=>'LDAP search filter for the tree entries',
			'default'=>'(objectClass=*)');

		# PLA will not display the header and footer parts in minimal mode.
		$this->default->appearance['minimalMode'] = array(
			'desc'=>'Minimal mode hides header and footer parts',
			'default'=>false);

		## Caching
		$this->default->cache['schema'] = array(
			'desc'=>'Cache Schema Activity',
			'default'=>true);

		$this->default->cache['query'] = array(
			'desc'=>'Cache Query Configuration',
			'default'=>true);

		$this->default->cache['query_time'] = array(
			'desc'=>'Cache the query configuration for atleast this amount of time in seconds',
			'default'=>5);

		$this->default->cache['template'] = array(
			'desc'=>'Cache Template Configuration',
			'default'=>true);

		$this->default->cache['template_time'] = array(
			'desc'=>'Cache the template configuration for atleast this amount of time in seconds',
			'default'=>60);

		$this->default->cache['tree'] = array(
			'desc'=>'Cache Browser Tree',
			'default'=>true);

		/** Confirm actions
		 */
		$this->default->confirm['copy'] = array(
			'desc'=>'Confirm copy actions',
			'default'=>true);

		$this->default->confirm['create'] = array(
			'desc'=>'Confirm creation actions',
			'default'=>true);

		$this->default->confirm['update'] = array(
			'desc'=>'Confirm update actions',
			'default'=>true);

		/** Commands
		 * Define command availability ; if the value of a command is true,
		 * the command will be available.
		 */
		$this->default->commands['cmd'] = array(
			'desc'=>'Define command availability',
			'default'=> array(
				'entry_internal_attributes_show' => true,
				'entry_refresh' => true,
				'oslinks' => true,
				'switch_template' => true
			));

		$this->default->commands['script'] = array(
			'desc'=>'Define scripts availability',
			'default'=> array(
				'add_attr_form' => true,
				'add_oclass_form' => true,
				'add_value_form' => true,
				'collapse' => true,
				'compare' => true,
				'compare_form' => true,
				'copy' => true,
				'copy_form' => true,
				'create' => true,
				'create_confirm' => true,
				'delete' => true,
				'delete_attr' => true,
				'delete_form' => true,
				'draw_tree_node' => true,
				'expand' => true,
				'export' => true,
				'export_form' => true,
				'import' => true,
				'import_form' => true,
				'login' => true,
				'logout' => true,
				'login_form' => true,
				'mass_delete' => true,
				'mass_edit' => true,
				'mass_update' => true,
				'modify_member_form' => true,
				'monitor' => true,
				'purge_cache' => true,
				'query_engine' => true,
				'rename' => true,
				'rename_form' => true,
				'rdelete' => true,
				'refresh' => true,
				'schema' => true,
				'server_info' => true,
				'show_cache' => true,
				'template_engine' => true,
				'update_confirm' => true,
				'update' => true
			));

		/** Aliases and Referrrals
		 * Similar to ldapsearch's -a option, the following options allow you to configure
		 * how phpLDAPadmin will treat aliases and referrals in the LDAP tree.
		 * For the following four settings, avaialable options include:
		 *
		 * LDAP_DEREF_NEVER	- aliases are never dereferenced (eg, the contents of
		 *			the alias itself are shown and not the referenced entry).
		 * LDAP_DEREF_SEARCHING	- aliases should be dereferenced during the search but
		 *			not when locating the base object of the search.
		 * LDAP_DEREF_FINDING	- aliases should be dereferenced when locating the base
		 *			object but not during the search.
		 * LDAP_DEREF_ALWAYS	- aliases should be dereferenced always (eg, the contents
		 *			of the referenced entry is shown and not the aliasing entry)
		 * We superceed these definitions with @ to suppress the error if php-ldap is
		 * not installed.
		 */
		@$this->default->deref['export'] = array(
			'desc'=>'',
			'default'=>LDAP_DEREF_NEVER);

		@$this->default->deref['search'] = array(
			'desc'=>'',
			'default'=>LDAP_DEREF_ALWAYS);

		@$this->default->deref['tree'] = array(
			'desc'=>'',
			'default'=>LDAP_DEREF_NEVER);

		@$this->default->deref['view'] = array(
			'desc'=>'',
			'default'=>LDAP_DEREF_NEVER);

		## Debug Attributes
		$this->default->debug['level'] = array(
			'desc'=>'Debug level verbosity',
			'default'=>0);

		$this->default->debug['syslog'] = array(
			'desc'=>'Whether to send debug messages to syslog',
			'default'=>false);

		$this->default->debug['file'] = array(
			'desc'=>'Name of file to send debug output to',
			'default'=>null);

		$this->default->debug['addr'] = array(
			'desc'=>'IP address of client to provide debugging info.',
			'default'=>null);

		$this->default->debug['append'] = array(
			'desc'=>'Whether to append to the debug file, or create it fresh each time',
			'default'=>true);

		## Temp Directories
		/** JPEG TMPDir
		 * This directory must be readable and writable by your web server
		 */
		$this->default->jpeg['tmpdir'] = array(
			'desc'=>'Temporary directory for jpegPhoto data',
			'default'=>'/tmp');

		## Mass update commands
		$this->default->mass['enabled'] = array(
			'desc'=>'Are mass update commands enabled',
			'default'=>true);

		## Modify members feature
		/**
		 * Search filter setting for new members. This is used to search possible members that can be added
		 * to the group. See modify_member_form.php
		 */
		$this->default->modify_member['filter'] = array(
			'desc'=>'Search filter for member searches',
			'default'=>'(objectclass=Person)');

		/**
		 * Group attributes. When these attributes are seen in template_engine.php, add "modify group members"
		 * link to the attribute
		 * See template_engine.php
		 */
		$this->default->modify_member['groupattr'] = array(
			'desc'=>'Group member attributes',
			'default'=>array('member','uniqueMember','memberUid'));

		/**
		 * Attribute that is added to the group member attribute. For groupOfNames or groupOfUniqueNames this is dn,
		 * for posixGroup it's uid. See modify_member_form.php
		 */
		$this->default->modify_member['attr'] = array(
			'desc'=>'Default attribute that is added to the group member attribute',
			'default'=>'dn');

		/**
		 * Attribute that is added to the group member attribute.
		 * For posixGroup it's uid. See modify_member_form.php
		 */
		$this->default->modify_member['posixattr'] = array(
			'desc'=>'Contents of the group member attribute',
			'default'=>'uid');

		/**
		 * Search filter setting for new members to group. This is used to search possible members that can be added
		 * to the posixGroup. See modify_member_form.php
		 */
		$this->default->modify_member['posixfilter'] = array(
			'desc'=>'Search filter for posixmember searches',
			'default'=>'(uid=*)');

		/**
		 * posixGroup attribute. When this attribute are seen in modify_member_form.php, only posixGroup members are shown
		 * See modify_member_form.php
		 */
		$this->default->modify_member['posixgroupattr'] = array(
			'desc'=>'posixGroup member attribute',
			'default'=>'memberUid');

		## Session Attributes
		/** Cookie Encryption
		 * phpLDAPadmin can encrypt the content of sensitive cookies if you set this to a big random string.
		 */
		$this->default->session['blowfish'] = array(
			'desc'=>'Blowfish key to encrypt cookie details',
			'default'=>null);

		/** Cookie Time
		 * If you used auth_type 'form' in the servers list, you can adjust how long the cookie will last
		 * (default is 0 seconds, which expires when you close the browser)
		 */
		$this->default->session['cookie_time'] = array(
			'desc'=>'Time in seconds for the life of cookies',
			'default'=>0);

		$this->default->session['http_realm'] = array(
			'desc'=>'HTTP Authentication Realm',
			'default'=>sprintf('%s %s',app_name(),_('login')));

		$this->default->session['memorylimit'] = array(
			'desc'=>'Set the PHP memorylimit warning threshold.',
			'default'=>24);

		$this->default->session['timelimit'] = array(
			'desc'=>'Set the PHP timelimit.',
			'default'=>30);

		/**
		 * Session Menu
		 */
		$this->default->menu['session'] = array(
			'desc'=>'Menu items when logged in.',
			'default'=>array(
				'schema'=>true,
				'search'=>true,
				'refresh'=>true,
				'server_info'=>true,
				'monitor'=>true,
				'import'=>true,
				'export'=>true
			));

		## Password Generation
		$this->default->password['length'] = array(
			'desc'=>'Length of autogenerated password',
			'default'=>8);

		$this->default->password['numbers'] = array(
			'desc'=>'Number of numbers required in the password',
			'default'=>2);

		$this->default->password['lowercase'] = array(
			'desc'=>'Number of lowercase letters required in the password',
			'default'=>2);

		$this->default->password['uppercase'] = array(
			'desc'=>'Number of uppercase letters required in the password',
			'default'=>2);

		$this->default->password['punctuation'] = array(
			'desc'=>'Number of punctuation letters required in the password',
			'default'=>2);

		$this->default->password['use_similar'] = array(
			'desc'=>'Whether to use similiar characters',
			'default'=>true);

		$this->default->password['no_random_crypt_salt'] = array(
			'desc'=>'Disable random salt for crypt()',
			'default'=>false);

		/** Search display
		 * By default, when searching you may display a list or a table of results.
		 * Set this to 'table' to see table formatted results.
		 * Set this to 'list' to see "Google" style formatted search results.
		 */
		$this->default->search['display'] = array(
			'desc'=>'Display a list or table of search results',
			'default'=>'list');

		$this->default->search['size_limit'] = array(
			'desc'=>'Limit the size of searchs on the search page',
			'default'=>50);

		/**
		 * The list of attributes to display in each search result entry.
		 * Note that you can add * to the list to display all attributes
		 */
		$this->default->search['result_attributes'] = array(
			'desc'=>'List of attributes to display in each search result entry',
			'default'=>array('cn','sn','uid','postalAddress','telephoneNumber'));

		$this->default->search['time_limit'] = array(
			'desc'=>'Maximum time to allow unlimited size_limit searches to the ldap server',
			'default'=>120);
	}

	/**
	 * Access the configuration, taking into account the defaults and the customisations
	 */
	private function getConfigArray($usecache=true) {
		static $CACHE = array();

		if ($usecache && count($CACHE))
			return $CACHE;

		foreach ($this->default as $key => $vals)
			$CACHE[$key] = $vals;

		foreach ($this->custom as $key => $vals)
			foreach ($vals as $index => $val)
				$CACHE[$key][$index]['value'] = $val;

		return $CACHE;
	}

	/**
	 * Get a configuration value.
	 */
	public function getValue($key,$index,$fatal=true) {
		$config = $this->getConfigArray();

		if (! isset($config[$key]))
			if ($fatal)
				error(sprintf('A call was made in [%s] to getValue requesting [%s] that isnt predefined.',
					basename($_SERVER['PHP_SELF']),$key),'error',null,true);
			else
				return '';

		if (! isset($config[$key][$index]))
			if ($fatal)
				error(sprintf('Requesting an index [%s] in key [%s] that isnt predefined.',$index,$key),'error',null,true);
			else
				return '';

		return isset($config[$key][$index]['value']) ? $config[$key][$index]['value'] : $config[$key][$index]['default'];
	}

	/**
	 * Return the untested config items
	 */
	public function untested() {
		$result = array();

		foreach ($this->default as $option => $details)
			foreach ($details as $param => $values)
				if (isset($values['untested']) && $values['untested'])
					array_push($result,sprintf('%s.%s',$option,$param));

		return $result;
	}

	/**
	 * Function to check and warn about any unusual defined variables.
	 */
	public function CheckCustom() {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',3,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (isset($this->custom)) {
			foreach ($this->custom as $masterkey => $masterdetails) {

				if (isset($this->default->$masterkey)) {

					if (! is_array($masterdetails))
						error(sprintf('Error in configuration file, [%s] should be an ARRAY.',$masterdetails),'error',null,true);

					foreach ($masterdetails as $key => $value) {
						# Test that the key is correct.
						if (! in_array($key,array_keys($this->default->$masterkey)))
							error(sprintf('Error in configuration file, [%s] has not been defined as a configurable variable.',$key),'error',null,true);

						# Test if its should be an array or not.
						if (is_array($this->default->{$masterkey}[$key]['default']) && ! is_array($value))
							error(sprintf('Error in configuration file, %s[\'%s\'] SHOULD be an array of values.',$masterkey,$key),'error',null,true);

						if (! is_array($this->default->{$masterkey}[$key]['default']) && is_array($value))
							error(sprintf('Error in configuration file, %s[\'%s\'] should NOT be an array of values.',$masterkey,$key),'error',null,true);
					}

				} else {
					error(sprintf('Error in configuration file, [%s] has not been defined as a MASTER configurable variable.',$masterkey),'error',null,true);
				}
			}
		}
	}

	/**
	 * Get a list of available commands.
	 */
	public function getCommandList() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',3,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$config = $this->getConfigArray(false);

		masort($config['command'],'summary');

		if (isset($config['command']) && is_array($config['command']))
			return $config['command'];
		else
			return array();
	}

	/**
	 * Simple ACL to see if commands can be run
	 */
	public function isCommandAvailable($index='cmd') {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',3,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$a = func_get_args();
		array_shift($a);
		$a = $a[0];

		# Command availability list
		$cmd = $this->getValue('commands',$index);

		if (! is_string($a) || ! isset($cmd[$a]))
			return false;
		else
			return $cmd[$a];
	}

	public function configDefinition($key,$index,$config) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',3,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! is_array($config) || ! array_key_exists('desc',$config) || ! array_key_exists('default',$config))
			return;

		if (isset($this->default->$key))
			$definition = $this->default->$key;

		$definition[$index] = $config;
		$this->default->$key = $definition;
	}

	/**
	 * Return the friendly attributes names
	 */
	private function getFriendlyAttrs() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',3,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return array_change_key_case($this->getValue('appearance','friendly_attrs'));
	}

	/**
	 * This function will return the friendly name of an attribute, if it exists.
	 * If the friendly name doesnt exist, the attribute name will be returned.
 	 *
	 * @param attribute
	 * @return string friendly name|attribute
	 */
	public function getFriendlyName($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',3,0,__FILE__,__LINE__,__METHOD__,$fargs);

		static $friendly_attrs;

		if (! $friendly_attrs)
			$friendly_attrs = $this->getFriendlyAttrs();

		if (! is_object($attr))
			if (isset($friendly_attrs[$attr]))
				return $friendly_attrs[$attr];
			else
				return $attr;

		if (isset($friendly_attrs[$attr->getName()]))
			return $friendly_attrs[$attr->getName()];
		else
			return $attr->getName(false);
	}

	/**
	 * This function will return true if a friendly name exists for an attribute.
	 * If the friendly name doesnt exist, it will return false.
 	 *
	 * @param attribute
	 * @return boolean true|false
	 */
	public function haveFriendlyName($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',3,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $attr->getName(false) != $this->getFriendlyName($attr);
	}

	/**
	 * This function will return the <ancronym> html for a friendly name attribute.
 	 *
	 * @param attribute
	 * @return string html for the friendly name.
	 */
	public function getFriendlyHTML($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',3,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($this->haveFriendlyName($attr))
			return sprintf('<acronym title="%s %s">%s</acronym>',
				_('Alias for'),$attr->getName(false),$this->getFriendlyName($attr));
		else
			return $attr->getName(false);
	}

	public function setServers($servers) {
		$this->servers = $servers;
	}

	public function getServer($index=null) {
		return $this->servers->Instance($index);
	}

	/**
	 * Return a list of our servers
	 * @param boolean $visible - Only return visible servers
	 */
	public function getServerList($visible=true) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',3,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->servers->getServerList($visible);
	}
}
?>
