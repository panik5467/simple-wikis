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
 *  @version $Id: file.php,v 1.1 2009/11/02 21:16:59 neven Exp $
 *  @author Neven Boyanov
 *
 */

require_once "config/config.php";

/*
	IMPORTANT: 
	* This module works assuming that all files are publicly available.
	* No any sort of user privileges are checked.
	* This module does not change the content of the files.
*/

	$files_location = $DATA_FOLDER . '/files';
	$file_filename = $_GET["name"];
	$file_filepath = $files_location . '/' . $file_filename;
	$file_filepath_pathinfo = pathinfo($file_filepath);
	// print "file_filepath_pathinfo: " . var_export($file_filepath_pathinfo, TRUE) . "\n";
	$file_filepath_basename = $file_filepath_pathinfo['basename'];
	$file_filepath_extension = $file_filepath_pathinfo['extension'];

	// print "extension:{$file_filepath_extension}\n";
	switch ($file_filepath_extension)
	{
		/*
			J2ME - JAR/JAD files. Reference: http://developers.sun.com/mobility/midp/articles/deploy/
			Reconfigure the web server so that it recognizes JAD and JAR files:
			* For the JAD file type, set the file extension to .jad and the MIME type to text/vnd.sun.j2me.app-descriptor.
			* For the JAR file type, set the file extension to .jar and the MIME type to application/java-archive.
		*/

		case  "gif":	$file_contenttype = "image/gif"; break;
		case  "png":	$file_contenttype = "image/png"; break;
		case  "jpg":	$file_contenttype = "image/jpg"; break;
		case  "txt":	$file_contenttype = "text/plain"; break;
		case  "htm":	$file_contenttype = "text/html"; break;
		case  "html":	$file_contenttype = "text/html"; break;
		case  "pdf":	$file_contenttype = "application/pdf"; break;

		default: $file_contenttype="application/force-download"; break;
	}
	// print "contenttype:{$file_contenttype}\n";
	
	header("Content-Type: {$file_contenttype}");
	// header("Content-Type: application/force-download");
	header("Content-Length: " . filesize($file_filepath));
	header("Content-Disposition: attachment; filename={$file_filepath_basename}");
	// TODO: Add modification date in the headers.
	// TODO: Add cache control.
	readfile($file_filepath);

?>
