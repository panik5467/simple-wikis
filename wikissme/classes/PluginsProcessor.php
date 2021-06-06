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
 *  @version $Id: PluginsProcessor.php,v 1.1 2009/07/04 15:33:16 neven Exp $
 *  @author Neven Boyanov
 *
 */

class PluginsProcessor
{
	private $wikissme;
	private $pluginsmanager;

	public function PluginsProcessor(&$wikissme)
	{
		$this->wikissme = $wikissme;
	}
	
	public function setPluginsManager(&$pluginsmanager)
	{
		$this->pluginsmanager = $pluginsmanager;
	}
	
	public function trigger($trigger)
	{
		$method_name = "trigger_" . $trigger;
		// print "method_name: {$method_name}<br />";	// DEBUG
		$plugins = $this->pluginsmanager->getPlugins();
		foreach ($plugins as $plugin_name => $plugin)
		{
			// print "plugin_name: {$plugin_name}<br />";	// DEBUG
			if (method_exists($plugin, $method_name))
			{
				// print "trigger({$trigger}): plugin(" . get_class($plugin) . ") / method: {$method_name}<br />";	// DEBUG
				$args = func_get_args();
				call_user_func_array(array($plugin, $method_name), array_slice($args, 1));
			}
			else
			{
				// print "trigger({$trigger}): method does not exist - plugin(" . get_class($plugin) . ") / method: {$method_name}<br />";	// DEBUG
			}
		}
	}
	
	public function inline()
	{
		global $PAGE_CONTENT;
		$PAGE_CONTENT = preg_replace_callback('/(%([0-9a-zA-Z\-_]*)%\n*)/U', array($this, "inline_callback"), $PAGE_CONTENT);
	}
	
	private function inline_callback($matches)
	{
		$plugin_name = $matches[2];
		$method_name = "trigger_inline";
		// print "inline: {$plugin_name}<br />";	// DEBUG
		$plugins = $this->pluginsmanager->getPlugins();
		$plugin = $plugins[$plugin_name];
		if ($plugin)
		{
			if (method_exists($plugin, $method_name))
			{
				$result = call_user_func(array ($plugin, $method_name), $method_param);
			}
			else
			{
				$result = $matches[1]; // METHOD DOES NOT EXIST
			}
		}
		else
		{
			$result = $matches[1]; // PLUGIN DOES NOT EXIST
		}
		return $result;
	}
}

?>
