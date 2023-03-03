<?php

namespace App\Ldap;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LdapRecord\Models\Model;

use App\Classes\LDAP\Attribute;
use App\Classes\LDAP\Attribute\Factory;

class Entry extends Model
{
	/* OVERRIDES */

	public function getAttributes(): array
	{
		static $result = NULL;

		if (is_null($result)) {
			$result = collect();

			foreach (parent::getAttributes() as $attribute => $value) {
				$o = Factory::create($attribute,$value);

				// Set the rdn flag
				if (preg_match('/^'.$attribute.'=/i',$this->dn))
					$o->setRDN();

				// Set required flag
				$o->required_by(collect($this->getAttribute('objectclass')));

				$result->put($attribute,$o);
			}

			$sort = collect(config('ldap.attr_display_order',[]))->transform(function($item) { return strtolower($item); });

			// Order the attributes
			$result = $result->sortBy([function(Attribute $a,Attribute $b) use ($sort): int {
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

				// Case where neither $a, nor $b are in ldap.attr_display_order, $a_key = $b_key = one greater than num elements.
				// So we sort them alphabetically
				if ($a_key === $b_key)
					return strcasecmp($a->name,$b->name);

				// Case where at least one attribute or its friendly name is in $attrs_display_order
				// return -1 if $a before $b in $attrs_display_order
				return ($a_key < $b_key) ? -1 : 1;
			} ])->toArray();
		}

		return $result;
	}

	/* ATTRIBUTES */

	/**
	 * Return a key to use for sorting
	 *
	 * @todo This should be the DN in reverse order
	 * @return string
	 */
	public function getSortKeyAttribute(): string
	{
		return $this->getDn();
	}

	/* METHODS */

	/**
	 * Return a list of LDAP internal attributes
	 *
	 * @return Collection
	 */
	public function getInternalAttributes(): Collection
	{
		return collect($this->getAttributes())->filter(function($item) {
			return $item->is_internal;
		});
	}

	/**
	 * Return this list of user attributes
	 *
	 * @return Collection
	 */
	public function getVisibleAttributes(): Collection
	{
		return collect($this->getAttributes())->filter(function($item) {
			return ! $item->is_internal;
		});
	}

	/**
	 * Return an icon for a DN based on objectClass
	 *
	 * @return string
	 */
	public function icon(): string
	{
		$objectclasses = array_map('strtolower',$this->objectclass);

		// Return icon based upon objectClass value
		if (in_array('person',$objectclasses) ||
			in_array('organizationalperson',$objectclasses) ||
			in_array('inetorgperson',$objectclasses) ||
			in_array('account',$objectclasses) ||
			in_array('posixaccount',$objectclasses))

			return 'fas fa-user';

		elseif (in_array('organization',$objectclasses))
			return 'fas fa-university';

		elseif (in_array('organizationalunit',$objectclasses))
			return 'fas fa-object-group';

		elseif (in_array('posixgroup',$objectclasses) ||
			in_array('groupofnames',$objectclasses) ||
			in_array('groupofuniquenames',$objectclasses) ||
			in_array('group',$objectclasses))

			return 'fas fa-users';

		elseif (in_array('dcobject',$objectclasses) ||
			in_array('domainrelatedobject',$objectclasses) ||
			in_array('domain',$objectclasses) ||
			in_array('builtindomain',$objectclasses))

			return 'fas fa-network-wired';

		elseif (in_array('alias',$objectclasses))
			return 'fas fa-theater-masks';

		elseif (in_array('country',$objectclasses))
			return sprintf('flag %s',strtolower(Arr::get($this->c,0)));

		elseif (in_array('device',$objectclasses))
			return 'fas fa-mobile-alt';

		elseif (in_array('document',$objectclasses))
			return 'fas fa-file-alt';

		elseif (in_array('iphost',$objectclasses))
			return 'fas fa-wifi';

		elseif (in_array('room',$objectclasses))
			return 'fas fa-door-open';

		elseif (in_array('server',$objectclasses))
			return 'fas fa-server';

		elseif (in_array('openldaprootdse',$objectclasses))
			return 'fas fa-info';

		// Default
		return 'fa-fw fas fa-cog';
	}
}