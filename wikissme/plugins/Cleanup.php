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
 *  @version $Id: Cleanup.php,v 1.1 2009/07/04 15:33:17 neven Exp $
 *  @author Neven Boyanov
 *
 */

require_once "classes/AbstractPlugin.php";

define ('WIKISSME_PLUGIN_CLEANUP_REPLACEMENT', '__cleanedup__');

class Cleanup extends AbstractPlugin
{
	protected static $description = 
		"Cleanup unnecessary text from a page. Removes unused blocks.";
	
	function Cleanup($wikissme)
	{
		parent::AbstractPlugin($wikissme);
		$this->rank = 3;
		$this->config_file = "cleanup";
	}
	
	public static function getDescription() { return self::$description; }
	
	function trigger_template()
	{
		global $PAGE_CONTENT, $WIKI_LANG;
		if ($this->isActive())
		{
			// Remove unused "{...}" blocks
			$PAGE_CONTENT = preg_replace('/{[0-9a-zA-Z\-_]*}/U', WIKISSME_PLUGIN_CLEANUP_REPLACEMENT,$PAGE_CONTENT);
			// Remove unused "%...%" blocks
			$PAGE_CONTENT = preg_replace('/%[0-9a-zA-Z\-_]*%/U', WIKISSME_PLUGIN_CLEANUP_REPLACEMENT,$PAGE_CONTENT);
			// Remove unused "$...$" blocks
			$PAGE_CONTENT = preg_replace('/\$[0-9a-zA-Z\-_]*\$/U', WIKISSME_PLUGIN_CLEANUP_REPLACEMENT,$PAGE_CONTENT);	// ---- TODO: FIX THIS ----
		}
		return FALSE;
	}
}

?>
