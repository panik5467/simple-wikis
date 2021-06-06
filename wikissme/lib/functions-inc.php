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
 *  @subpackage Lib
 *  @version $Id: functions-inc.php,v 1.3 2009/07/25 19:52:32 neven Exp $
 *  @author Neven Boyanov
 *
 */

function datetime_YYYYMMDDHHMMSS()
{
	return date("YmdHis");
}

function datetime_YYYYMMDDHHMMSS_parse($yyyymmddhhmmss, &$matches)
{
	if (preg_match('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', $yyyymmddhhmmss, $matches))
		return TRUE;
	else
		return FALSE;
}

function datetime_YYYYMMDDHHMMSS_format($yyyymmddhhmmss)
{
	datetime_YYYYMMDDHHMMSS_parse($yyyymmddhhmmss, $matches);
	$text =
		"{$matches[1]}-{$matches[2]}-{$matches[3]}|" . 
		"{$matches[4]}:{$matches[5]}:{$matches[6]}";
	return $text;
}

function authentication_cookie_remove()
{
	setcookie('WiKissMeAuthPass');
	$_COOKIE['WiKissMeAuthPass'] = ''; // remove cookie without reloading
}

/** test si l'utilisateur est authentifie
* pose un cookie si absent
*/
function authenticated()
{
	global $ADMIN_PASSWORD;
	$pwd = md5($ADMIN_PASSWORD);
	if (
		( ((isset($_COOKIE['WiKissMeAuthPass'])) && ($_COOKIE['WiKissMeAuthPass'] == $pwd)) || 
			((isset($_POST['sc']) && $_POST['sc'] == $ADMIN_PASSWORD)) ) &&
		($ADMIN_PASSWORD != '') 
		)
	{
		if (
			($ADMIN_PASSWORD != '') && (empty($_COOKIE['WiKissMeAuthPass']) || 
			($_COOKIE['WiKissMeAuthPass'] != $pwd))
			)
		{
			setcookie('WiKissMeAuthPass',$pwd, time() + 365*24*3600);
			$_COOKIE['WiKissMeAuthPass'] = $pwd;
		}
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

/** load language file
*/
function loadLang($lang)
{
	global $strings;
	// include locale file if needed
	$locale_fname = 'locale/'.$lang.'.php';
	if (file_exists($locale_fname))
	 include_once($locale_fname);
	else
	 $strings = array();
} // loadLang()

// ---- Localize functions ----

function localize($phrase)
{
	global $localize_phrases;
	$text = $localize_phrases[$phrase];
	return $text;
}

function localize_list()
{
	global $localize_phrases;
	foreach ($localize_phrases as $key => $value)
	{
		print "{$key}: {$value}<br />";
	}
}

/** translate a string if needed
* $str: the string to be translated
* return the translated string
*/
function tr($str)
{
	global $strings; // localized strings from locale/xx.php
	if (array_key_exists($str,$strings) and !empty($strings[$str]))
	{
	 return $strings[$str];
	}
	else
	{
	 return $str;
	}
}

/*
 * Call a method for all plugins
 * $mname: method name
 * [...] : method arguments
 * return: TRUE if treated by a plugin
 * NOTE: This functions should be used ONLY for old-style plugins.
 */
function plugins_old_call_method($mname)
{
	global $plugins_old_list;
	// print "plugins_old_list: " . var_export($plugins_old_list, TRUE) . "<br />"; // DEBUG
	$ret = FALSE;
	foreach ($plugins_old_list as $plugin)
	 if (method_exists($plugin,$mname))
	 {
	 	// print("Debug:" . __FUNCTION__ . " method_exists " . get_class($plugin) . " / $mname<br />\n");
	    $args = func_get_args();
	    $ret |= call_user_func_array(array($plugin,$mname),array_slice($args,1));
	 }
	 else
	 {
	 	// print("Debug:" . __FUNCTION__ . " !method_exists " . get_class($plugin) . " / $mname<br />\n");
	 }
	return $ret;
}

function plugins_sortbyrank_callback($p1, $p2)
{
	(method_exists(get_class($p1), "getRank") ? $p1_rank = $p1->getRank() : $p1_rank = 3);
	(method_exists(get_class($p2), "getRank") ? $p2_rank = $p2->getRank() : $p2_rank = 3);
	// print "cmp({$p1_rank},{$p2_rank})<br />";	// DEBUG
	if ($p1_rank == $p2_rank)
		$result = 0;
	else 
		($p1_rank < $p2_rank ? $result = -1 : $result = 1);
	return $result;
}

function plugins_sortbyrank(&$ps)
{
  // Sort Plugins by their rank.
  uasort($ps, "plugins_sortbyrank_callback");
}

?>
