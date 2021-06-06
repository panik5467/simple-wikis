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
 *  @version $Id: PluginsInfo.php,v 1.1 2009/07/04 15:33:17 neven Exp $
 *  @author Neven Boyanov
 *
 */

require_once "classes/AbstractPlugin.php";

class PluginsInfo extends AbstractPlugin
{
	protected static $description =
		"Prints out some plugins information";
	
	function PluginsInfo($wikissme)
	{
		parent::AbstractPlugin($wikissme);
		$this->rank = 3;
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
		// TODO: Print out information about old-style plugins as well.
		$p = $this->wikissme->getPluginsManager()->getPlugins();	// work with copy of the array
		// var_export($this->wikissme->getPlugins());
		ksort($p);
		foreach ($p as $plugin_name => $plugin)
		{
         	$plugin_rank = FALSE;
            if (method_exists($plugin_name, "getRank")) $plugin_rank = $plugin->getRank();
			$result .=
				"<tr>" .
				"<td><b>{$plugin_name}</b></td>" .
				"<td>" .
					"{$plugin}" .
					($plugin instanceof AbstractOldPlugin ? 
						" (old style plugin, updated)" : 
							($plugin instanceof AbstractPlugin ? "" : " (old style plugin)")
						) .
				"</td>" .
				"<td>" . 
					($plugin_rank ? " rank:{$plugin_rank} " : "") . 
					($plugin->config_file ? " config_file:{$plugin->config_file} " : "") . 
				"</td>" .
				"</tr>" .
				"";
		}
		$result .= "</table>";
		return $result;
	}
}

?>
