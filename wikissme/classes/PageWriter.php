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
 *  @version $Id: PageWriter.php,v 1.3 2009/07/25 14:56:50 neven Exp $
 *  @author Neven Boyanov
 *
 */

class PageWriter
{
	protected $wikissme;
	
	private $writePermitted = FALSE;	// MUST be disabled by default.
	private $page;

	public function PageWriter(&$wikissme)
	{
		$this->wikissme = $wikissme;
	}
	
	public function setWritePermitted($writePermitted = TRUE)
	{
		$this->writePermitted = $writePermitted;
	}
	
	public function isWritePermitted()
	{
		return $this->writePermitted;
	}
	
	public function proceed(&$page)
	{
		global $DATA_FOLDER, $HISTORY_FOLDER;

		$this->page = $page;
		$page_filepath = $this->page->getFilepath();

		if ($_POST['content'] == '')
		{
			// of content empty - DELETE the page ...
			if (file_exists($page_filepath)) unlink($page_filepath);
			// NOTE: Page deletion should be handled differently, may be by adding ".deleted" sufix to the 
			//       file name. This will allow afterwards the page to be removed corectly from the repository.
		}
		else
		{
			// Open file for writing, store content ...
			if (!$file = @fopen($page_filepath, 'w')) 
				die('Could not write page!');
			//~ $safe_content = htmlentities($_POST['content'],ENT_COMPAT,"UTF-8");
			$safe_content = str_replace('<','&lt;', $_POST['content']);
			if (get_magic_quotes_gpc())
				fputs($file, stripslashes($safe_content));
			else
				fputs($file, $safe_content);
			fclose($file);
			// Save a backup copy of the page ...
			if ($HISTORY_FOLDER <> '')
			{
				$complete_dir_s = $DATA_FOLDER . '/' . $HISTORY_FOLDER . '/' . $this->page->name . '/'; // TODO BUG
				if (! $dir = @opendir($complete_dir_s))
				{
					mkdir($complete_dir_s);
					chmod($complete_dir_s,0777);
				}
				// Open file for writing, backup content ...
				if (! $file = @fopen($complete_dir_s . date('Ymd-Hi', mktime(date('H') + $LOCAL_HOUR)) . '.bak', 'a')) 
					die('Could not write backup of page!');
				fputs($file, "\n// " . $datetw . ' / ' . ' ' . $_SERVER['REMOTE_ADDR'] . "\n");
				if (get_magic_quotes_gpc())
					fputs($file, stripslashes($safe_content));
				else
					fputs($file, $safe_content);
				fclose($file);
			}
		}
	}
}

?>
