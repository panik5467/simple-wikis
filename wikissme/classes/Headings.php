<?php

/**
 *  WiKissMe
 *
 *  Copyright (c) 2008-2009 by Neven Boyanov (Boyanov.Org)
 *  Licensed under GNU/GPLv2 - http://www.gnu.org/licenses/
 *
 *  This program is distributed under the terms of the License,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See
 *  the License for more details.
 *
 *  @package WiKissMe
 *  @subpackage Classes
 *  @version $Id: Headings.php,v 1.2 2009/11/02 21:12:22 neven Exp $
 *  @author Neven Boyanov
 *
 */

class Headings
{
	private $wikissme;
	private $headings = array();
	
	public function Headings($wikissme)
	{
		$this->wikissme = $wikissme;
	}
	
	private function format_callback($matches)
	{
		static $count = 0;
		$name = preg_replace('/[^\da-z]/i','_',$matches[2]) . '_' . ++$count;
		$description = $matches[2];
		$rank = strlen($matches[1]);
		$this->headings[] = 
			array (
				'name' => $name,
				'description' => $description,
				'rank' => $rank);
		// $result = "**** {$description} (name:{$name},rank:{$rank}) ****";
		$result = 
			"<h{$rank} class='heading'><a name=\"{$name}\">{$description}</a></h{$rank}>" .
			// "(name:{$name},rank:{$rank})" . // DEBUG
			"";
		return $result;
	}
	
	public function format()
	{
		$this->wikissme->page->content = 
			preg_replace_callback(
				'/^(!+?) (.*)$/Um', 
				array($this,'format_callback'), 
				$this->wikissme->page->content);
		// var_export($this->headings);
		// print "Debug: " . __METHOD__ . "<br />";
	}
	
	public function toc()
	{
		// var_export($this->headings);
		if (!empty($this->headings))
		{
			$result .= "<div id='page-toc'>";
			foreach ($this->headings as $heading)
			{
/*
				$result .=
					"<div class='page-toc{$heading['rank']}'>" .
					"<a href='#{$heading['name']}'>{$heading['description']}</a>" .
					"</div>";
*/
				$result .=
					"<div class='page-toc-h{$heading['rank']}'>" .
					"<a href='#{$heading['name']}'>{$heading['description']}</a>" .
					"</div>";
			}
			$result .= "</div>";
		}
		return $result;
	}
	
	public function test() { $this->wikissme->page->content = "TEST HEADINGS"; }
}

?>
