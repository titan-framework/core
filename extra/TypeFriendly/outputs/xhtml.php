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
// $Id: xhtml.php 70 2010-05-02 07:04:23Z zyxist $

	class xhtml extends standardOutput
	{
		protected $date = '';
		protected $translate = null;

		protected $_tagVersion = array();
		protected $_currentPage = null;

		/**
		 * Initializes the generation, creating the index.html file with the
		 * table of contents.
		 * @param tfProject $project The project
		 * @param String $path Output path
		 */
		public function init($project, $path)
		{		
			$this->translate = $translate = tfTranslate::get();
			$this->date = date('d.m.Y');
			
			$this->project = $project;
			$this->path = $path;
			// Generating TOC.
			$code = $this->createHeader($translate->_('general','table_of_contents'), array());
			
			$code .= '<h1>'.$this->project->config['title'].' '.$this->project->config['version'].'</h1>';
			
			$code .= '<div class="tf_reference"><table>';
			$code .= '<tr><td><strong>Copyright &copy; '.$this->project->config['copyright'].'</strong></td></tr>';
			$code .= '<tr><td>'.$translate->_('general','doc_license',$this->project->config['license']).'</td></tr>';
			$code .= '<tr><td>'.$translate->_('general','generated_in',$this->date).'</td></tr>';
			$code .= '</table><hr/></div>';
			
			$code .= $this->menuGen('', true, true);
			
			$code .= $this->createFooter();
			
			$this->project->fs->write($this->path.'index.html', $code);			
		} // end init();

		/**
		 * Generates a single page and saves it on the disk.
		 *
		 * @param Array $page The page meta-info.
		 */
		public function generate($page)
		{
			tfTags::setTagList($page['Tags']);
			$nav = array();
			$this->_currentPage = $page;
			
			$nav[$page['Id']] = $page['Tags']['ShortTitle'];
			
			$parent = $page['_Parent']; 
						
			do
			{
				$parent = $this->project->getMetaInfo($parent, false);
				if(!is_null($parent))
				{
					$nav[$parent['Id']] = $parent['Tags']['ShortTitle'];
					$parent = $parent['_Parent']; 
				}
			}
			while(!is_null($parent));
			
			$nav = array_reverse($nav, true);
			
			/*if($this->project->config['showNumbers'])
			{			
				$code = $this->createHeader($page['FullNumber'].'. '.$page['Tags']['Title'], $nav);
			}
			else*/
			{
				$code = $this->createHeader($page['Tags']['Title'], $nav);
			}
			
			$code .= $this->createTopNavigator($page);
			
			$subtitle = '';
			if(isset($page['Tags']['Appendix']) && $page['Tags']['Appendix'])
			{
				$subtitle = $this->translate->_('tags', 'appendix').' ';
				if(!$this->project->config['showNumbers'])
				{
					$subtitle = trim($subtitle).': ';
				}
			}
			
			if($this->project->config['showNumbers'])
			{
				$code .= '<h1>'.$subtitle.$page['FullNumber'].'. '.$page['Tags']['Title'].'</h1>';
			}
			else
			{
				$code .= '<h1>'.$subtitle.$page['Tags']['Title'].'</h1>';
			}
			
			$code .= $this->menuGen($page['Id'], false, true);

			$this->_tagVersion = array();

			$reference =
				tfTags::orderProcessTag('General', 'Author', $this).
				tfTags::orderProcessTag('Status', 'Status', $this).
				tfTags::orderProcessTags('Programming', $this).
				tfTags::orderProcessTags('Behaviour', $this).
				tfTags::orderProcessTags('VersionControl', $this);
			
			if(sizeof($this->_tagVersion) > 0)
			{
				$reference .= '<tr><th>'.$this->translate->_('tags','versions').'</th><td>';
				if(isset($this->_tagVersion['since']))
				{
					$reference .= $this->translate->_('general', 'period_since').' <code>'.$this->_tagVersion['since'].'</code>';
				}
				if(isset($this->_tagVersion['to']))
				{
					$reference .= ' '.$this->translate->_('general', 'period_to').' <code>'.$this->_tagVersion['to'].'</code>';
				}
				$reference .= '</td></tr>'.PHP_EOL;
			}

			if($reference != '')
			{
				$code .= '<div class="tf_reference"><table>'.$reference.'</table><hr/></div>';
			}
			
			$code .= tfTags::orderProcessTag('General', 'FeatureInformationFrame', $this);
			$code .= $page['Content'];
			$code .= tfTags::orderProcessTag('Navigation', 'SeeAlso', $this);
			
			$code .= $this->createBottomNavigator($page);
			$code .= $this->createFooter();          
			$this->project->fs->write($this->path.$page['Id'].'.html', $code);
		} // end generate();

		/**
		 * Closes the parsing - unused.
		 */
		public function close()
		{
		
		} // end close();

		/**
		 * Internal method that generates a common header for all the pages
		 * and returns the source code.
		 *
		 * @param String $title The page title.
		 * @param Array $nav The navigation list.
		 * @return String
		 */
		public function createHeader($title, Array $nav)
		{
			$translate = tfTranslate::get();
			$docTitle = $this->project->config['title'];
			$docVersion = $this->project->config['version']; 
			
			$textDocumentation = $translate->_('general', $this->project->config['projectType']);
$code = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="pl">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="all" />

	<title>{$title} - {$docTitle}</title>
	
	<link rel="stylesheet" type="text/css" href="design/generic.css" media="all"  />
	<link rel="stylesheet" type="text/css" href="design/print.css" media="print" />
	<!--[if lte IE 6]><link rel="stylesheet" href="design/ie.css" type="text/css" /><![endif]-->	
	<!--[if IE 7]><link rel="stylesheet" href="design/ie7.css" type="text/css" /><![endif]-->
</head>
<body>

<div id="wrap">
	<div id="header">
		<h1>{$docTitle} {$docVersion}</h1>
		<h2>{$title}</h2>
		<p class="generated">@ {$this->date}</p>
		<p class="location"><a href="index.html"><strong>{$textDocumentation}</strong></a>
EOF;
		foreach($nav as $id => $title)
		{
			$code .= ' &raquo; <a href="'.$id.'.html">'.$title.'</a>';
		}
$code .= <<<EOF
</p>
	</div>
	
	<div id="content">
EOF;
			return $code;
		} // end createHeader();

		/**
		 * Creates a common footer for each page.
		 *
		 * @return String
		 */
		public function createFooter()
		{
			$translate = tfTranslate::get();		

			if(strlen($this->project->config['copyrightLink']) > 0)
			{
				$copyright = '<a href="'.$this->project->config['copyrightLink'].'">'.$this->project->config['copyright'].'</a>';
			}
			else
			{
				$copyright = $this->project->config['copyright'];
			}
			if(strlen($this->project->config['licenseLink']) > 0)
			{
				$license = '<a href="'.$this->project->config['licenseLink'].'">'.$this->project->config['license'].'</a>';
			}
			else
			{
				$license = $this->project->config['license'];
			}
			
			$textLicense = $translate->_('general','doc_license',$license);
			
			$version = tfMain::VERSION;
$code = <<<EOF
	</div>
	
	<div id="footer">
		<p>Copyright &copy; {$copyright}</p>
		<p>{$textLicense}</p>
		<p>Generated by <strong>TypeFriendly {$version}</strong> by <a href="http://www.invenzzia.org/">Invenzzia</a></p>
	</div>
</div>

</body>
</html>
EOF;
			return $code;
		} // end createFooter();

		/**
		 * Creates the navigation above the page contents.
		 *
		 * @param Array &$page The page meta-info.
		 * @return String
		 */
		public function createTopNavigator(&$page)
		{
			$n =& $this->project->config['showNumbers'];
			
			$translate = tfTranslate::get();
			$parent = $this->project->getMetaInfo($page['_Parent'], false);
			$prev = $this->project->getMetaInfo($page['_Previous'], false);
			$next = $this->project->getMetaInfo($page['_Next'], false);
			$code = '<dl class="location">';
			if(!is_null($parent))
			{
				$code .= '<dt><a href="'.$parent['Id'].'.html">'.($n ? $parent['FullNumber'].'. ' : '').$parent['Tags']['Title'].'</a><br/>'.($n ? $page['FullNumber'].'. ' : '').$page['Tags']['Title'].'</dt>';
			}
			else
			{
				$code .= '<dt><a href="index.html">'.$translate->_('general','table_of_contents').'</a><br/>'.($n ? $page['FullNumber'].'. ' : '').$page['Tags']['Title'].'</dt>';
			}
			if(!is_null($prev))
			{
				$code .= '<dd class="prev">'.($n ? $prev['FullNumber'].'. ' : '').$prev['Tags']['Title'].'<br/><a href="'.$prev['Id'].'.html">&laquo; '.$translate->_('navigation','prev').'</a></dd>';
			}
			if(!is_null($next))
			{
				$code .= '<dd class="next">'.($n ? $next['FullNumber'].'. ' : '').$next['Tags']['Title'].'<br/><a href="'.$next['Id'].'.html">'.$translate->_('navigation','next').' &raquo;</a></dd>';
			}
			$code .= '</dl>	';
			return $code;
		} // end createTopNavigator();

		/**
		 * Creates the navigation below the page contents.
		 *
		 * @param Array &$page The page meta-info.
		 * @return String
		 */
		public function createBottomNavigator(&$page)
		{
			$n =& $this->project->config['showNumbers'];
			
			$translate = tfTranslate::get();
			$parent = $this->project->getMetaInfo($page['_Parent'], false);
			$prev = $this->project->getMetaInfo($page['_Previous'], false);
			$next = $this->project->getMetaInfo($page['_Next'], false);
			$code = '<dl class="location location-bottom">';
			if(!is_null($parent))
			{
				$code .= '<dt>'.($n ? $page['FullNumber'].'. ' : '').$page['Tags']['Title'].'<br/><a href="'.$parent['Id'].'.html">'.($n ? $parent['FullNumber'].'. ' : '').$parent['Tags']['Title'].'</a></dt>';
			}
			else
			{
				$code .= '<dt>'.($n ? $page['FullNumber'].'. ' : '').$page['Tags']['Title'].'<br/><a href="index.html">'.$translate->_('general','table_of_contents').'</a></dt>';
			}
			if(!is_null($prev))
			{
				$code .= '<dd class="prev"><a href="'.$prev['Id'].'.html">&laquo; '.$translate->_('navigation','prev').'</a><br/>'.($n ? $prev['FullNumber'].'. ' : '').$prev['Tags']['Title'].'</dd>';
			}
			if(!is_null($next))
			{
				$code .= '<dd class="next"><a href="'.$next['Id'].'.html">'.$translate->_('navigation','next').' &raquo;</a><br/>'.($n ? $next['FullNumber'].'. ' : '').$next['Tags']['Title'].'</dd>';
			}
			$code .= '</dl>	';
			return $code;
		} // end createBottomNavigator();

		/**
		 * Generates a menu.
		 *
		 * @param String $what The root page.
		 * @param Boolean $recursive Do we need a recursive tree?
		 * @param Boolean $start Do we include the "Table of contents" text?
		 * @return String
		 */
		public function menuGen($what, $recursive = true, $start = false)
		{
			$n =& $this->project->config['showNumbers'];
			
			$translate = tfTranslate::get();
			$code = '';
			if($start)
			{
				$code .= '<h4>'.$translate->_('general','table_of_contents').'</h4>';
			}
			if(isset($this->project->tree[$what]) && count($this->project->tree[$what]) > 0)
			{
				$code .= '<ul class="toc">';
				foreach($this->project->tree[$what] as $item)
				{
					if($recursive)
					{
						$code .= '<li><a href="'.$item['Id'].'.html">'.($n ? $item['FullNumber'].'. ' : '').$item['Tags']['Title'].'</a>'.$this->menuGen($item['Id'], true).'</li>';
					}
					else
					{
						$code .= '<li><a href="'.$item['Id'].'.html">'.($n ? $item['FullNumber'].'. ' : '').$item['Tags']['Title'].'</a></li>';
					}
				}
				$code .= '</ul>';
				return $code;
			}
			return '';
		} // end menuGen();

		/**
		 * Converts the page identifier to the URL.
		 *
		 * @param String $page The page identifier.
		 * @return String
		 */
		public function toAddress($page)
		{
			return $page.'.html';
		} // end toAddress();

		/**
		 * Creates "See also" links below the page content.
		 *
		 * @param Array $standard The links within the documentation
		 * @param Array $external The external SeeAlso links
		 * @return String
		 */
		public function _tagSeeAlso($standard, $external)
		{
			$n =& $this->project->config['showNumbers'];

			$translate = tfTranslate::get();
			$prog = tfProgram::get();
			$i = 0;
			
			$code = '<h4>'.$this->translate->_('navigation','see_also').':</h4><ul>';
			if(!is_null($standard))
			{
				foreach($standard as $value)
				{
					$meta = $this->project->getMetaInfo($value, false);
					if(is_null($meta))
					{
						$prog->console->stderr->writeln('The page "'.$value.'" linked in See Also of "'.$this->_currentPage['Id'].'" does not exist.');
					}
					else
					{
						$code .= '<li><a href="'.$meta['Id'].'.html">'.($n ? $meta['FullNumber'].'. ' : '').$meta['Tags']['ShortTitle'].'</a></li>';
						$i++;
					}
				}
			}
			if(!is_null($external))
			{
				foreach($external as $value)
				{
					if(($sep = strpos($value, ' ')) !== false)
					{
						$code .= '<li><a href="'.substr($value, 0, $sep).'">'.substr($value, $sep).'</a></li>';
						$i++;
					}
					else
					{
						$code .= '<li><a href="'.$value.'">'.$value.'</a></li>';
						$i++;
					}
				}
			}
			$code .= '</ul>';

			if($i == 0)
			{
				return '';
			}

			return $code;
		} // end _tagSeeAlso();

		/**
		 * Handles "Author" tag.
		 *
		 * @param String $value The tag value
		 * @return String
		 */
		public function _tagAuthor($value)
		{
			return '<tr><th>'.$this->translate->_('tags','author').'</th><td>'.$value.'</td></tr>';
		} // end _tagAuthor();

		/**
		 * Handles "Status" tag.
		 *
		 * @param String $value The tag value
		 * @return String
		 */
		public function _tagStatus($value)
		{
			return '<tr><th>'.$this->translate->_('tags','status').'</th><td>'.$value.'</td></tr>';
		} // end _tagStatus();

		/**
		 * Handles "VCSKeywords" tag.
		 *
		 * @param String $val The value to be displayed.
		 * @return String
		 */
		public function _tagVCSKeywords($val)
		{
			if($this->project->config['versionControlInfo'])
			{
				return '<tr><th>'.$this->translate->_('tags','version_control_info').'</th><td><code>'.$val.'</code></td></tr>';
			}
			return '';
		} // end _tagVCSKeywords();

		/**
		 * Handles "VersionSince" tag.
		 *
		 * @param String $val The value to be displayed.
		 * @return String
		 */
		public function _tagVersionSince($val)
		{
			$this->_tagVersion['since'] = $val;
			return '';
		} // end _tagVersionSince();

		/**
		 * Handles "VersionTo" tag.
		 *
		 * @param String $val The value to be displayed.
		 * @return String
		 */
		public function _tagVersionTo($val)
		{
			$this->_tagVersion['to'] = $val;
			return '';
		} // end _tagVersionTo();

		/**
		 * Handles "FeatureInformation" tag.
		 *
		 * @param String $val The parsed value to be displayed.
		 * @return String
		 */
		public function _tagFeatureInformationFrame($val)
		{
			return $val;
		} // end _tagImplements();

		/**
		 * Handles "Construct" tag.
		 *
		 * @param String $val The value to be displayed.
		 * @return String
		 */
		public function _tagConstruct($val)
		{
			return '<tr><th>'.$this->translate->_('tags','construct').'</th><td>'.$val.'</td></tr>';
		} // end _tagConstruct();

		/**
		 * Handles "Visibility" tag.
		 *
		 * @param String $value The tag value
		 * @return String
		 */
		public function _tagVisibility($value)
		{
			return '<tr><th>'.$this->translate->_('tags','visibility').'</th><td>'.$value.'</td></tr>';
		} // end _tagVisibility();

		/**
		 * Handles "Namespace" and "ENamespace" tags.
		 *
		 * @param String $value The tag value
		 * @return String
		 */
		public function _tagNamespace($namespace, $enamespace)
		{
			if($namespace === null)
			{
				return '<tr><th>'.$this->translate->_('tags','namespace').'</th><td><code>'.$enamespace.'</code></td></tr>';
			}
			else
			{
				$pp = $this->project->getMetaInfo($namespace, false);

				if($pp !== null)
				{
					return '<tr><th>'.$this->translate->_('tags','namespace').'</th><td><a href="'.$pp['Id'].'.html">'.$pp['Tags']['ShortTitle'].'</a></td></tr>';
				}
				else
				{
					return 'dupa';
				}
			}
		} // end _tagNamespace();

		/**
		 * Handles "File" tag.
		 *
		 * @param String $value The tag value
		 * @return String
		 */
		public function _tagFile($value)
		{
			return '<tr><th>'.$this->translate->_('tags','file').'</th><td><code>'.$value.'</code></td></tr>';
		} // end _tagFile();

		/**
		 * Handles "Reference" tag.
		 *
		 * @param String $value The tag value
		 * @return String
		 */
		public function _tagReference($value)
		{
			return '<tr><th>'.$this->translate->_('tags','reference').'</th><td><code>'.$value.'</code></td></tr>';
		} // end _tagReference();

		/**
		 * Handles "Files" tag.
		 *
		 * @param String $value The tag value
		 * @return String
		 */
		public function _tagFiles($value)
		{
			$code = '<tr><th>'.$this->translate->_('tags','files').'</th><td>';
			foreach($value as $file)
			{
				$code .= '<code>'.$file.'</code><br/>';
			}
			return $code.'</td></tr>';
		} // end _tagFiles();

		/**
		 * Handles "Returns" tag.
		 *
		 * @param String $value The tag value
		 * @return String
		 */
		public function _tagReturns($value)
		{
			return '<tr><th>'.$this->translate->_('tags','returns').'</th><td>'.$value.'</td></tr>';
		} // end _tagVisibility();

		/**
		 * Handles "Type" tag.
		 *
		 * @param String $value The tag value
		 * @return String
		 */
		public function _tagType($value)
		{
			return '<tr><th>'.$this->translate->_('tags','type').'</th><td>'.$value.'</td></tr>';
		} // end _tagType();

		/**
		 * Handles "Extends" and "EExtends" tags.
		 *
		 * @param String $extends The "Extends" list of values
		 * @param String $eextends The "EExtends" list of values
		 * @return String
		 */
		public function _tagExtends($extends, $eextends)
		{
			$extends = (is_null($extends) ? $eextends : $extends);
			$pp = $this->project->getMetaInfo($extends, false);
			if(!is_null($pp))
			{
				return '<tr><th>'.$this->translate->_('tags','obj_extends').'</th><td><a href="'.$pp['Id'].'.html">'.$pp['Tags']['ShortTitle'].'</a></td></tr>';
			}
		} // end _tagExtends();

		/**
		 * Handles "PartOf" and "EPartOf" tags.
		 *
		 * @param String $partOf The "PartOf" list of values
		 * @param String $ePartOf The "EPartOf" list of values
		 * @return String
		 */
		public function _tagPartOf($partOf, $ePartOf)
		{
			$partOf = (is_null($partOf) ? $ePartOf : $partOf);
			$pp = $this->project->getMetaInfo($partOf, false);
			if(!is_null($pp))
			{
				return '<tr><th>'.$this->translate->_('tags','part_of').'</th><td><a href="'.$pp['Id'].'.html">'.$pp['Tags']['ShortTitle'].'</a></td></tr>';
			}
		} // end _tagPartOf();

		/**
		 * Handles "ExtendedBy" and "EExtendedBy" tags.
		 *
		 * @param Array $val1 The "ExtendedBy" list of values
		 * @param Array $val2 The "EExtendedBy" list of values
		 * @return String
		 */
		public function _tagExtendedBy($val1, $val2)
		{
			return $this->_showLinks($val1, $val2, 'obj_extended');
		} // end _tagExtendedBy();

		/**
		 * Handles "ImplementedBy" and "EImplementedBy" tags.
		 *
		 * @param Array $val1 The "ImplementedBy" list of values
		 * @param Array $val2 The "EImplementedBy" list of values
		 * @return String
		 */
		public function _tagImplementedBy($val1, $val2)
		{
			return $this->_showLinks($val1, $val2, 'obj_implemented');
		} // end _tagExtendedBy();

		/**
		 * Handles "Implements" and "EImplements" tags.
		 *
		 * @param Array $val1 The "Implements" list of values
		 * @param Array $val2 The "EImplements" list of values
		 * @return String
		 */
		public function _tagImplements($val1, $val2)
		{
			return $this->_showLinks($val1, $val2, 'obj_implements');
		} // end _tagImplements();

		/**
		 * Handles "Throws" and "EThrows" tags.
		 *
		 * @param Array $val1 The "Implements" list of values
		 * @param Array $val2 The "EImplements" list of values
		 * @return String
		 */
		public function _tagThrows($val1, $val2)
		{
			return $this->_showLinks($val1, $val2, 'obj_throws');
		} // end _tagImplements();

		/**
		 * Handles "MultiExtends" and "EMultiExtends" tags.
		 *
		 * @param Array $val1 The "Implements" list of values
		 * @param Array $val2 The "EImplements" list of values
		 * @return String
		 */
		public function _tagMultiExtends($val1, $val2)
		{
			return $this->_showLinks($val1, $val2, 'obj_extends');
		} // end _tagMultiExtends();

		/**
		 * Handles "Mixins" and "EMixins" tags.
		 *
		 * @param Array $val1 The "Mixins" list of values
		 * @param Array $val2 The "EMixins" list of values
		 * @return String
		 */
		public function _tagMixins($val1, $val2)
		{
			return $this->_showLinks($val1, $val2, 'obj_mixins');
		} // end _tagMixins();

		/**
		 * Handles "Traits" and "ETraits" tags.
		 *
		 * @param Array $val1 The "Traits" list of values
		 * @param Array $val2 The "ETraits" list of values
		 * @return String
		 */
		public function _tagTraits($val1, $val2)
		{
			return $this->_showLinks($val1, $val2, 'obj_traits');
		} // end _tagTraits();

		/**
		 * Handles "Arguments" tag.
		 *
		 * @param Array $list The argument list
		 * @return String
		 */
		public function _tagArguments($list)
		{
			$output = tfProgram::get()->console->stderr;
			$typeOk = true;
			foreach($list as $item)
			{
				if(!isset($item['Type']) && !isset($item['EType']))
				{
					$typeOk = false;
				}
				// Do some validation here.
				if(!isset($item['Desc']))
				{
					$output->writeln('Missing Arguments:Desc tag in '.$this->_currentPage['Id']);
					return;
				}
				if(!isset($item['Name']))
				{
					$output->writeln('Missing Arguments:Name tag in '.$this->_currentPage['Id']);
					return;
				}
			}
			$code = '<tr><th>'.$this->translate->_('tags', 'arg_list').'</th><td>';//.$this->translate->_('tags', 'arg_name').'</th>';
			$code .= '<dl>';
			foreach($list as $item)
			{
				$code .= '<dt><code>'.$item['Name'].'</code>';
				if($typeOk)
				{
					$code .= ' <small>- ';
					if(isset($item['Type']))
					{

						$pp = $this->project->getMetaInfo($item['Type'], false);
						if(!is_null($pp))
						{
							$code .= '<a href="'.$pp['Id'].'.html">'.$pp['Tags']['ShortTitle'].'</a>';
						}
					}
					elseif(isset($item['EType']))
					{
						$code .= ''.$item['EType'].'';
					}          
					$code .= '</small>';
				}
				$code .= '</dt><dd>'.$item['Desc'].'</dd>';
			}
			return $code.'</dl></td></tr>';
		} // end _tagParameters();

		/**
		 * Handles "Package" tag.
		 *
		 * @param String $value The tag value
		 * @return String
		 */
		public function _tagPackage($package, $epackage)
		{
			if($package === null)
			{
				return '<tr><th>'.$this->translate->_('tags','package').'</th><td><code>'.$epackage.'</code></td></tr>';
			}
			else
			{
				$pp = $this->project->getMetaInfo($extends, false);
				if($pp !== null)
				{
					return '<tr><th>'.$this->translate->_('tags','package').'</th><td><code><a href="'.$pageDef['Id'].'.html">'.$pageDef['ShortTitle'].'</a></code></td></tr>';
				}
			}
		} // end _tagPackage();

		/**
		 * Handles "TimeComplexity" tag.
		 *
		 * @param String $val The value to be displayed.
		 * @return String
		 */
		public function _tagTimeComplexity($val)
		{
			return '<tr><th>'.$this->translate->_('tags','time_complexity').'</th><td><code>'.$val.'</code></td></tr>';
		} // end _tagTimeComplexity();

		/**
		 * Handles "MemoryComplexity" tag.
		 *
		 * @param String $val The value to be displayed.
		 * @return String
		 */
		public function _tagMemoryComplexity($val)
		{
			return '<tr><th>'.$this->translate->_('tags','memory_complexity').'</th><td><code>'.$val.'</code></td></tr>';
		} // end _tagMemoryComplexity();

		/**
		 * Handles "StartConditions" tag.
		 *
		 * @param Array $conditions The values to be displayed.
		 * @return String
		 */
		public function _tagStartConditions($conditions)
		{
						return $this->_showList($conditions, 'start_conditions');
		} // end _tagStartConditions();

		/**
		 * Handles "EndConditions" tag.
		 *
		 * @param Array $conditions The values to be displayed.
		 * @return String
		 */
		public function _tagEndConditions($conditions)
		{
			return $this->_showList($conditions, 'end_conditions');
		} // end _tagEndConditions();

		/**
		 * Handles "SideEffects" tag.
		 *
		 * @param Array $conditions The values to be displayed.
		 * @return String
		 */
		public function _tagSideEffects($conditions)
		{
			return $this->_showList($conditions, 'side_effects');
		} // end _tagSideEffects();

		/**
		 * Handles "Limitations" tag.
		 *
		 * @param Array $conditions The values to be displayed.
		 * @return String
		 */
		public function _tagLimitations($conditions)
		{
			return $this->_showList($conditions, 'limitations');
		} // end _tagLimitations();

		/**
		 * Handles "DataSources" tag.
		 *
		 * @param Array $conditions The values to be displayed.
		 * @return String
		 */
		public function _tagDataSources($val1, $val2)
		{
			$code = '<tr><th>'.$this->translate->_('tags', 'datasources').'</th><td><ol>';
			if($val1 !== null)
			{
				foreach($val1 as $item)
				{
					$pp = $this->project->getMetaInfo($item, false);
					if(!is_null($pp))
					{
						$code .= '<li><a href="'.$pp['Id'].'.html">'.$pp['Tags']['ShortTitle'].'</a></li>';
					}
				}
			}
			if($val2 !== null)
			{
				foreach($val2 as $item)
				{
					$code .= '<li>'.$item.'</li>';
				}
			}
			return $code.'</ol></td></tr>';
		} // end _tagDataSources();

		/**
		 * A helper method for tags like "SideEffects".
		 *
		 * @param Array $val1
		 * @param Array $val2
		 * @param String $message
		 */
		protected function _showList(array $val1, $message)
		{
			$code = '<tr><th>'.$this->translate->_('tags',$message).'</th><td>';
			$items = array();
			if(sizeof($val1) == 1)
			{
				$code .= $val1[0];
			}
			else
			{
				$code .= '<ol>';
				foreach($val1 as $item)
				{
					$code .= '<li>'.$item.'</li>';
				}
				$code .= '</ol>';
			}
			return $code.'</td></tr>';
		} // end _showList();

		/**
		 * A helper method for tags like "Implements".
		 *
		 * @param Array $val1
		 * @param Array $val2
		 * @param String $message
		 */
		protected function _showLinks($val1, $val2, $message)
		{
			$code = '<tr><th>'.$this->translate->_('tags',$message).'</th><td>';
			$items = array();
			if($val1 !== null)
			{
				foreach($val1 as $item)
				{
					$pp = $this->project->getMetaInfo($item, false);
					if(!is_null($pp))
					{
						$items[] = '<code><a href="'.$pp['Id'].'.html">'.$pp['Tags']['ShortTitle'].'</a></code>';
					}
				}
			}
			if($val2 !== null)
			{
				foreach($val2 as $item)
				{
					$items[] = '<code>'.$item.'</code>';
				}
			}
			return $code.implode(', ', $items).'</td></tr>';
		} // end _showLinks();
	} // end xhtml;
