<?php

namespace App\Ldap;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use LdapRecord\Laravel\Events\Auth\DiscoveredWithCredentials;
use LdapRecord\Laravel\LdapUserRepository as LdapUserRepositoryBase;
use LdapRecord\Models\Model;

class LdapUserRepository extends LdapUserRepositoryBase
{
	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param array $credentials
	 *
	 * @return Model|null
	 * @throws \LdapRecord\Query\ObjectNotFoundException
	 */
	public function findByCredentials(array $credentials = []): ?Model
	{
		if (empty($credentials)) {
			return NULL;
		}

		// Look for a user using all our baseDNs
		foreach ((new Entry)->baseDNs() as $base) {
			$query = $this->query()->setBaseDn($base);

			foreach ($credentials as $key => $value) {
				if (Str::contains($key, $this->bypassCredentialKeys)) {
					continue;
				}

				if (is_array($value) || $value instanceof Arrayable) {
					$query->whereIn($key, $value);
				} else {
					$query->where($key, $value);
				}
			}

			if (! is_null($user = $query->first())) {
				event(new DiscoveredWithCredentials($user));

				return $user;
			}
		}

		return NULL;
	}

	/**
	 * Get a user by their object GUID.
	 *
	 * @param string $guid
	 *
	 * @return Model|null
	 * @throws \LdapRecord\Query\ObjectNotFoundException
	 */
	public function findByGuid($guid): ?Model
	{
		// Look for a user using all our baseDNs
		foreach ((new Entry)->baseDNs() as $base) {
			$user = $this->query()->setBaseDn($base)->findByGuid($guid);

			if ($user)
				return $user;
		}

		return NULL;
	}
}