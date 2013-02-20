<?
class MenuRss extends MenuItem
{
	private $section = NULL;
	
	public function __construct ($input)
	{
		$business = Business::singleton ();
		
		if (isset ($input ['section']) && $business->sectionExists ($input ['section']))
			$this->section = $business->getSection ($input ['section']);
		else
			$this->section = $business->getSection (Section::TCURRENT);
		
		if (isset ($input ['label']) && trim ($input ['label']) != '')
			$this->label = translate ($input ['label']);
		else
			$this->label = __ ('RSS Feed');
		
		if (isset ($input ['image']) && trim ($input ['image']) != '')
			$this->image = $input ['image'];
		else
			$this->image = 'rss.png';
	}
	
	public function getMenuItem ()
	{
		if (Instance::singleton ()->getFriendlyUrl ('rss') == '')
			$link = Instance::singleton ()->getUrl () .'titan.php?target=rss&amp;toSection='. $this->section->getName ();
		else
			$link = Instance::singleton ()->getUrl () . Instance::singleton ()->getFriendlyUrl ('rss') .'/'. $this->section->getName ();
		
		return '<li class="cItemLong" onclick="JavaScript: rssLink (\''. $link .'\');"><img align="left" src="'. self::imageUrl ($this->image) .'" title="'. $this->label .'" alt="'. $this->label .'" />'. $this->label .'</li>';
	}
	
	public function getSubmenuItem ()
	{
		if (Instance::singleton ()->getFriendlyUrl ('rss') == '')
			$link = Instance::singleton ()->getUrl () .'titan.php?target=rss&amp;toSection='. $this->section->getName ();
		else
			$link = Instance::singleton ()->getUrl () . Instance::singleton ()->getFriendlyUrl ('rss') .'/'. $this->section->getName ();
		
		return '<li class="cSubItem" onclick="JavaScript: rssLink (\''. $link .'\');"><div>'. $this->label .'</div></li>';
	}
}
?>