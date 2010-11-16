<?php
/*******************************************************************************
 * @package default
 * emuhash - partly emulates the php mhash functions
 * version: 2004040701
 *
 * (c) 2004 - Simon Matter <simon.matter@invoca.ch>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ******************************************************************************/

/******************************************************************************/
/* Do we have builtin mhash support in this PHP version ?                     */
/******************************************************************************/

if (! function_exists('mhash') && ! function_exists('mhash_keygen_s2k')) {
	$emuhash_emu = array();

	if (! isset($emuhash_emu['openssl']))
		$emuhash_emu['openssl'] = '/usr/bin/openssl';

	# Don't create mhash functions if we don't have a working openssl
	if (! file_exists($emuhash_emu['openssl']))
		unset($emuhash_emu['openssl']);

	elseif (function_exists('is_executable') && ! is_executable($emuhash_emu['openssl']))
		unset($emuhash_emu['openssl']);

	else {
		if (! isset($emuhash_emu['tmpdir']))
			$emuhash_emu['tmpdir'] = '/tmp';

/******************************************************************************/
/* Define constants used in the mhash emulation code.                         */
/******************************************************************************/

		define('MHASH_MD5','md5');
		define('MHASH_SHA1','sha1');
		define('MHASH_RIPEMD160','rmd160');

/******************************************************************************/
/* Functions to emulate parts of php-mash.                                    */
/******************************************************************************/

		function openssl_hash($openssl_hash_id,$password_clear) {
			global $emuhash_emu;

			if (PHP_VERSION < 6) {
				$current_magic_quotes = @get_magic_quotes_runtime();
				@set_magic_quotes_runtime(0);
			}

			$tmpfile = tempnam($emuhash_emu['tmpdir'],'emuhash');
			$pwhandle = fopen($tmpfile,'w');

			if (! $pwhandle)
				error(sprintf('Unable to create a temporary file %s to create hashed password',$tmpfile) ,'error','index.php');

			fwrite($pwhandle,$password_clear);
			fclose($pwhandle);
			$cmd = sprintf('%s %s -binary <%s',$emuhash_emu['openssl'],$openssl_hash_id,$tmpfile);
			$prog = popen($cmd,'r');
			$pass = fread($prog,1024);
			pclose($prog);
			unlink($tmpfile);

			if (PHP_VERSION < 6)
				@set_magic_quotes_runtime($current_magic_quotes);

			return $pass;
		}

		function mhash($hash_id,$password_clear) {
			switch($hash_id) {
				case MHASH_MD5:
					$emuhash = openssl_hash(MHASH_MD5,$password_clear);
					break;

				case MHASH_SHA1:
					$emuhash = openssl_hash(MHASH_SHA1,$password_clear);
					break;

				case MHASH_RIPEMD160:
					$emuhash = openssl_hash(MHASH_RIPEMD160,$password_clear);
					break;

				default:
					$emuhash = FALSE;
			}

			return $emuhash;
		}

		function mhash_keygen_s2k($hash_id,$password_clear,$salt,$bytes) {
			return substr(pack('H*',bin2hex(mhash($hash_id,($salt.$password_clear)))),0,$bytes);
		}
	}
}
?>
