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
 *  @version $Id: ModulesInfo.php,v 1.1 2009/07/04 15:33:17 neven Exp $
 *  @author Neven Boyanov
 *
 */

class ModulesInfo extends AbstractPlugin
{
	protected static $description =
		"Prints out some modules information";
	
	function ModulesInfo($wikissme)
	{
		parent::AbstractPlugin($wikissme);
	}
	
	public static function getDescription() { return self::$description; }
	
	function trigger_inline()
	{
		$result .= "<table class='wikitable' style='border: 2px solid #bbbbbb;'>";
		$result .= 
			"<tr>" .
				"<td><b>Name</b></td>" .
				"<td><b>Description</b></td>" .
				"<td><b>Parameters</b></td>" .
			"</tr>" .
			"";
		$p = $this->wikissme->getModulesManager()->getModules();	// work with copy of the array
		// var_export($this->wikissme->getModules());
		ksort($p);
		foreach ($p as $module_name => $module)
		{
			$result .=
				"<tr>" .
				"<td><b>{$module_name}</b></td>" .
				"<td>{$module}</td>" .
				"<td>" . 
					($module->config_file ? " config_file:{$module->config_file} " : "") . 
				"</td>" .
				"</tr>" .
				"";
		}
		$result .= "</table>";
		return $result;
	}
}

?>
