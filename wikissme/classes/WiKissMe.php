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
 *  @version $Id: WiKissMe.php,v 1.2 2009/08/16 10:51:25 neven Exp $
 *  @author Neven Boyanov
 *
 */

class WiKissMe
{
	private static $wikissme;
	
	private $modulesmanager;
	private $pluginsmanager;
	private $pluginsprocessor;

	private $headings;
	
	public $template_html;
	public $script_basename;
	
	public $page;
	private $pagereader;
	private $pagewriter;
	
	private $errors = array();
	
	private function WiKissMe()
	{
		$this->script_basename = basename($_SERVER['SCRIPT_NAME']);
	}
	
	// Initialize the object
	public static function init()
	{
		if (isset(self::$wikissme))
		{
			return self::$wikissme;
		}
		else
		{
			self::$wikissme = new WiKissMe();
			// $c = __CLASS__; self::$wikissme = new $c;
			return self::$wikissme;
		}
	}
	
	public function addError($error_code, $error_message)
	{
		$this->errors[] = array($error_code, $error_message);
	}
	
	public function hasErrors()
	{
		return (count($this->errors) != 0 ? TRUE : FALSE);
	}
	
	public function getErrors()
	{
		return $this->errors;
	}
	
	public function setPage(&$page)
	{
		$this->page = $page;
	}
	
	public function setPageReader(&$pagereader) { $this->pagereader = $pagereader; }
	public function getPageReader() { return $this->pagereader; }
	
	public function setPageWriter(&$pagewriter) { $this->pagewriter = $pagewriter; }
	public function getPageWriter() { return $this->pagewriter; }
	
	public function setHeadings(&$headings) { $this->headings = $headings; }
	public function getHeadings() { return $this->headings; }
	
	public function setModulesManager(&$modulesmanager) { $this->modulesmanager = $modulesmanager; }
	public function getModulesManager() { return $this->modulesmanager; }
	
	public function setPluginsManager(&$pluginsmanager) { $this->pluginsmanager = $pluginsmanager; }
	public function getPluginsManager() { return $this->pluginsmanager; }
	
	public function setPluginsProcessor(&$pluginsprocessor) { $this->pluginsprocessor = $pluginsprocessor; }

	public function test() { $this->page->content = "THIS IS WIKISSME [:*]"; }
}

?>
