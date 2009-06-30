<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/EntryWriter1.php,v 1.3.2.6 2008/01/04 14:31:05 wurley Exp $

define('IdEntryRefreshMenuItem', '0');
define('IdEntryExportBaseMenuItem', '1');
define('IdEntryMoveMenuItem', '2');
define('IdEntryInternalAttributesMenuItem', '3');
define('IdEntryDeleteMenuItem', '4');
define('IdEntryRenameMenuItem', '5');
define('IdEntryDeleteAttributeMessage', '6');
define('IdEntryCompareMenuItem', '7');
define('IdEntryCreateMenuItem', '8');
define('IdEntryAddAttributeMenuItem', '9');
define('IdEntryShowChildrenMenuItem', '10');
define('IdEntryExportSubMenuItem', '11');
define('IdEntryViewSchemaMessage', '12');
define('IdEntryReadOnlyMessage', '13');
define('IdEntryModifiedAttributesMessage', '14');

define('IdAttributeAddValueMenuItem', '0');
define('IdAttributeModifyMemberMenuItem', '1');
define('IdAttributeRenameMenuItem', '2');

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 *
 * Visit an entry and its attributes to draw them
 */
class EntryWriter1 extends EntryWriter {
	protected $url_base;

	protected $hint_layout;
	protected $action_layout;

	protected $step;
	protected $laststep;

	/**************************/
	/* Paint an Entry         */
	/**************************/

	protected function drawEntryHeader($entry) {
		// title
		$this->draw('Title', $entry);
		$this->draw('Subtitle', $entry);
		echo "\n";

		// menu
		$this->draw('Menu', $entry);
	}

	protected function drawEntryTitle($entry) {}
	protected function drawEntrySubtitle($entry) {}
	protected function drawEntryMenu($entry) {}

	protected function drawEntryJavascript($entry) {
		if (isset($_SESSION[APPCONFIG])) {
			echo '<script type="text/javascript" language="javascript">';
			echo 'var defaults = new Array();var default_date_format = "';
			echo $_SESSION[APPCONFIG]->GetValue('appearance', 'date');
			echo '";</script>';
		}

		echo '<script type="text/javascript" language="javascript">
		      function pla_getComponentById(id) {
		          return document.getElementById(id);
		      }

		      function pla_getComponentsByName(name) {
		         return document.getElementsByName(name);
		      }

		      function pla_getComponentValue(component) {
		          if (component.type == "checkbox") {
		              if (component.checked) return component.value;
		          } else if (component.type == "select-one") {
		              if (component.selectedIndex >= 0) return component.options[component.selectedIndex].value;
		          } else if (component.type == "select-multiple") {
		              if (component.selectedIndex >= 0) return component.options[component.selectedIndex].value;
		          } else if (component.type == undefined) { // option
		              if (component.selected) return component.value;
		          } else {
		              return component.value;
		          }
		          return "";
		      }

		      function pla_setComponentValue(component, value) {
		          if (component.type == "checkbox") {
		              if (component.value == value) component.checked = true;
		              else component.checked = false;
		          } else if (component.type == "select-one") {
		              for (var i = 0; i < component.options.length; i++) {
		                  if (component.options[i].value == value) component.options[i].selected = true;
		              }
		          } else if (component.type == "select-multiple") {
		              for (var i = 0; i < component.options.length; i++) {
		                  if (component.options[i].value == value) component.options[i].selected = true;
		              }
		          } else if (component.type == undefined) { // option
		              if (component.value == value) component.selected = true;
		              else component.selected = false;
		          } else { // text, textarea
		              component.value = value;
		          }
		      }</script>';

		echo '<script type="text/javascript" language="javascript">
		      function getAttributeComponents(prefix, name) {
		          var components = new Array();
			  var i = 0;
		          var j = 0;
		          var c = pla_getComponentsByName(prefix + "_values[" + name + "][" + j + "]");
		          while (c && (c.length > 0)) {
		              for (var k = 0; k < c.length; k++) {
				  components[i++] = c[k];
		              }
		              ++j;
		              c = pla_getComponentsByName(prefix + "_values[" + name + "][" + j + "]");
		          }
			  c = pla_getComponentsByName(prefix + "_values[" + name + "][]");
		          if (c && (c.length > 0)) {
		              for (var k = 0; k < c.length; k++) {
				  components[i++] = c[k];
		              }
		          }
			  return components;
	              }
		      function getAttributeValues(prefix, name) {
			  var components = getAttributeComponents(prefix, name);
		          var values = new Array();
		          for (var k = 0; k < components.length; k++) {
		              var val = pla_getComponentValue(components[k]);
		              if (val) values[values.length] = val;
		          }
			  return values;
		      }</script>';

		echo '<script type="text/javascript" language="javascript">
		      function validateForm(silence) {
			  var i = 0;
			  var valid = true;
			  var components = null;';
		foreach ($entry->getAttributes() as $attribute) {
			if ($attribute->isVisible()) {
				echo 'components = getAttributeComponents("new", "'.$attribute->getName().'");
			              for (i = 0; i < components.length; i++) {
					  if (window.validate_'.$attribute->getName().') {
				              valid = (!validate_'.$attribute->getName().'(components[i], silence) || !valid) ? false : true;
				          }
				      }';
			}
		}
		echo '    return valid;
		      }
		      </script>';

		echo '<script type="text/javascript" language="javascript">
		      function submitForm(form) {
		          for (var i = 0; i < form.elements.length; i++) {
		              form.elements[i].blur();
		          }
			  return validateForm(true);
		      }
		      function alertError(err, silence) {
		          if (!silence) alert(err);
		      }
		      </script>';

		for ($i = 0; $i < count($this->shown_attributes); $i++) {
			$this->draw('Javascript', $this->shown_attributes[$i]);
		}

		echo '<script type="text/javascript" language="javascript">
		      validateForm(true);
		      </script>';
	}

	/********************************/
	/* Paint a DefaultCreatingEntry */
	/********************************/

	public function visitDefaultCreatingEntryStart($entry) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$entry->getDn());

		// init
		$this->visit('Entry::Start', $entry);

		// check
		$container = $entry->getContainer();
		$container_ok = true;
		$objectclasses_ok = true;

		if ($this->step != 1) {
			if (!$container || !$this->ldapserver->dnExists($container)) {
				$container_ok = false;
				$this->step = 1;
			}
			if (!$entry->getAttributes()) {
				$objectclasses_ok = false;
				$this->step = 1;
			}
		}

		// header
		$this->draw('Header', $entry);

		// errors
		if (!$container_ok) {
			pla_error(sprintf(_('The container you specified (%s) does not exist.'),htmlspecialchars($container)), null, -1, false);
			echo '<br />';
		}

		if (!$objectclasses_ok) {
			pla_error(_('You did not select any objectClasses for this object.'), null, -1, false);
			echo '<br />';
		}
	}

	public function visitDefaultCreatingEntryEnd($entry) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$entry->getDn());

		$this->draw('StepTitle', $entry, $this->step);
		$this->draw('StepFormStart', $entry, $this->step);
		$this->draw('StepForm', $entry, $this->step);
		$this->draw('StepFormEnd', $entry, $this->step);
	}

	protected function initDefaultCreatingEntryVisit($entry) {
		parent::initDefaultCreatingEntryVisit($entry);

		$this->step = 1;
		$step = get_request('step','REQUEST');
		if ($step) $this->step = $step;
	}

	protected function drawDefaultCreatingEntryTitle($entry) {
		printf('<h3 class="title">%s</h3>',_('Create Object'));
	}

	protected function drawDefaultCreatingEntrySubtitle($entry) {
		printf('<h3 class="subtitle">%s%s <b>%s</b></h3>',
			_('Server'),_(':'),$this->ldapserver->name);
	}

	protected function drawDefaultCreatingEntryStepTitle($entry, $step) {
		if ($step == 1) {
			echo '<center><h4>';
			printf(_('Step %s of %s'), '1', '2');
			echo _(':');
			echo ' ';
			echo _('Container and ObjectClass(es)');
			echo '</h4></center>';
		} else {
			echo '<center><h4>';
			printf(_('Step %s of %s'), '2', '2');
			echo _(':');
			echo ' ';
			echo _('Specify attributes and values');
			echo '</h4></center>';
		}
	}

	public function drawDefaultCreatingEntryStepFormStart($entry, $step) {
		if ($step == 1) {
			echo '<form action="cmd.php?cmd=template_engine" method="post" enctype="multipart/form-data" name="entry_form" onSubmit="return submitForm(this)">';
		} else {
			echo '<form action="cmd.php?cmd=create" method="post" enctype="multipart/form-data" name="entry_form" onSubmit="return submitForm(this)">';
		}
	}

	public function drawDefaultCreatingEntryStepForm($entry, $step) {
		$container = $entry->getContainer();

		if ($step == 1) {
			printf('<input type="hidden" name="server_id" value="%s" />', $this->ldapserver->server_id);
			printf('<input type="hidden" name="step" value="%s" />', $step + 1);
			echo '<table class="create" align="center">';

			$this->draw('ContainerChooser', $entry, $container);
			$this->draw('ObjectClassChooser', $entry);
			$this->draw('StepFormSubmitButton', $entry, $step);

			echo '</table>';
		} else {
			printf('<input type="hidden" name="container" value="%s" />', htmlspecialchars($container));
			printf('<input type="hidden" name="server_id" value="%s" />', $this->ldapserver->server_id);
			printf('<input type="hidden" name="step" value="%s" />', $step + 1);
			echo '<table class="edit_dn" cellspacing="0" align="center">';

			$this->draw('RdnChooser', $entry);
			$this->draw('ShownAttributes', $entry);
			$this->draw('StepFormSubmitButton', $entry, $step);

			echo '</table>';

			$this->draw('HiddenAttributes', $entry);
		}
	}

	public function drawDefaultCreatingEntryStepFormSubmitButton($entry, $step) {
		if ($step == 1) {
			echo '<tr><td>&nbsp;</td><td>';
			printf('<input type="submit" id="create_button" value="%s" />', _('Proceed &gt;&gt;'));
			echo '</td></tr>';

		} else {
			echo '<tr><td><center>';
			printf('<input type="submit" id="create_button" name="submit" value="%s" />', _('Create Object'));
			echo '</center></td></tr>';
		}
	}

	public function drawDefaultCreatingEntryStepFormEnd($entry, $step) {
		echo '</form>';

		// javascript
		$this->draw('Javascript', $entry);
	}

	protected function drawDefaultCreatingEntryContainerChooser($entry, $default_container) {
		echo '<tr>';
		printf('<td class="heading">%s</td>', _('Container'));
		printf('<td><input type="text" name="container" size="40" value="%s" />', htmlspecialchars($default_container));
		draw_chooser_link('entry_form.container');
		echo '</td>';
		echo '</tr>';
	}

	protected function drawDefaultCreatingEntryObjectClassChooser($entry) {
		$oclasses = $this->ldapserver->SchemaObjectClasses();
		if (!$oclasses) $oclasses = array();
		elseif (!is_array($oclasses)) $oclasses = array($oclasses);

		echo '<tr>';
		printf('<td class="heading">%s</td>', _('ObjectClasses'));
		echo '<td><select name="new_values[objectClass][]" multiple="true" size="15">';

		foreach ($oclasses as $name => $oclass) {
			if (0 == strcasecmp('top', $name)) continue;

			printf('<option %s value="%s">%s</option>',
				($oclass->getType() == 'structural') ? 'style="font-weight: bold" ' : '',
				htmlspecialchars($oclass->getName()), htmlspecialchars($oclass->getName()));
		}

		echo '</select>';
		echo '</td>';
		echo '</tr>';

		if ($_SESSION[APPCONFIG]->GetValue('appearance', 'show_hints')) {
			echo '<tr><td>&nbsp;</td><td><small><img src="images/light.png" alt="Hint" /><span class="hint">';
			echo _('Hint: You must choose exactly one structural objectClass (shown in bold above)');
			echo '</span></small><br /></td></tr>';
		}
	}

	protected function drawDefaultCreatingEntryRdnChooser($entry) {
		$attrs = $entry->getAttributes();
		$rdn_attr = $entry->getRdnAttribute();

		printf('<tr><th colspan="2">%s</th></tr>', 'RDN');
		echo '<tr><td class="val" colspan="2"><select name="rdn_attribute">';
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
					printf('<tr><th colspan="2">%s</th></tr>', _('Required Attributes'));
					$has_required_attrs = true;
				}

			} else {
				if (!$has_optional_attrs) {
					if (!$has_required_attrs) {
						printf('<tr><th colspan="2">%s</th></tr>', _('Required Attributes'));
						printf('<tr class="row1"><td colspan="2"><center>(%s)</center></td></tr>', _('none'));
					}
					printf('<tr><th colspan="2">%s</th></tr>', _('Optional Attributes'));
					$has_optional_attrs = true;
				}
			}

			$this->draw('', $attr);
			echo "\n";
		}

		if (!$has_optional_attrs) {
			printf('<tr><th colspan="2">%s</th></tr>', _('Optional Attributes'));
			printf('<tr class="row1"><td colspan="2"><center>(%s)</center></td></tr>', _('none'));
		}
	}

	protected function drawDefaultCreatingEntryHiddenAttributes($entry) {
		foreach ($this->hidden_attributes as $attr) {
			$this->draw('', $attr);
			echo "\n";
		}
	}

	protected function drawDefaultCreatingEntryJavascript($entry) {
		$this->draw('Entry::Javascript', $entry);
	}

	/*******************************/
	/* Paint a DefaultEditingEntry */
	/*******************************/

	public function visitDefaultEditingEntryStart($entry) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$entry->getDn());

		// init
		$this->visit('Entry::Start', $entry);

		// header
		$this->draw('Header', $entry);

		// form start
		if (! $entry->isReadOnly()) {
			echo '<form action="cmd.php?cmd=update_confirm" method="post" enctype="multipart/form-data" name="entry_form" onSubmit="return submitForm(this)">';
			printf('<input type="hidden" name="server_id" value="%s" />',$this->ldapserver->server_id);
			printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($entry->getDn()));
		}

		echo '<br />'."\n\n";
		echo '<table class="edit_dn" align="center">';
	}

	public function visitDefaultEditingEntryEnd($entry) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$entry->getDn());

		// draw internal attributes
		if (get_request('show_internal_attrs','REQUEST')) {
			$this->draw('InternalAttributes', $entry);
			echo "\n\n";
		}

		// draw visible attributes
		$this->draw('ShownAttributes', $entry);

		// form end
		if (! $entry->isReadOnly()) {
			$this->draw('FormSubmitButton', $entry);
			echo '</table>';

			// draw hidden attributes
			$this->draw('HiddenAttributes', $entry);

			printf('</form>');

		} else {
			printf('</table>');
		}

		// javascript
		$this->draw('Javascript', $entry);
	}

	protected function initDefaultEditingEntryVisit($entry) {
		parent::initDefaultEditingEntryVisit($entry);

		$this->url_base = sprintf('cmd.php?server_id=%s&dn=%s', $this->ldapserver->server_id, rawurlencode($entry->getDn()));
		$this->hint_layout = '<td class="icon"><img src="images/light.png" alt="'._('Hint').'" /></td><td colspan="3"><span class="hint">%s</span></td>';
		$this->action_layout = '<td class="icon"><img src="images/%s" alt="%s" /></td><td><a href="%s" title="%s">%s</a></td>';
	}

	protected function drawDefaultEditingEntryTitle($entry) {
		$dn = $entry->getDn();
		$rdn = get_rdn($dn);

		printf('<h3 class="title">%s</h3>',htmlspecialchars($rdn));
	}

	protected function drawDefaultEditingEntrySubtitle($entry) {
		echo '<h3 class="subtitle">';
		echo _('Server');
		echo _(':');
		echo ' <b>';
		echo $this->ldapserver->name;
		echo '</b> &nbsp;&nbsp;&nbsp; ';

		echo _('Distinguished Name');
		echo _(':');
		echo ' <b>';
		echo htmlspecialchars($entry->getDn());
		echo '</b>';
		echo '<br />';

		echo _('Template');
		echo _(':');
		echo ' <b>';
		echo htmlspecialchars($entry->getTemplateTitle());
		echo '</b>';
		if ($entry->getTemplateName()) {
			echo ' (<b>';
			echo htmlspecialchars($entry->getTemplateName());
			echo '</b>)';
		}
		echo '</h3>';
	}

	protected function drawDefaultEditingEntryMenu($entry) {
		$i = 0;
		$item = '';

		echo '<table class="edit_dn_menu" width="100%" border=0>';
		echo '<tr>';
		$menuitem_number = 0;

		while (($item = $this->get('MenuItem', $entry, $i)) !== false) {
			if ($item) {
				$endofrow = 0;
				$it = ''; // menu item
				$ms = ''; // item message

				if (is_array($item)) {
					if (count($item) > 0) {
						$it = $item[0];
						if (count($item) > 1) $ms = $item[1];
					}
				} else {
					$it = $item;
				}

				if ($it) {
					$menuitem_number++;
					echo $it;

					if ($ms) {
						if (($menuitem_number % 2) == 1) {
							$menuitem_number++;
							echo '<td>&nbsp;</td><td>&nbsp;</td>';
							$endofrow = 0;
						}
						if ($endofrow)
							print $ms;
						else
							echo "</tr><tr>$ms";
						echo "</tr><tr>";
						$endofrow = 1;

					} else {
						if ($menuitem_number > 1 && ($menuitem_number % 2) == 0) {
							echo '</tr><tr>';
							$endofrow = 1;
						}
					}

				} elseif ($ms) {
					if (($menuitem_number % 2) == 1) {
						$menuitem_number++;
						echo '<td>&nbsp;</td><td>&nbsp;</td>';
						$endofrow = 0;
					}

					if ($endofrow)
						print $ms;
					else
						echo "</tr><tr>$ms";
					echo "</tr><tr>";
					$endofrow = 1;
				}
			}
			$i++;
		}

		if (($menuitem_number % 2) == 1) echo '<td>&nbsp;</td><td>&nbsp;</td>';
		else echo '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
	}

	protected function getDefaultEditingEntryMenuItem($entry, $i) {
		$config = $_SESSION[APPCONFIG];

		switch ($i) {
			case IdEntryRefreshMenuItem :
				if ($config->isCommandAvailable('entry_refresh'))
					return $this->get('RefreshMenuItem', $entry);
				else return '';

			case IdEntryExportBaseMenuItem :
				if ($config->isCommandAvailable('export'))
					return $this->get('ExportBaseMenuItem', $entry);
				else return '';

			case IdEntryMoveMenuItem :
				if (!$entry->isReadOnly() && $config->isCommandAvailable('entry_move'))
					return $this->get('MoveMenuItem', $entry);
				else return '';

			case IdEntryInternalAttributesMenuItem :
				if ($config->isCommandAvailable('entry_internal_attributes_show'))
					return $this->get('InternalAttributesMenuItem', $entry);
				else return '';

			case IdEntryDeleteMenuItem :
				if (!$entry->isReadOnly() && $config->isCommandAvailable('entry_delete', 'simple_delete'))
					return $this->get('DeleteMenuItem', $entry);
				else return '';

			case IdEntryRenameMenuItem :
				if (!$entry->isReadOnly() && $config->isCommandAvailable('entry_rename')) {
					$rdnAttr = $entry->getAttribute($entry->getRdnAttributeName());
					if ($rdnAttr && $rdnAttr->isVisible() && !$rdnAttr->isReadOnly()) {
						return $this->get('RenameMenuItem', $entry);
					} else {
						return '';
					}
				} else return '';

			case IdEntryDeleteAttributeMessage :
				if ($config->GetValue('appearance', 'show_hints')
					&& $config->isCommandAvailable('attribute_delete'))
					return array('', $this->get('DeleteAttributeMessage', $entry));
				else return '';

			case IdEntryCompareMenuItem :
				if (!$entry->isReadOnly() && $config->isCommandAvailable('entry_compare'))
					return $this->get('CompareMenuItem', $entry);
				else return '';

			case IdEntryCreateMenuItem :
				if (!$entry->isReadOnly() && !$entry->isLeaf()
					&& $config->isCommandAvailable('entry_create'))
					return $this->get('CreateMenuItem', $entry);
				else return '';

			case IdEntryAddAttributeMenuItem :
				if (!$entry->isReadOnly() && $config->isCommandAvailable('attribute_add'))
					return $this->get('AddAttributeMenuItem', $entry);
				else return '';

			case IdEntryShowChildrenMenuItem :
			case IdEntryExportSubMenuItem :
				static $children_count = false;
				static $more_children = false;
				if ($children_count === false) {
					// visible children in the tree
					$children_count = $entry->getChildrenNumber();
					// is there filtered children ?
					$more_children = $entry->isSizeLimited();
					if (!$more_children) {
						// all children in ldap
						$all_children = $this->ldapserver->getContainerContents(
								 $entry->getDn(), $children_count + 1,
								 '(objectClass=*)', $config->GetValue('deref','view'));
						$more_children = (count($all_children) > $children_count);
					}
				}

				if ($children_count > 0 || $more_children) {
					if ($children_count <= 0) $children_count = '';
					if ($more_children) $children_count .= '+';

					if ($i == IdEntryShowChildrenMenuItem) {
						return $this->get('ShowChildrenMenuItem', $entry, $children_count);
					} elseif ($i == IdEntryExportSubMenuItem && $config->isCommandAvailable('export')) {
						return $this->get('ExportSubMenuItem', $entry);
					} else {
						return '';
					}
				} else {
					return '';
				}

			case IdEntryViewSchemaMessage :
				if ($config->GetValue('appearance', 'show_hints') && $config->isCommandAvailable('schema'))
					return array('', $this->get('ViewSchemaMessage', $entry));
				else return '';

			case IdEntryReadOnlyMessage :
				if ($entry->isReadOnly())
					return array('', $this->get('ReadOnlyMessage', $entry));
				else return '';

			case IdEntryModifiedAttributesMessage :
				$modified_attrs = array();
				foreach ($entry->getAttributes() as $attr) {
					if ($attr->hasBeenModified())
						$modified_attrs[] = $attr->getFriendlyName();
				}
				if ($modified_attrs) {
					return array('', $this->get('ModifiedAttributesMessage', $entry, $modified_attrs));
				} else return '';

			default :
				return false;
		}
	}

	protected function getDefaultEditingEntryRefreshMenuItem($entry) {
		$href = $this->url_base.'&cmd=template_engine&junk='.random_junk();

		return sprintf($this->action_layout,'refresh.png',_('Refresh'),
			htmlspecialchars($href),_('Refresh this entry'),_('Refresh'));
	}

	protected function getDefaultEditingEntryExportBaseMenuItem($entry) {
		$href = $this->url_base.'&cmd=export_form&scope=base';

		return sprintf($this->action_layout,'save.png',_('Save'),
			htmlspecialchars($href),_('Save a dump of this object'),_('Export'));
	}

	protected function getDefaultEditingEntryMoveMenuItem($entry) {
		$href = $this->url_base.'&cmd=copy_form';

		return sprintf($this->action_layout,'cut.png',_('Cut'),htmlspecialchars($href),
			_('Copy this object to another location,a new DN, or another server'),
			_('Copy or move this entry'));
	}

	protected function getDefaultEditingEntryInternalAttributesMenuItem($entry) {
		if (get_request('show_internal_attrs','REQUEST')) {
			$href = $this->url_base.'&cmd=template_engine&junk='.random_junk();

			return sprintf($this->action_layout,'tools-no.png',_('Hide'),
				htmlspecialchars($href),'',_('Hide internal attributes'));

		} else {
			$href = $this->url_base.'&cmd=template_engine&show_internal_attrs=true';

			return sprintf($this->action_layout,'tools.png',_('Show'),
				htmlspecialchars($href),'',_('Show internal attributes'));
		}
	}

	protected function getDefaultEditingEntryDeleteMenuItem($entry) {
		$href = $this->url_base.'&cmd=delete_form';

		return sprintf($this->action_layout,'trash.png',_('Trash'),htmlspecialchars($href),
			_('You will be prompted to confirm this decision'),_('Delete this entry'));
	}

	protected function getDefaultEditingEntryRenameMenuItem($entry) {
		$href = $this->url_base.'&cmd=rename_form';

		return sprintf($this->action_layout,'rename.png',_('Rename'),htmlspecialchars($href),'',_('Rename'));
	}

	protected function getDefaultEditingEntryCompareMenuItem($entry) {
		$href = $this->url_base.'&cmd=compare_form';

		return sprintf($this->action_layout,'compare.png',_('Compare'),
			htmlspecialchars($href),'',_('Compare with another entry'));
	}

	protected function getDefaultEditingEntryCreateMenuItem($entry) {
		$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&container=%s',
			$this->ldapserver->server_id,rawurlencode($entry->getDn()));

		return sprintf($this->action_layout,'star.png',_('Create'),htmlspecialchars($href),'',_('Create a child entry'));
	}

	protected function getDefaultEditingEntryAddAttributeMenuItem($entry) {
		$href = $this->url_base.'&cmd=add_attr_form';

		return sprintf($this->action_layout,'add.png',_('Add'),htmlspecialchars($href),'',_('Add new attribute'));
	}

	protected function getDefaultEditingEntryShowChildrenMenuItem($entry,$children_count) {
		$href = sprintf('cmd.php?cmd=search&server_id=%s&search=true&filter=%s&base_dn=%s&form=advanced&scope=one',
			$this->ldapserver->server_id,rawurlencode('objectClass=*'),rawurlencode($entry->getDn()));

		return sprintf($this->action_layout,'children.png',_('Children'),htmlspecialchars($href),'',
			($children_count == 1) ? _('View 1 child') : sprintf(_('View %s children'),$children_count));
	}

	protected function getDefaultEditingEntryExportSubMenuItem($entry) {
		$href = sprintf('%s&cmd=export_form&scope=%s',$this->url_base,'sub');

		return sprintf($this->action_layout,'save.png',_('Save'),htmlspecialchars($href),
			_('Save a dump of this object and all of its children'),_('Export subtree'));
	}

	protected function getDefaultEditingEntryDeleteAttributeMessage($entry) {
		if ($_SESSION[APPCONFIG]->isCommandAvailable('attribute_delete_value'))
			return sprintf($this->hint_layout,_('Hint: To delete an attribute, empty the text field and click save.'));
		else
			return '';
	}

	protected function getDefaultEditingEntryViewSchemaMessage($entry) {
		return sprintf($this->hint_layout,_('Hint: To view the schema for an attribute, click the attribute name.'));
	}

	protected function getDefaultEditingEntryReadOnlyMessage($entry) {
		return sprintf($this->hint_layout,_('Viewing entry in read-only mode.'));
	}

	protected function getDefaultEditingEntryModifiedAttributesMessage($entry,&$modified_attributes) {
		return sprintf($this->hint_layout,
			(count($modified_attributes) == 1)
			? sprintf(_('An attribute (%s) was modified and is highlighted below.'),implode('',$modified_attributes))
			: sprintf(_('Some attributes (%s) were modified and are highlighted below.'),implode(', ',$modified_attributes)));
	}

	protected function drawDefaultEditingEntryInternalAttributes($entry) {
		$counter = 0;

		foreach ($this->internal_attributes as $attr) {
			$this->draw('',$attr);
			$counter++;
			echo "\n";
		}

		if ($counter == 0) {
			echo '<tr><td colspan="2">(';
			echo _('No internal attributes');
			echo ')</td></tr>';
		}
	}

	protected function drawDefaultEditingEntryShownAttributes($entry) {
		foreach ($this->shown_attributes as $attr) {
			$this->draw('',$attr);
			echo "\n";
		}
	}

	protected function drawDefaultEditingEntryHiddenAttributes($entry) {
		foreach ($this->hidden_attributes as $attr) {
			$this->draw('',$attr);
			echo "\n";
		}
	}

	protected function drawDefaultEditingEntryFormSubmitButton($entry) {
		echo '<tr><td colspan="2"><center><input type="submit" value="';
		echo _('Save Changes');
		echo '" id="save_button" /></center></td></tr>';
	}

	protected function drawDefaultEditingEntryJavascript($entry) {
		$this->draw('Entry::Javascript', $entry);
	}

	/*********************************/
	/* Paint a TemplateCreatingEntry */
	/*********************************/

	public function visitTemplateCreatingEntryStart($entry) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$entry->getDn());

		$template = get_request('template','REQUEST');
		$step = get_request('step','REQUEST');

		if ($template) {
			if ($template == 'none') {
				$this->visit('DefaultCreatingEntry::Start', $entry);
				return;
			} else {
				$entry->setSelectedTemplateName($template);
			}

		} elseif ($step && $step > 0) {
			$this->visit('DefaultCreatingEntry::Start', $entry);
			return;
		}

		if ($entry->getSelectedTemplateName()) {
			# if we already choose an creation template, use it to display the entry
			$this->visit('DefaultCreatingEntry::Start', $entry);

		} elseif ($entry->getTemplatesCount() <= 0) {
			# if no template is available for this entry, draws it
			# to the parent manner
			$this->visit('DefaultCreatingEntry::Start', $entry);

		} elseif (($entry->getTemplatesCount() == 1) && !$entry->hasDefaultTemplate()) {
			$templates = &$entry->getTemplates();
			$template_names = array_keys($templates);
			$entry->setSelectedTemplateName($template_names[0]);
			$this->visit('DefaultCreatingEntry::Start', $entry);

		} else {
			$this->visit_attributes = false;

			# propose the template choice
			$this->draw('TemplateChoice', $entry);
		}
	}

	public function visitTemplateCreatingEntryEnd($entry) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$entry->getDn());

		if ($this->visit_attributes) {
			foreach ($this->shown_attributes as $attr) {
				$page = 1;
				if ($attr->hasProperty('page')) {
					$page = $attr->getProperty('page');
				} else {
					$attr->setProperty('page', 1);
				}
				if ($page && $page > $this->laststep) {
					$this->laststep = $page;
				}
			}
			$this->visit('DefaultCreatingEntry::End', $entry);
			$entry->setSelectedTemplateName('');
		}
	}

	protected function initTemplateCreatingEntryVisit($entry) {
		$this->init('DefaultCreatingEntry::Visit', $entry);
		$this->laststep = 1;
	}

	protected function drawTemplateCreatingEntryRdnChooser($entry) {
		$rdn_attr = $entry->getRdnAttribute();

		if (!$rdn_attr) {
			$this->draw('DefaultCreatingEntry::RdnChooser', $entry);
		} else {
			printf('<input type="hidden" name="rdn_attribute" value="%s" />', htmlspecialchars($rdn_attr->getName()));
		}
	}

	protected function drawTemplateCreatingEntryStepTitle($entry, $step) {
		if (!$entry->getSelectedTemplateName()) {
			$this->draw('DefaultCreatingEntry::StepTitle', $entry, $step);

		} else if ($entry->hasProperty('description')) {
			echo '<center><h4>';
			echo $entry->getProperty('description');
			echo ' ';
			echo _('(');
			printf(_('step %s of %s'), $step, $this->laststep);
			echo _(')');
			echo '</h4></center>';
		}
	}

	public function drawTemplateCreatingEntryStepFormStart($entry, $step) {
		if ($entry->getSelectedTemplateName()) {
			if ($step < $this->laststep) {
				echo '<form action="cmd.php?cmd=template_engine" method="post" enctype="multipart/form-data" name="entry_form" onSubmit="return submitForm(this)">';

			} else {
				// Patch 1539633
				// default action is create.php
				// you can change this behavior by setting <action>myscript.php</action> in template header
				echo '<form action="cmd.php" method="post" enctype="multipart/form-data" name="entry_form" onSubmit="return submitForm(this)">';
				printf('<input type="hidden" name="cmd" value="%s" />',
					$entry->hasProperty('action') ? rawurlencode($entry->getProperty('action')) : 'create');
			}
		} else {
			$this->draw('DefaultCreatingEntry::StepFormStart', $entry, $step);
		}
	}

	public function drawTemplateCreatingEntryStepForm($entry, $step) {
		if (!$entry->getSelectedTemplateName()) {
			$this->draw('DefaultCreatingEntry::StepForm', $entry, $step);
			return;
		}

		$container = $entry->getContainer();

		printf('<input type="hidden" name="container" value="%s" />', htmlspecialchars($container));
		printf('<input type="hidden" name="server_id" value="%s" />', $this->ldapserver->server_id);
		printf('<input type="hidden" name="template" value="%s" />', $entry->getSelectedTemplateName());
		printf('<input type="hidden" name="step" value="%s" />', $step + 1);
		echo '<table class="edit_dn" cellspacing="0" align="center">';

		$this->draw('RdnChooser', $entry);

		// draw attributes
		$this->draw('ShownAttributes', $entry);

		$this->draw('StepFormSubmitButton', $entry, $step);

		echo '</table>';

		$this->draw('HiddenAttributes', $entry);
	}

	protected function drawTemplateCreatingEntryStepFormSubmitButton($entry, $step) {
		if ($step < $this->laststep) $this->draw('DefaultCreatingEntry::StepFormSubmitButton', $entry, 1);
		else $this->draw('DefaultCreatingEntry::StepFormSubmitButton', $entry, 2);
	}

	protected function drawTemplateCreatingEntryTemplateChoice($entry) {
		$this->draw('Title', $entry);
		$this->draw('Subtitle', $entry);
		echo "\n";

		printf('<center><h3>%s</h3></center>',_('Select a template for the creation process'));

		$href = sprintf('cmd.php?%s', array_to_query_string($_GET,array('meth'),false));
		echo '<form name="template_choice_form" action="'.htmlspecialchars($href).'" method="post">';

		foreach ($_POST as $p => $v) {
			echo "<input type=\"hidden\" name=\"$p\" value=\"$v\">";
		}

		echo '<table class="create" width="100%">';

		$server_menu_html = server_select_list($this->ldapserver->server_id, true);
		printf('<tr><td class="heading">%s%s</td><td>%s</td></tr>', _('Server'), _(':'), $server_menu_html);

		echo '<tr>';
		printf('<td class="heading">%s%s</td>', _('Templates'), _(':'));
		echo '<td>';
		echo '<table class="template_display" width="100%">';
		echo '<tr><td>';
		echo '<table class="templates">';

		$i = -1;
		$templates = &$entry->getTemplates();
		$nb_templates = count($templates);
		if ($entry->hasDefaultTemplate()) $nb_templates++;

		foreach ($templates as $template_name => $template_attrs) {
			$i++;

			# If the template doesnt have a title, we'll use the desc field.
			$template_attrs['desc'] = isset($template_attrs['title']) ? $template_attrs['title'] : $template_attrs['desc'];

			# Balance the columns properly
			if (($nb_templates % 2 == 0 && $i == intval($nb_templates / 2)) ||
				($nb_templates % 2 == 1 && $i == intval($nb_templates / 2) + 1)) {
				echo '</table></td><td><table class="templates">';
			}

			echo '<tr>';

			if (isset($template_attrs['invalid']) && $template_attrs['invalid']) {
				echo '<td class="icon"><img src="images/error.png" alt="Error" /></td>';
			} else {
				printf('<td><input type="radio" name="template" value="%s" id="%s" onclick="document.forms.template_choice_form.submit()" /></td>',
				htmlspecialchars($template_name), htmlspecialchars($template_name));
			}

			printf('<td class="icon"><label for="%s"><img src="%s" alt="" /></label></td>',
				htmlspecialchars($template_name), $template_attrs['icon']);
			printf('<td class="name"><label for="%s">',
				htmlspecialchars($template_name));

			if (isset($template_attrs['invalid']) && $template_attrs['invalid']) {
				printf('<span style="color: gray"><acronym title="%s">',_('This template is not allowed in this container.'));
			}

			echo htmlspecialchars($template_attrs['desc']);

			if (isset($template_attrs['invalid']) && $template_attrs['invalid']) {
				echo '</acronym></span>';
			}

			echo '</label></td></tr>';
		}

		# Default template
		if ($entry->hasDefaultTemplate()) {
			$i++;
			if (($nb_templates % 2 == 0 && $i == intval($nb_templates / 2)) ||
				($nb_templates % 2 == 1 && $i == intval($nb_templates / 2) + 1)) {
				echo '</table></td><td><table class="templates">';
			}
			echo '<tr>'
				.'<td><input type="radio" name="template" value="none"'
				.' onclick="document.forms.template_choice_form.submit()" /></td>'
				.'<td class="icon"><label><img src="images/object.png" alt="" /></label></td>'
				.'<td class="name"><label>'
				._('Default')
				.'</label></td></tr>';
		}

		echo '</table>';
		echo '</td></tr></table>';
		echo '</td></tr>';

		echo '</table>';
		echo '</form>';
	}

	protected function drawTemplateCreatingEntryShownAttributes($entry) {
		if (!$entry->getSelectedTemplateName()) {
			$this->draw('DefaultCreatingEntry::ShownAttributes', $entry);
			return;
		}

		foreach ($this->shown_attributes as $attr) {
			$page = $attr->getProperty('page');
			if ($page == $this->step) {
				$this->draw('', $attr);
				echo "\n";
			//} elseif ($page < $this->step) {
			} else {
				// the displayed attributes are the visible attributes in shown_attributes list
				$attr->hide();
				$this->hidden_attributes[] = $attr;
			}
		}
	}

	protected function drawTemplateCreatingEntryHiddenAttributes($entry) {
		if (!$entry->getSelectedTemplateName()) {
			$this->draw('DefaultCreatingEntry::HiddenAttributes', $entry);
			return;
		}

		foreach ($this->hidden_attributes as $attr) {
			//$page = $attr->hasProperty('page') ? $attr->getProperty('page') : -1;
			//if ($page <= $this->step) {
				$this->draw('', $attr);
				echo "\n";
			//}
		}
	}

	protected function drawTemplateCreatingEntryJavascript($entry) {
		$this->draw('DefaultCreatingEntry::Javascript', $entry);

		$templates = new Templates($this->ldapserver->server_id);
		foreach ($entry->getAttributes() as $attribute) {
			if ($attribute->hasProperty('onchange')) {
				$onchange = $attribute->getProperty('onchange');
				if (is_array($onchange)) {
					foreach ($onchange as $value)
						$templates->OnChangeAdd($this->ldapserver,$attribute->getName(),$value);
				} else {
					$templates->OnChangeAdd($this->ldapserver,$attribute->getName(),$onchange);
				}
			}
		}
		$hash = $templates->getJsHash();

		echo '<script type="text/javascript" language="javascript">
			var attrTrace;
			function fill(id, value) {
				attrTrace = new Array();
				fillRec(id, value);
			}
			function fillRec(id, value) {
				if (attrTrace[id] == 1) {
					return;
				} else {
					var pre = "";
					var suf = "";
					var i;
					attrTrace[id] = 1;

					pla_setComponentValue(pla_getComponentById(id), value);

					// here comes template-specific implementation, generated by php
					if (false) {}';
		foreach ($entry->getAttributes() as $attribute) {
			$attr = $attribute->getName();
			echo "\n\t\t\t\t\telse if ((i = id.indexOf('_".$attr."_')) >= 0) {\n";
			echo "\t\t\t\t\t\tpre = id.substring(0, i+1);\n";
			echo "\t\t\t\t\t\tsuf = id.substring(i + 1 + '$attr'.length, id.length);\n";
			$this->draw('FillJavascript', $attribute, 'id', 'value');
			if (isset($hash['autoFill'.$attr])) {
				echo $hash['autoFill'.$attr];
			}
			echo "\t\t\t}\n";
		}
		echo '}}</script>';
	}

	/********************************/
	/* Paint a TemplateEditingEntry */
	/********************************/

	public function visitTemplateEditingEntryStart($entry) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$entry->getDn());

		$template = get_request('template','REQUEST');

		if ($template) {
			if ($template == 'none') {
				$this->visit('DefaultEditingEntry::Start', $entry);
				return;
			} else {
				$entry->setSelectedTemplateName($template);
			}
		}

		if ($entry->getSelectedTemplateName()) {
			# if we already choose an editing template, use it to display the entry
			$this->visit('DefaultEditingEntry::Start', $entry);
		} elseif ($entry->getTemplatesCount() <= 0) {
			# if no template is available for this entry, draws it
			# to the parent manner
			$this->visit('DefaultEditingEntry::Start', $entry);
		} elseif (($entry->getTemplatesCount() == 1) && !$entry->hasDefaultTemplate()) {
			$templates = &$entry->getTemplates();
			$template_names = array_keys($templates);
			$entry->setSelectedTemplateName($template_names[0]);
			$this->visit('DefaultEditingEntry::Start', $entry);
		} else {
			$this->visit_attributes = false;

			# propose the template choice
			$this->draw('TemplateChoice', $entry);
		}
	}

	public function visitTemplateEditingEntryEnd($entry) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s)',1,__FILE__,__LINE__,__METHOD__,$entry->getDn());

		if ($this->visit_attributes) {
			$this->visit('DefaultEditingEntry::End', $entry);
			$entry->setSelectedTemplateName('');
		}
	}

	protected function drawTemplateEditingEntryTemplateChoice($entry) {
		$this->draw('Title', $entry);
		$this->draw('Subtitle', $entry);
		echo "\n";

		printf('<center><h3>%s</h3></center>',_('Select a template to edit the entry'));

		$href = sprintf('cmd.php?%s', array_to_query_string($_GET,array('meth'),false));
		echo '<form name="template_choice_form" action="'.htmlspecialchars($href).'" method="post">';

		foreach ($_POST as $p => $v) {
			echo "<input type=\"hidden\" name=\"$p\" value=\"$v\">";
		}

		echo '<table class="create" width="100%">';
		echo '<tr>';
		printf('<td class="heading">%s%s</td>',_('Templates'), _(':'));
		echo '<td>';
		echo '<table class="template_display" width="100%">';
		echo '<tr><td>';
		echo '<table class="templates">';

		$i = -1;
		$templates = &$entry->getTemplates();
		$nb_templates = count($templates);
		if ($entry->hasDefaultTemplate()) $nb_templates++;

		foreach ($templates as $template_name => $template_attrs) {
			$i++;

			# If the template doesnt have a title, we'll use the desc field.
			$template_attrs['desc'] = isset($template_attrs['title']) ? $template_attrs['title'] : $template_attrs['desc'];

			# Balance the columns properly
			if (($nb_templates % 2 == 0 && $i == intval($nb_templates / 2)) ||
				($nb_templates % 2 == 1 && $i == intval($nb_templates / 2) + 1)) {
				echo '</table></td><td><table class="templates">';
			}

			echo '<tr>';

			printf('<td><input type="radio" name="template" value="%s" id="%s"'
				.' onclick="document.forms.template_choice_form.submit()" /></td>',
				htmlspecialchars($template_name), htmlspecialchars($template_name));

			printf('<td class="icon"><label for="%s"><img src="%s" alt="" /></label></td>',
				htmlspecialchars($template_name), $template_attrs['icon']);
			printf('<td class="name"><label for="%s">',htmlspecialchars($template_name));

			echo htmlspecialchars($template_attrs['desc']);

			echo '</label></td></tr>';
		}

		# Default template
		if ($entry->hasDefaultTemplate()) {
			$i++;
			if (($nb_templates % 2 == 0 && $i == intval($nb_templates / 2)) ||
				($nb_templates % 2 == 1 && $i == intval($nb_templates / 2) + 1)) {
				echo '</table></td><td><table class="templates">';
			}
			echo '<tr>'
				.'<td><input type="radio" name="template" value="none"'
				.' onclick="document.forms.template_choice_form.submit()" /></td>'
				.'<td class="icon"><label><img src="images/object.png" alt="" /></label></td>'
				.'<td class="name"><label>'
				._('Default')
				.'</label></td></tr>';
		}

		echo '</table>';
		echo '</td></tr></table>';
		echo '</td></tr>';

		echo '</table>';
		echo '</form>';
	}

	protected function drawTemplateEditingEntryShownAttributes($entry) {
		foreach ($this->shown_attributes as $attr) {
			// @todo if this->page == attr->page
			$this->draw('', $attr);
			echo "\n";
		}
	}

	protected function drawTemplateEditingEntryHiddenAttributes($entry) {
		printf('<input type="hidden" name="template" value="%s" />', $entry->getSelectedTemplateName());
		$this->draw('DefaultEditingEntry::HiddenAttributes', $entry);
	}

	protected function drawTemplateEditingEntryJavascript($entry) {
		$this->draw('DefaultEditingEntry::Javascript', $entry);

		$templates = new Templates($this->ldapserver->server_id);
		foreach ($entry->getAttributes() as $attribute) {
			if ($attribute->hasProperty('onchange')) {
				$onchange = $attribute->getProperty('onchange');
				if (is_array($onchange)) {
					foreach ($onchange as $value)
						$templates->OnChangeAdd($this->ldapserver,$attribute->getName(),$value);
				} else {
					$templates->OnChangeAdd($this->ldapserver,$attribute->getName(),$onchange);
				}
			}
		}
		$hash = $templates->getJsHash();

		echo '<script type="text/javascript" language="javascript">
			var attrTrace;
			function fill(id, value) {
				attrTrace = new Array();
				fillRec(id, value);
			}
			function fillRec(id, value) {
				if (attrTrace[id] == 1) {
					return;
				} else {
					var pre = "";
					var suf = "";
					var i;
					attrTrace[id] = 1;
					pla_setComponentValue(pla_getComponentById(id), value);
					// here comes template-specific implementation, generated by php
					if (false) {}';
		foreach ($entry->getAttributes() as $attribute) {
			$attr = $attribute->getName();
			echo "\n\t\t\telse if ((i = id.indexOf('_".$attr."_')) >= 0) {\n";
			echo "\t\t\t\tpre = id.substring(0, i+1);\n";
			echo "\t\t\t\tsuf = id.substring(i + 1 + '$attr'.length, id.length);\n";
			$this->draw('FillJavascript', $attribute, 'id', 'value');
			if (isset($hash['autoFill'.$attr])) {
				echo $hash['autoFill'.$attr];
			}
			echo "\t\t\t}\n";
		}
		echo '}}</script>';
	}

	/**************************/
	/* Paint an Attribute     */
	/**************************/

	protected function drawAttribute($attribute) {
		if ($attribute->isVisible()) $this->draw('Informations', $attribute);
		$this->draw('Values', $attribute);
	}

	protected function drawAttributeJavascript($attribute) {
		echo '<script type="text/javascript" language="javascript">';
		echo 'function focus_'.$attribute->getName().'(component) {';
		$this->draw('FocusJavascript', $attribute, 'component');
		echo '}';
		echo 'function blur_'.$attribute->getName().'(component) {';
		$this->draw('BlurJavascript', $attribute, 'component');
		echo '}';
		echo 'function validate_'.$attribute->getName().'(component, silence) {';
		echo '    var valid = true;';
		$this->draw('ValidateJavascript', $attribute, 'component', 'silence', 'valid');
		echo '    if (valid) component.style.backgroundColor = "white";';
		echo '    else component.style.backgroundColor = \'#ffffba\';';
		echo '    return valid;';
		echo '}';
		echo '</script>';
	}

	protected function drawAttributeFocusJavascript($attribute, $component) {
		$entry = $attribute->getEntry();

		if ($entry)
			echo $this->get('AttributeFocusJavascript', $entry, $attribute, $component);
	}

	protected function drawAttributeBlurJavascript($attribute, $component) {
		$entry = $attribute->getEntry();

		if ($entry)
			echo $this->get('AttributeBlurJavascript', $entry, $attribute, $component);
	}

	protected function drawAttributeValidateJavascript($attribute, $component, $silence, $var_valid) {
		if ($attribute->isRequired()) {
			echo 'var vals = getAttributeValues("new", "'.$attribute->getName().'");
			      if (vals.length <= 0) {
			          '.$var_valid.' = false;
				  alertError("'._('This attribute is required')._(':').' '.$attribute->getFriendlyName().'", '.$silence.');
			      }';
			echo 'var comp = getAttributeComponents("new", "'.$attribute->getName().'");
			      for (var i = 0; i < comp.length; i++) {
				   comp[i].style.backgroundColor = '.$var_valid.' ? "white" : \'#ffffba\';
			      }';
		}
	}

	protected function getEntryAttributeFocusJavascript($entry, $attribute, $component) {
		return '';
	}

	protected function getEntryAttributeBlurJavascript($entry, $attribute, $component) {
		return '';
	}

	protected function getDefaultCreatingEntryAttributeBlurJavascript($entry, $attribute, $component) {
		return "\n\t\t\t\t\t\t".'validate_'.$attribute->getName().'('.$component.', false);'."\n";
	}

	protected function getTemplateCreatingEntryAttributeBlurJavascript($entry, $attribute, $component) {
		$j = 'fill('.$component.'.id, pla_getComponentValue('.$component.'));';
		//$j .= $this->get('DefaultCreatingEntry::AttributeBlurJavascript',$entry, $attribute, $component);
		return $j;
	}

	protected function getDefaultEditingEntryAttributeBlurJavascript($entry, $attribute, $component) {
		return "\n\t\t\t\t\t\t".'validate_'.$attribute->getName().'('.$component.', false);'."\n";
	}

	protected function getTemplateEditingEntryAttributeBlurJavascript($entry, $attribute, $component) {
		$j = 'fill('.$component.'.id, pla_getComponentValue('.$component.'));';
		//$j .= $this->get('DefaultEditingEntry::AttributeBlurJavascript',$entry, $attribute, $component);
		return $j;
	}

	protected function drawAttributeFillJavascript($attribute, $component_id, $component_value) {
		echo "\n\t\t\t\t\t\t".'validate_'.$attribute->getName().'(pla_getComponentById('.$component_id.'), false);'."\n";
	}

	protected function drawAttributeInformations($attribute) {
		if (($this->context == ENTRY_WRITER_EDITING_CONTEXT) && $attribute->hasBeenModified()) echo '<tr class="updated_attr">';
		else echo '<tr>';

		echo '<td class="attr">';
		$this->draw('Name', $attribute);
		echo '</td>';

		echo '<td class="attr_note">';

		# Setup the $attr_note, which will be displayed to the right of the attr name (if any)
		if ($_SESSION[APPCONFIG]->GetValue('appearance', 'show_attribute_notes')) {
			$this->draw('Notes', $attribute);
		}

		echo '</td>';
		echo '</tr>';
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

		if ($attribute->isReadOnly() && $this->ldapserver->isAttrReadOnly($attribute->getName())) {
			printf('<small>(<acronym title="%s">%s</acronym>)</small>',
				_('This attribute has been flagged as read only by the phpLDAPadmin administrator'),
				_('read only'));
		}
	}

	protected function drawAttributeValues($attribute) {
		if ($attribute->isVisible()) $this->draw('StartValueLine', $attribute);

		# draws values
		$value_count = $attribute->getValueCount();
		$i = 0;
		for (; $i < $value_count; $i++) {
			$this->draw('Value', $attribute, $i);
		}

		if ($this->context == ENTRY_WRITER_CREATION_CONTEXT) {
			$blankvalue_count = $attribute->getMaxValueCount();
			if ($blankvalue_count < 0) $blankvalue_count = 1;
			else $blankvalue_count -= $value_count;

			for ($j = 0; $j < $blankvalue_count; $j++) {
				$this->draw('BlankValue', $attribute, $i + $j);
			}
		}

		if ($attribute->isVisible()) {
			$this->draw('Menu', $attribute);
			$this->draw('EndValueLine', $attribute);
		}
	}

	protected function drawAttributeMenu($attribute) {
		$i = 0;
		$item = '';

		while (($item = $this->get('MenuItem', $attribute, $i)) !== false) {
			if ($item) {
				echo '<div class="add_value">'.$item.'</div>';
			}
			$i++;
		}
	}

	protected function getAttributeMenuItem($attribute, $i) {
		if ($this->context != ENTRY_WRITER_EDITING_CONTEXT)
			return false;

		switch ($i) {
			case IdAttributeAddValueMenuItem :
				if ($attribute->isVisible() && !$attribute->isReadOnly()
					&& !$attribute->isRdn() && $_SESSION[APPCONFIG]->isCommandAvailable('attribute_add_value')) {
					if ($attribute->getMaxValueCount() < 0 || $attribute->getValueCount() < $attribute->getMaxValueCount()) {
						return $this->get('AddValueMenuItem', $attribute);
					}
				}
				return '';

			case IdAttributeModifyMemberMenuItem :
				if (in_array($attribute->getName(), $_SESSION[APPCONFIG]->GetValue('modify_member','groupattr'))) {
					if ($attribute->isVisible() && !$attribute->isReadOnly() && !$attribute->isRdn()
						&& ($_SESSION[APPCONFIG]->isCommandAvailable('attribute_add_value')
						|| $_SESSION[APPCONFIG]->isCommandAvailable('attribute_delete_value'))) {
						return $this->get('ModifyMemberMenuItem', $attribute);
					}
				}
				return '';

			case IdAttributeRenameMenuItem :
				if ($attribute->isVisible() && $attribute->isRdn() && !$attribute->isReadOnly()
					&& $_SESSION[APPCONFIG]->isCommandAvailable('entry_rename')) {
					return $this->get('RenameMenuItem', $attribute);
				}
				return '';

			default :
				return false;
		}
	}

	protected function drawAttributeStartValueLine($attribute) {
		if (($this->context == ENTRY_WRITER_EDITING_CONTEXT) && $attribute->hasBeenModified()) {
			echo '<tr class="updated_attr">';
		} else {
			echo '<tr>';
		}
		echo '<td class="val" colspan="2">';
	}

	protected function drawAttributeEndValueLine($attribute) {
		echo '</td>';
		echo '</tr>';

		if (($this->context == ENTRY_WRITER_EDITING_CONTEXT) && $attribute->hasBeenModified()) {
			echo '<tr class="updated_attr"><td class="bottom" colspan="2"></td></tr>';
		}
	}

	protected function drawAttributeValue($attribute, $i) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s, %d)',1,__FILE__,__LINE__,__METHOD__,$attribute->getName(),$i);

		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';

		if ($attribute->isVisible()) {
			echo '<table cellspacing="0" cellpadding="0" width="100%"><tr><td>';
			$this->draw('Icon', $attribute, $val);
			echo '</td><td valign="top">';
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

	/**
	 * Save the current value to detect changes
	 */
	protected function drawAttributeOldValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		printf('<input type="hidden" name="old_values[%s][%s]" value="%s" />',
			htmlspecialchars($attribute->getName()), $i, htmlspecialchars($val));
	}

	/**
	 * Display the current value
	 */
	protected function drawAttributeNewValue($attribute, $i) {
		if (!$attribute->isVisible()) {
			$this->draw('HiddenValue', $attribute, $i);

		} elseif ($attribute->isReadOnly() || ($attribute->getEntry() && $attribute->getEntry()->getDn() && $attribute->isRdn())) {
			$this->draw('ReadOnlyValue', $attribute, $i);

		} else {
			$this->draw('ReadWriteValue', $attribute, $i);
		}	
	}

	protected function drawAttributeBlankValue($attribute, $i) {
		$this->draw('Value', $attribute, $i);
	}

	protected function drawAttributeHiddenValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		printf('<input type="hidden" class="val" name="new_values[%s][%s]" id="new_values_%s_%s" value="%s" />',
			htmlspecialchars($attribute->getName()), $i, htmlspecialchars($attribute->getName()), $i,
			htmlspecialchars($val));
	}

	protected function drawAttributeReadOnlyValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		printf('<input type="text" class="roval" name="new_values[%s][%s]" id="new_values_%s_%s" value="%s" %s readonly /><br />',
			htmlspecialchars($attribute->getName()), $i, htmlspecialchars($attribute->getName()),
			$i, htmlspecialchars($val), ($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '');
	}

	protected function drawAttributeReadWriteValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		if ($attribute->hasProperty('helper')) {
			echo '<table cellspacing="0" cellpadding="0"><tr><td valign="top">';
		}

		/* We smack an id="..." tag in here that doesn't have [][] in it to allow the
		 * draw_chooser_link() to identify it after the user clicks. */
		$id = sprintf('new_values_%s_%s', htmlspecialchars($attribute->getName()), $i);

		printf('<input type="text" class="val" name="new_values[%s][%s]"'.
			' id="%s" value="%s" onFocus="focus_%s(this);" onBlur="blur_%s(this);" %s %s/>',
			htmlspecialchars($attribute->getName()), $i, $id,
			htmlspecialchars($val), $attribute->getName(), $attribute->getName(),
			($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '',
			($attribute->getMaxLength() > 0) ? 'maxlength="'.$attribute->getMaxLength().'"' : '');

		if ($attribute->hasProperty('helper')) {
			echo '</td><td valign="top">';
			$this->draw('Helper', $attribute, $i);
			echo '</td></tr></table>';
		}
	}

	protected function drawAttributeHelper($attribute, $i) {
		$params = $attribute->getProperty('helper');
		if (!$params) $params = array();
		elseif (!is_array($params)) $params = array($params);

		$id = isset($params['id']) ? $params['id'] : '';
		if (is_array($id)) $id = (count($id) > 0 ? $id[0] : '');

		$display = isset($params['display']) ? $params['display'] : '';
		if (is_array($display)) $display = (count($display) > 0 ? $display[0] : '');

		$vals = isset($params['value']) ? $params['value'] : array();
		if (!is_array($vals)) $vals = array($vals);

		$opts = isset($params['option']) ? $params['option'] : array();
		if (!is_array($opts)) $opts = array($opts);

		$dn = '';
		if ($attribute->getEntry()) {
			if ($this->context == ENTRY_WRITER_CREATION_CONTEXT) {
				$dn = $attribute->getEntry()->getContainer();
			} else {
				$dn = $attribute->getEntry()->getDn();
			}
		}

		$arr1 = array();
		foreach ($vals as $id_parval => $parval) {
			$arr2 = Templates::EvaluateDefault($this->ldapserver, $parval, $dn, null, null);
			if (is_array($arr2)) $arr1 = array_merge($arr1,$arr2);
			else $arr1[$id_parval] = $arr2;
		}
		$vals = $arr1;

		$arr1 = array();
		foreach ($opts as $id_parval => $parval) {
			$arr2 = Templates::EvaluateDefault($this->ldapserver, $parval, $dn, null, null);
			if (is_array($arr2)) $arr1 = array_merge($arr1,$arr2);
			else $arr1[$id_parval] = $arr2;
		}
		$opts = $arr1;

		$default = (count($vals) > 0 ? $vals[0] : '');
		if (!is_scalar($default)) $default = '';
		if (!is_null($attribute->getValue($i)) && (strlen($default) <= 0)) {
			$default = $this->get('DefaultValueHelper', $attribute, $i);
		}

		if (count($opts) > 0) {
			$found = false;

			printf('<select id="%s_%s_%s" name="%s[%s][%s]">',
			       $id, htmlspecialchars($attribute->getName()), $i,
			       $id, htmlspecialchars($attribute->getName()), $i);

			foreach ($opts as $v) {
				printf('<option value="%s" %s>%s</option>', $v, ($v == $default) ? 'selected' : '', $v);
				if ($v == $default) $found = true;
			}
			if (!$found) {
				printf('<option value="%s" selected>%s</option>', $default, $default);
			}
			echo '</select>';
		} else {
			printf('<input type="text" name="%s[%s][%s]" id="%s_%s_%s" value="%s" size="4" />',
			       $id, htmlspecialchars($attribute->getName()), $i,
			       $id, htmlspecialchars($attribute->getName()), $i,
			       htmlspecialchars($default));
		}

		if ($display) {
			echo '<div class="helper">';
			echo '<span class="hint">'.$display.'</span>';
			echo '</div>';
		}
	}

	protected function getAttributeDefaultValueHelper($attribute, $i) {
		$params = $attribute->getProperty('helper');

		# Should only return 1 default entry.
		if (isset($params['value']) && ! is_array($params['value']))
			return $params['value'];

		# If there are multiple values, return the first one.
		else if (isset($params['value']) && is_array($params['value']))
			return array_shift($params['value']);

		# No default values, return a blank.
		else
			return '';
	}

	protected function getAttributeRenameMenuItem($attribute) {
		$encoded_dn = '';
		if ($attribute->getEntry()) $encoded_dn = rawurlencode($attribute->getEntry()->getDn());
		if (!$encoded_dn) return; // creating entry

		$url_base = sprintf('cmd.php?server_id=%s&dn=%s', $this->ldapserver->server_id, $encoded_dn);
		$href = sprintf('%s&cmd=rename_form', $url_base);

		return sprintf('<small>(<a href="%s">%s</a>)</small>', htmlspecialchars($href), _('rename'));
	}

	protected function getAttributeAddValueMenuItem($attribute) {
		/* Draw the "add value" link under the list of values for this attributes */
		$encoded_dn = '';
		$template = '';
		if ($attribute->getEntry()) {
			$encoded_dn = rawurlencode($attribute->getEntry()->getDn());
			if (method_exists($attribute->getEntry(), 'getSelectedTemplateName'))
				$template = $attribute->getEntry()->getSelectedTemplateName();
		}
		if (!$encoded_dn) return; // creating entry

		$href = sprintf('cmd.php?cmd=add_value_form&server_id=%s&dn=%s%s&attr=%s',
			$this->ldapserver->server_id, $encoded_dn, $template ? "&template=$template" : '', rawurlencode($attribute->getName()));

		return sprintf('(<a href="%s" title="%s">%s</a>)',
			htmlspecialchars($href), sprintf(_('Add an additional value to attribute \'%s\''),
			$attribute->getName()), _('add value'));
	}

	protected function getAttributeModifyMemberMenuItem($attribute) {
		$dn = ($attribute->getEntry()) ? $attribute->getEntry()->getDn() : '';
		$encoded_dn = ($dn) ? rawurlencode($dn) : '';
		if (!$encoded_dn) return; // creating entry

		$href = sprintf('cmd.php?cmd=modify_member_form&server_id=%s&dn=%s&attr=%s',
			$this->ldapserver->server_id, $encoded_dn, rawurlencode($attribute->getName()));

		return sprintf('(<a href="%s" title="%s">%s</a>)',
			htmlspecialchars($href), sprintf(_('Modify members for \'%s\''), $dn), _('modify group members'));
	}

	protected function drawAttributeIcon($attribute, $val) {
		if (is_dn_string($val) || $this->ldapserver->isDNAttr($attribute->getName())) {
			$this->draw('DnValueIcon', $attribute, $val);
		} elseif (is_mail_string($val)) {
			$this->draw('MailValueIcon', $attribute, $val);
		} elseif (is_url_string($val)) {
			$this->draw('UrlValueIcon', $attribute, $val);
		} else {
			$icon = $attribute->getIcon();
			if ($icon) printf('<img src="%s" alt="Icon" align="top" />&nbsp;', $icon);
		}
	}

	protected function drawAttributeDnValueIcon($attribute, $val) {
		if (strlen($val) <= 0) {
			echo '<img src="images/go.png" alt="Go" align="top" />&nbsp;';

		} elseif ($this->ldapserver->dnExists($val)) {
			$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',$this->ldapserver->server_id,$val);
			printf('<a title="%s %s" href="%s"><img src="images/go.png" alt="Go" /></a>&nbsp;',_('Go to'),
				htmlspecialchars($val), htmlspecialchars($href));

		} else {
			printf('<a title="%s %s"><img src="images/nogo.png" alt="Go" /></a>&nbsp;',_('DN not available'),htmlspecialchars($val));
		}
	}

	protected function drawAttributeMailValueIcon($attribute, $val) {
		$img = '<img src="images/mail.png" alt="'._('Mail').'" align="top" />';
		if (strlen($val) <= 0) echo $img;
		else printf('<a href="mailto:%s">'.$img.'</a>', htmlspecialchars($val));
		echo '&nbsp;';
	}

	protected function drawAttributeUrlValueIcon($attribute, $val) {
		$img = '<img src="images/dc.png" alt="'._('URL').'" align="top" />';
		if (strlen($val) <= 0) echo $img;
		else printf('<a href="%s" target="new">'.$img.'</a>', htmlspecialchars($val));
		echo '&nbsp;';
	}

	protected function drawAttributeName($attribute) {
		$config = $_SESSION[APPCONFIG];

		$attr_display = $attribute->getFriendlyName();

		if ($attribute->getEntry() && $attribute->getEntry()->getDn() // if not creating attribute
			&& $config->isCommandAvailable('schema') ) {
			$href = sprintf('cmd.php?cmd=schema&server_id=%s&view=attributes&viewvalue=%s',
				$this->ldapserver->server_id, real_attr_name($attribute->getName()));
			printf('<a title="'._('Click to view the schema definition for attribute type \'%s\'')
				.'" href="%s">%s</a>', $attribute->getName(), htmlspecialchars($href), $attr_display);
		} else {
			printf('%s', $attr_display);
		}
	}

	protected function getAttributeAliasNote($attribute) {
		# is there a user-friendly translation available for this attribute?
		$friendly_name = $attribute->getFriendlyName();

		if ($friendly_name != $attribute->getName()) {
			return sprintf('<acronym title="%s: \'%s\' %s \'%s\'">%s</acronym>',_('Note'),$friendly_name,_('is an alias for'),$attribute->getName(),_('alias'));
		} else {
			return '';
		}
	}

	protected function getAttributeRequiredNote($attribute) {
		# is this attribute required by an objectClass ?
		$required_by = '';

		if ($attribute->getEntry()) {
			$schema_attr = $this->ldapserver->getSchemaAttribute($attribute->getName(),$attribute->getEntry()->getDn());
			if ($schema_attr) {
				$entry_attributes = $attribute->getEntry()->getAttributes();
				$objectclass_attribute = null;
				foreach ($entry_attributes as $entry_attribute) {
					# It seems that some LDAP servers (Domino) returns attributes in lower case?
					if ($entry_attribute->getName() == 'objectClass'
						|| $entry_attribute->getName() == 'objectclass') {
						$objectclass_attribute = $entry_attribute;
						break;
					}
				}

				if ($objectclass_attribute) {
					$classes = arrayLower($objectclass_attribute->getValues());
					foreach ($schema_attr->getRequiredByObjectClasses() as $required) {
						if (in_array(strtolower($required), $classes)) {
							$required_by .= $required . ' ';
						}
					}
				}
			}
		}

		if ($required_by) {
			return "<acronym title=\"" . sprintf(_('Required attribute for objectClass(es) %s'), $required_by) . "\">" . _('required') . "</acronym>";
		} else {
			return '';
		}
	}

	protected function getAttributeRdnNote($attribute) {
		# is this attribute required because its the RDN
		if ($attribute->isRdn()) {
			return "<acronym title=\"" . _('This attribute is required for the RDN.') . "\">" . 'rdn' . "</acronym>&nbsp;";
		} else {
			return '';
		}
	}

	protected function drawAttributeRequiredSymbol($attribute) {
		echo '*';
	}

	/***************************/
	/* Paint a BinaryAttribute */
	/***************************/

	protected function drawBinaryAttributeValues($attribute) {
		$valcount = $attribute->getValueCount();

		if ($attribute->isVisible()) {
			$this->draw('StartValueLine', $attribute);

			echo '<small>';
			echo _('Binary value');

			if ($valcount > 0) {
				if (strcasecmp($attribute->getName(), 'objectSid') == 0) {
					printf(' (%s)', binSIDtoText($attribute->getValue(0)));
				}
			}

			echo '<br />';

			if ($valcount > 0) {
				if ($attribute->getEntry() && $attribute->getEntry()->getDn()) {
					$href = sprintf('download_binary_attr.php?server_id=%s&dn=%s&attr=%s',
						$this->ldapserver->server_id, rawurlencode($attribute->getEntry()->getDn()),
							$attribute->getName());

					if ($valcount > 1) {
						for ($i=1; $i<=$valcount; $i++) {
							printf('<a href="%s&value_num=%s"><img src="images/save.png" alt="Save" /> %s(%s)</a><br />',
								htmlspecialchars($href), $i, _('download value'), $i);
						}
					} else {
						printf('<a href="%s"><img src="images/save.png" alt="Save" /> %s</a><br />',
							htmlspecialchars($href),_('download value'));
					}
				}

				if (! $attribute->isReadOnly() && $_SESSION[APPCONFIG]->isCommandAvailable('attribute_delete')) {
					printf('<a href="javascript:deleteAttribute(\'%s\', \'%s\');" style="color:red;">'.
						'<img src="images/trash.png" alt="Trash" /> %s</a>',
						$attribute->getName(), $attribute->getFriendlyName(), _('delete attribute'));
				}
			} elseif ($attribute->isReadOnly() || ! $_SESSION[APPCONFIG]->isCommandAvailable('attribute_add_value')) {
				printf('<input type="text" class="roval" value="%s" readonly /><br />',
					_("[no value]"));
			} else {
				$i = 0;
				$val = '';
				$id = sprintf('new_values_%s_%s', htmlspecialchars($attribute->getName()), $i);
				printf('<input type="file" class="val" name="new_values[%s][%s]"'.
					' id="%s" value="%s" onFocus="focus_%s(this);" onBlur="blur_%s(this);" %s %s/><br />',
					htmlspecialchars($attribute->getName()), $i, $id,
					htmlspecialchars($val), $attribute->getName(), $attribute->getName(),
					($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '',
					($attribute->getMaxLength() > 0) ? 'maxlength="'.$attribute->getMaxLength().'"' : '');
			}

			echo '</small>';

			$this->draw('EndValueLine', $attribute);
		} else {
			for ($i=0; $i<$valcount; $i++) {
				$n = $attribute->getFileName($i);
				$p = $attribute->getFilePath($i);
				if ($n && $p) {
					printf('<input type="hidden" name="old_values[%s][%s]" value="%s" />',
						htmlspecialchars($attribute->getName()), $i, md5("$n|$p"));
					printf('<input type="hidden" name="new_values[%s][%s]" value="%s" />',
						htmlspecialchars($attribute->getName()), $i, md5("$n|$p"));
				}
			}
		}
	}

	protected function drawBinaryAttributeJavascript($attribute) {
		$this->draw('Attribute::Javascript', $attribute);

		$dn = '';
		if ($attribute->getEntry()) $dn = $attribute->getEntry()->getDn();
		if (!$dn) return;

		static $already_draw = false;
		if ($already_draw) return;
		else $already_draw = true;

		echo '
	<!-- This form is submitted by JavaScript when the user clicks "Delete attribute" on a binary attribute -->
	<form name="delete_attribute_form" action="cmd.php?cmd=delete_attr" method="post">
		<input type="hidden" name="server_id" value="'.$this->ldapserver->server_id.'" />
		<input type="hidden" name="dn" value="'.htmlspecialchars($dn).'" />
		<input type="hidden" name="attr" value="FILLED IN BY JAVASCRIPT" />
	</form>';

		echo '
	<script type="text/javascript" language="javascript">
	function deleteAttribute(attrName, friendlyName)
	{
		if (confirm("'._('Really delete attribute').' \'" + friendlyName + "\'?")) {
			document.delete_attribute_form.attr.value = attrName;
			document.delete_attribute_form.submit();
		}
	}
	</script>';
	}

	protected function drawBinaryAttributeBlurJavascript($attribute, $component) {
	}

	/***************************/
	/* Paint a DateAttribute   */
	/***************************/

	protected function drawDateAttributeReadWriteValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';

		printf('<span style="white-space: nowrap;"><input type="text" class="val" id="f_date_%s_%s"'
		       .' name="new_values[%s][%s]" value="%s" onFocus="focus_%s(this);" onBlur="blur_%s(this);" %s %s/>&nbsp;',
			$attribute->getName(), $i, htmlspecialchars($attribute->getName()), $i, htmlspecialchars($val),
			$attribute->getName(), $attribute->getName(),
			($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '',
			($attribute->getMaxLength() > 0) ? 'maxlength="'.$attribute->getMaxLength().'"' : '');
		draw_date_selector_link($attribute->getName().'_'.$i);
		echo '</span>';
	}

	protected function drawDateAttributeJavascript($attribute) {
		$this->draw('Attribute::Javascript', $attribute);

		$entry['date'] = $_SESSION[APPCONFIG]->GetValue('appearance','date_attrs');
		$entry['time'] = $_SESSION[APPCONFIG]->GetValue('appearance','date_attrs_showtime');
		$entry['format'] = $_SESSION[APPCONFIG]->GetValue('appearance', 'date');

		if (isset($entry['date'][$attribute->getName()]))
			$entry['format'] = $entry['date'][$attribute->getName()];

		//included in class page to avoid multiple inclusions
		//printf('<script type="text/javascript" src="%sjscalendar/calendar.js"></script>','../htdocs/'.JSDIR);
		printf('<script type="text/javascript" src="%sjscalendar/lang/calendar-en.js"></script>','../htdocs/'.JSDIR);
		printf('<script type="text/javascript" src="%sjscalendar/calendar-setup.js"></script>','../htdocs/'.JSDIR);
		printf('<script type="text/javascript" src="%sdate_selector.js"></script>','../htdocs/'.JSDIR);

		for ($i = 0; $i <= $attribute->getValueCount(); $i++) {
			printf('<script type="text/javascript" language="javascript">defaults[\'f_date_%s_%s\'] = \'%s\';</script>',$attribute->getName(),$i,$entry['format']);

			if (in_array_ignore_case($attribute->getName(),array_keys($entry['time'])) && ($entry['time'][$attribute->getName()]))
				printf('<script type="text/javascript" language="javascript">defaults[\'f_time_%s_%s\'] = \'%s\';</script>',$attribute->getName(),$i,'true');
		}
	}

	/***************************/
	/* Paint a DnAttribute     */
	/***************************/

	protected function drawDnAttributeReadWriteValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		if ($attribute->hasProperty('helper')) {
			echo '<table cellspacing="0" cellpadding="0"><tr><td valign="top">';
		}

		$input_name = sprintf('new_values[%s][%s]', htmlspecialchars($attribute->getName()), $i);
		$input_id = sprintf('new_values_%s_%s', htmlspecialchars($attribute->getName()), $i);

		printf('<span style="white-space: nowrap;"><input type="text" class="val" name="%s" id="%s" value="%s"'
		       .' onFocus="focus_%s(this);" onBlur="blur_%s(this);" %s %s/>&nbsp;',
			$input_name, $input_id, htmlspecialchars($val),
			$attribute->getName(), $attribute->getName(),
			($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '',
			($attribute->getMaxLength() > 0) ? 'maxlength="'.$attribute->getMaxLength().'"' : '');

		/* draw a link for popping up the entry browser if this is the type of attribute
		 * that houses DNs. */
		draw_chooser_link("entry_form.$input_id", false);
		echo '</span>';

		if ($attribute->hasProperty('helper')) {
			echo '</td><td valign="top">';
			$this->draw('Helper', $attribute, $i);
			echo '</td></tr></table>';
		}
	}

	protected function drawDnAttributeIcon($attribute, $val) {
		$this->draw('DnValueIcon', $attribute, $val);
	}

	/***************************/
	/* Paint a GidAttribute    */
	/***************************/

	protected function drawGidAttributeReadWriteValue($attribute, $i) {
		$this->draw('Attribute::ReadWriteValue', $attribute, $i);

		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';

		$dn = '';
		if ($attribute->getEntry()) $dn = $attribute->getEntry()->getDn();

		# If this is a gidNumber on a non-PosixGroup entry, lookup its name and description for convenience
		if (! in_array_ignore_case('posixGroup', $this->ldapserver->getDNAttr($dn, 'objectClass'))) {
			$gid_number = $val;
			$search_group_filter = "(&(objectClass=posixGroup)(gidNumber=$val))";
			$group = $this->ldapserver->search(null, null, $search_group_filter,array('dn','description'));

			if (count($group) > 0) {
				echo '<br />';

				$group = array_pop($group);
				$group_dn = $group['dn'];
				$group_name = explode('=',get_rdn($group_dn));
				$group_name = $group_name[1];
				$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',
					$this->ldapserver->server_id, rawurlencode($group_dn));

				echo '<small>';
				printf('<a href="%s">%s</a>', htmlspecialchars($href), htmlspecialchars($group_name));

				$description = isset($group['description']) ? $group['description'] : null;

				if (is_array($description)) {
					foreach ($description as $item)
						printf(' (%s)',htmlspecialchars($item));
				} else {
					printf(' (%s)',htmlspecialchars($description));
				}

				echo '</small>';
			}
		}
	}

	/***************************/
	/* Paint a JpegAttribute   */
	/***************************/

	protected function drawJpegAttributeValues($attribute) {
		if ($attribute->isVisible()) {
			$this->draw('StartValueLine', $attribute);

			$value_count = $attribute->getValueCount();
			if ($value_count > 0) {
				/* Don't draw the delete buttons if there is more than one jpegPhoto
				 * (phpLDAPadmin can't handle this case yet) */
				if ($attribute->getEntry() && $attribute->getEntry()->getDn()) {
					draw_jpeg_photos($this->ldapserver, $attribute->getEntry()->getDn(),
						$attribute->getName(), ! $attribute->isReadOnly()
						&& $_SESSION[APPCONFIG]->isCommandAvailable('attribute_delete'));
				}
			} elseif ($attribute->isReadOnly() || ! $_SESSION[APPCONFIG]->isCommandAvailable('attribute_add_value')) {
				printf('<input type="text" class="roval" value="%s" readonly /><br />',_('[no value]'));
			} else {
				$i = 0;
				$val = '';
				$id = sprintf('new_values_%s_%s', htmlspecialchars($attribute->getName()), $i);
				printf('<input type="file" class="val" name="new_values[%s][%s]"'.
					' id="%s" value="%s" onFocus="focus_%s(this);" onBlur="blur_%s(this);" %s %s/><br />',
					htmlspecialchars($attribute->getName()), $i, $id,
					htmlspecialchars($val), $attribute->getName(), $attribute->getName(),
					($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '',
					($attribute->getMaxLength() > 0) ? 'maxlength="'.$attribute->getMaxLength().'"' : '');
			}

			$this->draw('EndValueLine', $attribute);
		} else {
			$this->draw('BinaryAttribute::Values', $attribute);
		}
	}

	/******************************/
	/* Paint a MultiLineAttribute */
	/******************************/

	protected function drawMultiLineAttributeReadOnlyValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		printf('<textarea class="roval" %s %s name="new_values[%s][%s]" '.
			'id="new_values_%s_%s" readonly>%s</textarea><br />',
			($attribute->getRows() > 0) ? 'rows="'.$attribute->getRows().'"' : '',
			($attribute->getCols() > 0) ? 'cols="'.$attribute->getCols().'"' : '',
			htmlspecialchars($attribute->getName()), $i,
			htmlspecialchars($attribute->getName()), $i,
			htmlspecialchars($val));
	}

	protected function drawMultiLineAttributeReadWriteValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		printf('<textarea class="val" %s %s name="new_values[%s][%s]" '.
			'id="new_values_%s_%s" onFocus="focus_%s(this);" onBlur="blur_%s(this);">%s</textarea>',
			($attribute->getRows() > 0) ? 'rows="'.$attribute->getRows().'"' : '',
			($attribute->getCols() > 0) ? 'cols="'.$attribute->getCols().'"' : '',
			htmlspecialchars($attribute->getName()), $i,
			htmlspecialchars($attribute->getName()), $i,
			$attribute->getName(), $attribute->getName(),
			htmlspecialchars($val));
	}

	/********************************/
	/* Paint a ObjectClassAttribute */
	/********************************/

	protected function drawObjectClassAttributeNewValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		$schema_object = ($val) ? $this->ldapserver->getSchemaObjectClass($val) : false;
		$structural = (is_object($schema_object) && $schema_object->getType() == 'structural');

		if (!$attribute->isVisible()) {
			$this->draw('HiddenValue', $attribute, $i);
		} elseif ($structural) {
			# Is this value is a structural objectClass, make it read-only
			printf('<input type="text" class="roval" name="new_values[%s][%s]"
				id="new_values_%s_%s" value="%s" readonly />',
				htmlspecialchars($attribute->getName()), $i,
				htmlspecialchars($attribute->getName()), $i, htmlspecialchars($val));

			printf(' <small>(<acronym title="%s">%s</acronym>)</small><br />',
				_('This is a structural ObjectClass and cannot be removed.'),
				_('structural'));
		} else {
			$this->draw('Attribute::NewValue', $attribute, $i);
		}
	}

	protected function drawObjectClassAttributeIcon($attribute, $val) {
		if (strlen($val) > 0) {
			$href = sprintf('cmd.php?cmd=schema&server_id=%s&view=objectClasses&viewvalue=%s',
				$this->ldapserver->server_id, $val);
			printf('<a title="%s" href="%s"><img src="images/info.png" alt="Info" /></a>&nbsp;',
				_('View the schema description for this objectClass'), htmlspecialchars($href));
		}
	}

	/*****************************/
	/* Paint a PasswordAttribute */
	/*****************************/

	protected function drawPasswordAttributeOldValue($attribute, $i) {
		//if ($this->context == ENTRY_WRITER_CREATION_CONTEXT) {
			$this->draw('Attribute::OldValue', $attribute, $i);
		//}
	}

	protected function drawPasswordAttributeHiddenValue($attribute, $i) {
		if ($this->context == ENTRY_WRITER_CREATION_CONTEXT) {
			$this->draw('Attribute::HiddenValue', $attribute, $i);
		}
	}

	protected function drawPasswordAttributeReadOnlyValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		$enc_type = get_enc_type($val);
		if ($val == '') $enc_type = get_default_hash($this->ldapserver->server_id);
		$obfuscate_password = obfuscate_password_display($enc_type);

		printf('<input type="%s" class="roval" name="new_values[%s][%s]" id="new_values_%s_%s" value="%s" %s readonly /><br />',
		        ($obfuscate_password ? 'password' : 'text'),
			htmlspecialchars($attribute->getName()), $i, htmlspecialchars($attribute->getName()),
			$i, htmlspecialchars($val), ($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '');
		if ($val != '') $this->draw('CheckLink', $attribute, 'new_values_'.htmlspecialchars($attribute->getName()).'_'.$i);
	}

	protected function drawPasswordAttributeReadWriteValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		$enc_type = get_enc_type($val);

		# Set the default hashing type if the password is blank (must be newly created)
		if ($val == '') {
			$enc_type = get_default_hash($this->ldapserver->server_id);
		}

		//printf('<input type="hidden" name="old_values[%s][%s]" value="%s" />', $attribute->getName(), $i, htmlspecialchars($val));

		//if (strlen($val) > 0) {
		//	if (obfuscate_password_display($enc_type)) {
		//		echo htmlspecialchars(preg_replace('/./','*', $val));
		//	} else {
		//		echo htmlspecialchars($val);
		//	}
		//	echo '<br />';
		//}

		echo '<table cellspacing="0" cellpadding="0"><tr><td valign="top">';

		$obfuscate_password = obfuscate_password_display($enc_type);
		$id = sprintf('new_values_%s_%s', htmlspecialchars($attribute->getName()), $i);
		printf('<input type="%s" class="val" name="new_values[%s][%s]" id="%s" value="%s"'
		       .' onFocus="focus_%s(this);" onBlur="blur_%s(this);" %s %s/>',
			($obfuscate_password ? 'password' : 'text'),
			htmlspecialchars($attribute->getName()), $i, $id,
			htmlspecialchars($val),
			$attribute->getName(), $attribute->getName(),
			($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '',
			($attribute->getMaxLength() > 0) ? 'maxlength="'.$attribute->getMaxLength().'"' : '');

		echo '</td><td valign="top">';
		if ($attribute->hasProperty('helper')) {
			$this->draw('Helper', $attribute, $i);
		}
		echo '</td></tr><tr><td valign="top">';

		if ($attribute->hasProperty('verify') && $attribute->getProperty('verify') && $obfuscate_password) {
			$id_v = sprintf('new_values_verify_%s_%s', htmlspecialchars($attribute->getName()), $i);
			printf('<input type="password" class="val" name="new_values_verify[%s][%s]" id="%s" value="" %s %s/>',
			       htmlspecialchars($attribute->getName()), $i, $id_v,
			       ($attribute->getSize() > 0) ? 'size="'.$attribute->getSize().'"' : '',
			       ($attribute->getMaxLength() > 0) ? 'maxlength="'.$attribute->getMaxLength().'"' : '');
			echo '</td><td valign="top">';
			printf('(%s)', _('confirm'));
			echo '</td></tr><tr><td valign="top">';
		}

		$this->draw('CheckLink', $attribute, $id);
		echo '</td></tr></table>';
	}

	protected function getPasswordAttributeDefaultValueHelper($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		$enc_type = get_enc_type($val);

		# Set the default hashing type if the password is blank (must be newly created)
		if ($val == '') {
			$enc_type = get_default_hash($this->ldapserver->server_id);
		}

		return $enc_type;
	}

	protected function drawPasswordAttributeCheckLink($attribute, $component_id) {
		printf('<small><a href="javascript:passwordComparePopup(\'%s\')">%s</a></small><br />',
			$component_id/*base64_encode($val)*/, _('Check password...'));
	}

	protected function drawPasswordAttributeJavascript($attribute) {
		$this->draw('Attribute::Javascript', $attribute);

		static $already_draw = false;
		if ($already_draw) return;
		else $already_draw = true;

		# add the javascript so we can call check password later.
		echo '
	<script type="text/javascript" language="javascript">
		function passwordComparePopup(component_id) {
			mywindow = open(\'password_checker.php\',\'myname\',\'resizable=no,width=500,height=200,scrollbars=1\');
			mywindow.location.href = \'password_checker.php?componentid=\'+component_id;
			if (mywindow.opener == null) mywindow.opener = self;
		}
	</script>';
	}

	/***********************************/
	/* Paint a RandomPasswordAttribute */
	/***********************************/

	protected function drawRandomPasswordAttributeJavascript($attribute) {
		$this->draw('PasswordAttribute::Javascript', $attribute);

		$pwd = password_generate();
		$pwd = str_replace("\\", "\\\\", $pwd);
		$pwd = str_replace("'", "\\'", $pwd);

		echo '<script type="text/javascript" language="javascript">';
		printf('var i = 0; var component = document.getElementById(\'new_values_%s_\'+i);', $attribute->getName());
		printf('while (component) { if (!component.value) {');
		printf('component.value = \'%s\';', $pwd);
		printf('alert(\'%s%s\n%s\');', _('A random password was generated for you'), _(':'), $pwd);
		printf('} i++; component = document.getElementById(\'new_values_%s_\'+i); }', $attribute->getName());
		echo '</script>';
	}

	/******************************/
	/* Paint a SelectionAttribute */
	/******************************/

	protected function drawSelectionAttributeValues($attribute) {
		if (!$attribute->isVisible() || !$attribute->isMultiple() || ($attribute->getValueCount() > 0)) {
			$this->draw('Attribute::Values', $attribute);
		} else {
			$this->draw('StartValueLine', $attribute);
			$this->draw('Value', $attribute, 0);
			$this->draw('Menu', $attribute);
			$this->draw('EndValueLine', $attribute);
		}
	}

	protected function drawSelectionAttributeReadOnlyValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		printf('<input type="hidden" class="val" name="new_values[%s][%s]" id="new_values_%s_%s" value="%s" />',
			htmlspecialchars($attribute->getName()), $i,
			htmlspecialchars($attribute->getName()), $i,
			htmlspecialchars($val));

		$select = $attribute->getSelection();
		if (isset($select[$val])) $val = $select[$val];
		echo htmlspecialchars($val).'<br />';
	}

	protected function drawSelectionAttributeReadWriteValue($attribute, $i) {
		if ($attribute->isMultiple()) {
			// for multiple selection, we draw the component only one time
			if ($i > 0) return;

			if (($attribute->getSize() > 0) && ($attribute->getSize() < $attribute->getOptionCount())) {
				$selected = array();
				printf('<select name="new_values[%s][]" multiple size="%s">',
					htmlspecialchars($attribute->getName()), $attribute->getSize());
				$vals = $attribute->getValues();
				$j = 0;
				foreach ($attribute->getSelection() as $value => $description) {
					if (in_array($value, $vals)) $selected[$value] = true;
					$id = 'new_values_'.htmlspecialchars($attribute->getName()).'_'.($j++);
					printf('<option id="%s" value="%s" onMouseDown="focus_%s(this);" onClick="blur_%s(this);" %s>%s</option>',
					       $id, $value, htmlspecialchars($attribute->getName()), htmlspecialchars($attribute->getName()),
					       isset($selected[$value]) ? 'selected' : '', $description);
				}
				foreach ($vals as $val) {
					if (!isset($selected[$val])) {
						$id = 'new_values_'.htmlspecialchars($attribute->getName()).'_'.($j++);
						printf('<option id="%s" value="%s" onMouseDown="focus_%s(this);" onClick="blur_%s(this);" selected>'
						       .'%s</option>', $id, $val, htmlspecialchars($attribute->getName()),
						       htmlspecialchars($attribute->getName()), $val);
					}
				}
				echo '</select>';
			} else {
				$selected = array();
				$vals = $attribute->getValues();
				$j = 0;

				echo '<table cellspacing="0" cellpadding="0">';
				foreach ($attribute->getSelection() as $value => $description) {
					if (in_array($value, $vals)) $selected[$value] = true;
					$id = 'new_values_'.htmlspecialchars($attribute->getName()).'_'.($j++);
					printf('<tr><td><input type="checkbox" id="%s" name="new_values[%s][]" value="%s"'
					       .' onFocus="focus_%s(this);" onClick="blur_%s(this);" %s /></td><td>%s</td></tr>',
					        $id, htmlspecialchars($attribute->getName()), $value,
				                $attribute->getName(), $attribute->getName(),
						isset($selected[$value]) ? 'checked' : '',
						"<span style=\"white-space: nowrap;\">&nbsp;$description</span>");
				}
				foreach ($vals as $val) {
					if (!isset($selected[$val])) {
						$id = 'new_values_'.htmlspecialchars($attribute->getName()).'_'.($j++);
						printf('<tr><td><input type="checkbox" id="%s" name="new_values[%s][]"'
							.' value="%s" onFocus="focus_%s(this);" onClick="blur_%s(this);" checked /></td><td>%s</td></tr>',
							$id, htmlspecialchars($attribute->getName()), $val,
							$attribute->getName(), $attribute->getName(),
							"<span style=\"white-space: nowrap;\">&nbsp;$val</span>");
					}
				}
				echo '</table>';
			}
		} else {
			$val = $attribute->getValue($i);
			if (!is_string($val)) $val = '';
			if ($i < 0) $i = 0;

			if ($attribute->hasProperty('helper')) {
				echo '<table cellspacing="0" cellpadding="0"><tr><td valign="top">';
			}

			$found = false;
			$empty_value = false;

			$id = 'new_values_'.htmlspecialchars($attribute->getName()).'_'.$i;
			printf('<select id="%s" name="new_values[%s][]" onFocus="focus_%s(this);" onChange="blur_%s(this);">',
			       $id, htmlspecialchars($attribute->getName()), $attribute->getName(), $attribute->getName());

			foreach ($attribute->getSelection() as $value => $description) {
				printf('<option value="%s" %s>%s</option>', $value,
					($value == $val) ? 'selected' : '', $description);
				if ($value == $val) $found = true;
				if ($value == '') $empty_value = true;
			}
			if (!$found) {
				/*if ($val || ($i >= 0) || ($attribute->getEntry() && $attribute->getEntry()->getDn()))*/
				printf('<option value="%s" selected>%s</option>', $val, $val);
				if ($val == '') $empty_value = true;
			}
			if ((strlen($val) > 0) && !$empty_value && ($attribute->getEntry() && $attribute->getEntry()->getDn())) {
				printf('<option value="">(%s)</option>', _('none, remove value'));
			}
			echo '</select>';

			if ($attribute->hasProperty('helper')) {
				echo '</td><td valign="top">';
				$this->draw('Helper', $attribute, $i);
				echo '</td></tr></table>';
			}
		}
	}

	protected function getSelectionAttributeMenuItem($attribute, $i) {
		switch ($i) {
			case IdAttributeAddValueMenuItem :
				if (!$attribute->isMultiple()) {
					return $this->get('Attribute::MenuItem', $attribute, $i);
				}
				return '';
			case IdAttributeModifyMemberMenuItem :
				return '';
			default :
				return $this->get('Attribute::MenuItem', $attribute, $i);
		}
	}

	protected function drawSelectionAttributeIcon($attribute, $val) {
		if (!$attribute->isMultiple() || $attribute->isReadOnly()) {
			$this->draw('Attribute::Icon', $attribute, $val);
		}
	}

	/***************************/
	/* Paint a ShadowAttribute */
	/***************************/

	protected function drawShadowAttributeReadOnlyValue($attribute, $i) {
		$val = $attribute->getValue($i);
		if (!is_string($val)) $val = '';
		if ($i < 0) $i = 0;

		printf('<input type="hidden" class="val" name="new_values[%s][%s]" id="new_values_%s_%s" value="%s" />',
			htmlspecialchars($attribute->getName()), $i,
			htmlspecialchars($attribute->getName()), $i,
			htmlspecialchars($val));

		$today = date('U');
		$shadow_date = $today;
		if ($attribute->getEntry()) {
			$shadow_date = shadow_date($attribute->getEntry()->getAttributes(),$attribute->getName());
		}

		echo htmlspecialchars($val).'&nbsp;';
		echo '<small>';
		$this->draw('ShadowDate', $attribute, $shadow_date);
		echo '</small><br />';
	}

	protected function drawShadowAttributeReadWriteValue($attribute, $i) {
		$this->draw('Attribute::ReadWriteValue', $attribute, $i);

		$val = $attribute->getValue($i);
		if (!is_string($val) || (strlen($val) <= 0)) return;

		$today = date('U');
		$shadow_date = $today;
		if ($attribute->getEntry()) {
			$shadow_date = shadow_date($attribute->getEntry()->getAttributes(),$attribute->getName());
		}

		# Show the dates for all the shadow attributes.
		if ($shadow_date !== false) {
			echo '<small>';
			$this->draw('ShadowDate', $attribute, $shadow_date);
			echo '</small>';
			echo '<br />';
		}
	}

	protected function drawShadowAttributeShadowDate($attribute, $shadow_date) {
		$config = $_SESSION[APPCONFIG];

		//$shadow_format_attrs = array_merge($shadow_before_today_attrs,$shadow_after_today_attrs);
		$shadow_before_today_attrs = arrayLower($attribute->shadow_before_today_attrs);
		$shadow_after_today_attrs = arrayLower($attribute->shadow_after_today_attrs);
		$today = date('U');

		if (($today < $shadow_date) && in_array(strtolower($attribute->getName()),$shadow_before_today_attrs)) {
			echo '<span style="color:red">(';
			echo htmlspecialchars(strftime($config->GetValue('appearance', 'date'), $shadow_date));
			echo ')</span>';

		} elseif (($today > $shadow_date) && in_array(strtolower($attribute->getName()),$shadow_after_today_attrs)) {
			echo '<span style="color:red">(';
			echo htmlspecialchars(strftime($config->GetValue('appearance', 'date'), $shadow_date));
			echo ')</span>';

		} else {
			echo '(';
			echo htmlspecialchars(strftime($config->GetValue('appearance', 'date'), $shadow_date));
			echo ')';
		}
	}
}
?>
