<?php

namespace App\Classes\LDAP;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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

	private Model $rootDSE;

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
		Log::debug(sprintf('%s:Fetching baseDNs [%s] objects',self::LOGKEY,$objects ? 'WITH' : 'withOUT'));

		try {
			$namingcontexts = collect(config('pla.base_dns') ?: self::rootDSE()?->namingcontexts);

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

		Log::debug(sprintf('%s:- Got namingcontexts',self::LOGKEY),['namingcontexts'=>$namingcontexts->join(':')]);

		if (! $objects)
			return $namingcontexts;

		return Cache::remember('basedns'.Session::id(),config('ldap.cache.time'),function() use ($namingcontexts) {
			$result = collect();

			// @note: Incase our rootDSE didnt return a namingcontext, we'll have no base DNs
			foreach ($namingcontexts as $dn) {
				$o = self::get($dn)->read()->find($dn);

				if ($o) {
					$o->setBase();
					$result->push($o);

				} else {
					Log::alert(sprintf('%s:! DN [%s] was not found for [%s]',self::LOGKEY,$dn,Auth::user()?->getDn ?: __('Anonymous')));
				}
			}

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
	 * @param array $attrs Includes pwdReset explicitly as ppolicy operational attributes aren't returned by '+'
	 * @return Builder
	 */
	private static function get(string $dn,array $attrs=['*','+','pwdReset']): Builder
	{
		Log::debug(sprintf('%s:Getting [%s]',self::LOGKEY,$dn));

		$result = Entry::query()
			->setDN($dn)
			->cache(
				until: self::cachetime(),
				flush: self::cacheflush($dn)
			)
			->select($attrs);

		Log::debug(sprintf('%s:= Got [%s]',self::LOGKEY,$dn));

		return $result;
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

		if (is_null($rootdse)) {
			$rootdse = self::get('',['+','*'])
				->read()
				->firstOrFail();

			Log::debug(sprintf('%s:Fetched rootDSE',self::LOGKEY),['rootDSE'=>$rootdse]);
		}

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
					'numsubordinates',	// Needed for the tree to know if an entry has children
					'hassubordinates',	// Needed for the tree to know if an entry has children
					'c'					// Needed for the tree to show icons for countries
				]))
			->list()
			->get() ?: NULL;
	}

	/**
	 * Fetch a DN from the server
	 *
	 * @param string $dn
	 * @param array $attrs
	 * @return Model|null
	 */
	public function fetch(string $dn,array $attrs=['*','+','pwdReset']): ?Model
	{
		static $depth = [];
		$cd = Arr::get($depth,$dn,0);

		Log::debug(sprintf('%s:Fetching [%s] depth [%d]',self::LOGKEY,$dn,$cd));

		if ($cd > 2) {
			Log::error(sprintf('%s:! Something is wrong, loop detecting triggered for [%s] (%d)',self::LOGKEY,$dn,$cd));

			throw new InvalidUsage(sprintf('Something is wrong, loop detecting triggered for [%s] (%d)',$dn,$cd));
		}

		$depth[$dn] = $cd+1;

		$result = $this->get($dn,$attrs)
			->read()
			->first() ?: NULL;

		$depth[$dn] = $cd;

		Log::debug(sprintf('%s:= Fetched [%s]',self::LOGKEY,$dn),['dn'=>$dn]);

		return $result;
	}

	/**
	 * Get the baseDN for a given DN
	 *
	 * @param string $dn
	 * @return Entry
	 */
	public function get_base(string $dn): Entry
	{
		foreach (self::baseDNs() as $base) {
			if (\Str::endsWith($dn,$base->getDn()))
				break;
		}

		return $base;
	}

	/**
	 * Get an attribute key for an attributetype name
	 *
	 * @param string $key
	 * @return int|bool
	 * @throws InvalidUsage
	 */
	public function get_attr_id(string $key): int|bool
	{
		static $attributes = $this->schema('attributetypes');

		$attrid = $attributes->search(fn($item)=>$item->names->contains($key));

		// Second chance search using lowercase items (our Entry attribute keys are lowercase)
		if ($attrid === FALSE)
			$attrid = $attributes->search(fn($item)=>$item->names_lc->contains(strtolower($key)));

		return $attrid;
	}

	/**
	 * Given an OID, return the ldapsyntax for the OID
	 *
	 * @param string $oid
	 * @return LDAPSyntax|null
	 * @throws InvalidUsage
	 */
	public function get_syntax(string $oid): ?LDAPSyntax
	{
		return (($id=$this->schema('ldapsyntaxes')->search(fn($item)=>$item->oid === $oid)) !== FALSE)
			? $this->ldapsyntaxes[$id]
			: NULL;
	}

	public function hasMore(): bool
	{
		return (new Entry)->hasMore();
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
	 * @throws InvalidUsage
	 */
	public function schema(string $item,?string $key=NULL): Collection|LDAPSyntax|Base|NULL
	{
		Log::debug(sprintf('%s:Fetching SchemaItem [%s]',self::LOGKEY,$item),['key'=>$key,'already'=>$this->{$item}->count()]);

		// Ensure our item to fetch is lower case
		$item = strtolower($item);

		if (! $this->{$item}->count()) {
			Log::debug(sprintf('%s:/ SchemaItem NOT loaded [%s]',self::LOGKEY,$item),['count'=>$this->{$item}->count()]);

			$this->{$item} = Cache::remember('schema.'.$item,config('ldap.cache.time'),function() use ($item) {
				Log::debug(sprintf('%s:? Finding out SchemaDN',self::LOGKEY));

				// Try to get the schema DN from the specified entry.
				$schema_dn = $this->schemaDN();
				Log::debug(sprintf('%s:/ Found out SchemaDN is [%s]',self::LOGKEY,$schema_dn));

				// @note: If the LDAP server doesnt return a subschemasubentry, then we end up looping
				try {
					Log::debug(sprintf('%s:/ Fetching schema at [%s]',self::LOGKEY,$schema_dn));

					// @note OpenBSD/ldapd doesnt return the schema with +, it needs to call schema attributes explicitly
					$schema = $this->fetch($schema_dn,[$item]);

				} catch (InvalidUsage $e) {
					abort(599,$e->getMessage());
				}

				// If our schema's null, we didnt find it.
				if (! $schema) {
					Log::error(sprintf('%s:! Couldnt find schema at [%s]',self::LOGKEY,$schema_dn));

					throw new Exception('Couldnt find schema at:'.$schema_dn);
				}

				switch ($item) {
					case 'attributetypes':
						Log::debug(sprintf('%s:Attribute Types',self::LOGKEY));

						foreach ($schema->{$item} ?: [] as $line) {
							if (is_null($line) || ! strlen($line))
								continue;

							$o = new AttributeType($line);
							$this->attributetypes->push($o);
						}

						foreach ($this->attributetypes as $o) {
							// Now go through and reference the parent/child relationships
							if ($o->sup_attribute) {
								$attrid = $this->get_attr_id($o->sup_attribute);

								if (! $this->attributetypes[$attrid]->children->contains($o->oid))
									$this->attributetypes[$attrid]->addChild($o->oid);
							}

							// go through any children and add details if the child doesnt have them (ie, cn inherits name)
							foreach ($o->children as $child) {
								$attrid = $this->attributetypes->search(fn($o)=>$o->oid === $child);

								/* only overwrite the child's SINGLE-VALUE property if the parent has it set, and the child doesnt
								 * (note: All LDAP attributes default to multi-value if not explicitly set SINGLE-VALUE) */
								if (! is_null($o->is_single_value) && is_null($this->attributetypes[$attrid]->is_single_value))
									$this->attributetypes[$attrid]->setIsSingleValue($o->is_single_value);
							}
						}

						return $this->attributetypes;

					case 'ldapsyntaxes':
						Log::debug(sprintf('%s:LDAP Syntaxes',self::LOGKEY));

						foreach ($schema->{$item} ?: [] as $line) {
							if (is_null($line) || ! strlen($line))
								continue;

							$o = new LDAPSyntax($line);
							$this->ldapsyntaxes->push($o);
						}

						return $this->ldapsyntaxes;

					case 'matchingrules':
						Log::debug(sprintf('%s:Matching Rules',self::LOGKEY));

						foreach ($schema->{$item} ?: [] as $line) {
							if (is_null($line) || ! strlen($line))
								continue;

							$o = new MatchingRule($line);
							$this->matchingrules->push($o);
						}

						foreach ($this->schema('attributetypes') as $attr) {
							$rule_id = $this->matchingrules->search(fn($item)=>$item->oid === $attr->equality);

							if ($rule_id !== FALSE)
								$this->matchingrules[$rule_id]->addUsedByAttr($attr->name);
						}

						return $this->matchingrules;

					case 'objectclasses':
						Log::debug(sprintf('%s:Object Classes',self::LOGKEY));

						foreach ($schema->{$item} ?: [] as $line) {
							if (is_null($line) || ! strlen($line))
								continue;

							$o = new ObjectClass($line);
							$this->objectclasses->push($o);
						}

						foreach ($this->objectclasses as $o) {
							// Now go through and reference the parent/child relationships
							foreach ($o->sup_classes as $sup) {
								$oc_id = $this->objectclasses->search(fn($item)=>$item->name === $sup);

								if (($oc_id !== FALSE) &&  (! $this->objectclasses[$oc_id]->child_classes->contains($o->name)))
									$this->objectclasses[$oc_id]->addChildObjectClass($o->name);
							}

							// Add the used in and required_by values for attributes.
							foreach ($o->attributes as $attribute) {
								if (($attrid = $this->schema('attributetypes')->search(fn($item)=>$item->oid === $attribute->oid)) !== FALSE) {
									// Add Used In.
									$this->attributetypes[$attrid]->addUsedInObjectClass($o->name,$o->isStructural());

									// Add Required By.
									if ($attribute->is_must)
										$this->attributetypes[$attrid]->addRequiredByObjectClass($o->name,$o->isStructural());
								}
							}
						}

						// Put the updated attributetypes back in the cache
						Cache::put('schema.attributetypes',$this->attributetypes,config('ldap.cache.time'));

						return $this->objectclasses;

					// Shouldnt get here
					default:
						Log::alert(sprintf('%s:? Unknown item to fetch [%s] ',self::LOGKEY,$item));
						throw new InvalidUsage('Invalid request to fetch schema: '.$item);
				}
			});

			Log::debug(sprintf('%s:/ SchemaItem LOADED [%s] ',self::LOGKEY,$item),['count'=>$this->{$item}->count()]);
		}

		if (is_null($key))
			return $this->{$item};

		Log::debug(sprintf('%s:/ SchemaItem [%s] Looking for key [%s] ',self::LOGKEY,$item,$key));

		switch ($item) {
			case 'attributetypes':
				$attrid = $this->get_attr_id($key);

				$attr = ($attrid === FALSE)
					? new AttributeType($key)
					: clone $this->{$item}->get($attrid);

				$attr->setName($attr->names->get($attr->names_lc->search(strtolower($key))) ?: $key);

				return $attr;

			default:
				return $this->{$item}->get($key)
					?: $this->{$item}->first(fn($item)=>$item->name_lc === strtolower($key));
		}
	}

	/**
	 * Get the Schema DN
	 *
	 * @return string
	 */
	public function schemaDN(): string
	{
		return $this->rootDSE
			->getFirstAttribute('subschemaSubentry','');
	}

	public function subordinates(string $dn,array $attrs=['dn'],bool $containers=TRUE): ?LDAPCollection
	{
		return $this
			->get(
				dn: $dn,
				attrs: array_merge($attrs,[]))
			->rawFilter(sprintf('(hassubordinates=%s)',$containers ? 'TRUE' : 'FALSE'))
			->search()
			->get() ?: NULL;
	}
}