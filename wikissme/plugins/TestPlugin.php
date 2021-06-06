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
 *  @version $Id: TestPlugin.php,v 1.1 2009/07/04 15:33:17 neven Exp $
 *  @author Neven Boyanov
 *
 */

class TestPlugin extends AbstractPlugin
{
	protected static $description = 
		"Test plugin.";
	
	function TestPlugin($wikissme)
	{
		parent::AbstractPlugin($wikissme);
		$this->rank = 3;
	}
	
	public static function getDescription() { return self::$description; }
}

?>
