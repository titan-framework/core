<?
class LdapClass
{
	protected $path = '';
	
	protected $ldap = NULL;
	
	public function __construct (&$ldap, $path, $array)
	{
		$this->path = $path;
		
		$this->ldap = $ldap;
	}
	
	public function getPath ()
	{
		return $this->path;
	}
	
	public function getLdap ()
	{
		return $this->ldap;
	}
	
	public function genRequiredFields ($uid, $name, $email, $password, $id)
	{
		return array ();
	}
	
	public function genPasswordFields ($uid, $password)
	{
		return array ();
	}
}
?>