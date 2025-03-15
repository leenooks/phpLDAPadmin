<?php

namespace App\Classes\LDAP\Export;

use Illuminate\Support\Str;

use App\Classes\LDAP\Export;

/**
 * Export from LDAP using an LDIF format
 */
class LDIF extends Export
{
	// The maximum length of the ldif line
	private int $line_length = 76;
	protected const type = 'LDIF Export';

	public function __toString(): string
	{
		$result = parent::header();
		$result .= 'version: 1';
		$result .= $this->br;

		$c = 1;
		foreach ($this->items as $o) {
			if ($c > 1)
				$result .= $this->br;

			$title = (string)$o;
			if (strlen($title) > $this->line_length)
				$title = Str::of($title)->limit($this->line_length-3-5,'...'.substr($title,-5));

			$result .= sprintf('# %s %s: %s',__('Entry'),$c++,$title).$this->br;

			// Display DN
			$result .= $this->multiLineDisplay(
				Str::isAscii($o)
					? sprintf('dn: %s',$o)
					: sprintf('dn:: %s',base64_encode($o))
				,$this->br);

			// Display Attributes
			foreach ($o->getObjects() as $ao) {
				foreach ($ao->values as $tag => $tagvalues) {
					foreach ($tagvalues as $value) {
						$result .= $this->multiLineDisplay(
							Str::isAscii($value)
								? sprintf('%s: %s',$ao->name.($tag ? ';'.$tag : ''),$value)
								: sprintf('%s:: %s',$ao->name.($tag ? ';'.$tag : ''),base64_encode($value))
						,$this->br);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Helper method to wrap LDIF lines
	 *
	 * @param string $str The line to be wrapped if needed.
	 */
	private function multiLineDisplay(string $str,string $br): string
	{
		$length_string = strlen($str);
		$length_max = $this->line_length;

		$output = '';
		while ($length_string > $length_max) {
			$output .= substr($str,0,$length_max).$br;
			$str = ' '.substr($str,$length_max);
			$length_string = strlen($str);
		}

		$output .= $str.$br;

		return $output;
	}
}