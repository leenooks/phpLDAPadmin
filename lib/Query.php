<?php
/**
 * Classes and functions for the query engine.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * Query Class
 *
 * @package phpLDAPadmin
 * @subpackage Queries
 */
class Query extends xmlTemplate {
	protected $description = '';
	public $results = array();

	/**
	 * Main processing to store the template.
	 *
	 * @param xmldata Parsed xmldata from xml2array object
	 */
	protected function storeTemplate($xmldata) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$server = $this->getServer();

		foreach ($xmldata['query'] as $xml_key => $xml_value) {
			if (DEBUG_ENABLED)
				debug_log('Foreach loop Key [%s] Value [%s]',4,0,__FILE__,__LINE__,__METHOD__,$xml_key,is_array($xml_value));

			switch ($xml_key) {

				# Build our attribute list from the DN and Template.
				case ('attributes'):
					if (DEBUG_ENABLED)
						debug_log('Case [%s]',4,0,__FILE__,__LINE__,__METHOD__,$xml_key);

					if (is_array($xmldata['query'][$xml_key])) {
						foreach ($xmldata['query'][$xml_key] as $tattrs) {
							foreach ($tattrs as $index => $details) {

								if (DEBUG_ENABLED)
									debug_log('Foreach tattrs Key [%s] Value [%s]',4,0,__FILE__,__LINE__,__METHOD__,
										$index,$details);

								# If there is no schema definition for the attribute, it will be ignored.
								if ($sattr = $server->getSchemaAttribute($index)) {
									if (is_null($attribute = $this->getAttribute($sattr->getName())))
										$attribute = $this->addAttribute($sattr->getName(false),array('values'=>array()));

									$attribute->show();
									$attribute->setXML($details);
								}
							}
						}
					}

					break;

				# Build our bases list from the DN and Template.
				case ('bases'):
					if (isset($xmldata['query'][$xml_key]['base']))
						if (is_array($xmldata['query'][$xml_key]['base']))
							$this->base = $xmldata['query'][$xml_key]['base'];
						else
							$this->base = array($xmldata['query'][$xml_key]['base']);
					else
						error(sprintf(_('In the XML file (%s), [%s] contains an unknown key.'),
							$this->filename,$xml_key),'error','index.php');

					$this->base = array_unique($this->base);
					break;

				default:
					if (DEBUG_ENABLED)
						debug_log('Case [%s]',4,0,__FILE__,__LINE__,__METHOD__,$xml_key);

					# Some key definitions need to be an array, some must not be:
					$allowed_arrays = array('');
					$storelower = array('');
					$storearray = array('');

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
			}
		}

		# Check we have some manditory items.
		foreach (array() as $key) {
			if (! isset($this->$key)
				|| (! is_array($this->$key) && ! trim($this->$key))) {

				$this->setInvalid(sprintf(_('Missing %s in the XML file.'),$key));
				break;
			}
		}
	}

	/**
	 * Accept will run the query and store the results in results()
	 */
	public function accept() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$server = $this->getServer();

		$query = array();
		$query['size_limit'] = get_request('size_limit','REQUEST',false,$_SESSION[APPCONFIG]->getValue('search','size_limit'));
		$query['format'] = get_request('format','REQUEST',false,$_SESSION[APPCONFIG]->getValue('search','display'));
		$query['orderby'] = get_request('orderby','REQUEST',false,'dn');

		# If this is a custom search, we need to populate are paramters
		if ($this->getID() == 'none') {
			$bases = get_request('base','REQUEST',false,null);
			$query['filter'] = get_request('filter','REQUEST',false,'objectClass=*');
			$query['scope'] = get_request('scope','REQUEST',false,'sub');
			$attrs = get_request('display_attrs','REQUEST');

			$attrs = preg_replace('/\s+/','',$attrs);
			if ($attrs)
				$query['attrs'] = explode(',',$attrs);
			else
				$query['attrs'] = array('*');

		} else {
			$bases = $this->base;
			$query['filter'] = $this->filter;
			$query['scope'] = $this->scope;
			$query['attrs'] = $this->getAttributeNames();
		}

		if (! $bases)
			$bases = $server->getBaseDN();
		elseif (! is_array($bases))
			$bases = explode('|',$bases);

		foreach ($bases as $base) {
			$query['base'] = $base;

			$time_start = utime();
			$this->results[$base] = $server->query($query,null);
			$time_end = utime();

			$this->resultsdata[$base]['time'] = round($time_end-$time_start,2);
			$this->resultsdata[$base]['scope'] = $query['scope'];
			$this->resultsdata[$base]['filter'] = $query['filter'];
			$this->resultsdata[$base]['attrs'] = $query['attrs'];

			if ($this->getAttrSortOrder() == 'dn')
				usort($this->results[$base],'pla_compare_dns');
			elseif ($this->getAttrSortOrder())
				masort($this->results[$base],$this->getAttrSortOrder());
		}
	}

	/**
	 * This is temporary to get around objects that use a DN for rendering, for example jpegPhoto
	 */
	public function setDN($dn) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs);

		$this->dn = $dn;
	}

	/**
	 * This is temporary to get around objects that use a DN for rendering, for example jpegPhoto
	 */
	public function getDN() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->dn);

		return $this->dn;
	}

	public function getDNEncode($url=true) {
		// @todo Be nice to do all this in 1 location
		if ($url)
			return urlencode(preg_replace('/%([0-9a-fA-F]+)/',"%25\\1",$this->dn));
		else
			return preg_replace('/%([0-9a-fA-F]+)/',"%25\\1",$this->dn);
	}

	public function getAttrSortOrder() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$result = array();

		if (count($this->attributes)) {
			masort($this->attributes,'ordersort');

			foreach ($this->attributes as $attribute)
				array_push($result,$attribute->getName());

		} else {
			$display = preg_replace('/,\s+/',',',get_request('orderby','REQUEST',false,'dn'));

			if (trim($display))
				$result = explode(',',$display);
		}

		return implode(',',$result);
	}

	public function getAttrDisplayOrder() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$result = array();

		if (count($this->attributes)) {
			masort($this->attributes,'order');

			foreach ($this->attributes as $attribute)
				array_push($result,$attribute->getName());

		} else {
			$display = preg_replace('/,\s+/',',',get_request('display_attrs','REQUEST',false,''));

			if (trim($display))
				$result = explode(',',$display);
		}

		# If our display order is empty, then dynamically build it
		if (! count($result)) {
			foreach ($this->results as $details)
				foreach ($details as $attrs)
					$result = array_merge($result,array_keys(array_change_key_case($attrs)));

			$result = array_unique($result);
			sort($result);
		}

		# Put the DN first
		array_unshift($result,'dn');
		$result = array_unique($result);

		return implode(',',$result);
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

	public function getDescription() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',5,1,__FILE__,__LINE__,__METHOD__,$fargs,$this->description);

		return $this->description;
	}
}
?>
