<?php

namespace App\Classes\LDAP\Schema;

use Illuminate\Support\Facades\Log;

/**
 * Represents an LDAP Syntax
 */
final class LDAPSyntax extends Base
{
	private const LOGKEY = 'SLS';

	// Is human readable?
	private(set) ?bool $is_not_human_readable = NULL;

	// Binary transfer required?
	private(set) ?bool $binary_transfer_required = NULL;

	/**
	 * Creates a new Syntax object from a raw LDAP syntax string.
	 */
	protected function parse(string $line): void
	{
		if (static::DEBUG_VERBOSE)
			Log::debug(sprintf('%s:Parsing LDAPSyntax [%s]',self::LOGKEY,$line));

		parent::parse($line);
	}

	protected function parse_chunk(array $strings,int &$i): void
	{
		for ($i=0; $i<count($strings); $i++) {
			switch($strings[$i]) {
				case 'X-BINARY-TRANSFER-REQUIRED':
					$this->binary_transfer_required = (str_replace("'",'',$strings[++$i]) === 'TRUE');

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('%s:- Case X-BINARY-TRANSFER-REQUIRED returned (%s)',self::LOGKEY,$this->binary_transfer_required));
					break;

				case 'X-NOT-HUMAN-READABLE':
					$this->is_not_human_readable = (str_replace("'",'',$strings[++$i]) === 'TRUE');

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('%s:- Case X-NOT-HUMAN-READABLE returned (%s)',self::LOGKEY,$this->is_not_human_readable));
					break;

				default:
					parent::parse_chunk($strings,$i);
			}
		}
	}
}