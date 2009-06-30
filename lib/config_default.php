<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/config_default.php,v 1.16.2.6 2007/01/27 13:25:49 wurley Exp $

/**
 * Configuration processing and defaults.
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 * @todo Add validation of set variables to enforce limits or particular values.
 */

# The minimum version of PHP required to run phpLDAPadmin.
define('REQUIRED_PHP_VERSION','4.1.0');

class Config {
	var $custom;
	var $default;

	function Config() {

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
		 */
		$this->default->appearance['anonymous_bind_redirect_no_tree'] = array(
			'desc'=>'Redirect user to search form if anonymous',
			'default'=>false);

		$this->default->appearance['date'] = array(
			'desc'=>'Date format whenever dates are shown',
			'default'=>'%A %e %B %Y');

		$this->default->appearance['date_attrs'] = array(
			'desc'=>'Array of attributes that should show a jscalendar',
			'default'=>array('shadowExpire'=>'%es','shadowLastChange'=>'%es'));

		$this->default->appearance['hide_configuration_management'] = array(
			'desc'=>'Hide the Sourceforge related links',
			'default'=>false);

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

		/** Mass Delete
		 * Set to true if you want to draw a checkbox next to each entry in the tree viewer
		 * to be able to delete multiple entries at once
		 */
		$this->default->appearance['mass_delete'] = array(
			'desc'=>'Enable mass delete in tree viewer',
			'default'=>false);

		/**
		 * If you want certain attributes to be editable as multi-line, include them in this list
		 * A multi-line textarea will be drawn instead of a single-line text field
		 */
		$this->default->appearance['multi_line_attributes'] = array(
			'desc'=>'Attributes to show as multiline attributes',
			'default'=>array("postalAddress","homePostalAddress","personalSignature"));

		/**
		 * A list of syntax OIDs which support multi-line attribute values:
		 */
		$this->default->appearance['multi_line_syntax_oids'] = array(
			'desc'=>'Attributes to show as multiline attributes',
			'default'=>array(
				// octet string syntax OID:
				"1.3.6.1.4.1.1466.115.121.1.40",
				// postal address syntax OID:
				"1.3.6.1.4.1.1466.115.121.1.41"));

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

		$this->default->appearance['tree_width'] = array(
			'desc'=>'Pixel width of the left frame view (tree browser)',
			'default'=>320);

		$this->default->appearance['tree_plm'] = array(
			'desc'=>'Whether to enable the PHPLayersMenu for the tree',
			'default'=>false);

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

		/** Aliases and Referrrals
		 * Similar to ldapsearch's -a option, the following options allow you to configure
		 * how phpLDAPadmin will treat aliases and referrals in the LDAP tree.
		 * For the following four settings, avaialable options include:
		 *
		 *    LDAP_DEREF_NEVER     - aliases are never dereferenced (eg, the contents of
		 *                           the alias itself are shown and not the referenced entry).
		 *    LDAP_DEREF_SEARCHING - aliases should be dereferenced during the search but
		 *                           not when locating the base object of the search.
		 *    LDAP_DEREF_FINDING   - aliases should be dereferenced when locating the base
		 *                           object but not during the search.
		 *    LDAP_DEREF_ALWAYS    - aliases should be dereferenced always (eg, the contents
		 *                           of the referenced entry is shown and not the aliasing entry)
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

		/** Temp Directories
		 * This directory must be readable and writable by your web server
		 */
		$this->default->jpeg['tmpdir'] = array(
			'desc'=>'Temporary directory for jpegPhoto data',
			'default'=>'/tmp');

		$this->default->jpeg['tmp_keep_time'] = array(
			'desc'=>'Time in seconds to keep jpegPhoto temporary files in the temp directory',
			'default'=>120);

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

	function GetValue($key,$index) {

		$value = null;

		if (! isset($this->default->$key))
			pla_error(sprintf('A call was made in [%s] to GetValue requesting [%s] that isnt predefined.',
				basename($_SERVER['PHP_SELF']),$key));
		else
			$default = $this->default->$key;

		if (! isset($default[$index]))
			pla_error("Requesting a index [$index] that isnt predefined.");
		else
			$default = $default[$index];

		if (isset($default['default']))
			$value = $default['default'];

		if (isset($this->custom->$key)) {
			$custom = $this->custom->$key;

			if (isset($custom[$index]))
				$value = $custom[$index];
		}

		//print "Returning [$value] for key [$key], index [$index]<BR>";
		return $value;
	}

	/**
	 * Function to check and warn about any unusual defined variables.
	 */
	function CheckCustom() {
		if (isset($this->custom)) {
			foreach ($this->custom as $masterkey => $masterdetails) {

				if (isset($this->default->$masterkey)) {

					if (! is_array($masterdetails))
						pla_error("Error in configuration file, [$masterdetails] should be an ARRAY.");

					foreach ($masterdetails as $key => $value) {
						# Test that the key is correct.
						if (! in_array($key,array_keys($this->default->$masterkey)))
							pla_error("Error in configuration file, [$key] has not been defined as a PLA configurable variable.");

						# Test if its should be an array or not.
						if (is_array($this->default->{$masterkey}[$key]['default']) && ! is_array($value))
							pla_error("Error in configuration file, {$masterkey}['$key'] SHOULD be an array of values.");

						if (! is_array($this->default->{$masterkey}[$key]['default']) && is_array($value))
							pla_error("Error in configuration file, {$masterkey}['$key'] should NOT be an array of values.");
					}

				} else {
					pla_error("Error in configuration file, [$masterkey] has not been defined as a PLA MASTER configurable variable.");
				}
			}
		}
	}
}

# Define our configuration variable.
$config = new Config;
require (CONFDIR.'config.php');

if (($config->GetValue('debug','syslog') || $config->GetValue('debug','file')) && $config->GetValue('debug','level'))
	define('DEBUG_ENABLED',1);
else
	define('DEBUG_ENABLED',0);
?>
