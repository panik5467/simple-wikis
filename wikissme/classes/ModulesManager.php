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
 *  @version $Id: ModulesManager.php,v 1.1 2009/07/04 15:33:16 neven Exp $
 *  @author Neven Boyanov
 *
 */

class ModulesManager
{
	protected $wikissme;

	private $modules = array();
	
	public function ModulesManager(&$wikissme)
	{
		$this->wikissme = $wikissme;
	}
	
	public function getModules()
	{
		return $this->modules;
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
					if (preg_match('/^(.+)\.php$/', $file_name, $matches)>0)
					{
					 	$module_name = $matches[1];
					 	// print "module: {$module_name} @{$file_path}<br />";	// DEBUG
					 	require_once $file_path;	// This MUST be changed to "require" only.
				        $module = new $module_name($this->wikissme);
				        $this->modules[$module_name] = $module;
					    // Load module specific settings
					    if (method_exists($module, "init")) $module->init();
					}
					// ELSE: file does not match name criteria
				}
				// ELSE: not a regular file, may be a folder.
			}
			// TODO: Sort modules, if necessary.
		}
		// ELSE: Either not a directory or cannot be opened for reading
	}
}

?>
