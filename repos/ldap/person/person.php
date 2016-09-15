<?php
class LdapPerson extends LdapClass
{
	public function genRequiredFields ($uid, $name, $email, $password, $id)
	{
		$pieces = explode (' ', $name);
		
		do
		{
			$last = trim (array_pop ($pieces));
		} while ($last == '' && sizeof ($pieces));
		
		$first = trim (implode (' ', $pieces));
		
		if ($first == '' || $last == '')
			throw new Exception (__ ('Your name is invalid! Please, put at least your first and last name in appropriate field. [[1]]', $name));
		
		if (trim ($password) == '')
			throw new Exception (__ ('Your password is invalid! Please, put a valid password in appropriate field.'));
		
		$array = array ();
		
		$array ['cn'] = removeAccents ($name);
		$array ['sn'] = removeAccents ($last);
		$array ['userpassword'] = $this->getLdap ()->encryptPassword ($password);
		
		return $array;
	}
	
	public function genPasswordFields ($uid, $password)
	{
		return array ('userpassword' => $this->getLdap ()->encryptPassword ($password));
	}
}
?>