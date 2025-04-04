<?php

namespace App\Classes\LDAP;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Schema\AttributeType;

/**
 * Represents an attribute of an LDAP Object
 */
class Attribute implements \Countable, \ArrayAccess, \Iterator
{
	// Attribute Name
	protected string $name;
	private int $counter = 0;

	// Is this attribute an internal attribute
	protected(set) bool $is_internal = FALSE;
	protected(set) bool $no_attr_tags = FALSE;

	// MIN/MAX number of values
	protected(set) int $min_values_count = 0;
	protected(set) int $max_values_count = 0;

	// The schema's representation of this attribute
	protected(set) ?AttributeType $schema;

	// The DN this object is in
	protected(set) string $dn;
	// The old values for this attribute - helps with isDirty() to determine if there is an update pending
	private Collection $_values_old;
	// Current Values
	private Collection $_values;
	// The objectclasses of the entry that has this attribute
	protected(set) Collection $oc;

	/*
	# Has the attribute been modified
	protected $modified = false;
	# Is the attribute being deleted because of an object class removal
	protected $forcedelete = false;
	# Is the attribute visible
	protected $visible = false;
	protected $forcehide = false;
	# Is the attribute modifiable
	protected $readonly = false;
	# LDAP attribute type MUST/MAY
	protected $ldaptype = null;
	# Attribute property type (eg password, select, multiselect)
	protected $type = '';
	# Attribute value to keep unique
	protected $unique = false;

	# Display parameters
	protected $display = '';
	protected $icon = '';
	protected $hint = '';
	# Helper details
	protected $helper = array();
	protected $helpervalue = array();
	# Onchange details
	protected $onchange = array();
	# Show spacer after this attribute is rendered
	protected $spacer = false;
	protected $verify = false;

	# Component size
	protected $size = 0;
	# Value max length
	protected $maxlength = 0;
	# Text Area sizings
	protected $cols = 0;
	protected $rows = 0;

	# Public for sorting
	public $page = 1;
	public $order = 255;
	public $ordersort = 255;

	# Schema Aliases for this attribute (stored in lowercase)
	protected $aliases = array();

	# Configuration for automatically generated values
	protected $autovalue = array();
	protected $postvalue = array();
	*/

	/**
	 * Create an Attribute
	 *
	 * @param string $dn DN this attribute is used in
	 * @param string $name Name of the attribute
	 * @param array $values Current Values
	 * @param array $oc The objectclasses that the DN of this attribute has
	 */
	public function __construct(string $dn,string $name,array $values,array $oc=[])
	{
		$this->dn = $dn;
		$this->name = $name;
		$this->values_old = collect($values);

		$this->values = collect();
		$this->oc = collect($oc);

		$this->schema = (new Server)
			->schema('attributetypes',$name);

		/*
		# Should this attribute be hidden
		if ($server->isAttrHidden($this->name))
			$this->forcehide = true;

		# Should this attribute value be read only
		if ($server->isAttrReadOnly($this->name))
			$this->readonly = true;

		# Should this attribute value be unique
		if ($server->isAttrUnique($this->name))
			$this->unique = true;
		*/
	}

	public function __get(string $key): mixed
	{
		return match ($key) {
			// List all the attributes
			'attributes' => $this->attributes(),
			// Can this attribute have more values
			'can_addvalues' => $this->schema && (! $this->schema->is_single_value) && ((! $this->max_values_count) || ($this->values->count() < $this->max_values_count)),
			// Schema attribute description
			'description' => $this->schema ? $this->schema->{$key} : NULL,
			// Attribute hints
			'hints' => $this->hints(),
			// Can this attribute be edited
			'is_editable' => $this->schema ? $this->schema->{$key} : NULL,
			// Objectclasses that required this attribute for an LDAP entry
			'required' => $this->required(),
			// Is this attribute an RDN attribute
			'is_rdn' => $this->isRDN(),
			// We prefer the name as per the schema if it exists
			'name' => $this->schema ? $this->schema->{$key} : $this->{$key},
			// Attribute name in lower case
			'name_lc' => strtolower($this->name),
			// Required by Object Classes
			'required_by' => $this->schema?->required_by_object_classes ?: collect(),
			// Used in Object Classes
			'used_in' => $this->schema?->used_in_object_classes ?: collect(),
			// The current attribute values
			'values' => $this->no_attr_tags ? collect($this->_values->first()) : $this->_values,
			// The original attribute values
			'values_old' => $this->no_attr_tags ? collect($this->_values_old->first()) : $this->_values_old,

			default => throw new \Exception('Unknown key:' . $key),
		};
	}

	public function __set(string $key,mixed $values)
	{
		switch ($key) {
			case 'values':
				$this->_values = $values;
				break;

			case 'values_old':
				$this->_values_old = $values;
				break;

			default:
				throw new \Exception('Unknown key:'.$key);
		}
	}

	public function __toString(): string
	{
		return $this->name;
	}

	public function addValue(string $tag,string $value): void
	{
		$this->_values->put(
			$tag,
			$this->_values
				->get($tag,collect())
				->push($value));
	}

	public function current(): mixed
	{
		return $this->values->get($this->counter);
	}

	public function next(): void
	{
		$this->counter++;
	}

	public function key(): mixed
	{
		return $this->counter;
	}

	public function valid(): bool
	{
		return $this->values->has($this->counter);
	}

	public function rewind(): void
	{
		$this->counter = 0;
	}

	public function count(): int
	{
		return $this->values->count();
	}

	public function offsetExists(mixed $offset): bool
	{
		return ! is_null($this->values->has($offset));
	}

	public function offsetGet(mixed $offset): mixed
	{
		return $this->values->get($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		// We cannot set new values using array syntax
	}

	public function offsetUnset(mixed $offset): void
	{
		// We cannot clear values using array syntax
	}

	/**
	 * Return the hints about this attribute, ie: RDN, Required, etc
	 *
	 * @return array
	 */
	public function hints(): array
	{
		$result = collect();

		// Is this Attribute an RDN
		if ($this->is_rdn)
			$result->put(__('rdn'),__('This attribute is required for the RDN'));

		// If this attribute name is an alias for the schema attribute name
		// @todo

		if ($this->required()->count())
			$result->put(__('required'),sprintf('%s: %s',__('Required Attribute by ObjectClass(es)'),$this->required()->join(', ')));

		return $result->toArray();
	}

	/**
	 * Determine if this attribute has changes
	 *
	 * @return bool
	 */
	public function isDirty(): bool
	{
		return ($this->values_old->count() !== $this->values->count())
			|| ($this->values->diff($this->values_old)->count() !== 0);
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
	 * @param bool $edit Render an edit form
	 * @param bool $old Use old value
	 * @param bool $new Enable adding values
	 * @return View
	 */
	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE): View
	{
		$view = view()->exists($x='components.attribute.'.$this->name_lc)
			? view($x)
			: view('components.attribute');

		return $view
			->with('o',$this)
			->with('edit',$edit)
			->with('old',$old)
			->with('new',$new);
	}

	public function render_item_old(int $key): ?string
	{
		return Arr::get($this->values_old,$key);
	}

	public function render_item_new(int $key): ?string
	{
		return Arr::get($this->values,$key);
	}

	/**
	 * Work out if this attribute is required by an objectClass the entry has
	 *
	 * @return Collection
	 */
	public function required(): Collection
	{
		//avoid passing null values that will cause a /frame 409 error
		if ( !$this->oc || !$this->schema || !$this->schema->required_by_object_classes ) {
        	return collect();
    	}
		// If we dont have any objectclasses then we cant know if it is required
		return $this->oc->count()
			? $this->oc->intersect($this->schema->required_by_object_classes->keys())->sort()
			: collect();
	}
}
