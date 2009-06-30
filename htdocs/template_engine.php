<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/template_engine.php,v 1.45 2007/12/15 07:50:30 wurley Exp $

/**
 * Template render engine.
 * @param dn $dn DN of the object being edited. (For editing existing entries)
 * @param dn $container DN where the new object will be created. (For creating new entries)
 * @param string $template to use for new entry. (For creating new entries)
 * @todo schema attr keys should be in lowercase.
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 */
/**
 */

require_once './common.php';

$entry['dn']['encode'] = get_request('dn','REQUEST');
$entry['dn']['string'] = rawurldecode($entry['dn']['encode']);
$entry['template'] = get_request('template','REQUEST',false,'');

# If we have a DN, then this is to edit the entry.
if ($entry['dn']['string']) {
	$ldapserver->dnExists($entry['dn']['string'])
		or pla_error(sprintf(_('No such entry: %s'),pretty_print_dn($entry['dn']['string'])));

	$tree = get_cached_item($ldapserver->server_id,'tree');

	if ($tree) {
		$entry['dn']['tree'] = $tree->getEntry($entry['dn']['string']);

		if (! $entry['dn']['tree']) {
			/*
			 * The entry doesn't exists in the tree because it
			 * may be filtered ; as we ask for its display, we
			 * add all the same the entry in the tree
			 */
			$tree->addEntry($entry['dn']['string']);
			$entry['dn']['tree'] = $tree->getEntry($entry['dn']['string']);

		}

		if ($entry['dn']['tree']) {
			eval('$reader = new '.$_SESSION['plaConfig']->GetValue('appearance', 'entry_reader').'($ldapserver);');
			$entry['dn']['tree']->accept($reader);

			eval('$writer = new '.$_SESSION['plaConfig']->GetValue('appearance', 'entry_writer').'($ldapserver);');
			$entry['dn']['tree']->accept($writer);
		}
	}

} else {
	if ($ldapserver->isReadOnly())
		pla_error(_('You cannot perform updates while server is in read-only mode'));

	# Create a new empty entry
	$entryfactoryclass = $_SESSION['plaConfig']->GetValue('appearance','entry_factory');
	eval('$entry_factory = new '.$entryfactoryclass.'();');
	$entry['dn']['tree'] = $entry_factory->newCreatingEntry('');

	# Init the entry with incoming data
	eval('$reader = new '.$_SESSION['plaConfig']->GetValue('appearance', 'entry_reader').'($ldapserver);');
	$entry['dn']['tree']->accept($reader);

	# Display the creating entry
	eval('$writer = new '.$_SESSION['plaConfig']->GetValue('appearance', 'entry_writer').'($ldapserver);');
	$entry['dn']['tree']->accept($writer);
}
?>
