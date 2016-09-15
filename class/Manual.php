<?php
class Manual
{
	static private $mapping = array ('en_US' => 'en',
									 'es_ES' => 'en',
									 'pt_BR' => 'pt-BR');
	
	static private $default = array ('title' => '',
									 'version' => '1.0',
									 'copyright' => 'Camilo Carromeu',
									 'copyrightLink' => 'http://www.carromeu.com/',
									 'license' => 'Creative Commons BY-ND 2.5 Brazil License',
									 'licenseLink' => 'http://creativecommons.org/licenses/by-nd/2.5/br/',
									 'projectType' => 'manual',
									 'outputs' => 'xhtml_single',
									 'baseLanguage' => 'en',
									 'navigation' => 'book',
									 'showNumbers' => 'true',
									 'versionControlInfo' => 'false');
	
	static public function generate ()
	{
		$instance = Instance::singleton ();
		
		$languages = array ('en', 'pt-BR');
		
		$path = $instance->getCachePath () .'doc/';
		
		if (!file_exists ($path) && !@mkdir ($path, 0777))
			throw new Exception (__ ('Impossible to create directory! [[1]]', $path));
		
		if (!file_exists ($path .'input') && !@mkdir ($path .'input', 0777))
			throw new Exception (__ ('Impossible to create directory! [[1]]', $path .'input'));
		
		if (!file_exists ($path .'output') && !@mkdir ($path .'output', 0777))
			throw new Exception (__ ('Impossible to create directory! [[1]]', $path .'output'));
			
		$originalLanguage = Localization::singleton ()->getLanguage ();
		
		foreach (self::$mapping as $language => $lang)
		{
			if (!file_exists ($path .'input/'. $lang) && !@mkdir ($path .'input/'. $lang, 0777))
				throw new Exception (__ ('Impossible to create directory! [[1]]', $path .'input/'. $lang));
			
			if (!file_exists ($path .'input/'. $lang .'/media') && !@mkdir ($path .'input/'. $lang .'/media', 0777))
				throw new Exception (__ ('Impossible to create directory! [[1]]', $path .'input/'. $lang .'/media'));
			
			$settings = self::$default;
			
			$settings ['title'] = $instance->getName ();
			
			$pathCustomDoc = Instance::singleton ()->getDocPath ();
			
			if (file_exists ($pathCustomDoc .'settings.ini'))
				copy ($pathCustomDoc .'settings.ini', $path .'settings.ini');
			elseif (!file_exists ($path .'settings.ini'))
				self::makeIniFile ($settings, $path .'settings.ini');
			
			Localization::singleton ()->setLanguage ($language);
			
			Business::reload ();
			
			self::generateSections ($language, $path);
			
			// self::generateTypes ($language, $path);
			
			if (file_exists ($pathCustomDoc .'input/'. self::$mapping [$language] .'/media/'))
				copyDir ($pathCustomDoc .'input/'. self::$mapping [$language] .'/media/', $path .'input/'. self::$mapping [$language] .'/media/');
			
			set_error_handler ('logPhpError');
			
			$errors = array ();
			
			$commands = array ('php', '/usr/bin/php', '/usr/local/bin/php');
			
			foreach ($commands as $trash => $cmd)
			{
				$line = exec ($cmd .' '. $instance->getCorePath () .'extra/TypeFriendly/typefriendly.php build "'. $path .'" -l '. $lang .' -o xhtml_single > '. $path .'log', $output, $return);
			
				if (is_dir ($path .'output/xhtml_single'))
					break;
				
				toLog ('Error on shell command. Last line: ['. $line .']. Output: ['. print_r ($output, TRUE) .']. Return: ['. $return .']. Command:  ['. $cmd .' '. $instance->getCorePath () .'extra/TypeFriendly/typefriendly.php build "'. $path .'" -l '. $lang .' -o xhtml_single > '. $path .'log].');
			}
			
			restore_error_handler ();
			
			if (!is_dir ($path .'output/xhtml_single'))
				throw new Exception ('Impossible to generate manual! View DEBUG LOG for more info.');
			
			removeDir ($path .'output/'. $language);
			
			if (!@rename ($path .'output/xhtml_single', $path .'output/'. $language))
				throw new Exception (__ ('Impossible to rename directory! [[1]] to [[2]]', $path .'output/xhtml_single', $path .'output/'. $language));
		}
		
		Localization::singleton ()->setLanguage ($originalLanguage);
	}
	
	static public function makeIniFile ($array, $path)
	{
		$buffer = array ();
		
		foreach ($array as $key => $value)
			$buffer [] = $key ." = \"". $value ."\"";
		
		if (file_put_contents ($path, "; ". date ('d-m-Y H:i:s') ."\n\n". implode ("\n", $buffer)) === FALSE)
			throw new Exception (__ ('Impossible to create file! [[1]]', $path));
	}
	
	static private function generateSections ($language, $path)
	{
		$sections = self::getSections ();
		
		$chapters = self::getChapters ($sections);
		
		$pathCustomDoc = Instance::singleton ()->getDocPath ();
		
		if (file_exists ($pathCustomDoc .'sort_hints.txt'))
			copy ($pathCustomDoc .'sort_hints.txt', $path .'sort_hints.txt');
		elseif (file_put_contents ($path .'sort_hints.txt', implode ("\n", $chapters)) === FALSE)
			throw new Exception (__ ('Impossible to create file! [[1]]', $path));
		
		foreach ($chapters as $section => $chapter)
			if (file_exists ($pathCustomDoc .'input/'. self::$mapping [$language] .'/'. $chapter .'.txt'))
				copy ($pathCustomDoc .'input/'. self::$mapping [$language] .'/'. $chapter .'.txt', $path .'input/'. self::$mapping [$language] .'/'. $chapter .'.txt');
			else
				self::createChapter ($path, $chapter, $section, $language);
	}
	
	static private function getSections ($father = '')
	{
		$business = Business::singleton ();
		
		$children = $business->getChildren ($father);
		
		$sections = array ();
		foreach ($children as $section => $trash)
			$sections [$section] = self::getSections ($section);
		
		return $sections;
	}
	
	static private function getChapters ($sections, $level = '')
	{
		$chapters = array ();
		foreach ($sections as $section => $sub)
		{
			$chapters [$section] = $level . str_replace ('.', '_', $section);
			
			if (is_array ($sub) && sizeof ($sub))
				$chapters = array_merge ($chapters, self::getChapters ($sub, $level . str_replace ('.', '_', $section) .'.'));
		}
		
		return $chapters;
	}
	
	static private function createChapter ($path, $chapter, $sectionName, $language)
	{
		$originalSection = Business::singleton ()->getSection (Section::TCURRENT);
		$originalAction  = Business::singleton ()->getAction (Action::TCURRENT);
		
		$section = Business::singleton ()->getSection ($sectionName);
		
		$buffer  = "Title: ". $section->getLabel () ."\n\n";
		$buffer .= "---\n\n";
		
		if (trim ($section->getDoc ()) != "")
			$buffer .= $section->getDoc () ."\n\n";
		
		if (trim ($section->getDescription ()) != '')
		{
			$buffer .= "> [information]\n";
			$buffer .= "> ". $section->getDescription () ."\n\n";
		}
		
		while ($action = $section->getAction ())
		{
			Business::singleton ()->setCurrent ($section->getName (), $action->getName ());
			
			$buffer .= "# ". $action->getLabel () ." #\n\n";
			
			$content = $section->getDoc ($action->getName ());
			
			if (trim ($content) != "")
				$buffer .= $content ."\n\n";
			
			if (trim ($action->getDescription ()) != '')
			{
				$buffer .= "> [information]\n";
				$buffer .= "> ". $action->getDescription () ."\n\n<!-- # -->\n\n";
			}
			
			if (trim ($action->getWarning ()) != '')
			{
				$buffer .= "> [warning]\n";
				$buffer .= "> ". $action->getWarning () ."\n\n";
			}
			
			if (sizeof ($action->getMenu ()))
			{
				$buffer .= "### ". __ ('Menu Actions') ." ###\n\n";
				
				$buffer .= "| ". __ ('Menu button') ." | ". __ ('Action by clicking') ." |\n";
				$buffer .= "|-|:-|\n";
				
				$menu = new Menu ($action->getMenu ());
				
				while ($item = $menu->getItem ())
				{
					if (!@copy ($item->getImagePath (), $path .'input/'. self::$mapping [$language] .'/media/'. $item->getImage ()))
						continue;
					
					$buffer .= "| ![". $section->getName () ."_". $action->getName () ."_". $item->getImage () ."](media/". $item->getImage () ." \"". $item->getLabel () ."\") | **". $item->getLabel () ."**: ". $item->getDoc () ." |\n";
				}
			}
			
			try
			{
				$obj = NULL;
				
				try
				{
					$obj = new Form (array ($action->getXmlPath (), $action->getName () .'.xml', $action->getEngine () .'.xml', 'all.xml'));
				}
				catch (Exception $e)
				{
					try
					{
						$obj = new View (array ($action->getXmlPath (), $action->getName () .'.xml', $action->getEngine () .'.xml', 'all.xml'));
					}
					catch (Exception $e)
					{
						throw new Exception ();
					}
				}
				
				if (!is_object ($obj))
					throw new Exception ();
				
				$buffer .= "### ". __ ('This action contains the fowlling fields') .": ###\n\n";
				
				$controlKeys = array ('label', 'desc', 'help');
				
				while ($field = $obj->getField ())
				{
					$doc = $field->genDoc ();
					
					$buffer .= "####". (trim ($doc ['label']) != '' ? $doc ['label'] : __ ('Unlabed Field')) ."####\n\n";
					
					if (array_key_exists ('desc', $doc) && trim ($doc ['desc']) != '')
						$buffer .= ":    ". trim ($doc ['desc']) ."\n\n";
					
					foreach ($doc as $key => $value)
						if (!in_array ($key, $controlKeys) && trim ($value) != '')
							$buffer .= ":    ". trim ($value) ."\n\n";
					
					if (array_key_exists ('help', $doc) && trim ($doc ['help']) != '')
					{
						$buffer .= "> [help]\n";
						$buffer .= "> ". $doc ['help'] ."\n\n";
					}
				}
			}
			catch (Exception $e)
			{}
		}
		
		Business::singleton ()->setCurrent ($originalSection, $originalAction);
		
		if (file_put_contents ($path .'input/'. self::$mapping [$language] .'/'. $chapter .'.txt', $buffer) === FALSE)
			throw new Exception (__ ('Impossible to create file! [[1]]', $path));
	}
	
	static public function isActive ()
	{
		$doc = Instance::singleton ()->getDocPath ();
		
		return !empty ($doc);
	}
}
?>