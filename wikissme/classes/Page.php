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
 *  @version $Id: Page.php,v 1.3 2009/07/25 14:56:50 neven Exp $
 *  @author Neven Boyanov
 *
 */

class Page
{
	public $name;
	public $content;
	public $date_modified;
	public $date_accessed;
	
	private $filepath;
	
	public function getFilepath()
	{
		$zones_folder = NULL;
		$zone_folder = NULL;
		$pages_folder = NULL;
		$this->filepath = self::filepath($this->name);
		// TODO: Possible optimization - rebuild the filepath variable everytime
		//       any of it's components change, here just return its value.
		return $this->filepath;
	}
	
	public static function filename($page_name)
	{
		$filename = $page_name . '.txt';
		// print "filename: {$filename}<br />";	// DEBUG
		return $filename;
	}

	public static function filepath($page_name, $pages_folder = NULL, $zone_folder = NULL, $zones_folder = NULL)
	{		
		// installation_path/zones_folder/zone_folder/pages_folder/page_name.page_file_extension
		//  ---------------   ----------   ---------   ----------   -------   ----------------- 
		//                  $zones_folder $zone_folder $pages_folder $page_name '.txt'
		// Example: /my_wikissme/zones/research/pages/mobile.txt
		
		if ($pages_folder == NULL && $zone_folder == NULL && $zones_folder == NULL)
		// if all additional parameters are not present, then use ald style with a global variable
		{
			global $DATA_FOLDER, $PAGES_FOLDER;
			$filepath = 
				($DATA_FOLDER ? $DATA_FOLDER . '/' : '') .	// add folder where all data should reside
				($PAGES_FOLDER ? $PAGES_FOLDER . '/' : '') .	// add folder where the page resides
				self::filename($page_name);
		}
		else
		// otherwise compose the file path using the defined variables
		{
			$filepath = 
				($zones_folder ? $zones_folder . '/' : '') .	// add folder wher all zones reside
				($zone_folder ? $zone_folder . '/' : '') .	// add folder where the zone resides
				($pages_folder ? $pages_folder . '/' : '') .	// add folder where the page resides
				self::filename($page_name);
		}
		
		// print "filepath: {$filepath}<br />";	// DEBUG
		return $filepath;
	}
}

?>
