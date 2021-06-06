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
 *  @subpackage Plugins
 *  @version $Id: TOC.php,v 1.2 2009/11/02 20:42:39 neven Exp $
 *  @author Neven Boyanov
 *
 */

require_once "classes/AbstractPlugin.php";

class TOC extends AbstractPlugin
{
	protected static $description = 
		"Table of contents.";
	
	function TOC($wikissme)
	{
		parent::AbstractPlugin($wikissme);
		$this->rank = 1;
	}
	
	public static function getDescription() { return self::$description; }
	
	function trigger_template()
	{
/*
		// This should include a TOC in the begining of every page.
		// NOTE: this is not correct way of adding content to the page because
		//       there is no a placeholder in the template for that content.
		$this->wikissme->page->content = 
			$this->wikissme->getHeadings()->toc() . 
			$this->wikissme->page->content .
			"";
*/
		$template_html = &$this->wikissme->template_html;	// MUST be passed by reference.
		$template_html = str_replace("{PAGE_TOC}", $this->wikissme->getHeadings()->toc(), $template_html);
		return FALSE;
	}

	function trigger_inline()
	{
		// NOTE: This is NOT WORKING because the headings info (i.e. TOC) is 
		//       not availabe at the time "inline" plugin methods is called.
		// print "Debug: " . __METHOD__ . "<br />";
		$result .= $this->wikissme->getHeadings()->toc();
		return $result;
	}
}

?>
