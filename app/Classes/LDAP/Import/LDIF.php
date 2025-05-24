<?php

namespace App\Classes\LDAP\Import;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Nette\NotImplementedException;

use App\Classes\LDAP\Import;
use App\Exceptions\Import\{GeneralException,VersionException};
use App\Ldap\Entry;

/**
 * Import LDIF to LDAP using an LDIF format
 *
 *  The LDIF spec is described by RFC2849
 *  http://www.ietf.org/rfc/rfc2849.txt
 */
class LDIF extends Import
{
	private const LOGKEY = 'ILF';

	public function process(): Collection
	{
		$c = 0;
		$action = NULL;
		$attribute = NULL;
		$base64encoded = FALSE;
		$o = NULL;
		$value = '';
		$version = NULL;
		$result = collect();

		// @todo When renaming DNs, the hotlink should point to the new entry on success, or the old entry on failure.
		foreach (preg_split('/(\r?\n|\r)/',$this->input) as $line) {
			$c++;
			Log::debug(sprintf('%s:LDIF Line [%s]',self::LOGKEY,$line));
			$line = trim($line);

			// If the line starts with a comment, ignore it
			if (preg_match('/^#/',$line))
				continue;

			// If we have a blank line, then that completes this command
			if (! $line) {
				if (! is_null($o)) {
					// Add the last attribute;
					$o->addAttributeItem($attribute,$base64encoded ? base64_decode($value) : $value);

					Log::debug(sprintf('%s:- Committing Entry [%s]',self::LOGKEY,$o->getDN()));

					// Commit
					$result->push($this->commit($o,$action));
					$result->last()->put('line',$c);

					$o = NULL;
					$action = NULL;
					$base64encoded = FALSE;
					$attribute = NULL;
					$value = '';
				}

				continue;
			}

			$m = [];
			preg_match('/^([a-zA-Z0-9;-]+)(:+)\s+(.*)$/',$line,$m);

			switch (Arr::get($m,1)) {
				case 'changetype':
					if ($m[2] !== ':')
						throw new GeneralException(sprintf('ChangeType cannot be base64 encoded set at [%d]. (line %d)',$version,$c));

					switch ($m[3]) {
						// if (preg_match('/^changetype:[ ]*(delete|add|modrdn|moddn|modify)/i',$lines[0])) {
						default:
							throw new NotImplementedException(sprintf('Unknown change type [%s]? (line %d)',$m[3],$c));
					}

					break;

				case 'version':
					if (! is_null($version))
						throw new VersionException(sprintf('Version has already been set at [%d]. (line %d)',$version,$c));

					if ($m[2] !== ':')
						throw new VersionException(sprintf('Version cannot be base64 encoded set at [%d]. (line %d)',$version,$c));

					$version = (int)$m[3];
					break;

				// Treat it as an attribute
				default:
					// If $m is NULL, then this is the 2nd (or more) line of a base64 encoded value
					if (! $m) {
						$value .= $line;
						Log::debug(sprintf('%s:- Attribute [%s] adding [%s] (%d)',self::LOGKEY,$attribute,$line,$c));

						// add to last attr value
						continue 2;
					}

					// We are ready to create the entry or add the attribute
					if ($attribute) {
						if ($attribute === 'dn') {
							if (! is_null($o))
								throw new GeneralException(sprintf('Previous Entry not complete? (line %d)',$c));

							$dn = $base64encoded ? base64_decode($value) : $value;
							Log::debug(sprintf('%s:Creating new entry:',self::LOGKEY,$dn));
							//$o = Entry::find($dn);

							// If it doesnt exist, we'll create it
							//if (! $o) {
								$o = new Entry;
								$o->setDn($dn);
							//}

							$action = self::LDAP_IMPORT_ADD;

						} else {
							Log::debug(sprintf('%s:Adding Attribute [%s] value [%s] (%d)',self::LOGKEY,$attribute,$value,$c));

							if ($value)
								$o->addAttributeItem($attribute,$base64encoded ? base64_decode($value) : $value);
							else
								throw new GeneralException(sprintf('Attribute has no value [%s] (line %d)',$attribute,$c));
						}
					}

					// Start of a new attribute
					$base64encoded = ($m[2] === '::');
					$attribute = $m[1];
					$value = $m[3];

					Log::debug(sprintf('%s:- New Attribute [%s] with [%s] (%d)',self::LOGKEY,$attribute,$value,$c));
			}

			if ($version !== 1)
				throw new VersionException('LDIF import cannot handle version: '.($version ?: __('NOT DEFINED')));
		}

		// We may still have a pending action
		if ($action) {
			// Add the last attribute;
			$o->addAttributeItem($attribute,$base64encoded ? base64_decode($value) : $value);

			Log::debug(sprintf('%s:- Committing Entry [%s]',self::LOGKEY,$o->getDN()));

			// Commit
			$result->push($this->commit($o,$action));
			$result->last()->put('line',$c);
		}

		return $result;
	}

	public function xreadEntry() {
		static $haveVersion = FALSE;

		if ($lines = $this->nextLines()) {

			$server = $this->getServer();

			# The first line should be the DN
			if (preg_match('/^dn:/',$lines[0])) {
				list($text,$dn) = $this->getAttrValue(array_shift($lines));

				# The second line should be our changetype
				if (preg_match('/^changetype:[ ]*(delete|add|modrdn|moddn|modify)/i',$lines[0])) {
					$attrvalue = $this->getAttrValue($lines[0]);
					$changetype = $attrvalue[1];
					array_shift($lines);

				} else
					$changetype = 'add';

				$this->template = new Template($this->server_id,NULL,NULL,$changetype);

				switch ($changetype) {
					case 'add':
						$rdn = get_rdn($dn);
						$container = $server->getContainer($dn);

						$this->template->setContainer($container);
						$this->template->accept();

						$this->getAddDetails($lines);
						$this->template->setRDNAttributes($rdn);

						return $this->template;

						break;

					case 'modify':
						if (! $server->dnExists($dn))
							return $this->error(sprintf('%s %s',_('DN does not exist'),$dn),$lines);

						$this->template->setDN($dn);
						$this->template->accept(FALSE,TRUE);

						return $this->getModifyDetails($lines);

						break;

					case 'moddn':
					case 'modrdn':
						if (! $server->dnExists($dn))
							return $this->error(sprintf('%s %s',_('DN does not exist'),$dn),$lines);

						$this->template->setDN($dn);
						$this->template->accept();

						return $this->getModRDNAttributes($lines);

						break;

					default:
						if (! $server->dnExists($dn))
							return $this->error(_('Unknown change type'),$lines);
				}

			} else
				return $this->error(_('A valid dn line is required'),$lines);

		} else
			return FALSE;
	}
}