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
 *  @subpackage Modules
 *  @version $Id: TestModule.php,v 1.1 2009/07/04 15:33:17 neven Exp $
 *  @author Neven Boyanov
 *
 */

class TestModule extends AbstractModule
{
	protected static $description = 
		"Test module.";
	
	function TestModule($wikissme)
	{
		parent::AbstractModule($wikissme);
		$this->config_file = "testmodule";
	}
	
	public static function getDescription() { return self::$description; }
}

?>
