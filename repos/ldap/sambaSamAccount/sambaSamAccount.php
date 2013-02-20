<?
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
	  
		$is = mcrypt_get_iv_size (MCRYPT_DES, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv ($is, MCRYPT_RAND);
		
		$key0 = '';
	  
		foreach ($key as $trash => $k)
			$key0 .= chr($k);
		
		$crypt = mcrypt_encrypt (MCRYPT_DES, $key0, 'KGS!@#$%', MCRYPT_MODE_ECB, $iv);
	
		return bin2hex ($crypt);
	}
	
	static protected function ntHash ($str)
	{
		$str = iconv ('UTF-8', 'UTF-16LE', $str);
		
		return strtoupper (hash ('md4', $str));
	}
}
?>