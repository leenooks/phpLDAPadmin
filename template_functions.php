<?php
/* $Header: /cvsroot/phpldapadmin/phpldapadmin/template_functions.php,v 1.25 2005/09/25 16:11:44 wurley Exp $ */

/**
 * Classes and functions for the template engine.ation and capability
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 * @todo: Should be able to auto figure what type of entry we are asking for ie: DN entry.
 */

class xml2array {
	var $stack = array();
	var $stack_ref;
	var $arrOutput = array();
	var $resParser;
	var $strXmlData;

	function push_pos(&$pos) {
		$this->stack[count($this->stack)] = &$pos;
		$this->stack_ref = &$pos;
	}

	function pop_pos() {
		unset($this->stack[count($this->stack) - 1]);
		$this->stack_ref = &$this->stack[count($this->stack) - 1];
	}

	function parse($file) {
		$f = fopen($file,'r');
		$strInputXML = fread($f,filesize($file));
		fclose($f);

		$this->resParser = xml_parser_create();
		xml_set_object($this->resParser,$this);
		xml_set_element_handler($this->resParser,"tagOpen","tagClosed");

		xml_set_character_data_handler($this->resParser,"tagData");

		$this->push_pos($this->arrOutput);

		$this->strXmlData = xml_parse($this->resParser,$strInputXML);

		if (! $this->strXmlData)
			die(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($this->resParser)),
				xml_get_current_line_number($this->resParser)));

		xml_parser_free($this->resParser);

		return $this->arrOutput;
	}

	function tagOpen($parser,$name,$attrs) {
		$name = strtolower($name);

		if (isset($this->stack_ref[$name])) {
			if (! isset($this->stack_ref[$name][0])) {
				$tmp = $this->stack_ref[$name];
				unset($this->stack_ref[$name]);
				$this->stack_ref[$name][0] = $tmp;
			}

			$cnt = count($this->stack_ref[$name]);
			$this->stack_ref[$name][$cnt] = array();
			if (isset($attrs))
				$this->stack_ref[$name][$cnt] = $attrs;

			$this->push_pos($this->stack_ref[$name][$cnt]);

		} else {
			$this->stack_ref[$name]=array();

     			if (isset($attrs))
				$this->stack_ref[$name]=$attrs;

			$this->push_pos($this->stack_ref[$name]);
		}
	}

	function tagData($parser,$tagData) {
		if (trim($tagData) != '') {

			if (isset($this->stack_ref['#text']))
				$this->stack_ref['#text'] .= $tagData;
			else
				$this->stack_ref['#text'] = $tagData;
		}
	}

	function tagClosed($parser,$name) {
		$this->pop_pos();
	}
}

class Templates {

	var $_template = array();

	function Templates($server_id) {
		debug_log(sprintf('%s::init(): Entered with ()',get_class($this)),2);

		if ($this->_template = get_cached_item($server_id,'template','all')) {
			debug_log(sprintf('%s::init(): Using CACHED [%s]',get_class($this),'templates'),3);
		} else {

			$dir = opendir(TMPLDIR);

			$this->template_num = 0;
			while( ( $file = readdir( $dir ) ) !== false ) {
				if (! preg_match('/.xml$/',$file)) continue;

				$objXML = new xml2array();
				$xmldata = $objXML->parse(TMPLDIR.$file);

				$template_name = preg_replace('/.xml$/','',$file);

				$this->storeTemplate($template_name,$xmldata);
			}
			masort($this->_template,'title');
			set_cached_item($server_id,'template','all',$this->_template);
		}
	}

	function storeTemplate($template,$xmldata) {
		debug_log(sprintf('%s::storeTemplate(): Entered with (%s,%s)',get_class($this),$template,serialize($xmldata)),2);

		global $ldapserver, $lang;

		foreach ($xmldata['template'] as $xml_key => $xml_value) {
			debug_log(sprintf('%s::storeTemplate(): Foreach loop Key [%s] Value [%s]',get_class($this),$xml_key,is_array($xml_value)),9);

			switch ($xml_key) {
				# Build our object Classes from the DN and Template.
				case ('objectclasses') :
					if (isset($xmldata['template']['objectclasses']) && is_array($xmldata['template']['objectclasses'])) {
						foreach ($xmldata['template']['objectclasses']['objectclass'] as $index => $details) {

							if (is_numeric($index)) {
								if (! isset($this->_template[$template]['objectclass']) ||
									! in_array($details['ID'],$this->_template[$template]['objectclass']))

									$this->_template[$template]['objectclass'][] = $details['ID'];

							} else {
								if (! isset($this->_template[$template]['objectclass']) ||
									! in_array($xmldata['template']['objectclasses']['objectclass']['ID'],$this->_template[$template]['objectclass']))

									$this->_template[$template]['objectclass'][] = $xmldata['template']['objectclasses']['objectclass']['ID'];
							}
						}
					}

					break;

				# Build our attribute list from the DN and Template.
				case ('attributes') :
					debug_log(sprintf('%s::storeTemplate(): Case [%s]',get_class($this),'attributes'),8);

					if (isset($xmldata['template']['attributes']) && is_array($xmldata['template']['attributes'])) {
						$this->_template[$template]['attribute'] = array();

						foreach ($xmldata['template']['attributes'] as $tattrs) {
							foreach ($tattrs as $index => $attr_details) {

								debug_log(sprintf('%s::storeTemplate(): Foreach tattrs Key [%s] Value [%s]',get_class($this),$index,is_array($attr_details)),9);

								# Single attribute XML files are not indexed.
								if (! is_numeric($index)) {
									$this->_template[$template]['attribute'][$tattrs['ID']][$index] = $this->_parseXML($index,$attr_details);

								} else {
									foreach ($attr_details as $key => $values) {
										if (preg_match('/^@/',$key))
											continue;

										if (isset($values['ID']))
											$key = $values['ID'];

										$this->_template[$template]['attribute'][$attr_details['ID']][$key] = $this->_parseXML($key,$values);
									}
								}
							}
						}

						# Do we have an override parameter?
						foreach ($this->_template[$template]['attribute'] as $key => $data) {
							if (isset($data['override'])) {
								$this->_template[$template]['attribute'][$data['override']] = $data;
								unset($this->_template[$template]['attribute'][$key]);
								$this->_template[$template]['attribute'][$key] = $data['override'];
							}
						}
						#if (isset($this->_template[$template]['attribute']);
					}

					break;

				default :
					$this->_template[$template][$xml_key] = $xml_value['#text'];
			}
		}

		# Collect our structural, must & may attributes.
		$this->_template[$template]['must'] = array();
		$this->_template[$template]['may'] = array();
		$this->_template[$template]['empty_attrs'] = array();

		$superclasslist = array();
		foreach ($this->_template[$template]['objectclass'] as $oclass) {

			# If we get some superclasses - then we'll need to go through them too.
			$supclass = true;
			$enherited = false;
			while ($supclass == true) {
				$schema_object = get_schema_objectclass( $ldapserver, $oclass);

				# Test that this is a valid objectclass - disable if an invalid one found.
				if (! $schema_object)
					$this->_template[$template]['invalid'] = 1;

				if ($schema_object->type == 'structural' && (! $enherited))
					$this->_template[$template]['structural'][] = $oclass;

				if ($schema_object->must_attrs )
					foreach ($schema_object->must_attrs as $index => $detail)
						if (! in_array($detail->name,$this->_template[$template]['must']) && $detail->name != 'objectClass') {
							if (isset($this->_template[$template]['attribute'][$detail->name]) &&
								! is_array($this->_template[$template]['attribute'][$detail->name]))

								$this->_template[$template]['must'][] = 
									$this->_template[$template]['attribute'][$detail->name];
							else
								$this->_template[$template]['must'][] = $detail->name;
						}

				if ($schema_object->may_attrs )
					foreach ($schema_object->may_attrs as $index => $detail)
						if (! in_array($detail->name,$this->_template[$template]['may']))
							$this->_template[$template]['may'][] = $detail->name;

				# Keep a list to objectclasses we have processed, so we dont get into a loop.
				$oclass_processed[] = $oclass;

				if ((count($schema_object->sup_classes)) || count($superclasslist)) {
					foreach ($schema_object->sup_classes as $supoclass) {
						if (! in_array($supoclass,$oclass_processed))
							$supoclasslist[] = $supoclass;
					}

					$oclass = array_shift($supoclasslist);
					if ($oclass)
						$enherited = true;
					else
						$supclass = false;

				} else {
					$supclass = false;
				}
			}
		}

		# Translate anything.
		foreach (array('title','description','display','hint') as $transkey) {
			if (isset($this->_template[$template][$transkey]) && isset($lang[$this->_template[$template][$transkey]]))
				$this->_template[$template][$transkey] = $lang[$this->_template[$template][$transkey]];

			foreach ($this->_template[$template]['attribute'] as $key => $value) {
				if (isset($value[$transkey]) && isset($lang[$value[$transkey]]))
					$this->_template[$template]['attribute'][$key][$transkey] = $lang[$value[$transkey]];

				if (isset($value['helper'][$transkey]) && isset($lang[$value['helper'][$transkey]]))
					$this->_template[$template]['attribute'][$key]['helper'][$transkey] = $lang[$value['helper'][$transkey]];
			}
		}

		# Remove any must attributes in the may list.
		foreach ($this->_template[$template]['may'] as $index => $detail) {
			if (in_array($detail,$this->_template[$template]['must'])) {
				unset($this->_template[$template]['may'][$index]);
				continue;
			}
		}

		# Remove any attributes not in the xml file and not in the dn.
		foreach ($this->_template[$template]['may'] as $index => $detail) {
			if (isset($this->_template[$template]['attribute'])
				&& ! isset($this->_template[$template]['attribute'][$detail])) {

				unset($this->_template[$template]['may'][$index]);
				continue;
			}

			if (! isset($attrs[$detail]))
				if (isset($this->_template[$template]['attribute'][$detail]))
					$this->_template[$template]['empty_attrs'][$detail] = $this->_template[$template]['attribute'][$detail];
				else
					$this->_template[$template]['empty_attrs'][$detail]['display'] = $detail;

			else
				$this->_template[$template]['attrs'][$detail] = $attrs[$detail];
		}

		# Add the must attrs to the attributes key.
		foreach ($this->_template[$template]['must'] as $index => $detail) {

			if (! isset($attrs[$detail])) {
				if (isset($this->_template[$template]['attribute'][$detail]))
					$this->_template[$template]['empty_attrs'][$detail] = $this->_template[$template]['attribute'][$detail];
				else
					$this->_template[$template]['empty_attrs'][$detail]['display'] = $detail;

				$this->_template[$template]['empty_attrs'][$detail]['must'] = true;
			} else
				$this->_template[$template]['attrs'][$detail] = $attrs[$detail];
		}

		# Check if there are any items without a page or order parameter, and make it 1 and 255.
		foreach ($this->_template[$template]['empty_attrs'] as $index => $detail) {
			if (! isset($detail['page']))
				$this->_template[$template]['empty_attrs'][$index]['page'] = 1;
			if (! isset($detail['order']))
				$this->_template[$template]['empty_attrs'][$index]['order'] = 255;
		}

		# Check we have some manditory items.
		foreach (array('rdn','structural','visible') as $key) {
			if (! isset($this->_template[$template][$key])
				|| (! is_array($this->_template[$template][$key]) && ! trim($this->_template[$template][$key]))) {

				//unset($this->_template[$template]);
				$this->_template[$template]['invalid'] = 1;
				break;
			}
		}
	}

	function _parseXML($index,$attr_details) {
		debug_log(sprintf('%s::_parseXML(): Entered with (%s,%s)',get_class($this),$index,serialize($attr_details)),2);

		if (! $attr_details) {
			return "";

		} elseif (isset($attr_details['#text'])) {
			return $attr_details['#text'];
		}

		foreach ($attr_details as $key => $values) {
			$parseXML[$key] = $this->_parseXML($index,$values);
		}

		return $parseXML;
	}

	function getTemplate($template) {
		return isset($this->_template[$template]) ? $this->_template[$template] : null;
	}

	function OnChangeAdd($function) {
		debug_log(sprintf('%s::OnChangeAdd(): Entered with (%s)',get_class($this),$function),2);

		global $js;

		list($command,$arg) = split(':',$function);

		switch ($command) {
			#autoFill:attr,string (with %attr%)
			#@todo: The autofill mods need to be more flexible, so that multiple can be used eg: /T/l
			case 'autoFill' :
				list($attr,$string) = split(',',$arg);
				preg_match_all('/%(\w+)(\|[0-9]*-[0-9]*)?(\/[lTU])?%/U',$string,$matchall);
				//print"<PRE>";print_r($matchall); //0 = highlevel match, 1 = attr, 2 = subst, 3 = mod

				$html = sprintf('autoFill%s(this.form)',$attr);

				if (! isset($js["autoFill".$attr]) ) {

					$js["autoFill".$attr] = sprintf("\nfunction autoFill%s( form ) {\n",$attr);
					$formula = $string;
					$formula = preg_replace('/^([^%])/','\'$1',$formula);
					$formula = preg_replace('/([^%])$/','$1\'',$formula);

					foreach ($matchall[0] as $index => $null) {
						$substrarray = array();

						$js["autoFill".$attr] .= sprintf("	var %s;\n",$matchall[1][$index]);

						if (trim($matchall[2][$index])) {
							preg_match_all('/([0-9]*)-([0-9]*)/',$matchall[2][$index],$substrarray);
						}

						if ($matchall[3][$index] == "/T") {
							$js["autoFill".$attr] .= sprintf("	%s = form.%s.options[form.%s.selectedIndex].text;\n",
								$matchall[1][$index],$matchall[1][$index],$matchall[1][$index]);

						} else {

							if ((isset($substrarray[1][0]) && $substrarray[1][0]) || (isset($substrarray[2][0]) && $substrarray[2][0])) {
								$js["autoFill".$attr] .= sprintf("	%s = form.%s.value.substr(%s,%s)",
									$matchall[1][$index],$matchall[1][$index],
									$substrarray[1][0] ? $substrarray[1][0] : '0',
									$substrarray[2][0] ? $substrarray[2][0] : sprintf('form.%s.value.length',$matchall[1][$index]));

							} else {
								$js["autoFill".$attr] .= sprintf("	%s = form.%s.value",$matchall[1][$index],$matchall[1][$index]);
							}

							switch ($matchall[3][$index]) {
								case '/l':
									$js["autoFill".$attr] .= ".toLowerCase()";
									break;
							}
							$js["autoFill".$attr] .= ";\n";
						}

						$formula = preg_replace('/^%('.$matchall[1][$index].')(\|[0-9]*-[0-9]*)?(\/[lTU])?%/U','$1 + \'',$formula);
						$formula = preg_replace('/%('.$matchall[1][$index].')(\|[0-9]*-[0-9]*)?(\/[lTU])?%$/U','\' + $1 ',$formula);
						$formula = preg_replace('/%('.$matchall[1][$index].')(\|[0-9]*-[0-9]*)?(\/[lTU])?%/U','\' + $1 + \'',$formula);
					}

					$js["autoFill".$attr] .= sprintf("	form.%s.value = %s;\n",$attr,$formula);
					$js["autoFill".$attr] .= "}\n";
				}

				break;

			default: $html = '';
		}
		return $html;
	}

	function OnChangeDisplay() {
		global $js;

		return (isset($js) ? implode("\n",$js) : '');
	}

	function EvaluateDefault($ldapserver,$default,$container,$counter='') {
		debug_log(sprintf('%s::EvaluateDefault(): Entered with (%s,%s,%s,%s)',
			get_class($this),$ldapserver->server_id,$default,$container,$counter),2);

		global $lang;

		if (preg_match('/^=php\.(\w+)\((.*)\)$/',$default,$matches)) {
			$args = preg_split('/,/',$matches[2]);

			switch($matches[1]) {
				case 'GetNextNumber' :
					$container = get_container_parent ($container, $args[0]);

					$detail['default'] = get_next_uid_number($ldapserver, $container, $args[1]);
					break;

				case 'PickList' :
					$container = get_container_parent ($container, $args[0]);
					preg_match_all('/%(\w+)(\|.+)?(\/[lU])?%/U',$args[3],$matchall);
					//print_r($matchall); // -1 = highlevel match, 1 = attr, 2 = subst, 3 = mod

					$ldap_attrs = $matchall[1];
					array_push($ldap_attrs,$args[2]);
					$picklistvalues = return_ldap_hash($ldapserver,$container,$args[1],$args[2],$ldap_attrs);

					$detail['default'] = sprintf('<select name="form[%s]" id="%%s" %%s %%s/>',$args[2]);
					foreach ($picklistvalues as $key => $values) {
						$display = $args[3];

						foreach ($matchall[1] as $arg) {
							$display = preg_replace('/%('.$arg.')(\|.+)?(\/[lU])?%/U',$values[$arg],$display);
						}

						if (! isset($picklist[$display])) {
							$detail['default'] .= sprintf('<option name="%s" value="%s">%s</option>',$display,$values[$args[2]],$display);
							$picklist[$display] = true;
						}
					}
					$detail['default'] .= '</select>';

					break;

				case 'RandomPassword' :
					$detail['default'] = password_generate();
					printf('<script language="javascript">alert(\'%s:\n%s\')</script>',
						$lang['random_password'],$detail['default']);
					break;

				case 'DrawChooserLink' :
					$detail['default'] = draw_chooser_link(sprintf("template_form.%s%s",$args[0],$counter),$args[1]);

					break;

				case 'Function' :
					# Capture the function name and remove function name from $args
					$function_name = array_shift($args);

					# Call the PHP function if exists (PHP 4 >= 4.0.4, PHP 5)
					if (function_exists($function_name))
						$detail['default'] = call_user_func_array($function_name,$args);

					break;

				default : $detail['default'] = 'UNKNOWN';
			}

			$return = $detail['default'];

		} else {
			$return = $default;
		}

		debug_log(sprintf('%s::EvaluateDefault(): Returning (%s)',get_class($this),serialize($return)),1);
		return $return;
	}

	function HelperValue($helper,$id='',$container='',$ldapserver='',$counter='',$default='') {
		debug_log(sprintf('%s::HelperValue(): Entered with (%s,%s,%s,%s,%s,%s)',
			get_class($this),count($helper),$id,$container,$ldapserver->server_id,$counter,$default),2);

		$html = '';

		if ($container && $ldapserver && ! is_array($helper)) {
			return $this->EvaluateDefault($ldapserver,$helper,$container,$counter);

		} else {
			if (is_array($helper)) {

				$html = sprintf('<select name="%s" id="%s" />',$id,$id);
				foreach ($helper as $value) {
					$html .= sprintf('<option name="%s" value="%s" %s>%s</option>',
						$value,$value,($default == $value ? 'selected' : ''),$value);
				}
				$html .= '</select>';

			} else {
				print "ERROR: HelperValue NOT complete, how did you get HERE?";
				die();
			}
		}

		return $html;
	}
}
?>
