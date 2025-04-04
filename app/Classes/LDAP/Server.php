<?php

namespace App\Classes\LDAP;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\Model;
use LdapRecord\Query\Collection as LDAPCollection;
use LdapRecord\Query\ObjectNotFoundException;

use App\Classes\LDAP\Schema\{AttributeType,Base,LDAPSyntax,MatchingRule,MatchingRuleUse,ObjectClass};
use App\Exceptions\InvalidUsage;
use App\Ldap\Entry;

final class Server
{
	// Connection information used for these object and children
	private ?string $connection;

	// This servers schema objectclasses
	private Collection $attributetypes;
	private Collection $ldapsyntaxes;
	private Collection $matchingrules;
	private Collection $matchingruleuse;
	private Collection $objectclasses;

	/* ObjectClass Types */
	public const OC_STRUCTURAL = 0x01;
	public const OC_ABSTRACT = 0x02;
	public const OC_AUXILIARY = 0x03;

	public function __construct(?string $connection=NULL)
	{
		$this->connection = $connection;
	}

	public function __get(string $key): mixed
	{
		return match($key) {
			'attributetypes' => $this->attributetypes,
			'connection' => $this->connection,
			'ldapsyntaxes' => $this->ldapsyntaxes,
			'matchingrules' => $this->matchingrules,
			'objectclasses' => $this->objectclasses,
			'config' => config('ldap.connections.'.config('ldap.default')),
			'name' => Arr::get($this->config,'name',__('No Server Name Yet')),
			default => throw new Exception('Unknown key:' . $key),
		};
	}

	/* STATIC METHODS */

	/**
	 * Gets the root DN of the specified LDAPServer, or throws an exception if it
	 * can't find it.
	 *
	 * @param string|null $connection Return a collection of baseDNs
	 * @param bool $objects Return a collection of Entry Models
	 * @return Collection
	 * @throws ObjectNotFoundException
	 * @testedin GetBaseDNTest::testBaseDNExists();
	 * @todo Need to allow for the scenario if the baseDN is not readable by ACLs
	 */
	public static function baseDNs(?string $connection=NULL,bool $objects=TRUE): Collection
	{
		$cachetime = Carbon::now()
			->addSeconds(Config::get('ldap.cache.time'));

		try {
			$base = self::rootDSE($connection,$cachetime);

		/**
		 * LDAP Error Codes:
		 * https://ldap.com/ldap-result-code-reference/
		 * + success						0
		 * + operationsError				1
		 * + protocolError					2
		 * + timeLimitExceeded				3
		 * + sizeLimitExceeded				4
		 * + compareFalse					5
		 * + compareTrue					6
		 * + authMethodNotSupported			7
		 * + strongerAuthRequired			8
		 * + referral						10
		 * + adminLimitExceeded				11
		 * + unavailableCriticalExtension	12
		 * + confidentialityRequired		13
		 * + saslBindInProgress				14
		 * + noSuchAttribute				16
		 * + undefinedAttributeType			17
		 * + inappropriateMatching			18
		 * + constraintViolation			19
		 * + attributeOrValueExists			20
		 * + invalidAttributeSyntax			21
		 * + noSuchObject					32
		 * + aliasProblem					33
		 * + invalidDNSyntax				34
		 * + isLeaf							35
		 * + aliasDereferencingProblem		36
		 * + inappropriateAuthentication	48
		 * + invalidCredentials				49
		 * + insufficientAccessRights		50
		 * + busy							51
		 * + unavailable					52
		 * + unwillingToPerform				53
		 * + loopDetect						54
		 * + sortControlMissing				60
		 * + offsetRangeError				61
		 * + namingViolation				64
		 * + objectClassViolation			65
		 * + notAllowedOnNonLeaf			66
		 * + notAllowedOnRDN				67
		 * + entryAlreadyExists				68
		 * + objectClassModsProhibited		69
		 * + resultsTooLarge				70
		 * + affectsMultipleDSAs			71
		 * + virtualListViewError or controlError	76
		 * + other							80
		 * + serverDown						81
		 * + localError						82
		 * + encodingError					83
		 * + decodingError					84
		 * + timeout						85
		 * + authUnknown					86
		 * + filterError					87
		 * + userCanceled					88
		 * + paramError						89
		 * + noMemory						90
		 * + connectError					91
		 * + notSupported					92
		 * + controlNotFound				93
		 * + noResultsReturned				94
		 * + moreResultsToReturn			95
		 * + clientLoop						96
		 * + referralLimitExceeded			97
		 * + invalidResponse				100
		 * + ambiguousResponse				101
		 * + tlsNotSupported				112
		 * + intermediateResponse			113
		 * + unknownType					114
		 * + canceled						118
		 * + noSuchOperation				119
		 * + tooLate						120
		 * + cannotCancel					121
		 * + assertionFailed				122
		 * + authorizationDenied			123
		 * + e-syncRefreshRequired			4096
		 * + noOperation					16654
		 *
		 * LDAP Tag Codes:
		 * + A client bind operation				97
		 * + The entry for which you were searching	100
		 * + The result from a search operation		101
		 * + The result from a modify operation		103
		 * + The result from an add operation		105
		 * + The result from a delete operation		107
		 * + The result from a modify DN operation	109
		 * + The result from a compare operation	111
		 * + A search reference when the entry you perform your search on holds a referral to the entry you require.
		 * +   Search references are expressed in terms of a referral.
		 * 											115
		 * + A result from an extended operation	120
		 */
		// If we cannot get to our LDAP server we'll head straight to the error page
		} catch (LdapRecordException $e) {
			switch ($e->getDetailedError()?->getErrorCode()) {
				case 49:
					// Since we failed authentication, we should delete our auth cookie
					if (Cookie::has('password_encrypt')) {
						Log::alert('Clearing user credentials and logging out');

						Cookie::queue(Cookie::forget('password_encrypt'));
						Cookie::queue(Cookie::forget('username_encrypt'));

						Session::invalidate();
					}

					abort(401,$e->getDetailedError()->getErrorMessage());

				default:
					abort(597,$e->getDetailedError()?->getErrorMessage() ?: $e->getMessage());
			}
		}

		if (! $objects)
			return collect($base->namingcontexts);

		/**
		 * @note While we are caching our baseDNs, it seems if we have more than 1,
		 * our caching doesnt generate a hit on a subsequent call to this function (before the cache expires).
		 * IE: If we have 5 baseDNs, it takes 5 calls to this function to case them all.
		 * @todo Possibly a bug wtih ldaprecord, so need to investigate
		 */
		$result = collect();
		foreach ($base->namingcontexts as $dn)
			$result->push((new Entry)->cache($cachetime)->findOrFail($dn));

		return $result;
	}

	/**
	 * Obtain the rootDSE for the server, that gives us server information
	 *
	 * @param string|null $connection
	 * @param Carbon|null $cachetime
	 * @return Entry|null
	 * @throws ObjectNotFoundException
	 * @testedin TranslateOidTest::testRootDSE();
	 */
	public static function rootDSE(?string $connection=NULL,?Carbon $cachetime=NULL): ?Model
	{
		$e = new Entry;

		return Entry::on($connection ?? $e->getConnectionName())
			->cache($cachetime)
			->in(NULL)
			->read()
			->select(['+'])
			->whereHas('objectclass')
			->firstOrFail();
	}

	/**
	 * Get the Schema DN
	 *
	 * @param string|null $connection
	 * @return string
	 * @throws ObjectNotFoundException
	 */
	public static function schemaDN(?string $connection=NULL): string
	{
		$cachetime = Carbon::now()->addSeconds(Config::get('ldap.cache.time'));

		return collect(self::rootDSE($connection,$cachetime)->subschemasubentry)->first();
	}

	/**
	 * Query the server for a DN and return its children and if those children have children.
	 *
	 * @param string $dn
	 * @param array $attrs
	 * @return LDAPCollection|NULL
	 */
	public function children(string $dn,array $attrs=['dn']): ?LDAPCollection
	{
		return ($x=(new Entry)
			->on($this->connection)
			->cache(Carbon::now()->addSeconds(Config::get('ldap.cache.time')))
			->select(array_merge($attrs,[
				'hassubordinates',	// Needed for the tree to know if an entry has children
				'c'					// Needed for the tree to show icons for countries
			]))
			->setDn($dn)
			->list()
			->orderBy('dn')
			->get()) ? $x : NULL;
	}

	/**
	 * Fetch a DN from the server
	 *
	 * @param string $dn
	 * @param array $attrs
	 * @return Entry|null
	 */
	public function fetch(string $dn,array $attrs=['*','+']): ?Entry
	{
		return ($x=(new Entry)
			->on($this->connection)
			->cache(Carbon::now()->addSeconds(Config::get('ldap.cache.time')))
			->select($attrs)
			->find($dn)) ? $x : NULL;
	}

	/**
	 * This function determines if the specified attribute is contained in the force_may list
	 * as configured in config.php.
	 *
	 * @return boolean True if the specified attribute is configured to be force as a may attribute
	 */
	public function isForceMay($attr_name): bool
	{
		return in_array($attr_name,config('pla.force_may',[]));
	}

	/**
	 * Does this server support RFC3666 language tags
	 * OID: 1.3.6.1.4.1.4203.1.5.4
	 *
	 * @return bool
	 * @throws ObjectNotFoundException
	 */
	public function isLanguageTags(): bool
	{
		return in_array('1.3.6.1.4.1.4203.1.5.4',$this->rootDSE()->supportedfeatures);
	}

	/**
	 * Return the server's schema
	 *
	 * @param string $item Schema Item to Fetch
	 * @param string|null $key
	 * @return Collection|LDAPSyntax|Base|NULL
	 */
	public function schema(string $item,?string $key=NULL): Collection|LDAPSyntax|Base|NULL
	{
		// Ensure our item to fetch is lower case
		$item = strtolower($item);
		if ($key)
			$key = strtolower($key);

		$result = Cache::remember('schema'.$item,config('ldap.cache.time'),function() use ($item) {
			// First pass if we have already retrieved the schema item
			switch ($item) {
				case 'attributetypes':
					if (isset($this->attributetypes))
						return $this->attributetypes;
					else
						$this->attributetypes = collect();

					break;

				case 'ldapsyntaxes':
					if (isset($this->ldapsyntaxes))
						return $this->ldapsyntaxes;
					else
						$this->ldapsyntaxes = collect();

					break;

				case 'matchingrules':
					if (isset($this->matchingrules))
						return $this->matchingrules;
					else
						$this->matchingrules = collect();

					break;

				case 'objectclasses':
					if (isset($this->objectclasses))
						return $this->objectclasses;
					else
						$this->objectclasses = collect();

					break;

				// This error message is not localized as only developers should ever see it
				default:
					throw new InvalidUsage('Invalid request to fetch schema: '.$item);
			}

			// Try to get the schema DN from the specified entry.
			$schema_dn = $this->schemaDN($this->connection);
			$schema = $this->fetch($schema_dn);

			// If our schema's null, we didnt find it.
			if (! $schema)
				throw new Exception('Couldnt find schema at:'.$schema_dn);

			switch ($item) {
				case 'attributetypes':
					Log::debug('Attribute Types');
					// build the array of attribueTypes
					//$syntaxes = $this->SchemaSyntaxes($dn);

					foreach ($schema->{$item} as $line) {
						if (is_null($line) || ! strlen($line))
							continue;

						$o = new AttributeType($line);
						$this->attributetypes->put($o->name_lc,$o);
					}

					// go back and add data from aliased attributeTypes
					foreach ($this->attributetypes as $o) {
						/* foreach of the attribute's aliases, create a new entry in the attrs array
						 * with its name set to the alias name, and all other data copied.*/

						if ($o->aliases->count()) {
							Log::debug(sprintf('\ Attribute [%s] has the following aliases [%s]',$o->name,$o->aliases->join(',')));

							foreach ($o->aliases as $alias) {
								$new_attr = clone $o;
								$new_attr->setName($alias);
								$new_attr->addAlias($o->name);
								$new_attr->removeAlias($alias);

								$this->attributetypes->put(strtolower($alias),$new_attr);
							}
						}
					}

					// Now go through and reference the parent/child relationships
					foreach ($this->attributetypes as $o)
						if ($o->sup_attribute) {
							$parent = strtolower($o->sup_attribute);

							if ($this->attributetypes->has($parent) !== FALSE)
								$this->attributetypes[$parent]->addChild($o->name);
						}

					// go through any children and add details if the child doesnt have them (ie, cn inherits name)
					// @todo This doesnt traverse children properly, so children of children may not get the settings they should
					foreach ($this->attributetypes as $parent) {
						foreach ($parent->children as $child) {
							$child = strtolower($child);

							/* only overwrite the child's SINGLE-VALUE property if the parent has it set, and the child doesnt
							 * (note: All LDAP attributes default to multi-value if not explicitly set SINGLE-VALUE) */
							if (! is_null($parent->is_single_value) && is_null($this->attributetypes[$child]->is_single_value))
								$this->attributetypes[$child]->setIsSingleValue($parent->is_single_value);
						}
					}

					// Add the used in and required_by values.
					foreach ($this->schema('objectclasses') as $object_class) {
						$must_attrs = $object_class->getMustAttrNames();
						$may_attrs = $object_class->getMayAttrNames();
						$oclass_attrs = $must_attrs->merge($may_attrs)->unique();

						// Add Used In.
						foreach ($oclass_attrs as $attr_name)
							if ($this->attributetypes->has(strtolower($attr_name)))
								$this->attributetypes[strtolower($attr_name)]->addUsedInObjectClass($object_class->name,$object_class->isStructural());

						// Add Required By.
						foreach ($must_attrs as $attr_name)
							if ($this->attributetypes->has(strtolower($attr_name)))
								$this->attributetypes[strtolower($attr_name)]->addRequiredByObjectClass($object_class->name,$object_class->isStructural());

						// Force May
						foreach ($object_class->getForceMayAttrs() as $attr_name)
							if ($this->attributetypes->has(strtolower($attr_name->name)))
								$this->attributetypes[strtolower($attr_name->name)]->setForceMay();
					}

					return $this->attributetypes;

				case 'ldapsyntaxes':
					Log::debug('LDAP Syntaxes');

					foreach ($schema->{$item} as $line) {
						if (is_null($line) || ! strlen($line))
							continue;

						$o = new LDAPSyntax($line);
						$this->ldapsyntaxes->put(strtolower($o->oid),$o);
					}

					return $this->ldapsyntaxes;

				case 'matchingrules':
					Log::debug('Matching Rules');
					$this->matchingruleuse = collect();

					foreach ($schema->{$item} as $line) {
						if (is_null($line) || ! strlen($line))
							continue;

						$o = new MatchingRule($line);
						$this->matchingrules->put($o->name_lc,$o);
					}

					/*
					 * For each MatchingRuleUse entry, add the attributes who use it to the
					 * MatchingRule in the $rules array.
					 */
					if ($schema->matchingruleuse) {
						foreach ($schema->matchingruleuse as $line) {
							if (is_null($line) || ! strlen($line))
								continue;

							$o = new MatchingRuleUse($line);
							$this->matchingruleuse->put($o->name_lc,$o);

							if ($this->matchingrules->has($o->name_lc) !== FALSE)
								$this->matchingrules[$o->name_lc]->setUsedByAttrs($o->getUsedByAttrs());
						}

					} else {
						/* No MatchingRuleUse entry in the subschema, so brute-forcing
						 * the reverse-map for the "$rule->getUsedByAttrs()" data.*/
						foreach ($this->schema('attributetypes') as $attr) {
							$rule_key = strtolower($attr->getEquality());

							if ($this->matchingrules->has($rule_key) !== FALSE)
								$this->matchingrules[$rule_key]->addUsedByAttr($attr->name);
						}
					}

					return $this->matchingrules;

				case 'objectclasses':
					Log::debug('Object Classes');

					foreach ($schema->{$item} as $line) {
						if (is_null($line) || ! strlen($line))
							continue;

						$o = new ObjectClass($line,$this);
						$this->objectclasses->put($o->name_lc,$o);
					}

					// Now go through and reference the parent/child relationships
					foreach ($this->objectclasses as $o)
						foreach ($o->getSupClasses() as $parent) {
							$parent = strtolower($parent);

							if (! $this->objectclasses->contains($parent))
								$this->objectclasses[$parent]->addChildObjectClass($o->name);
						}

					return $this->objectclasses;

				// Shouldnt get here
				default:
					throw new InvalidUsage('Invalid request to fetch schema: '.$item);
			}
		});

		return is_null($key) ? $result : $result->get($key);
	}

	/**
	 * Given an OID, return the ldapsyntax for the OID
	 *
	 * @param string $oid
	 * @return LDAPSyntax|null
	 */
	public function schemaSyntaxName(string $oid): ?LDAPSyntax
	{
		return $this->schema('ldapsyntaxes',$oid);
	}
}