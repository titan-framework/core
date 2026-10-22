<?php
class LdapSambaSamAccount extends LdapClass
{
	public function genRequiredFields ($uid, $name, $email, $password, $id)
	{
		$array = array ();
		
		$array ['sambaLMPassword'] = self::lmHash ($password);
		$array ['sambaNTPassword'] = self::ntHash ($password);
		$array ['sambaSID'] = 'S-1-5-999-'. str_pad ((string) $id, 11, '0', STR_PAD_LEFT);
		
		return $array;
	}
	
	public function genPasswordFields ($uid, $password)
	{
		$array = array ();
		
		$array ['sambaLMPassword'] = self::lmHash ($password);
		$array ['sambaNTPassword'] = self::ntHash ($password);
		
		return $array;
	}
	
	static protected function lmHash ($str)
	{
		$str = strtoupper (substr ($str, 0, 14));
		
		$p1 = self::lmHashDesEncrypt (substr ($str, 0, 7));
		$p2 = self::lmHashDesEncrypt (substr ($str, 7, 7));
		
		return strtoupper ($p1 . $p2);
	}

	/**
	 * Cifra a constante "KGS!@#$%" com DES-ECB usando a chave de 56 bits
	 * derivada de 7 caracteres da senha (algoritmo LM hash do LAN Manager).
	 *
	 * Ate a versao anterior isto usava a extensao mcrypt, removida no PHP 7.2.
	 * Agora usa OpenSSL. Como o DES simples ("des-ecb") esta no provider
	 * "legacy" do OpenSSL 3 (indisponivel por padrao), a cifra e feita com
	 * 3DES ("des-ede3-ecb") usando a mesma chave nas tres rodadas, o que e
	 * matematicamente identico ao DES simples e esta no provider padrao.
	 * A saida foi validada contra o vetor conhecido LM("password") =
	 * E52CAC67419A9A224A3B108F3FA6CB6D nos PHP 7.4, 8.3 e 8.4.
	 */
	static protected function lmHashDesEncrypt ($str)
	{
		$key = array();
		$tmp = array();
		
		$len = strlen ($str);
		
		for ($i = 0; $i < 7; ++$i)
			$tmp [] = $i < $len ? ord ($str [$i]) : 0;
		
		$key[] = $tmp[0] & 254;
		$key[] = ($tmp[0] << 7) | ($tmp[1] >> 1);
		$key[] = ($tmp[1] << 6) | ($tmp[2] >> 2);
		$key[] = ($tmp[2] << 5) | ($tmp[3] >> 3);
		$key[] = ($tmp[3] << 4) | ($tmp[4] >> 4);
		$key[] = ($tmp[4] << 3) | ($tmp[5] >> 5);
		$key[] = ($tmp[5] << 2) | ($tmp[6] >> 6);
		$key[] = $tmp[6] << 1;
		
		$key0 = '';
		
		foreach ($key as $trash => $k)
			$key0 .= chr ($k & 255);
		
		$crypt = self::desEcbEncrypt ($key0, 'KGS!@#$%');
	
		return bin2hex ($crypt);
	}

	/**
	 * DES-ECB de um unico bloco de 8 bytes, sem padding.
	 *
	 * @param string $key   Chave DES de 8 bytes.
	 * @param string $block Bloco de 8 bytes.
	 * @return string Bloco cifrado (8 bytes).
	 * @throws Exception Se nao houver implementacao disponivel.
	 */
	static protected function desEcbEncrypt ($key, $block)
	{
		if (function_exists ('openssl_encrypt'))
		{
			$ciphers = array_map ('strtolower', openssl_get_cipher_methods ());

			$flags = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;

			if (in_array ('des-ede3-ecb', $ciphers))
			{
				$crypt = openssl_encrypt ($block, 'des-ede3-ecb', $key . $key . $key, $flags);

				if ($crypt !== FALSE && strlen ($crypt) == 8)
					return $crypt;
			}

			if (in_array ('des-ecb', $ciphers))
			{
				$crypt = openssl_encrypt ($block, 'des-ecb', $key, $flags);

				if ($crypt !== FALSE && strlen ($crypt) == 8)
					return $crypt;
			}
		}

		if (function_exists ('mcrypt_encrypt'))
			return mcrypt_encrypt (MCRYPT_DES, $key, $block, MCRYPT_MODE_ECB, str_repeat ("\0", 8));

		throw new Exception ('Impossible to generate Samba LM hash: neither OpenSSL (with DES or 3DES in ECB mode) nor mcrypt is available in this PHP build!');
	}
	
	static protected function ntHash ($str)
	{
		$str = iconv ('UTF-8', 'UTF-16LE', $str);
		
		return strtoupper (hash ('md4', $str));
	}
}
?>
