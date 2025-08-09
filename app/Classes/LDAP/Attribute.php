<?php

namespace App\Classes\LDAP;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Schema\AttributeType;
use App\Classes\Template;
use App\Exceptions\InvalidUsage;
use App\Ldap\Entry;

/**
 * Represents an attribute of an LDAP Object
 */
class Attribute implements \Countable, \ArrayAccess
{
	// Is this attribute an internal attribute
	protected ?bool $_is_internal = NULL;
	protected(set) bool $no_attr_tags = FALSE;
	protected(set) bool $base64_values = FALSE;

	// MIN/MAX number of values
	protected(set) int $min_values_count = 0;
	protected(set) int $max_values_count = 0;

	// The schema's representation of this attribute
	protected(set) ?AttributeType $schema;

	// The DN this object is in
	protected(set) string $dn;
	// The old values for this attribute - helps with isDirty() to determine if there is an update pending
	protected(set) Collection $values_old;
	// Current Values
	protected Collection $values;
	// The objectclasses of the entry that has this attribute
	protected(set) Collection $oc;

	private const SYNTAX_CERTIFICATE = '1.3.6.1.4.1.1466.115.121.1.8';
	private const SYNTAX_CERTIFICATE_LIST = '1.3.6.1.4.1.1466.115.121.1.9';
	protected const CERTIFICATE_ENCODE_LENGTH = 76;

	// If rendering is done in a table, with a <tr> for each value
	protected(set) bool $render_tables = FALSE;

	/**
	 * Create an Attribute
	 *
	 * @param string $dn DN this attribute is used in
	 * @param string $name Name of the attribute
	 * @param array $values Current Values
	 * @param array $oc The object classes that the DN of this attribute has
	 * @throws InvalidUsage
	 */
	public function __construct(string $dn,string $name,array $values,array $oc=[])
	{
		$this->dn = $dn;
		$this->values = collect($values)
			->map(function($item) { if (is_array($item)) asort($item); return $item; });
		$this->values_old = clone $this->values;

		$this->schema = config('server')
			->schema('attributetypes',$name);

		$this->oc = collect();

		// Get the objectclass heirarchy for required attribute determination
		foreach ($oc as $objectclass) {
			$soc = config('server')->schema('objectclasses',$objectclass);

			if ($soc) {
				$this->oc->push($soc->name);
				$this->oc = $this->oc->merge($soc->getParents()->pluck('name'));
			}
		}
	}

	public function __get(string $key): mixed
	{
		return match ($key) {
			// Binary attr tags
			'binarytags' =>collect(),
			// Can this attribute have more values
			'can_addvalues' => $this->schema && (! $this->schema->is_single_value) && ((! $this->max_values_count) || ($this->values->count() < $this->max_values_count)),
			// Schema attribute description
			'description' => $this->schema ? $this->schema->{$key} : NULL,
			// Attribute hints
			'hints' => $this->hints(),
			// Keys with values
			'keys' => $this->values
				->keys()
				->filter(fn($item)=>collect(Entry::TAG_NOVALUES)
					->filter(fn($item)=>\Str::endsWith($key,$item))->count() === 0),
			// Attribute language tags
			'langtags' => ($this->no_attr_tags || (! $this->values->count()))
				? collect(Entry::TAG_NOTAG)
				: $this->values
					->keys()
					->filter(fn($item)=>($item === Entry::TAG_NOTAG) || preg_match(sprintf('/%s;?/',Entry::TAG_CHARS_LANG),$item))
					->sortBy(fn($item)=>($item === Entry::TAG_NOTAG) ? NULL : $item),
			// Can this attribute be edited
			'is_editable' => $this->schema ? $this->schema->{$key} : NULL,
			// Is this an internal attribute
			'is_internal' => is_null($this->_is_internal) ? ($this->used_in->count() === 0) : $this->_is_internal,
			// Objectclasses that required this attribute for an LDAP entry
			'required' => $this->required(),
			// Is this attribute an RDN attribute
			'is_rdn' => $this->isRDN(),
			// We prefer the name as per the schema if it exists
			'name' => $this->schema->{$key},
			// Attribute name in lower case
			'name_lc' => strtolower($this->name),
			// Required by Object Classes
			'required_by' => $this->schema?->required_by_object_classes ?: collect(),
			// Used in Object Classes
			'used_in' => $this->schema?->used_in_object_classes ?: collect(),
			// For single value attributes
			'value' => $this->schema?->is_single_value ? $this->values->first() : NULL,
			// This attribute's value, less special tags
			'values' => $this->values
				->only($this->keys)
				->filter(fn($item)=>array_filter($item)),
			// Return the values as they would be rendered
			'values_rendered' => $this->values
				->only($this->keys)
				->filter(fn($item)=>array_filter($item))
				->dot()
				->map(fn($item,$key)=>$this->render_item_new($key))
				->undot(),

			default => throw new \Exception('Unknown key:' . $key),
		};
	}

	public function __toString(): string
	{
		return $this->values->dot()->join("\n");
	}

	/* INTERFACE */

	public function count(): int
	{
		return $this->values
			->dot()
			->count();
	}

	public function offsetExists(mixed $offset): bool
	{
		return $this->values
			->dot()
			->has($offset);
	}

	public function offsetGet(mixed $offset): mixed
	{
		return $this->values
			->dot()
			->get($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		// We cannot set new values using array syntax
	}

	public function offsetUnset(mixed $offset): void
	{
		// We cannot clear values using array syntax
	}

	/* METHODS */

	public function addValue(string $tag,array $values): void
	{
		$this->values
			->put(
				$tag,
				array_unique(array_filter(array_merge($this->values
					->get($tag,[]),$values))));
	}

	public function addValueOld(string $tag,array $values): void
	{
		$this->values_old
			->put(
				$tag,
				array_unique(array_merge($this->values_old
					->get($tag,[]),$values)));
	}

	protected function dotkey(string $attrtag,int $index): string
	{
		return sprintf('%s.%s',$attrtag,$index);
	}

	/**
	 * If this attribute has changes, re-render the attribute values
	 *
	 * @return array
	 */
	public function getDirty(): array
	{
		$dirty = [];

		if ($this->isDirty())
			$dirty = [$this->name_lc => $this->values->toArray()];

		return $dirty;
	}

	/**
	 * Does this attribute values have a helper to process the values
	 *
	 * @param array $values
	 * @return bool
	 */
	protected function getHelpers(array $values): Collection
	{
		return collect($values)->keys()
			->filter(fn($item)=>\Str::endsWith($item,Entry::TAG_HELPER));
	}

	/**
	 * Return the hints about this attribute, ie: RDN, Required, etc
	 *
	 * @return Collection
	 */
	public function hints(): Collection
	{
		$result = collect();

		if ($this->is_internal)
			return $result;

		// Is this Attribute an RDN
		if ($this->is_rdn)
			$result->put(__('rdn'),__('This attribute is required for the RDN'));

		if ($this->required()->count())
			$result->put(__('required'),sprintf('%s: %s',__('Required Attribute by ObjectClass(es)'),$this->required()->join(', ')));

		// If this attribute is a dynamic attribute
		if ($this->isDynamic())
			$result->put(__('dynamic'),__('These are dynamic values present as a result of another attribute'));

		return $result;
	}

	/**
	 * Determine if this attribute has changes
	 *
	 * @return bool
	 */
	public function isDirty(): bool
	{
		return (($a=$this->values_old->dot()->filter())->keys()->count() !== ($b=$this->values->dot()->filter())->keys()->count())
			|| ($a->count() !== $b->count())
			|| ($a->diff($b)->count() !== 0);
	}

	/**
	 * Are these values as a result of a dynamic attribute
	 *
	 * @return bool
	 */
	public function isDynamic(): bool
	{
		return $this->schema->used_in_object_classes
			->keys()
			->intersect($this->oc)
			->count() === 0;
	}

	/**
	 * Work out if this attribute is an RDN attribute
	 *
	 * @return bool
	 */
	public function isRDN(): bool
	{
		// If we dont have an DN, then we cant know
		if (! $this->dn)
			return FALSE;

		$rdns = collect(explode('+',substr($this->dn,0,strpos($this->dn,','))));

		return $rdns->filter(fn($item) => str_starts_with($item,$this->name.'='))->count() > 0;
	}

	/**
	 * Display the attribute value
	 *
	 * @param string $attrtag Attribute tag for the value being rendered
	 * @param int $index Index of a multivalue attribute being rendered
	 * @param bool $edit Render an edit form
	 * @param bool $editable Render the item as readonly; however, JavaScript can make it editable
	 * @param bool $new Is the rendered attribute new (used to set border-focus)
	 * @param bool $updated Has the entry been updated (used to set border-focus)
	 * @param Template|null $template The template this value is being rendered with
	 * @return View
	 */
	public function render(string $attrtag,int $index,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		$dotkey = $this->dotkey($attrtag,$index);

		// @note Internal attributes cannot be edited
		if ($this->is_internal)
			return view('components.attribute.value.internal')
				->with('o',$this)
				->with('value',$this->render_item_new($dotkey));

		$view = view()->exists($x='components.attribute.value.'.$this->name_lc)
			? view($x)
			: view('components.attribute.value');

		return $view
			->with('o',$this)
			->with('dotkey',$dotkey)
			->with('value',$this->render_item_new($dotkey))
			->with('edit',$edit)
			->with('editable',$editable)
			->with('new',$new)
			->with('attrtag',$attrtag)
			->with('index',$index)
			->with('updated',$updated)
			->with('template',$template);
	}

	/**
	 * Return the value of the original old values
	 *
	 * @param string $dotkey
	 * @return string|null
	 */
	public function render_item_old(string $dotkey): ?string
	{
		return match ($this->schema->syntax_oid) {
			self::SYNTAX_CERTIFICATE => join("\n",str_split(base64_encode($this->values_old->dot()->get($dotkey)),self::CERTIFICATE_ENCODE_LENGTH)),
			self::SYNTAX_CERTIFICATE_LIST => join("\n",str_split(base64_encode($this->values_old->dot()->get($dotkey)),self::CERTIFICATE_ENCODE_LENGTH)),

			default => $this->values_old->dot()->get($dotkey),
		};
	}

	/**
	 * Return the value of the new values, which would include any pending udpates
	 *
	 * @param string $dotkey
	 * @return string|null
	 */
	public function render_item_new(string $dotkey): ?string
	{
		return $this->values->dot()->get($dotkey);
	}

	/**
	 * Work out if this attribute is required by an objectClass the entry has
	 *
	 * @return Collection
	 */
	private function required(): Collection
	{
		// If we dont have any objectclasses then we cant know if it is required
		return $this->oc->count()
			? $this->oc->intersect($this->required_by->keys())->sort()
			: collect();
	}

	public function setValues(array $values): void
	{
		$this->values =
			($this->getHelpers($values)->count())
				? $this->setValuesHelper(collect($values))
				: collect($values)
					->map(fn($item)=>$this->base64_values ? array_map('base64_decode',$item) : $item);
	}

	/**
	 * Process TAG_HELPER values, typically in sub classes
	 *
	 * @param Collection $values
	 * @return Collection
	 * @note This method should be overridden in a child class
	 */
	protected function setValuesHelper(Collection $values): Collection
	{
		return $values;
	}

	/**
	 * Return the new values for this attribute, which would include any pending updates
	 *
	 * @param string $tag
	 * @return Collection
	 */
	public function tagValues(string $tag=Entry::TAG_NOTAG): Collection
	{
		return collect($this->values
			->filter(fn($item,$key)=>($key===$tag))
			->get($tag,[]));
	}

	/**
	 * Return the original values for this attribute, as stored in the LDAP server
	 *
	 * @param string $tag
	 * @return Collection
	 */
	public function tagValuesOld(string $tag=Entry::TAG_NOTAG): Collection
	{
		return collect($this->values_old
			->filter(fn($item,$key)=>($key===$tag))
			->get($tag,[]));
	}
}