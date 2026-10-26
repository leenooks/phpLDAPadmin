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
		preg_match_all('/\'(?:\\\\.|[^\'\\\\])*\'|[()]|\$|[^\s(),$]+/',$line,$matches);
		$strings = $matches[0];

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
				if (($strings[$i+1] ?? NULL) === '(') {
					$i += 2;
					$this->name = preg_replace("/^\'(.*)\'$/",'$1',$strings[$i]);

					while (isset($strings[$i]) && $strings[$i] !== ')')
						$i++;

				} else
					$this->name = preg_replace("/^\'(.*)\'$/",'$1',$strings[++$i]);

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
				$this->skipExtensionValue($strings,$i);
				break;

			default:
				if (preg_match('/^X-/i',$strings[$i])) {
					$this->skipExtensionValue($strings,$i);

				} elseif ($i === 1) {
					$this->oid = $strings[$i];

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('%s:- Case default returned OID (%s)',self::LOGKEY,$this->oid));

				} elseif ($strings[$i])
					Log::alert(sprintf('%s:! Case default discovered a value NOT parsed (%s)',self::LOGKEY,$strings[$i]),[
						'parser'=>static::class,
						'token_index'=>$i,
						'schema'=>$this->line,
					]);
		}
	}

	protected function skipExtensionValue(array $strings,int &$i): void
	{
		if (($strings[$i+1] ?? NULL) !== '(') {
			if (isset($strings[$i+1]))
				$i++;

			return;
		}

		$depth = 0;

		do {
			$i++;

			if ($strings[$i] === '(')
				$depth++;
			elseif ($strings[$i] === ')')
				$depth--;

		} while (isset($strings[$i]) && $depth > 0);
	}
}