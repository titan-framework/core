<?
class Template
{
	public static function import ($template)
	{
		if (file_exists ($template))
			return $template;
		
		if (file_exists (Instance::singleton ()->getReposPath () .'template/'. $template .'.php'))
			return Instance::singleton ()->getReposPath () .'template/'. $template .'.php';
		
		throw new Exception ('The files ['. $template .'] and ['. Instance::singleton ()->getReposPath () .'template/'. $template .'.php] has not located.');
	}
}
?>