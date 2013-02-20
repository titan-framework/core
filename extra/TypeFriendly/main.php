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
// $Id: main.php 68 2010-01-16 11:11:50Z extremo $

	class tfMain extends tfApplication
	{
		const VERSION = '0.1.4';
		private $args;

		/**
		 * Parses the application arguments.
		 *
		 * @param tfProgram $prg The program instance.
		 */
		public function parseArgs(tfProgram $prg)
		{		
			$this->args = array(
				'#operation' => array(0 => OPT_REQUIRED, TYPE_STRING),
				'#path' => array(0 => OPT_OPTIONAL, TYPE_PATH),
				'-l' => array(0 => OPT_OPTIONAL, TYPE_STRING),
				'-o' => array(0 => OPT_OPTIONAL, TYPE_STRING),
			);
			try
			{
				$prg->console->testArgs($this->args);

				switch($this->args['#operation'])
				{
					case 'create':
						$this->action = 'create';
						break;
					case 'build':
						$this->action = 'build';
						break;
					case 'compare':
						$this->action = 'compare';
						break;
					case 'version':
						$this->action = 'version';
						break;
					default:
						$this->action = 'main';
				}
			}
			catch(Exception $e)
			{
				$this->action = 'main';
			}
		} // end parseArgs();

		/**
		 * The main action, executed if no parameters were provided.
		 * @param tfProgram $prg
		 */
		public function main(tfProgram $prg)
		{
			$out = $prg->console->stdout;
			
			$out->writeHr('=', 80);
			$out->space();
			$out->center('TypeFriendly', 80);
			$out->center('Documentation building tool', 80);
			$out->center('(c) Invenzzia Group 2008-2010', 80);
			$out->center('www.invenzzia.org', 80);
			$out->space();
			$out->center('This program is free software. You can use, redistribute and/or modify it', 80);
			$out->center('under the terms of GNU General Public License 3 or later. The license', 80);
			$out->center('should be provided within the sources. The program comes with', 80);
			$out->center('ABSOLUTELY NO WARRANTY!', 80);
			$out->space();
			$out->writeHr('=', 80);
			$out->writeln('Usage:');
			$out->writeln('   typefriendly.php ACTION [DOC_PATH] [OPTIONS]');
			$out->space();
			$out->writeln('Actions:');
			$out->writeln('  create      - creates a new documentation from a template under the specified path.');
			$out->writeln('  build       - builds an existing documentation.');
			$out->writeln('  compare     - compares the translations in existing documentation. Use with -l option.');
			$out->writeln('  version     - version information.');
			$out->space();
			$out->writeln('Options:');
			$out->writeln('   -l language - [build, compare] the rendered or compared language');
			$out->writeln('   -o output   - [build] render only the specified output. The output');
			$out->writeln('                 must be declared within the project.');
		} // end main();

		/**
		 * New publication wizard
		 * @param tfProgram $prg
		 */
		public function create(tfProgram $prg)
		{
			$out = $prg->console->stdout;

			if(!isset($this->args['#path']))
			{
				return $this->main($prg);
			}

			if(!is_dir($this->args['#path']))
			{
				$err = $prg->console->stderr;
				$err->writeln('Error: the specified directory does not exist.');
				return;
			}

			$fs = new tfFilesystem();
			if(!$fs->setMasterDirectory($this->args['#path'], TF_READ | TF_WRITE | TF_EXEC))
			{
				$err = $prg->console->stderr;
				$err->writeln('Error: unable to switch to "'.$this->args['#path'].'" - permission denied.');
				return;
			}

			if($fs->containsItems(''))
			{
				$err = $prg->console->stderr;
				$err->writeln('Error: the specified directory is not empty.');
				return;
			}

			$out->writeln('Enter the title:');
			$title = $prg->console->stdin->read(40);
			$out->space();
			$out->writeln('Enter the version:');
			$version = $prg->console->stdin->read(40);
			$out->space();
			$out->writeln('Enter the copyright information:');
			$copyright = $prg->console->stdin->read(100);
			$out->space();
			$out->writeln('Enter the license information:');
			$license = $prg->console->stdin->read(100);
			$out->space();
			$projectType = -1;
			while($projectType < 1 || $projectType > 4)
			{
				$out->writeln('Select the project type:');
				$out->writeln(' (1) Documentation');
				$out->writeln(' (2) User manual');
				$out->writeln(' (3) Book');
				$out->writeln(' (4) Article');
				$out->space();
				$projectType = (int)trim($prg->console->stdin->read(1));
			}

			$projectTypes = array(1 => 'documentation', 'manual', 'book', 'article');

			$out->writeln('Generating files...');

$settings = '; This file was auto-generated by TypeFriendly
title = "'.trim(strtr($title, '\"\r\n', '   ')).'"
version = "'.trim(strtr($version, '\"\r\n', '   ')).'"
copyright = "'.trim(strtr($copyright, '\"\r\n', '   ')).'"
copyrightLink = ""
license = "'.trim(strtr($license, '\"\r\n', '   ')).'"
licenseLink = ""
projectType = "'.$projectTypes[$projectType].'"

outputs = "xhtml, xhtml_single"
baseLanguage = "en"
navigation = "book"
showNumbers = true
versionControlInfo = false
';
			$fs->write('settings.ini', $settings);
			$fs->write('sort_hints.txt', 'preface'.PHP_EOL);
			$fs->safeMkdir('input', TF_READ | TF_WRITE | TF_EXEC);
			$fs->safeMkdir('input/en', TF_READ | TF_WRITE | TF_EXEC);
			$fs->safeMkdir('output', TF_READ | TF_WRITE | TF_EXEC);

			$fs->write('input/en/preface.txt', 'Title: Preface'.PHP_EOL.PHP_EOL.'---'.PHP_EOL.PHP_EOL.'This documentation was auto-generated by TypeFriendly. You can fill it with your content now.'.PHP_EOL);
			$out->writeln('Generation completed.');
		} // end create();

		/**
		 * Builds an output document
		 * @param tfProgram $prg
		 */
		public function build(tfProgram $prg)
		{

			if(!isset($this->args['#path']))
			{
				return $this->main($prg);
			}

			$prg->loadLibrary('parsers');
			$prg->loadLibrary('output');
			$prg->loadLibrary('project');
			$prg->loadLibrary('i18n');
			$prg->loadLibrary('tags');

			$project = new tfProject($this->args['#path']);
			tfProject::set($project);

			// Choose the language
			if(isset($this->args['-l']))
			{
				$project->setLanguage($this->args['-l']);
			}
			else
			{
				$project->setLanguage($project->config['baseLanguage']);
			}
			tfTags::setProject($project);
			try
			{
				if(isset($this->args['-o']))
				{
					$prg->console->stdout->writeln('Processing the files.');
					$project->loadItems();
					$prg->console->stdout->writeln('Starting '.$this->args['-o'].'.');
					$project->setOutput($this->args['-o']);
					$project->generate();       
					$prg->console->stdout->writeln('Generation completed.');
				}
				else
				{
					$prg->console->stdout->writeln('Processing the files.');
					$project->loadItems();
					foreach($project->config['outputs'] as $out)
					{
						$prg->console->stdout->writeln('Starting '.$out.'.');
						$project->setOutput($out);
						$project->generate();
					}
					$prg->console->stdout->writeln('Generation completed.');
				}
			}
			catch(Exception $e)
			{
				$prg->console->stderr->writeln($e->getMessage());
			}
		} // end build();

		/**
		 * Compares the language versions.
		 * @param tfProgram $prg
		 */
		public function compare(tfProgram $prg)
		{
			if(!isset($this->args['-l']))
			{
				return $this->main($prg);
			}

			$prg->loadLibrary('project');
			$prg->loadLibrary('parsers');
			$prg->loadLibrary('i18n');
			$prg->loadLibrary('tags');

			$project = new tfProject($this->args['#path']);
			tfProject::set($project);

			$project->versionCompare($this->args['-l']);
		} // end compare();

		public function version(tfProgram $prg)
		{
			$out = $prg->console->stdout;
			$out->writeln('TypeFriendly '.self::VERSION);
			$out->writeln('(c) Invenzzia Group 2008 - 2010');
		} // end version();
	} // end tfMain;
