<?php

namespace App\Ldap;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LdapRecord\Support\Arr;
use LdapRecord\Models\Model;

use App\Classes\Template;
use App\Classes\LDAP\Attribute;
use App\Classes\LDAP\Attribute\Factory;
use App\Classes\LDAP\Export\LDIF;
use App\Exceptions\Import\AttributeException;
use App\Exceptions\InvalidUsage;

/**
 * An Entry in an LDAP server
 *
 * @notes https://ldap.com/ldap-dns-and-rdns
 */
class Entry extends Model
{
	private const LOGKEY = 'E--';

	/** @var string regex used to identify tags */
	private const TAG_CHARS = 'a-zA-Z0-9-';
	/** @var string prefix used for langtags */
	public const LANG_TAG_PREFIX = 'lang-';
	public const TAG_CHARS_LANG = self::LANG_TAG_PREFIX.'['.self::TAG_CHARS.']+';
	/** @var string For attributes that dont have any tags */
	public const TAG_NOTAG = '_null_';
	public const TAG_MD5 = '_md5_';
	/** @var string For attributes that are manipulated internally before sending to the LDAP server */
	public const TAG_INTERNAL = '_internal_';
	/** @var string For attributes that has additional processing by the value of the helper */
	public const TAG_HELPER = '_helper_';
	public const TAG_NOVALUES = [self::TAG_MD5,self::TAG_HELPER];

	// Our Attribute objects
	private Collection $objects;

	// Templates that apply to this entry
	private(set) Collection $templates;

	// For new entries, this is the container that this entry will be stored in
	private string $rdnbase = '';
	private(set) bool $is_base;

	/* OVERRIDES */

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$this->guidKey = config('pla.guidkey');
		$this->objects = collect();
		$this->templates = collect();
	}

	public function discardChanges(): static
	{
		parent::discardChanges();

		// If we are discharging changes, we need to reset our $objects;
		$this->objects = $this->getAttributesAsObjects();

		return $this;
	}

	/**
	 * This function overrides getAttributes to use our collection of Attribute objects instead of the models attributes.
	 *
	 * This returns an array that should be consistent with $this->attributes
	 *
	 * @return array
	 */
	public function getAttributes(): array
	{
		return $this->objects
			->filter(fn($item)=>(! $item->is_internal))
			->flatMap(fn($item)=>
				$item->values
					->flatMap(fn($v,$k)=>[strtolower($item->name.(($k !== self::TAG_NOTAG) ? ';'.$k : ''))=>$v]))
			->toArray();
	}

	/**
	 * This replaces the model's get dirty, given that we store LDAP attributes in $this->objects, replacing
	 * $this->original/$this->attributes
	 *
	 * @return array
	 */
	public function getDirty(): array
	{
		$result = collect();

		foreach ($this->objects as $o)
			if ($o->isDirty())
				$result = $result->merge($o->getDirty());

		$result = $result
			->flatMap(function($item,$attr) {
				return ($x=collect($item))->count()
					? $x->flatMap(fn($v,$k)=>[strtolower($attr.(($k !== self::TAG_NOTAG) ? ';'.$k : ''))=>$v])
					: [strtolower($attr)=>[]];
			});

		return $result->toArray();
	}

	/**
	 * Determine if the new and old values for a given key are equivalent.
	 */
	protected function originalIsEquivalent(string $key): bool
	{
		$key = $this->normalizeAttributeKey($key);

		list($attribute,$tag) = $this->keytag($key);

		return ((! array_key_exists($key,$this->original)) && (! $this->objects->has($attribute)))
			|| (! $this->getObject($attribute)->isDirty());
	}

	/**
	 * As attribute values are updated, or new ones created, we need to mirror that
	 * into our $objects. This is called when we $o->key = $value
	 *
	 * If $value is an array, it should be an array of [tag => values]
	 * If $value is a string, then the key may have the tag "attribute;tag = value", and the tag is taken from the key
	 *
	 * This function should update $this->attributes and correctly reflect changes in $this->objects
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return $this
	 * @throws \Exception
	 */
	public function setAttribute(string $key,mixed $value): static
	{
		if (! is_array($value))
			throw new \Exception('Value must be an array');

		foreach ($value as $k => $v)
			parent::setAttribute($key.($k !== self::TAG_NOTAG ? ';'.$k : ''),$v);

		$key = $this->normalizeAttributeKey($key);
		list($attribute,$tag) = $this->keytag($key);

		$o = $this->objects->get($attribute)
			?: Factory::create(
				dn: $this->dn ?: '',
				attribute: $attribute,
				values: [$tag=>Arr::get($this->original,$tag,[])],
				oc: Arr::get($this->attributes,'objectclass',[]));

		if (is_array($value))
			$o->setValues($value);
		else
			$o->addValue($tag,$value);

		$this->objects->put($key,$o);

		return $this;
	}

	/**
	 * We'll shadow $this->attributes to $this->objects - a collection of Attribute objects
	 *
	 * Using the objects, it'll make it easier to work with attribute values
	 *
	 * @param array $attributes
	 * @return $this
	 */
	public function setRawAttributes(array $attributes = []): static
	{
		parent::setRawAttributes($attributes);

		// We only set our objects on DN entries (otherwise we might get into a recursion loop if this is the schema DN)
		if ($this->dn && ($this->dn !== config('server')->schemaDN())) {
			$this->objects = $this->getAttributesAsObjects();

		} else {
			$this->objects = collect();
		}

		// Filter out our templates specific for this entry
		if ($this->dn && ($this->dn !== config('server')->schemaDN())) {
			$this->templates = $this->templates()
				->filter(fn($item)=>$item->enabled
					&& (! $item->objectclasses
						->map('strtolower')
						->diff(array_map('strtolower',Arr::get($this->attributes,'objectclass')))
						->count()))
				->sortBy(fn($item)=>$item->title);
		}

		return $this;
	}

	/* ATTRIBUTES */

	/**
	 * Return an RDN object
	 *
	 * @return Attribute\RDN
	 * @throws InvalidUsage
	 */
	public function getRDNAttribute(): Attribute\RDN
	{
		$rdn = explode('=',$this->getRdn());
		$o = new Attribute\RDN('','dn',[self::TAG_NOTAG=>(array_filter($rdn) && (count($rdn) === 2)) ? [$rdn[0]=>$rdn[1]] : [NULL]]);
		$o->setBase($this->getContainer());
		$o->setAttributes($this->objects->pluck('schema'));

		return $o;
	}

	/**
	 * Return a key to use for sorting
	 *
	 * @return string
	 */
	public function getSortKeyAttribute(): string
	{
		return collect(explode(',',$this->getDn()))->reverse()->join(',');
	}

	public function getHasChildrenAttribute(): bool
	{
		return (strcasecmp(Arr::get($this->getAttribute('hassubordinates',[]),0),'TRUE') === 0)
			|| Arr::get($this->getAttribute('numsubordinates',[]),0) > 0;
	}

	/* METHODS */

	/**
	 * Add an attribute to this entry, if the attribute already exists, then we'll add the value to the existing item.
	 *
	 * This is primarily used by LDIF imports, where attributes have multiple entries over multiple lines
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 * @throws AttributeException
	 * @note Attributes added this way dont have objectclass information, and the Model::attributes are not populated
	 */
	public function addAttributeItem(string $key,string $value): void
	{
		$key = $this->normalizeAttributeKey(strtolower($key));

		// If the attribute name has tags
		list($attribute,$tag) = $this->keytag($key);

		if (config('server')->get_attr_id($attribute) === FALSE)
			throw new AttributeException(sprintf('Schema doesnt have attribute [%s]',$attribute));

		$o = $this->objects->get($attribute)
			?: Factory::create(
				dn: $this->dn ?: '',
				attribute: $attribute,
				values: [$tag=>Arr::get($this->original,$tag,[])],
			);

		$o->addValue($tag,[$value]);

		$this->objects->put($attribute,$o);
	}

	/**
	 * Export this record
	 *
	 * @param string $method
	 * @param string $scope
	 * @return string
	 * @throws \Exception
	 */
	public function export(string $method,string $scope): string
	{
		// @todo To implement
		switch ($scope) {
			case 'base':
			case 'one':
			case 'sub':
				break;

			default:
				throw new \Exception('Export scope unknown:'.$scope);
		}

		switch ($method) {
			case 'ldif':
				return new LDIF(collect($this));

			default:
				throw new \Exception('Export method not implemented:'.$method);
		}
	}

	/**
	 * Convert all our attribute values into an array of Objects
	 *
	 * @return Collection
	 */
	private function getAttributesAsObjects(): Collection
	{
		$result = collect();
		$entry_oc = Arr::get($this->attributes,'objectclass',[]);

		// Set the initial attributes
		foreach ($this->original as $attrtag => $values) {
			list($attribute,$tag) = $this->keytag($attrtag);

			// If the attribute doesnt exist we'll create it
			$o = Arr::get(
				$result,
				$attribute,
				Factory::create(
					dn: $this->dn,
					attribute: $attribute,
					values: [$tag=>$values],
					oc: $entry_oc,
				));

			$o->addValueOld($tag,$values);
			$o->addValue($tag,$values);

			$result->put($attribute,$o);
		}

		// Get any changes
		foreach ($this->attributes as $attrtag => $values) {
			list($attribute,$tag) = $this->keytag($attrtag);

			// If the attribute doesnt exist we'll create it
			$o = Arr::get(
				$result,
				$attribute,
				Factory::create(
					dn: $this->dn,
					attribute: $attribute,
					values: [$tag=>Arr::get($this->original,$attrtag,[])],
					oc: $entry_oc,
				));

			$o->addValue($tag,$values);

			$result->put($attribute,$o);
		}

		$sort = collect(config('pla.attr_display_order',[]))->map(fn($item)=>strtolower($item));

		// Order the attributes
		return $result->sortBy([function(Attribute $a,Attribute $b) use ($sort): int {
			if ($a === $b)
				return 0;

			// Check if $a/$b are in the configuration to be sorted first, if so get it's key
			$a_key = $sort->search($a->name_lc);
			$b_key = $sort->search($b->name_lc);

			// If the keys were not in the sort list, set the key to be the count of elements (ie: so it is last to be sorted)
			if ($a_key === FALSE)
				$a_key = $sort->count()+1;

			if ($b_key === FALSE)
				$b_key = $sort->count()+1;

			// Case where neither $a, nor $b are in pla.attr_display_order, $a_key = $b_key = one greater than num elements.
			// So we sort them alphabetically
			if ($a_key === $b_key)
				return strcasecmp($a->name,$b->name);

			// Case where at least one attribute or its friendly name is in $attrs_display_order
			// return -1 if $a before $b in $attrs_display_order
			return ($a_key < $b_key) ? -1 : 1;
		}]);
	}

	/**
	 * Return a list of available attributes - as per the objectClass entry of the record
	 *
	 * @return Collection
	 */
	public function getAvailableAttributes(): Collection
	{
		$result = collect();

		foreach (($this->getObject('objectclass')?->values->dot() ?: []) as $oc)
			$result = $result->merge(config('server')->schema('objectclasses',$oc)?->all_attributes);

		return $result;
	}

	public function getContainer(): string
	{
		return $this->rdnbase ?: dn_container($this->getDn());
	}

	/**
	 * Return a secure version of the DN
	 * @param string $cmd
	 * @return string
	 */
	public function getDNSecure(string $cmd=''): string
	{
		return Crypt::encryptString(($cmd ? sprintf('*%s|',$cmd) : '').$this->getDn());
	}

	public function getDNContainerSecure(string $cmd=''): string
	{
		return Crypt::encryptString(($cmd ? sprintf('*%s|',$cmd) : '').$this->getContainer());
	}

	/**
	 * Return a list of LDAP internal attributes
	 *
	 * @return Collection
	 */
	public function getInternalAttributes(): Collection
	{
		return $this->objects
			->filter(fn($item)=>$item->is_internal);
	}

	/**
	 * Identify the language tags (RFC 3866) used by this entry
	 *
	 * @return Collection
	 */
	public function getLangTags(): Collection
	{
		return $this->getObjects()
			->map(fn($item)=>$item->langtags);
	}

	/**
	 * Of all the items with lang tags, which ones have more than 1 lang tag
	 *
	 * @return Collection
	 */
	public function getLangMultiTags(): Collection
	{
		return $this->getLangTags()
			->map(fn($item)=>$item->values()
				->map(fn($item)=>explode(';',$item))
				->filter(fn($item)=>count($item) > 1))
			->filter(fn($item)=>$item->count());
	}

	/**
	 * Get an attribute as an object
	 *
	 * @param string $key
	 * @return Attribute|NULL
	 */
	public function getObject(string $key): Attribute|NULL
	{
		return $this->objects
			->get($this->normalizeAttributeKey($key));
	}

	public function getObjects(): Collection
	{
		// In case we havent built our objects yet (because they werent available while determining the schema DN)
		if ((! $this->objects->count()) && $this->attributes)
			$this->objects = $this->getAttributesAsObjects();

		return $this->objects;
	}

	/**
	 * Find other attribute tags used by this entry
	 *
	 * @return Collection
	 */
	public function getOtherTags(): Collection
	{
		return $this->getObjects()
			->filter(fn($item)=>! $item->no_attr_tags)
			->map(fn($item)=>$item
				->values
				->keys()
				->filter(fn($item)=>
					$item && collect(explode(';',$item))->filter(
						fn($item)=>
							(! preg_match(sprintf('/^%s$/',self::TAG_NOTAG),$item))
							&& (! preg_match(sprintf('/^%s$/',self::TAG_CHARS_LANG),$item))
						)
						->count())
			)
			->filter(fn($item)=>$item->count());
	}

	/**
	 * Return a list of attributes without any values
	 *
	 * @return Collection
	 */
	public function getMissingAttributes(): Collection
	{
		$missing = $this->getAvailableAttributes()
			->filter(fn($a)=>(! $this->getVisibleAttributes()->contains(fn($b)=>($a->name === $b->name))));

		// Add ppolicy operational attributes for user entries
		if ($this->isUserEntry()) {
			$ppolicyAttrs = ['pwdReset'];

			foreach ($ppolicyAttrs as $attrName) {
				$attrLower = strtolower($attrName);

				// Only add if not already present in entry or missing list
				if (! $this->hasAttribute($attrLower) && ! $missing->contains(fn($item)=>strtolower($item->name) === $attrLower)) {
					$schema = config('server')->schema('attributetypes',$attrName);
					if ($schema)
						$missing->push($schema);
				}
			}
		}

		return $missing;
	}

	/**
	 * Return this list of user attributes
	 *
	 * @return Collection
	 */
	public function getVisibleAttributes(): Collection
	{
		static $cache = collect();

		if (! $cache->count())
			$cache = $this->objects
				->filter(fn($item)=>(! $item->is_internal));

		return $cache;
	}

	public function hasAttribute(int|string $key): bool
	{
		return $this->objects
			->has($key);
	}

	/**
	 * Check if this entry is a user-like entry based on objectclasses
	 *
	 * @return bool
	 */
	public function isUserEntry(): bool
	{
		static $userObjectClasses = ['posixaccount','inetorgperson','person','account','organizationalperson'];

		$entryOCs = $this->getObject('objectclass')?->tagValues()->map(fn($item)=>strtolower(trim($item))) ?? collect();

		return $entryOCs->intersect($userObjectClasses)->isNotEmpty();
	}

	/**
	 * Did this query generate a size limit exception
	 *
	 * @return bool
	 * @throws \LdapRecord\ContainerException
	 */
	public function hasMore(): bool
	{
		return $this->getConnectionContainer()
			->getConnection()
			->getLdapConnection()
			->getDetailedError()
			?->getErrorCode() === 4;
	}

	/**
	 * Return an icon for a DN based on objectClass
	 *
	 * @return string
	 */
	public function icon(): string
	{
		$objectclasses = ($x=$this->getObject('objectclass'))
			? $x->tagValues()
				->map(fn($item)=>strtolower($item))
			: collect();

		// Return icon based upon objectClass value
		if ($objectclasses->intersect([
			'account',
			'inetorgperson',
			'organizationalperson',
			'person',
			'posixaccount',
		])->count())
			return 'fas fa-user';

		elseif ($objectclasses->contains('organization'))
			return 'fas fa-university';

		elseif ($objectclasses->contains('organizationalunit'))
			return 'fas fa-object-group';

		elseif ($objectclasses->intersect([
			'posixgroup',
			'groupofnames',
			'groupofuniquenames',
			'group',
		])->count())
			return 'fas fa-users';

		elseif ($objectclasses->intersect([
			'dcobject',
			'domainrelatedobject',
			'domain',
			'builtindomain',
		])->count())
			return 'fas fa-network-wired';

		elseif ($objectclasses->contains('alias'))
			return 'fas fa-theater-masks';

		elseif ($objectclasses->contains('country'))
			return sprintf('flag %s',strtolower(Arr::get($this->c ?: [],0)));

		elseif ($objectclasses->contains('device'))
			return 'fas fa-mobile-alt';

		elseif ($objectclasses->contains('document'))
			return 'fas fa-file-alt';

		elseif ($objectclasses->contains('iphost'))
			return 'fas fa-wifi';

		elseif ($objectclasses->contains('room'))
			return 'fas fa-door-open';

		elseif ($objectclasses->contains('server'))
			return 'fas fa-server';

		elseif ($objectclasses->contains('openldaprootdse'))
			return 'fas fa-info';

		// Default
		return 'fa-fw fas fa-cog';
	}

	/**
	 * Given an LDAP attribute, this will return the attribute name and the tag
	 * eg: description;lang-cn will return [description,lang-cn]
	 *
	 * @param string $key
	 * @return array
	 */
	private function keytag(string $key): array
	{
		$matches = [];
		if (preg_match(sprintf('/^([%s]+);+([%s;]+)/',self::TAG_CHARS,self::TAG_CHARS),$key,$matches)) {
			$attribute = $matches[1];
			$tags = $matches[2];

		} else {
			$attribute = $key;
			$tags = self::TAG_NOTAG;
		}

		return [$attribute,$tags];
	}

	/**
	 * Is this entry a baseDN
	 *
	 * @return void
	 */
	public function setBase(): void
	{
		$this->is_base = TRUE;
	}

	public function setRDNBase(string $bdn): void
	{
		if ($this->exists)
			throw new InvalidUsage('Cannot set RDN base on existing entries');

		$this->rdnbase = $bdn;

		// Load any templates
		$this->templates = $this->templates()
			->filter(fn($item)=>(! $item->regexp) || preg_match($item->regexp,$bdn));
	}

	private function templates(): Collection
	{
		return Cache::remember('templates'.Session::id(),config('ldap.cache.time'),function() {
			$template_dir = Storage::disk(config('pla.template.dir'));
			$templates = collect();

			foreach (array_filter($template_dir->files('.',TRUE),fn($item)=>Str::endsWith($item,'.json')) as $file) {
				if (config('pla.template.exclude_system',FALSE) && Str::doesntContain($file,'/'))
					continue;

				$to = new Template($file);

				if (! $to->invalid)
					$templates->put($file,$to);
			}

			return $templates;
		});
	}
}