<?php
// $Header$

/**
 * Classes and functions for the LDAP tree.
 *
 * @author The phpLDAPadmin development team
 * @package phpLDAPadmin
 */

/**
 * This class implements an AJAX based tree.
 *
 * @package phpLDAPadmin
 * @subpackage Tree
 * @see HTMLTree Tree
 */
class AJAXTree extends HTMLTree {
	/**
	 * Draw a node of the tree
	 *
	 * @param dn The Base DN to draw
	 * @param string $level a string of 0 and 1 ; $level == "000101" will draw "   | |<node>"
	 * @param boolean $first_child is the first child entry, which is normally the "Create New Entry" option
	 * @param boolean $last_child is the last child entry, which is normally the "Create New Entry" option
	 */
	protected function draw_dn($dn,$level,$first_child=true,$last_child=true) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s,%s,%s,%s)',33,__FILE__,__LINE__,__METHOD__,$dn,$level,$first_child,$last_child);

		$server = $this->getServer();

		# Level pre-treatment
		$code = '';
		if (is_string($level)) {
			for ($i=0; $i<strlen($level); $i++) {
				if ($level[$i] == '0')
					$code .= '0';
				elseif ($level[$i] == '1')
					$code .= '1';
			}

		} elseif ($level > 0)
			$code = '0' * $level;

		$level = strlen($code);

		# Get entry to display as node
		$entry = $this->getEntry($dn);

		# If the entry doesnt exist, we'll add it.
		if (! $entry) {
			$this->addEntry($dn);
			$entry = $this->getEntry($dn);
		}

		# If the entry doesnt exist in the server, then return here with an empty string.
		if (! $entry)
			return '';

		# Get our children.
		$child_count = $this->readChildrenNumber($dn);

		$nb = 0;
		if ($first_child)
			$nb += 1;
		if ($last_child)
			$nb += 2;

		$imgs['expand'] = array(JSDIR.'phplayersmenu/menuimages/tree_expand.png',
			JSDIR.'phplayersmenu/menuimages/tree_expand.png',
			JSDIR.'phplayersmenu/menuimages/tree_expand_corner.png',
			($level > 0) ? JSDIR.'phplayersmenu/menuimages/tree_expand_corner.png' : JSDIR.'phplayersmenu/menuimages/tree_expand_corner_first.png');

		$imgs['collapse'] = array(JSDIR.'phplayersmenu/menuimages/tree_collapse.png',
			JSDIR.'phplayersmenu/menuimages/tree_collapse.png',
			JSDIR.'phplayersmenu/menuimages/tree_collapse_corner.png',
			($level > 0) ? JSDIR.'phplayersmenu/menuimages/tree_collapse_corner.png' : JSDIR.'phplayersmenu/menuimages/tree_collapse_corner_first.png');

		$imgs['tree'] = array(JSDIR.'phplayersmenu/menuimages/tree_split.png',
			JSDIR.'phplayersmenu/menuimages/tree_split.png',
			JSDIR.'phplayersmenu/menuimages/tree_corner.png',
			JSDIR.'phplayersmenu/menuimages/tree_corner.png');

		/** Information on array[$nb]
		 * nb == 1 => the node is the first child
		 * nb == 2 => the node is the last child
		 * nb == 3 => the node is the unique child
		 * nb == 0 => the node is a child */
		$new_code = array('1','1','0','0');

		# Links
		$parms['openclose'] = htmlspecialchars(sprintf('server_id=%s&dn=%s&code=%s%s',$this->getServerID(),rawurlencode($dn),$code,$new_code[$nb]));
		$parms['edit'] = htmlspecialchars(sprintf('cmd=template_engine&server_id=%s&dn=%s',$this->getServerID(),rawurlencode($dn)));
		$href = sprintf('cmd.php?%s',$parms['edit']);

		# Each node has a unique id based on dn
		$node_id = sprintf('node%s',base64_encode(sprintf('%s-%s',$server->getIndex(),$dn)));
		$node_id = str_replace('=','_',$node_id);

		if ($level == 0)
			printf('<tr><td class="spacer"></td><td colspan="%s">',$this->getDepth()+3-1);

		printf('<div id="jt%s" class="treemenudiv">',$node_id);

		echo $this->get_indentation($code);

		if (! $child_count)
			printf('<img align="top" border="0" class="imgs" id="jt%snode" src="%s" alt="--" />',$node_id,$imgs['tree'][$nb]);

		else {
			printf('<a href="#" onclick="return opencloseTreeNode(\'%s\',\'%s\');">',$node_id,$parms['openclose']);

			if ($entry->isOpened())
				printf('<img align="top" border="0" class="imgs" id="jt%snode" src="%s" alt="+-" />',$node_id,$imgs['collapse'][$nb]);
			else
				printf('<img align="top" border="0" class="imgs" id="jt%snode" src="%s" alt="+-" />',$node_id,$imgs['expand'][$nb]);

			echo '</a>';
		}

		printf('<a href="%s" onclick="return displayAJ(\'BODY\',\'%s\',\'%s\');" title="%s" >',$href,$parms['edit'],_('Retrieving DN'),htmlspecialchars($dn));
		printf('<span class="dnicon"><img align="top" border="0" class="imgs" id="jt%sfolder" src="%s/%s" alt="->" /></span>',$node_id,IMGDIR,$entry->getIcon($server));
		echo '</a>';

		echo '&nbsp;';
		printf('<a href="%s" onclick="return displayAJ(\'BODY\',\'%s\',\'%s\');" title="%s" class="phplm">',$href,$parms['edit'],_('Retrieving DN'),htmlspecialchars($dn));
		echo $this->get_formatted_dn($entry,$level-1);
		echo ($child_count ? (sprintf(' (%s%s)',$child_count,($entry->isSizeLimited() ? '+' : ''))) : '');
		echo '</a>';

		echo '</div>';

		printf('<div id="jt%sson" style="display: %s" class="treemenudiv">',$node_id,($entry->isOpened() ? 'block' : 'none'));
		if ($entry->isOpened())
			$this->draw_children($entry,$code.$new_code[$nb]);

		echo '</div>';

		if ($level == 0)
			echo '</td></tr>';
	}

	/**
	 * Expand and draw a child entry, when it is clicked on. This is using AJAX just to render this section of the tree.
	 */
	public function draw_children($parent_entry,$code) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s,%s)',33,__FILE__,__LINE__,__METHOD__,$parent_entry,$code);

		$children = array();
		foreach ($parent_entry->getChildren() as $child) {
			if (! $this->getEntry($child))
				$this->addEntry($child);

			array_push($children,$this->getEntry($child));
		}

		$first_child = $this->create_before_child($parent_entry,$code);
		$last_child = $this->create_after_child($parent_entry,$code);

		# If compression is on, we need to compress this output - but only if called by draw_tree_node
		if (function_exists('isCompress') && isCompress() && get_request('cmd','REQUEST') == 'draw_tree_node')
			ob_start();

		echo $first_child;

		for ($i=0; $i<count($children); $i++) {
			$first = ($i == 0) && (! $first_child);
			$last = ($i == (count($children)-1)) && (! $last_child);

			if (is_object($children[$i]))
				$this->draw_dn($children[$i]->getDN(),$code,$first,$last);
			else
				echo '<br/>problem getting DN entry from ldap';

			echo "\n";
		}

		echo $last_child;

		# If compression is on, we need to compress this output
		if (function_exists('isCompress') && isCompress() && get_request('cmd','REQUEST') == 'draw_tree_node') {
			$output = ob_get_clean();
			echo gzencode($output);
		}
	}

	/**
	 * Return the indentation before a node
	 *
	 * @param $code a string of 0 and 1 ; $code == "000101" will return "   | |"
	 */
	protected function get_indentation($code) {
		$indent = '';

		for ($i=0; $i<strlen($code); $i++) {
			switch ($code[$i]) {
				case '0':
					$indent .= sprintf('<img align="top" border="0" class="imgs" src="%s/phplayersmenu/menuimages/tree_space.png" alt="  " />',JSDIR);
					break;

				case '1':
					$indent .= sprintf('<img align="top" border="0" class="imgs" src="%s/phplayersmenu/menuimages/tree_vertline.png" alt="| " />',JSDIR);
					break;
			}
		}

		return $indent;
	}

	/**
	 * Draw the javascript to support the tree.
	 */
	protected function draw_javascript() {
		printf('<script type="text/javascript" language="javascript" src="%sphplayersmenu/libjs/layersmenu-browser_detection.js"></script>',JSDIR);
		printf('<script type="text/javascript" language="javascript" src="%sajaxtree.js"></script>',JSDIR);
	}

	/**
	 * Draw the "Create New Entry" item before the children.
	 */
	private function create_before_child($entry,$level) {
		if (strlen($level) == 0)
			return '';

		$server = $this->getServer();
		$output = '';

		if (! $server->isReadOnly() && ! $entry->isLeaf() && (count($entry->getChildren()) > 10) && $this->getServer()->isShowCreateEnabled()
			&& $_SESSION[APPCONFIG]->getValue('appearance','show_top_create'))
			$output = $this->draw_create_new_entry($entry,$level,JSDIR.'phplayersmenu/menuimages/tree_split.png');

		return $output;
	}

	/**
	 * Draw the "Create New Entry" item after the children.
	 */
	private function create_after_child($entry,$level) {
		if (strlen($level) == 0)
			return '';

		$server = $this->getServer();
		$output = '';

		if (! $server->isReadOnly() && ! $entry->isLeaf() && $this->getServer()->isShowCreateEnabled())
			$output = $this->draw_create_new_entry($entry,$level,JSDIR.'phplayersmenu/menuimages/tree_corner.png');

		return $output;
	}

	/**
	 * Draw the "Create New Entry" item.
	 */
	private function draw_create_new_entry($entry,$level,$img) {
		$output = '';

		$href = sprintf('cmd=template_engine&server_id=%s&container=%s',$this->getServerID(),rawurlencode($entry->getDN()));

		$output .= $this->get_indentation($level);
		$output .= sprintf('<img align="top" border="0" class="imgs" src="%s" alt="--" />',$img);
		$output .= sprintf('<a href="%s" title="%s">',htmlspecialchars($href),$entry->getDN());
		$output .= sprintf('<img align="top" border="0" class="imgs" src="%s/create.png" alt="->" />',IMGDIR);
		$output .= '</a>';
		$output .= '&nbsp;';

		if (isAjaxEnabled())
			$output .= sprintf('<a href="cmd.php?%s" title="%s" class="phplm" onclick="return displayAJ(\'BODY\',\'%s\',\'%s\');">',
				htmlspecialchars($href),_('Create new entry here'),
				htmlspecialchars($href),_('Loading'));
		else
			$output .= sprintf('<a href="cmd.php?%s" title="%s" class="phplm">',htmlspecialchars($href),_('Create new entry here'));

		$output .= _('Create new entry here');
		$output .= '</a>';

		return $output;
	}

	/**
	 * List the items in the tree that are open
	 *
	 * @return array List of open nodes
	 */
	public function listOpenItems() {
		$result = array();

		foreach ($this->entries as $dn => $value)
			if ($value->isOpened())
				array_push($result,$value->getDN());

		return $result;
	}
}
?>
