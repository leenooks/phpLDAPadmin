<?php

namespace App\Classes\LDAP;

use InvalidArgumentException;

/**
 * Encodes/decodes passwords using SSSD's "obfuscated_password" format, ie compatible with
 * sss_obfuscate(8) and ldap_default_authtok_type=obfuscated_password in sssd.conf.
 *
 * This is *not* encryption - the AES key travels in the same buffer as the ciphertext, so
 * anyone with the obfuscated string can trivially recover the password. It only avoids the
 * password appearing as cleartext in configuration.
 *
 * Buffer layout, base64 encoded (see sssd/src/util/crypto/libcrypto/crypto_obfuscate.c):
 *   uint16 method (0 = AES-256-CBC)
 *   uint16 ciphertext length
 *   32 bytes key
 *   16 bytes IV
 *   ciphertext bytes (PKCS7 padded encryption of the password plus a trailing NUL)
 *   4 byte sentinel: \x00\x01\x02\x03
 */
final class SSSDPassword
{
	private const METHOD_AES_256 = 0;
	private const CIPHER = 'aes-256-cbc';
	private const KEY_LEN = 32;
	private const IV_LEN = 16;
	private const SENTINEL = "\x00\x01\x02\x03";

	public static function obfuscate(string $password): string
	{
		$key = random_bytes(self::KEY_LEN);
		$iv = random_bytes(self::IV_LEN);

		$ciphertext = openssl_encrypt($password."\x00",self::CIPHER,$key,OPENSSL_RAW_DATA,$iv);

		return base64_encode(
			pack('vv',self::METHOD_AES_256,strlen($ciphertext))
			.$key
			.$iv
			.$ciphertext
			.self::SENTINEL
		);
	}

	public static function deobfuscate(string $encoded): string
	{
		$buffer = base64_decode($encoded,true);

		if ($buffer === false || strlen($buffer) < 4)
			throw new InvalidArgumentException('Invalid obfuscated password: not a valid base64 buffer.');

		['method'=>$method,'ctsize'=>$ctsize] = unpack('vmethod/vctsize',substr($buffer,0,4));

		if ($method !== self::METHOD_AES_256)
			throw new InvalidArgumentException(sprintf('Invalid obfuscated password: unsupported method [%d].',$method));

		if (strlen($buffer) !== 4+self::KEY_LEN+self::IV_LEN+$ctsize+strlen(self::SENTINEL))
			throw new InvalidArgumentException('Invalid obfuscated password: unexpected buffer length.');

		$p = 4;
		$key = substr($buffer,$p,self::KEY_LEN); $p += self::KEY_LEN;
		$iv = substr($buffer,$p,self::IV_LEN); $p += self::IV_LEN;
		$ciphertext = substr($buffer,$p,$ctsize); $p += $ctsize;

		if (substr($buffer,$p,strlen(self::SENTINEL)) !== self::SENTINEL)
			throw new InvalidArgumentException('Invalid obfuscated password: sentinel mismatch, buffer may be corrupt.');

		$plaintext = openssl_decrypt($ciphertext,self::CIPHER,$key,OPENSSL_RAW_DATA,$iv);

		if ($plaintext === false)
			throw new InvalidArgumentException('Invalid obfuscated password: unable to decrypt buffer.');

		// The plaintext was encrypted with a trailing NUL terminator (C string).
		$nul = strpos($plaintext,"\x00");

		return $nul === false ? $plaintext : substr($plaintext,0,$nul);
	}
}
