<?php

namespace App\Classes\LDAP\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Represents an LDAP schema matchingRuleUse entry
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
class MatchingRuleUse extends Base {
	// An array of attribute names who use this MatchingRule
	private Collection $used_by_attrs;

	function __construct(string $line) {
		Log::debug(sprintf('Parsing MatchingRuleUse [%s]',$line));

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

					$this->name = preg_replace("/^\'(.*)\'$/",'$1',$this->name);

					Log::debug(sprintf(sprintf('- Case NAME returned (%s)',$this->name)));
					break;

				case 'APPLIES':
					if ($strings[$i+1] != '(') {
						// Has a single attribute name
						$this->used_by_attrs = collect($strings[++$i]);

					} else {
						// Has multiple attribute names
						while ($strings[++$i] != ')') {
							$new_attr = $strings[++$i];
							$new_attr = preg_replace("/^\'(.*)\'$/",'$1',$new_attr);

							$this->used_by_attrs->push($new_attr);
						}
					}

					Log::debug(sprintf('- Case APPLIES returned (%s)',$this->used_by_attrs->join(',')));
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

	/**
	 * Gets an array of attribute names (strings) which use this MatchingRuleUse object.
	 *
	 * @return array The array of attribute names (strings).
	 * @deprecated use $this->used_by_attrs
	 */
	public function getUsedByAttrs()
	{
		return $this->used_by_attrs;
	}
}