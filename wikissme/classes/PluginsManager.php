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
 *  @version $Id: PluginsManager.php,v 1.1 2009/07/04 15:33:16 neven Exp $
 *  @author Neven Boyanov
 *
 */

class PluginsManager
{
	protected $wikissme;

	private $plugins = array();
	private $plugins_old = array();
	
	public function PluginsManager(&$wikissme)
	{
		$this->wikissme = $wikissme;
	}
	
	public function getPluginsOld()
	{
		return $this->plugins_old;
	}
	
	public function getPlugins()
	{
		return $this->plugins;
	}
	
	public function load($folder)
	{
		if (is_dir($folder) && ($dir = opendir($folder)))
		{
			while (($file_name = readdir($dir)) !== false)
			{
				$file_path = $folder ."/" . $file_name;
				// print "file: {$file_name} (" . is_file($file_path) . ")<br />";	// DEBUG
				if (is_file($file_path))
				{
					if (preg_match('/^wkp_(.+)\.php$/', $file_name, $matches)>0)
					{
						// Old-style plugin
					 	$plugin_name = $matches[1];
					 	// print "plugin(wkp_*): {$plugin_name} @{$file_path}<br />";	// DEBUG
					 	require_once $file_path;	// This MUST be changed to "require" only.
				        $plugin = new $plugin_name($this->wikissme);
				        $this->plugins_old[$plugin_name] = $plugin;
					    // Load plugin specific settings
					    if (method_exists($plugin, "init")) $plugin->init();	// REMOVE this if not necessary for old-style plugins.
					}
					else
					{
						if (preg_match('/^(.+)\.php$/', $file_name, $matches)>0)
						{
							// New-style plugin
						 	$plugin_name = $matches[1];
						 	// print "plugin: {$plugin_name} @{$file_path}<br />";	// DEBUG
						 	require_once $file_path;	// This MUST be changed to "require" only.
					        $plugin = new $plugin_name($this->wikissme);
					        $this->plugins[$plugin_name] = $plugin;
						    // Load plugin specific settings
						    if (method_exists($plugin, "init")) $plugin->init();
						}
						// ELSE: file does not match name criteria
					}
				}
				// ELSE: not a regular file, may be a folder.
			}
			// var_export($this->plugins_old);
			if (!empty($this->plugins_old)) plugins_sortbyrank($this->plugins_old);
			if (!empty($this->plugins)) plugins_sortbyrank($this->plugins);
		}
		// ELSE: Either not a directory or cannot be opened for reading
	}
}

?>
