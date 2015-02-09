<?
class CKEditor extends Fck
{
	public function __construct ($table, $field)
	{
		parent::__construct ($table, $field);
	}
	
	public static function getCKEditorPath ()
	{
		return Instance::singleton ()->getCorePath () .'repos/type/global.'. __CLASS__ .'/CKEditor/';
	}
	
	public static function getLanguage ()
	{
		$conversion = array ('pt_BR' => 'pt-br', 'en_US' => 'en', 'es_ES' => 'es');
		
		$language = Localization::singleton ()->getLanguage ();
		
		if (array_key_exists ($language, $conversion))
			return $conversion [$language];
		
		return 'en';
	}
}
?>