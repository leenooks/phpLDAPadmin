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

	protected ?AttributeType $schema = NULL;

	/*
	# Source of this attribute definition
	protected $source;
	*/

	// Current and Old Values
	protected Collection $values;

	// Is this attribute an internal attribute
	protected bool $is_internal = FALSE;

	// Is this attribute the RDN?
	protected bool $is_rdn = FALSE;

	// MIN/MAX number of values
	protected int $min_values_count = 0;
	protected int $max_values_count = 0;

	// RFC3866 Language Tags
	protected Collection $lang_tags;

	// The old values for this attribute - helps with isDirty() to determine if there is an update pending
	protected Collection $oldValues;

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

	public function __construct(string $name,array $values)
	{
		$this->name = $name;
		$this->values = collect($values);
		$this->lang_tags = collect();
		$this->oldValues = collect($values);

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
			// Is this an internal attribute
			'is_internal' => isset($this->{$key}) && $this->{$key},
			// Is this attribute the RDN
			'is_rdn' => $this->is_rdn,
			// We prefer the name as per the schema if it exists
			'name' => $this->schema ? $this->schema->{$key} : $this->{$key},
			// Attribute name in lower case
			'name_lc' => strtolower($this->name),
			// Old Values
			'old_values' => $this->oldValues,
			// Attribute values
			'values' => $this->values,
			// Required by Object Classes
			'required_by' => $this->schema?->required_by_object_classes ?: collect(),
			// Used in Object Classes
			'used_in' => $this->schema?->used_in_object_classes ?: collect(),

			default => throw new \Exception('Unknown key:' . $key),
		};
	}

	public function __set(string $key,mixed $values): void
	{
		switch ($key) {
			case 'value':
				$this->values = collect($values);
				break;

			default:
		}
	}

	public function __toString(): string
	{
		return $this->name;
	}

	public function addValue(string $value): void
	{
		$this->values->push($value);
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

		// objectClasses requiring this attribute
		// @todo limit this to this DNs objectclasses
		// eg: $result->put('required','Required by objectClasses: a,b');
		if ($this->required_by->count())
			$result->put(__('required'),sprintf('%s: %s',__('Required Attribute by ObjectClass(es)'),$this->required_by->join(',')));

		// This attribute has language tags
		if ($this->lang_tags->count())
			$result->put(__('language tags'),sprintf('%s: %d',__('This Attribute has Language Tags'),$this->lang_tags->count()));

		return $result->toArray();
	}

	/**
	 * Determine if this attribute has changes
	 *
	 * @return bool
	 */
	public function isDirty(): bool
	{
		return ($this->oldValues->count() !== $this->values->count())
			|| ($this->values->diff($this->oldValues)->count() !== 0);
	}

	public function oldValues(array $array): void
	{
		$this->oldValues = collect($array);
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
		return view('components.attribute')
			->with('o',$this)
			->with('edit',$edit)
			->with('old',$old)
			->with('new',$new);
	}

	public function render_item_old(int $key): ?string
	{
		return Arr::get($this->old_values,$key);
	}

	public function render_item_new(int $key): ?string
	{
		return Arr::get($this->values,$key);
	}

	/**
	 * If this attribute has RFC3866 Language Tags, this will enable those values to be captured
	 *
	 * @param string $tag
	 * @param array $value
	 * @return void
	 */
	public function setLangTag(string $tag,array $value): void
	{
		$this->lang_tags->put($tag,$value);
	}

	public function setRDN(): void
	{
		$this->is_rdn = TRUE;
	}
}