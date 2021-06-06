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
 *  @version $Id: index.php,v 1.1 2009/07/04 15:33:16 neven Exp $
 *  @author Neven Boyanov
 *
 */

$script = "wiki.php";
$location = 
	"http" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on" ? "s" : "") . 
	"://{$_SERVER['SERVER_NAME']}" . 
	($_SERVER['SERVER_PORT'] != 80 ? ":{$_SERVER['SERVER_PORT']}" : "") .
	($_SERVER['SCRIPT_NAME'] ? dirname($_SERVER['SCRIPT_NAME']) : "") .
	"/{$script}" .
//	($_SERVER['QUERY_STRING'] ? "?{$_SERVER['QUERY_STRING']}" : "") .
	"";	// Any additional parameters go here.
print("location: {$location}<br />");
header("Location: {$location}");

// DEBUG
// print_r($_SERVER);
// $script_basename = basename($_SERVER['SCRIPT_NAME']);
// print("script_basename: {$script_basename}<br />");

?>
