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
 *  @version $Id: Comments.php,v 1.6 2009/08/16 10:52:53 neven Exp $
 *  @author Neven Boyanov
 *
 */

/*
	The format of the generated page should be in form: "prefix page_name datetime"
	where prefix is optional and datetime is in YYYYDDMMHHMMSS form.
*/

require_once "classes/AbstractPlugin.php";

define ('WIKISSME_PLUGIN_COMMENTS_REGEX_FILENAME', ''); // todo: move the regex from code here
define ('WIKISSME_PLUGIN_COMMENTS_REGEX_PAGENAME', ''); // todo: move the regex from code here

class Comments extends AbstractPlugin
{
	protected static $description = 
		"Comments management module";
	
	function Comments($wikissme)
	{
		parent::AbstractPlugin($wikissme);
		$this->rank = 3;
		$this->config_file = "comments";
	}
	
	public static function getDescription() { return self::$description; }
	
	public function init()
	{
		parent::init();
		if (!$this->config["folder"] && !$this->config["prefix"]) 
			// Set prefix to "comments" if no folder and no prefix were 
			// specified in the plugin configuration.
			$this->config["prefix"] = "comment";
    	// print "config[{$this->config_file}]: " . var_export($this->config, TRUE) . "<br />";	// DEBUG
	}

	private function files_path()
	{
		global $DATA_FOLDER, $PAGES_FOLDER;
		// $files_path = ($this->config["folder"] ? ($this->config["use-datafolder"] ? "{$DATA_FOLDER}/" : "") . $this->config["folder"] : "{$PAGES_FOLDER}");
		$files_path =
			($DATA_FOLDER ? $DATA_FOLDER . '/' : '') .	// add folder where all data should reside
			($this->config["folder"] ? $this->config["folder"] : $PAGES_FOLDER);
		return $files_path;
	}

	private function parse_filename($file_name, $page_name, &$matches)
	{
		// print "file_name: " . var_export($file_name, true) . "<br />";	// DEBUG
		$regex = '/^' . 
			($this->files_path() ? preg_quote($this->files_path() . '/', '/') : '' ) . 
			'(' . ($this->config["prefix"] ? $this->config["prefix"] . ' ' : '') . 
			($page_name != NULL ? preg_quote($page_name) : '.*') .
			' \d{14})' . '\.txt/';
		$result = preg_match($regex, $file_name, $matches);
		// print "matches({$regex}): " . var_export($matches, true) . "<br />";	// DEBUG
		return $result;
	}

	function page_load($page_name)
	{
		$page_folder = $this->files_path();
		$page_filename = $page_folder . '/' . $page_name . '.txt';
        $file = fopen($page_filename, 'r');
        if ($file)
        {
	        $content = fread($file, filesize($page_filename));
	        fclose($file);
        }
        return $content;
	}
	
	private function parse_pagename($page_name, &$matches)
	{
		// print "page_name: " . var_export($page_name, true) . "<br />";	// DEBUG
		$regex = '/^' . ($this->config["prefix"] ? '(' . $this->config["prefix"] . ') ' : '()') . '(.*) (\d{14})/';
		$result = preg_match($regex, $page_name, $matches);
		// print "matches({$regex}): " . var_export($matches, true) . "<br />";	// DEBUG
		return $result;
	}
	
	private function recent_list($page_name = NULL, $num = NULL)
	{
		$result = array();
		$count = 0;
		// List existing comment entries
		$files_path = $this->files_path();
		$files_path_glob = 
			$files_path . "/" . 
			($this->config["prefix"] ? $this->config["prefix"] . " " : "") . 
			"*.txt";
		// print "debug[" . __METHOD__ . "]: files_path_glob={$files_path_glob}<br />"; // DEBUG
		$files = glob($files_path_glob);	// TODO: Implement with opendir.
		rsort($files);
		// print "files: " . var_export($files, TRUE) . "<br />";	// DEBUG
		foreach ($files as $file_name)
		{
			if ($num != NULL && ++$count > $num) break;
			if ($this->parse_filename($file_name, $page_name, $matches))
			{
				// print "---- matches: " . var_export($matches, true) . "<br />";	// DEBUG
				$matches_pagename = $matches[1];
				$result[] = $matches_pagename;
			}
		}
		// Returns list of page names
		return $result;
	}
	
	private function recent_view($page_name = NULL, $num = NULL, $with_content = TRUE)
	{
		$comments = $this->recent_list($page_name);
		// print "comments: " . var_export($comments, TRUE) . "<br />";	// DEBUG
		foreach ($comments as $comment)
		{
			if ($this->parse_pagename($comment, $matches))
			{
				$matches_pagename = $matches[2];
				$matches_datetime = $matches[3];
				$comment_datetime = datetime_YYYYMMDDHHMMSS_format($matches_datetime);
				if ($with_content)
				{
					$result .=
						'<hr style="text-align: right; margin-left: 0; width: 60%;" />' .
						'<a href="' . $this->wikissme->script_basename . '?page=' . urlencode($comment) . '">' . $comment_datetime . '</a>'.
						"<br />";
					$comment_content = $this->page_load($comment);
					$result .= "{$comment_content}<br />";
				}
				else
				{
					$result .=
						$matches_pagename . ' -- ' .
						'<a href="' . $this->wikissme->script_basename . '?page=' . urlencode($comment) . '">' . $comment_datetime . '</a>'.
						"<br />";
				}
			}
		}
		return $result;
	}
	
	function trigger_action($action)
	{
		global $PAGE_CONTENT;
		if ($action == 'comments')
		{
			$this->wikissme->page->name = "Comments";
			$PAGE_CONTENT = "";
			
			// List existing comment entries
			$PAGE_CONTENT .= "List of comment entries:<br /><br />";
			$PAGE_CONTENT .= $this->recent_view(NULL, NULL, FALSE) . "<br />";
			
			return TRUE;
		}
	}
	
	function trigger_read()
	{
		global $PAGES_FOLDER;
		// print "debug: method(" . __METHOD__ . ")<br />"; // DEBUG
		$page_name = $this->wikissme->page->name;
		// if page_name matches the comment-pagename pattern, then set the pages folder accordingly
		if ($this->parse_pagename($page_name, $matches))
		{
			// if folder for plugin pages is defined, then assign it to pages folder
			if ($this->config["folder"]) $PAGES_FOLDER = $this->config["folder"];
		}
	}
	
	function trigger_write()
	{
		global $PAGES_FOLDER;

		if (isset($_POST['comment']) && $_POST['comment'] = 'create')
		// If it is a new comment entry, then set page name and folder accordingly
		{
			// TODO: Should veriify if page (that the comment is about) exists.
			//       Otherwise fake comments could be submitted.
			// Add prefix and date to the new comment entry
			$this->wikissme->page->name = 
				($this->config["prefix"] ? $this->config["prefix"] . ' ' : '') . 
				$this->wikissme->page->name . ' ' . datetime_YYYYMMDDHHMMSS();
			
			// ---- Captcha processing -----
			// print "SESSION: " . var_export($_SESSION, TRUE) . "<br /><br />";	// DEBUG
			
			// Get the pagewriter object
			$pagewriter = $this->wikissme->getPageWriter();
			// print "pagewriter: " . get_class($pagewriter) . "<br />";	// DEBUG
			
			// TEST: disable writing temporarly
			// $pagewriter->setWritePermitted(FALSE);	// DEBUG
			// $this->wikissme->addError(-1, "(DEBUG/TESTING) Captcha - writing not permited");	// DEBUG
			
			// Get module Captcha settings
			$modules = $this->wikissme->getModulesManager()->getModules();
			$module_captcha_active = $modules['Captcha']->isActive();
			// $text .=  "module_captcha_active: " . var_export($module_captcha_active, TRUE) . "<br />";	// DEBUG

			$captcha_keys_match = ($_SESSION['CaptchaSecurityImages_key'] == $_POST['CaptchaSecurityImages_key']);
			$captcha_codes_match = ($_SESSION['CaptchaSecurityImages_code'] == $_POST['CaptchaSecurityImages_code']);
			
			/*
			// DEBUG
			print "keys(session/post): '{$_SESSION['CaptchaSecurityImages_key']}' == '{$_POST['CaptchaSecurityImages_key']}' - " . 
				($captcha_keys_match ? "true" : "false") . "<br />";
			// DEBUG
			print "codes(session/post): '{$_SESSION['CaptchaSecurityImages_code']}' == '{$_POST['CaptchaSecurityImages_code']}' - " . 
				($captcha_codes_match ? "true" : "false") . "<br />";
			*/

			if ( ($module_captcha_active && 
					!empty($_SESSION['CaptchaSecurityImages_key']) && $captcha_keys_match &&
					!empty($_SESSION['CaptchaSecurityImages_code']) && $captcha_codes_match)
				|| (!$module_captcha_active) )
			{
				// print "<span style='color: green;'><b>accepted</b></span><br />";	// DEBUG
				
				// Set WritePermitted flag to TRUE, i.e. enable writing.
				$pagewriter->setWritePermitted(TRUE);

				// On success the code should be cleared so it will not be used to submit another form.
				// unset($_SESSION['CaptchaSecurityImages_code']);
				// unset($_SESSION['CaptchaSecurityImages_key']);

				// Setup comment page file folder
				if ($this->config["folder"]) $PAGES_FOLDER = $this->config["folder"];
			}
			else
			{
				// print "<span style='color: red;'><b>security code incorrect</b></span><br />";	// DEBUG
	
				// Set WritePermitted flag to FALSE, i.e. disable writing.
				$pagewriter->setWritePermitted(FALSE);
				$this->wikissme->addError(-1, "Security code incorrect");	// DEBUG

				// On failure the code should be cleared so it will not be used to try again.
				// unset($_SESSION['CaptchaSecurityImages_code']);
				// unset($_SESSION['CaptchaSecurityImages_key']);
			}
		}
		else
		// Otherwise, check if the page is an existing comment entry
		{
			$page_name = $this->wikissme->page->name;
			if ($this->parse_pagename($page_name, $matches))
			// if page name matches the comment-pagename pattern, then set the pages folder accordingly
			{
				if ($this->config["folder"]) $PAGES_FOLDER = $this->config["folder"];
			}
		}
	}
	
	function trigger_format()
	{
		global $PAGE_CONTENT;
		
		// View comment entry (if it matches pagename format)
		// ... inserts some text at the begining of the page.
		if ($this->parse_pagename($this->wikissme->page->name, $matches))
		{
			$matches_pagename = $matches[2];
			$matches_datetime = $matches[3];
			$comment_datetime = datetime_YYYYMMDDHHMMSS_format($matches_datetime);
			$PAGE_CONTENT =
				"Comment entry from <b>{$comment_datetime}</b> " .
				'about <a href="' . $this->wikissme->script_basename . '?page=' . urlencode($matches_pagename) . '">' . $matches_pagename . '</a>' .
				"<br /><br />" .
				$PAGE_CONTENT;
		}
	}

	function trigger_inline()
	{
		// print "SESSION: " . var_export($_SESSION, TRUE) . "<br /><br />";	// DEBUG

		$modules = $this->wikissme->getModulesManager()->getModules();
		$module_captcha_active = $modules['Captcha']->isActive();
		// $text .=  "module_captcha_active: " . var_export($module_captcha_active, TRUE) . "<br />";	// DEBUG
		
		$CaptchaSecurityImages_key = Captcha::generateKey();
		// $text .= "CaptchaSecurityImages_key: {$CaptchaSecurityImages_key}<br />";	// DEBUG
		$_SESSION['CaptchaSecurityImages_key'] = $CaptchaSecurityImages_key;

		$text .= 
			'<form method="post" action="' . $this->wikissme->script_basename . '">' .
			'<input type="hidden" name="page" value="' . $this->wikissme->page->name . '" />' .
			'<input type="hidden" name="comment" value="create" />' .
			'New comment entry (' . datetime_YYYYMMDDHHMMSS_format(date("YmdHis")) . ')<br />' .
			'<textarea name="content" cols="80" rows="4" style="width: 75%;">'.''.'</textarea><br />' .
			($module_captcha_active ? 
				'<img src="captcha.php?key=' . $CaptchaSecurityImages_key . '" /><br />' .
				'secret code:<br />' .
				'<input type="text" name="CaptchaSecurityImages_code" style="width: 120px;"/><br />' .
				'<input type="hidden" name="CaptchaSecurityImages_key" value="' . $CaptchaSecurityImages_key . '" />'
				: '') .
			// '<input type="submit" value="'.tr('Enregistrer').'" accesskey="s" />' .
			'<input type="submit" value="' . localize('ACTLABEL_SUBMIT') . '" accesskey="s" />' .
			'</form><br /><br />';
		
		// View existing comment entries
		$text .= "Comment entries:<br />";
		$text .= $this->recent_view($this->wikissme->page->name) . "<br />";

		return $text;
	}
}

?>
