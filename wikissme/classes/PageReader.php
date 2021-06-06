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
 *  @version $Id: PageReader.php,v 1.4 2009/07/25 14:56:50 neven Exp $
 *  @author Neven Boyanov
 *
 */

class PageReader
{
	protected $wikissme;
	
	private $readPermitted = TRUE;
	private $page;

	public function PageReader(&$wikissme)
	{
		$this->wikissme = $wikissme;
	}
	
	public function setReadPermitted($readPermitted = TRUE)
	{
		$this->readPermitted = $readPermitted;
	}
	
	public function isReadPermitted()
	{
		return $this->readPermitted;
	}
	
	public function proceed(&$page)
	{
		global $DATA_FOLDER, $HISTORY_FOLDER, $gtime;

		$this->page = $page;
		// $page_filepath = self::filepath($this->page->name);
		$page_filepath = $this->page->getFilepath();

		if (file_exists($page_filepath))
		{
			// Get file date and time.
			$date_modified = date('Y-m-d H:i:s', @filemtime($page_filepath) + $LOCAL_HOUR * 3600);
			// print "date_modified: {$date_modified}<br />";	// DEBUG
			$this->page->date_modified = $date_modified;
			
			// TODO: Replace this by file_get_contents() function, if possible.
			if ($file = @fopen($page_filepath, 'r'))
			{
				// Read the file content.
				$content = @fread($file, @filesize($page_filepath));
				@fclose($file);
	
				// TODO: This functionality should be implemented differently.
				// Restore a page
				if (isset($_GET['page']) && isset($gtime) && isset($_GET['restore']) && $_GET['restore'] == 1)
				{
					if ($file = @fopen($DATA_FOLDER . '/' . $HISTORY_FOLDER . '/' . $this->page->name . '/' . $gtime, 'r'))
					{
						$content = "\n" . @fread($file, @filesize($DATA_FOLDER . '/' . $HISTORY_FOLDER . '/' . $this->page->name . '/' . $gtime)) . "\n";
						@fclose($file);
					}
				}
				
				$this->page->content = $content;
			}
			else
			{
				if ($action <> '')
				{
					// NOTE: This is unknown situation, copied from the original 
					//       code, should be researched.
				}
			}
		}
		else
		{
			// The page file was not found.
			$this->wikissme->addError(-1, localize('INFOLABEL_PAGEEMPTY') . ': ' . stripslashes($this->page->name));
		}
	}
	
	// Not in use - TO BE REMOVED
	// Moved to class Page
	// public static function filename($name)
	
	// Not in use - TO BE REMOVED
	// Moved to class Page
	// public static function filepath($name)
}

?>
