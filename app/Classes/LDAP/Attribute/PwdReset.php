<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute;
use App\Classes\Template;
use App\Interfaces\{ForceSingleValue,NoAttrTag};
use App\Ldap\Entry;

/**
 * Represents the pwdReset attribute from OpenLDAP ppolicy overlay
 */
final class PwdReset extends Attribute implements ForceSingleValue,NoAttrTag
{
	public function __construct(string $dn, string $name, array $values, array $oc = [])
	{
		parent::__construct($dn, $name, $values, $oc);
		$this->_is_internal = FALSE;
	}

	/**
	 * Override properties to handle NULL schema gracefully for this virtual attribute
	 */
	public function __get(string $key): mixed
	{
		// Handle schema-based properties if schema exists
		if ($this->schema !== NULL) {
			switch ($key) {
				case 'description':
				case 'name':
				case 'name_lc':
				case 'is_editable':
				case 'required_by':
				case 'used_in':
					return parent::__get($key);
			}
		}

		// Fallback values when schema is NULL (operational attribute not in LDAP schema)
		return match ($key) {
			'description' => 'Password Reset Flag - Forces user to change password at next login (ppolicy overlay)',
			'name' => 'pwdReset',
			'name_lc' => 'pwdreset',
			'is_editable' => TRUE,
			'required_by' => collect(),
			'used_in' => collect(),
			default => parent::__get($key),
		};
	}

	/* METHODS */

	public function isDirty(): bool
	{
		$old = $this->values_old->dot()->filter(fn($item)=>! is_null($item) && $item !== '');
		$new = $this->values->dot()->filter(fn($item)=>! is_null($item) && $item !== '');

		return $old->count() !== $new->count() || $old->diff($new)->count() !== 0;
	}

	/**
	 * pwdReset is an operational attribute (ppolicy overlay) that:
	 * - Can only be set to TRUE (server manages FALSE/removal automatically)
	 * - When set to TRUE, user must change password at next login
	 * - When set to FALSE we keep the attribute present with value FALSE to remain editable
	 */
	public function getDirty(): array
	{
		if (! $this->isDirty())
			return [];

		$normalized = collect($this->values->toArray())
			->map(fn($values)=>collect($values)
				->map(fn($v)=>strtoupper(trim($v)) === 'TRUE' ? 'TRUE' : 'FALSE')
				->values()
				->toArray());

		// If any TRUE values exist, send only the TRUEs; otherwise send FALSE to keep attribute present
		$trueValues = $normalized
			->map(fn($values)=>collect($values)->filter(fn($v)=>$v === 'TRUE')->values()->toArray())
			->filter(fn($values)=>count($values) > 0);

		return $trueValues->isNotEmpty()
			? [$this->name_lc => $trueValues->toArray()]
			: [$this->name_lc => [Entry::TAG_NOTAG => ['FALSE']]];
	}

	public function render_item_old(string $dotkey): ?string
	{
		$value = $this->values_old->dot()->get($dotkey);

		if ($value === NULL || $value === '')
			return NULL;

		return strtoupper($value) === 'TRUE' ? 'TRUE' : 'FALSE';
	}

	public function render_item_new(string $dotkey): ?string
	{
		$value = $this->values->dot()->get($dotkey);

		if ($value === NULL || $value === '')
			return NULL;

		return strtoupper($value) === 'TRUE' ? 'TRUE' : 'FALSE';
	}

	public function render(string $attrtag,int $index,?View $view=NULL,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return parent::render(
			attrtag: $attrtag,
			index: $index,
			view: view('components.attribute.value.pwdreset'),
			edit: $edit,
			editable: $editable,
			new: $new,
			updated: $updated,
			template: $template);
	}
}