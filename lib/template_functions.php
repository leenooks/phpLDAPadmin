<?php
/* $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/template_functions.php,v 1.41 2006/10/28 16:38:36 wurley Exp $ */

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
	var $_creation_template = array();
	var $_js_hash = array();

	function Templates($server_id) {
		if (DEBUG_ENABLED)
			debug_log('%s::__construct(): Entered with ()',5,get_class($this));

		if ($this->_creation_template = get_cached_item($server_id,'template','creation')) {
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
			masort($this->_creation_template,'title');
			set_cached_item($server_id,'template','creation',$this->_creation_template);
		}
	}

	function storeTemplate($xtemplate,$xmldata) {
		if (DEBUG_ENABLED)
			debug_log('%s::storeTemplate(): Entered with (%s,%s)',5,
				get_class($this),$template,$xmldata);

		global $ldapserver;

		$template['objectclass'] = array();
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
									if (! isset($template['objectclass']) ||
										! in_array($schema->getName(),$template['objectclass'])) {

										$template['objectclass'][] = $schema->getName();
									}

								# This objectClass doesnt exist.
								} else {
								}

							} else {
								if ($schema = $ldapserver->getSchemaObjectClass($details)) {
									if (! isset($template['objectclass']) ||
										! in_array($details,$template['objectclass'])) {

										$template['objectclass'][] = $schema->getName();
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
						$template['attribute'] = array();

						foreach ($xmldata['template']['attributes'] as $tattrs) {
							foreach ($tattrs as $index => $attr_details) {

								if (DEBUG_ENABLED)
									debug_log('%s::storeTemplate(): Foreach tattrs Key [%s] Value [%s]',4,
										get_class($this),$index,serialize($attr_details));

								# Single attribute XML files are not indexed.
								if (is_numeric($index)) {
									if ($attr = $ldapserver->getSchemaAttribute($attr_details['ID']))
										$template['attribute'][$attr->getName()] = $this->_parseXML($index,$attr_details);

								} else {
									if (! strcmp($index,'ID'))
										continue;

									if ($attr = $ldapserver->getSchemaAttribute($tattrs['ID'])) {
										foreach ($attr_details as $key => $values) {
											if (is_array($values) && isset($values['ID'])) {
												$template['attribute'][$attr->getName()][$index]['_KEY:'.$values['ID']] = $this->_parseXML($key,$values);
											} elseif (is_array($values) && isset($values['#text'])) {
												$template['attribute'][$attr->getName()][$index][] = $values['#text'];

											} else {
												$template['attribute'][$attr->getName()][$index] = $this->_parseXML($key,$values);
											}
										}
									}
								}
							}
						}

						# Do we have an override parameter?
						foreach ($template['attribute'] as $key => $data) {
							if (isset($data['override'])) {
								$template['attribute'][$data['override']] = $data;
								unset($template['attribute'][$key]);
								$template['attribute'][$key] = $data['override'];
							}
						}
					}

					break;

				default :
					$template[$xml_key] = $xml_value['#text'];
			}
		}

		if (! count($template['objectclass'])) {
			$template['invalid'] = 1;
			$template['invalid_reason'] = _('ObjectClasses in XML dont exist in LDAP server.');
			return;
		}

		# Collect our structural, must & may attributes.
		$template['must'] = array();
		$template['may'] = array();
		$template['empty_attrs'] = array();

		$superclasslist = array();
		foreach ($template['objectclass'] as $oclass) {

			# If we get some superclasses - then we'll need to go through them too.
			$supclass = true;
			$enherited = false;
			while ($supclass == true) {
				$schema_object = $ldapserver->getSchemaObjectClass($oclass);

				/*
				 * Shouldnt be required now...
				# Test that this is a valid objectclass - disable if an invalid one found.
				if (! $schema_object) {
					$template['invalid'] = 1;
					$supclass = false;
					continue;
				}
				*/

				if ($schema_object->getType() == 'structural' && (! $enherited))
					$template['structural'][] = $oclass;

				if ($schema_object->getMustAttrs() )
					foreach ($schema_object->getMustAttrs() as $index => $detail) {
						$objectclassattr = $detail->getName();

						if (! in_array($objectclassattr,$template['must']) &&
							strcasecmp('objectClass',$objectclassattr) != 0) {

							# Go through the aliases, and ignore any that are already defined.
							$ignore = false;
							$attr = $ldapserver->getSchemaAttribute($objectclassattr);
							foreach ($attr->aliases as $alias) {
								if (in_array($alias,$template['must'])) {
									$ignore = true;
									break;
									}
							}

							if ($ignore)
								continue;

							if (isset($template['attribute'][$objectclassattr]) &&
								! is_array($template['attribute'][$objectclassattr]))

								$template['must'][] =
									$template['attribute'][$objectclassattr];

							else
								$template['must'][] = $objectclassattr;
						}
				}

				if ($schema_object->getMayAttrs())
					foreach ($schema_object->getMayAttrs() as $index => $detail) {
						$objectclassattr = $detail->getName();

						if (! in_array($objectclassattr,$template['may']))
							$template['may'][] = $objectclassattr;
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
		foreach ($template['may'] as $index => $detail) {
			if (in_array($detail,$template['must'])) {
				unset($template['may'][$index]);
				continue;
			}
		}

		# Remove any attributes not in the xml file and not in the dn.
		foreach ($template['may'] as $index => $detail) {
			if (isset($template['attribute'])
				&& ! isset($template['attribute'][$detail])) {

				unset($template['may'][$index]);
				continue;
			}

			if (! isset($attrs[$detail]))
				if (isset($template['attribute'][$detail]))
					$template['empty_attrs'][$detail] = $template['attribute'][$detail];
				else
					$template['empty_attrs'][$detail]['display'] = $detail;

			else
				$template['attrs'][$detail] = $attrs[$detail];
		}

		# Add the must attrs to the attributes key.
		foreach ($template['must'] as $index => $detail) {

			if (! isset($attrs[$detail])) {
				if (isset($template['attribute'][$detail]))
					$template['empty_attrs'][$detail] = $template['attribute'][$detail];
				else
					$template['empty_attrs'][$detail]['display'] = $detail;

				$template['empty_attrs'][$detail]['must'] = true;
			} else
				$template['attrs'][$detail] = $attrs[$detail];
		}

		# Check if there are any items without a page or order parameter, and make it 1 and 255.
		foreach ($template['empty_attrs'] as $index => $detail) {
			if (! isset($detail['page']))
				$template['empty_attrs'][$index]['page'] = 1;
			if (! isset($detail['order']))
				$template['empty_attrs'][$index]['order'] = 255;
		}

		# Check we have some manditory items.
		foreach (array('rdn','structural','visible') as $key) {
			if (! isset($template[$key])
				|| (! is_array($template[$key]) && ! trim($template[$key]))) {

				//unset($template);
				$template['invalid'] = 1;
				$template['invalid_reason'] = sprintf(_('Missing %s in the XML file.'),$key);
				break;
			}
		}

		$this->_creation_template[$xtemplate] = $template;
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

	function getCreationTemplate($template) {
		return isset($this->_creation_template[$template]) ? $this->_creation_template[$template] : null;
	}

	function getCreationTemplates() {
		return $this->_creation_template;
	}

	function OnChangeAdd($ldapserver,$origin,$value) {
		if (DEBUG_ENABLED)
			debug_log('%s::OnChangeAdd(): Entered with (%s,%s,%s)',5,
				get_class($this),$ldapserver->server_id,$origin,$value);

		global $_js_hash;

		# limit to 2 fields because of 'C:\\my directory\\foobar'
		list($command,$arg) = split(':',$value,2);

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
				preg_match_all('/%(\w+)(\|[0-9]*-[0-9]*)?(\/[klTUA]+)?%/U',$string,$matchall);
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
					if (strstr($match_mod,'A')) {
						$_js_hash['autoFill'.$origin] .= sprintf("   %s = toAscii(%s);\n",$match_attr,$match_attr);
					}

					# Matchfor only entry without modifiers.
					$formula = preg_replace('/^%('.$match_attr.')%$/U','$1 + \'\'',$formula);
					# Matchfor only entry with modifiers.
					$formula = preg_replace('/^%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[klTUA]+)?%$/U','$1 + \'\'',$formula);
					# Matchfor begining entry.
					$formula = preg_replace('/^%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[klTUA]+)?%/U','$1 + \'',$formula);
					# Matchfor ending entry.
					$formula = preg_replace('/%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[klTUA]+)?%$/U','\' + $1 ',$formula);
					# Match for entries not at begin/end.
					$formula = preg_replace('/%('.$match_attr.')(\|[0-9]*-[0-9]*)?(\/[:lTUA]+)?%/U','\' + $1 + \'',$formula);
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
					/*
					 * mandatory arguments:
					 * * arg 0
					 *   - "$" => 'auto_number','search_base' in config file
					 *   - "/","..","." => get container parent as usual
					 * * arg 1
					 *   - "gid" or "uid" for autosearch
					 *   - idem or real attribute name for uidpool mechanism
					 *     (gid and uid are mapped to sambaNextGroupRid and sambaNextUserRid)
					 * optional arguments:
					 * * arg 2 (uidpool mechanism only)
					 *   - "true" increments attribute by 1
					 *   - "false" do nothing
					 * * arg 3 (uidpool mechanism only)
					 *   ldap filter (must match one entry only in container)
					 * * arg 4
					 *   calculus on number, eg:
					 *   *2;+1000 => number = (2*number) + 1000
					 */

					if ($args[0] == '$')
						$args[0] = $ldapservers->GetValue($ldapserver->server_id,'auto_number','search_base');

					$container = $ldapserver->getContainerParent($container,$args[0]);
					$detail['value'] = get_next_number($ldapserver,$container,$args[1],
						(!empty($args[2]) && ($args[2] == 'true')) ? true : false,(!empty($args[3])) ? $args[3] : false);

					# operate calculus on next number.
					if (!empty($args[4])) {
						$mod = split(';',$args[4]);

						$next_number = $detail['value'];

						foreach ($mod as $calc) {
							$operand = $calc{0};
							$operator = substr ($calc,1);

							switch ($operand) {
								case '*':
									$next_number = $next_number * $operator;
									break;

								case '+':
									$next_number = $next_number + $operator;
									break;

								case '-':
									$next_number = $next_number - $operator;
									break;

								case '/':
									$next_number = $next_number / $operator;
									break;
							}
						}

						$detail['value'] = $next_number;
					}
					break;

				case 'PickList' :
					/*
					 * PickList Syntax:
					 * arg0: container, from current position
					 * arg1: LDAP filter. must replace '&' by '&amp;'
					 * arg2: list attribute key
					 * arg3: display, as usual
					 optional arguments:
					 * arg4: output attribute
					 * arg5: container override
					 * arg6: csv list (; separator) of added values. syntax key => display_attribute=value; key...
					 * arg7: csv list (; separator) of sort attributes (less to more important)
					 * example
					 * <value>=php.PickList(/,(&amp;(objectClass=sambaGroupMapping)(|(cn=domain administrator)(cn=domain users)(cn=domain guests))),sambaSID,%cn% (%sambaSID%),sambaPrimaryGroupSID,dmdname=users:::dmdName=groups:::dc=example:::dc=com, S-1-5-XX-YYY => cn=Administrators ; S-1-5-XX-YYY => cn=Users ; S-1-5-XX-YYY => cn=Guests ; S-1-5-XX-YYY => cn=power users,cn)</value>
					 */

					$container = $ldapserver->getContainerParent($container,$args[0]);
					preg_match_all('/%(\w+)(\|.+)?(\/[lU])?%/U',$args[3],$matchall);
					//print_r($matchall); // -1 = highlevel match, 1 = attr, 2 = subst, 3 = mod

					$ldap_attrs = $matchall[1];
					array_push($ldap_attrs,$args[2]);

					$args[1] = str_replace ('&amp;','&',$args[1]);

					# arg5 overrides container
					if (!empty($args[5]))
						$container = str_replace(':::',',',$args[5]);

					if (!empty($args[7])) {
						$sort_attrs = split(';',$args[7]);
						$ldap_attrs = array_merge($ldap_attrs,$sort_attrs);
					}

					$picklistvalues = return_ldap_hash($ldapserver,$container,$args[1],$args[2],$ldap_attrs,
						(isset($args[7])) ? $sort_attrs : false);

					if (!empty($args[6])) {
						$args[6] = str_replace(':::',',',$args[6]);
						$fixedvalues = split(';',$args[6]);

						foreach ($fixedvalues as $fixedvalue) {
							$fixedvalue = preg_split('#=\>#',$fixedvalue);
							$displayvalue = split('=',$fixedvalue[1]);
							$newvalue[trim($fixedvalue[0])] = array( $args[2] => trim($fixedvalue[0]),
								trim($displayvalue[0]) => trim($displayvalue[1]));
							$picklistvalues = array_merge($picklistvalues,$newvalue);
						}
					}

					$detail['value'] = sprintf('<select name="form[%s]" id="%%s" %%s %%s>',(isset($args[4]) ? $args[4] : $args[2]));
					$counter = 0;
					foreach ($picklistvalues as $key => $values) {
						$display = $args[3];

						foreach ($matchall[1] as $arg)
							$display = preg_replace('/%('.$arg.')(\|.+)?(\/[lU])?%/U',$values[$arg],$display);

						if (! isset($picklist[$values[$args[2]]])) {
							$detail['value'] .= sprintf('<option id="%s%s" value="%s" %s>%s</option>',
								(!empty($args[4]) ? $args[4] : $args[2]),++$counter,$values[$args[2]],
								($default == $values[$args[2]]) ? 'selected' : '',
								$display);

							$picklist[$values[$args[2]]] = true;
						}
					}

					$detail['value'] .= '</select>';

					break;

				case 'MultiList' :
					/*
					 * MultiList Syntax:
					 */
					/**
					 mandatory fields:
						arg 0: "/" ,"..","." - from container dn
						arg 1: search filter, may have values like '%gidNumber%, in case of it is replaced
								by the gidNumber setted in previous pages. '&' must be replaced by '&amp;'
								because of xml...
						arg 2: the key of retrived values
					optional fields:
						arg 3: display, as usual (plus modifier /C: Capitalize). replaced by %arg 2% if not given
						arg 4: the value furnished in output - must be attribute id. replaced by arg 2 if not given
						arg 5: override of container (replace ',' by ':::' in dn)
						arg 6: csv (; separator) list of added values. syntax: value => display_key=display_value
						arg 7: csv (; separator) list of attributes which list must be sort by. less to more important
						arg 8: size of displayed list (default: 10lines)
						arg 9: preselected values filter. see arg 1.
						arg 10: key of preselected values. replaced by arg 4 if not given. replaced bty arg 2 if both are not given.
						arg 11: base dn override for preselected values

					unusual exemple:)
					 <value>=php.MultiList(/,(&amp;(objectClass=posixAccount)(uid=groupA*)),uid,%cn/U% (%gidNumber%),memberUid,dmdName=users,root => cn=root; nobody => cn=nobody,gidNumber,10,(gidNuber=%gidNumber%),uid)</value>
					minimal exemple:
					 <value>=php.MultiList(/,(objectClass=posixAccount),uid)</value>
					**/

					$container = $ldapserver->getContainerParent($container,$args[0]);

					/*
					 * process filter (arg 1), eventually replace %attr% by it's value
					 * setted in a previous page.
					 */
					$args[1] = str_replace('&amp;','&',$args[1]);

					preg_match_all('/%(\w+)(\|.+)?(\/[lUC])?%/U',$args[1],$filtermatchall);
					$formvalues = array_change_key_case($_REQUEST['form']);

					foreach ($filtermatchall[1] as $arg) {
						$value=$formvalues[strtolower($arg)];
						$args[1] = preg_replace('/%('.$arg.')(\|.+)?(\/[lU])?%/U',$value,$args[1]);
					}

					$args[3] = !empty($args[3]) ? $args[3] : "%{$args[2]}%";

					preg_match_all('/%(\w+)(\|.+)?(\/[lUC])?%/U',$args[3],$matchall);
					//print_r($matchall); // -1 = highlevel match, 1 = attr, 2 = subst, 3 = mod

					$ldap_attrs = $matchall[1];
					array_push($ldap_attrs,$args[2]);

					/*
					 * container is arg 5 if set
					 * with arg 5 = 'dc=thissubtree:::dc=thistree' stands for 'dc=subtree,dc=tree'
					 * => 'dc=subtree,dc=tree,dc=container'
					 */
					if (isset($args[5]) && ($args[5]))
						$container = str_replace(':::',',',$args[5]);

					/*
					 * arg 7 is sort attributes
					 * eg: 'sn;givenName'
					 */
					if (isset($args[7])) {
						$sort_attrs = split(';',$args[7]);
						$ldap_attrs = array_merge($ldap_attrs,$sort_attrs);
					}

					$picklistvalues = return_ldap_hash($ldapserver,$container,$args[1],$args[2],$ldap_attrs,
						(isset($args[7]) && ($args[7])) ? $sort_attrs : false);

					# arg 6 is a set of fixed values to add to search result
					if (isset($args[6])) {
						$args[6] = str_replace(':::',',',$args[6]);
						$fixedvalues = split(';',$args[6]);

						foreach ($fixedvalues as $fixedvalue) {
							if (empty($fixedvalue))
								continue;

							$fixedvalue = preg_split('#=\>#',$fixedvalue);
							$displayvalue = split('=',$fixedvalue[1]);
							$newvalue[trim($fixedvalue[0])] = array($args[2] => trim($fixedvalue[0]),
								trim($displayvalue[0]) => trim($displayvalue[1]));
							$picklistvalues = array_merge($picklistvalues,$newvalue);
						}
					}

					/*
					 * arg 9 is the search filter for already selected values, with criteriai eventually
					 * coming from previous pages (eg: %uid%)
					 */
					if (isset($args[9])) {
						$args[9] = str_replace('&amp;','&',$args[9]);

						preg_match_all('/%(\w+)(\|.+)?(\/[lUC])?%/U',$args[9],$matchallinlist);

						foreach ($matchallinlist[1] as $arg) {
							$value=$formvalues[strtolower($arg)];

							$args[9] = preg_replace('/%('.$arg.')(\|.+)?(\/[lU])?%/U',$value,$args[9]);
						}

						# arg 11 overrides container dn for selected values
						if (!empty($args[11]))
							$container = str_replace(':::',',',$args[11]);

						$inpicklistvalues = return_ldap_hash($ldapserver,$container,$args[9],$args[2],$ldap_attrs);
					}

					$detail['value'] = sprintf('<select name="form[%s][]" multiple="multiple" size="%s" id="%%s" %%s %%s>',
						(isset($args[4])) ? $args[4] : $args[2],
						# arg 8 is the size (nbr of displayed lines) of select
						(isset($args[8])) ? $args[8] : 10);

					$counter = 0;
					foreach ($picklistvalues as $key => $values) {
						$display = $args[3];

						foreach ($matchall[1] as $key => $arg) {
							$disp_val = $values[$arg];

							if ($matchall[3][$key])
								switch ($matchall[3][$key]) {
									case '/l':
									# lowercase
										$disp_val = mb_convert_case($disp_val,MB_CASE_LOWER,'utf-8');
										break;

									case '/U':
									# uppercase
										$disp_val = mb_convert_case($disp_val,MB_CASE_UPPER,'utf-8');
										break;

									case '/C':
									# capitalize
										$disp_val = mb_convert_case($disp_val,MB_CASE_TITLE,'utf-8');
										break;

									default:
										break;
								}

							# make value a substring of
							preg_match_all('/^\|([0-9]*)-([0-9]*)$/',trim($matchall[2][$key]),$substrarray);

							if ((isset($substrarray[1][0]) && $substrarray[1][0]) || (isset($substrarray[2][0]) && $substrarray[2][0])) {
								$begin = $substrarray[1][0] ? $substrarray[1][0] : '0';
								$end = $substrarray[2][0] ? $substrarray[2][0] : strlen($disp_val);
								$disp_val = mb_substr($disp_val,$begin,$end,'utf-8');
							}

							$display = preg_replace('/%('.$arg.')(\|.+)?(\/[lUC])?%/U',$disp_val,$display);
						}

						if (! isset($picklist[$values[$args[2]]])) {
							if (!isset($args[9])) {

								# there is no criteria filter for selected values
								$detail['value'] .= sprintf('<option id="%s%s" value="%s" %s>%s</option>',
									# arg 4 is the output criteria
									((isset($args[4]) && !empty($args[4])) ? $args[4] : $args[2]),
									++$counter,
									$values[$args[2]],
									# if the value the default, then select it
									(in_array($values[$args[2]],$default)) ? 'selected' : '',
									$display);

							} else {
								# if default filter is given
								$detail['value'] .= sprintf('<option id="%s%s" value="%s" %s>%s</option>',
									(isset($args[4]) ? $args[4] : $args[2]),
									++$counter,
									$values[$args[2]],
									# arg 10 is the key for filter values
									(array_key_exists($values[(isset($args[10]) ? $args[10] : (isset($args[4]) ? $args[4] : $args[2]))],$inpicklistvalues)) ? 'selected' : '',
									$display);
							}

							$picklist[$values[$args[2]]] = true;
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
				$html = sprintf('<input type="text" name="%s" value="%s" size="8" />',$id,
					$this->EvaluateDefault($ldapserver,$helper,$container,$counter));

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
