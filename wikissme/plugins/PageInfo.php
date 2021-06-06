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
 *  @version $Id: PageInfo.php,v 1.1 2009/07/04 15:33:17 neven Exp $
 *  @author Neven Boyanov
 *
 */

require_once "classes/AbstractPlugin.php";

class PageInfo extends AbstractPlugin
{
	protected static $description = 
		"Prints out some page information";

	function PageInfo($wikissme)
	{
		parent::AbstractPlugin($wikissme);
	}
	
	public static function getDescription() { return self::$description; }
	
	function trigger_inline()
	{
		$result .= 
			"<hr style='text-align: right; margin-left: 0; width: 25%;' />" . // TODO: move this to stylesheet
			"<b>Page Info</b><br />" .
			"script_basename: {$this->wikissme->script_basename}<br />" .
			"page.name: {$this->wikissme->page->name}<br />";
		return $result;
	}
}

?>
