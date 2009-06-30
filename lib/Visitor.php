<?php
// $Header

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 *
 * Visit an entry and its attributes and perform a treatment
 * @see EntryReader
 * @see EntryWriter
 */
class Visitor {
	public function __call($method,$args) {
		$class = '';
		$fnct = '';
		$a0 = isset($args[0]) ? $args[0] : '';
		for ($i = 0; $i < strlen($a0); $i++) {
			if ($class) {
				if ($a0[$i] != ':') $fnct .= $a0[$i];
			} else {
				if ($a0[$i] != ':') {
					$fnct .= $a0[$i];
				} else {
					$class = $fnct;
					$fnct = '';
				}
			}
		}

		$obj = isset($args[1]) ? $args[1] : null;
		if (! $obj) {
			if (DEBUG_ENABLED)
				debug_log('null param (%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,$method,$class,$fnct);
			return;
		}

		if (! $class)
			$class = get_class($obj);

		if (DEBUG_ENABLED)
			$c = $class;

		$call = "$method$class$fnct";
		while ($class && !method_exists($this,$call)) {
			$class = get_parent_class($class);
			$call = "$method$class$fnct";
		}

		if ($class) {
			$call .= '($obj';
			for ($i = 2; $i < count($args); $i++) {
				$call .= ',$args['.$i.']';
			}
			$call .= ');';

			eval('$r = $this->'.$call);

			if (isset($r)) return $r;
			else return;

		} elseif (DEBUG_ENABLED) {
			debug_log('Doesnt exist param (%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,$method,$c,$fnct);
		}
	}
}
?>
