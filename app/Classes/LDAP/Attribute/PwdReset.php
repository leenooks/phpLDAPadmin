<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute;
use App\Classes\Template;

/**
 * Represents the pwdReset attribute from OpenLDAP ppolicy overlay
 */
final class PwdReset extends Attribute
{
	protected(set) bool $no_attr_tags = TRUE;
	protected(set) int $max_values_count = 1;
	protected ?bool $_is_internal = FALSE;

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
	 * - FALSE values or empty values should not be sent to the server
	 */
	public function getDirty(): array
	{
		$dirty = [];

		if (! $this->isDirty())
			return $dirty;

		// Only send TRUE values - FALSE/empty is managed by server
		$trueValues = collect($this->values->toArray())
			->map(fn($values)=>collect($values)->filter(fn($v)=>strtoupper(trim($v)) === 'TRUE')->values()->toArray())
			->filter(fn($values)=>count($values) > 0);

		if ($trueValues->isNotEmpty())
			$dirty = [$this->name_lc => $trueValues->toArray()];

		return $dirty;
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