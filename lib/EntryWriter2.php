<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/EntryWriter2.php,v 1.2.2.4 2008/11/29 11:33:53 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 *
 * Visit an entry and its attributes to draw them
 */
class EntryWriter2 extends EntryWriter1 {
	/********************************/
	/* Paint a DefaultCreatingEntry */
	/********************************/

	protected function drawDefaultCreatingEntryRdnChooser($entry) {
		$attrs = $entry->getAttributes();
		$rdn_attr = $entry->getRdnAttribute();

		echo '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';
		printf('<tr><td class="ew2_icon">&nbsp;</td><td class="ew2_attr"><b>%s%s</b></td><td class="ew2_val">','RDN',_(':'));
		echo '<select name="rdn_attribute">';
		printf('<option value="">%s</option>', _('select the rdn attribute'));

		foreach ($attrs as $attr) {
			$n = $attr->getName();
			if ($attr->getName() != 'objectClass') {
				$m = $attr->getFriendlyName();
				$b = '&nbsp;';
				printf('<option value="%s" %s>%s%s(%s)</option>', $n, ($rdn_attr == $attr) ? 'selected' : '', htmlspecialchars($m), $b, $n);
			}
		}
		echo '</select></td></tr>';
	}

	protected function drawDefaultCreatingEntryShownAttributes($entry) {
		$attrs = array();

		// put required attributes first
		foreach ($this->shown_attributes as $sa) {
			if ($sa->isRequired()) $attrs[] = $sa;
		}
		foreach ($this->shown_attributes as $sa) {
			if (!$sa->isRequired()) $attrs[] = $sa;
		}

		$has_required_attrs = false;
		$has_optional_attrs = false;
		foreach ($attrs as $attr) {
			if ($attr->isRequired()) {
				if (!$has_required_attrs) {
					echo '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';
					$has_required_attrs = true;
				}
			} else {
				if (!$has_optional_attrs) {
					if (!$has_required_attrs) {
						// no required attributes
					}
					echo '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';
					$has_optional_attrs = true;
				}
			}

			$this->draw('', $attr);
			echo "\n";
		}

		if (!$has_optional_attrs) {
			// no optional attributes
		}
	}

	public function drawDefaultCreatingEntryStepFormSubmitButton($entry, $step) {
		echo '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';
		if ($step == 1) {
			echo '<tr><td colspan="3"><center>';
			printf('<input type="submit" id="create_button" value="%s" />', _('Proceed &gt;&gt;'));
			echo '</center></td></tr>';
		} else {
			echo '<tr><td colspan="3"><center>';
			printf('<input type="submit" id="create_button" name="submit" value="%s" />', _('Create Object'));
			echo '</center></td></tr>';
		}
	}

	/*******************************/
	/* Paint a DefaultEditingEntry */
	/*******************************/

	protected function drawDefaultEditingEntryInternalAttributes($entry) {
		$counter = 0;

		echo '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';

		foreach ($this->internal_attributes as $attr) {
			$this->draw('', $attr);
			$counter++;
			echo "\n";
		}

		if ($counter == 0) {
			echo '<tr><td colspan="3">(';
			echo _('No internal attributes');
			echo ')</td></tr>';
		}
	}

	protected function drawDefaultEditingEntryShownAttributes($entry) {
		echo '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';
		parent::drawDefaultEditingEntryShownAttributes($entry);
	}

	protected function drawDefaultEditingEntryFormSubmitButton($entry) {
		echo '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';
		echo '<tr><td colspan="3"><center><input type="submit" value="';
		echo _('Save Changes');
		echo '" id="save_button" /></center></td></tr>';
	}

	/*********************************/
	/* Paint a TemplateCreatingEntry */
	/*********************************/

	protected function drawTemplateCreatingEntryShownAttributes($entry) {
		if ($entry->getSelectedTemplateName()) {
			echo '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';
		}
		parent::drawTemplateCreatingEntryShownAttributes($entry);
	}

	/********************************/
	/* Paint a TemplateEditingEntry */
	/********************************/

	protected function drawTemplateEditingEntryShownAttributes($entry) {
		echo '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';
		parent::drawTemplateEditingEntryShownAttributes($entry);
	}

	/**************************/
	/* Paint an Attribute     */
	/**************************/

	protected function drawAttribute($attribute) {
		if ($attribute->isVisible()) {
			if (($this->context == ENTRY_WRITER_EDITING_CONTEXT) && $attribute->hasBeenModified()) {
				echo '<tr class="updated">';
			} else {
				echo '<tr>';
			}
			$this->draw('Informations', $attribute);
		}

		$this->draw('Values', $attribute);

		if ($attribute->isVisible()) {
			echo '</tr>';
			if (($this->context == ENTRY_WRITER_EDITING_CONTEXT) && $attribute->hasBeenModified()) {
				//echo '<tr class="updated"><td class="bottom" colspan="3"></td></tr>';
			}
			if ($attribute->hasProperty('spacer') && $attribute->getProperty('spacer')) {
				echo '<tr class="spacer"><td colspan="3">&nbsp;</td></tr>';
			}
		}
	}

	protected function drawAttributeInformations($attribute) {
		echo '<td class="ew2_icon">';
		$this->draw('Icon', $attribute, '');
		echo '</td>';

		echo '<td class="ew2_attr">';
		$this->draw('Name', $attribute);
		echo _(':');

		echo '<br/>';
		if ($_SESSION[APPCONFIG]->GetValue('appearance', 'show_attribute_notes')) {
			$this->draw('Notes', $attribute);
		}

		echo '</td>';
	}

	protected function drawAttributeNotes($attribute) {
		$attr_note = '';

		$alias_note = $this->get('AliasNote', $attribute);
		if ($alias_note) {
			if (trim($attr_note)) $attr_note .= ', ';
			$attr_note .= $alias_note;
		}

		$required_note = $this->get('RequiredNote', $attribute);
		if ($required_note) {
			if (trim($attr_note)) $attr_note .= ', ';
			$attr_note .= $required_note;
		}

		$rdn_note = $this->get('RdnNote', $attribute);
		if ($rdn_note) {
			if (trim($attr_note)) $attr_note .= ', ';
			$attr_note .= $rdn_note;
		}

		if ($attr_note) printf('<sup><small>%s</small></sup>', $attr_note);

		if ($attribute->isReadOnly() && $this->getLDAPServer()->isAttrReadOnly($attribute->getName())) {
			printf('<small>(<acronym title="%s">%s</acronym>)</small>',
			       _('This attribute has been flagged as read only by the phpLDAPadmin administrator'),
			       _('read only'));
		}
	}

	protected function drawAttributeStartValueLine($attribute) {
		echo '<td class="ew2_val">';
	}

	protected function drawAttributeEndValueLine($attribute) {
		echo '</td>';
	}

	protected function drawAttributeValue($attribute, $i) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s, %d)',1,__FILE__,__LINE__,__METHOD__,$attribute->getName(),$i);

		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';

		if ($attribute->isVisible()) {
			echo '<table cellspacing="0" cellpadding="0" width="100%"><tr><td>';
		}

		$this->draw('OldValue', $attribute, $i);

		$this->draw('NewValue', $attribute, $i);

		if ($attribute->isVisible()) {
			echo '</td><td valign="top" align="right" width="100%">';
			if (($i == 0) && $attribute->isRequired() && $attribute->getEntry() && !$attribute->getEntry()->isReadOnly()) {
				echo '&nbsp;';
				$this->draw('RequiredSymbol', $attribute);
			}
			echo '</td></tr></table>';
		}
	}

	protected function drawAttributeMenu($attribute) {
		if ($attribute->getHint())
			printf('<img src="%s/light.png" alt="Hint" /> <span class="hint">%s</span>',IMGDIR,$attribute->getHint());

		parent::drawAttributeMenu($attribute);
	}

	/***************************/
	/* Paint a BinaryAttribute */
	/***************************/

	/***************************/
	/* Paint a DateAttribute   */
	/***************************/

	/***************************/
	/* Paint a DnAttribute     */
	/***************************/

	/***************************/
	/* Paint a GidAttribute    */
	/***************************/

	/***************************/
	/* Paint a JpegAttribute   */
	/***************************/

	/******************************/
	/* Paint a MultiLineAttribute */
	/******************************/

	/********************************/
	/* Paint a ObjectClassAttribute */
	/********************************/

	/*****************************/
	/* Paint a PasswordAttribute */
	/*****************************/

	/***********************************/
	/* Paint a RandomPasswordAttribute */
	/***********************************/

	/******************************/
	/* Paint a SelectionAttribute */
	/******************************/

	/***************************/
	/* Paint a ShadowAttribute */
	/***************************/

}

?>
