<?php

namespace App\Classes\LDAP\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Represents an LDAP MatchingRule
 */
final class MatchingRule extends Base
{
	private const LOGKEY = 'SMR';

	// This rule's syntax OID
	private(set) ?string $syntax = NULL;

	// An array of attribute names who use this MatchingRule
	private(set) Collection $used_by_attrs;

	/**
	 * Adds an attribute name to the list of attributes who use this MatchingRule
	 */
	public function addUsedByAttr(string $name): void
	{
		$name = trim($name);

		if (! $this->used_by_attrs->contains($name))
			$this->used_by_attrs->push($name);
	}

	/**
	 *  Creates a new MatchingRule object from a raw LDAP MatchingRule string.
	 *
	 * @param string $line
	 * @return void
	 */
	protected function parse(string $line): void
	{
		if (static::DEBUG_VERBOSE)
			Log::debug(sprintf('%s:Parsing MatchingRule [%s]',self::LOGKEY,$line));

		// Init
		$this->used_by_attrs = collect();

		parent::parse($line);
	}

	protected function parse_chunk(array $strings,int &$i): void
	{
		switch ($strings[$i]) {
			case 'SYNTAX':
				$this->syntax = $strings[++$i];

				Log::debug(sprintf('- Case SYNTAX returned (%s)',$this->syntax));
				break;

			default:
				parent::parse_chunk($strings,$i);
		}
	}
}
