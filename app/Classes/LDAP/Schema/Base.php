<?php

namespace App\Classes\LDAP\Schema;

use Illuminate\Support\Facades\Log;

use App\Exceptions\InvalidUsage;

/**
 * Generic parent class for all schema items.
 *
 * A schema item is an ObjectClass, an AttributeBype, a MatchingRule, or a Syntax.
 * All schema items have at least two things in common: An OID and a Description.
 */
abstract class Base
{
	private const LOGKEY = 'Sb-';

	protected const DEBUG_VERBOSE = FALSE;

	// Record the LDAP String
	private(set) string $line;

	// The schema item's name.
	protected(set) string $name = '';

	// The OID of this schema item.
	protected(set) string $oid = '';

	# The description of this schema item.
	protected(set) string $description = '';

	// Boolean value indicating whether this objectClass is obsolete
	private(set) bool $is_obsolete = FALSE;

	public function __construct(string $line)
	{
		$this->line = $line;

		$this->parse($line);
	}

	public function __get(string $key): mixed
	{
		switch ($key) {
			case 'name_lc': return strtolower($this->name);

			default:
				throw new InvalidUsage('Unknown key:'.$key);
		}
	}

	public function __isset(string $key): bool
	{
		return isset($this->{$key});
	}

	public function __toString(): string
	{
		return $this->oid;
	}

	protected function parse(string $line): void
	{
		$strings = preg_split('/[\s,]+/',$line,-1,PREG_SPLIT_DELIM_CAPTURE);

		for ($i=0; $i < count($strings); $i++) {
			$this->parse_chunk($strings,$i);
		}
	}

	protected function parse_chunk(array $strings,int &$i): void
	{
		switch ($strings[$i]) {
			case '(':
			case ')':
				break;

			case 'NAME':
				if ($strings[$i+1] !== '(') {
					do {
						$this->name .= (strlen($this->name) ? ' ' : '').$strings[++$i];
					} while (! preg_match('/\'$/s',$strings[$i]));

				} else {
					$i++;

					do {
						$this->name .= (strlen($this->name) ? ' ' : '').$strings[++$i];
					} while (! preg_match('/\'$/s',$strings[$i]));

					do {
						$i++;
					} while (! preg_match('/\)+\)?/',$strings[$i]));
				}

				$this->name = preg_replace("/^\'(.*)\'$/",'$1',$this->name);

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case NAME returned (%s)',self::LOGKEY,$this->name));
				break;

			case 'DESC':
				do {
					$this->description .= (strlen($this->description) ? ' ' : '').$strings[++$i];

				} while (! preg_match('/\'$/s',$strings[$i]));

				$this->description = preg_replace("/^\'(.*)\'$/",'$1',$this->description);

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case DESC returned (%s)',self::LOGKEY,$this->description));
				break;

			case 'OBSOLETE':
				$this->is_obsolete = TRUE;

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case OBSOLETE returned (%s)',self::LOGKEY,$this->is_obsolete));
				break;

			// @note currently not captured
			case 'X-SUBST':
			case 'X-ORDERED':
			case 'X-EQUALITY':
			case 'X-ORIGIN':
				$value = '';

				do {
					$value .= ($value ? ' ' : '').preg_replace('/^\'(.+)\'$/','$1',$strings[++$i]);

				} while (! preg_match("/\'$/s",$strings[$i]));

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case [%s] returned (%s) - IGNORED',self::LOGKEY,$strings[$i],$value));

				break;

			default:
				if (preg_match('/[\d\.]+/i',$strings[$i]) && ($i === 1)) {
					$this->oid = $strings[$i];

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('%s:- Case default returned OID (%s)',self::LOGKEY,$this->oid));

				} elseif ($strings[$i])
					Log::alert(sprintf('%s:! Case default discovered a value NOT parsed (%s)',self::LOGKEY,$strings[$i]));
		}
	}
}