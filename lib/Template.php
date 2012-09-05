<?php
/**
 * Classes and functions for the template engine.
 *
 * Templates are either:
 * + Creating or Editing, (a Container or DN passed to the object)
 * + A predefined template, or a default template (template ID passed to the object)
 *
 * The template object will know which attributes are mandatory (MUST
 * attributes) and which attributes are optional (MAY attributes). It will also
 * contain a list of optional attributes. These are attributes that the schema
 * will allow data for (they are MAY attributes), but the template has not
 * included a definition for them.
 *
 * The template object will be invalidated if it does contain the necessary
 * items (objectClass, MUST attributes, etc) to make a successful LDAP update.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * Template Class
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 * @todo RDN attributes should be treated as MUST attributes even though the schema marks them as MAY
 * @todo RDN attributes need to be checked that are included in the schema, otherwise mark it is invalid
 * @todo askcontainer is no longer used?
 */
class Template extends xmlTemplate {
	# If this template visible on the template choice list
	private $visible = true;
	# Is this template valid after parsing the XML file
	private $invalid = false;
	private $invalid_admin = false;
	private $invalid_reason;
	# The TEMPLATE structural objectclasses
	protected $structural_oclass = array();
	protected $description = '';
	# Is this a read-only template (only valid in modification templates)
	private $readonly = false;

	# If this is set, it means we are editing an entry.
	private $dn;
	# Where this template will store its data
	protected $container;
	# Does this template prohibit children being created
	private $noleaf = false;
	# A regexp that determines if this template is valid in the container.
	private $regexp;
	# Template Title
	public $title;
	# Icon for the template
	private $icon;
	# Template RDN attributes
	private $rdn;

	public function __construct($server_id,$name=null,$filename=null,$type=null,$id=null) {
		parent::__construct($server_id,$name,$filename,$type,$id);

		# If this is the default template, we might disable leafs by default.
		if (is_null($filename))
			$this->noleaf = $_SESSION[APPCONFIG]->getValue('appearance','disable_default_leaf');
	}

	public function __clone() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# We need to clone our attributes, when passing back a template with getTemplate
		foreach ($this->attributes as $key => $value)
			$this->attributes[$key] = clone $value;
	}

	/**
	 * Main processing to store the template.
	 *
	 * @param xmldata Parsed xmldata from xml2array object
	 */
	protected function storeTemplate($xmldata) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$server = $this->getServer();
		$objectclasses = array();

		foreach ($xmldata['template'] as $xml_key => $xml_value) {
			if (DEBUG_ENABLED)
				debug_log('Foreach loop Key [%s] Value [%s]',4,0,__FILE__,__LINE__,__METHOD__,$xml_key,is_array($xml_value));

			switch ($xml_key) {
				# Build our object Classes from the DN and Template.
				case ('objectclasses'):
					if (DEBUG_ENABLED)
						debug_log('Case [%s]',4,0,__FILE__,__LINE__,__METHOD__,$xml_key);

					if (isset($xmldata['template'][$xml_key]['objectclass']))
						if (is_array($xmldata['template'][$xml_key]['objectclass'])) {
							foreach ($xmldata['template'][$xml_key]['objectclass'] as $index => $details) {

								# XML files with only 1 objectClass dont have a numeric index.
								$soc = $server->getSchemaObjectClass(strtolower($details));

								# If we havent recorded this objectclass already, do so now.
								if (is_object($soc) && ! in_array($soc->getName(),$objectclasses))
									array_push($objectclasses,$soc->getName(false));

								elseif (! is_object($soc) && ! $_SESSION[APPCONFIG]->getValue('appearance','hide_template_warning'))
									system_message(array(
										'title'=>_('Automatically removed objectClass from template'),
										'body'=>sprintf('%s: <b>%s</b> %s',$this->getTitle(),$details,_('removed from template as it is not defined in the schema')),
										'type'=>'warn'));
							}

						} else {
							# XML files with only 1 objectClass dont have a numeric index.
							$soc = $server->getSchemaObjectClass(strtolower($xmldata['template'][$xml_key]['objectclass']));

							# If we havent recorded this objectclass already, do so now.
							if (is_object($soc) && ! in_array($soc->getName(),$objectclasses))
								array_push($objectclasses,$soc->getName(false));
						}

					break;

				# Build our attribute list from the DN and Template.
				case ('attributes'):
					if (DEBUG_ENABLED)
						debug_log('Case [%s]',4,0,__FILE__,__LINE__,__METHOD__,$xml_key);

					if (is_array($xmldata['template'][$xml_key])) {
						foreach ($xmldata['template'][$xml_key] as $tattrs)
							foreach ($tattrs as $index => $details) {
								if (DEBUG_ENABLED)
									debug_log('Foreach tattrs Key [%s] Value [%s]',4,0,__FILE__,__LINE__,__METHOD__,
										$index,$details);

								# If there is no schema definition for the attribute, it will be ignored.
								if ($sattr = $server->getSchemaAttribute($index))
									if (is_null($this->getAttribute($sattr->getName())))
										$this->addAttribute($sattr->getName(),$details,'XML');
							}

						masort($this->attributes,'order');
					}

					break;

				default:
					if (DEBUG_ENABLED)
						debug_log('Case [%s]',4,0,__FILE__,__LINE__,__METHOD__,$xml_key);

					# Some key definitions need to be an array, some must not be:
					$allowed_arrays = array('rdn');
					$storelower = array('rdn');
					$storearray = array('rdn');

					# Items that must be stored lowercase
					if (in_array($xml_key,$storelower))
						if (is_array($xml_value))
							foreach ($xml_value as $index => $value)
								$xml_value[$index] = strtolower($value);
						else
							$xml_value = strtolower($xml_value);

					# Items that must be stored as arrays
					if (in_array($xml_key,$storearray) && ! is_array($xml_value))
						$xml_value = array($xml_value);

					# Items that should not be an array
					if (! in_array($xml_key,$allowed_arrays) && is_array($xml_value)) {
						debug_dump(array(__METHOD__,'key'=>$xml_key,'value'=>$xml_value));
						error(sprintf(_('In the XML file (%s), [%s] is an array, it must be a string.'),
							$this->filename,$xml_key),'error');
					}

					$this->$xml_key = $xml_value;

					if ($xml_key == 'invalid' && $xml_value)
						$this->setInvalid(_('Disabled by XML configuration'),true);
			}
		}

		if (! count($objectclasses)) {
			$this->setInvalid(_('ObjectClasses in XML dont exist in LDAP server.'));
			return;

		} else {
			$attribute = $this->addAttribute('objectClass',array('values'=>$objectclasses),'XML');
			$attribute->justModified();
			$attribute->setRequired();
			$attribute->hide();
		}

		$this->rebuildTemplateAttrs();

		# Check we have some manditory items.
		foreach (array('rdn','structural_oclass','visible') as $key) {
			if (! isset($this->$key)
				|| (! is_array($this->$key) && ! trim($this->$key))) {

				$this->setInvalid(sprintf(_('Missing %s in the XML file.'),$key));
				break;
			}
		}

		# Mark our RDN attributes as RDN
		$counter = 1;
		foreach ($this->rdn as $key) {
			if ((is_null($attribute = $this->getAttribute($key))) && (in_array_ignore_case('extensibleobject',$this->getObjectClasses()))) {
				$attribute = $this->addAttribute($key,array('values'=>array()));
				$attribute->show();
			}

			if (! is_null($attribute))
				$attribute->setRDN($counter++);
			elseif ($this->isType('creation'))
				$this->setInvalid(sprintf(_('Missing RDN attribute %s in the XML file.'),$key));
		}
	}

	/**
	 * Is default templates enabled?
	 * This will disable the default template from the engine.
	 *
	 * @return boolean
	 */
	protected function hasDefaultTemplate() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($_SESSION[APPCONFIG]->getValue('appearance','disable_default_template'))
			return false;
		else
			return true;
	}

	/**
	 * Return the templates of type (creation/modification)
	 *
	 * @param $string type - creation/modification
	 * @return array - Array of templates of that type
	 */
	protected function readTemplates($type) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$template_xml = new Templates($this->server_id);
		return $template_xml->getTemplates($type);
	}

	/**
	 * This function will perform the following intialisation steps:
	 * + If a DN is set, query the ldap and load the object
	 * + Read our $_REQUEST variable and set the values
	 * After this action, the template should self describe as to whether it is an update, create
	 * or delete.
	 * (OLD values are IGNORED, we will have got them when we build this object from the LDAP server DN.)
	 */
	public function accept($makeVisible=false,$nocache=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$server = $this->getServer();

		# If a DN is set, then query the LDAP server for the details.
		if ($this->dn) {
			if (! $server->dnExists($this->dn))
				system_message(array(
					'title'=>__METHOD__,
					'body'=>sprintf('DN (%s) didnt exist in LDAP?',$this->dn),
					'type'=>'info'));

			$rdnarray = rdn_explode(strtolower(get_rdn(dn_escape($this->dn))));

			$counter = 1;
			foreach ($server->getDNAttrValues($this->dn,null,LDAP_DEREF_NEVER,array_merge(array('*'),$server->getValue('server','custom_attrs')),$nocache) as $attr => $values) {
				# We ignore DNs.
				if ($attr == 'dn')
					continue;

				$attribute = $this->getAttribute($attr);

				if (is_null($attribute))
					$attribute = $this->addAttribute($attr,array('values'=>$values));
				else
					if ($attribute->getValues()) {
						# Override values to those that are defined in the XML file.
						if ($attribute->getSource() != 'XML')
							$attribute->setValue(array_values($values));
						else
							$attribute->setOldValue(array_values($values));

					} else
						$attribute->initValue(array_values($values));

				# Work out the RDN attributes
				foreach ($attribute->getValues() as $index => $value)
					if (in_array(sprintf('%s=%s',
						$attribute->getName(),strtolower($attribute->getValue($index))),$rdnarray))
						$attribute->setRDN($counter++);

				if ($makeVisible)
					$attribute->show();
			}

			# Get the Internal Attributes
			foreach ($server->getDNAttrValues($this->dn,null,LDAP_DEREF_NEVER,array_merge(array('+'),$server->getValue('server','custom_sys_attrs'))) as $attr => $values) {
				$attribute = $this->getAttribute($attr);

				if (is_null($attribute))
					$attribute = $this->addAttribute($attr,array('values'=>$values));
				else
					if ($attribute->getValues())
						$attribute->setValue(array_values($values));
					else
						$attribute->initValue(array_values($values));

				if (! in_array_ignore_case($attribute->getName(),$server->getValue('server','custom_attrs')))
					$attribute->setInternal();
			}

		# If this is the default template, and our $_REQUEST has defined our objectclass, then query the schema to get the attributes
		} elseif ($this->container) {
			if ($this->isType('default') && ! count($this->getAttributes(true)) && isset($_REQUEST['new_values']['objectclass'])) {
				$attribute = $this->addAttribute('objectclass',array('values'=>$_REQUEST['new_values']['objectclass']));
				$attribute->justModified();
				$this->rebuildTemplateAttrs();
				unset($_REQUEST['new_values']['objectclass']);
			}

		} elseif (get_request('create_base')) {
			if (get_request('rdn')) {
				$rdn = explode('=',get_request('rdn'));
				$attribute = $this->addAttribute($rdn[0],array('values'=>array($rdn[1])));
				$attribute->setRDN(1);
			}

		} else {
			debug_dump_backtrace('No DN or CONTAINER?',1);
		}

		# Read in our new values.
		foreach (array('new_values') as $key) {
			if (isset($_REQUEST[$key]))
				foreach ($_REQUEST[$key] as $attr => $values) {
					# If it isnt an array, silently ignore it.
					if (! is_array($values))
						continue;

					# If _REQUEST['skip_array'] with this attr set, we'll ignore this new_value
					if (isset($_REQUEST['skip_array'][$attr]) && $_REQUEST['skip_array'][$attr] == 'on')
						continue;

					# Prune out entries with a blank value.
					foreach ($values as $index => $value)
						if (! strlen(trim($value)))
							unset($values[$index]);

					$attribute = $this->getAttribute($attr);
					# If the attribute is null, then no attribute exists, silently ignore it (unless this is the default template)
					if (is_null($attribute) && (! $this->isType('default') && ! $this->isType(null)))
						continue;

					# If it is a binary attribute, the post should have base64 encoded the value, we'll need to reverse that
					if ($server->isAttrBinary($attr))
						foreach ($values as $index => $value)
							$values[$index] = base64_decode($value);

					if (is_null($attribute)) {
						$attribute = $this->addAttribute($attr,array('values'=>$values));

						if (count($values))
							$attribute->justModified();

					} else
						$attribute->setValue(array_values($values));
				}

			# Read in our new binary values
			if (isset($_FILES[$key]['name']))
				foreach ($_FILES[$key]['name'] as $attr => $values) {
					$new_values = array();

					foreach ($values as $index => $details) {
						# Ignore empty files
						if (! $_FILES[$key]['size'][$attr][$index])
							continue;

						if (! is_uploaded_file($_FILES[$key]['tmp_name'][$attr][$index])) {
							if (isset($_FILES[$key]['error'][$attr][$index]))
								switch($_FILES[$key]['error'][$attr][$index]) {

									# No error; possible file attack!
									case 0:
										$msg = _('Security error: The file being uploaded may be malicious.');
										break;

									# Uploaded file exceeds the upload_max_filesize directive in php.ini
									case 1:
										$msg = _('The file you uploaded is too large. Please check php.ini, upload_max_size setting');
										break;

									# Uploaded file exceeds the MAX_FILE_SIZE directive specified in the html form
									case 2:
										$msg = _('The file you uploaded is too large. Please check php.ini, upload_max_size setting');
										break;

									# Uploaded file was only partially uploaded
									case 3:
										$msg = _('The file you selected was only partially uploaded, likley due to a network error.');
										break;

									# No file was uploaded
									case 4:
										$msg = _('You left the attribute value blank. Please go back and try again.');
										break;

									# A default error, just in case! :)
									default:
										$msg = _('Security error: The file being uploaded may be malicious.');
										break;
								}

							else
								$msg = _('Security error: The file being uploaded may be malicious.');

							system_message(array(
								'title'=>_('Upload Binary Attribute Error'),'body'=>$msg,'type'=>'warn'));

						} else {
							$binaryfile = array();
							$binaryfile['name'] = $_FILES[$key]['tmp_name'][$attr][$index];
							$binaryfile['handle'] = fopen($binaryfile['name'],'r');
							$binaryfile['data'] = fread($binaryfile['handle'],filesize($binaryfile['name']));
							fclose($binaryfile['handle']);

							$new_values[$index] = $binaryfile['data'];
						}
					}

					if (count($new_values)) {
						$attribute = $this->getAttribute($attr);

						if (is_null($attribute))
							$attribute = $this->addAttribute($attr,array('values'=>$new_values));
						else
							foreach ($new_values as $value)
								$attribute->addValue($value);

						$attribute->justModified();
					}
				}
		}

		# If there are any single item additions (from the add_attr form for example)
		if (isset($_REQUEST['single_item_attr'])) {
			if (isset($_REQUEST['single_item_value'])) {
				if (! is_array($_REQUEST['single_item_value']))
					$values = array($_REQUEST['single_item_value']);
				else
					$values = $_REQUEST['single_item_value'];

			} elseif (isset($_REQUEST['binary'])) {
				/* Special case for binary attributes (like jpegPhoto and userCertificate):
				 * we must go read the data from the file and override $_REQUEST['single_item_value'] with the
				 * binary data. Secondly, we must check if the ";binary" option has to be appended to the name
				 * of the attribute. */

				if ($_FILES['single_item_value']['size'] === 0)
					system_message(array(
						'title'=>_('Upload Binary Attribute Error'),
						'body'=>sprintf('%s %s',_('The file you chose is either empty or does not exist.'),_('Please go back and try again.')),
						'type'=>'warn'));

				else {
					if (! is_uploaded_file($_FILES['single_item_value']['tmp_name'])) {
						if (isset($_FILES['single_item_value']['error']))
							switch($_FILES['single_item_value']['error']) {

								# No error; possible file attack!
								case 0:
									$msg = _('Security error: The file being uploaded may be malicious.');
									break;

								# Uploaded file exceeds the upload_max_filesize directive in php.ini
								case 1:
									$msg = _('The file you uploaded is too large. Please check php.ini, upload_max_size setting');
									break;

								# Uploaded file exceeds the MAX_FILE_SIZE directive specified in the html form
								case 2:
									$msg = _('The file you uploaded is too large. Please check php.ini, upload_max_size setting');
									break;

								# Uploaded file was only partially uploaded
								case 3:
									$msg = _('The file you selected was only partially uploaded, likley due to a network error.');
									break;

								# No file was uploaded
								case 4:
									$msg = _('You left the attribute value blank. Please go back and try again.');
									break;

								# A default error, just in case! :)
								default:
									$msg = _('Security error: The file being uploaded may be malicious.');
									break;
							}

						else
							$msg = _('Security error: The file being uploaded may be malicious.');

						system_message(array(
							'title'=>_('Upload Binary Attribute Error'),'body'=>$msg,'type'=>'warn'),'index.php');
					}

					$binaryfile = array();
					$binaryfile['name'] = $_FILES['single_item_value']['tmp_name'];
					$binaryfile['handle'] = fopen($binaryfile['name'],'r');
					$binaryfile['data'] = fread($binaryfile['handle'],filesize($binaryfile['name']));
					fclose($binaryfile['handle']);

					$values = array($binaryfile['data']);
				}
			}

			if (count($values)) {
				$attribute = $this->getAttribute($_REQUEST['single_item_attr']);

				if (is_null($attribute))
					$attribute = $this->addAttribute($_REQUEST['single_item_attr'],array('values'=>$values));
				else
					$attribute->setValue(array_values($values));

				$attribute->justModified();
			}
		}

		# If this is the default creation template, we need to set some additional values
		if ($this->isType('default') && $this->getContext() == 'create') {
			# Load our schema, based on the objectclasses that may have already been defined.
			if (! get_request('create_base'))
				$this->rebuildTemplateAttrs();

			# Set the RDN attribute
			$counter = 1;
			foreach (get_request('rdn_attribute','REQUEST',false,array()) as $key => $value) {
				$attribute = $this->getAttribute($value);

				if (! is_null($attribute))
					$attribute->setRDN($counter++);

				else {
					system_message(array(
						'title'=>_('No RDN attribute'),
						'body'=>_('No RDN attribute was selected'),
						'type'=>'warn'),'index.php');

					die();
				}
			}
		}
	}

	/**
	 * Set the DN for this template, if we are editing entries
	 *
	 * @param dn The DN of the entry
	 */
	public function setDN($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (isset($this->container))
			system_message(array(
				'title'=>__METHOD__,
				'body'=>'CONTAINER set while setting DN',
				'type'=>'info'));

		$this->dn = $dn;
	}

	/**
	 * Set the RDN attributes
	 * Given an RDN, mark the attributes as RDN attributes. If there is no defined attribute,
	 * then the remaining RDNs will be returned.
	 *
	 * @param RDN
	 * @return RDN attributes not processed
	 */
	public function setRDNAttributes($rdn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# Setup to work out our RDN.
		$rdnarray = rdn_explode($rdn);

		$counter = 1;
		foreach ($this->getAttributes(true) as $attribute)
			foreach ($rdnarray as $index => $rdnattr) {
				list($attr,$value) = explode('=',$rdnattr);

				if (strtolower($attr) == $attribute->getName()) {
					$attribute->setRDN($counter++);
					unset($rdnarray[$index]);
				}
			}

		return $rdnarray;
	}

	/**
	 * Display the DN for this template entry. If the DN is not set (creating a new entry), then
	 * a generated DN will be produced, taken from the RDN and the CONTAINER details.
	 *
	 * @return dn
	 */
	public function getDN() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs,$this->dn);

		if ($this->dn)
			return $this->dn;

		# If DN is not set, our DN will be made from our RDN and Container.
		elseif ($this->getRDN() && $this->getContainer())
			return sprintf('%s,%s',$this->getRDN(),$this->GetContainer());

		# If container is not set, we're probably creating the base
		elseif ($this->getRDN() && get_request('create_base'))
			return $this->getRDN();
	}

	public function getDNEncode($url=true) {
		// @todo Be nice to do all this in 1 location
		if ($url)
			return urlencode(preg_replace('/%([0-9a-fA-F]+)/',"%25\\1",$this->getDN()));
		else
			return preg_replace('/%([0-9a-fA-F]+)/',"%25\\1",$this->getDN());
	}

	/**
	 * Set the container for this template, if we are creating entries
	 *
	 * @param dn The DN of the container
	 * @todo Trigger a query to the LDAP server and generate an error if the container doesnt exist
	 */
	public function setContainer($container) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (isset($this->dn))
			system_message(array(
				'title'=>__METHOD__,
				'body'=>'DN set while setting CONTAINER',
				'type'=>'info'));

		$this->container = $container;
	}

	/**
	 * Get the DN of the container for this entry
	 *
	 * @return dn DN of the container
	 */
	public function getContainer() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->container);

		return $this->container;
	}

	public function getContainerEncode($url=true) {
		// @todo Be nice to do all this in 1 location
		if ($url)
			return urlencode(preg_replace('/%([0-9a-fA-F]+)/',"%25\\1",$this->container));
		else
			return preg_replace('/%([0-9a-fA-F]+)/',"%25\\1",$this->container);
	}

	/**
	 * Copy a DN
	 */
	public function copy($template,$rdn,$asnew=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$rdnarray = rdn_explode($rdn);

		$counter = 1;
		foreach ($template->getAttributes(true) as $sattribute) {
			$attribute = $this->addAttribute($sattribute->getName(false),array('values'=>$sattribute->getValues()));

			# Set our new RDN, and its values
			if (is_null($attribute)) {
				debug_dump_backtrace('Attribute is null, it probably doesnt exist in the destination server?');

			} else {

				# Mark our internal attributes.
				if ($sattribute->isInternal())
					$attribute->setInternal();

				$modified = false;
				foreach ($rdnarray as $index => $rdnattr) {
					list($attr,$value) = explode('=',$rdnattr);
					if (strtolower($attr) == $attribute->getName()) {

						# If this is already marked as an RDN, then this multivalue RDN was updated on a previous loop
						if (! $modified) {
							$attribute->setValue(array($value));
							$attribute->setRDN($counter++);
							$modified = true;

						} else {
							$attribute->addValue($value);
						}

						# This attribute has been taken care of, we'll drop it from our list.
						unset($rdnarray[$index]);
					}
				}
			}

			// @todo If this is a Jpeg Attribute, we need to mark it read only, since it cant be deleted like text attributes can
			if (strcasecmp(get_class($attribute),'jpegAttribute') == 0)
				$attribute->setReadOnly();
		}

		# If we have any RDN values left over, there werent in the original entry and need to be added.
		foreach ($rdnarray as $rdnattr) {
			list($attr,$value) = explode('=',$rdnattr);

			$attribute = $this->addAttribute($attr,array('values'=>array($value)));

			if (is_null($attribute))
				debug_dump_backtrace('Attribute is null, it probably doesnt exist in the destination server?');
			else
				$attribute->setRDN($counter++);
		}

		# If we are copying into a new entry, we need to discard all the "old values"
		if ($asnew)
			foreach ($this->getAttributes(true) as $sattribute)
				$sattribute->setOldValue(array());
	}

	/**
	 * Get Attributes by LDAP type
	 * This function will return a list of attributes by LDAP type (MUST,MAY).
	 *
	 * @return array Array of attributes.
	 */
	function getAttrbyLdapType($type) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$result = array();

		foreach ($this->attributes as $index => $attribute) {
			if ($attribute->getLDAPtype() == strtolower($type))
				array_push($result,$attribute->getName());
		}

		return $result;
	}

	/**
	 * Return true if this is a MUST,MAY attribute
	 */
	function isAttrType($attr,$type) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (in_array(strtolower($attr),$this->getAttrbyLdapType($type)))
			return true;
		else
			return false;
	}

	/**
	 * Return the attributes that comprise the RDN.
	 *
	 * @return array Array of RDN objects
	 */
	private function getRDNObjects() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$return = array();

		foreach ($this->attributes as $attribute)
			if ($attribute->isRDN())
				array_push($return,$attribute);

		masort($return,'rdn');
		return $return;
	}

	/**
	 * Get all the RDNs for this template, in RDN order.
	 *
	 * @return array RDNs in order.
	 */
	public function getRDNAttrs() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$return = array();

		foreach ($this->getRDNObjects() as $attribute) {
			# We'll test if two RDN's have the same number (we cant test anywhere else)
			if (isset($return[$attribute->isRDN()]) && $this->getType() == 'creation')
				system_message(array(
					'title'=>_('RDN attribute sequence already defined'),
					'body'=>sprintf('%s %s',
						sprintf(_('There is a problem in template [%s].'),$this->getName()),
						sprintf(_('RDN attribute sequence [%s] is already used by attribute [%s] and cant be used by attribute [%s] also.'),
							$attribute->isRDN(),$return[$attribute->isRDN()],$attribute->getName())),
					'type'=>'error'),'index.php');

			$return[$attribute->isRDN()] = $attribute->getName();
		}

		return $return;
	}

	/**
	 * Return the RDN for this template. If the DN is already defined, then the RDN will be calculated from it.
	 * If the DN is not set, then the RDN will be calcuated from the template attribute definitions
	 *
	 * @return rdn RDN for this template
	 */
	public function getRDN() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		# If the DN is set, then the RDN will be calculated from it.
		if ($this->dn)
			return get_rdn($this->dn);

		$rdn = '';

		foreach ($this->getRDNObjects() as $attribute) {
			$vals = $attribute->getValues();

			# If an RDN attribute has no values, return with an empty string. The calling script should handle this.
			if (! count($vals))
				return '';

			foreach ($vals as $val)
				$rdn .= sprintf('%s=%s+',$attribute->getName(false),$val);
		}

		# Chop the last plus sign off when returning
		return preg_replace('/\+$/','',$rdn);
	}

	/**
	 * Return the attribute name part of the RDN
	 */
	public function getRDNAttributeName() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attr = array();

		if ($this->getDN()) {
			$i = strpos($this->getDN(),',');
			if ($i !== false) {
				$attrs = explode('\+',substr($this->getDN(),0,$i));
				foreach ($attrs as $id => $attr) {
					list ($name,$value) = explode('=',$attr);
					$attrs[$id] = $name;
				}

				$attr = array_unique($attrs);
			}
		}

		return $attr;
	}

	/**
	 * Determine the type of template this is
	 */
	public function getContext() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($this->getContainer() && get_request('cmd','REQUEST') == 'copy')
			return 'copyasnew';
		elseif ($this->getContainer() || get_request('create_base'))
			return 'create';
		elseif ($this->getDN())
			return 'edit';
		else
			return 'unknown';
	}

	/**
	 * Test if the template is visible
	 *
	 * @return boolean
	 */
	public function isVisible() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->visible);

		return $this->visible;
	}

	public function setVisible() {
		$this->visible = true;
	}

	public function setInvisible() {
		$this->visible = false;
	}

	public function getRegExp() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->regexp);

		return $this->regexp;
	}

	/**
	 * Test if this template has been marked as a read-only template
	 */
	public function isReadOnly() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ((($this->getContext() == 'edit') && $this->readonly) || $this->getServer()->isReadOnly())
			return true;
		else
			return false;
	}

	/**
	 * Get the attribute entries
	 *
	 * @param boolean Include the optional attributes
	 * @return array Array of attributes
	 */
	public function getAttributes($optional=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($optional)
			return $this->attributes;

		$result = array();
		foreach ($this->attributes as $attribute) {
			if (! $attribute->isRequired())
				continue;

			array_push($result,$attribute);
		}

		return $result;
	}

	/**
	 * Return a list of attributes that should be shown
	 */
	public function getAttributesShown() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$result = array();

		foreach ($this->attributes as $attribute)
			if ($attribute->isVisible())
				array_push($result,$attribute);

		return $result;
	}

	/**
	 * Return a list of the internal attributes
	 */
	public function getAttributesInternal() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$result = array();

		foreach ($this->attributes as $attribute)
			if ($attribute->isInternal())
				array_push($result,$attribute);

		return $result;
	}

	/**
	 * Return the objectclasses defined in this template
	 *
	 * @return array Array of Objects
	 */
	public function getObjectClasses() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attribute = $this->getAttribute('objectclass');
		if ($attribute)
			return $attribute->getValues();
		else
			return array();
	}

	/**
	 * Get template icon
	 */
	public function getIcon() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->icon);

		return isset($this->icon) ? sprintf('%s/%s',IMGDIR,$this->icon) : '';
	}

	/**
	 * Return the template description
	 *
	 * @return string Description
	 */
	public function getDescription() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->description);

		return $this->description;
	}

	/**
	 * Set a template as invalid
	 *
	 * @param string Message indicating the reason the template has been invalidated
	 */
	public function setInvalid($msg,$admin=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->invalid = true;
		$this->invalid_reason = $msg;
		$this->invalid_admin = $admin;
	}

	/**
	 * Get the template validity or the reason it is invalid
	 *
	 * @return string Invalid reason, or false if not invalid
	 */
	public function isInValid() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($this->invalid)
			return $this->invalid_reason;
		else
			return false;
	}

	public function isAdminDisabled() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->invalid_admin);

		return $this->invalid_admin;
	}

	/**
	 * Set the minimum number of values for an attribute
	 *
	 * @param object Attribute
	 * @param int
	 */
	private function setMinValueCount($attr,$value) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attribute = $this->getAttribute($attr);

		if (! is_null($attribute))
			$attribute->setMinValueCount($value);
	}

	/**
	 * Set the LDAP type property for an attribute
	 *
	 * @param object Attribute
	 * @param string (MUST,MAY,OPTIONAL)
	 */
	private function setAttrLDAPtype($attr,$value) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attribute = $this->getAttribute($attr);

		if (is_null($attribute))
			$attribute = $this->addAttribute($attr,array('values'=>array()));

		$attribute->setLDAPtype($value);
	}

	/**
	 * OnChangeAdd javascript processing
	 */
	public function OnChangeAdd($origin,$value) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attribute = $this->getAttribute($origin);

		if (preg_match('/^=(\w+)\((.*)\)$/',$value,$matches)) {
			$command = $matches[1];
			$arg = $matches[2];
		} else
			return;

		switch ($command) {
			/*
			autoFill:string
			string is a literal string, and may contain many fields like %attr|start-end/flags%
				to substitute values read from other fields.
			|start-end is optional, but must be present if the k flag is used.
			/flags is optional.

			flags may be:
			T:	Read display text from selection item (drop-down list), otherwise, read the value of the field
				For fields that aren't selection items, /T shouldn't be used, and the field value will always be read.
			k:	Tokenize:
				If the "k" flag is not given:
					A |start-end instruction will perform a sub-string operation upon
					the value of the attr, passing character positions start-end through.
					start can be 0 for first character, or any other integer.
					end can be 0 for last character, or any other integer for a specific position.
				If the "k" flag is given:
					The string read will be split into fields, using : as a delimiter
					"start" indicates which field number to pass through.
			K:	The string read will be split into fields, using ' ' as a delimiter "start" indicates which field number to pass through.
			l:	Make the result lower case.
			U:	Make the result upper case.
			*/
			case 'autoFill':
				if (! preg_match('/;/',$arg)) {
					system_message(array(
						'title'=>_('Problem with autoFill() in template'),
						'body'=>sprintf('%s (<b>%s</b>)',_('There is only 1 argument, when there should be two'),$attribute->getName(false)),
						'type'=>'warn'));

					return;
				}

				list($attr,$string) = preg_split('(([^,]+);(.*))',$arg,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
				preg_match_all('/%(\w+)(\|[0-9]*-[0-9]*)?(\/[KklTUA]+)?%/U',$string,$matchall);
				//print"<PRE>";print_r($matchall); //0 = highlevel match, 1 = attr, 2 = subst, 3 = mod

				if (! isset($attribute->js['autoFill']))
					$attribute->js['autoFill'] = '';

				$formula = $string;
				$formula = preg_replace('/^([^%])/','\'$1',$formula);
				$formula = preg_replace('/([^%])$/','$1\'',$formula);

				# Check that our attributes match our schema attributes.
				foreach ($matchall[1] as $index => $checkattr) {
					$sattr = $this->getServer()->getSchemaAttribute($checkattr);

					# If the attribute is the same as in the XML file, then dont need to do anything.
					if (! $sattr || ! strcasecmp($sattr->getName(),$checkattr))
						continue;

					$formula = preg_replace("/$checkattr/",$sattr->getName(),$formula);
					$matchall[1][$index] = $sattr->getName();
				}

				$elem_id = 0;

				foreach ($matchall[0] as $index => $null) {
					$match_attr = strtolower($matchall[1][$index]);
					$match_subst = $matchall[2][$index];
					$match_mod = $matchall[3][$index];

					$substrarray = array();

					if (! isset($varcount[$match_attr]))
						$varcount[$match_attr] = 0;
					else
						$varcount[$match_attr]++;

					$js_match_attr = $match_attr;
					$match_attr = $js_match_attr.'xx'.$varcount[$match_attr];

					$formula = preg_replace('/%'.$js_match_attr.'([|\/%])/i','%'.$match_attr.'$1',$formula,1);

					$attribute->js['autoFill'] .= sprintf("  var %s;\n",$match_attr);
					$attribute->js['autoFill'] .= sprintf(
							"  var elem$elem_id = document.getElementById(pre+'%s'+suf);\n".
							"  if (!elem$elem_id) return;\n", $js_match_attr);

					if (strstr($match_mod,'T')) {
						$attribute->js['autoFill'] .= sprintf("  %s = elem$elem_id.options[elem$elem_id.selectedIndex].text;\n",
							$match_attr);
					} else {
						$attribute->js['autoFill'] .= sprintf("  %s = elem$elem_id.value;\n",$match_attr);
					}

					$elem_id++;

					if (strstr($match_mod,'k')) {
						preg_match_all('/([0-9]+)/',trim($match_subst),$substrarray);
						if (isset($substrarray[1][0])) {
							$tok_idx = $substrarray[1][0];
						} else {
							$tok_idx = '0';
						}
						$attribute->js['autoFill'] .= sprintf("   %s = %s.split(':')[%s];\n",$match_attr,$match_attr,$tok_idx);

					} elseif (strstr($match_mod,'K')) {
						preg_match_all('/([0-9]+)/',trim($match_subst),$substrarray); 
						if (isset($substrarray[1][0])) { 
							$tok_idx = $substrarray[1][0]; 
						} else { 
							$tok_idx = '0'; 
						} 
						$attribute->js['autoFill'] .= sprintf("   %s = %s.split(' ')[%s];\n",$match_attr,$match_attr,$tok_idx); 

					} else {
						preg_match_all('/([0-9]*)-([0-9]*)/',trim($match_subst),$substrarray);
						if ((isset($substrarray[1][0]) && $substrarray[1][0]) || (isset($substrarray[2][0]) && $substrarray[2][0])) {
							$attribute->js['autoFill'] .= sprintf("   %s = %s.substr(%s,%s);\n",
								$match_attr,$match_attr,
								$substrarray[1][0] ? $substrarray[1][0] : '0',
								$substrarray[2][0] ? $substrarray[2][0] : sprintf('%s.length',$match_attr));
						}
					}

					if (strstr($match_mod,'l')) {
						$attribute->js['autoFill'] .= sprintf("   %s = %s.toLowerCase();\n",$match_attr,$match_attr);
					}
					if (strstr($match_mod,'U')) {
						$attribute->js['autoFill'] .= sprintf("   %s = %s.toUpperCase();\n",$match_attr,$match_attr);
					}
					if (strstr($match_mod,'A')) {
						$attribute->js['autoFill'] .= sprintf("   %s = toAscii(%s);\n",$match_attr,$match_attr);
					}

					# Matchfor only entry without modifiers.
					$formula = preg_replace('/^%('.$match_attr.')%$/U','$1 + \'\'',$formula);
					# Matchfor only entry with modifiers.
					$formula = preg_replace('/^%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[KklTUA]+)?%$/U','$1 + \'\'',$formula);
					# Matchfor begining entry.
					$formula = preg_replace('/^%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[KklTUA]+)?%/U','$1 + \'',$formula);
					# Matchfor ending entry.
					$formula = preg_replace('/%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[KklTUA]+)?%$/U','\' + $1 ',$formula);
					# Match for entries not at begin/end.
					$formula = preg_replace('/%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[:lTUA]+)?%/U','\' + $1 + \'',$formula);
					$attribute->js['autoFill'] .= "\n";
				}

				$attribute->js['autoFill'] .= sprintf(" fillRec(pre+'%s'+suf, %s); // %s\n",strtolower($attr),$formula,$string);
				$attribute->js['autoFill'] .= "\n";
				break;

			default: $return = '';
		}
	}

	/**
	 * This functions main purpose is to discover our MUST attributes based on objectclass
	 * definitions in the template file and to discover which of the objectclasses are
	 * STRUCTURAL - without one, creating an entry will just product an LDAP error.
	 */
	private function rebuildTemplateAttrs() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$server = $this->getServer();

		# Collect our structural, MUST & MAY attributes.
		$oclass_processed = array();
		$superclasslist = array();
		$allattrs = array('objectclass');

		foreach ($this->getObjectClasses() as $oclass) {
			# If we get some superclasses - then we'll need to go through them too.
			$supclass = true;
			$inherited = false;

			while ($supclass) {
				$soc = $server->getSchemaObjectClass($oclass);

				if ($soc->getType() == 'structural' && (! $inherited))
					array_push($this->structural_oclass,$oclass);

				# Make sure our MUST attributes are marked as such for this template.
				if ($soc->getMustAttrs())
					foreach ($soc->getMustAttrs() as $index => $details) {
						$objectclassattr = $details->getName();

						# We add the 'objectClass' attribute, only if it's explicitly in the template attribute list
						if ((strcasecmp('objectClass',$objectclassattr) != 0) ||
								((strcasecmp('objectClass',$objectclassattr) == 0) && (! is_null($this->getAttribute($objectclassattr))))) {

							# Go through the aliases, and ignore any that are already defined.
							$ignore = false;
							$sattr = $server->getSchemaAttribute($objectclassattr);
							foreach ($sattr->getAliases() as $alias) {
								if ($this->isAttrType($alias,'must')) {
									$ignore = true;
									break;
								}
							}

							if ($ignore)
								continue;

							$this->setAttrLDAPtype($sattr->getName(),'must');
							$this->setMinValueCount($sattr->getName(),1);

							# We need to mark the attributes as show, except for the objectclass attribute.
							if (strcasecmp('objectClass',$objectclassattr) != 0) {
								$attribute = $this->getAttribute($sattr->getName());
								$attribute->show();
							}
						}

						if (! in_array($objectclassattr,$allattrs))
							array_push($allattrs,$objectclassattr);
					}

				if ($soc->getMayAttrs())
					foreach ($soc->getMayAttrs() as $index => $details) {
						$objectclassattr = $details->getName();
						$sattr = $server->getSchemaAttribute($objectclassattr);

						# If it is a MUST attribute, skip to the next one.
						if ($this->isAttrType($objectclassattr,'must'))
							continue;

						if (! $this->isAttrType($objectclassattr,'may'))
							$this->setAttrLDAPtype($sattr->getName(false),'may');

						if (! in_array($objectclassattr,$allattrs))
							array_push($allattrs,$objectclassattr);
					}

				# Keep a list to objectclasses we have processed, so we dont get into a loop.
				array_push($oclass_processed,$oclass);
				$supoclasses = $soc->getSupClasses();

				if (count($supoclasses) || count($superclasslist)) {
					foreach ($supoclasses as $supoclass) {
						if (! in_array($supoclass,$oclass_processed))
							$superclasslist[] = $supoclass;
					}

					$oclass = array_shift($superclasslist);
					if ($oclass)
						$inherited = true;
					else
						$supclass = false;

				} else {
					$supclass = false;
				}
			}
		}

		# Check that attributes are defined by an ObjectClass
		foreach ($this->getAttributes(true) as $index => $attribute)
			if (! in_array($attribute->getName(),$allattrs) && (! array_intersect($attribute->getAliases(),$allattrs))
				&& (! in_array_ignore_case('extensibleobject',$this->getObjectClasses()))
				&& (! in_array_ignore_case($attribute->getName(),$server->getValue('server','custom_attrs')))) {
				unset($this->attributes[$index]);

				if (! $_SESSION[APPCONFIG]->getValue('appearance','hide_template_warning'))
					system_message(array(
						'title'=>_('Automatically removed attribute from template'),
						'body'=>sprintf('%s: <b>%s</b> %s',$this->getTitle(),$attribute->getName(false),_('removed from template as it is not defined by an ObjectClass')),
						'type'=>'warn'));
			}
	}

	/**
	 * Return an array, that can be passed to ldap_add().
	 * Attributes with empty values will be excluded.
	 */
	public function getLDAPadd($attrsOnly=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$return = array();
		$returnattrs = array();

		if ($attrsOnly && count($returnattrs))
			return $returnattrs;

		foreach ($this->getAttributes(true) as $attribute)
			if (! $attribute->isInternal() && count($attribute->getValues())) {
				$return[$attribute->getName()] = $attribute->getValues();
				$returnattrs[$attribute->getName()] = $attribute;
			}

		# Ensure that our objectclasses has "top".
		if (isset($return['objectclass']) && ! in_array('top',$return['objectclass']))
			array_push($return['objectclass'],'top');

		if ($attrsOnly)
			return $returnattrs;

		return $return;
	}

	/**
	 * Return an array, that can be passed to ldap_mod_replace().
	 * Only attributes that have changed their value will be returned.
	 *
	 * This function will cache its results, so that it can be called with count() to see
	 * if there are changes, and if they are, the 2nd call will just return the results
	 *
	 * @param boolean Return the attribute objects (useful for a confirmation process), or the modification array for ldap_modify()
	 */
	public function getLDAPmodify($attrsOnly=false,$index=0) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		static $return = array();
		static $returnattrs = array();

		if ($attrsOnly && isset($returnattrs[$index]) && count($returnattrs[$index]))
			return $returnattrs[$index];

		$returnattrs[$index] = array();
		$return[$index] = array();

		# If an objectclass is being modified, we need to remove all the orphan attributes that would result.
		if ($this->getAttribute('objectclass')->hasBeenModified()) {
			$attr_to_keep = array();
			$server = $this->getServer();

			# Make sure that there will be a structural object class remaining.
			$haveStructural = false;
			foreach ($this->getAttribute('objectclass')->getValues() as $value) {
				$soc = $server->getSchemaObjectClass($value);

				if ($soc) {
					if ($soc->isStructural())
						$haveStructural = true;

					# While we are looping, workout which attributes these objectclasses define.
					foreach ($soc->getMustAttrs(true) as $value)
						if (! in_array($value->getName(),$attr_to_keep))
							array_push($attr_to_keep,$value->getName());

					foreach ($soc->getMayAttrs(true) as $value)
						if (! in_array($value->getName(),$attr_to_keep))
							array_push($attr_to_keep,$value->getName());
				}
			}

			if (! $haveStructural)
				error(_('An entry should have one structural objectClass.'),'error','index.php');

			# Work out the attributes to delete.
			foreach ($this->getAttribute('objectclass')->getRemovedValues() as $value) {
				$soc = $server->getSchemaObjectClass($value);

				foreach ($soc->getMustAttrs() as $value) {
					$attribute = $this->getAttribute($value->getName());

					if ($attribute && (! in_array($value->getName(),$attr_to_keep)) && ($value->getName() != 'objectclass'))
						#array_push($attr_to_delete,$value->getName(false));
						$attribute->setForceDelete();
				}

				foreach ($soc->getMayAttrs() as $value) {
					$attribute = $this->getAttribute($value->getName());

					if ($attribute && (! in_array($value->getName(),$attr_to_keep)) && ($value->getName() != 'objectclass'))
						$attribute->setForceDelete();
				}
			}
		}

		foreach ($this->getAttributes(true) as $attribute)
			if ($attribute->hasBeenModified()
				&& (count(array_diff($attribute->getValues(),$attribute->getOldValues())) || ! count($attribute->getValues())
					|| $attribute->isForceDelete() || (count($attribute->getValues()) != count($attribute->getOldValues()))))
				$returnattrs[$index][$attribute->getName()] = $attribute;

		if ($attrsOnly)
			return $returnattrs[$index];

		foreach ($returnattrs[$index] as $attribute)
			$return[$index][$attribute->getName()] = $attribute->getValues();

		return $return[$index];
	}

	/**
	 * Get the attributes that are marked as force delete
	 * We'll cache this result in the event of multiple calls.
	 */
	public function getForceDeleteAttrs() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		static $result = array();

		if (count($result))
			return $result;

		foreach ($this->attributes as $attribute)
			if ($attribute->isForceDelete())
				array_push($result,$attribute);

		return $result;
	}

	/**
	 * Get available attributes
	 */
	public function getAvailAttrs() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$attributes = array();
		$server = $this->getServer();

		# Initialise the Attribute Factory.
		$attribute_factory = new AttributeFactory();

		if (in_array_ignore_case('extensibleobject',$this->getObjectClasses())) {
			foreach ($server->SchemaAttributes() as $sattr) {
				$attribute = $attribute_factory->newAttribute($sattr->getName(),array('values'=>array()),$server->getIndex(),null);
				array_push($attributes,$attribute);
			}

		} else {
			$attrs = array();

			foreach ($this->getObjectClasses() as $oc) {
				$soc = $server->getSchemaObjectClass($oc);
				$attrs = array_merge($attrs,$soc->getMustAttrNames(true),$soc->getMayAttrNames(true));
				$attrs = array_unique($attrs);
			}

			foreach ($attrs as $attr)
				if (is_null($this->getAttribute($attr))) {
					$attribute = $attribute_factory->newAttribute($attr,array('values'=>array()),$server->getIndex(),null);
					array_push($attributes,$attribute);
				}
		}

		masort($attributes,'name');
		return $attributes;
	}

	public function isNoLeaf() {
		return $this->noleaf;
	}

	public function sort() {
		usort($this->attributes,'sortAttrs');
	}
}
?>
