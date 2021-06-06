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
 *  @version $Id: Captcha.php,v 1.1 2009/07/04 15:33:17 neven Exp $
 *  @author Neven Boyanov
 *
 */

class Captcha extends AbstractModule
{
	protected static $description = 
		"Generates CAPTCHA images.";
	
	function Captcha(&$wikissme)
	{
		parent::AbstractModule($wikissme);
		$this->config_file = "captcha";
	}
	
	public static function getDescription() { return self::$description; }
	
	public function generateKey()
	{
		$key = strtoupper(substr(md5(rand()), 0, 6));
		return $key;
	}
}

?>
