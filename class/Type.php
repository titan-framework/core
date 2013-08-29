<?
/**
 * Type.php
 *
 * Base class for Titan types.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage type
 * @copyright Creative Commons Attribution No Derivatives (CC-BY-ND)
 */
abstract class Type
{
	protected $value = NULL;
	
	protected $name	= '';
	
	protected $label = '';
	
	protected $table = '';
	
	protected $assign = '';
	
	protected $required = FALSE;
	
	protected $unique = FALSE;
	
	protected $readOnly = FALSE;
	
	protected $help = '';
	
	protected $tip = '';
	
	protected $restrict = array ();
	
	protected $style = '';
	
	protected $loadable = TRUE;
	
	protected $savable = TRUE;
	
	protected $submittable = TRUE;
	
	protected $bind = FALSE;
	
	protected $bindType = PDO::PARAM_STR;
	
	protected $forGraph = FALSE;
	
	protected $fullWidth = FALSE;
	
	protected $ldap = '';
	
	protected $doc = '';
	
	protected function __construct ($table, $field)
	{
		if (array_key_exists ('name', $field))
			$this->setName ($field ['name']);
		
		if (array_key_exists ('column', $field))
			$this->setName ($field ['column']);
		
		if (array_key_exists ('label', $field))
			$this->setLabel ($field ['label']);
		
		if (array_key_exists ('help', $field))
			$this->setHelp ($field ['help']);
		
		if (array_key_exists ('tip', $field))
			$this->setTip ($field ['tip']);
		
		if (array_key_exists ('table', $field))
			$this->setTable ($field ['table']);
		else
			$this->setTable ($table);
		
		if (array_key_exists ('value', $field))
		{
			$field ['value-default'] = $field ['value'];
			
			$this->setValue ($field ['value']);
		}
		elseif (array_key_exists ('value-default', $field))
			$this->setValue ($field ['value-default']);
		
		if (array_key_exists ('id', $field))
			$this->setAssign ($field ['id']);
		else
			$this->setAssign (str_replace ('.', '__', $this->getTable () .'_'. $this->getName ()));
		
		if (array_key_exists ('on-api-as', $field))
			$this->setApiColumn ($field ['on-api-as']);
		else
			$this->setApiColumn ($this->getColumn ());
		
		if (array_key_exists ('on-ldap-as', $field))
			$this->setLdap ($field ['on-ldap-as']);
		
		if (array_key_exists ('required', $field))
			$this->setRequired (strtoupper ($field ['required']) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('unique', $field))
			$this->setUnique (strtoupper ($field ['unique']) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('read-only', $field))
			$this->setReadOnly (strtoupper ($field ['read-only']) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('style', $field))
			$this->setStyle ($field ['style']);
		
		if (array_key_exists ('restrict', $field))
			$this->setRestrict (explode (',', $field ['restrict']));
		
		if (array_key_exists ('loadable', $field))
			$this->setLoadable (strtoupper ($field ['loadable']) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('savable', $field))
			$this->setSavable (strtoupper ($field ['savable']) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('submittable', $field))
			$this->setSubmittable (strtoupper ($field ['submittable']) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('use-bind', $field))
			$this->setBind (strtoupper ($field ['use-bind']) == 'TRUE' ? TRUE : FALSE);
		
		if (array_key_exists ('doc', $field))
			$this->setDoc ($field ['doc']);
	}
	
	public static final function factory ($table, $array)
	{
		if (!array_key_exists ('type', $array))
			return NULL;
		
		if (!array_key_exists ($array ['type'], Instance::singleton ()->getTypes ()))
			return NULL;
		
		return new $array ['type'] ($table, $array);
	}
	
	public function getAssign ()
	{
		return $this->assign;
	}
	
	public function setAssign ($assign)
	{
		$this->assign = $assign;
	}
	
	public function getValue ()
	{
		return $this->value;
	}
	
	public function setValue ($value)
	{
		$this->value = $value;
	}
	
	public function getName ()
	{
		return $this->name;
	}
	
	public function setName ($name)
	{
		$this->name = $name;
	}
	
	public function getColumn ()
	{
		return $this->getName ();
	}
	
	public function setColumn ($name)
	{
		$this->setName ($name);
	}
	
	public function getApiColumn ()
	{
		return $this->api;
	}
	
	public function setApiColumn ($name)
	{
		$this->api = $name;
	}
	
	public function getLabel ()
	{
		return $this->label;
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
		
		$this->label = $label;
	}
	
	public function getHelp ()
	{
		return $this->help;
	}
	
	public function setHelp ($help)
	{
		$array = explode ('|', $help);

		if (sizeof ($array) > 1)
		{
			$language = Localization::singleton ()->getLanguage ();

			foreach ($array as $key => $value)
			{
				$aux = explode (':', $value);

				if (!$key)
					$help = sizeof ($aux) > 1 ? $aux [1] : $aux [0];

				if ($language != trim ($aux [0]))
					continue;

				$help = trim ($aux [1]);

				break;
			}
		}
		
		$this->help = $help;
	}
	
	public function getTip ()
	{
		return $this->tip;
	}
	
	public function setTip ($tip)
	{
		$array = explode ('|', $tip);

		if (sizeof ($array) > 1)
		{
			$language = Localization::singleton ()->getLanguage ();

			foreach ($array as $key => $value)
			{
				$aux = explode (':', $value);

				if (!$key)
					$tip = sizeof ($aux) > 1 ? $aux [1] : $aux [0];

				if ($language != trim ($aux [0]))
					continue;

				$tip = trim ($aux [1]);

				break;
			}
		}
		
		$this->tip = $tip;
	}
	
	public function getTable ()
	{
		return $this->table;
	}
	
	public function setTable ($table)
	{
		$this->table = $table;
	}
	
	public function setRequired ($required)
	{
		$this->required = (bool) $required;
	}
	
	public function isRequired ()
	{
		return $this->required;
	}
	
	public function setUnique ($unique)
	{
		$this->unique = (bool) $unique;
	}
	
	public function isUnique ()
	{
		return $this->unique;
	}
	
	public function setReadOnly ($readOnly)
	{
		$this->readOnly = (bool) $readOnly;
	}
	
	public function isReadOnly ()
	{
		return $this->readOnly;
	}
	
	public function isEmpty ()
	{
		$value = $this->getValue ();
		
		if (empty ($value) && $value !== FALSE)
			return TRUE;
		
		return FALSE;
	}
	
	public function getStyle ()
	{
		return $this->style;
	}
	
	public function setStyle ($style)
	{
		$this->style = $style;
	}
	
	public function __toString ()
	{
		return (string) Form::toText ($this);
	}
	
	public function setRestrict ($restrict)
	{
		$this->restrict = $restrict;
		
		reset ($this->restrict);
	}
	
	public function getRestrict ()
	{
		$perm = each ($this->restrict);
		
		if ($perm !== FALSE)
			return $perm ['value'];
		
		reset ($this->restrict);
		
		return NULL;
	}
	
	public function isRestrict ()
	{
		return (bool) sizeof ($this->restrict);
	}
	
	public function isLoadable ()
	{
		return $this->loadable;
	}
	
	public function setLoadable ($loadable)
	{
		$this->loadable = (bool) $loadable;
	}
	
	public function isSavable ()
	{
		return $this->savable;
	}
	
	public function setSavable ($savable)
	{
		$this->savable = (bool) $savable;
	}
	
	public function isSubmittable ()
	{
		return $this->submittable;
	}
	
	public function setSubmittable ($submittable)
	{
		$this->submittable = (bool) $submittable;
	}
	
	public function useFullWidth ()
	{
		return $this->fullWidth;
	}
	
	public function setFullWidth ($fullWidth)
	{
		$this->fullWidth = (bool) $fullWidth;
	}
	
	public function getBind ()
	{
		return $this->bind;
	}
	
	public function setBind ($bind)
	{
		$this->bind = (bool) $bind;
	}
	
	public function getBindType ()
	{
		return $this->bindType;
	}
	
	public function setBindType ($type)
	{
		$this->bindType = $type;
	}
	
	public function isLdapField ()
	{
		return trim ($this->ldap) != '';
	}
	
	public function getLdap ()
	{
		return $this->ldap;
	}
	
	public function setLdap ($ldap)
	{
		$this->ldap = $ldap;
	}
	
	public function forGraph ()
	{
		return $this->forGraph;
	}
	
	public function isValid ()
	{
		return TRUE;
	}
	
	public function save ($id = 0)
	{
		return TRUE;
	}
	
	public function load ($id = 0)
	{
		return TRUE;
	}
	
	public function copy ($itemId, $newId)
	{
		return TRUE;
	}
	
	public function setDoc ($doc)
	{
		$this->doc = $doc;
	}
	
	public function getDoc ()
	{
		$array = explode ('|', $this->doc);

		if (sizeof ($array) > 1)
		{
			$language = Localization::singleton ()->getLanguage ();

			foreach ($array as $key => $value)
			{
				$aux = explode (':', $value);

				if (!$key)
					$help = sizeof ($aux) > 1 ? $aux [1] : $aux [0];

				if ($language != trim ($aux [0]))
					continue;

				$this->doc = trim ($aux [1]);

				break;
			}
		}
		
		return $this->doc;
	}
	
	public function genDoc ()
	{
		$default = $this->isEmpty () ? '' : Form::toText ($this);
		
		$array = array ('label' => $this->getLabel (),
						'help' 	=> $this->getHelp (),
						'value' => (!empty ($default) ? __ ('This field contains, by default, the value "**[1]**".', $default) : __ ('This field is, by default, **empty**.')),
						'desc'	=> $this->getDoc (),
						'tip'	=> trim ($this->getTip ()) != '' ? __ ('Tip: [1]', $this->getTip ()) : '');
		
		if ($this->isLdapField ())
			$array ['ldap'] = __ ('This field will **update LDAP data**.');
		
		if ($this->isReadOnly ())
			$array ['readOnly'] = __ ('This field is **read only**.');
		
		if ($this->isRequired ())
			$array ['required'] = __ ('This field is **required**. You need fill value to save form.');
		
		if ($this->isUnique ())
			$array ['unique'] = __ ('The content of this field need be **unique**.');
		
		if ($this->isRestrict ())
			$array ['restrict'] = __ ('Is necessary a special permission to edit this field.');
		
		return $array;
	}
}
?>