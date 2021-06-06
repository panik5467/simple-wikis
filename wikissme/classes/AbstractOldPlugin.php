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
 *  @version $Id: AbstractOldPlugin.php,v 1.1 2009/07/04 15:33:16 neven Exp $
 *  @author Neven Boyanov
 *
 */

require_once "AbstractPlugin.php";

abstract class AbstractOldPlugin extends AbstractPlugin
{
	protected function AbstractOldPlugin(&$wikissme)
	{
		parent::AbstractPlugin($wikissme);
		$this->rank = 1;
	}
	
	public static function getDescription()
	{
		return $this->__toString();
	}
}

?>
