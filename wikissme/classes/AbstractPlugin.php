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
 *  @version $Id: AbstractPlugin.php,v 1.1 2009/07/04 15:33:16 neven Exp $
 *  @author Neven Boyanov
 *
 */

require_once "Plugin.php";

abstract class AbstractPlugin implements Plugin
{
	protected $wikissme;
	protected static $description = "Plugin";
	protected $config_file;	// should be static
	protected $config;
	protected $active = TRUE;
	protected $rank = 1;
	
	protected function AbstractPlugin(&$wikissme)
	{
		// QUESTION: Should this be passed by reference to avoid working on a copy?
		$this->wikissme = $wikissme;
	}

	// This should be replaced by static::$description when new
	// PHP 5.3.x with "Late Static Bindings" becomes available.
	// NOTE: For now all extending classes implement their getDescription().
	// TODO: Replace this with non-static implementation to avoid redundand
	//       code.
	public static function getDescription() { return self::$description; }

	public function __toString() { return $this->getDescription(); }
	
	public function loadConfig()
	{
		global $PLUGINS_CONFIG_FOLDER;
		if ($this->config_file)
		{
	    	$config_filename = "plugin-{$this->config_file}.ini";
	    	$config_filepath = "{$PLUGINS_CONFIG_FOLDER}/{$config_filename}";
	    	// print "config_filepath: {$config_filepath}<br />";	// DEBUG
	    	if (file_exists($config_filepath))
	    	{
	    		$this->config = parse_ini_file($config_filepath, TRUE);
		    	// print "config[{$this->config_file}]: " . var_export($this->config, TRUE) . "<br />";	// DEBUG
	    	}
	    	else
	    	{
		    	// print "ERROR[" . __METHOD__ . "]: config_filepath={$config_filepath}<br />";	// DEBUG
	    	}
	    	
	    	$this->active = ($this->config["active"] ? TRUE : FALSE);
	    	// print "this.active: " . var_export($this->active, TRUE) . "<br />";
		}
	}
	
	public function init()
	{
		$this->loadConfig();
	}
	
	public function getRank()
	{
		return $this->rank;
	}
	
	// * This method should be used to determine if the plugin is set to active
	//   in the plugin configuration.
	// * It is recommended to check if active before executing some methods.
	public function isActive() { return $this->active; }
}

?>
