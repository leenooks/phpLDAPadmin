<?php
/* $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/template_functions.php,v 1.29.2.20 2007/03/21 23:12:03 wurley Exp $ */

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
		xml_set_element_handler($this->resParser,'tagOpen','tagClosed');

		xml_set_character_data_handler($this->resParser,'tagData');

		$this->push_pos($this->arrOutput);

		$this->strXmlData = xml_parse($this->resParser,$strInputXML);

		if (! $this->strXmlData)
			die(sprintf('XML error: %s at line %d',
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
	var $_js_hash = array();

	function Templates($server_id) {
		if (DEBUG_ENABLED)
			debug_log('%s::__construct(): Entered with ()',5,get_class($this));

		if ($this->_template = get_cached_item($server_id,'template','all')) {
			if (DEBUG_ENABLED)
				debug_log('%s::init(): Using CACHED [%s]',5,get_class($this),'templates');

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
		if (DEBUG_ENABLED)
			debug_log('%s::storeTemplate(): Entered with (%s,%s)',5,
				get_class($this),$template,$xmldata);

		global $ldapserver;

		$this->_template[$template]['objectclass'] = array();
		foreach ($xmldata['template'] as $xml_key => $xml_value) {
			if (DEBUG_ENABLED)
				debug_log('%s::storeTemplate(): Foreach loop Key [%s] Value [%s]',4,
					get_class($this),$xml_key,is_array($xml_value));

			switch ($xml_key) {

				# Build our object Classes from the DN and Template.
				case ('objectclasses') :
					if (isset($xmldata['template']['objectclasses']) && is_array($xmldata['template']['objectclasses'])) {
						foreach ($xmldata['template']['objectclasses']['objectclass'] as $index => $details) {

							# XML files with only 1 objectClass dont have a numeric index.
							if (is_numeric($index)) {
								if ($schema = $ldapserver->getSchemaObjectClass($details['ID'])) {

									# If we havent recorded this objectclass already, do so now.
									if (! isset($this->_template[$template]['objectclass']) ||
										! in_array($schema->getName(),$this->_template[$template]['objectclass'])) {

										$this->_template[$template]['objectclass'][] = $schema->getName();
									}

								# This objectClass doesnt exist.
								} else {
								}

							} else {
								if ($schema = $ldapserver->getSchemaObjectClass($details)) {
									if (! isset($this->_template[$template]['objectclass']) ||
										! in_array($details,$this->_template[$template]['objectclass'])) {
	
										$this->_template[$template]['objectclass'][] = $schema->getName();
									}

								# This objectClass doesnt exist.
								} else {
								}
							}
						}
					}

					break;

				# Build our attribute list from the DN and Template.
				case ('attributes') :
					if (DEBUG_ENABLED)
						debug_log('%s::storeTemplate(): Case [%s]',4,get_class($this),'attributes');

					if (isset($xmldata['template']['attributes']) && is_array($xmldata['template']['attributes'])) {
						$this->_template[$template]['attribute'] = array();

						foreach ($xmldata['template']['attributes'] as $tattrs) {
							foreach ($tattrs as $index => $attr_details) {

								if (DEBUG_ENABLED)
									debug_log('%s::storeTemplate(): Foreach tattrs Key [%s] Value [%s]',4,
										get_class($this),$index,serialize($attr_details));

								# Single attribute XML files are not indexed.
								if (is_numeric($index)) {
									if ($attr = $ldapserver->getSchemaAttribute($attr_details['ID']))
										$this->_template[$template]['attribute'][$attr->getName()] = $this->_parseXML($index,$attr_details);

								} else {
									if (! strcmp($index,'ID'))
										continue;

									if ($attr = $ldapserver->getSchemaAttribute($tattrs['ID'])) {
										foreach ($attr_details as $key => $values) {
											if (is_array($values) && isset($values['ID'])) {
												$this->_template[$template]['attribute'][$attr->getName()][$index]['_KEY:'.$values['ID']] = $this->_parseXML($key,$values);
											} elseif (is_array($values) && isset($values['#text'])) {
												$this->_template[$template]['attribute'][$attr->getName()][$index][] = $values['#text'];
	
											} else {
												$this->_template[$template]['attribute'][$attr->getName()][$index] = $this->_parseXML($key,$values);
											}
										}
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
					}

					break;

				default :
					$this->_template[$template][$xml_key] = $xml_value['#text'];
			}
		}

		if (! count($this->_template[$template]['objectclass'])) {
			$this->_template[$template]['invalid'] = 1;
			$this->_template[$template]['invalid_reason'] = _('ObjectClasses in XML dont exist in LDAP server.');
			return;
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
				$schema_object = $ldapserver->getSchemaObjectClass($oclass);

				/*
				 * Shouldnt be required now...
				# Test that this is a valid objectclass - disable if an invalid one found.
				if (! $schema_object) {
					$this->_template[$template]['invalid'] = 1;
					$supclass = false;
					continue;
				}
				*/

				if ($schema_object->getType() == 'structural' && (! $enherited))
					$this->_template[$template]['structural'][] = $oclass;

				if ($schema_object->getMustAttrs() )
					foreach ($schema_object->getMustAttrs() as $index => $detail) {
						$objectclassattr = $detail->getName();

						if (! in_array($objectclassattr,$this->_template[$template]['must']) &&
							strcasecmp('objectClass',$objectclassattr) != 0) {

							# Go through the aliases, and ignore any that are already defined.
							$ignore = false;
							$attr = $ldapserver->getSchemaAttribute($objectclassattr);
							foreach ($attr->aliases as $alias) {
								if (in_array($alias,$this->_template[$template]['must'])) {
									$ignore = true;
									break;
									}
							}

							if ($ignore)
								continue;

							if (isset($this->_template[$template]['attribute'][$objectclassattr]) &&
								! is_array($this->_template[$template]['attribute'][$objectclassattr]))

								$this->_template[$template]['must'][] =
									$this->_template[$template]['attribute'][$objectclassattr];

							else
								$this->_template[$template]['must'][] = $objectclassattr;
						}
				}

				if ($schema_object->getMayAttrs())
					foreach ($schema_object->getMayAttrs() as $index => $detail) {
						$objectclassattr = $detail->getName();

						if (! in_array($objectclassattr,$this->_template[$template]['may']))
							$this->_template[$template]['may'][] = $objectclassattr;
					}

				# Keep a list to objectclasses we have processed, so we dont get into a loop.
				$oclass_processed[] = $oclass;

				if ((count($schema_object->getSupClasses())) || count($superclasslist)) {
					foreach ($schema_object->getSupClasses() as $supoclass) {
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
				$this->_template[$template]['invalid_reason'] = sprintf(_('Missing %s in the XML file.'),$key);
				break;
			}
		}
	}

	function _parseXML($index,$attr_details) {
		if (DEBUG_ENABLED)
			debug_log('%s::_parseXML(): Entered with (%s,%s)',5,
				get_class($this),$index,$attr_details);

		if (! $attr_details) {
			return '';

		} elseif (! is_array($attr_details)) {
			return $attr_details;

		} elseif (isset($attr_details['#text'])) {

			# If index is numeric, then this is part of an array...
			return $attr_details['#text'];
		}

		foreach ($attr_details as $key => $values) {
			if (($key == 'ID') && ! is_array($values))
				continue;

			elseif (isset($values['ID']) && (! $key['#text'])) {
				$key = '_KEY:'.$values['ID'];
				unset($values['ID']);

			} elseif (isset($values['ID']) && ($values['#text'])) {
				$key = '_KEY:'.$values['ID'];
			}

			$parseXML[$key] = $this->_parseXML($index,$values);
		}

		return $parseXML;
	}

	function getTemplate($template) {
		return isset($this->_template[$template]) ? $this->_template[$template] : null;
	}

	function getTemplates() {
		return $this->_template;
	}

	function OnChangeAdd($ldapserver,$origin,$value) {
		if (DEBUG_ENABLED)
			debug_log('%s::OnChangeAdd(): Entered with (%s,%s,%s)',5,
				get_class($this),$ldapserver->server_id,$origin,$value);

		global $_js_hash;

		list($command,$arg) = split(':',$value);

		switch ($command) {
			/*
			autoFill:string
			string is a literal string, and may contain many fields like %attr|start-end/flags%
			       to substitute values read from other fields.
			|start-end is optional, but must be present if the k flag is used.
			/flags is optional.
			
			flags may be:
			T:    Read display text from selection item (drop-down list), otherwise, read the value of the field
			      For fields that aren't selection items, /T shouldn't be used, and the field value will always be read.
			k:    Tokenize:
			      If the "k" flag is not given:
			           A |start-end instruction will perform a sub-string operation upon
			           the value of the attr, passing character positions start-end through.
			           start can be 0 for first character, or any other integer.
			           end can be 0 for last character, or any other integer for a specific position.
			      If the "k" flag is given:
			      The string read will be split into fields, using : as a delimiter
			           "start" indicates which field number to pass through.
			l:    Make the result lower case.
			U:    Make the result upper case.
			*/
			case 'autoFill' :
				list($attr,$string) = preg_split('(([^,]+),(.*))',$arg,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
				preg_match_all('/%(\w+)(\|[0-9]*-[0-9]*)?(\/[klTU]+)?%/U',$string,$matchall);
				//print"<PRE>";print_r($matchall); //0 = highlevel match, 1 = attr, 2 = subst, 3 = mod

				if (! isset($_js_hash['autoFill'.$origin]))
					$_js_hash['autoFill'.$origin] = '';

				$formula = $string;
				$formula = preg_replace('/^([^%])/','\'$1',$formula);
				$formula = preg_replace('/([^%])$/','$1\'',$formula);

				# Check that our attributes match our schema attributes.
				foreach ($matchall[1] as $index => $checkattr) {
					$matchattr = $ldapserver->getSchemaAttribute($checkattr);

					# If the attribute is the same as in the XML file, then dont need to do anything.
					if ($matchattr->getName() == $checkattr)
						continue;

					$formula = preg_replace("/$checkattr/",$matchattr->getName(),$formula);
					$matchall[1][$index] = $matchattr->getName();
				}

				foreach ($matchall[0] as $index => $null) {
					$match_attr = $matchall[1][$index];
					$match_subst = $matchall[2][$index];
					$match_mod = $matchall[3][$index];

					$substrarray = array();

					$_js_hash['autoFill'.$origin] .= sprintf("  var %s;\n",$match_attr);

					if (strstr($match_mod,'T')) {
						$_js_hash['autoFill'.$origin] .= sprintf(
							"   %s = document.getElementById('%s').options[document.getElementById('%s').selectedIndex].text;\n",
							$match_attr,$match_attr,$match_attr);
					} else {
						$_js_hash['autoFill'.$origin] .= sprintf("   %s = document.getElementById('%s').value;\n",
							$match_attr,$match_attr);
					}

					if (strstr($match_mod,'k')) {
						preg_match_all('/([0-9]+)/',trim($match_subst),$substrarray);
						if (isset($substrarray[1][0])) {
							$tok_idx = $substrarray[1][0];
						} else {
							$tok_idx = '0';
						}
						$_js_hash['autoFill'.$origin] .= sprintf("   %s = %s.split(':')[%s];\n",$match_attr,$match_attr,$tok_idx);
					} else {
						preg_match_all('/([0-9]*)-([0-9]*)/',trim($match_subst),$substrarray);
						if ((isset($substrarray[1][0]) && $substrarray[1][0]) || (isset($substrarray[2][0]) && $substrarray[2][0])) {
							$_js_hash['autoFill'.$origin] .= sprintf("   %s = %s.substr(%s,%s);\n",
								$match_attr,$match_attr,
								$substrarray[1][0] ? $substrarray[1][0] : '0',
								$substrarray[2][0] ? $substrarray[2][0] : sprintf('%s.length',$match_attr));
						}
					}

					if (strstr($match_mod,'l')) {
						$_js_hash['autoFill'.$origin] .= sprintf("   %s = %s.toLowerCase();\n",$match_attr,$match_attr);
					}
					if (strstr($match_mod,'U')) {
						$_js_hash['autoFill'.$origin] .= sprintf("   %s = %s.toUpperCase();\n",$match_attr,$match_attr);
					}

					# Matchfor only entry without modifiers.
					$formula = preg_replace('/^%('.$match_attr.')%$/U','$1 + \'\'',$formula);
					# Matchfor only entry with modifiers.
					$formula = preg_replace('/^%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[klTU]+)?%$/U','$1 + \'\'',$formula);
					# Matchfor begining entry.
					$formula = preg_replace('/^%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[klTU]+)?%/U','$1 + \'',$formula);
					# Matchfor ending entry.
					$formula = preg_replace('/%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[klTU]+)?%$/U','\' + $1 ',$formula);
					# Match for entries not at begin/end.
					$formula = preg_replace('/%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[:lTU]+)?%/U','\' + $1 + \'',$formula);
				}

				$_js_hash['autoFill'.$origin] .= sprintf(" fillRec('%s', %s);\n",$attr,$formula);
				$_js_hash['autoFill'.$origin] .= "\n";
				break;

			default: $return = '';
		}
		return '1';
	}

	function getJsHash() {
		global $_js_hash;
		return $_js_hash;
	}

	// @todo: The XML files need to change the field seperater to something else (ie: not comma)
	// as it is clashing when a DN is used as an argument.
	function EvaluateDefault(&$ldapserver,$value,$container,$counter='',$default=null) {
		if (DEBUG_ENABLED)
			debug_log('%s::EvaluateDefault(): Entered with (%s,%s,%s,%s)',5,
				get_class($this),$ldapserver->server_id,$value,$container,$counter);

		global $ldapservers;

		if (preg_match('/^=php\.(\w+)\((.*)\)$/',$value,$matches)) {
			$args = preg_split('/,/',$matches[2]);

			switch($matches[1]) {
				case 'GetNextNumber' :
					if ($args[0] == '$')
						$args[0] = $ldapservers->GetValue($ldapserver->server_id,'auto_number','search_base');

					$container = $ldapserver->getContainerParent($container,$args[0]);

					$detail['value'] = get_next_number($ldapserver,$container,$args[1]);
					break;

				case 'PickList' :
					$container = $ldapserver->getContainerParent($container,$args[0]);
					preg_match_all('/%(\w+)(\|.+)?(\/[lU])?%/U',$args[3],$matchall);
					//print_r($matchall); // -1 = highlevel match, 1 = attr, 2 = subst, 3 = mod

					$ldap_attrs = $matchall[1];
					array_push($ldap_attrs,$args[2]);
					$picklistvalues = return_ldap_hash($ldapserver,$container,$args[1],$args[2],$ldap_attrs);

					$detail['value'] = sprintf('<select name="form[%s]" id="%%s" %%s %%s>',(isset($args[4]) ? $args[4] : $args[2]));
					$counter = 0;
					foreach ($picklistvalues as $key => $values) {
						$display = $args[3];

						foreach ($matchall[1] as $arg) {
							$display = preg_replace('/%('.$arg.')(\|.+)?(\/[lU])?%/U',$values[$arg],$display);
						}

						if (! isset($picklist[$display])) {
							$detail['value'] .= sprintf('<option id="%s%s" value="%s" %s>%s</option>',
								(isset($args[4]) ? $args[4] : $args[2]),++$counter,$values[$args[2]],
								($default == $display ? 'selected' : ''),
								$display);
							$picklist[$display] = true;
						}
					}
					$detail['value'] .= '</select>';

					break;

				case 'RandomPassword' :
					$detail['value'] = password_generate();
					printf('<script type="text/javascript" language="javascript">alert(\'%s:\n%s\')</script>',
						_('A random password was generated for you'),$detail['value']);
					break;

				case 'DrawChooserLink' :
					$detail['value'] = draw_chooser_link(sprintf('template_form.%s%s',$args[0],$counter),$args[1]);

					break;

				case 'Function' :
					# Capture the function name and remove function name from $args
					$function_name = array_shift($args);

					$function_args = array();
					foreach ($args as $arg) {
						if (preg_match('/^%(\w+)(\|.+)?(\/[lU])?%/U',$arg,$matches)) {

							$varname = $matches[1];

							if (isset($_POST['form'][$varname]))
								$function_args[] = $_POST['form'][$varname];
							else
								pla_error(sprintf(_('Your template calls php.Function for a default value, however (%s) is NOT available in the POST FORM variables. The following variables are available [%s].'),$varname,
									(isset($_POST['form']) ? implode('|',array_keys($_POST['form'])) : 'NONE')));
						} else {
							$function_args[] = $arg;
						}
					}

					# Call the PHP function if exists (PHP 4 >= 4.0.4, PHP 5)
					if (function_exists($function_name))
						$detail['value'] = call_user_func_array($function_name,$function_args);

					break;

				default : $detail['value'] = 'UNKNOWN';
			}

			$return = $detail['value'];

		} else {
			$return = $value;
		}

		if (DEBUG_ENABLED)
			debug_log('%s::EvaluateDefault(): Returning (%s)',5,get_class($this),$return);
		return $return;
	}

	function HelperValue($helper,$id='',$container='',$ldapserver='',$counter='',$default='') {
		if (DEBUG_ENABLED)
			debug_log('%s::HelperValue(): Entered with (%s,%s,%s,%s,%s,%s)',5,
				get_class($this),$helper,$id,$container,$ldapserver->server_id,$counter,$default);

		if ($container && $ldapserver && ! is_array($helper)) {
			if (preg_match('/^=php./',$helper))
				return $this->EvaluateDefault($ldapserver,$helper,$container,$counter);

			else
				# @todo: Enable size and width configuration in template
				$html = sprintf('<input type="text" name="%s" size="8" />',$id);

		} else {
			if (is_array($helper)) {

				$html = sprintf('<select name="%s" id="%s">',$id,$id);
				foreach ($helper as $value) {
					$html .= sprintf('<option id="%s" value="%s" %s>%s</option>',
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
