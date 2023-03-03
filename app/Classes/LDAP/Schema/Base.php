<?php

namespace App\Classes\LDAP\Schema;

use App\Exceptions\InvalidUsage;

/**
 * Generic parent class for all schema items.
 *
 * A schema item is an ObjectClass, an AttributeBype, a MatchingRule, or a Syntax.
 * All schema items have at least two things in common: An OID and a Description.
 */
abstract class Base {
	// Record the LDAP String
	private string $line;

	// The schema item's name.
	protected ?string $name = NULL;

	// The OID of this schema item.
	protected string $oid;

	# The description of this schema item.
	protected ?string $description = NULL;

	// Boolean value indicating whether this objectClass is obsolete
	private bool $is_obsolete = FALSE;

	public function __construct(string $line)
	{
		$this->line = $line;
	}

	public function __get(string $key): mixed
	{
		switch ($key) {
			case 'description': return $this->description;
			case 'is_obsolete': return $this->is_obsolete;
			case 'line': return $this->line;
			case 'name': return $this->name;
			case 'name_lc': return strtolower($this->name);
			case 'oid': return $this->oid;

			default:
				throw new InvalidUsage('Unknown key:'.$key);
		}
	}

	/**
	 * @return string
	 * @deprecated replace with $class->description
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * Gets whether this item is flagged as obsolete by the LDAP server.
	 *
	 * @deprecated replace with $this->is_obsolete
	 */
	public function getIsObsolete(): bool
	{
		return $this->is_obsolete;
	}

	/**
	 * Return the objects name.
	 *
	 * @param boolean $lower Return the name in lower case (default)
	 * @return string The name
	 * @deprecated use object->name
	 */
	public function getName(bool $lower=TRUE): string
	{
		return $lower ? strtolower($this->name) : $this->name;
	}

	/**
	 * Return the objects name.
	 *
	 * @return string The name
	 * @deprecated use object->oid
	 */
	public function getOID(): string
	{
		return $this->oid;
	}

	public function setDescription(string $desc): void
	{
		$this->description = $desc;
	}

	/**
	 * Sets this attribute's name.
	 *
	 * @param string $name The new name to give this attribute.
	 */
	public function setName($name): void
	{
		$this->name = $name;
	}

	public function setOID(string $oid): void
	{
		$this->oid = $oid;
	}
}