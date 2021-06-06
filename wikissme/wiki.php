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
 *  Based on WiKiss code, partially on TigerWiki and other derivatives.
 *
 *  @package WiKissMe
 *  @version $Id: wiki.php,v 1.6 2009/11/02 21:08:52 neven Exp $
 *  @author Neven Boyanov
 *
 * ORIGINAL COMMENTS:
 *   WiKiss - http://wikiss.tuxfamily.org/
 *   Licence GNU/GPLv2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *   Copyright (C) JJL 2007
 *   Basé sur TigerWiki 2.22 par Chabel.org - http://chabel.org
 *
 * Modified Edition by Neven Boyanov, therefore the M.E. in WiKissMe.
 */

// performances calculation
global $LOGS_PERFORMANCE_ON;
if ($LOGS_PERFORMANCE_ON)
{
	$performance_mt1 = microtime();
}

// Starintg PHP session
// Required for some modules to work: captcha, etc.
session_start();

$WIKISSME_VERSION = file_get_contents("VERSION");

// UTF-8 settings
ini_set('default_charset','UTF-8'); // Default character set for auto content type header
header('Content-type: text/html; charset=UTF-8');

// Include configuration
include('config/config.php');

// Include required modules
require_once "lib/functions-inc.php";
require_once "classes/Module.php";
require_once "classes/AbstractModule.php";
require_once "classes/Headings.php";
require_once "classes/ModulesManager.php";
require_once "classes/Page.php";
require_once "classes/PageReader.php";
require_once "classes/PageWriter.php";
require_once "classes/PluginsManager.php";
require_once "classes/PluginsProcessor.php";
require_once "classes/WiKissMe.php";

// Regular Expressions
define ('WIKISSME_REGEX_HRLINE', '/----*(\r\n|\r|\n)/m');
define ('WIKISSME_REGEX_EMAILADDRESS', '#([0-9a-zA-Z\./~\-_]+@[0-9a-z\./~\-_]+)#i');

/* ==== Initialisations ==== */
$PAGE_CONTENT = ''; // contenu de la page
$HISTORY_LINK_HTML = ''; // lien vers l'historique

// Setup Theme
$request_theme = $_REQUEST['theme'];
if ($request_theme) { $THEME = $request_theme; }
$THEME_PATH = $THEMES_FOLDER . '/' . $THEME;
$template = $THEME_PATH . '/' .'template.html'; // Fichier template

$PAGE_TITLE_link = TRUE; // y-a-t-il un lien sur le titre de la page ?
// $html_toc = ''; // Table Of Content	// TO BE REMOVED - not in use anymore
$editable = TRUE; // la page est editable, TODO: move this attribute to Page object.

// Setup wikissme object.
$wikissme = WiKissMe::init();
// print("script_basename: {$wikissme->script_basename}<br />\n");
$wikissme->template_html = &$TEMPLATE_HTML;	// MUST be passed by reference.

// Setup Headings object
$headings = new Headings($wikissme);
$wikissme->setHeadings($headings);

// Setup Page object
$page = new Page();
// $page->name = &$PAGE_NAME;	// Not needed anymore, should be removed.
$page->content = &$PAGE_CONTENT;
$wikissme->setPage($page);

// Setup PageReader
$pagereader = new PageReader($wikissme);
$wikissme->setPageReader($pagereader);

// Setup PageWriter
$pagewriter = new PageWriter($wikissme);
$wikissme->setPageWriter($pagewriter);

// Setup the Modules Manager
$modules_folder = 'modules';
$modulesmanager = new ModulesManager($wikissme);
$modulesmanager->load($modules_folder);
$wikissme->setModulesManager($modulesmanager);

// Setup the Plugins Manager
$plugins_folder = 'plugins'; // repertoire ou stocker les plugins
$plugins_old_list = array(); // list of old-style plugins instances
// $plugins_list = array(); // list of plugins instances ---- NO LONGER IN USE
$pluginsmanager = new PluginsManager($wikissme);
$pluginsmanager->load($plugins_folder);
$wikissme->setPluginsManager($pluginsmanager);
$plugins_old_list = $pluginsmanager->getPluginsOld();

// Setup the Plugins Processor
$pluginsprocessor = new PluginsProcessor($wikissme);
$pluginsprocessor->setPluginsManager($pluginsmanager);
$wikissme->setPluginsProcessor($pluginsprocessor);

// Load current language
loadLang($WIKI_LANG);

/** Traitement des variables passées au script */
// Conversion en UTF-8
if (extension_loaded('mbstring'))
{
  ini_set('mbstring.language','Neutral'); // Set default language to Neutral(UTF-8) (default)
  ini_set('mbstring.internal_encoding','UTF-8'); // Set default internal encoding to UTF-8
  ini_set('mbstring.http_output','UTF-8'); // Set HTTP output encoding to UTF-8
  ini_set('mbstring.detect_order','UTF-8,ISO-8859-1'); // Set default character encoding detection order
  ini_set('mbstring.func_overload',MB_OVERLOAD_STRING);

  $get_conv = array('page','query');
  $post_conv = array('sc','content','page');
  foreach ($get_conv as $get_key)
  {
     if (isset($_GET[$get_key]))
     {
        // print "$get_key detected:". mb_detect_encoding($_GET[$get_key])."\n"; // DBG
        $_GET[$get_key] = mb_convert_encoding($_GET[$get_key],'UTF-8',mb_detect_encoding($_GET[$get_key]));
     }
  }
  foreach ($post_conv as $post_key)
     if (isset($_POST[$post_key]))
        $_POST[$post_key] = mb_convert_encoding($_POST[$post_key],'UTF-8',mb_detect_encoding($_POST[$post_key]));
}

// ---- Setup localize data ----

	$localize_language = $WIKI_LANG;
	if (!$localize_language) $localize_language = "en";
	
	$localize_includefilename = "{$localize_language}-inc.php";
	include "locs/{$localize_includefilename}";
	
	$localize_phrases_arrayname = "localize_phrases_{$localize_language}";
	$localize_phrases = $$localize_phrases_arrayname;
	
	// print "localize_phrases: " . var_export($localize_phrases, true) . "<br />";	// DEBUG
	// localize_list();	// DEBUG

// ----

	// Erase the cokie that holds the administrative password
	if (isset($_GET['erasecookie']))
	{
		authentication_cookie_remove();
	}

// IMPORTANT: If the method is GET then the page(s) will be viewed.

	// Trouver la page a afficher
	if (isset($_GET['page']))
	{
		$page->name = stripslashes($_GET['page']);
	}
	else
	{
		$_GET['page'] = '';
		if (isset($_GET['action']))
		{
			if ($_GET['action'] == 'search')
				if (isset($_GET['query']) && $_GET['query'] != '')
					$page->name = localize('INFOLABEL_SEARCHRESULTSFOR').': '. $_GET['query']; // html encoded later
				else
					$page->name = localize('INFOLABEL_LISTOFPAGES');
			elseif ($_GET['action'] == 'recent')
					$page->name = localize('INFOLABEL_RECENTCHANGES');
				else
					$page->name = "$PAGE_START";
		}
		else
			$page->name = "$PAGE_START";
	}

	if (isset($_GET['action']))
		$action = $_GET['action'];
	else
		$action = '';

	if (isset($_GET['time']))
	{
		$gtime = $_GET['time'];
		if (preg_match('/\//', $gtime)) $gtime = '';
	}
	@date_default_timezone_set('Europe/Paris');
	$datetw = date('Y/m/d H:i', mktime(date('H') + $LOCAL_HOUR));
	// Arreter les acces malicieux via repertoire et accents
	if (preg_match('/\//', $page->name)) $page->name = $PAGE_START;

// ----------------------------------------------------------------------------

// IMPORTANT: If the method is POST that means the page will be saved, deleted, 
// or in other words - manipulated.

// Ecrire les modifications, s'il y a lieu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content']))
{
	$page->name = str_replace(array('/','\\'), '', stripslashes($_POST['page']));
	// print "page->name: {$page->name}<br />\n";	// ---- DEBUGING ----
	
	/*  ---- PLUGINS ---- */
	// Trigger plugin event
	$pluginsprocessor->trigger("write");
	// NOTE: Some of the plugins could change the WritePermitted flag.

	if ($pagewriter->isWritePermitted() || authenticated())
	{
		$pagewriter->proceed($page);
		
		/*  ---- PLUGINS (old-style) ---- */
		// Call plugins that depend on page modifications. Example: RSS feeds.
		plugins_old_call_method('writedPage',$file);
		// NOTE: For the new-style plugin should be added new trigger type 
		//       "written" that will be activated uppon successful writing.
		
		// Redirect to view the just modified page
		// TODO: Fix the URL, should be FQDN + path
		header('location: ' . $wikissme->script_basename . '?page=' . urlencode(stripslashes($page->name)));
		exit();
	}
	else
	{
		// NOT AUTHENTICATED
		// If not authenticated generate an error and continue.
		$wikissme->addError(-1, localize('INFOLABEL_WRITINGNOTPERMITED'));
		$wikissme->addError(-1, localize('INFOLABEL_PASSWORD') . ' ' . localize('INFOLABEL_INCORRECT'));	// TODO: This should be removed.
		$action = 'edit';
		$PAGE_CONTENT = str_replace("<","&lt;", $_POST['content']);
	}
}

// ---- Load TEMPLATE file ----

	// Open file for reading, load the template file ...
	if (! $file = @fopen($template, 'r')) die("'$template' is missing!");
	$TEMPLATE_HTML = fread($file, filesize($template));
	fclose($file);

// ----------------------------------------------------------------------------

/* ==== Load the page from a file ==== */

	// Plugins trigger "read".
	$pluginsprocessor->trigger("read");

	// Lecture du contenu et de la date de modification de la page
	// if (empty($_GET['error']))	// Replaced by the code below ...
	if (!$wikissme->hasErrors())
	{
		$pagereader->proceed($page);
		
		// $PAGE_CONTENT = $page->content;	// It is the other way arbound - page.content references to PAGE_CONTENT - this should chenage though.
		
		$PAGE_DATEMODIFIED = $page->date_modified;
		
		// TODO: Find out why the replacements below is needed.
		$PAGE_CONTENT = preg_replace('/\\$/Umsi', '&#036;', $PAGE_CONTENT);	// This is the "$" sign.
		$PAGE_CONTENT = preg_replace('/\\\/Umsi', '&#092;', $PAGE_CONTENT);	// This is the "\" character.
	}
	else
	{
		// Switch action to edit mode.
		$action = 'edit';
		// NOTE: It seams that this will happen only when there is and error while
		//       writing content to a file, but then the action is already set to
		//       edit mode anyways. May this should be removed.
	}

	// ---- Special Wiki actions ----
	// TODO: use switch/case in stead of if/elseif for more readable code.
	if ($action == 'edit')
	{
		// Editing the page
		$editable = FALSE;
		$HISTORY_LINK_HTML =
			'<a href="' . $wikissme->script_basename . '?page=' . urlencode($page->name) . '&amp;action=history" accesskey="6" rel="nofollow">'.
			localize('ACTLABEL_HISTORY') .
			'</a><br />';
		$PAGE_CONTENT =
			'<div style="margin: 6px; padding: 6px; ">' .
			'<form method="post" action="' . $wikissme->script_basename . '">' .
			'<textarea name="content" cols="75" rows="24" style="width: 100%;">'.$PAGE_CONTENT.'</textarea>' .
			'<input type="hidden" name="page" value="' . $page->name . '" /><br />' .
			'<p align="right">';
		if (!authenticated()) $PAGE_CONTENT .= localize('INFOLABEL_PASSWORD').' : <input type="password" name="sc" />';
		$PAGE_CONTENT .=
			'<input type="submit" value="'.localize('ACTLABEL_SUBMIT').'" accesskey="s" />' .
			'</p>' .
			'</form>' .
			'</div>';
		//Retrait d'un </div> avant le </form>
	}
	elseif ($action == 'history')
	{
		// historique d'une page
		if (isset($gtime))
		{
			// Afficaheg d'un fichier d'historique
			$complete_dir = $DATA_FOLDER . '/' . $HISTORY_FOLDER . '/' . $page->name . '/';
			if ($file = @fopen($DATA_FOLDER . '/' . $HISTORY_FOLDER . '/' . $page->name . '/' . $gtime, 'r'))
			{
			$HISTORY_LINK_HTML = 
				'<a href="' . $wikissme->script_basename . '?page=' . urlencode($page->name) . '&amp;action=history" rel="nofollow">'.
				localize('ACTLABEL_HISTORY').'</a> ' .
				'<a href="' . $wikissme->script_basename . '?page=' . urlencode($page->name) . '&amp;action=edit&amp;time='.$gtime.'&amp;restore=1" rel="nofollow">'.
				localize('ACTLABEL_RESTORE').'</a>';
			$PAGE_CONTENT = @fread($file, @filesize($complete_dir . $gtime)) . "\n";
			$PAGE_CONTENT = str_replace("\n",'<br/>',str_replace("\r",'',$PAGE_CONTENT));
		}
		else
			$HISTORY_LINK_HTML = '<a href="' . $wikissme->script_basename . '?page=' . urlencode($page->name) . '&amp;action=history" rel="nofollow">'.
			localize('ACTLABEL_HISTORY').'</a> -';
		}
		else
		{
			// Liste des versions historiques d'une page
			$HISTORY_LINK_HTML = localize('INFOLABEL_HISTORY');
			$complete_dir = $DATA_FOLDER . '/' . $HISTORY_FOLDER . '/' . $page->name . '/';
			if ($opening_dir = @opendir($complete_dir))
			{
				while (false !== ($filename = @readdir($opening_dir)))
					if (preg_match('/\.bak$/',$filename))
						$files[] = $filename;
				rsort ($files);
				$PAGE_CONTENT = 
					'<form method="GET" action="' . $wikissme->script_basename . '">'."\n".
					'<input type=hidden name=action value=diff><input type=hidden name=page value="' . $page->name . '">';
				for ($cptfiles = 0; $cptfiles < sizeof($files); $cptfiles++)
				{
					$PAGE_CONTENT .= 
						'<input type="radio" name="f1" value="'.$files[$cptfiles].'">'.
						'<input type="radio" name="f2" value="'.$files[$cptfiles].'" />';
					$PAGE_CONTENT .= 
						'<a href="' . $wikissme->script_basename . '?page=' . urlencode($page->name) . '&amp;action=history&amp;time='.$files[$cptfiles].'">'.$files[$cptfiles].'</a><br />';
				}
				$PAGE_CONTENT .= '<input type="submit" value="diff"></form>';
			}
			else
				$PAGE_CONTENT = localize('INFOLABEL_HISTORYDOESNOTEXIST');
		}
	}
   elseif ($action == 'diff')
   { // differences entre deux révisions
      if (isset($_GET['f1']))
      { // diff très simple entre deux pages
         $PAGE_CONTENT = '';
         function pcolor($color,$txt)
            {return '<font color="'.$color.'">'.$txt.'</font><br/>';}
         $HISTORY_LINK_HTML = 
		 	'<a href="' . $wikissme->script_basename . '?page=' . urlencode($page->name) . '&amp;action=history">'.
		 		localize('ACTLABEL_HISTORY').'</a>';
         if (!strpos($_GET['f1'],'/'))
            $fn1 = $DATA_FOLDER . '/' . $HISTORY_FOLDER . '/' . $page->name . '/' . $_GET['f1'];
         if (!strpos($_GET['f2'],'/'))
            $fn2 = $DATA_FOLDER . '/' . $HISTORY_FOLDER . '/' . $page->name . '/' . $_GET['f2'];
         if ($fn2 < $fn1) {$tmp=$fn1;$fn1=$fn2;$fn2=$tmp;}
         $a1 = explode("\n",@file_get_contents($fn1));
         $a2 = explode("\n",@file_get_contents($fn2));
         // fclose ?
         $d1 = array_diff($a1,$a2);
         $d2 = array_diff($a2,$a1);
         for ($i=0;$i<=max(sizeof($a2),sizeof($a1));$i++)
         {
            if (array_key_exists($i,$d1))
               $PAGE_CONTENT .= pcolor('red',$d1[$i]);
            if (array_key_exists($i,$d2))
               $PAGE_CONTENT .= pcolor('green',$d2[$i]);
            if (!(array_key_exists($i,$d1) && array_key_exists($i,$d2)) && @$d2[$i] != @$a2[$i])
               $PAGE_CONTENT .= pcolor ('black',$a2[$i]);
         }
      }
      else
      { // diff auto entre les 2 dernières versions
         $complete_dir = $DATA_FOLDER . '/' . $HISTORY_FOLDER . '/' . $page->name . '/';
         if ($opening_dir = @opendir($complete_dir))
         {
            while (false !== ($filename = @readdir($opening_dir)))
               if (preg_match('/\.bak$/',$filename))
                  $files[] = $filename;
            rsort ($files);
			// TODO: Fix the URL
            header('location: ' . $wikissme->script_basename . '?page=' . urlencode($page->name) .'&action='.$action.'&f1='.$files[0].'&f2='.$files[1]);
            exit();
         }
      }
   }
   elseif ($action == 'search')
   { // Page de recherche
      $files= '';
      $PAGE_TITLE_link = FALSE;
      $editable = FALSE;
      // Open the folder where all regular pages reside
		$search_folder = 
			getcwd() . '/' . 
			($DATA_FOLDER ? $DATA_FOLDER . '/' : '') .	// add folder where all data should reside
			($PAGES_FOLDER ? $PAGES_FOLDER . '/' : '') .	// add folder where the page resides
			'/';
      $dir = opendir($search_folder);
      while ($file = readdir($dir))
      {
         if (preg_match('/\.txt$/',$file))
         {
         	// Open file for reading, to search withing its content
         	$search_file_path = 
				($DATA_FOLDER ? $DATA_FOLDER . '/' : '') .	// add folder where all data should reside
				($PAGES_FOLDER ? $PAGES_FOLDER . '/' : '') .	// add folder where the page resides
				 '/' . $file;
            $handle = fopen($search_file_path, 'r');
            @$content = fread($handle, filesize($search_file_path));
            fclose($handle);
            if (isset($_GET['query']))
               $query = preg_quote($_GET['query'],'/');
            else
               $query='';
            if (@preg_match("/$query/i", $content) || preg_match("/$query/i", "$file"))
               $files[] = substr($file, 0, strlen($file) - 4);
         }
      }
      if (is_array($files))
      {
         sort($files);
         foreach ($files as $file)
            $PAGE_CONTENT .= '<a href="' . $wikissme->script_basename . '?page=' . urlencode($file) . '">'.$file.'</a><br />';
      }
      $page->name .= ' ('.count($files).')';
   }
   elseif ($action != '')
   {
		/*  ---- PLUGINS (old-style) ---- */
		plugins_old_call_method('action',$action);
		/*  ---- PLUGINS ---- */
		// Proceed with actions handled by the plugins ...
		$pluginsprocessor->trigger("action", $action);
   }

// ----------------------------------------------------------------------------

	// FORMAT PAGE ACCORDING TO THE WIKI FORMATING RULES

	if ($action == '')
	{
		// Remove the "\r" from text. This way all lines are "\n" terminated.
		$PAGE_CONTENT = str_replace("\r",'',$PAGE_CONTENT);

		$PAGE_CONTENT = preg_replace('/&(?!lt;)/','&amp;',$PAGE_CONTENT);
		$PAGE_CONTENT = str_replace('<','&lt;',$PAGE_CONTENT);
		//~ $PAGE_CONTENT = htmlentities($PAGE_CONTENT,ENT_COMPAT,"UTF-8");
		$PAGE_CONTENT = preg_replace('/&amp;#036;/Umsi', '&#036;', $PAGE_CONTENT);	// This is the "$" sign.
		$PAGE_CONTENT = preg_replace('/&amp;#092;/Umsi', '&#092;', $PAGE_CONTENT);	// This is the "\" character.		
		$PAGE_CONTENT = preg_replace('/\^(.)/Umsie', "'&#'.ord('$1').';'", $PAGE_CONTENT); // escape caractère

		//~ {{CODE}}
		$nbcode = preg_match_all('/{{\n(.+)}}/Ums',$PAGE_CONTENT,$matches_code);
		$PAGE_CONTENT = preg_replace('/{{(.+)}}/Ums','<pre><code>{{CODE}}</code></pre>',$PAGE_CONTENT);
		//~ {{CODE}}

		/* ---- PLUGINS ---- */
		// Plugins trigger "inline" ...
		// note: should be placed before the formatting takes place so the text
		//       from the plugin will be formated as well.
		$pluginsprocessor->inline();
		// Old style plugins ...
		plugins_old_call_method('formatBegin');
	
		// Plugins trigger "format".
		$pluginsprocessor->trigger("format");
		
		// $wikissme->test();
		// $wikissme->headings->test();
		$wikissme->getHeadings()->format();

		// Formt horizontal line
		$PAGE_CONTENT = preg_replace(WIKISSME_REGEX_HRLINE, '<hr />', $PAGE_CONTENT);

		// Format lists - ordered and unordered
		$PAGE_CONTENT = preg_replace('/^\*\*\*(.*)(\n)/Um', "<ul><ul><ul><li>$1</li></ul></ul></ul>$2", $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('/^\*\*(.*)(\n)/Um', "<ul><ul><li>$1</li></ul></ul>$2", $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('/^\*(.*)(\n)/Um', "<ul><li>$1</li></ul>$2", $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('/^\#\#\#(.*)(\n)/Um', "<ol><ol><ol><li>$1</li></ol></ol></ol>$2", $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('/^\#\#(.*)(\n)/Um', "<ol><ol><li>$1</li></ol></ol>$2", $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('/^\#(.*)(\n)/Um', "<ol><li>$1</li></ol>$2", $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('/(<\/ol>\n*<ol>|<\/ul>\n*<ul>)/', '', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('/(<\/ol>\n*<ol>|<\/ul>\n*<ul>)/', '', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('/(<\/ol>\n*<ol>|<\/ul>\n*<ul>)/', '', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#</li><ul><li>*#', '<ul><li>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#</ul></ul>*#', '</ul></li></ul>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#</ul></ul>*#', '</ul></li></ul>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#</li></ul><li>*#', '</li></ul></li><li>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#</li><ol><li>*#', '<ol><li>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#</ol></ol>*#', '</ol></li></ol>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#</ol></ol>*#', '</ol></li></ol>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#</li></ol><li>*#', '</li></ol></li><li>', $PAGE_CONTENT);

		// Paragraphs
		// $PAGE_CONTENT = preg_replace('/(^$\n)+([^<]+?)^$/ms',"<p>\n$2</p>",$PAGE_CONTENT); // <p></p> (sans balise) -- ORIGINAL
		$PAGE_CONTENT = preg_replace('/\n{0,2}(^)([^<]+?)(\n\n)/ms',"<p class='content-paragraph'>$2</p>",$PAGE_CONTENT);
		// Add line break for each CR
		// Note: all lines are CR ("\n") terminated.
		$PAGE_CONTENT = str_replace("\n", '<br />', $PAGE_CONTENT);

		// balises type en ligne
		$PAGE_CONTENT = str_replace('%%','<br />',$PAGE_CONTENT); // replace the "%%" with new line

		// Format arrows
		$PAGE_CONTENT = str_replace('&lt;-->', '&harr;', $PAGE_CONTENT); // <-->
		$PAGE_CONTENT = str_replace('-->', '&rarr;', $PAGE_CONTENT); // -->
		$PAGE_CONTENT = str_replace('&lt;--', '&larr;', $PAGE_CONTENT); // <--

		// Format copyrights signs
		// TODO: use str_replace for better performance.
		$PAGE_CONTENT = preg_replace('/\([c]\)/i', '&copy;', $PAGE_CONTENT); // (c)
		$PAGE_CONTENT = preg_replace('/\([r]\)/i', '&reg;', $PAGE_CONTENT); // (r)
		$PAGE_CONTENT = preg_replace('/\(tm\)/i', '&trade;', $PAGE_CONTENT); // (tm)
		
		
		/* ---- Format Links - new style ---- */
		function wikissme_format_links_callback($matches)
		{
			// print "<code>matches: " . var_export($matches, TRUE) . "</code><br />";
			switch ($matches[1])
			{
				case '':
					$page_name = $matches[2];
					$result = "<a href='wiki.php?page=" . urlencode($page_name) . "'>{$page_name}</a>";
				break;
				
				case 'file:':
					$file_name = $matches[2];
					$result = "<a href='file.php?name=" . urlencode($file_name) . "'>{$file_name}</a>";
				break;
				
				case 'image:':
					$image_name = $matches[2];
					$result = "<img src='image.php?name=" . urlencode($image_name) . "' />";
				break;
				
				case 'https://':
				case 'http://':
					$link = $matches[1] . $matches[2];
					$result = "<a href='" . $link . "'>{$link}</a>";	// CASE 1 - this should be used after the old code is removed.
					$result = $link;	// CASE 2 - this should be used until the old code for formating http link is active.
				break;
				
				default:
					$action = substr($matches[1], 0, -1);
					$routine = $matches[2];
					$result = "<a href='wiki.php?action={$action}" . ($routine ? "&routine={$routine}" : "") . "'>{$action}</a>";
				break;
			}
			
			return $result;
		}
		function wikissme_format_links(&$text)
		{
			$links_regex = 
				'/\[' .
				'(file:|image:|https:\/\/|http:\/\/|mailto:|[0-9a-zA-Z]*:|)' .
				'([0-9a-zA-Z\s\/\:\.\-\_]*)' .
				'\]/U';
			$text = preg_replace_callback($links_regex, "wikissme_format_links_callback", $text);
		}
		wikissme_format_links($PAGE_CONTENT);

		/* ---- Format Links - old style ---- */
		// Format URLs
		$rg_url        = "[0-9a-zA-Z\.\#/~\-_%=\?\&,\+\:@;!\(\)\*\$']*"; // TODO: verif & / &amp;
		$rg_img_local  = '('.$rg_url.'\.(jpeg|jpg|gif|png))';
		$rg_img_http   = 'h(ttps?://'.$rg_url.'\.(jpeg|jpg|gif|png))';
		$rg_link_local = '('.$rg_url.')';
		$rg_link_http  = 'h(ttps?://'.$rg_url.')';
		// image
		$PAGE_CONTENT = preg_replace('#\['.$rg_img_http.'(\|(right|left))?\]#','<img src="xx$1" alt="xx$1" style="float:$4;"/>',$PAGE_CONTENT); // [http.png] / [http.png|right]
		$PAGE_CONTENT = preg_replace('#\['.$rg_img_local.'(\|(right|left))?\]#','<img src="$1" alt="$1" style="float:$4"/>',$PAGE_CONTENT); // [local.png] / [local.png|left]
		// image link [http://wikiss.tuxfamily.org/img/logo_100.png|http://wikiss.tuxfamily.org/img/logo_100.png]
		$PAGE_CONTENT = preg_replace('#\['.$rg_img_http.'\|'.$rg_link_http  .'(\|(right|left))?\]#U', '<a href="xx$3" class="url"><img src="xx$1" alt="xx$3" title="xx$3" style="float:$5;"/></a>', $PAGE_CONTENT);  // [http|http]
		$PAGE_CONTENT = preg_replace('#\['.$rg_img_http.'\|'.$rg_link_local .'(\|(right|left))?\]#U', '<a href="$3" class="url"><img src="xx$1" alt="$3" title="$3" style="float:$5;"/></a>', $PAGE_CONTENT); // [http|local]
		$PAGE_CONTENT = preg_replace('#\['.$rg_img_local.'\|'.$rg_link_http .'(\|(right|left))?\]#U', '<a href="xx$3" class="url"><img src="$1" alt="xx$3" title="xx$3" style="float:$5;"/></a>', $PAGE_CONTENT); // [local|http]
		$PAGE_CONTENT = preg_replace('#\['.$rg_img_local.'\|'.$rg_link_local.'(\|(right|left))?\]#U', '<a href="$3" class="url"><img src="$1" alt="$3" title="$3" style="float:$5;"/></a>', $PAGE_CONTENT); // [local|local]
		$PAGE_CONTENT = preg_replace('#\[([^\]]+)\|'.$rg_link_http.'\]#U', '<a href="xx$2" class="url">$1</a>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#\[([^\]]+)\|'.$rg_link_local.'\]#U', '<a href="$2" class="url">$1</a>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#'.$rg_link_http.'#i', '<a href="$0" class="url">xx$1</a>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#xxttp#', 'http', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('#\[\?(.*)\]#Ui', '<a href="http://'.$WIKI_LANG.'.wikipedia.org/wiki/$1" class="url" title="Wikipedia">$1</a>', $PAGE_CONTENT); // Wikipedia

		// Render links in square brackets: "[" & "]".
		preg_match_all('/\[([^\/]+)\]/U', $PAGE_CONTENT, $matches, PREG_PATTERN_ORDER);
		foreach ($matches[1] as $match)
			// Check if page exists or not, so display the link in different color
			if (file_exists(Page::filepath($match)))
				$PAGE_CONTENT = str_replace("[$match]",
					'<a href="' . $wikissme->script_basename . '?page=' . urlencode($match) . '">' . 
					$match . '</a>', $PAGE_CONTENT);
			else
				$PAGE_CONTENT = str_replace("[$match]",
					'<a href="' . $wikissme->script_basename . '?page=' . urlencode($match) . '" class="pending" >' . 
					$match . '</a>', $PAGE_CONTENT);

		// Render email addresses.
		$PAGE_CONTENT = preg_replace(WIKISSME_REGEX_EMAILADDRESS, '<a href="mailto:$0">$0</a>', $PAGE_CONTENT);

		// Replace spaces with nbsp's at the beginning of the line.
		while (preg_match('/^  /Um', $PAGE_CONTENT))
			$PAGE_CONTENT = preg_replace('/^( +) ([^ ])/Um', '$1&nbsp;&nbsp;&nbsp;&nbsp;$2', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace('/^ /Um', '&nbsp;&nbsp;&nbsp;&nbsp;', $PAGE_CONTENT);

		// Format stricked text
		$PAGE_CONTENT = preg_replace("/'--(.*)--'/Um", '<span style="text-decoration:line-through">$1</span>', $PAGE_CONTENT); // barré
		// Format underlied text.
		$PAGE_CONTENT = preg_replace("/'__(.*)__'/Um", '<span style="text-decoration:underline">$1</span>', $PAGE_CONTENT); // souligné
		// Format stringer text.
		$PAGE_CONTENT = preg_replace("/'''''(.*)'''''/Um", '<strong><em>$1</em></strong>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace("/'''(.*)'''/Um", '<strong>$1</strong>', $PAGE_CONTENT);
		$PAGE_CONTENT = preg_replace("/''(.*)''/Um", '<em>$1</em>', $PAGE_CONTENT);

		// Format code
		function format_code_callback($m)
		{
			global $matches_code;
			static $idxcode = 0;
			return $matches_code[1][$idxcode++];
		}
		if ($nbcode > 0)
			$PAGE_CONTENT = preg_replace_callback(array_fill(0,$nbcode,'/{{CODE}}/'),'format_code_callback',$PAGE_CONTENT);

		/*  ---- PLUGINS ---- */
		// Old style plugins ...
		plugins_old_call_method('formatEnd');
	
	}	// END OF if ($action == '')

// ----------------------------------------------------------------------------

	// Remplacement dans le template

	// remplacement selon l'action
	$INFOLABEL_RECENTCHANGES = localize('INFOLABEL_RECENTCHANGES');
	if ($action == 'recent')
		$RECENTCHANGES_LINK_HTML = $INFOLABEL_RECENTCHANGES;
	else
		$RECENTCHANGES_LINK_HTML = 
			'<a href="' . $wikissme->script_basename . '?action=recent" accesskey="3">'.
			$INFOLABEL_RECENTCHANGES.'</a>';
	$TEMPLATE_HTML = preg_replace('/{([^}]*)RECENTCHANGES_LINK_HTML(.*)}/U',"$1".$RECENTCHANGES_LINK_HTML."$2",$TEMPLATE_HTML);

	if ($page->name == $PAGE_START && $action <> 'search')
		$HOME_LINK_HTML = localize('ACTLABEL_HOME');
	else
		$HOME_LINK_HTML = 
			'<a href="' . $wikissme->script_basename . '?page=' . urlencode($PAGE_START) . '" accesskey="1">' .
			localize('ACTLABEL_HOME') . '</a>';
	$TEMPLATE_HTML = preg_replace('/{([^}]*)HOME_LINK_HTML(.*)}/U',"$1".$HOME_LINK_HTML."$2",$TEMPLATE_HTML);

	if ($action != 'edit')
		$TEMPLATE_HTML = preg_replace('/{[^}]*HELP_LINK_HTML.*}/U', '', $TEMPLATE_HTML);
	else
		$TEMPLATE_HTML = 
			preg_replace(
				'/{([^}]*)HELP_LINK_HTML(.*)}/U', 
				"$1<a href=\"{$wikissme->script_basename}?page=" . urlencode($PAGE_HELP) . '" accesskey="2" rel="nofollow">'.
					localize('ACTLABEL_HELP')."</a>$2", 
				$TEMPLATE_HTML);
	
	if (!isset($_GET['query'])) $_GET['query'] = '';
	$search_form_html =
		'<div>' .
		'<form method="get" action="' . $wikissme->script_basename . '?page=' . urlencode($page->name) . '">' .
		'<input type="hidden" name="action" value="search" />' .
		'<input type="text" name="query" value="'.htmlentities($_GET['query'],ENT_COMPAT,'UTF-8').'" tabindex="1" /> ' .
		'<input type="submit" value="' . localize('ACTLABEL_SEARCH') . '" accesskey="q" />' .
		'</form>' .
		'</div>';
	$TEMPLATE_HTML = str_replace('{SEARCH_FORM_HTML}', $search_form_html, $TEMPLATE_HTML);

	if ($action != '' && $action != 'edit' || (!file_exists($page->getFilepath())))
		$PAGE_DATEMODIFIED = '-';

	/* ---- PLUGINS ---- */
	// Old style plugins ...
	plugins_old_call_method('template');
	
	if ($action == '')
	{
		/*  ---- PLUGINS ---- */
		// Plugins trigger "template".
		$pluginsprocessor->trigger("template");
	}

	// Erreur du mot de passe
	// if (isset($_GET['error']))	// Replaced by the code below ...
	if ($wikissme->hasErrors())
	{
		// $TEMPLATE_HTML = str_replace('{ERROR_MESSAGES_HTML}', $_GET['error'], $TEMPLATE_HTML);	// Replaced by the code below ...
		// $TEMPLATE_HTML = str_replace('{ERROR_MESSAGES_HTML}', var_export($wikissme->getErrors(), TRUE), $TEMPLATE_HTML);
		$errors = $wikissme->getErrors();
		foreach ($errors as $index => $error)
		{
			$errors_text .= "error[{$index}] ({$error[0]}) {$error[1]}<br />";
		}
		$TEMPLATE_HTML = str_replace('{ERROR_MESSAGES_HTML}', "<div class='message-error'>{$errors_text}</div>", $TEMPLATE_HTML);
	}
	else
	{
		$TEMPLATE_HTML = str_replace('{ERROR_MESSAGES_HTML}', '', $TEMPLATE_HTML);
	}

	// remplacement selon variables
	if (!empty($HISTORY_LINK_HTML))
		$TEMPLATE_HTML = preg_replace('/{([^}]*)HISTORY_LINK_HTML(.*)}/U',"$1".$HISTORY_LINK_HTML."$2",$TEMPLATE_HTML);
	else
		$TEMPLATE_HTML = preg_replace('/{([^}]*)HISTORY_LINK_HTML(.*)}/U','',$TEMPLATE_HTML);
	if ($PAGE_TITLE_link)
		$TEMPLATE_HTML = 
			str_replace('{PAGE_NAME}', 
				'<a href="' . $wikissme->script_basename . '?page=' . urlencode($page->name) . '">'.htmlentities($page->name,ENT_COMPAT,'UTF-8').'</a>', 
				$TEMPLATE_HTML);
	else
		$TEMPLATE_HTML = str_replace('{PAGE_NAME}', htmlentities($page->name, ENT_COMPAT,'UTF-8'), $TEMPLATE_HTML);
	if ($editable)
		if (is_writable($page->getFilepath()) || !file_exists($page->getFilepath()))
			$EDIT_LINK_HTML = 
				'<a href="' . $wikissme->script_basename . '?page=' . urlencode($page->name) . '&amp;action=edit" accesskey="5" rel="nofollow">'.
				localize('ACTLABEL_EDIT').'</a>';
		else
			$EDIT_LINK_HTML = localize('INFOLABEL_PAGELOCKED');
	else
		$EDIT_LINK_HTML = localize('INFOLABEL_EDIT');
	$TEMPLATE_HTML = preg_replace('/{([^}]*)EDIT_LINK_HTML(.*)}/U',"$1".$EDIT_LINK_HTML."$2",$TEMPLATE_HTML);

	// remplacements fixes
	$TEMPLATE_HTML = str_replace('{THEME_PATH}', $THEME_PATH, $TEMPLATE_HTML);
	$TEMPLATE_HTML = str_replace('{PAGE_TITLE_HTML}', htmlentities($page->name, ENT_COMPAT,'UTF-8'), $TEMPLATE_HTML);
	$TEMPLATE_HTML = str_replace('{SITE_TITLE}', $SITE_TITLE, $TEMPLATE_HTML);
	$TEMPLATE_HTML = str_replace('{INFOLABEL_LASTMODIFIED}', localize('INFOLABEL_LASTMODIFIED').' :', $TEMPLATE_HTML);
	$TEMPLATE_HTML = str_replace('{PAGE_CONTENT}', $PAGE_CONTENT, $TEMPLATE_HTML);
	$TEMPLATE_HTML = str_replace('{WIKI_LANG}', $WIKI_LANG, $TEMPLATE_HTML);
	$TEMPLATE_HTML = str_replace('{WIKISSME_VERSION}', $WIKISSME_VERSION, $TEMPLATE_HTML);
	$TEMPLATE_HTML = str_replace('{PAGE_DATEMODIFIED}', $PAGE_DATEMODIFIED, $TEMPLATE_HTML);
	$TEMPLATE_HTML = str_replace('{SERVER_DATETIME}', $datetw, $TEMPLATE_HTML);
	$TEMPLATE_HTML = str_replace('{SERVER_REMOTEADDR}', $_SERVER['REMOTE_ADDR'], $TEMPLATE_HTML);
	if (isset($_COOKIE['WiKissMeAuthPass']) && $_COOKIE['WiKissMeAuthPass'] != '')
		$TEMPLATE_HTML = 
			str_replace('{ERASECOOKIE_LINK_HTML}', 
			'-- <a href="' . $wikissme->script_basename . '?erasecookie=1&amp;'.$_SERVER['QUERY_STRING'].'" rel="nofollow">'.
			localize('ACTLABEL_ERASECOOKIE').'</a>', $TEMPLATE_HTML);
	else
		$TEMPLATE_HTML = str_replace('{ERASECOOKIE_LINK_HTML}', '', $TEMPLATE_HTML);
	// Affichage de la page
	echo $TEMPLATE_HTML;

// DEBUG
// Performance calculation
// TODO: Put this inside of HTML.
global $LOGS_PERFORMANCE_ON;
if ($LOGS_PERFORMANCE_ON)
{
	global $LOGS_FOLDER;
	$performance_mt2 = microtime();
	$performance_mt = $performance_mt2 - $performance_mt1;
	global $LOGS_PERFORMANCE_WRITE;
	if ($LOGS_PERFORMANCE_WRITE)
	{
		$file_performance = fopen($LOGS_FOLDER . "/" . "performance-" . date("Ymd") . ".log", 'a');
		if ($file_performance !== false)
		{
			fwrite(
				$file_performance,
				date("Y-m-d/H:i:s") . " " .
				$_SERVER['REMOTE_ADDR'] . " " .
				"microtime={$performance_mt}," . "page=" . urlencode($page->name) . "," .
				// "\t# " . "file=" . __FILE__ . 
				"\n");
			fclose($file_performance);
		}
	}
	global $LOGS_PERFORMANCE_PRINT;
	if ($LOGS_PERFORMANCE_PRINT) print "<small><small><i>microtime={$performance_mt}</i></small></small>\n";
}

?>
