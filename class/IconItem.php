<?
class IconItem
{
	protected $view = NULL;
	
	protected $id = NULL;
	
	protected $section = NULL;
	
	protected $action = NULL;
	
	protected $label = '';
	
	protected $image = '';
	
	protected $variables = array ();
	
	protected $accessible = TRUE;
	
	public function __construct ($icon, $view = NULL)
	{
		if (!is_null ($view) && is_object ($view) != '')
			$this->view = $view;
		
		if (array_key_exists ('id', $icon) && trim ($icon ['id']) != '')
			$this->id = $icon ['id'];
		
		$this->section = array_key_exists ('section', $icon) ? Business::singleton ()->getSection ($icon ['section']) : Business::singleton ()->getSection (Section::TCURRENT);
		
		$user = User::singleton ();
		
		if (array_key_exists ('action', $icon))
		{
			$this->action =  $this->section->getAction ($icon ['action']);
			
			if (!$user->accessAction ($this->action->getName (), $this->section->getName ()))
				$this->accessible = FALSE;
		}
		else
			$this->action = $this->section->getAction (Action::TDEFAULT);
		
		if (array_key_exists ('label', $icon))
			$this->label = translate ($icon ['label']);
		
		if (array_key_exists ('image', $icon))
			$this->image = $icon ['image'];
		elseif ($this->image == '')
			$this->image = $this->action->getName () .'.gif';
		
		if (array_key_exists ('variable', $icon))
			$this->variables = explode (',', $icon ['variable']);
		
		if (array_key_exists ('restrict', $icon))
		{
			$aux = explode (',', $icon ['restrict']);
			
			foreach ($aux as $trash => $perm)
			{
				if ($user->hasPermission ($perm))
					continue;
				
				$this->accessible = FALSE;
				
				break;
			}
		}
	}
	
	//abstract public function makeIcon ();
	
	//abstract public function makeLink ();
	
	protected function iconImage ($image)
	{
		$section = Business::singleton ()->getSection (Section::TCURRENT);
		
		if (file_exists ($section->getCompPath () .'_icon/'. $image))
			return $section->getCompPath () .'_icon/'. $image;
		
		return Skin::singleton ()->getIconsFolder () . $image;
	}
	
	public function getId ()
	{
		return $this->id;
	}
}
?>