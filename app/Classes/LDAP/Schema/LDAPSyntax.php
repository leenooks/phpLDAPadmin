<?php

namespace App\Classes\LDAP\Schema;

use Illuminate\Support\Facades\Log;

/**
 * Represents an LDAP Syntax
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
final class LDAPSyntax extends Base {
	// Is human readable?
	private ?bool $is_not_human_readable = NULL;

	// Binary transfer required?
	private ?bool $binary_transfer_required = NULL;

	/**
	 * Creates a new Syntax object from a raw LDAP syntax string.
	 */
	public function __construct(string $line) {
		Log::debug(sprintf('Parsing LDAPSyntax [%s]',$line));

		parent::__construct($line);

		$strings = preg_split('/[\s,]+/',$line,-1,PREG_SPLIT_DELIM_CAPTURE);

		for ($i=0; $i<count($strings); $i++) {
			switch($strings[$i]) {
				case '(':
				case ')':
					break;

				case 'DESC':
					do {
						$this->description .= (strlen($this->description) ? ' ' : '').$strings[++$i];

					} while (! preg_match("/\'$/s",$strings[$i]));

					$this->description = preg_replace("/^\'(.*)\'$/",'$1',$this->description);

					Log::debug(sprintf('- Case DESC returned (%s)',$this->description));
					break;

				case 'X-BINARY-TRANSFER-REQUIRED':
					$this->binary_transfer_required = (str_replace("'",'',$strings[++$i]) === 'TRUE');

					Log::debug(sprintf('- Case X-BINARY-TRANSFER-REQUIRED returned (%s)',$this->binary_transfer_required));
					break;

				case 'X-NOT-HUMAN-READABLE':
					$this->is_not_human_readable = (str_replace("'",'',$strings[++$i]) === 'TRUE');

					Log::debug(sprintf('- Case X-NOT-HUMAN-READABLE returned (%s)',$this->is_not_human_readable));
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
			case 'binary_transfer_required': return $this->binary_transfer_required;
			case 'is_not_human_readable': return $this->is_not_human_readable;

			default: return parent::__get($key);
		}
	}
}