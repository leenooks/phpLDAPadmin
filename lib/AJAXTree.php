<?php
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
	protected function draw_item($item,$level,$first_child=true,$last_child=true) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

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
		$entry = $this->getEntry($item);

		# If the entry doesnt exist, we'll add it.
		if (! $entry) {
			$this->addEntry($item);
			$entry = $this->getEntry($item);
		}

		# If the entry doesnt exist in the server, then return here with an empty string.
		if (! $entry)
			return '';

		# Get our children.
		$child_count = $this->readChildrenNumber($entry->getDN());

		$nb = 0;
		if ($first_child)
			$nb += 1;
		if ($last_child)
			$nb += 2;

		$imgs['expand'] = array('tree_expand.png','tree_expand.png','tree_expand_corner.png',
			($level > 0) ? 'tree_expand_corner.png' : 'tree_expand_corner_first.png');

		$imgs['collapse'] = array('tree_collapse.png','tree_collapse.png','tree_collapse_corner.png',
			($level > 0) ? 'tree_collapse_corner.png' : 'tree_collapse_corner_first.png');

		$imgs['tree'] = array('tree_split.png','tree_split.png','tree_corner.png','tree_corner.png');

		/** Information on array[$nb]
		 * nb == 1 => the node is the first child
		 * nb == 2 => the node is the last child
		 * nb == 3 => the node is the unique child
		 * nb == 0 => the node is a child */
		$new_code = array('1','1','0','0');

		# Links
		$parms['openclose'] = htmlspecialchars(sprintf('server_id=%s&dn=%s&code=%s%s',$this->getServerID(),$entry->getDNEncode(),$code,$new_code[$nb]));
		$parms['edit'] = htmlspecialchars(sprintf('cmd=template_engine&server_id=%s&dn=%s',$this->getServerID(),$entry->getDNEncode()));
		$href = sprintf('cmd.php?%s',$parms['edit']);

		# Each node has a unique id based on dn
		$node_id = sprintf('node%s',base64_encode(sprintf('%s-%s',$server->getIndex(),$entry->getDN())));
		$node_id = str_replace('=','_',$node_id);

		if ($level == 0)
			printf('<tr><td class="spacer"></td><td colspan="%s">',$this->getDepth()+3-1);

		printf('<div id="jt%s" class="treemenudiv">',$node_id);

		echo $this->get_indentation($code);

		if (! $child_count)
			printf('<img id="jt%snode" src="%s/%s" alt="--" class="imgs" style="border: 0px; vertical-align:text-top;" />',$node_id,IMGDIR,$imgs['tree'][$nb]);

		else {
			printf('<a href="#" onclick="return opencloseTreeNode(\'%s\',\'%s\',\'%s\');">',$node_id,$parms['openclose'],IMGDIR);

			if ($entry->isOpened())
				printf('<img id="jt%snode" src="%s/%s" alt="+-" class="imgs" style="border: 0px; vertical-align:text-top;" />',$node_id,IMGDIR,$imgs['collapse'][$nb]);
			else
				printf('<img id="jt%snode" src="%s/%s" alt="+-" class="imgs" style="border: 0px; vertical-align:text-top;" />',$node_id,IMGDIR,$imgs['expand'][$nb]);

			echo '</a>';
		}

		printf('<a href="%s" onclick="return ajDISPLAY(\'BODY\',\'%s\',\'%s\');" title="%s" >',$href,$parms['edit'],_('Retrieving DN'),htmlspecialchars($entry->getDN()));
		printf('<span class="dnicon"><img id="jt%sfolder" src="%s/%s" alt="->" class="imgs" style="border: 0px; vertical-align:text-top;" /></span>',$node_id,IMGDIR,$entry->getIcon($server));
		echo '</a>';

		echo '&nbsp;';
		printf('<a href="%s" onclick="return ajDISPLAY(\'BODY\',\'%s\',\'%s\');" title="%s" class="phplm">',$href,$parms['edit'],_('Retrieving DN'),htmlspecialchars($entry->getDN()));
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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

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
				$this->draw_item($children[$i]->getDN(),$code,$first,$last);
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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$indent = '';

		for ($i=0; $i<strlen($code); $i++) {
			switch ($code[$i]) {
				case '0':
					$indent .= sprintf('<img src="%s/tree_space.png" alt="  " class="imgs" style="border: 0px; vertical-align:text-top;" />',IMGDIR);
					break;

				case '1':
					$indent .= sprintf('<img src="%s/tree_vertline.png" alt="| " class="imgs" style="border: 0px; vertical-align:text-top;" />',IMGDIR);
					break;
			}
		}

		return $indent;
	}

	/**
	 * Draw the javascript to support the tree.
	 */
	protected function draw_javascript() {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		parent::draw_javascript();
		printf('<script type="text/javascript" src="%slayersmenu-browser_detection.js"></script>',JSDIR);
		printf('<script type="text/javascript" src="%sajax_tree.js"></script>',JSDIR);
	}

	/**
	 * Draw the "Create New Entry" item before the children.
	 */
	private function create_before_child($entry,$level) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (strlen($level) == 0)
			return '';

		$server = $this->getServer();
		$output = '';

		if (! $server->isReadOnly() && ! $entry->isLeaf() && (count($entry->getChildren()) > 10) && $this->getServer()->isShowCreateEnabled()
			&& $_SESSION[APPCONFIG]->getValue('appearance','show_top_create'))
			$output = $this->draw_create_new_entry($entry,$level,IMGDIR.'/tree_split.png');

		return $output;
	}

	/**
	 * Draw the "Create New Entry" item after the children.
	 */
	private function create_after_child($entry,$level) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		if (strlen($level) == 0)
			return '';

		$server = $this->getServer();
		$output = '';

		if (! $server->isReadOnly() && ! $entry->isLeaf() && $this->getServer()->isShowCreateEnabled())
			$output = $this->draw_create_new_entry($entry,$level,IMGDIR.'/tree_corner.png');

		return $output;
	}

	/**
	 * Draw the "Create New Entry" item.
	 */
	private function draw_create_new_entry($entry,$level,$img) {
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$output = '';

		$href = sprintf('cmd=template_engine&server_id=%s&container=%s',$this->getServerID(),$entry->getDNEncode());

		$output .= $this->get_indentation($level);
		$output .= sprintf('<img src="%s" alt="--" class="imgs" style="border: 0px; vertical-align:text-top;" />',$img);
		$output .= sprintf('<a href="%s" title="%s">',htmlspecialchars($href),$entry->getDN());
		$output .= sprintf('<img src="%s/create.png" alt="->" class="imgs" style="border: 0px; vertical-align:text-top;" />',IMGDIR);
		$output .= '</a>';
		$output .= '&nbsp;';

		if (isAjaxEnabled())
			$output .= sprintf('<a href="cmd.php?%s" title="%s" class="phplm" onclick="return ajDISPLAY(\'BODY\',\'%s\',\'%s\');">',
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
		if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
			debug_log('Entered (%%)',33,0,__FILE__,__LINE__,__METHOD__,$fargs);

		$result = array();

		foreach ($this->entries as $dn => $value)
			if ($value->isOpened())
				array_push($result,$value->getDN());

		return $result;
	}
}
?>
