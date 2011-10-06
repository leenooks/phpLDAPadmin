<?php
/**
 * Classes and functions for XML based templates.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * XML Templates Class
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 */
abstract class xmlTemplates {
	# The server ID that these templates are configured for.
	protected $server_id;
	# Our array of the available templates.
	protected $templates = array();

	function __construct($server_id) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->server_id = $server_id;
		$server = $_SESSION[APPCONFIG]->getServer($this->server_id);
		$custom_prefix = $server->getValue('custom','pages_prefix');
		$class = $this->getClassVars();
		$changed = false;

		# Try to get the templates from our CACHE.
		if ($this->templates = get_cached_item($server_id,$class['item'])) {
			if (DEBUG_ENABLED)
				debug_log('Using CACHED templates',4,0,__FILE__,__LINE__,__METHOD__);

			# See if the template_time has expired to see if we should reload the templates.
			foreach ($this->templates as $index => $template) {
				# If the file no longer exists, we'll delete the template.
				if (! file_exists($template->getFileName())) {
					unset($this->templates[$index]);
					$changed = true;

					system_message(array(
						'title'=>_('Template XML file removed.'),
						'body'=>sprintf('%s %s (%s)',_('Template XML file has removed'),$template->getName(false),$template->getType()),
						'type'=>'info','special'=>true));

					continue;
				}

				if (($template->getReadTime() < (time()-$class['cachetime']))
					&& (filectime($template->getFileName()) > $template->getReadTime())) {

					system_message(array(
						'title'=>_('Template XML file changed.'),
						'body'=>sprintf('%s %s (%s)',_('Template XML file has changed and been reread'),$template->getName(false),$template->getType()),
						'type'=>'info','special'=>true));

					$changed = true;
					$this->templates[$index] = new $class['name']($this->server_id,$template->getName(false),$template->getFileName(),$template->getType(),$index);
				}
			}

			if (DEBUG_ENABLED)
				debug_log('Templates refreshed',4,0,__FILE__,__LINE__,__METHOD__);

			# See if there are any new template files
			$index = max(array_keys($this->templates))+1;
			foreach ($class['types'] as $type) {
				$dir = $class['dir'].$type;
				$dh = opendir($dir);
				if (! $type)
					$type = 'template';

				while ($file = readdir($dh)) {
					# Ignore any files that are not XML files.
					if (! preg_match('/.xml$/',$file))
						continue;

					# Ignore any files that are not the predefined custom files.
					if ($_SESSION[APPCONFIG]->getValue('appearance','custom_templates_only')
						&& ! preg_match("/^${custom_prefix}/",$file))
						continue;

					$filename = sprintf('%s/%s',$dir,$file);

					if (! in_array($filename,$this->getTemplateFiles())) {
						$templatename = preg_replace('/.xml$/','',$file);
	
						$this->templates[$index] = new $class['name']($this->server_id,$templatename,$filename,$type,$index);
						$index++;

						$changed = true;

						system_message(array(
							'title'=>_('New Template XML found.'),
							'body'=>sprintf('%s %s (%s)',_('A new template XML file has been loaded'),$file,$type),
							'type'=>'info','special'=>true));
					}
				}
			}

		} else {
			if (DEBUG_ENABLED)
				debug_log('Parsing templates',4,0,__FILE__,__LINE__,__METHOD__);

			# Need to reset this, as get_cached_item() returns null if nothing cached.
			$this->templates = array();
			$changed = true;

			$counter = 0;
			foreach ($class['types'] as $type) {
				$dir = $class['dir'].$type;
				$dh = opendir($class['dir'].$type);
				if (! $type)
					$type = 'template';

				while ($file = readdir($dh)) {
					# Ignore any files that are not XML files.
					if (! preg_match('/.xml$/',$file))
						continue;

					# Ignore any files that are not the predefined custom files.
					if ($_SESSION[APPCONFIG]->getValue('appearance','custom_templates_only')
						&& ! preg_match("/^${custom_prefix}/",$file))
						continue;

					$filename = sprintf('%s/%s',$dir,$file);

					# Store the template
					$templatename = preg_replace('/.xml$/','',$file);
					$this->templates[$counter] = new $class['name']($this->server_id,$templatename,$filename,$type,$counter);
					$counter++;
				}
			}
		}

		if (DEBUG_ENABLED)
			debug_log('Templates loaded',4,0,__FILE__,__LINE__,__METHOD__);

		if ($changed) {
			masort($this->templates,'title');
			set_cached_item($server_id,$class['item'],'null',$this->templates);
		}
	}

	/**
	 * This will return our custom class variables, used by the parent to create objects.
	 */
	private function getClassVars() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$class = array();

		switch (get_class($this)) {
			case 'Queries':
				$class['item'] = 'query';
				$class['name'] = 'Query';
				$class['cachetime'] = $_SESSION[APPCONFIG]->getValue('cache','query_time');
				$class['types'] = array('');
				$class['dir'] = QUERYDIR;

				break;

			case 'Templates':
				$class['item'] = 'template';
				$class['name'] = 'Template';
				$class['cachetime'] = $_SESSION[APPCONFIG]->getValue('cache','template_time');
				$class['types'] = array('creation','modification');
				$class['dir'] = TMPLDIR;

				break;

			default:
				debug_dump_backtrace(sprintf('Unknown class %s',get_class($this)),1);
		}

		return $class;
	}

	/**
	 * Return a list of templates by their type
	 * This function should return a sorted list, as the array is built sorted.
	 *
	 * @param string Type of template, eg: creation, modification
	 * @param boolean Exclude templates purposely disabled.
	 * @return array List of templates of the type
	 */
	public function getTemplates($type=null,$container=null,$disabled=false) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$result = array();

		if (is_array($this->templates))
			foreach ($this->templates as $details) {

				# Clone this, as we'll disable some templates, as a result of the container being requested.
				$template = clone $details;
				if (! is_null($container) && ($regexp = $template->getRegExp()) && (! @preg_match('/'.$regexp.'/i',$container))) {
					$template->setInvalid(_('This template is not valid in this container'),true);

					if ($_SESSION[APPCONFIG]->getValue('appearance','hide_template_regexp'))
						$template->setInvisible();
				}

				if ($template->isVisible() && (! $disabled || ! $template->isAdminDisabled()))
					if (is_null($type) || (! is_null($type) && $template->isType($type)))
						array_push($result,$template);
			}

		return $result;
	}

	/**
	 * Return a template by its ID
	 *
	 * @param string The template ID as it was when it was generated (normally used in $_REQUEST vars).
	 * @return object Template (or default template if the ID doesnt exist)
	 */
	function getTemplate($templateid) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$class = $this->getClassVars();

		foreach ($this->templates as $template)
			if ($template->getID() === $templateid)
				return clone $template;

		# If we get here, the template ID didnt exist, so return a blank template, which be interpreted as the default template
		$object = new $class['name']($this->server_id,null,null,'default');
		return $object;
	}

	/**
	 * Get a list of template filenames.
	 */
	private function getTemplateFiles() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$result = array();

		foreach ($this->templates as $template)
			array_push($result,$template->getFileName());

		return $result;
	}
}

/**
 * XML Template Class
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 */
abstract class xmlTemplate {
	# Server ID that the template is linked to
	protected $server_id;
	# Template unique ID
	protected $id;
	# Template name - as extracted from the filename
	protected $name;
	# Template type - creation/modification
	protected $type;
	# Time this object was created
	protected $readtime;
	# Template file name
	protected $filename;
	# The TEMPLATE attributes as per the template definition, or the DN entry
	protected $attributes = array();

	public function __construct($server_id,$name=null,$filename=null,$type=null,$id=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->server_id = $server_id;
		$this->name = $name;
		$this->type = $type;
		$this->filename = $filename;
		$this->readtime = time();
		$this->id = $id;

		# If there is no filename, then this template is a default template.
		if (is_null($filename))
			return;

		# If we have a filename, parse the template file and build the object.
		$objXML = new xml2array();
		$xmldata = $objXML->parseXML(file_get_contents($filename),$filename);
		$this->storeTemplate($xmldata);
	}

	/**
	 * Get an attribute ID
	 *
	 * @param string The Attribute being searched.
	 * @return int Attribute ID in the array
	 */
	protected function getAttrID($attr) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		foreach ($this->attributes as $index => $attribute)
			if (strtolower($attr) == $attribute->getName() || in_array(strtolower($attr),$attribute->getAliases()))
				return $index;

		return null;
	}

	/**
	 * Get the Template filename.
	 */
	public function getFileName() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->filename);

		return $this->filename;
	}

	/**
	 * Return the template by ID
	 */
	public function getID() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs,$this->id);

		if ($this->name)
			return sprintf('%s:%s',$this->getName(false),$this->id);
		else
			return 'none';
	}

	/**
	 * Return the template name
	 *
	 * @param boolean Force the name to be lowercase (default)
	 */
	public function getName($lower=true) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->name);

		if ($lower)
			return strtolower($this->name);
		else
			return $this->name;
	}

	/**
	 * Get the Template read time.
	 */
	public function getReadTime() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->readtime);

		return $this->readtime;
	}

	/**
	 * Return this LDAP Server object
	 *
	 * @return object DataStore Server
	 */
	protected function getServer() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs);

		return $_SESSION[APPCONFIG]->getServer($this->getServerID());
	}

	/**
	 * Return the LDAP server ID
	 *
	 * @return int Server ID
	 */
	protected function getServerID() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->server_id);

		return $this->server_id;
	}

	/**
	 * Test if a template is of a type
	 *
	 * @return boolean
	 */
	public function isType($type) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs,$this->type);

		if ($this->type == $type)
			return true;
		else
			return false;
	}

	/**
	 * Return the template type
	 */
	public function getType() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->type);

		return $this->type;
	}

	/**
	 * Get template title
	 */
	public function getTitle() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! isset($this->title) && ! isset($this->description))
			return '';

		return isset($this->title) ? $this->title : $this->description;
	}

	/**
	 * Add another attribute to this template
	 *
	 * @return int Attribute ID
	 */
	public function addAttribute($name,$value,$source=null) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (! is_array($value))
			debug_dump_backtrace('Value should be an array()',1);

		$server = $this->getServer();

		# Initialise the Attribute Factory.
		$attribute_factory = new AttributeFactory();

		if (preg_match('/;/',$name))
			system_message(array(
				'title'=>'phpLDAPadmin doesnt support RFC3866.',
				'body'=>sprintf('%s {%s} (%s)','PLA might not do what you expect...',$name,(is_array($value) ? serialize($value) : $value)),
				'type'=>'warn'));

		# If there isnt a schema item for this attribute
		$attribute = $attribute_factory->newAttribute($name,$value,$server->getIndex(),$source);

		$attrid = $this->getAttrID($attribute->getName());

		if (is_null($attrid))
			array_push($this->attributes,$attribute);

		return $attribute;
	}

	/**
	 * Get the attribute names
	 *
	 * @return array Array of attributes Names
	 */
	public function getAttributeNames() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$result = array();

		foreach ($this->attributes as $attribute)
			array_push($result,$attribute->getName());

		return $result;
	}

	/**
	 * Get a specific Attribute
	 *
	 * @param string Name of attribute to retrieve
	 * @return object Attribute
	 */
	public function getAttribute($name) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		foreach ($this->attributes as $attribute)
			if (($attribute->getName() == strtolower($name)) || in_array(strtolower($name),$attribute->getAliases()))
				return $attribute;

		return null;
	}

	/**
	 * May be overloaded in other classes
	 */
	public function isAdminDisabled() {}
}
?>
