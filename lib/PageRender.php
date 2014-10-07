<?php
/**
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * PageRender class
 *
 * @package phpLDAPadmin
 * @subpackage Templates
 */
class PageRender extends Visitor {
	# Template ID
	protected $template_id;
	protected $template = null;
	# Object Variables
	protected $dn;
	protected $container;
	# Page number
	protected $page;

	public function __construct($server_id,$template_id) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->server_id = $server_id;
		$this->template_id = $template_id;
	}

	/**
	 * Dummy method...
	 */
	protected function visitAttribute() {}

	/**
	 * Get our templates applicable for this object
	 */
	protected function getTemplates() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return new Templates($this->server_id);
	}

	public function getTemplate() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->template;
	}

	public function getTemplateID() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		return $this->template->getID();
	}

	/**
	 * Initialise the PageRender
	 */
	public function accept() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (DEBUGTMP) printf('<font size=-2>%s:%s</font><br />',time(),__METHOD__);

		if ($this->template_id) {
			$templates = $this->getTemplates();
			$this->template = $templates->getTemplate($this->template_id);

			if ($this->dn)
				$this->template->setDN($this->dn);
			elseif ($this->container)
				$this->template->setContainer($this->container);

			$this->template->accept();

			# Process our <post> actions
			if (get_request('post_value','REQUEST'))
				foreach (get_request('post_value','REQUEST') as $attr => $values) {
					$attribute = $this->template->getAttribute($attr);

					if (! $attribute)
						debug_dump_backtrace(sprintf('There was a post_value for an attribute [%s], but it doesnt exist?',$attr),1);

					foreach ($values as $index)
						if ($attribute->getPostValue())
							$this->get('Post',$attribute,$index);
						else
							$this->get('AutoPost',$attribute,$index);
				}

			foreach ($this->template->getAttributes(true) as $attribute) {
				if (DEBUGTMP||DEBUGTMPSUB) printf('<font size=-2>* %s [Accept:%s]</font><br />',__METHOD__,get_class($attribute));

				$this->visit('',$attribute);
			}

			// Sort our attribute values for display, if we are the custom template.
			if ($this->template->getID() == 'none')
				$this->template->sort();
		}
	}

	public function drawTitle($title='Title') {
		printf('<h3 class="title">%s</h3>',$title);
	}

	public function drawSubTitle($subtitle=null) {
		if (is_null($subtitle))
			$subtitle = sprintf('%s: <b>%s</b>&nbsp;&nbsp;&nbsp;%s: <b>%s</b>',
				_('Server'),$this->getServer()->getName(),_('Distinguished Name'),$this->dn);

		printf('<h3 class="subtitle">%s</h3>',$subtitle);
	}

	public function setDN($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($this->container)
			system_message(array(
				'title'=>__METHOD__,
				'body'=>'CONTAINER set while setting DN',
				'type'=>'info'));

		$this->dn = $dn;
	}

	public function setContainer($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if ($this->dn)
			system_message(array(
				'title'=>__METHOD__,
				'body'=>'DN set while setting CONTAINER',
				'type'=>'info'));

		$this->container = $dn;
	}

	/**
	 * May be overloaded in other classes
	 */
	protected function getMode() {}
	protected function getModeContainer() {}

	/**
	 * Process our <post> arguments from the templates
	 */
	protected function getPostAttribute($attribute,$i) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$autovalue = $attribute->getPostValue();
		$args = explode(';',$autovalue['args']);
		$server = $this->getServer();
		$vals = $attribute->getValues();

		switch ($autovalue['function']) {
			/**
			 * Join will concatenate values with a string, similiar to explode()
			 * eg: =php.Join(-;%sambaSID%,%sidsuffix%)
			 *
			 * * arg 0
			 *   - character to use when joining the attributes
			 *
			 * * arg 1
			 *   - values to concatenate together. we'll explode %attr% values.
			 */
			case 'Join':
				preg_match_all('/%(\w+)(\|.+)?(\/[lU])?%/U',$args[1],$matchall);
				$matchattrs = $matchall[1];
				$char = $args[0];

				$values = array();
				$blank = 0;
				foreach ($matchattrs as $joinattr) {
						$attribute2 = $this->template->getAttribute($joinattr);

						if (! $attribute2) {
							if (($pv = get_request(strtolower($joinattr),'REQUEST')) && isset($pv[$attribute->getName()][$i])) {
								array_push($values,$pv[$attribute->getName()][$i]);

								if (! $pv[$attribute->getName()][$i])
									$blank++;

							} else {
								array_push($values,'');
								$blank++;
							}

						} elseif (count($attribute2->getValues()) == 0) {
							return;

						} elseif (count($attribute2->getValues()) != 1) {
							array_push($values,'');
							$blank++;

							system_message(array(
								'title'=>_('Invalid value count for [post] processing'),
								'body'=>sprintf('%s (<b>%s [%s]</b>)',_('Function() variable expansion can only handle 1 value'),
									$attribute->getName(false),count($attribute->getValues())),
								'type'=>'warn'));

						} else
							array_push($values,$attribute2->getValue(0));
				}

				# If all our value expansion results in blanks, we'll return no value
				if (count($matchattrs) == $blank)
					if (count($vals) > 1)
						$vals[$i] = null;
					else
						$vals = null;

				else
					$vals[$i] = implode($char,$values);

				break;

			/**
			 * PasswordEncrypt will encrypt a password
			 * eg: =php.PasswordEncrypt(%enc%;%userPassword%)
			 *
			 * This function will encrypt the users password "userPassword" using the "enc" method.
			 */
			case 'PasswordEncrypt':
				if (count($args) != 2) {
					system_message(array(
						'title'=>_('Invalid argument count for PasswordEncrypt'),
						'body'=>sprintf('%s (<b>%s</b>)',_('PasswordEncrypt() only accepts two arguments'),$autovalue['args']),
						'type'=>'warn'));

					return;
				}

				if (! $attribute->hasBeenModified())
					return;

				# Get the attribute.
				if (preg_match_all('/%(\w+)(\|.+)?(\/[lU])?%/U',strtolower($args[1]),$matchall)) {
					if (count($matchall[1]) != 1)
						system_message(array(
							'title'=>_('Invalid value count for PasswordEncrypt'),
							'body'=>sprintf('%s (<b>%s</b>)',_('Unable to get the attribute value for PasswordEncrypt()'),count($matchall[1])),
							'type'=>'warn'));

					$passwordattr = $matchall[1][0];
					$passwordvalue = $_REQUEST['new_values'][$passwordattr][$i];

				} else
					$passwordvalue = $args[1];

				if (! trim($passwordvalue) || in_array($passwordvalue,$attribute->getOldValues()))
					return;

				# Get the encoding
				if ($passwordattr && preg_match_all('/%(\w+)(\|.+)?(\/[lU])?%/U',strtolower($args[0]),$matchall)) {
					if (count($matchall[1]) != 1)
						system_message(array(
							'title'=>_('Invalid value count for PasswordEncrypt'),
							'body'=>sprintf('%s (<b>%s</b>)',_('Unable to get the attribute value for PasswordEncrypt()'),count($matchall[1])),
							'type'=>'warn'));

					$enc = $_REQUEST[$matchall[1][0]][$passwordattr][$i];

				} else
					$enc = $args[0];

				$enc = strtolower($enc);

				switch ($enc) {
					case 'lm':
						$sambapassword = new smbHash;
						$vals[$i] = $sambapassword->lmhash($passwordvalue);

						break;

					case 'nt':
						$sambapassword = new smbHash;
						$vals[$i] = $sambapassword->nthash($passwordvalue);

						break;

					default:
						$vals[$i] = pla_password_hash($passwordvalue,$enc);
				}

				$vals = array_unique($vals);

				break;

			default:
				$vals = $this->get('AutoPost',$attribute,$i);
		}

		if (! $vals || $vals == $attribute->getValues())
			return;

		$attribute->clearValue();

		if (! is_array($vals))
			$attribute->setValue(array($vals));
		else
			$attribute->setValue($vals);
	}

	/**
	 * This function is invoked if we dont know which template we should be using.
	 *
	 * @return string Template ID to be used or null if the user was presented with a list.
	 */
	protected function getTemplateChoice() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		# First work out our template
		$templates = $this->getTemplates();
		$template = $templates->getTemplate($this->template_id);

		# If the template we asked for is available
		if ($this->template_id === $template->getID()) {
			if (DEBUGTMP) printf('<font size=-2>%s:<u>%s</u></font><br />',__METHOD__,'Choosing the SELECTED template');

			return $this->template_id;

		# If there are no defined templates
		} elseif (count($templates->getTemplates($this->getMode(),$this->getModeContainer(),false)) <= 0) {
			if (DEBUGTMP) printf('<font size=-2>%s:<u>%s</u></font><br />',__METHOD__,'Choosing the DEFAULT template, no other template applicable');

			# Since getTemplate() returns a default template if the one we want doesnt exist, we can return $templates->getID(), it should be the default.
			if ($_SESSION[APPCONFIG]->getValue('appearance','disable_default_template') AND $this->getMode() == 'creation') {

				system_message(array(
					'title'=>_('No available templates'),
					'body'=>_('There are no available active templates for this container.'),
					'type'=>'warn'));

				return 'invalid';

			} else
				return $template->getID();

		# If there is only 1 defined template, and no default available, then that is our template.
		} elseif ((count($templates->getTemplates($this->getMode(),$this->getModeContainer(),true)) == 1) && ! $this->haveDefaultTemplate()) {
			if (DEBUGTMP) printf('<font size=-2>%s:<u>%s</u></font><br />',__METHOD__,'AUTOMATIC choosing a template, only 1 template applicable');

			$template = $templates->getTemplates($this->getMode(),$this->getModeContainer(),true);
			$template = array_shift($template);

			# Dont render the only available template if it is invalid.
			if (! $template->isInvalid())
				return $template->getID();
			else
				$this->drawTemplateChoice();

		} else {
			if (DEBUGTMP) printf('<font size=-2>%s:<u>%s</u></font><br />',__METHOD__,'SELECT a template to use.');

			# Propose the template choice
			$this->drawTemplateChoice();
		}

		# If we got here, then there wasnt a template.
		return null;
	}

	/** DRAW ATTRIBUTE NAME **/

	final protected function drawNameAttribute($attribute) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$href = sprintf('cmd.php?cmd=schema&server_id=%s&view=attributes&viewvalue=%s',
			$this->getServerID(),$attribute->getName());

		if (! $_SESSION[APPCONFIG]->getValue('appearance','show_schema_link') || !$_SESSION[APPCONFIG]->isCommandAvailable('script','schema'))
			printf('%s',_($attribute->getFriendlyName()));

		elseif ($attribute->getLDAPtype())
			printf('<a href="%s" title="%s: %s">%s</a>',
				htmlspecialchars($href),
				_('Click to view the schema definition for attribute type'),$attribute->getName(false),_($attribute->getFriendlyName()));
		else
			printf('<acronym title="%s">%s</acronym>',_('This attribute is not defined in the LDAP schema'),_($attribute->getFriendlyName()));

		if (DEBUGTMPSUB) printf(' <small>[%s]</small>',get_class($attribute));
	}

	/** ATTRIBUTE NOTES */

	protected function drawNotesAttribute($attribute) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$attr_note = '';

		foreach (array('NoteAlias','NoteRequired','NoteRDN','NoteHint','NoteRO') as $note) {
			$alias_note = $this->get($note,$attribute);

			if ($alias_note) {
				if (trim($attr_note))
					$attr_note .= ', ';

				$attr_note .= $alias_note;
			}
		}

		if ($attr_note)
			printf('<sup><small>%s</small></sup>',$attr_note);
	}

	protected function getNoteAliasAttribute($attribute) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		# Is there a user-friendly translation available for this attribute?
		$friendly_name = $attribute->getFriendlyName();

		if (strtolower($friendly_name) != $attribute->getName())
			return sprintf('<acronym title="%s: \'%s\' %s \'%s\'">%s</acronym>',
				_('Note'),$friendly_name,_('is an alias for'),$attribute->getName(false),_('alias'));
		else
			return '';
	}

	#@todo this function shouldnt re-calculate requiredness, it should be known in the template already - need to set the ldaptype when initiating the attribute.
	protected function getNoteRequiredAttribute($attribute) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$required_by = '';
		$sattr_required = '';

		# Is this attribute required by an objectClass ?
		$sattr = $this->getServer()->getSchemaAttribute($attribute->getName());
		if ($sattr)
			$sattr_required = $sattr->getRequiredByObjectClasses();

		if ($sattr_required) {
			$oc = $this->template->getAttribute('objectclass');

			if ($oc)
				foreach ($oc->getValues() as $objectclass) {
					# If this objectclass is in our required list
					if (in_array_ignore_case($objectclass,$sattr_required)) {
						$required_by .= sprintf('%s ',$objectclass);
						continue;
					}

					# If not, see if it is in our parent.
					$sattr = $this->getServer()->getSchemaObjectClass($objectclass);

					if (array_intersect($sattr->getParents(),$sattr_required))
						$required_by .= sprintf('%s ',$objectclass);
				}

			else
				debug_dump_backtrace('How can there be no objectclasses?',1);
		}

		if ($required_by)
			return sprintf('<acronym title="%s %s">%s</acronym>',_('Required attribute for objectClass(es)'),$required_by,_('required'));
		else
			return '';
	}

	protected function getNoteRDNAttribute($attribute) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		# Is this attribute required because its the RDN
		if ($attribute->isRDN())
			return sprintf('<acronym title="%s">rdn</acronym>',_('This attribute is required for the RDN.'));
		else
			return '';
	}

	protected function getNoteHintAttribute($attribute) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		# Is there a hint for this attribute
		if ($attribute->getHint())
			return sprintf('<acronym title="%s">%s</acronym>',_($attribute->getHint()),_('hint'));
		else
			return '';
	}

	protected function getNoteROAttribute($attribute) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		# Is this attribute is readonly
		if ($attribute->isReadOnly())
			return sprintf('<acronym title="%s">ro</acronym>',_('This attribute has been marked as Read Only.'));
		else
			return '';
	}
	/** DRAW HIDDEN VALUES **/

	/**
	 * Draw all hidden attributes
	 */
	final public function drawHiddenAttributes() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		foreach ($this->template->getAttributes(true) as $attribute)
			if ($attribute->hasbeenModified()) {
				if ($attribute->getValues())
					foreach ($attribute->getValues() as $index => $details)
						$this->draw('HiddenValue',$attribute,$index);

				# We are deleting this attribute, so we need to display an empty value
				else
					$this->draw('HiddenValue',$attribute,0);
			}
	}

	/**
	 * Draw specific hidden attribute
	 */
	final protected function drawHiddenValueAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$val = $attribute->getValue($i);

		printf('<input type="hidden" name="new_values[%s][%s]" id="new_values_%s_%s" value="%s" />',
			htmlspecialchars($attribute->getName()),$i,htmlspecialchars($attribute->getName()),$i,
			htmlspecialchars($val));
	}

	/** DRAW DISPLAYED OLD VALUES **/
	protected function drawOldValuesAttribute($attribute) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		foreach ($attribute->getValues() as $index => $details)
			$this->draw('OldValue',$attribute,$index);
	}

	final protected function drawOldValueAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		echo $attribute->getOldValue($i);
	}

	/** DRAW DISPLAYED CURRENT VALUES **/

	protected function drawCurrentValuesAttribute($attribute) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		for ($i=0;$i<$attribute->getValueCount();$i++) {
			if ($i > 0)
				echo '<br/>';

			$this->draw('CurrentValue',$attribute,$i);
		}
	}

	/**
	 * Draw the current specific value of an attribute
	 */
	final protected function drawCurrentValueAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);
		if (DEBUGTMPSUB) printf(' <small>[%s]</small>',__METHOD__);

		echo htmlspecialchars($attribute->getValue($i));
	}

	/**
	 * Draw a input value for an attribute - used in a form.
	 */
	protected function drawFormValueAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);
		if (DEBUGTMPSUB) printf(' <small>[%s]</small>',__METHOD__);

		if ($this->getServer()->isReadOnly() || $attribute->isReadOnly()
			|| ($attribute->isRDN() && $this->template->getType() != 'creation' && $i < count($attribute->getValues())))

			$this->draw('FormReadOnlyValue',$attribute,$i);
		else
			$this->draw('FormReadWriteValue',$attribute,$i);

		# Show the ADDVALUE DIV if the attribute can have more values, and we have rendered the last value
		if ($attribute->haveMoreValues() && $attribute->getValueCount() == $i+1)
			printf('<div id="ajADDVALUE%s"></div>',$attribute->getName());

		if ($attribute->getPostValue())
			printf('<input type="hidden" name="post_value[%s][]" value="%s"/>',$attribute->getName(),$i);
	}

	protected function drawFormReadOnlyValueAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$val = $attribute->getValue($i);

		printf('<input type="text" class="roval" name="new_values[%s][%s]" id="new_values_%s_%s" value="%s" readonly="readonly" />',
			htmlspecialchars($attribute->getName()),$i,htmlspecialchars($attribute->getName()),$i,htmlspecialchars($val));
	}

	protected function drawFormReadWriteValueAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$val = $attribute->getValue($i);

		if ($attribute->getHelper() || $attribute->getVerify())
			echo '<table cellspacing="0" cellpadding="0" border="0"><tr><td valign="top">';

		printf('<input type="text" class="value" name="new_values[%s][%s]" id="new_values_%s_%s" value="%s" %s%s %s %s/>',
			htmlspecialchars($attribute->getName()),$i,
			htmlspecialchars($attribute->getName()),$i,
			htmlspecialchars($val),
			$attribute->needJS('focus') ? sprintf('onfocus="focus_%s(this);" ',$attribute->getName()) : '',
			$attribute->needJS('blur') ? sprintf('onblur="blur_%s(this);" ',$attribute->getName()) : '',
			($attribute->getSize() > 0) ? sprintf('size="%s"',$attribute->getSize()) : '',
			($attribute->getMaxLength() > 0) ? sprintf('maxlength="%s"',$attribute->getMaxLength()) : '');

		if ($attribute->getHelper()) {
			echo '</td><td valign="top">';
			$this->draw('AttributeHelper',$attribute,$i);
			echo '</td></tr>';

		} elseif ($attribute->getVerify())
			echo '</td></tr>';

		if ($attribute->getVerify()) {
			printf('<tr><td><input type="text" class="value" name="new_values_verify[%s][%s]" id="new_values_verify_%s_%s" value="" %s %s/>',
				htmlspecialchars($attribute->getName()),$i,
				htmlspecialchars($attribute->getName()),$i,
				($attribute->getSize() > 0) ? sprintf('size="%s"',$attribute->getSize()) : '',
				($attribute->getMaxLength() > 0) ? sprintf('maxlength="%s"',$attribute->getMaxLength()) : '');

			echo '</td><td valign="top">';
			printf('(%s)',_('confirm'));
			echo '</td></tr>';
		}

		if ($attribute->getHelper() || $attribute->getVerify())
			echo '</table>';
	}

	/**
	 * Draw specific hidden binary attribute
	 */
	final protected function drawHiddenValueBinaryAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$val = $attribute->getValue($i);

		printf('<input type="hidden" name="new_values[%s][%s]" value="%s" />',
			htmlspecialchars($attribute->getName()),$i,base64_encode($val));
	}

	final protected function drawOldValueBinaryAttribute($attribute,$i) {
		# If we dont have a value, we'll just return;
		if (! $attribute->getOldValue($i))
			return;

		printf('<small>[%s]</small>',_('Binary Value'));
	}

	final protected function drawCurrentValueBinaryAttribute($attribute,$i) {
		printf('<small>[%s]</small>',_('Binary Value'));

		if (in_array($attribute->getName(),array('objectsid')))
			printf('<small> (%s)</small>', binSIDtoText($attribute->getValue(0)));
	}

	protected function drawFormReadOnlyValueBinaryAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$this->draw('CurrentValue',$attribute,$i);
		echo '<br/><br/>';

		$href = sprintf('download_binary_attr.php?server_id=%s&dn=%s&attr=%s&index=%s',
		$this->getServerID(),rawurlencode($this->template->getDN()),$attribute->getName(),$i);

		printf('<a href="%s"><img src="%s/save.png" alt="Save" /> %s</a>',
			htmlspecialchars($href),IMGDIR,_('download value'));

		echo '<br/>';
	}

	protected function drawFormReadWriteValueBinaryAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		if ($attribute->getValue($i)) {
			$this->draw('FormReadOnlyValue',$attribute,$i);

			if (! $attribute->isReadOnly() && $_SESSION[APPCONFIG]->isCommandAvailable('script','delete_attr'))
				printf('<a href="javascript:deleteAttribute(\'%s\',\'%s\',\'%s\');" style="color:red;"><img src="%s/trash.png" alt="Trash" /> %s</a>',
					$attribute->getName(),$attribute->getFriendlyName(),$i,IMGDIR,_('delete attribute'));

		} else {
			printf('<input type="file" class="value" name="new_values[%s][%s]" id="new_values_%s_%s" value="" %s%s %s %s/><br />',
				htmlspecialchars($attribute->getName()),$i,
				htmlspecialchars($attribute->getName()),$i,
				$attribute->needJS('focus') ? sprintf('onfocus="focus_%s(this);" ',$attribute->getName()) : '',
				$attribute->needJS('blur') ? sprintf('onblur="blur_%s(this);" ',$attribute->getName()) : '',
				($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '',
				($attribute->getMaxLength() > 0) ? 'maxlength="'.$attribute->getMaxLength().'"' : '');
		}
	}

	protected function drawFormReadWriteValueDateAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$val = $attribute->getValue($i);

		echo '<span style="white-space: nowrap;">';
		printf('<input type="text" class="value" id="new_values_%s_%s" name="new_values[%s][%s]" value="%s" %s%s %s %s/>&nbsp;',
			$attribute->getName(),$i,
			htmlspecialchars($attribute->getName()),$i,htmlspecialchars($val),
			$attribute->needJS('focus') ? sprintf('onfocus="focus_%s(this);" ',$attribute->getName()) : '',
			$attribute->needJS('blur') ? sprintf('onblur="blur_%s(this);" ',$attribute->getName()) : '',
			($attribute->getSize() > 0) ? sprintf('size="%s"',$attribute->getSize()) : '',
			($attribute->getMaxLength() > 0) ? sprintf('maxlength="%s"',$attribute->getMaxLength()) : '');

		$this->draw('SelectorPopup',$attribute,$i);
		echo '</span>'."\n";
	}

	protected function drawFormReadWriteValueDnAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$val = $attribute->getValue($i);

		if ($attribute->getHelper())
			echo '<table cellspacing="0" cellpadding="0"><tr><td valign="top">';

		$input_name = sprintf('new_values[%s][%s]',htmlspecialchars($attribute->getName()),$i);
		$id = sprintf('new_values_%s_%s',htmlspecialchars($attribute->getName()),$i);

		printf('<span style="white-space: nowrap;"><input type="text" class="value" name="%s" id="%s" value="%s" %s%s %s %s/>&nbsp;',
			$input_name,$id,htmlspecialchars($val),
			$attribute->needJS('focus') ? sprintf('onfocus="focus_%s(this);" ',$attribute->getName()) : '',
			$attribute->needJS('blur') ? sprintf('onblur="blur_%s(this);" ',$attribute->getName()) : '',
			($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '',
			($attribute->getMaxLength() > 0) ? 'maxlength="'.$attribute->getMaxLength().'"' : '');

		# Draw a link for popping up the entry browser if this is the type of attribute that houses DNs.
		draw_chooser_link('entry_form',$id,false);
		echo '</span>';

		if ($attribute->getHelper()) {
			echo '</td><td valign="top">';
			$this->draw('Helper',$attribute,$i);
			echo '</td></tr></table>';
		}

		echo "\n";
	}

	protected function drawFormReadWriteValueGidAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$this->drawFormReadWriteValueAttribute($attribute,$i);

		$server = $this->getServer();
		$val = $attribute->getValue($i);

		# If this is a gidNumber on a non-PosixGroup entry, lookup its name and description for convenience
		if ($this->template->getDN() && ! in_array_ignore_case('posixGroup',$this->getServer()->getDNAttrValue($this->template->getDN(),'objectclass'))) {
			$query['filter'] = sprintf('(&(objectClass=posixGroup)(gidNumber=%s))',$val);
			$query['attrs'] = array('dn','description');

			# Reorganise our base, so that our base is first
			$bases = array_unique(array_merge(array($server->getContainerTop($this->template->getDN())),$server->getBaseDN()));

			# Search our bases, until we find a match.
			foreach ($bases as $base) {
				$query['base'] = $base;
				$group = $this->getServer()->query($query,null);

				if (count($group) > 0) {
					echo '<br />';

					$group = array_pop($group);
					$group_dn = $group['dn'];
					$group_name = explode('=',get_rdn($group_dn));
					$group_name = $group_name[1];
					$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',
						$this->getServerID(),rawurlencode($group_dn));

					echo '<small>';
					printf('<a href="%s">%s</a>',htmlspecialchars($href),$group_name);

					$description = isset($group['description']) ? $group['description'] : null;

					if (is_array($description))
						foreach ($description as $item)
							printf(' (%s)',$item);
					else
						printf(' (%s)',$description);

					echo '</small>';

					break;
				}
			}
		}
	}

	/**
	 * Draw a Jpeg Attribute
	 */
	final protected function drawOldValueJpegAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);
		if (DEBUGTMPSUB) printf(' <small>[%s]</small>',__METHOD__);

		# If we dont have a value, we'll just return;
		if (! $attribute->getOldValue($i))
			return;

		draw_jpeg_photo($this->getServer(),$this->template->getDN(),$attribute->getName(),$i,false,false);
	}

	/**
	 * Draw a Jpeg Attribute
	 */
	final protected function drawCurrentValueJpegAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);
		if (DEBUGTMPSUB) printf(' <small>[%s]</small>',__METHOD__);

		# If we dont have a value, we'll just return;
		if (! $attribute->getValue($i))
			return;

		# If the attribute is modified, the new value needs to be stored in a session variable for the draw_jpeg_photo callback.
		if ($attribute->hasBeenModified()) {
			$_SESSION['tmp'][$attribute->getName()][$i] = $attribute->getValue($i);
			draw_jpeg_photo(null,$this->template->getDN(),$attribute->getName(),$i,false,false);
		} else
			draw_jpeg_photo($this->getServer(),$this->template->getDN(),$attribute->getName(),$i,false,false);
	}

	protected function drawFormReadOnlyValueJpegAttribute($attribute,$i) {
		$this->draw('HiddenValue',$attribute,$i);
		$_SESSION['tmp'][$attribute->getName()][$i] = $attribute->getValue($i);

		draw_jpeg_photo(null,$this->template->getDN(),$attribute->getName(),$i,false,false);
	}

	protected function drawFormReadOnlyValueMultiLineAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$val = $attribute->getValue($i);

		printf('<textarea class="roval" rows="%s" cols="%s" name="new_values[%s][%s]" id="new_values_%s_%s" readonly="readonly">%s</textarea>',
			($attribute->getRows() > 0) ? $attribute->getRows() : 5,
			($attribute->getCols() > 0) ? $attribute->getCols() : 100,
			htmlspecialchars($attribute->getName()),$i,
			htmlspecialchars($attribute->getName()),$i,
			$val);
	}

	protected function drawFormReadWriteValueMultiLineAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$val = $attribute->getValue($i);

		printf('<textarea class="value" rows="%s" cols="%s" name="new_values[%s][%s]" id="new_values_%s_%s" %s%s>%s</textarea>',
			($attribute->getRows() > 0) ? $attribute->getRows() : 5,
			($attribute->getCols() > 0) ? $attribute->getCols() : 100,
			htmlspecialchars($attribute->getName()),$i,
			htmlspecialchars($attribute->getName()),$i,
			$attribute->needJS('focus') ? sprintf('onfocus="focus_%s(this);" ',$attribute->getName()) : '',
			$attribute->needJS('blur') ? sprintf('onblur="blur_%s(this);" ',$attribute->getName()) : '',
			$val);
	}

	protected function drawFormValueObjectClassAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$val = $attribute->getValue($i);

		/* It seems that openLDAP allows us to remove additional structural objectclasses
		   however other LDAP servers, dont remove them (even if we ask them to). */
		# Do we have our internal attributes.
		$internal = $this->template->getAttribute('structuralobjectclass');

		if ($internal) {
			$structural = in_array_ignore_case($val,$internal->getValues());

		# We'll work it out the traditional way.
		} else {
			# If this schema structural?
			$schema_object = ($val) ? $this->getServer()->getSchemaObjectClass($val) : false;
			$structural = (is_object($schema_object) && $schema_object->getType() == 'structural');
		}

		if ($structural) {
			$this->draw('FormReadOnlyValue',$attribute,$i);

			printf(' <small>(<acronym title="%s">%s</acronym>)</small>',
				_('This is a structural ObjectClass and cannot be removed.'),
				_('structural'));

		} else
			$this->draw('FormReadWriteValue',$attribute,$i);
	}

	protected function getAutoPostPasswordAttribute($attribute,$i) {
		# If the password is already encoded, then we'll return
		if (preg_match('/^\{.+\}.+/',$attribute->getValue($i)))
			return;

		$attribute->setPostValue(array('function'=>'PasswordEncrypt','args'=>sprintf('%%enc%%;%%%s%%',$attribute->getName())));
		$this->get('Post',$attribute,$i);
	}

	protected function drawOldValuePasswordAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);
		if (DEBUGTMPSUB) printf(' <small>[%s]</small>',__METHOD__);

		$val = $attribute->getOldValue($i);

		if (obfuscate_password_display(get_enc_type($val)))
			echo str_repeat('*',16);
		else
			echo nl2br(htmlspecialchars($attribute->getOldValue($i)));
	}

	final protected function drawCurrentValuePasswordAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);
		if (DEBUGTMPSUB) printf(' <small>[%s]</small>',__METHOD__);

		$val = $attribute->getValue($i);

		if (obfuscate_password_display(get_enc_type($val)))
			echo str_repeat('*',16);
		else
			echo nl2br(htmlspecialchars($attribute->getValue($i)));
	}

	protected function drawFormReadOnlyValuePasswordAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$server = $this->getServer();
		$val = $attribute->getValue($i);

		if (trim($val))
			$enc_type = get_enc_type($val);
		else
			$enc_type = $server->getValue('appearance','pla_password_hash');

		$obfuscate_password = obfuscate_password_display($enc_type);

		printf('<input type="%s" class="roval" name="new_values[%s][%s]" id="new_values_%s_%s" value="%s" %s readonly="readonly" /><br />',
			($obfuscate_password ? 'password' : 'text'),
			htmlspecialchars($attribute->getName()),$i,htmlspecialchars($attribute->getName()),
			$i,htmlspecialchars($val),($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '');

		if (trim($val))
			$this->draw('CheckLink',$attribute,'new_values_'.htmlspecialchars($attribute->getName()).'_'.$i);
	}

	protected function drawFormReadWriteValuePasswordAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$server = $this->getServer();
		$val = $attribute->getValue($i);

		$enc_type = get_enc_type($val);

		# Set the default hashing type if the password is blank (must be newly created)
		if (trim($val))
			$enc_type = get_enc_type($val);
		else
			$enc_type = $server->getValue('appearance','pla_password_hash');

		echo '<table cellspacing="0" cellpadding="0"><tr><td valign="top">';

		$obfuscate_password = obfuscate_password_display($enc_type);
		$id = sprintf('new_values_%s_%s',htmlspecialchars($attribute->getName()),$i);

		printf('<input type="%s" class="value" name="new_values[%s][%s]" id="%s" value="%s" %s%s %s %s/>',
			($obfuscate_password ? 'password' : 'text'),
			htmlspecialchars($attribute->getName()),$i,$id,
			htmlspecialchars($val),
			$attribute->needJS('focus') ? sprintf('onfocus="focus_%s(this);" ',$attribute->getName()) : '',
			$attribute->needJS('blur') ? sprintf('onblur="blur_%s(this);" ',$attribute->getName()) : '',
			($attribute->getSize() > 0) ? sprintf('size="%s"',$attribute->getSize()) : '',
			($attribute->getMaxLength() > 0) ? sprintf('maxlength="%s"',$attribute->getMaxLength()) : '');

		echo '</td><td valign="top">';

		if ($attribute->getHelper())
			$this->draw('Helper',$attribute,$i);
		else
			$this->draw('DefaultHelper',$attribute,$i);

		echo '</td></tr><tr><td valign="top">';

		if ($attribute->getVerify() && $obfuscate_password) {
			printf('<input type="password" class="value" name="new_values_verify[%s][%s]" id="new_values_verify_%s_%s" value="" %s %s/>',
				htmlspecialchars($attribute->getName()),$i,
				htmlspecialchars($attribute->getName()),$i,
				($attribute->getSize() > 0) ? sprintf('size="%s"',$attribute->getSize()) : '',
				($attribute->getMaxLength() > 0) ? sprintf('maxlength="%s"',$attribute->getMaxLength()) : '');

			echo '</td><td valign="top">';
			printf('(%s)',_('confirm'));
			echo '</td></tr><tr><td valign="top">';
		}

		$this->draw('CheckLink',$attribute,$id);
		echo '</td></tr></table>';
	}

	protected function drawFormReadWriteValueSelectionAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		if ($attribute->isMultiple()) {
			# For multiple selection, we draw the component only one time
			if ($i > 0)
				return;

			$selected = array();
			$vals = $attribute->getValues();
			$j = 0;

			if (! $vals && ! is_null($attribute->getDefault()) && ! is_array($vals = $attribute->getDefault()))
				$vals = array($attribute->getDefault());

			if (($attribute->getSize() > 0) && ($attribute->getSize() < $attribute->getOptionCount())) {

				printf('<select name="new_values[%s][]" size="%s" multiple="multiple">',
					htmlspecialchars($attribute->getName()),$attribute->getSize());

				foreach ($attribute->getSelection() as $value => $description) {
					if (in_array($value,$vals))
						$selected[$value] = true;

					printf('<option id="new_values_%s_%s" value="%s" onmouseDown="focus_%s(this);" onclick="blur_%s(this);" %s>%s</option>',
						htmlspecialchars($attribute->getName()),$j++,
						$value,htmlspecialchars($attribute->getName()),htmlspecialchars($attribute->getName()),
						isset($selected[$value]) ? 'selected="selected"' : '',$description);

					echo "\n";
				}

				foreach ($vals as $val) {
					if (! isset($selected[$val]))
						printf('<option id="new_values_%s_%s" value="%s" onmousedown="focus_%s(this);" onclick="blur_%s(this);" selected="selected">%s</option>',
							htmlspecialchars($attribute->getName()),$j++,
							$val,htmlspecialchars($attribute->getName()),
							htmlspecialchars($attribute->getName()),$val);

					echo "\n";
				}

				echo '</select>';

			} else {
				echo '<table cellspacing="0" cellpadding="0" border="0">';

				// For checkbox items, we need to render a blank entry, so that we detect an all-unselect situation
				printf('<tr><td colspan="2"><input type="hidden" id="new_values_%s_%s" name="new_values[%s][]" value="%s"/></td></tr>',
					htmlspecialchars($attribute->getName()),$j++,
					htmlspecialchars($attribute->getName()),'');

				foreach ($attribute->getSelection() as $value => $description) {
					if (in_array($value,$vals))
						$selected[$value] = true;

					printf('<tr><td><input type="checkbox" id="new_values_%s_%s" name="new_values[%s][]" value="%s" %s%s %s/></td><td><span style="white-space: nowrap;">&nbsp;%s</span></td></tr>',
						htmlspecialchars($attribute->getName()),$j++,
						htmlspecialchars($attribute->getName()),$value,
						$attribute->needJS('focus') ? sprintf('onfocus="focus_%s(this);" ',$attribute->getName()) : '',
						$attribute->needJS('blur') ? sprintf('onblur="blur_%s(this);" ',$attribute->getName()) : '',
						isset($selected[$value]) ? 'checked="checked"' : '',
						$description);
				}

				foreach ($vals as $val)
					if (! isset($selected[$val]))
						printf('<tr><td><input type="checkbox" id="new_values_%s_%s" name="new_values[%s][]" value="%s" %s%s checked="checked"/></td><td><span style="white-space: nowrap;">&nbsp;%s</span></td></tr>',
							htmlspecialchars($attribute->getName()),$j++,
							htmlspecialchars($attribute->getName()),$val,
							$attribute->needJS('focus') ? sprintf('onfocus="focus_%s(this);" ',$attribute->getName()) : '',
							$attribute->needJS('blur') ? sprintf('onblur="blur_%s(this);" ',$attribute->getName()) : '',
							$val);

				echo '</table>';
			}

		# This is a single value attribute
		} else {
			$val = $attribute->getValue($i) ? $attribute->getValue($i) : $attribute->getDefault();

			if ($attribute->getHelper())
				echo '<table cellspacing="0" cellpadding="0"><tr><td valign="top">';

			$found = false;
			$empty_value = false;

			# If we are a required attribute, and the selection is blank, then the user cannot submit this form.
			if ($attribute->isRequired() && ! count($attribute->getSelection()))
				system_message(array(
					'title'=>_('Template Value Error'),
					'body'=>sprintf('This template uses a selection list for attribute [<b>%s</b>], however the selection list is empty.<br />You may need to create some dependancy entries in your LDAP server so that this attribute renders with values. Alternatively, you may be able to define the appropriate selection values in the template file.',$attribute->getName(false)),
					'type'=>'warn'));

			printf('<select name="new_values[%s][]" id="new_values_%s_%s" %s%s>',
				htmlspecialchars($attribute->getName()),
				htmlspecialchars($attribute->getName()),$i,
				$attribute->needJS('focus') ? sprintf('onfocus="focus_%s(this);" ',$attribute->getName()) : '',
				$attribute->needJS('blur') ? sprintf('onblur="blur_%s(this);" ',$attribute->getName()) : '');

			foreach ($attribute->getSelection() as $value => $description) {
				printf('<option value="%s" %s>%s</option>',$value,
					((strcasecmp($value,$val) == 0) && $found = true) ? 'selected="selected"' : '',$description);

				if ($value == '')
					$empty_value = true;

				echo "\n";
			}

			if (!$found) {
				printf('<option value="%s" selected="selected">%s</option>',$val,$val == '' ? '&nbsp;' : $val);
				if ($val == '')
					$empty_value = true;
				echo "\n";
			}

			if ((strlen($val) > 0) && ! $empty_value && $this->template->getDN()) {
				printf('<option value="">(%s)</option>',_('none, remove value'));
				echo "\n";
			}
			echo '</select>';

			if ($attribute->getHelper()) {
				echo '</td><td valign="top">';
				$this->draw('Helper',$attribute,$i);
				echo '</td></tr></table>';
			}
		}
	}

	/**
	 * Takes a shadow* attribute and returns the date as an integer.
	 *
	 * @param array Attribute objects
	 * @param string A shadow attribute name
	 */
	private function shadow_date($attribute) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',129,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$shadowattr = array();
		$shadowattr['lastchange'] = $this->template->getAttribute('shadowlastchange');
		$shadowattr['max'] = $this->template->getAttribute('shadowmax');

		$shadow = array();
		$shadow['lastchange'] = $shadowattr['lastchange'] ? $shadowattr['lastchange']->getValue(0) : null;
		$shadow['max'] = $shadowattr['max'] ? $shadowattr['max']->getValue(0) : null;

		if (($attribute->getName() == 'shadowlastchange') && $shadow['lastchange'])
			$shadow_date = $shadow['lastchange'];

		elseif (($attribute->getName() == 'shadowmax') && ($shadow['max'] > 0) && $shadow['lastchange'])
			$shadow_date = $shadow['lastchange']+$shadow['max'];

		elseif (($attribute->getName() == 'shadowwarning') && ($attribute->getValue(0) > 0)
			&& $shadow['lastchange'] && $shadow['max'] && $shadow['max'] > 0)
			$shadow_date = $shadow['lastchange']+$shadow['max']-$attribute->getValue(0);

		elseif (($attribute->getName() == 'shadowinactive') && ($attribute->getValue(0) > 0)
			&& $shadow['lastchange'] && $shadow['max'] && $shadow['max'] > 0)
			$shadow_date = $shadow['lastchange']+$shadow['max']+$attribute->getValue(0);

		elseif (($attribute->getName() == 'shadowmin') && ($attribute->getValue(0) > 0) && $shadow['lastchange'])
			$shadow_date = $shadow['lastchange']+$attribute->getValue(0);

		elseif (($attribute->getName() == 'shadowexpire') && ($attribute->getValue(0) > 0))
			$shadow_date = $shadowattr->getValue(0);

		# Couldn't interpret the shadow date (could be 0 or -1 or something)
		else
			return false;

		return $shadow_date*24*3600;
	}

	protected function drawShadowDateShadowAttribute($attribute) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$shadow_before_today_attrs = arrayLower($attribute->shadow_before_today_attrs);
		$shadow_after_today_attrs = arrayLower($attribute->shadow_after_today_attrs);
		$shadow_date = $this->shadow_date($attribute);

		if (! $shadow_date)
			return;

		$today = date('U');

		echo '<br/><small>';
		if (($today < $shadow_date) && in_array(strtolower($attribute->getName()),$shadow_before_today_attrs))
			printf('<span style="color:red">(%s)</span>',
				strftime($_SESSION[APPCONFIG]->getValue('appearance','date'),$shadow_date));

		elseif (($today > $shadow_date) && in_array(strtolower($attribute->getName()),$shadow_after_today_attrs))
			printf('<span style="color:red">(%s)</span>',
				strftime($_SESSION[APPCONFIG]->getValue('appearance','date'),$shadow_date));

		else
			printf('(%s)',
				strftime($_SESSION[APPCONFIG]->getValue('appearance','date'),$shadow_date));

		echo '</small><br />';
	}

	protected function drawFormReadOnlyValueShadowAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$this->drawFormReadOnlyValueAttribute($attribute,$i);
		$this->draw('ShadowDate',$attribute);
	}

	protected function drawFormReadWriteValueShadowAttribute($attribute,$i) {
		if (DEBUGTMP) printf('<font size=-2>%s</font><br />',__METHOD__);

		$this->drawFormReadWriteValueAttribute($attribute,$i);
		$this->draw('ShadowDate',$attribute);
	}
}
?>
