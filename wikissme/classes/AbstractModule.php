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
 *  @version $Id: AbstractModule.php,v 1.1 2009/07/04 15:33:16 neven Exp $
 *  @author Neven Boyanov
 *
 */

abstract class AbstractModule implements Module
{
	protected $wikissme;
	protected static $description = "Module";
	public $config_file;	// should be static and protected/private
	protected $config;
	private $active = TRUE;
	
	protected function AbstractModule(&$wikissme)
	{
		// QUESTION: Should this be passed by reference to avoid working on a copy?
		$this->wikissme = $wikissme;
	}

	// This should be replaced by static::$description when new
	// PHP 5.3.x with "Late Static Bindings" becomes available.
	public static function getDescription() { return self::$active; }

	public function __toString() { return $this->getDescription(); }
	
	public function loadConfig()
	{
		global $MODULES_CONFIG_FOLDER;
		if ($this->config_file)
		{
	    	$config_filename = "module-{$this->config_file}.ini";
	    	$config_filepath = "{$MODULES_CONFIG_FOLDER}/{$config_filename}";
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
	
	// * This method should be used to determine if the module is set to active
	//   in the module configuration file.
	// * It is recommended to check if it is active before executing a method.
	public function isActive() { return $this->active; }
}

?>
