<?php

namespace App\Classes\LDAP\Schema;

/**
 * A simple class for representing AttributeTypes used only by the ObjectClass class.
 *
 * Users should never instantiate this class. It represents an attribute internal to
 * an ObjectClass. If PHP supported inner-classes and variable permissions, this would
 * be interior to class ObjectClass and flagged private. The reason this class is used
 * and not the "real" class AttributeType is because this class supports the notion of
 * a "source" objectClass, meaning that it keeps track of which objectClass originally
 * specified it. This class is therefore used by the class ObjectClass to determine
 * inheritance.
 */
final class ObjectClassAttribute extends Base {
	// This Attribute's root.
	private string $source;
	public bool $required = FALSE;

	/**
	 * Creates a new ObjectClassAttribute with specified name and source objectClass.
	 *
	 * @param string $name the name of the new attribute.
	 * @param string $source the name of the ObjectClass which specifies this attribute.
	 */
	public function __construct($name,$source)
	{
		$this->name = $name;
		$this->source = $source;
	}

	public function __get(string $key): mixed
	{
		return match ($key) {
			'source' => $this->source,
			default => parent::__get($key),
		};
	}
}