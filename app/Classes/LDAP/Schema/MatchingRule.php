<?php

namespace App\Classes\LDAP\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Represents an LDAP MatchingRule
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
final class MatchingRule extends Base {
	// This rule's syntax OID
	private ?string $syntax = NULL;

	// An array of attribute names who use this MatchingRule
	private Collection $used_by_attrs;

	/**
	 * Creates a new MatchingRule object from a raw LDAP MatchingRule string.
	 */
	function __construct(string $line) {
		Log::debug(sprintf('Parsing MatchingRule [%s]',$line));

		parent::__construct($line);

		$strings = preg_split('/[\s,]+/',$line,-1,PREG_SPLIT_DELIM_CAPTURE);

		// Init
		$this->used_by_attrs = collect();

		for ($i=0; $i<count($strings); $i++) {
			switch ($strings[$i]) {
				case '(':
				case ')':
					break;

				case 'NAME':
					if ($strings[$i+1] != '(') {
						do {
							$this->name .= (strlen($this->name) ? ' ' : '').$strings[++$i];

						} while (! preg_match("/\'$/s",$strings[$i]));

					} else {
						$i++;

						do {
							$this->name .= (strlen($this->name) ? ' ' : '').$strings[++$i];

						} while (! preg_match("/\'$/s",$strings[$i]));

						do {
							$i++;

						} while (! preg_match('/\)+\)?/',$strings[$i]));
					}

					$this->name = preg_replace("/^\'/",'',$this->name);
					$this->name = preg_replace("/\'$/",'',$this->name);

					Log::debug(sprintf(sprintf('- Case NAME returned (%s)',$this->name)));
					break;

				case 'DESC':
					do {
						$this->description .= (strlen($this->description) ? ' ' : '').$strings[++$i];

					} while (! preg_match("/\'$/s",$strings[$i]));

					$this->description = preg_replace("/^\'(.*)\'$/",'$1',$this->description);

					Log::debug(sprintf('- Case DESC returned (%s)',$this->description));
					break;

				case 'OBSOLETE':
					$this->is_obsolete = TRUE;

					Log::debug(sprintf('- Case OBSOLETE returned (%s)',$this->is_obsolete));
					break;

				case 'SYNTAX':
					$this->syntax = $strings[++$i];

					Log::debug(sprintf('- Case SYNTAX returned (%s)',$this->syntax));
					break;

				default:
					if (preg_match('/[\d\.]+/i',$strings[$i]) && ($i === 1)) {
						$this->oid = $strings[$i];
						Log::debug(sprintf('- Case default returned (%s)',$this->oid));

					} elseif ($strings[$i])
						Log::alert(sprintf('! Case default discovered a value NOT parsed (%s)',$strings[$i]),['line'=>$line]);
			}
		}
	}

	public function __get(string $key): mixed
	{
		switch ($key) {
			case 'syntax': return $this->syntax;
			case 'used_by_attrs': return $this->used_by_attrs;

			default: return parent::__get($key);
		}
	}

	/**
	 * Adds an attribute name to the list of attributes who use this MatchingRule
	 */
	public function addUsedByAttr(string $name): void
	{
		$name = trim($name);

		if ($this->used_by_attrs->search($name) === FALSE)
			$this->used_by_attrs->push($name);
	}

	/**
	 * Gets an array of attribute names (strings) which use this MatchingRule
	 *
	 * @return array The array of attribute names (strings).
	 * @deprecated use $this->used_by_attrs
	 */
	public function getUsedByAttrs()
	{
		return $this->used_by_attrs;
	}

	/**
	 * Sets the list of used_by_attrs to the array specified by $attrs;
	 *
	 * @param Collection $attrs The array of attribute names (strings) which use this MatchingRule
	 */
	public function setUsedByAttrs(Collection $attrs): void
	{
		$this->used_by_attrs = $attrs;
	}
}
