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
 *  @version $Id: Blog.php,v 1.5 2009/07/26 13:01:12 neven Exp $
 *  @author Neven Boyanov
 *
 */

/*
	---- NOTE: This information needs to be updated. ----
		
	The format of the generated page should be in form: "prefix_datetime topic"
	where prefix is optional and dtetime is in YYYYDDMMHHMMSS form.
	
	==== Create Capabilities ====
	
	The name of the page should be in form  "PREFIX_DATETIME TITLE" 
	... where PREFIX shold be either separate folder or just blog_ (or whatever 
	is set in the config file) to distinguish blog entries. DATE should be in 
	form of YYYYMMDD and TIME should be in form HHMMSS, to order bolg entries 
	by date and time.
	
	The text should be submited through the standard save/create page procedure.
	
	==== View Capabilities ====
	
	When a page is viewed this plugin shold detect if the page looks like blog 
	entry and parse the name properly so the date and title could be displayed.
	
	Additionaly, the previous and the next entries could be pointed.
		
	NEW:
	A handle should be added to the main source code so the plugins should be able
	to manupulate the data before page editing takes place. Name: editBegin.
	This way it will be possible for the Blog plugin to ask for the date and title
	separately and later merge them into a page name durring the page writing.
	Done: prefix "blog_" and timestamp.
*/

require_once "classes/AbstractPlugin.php";

define ('WIKISSME_PLUGIN_BLOG_REGEX_FILENAME', ''); // todo: move the regex from code here - this should be a function.
define ('WIKISSME_PLUGIN_BLOG_REGEX_PAGENAME', ''); // todo: move the regex from code here - this should be a function.

class Blog extends AbstractPlugin
{
	protected static $description = 
		"Blog management module";
	
	function Blog(&$wikissme)
	{
		parent::AbstractPlugin($wikissme);
		$this->rank = 3;
		$this->config_file = "blog";
	}
	
	public static function getDescription() { return self::$description; }
	
	public function init()
	{
		parent::init();
		if (!$this->config["folder"] && !$this->config["prefix"]) 
			// Set prefix to "blog" if no folder and no prefix were specified
			// in the plugin configuration.
			$this->config["prefix"] = "blog";
    	// print "config[{$this->config_file}]: " . var_export($this->config, TRUE) . "<br />";	// DEBUG
	}
	
	private function files_path()
	{
		global $DATA_FOLDER, $PAGES_FOLDER;
		// $files_path = ($this->config["folder"] ? ($this->config["use-datafolder"] ? "{$DATA_FOLDER}/" : "") . $this->config["folder"] : "{$PAGES_FOLDER}");
		$files_path =
			($DATA_FOLDER ? $DATA_FOLDER . '/' : '') .	// add folder where all data should reside
			($this->config["folder"] ? $this->config["folder"] : $PAGES_FOLDER);
		// print "files_path: " . var_export($files_path, true) . "<br />";	// DEBUG
		return $files_path;
	}
	
	private function parse_filename($file_name, &$matches)
	{
		// print "file_name: " . var_export($file_name, true) . "<br />";	// DEBUG
		$files_path = $this->files_path();
		$regex = '/^' . 
			($files_path ? preg_quote($files_path . '/', '/') : '' ) . 
			'(' . ($this->config["prefix"] ? $this->config["prefix"] . '_' : '') . '\d{14} .*)' .
			'\.txt/';
		$result = preg_match($regex, $file_name, $matches);
		// print "matches({$regex}): " . var_export($matches, true) . "<br />";	// DEBUG
		return $result;
	}
	
	private function parse_pagename($page_name, &$matches)
	{
		// print "page_name: " . var_export($page_name, true) . "<br />";	// DEBUG
		$regex = '/^' . ($this->config["prefix"] ? '(' . $this->config["prefix"] . ')_' : '()') . '(\d{14}) (.*)/';
		$result = preg_match($regex, $page_name, $matches);
		// print "matches({$regex}): " . var_export($matches, true) . "<br />";	// DEBUG
		return $result;
	}

	private function recent_list($num = NULL)
	{
		$result = array();
		$count = 0;
		// List existing blog entries
		$files_path = $this->files_path();
		$files_path_glob = 
			$files_path . "/" . 
			($this->config["prefix"] ? $this->config["prefix"] . "_" : "") . 
			"*.txt";
		// print "debug[" . __METHOD__ . "]: files_path_glob={$files_path_glob}<br />"; // DEBUG
		$files = glob($files_path_glob);	// TODO: Implement with opendir.
		rsort($files);
		// print "files: " . var_export($files, TRUE) . "<br />";	// DEBUG
		foreach ($files as $file_name)
		{
			if ($num != NULL && ++$count > $num) break;
			if ($this->parse_filename($file_name, $matches))
			{
				// print "---- matches: " . var_export($matches, true) . "<br />";	// DEBUG
				$matches_pagename = $matches[1];
				$result[] = $matches_pagename;
			}
		}
		// Returns list of page names
		return $result;
	}
	
	private function recent_view($num = NULL)
	{
		$blogs = $this->recent_list($num);
		// print "blogs: " . var_export($blogs, TRUE) . "<br />";	// DEBUG		
		foreach ($blogs as $blog)
		{
			// print "blog: " . var_export($blog, TRUE) . "<br />";	// DEBUG
			if ($this->parse_pagename($blog, $matches))
			{
				// print "matches: " . var_export($matches, true) . "<br />";	// DEBUG
				$matches_datetime = $matches[2];
				$matches_pagename = $matches[3];
				$blog_datetime = datetime_YYYYMMDDHHMMSS_format($matches_datetime);
				$result .=
					"{$blog_datetime} " .
					'<a href="' . $this->wikissme->script_basename . '?page=' . urlencode($blog) . '">' . $matches_pagename . '</a>'.
					"<br />";
			}
		}
		
		return $result;
	}

	function trigger_action($action)
	{
		global $PAGE_CONTENT;

		if ($action == 'blog')
		{
			$this->wikissme->page->name = "Blog";
			$PAGE_CONTENT = "";
			$blog_title = "This is what I did today";
			$PAGE_CONTENT .= 
				'<form method="post" action="' . $this->wikissme->script_basename . '">' .
				'<input type="hidden" name="blog" value="create" />' .
				'New blog entry (' . datetime_YYYYMMDDHHMMSS_format(datetime_YYYYMMDDHHMMSS()) . ')<br />' .
				'<input type="text" name="page" value="' . $blog_title . '"  style="width: 100%;" /><br />' .
				'<textarea name="content" cols="80" rows="8" style="width: 100%;">'.''.'</textarea>' .
				'<p align="right">';
			// if (!authenticated()) $PAGE_CONTENT .= tr('Mot de passe').' : <input type="password" name="sc" />';
			if (!authenticated()) $PAGE_CONTENT .= localize('INFOLABEL_PASSWORD') . ' : <input type="password" name="sc" />';
			$PAGE_CONTENT .= 
				// '<input type="submit" value="'.tr('Enregistrer').'" accesskey="s" />' .
				'<input type="submit" value="' . localize('ACTLABEL_SUBMIT') . '" accesskey="s" />' .
				'</p>' .
				'</form>';
			
			// List existing blog entries
			$PAGE_CONTENT .= "List of blog entries:<br />";
			$PAGE_CONTENT .= $this->recent_view() . "<br />";
			
			return TRUE;
		}
	}
	
	function trigger_read()
	{
		global $PAGES_FOLDER;
		// print "debug: method(" . __METHOD__ . ")<br />"; // DEBUG
		$page_name = $this->wikissme->page->name;
		// if page_name matches the blog-pagename pattern, then set the pages folder accordingly
		if ($this->parse_pagename($page_name, $matches))
		{
			// if folder for plugin pages is defined, then assign it to pages folder
			if ($this->config["folder"]) $PAGES_FOLDER = $this->config["folder"];
		}
	}
	
	function trigger_write()
	{
		global $PAGES_FOLDER;

		if (isset($_POST['blog']) && $_POST['blog'] = 'create')
		// If it is new blog, then set page name and folders accordingly
		{
			// Setup blog page file folder
			if ($this->config["folder"]) $PAGES_FOLDER = $this->config["folder"];

			// Setup blog page name
			$page_name = 
				($this->config["prefix"] ? $this->config["prefix"] . "_" : "") . 
				datetime_YYYYMMDDHHMMSS() . " " . $this->wikissme->page->name;
			$this->wikissme->page->name = $page_name;
		}
		else
		// Otherwise, check if it is an existing blog page.
		{
			$page_name = $this->wikissme->page->name;
			if ($this->parse_pagename($page_name, $matches))
			// if page name matches the blog-pagename pattern, then set the pages folder accordingly
			{
				if ($this->config["folder"]) $PAGES_FOLDER = $this->config["folder"];
			}
		}
	}
	
	function trigger_format()
	{
		global $PAGE_CONTENT;
		
		// View blog entry (if it matches filename format)
		// ... inserts/replace some text at the begining of the page.
		if ($this->parse_pagename($this->wikissme->page->name, $matches))
		{
			$matches_datetime = $matches[2];
			$matches_pagename = $matches[3];
			$blog_datetime = datetime_YYYYMMDDHHMMSS_format($matches_datetime);
			$blog_title = $matches_pagename;
			// strptime($match_datetime, "...");	// Function strptime is not implemented under Windows.
			$PAGE_CONTENT =
				"<b>Blog</b> entry from <b>{$blog_datetime}</b> about <b><i>{$blog_title}</i></b><br /><br />" . 
				$PAGE_CONTENT;
		}
	}

	function trigger_inline()
	{
		// View recent blog entries
		$result .= $this->recent_view(4) . "<br />";
		return $result;
	}
}

?>
