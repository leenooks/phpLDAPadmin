<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/AJAXTree.php,v 1.2.2.3 2009/06/28 05:30:12 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 */
class AJAXTree extends PLMTree {
	/**
	 * draw a node of the tree
	 * @param $level a string of 0 and 1 ; $level == "000101" will draw "   | |<node>"
	 * @param $first_child is this the first child ?
	 * @param $last_child is this the last child ?
	 */
	protected function draw_dn($dn,$level,$first_child=true,$last_child=true) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s,%s)',33,__FILE__,__LINE__,__METHOD__,$dn,$level);

		$ldapserver = $this->getLdapServer();

		# level pre-treatment
		$code = '';
		if (is_string($level)) {
			for ($i=0; $i<strlen($level); $i++) {
				if ($level[$i] == '0') {
					$code .= '0';

				} elseif ($level[$i] == '1') {
					$code .= '1';
				}
			}

		} elseif ($level > 0) {
			$code = '0' * $level;
		}
		$level = strlen($code);

		# get entry to display as node
		$entry = $this->getEntry($dn);
		if (! $entry) {
			$this->addEntry($dn);
			$entry = $this->getEntry($dn);
		}

		if (! $entry)
			return '';

		# some informations
		$rdn = $entry->getRdn();
		$encoded_dn = rawurlencode($dn);
		$formatted_dn = $this->get_formatted_dn($entry,$level-1);
		$child_count = $this->get_children_number($entry);

		$nb = 0;
		if ($first_child) $nb += 1;
		if ($last_child) $nb += 2;

		# informations array[$nb]
		# nb == 1 => the node is the first child
		# nb == 2 => the node is the last child
		# nb == 3 => the node is the unique child
		# nb == 0 => the node is a child
		$expand_imgs = array('js/phplayersmenu/menuimages/tree_expand.png', 'js/phplayersmenu/menuimages/tree_expand.png', 'js/phplayersmenu/menuimages/tree_expand_corner.png', ($level > 0) ? 'js/phplayersmenu/menuimages/tree_expand_corner.png' : 'js/phplayersmenu/menuimages/tree_expand_corner_first.png');
		$collapse_imgs = array('js/phplayersmenu/menuimages/tree_collapse.png', 'js/phplayersmenu/menuimages/tree_collapse.png', 'js/phplayersmenu/menuimages/tree_collapse_corner.png', ($level > 0) ? 'js/phplayersmenu/menuimages/tree_collapse_corner.png' : 'js/phplayersmenu/menuimages/tree_collapse_corner_first.png');
		$tree_imgs = array('js/phplayersmenu/menuimages/tree_split.png', 'js/phplayersmenu/menuimages/tree_split.png', 'js/phplayersmenu/menuimages/tree_corner.png', 'js/phplayersmenu/menuimages/tree_corner.png');
		$new_code = array('1', '1', '0', '0');

		# links
		$edit_href_params = htmlspecialchars(sprintf('cmd=template_engine&server_id=%s&dn=%s',$this->server_id,$encoded_dn));
		$edit_href = "cmd.php?$edit_href_params";
		$openclose_params = htmlspecialchars(sprintf('server_id=%s&dn=%s&code=%s',$this->server_id,$encoded_dn,$code.$new_code[$nb]));

		# each node has a unique id based on dn
		$node_id = 'node'.base64_encode($ldapserver->server_id.'-'.$dn);
		$node_id = str_replace('=','_',$node_id);

		if ($level == 0)
			printf('<tr><td class="spacer"></td><td colspan="%s">',$this->getDepth()+3-1);

		printf('<div id="jt%s" class="treemenudiv">',$node_id);
		echo $this->get_indentation($code);

		if ($entry->isOpened()) {
			if (! $child_count) {
				echo '<img align="top" border="0" class="imgs" id="jt'.$node_id.'node" src="'.$tree_imgs[$nb].'" alt="--" />';
			} else {
				echo '<a href="#" onclick="return opencloseTreeNode(\''.$node_id.'\',\''.$openclose_params.'\');">';
				echo '<img align="top" border="0" class="imgs" id="jt'.$node_id.'node" src="'.$collapse_imgs[$nb].'" alt="+-" />';
				echo '</a>';
			}

		} else {
			if (($child_count !== false) && (!$child_count)/* && (!$ldapserver->isShowCreateEnabled())*/) {
				echo '<img align="top" border="0" class="imgs" id="jt'.$node_id.'node" src="'.$tree_imgs[$nb].'" alt="--" />';
			} else {
				echo '<a href="#" onclick="return opencloseTreeNode(\''.$node_id.'\',\''.$openclose_params.'\');">';
				echo '<img align="top" border="0" class="imgs" id="jt'.$node_id.'node" src="'.$expand_imgs[$nb].'" alt="+-" />';
				echo '</a>';
			}
		}

		echo '<a href="'.$edit_href.'" onclick="return displayMainPage(\''.$edit_href_params.'\');" title="'.$dn.'" >';
		printf('<img align="top" border="0" class="imgs" id="jt%sfolder" src="%s/%s" alt="->" />',$node_id,IMGDIR,$entry->getIcon($ldapserver));
		echo '</a>';
		echo '&nbsp;';
		echo '<a href="'.$edit_href.'" onclick="return displayMainPage(\''.$edit_href_params.'\');" title="'.$dn.'" class="phplm">';
		echo $formatted_dn;
		echo ($child_count ? ' ('.$child_count.')' : '');
		echo '</a>';
		echo '</div>';
		echo '<div id="jt'.$node_id.'son" style="display: '.($entry->isOpened() ? 'block' : 'none').'" class="treemenudiv">';
		if ($entry->isOpened()) {
			$this->draw_children($entry,$code.$new_code[$nb]);
		}
		echo '</div>';

		if ($level == 0) {
			echo '</td></tr>';
		}
	}

	public function draw_children($parent_entry,$code) {
		if (DEBUG_ENABLED)
			debug_log('Entered with (%s,%s)',33,__FILE__,__LINE__,__METHOD__,$parent_entry,$code);

		$children = array();
		foreach($parent_entry->getChildren() as $childDn)
			$children[] = $this->getEntry($childDn);

		$first_child = $this->get_plm_before_first_child($parent_entry,$code);
		$last_child = $this->get_plm_after_last_child($parent_entry,$code);

		# If compression is on, we need to compress this output - but only if called by draw_tree_node
		if (function_exists('isCompress') && isCompress() && get_request('cmd','REQUEST') == 'draw_tree_node')
			ob_start();

		echo $first_child;

		for ($i=0; $i<count($children); $i++) {
			$first = ($i == 0) && (! $first_child);
			$last = ($i == (count($children)-1)) && (! $last_child);

			$this->draw_dn($children[$i]->getDn(),$code,$first,$last);
		}

		echo $last_child;

		# If compression is on, we need to compress this output
		if (function_exists('isCompress') && isCompress() && get_request('cmd','REQUEST') == 'draw_tree_node') {
			$output = ob_get_clean();
			echo gzencode($output);
		}
	}

	/**
	 * return the indentation bafore a node
	 * @param $code a string of 0 and 1 ; $code == "000101" will return "   | |"
	 */
	protected function get_indentation($code) {
		$indent = '';
		for ($i=0; $i<strlen($code); $i++) {
			if ($code[$i] == '0') {
				$indent .= '<img align="top" border="0" class="imgs" src="js/phplayersmenu/menuimages/tree_space.png" alt="  " />';

			} elseif ($code[$i] == '1') {
				$indent .= '<img align="top" border="0" class="imgs" src="js/phplayersmenu/menuimages/tree_vertline.png" alt="| " />';
			}
		}

		return $indent;
	}

	protected function draw_javascript() {
		echo '
		<script type="text/javascript" language="javascript" src="js/phplayersmenu/libjs/layersmenu-browser_detection.js"></script>
		<script type="text/javascript" language="javaScript">
		<!--
		var collapsedNodes = new Array();
		var nodeLayer = null;
		var sonLayer = null;
		var oldstyle = \'\';
		var newstyle = \'\';
		var oldimg = \'\';
		var newimg = \'\';
		function readCollapsedNodes() {
			collapsedNodes = new Array();
			cn = document.cookie.split(\'collapsedNodes=\');
			if (cn.length < 2) return;
			vl = cn[1];
			if (vl.indexOf(\';\') != -1) {
				vl = vl.split(\';\');
				vl = vl[0];
			}
			if (vl) {
				collapsed = vl.split(\'|\');
				for (i = 0; i < collapsed.length; i++) {
					collapsedNodes[i] = collapsed[i];
				}
			}
		}
		function writeCollapsedNodes() {
			document.cookie = \'collapsedNodes=\' + collapsedNodes.join(\'|\') + \';path=/\';
		}
		function addCollapsedNode(nodeId) {
			for (i = 0; i < collapsedNodes.length; i++) {
				if (collapsedNodes[i] == nodeId) return;
			}
			collapsedNodes[collapsedNodes.length] = nodeId;
			writeCollapsedNodes();
		}
		function delCollapsedNode(nodeId) {
			newCollapsedNodes = new Array();
			j = 0;
			for (i = 0; i < collapsedNodes.length; i++) {
				if (collapsedNodes[i] != nodeId) {
					newCollapsedNodes[j++] = collapsedNodes[i];
				}
			}
			collapsedNodes = newCollapsedNodes;
			writeCollapsedNodes();
		}
		function updateNewStyle() {
			nodeLayer.src = newimg;
			sonLayer.style.display = newstyle;
		}
		function cancelNewStyle() {
			nodeLayer.src = oldimg;
			sonLayer.style.display = oldstyle;
		}
		function alertTreeNodeContents(html) {
			//alert(html);
			if (html.replace(/(^\s*)|(\s*$)/g, \'\')) {
				includeHTML(sonLayer, html);
			}
			updateNewStyle();
		}
		function opencloseTreeNode(nodeid, params) {
			cancelHttpRequest(); // cancel last request

			// get the node element
			if ((!DOM || Opera56 || Konqueror22) && !IE4) return;
			if (!IE4) {
				sonLayer = document.getElementById(\'jt\' + nodeid + \'son\');
				nodeLayer = document.getElementById(\'jt\' + nodeid + \'node\');
				//folderLayer = document.getElementById(\'jt\' + nodeid + \'folder\');
			} else {
				sonLayer = document.all(\'jt\' + nodeid + \'son\');
				nodeLayer = document.all(\'jt\' + nodeid + \'node\');
				//folderLayer = document.all(\'jt\' + nodeid + \'folder\');
			}
			if (!sonLayer || !nodeLayer) return false;

			// update global variables
			oldstyle = sonLayer.style.display;
			oldimg = nodeLayer.src;
			var action = 0; // (action = 1) => expand ; (action = 2) => collapse
			if (oldimg.indexOf(\'js/phplayersmenu/menuimages/tree_expand.png\') > -1) {
				newimg = \'js/phplayersmenu/menuimages/tree_collapse.png\';
				action = 1;
			} else if (oldimg.indexOf(\'js/phplayersmenu/menuimages/tree_expand_first.png\') > -1) {
				newimg = \'js/phplayersmenu/menuimages/tree_collapse_first.png\';
				action = 1;
			} else if (oldimg.indexOf(\'js/phplayersmenu/menuimages/tree_expand_corner.png\') > -1) {
				newimg = \'js/phplayersmenu/menuimages/tree_collapse_corner.png\';
				action = 1;
			} else if (oldimg.indexOf(\'js/phplayersmenu/menuimages/tree_expand_corner_first.png\') > -1) {
				newimg = \'js/phplayersmenu/menuimages/tree_collapse_corner_first.png\';
				action = 1;
			} else if (oldimg.indexOf(\'js/phplayersmenu/menuimages/tree_collapse.png\') > -1) {
				newimg = \'js/phplayersmenu/menuimages/tree_expand.png\';
				action = 2;
			} else if (oldimg.indexOf(\'js/phplayersmenu/menuimages/tree_collapse_first.png\') > -1) {
				newimg = \'js/phplayersmenu/menuimages/tree_expand_first.png\';
				action = 2;
			} else if (oldimg.indexOf(\'js/phplayersmenu/menuimages/tree_collapse_corner.png\') > -1) {
				newimg = \'js/phplayersmenu/menuimages/tree_expand_corner.png\';
				action = 2;
			} else if (oldimg.indexOf(\'js/phplayersmenu/menuimages/tree_collapse_corner_first.png\') > -1) {
				newimg = \'js/phplayersmenu/menuimages/tree_expand_corner_first.png\';
				action = 2;
			}
			//folderLayer.src = \'js/phplayersmenu/menuimages/tree_folder_open.png\';
			//folderLayer.src = \'js/phplayersmenu/menuimages/tree_folder_closed.png\';
			nodeLayer.src = \''.IMGDIR.'ajax-spinner.gif\';

			// perform action
			if (action == 2) {
				newstyle = \'none\';
				//makeGETRequest(\'cmd.php\', params+\'&cmd=draw_tree_node&action=0\', \'alertTreeNodeContents\', \'cancelNewStyle\');
				updateNewStyle();
				addCollapsedNode(nodeid);
			} else if (action == 1) {
				newstyle = \'block\';
				if (sonLayer.innerHTML == \'\') {
					makeGETRequest(\'cmd.php\', params+\'&cmd=draw_tree_node&action=1\', \'alertTreeNodeContents\', \'cancelNewStyle\');
				} else {
					//makeGETRequest(\'cmd.php\', params+\'&cmd=draw_tree_node&action=2\', \'alertTreeNodeContents\', \'cancelNewStyle\');
					updateNewStyle();
				}
				delCollapsedNode(nodeid);
			}
			return false;
		}
		function getMainPageDiv() {
			if (!IE4) {
				return document.getElementById(\'main_page\');
			} else {
				return document.all(\'main_page\');
			}
		}
		function alertMainPage(html) {
			//alert(html);
			var mainPageDiv = getMainPageDiv();
			if (mainPageDiv) includeHTML(mainPageDiv, html);
		}
		function cancelMainPage() {
			var mainPageDiv = getMainPageDiv();
			if (mainPageDiv) includeHTML(mainPageDiv, \'\');
		}
		function displayMainPage(urlParameters) {
			var mainPageDiv = getMainPageDiv();
			if (mainPageDiv) includeHTML(mainPageDiv, \'<img src="'.IMGDIR.'ajax-progress.gif"><br><small>'._('Retrieving DN').'...<\/small>\');
			makeGETRequest(\'cmd.php\', urlParameters+\'&meth=get_body\', \'alertMainPage\', \'cancelMainPage\');
			return false;
		}

		// close initial collapsed nodes
		readCollapsedNodes();
		for (k = 0; k < collapsedNodes.length; k++) {
			opencloseTreeNode(collapsedNodes[k], \'#\');
		}
		// -->
		</script>';
	}

	protected function get_plm_before_first_child($entry,$level) {
		if (strlen($level) == 0) return '';

		$ldapserver = $this->getLdapServer();
		$output = '';

		if (!$ldapserver->isReadOnly() && ($entry->getChildrenNumber() > 10) && ($ldapserver->isShowCreateEnabled())) {
			$encoded_dn = rawurlencode($entry->getDn());
			$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&container=%s',$ldapserver->server_id,$encoded_dn);

			$output .= $this->get_indentation($level);
			$output .= '<img align="top" border="0" class="imgs" src="js/phplayersmenu/menuimages/tree_split.png" alt="--" />';
			$output .= '<a href="'.htmlspecialchars($href).'" title="'.$entry->getDn().'">';
			$output .= sprintf('<img align="top" border="0" class="imgs" src="%s/star.png" alt="->" />',IMGDIR);
			$output .= '</a>';
			$output .= '&nbsp;';
			$output .= '<a href="'.htmlspecialchars($href).'" title="'._('Create new entry here').'" class="phplm">';
			$output .= _('Create new entry here');
			$output .= '</a>';
		}

		return $output;
	}

	protected function get_plm_after_last_child($entry,$level) {
		if (strlen($level) == 0) return '';

		$ldapserver = $this->getLdapServer();
		$output = '';

		if (!$ldapserver->isReadOnly() && !$entry->isLeaf() && $ldapserver->isShowCreateEnabled()) {
			$encoded_dn = rawurlencode($entry->getDn());
			$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&container=%s',$ldapserver->server_id,$encoded_dn);

			$output .= $this->get_indentation($level);
			$output .= '<img align="top" border="0" class="imgs" src="js/phplayersmenu/menuimages/tree_corner.png" alt="--" />';
			$output .= '<a href="'.htmlspecialchars($href).'" title="'.$entry->getDn().'">';
			$output .= sprintf('<img align="top" border="0" class="imgs" src="%s/star.png" alt="->" />',IMGDIR);
			$output .= '</a>';
			$output .= '&nbsp;';
			$output .= '<a href="'.htmlspecialchars($href).'" title="'._('Create new entry here').'" class="phplm">';
			$output .= _('Create new entry here');
			$output .= '</a>';
		}

		return $output;
	}
}
?>
