<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/EntryWriter.php,v 1.2.2.1 2008/01/27 06:48:59 wurley Exp $

define('ENTRY_WRITER_CREATION_CONTEXT', '1');
define('ENTRY_WRITER_EDITING_CONTEXT', '2');

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 *
 * Visit an entry and its attributes to draw them
 */
class EntryWriter extends Visitor {
	# Ldapserver from context
	protected $index;

	# Context : creation or editing
	protected $context;

	# visited attributes
	protected $internal_attributes;
	protected $shown_attributes;
	protected $hidden_attributes;

	# are we visiting the attributes of an entry
	protected $visit_attributes;

	public function __construct($ldapserver) {
		$this->index = $ldapserver->server_id;
		$this->visit_attributes = true;
		$this->context = 0;
	}

	public function getLDAPServer() {
		static $CACHE;

		if (! isset($CACHE[$this->index]))
			$CACHE[$this->index] = $_SESSION[APPCONFIG]->ldapservers->Instance($this->index);

		return $CACHE[$this->index];
	}

	/**************************/
	/* Paint an Entry         */
	/**************************/

	public function visitEntryStart($entry) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$entry->getDn());

		// init
		$this->init('Visit', $entry);
	}

	public function visitEntryEnd($entry) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$entry->getDn());
	}

	protected function initEntryVisit($entry) {
		$this->internal_attributes = array();
		$this->shown_attributes = array();
		$this->hidden_attributes = array();
	}

	/********************************/
	/* Paint a DefaultCreatingEntry */
	/********************************/

	protected function initDefaultCreatingEntryVisit($entry) {
		$this->context = ENTRY_WRITER_CREATION_CONTEXT;
		$this->init('Entry::Visit', $entry);
	}

	/*******************************/
	/* Paint a DefaultEditingEntry */
	/*******************************/

	protected function initDefaultEditingEntryVisit($entry) {
		$this->context = ENTRY_WRITER_EDITING_CONTEXT;
		$this->init('Entry::Visit', $entry);
	}

	/*********************************/
	/* Paint a TemplateCreatingEntry */
	/*********************************/

	/********************************/
	/* Paint a TemplateEditingEntry */
	/********************************/

	/**************************/
	/* Paint an Attribute     */
	/**************************/

	public function visitAttribute($attribute) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s,%s)',1,__FILE__,__LINE__,__METHOD__,$attribute->getName(),$this->visit_attributes);

		if (!$this->visit_attributes) return;

		if ($attribute->isInternal()) $this->internal_attributes[] = $attribute;
		elseif ($attribute->isVisible()) $this->shown_attributes[] = $attribute;
		else $this->hidden_attributes[] = $attribute;
	}
}

?>
