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
// $Id: xhtml_single.php 68 2010-01-16 11:11:50Z extremo $

	require_once('xhtml.php');

	class xhtml_single extends xhtml
	{
		protected $pageOrder = array();
		protected $pageContent = array();
		
		/**
		 * Initializes the generation.
		 * 		 
		 * @param tfProject $project The project
		 * @param String $path Output path
		 */
		public function init($project, $path)
		{
			$this->translate = tfTranslate::get();
			$this->date = date('d.m.Y');
			
			$this->project = $project;
			$this->path = $path;
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
			
			$code = $this->createTopNavigator($page);
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
			
			$this->pageContent[$page['Id']] = $code;
		} // end generate();
		
		/**
		 * Finalizes the generation and saves the results to the hard disk.
		 */
		public function close()
		{
			
			$code = $this->createHeader('', array());
			
			$code .= '<h1>'.$this->project->config['title'].' '.$this->project->config['version'].'</h1>';
			
			$code .= '<div class="tf_reference"><table>';
			$code .= '<tr><td><strong>Copyright &copy; '.$this->project->config['copyright'].'</strong></td></tr>';
			$code .= '<tr><td>'.$this->translate->_('general','doc_license',$this->project->config['license']).'</td></tr>';
			$code .= '<tr><td>'.$this->translate->_('general','generated_in',$this->date).'</td></tr>';
			$code .= '</table><hr/></div>';
			
			$code .= '<h4 id="toc">'.$this->translate->_('general','table_of_contents').'</h4>';
			
			$code .= $this->menuGen('', true);
			foreach($this->pageOrder as $id)
			{
				$code .= $this->pageContent[$id];
			}
			
			$code .= $this->createFooter();
		
			$this->project->fs->write($this->path.'index.html', $code);      
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
			
			$docTitle = $this->project->config['title'];
			$docVersion = $this->project->config['version'];
			
			$textDocumentation = $this->translate->_('general',$this->project->config['projectType']);
$code = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="pl">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="all" />

	<title>{$docTitle}</title>
	
	<link rel="stylesheet" type="text/css" href="design/generic.css" media="all"  />
	<link rel="stylesheet" type="text/css" href="design/print.css" media="print" />
	<!--[if lte IE 6]><link rel="stylesheet" href="design/ie.css" type="text/css" /><![endif]-->
	<!--[if IE 7]><link rel="stylesheet" href="design/ie7.css" type="text/css" /><![endif]-->	
</head>
<body>

<div id="wrap">
	<div id="header">
		<h1>{$docTitle} {$docVersion}</h1>
		<p class="generated">@ {$this->date}</p>
		<p class="location"><a href="index.html"><strong>{$textDocumentation}</strong></a></p>
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
			
			$textLicense = $this->translate->_('general','doc_license',$license);
			
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
            
            $id = 'toc:'.str_replace('.', '_', $page['Id']);
			
			$parent = $this->project->getMetaInfo($page['_Parent'], false);
			$prev = $this->project->getMetaInfo($page['_Previous'], false);
			$next = $this->project->getMetaInfo($page['_Next'], false);
			$code = '<dl id="'.$id.'" class="location location-middle">';
			if(!is_null($parent))
			{
				$code .= '<dt><a href="'.$this->toAddress($parent['Id']).'">'.($n ? $parent['FullNumber'].'. ' : '').$parent['Tags']['Title'].'</a><br/>'.($n ? $page['FullNumber'].'. ' : '').$page['Tags']['Title'].'</dt>';
			}
			else
			{
				$code .= '<dt><a href="#toc">'.$this->translate->_('general', 'table_of_contents').'</a><br/>'.($n ? $page['FullNumber'].'. ' : '').$page['Tags']['Title'].'</dt>';
			}
			if(!is_null($prev))
			{
				$code .= '<dd class="prev">'.($n ? $prev['FullNumber'].'. ' : '').$prev['Tags']['Title'].'<br/><a href="'.$this->toAddress($prev['Id']).'">&laquo; '.$this->translate->_('navigation','prev').'</a></dd>';
			}
			if(!is_null($next))
			{
				$code .= '<dd class="next">'.($n ? $next['FullNumber'].'. ' : '').$next['Tags']['Title'].'<br/><a href="'.$this->toAddress($next['Id']).'">'.$this->translate->_('navigation','next').' &raquo;</a></dd>';
			}
			$code .= '</dl>	';
			return $code;
		} // end createTopNavigator();
		
		/**
		 * Converts the page identifier to the URL.
		 *
		 * @param String $page The page identifier.
		 * @return String
		 */
		public function toAddress($page)
		{
			$page = str_replace('.', '_', $page);
			return '#toc:'.$page;
		} // end toAddress();
		
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
			
			if(isset($this->project->tree[$what]) && count($this->project->tree[$what]) > 0)
			{			
				$code = '<ul class="toc">';
				foreach($this->project->tree[$what] as $item)
				{
					$this->pageOrder[] = $item['Id'];
					if($recursive)
					{
						$code .= '<li><a href="'.$this->toAddress($item['Id']).'">'.($n ? $item['FullNumber'].'. ' : '').$item['Tags']['Title'].'</a>'.$this->menuGen($item['Id'], true).'</li>';
					}
					else
					{
						$code .= '<li><a href="'.$this->toAddress($item['Id']).'">'.($n ? $item['FullNumber'].'. ' : '').$item['Tags']['Title'].'</a></li>';
					}
				}
				$code .= '</ul>';
				return $code;
			}
			return '';
		} // end menuGen();
	}
