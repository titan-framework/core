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
// $Id: markdown.php 68 2010-01-16 11:11:50Z extremo $

	@define('MARKDOWN_PARSER_CLASS', 'MarkdownDocs_Parser');
	
	require_once TF_VENDOR.'markdown/markdown.php';
	require_once TF_VENDOR.'geshi/geshi.php';
	
	/*
		Original Markdown parser is written in PHP4, so I don't think it is
		necessary to use 'private' and 'public' in functions here
		- eXtreme
	*/
	
	class MarkdownDocs_Parser extends MarkdownExtra_Parser
	{
		var $page_id = '';
		
		function _doCodeBlocks_callback($matches)
		{
			$codeblock = $matches[1];
			
			$codeblock = $this->outdent($codeblock);
	
			# trim leading newlines and trailing newlines
			$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);
			
			$clear = true;
			$codeblock = $this->_codeBlockHighlighter($codeblock, $clear);
	
			if($clear)
			{
				$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
				$codeblock = "<pre><code>$codeblock\n</code></pre>";
			}
			return "\n\n".$this->hashBlock($codeblock)."\n\n";
		} // end _doCodeBlocks_callback();
		
		function _doFencedCodeBlocks_callback($matches)
		{
			$codeblock = $matches[2];
			
			$clear = true;
			$codeblock = $this->_codeBlockHighlighter($codeblock, $clear);
			
			if($clear)
			{
				$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
				$codeblock = preg_replace_callback('/^\n+/', array($this, '_doFencedCodeBlocks_newlines'), $codeblock);
				$codeblock = "<pre><code>$codeblock</code></pre>";
			}
			
			return "\n\n".$this->hashBlock($codeblock)."\n\n";
		} // end _doFencedCodeBlocks_callback();
		
		function _codeBlockHighlighter($codeblock, &$clear)
		{
			$split = preg_split('/[\r\n]/', $codeblock, 2, PREG_SPLIT_NO_EMPTY); 
			
			if(count($split) == 2 && preg_match('/^\s*((\\\){0,2}\[([a-zA-Z0-9\-_]+)\]\s*)/', $split[0], $matches))
			{
				
				if($matches[2] == '\\')
				{
					$codeblock = substr($codeblock, 1);
					return $codeblock;
				}
				
				$strlen = strlen($matches[0]);
				$parser = strtolower($matches[3]);

				$codeblock = $split[1];
				
				if($strlen > 0)
				{
					if($parser == 'console')
					{
						$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
						$codeblock = preg_replace_callback('/^\n+/', array($this, '_doFencedCodeBlocks_newlines'), $codeblock);
						$codeblock = "<pre class=\"console\"><code>$codeblock</code></pre>";
					}
					else
					{
						$codeblock = preg_replace('/\n+$/', '', $codeblock);
						$geshi = new GeSHi($codeblock, $parser);
						$geshi->set_overall_style('');
					
						$codeblock = $geshi->parse_code();
					}
					
					$clear = false;
				}
			}
			return $codeblock;						
		} // end _codeBlockHighlighter();
		
		function _doBlockQuotes_callback($matches)
		{
			$bq = $matches[1];
			# trim one level of quoting - trim whitespace-only lines
			$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
			                                                          
			$addClass = '';
			if(preg_match('/^((\\\){0,2}\[([a-zA-Z0-9\-_]+)\]\s*\n)/', $bq, $matches))
			{
				if($matches[2] == '\\')
				{
					$bq = substr($bq, 1);
				}
				else
				{	
					$strlen = strlen($matches[1]);
					$parser = strtolower($matches[3]);
					
					if($strlen > 0)
					{
						$bq = substr($bq, $strlen);
						$addClass = ' class="'.$parser.'"';
					}
				}
			}
			
			$bq = $this->runBlockGamut($bq);		# recurse
			
			$bq = preg_replace('/^/m', "  ", $bq);
			# These leading spaces cause problem with <pre> content, 
			# so we need to fix that:
			$bq = preg_replace_callback('{(\s*<pre[^>]*>.+?</pre>)}sx', 
				array(&$this, '_DoBlockQuotes_callback2'), $bq);
			
			return "\n".$this->hashBlock("<blockquote$addClass>\n$bq\n</blockquote>")."\n\n";
		} // end _doBlockQuotes_callback();
		
		function _doHeaders_attr($attr)
		{
			if(empty($attr)) return "";
			return ' id="h:'.str_replace('.', '_', $this->page_id).':'.$attr.'"';
		} // end _doHeaders_attr();
		
		function _doHeaders_callback_setext($matches)
		{
			if($matches[3] == '-' && preg_match('{^- }', $matches[1]))
				return $matches[0];
			
			$level = $matches[3]{0} == '=' ? 1 : 2;
			$level += 1;
			$attr  = $this->_doHeaders_attr($id =& $matches[2]);
			$block = "<h$level$attr>".$this->runSpanGamut($matches[1])."</h$level>";
			return "\n".$this->hashBlock($block)."\n\n";
		} // end _doHeaders_callback_setext();
		
		function _doHeaders_callback_atx($matches)
		{
			$level = strlen($matches[1]);
			$level += 1;
			if($level > 6)
				$level = 6;
				
			$attr  = $this->_doHeaders_attr($id =& $matches[3]);
			$block = "<h$level$attr>".$this->runSpanGamut($matches[2])."</h$level>";
			return "\n".$this->hashBlock($block)."\n\n";
		} // end _doHeaders_callback_atx();
		
		function _doAnchors_reference_callback($matches)
		{
			$whole_match =  $matches[1];
			$res = parent::_doAnchors_reference_callback($matches);
			if($res != $whole_match || strpos($matches[3], '#') === false)
			{
			    return $res;
			}	
			list($matches[3], $anchor) = explode('#', $matches[3], 2);
			
			$link_id = strtolower($matches[3]);
			
			$temp = false;
			if(isset($this->urls[$link_id]))
			{
				$temp = $this->urls[$link_id];
				$explode = explode('#', $this->urls[$link_id], 2);
				if(count($explode) == 2)
				{
					$this->urls[$link_id] = $explode[0];
				}
				$this->urls[$link_id] .= '#h:'.str_replace('.', '_', $link_id).':'.$anchor;
			}
			$res = parent::_doAnchors_reference_callback($matches);
			if($temp !== false)
			{
				$this->urls[$link_id] = $temp;
			}
			
			return $res;
		} // end _doAnchors_reference_callback();
	} // end MarkdownDocs_Parser;
