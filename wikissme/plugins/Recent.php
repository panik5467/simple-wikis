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
 *  @version $Id: Recent.php,v 1.3 2009/07/25 15:07:32 neven Exp $
 *  @author Neven Boyanov
 *
 */

require_once "classes/AbstractPlugin.php";

define ('WIKISSME_PLUGIN_RECENT_PAGESNUM', '20');

class Recent extends AbstractPlugin
{
	protected static $description = 
		"Recent pages";
	public $on = TRUE;
	
	function Recent($wikissme)
	{
		parent::AbstractPlugin($wikissme);
		$this->rank = 3;
	}
	
	public static function getDescription() { return self::$description; }
	
	function trigger_action($action)
	{
		global $DATA_FOLDER, $PAGES_FOLDER, $PAGE_TITLE_link, $PAGE_CONTENT;
		global $LOCAL_HOUR, $editable;
		$TIME_FORMAT = "%Y-%m-%d | %H:%M";
		if ($action == 'recent')
		{
			// Changements recents
			$PAGE_TITLE_link = FALSE;
			$editable = FALSE;
			$folder = 
				getcwd() . '/' . 
				($DATA_FOLDER ? $DATA_FOLDER . '/' : '') .	// add folder where all data should reside
				($PAGES_FOLDER ? $PAGES_FOLDER . '/' : '') .	// add folder where the page resides
				'/';
			$dir = opendir($folder);
			while ($file = readdir($dir))
			{
				if (preg_match('/(.*)\.txt$/', $file, $matches))
				{
					$filetime[$file] = filemtime(Page::filepath($matches[1]));
				}
			}
			arsort($filetime);
			$filetime = array_slice($filetime, 0, WIKISSME_PLUGIN_RECENT_PAGESNUM);
			// print "filetime: " . var_export($filetime, TRUE) . "<br />";	// DEBUG
			$PAGE_CONTENT .= "<table>";
			foreach ($filetime as $filename => $timestamp)
			{
				$filename = substr($filename, 0, strlen($filename) - 4);
				$PAGE_CONTENT .=
					'<tr>' .
					'<td>' . '<a href="' . $this->wikissme->script_basename . '?page=' . urlencode($filename) . ' ">'.$filename.'</a> ' . '</td>' .
					'<td>' . strftime($TIME_FORMAT, $timestamp + $LOCAL_HOUR * 3600) . '</td>' . 
					'<td>' . '[<a href="' . $this->wikissme->script_basename . '?page=' . urlencode($filename) . '&amp;action=diff">diff</a>]' . '</td>' . 
					'</tr>';
			}
			$PAGE_CONTENT .= "</table>";
			return TRUE;
		}
	}
}

?>
