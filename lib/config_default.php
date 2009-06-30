<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/config_default.php,v 1.27.2.9 2008/12/12 12:20:22 wurley Exp $

/**
 * Configuration processing and defaults.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 *
 * @todo Add validation of set variables to enforce limits or particular values.
 */

# The minimum version of PHP required to run phpLDAPadmin.
define('REQUIRED_PHP_VERSION','5.0.0');

/**
 * The config class contains all our configuration settings for a session.
 * 
 * An instance of this class should be stored in $_SESSION to maintain state, and to avoid
 * rebuilding/rereading it at the state of each page output.
 *
 * @package phpLDAPadmin
 *
 * @author The phpLDAPadmin development team
 * @author Deon George
 */
class Config {
	public $custom;
	protected $default;

	public $ldapservers = array();
	protected $friendly_attrs = array();
	public $queries = array();
	public $attrs_display_order = array();
	public $hidden_attrs = array();
	public $hidden_except_dn = '';
	public $hidden_attrs_ro = array();
	public $read_only_attrs = array();
	public $read_only_except_dn = '';
	public $unique_attrs = array();

	public $hooks = array();

	public function __construct() {
		$this->custom = new stdClass;
		$this->default = new stdClass;

		## Appearance Attributes
		/** Anonymous implies read only
		 * Set to true if you want LDAP data to be displayed read-only (without input fields)
		 * when a user logs in to a server anonymously
		 */
		$this->default->appearance['anonymous_bind_implies_read_only'] = array(
			'desc'=>'Display as read only if user logs in with anonymous bind',
			'default'=>true);

		/* Anonymous redirect
		 * Set to true if you want phpLDAPadmin to redirect anonymous
		 * users to a search form with no tree viewer on the left after
		 * logging in.
		 * @todo: With the new no-frames PLA, this code is broken, and needs to be fixed.
		 */
		$this->default->appearance['anonymous_bind_redirect_no_tree'] = array(
			'desc'=>'Redirect user to search form if anonymous',
			'default'=>false);

		$this->default->appearance['compress'] = array(
			'desc'=>'Compress Output',
			'default'=>false);

		$this->default->appearance['date'] = array(
			'desc'=>'Date format whenever dates are shown',
			'default'=>'%A %e %B %Y');

		$this->default->appearance['custom_templates_only'] = array(
			'desc'=>'Only display the custom templates.',
			'default'=>false);

		$this->default->appearance['date_attrs'] = array(
			'desc'=>'Array of attributes that should show a jscalendar',
			'default'=>array('shadowExpire'=>'%es','shadowLastChange'=>'%es'));

		$this->default->appearance['date_attrs_showtime'] = array(
			'desc'=>'Array of attributes that should show a the time when showing the jscalendar',
			'default'=>array(''));

		$this->default->appearance['disable_default_template'] = array(
			'desc'=>'Disabled the Default Template',
			'default'=>false);

		$this->default->appearance['hide_debug_info'] = array(
			'desc'=>'Hide the features that may provide sensitive debugging information to the browser',
			'default'=>true);

		$this->default->appearance['timezone'] = array(
			'desc'=>'Define our timezone, if not defined in php.ini',
			'default'=>null);

		/** Language
		 * The language setting. If you set this to 'auto', phpLDAPadmin will
		 * attempt to determine your language automatically. Otherwise, available
		 * lanaguages are: 'ct', 'de', 'en', 'es', 'fr', 'it', 'nl', and 'ru'
		 * Localization is not complete yet, but most strings have been translated.
		 * Please help by writing language files. See lang/en.php for an example.
		 */
		$this->default->appearance['language'] = array(
			'desc'=>'Language',
			'default'=>'auto');

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

		$this->default->appearance['show_clear_password'] = array(
			'desc'=>'Whether to show clear passwords if we dont obfuscate them',
			'default'=>false);

		$this->default->appearance['page_title'] = array(
			'desc'=>'Change the page title to this text',
			'default'=>'');

		$this->default->appearance['show_hints'] = array(
			'desc'=>'Show helpful hints',
			'default'=>true);

		$this->default->appearance['show_top_create'] = array(
			'desc'=>'Show a additional create link on the top of the list if there are more than 10 entries',
			'default'=>true);

		/*
		 * What to do after entry creation :
		 * 2 : display the creation form again
		 * 1 : display the new created entry
		 * 0 : display the choice between 1 and 2
		 */
		$this->default->appearance['action_after_creation'] = array(
			'desc'=>'Display the new created entry',
			'default'=>1);

		$this->default->appearance['show_schema_link'] = array(
			'desc'=>'Show the schema link for each attribute',
			'default'=>true);

		$this->default->appearance['show_attribute_notes'] = array(
			'desc'=>'Show notes for each attribute',
			'default'=>true);

		$this->default->appearance['stylesheet'] = array(
			'desc'=>'Style sheet to use',
			'default'=>'style.css');

		/** Tree display
		 * A format string used to display enties in the tree viewer (left-hand side)
		 * You can use special tokens to draw the entries as you wish. You can even mix in HTML to format the string
		 * Here are all the tokens you can use:
		 *	%rdn - draw the RDN of the entry (ie, "cn=Dave")
		 *	%dn - draw the DN of the entry (ie, "cn=Dave,ou=People,dc=example,dc=com"
		 *	%rdnValue - draw the value of the RDN (ie, instead of "cn=Dave", just draw "Dave")
		 *	%[attrname]- draw the value (or values) of the specified attribute.
		 *	 example: %gidNumber
		 *
		 * Examples:
		 *
		 * To draw the gidNumber and uidNumber to the right of the RDN in a small, gray font:
		 *	'%rdn <small style="color:gray">( %gidNumber / %uidNumber )</span>'
		 * To draw the full DN of each entry:
		 *	'%dn'
		 * To draw the objectClasses to the right in parenthesis:
		 *	'%rdn <small style="color: gray">( %objectClass )</small>'
		 * To draw the user-friendly RDN value (ie, instead of "cn=Dave", just draw "Dave"):
		 *	'%rdnValue'
		 */
		$this->default->appearance['tree_display_format'] = array(
			'desc'=>'LDAP attribute to show in the tree',
			'default'=>'%rdn');

		$this->default->appearance['tree_height'] = array(
			'desc'=>'Pixel height of the tree browser',
			'default'=>null);

		$this->default->appearance['tree_width'] = array(
			'desc'=>'Pixel width of the tree browser',
			'default'=>null);

		/**
		 * Tree display filter
		 * LDAP filter used to search entries for the tree viewer (left-hand side)
		 */
		$this->default->appearance['tree_filter'] = array(
			'desc'=>'LDAP search filter for the tree entries',
			'default'=>'(objectClass=*)');

		$this->default->appearance['tree'] = array(
			'desc'=>'Class name which inherits from Tree class and implements the draw() method',
			'default'=>'AJAXTree');

		$this->default->appearance['entry_factory'] = array(
			'desc'=>'Class name which inherits from EntryFactory class',
			'default'=>'TemplateEntryFactory');

		$this->default->appearance['attribute_factory'] = array(
			'desc'=>'Class name which inherits from AttributeFactory class',
			'default'=>'AttributeFactory');

		$this->default->appearance['entry_reader'] = array(
			'desc'=>'Class name which inherits from EntryReader class',
			'default'=>'EntryReader');

		$this->default->appearance['entry_writer'] = array(
			'desc'=>'Class name which inherits from EntryWriter class',
			'default'=>'EntryWriter1');

		/** Caching
		 */
		$this->default->cache['schema'] = array(
			'desc'=>'Cache schema activity',
			'default'=>true);

		$this->default->cache['template'] = array(
			'desc'=>'Cache Template configuration',
			'default'=>true);

		$this->default->cache['tree'] = array(
			'desc'=>'Cache Browser Tree',
			'default'=>true);

		/**
		 * Define command availability ; if the value of a command is true,
		 * the command will be available.
		 */
		$this->default->commands['all'] = array(
			'desc'=>'Define command availability',
			'default'=> array(
				'home' => true,
				'external_links' => array('feature' => true,
					'bug' => true,
					'donation' => true,
					'help' => true,
					'credits' => true),
				'purge' => true,
				'schema' => true,
				'import' => true,
				'export' => true,
				'logout' => true,
				'search' => array('simple_search' => true,
					'predefined_search' => true,
					'advanced_search' => true),
				'server_refresh' => true,
				'server_info' => true,
				'entry_refresh' => true,
				'entry_move' => true,
				'entry_internal_attributes_show' => true,
				'entry_delete' => array('simple_delete' => true,
					'mass_delete' => false),
				'entry_rename' => true,
				'entry_compare' => true,
				'entry_create' => true,
				'attribute_add' => true,
				'attribute_add_value' => true,
				'attribute_delete' => true,
				'attribute_delete_value' => true
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

		/** Temp Directories
		 * This directory must be readable and writable by your web server
		 */
		$this->default->jpeg['tmpdir'] = array(
			'desc'=>'Temporary directory for jpegPhoto data',
			'default'=>'/tmp');

		$this->default->jpeg['tmp_keep_time'] = array(
			'desc'=>'Time in seconds to keep jpegPhoto temporary files in the temp directory',
			'default'=>120);

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

		$this->default->session['memorylimit'] = array(
			'desc'=>'Set the PHP memorylimit warning threshold.',
			'default'=>24);

		$this->default->session['timelimit'] = array(
			'desc'=>'Set the PHP timelimit.',
			'default'=>30);

		/** Cookie Time
		 * If you used auth_type 'form' in the servers list, you can adjust how long the cookie will last
		 * (default is 0 seconds, which expires when you close the browser)
		 */
		$this->default->session['cookie_time'] = array(
			'desc'=>'Time in seconds for the life of cookies',
			'default'=>0);

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
			'descr'=>'Disable random salt for crypt()',
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
		 * Which attributes to include in the drop-down menu of the simple search form (comma-separated)
		 * Change this to suit your needs for convenient searching. Be sure to change the corresponding
		 * list below ($search_attributes_display)
		 */
		$this->default->search['attributes'] = array(
			'desc'=>'Attributes to include in the drop down menu of the simple search form (comma separated)',
			'default'=>array('uid','cn','gidNumber','objectClass','telephoneNumber','mail','street'));

		/**
		 * You can re-arrange the order of the search criteria on the simple search form by modifying this array
		 * You cannot however change the names of the criteria. Criteria names will be translated at run-time.
		 */
		$this->default->search['criteria_options'] = array(
			'desc'=>'Rearrange the order of the search criteria',
			'default'=>array('equals','starts with','contains','ends with','sounds like'));

		/**
		 * The list of attributes to display in each search result entry.
		 * Note that you can add * to the list to display all attributes
		 */
		$this->default->search['result_attributes'] = array(
			'desc'=>'List of attributes to display in each search result entry',
			'default'=>array('cn','sn','uid','postalAddress','telephoneNumber'));
	}

	private function getConfigArray($usecache=true) {
		global $CACHE;

		if ($usecache && isset($CACHE[__METHOD__]))
			return $CACHE[__METHOD__];

		foreach ($this->default as $key => $vals)
			$CACHE[__METHOD__][$key] = $vals;

		foreach ($this->custom as $key => $vals)
			foreach ($vals as $index => $val)
				$CACHE[__METHOD__][$key][$index]['value'] = $val;

		return $CACHE[__METHOD__];
	}

	/**
	 * Get a configuration value.
	 */
	public function GetValue($key,$index) {
		$config = $this->getConfigArray();

		if (! isset($config[$key]))
			error(sprintf('A call was made in [%s] to GetValue requesting [%s] that isnt predefined.',
				basename($_SERVER['PHP_SELF']),$key),'error',null,true);

		if (! isset($config[$key][$index]))
			error(sprintf('Requesting an index [%s] in key [%s] that isnt predefined.',$index,$key),'error',null,true);

		return isset($config[$key][$index]['value']) ? $config[$key][$index]['value'] : $config[$key][$index]['default'];
	}

	/**
	 * Function to check and warn about any unusual defined variables.
	 */
	public function CheckCustom() {
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
	 * The parameter number is variable.
	 * For example : isCommandAvailable('search', 'simple_search')
	 */
	public function isCommandAvailable() {
		$a = func_get_args();
		if (count($a) == 1 && is_array($a[0]))
			$a = $a[0];
		$i = 0;

		# Command availability list
		$cmd = $this->GetValue('commands','all');
		# Search for the command
		while ($i < count($a)) {
			if (! is_array($cmd))
				return $cmd;
			if (! isset($cmd[$a[$i]]))
				return false;

			$cmd = $cmd[$a[$i]];
			$i++;
		}

		# If this is a leaf command, return its availability
		if (! is_array($cmd))
			return $cmd;

		# Else the command is available, if one of its sub-command is available
		$a[] = '';
		foreach ($cmd as $c => $v) {
			$a[$i] = $c;
			if ($this->isCommandAvailable($a))
				return true;
		}
		return false;
	}

	/**
	 * Reads the friendly_attrs array as defined in config.php and lower-cases all
	 * the keys. Will return an empty array if the friendly_attrs array is not defined
	 * in config.php. This is simply used so we can more easily lookup user-friendly
	 * attributes configured by the admin.
	 */
	function getFriendlyAttrs($friendly_attrs) {
		if (defined('DEBUG_ENABLED') && DEBUG_ENABLED)
			debug_log('Entered with ()',1,__FILE__,__LINE__,__METHOD__);

		# If friendly_attrs is not an array, then set to an empty array.
		if (! is_array($friendly_attrs))
			$this->friendly_attrs =  array();

		else
			foreach ($friendly_attrs as $old_name => $new_name)
				$this->friendly_attrs[strtolower($old_name)] = $new_name;
		}

	/**
	 * This function will return the friendly name of an attribute, if it exists.
	 * If the friendly name doesnt exist, the attribute name will be returned.
 	 *
	 * @param attribute
	 * @return string friendly name|attribute
	 */
	public function getFriendlyName($attr) {
		if ($this->haveFriendlyName($attr))
			return $this->friendly_attrs[strtolower($attr)];
		else
			return $attr;
	}

	/**
	 * This function will return true if a friendly name exists for an attribute.
	 * If the friendly name doesnt exist, it will return false.
 	 *
	 * @param attribute
	 * @return boolean true|false
	 */
	public function haveFriendlyName($attr) {
		return isset($this->friendly_attrs[strtolower($attr)]);

	}

	/**
	 * This function will return the <ancronym> html for a friendly name attribute.
 	 *
	 * @param attribute
	 * @return string html for the friendly name.
	 */
	public function getFriendlyHTML($attr) {
		if ($this->haveFriendlyName($attr))
			return sprintf('<acronym title="%s %s">%s</acronym>',
				_('Alias for'),$attr,htmlspecialchars($this->getFriendlyName($attr)));
		else
			return $attr;
	}
}
?>
