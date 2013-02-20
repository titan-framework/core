<?php
/*
  --------------------------------------------------------------------
                           TypeFriendly
              Copyright (c) 2008-2010 Invenzzia Team
                    http://www.invenzzia.org/
                See README for more author details
  --------------------------------------------------------------------
  This file is part of TypeFriendly.
                                                                   
  TypeFriendly is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  TypeFriendly is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with TypeFriendly. If not, see <http://www.gnu.org/licenses/>.
*/
// $Id: project.php 68 2010-01-16 11:11:50Z extremo $

	function walkTrim(&$val)
	{
		$val = trim($val);
	} // end walkTrim();
	
	function array_duplicates($array) 
	{ 
		if(!is_array($array))
		{ 
			return false; 
		} 
		
		$duplicates = array(); 
		
		foreach($array as $key => $val)
		{ 
			end($array); 
			$k = key($array); 
			$v = current($array); 
			
			while($k !== $key)
			{ 
				if($v === $val)
				{ 
					$duplicates[$key] = $v; 
					break; 
				} 
				
				$v = prev($array); 
				$k = key($array); 
			} 
		} 
		
		return $duplicates; 
	} // end array_duplicates();

	class SystemException extends Exception{}

	/**
	 * The class represents an item in the navigation tree.
	 */
	class tfItem
	{
		private $name;
		
		public $iParent = null;
		public $iNext = null;
		public $iPrev = null;
		public $firstChild = null;
		public $lastChild = null;
		
		private $level;
		
		public function __construct($name)
		{
			$this->name = $name;
			$this->level = substr_count($name, '.');
		} // end __construct();
		
		public function getName()
		{
			return $this->name;
		} // end getName();
		
		public function getLevel()
		{
			return $this->level;
		} // end getLevel();
	} // end tfItem;

	/**
	 * Represents a documentation project.
	 */
	class tfProject
	{
		public $fs;
		
		public $config = array();
		public $sortHints = array();
		public $autoLinks = array();
		public $tree;
		
		private $output;
		private $outputObj;
		private $language;
		private $baseLanguage;
	
		private $langs;
		private $prog;
		private $media = array();
		private $pages = array();
		private $parsed = false;

		private $templates = array();
		
		static private $object;

		/**
		 * Loads the project from the specified directory.
		 *
		 * @param String $directory The project directory
		 */
		public function __construct($directory)
		{
			$p = tfParsers::get();
			$this->prog = tfProgram::get();

			$this->fs = new tfFilesystem;
			if(!$this->fs->setMasterDirectory($directory, TF_READ | TF_EXEC))
			{
				throw new SystemException('The project directory: "'.$directory.'" is not accessible');
			}
			
			$this->config = $p->config($this->fs->get('settings.ini'));
			// Some workaround
			if(isset($this->config['outputs']))
			{
				$this->config['outputs'] = explode(',', $this->config['outputs']);
				array_walk($this->config['outputs'], 'walkTrim');
			}
			$baseConfig = array(
				'title' => NULL,
				'version' => NULL,
				'copyright' => NULL,
				'license' => NULL,
				'projectType' => 'manual',
			
				'copyrightLink' => '',
				'licenseLink' => '',
				'navigation' => 'tree',
				'showNumbers' => true,
				'versionControlInfo' => false
			);
			
			foreach($baseConfig as $name => $value)
			{
				if(!isset($this->config[$name]))
				{
					if(is_null($value))
					{
						throw new Exception('The configuration option "'.$name.'" is not defined in this project.');
					}
					$this->config[$name] = $value;
				}
			}

			if($this->config['projectType'] != 'manual' && $this->config['projectType'] != 'documentation' &&
				$this->config['projectType'] != 'article' && $this->config['projectType'] != 'book')
			{
				throw new Exception('Invalid value of the "projectType" option: '.$this->config['value']);
			}
			
			// Base language settings for the documentation interface translator
			$translate = tfTranslate::get();
			$translate->setBaseLanguage($this->config['baseLanguage']);
			$this->baseLanguage = $this->config['baseLanguage'];
			
			// Now we have to check the directory accessibility
			if(!$this->fs->checkDirectories(array(
				'input/' => TF_READ | TF_EXEC,
				'output/' => TF_READ | TF_WRITE,
				'media/' => TF_READ | TF_EXEC
			)))
			{
				throw new Exception('The project does not have the necessary directories.');
			}
			
			// Retrieve the language versions
			$this->langs = $this->fs->listDirectory('input/', false, true);
		} // end __construct();

		/**
		 * Registers the project in the global scope.
		 * @param tfProject $project The project to register.
		 */
		static public function set(tfProject $project)
		{
			self::$object = $project;
		} // end set();

		/**
		 * Returns the project currently registered in the global scope.
		 * @return tfProject
		 */
		static public function get()
		{
			return self::$object;
		} // end get();

		/**
		 * Returns the output object.
		 * @return standardOutput
		 */
		public function getOutput()
		{
			return $this->outputObj;
		} // end getOutput();

		/**
		 * Sets the new project language.
		 * @param String $language The new language abbreviation, i.e. "pl" or "en".
		 */
		public function setLanguage($language)
		{
			if(!in_array($language, $this->langs))
			{
				throw new SystemException('The used language "'.$language.'" is not supported in this project.');
			}

			$translate = tfTranslate::get();
			$translate->setLanguage($language);

			$this->language = $language;
		} // end setLanguage();

		/**
		 * Returns the specified content template.
		 * @param String $template The template name
		 * @throws SystemException
		 * @return String
		 */
		public function getTemplate($template)
		{
			if(!isset($this->templates[$template]))
			{
				try
				{
					$this->templates[$template] = $this->fs->read('input/'.$this->language.'/templates/'.$template.'.txt');
				}
				catch(SystemException $e)
				{
					try
					{
						$this->templates[$template] = $this->fs->read('input/'.$this->baseLanguage.'/templates/'.$template.'.txt');
					}
					catch(SystemException $e)
					{
						throw $e;
					}
				}
			}
			return $this->templates[$template];
		} // end getTemplate();

		/**
		 * Sets the output.
		 * @param standardOutput $output The project output.
		 */
		public function setOutput($output)
		{
			$res = tfResources::get();
			if(!in_array($output, $res->outputs))
			{
				throw new SystemException('The used output "'.$output.'" is not supported by TypeFriendly.');
			}
			
			$this->output = $output;
		} // end setOutput();

		/**
		 * Loads the publication into the memory and organizes it into a tree.
		 */
		public function loadItems()
		{
			/* HOW DOES IT WORK?
			
			All the mystery of the chapter tree sorting algorithm lies in the data organization.
			The main data structure is a 2-dimensional array, which shows, what the children of the
			specified node are. So, in the first level, the index is the name of a chapter, and the
			value is an array of subchapters that are identified by:
			 - Name
			 - Order

			In the first stage, we simply load the list of TXT files from the directory. We sort them in
			order to provide the standard alphanumerical sorting. The structure mentioned above is constructed
			in the stage 2. We iterate through the filelist and explode each item with a dot. By cutting down the
			last path element, we are able to specify the parent of the chapter. Now we do two things:
			 - We create an empty list for the currently processed chapter.
			 - We add the chapter to its parent children list.

			The stage 3 applies the sorting hints from sort_hints.txt file. We load the file and use basically the
			same algorithm, as in stage 2, to process its content. So, now we have two lists:
			 - The first one, sorted alphanumerically
			 - The second one, that uses the sorting hints.

			In the stage 4, we simply connect them, by scanning the first list. We check, whether it figures in the
			second one (that means we have to use hints instead of standard sorting). If yes, we copy the order. Once
			it is completed, we run PHP sort again to apply the order physically.

			Stage 5 creates some meta data for each page, as well as resolves the navigation issue.

			*/
		
			// The tree structure is always built upon the base language directory content.
			$items = $this->fs->listDirectory('input/'.$this->baseLanguage.'/', true, true);

			// Stage 1
			// See, what are the documentation pages, and what - other files.
			$doc = array();
			foreach($items as $item)
			{
				if(($s = strpos($item, '.txt')) !== false)
				{
					if($s == strlen($item) - 4)
					{
						$doc[] = substr($item, 0, $s);
					}
				}
				else
				{
					$this->media[] = $item;
				}
			}
			sort($doc);
			
			// Stage 2
			// Build the standard connections
			$list = array();
			foreach($doc as &$item)
			{
				$extract = explode('.', $item);
				array_pop($extract);
				$parentId = implode('.', $extract);
				if(!isset($list[$parentId]))
				{
					if($parentId != '')
					{
						echo 'fool';
						throw new Exception('The parent of "'.$item.'" does not exist.');
					}
					$list[$parentId] = array(0 => array('id' => $item, 'order' => 0));
				}
				else
				{
					$list[$parentId][] = array('id' => $item, 'order' => sizeof($list[$parentId]));
				}
				if(!isset($list[$item]))
				{
					$list[$item] = array();
				}
			}

			try
			{
				// Stage 3
				// If the hints are not defined, the exception will be thrown and
				// the stages 3 and 4 won't be executed.
				$this->sortHint = $this->fs->readAsArray('sort_hints.txt');

				$sortDuplicates = array_duplicates($this->sortHint);
				
				if(count($sortDuplicates) > 0)
				{
					foreach($sortDuplicates as $duplicate)
					{
						$this->prog->console->stdout->writeln('Duplicates of page "'.$duplicate.'" in sort hints.');
					}
					$this->sortHint = array_values(array_unique($this->sortHint));
				}
				
				$hintedList = array();
				foreach($this->sortHint as &$item)
				{
					$extract = explode('.', $item);
					array_pop($extract);
					$parentId = implode('.', $extract);
					
					$exists = false;
					foreach($list[$parentId] as &$subitem)
					{
						if($subitem['id'] == $item)
						{
							$exists = true;
							break;
						}
					}
					
					if(!$exists)
					{
						$this->prog->console->stdout->writeln('Sort hint for "'.$item.'" does not have existing page.');
						unset($item);
						continue;
					}
	
					if(!isset($hintedList[$parentId]))
					{
						$hintedList[$parentId] = array($item => array('id' => $item, 'order' => 0));
					}
					else
					{
						$hintedList[$parentId][$item] = array('id' => $item, 'order' => sizeof($hintedList[$parentId]));
					}
				}
				
				// Stage 4
				foreach($list as $id => &$item)
				{
					if(isset($hintedList[$id]))
					{
						foreach($item as &$val)
						{
							if(isset($hintedList[$id][$val['id']]))
							{
								$val['order'] = $hintedList[$id][$val['id']]['order'];
							}
							elseif(strlen($id) == 0)
							{
								throw new Exception('Not all base chapters are defined in the sorting hint list. Missing: "'.$val['id'].'". I don\'t know, what to do.');
							}
							else
							{
								throw new Exception('Not all children of "'.$id.'" are defined in the sorting hint list. Missing: "'.$val['id'].'". I don\'t know, what to do.');
							}
						}
						usort($item, array($this, 'orderSort'));
					}
				}
			}
			catch(SystemException $e)
			{
				// Nothing to worry, if the file is not accessible. At least the data won't be sorted.
				// TODO: However, if the debug is active, there must be some kind of message.
			}
			/*
			 * Part 2 - create the meta-data for each page. (stage 5)
			 */

			
			$this->pages = array();
			$parser = tfParsers::get();

			foreach($list as $id => &$sublist)
			{		
				foreach($sublist as $subId => &$item)
				{
					// Try to load the content: first check the current language
					// if does not exist, load the base language file.
					$metaData = array();
					try
					{
						/* If you remove the temporary variable below, you will be killed.
						 * Read the links below and think:
						 *  - http://bugs.php.net/bug.php?id=48408
						 *  - http://bugs.php.net/bug.php?id=48409
						 */
						$tempVariable = $this->fs->get('input/'.$this->language.'/'.$item['id'].'.txt');
						$metaData = $parser->tfdoc($tempVariable);
					}
					catch(SystemException $e)
					{
						$tempVariable = $this->fs->get('input/'.$this->baseLanguage.'/'.$item['id'].'.txt');
						$metaData = $parser->tfdoc($tempVariable);
					}
					
					// Create the additional meta.
					$metaData['Id'] = $item['id'];
					$metaData['Number'] = $item['order'] + 1;
					
					// Create the navigation according to the chapter layout
					$metaData['_Parent'] = $id;
					$metaData['_Previous'] = null;
					$metaData['_Next'] = null; 
					
					if(isset($sublist[$subId-1]))
					{
						$metaData['_Previous'] = $sublist[$subId-1]['Id'];
					}
					if(isset($sublist[$subId+1]))
					{
						$metaData['_Next'] = $sublist[$subId+1]['id'];
					}
					
					if($this->config['navigation'] == 'book')
					{
						// Create a flat navigation, where "Next" can point to the first child, if accessible
						$metaData['_XNext'] = $metaData['_Next'];

						if(isset($this->pages[$metaData['Id']]['_Next']))
						{
							$metaData['_Next'] = $this->pages[$metaData['Id']]['_Next'];
						}
						if(!is_null($metaData['_Previous']))
						{
							$xid = $metaData['_Previous'];
							while(($size = sizeof($list[$xid])) > 0)
							{
								$xid = $list[$xid][$size-1]['id'];
							}
							$metaData['_Previous'] = $xid;
						}
						elseif(is_null($metaData['_Previous']) && $id != '')
						{
							$metaData['_Previous'] = $id;
						}

						if(!is_null($metaData['_Previous']))
						{
							$this->pages[$metaData['_Previous']]['_Next'] = $metaData['Id'];
						}
					}
					$item = $metaData;
					$this->pages[$item['Id']] = &$item;
				}
			}
			
			// Additional stage that adds the numbers
			$queue = new SplQueue;
			foreach($list[''] as &$item)
			{
				$queue->enqueue($item['Id']);
			}
			// Add the numbering.
			$appendixEnum = 'A';
			while($queue->count() > 0)
			{
				$id = $queue->dequeue();
				if(isset($this->pages[$id]['Tags']['Appendix']) && $this->pages[$id]['Tags']['Appendix'])
				{
					$this->pages[$id]['FullNumber'] = ($appendixEnum++);
				}
				else
				{
					if($this->pages[$id]['_Parent'] == '')
					{
						$this->pages[$id]['FullNumber'] = $this->pages[$id]['Number'];
					}
					else
					{
						$this->pages[$id]['FullNumber'] = $this->pages[$this->pages[$id]['_Parent']]['FullNumber'].'.'.$this->pages[$id]['Number'];
					}
				}
				foreach($list[$id] as &$item)
				{
					$queue->enqueue($item['Id']);
				}
			}

			$this->tree = $list;
		} // end loadItems();

		/**
		 * Copies the multimedia files to the output directory.
		 */
		public function copyMedia()
		{
			try
			{
				$this->fs->copyFromVFS($this->prog->fs, 'media/'.$this->output.'/', 'output/'.$this->output.'/');
			}
			catch(SystemException $e){}
			try
			{
				$this->fs->copyItem('input/'.$this->baseLanguage.'/media/', 'output/'.$this->output.'/media/');
			}
			catch(SystemException $e){}
			try
			{
				$this->fs->copyItem('input/'.$this->language.'/media/', 'output/'.$this->output.'/media/');
			}
			catch(SystemException $e){}				
		} // end copyMedia();

		/**
		 * Generates the output document.
		 *
		 * @staticvar standardOutput $lastOutput The last output used.
		 */
		public function generate()
		{
			static $lastOutput = NULL;
			
			$prog = tfProgram::get();
			
			$reparse = false;
			
			if($lastOutput != $this->output)
			{
				$lastOutput = $this->output;
				$reparse = true;
			}
			
			
			$this->fs->safeMkDir('output/'.$this->output, TF_READ | TF_WRITE | TF_EXEC);
			
			$this->fs->cleanUpDirectory('output/'.$this->output);
			
			$this->copyMedia();
			
			$this->outputObj = $out = $this->prog->fs->loadObject('outputs/'.$this->output.'.php', $this->output);
			
			if($reparse)
			{
				$parsers = tfParsers::get();
				
				$refs = array();
				$refTitles = array();
				foreach($this->pages as &$page)
				{
					$refs[$page['Id']] = $this->outputObj->toAddress($page['Id']);
					$refTitles[$page['Id']] = ($this->config['showNumbers'] ? $page['FullNumber'].'. ' : '').(!isset($page['Tags']['ShortTitle']) ? $page['Tags']['Title'] : $page['Tags']['ShortTitle']);
				}
				
				$parsers->getParser()->predef_urls = $refs;
				$parsers->getParser()->predef_titles = $refTitles;
			}
			
			foreach($this->pages as &$page)
			{
				if(!tfTags::validateTags($page['Tags']))
				{
					throw new Exception('Tag validation error in "'.$page['Id'].'": '.PHP_EOL.tfTags::getError());
				}
			}
			
			$out->init($this, 'output/'.$this->output.'/');
			
			foreach($this->pages as &$page)
			{
				if(!$this->parsed)
				{	
					$page['Markdown'] = $page['Content'];
				}
				$parsers->getParser()->fn_id_prefix = str_replace('.', '_', $page['Id']).':';
				$parsers->getParser()->page_id = $page['Id'];
				$page['Content'] = $parsers->parse($page['Markdown']);
				$out->generate($page);
				$prog->console->stderr->write('.');				
			}
			$prog->console->stderr->write(PHP_EOL);	
			$this->parsed = true;
			$out->close();
		} // end generate();

		/**
		 * Compares the base language to the translation.
		 * @param String $secondLanguage The translation language abbreviation, i.e. "pl" or "en"
		 */
		public function versionCompare($secondLanguage)
		{
			if(!in_array($this->config['baseLanguage'], $this->langs))
			{
				throw new SystemException('The used language "'.$this->config['baseLanguage'].'" is not supported in this project.');
			}
			if(!in_array($secondLanguage, $this->langs))
			{
				throw new SystemException('The used language "'.$secondLanguage.'" is not supported in this project.');
			}
			if($secondLanguage == $this->config['baseLanguage'])
			{
				throw new SystemException('Given language is the same as the base language. There is nothing to compare.');
			}
			
			$statBase = $this->fs->getModificationTime('input/'.$this->config['baseLanguage'].'/');
			$statSec = $this->fs->getModificationTime('input/'.$secondLanguage.'/');
			$thirdList = array();
			
			$out = $this->prog->console->stdout;
			
			$out->writeln('Comparing "'.$secondLanguage.'" to the base language: "'.$this->config['baseLanguage'].'".');
			$out->space();
			$out->writeln('Files that are not up-to-date in "'.$secondLanguage.'":');
			$out->space();
			
			foreach($statBase as $name => $time)
			{
				if(isset($statSec[$name]))
				{
					if($time > $statSec[$name])
					{
						$out->writeln('  '.$name);
					}
					unset($statSec[$name]);
				}
				else
				{
					$thirdList[] = $name;
				}
			}
			
			$out->space();
			$out->writeln('Files that do not exist in "'.$secondLanguage.'":');
			$out->space();
			
			foreach($thirdList as $name)
			{
				$out->writeln('  '.$name);
			}

			$out->space();
			$out->writeln('Files that do not exist in "'.$this->config['baseLanguage'].'" (but should, if they are used in "'.$secondLanguage.'"):');
			$out->space();
			
			foreach($statSec as $name => $time)
			{
				$out->writeln('  '.$name);
			}
		} // end versionCompare();

		/**
		 * A helper function for sorting.
		 * @param Array $a Left element
		 * @param Array $b Right element
		 * @return Integer
		 */
		public function orderSort($a, $b)
		{
			return $a['order'] - $b['order'];
		} // end orderSort();

		/**
		 * Gets some meta information about the specified page.
		 * @param String $pageId The page identifier.
		 * @param Boolean $exception Do we throw exceptions if something goes wrong?
		 * @return Array
		 */
		public function getMetaInfo($pageId, $exception = true)
		{
			if(isset($this->pages[$pageId]))
			{
				return $this->pages[$pageId];
			}
			if($exception)
			{
				throw new SystemException('An attemt to access the meta-data of unexisting page: '.$pageId);
			}
			return NULL;
		} // end getMetaInfo();
	} // end tfProject;
