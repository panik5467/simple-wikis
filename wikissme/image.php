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
 *  @version $Id: image.php,v 1.1 2009/11/02 21:16:59 neven Exp $
 *  @author Neven Boyanov
 *
 */

require_once "config/config.php";

/*
	IMPORTANT: 
	* This module works assuming that all images are with public access.
	* No any sort of privileges are checked.
	* This module does not change the content of any files.
*/

	$images_location = $DATA_FOLDER . '/files';
	$image_filename = $_GET["name"];
	$image_filepath = $images_location . '/' . $image_filename;
	$image_filepath_pathinfo = pathinfo($image_filepath);
	// print "image_filepath_pathinfo: " . var_export($image_filepath_pathinfo, TRUE) . "\n";
	$image_filepath_basename = $image_filepath_pathinfo['basename'];
	$image_filepath_extension = $image_filepath_pathinfo['extension'];
	// print "extension:{$image_filepath_extension}\n";
	switch ($image_filepath_extension)
	{
		case "gif": $image_contenttype="image/gif"; break;
		case "png": $image_contenttype="image/png"; break;
		case "jpg": $image_contenttype="image/jpg"; break;
		default: $image_contenttype="application/force-download"; break;
	}
	// print "contenttype:{$image_contenttype}\n";
	header("Content-Type: {$image_contenttype}");
	header('Content-Length: ' . filesize($image_filepath));
	header("Content-Disposition: filename={$image_filepath_basename}");
	readfile($image_filepath);

?>
