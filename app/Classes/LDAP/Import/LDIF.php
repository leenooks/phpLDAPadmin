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
		$action = self::LDAP_IMPORT_ADD; // Assume add mode
		$subaction = 'add';	// Assume add
		$dn = NULL;
		$attribute = NULL;
		$base64encoded = FALSE;
		$o = NULL;
		$value = '';
		$version = NULL;
		$result = collect();

		foreach (preg_split('/(\r?\n|\r)/',$this->input) as $line) {
			$c++;
			Log::debug(sprintf('%s:LDIF Line [%s] (%d)',self::LOGKEY,$line,$c));
			$line = trim($line);

			// If the line starts with a comment, ignore it
			if (preg_match('/^#/',$line))
				continue;

			// If the line starts with a dash, its more updates to the same entry
			if (preg_match('/^-/',$line)) {
				Log::debug(sprintf('%s:/ DASH Line [%s] (%d)',self::LOGKEY,$line,$c),['action'=>$action,'subaction'=>$subaction,'attribute'=>$attribute,'value'=>$value]);
				if ($attribute)
					$o = $this->entry($o,$attribute,$subaction,$base64encoded ? base64_decode($value) : $value,$c);

				$base64encoded = FALSE;
				$attribute = NULL;
				$value = '';
				continue;
			}

			// If we have a blank line, then that completes this command
			if (! $line) {
				// If we havent got a version yet, then we havent started
				if (! $version) {
					continue;

				} elseif (! is_null($o)) {
					if ($attribute)
						$o = $this->entry($o,$attribute,$subaction,$base64encoded ? base64_decode($value) : $value,$c);

					Log::debug(sprintf('%s:- Committing Entry (More) [%s]',self::LOGKEY,$o->getDN()));

					// Commit
					$result->push($this->commit($o,$action));
					$result->last()->put('line',$c);

					$o = NULL;
					$action = NULL;
					$base64encoded = FALSE;
					$attribute = NULL;
					$value = '';

				} else {
					throw new GeneralException(sprintf('Processing Error - Line exists [%s] on (%d) but object is NULL',$line,$c));
				}

				continue;
			}

			$m = [];
			preg_match('/^([a-zA-Z0-9;-]+)(:+)\s*(.*)$/',$line,$m);

			// If $m is NULL, then this is the 2nd (or more) line of a base64 encoded value
			if (! $m) {
				$value .= $line;
				Log::debug(sprintf('%s:+ attribute [%s] appending [%s] (%d)',self::LOGKEY,$attribute,$line,$c));

				// add to last attr value
				continue;

			} else {
				// If base64 mode was enabled, and there is a following attribute after a base64encoded attribute, it hasnt been processed yet
				if ($base64encoded) {
					Log::debug(sprintf('%s:- Completing base64 attribute [%s]',self::LOGKEY,$attribute));

					$o = $this->entry($o,$attribute,$subaction,base64_decode($value),$c);

					$attribute = NULL;
				}

				$base64encoded = ($m[2] === '::');
				$value = $m[3];

				// If we are base64encoded, we need to loop around
				if ($base64encoded) {
					$attribute = $m[1];
					Log::debug(sprintf('%s:/ Retrieving base64 attribute [%s] (%c)',self::LOGKEY,$attribute,$c));

					continue;
				}
			}

			// changetype needs to be after the dn, and if it isnt we'll assume add.
			if ($dn && Arr::get($m,1) !== 'changetype') {
				if ($action === self::LDAP_IMPORT_ADD) {
					Log::debug(sprintf('%s:Creating new entry [%s]:',self::LOGKEY,$dn),['o'=>$o]);
					$o = new Entry;
					$o->setDn($dn);
					$dn = NULL;

				} else {
					Log::debug(sprintf('%s:Looking for existing entry [%s]:',self::LOGKEY,$dn),['o'=>$o]);
					$o = Entry::find($dn);
					$dn = NULL;

					if (! $o) {
						$result->push(collect(['dn'=>$dn,'result'=>__('Entry doesnt exist')]));
						$result->last()->put('line',$c);;

						continue;
					}
				}
			}

			switch (Arr::get($m,1)) {
				case 'dn':
					$dn = $base64encoded ? base64_decode($value) : $value;
					Log::debug(sprintf('%s:# Got DN [%s]:',self::LOGKEY,$dn));

					$value = '';
					$base64encoded = FALSE;
					break;

				case 'changetype':
					if ($m[2] !== ':')
						throw new GeneralException(sprintf('changetype cannot be base64 encoded set at [%d]. (line %d)',$version,$c));

					if (! is_null($o))
						throw new GeneralException(sprintf('Previous Entry not complete? (line %d)',$c));

					Log::debug(sprintf('%s:- Action [%s]',self::LOGKEY,$m[3]));

					switch ($m[3]) {
						case 'add':
							$action = self::LDAP_IMPORT_ADD;
							break;

						case 'modify':
							$action = self::LDAP_IMPORT_MODIFY;
							break;

						/*
						case 'delete':
							$action = self::LDAP_IMPORT_DELETE;
							break;
						*/

						// @todo modrdn|moddn
						default:
							throw new NotImplementedException(sprintf('Unknown change type [%s]? (line %d)',$m[3],$c));
					}

					break;

				case 'add':
				case 'replace':
					if ($action !== self::LDAP_IMPORT_MODIFY)
						throw new GeneralException(sprintf('%s action can only be used with changetype: modify (line %d)',$m[1],$c));

					$subaction = $m[1];
					break;

				case 'delete':
					$subaction = $m[1];
					$attribute = $m[3];
					$value = NULL;
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
					// Start of a new attribute
					$attribute = $m[1];
					Log::debug(sprintf('%s:- Working with Attribute [%s] with [%s] (%d)',self::LOGKEY,$attribute,$value,$c));

					// We are ready to create the entry or add the attribute
					$o = $this->entry($o,$attribute,$subaction,$base64encoded ? base64_decode($value) : $value,$c);
					$attribute = NULL;
					$value = NULL;
			}

			if ($version !== 1)
				throw new VersionException('LDIF import cannot handle version: '.($version ?: __('NOT DEFINED')));
		}

		// We may still have a pending action
		if ($o) {
			if ($attribute)
				$o = $this->entry($o,$attribute,$subaction,$base64encoded ? base64_decode($value) : $value,$c);

			Log::debug(sprintf('%s:- Committing Entry (Final) [%s]',self::LOGKEY,$o->getDN()));

			// Commit
			$result->push($this->commit($o,$action));
			$result->last()->put('line',$c);
		}

		return $result;
	}

	private function entry(Entry $o,string $attribute,string $subaction,?string $value,int $c): Entry
	{
		Log::debug(sprintf('%s:. %s Attribute [%s] value [%s] (%d)',self::LOGKEY,$subaction,$attribute,$value,$c));

		switch ($subaction) {
			case 'add':
				if (! strlen($value))
					throw new GeneralException(sprintf('Attribute has no value [%s] (line %d)',$attribute,$c));

				$o->addAttributeItem($attribute,$value);
				break;

			case 'replace':
				if (! strlen($value))
					throw new GeneralException(sprintf('Attribute has no value [%s] (line %d)',$attribute,$c));

				if (! ($x=$o->getObject(($xx=strstr($attribute,';',TRUE)) ?: $attribute)))
					throw new \Exception(sprintf('Attribute [%s] doesnt exist in [%s] (line %d)',$attribute,$o->getDn(),$c));

				// If the attribute has changed, we'll assume this is an additional value for it
				if ($x->isDirty()) {
					Log::debug(sprintf('%s:/ Attribute [%s] has changed, assuming add',self::LOGKEY,$attribute));
					$o->addAttributeItem($attribute,$value);

				} else
					$o->{$attribute} = [Entry::TAG_NOTAG=>[$value]];

				break;

			case 'delete':
				if (! $o->getObject($attribute))
					throw new \Exception(sprintf('Attribute [%s] doesnt exist in [%s] (line %d)',$attribute,$o->getDn(),$c));

				$o->{$attribute} = [];
				break;

			default:
				throw new \Exception('Unknown subaction:'.$subaction);
		}

		return $o;
	}
}