<?php

namespace App\Ldap;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
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
	private const TAG_CHARS = 'a-zA-Z0-9-';
	public const LANG_TAG_PREFIX = 'lang-';
	public const TAG_CHARS_LANG = self::LANG_TAG_PREFIX.'['.self::TAG_CHARS.']+';
	public const TAG_NOTAG = '_null_';

	// Our Attribute objects
	private Collection $objects;

	// Templates that apply to this entry
	private(set) Collection $templates;

	// For new entries, this is the container that this entry will be stored in
	private string $rdnbase;

	/* OVERRIDES */

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$this->objects = collect();

		// Load any templates
		$this->templates =  Cache::remember('templates'.Session::id(),config('ldap.cache.time'),function() {
			$template_dir = Storage::disk(config('pla.template.dir'));
			$templates = collect();

			foreach (array_filter($template_dir->files(),fn($item)=>Str::endsWith($item,'.json')) as $file) {
				$to = new Template($file);

				if ($to->invalid) {
					Log::debug(sprintf('Template [%s] is not valid (%s) - ignoring',$file,$to->reason));

				} else {
					$templates->put($file,new Template($file));
				}
			}

			return $templates;
		});
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
			->flatMap(fn($item)=>
				($item->no_attr_tags)
					? [strtolower($item->name)=>$item->values]
					: $item->values
						->flatMap(fn($v,$k)=>[strtolower($item->name.($k !== self::TAG_NOTAG ? ';'.$k : ''))=>$v]))
			->toArray();
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
	 * This function should update $this->attributes and correctly reflect changes in $this->objects
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return $this
	 */
	public function setAttribute(string $key,mixed $value): static
	{
		foreach ($value as $k => $v)
			parent::setAttribute($key.($k !== self::TAG_NOTAG ? ';'.$k : ''),$v);

		$key = $this->normalizeAttributeKey($key);
		list($attribute,$tags) = $this->keytag($key);

		$o = $this->objects->get($attribute) ?: Factory::create($this->dn ?: '',$attribute,[],Arr::get($this->attributes,'objectclass',[]));
		$o->values = collect($value);

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
		if ($this->dn && (! in_array($this->dn,Arr::get($this->attributes,'subschemasubentry',[])))) {
			$this->objects = $this->getAttributesAsObjects();

		} else {
			$this->objects = collect();
		}

		// Filter out our templates specific for this entry
		if ($this->dn && (! in_array(strtolower($this->dn),['cn=subschema']))) {
			$this->templates = $this->templates
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
	 * Return a key to use for sorting
	 *
	 * @return string
	 */
	public function getSortKeyAttribute(): string
	{
		return collect(explode(',',$this->getDn()))->reverse()->join(',');
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
	public function addAttributeItem(string $key,mixed $value): void
	{
		// While $value is mixed, it can only be a string
		if (! is_string($value))
			throw new \Exception('value should be a string');

		$key = $this->normalizeAttributeKey(strtolower($key));

		// If the attribute name has tags
		list($attribute,$tag) = $this->keytag($key);

		if (config('server')->get_attr_id($attribute) === FALSE)
			throw new AttributeException(sprintf('Schema doesnt have attribute [%s]',$attribute));

		$o = $this->objects->get($attribute) ?: Attribute\Factory::create($this->dn ?: '',$attribute,[]);
		$o->addValue($tag,[$value]);

		$this->objects->put($key,$o);
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

		foreach ($this->attributes as $attrtag => $values) {
			list($attribute,$tags) = $this->keytag($attrtag);

			$orig = Arr::get($this->original,$attrtag,[]);

			// If the attribute doesnt exist we'll create it
			$o = Arr::get(
				$result,
				$attribute,
				Factory::create(
					$this->dn,
					$attribute,
					[$tags=>$orig],
					$entry_oc,
				));

			$o->addValue($tags,$values);
			$o->addValueOld($tags,Arr::get($this->original,$attrtag));

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

		foreach (($this->getObject('objectclass')?->values ?: []) as $oc)
			$result = $result->merge(config('server')->schema('objectclasses',$oc)->all_attributes);

		return $result;
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
	 * @return Attribute|null
	 */
	public function getObject(string $key): Attribute|null
	{
		return match ($key) {
			'rdn' => $this->getRDNObject(),

			default => $this->objects
				->get($this->normalizeAttributeKey($key))
		};
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
		return $this->getAvailableAttributes()
			->filter(fn($a)=>(! $this->getVisibleAttributes()->contains(fn($b)=>($a->name === $b->name))));
	}

	private function getRDNObject(): Attribute\RDN
	{
		$o = new Attribute\RDN('','dn',['']);
		// @todo for an existing object, rdnbase would be null, so dynamically get it from the DN.
		$o->setBase($this->rdnbase);
		$o->setAttributes($this->getAvailableAttributes()->filter(fn($item)=>$item->is_must));

		return $o;
	}

	/**
	 * Return this list of user attributes
	 *
	 * @param string $tag If null return all tags
	 * @return Collection
	 */
	public function getVisibleAttributes(string $tag=''): Collection
	{
		static $cache = [];

		if (! Arr::get($cache,$tag ?: '_all_')) {
			$ot = $this->getOtherTags();

			$cache[$tag ?: '_all_'] = $this->objects
				->filter(fn($item)=>(! $item->is_internal) && ((! $item->no_attr_tags) || (! $tag) || ($tag === Entry::TAG_NOTAG)))
				->filter(fn($item)=>(! $tag) || $ot->has($item->name_lc) || count($item->tagValues($tag)) > 0);
		}

		return $cache[$tag ?: '_all_'];
	}

	public function hasAttribute(int|string $key): bool
	{
		return $this->objects
			->has($key);
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

	public function setRDNBase(string $bdn): void
	{
		if ($this->exists)
			throw new InvalidUsage('Cannot set RDN base on existing entries');

		$this->rdnbase = $bdn;

		$this->templates = $this->templates
			->filter(fn($item)=>(! $item->regexp) || preg_match($item->regexp,$bdn));
	}

	public function template(string $item): Template|Null
	{
		return Arr::get($this->templates,$item);
	}
}