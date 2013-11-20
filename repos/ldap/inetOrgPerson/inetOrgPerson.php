<?
class LdapInetOrgPerson extends LdapClass
{
	public function genRequiredFields ($uid, $name, $email, $password, $id)
	{
		$pieces = explode (' ', $name);
		
		do
		{
			$last = trim (array_pop ($pieces));
		} while ($last == '' && sizeof ($pieces));
		
		$first = trim (implode (' ', $pieces));
		
		if ($first == '')
			$first = $uid;
		
		if ($last == '')
			$last = $uid;
		
		if ($name == '')
			$name = $uid;
		
		//throw new Exception (__ ('Your name is invalid! Please, put at least your first and last name in appropriate field. [[1]]', $name));
		
		if (trim ($email) == '')
			throw new Exception (__ ('Your e-mail is invalid! Please, put a valid e-mail in appropriate field. [[1]]', $email));
		
		$array = array ();
		
		$array ['mail'] = $email;
		$array ['givenname'] = removeAccents ($first);
		
		return $array;
	}
}
?>