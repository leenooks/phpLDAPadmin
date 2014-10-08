<?php
/**
 * Classes and functions for communication of Data Stores
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * This abstract class provides variables and methods for LDAP datastores for use by PLA.
 *
 * @package phpLDAPadmin
 * @subpackage DataStore
 */
class ldap_pla extends ldap {
	function __construct($index) {
		parent::__construct($index);

		$this->default->appearance['pla_password_hash'] = array(
			'desc'=>'Default HASH to use for passwords',
			'default'=>'md5');

		$this->default->appearance['show_create'] = array(
			'desc'=>'Whether to show the "Create new Entry here" in the tree browser',
			'default'=>true);

		$this->default->appearance['open_tree'] = array(
			'desc'=>'Whether to initially open each tree',
			'default'=>false);

		$this->default->login['fallback_dn'] = array(
			'desc'=>'If the attribute base login fails, see if a DN was entered',
			'default'=>false);

		$this->default->query['disable_default'] = array(
			'desc'=>'Configuration to disable the default query template',
			'default'=>false);

		$this->default->query['custom_only'] = array(
			'desc'=>'Configuration to force the usage of custom query templates',
			'default'=>false);

		$this->default->server['branch_rename'] = array(
			'desc'=>'Enable renaming of branches',
			'default'=>false);

		$this->default->server['custom_attrs'] = array(
			'desc'=>'Custom operational attributes to be treated as regular attributes',
			'default'=>array(''));

		$this->default->server['custom_sys_attrs'] = array(
			'desc'=>'Custom operational attributes to be treated as internal attributes',
			'default'=>array('+'));

		$this->default->server['jpeg_attributes'] = array(
			'desc'=>'Additional attributes to treat as Jpeg Attributes',
			'default'=>array());

		# This was added in case the LDAP server doesnt provide them with a base +,* query.
		$this->default->server['root_dse_attributes'] = array(
			'desc'=>'RootDSE attributes for use when displaying server info',
			'default'=>array(
				'namingContexts',
				'subschemaSubentry',
				'altServer',
				'supportedExtension',
				'supportedControl',
				'supportedSASLMechanisms',
				'supportedLDAPVersion',
				'currentTime',
				'dsServiceName',
				'defaultNamingContext',
				'schemaNamingContext',
				'configurationNamingContext',
				'rootDomainNamingContext',
				'supportedLDAPPolicies',
				'highestCommittedUSN',
				'dnsHostName',
				'ldapServiceName',
				'serverName',
				'supportedCapabilities',
				'changeLog',
				'tlsAvailableCipherSuites',
				'tlsImplementationVersion',
				'supportedSASLMechanisms',
				'dsaVersion',
				'myAccessPoint',
				'dseType',
				'+',
				'*'
			));

		$this->default->server['force_may'] = array(
			'desc'=>'Force server MUST attributes as MAY attributes',
			'default'=>array(
			));

		# Settings for auto_number
		$this->default->auto_number['enable'] = array(
			'desc'=>'Enable the AUTO UID feature',
			'default'=>true);

		$this->default->auto_number['mechanism'] = array(
			'desc'=>'Mechanism to use to search for automatic numbers',
			'default'=>'search');

		$this->default->auto_number['search_base'] = array(
			'desc'=>'Base DN to use for search mechanisms',
			'default'=>null);

		$this->default->auto_number['min'] = array(
			'desc'=>'Minimum number to start with',
			'default'=>array('uidNumber'=>1000,'gidNumber'=>500));

		$this->default->auto_number['dn'] = array(
			'desc'=>'DN to use when evaluating numbers',
			'default'=>null);

		$this->default->auto_number['pass'] = array(
			'desc'=>'Password for DN to use when evaluating numbers',
			'default'=>null);

		$this->default->unique['attrs'] = array(
			'desc'=>'Attributes to check for uniqueness before allowing updates',
			'default'=>array('mail','uid','uidNumber'));

		$this->default->unique['dn'] = array(
			'desc'=>'DN to use when evaluating attribute uniqueness',
			'default'=>null);

		$this->default->unique['pass'] = array(
			'desc'=>'Password for DN to use when evaluating attribute uniqueness',
			'default'=>null);
	}

	public function __get($key) {
		switch ($key) {
			case 'name':
				return $this->getValue('server','name');

			default:
				system_message(array(
					'title'=>_('Unknown request for Object value.'),
					'body'=>sprintf(_('Attempt to obtain value %s from %s'),$key,get_class($this)),
					'type'=>'error'));
		}
	}

	/**
	 * Gets whether the admin has configured phpLDAPadmin to show the "Create New" link in the tree viewer.
	 * <code>
	 *	$servers->setValue('appearance','show_create',true|false);
	 * </code>
	 * If NOT set, then default to show the Create New item.
	 * If IS set, then return the value (it should be true or false).
	 *
	 * The entry creation command must be available.
	 * <code>
	 *	$config->custom->commands['script'] = array('create' => true);
	 * </code>
	 *
	 * @return boolean true if the feature is enabled and false otherwise.
	 */
	function isShowCreateEnabled() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! $_SESSION[APPCONFIG]->isCommandAvailable('script','create'))
			return false;
		else
			return $this->getValue('appearance','show_create');
	}

	/**
	 * Fetch whether the user has configured a certain server login to be non anonymous
	 *
	 * <code>
	 *	$servers->setValue('login','anon_bind',true|false);
	 * </code>
	 *
	 * @return boolean
	 */
	public function isAnonBindAllowed() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# If only_login_allowed_dns is set, then we cant have anonymous.
		if (count($this->getValue('login','allowed_dns')) > 0)
			$return = false;
		else
			$return = $this->getValue('login','anon_bind');

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',17,0,__FILE__,__LINE__,__METHOD__,$return);

		return $return;
	}

	/**
	 * Returns true if the user has configured the specified server to enable branch (non-leaf) renames.
	 *
	 * This is configured in config.php thus:
	 * <code>
	 *	$servers->setValue('server','branch_rename',true|false);
	 * </code>
	 *
	 * @return boolean
	 */
	function isBranchRenameEnabled() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

 		return $this->getValue('server','branch_rename');
	}

	/**
	 * Determines if an attribute's value can contain multiple lines. Attributes that fall
	 * in this multi-line category may be configured in config.php. Hence, this function
	 * accesses the global variable $_SESSION[APPCONFIG]->custom->appearance['multi_line_attributes'];
	 *
	 * Usage example:
	 * <code>
	 *	if ($ldapserver->isMultiLineAttr('postalAddress'))
	 *		echo '<textarea name="postalAddress"></textarea>';
	 *	else
	 *		echo '<input name="postalAddress" type="text">';
	 * </code>
	 *
	 * @param string The name of the attribute of interested (case insensivite)
	 * @param string (optional) The current value of the attribute (speeds up the process by searching for carriage returns already in the attribute value)
	 * @return boolean
	 */
	function isMultiLineAttr($attr_name,$val=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# Set default return
		$return = false;

		# First, check the optional val param for a \n or a \r
		if (! is_null($val) && (strpos($val,"\n") || strpos($val,"\r")))
			$return = true;

		# Next, compare strictly by name first
		else
			foreach ($_SESSION[APPCONFIG]->getValue('appearance','multi_line_attributes') as $multi_line_attr_name)
				if (strcasecmp($multi_line_attr_name,$attr_name) == 0) {
					$return = true;
					break;
				}

		# If unfound, compare by syntax OID
		if (! $return) {
			$sattr = $this->getSchemaAttribute($attr_name);

			if ($sattr) {
				$syntax_oid = $sattr->getSyntaxOID();

				if ($syntax_oid)
					foreach ($_SESSION[APPCONFIG]->getValue('appearance','multi_line_syntax_oids') as $multi_line_syntax_oid)
						if ($multi_line_syntax_oid == $syntax_oid) {
							$return = true;
							break;
						}
			}
		}

		if (DEBUG_ENABLED)
			debug_log('Returning (%s)',17,0,__FILE__,__LINE__,__METHOD__,$return);

		return $return;
	}

	/**
	 * Returns true if the specified attribute is configured according to
	 * the test enabled in config.php
	 *
	 * @param string The name of the attribute to test.
	 * @param array The attributes to test against.
	 * @param dn A DN that is exempt from these tests.
	 * @return boolean
	 */
	private function isAttrTest($attr,$attrs,$except_dn) {
		$attr = trim($attr);
		if (! trim($attr) || ! count($attrs))
			return false;

		# Is the user excluded?
		if ($except_dn && $this->userIsMember($this->getLogin(),$except_dn))
			return false;

		foreach ($attrs as $attr_name)
			if (strcasecmp($attr,trim($attr_name)) == 0)
				return true;

		return false;
	}

	/**
	 * Returns true if the specified attribute is configured as read only
	 * in config.php.
	 * Attributes are configured as read-only in config.php thus:
	 * <code>
	 *	$config->custom->appearance['readonly_attrs'] = array('objectClass');
	 * </code>
	 *
	 * @param string The name of the attribute to test.
	 * @return boolean
	 */
	public function isAttrReadOnly($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attrs = $_SESSION[APPCONFIG]->getValue('appearance','readonly_attrs');
		$except_dn = $_SESSION[APPCONFIG]->getValue('appearance','readonly_attrs_exempt');

		return $this->isAttrTest($attr,$attrs,$except_dn);
	}

	/**
	 * Returns true if the specified attribute is configured as hidden
	 * in config.php.
	 * Attributes are configured as hidden in config.php thus:
	 * <code>
	 *	$config->custom->appearance['hide_attrs'] = array('objectClass');
	 * </code>
	 *
	 * @param string The name of the attribute to test.
	 * @return boolean
	 */
	public function isAttrHidden($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attrs = $_SESSION[APPCONFIG]->getValue('appearance','hide_attrs');
		$except_dn = $_SESSION[APPCONFIG]->getValue('appearance','hide_attrs_exempt');

		return $this->isAttrTest($attr,$attrs,$except_dn);
	}

	/**
	 * Add objects
	 */
	public function add($dn,$entry_array,$method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		foreach ($entry_array as $attr => $val)
			$entry_array[$attr] = dn_unescape($val);

		$result = false;

		# Check our unique attributes.
		if (! $this->checkUniqueAttrs($dn,$entry_array))
			return false;

		if (run_hook('pre_entry_create',array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'attrs'=>$entry_array))) {
			$result = @ldap_add($this->connect($method),dn_escape($dn),$entry_array);

			if ($result) {
				# Update the tree
				$tree = get_cached_item($this->index,'tree');

				# If we created the base, delete it, then add it back
				if (get_request('create_base'))
					$tree->delEntry($dn);

				$tree->addEntry($dn);

				set_cached_item($this->index,'tree','null',$tree);

				run_hook('post_entry_create',array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'attrs'=>$entry_array));

			} else {
				system_message(array(
					'title'=>_('Could not add the object to the LDAP server.'),
					'body'=>ldap_error_msg($this->getErrorMessage(null),$this->getErrorNum(null)),
					'type'=>'error'));
			}
		}

		return $result;
	}

	/**
	 * Delete objects
	 */
	public function delete($dn,$method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$result = false;

		if (run_hook('pre_entry_delete',array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn))) {
			$result = @ldap_delete($this->connect($method),dn_escape($dn));

			if ($result) {
				# Update the tree
				$tree = get_cached_item($this->index,'tree');
				$tree->delEntry($dn);

				set_cached_item($this->index,'tree','null',$tree);

				run_hook('post_entry_delete',array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn));
			}
		}

		return $result;
	}

	/**
	 * Rename objects
	 */
	public function rename($dn,$new_rdn,$container,$deleteoldrdn,$method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$result = false;

		if (run_hook('pre_entry_rename',array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'rdn'=>$new_rdn,'container'=>$container))) {
			$result = @ldap_rename($this->connect($method),$dn,$new_rdn,$container,$deleteoldrdn);

			if ($result) {
				# Update the tree
				$tree = get_cached_item($this->index,'tree');
				$newdn = sprintf('%s,%s',$new_rdn,$container);
				$tree->renameEntry($dn,$newdn);

				set_cached_item($this->index,'tree','null',$tree);

				run_hook('post_entry_rename',array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'rdn'=>$new_rdn,'container'=>$container));
			}
		}

		return $result;
	}

	/**
	 * Modify objects
	 */
	public function modify($dn,$attrs,$method=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# Check our unique attributes.
		if (! $this->checkUniqueAttrs($dn,$attrs))
			return false;

		$result = false;
		$summary = array();
		$current_attrs = $this->getDNAttrValues($dn,$method,LDAP_DEREF_NEVER,array('*'));

		# Go through our attributes and call our hooks for each attribute changing its value
		foreach ($attrs as $attr => $values) {
			# For new attributes
			if (count($values) && ! isset($current_attrs[$attr])) {
				if (! run_hook('pre_attr_add',
					array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'attr'=>$attr,'newvalue'=>$values))) {

					unset($attrs[$attr]);
					system_message(array(
						'title'=>_('Attribute not added'),
						'body'=>sprintf('%s (<b>%s</b>)',_('Hook pre_attr_add prevented attribute from being added'),$attr),
						'type'=>'warn'));

				} else
					$summary['add'][$attr]['new'] = $values;

			# For modify attributes
			} elseif (count($values)) {
				if (! run_hook('pre_attr_modify',
					array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'attr'=>$attr,'oldvalue'=>$current_attrs[$attr],'newvalue'=>$values))) {

					unset($attrs[$attr]);
					system_message(array(
						'title'=>_('Attribute not modified'),
						'body'=>sprintf('%s (<b>%s</b>)',_('Hook pre_attr_modify prevented attribute from being modified'),$attr),
						'type'=>'warn'));

				} else {
					$summary['modify'][$attr]['new'] = $values;
					$summary['modify'][$attr]['old'] = $current_attrs[$attr];
				}

			# For delete attributes
			} else {
				if (! run_hook('pre_attr_delete',
					array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'attr'=>$attr,'oldvalue'=>$current_attrs[$attr]))) {

					unset($attrs[$attr]);
					system_message(array(
						'title'=>_('Attribute not deleted'),
						'body'=>sprintf('%s (<b>%s</b>)',_('Hook pre_attr_delete prevented attribute from being deleted'),$attr),
						'type'=>'warn'));

				} else
					$summary['delete'][$attr]['old'] = $current_attrs[$attr];
			}
		}

		if (! count($attrs))
			return false;

		if (run_hook('pre_entry_modify',array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'attrs'=>$attrs))) {
			$result = @ldap_modify($this->connect($method),$dn,$attrs);

			if ($result) {
				run_hook('post_entry_modify',array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'attrs'=>$attrs));

				foreach (array('add','modify','delete') as $mode)
					if (isset($summary[$mode]))
						foreach ($summary[$mode] as $attr => $values)
							switch ($mode) {
								case 'add':
									run_hook(sprintf('post_attr_%s',$mode),
										array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'attr'=>$attr,'newvalue'=>$values['new']));
									break;

								case 'modify':
									run_hook(sprintf('post_attr_%s',$mode),
										array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'attr'=>$attr,'oldvalue'=>$values['old'],'newvalue'=>$values['new']));
									break;

								case 'delete':
									run_hook(sprintf('post_attr_%s',$mode),
										array('server_id'=>$this->index,'method'=>$method,'dn'=>$dn,'attr'=>$attr,'oldvalue'=>$values['old']));
									break;

								default:
									debug_dump_backtrace(sprintf('Unkown mode %s',$mode),1);
							}
			} else {
				system_message(array(
					'title'=>_('Could not perform ldap_modify operation.'),
					'body'=>ldap_error_msg($this->getErrorMessage($method),$this->getErrorNum($method)),
					'type'=>'error'));
			}
		}

		return $result;
	}

	/**
	 * Returns true if the specified attribute is configured as unique
	 * in config.php.
	 * Attributes are configured as hidden in config.php thus:
	 * <code>
	 *	$servers->setValue('unique','attrs',array('mail','uid','uidNumber'));
	 * </code>
	 *
	 * @param string $attr The name of the attribute to test.
	 * @return boolean
	 */
	public function isAttrUnique($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# Should this attribute value be unique
		if (in_array_ignore_case($attr,$this->getValue('unique','attrs')))
			return true;
		else
			return false;
	}

	/**
	 * This function will check whether the value for an attribute being changed
	 * is already assigned to another DN.
	 *
	 * Returns the bad value, or null if all values are OK
	 *
	 * @param dn DN that is being changed
	 * @param string Attribute being changed
	 * @param string|array New values for the attribute
	 */
	public function checkUniqueAttrs($dn,$attrs) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# If none of the attributes are defined unique, we'll return immediately;
		if (! $checkattrs = array_intersect(arrayLower($this->getValue('unique','attrs')),array_keys(array_change_key_case($attrs))))
			return true;

		# Check see and use our alternate uid_dn and password if we have it.
		if (! $this->login($this->getValue('unique','dn'),$this->getValue('unique','pass'),'unique')) {
			system_message(array(
				'title'=>_('UNIQUE invalid login/password'),
				'body'=>sprintf('%s (<b>%s</b>)',_('Unable to connect to LDAP server with the unique login/password, please check your configuration.'),
					$this->getName()),
				'type'=>'warn'));

			return false;
		}

		$query = array();

		# Build our search filter to double check each attribute.
		$query['filter'] = '(|';
		foreach ($checkattrs as $attr)
			foreach ($attrs[$attr] as $val)
				if ($val)
					$query['filter'] .= sprintf('(%s=%s)',$attr,$val);
		$query['filter'] .= ')';

		$query['attrs'] = $checkattrs;

		# Search through our bases and see if we have match
		foreach ($this->getBaseDN() as $base) {
			$query['base'] = $base;

			# Do the search
			$results = $this->query($query,'unique');

			# If we have a match.
			if (count($results))
				foreach ($results as $values)
					# If one of the attributes is owned to somebody else, then we may as well die here.
					if ($values['dn'] != $dn) {
						$href = sprintf('cmd.php?cmd=query_engine&server_id=%s&filter=%s&scope=sub&query=none&format=list&search=true',$this->index,$query['filter']);

						system_message(array(
							'title'=>_('Attribute value would not be unique'),
							'body'=>sprintf('%s (<b><a href="%s">%s</a></b>)',
								_('This update has been or will be cancelled, it would result in an attribute value not being unique. You might like to search the LDAP server for the offending entry.'),
								htmlspecialchars($href),
								_('Search')),
							'type'=>'warn'));

						return false;
					}
		}

		# If we get here, then it must be OK?
		return true;
	}

	/**
	 * Check if the session timeout has occured for this LDAP server.
	 */
	public function isSessionValid() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',17,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# If inactiveTime() returns a value, we need to check that it has not expired.
		if (is_null($this->inactivityTime()) || ! $this->isLoggedIn())
			return true;

		# If session has expired
		if ((isset($_SESSION['ACTIVITY'][$this->getIndex()])) && ($_SESSION['ACTIVITY'][$this->getIndex()] < time())) {
			$this->logout();
			unset($_SESSION['ACTIVITY'][$this->getIndex()]);

			return false;
		}

		$_SESSION['ACTIVITY'][$this->getIndex()] = $this->inactivityTime();
		return true;
	}
}
?>
