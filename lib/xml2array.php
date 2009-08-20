<?php
/**
 * Classes and functions for manipulating XML templates.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * XML Parser
 *
 * This will read our XML file and convert it into variables for us to parse.
 *
 * @package phpLDAPadmin
 * @subpackage XML
 */
class xml2array {
	var $stack = array();
	var $stack_ref;
	var $arrOutput = array();
	var $resParser;
	var $strXmlData;

	private function push_pos(&$pos) {
		$this->stack[count($this->stack)] = &$pos;
		$this->stack_ref = &$pos;
	}

	private function pop_pos() {
		unset($this->stack[count($this->stack) - 1]);
		$this->stack_ref = &$this->stack[count($this->stack) - 1];
	}

	public function parseXML($strInputXML,$filename) {
		$this->resParser = xml_parser_create();
		xml_set_object($this->resParser,$this);
		xml_set_element_handler($this->resParser,'tagOpen','tagClosed');

		xml_set_character_data_handler($this->resParser,'tagData');

		$this->push_pos($this->arrOutput);

		$this->strXmlData = xml_parse($this->resParser,$strInputXML);

		if (! $this->strXmlData)
			die(sprintf('XML error: %s at line %d in file %s',
				xml_error_string(xml_get_error_code($this->resParser)),
				xml_get_current_line_number($this->resParser),
				$filename));

		xml_parser_free($this->resParser);

		$output = array();
		foreach ($this->arrOutput as $key => $values)
			$output[$key] = $this->cleanXML($values);

		#return $this->arrOutput;
		return $output;
	}

	private function tagOpen($parser,$name,$attrs) {
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

	private function tagData($parser,$tagData) {
		if (trim($tagData) != '') {

			if (isset($this->stack_ref['#text']))
				$this->stack_ref['#text'] .= $tagData;
			else
				$this->stack_ref['#text'] = $tagData;
		}
	}

	private function tagClosed($parser,$name) {
		$this->pop_pos();
	}

	/**
	 * This function will parse an XML array and make a normal array.
	 *
	 * @return array - Clean XML data
	 */
	private function cleanXML($details) {
		# Quick processing for the final branch of the XML array.
		if (is_array($details) && isset($details['#text']))
			return $details['#text'];

		elseif (is_array($details) && isset($details['ID']) && count($details) == 1)
			return $details['ID'];

		$cleanXML = array();

		# Quick processing for the final branch, when it holds the ID and values.
		if (is_array($details) && isset($details['ID']) && count($details) > 1) {
				$key = $details['ID'];
				unset($details['ID']);
				$cleanXML[$key] = $this->cleanXML($details);
				$details = array();
		}

		# More detailed processing...
		if (is_array($details))
			foreach ($details as $key => $values)
				if (is_numeric($key) && isset($values['ID']) && count($values) > 1) {
					$key = $values['ID'];
					unset($values['ID']);
					$cleanXML[$key] = $this->cleanXML($values);

				} elseif (isset($values['#text']))
					$cleanXML[$key] = $this->cleanXML($values);

				elseif (is_array($values))
					$cleanXML[$key] = $this->cleanXML($values);

		if (! $cleanXML)
			return $details;
		else
			return $cleanXML;
	}
}
