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
 *  @version $Id: Debug.php,v 1.2 2009/11/02 20:33:27 neven Exp $
 *  @author Neven Boyanov
 *
 */

require_once "classes/AbstractPlugin.php";

class Debug extends AbstractPlugin
{
	protected static $description = 
		"Debug info (this is a test plugin for now, it does not do anyhing)";
	private $text = "";
	
	function Debug($wikissme)
	{
		parent::AbstractPlugin($wikissme);
		$this->rank = 3;
		$this->config_file = "debug";
	}
	
	public static function getDescription() { return self::$description; }
	
	function trigger_write()
	{
		global $WIKI_LANG;
		if ($this->isActive())
		{
			$this->text .= 
				"trigger_write" .
				"<br />";
		}
	}
	
	function trigger_action()
	{
		global $WIKI_LANG;
		if ($this->isActive())
		{
			$this->text .= 
				"trigger_action" .
				"<br />";
		}
	}
	
	function trigger_inline()
	{
		global $WIKI_LANG;
		if ($this->isActive())
		{
			$this->text .= 
				"trigger_inline" .
				"<br />";
		}
	}
	
	function trigger_format()
	{
		global $WIKI_LANG;
		if ($this->isActive())
		{
			$this->text .= 
				"trigger_format" .
				"<br />";
		}
	}
	
	function trigger_template()
	{
		global $WIKI_LANG;
		if ($this->isActive())
		{
			$this->text .= 
				"trigger_template" .
				"<br />";
		}
		// Rreplace the {DEBUG_MESSAGES_HTML} tag/placeholder in the template ...
		$template_html = &$this->wikissme->template_html;	// MUST be passed by reference.
		if ($this->isActive())
		{
			$template_html = str_replace("{DEBUG_MESSAGES_HTML}", "<br /><hr />DEBUG:<br \>{$this->text}", $template_html);
		}
		else
		{
			$template_html = str_replace("{DEBUG_MESSAGES_HTML}", "", $template_html);
		}
	}
}

?>
