<?
/**
 * Group.php
 *
 * Load user group definitions.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage security
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 * @see Security, User, UserType, AjaxLogon, AjaxPasswd, Ldap
 */
class Group
{
	private $array = array ();
	
	public function __construct ($input)
	{
		if (!is_array ($input))
			throw new Exception ('The input for group mapping is not array!');
		
		$this->array = array (	'id' 	  => 0,
								'label'   => '',
								'display' => 'visible');
		
		if (array_key_exists (0, $input))
			$this->array ['id'] = (int) $input [0];
		
		if (array_key_exists (1, $input))
			$this->setLabel ($input [1]);
		
		if (array_key_exists (2, $input))
			$this->array ['display'] = $input [2];
		
		if (array_key_exists (3, $input))
			$this->setInfo ($input [3]);
	}
	
	public function getId ()
	{
		return $this->array ['id'];
	}
	
	public function setLabel ($label)
	{
		$array = explode ('|', $label);

		if (sizeof ($array) > 1)
		{
			$language = Localization::singleton ()->getLanguage ();

			foreach ($array as $key => $value)
			{
				$aux = explode (':', $value);

				if (!$key)
					$label = sizeof ($aux) > 1 ? $aux [1] : $aux [0];

				if ($language != trim ($aux [0]))
					continue;

				$label = trim ($aux [1]);

				break;
			}
		}
		
		$this->array ['label'] = $label;
	}
	
	public function getInfo ()
	{
		return $this->array ['info'];
	}
	
	public function setInfo ($info)
	{
		$array = explode ('|', $info);

		if (sizeof ($array) > 1)
		{
			$language = Localization::singleton ()->getLanguage ();

			foreach ($array as $key => $value)
			{
				$aux = explode (':', $value);

				if (!$key)
					$info = sizeof ($aux) > 1 ? $aux [1] : $aux [0];

				if ($language != trim ($aux [0]))
					continue;

				$info = trim ($aux [1]);

				break;
			}
		}
		
		$this->array ['info'] = $info;
	}
	
	public function getLabel ()
	{
		return $this->array ['label'];
	}
	
	public function isVisible ()
	{
		return $this->array ['display'] != 'hidden' ? TRUE : FALSE;
	}
}
?>