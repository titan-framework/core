<?
class LdapPosixAccount extends LdapClass
{
	public function genRequiredFields ($uid, $name, $email, $password, $id)
	{
		$array = array ();
		
		$array ['gidNumber'] = $this->getLdap ()->getGroupId ();
		$array ['uidNumber'] = (string) $id;
		$array ['homeDirectory'] = '/home/'. $uid;

		return $array;
	}
}
?>