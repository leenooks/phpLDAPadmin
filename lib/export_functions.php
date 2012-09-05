<?php
/**
 * Classes and functions for export data from LDAP
 *
 * These classes provide differnet export formats.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 * @see export.php and export_form.php
 */

/**
 * Exporter Class
 *
 * This class serves as a top level exporter class, which will return
 * the correct Export class.
 *
 * @package phpLDAPadmin
 * @subpackage Export
 */
class Exporter {
	# Server ID that the export is linked to
	private $server_id;
	# Exporter Type
	private $template_id;
	private $template;

	public function __construct($server_id,$template_id) {
		$this->server_id = $server_id;
		$this->template_id = $template_id;

		$this->accept();
	}

	static function types() {
		$type = array();

		$details = ExportCSV::getType();
		$type[$details['type']] = $details;
		$details = ExportDSML::getType();
		$type[$details['type']] = $details;
		$details = ExportLDIF::getType();
		$type[$details['type']] = $details;
		$details = ExportVCARD::getType();
		$type[$details['type']] = $details;

		return $type;
	}

	private function accept() {
		switch($this->template_id) {
			case 'CSV':
				$this->template = new ExportCSV();
				break;

			case 'DSML':
				$this->template = new ExportDSML();
				break;

			case 'LDIF':
				$this->template = new ExportLDIF();
				break;

			case 'VCARD':
				$this->template = new ExportVCARD();
				break;

			default:
				system_message(array(
					'title'=>sprintf('%s %s',_('Unknown Export Type'),$this->template_id),
					'body'=>_('phpLDAPadmin has not been configured for that export type'),
					'type'=>'warn'),'index.php');
				die();
		}

		$this->template->accept();
	}

	public function getTemplate() {
		return $this->template;
	}
}

/**
 * Export Class
 *
 * This abstract classes provides all the common methods and variables for the
 * custom export classes.
 *
 * @package phpLDAPadmin
 * @subpackage Export
 */
abstract class Export {
	# Line Break
	protected $br;
	# Compress the output
	protected $compress;
	# Export Results
	protected $results;
	protected $resultsdata;
	protected $items = 0;

	/**
	 * Return this LDAP Server object
	 *
	 * @return object DataStore Server
	 */
	protected function getServer() {
		return $_SESSION[APPCONFIG]->getServer($this->getServerID());
	}

	/**
	 * Return the LDAP server ID
	 *
	 * @return int Server ID
	 */
	protected function getServerID() {
		return get_request('server_id','REQUEST');
	}

	public function accept() {
		$server = $this->getServer();

		# Get the data to be exported
		$query = array();
		$base = get_request('dn','REQUEST');
		$query['baseok'] = true;
		$query['filter'] = get_request('filter','REQUEST',false,'objectclass=*');
		$query['scope'] = get_request('scope','REQUEST',false,'base');
		$query['deref'] = $_SESSION[APPCONFIG]->getValue('deref','export');
		$query['size_limit'] = 0;
		$attrs = get_request('attributes','REQUEST');

		$attrs = preg_replace('/\s+/','',$attrs);
		if ($attrs)
			$query['attrs'] = explode(',',$attrs);
		else
			$query['attrs'] = array('*');

		if (get_request('sys_attr')) {
			if (! in_array('*',$query['attrs']))
				array_push($query['attrs'],'*');
			array_push($query['attrs'],'+');
		}

		if (! $base)
			$bases = $server->getBaseDN();
		else
			$bases = array($base);

		foreach ($bases as $base) {
			$query['base'] = $base;

			$time_start = utime();
			$this->results[$base] = $server->query($query,null);
			$time_end = utime();

			usort($this->results[$base],'pla_compare_dns');
			$this->resultsdata[$base]['time'] = round($time_end-$time_start,2);

			# If no result, there is a something wrong
			if (! $this->results[$base] && $server->getErrorNum(null))
				system_message(array(
					'title'=>_('Encountered an error while performing search.'),
					'body'=>ldap_error_msg($server->getErrorMessage(null),$server->getErrorNum(null)),
					'type'=>'error'));

			$this->items += count($this->results[$base]);
		}

		$this->resultsdata['scope'] = $query['scope'];
		$this->resultsdata['filter'] = $query['filter'];
		$this->resultsdata['attrs'] = $query['attrs'];

		# Other settings
		switch (get_request('format','POST',false,'unix')) {
			case 'win':
				$this->br = "\r\n";
				break;

			case 'mac':
				$this->br = "\r";
				break;

			case 'unix':
			default:
				$this->br = "\n";
		}

		if (get_request('compress','REQUEST') == 'on')
			$this->compress = true;
	}

	public function isCompressed() {
		return $this->compress;
	}

	protected function getHeader() {
		$server = $this->getServer();
		$type = $this->getType();

		$output = '';

		$output .= sprintf('# %s %s %s%s',$type['description'],_('for'),implode('|',array_keys($this->results)),$this->br);
		$output .= sprintf('# %s: %s (%s)%s',_('Server'),$server->getName(),$server->getValue('server','host'),$this->br);
		$output .= sprintf('# %s: %s%s',_('Search Scope'),$this->resultsdata['scope'],$this->br);
		$output .= sprintf('# %s: %s%s',_('Search Filter'),$this->resultsdata['filter'],$this->br);
		$output .= sprintf('# %s: %s%s',_('Total Entries'),$this->items,$this->br);
		$output .= sprintf('#%s',$this->br);
		$output .= sprintf('# Generated by %s (%s) on %s%s',app_name(),get_href('web'),date('F j, Y g:i a'),$this->br);
		$output .= sprintf('# Version: %s%s',app_version(),$this->br);

		$output .= $this->br;

		return $output;
	}

	/**
	 * Helper method to check if the attribute value should be base 64 encoded.
	 *
	 * @param The string to check.
	 * @return boolean true if the string is safe ascii, false otherwise.
	 */
	protected function isSafeAscii($str) {
		for ($i=0;$i<strlen($str);$i++)
			if (ord($str{$i}) < 32 || ord($str{$i}) > 127)
				return false;

		return true;
	}
}

/**
 * Export entries to CSV
 *
 * @package phpLDAPadmin
 * @subpackage Export
 */
class ExportCSV extends Export {
	private $separator = ',';
	private $qualifier = '"';
	private $multivalue_separator = ' | ';
	private $escapeCode = '"';

	static public function getType() {
		return array('type'=>'CSV','description' => 'CSV (Spreadsheet)','extension'=>'csv');
	}

	function export() {
		$server = $this->getServer();

		/* Go thru and find all the attribute names first. This is needed, because, otherwise we have
		 * no idea as to which search attributes were actually populated with data */
		$headers = array('dn');
		$entries = array();
		foreach ($this->results as $base => $results) {
			foreach ($results as $dndetails) {
				array_push($entries,$dndetails);

				unset($dndetails['dn']);
				foreach (array_keys($dndetails) as $key)
					if (! in_array($key,$headers))
						array_push($headers,$key);

			}
		}

		$output = '';
		$num_headers = count($headers);

		# Print out the headers
		for ($i=0; $i<$num_headers; $i++) {
			$output .= sprintf('%s%s%s',$this->qualifier,$headers[$i],$this->qualifier);

			if ($i < $num_headers-1)
				$output .= $this->separator;
		}

		# Drop out our DN header.
		array_shift($headers);
		$num_headers--;

		$output .= $this->br;

		# Loop on every entry
		foreach ($entries as $index => $entry) {
			$dn = $entry['dn'];
			unset($entry['dn']);
			$output .= sprintf('%s%s%s%s',$this->qualifier,$this->LdapEscape($dn),$this->qualifier,$this->separator);

			# Print the attributes
			for ($j=0; $j<$num_headers; $j++) {
				$attr = $headers[$j];
				$output .= $this->qualifier;

				if (array_key_exists($attr,$entry)) {
					$binary_attribute = $server->isAttrBinary($attr) ? 1 : 0;

					if (! is_array($entry[$attr]))
						$attr_values = array($entry[$attr]);
					else
						$attr_values = $entry[$attr];

					$num_attr_values = count($attr_values);

					for ($i=0; $i<$num_attr_values; $i++) {
						if ($binary_attribute)
							$output .= base64_encode($attr_values[$i]);
						else
							$output .= $this->LdapEscape($attr_values[$i]);

						if ($i < $num_attr_values-1)
							$output .= $this->multivalue_separator;
					}
				}

				$output .= $this->qualifier;

				if ($j < $num_headers-1)
					$output .= $this->separator;
			}

			$output .= $this->br;
		}

		if ($this->compress)
			return gzencode($output);
		else
			return $output;
	}

	/**
	 * Function to escape data, where the qualifier happens to also
	 * be in the data.
	 */
	private function LdapEscape ($var) {
		return str_replace($this->qualifier,$this->escapeCode.$this->qualifier,$var);
	}
}

/**
 * Export entries to DSML v.1
 *
 * @package phpLDAPadmin
 * @subpackage Export
 */
class ExportDSML extends Export {
	static public function getType() {
		return array('type'=>'DSML','description' => _('DSML V.1 Export'),'extension'=>'xml');
	}

	/**
	 * Export entries to DSML format
	 */
	function export() {
		$server = $this->getServer();

		# Not very elegant, but do the job for the moment as we have just 4 level
		$indent = array();
		$indent['dir'] = '  ';
		$indent['ent'] = '    ';
		$indent['att'] = '      ';
		$indent['val'] = '        ';

		# Print declaration
		$output = sprintf('<?xml version="1.0"?>%s',$this->br);

		# Print root element
		$output .= sprintf('<dsml>%s',$this->br);

		# Print info related to this export
		$output .= sprintf('<!--%s',$this->br);
		$output .= $this->getHeader();
		$output .= sprintf('-->%s',$this->br);
		$output .= $this->br;

		$output .= sprintf('%s<directory-entries>%s',$indent['dir'],$this->br);

		# Sift through the entries.
		$counter = 0;
		foreach ($this->results as $base => $results) {
			foreach ($results as $dndetails) {
				$counter++;

				$dn = $dndetails['dn'];
				unset($dndetails['dn']);
				ksort($dndetails);

				# Display DN
				$output .= sprintf('%s<entry dn="%s">%s',$indent['ent'],htmlspecialchars($dn),$this->br);

				# Display the objectClass attributes first
				if (isset($dndetails['objectClass'])) {
					if (! is_array($dndetails['objectClass']))
						$dndetails['objectClass'] = array($dndetails['objectClass']);

					$output .= sprintf('%s<objectClass>%s',$indent['att'],$this->br);

					foreach ($dndetails['objectClass'] as $ocValue)
						$output .= sprintf('%s<oc-value>%s</oc-value>%s',$indent['val'],$ocValue,$this->br);

					$output .= sprintf('%s</objectClass>%s',$indent['att'],$this->br);
					unset($dndetails['objectClass']);
				}

				# Display the attributes
				foreach ($dndetails as $key => $attr) {
					if (! is_array($attr))
						$attr = array($attr);

					$output .= sprintf('%s<attr name="%s">%s',$indent['att'],$key,$this->br);

					# If the attribute is binary, set the flag $binary_mode to true
					$binary_mode = $server->isAttrBinary($key) ? 1 : 0;

					foreach ($attr as $value)
						$output .= sprintf('%s<value>%s</value>%s',
							$indent['val'],($binary_mode ? base64_encode($value) : htmlspecialchars($value)),$this->br);

					$output .= sprintf('%s</attr>%s',$indent['att'],$this->br);
				}

				$output .= sprintf('%s</entry>%s',$indent['ent'],$this->br);
			}
		}

		$output .= sprintf('%s</directory-entries>%s',$indent['dir'],$this->br);
		$output .= sprintf('</dsml>%s',$this->br);

		if ($this->compress)
			return gzencode($output);
		else
			return $output;
	}
}

/**
 * Export from LDAP using an LDIF format
 *
 * @package phpLDAPadmin
 * @subpackage Export
 */
class ExportLDIF extends Export {
	# The maximum length of the ldif line
	private $line_length = 76;

	static public function getType() {
		return array('type'=>'LDIF','description' => _('LDIF Export'),'extension'=>'ldif');
	}

	/**
	 * Export entries to LDIF format
	 */
	public function export() {
		if (! $this->results) {
			echo _('Nothing to export');
			return;
		}

		$server = $this->getServer();

		$output = $this->getHeader();

		# Add our version.
		$output .= 'version: 1';
		$output .= $this->br;
		$output .= $this->br;

		# Sift through the entries.
		$counter = 0;
		foreach ($this->results as $base => $results) {
			foreach ($results as $dndetails) {
				$counter++;

				$dn = $dndetails['dn'];
				unset($dndetails['dn']);
				ksort($dndetails);

				$title_string = sprintf('# %s %s: %s%s',_('Entry'),$counter,$dn,$this->br);

				if (strlen($title_string) > $this->line_length-3)
					$title_string = substr($title_string,0,$this->line_length-3).'...'.$this->br;

				$output .= $title_string;

				# Display dn
				if ($this->isSafeAscii($dn))
					$output .= $this->multiLineDisplay(sprintf('dn: %s',$dn));
				else
					$output .= $this->multiLineDisplay(sprintf('dn:: %s',base64_encode($dn)));

				# display the attributes
				foreach ($dndetails as $key => $attr) {
					if (! is_array($attr))
						$attr = array($attr);

					foreach ($attr as $value)
						if (! $this->isSafeAscii($value) || $server->isAttrBinary($key))
							$output .= $this->multiLineDisplay(sprintf('%s:: %s',$key,base64_encode($value)));
						else
							$output .= $this->multiLineDisplay(sprintf('%s: %s',$key,$value));
				}

				$output .= $this->br;
			}
		}

		if ($this->compress)
			return gzencode($output);
		else
			return $output;
	}

	/**
	 * Helper method to wrap ldif lines
	 *
	 * @param The line to be wrapped if needed.
	 */
	private function multiLineDisplay($str) {
		$length_string = strlen($str);
		$length_max = $this->line_length;

		$output = '';
		while ($length_string > $length_max) {
			$output .= substr($str,0,$length_max).$this->br.' ';
			$str = substr($str,$length_max,$length_string);
			$length_string = strlen($str);

			/* Need to do minus one to align on the right
			 * the first line with the possible following lines
			 * as these will have an extra space. */
			$length_max = $this->line_length-1;
		}

		$output .= $str.$this->br;

		return $output;
	}
}

/**
 * Export entries to VCARD v2.1
 *
 * @package phpLDAPadmin
 * @subpackage Export
 */
class ExportVCARD extends Export {
	static public function getType() {
		return array('type'=>'VCARD','description' => _('VCARD 2.1 Export'),'extension'=>'vcf');
	}

	# Mappping one to one attribute
	private $mapping = array(
		'cn' => 'FN',
		'title' => 'TITLE',
		'homephone' => 'TEL;HOME',
		'mobile' => 'TEL;CELL',
		'mail' => 'EMAIL;Internet',
		'labeleduri' =>'URL',
		'o' => 'ORG',
		'audio' => 'SOUND',
		'facsmiletelephoneNumber' =>'TEL;WORK;HOME;VOICE;FAX',
		'jpegphoto' => 'PHOTO;ENCODING=BASE64',
		'businesscategory' => 'ROLE',
		'description' => 'NOTE'
		);

	private $deliveryAddress = array(
		'postofficebox',
		'street',
		'l',
		'st',
		'postalcode',
		'c');

	/**
	 * Export entries to VCARD format
	 */
	function export() {
		$server = $this->getServer();
		$output = '';

		# Sift through the entries.
		foreach ($this->results as $base => $results) {
			foreach ($results as $dndetails) {
				$dndetails = array_change_key_case($dndetails);

				# Check the attributes needed for the delivery address field
				$addr = 'ADR:';
				foreach ($this->deliveryAddress as $attr) {
					if (isset($dndetails[$attr])) {
						$addr .= $dndetails[$attr];
						unset($dndetails[$attr]);
					}

					$addr .= ';';
				}

				$output .= sprintf('BEGIN:VCARD%s',$this->br);

				# Loop for the attributes
				foreach ($dndetails as $key => $attr) {
					if (! is_array($attr))
						$attr = array($attr);

					# If an attribute of the ldap entry exist in the mapping array for vcard
					if (isset($this->mapping[$key])) {

						# Case of organisation. Need to append the possible ou attribute
						if ($key == 'o') {
							$output .= sprintf('%s:%s',$this->mapping[$key],$attr[0]);

							if (isset($entry['ou']))
								foreach ($entry['ou'] as $ou_value)
									$output .= sprintf(';%s',$ou_value);

						# The attribute is binary. (to do : need to fold the line)
						} elseif (in_array($key,array('audio','jpegphoto'))) {
							$output .= $this->mapping[$key].':'.$this->br;
							$output .= ' '.base64_encode($attr[0]);

						} else {
							$output .= $this->mapping[$key].':'.$attr[0];
						}

						$output .= $this->br;
					}
				}

				$output .= sprintf('UID:%s%s',isset($dndetails['entryUUID']) ? $dndetails['entryUUID'] : $dndetails['dn'],$this->br);
				$output .= sprintf('VERSION:2.1%s',$this->br);
				$output .= sprintf('%s%s',$addr,$this->br);
				$output .= sprintf('END:VCARD%s',$this->br);
			}
		}

		if ($this->compress)
			return gzencode($output);
		else
			return $output;
	}
}
?>
