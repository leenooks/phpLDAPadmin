<?php

namespace App\Classes\LDAP;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as ArrayCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use LdapRecord\Query\Collection;

use App\Classes\LDAP\Schema\{AttributeType,Base,LDAPSyntax,MatchingRule,MatchingRuleUse,ObjectClass};
use App\Exceptions\InvalidUsage;
use App\Ldap\Entry;

class Server
{
	// This servers schema objectclasses
	private ArrayCollection $attributetypes;
	private ArrayCollection $ldapsyntaxes;
	private ArrayCollection $matchingrules;
	private ArrayCollection $matchingruleuse;
	private ArrayCollection $objectclasses;

	// Valid items that can be fetched
	public const schema_types = [
		'objectclasses',
		'attributetypes',
		'ldapsyntaxes',
		'matchingrules',
	];

	/**
	 * Query the server for a DN and return its children and if those children have children.
	 *
	 * @param string $dn
	 * @return Collection|null
	 */
	public function children(string $dn): ?Collection
	{
		return ($x=(new Entry)
			->query()
			->cache(Carbon::now()->addSeconds(Config::get('ldap.cache.time')))
			->select(['*','hassubordinates'])
			->setDn($dn)
			->listing()
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
			->query()
			->cache(Carbon::now()->addSeconds(Config::get('ldap.cache.time')))
			->select($attrs)
			->find($dn)) ? $x : NULL;
	}

	/**
	 * Given an LDAP OID number, returns a verbose description of the OID.
	 * This function parses ldap_supported_oids.txt and looks up the specified
	 * OID, and returns the verbose message defined in that file.
	 *
	 * <code>
	 *  Array (
	 *    [title] => All Operational Attribute
	 *    [ref] => RFC 3673
	 *    [desc] => An LDAP extension which clients may use to request the return of all operational attributes.
	 *  )
	 * </code>
	 *
	 * @param string $oid The OID number (ie, "1.3.6.1.4.1.4203.1.5.1") of the OID of interest.
	 * @param string $key The title|ref|desc to return
	 * @return string|null
	 * @testedby TranslateOidTest::testRootDSE()
	 */
	public static function getOID(string $oid,string $key): ?string
	{
		$oids = Cache::remember('oids',86400,function() {
			try {
				$f = fopen(config_path('ldap_supported_oids.txt'),'r');

			} catch (Exception $e) {
				return NULL;
			}

			$result = collect();

			while (! feof($f)) {
				$line = trim(fgets($f));

				if (! $line OR preg_match('/^#/',$line))
					continue;

				$fields = explode(':',$line);

				$result->put(Arr::get($fields,0),[
					'title'=>Arr::get($fields,1),
					'ref'=>Arr::get($fields,2),
					'desc'=>Arr::get($fields,3),
				]);
			}
			fclose($f);

			return $result;
		});

		return Arr::get(
			($oids ? $oids->get($oid) : []),
			$key,
			($key == 'desc' ? 'No description available, can you help with one?' : ($key == 'title' ? $oid : NULL))
		);
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
	 * Return the server's schema
	 *
	 * @param string $item Schema Item to Fetch
	 * @param string|null $key
	 * @return ArrayCollection|Base
	 * @throws InvalidUsage
	 */
	public function schema(string $item,string $key=NULL): ArrayCollection|Base|NULL
	{
		// Ensure our item to fetch is lower case
		$item = strtolower($item);
		if ($key)
			$key = strtolower($key);

		// This error message is not localized as only developers should ever see it
		if (! in_array($item,self::schema_types))
			throw new InvalidUsage('Invalid request to fetch schema: '.$item);

		// First pass if we have already retrieved the schema item
		switch ($item) {
			case 'attributetypes':
				if (isset($this->attributetypes))
					return is_null($key) ? $this->attributetypes : $this->attributetypes->get($key);
				else
					$this->attributetypes = collect();

				break;

			case 'ldapsyntaxes':
				if (isset($this->ldapsyntaxes))
					return is_null($key) ? $this->ldapsyntaxes : $this->ldapsyntaxes->get($key);
				else
					$this->ldapsyntaxes = collect();

				break;

			case 'matchingrules':
				if (isset($this->matchingrules))
					return is_null($key) ? $this->matchingrules : $this->matchingrules->get($key);
				else
					$this->matchingrules = collect();

				break;

			/*
			case 'matchingruleuse':
				if (isset($this->matchingruleuse))
					return is_null($key) ? $this->matchingruleuse : $this->matchingruleuse->get($key);
				else
					$this->matchingruleuse = collect();

			break;
			*/

			case 'objectclasses':
				if (isset($this->objectclasses))
					return is_null($key) ? $this->objectclasses : $this->objectclasses->get($key);
				else
					$this->objectclasses = collect();

				break;

			// Shouldnt get here
			default:
				throw new InvalidUsage('Invalid request to fetch schema: '.$item);
		}

		// Try to get the schema DN from the specified entry.
		$schema_dn = Entry::schemaDN();
		$schema = (new Server)->fetch($schema_dn);

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

					/*
					if (isset($syntaxes[$attr->getSyntaxOID()])) {
						$syntax = $syntaxes[$attr->getSyntaxOID()];
						$attr->setType($syntax->getDescription());
					}
					$this->attributetypes[$attr->getName()] = $attr;
					*/

					/**
					 * bug 856832: create an entry in the $attrs_oid array too. This
					 * will be a ref to the $attrs entry for maintenance and performance
					 * reasons
					 */
					//$attrs_oid[$attr->getOID()] = &$attrs[$attr->getName()];
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
							$this->attributetypes[strtolower($attr_name)]->addUsedInObjectClass($object_class->name);

					// Add Required By.
					foreach ($must_attrs as $attr_name)
						if ($this->attributetypes->has(strtolower($attr_name)))
							$this->attributetypes[strtolower($attr_name)]->addRequiredByObjectClass($object_class->name);

					// Force May
					foreach ($object_class->getForceMayAttrs() as $attr_name)
						if ($this->attributetypes->has(strtolower($attr_name->name)))
							$this->attributetypes[strtolower($attr_name->name)]->setForceMay();
				}

				return is_null($key) ? $this->attributetypes : $this->attributetypes->get($key);

			case 'objectclasses':
				Log::debug('Object Classes');

				foreach ($schema->{$item} as $line) {
					if (is_null($line) || ! strlen($line))
						continue;

					$o = new ObjectClass($line,$schema,$this);
					$this->objectclasses->put($o->name_lc,$o);
				}

				// Now go through and reference the parent/child relationships
				foreach ($this->objectclasses as $o)
					foreach ($o->getSupClasses() as $parent) {
						$parent = strtolower($parent);
						if ($this->objectclasses->has($parent) !== FALSE)
							$this->objectclasses[$parent]->addChildObjectClass($o->name);
					}

				return is_null($key) ? $this->objectclasses : $this->objectclasses->get($key);

			case 'ldapsyntaxes':
				Log::debug('LDAP Syntaxes');

				foreach ($schema->{$item} as $line) {
					if (is_null($line) || ! strlen($line))
						continue;

					$o = new LDAPSyntax($line);
					$this->ldapsyntaxes->put(strtolower($o->oid),$o);
				}

				return is_null($key) ? $this->ldapsyntaxes : $this->ldapsyntaxes->get($key);

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

				return is_null($key) ? $this->matchingrules : $this->matchingrules->get($key);
		}

		return NULL;
	}

	public function schemaSyntaxName(string $oid): ?LDAPSyntax
	{
		return $this->schema('ldapsyntaxes',$oid);
	}
}