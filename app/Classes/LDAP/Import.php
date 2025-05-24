<?php

namespace App\Classes\LDAP;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LdapRecord\LdapRecordException;

use App\Exceptions\Import\GeneralException;
use App\Ldap\Entry;

/**
 * Import Class
 *
 * This abstract classes provides all the common methods and variables for the
 * import classes.
 */
abstract class Import
{
	private const LOGKEY = 'aI-';

	// Valid LDIF commands
	protected const LDAP_IMPORT_ADD = 1;
	protected const LDAP_IMPORT_DELETE = 2;
	protected const LDAP_IMPORT_MODRDN = 3;
	protected const LDAP_IMPORT_MODDN = 4;
	protected const LDAP_IMPORT_MODIFY = 5;

	protected const LDAP_ACTIONS = [
		'add' => self::LDAP_IMPORT_ADD,
		'delete' => self::LDAP_IMPORT_DELETE,
		'modrdn' => self::LDAP_IMPORT_MODRDN,
		'moddn' => self::LDAP_IMPORT_MODDN,
		'modify' => self::LDAP_IMPORT_MODIFY,
	];

	// The import data to process
	protected string $input;
	// The attributes the server knows about
	protected Collection $server_attributes;

	public function __construct(string $input) {
		$this->input = $input;
		$this->server_attributes = config('server')->schema('attributetypes');
	}

	/**
	 * Attempt to commit an entry and return the result.
	 *
	 * @param Entry $o
	 * @param int $action
	 * @return Collection
	 * @throws GeneralException
	 */
	final protected function commit(Entry $o,int $action): Collection
	{
		switch ($action) {
			case static::LDAP_IMPORT_ADD:
				try {
					$o->save();

				} catch (LdapRecordException $e) {
					Log::error(sprintf('%s:Import Commit Error',self::LOGKEY),['e'=>$e->getMessage(),'detailed'=>$e->getDetailedError()]);

					if ($e->getDetailedError())
						return collect([
							'dn'=>$o->getDN(),
							'result'=>sprintf('%d: %s (%s)',
								($x=$e->getDetailedError())->getErrorCode(),
								$x->getErrorMessage(),
								$x->getDiagnosticMessage(),
							)
						]);
					else
						return collect([
							'dn'=>$o->getDN(),
							'result'=>sprintf('%d: %s',
								$e->getCode(),
								$e->getMessage(),
							)
						]);
				}

				Log::debug(sprintf('%s:Import Commited',self::LOGKEY));

				return collect(['dn'=>$o->getDN(),'result'=>__('Created')]);

			default:
				throw new GeneralException('Unhandled action during commit: '.$action);
		}
	}

	abstract public function process(): Collection;
}