<?php

namespace App\Classes\LDAP;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\Model;
use LdapRecord\Query\Builder;
use LdapRecord\Query\Collection as LDAPCollection;
use LdapRecord\Query\ObjectNotFoundException;

use App\Classes\LDAP\Schema\{AttributeType,Base,LDAPSyntax,MatchingRule,ObjectClass};
use App\Exceptions\InvalidUsage;
use App\Ldap\Entry;

final class Server
{
	private const LOGKEY = 'SVR';

	// This servers schema objectclasses
	private Collection $attributetypes;
	private Collection $ldapsyntaxes;
	private Collection $matchingrules;
	private Collection $objectclasses;

	private Entry $rootDSE;

	/* ObjectClass Types */
	public const OC_STRUCTURAL = 0x01;
	public const OC_ABSTRACT = 0x02;
	public const OC_AUXILIARY = 0x03;

	public function __construct()
	{
		$this->rootDSE = self::rootDSE();

		$this->attributetypes = collect();
		$this->ldapsyntaxes = collect();
		$this->matchingrules = collect();
		$this->objectclasses = collect();
	}

	public function __get(string $key): mixed
	{
		return match($key) {
			'attributetypes' => $this->attributetypes,
			'ldapsyntaxes' => $this->ldapsyntaxes,
			'matchingrules' => $this->matchingrules,
			'objectclasses' => $this->objectclasses,
			'config' => config(sprintf('ldap.connections.%s',config('ldap.default'))),
			'name' => Arr::get($this->config,'name',__('No Server Name Yet')),
			default => throw new Exception('Unknown key:'.$key),
		};
	}

	/* STATIC METHODS */

	/**
	 * Gets the root DN of the specified LDAPServer, or throws an exception if it
	 * can't find it.
	 *
	 * @param bool $objects Return a collection of Entry Models
	 * @return Collection
	 * @testedin GetBaseDNTest::testBaseDNExists();
	 */
	public static function baseDNs(bool $objects=TRUE): Collection
	{
		try {
			$rootdse = self::rootDSE();

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
					abort(401,$e->getDetailedError()->getErrorMessage());

				default:
					abort(597,$e->getDetailedError()?->getErrorMessage() ?: $e->getMessage());
			}
		}

		if (! $objects)
			return collect($rootdse->namingcontexts ?: []);

		return Cache::remember('basedns'.Session::id(),config('ldap.cache.time'),function() use ($rootdse) {
			$result = collect();

			// @note: Incase our rootDSE didnt return a namingcontext, we'll have no base DNs
			foreach (($rootdse->namingcontexts ?: []) as $dn)
				$result->push(self::get($dn)->read()->find($dn));

			return $result->filter()->sort(fn($item)=>$item->sort_key);
		});
	}

	/**
	 * Work out if we should flush the cache when retrieving an entry
	 *
	 * @param string $dn
	 * @return bool
	 * @note: We dont need to flush the cache for internal LDAP attributes, as we dont change them
	 */
	private static function cacheflush(string $dn): bool
	{
		$cache = (! config('ldap.cache.enabled'))
			|| match (strtolower($dn)) {
				'','cn=schema','cn=subschema' => FALSE,
				default => TRUE,
			};

		Log::debug(sprintf('%s:%s - %s',self::LOGKEY,$cache ? 'DN CACHEABLE' : 'DN NOT cacheable',$dn));
		return $cache;
	}

	/**
	 * Return our cache time as per the configuration
	 *
	 * @return Carbon
	 */
	private static function cachetime(): Carbon
	{
		return Carbon::now()
			->addSeconds(Config::get('ldap.cache.time') ?: 0);
	}

	/**
	 * Generic Builder method to setup our queries consistently - mainly to ensure we cache results
	 *
	 * @param string $dn
	 * @param array $attrs
	 * @return Builder
	 */
	private static function get(string $dn,array $attrs=['*','+']): Builder
	{
		return Entry::query()
			->setDN($dn)
			->cache(
				until: self::cachetime(),
				flush: self::cacheflush($dn)
			)
			->select($attrs);
	}

	/**
	 * Obtain the rootDSE for the server, that gives us server information
	 *
	 * @return Model
	 * @throws ObjectNotFoundException
	 * @testedin TranslateOidTest::testRootDSE();
	 * @note While we are using a static variable for in session performance, we'll also cache the result normally
	 */
	public static function rootDSE(): Model
	{
		static $rootdse = NULL;

		if (is_null($rootdse))
			$rootdse = self::get('',['+','*'])
				->read()
				->firstOrFail();

		return $rootdse;
	}

	/* METHODS */

	/**
	 * Query the server for a DN and return its children and if those children have children.
	 *
	 * @param string $dn
	 * @param array $attrs
	 * @return LDAPCollection|NULL
	 */
	public function children(string $dn,array $attrs=['dn']): ?LDAPCollection
	{
		return $this
			->get(
				dn: $dn,
				attrs: array_merge($attrs,[
					'hassubordinates',	// Needed for the tree to know if an entry has children
					'c'					// Needed for the tree to show icons for countries
				]))
			->list()
			->orderBy('dn')
			->get() ?: NULL;
	}

	/**
	 * Fetch a DN from the server
	 *
	 * @param string $dn
	 * @param array $attrs
	 * @return Model|null
	 */
	public function fetch(string $dn,array $attrs=['*','+']): ?Model
	{
		return $this->get($dn,$attrs)
			->read()
			->first() ?: NULL;
	}

	/**
	 * This function determines if the specified attribute is contained in the force_may list
	 * as configured in config.php.
	 *
	 * @return boolean True if the specified attribute is configured to be force as a may attribute
	 * @todo There are 3 isForceMay() functions - we only need one
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
				case 'ldapsyntaxes':
				case 'matchingrules':
				case 'objectclasses':
					if ($this->{$item}->count())
						return $this->{$item};

					break;

				// This error message is not localized as only developers should ever see it
				default:
					throw new InvalidUsage('Invalid request to fetch schema: '.$item);
			}

			// Try to get the schema DN from the specified entry.
			$schema_dn = $this->schemaDN();
			// @note: 389DS does not return subschemaSubentry unless it is requested
			$schema = $this->fetch($schema_dn,['*','+','subschemaSubentry']);

			// If our schema's null, we didnt find it.
			if (! $schema)
				throw new Exception('Couldnt find schema at:'.$schema_dn);

			switch ($item) {
				case 'attributetypes':
					Log::debug(sprintf('%s:Attribute Types',self::LOGKEY));
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
							Log::debug(sprintf('%s:\ Attribute [%s] has the following aliases [%s]',self::LOGKEY,$o->name,$o->aliases->join(',')));

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
					Log::debug(sprintf('%s:LDAP Syntaxes',self::LOGKEY));

					foreach ($schema->{$item} as $line) {
						if (is_null($line) || ! strlen($line))
							continue;

						$o = new LDAPSyntax($line);
						$this->ldapsyntaxes->put(strtolower($o->oid),$o);
					}

					return $this->ldapsyntaxes;

				case 'matchingrules':
					Log::debug(sprintf('%s:Matching Rules',self::LOGKEY));

					foreach ($schema->{$item} as $line) {
						if (is_null($line) || ! strlen($line))
							continue;

						$o = new MatchingRule($line);
						$this->matchingrules->put($o->name_lc,$o);
					}

					foreach ($this->schema('attributetypes') as $attr) {
						$rule_key = strtolower($attr->getEquality());

						if ($this->matchingrules->has($rule_key) !== FALSE)
							$this->matchingrules[$rule_key]->addUsedByAttr($attr->name);
					}

					return $this->matchingrules;

				case 'objectclasses':
					Log::debug(sprintf('%s:Object Classes',self::LOGKEY));

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
	 * Get the Schema DN
	 *
	 * @return string
	 * @throws ObjectNotFoundException
	 */
	public function schemaDN(): string
	{
		return Arr::get($this->rootDSE->subschemasubentry,0);
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