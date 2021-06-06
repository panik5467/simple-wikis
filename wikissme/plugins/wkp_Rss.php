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
 *  Based on WiKiss code and partially on TigerWiki and other derivatives.
 *
 *  @package WiKissMe
 *  @subpackage Plugins
 *  @version $Id: wkp_Rss.php,v 1.6 2009/11/02 20:36:05 neven Exp $
 *  @author Neven Boyanov
 *
 * ORIGINAL COMMENTS:
 *  Code basé sur ModuleRss.php de TigerWiki
 *  *****
 *  Génération d'un flux rss des 10 dernières modifications
 *  lorsque on écrit une page.
 *  Fichier généré: rss.xml à la racine du wiki.
 *  Ajoutez {RSS} dans entre les tags <head></head> du template.html
 *  pour que vos visiteurs découvrent le flux.
 *
 */

require_once "classes/AbstractOldPlugin.php";

class Rss extends AbstractOldPlugin
{
	function Rss($wikissme)
	{
		parent::AbstractOldPlugin($wikissme);
	}

	function __toString()
	{
		// return tr('Create RSS feed of last changes');
		return 'Create RSS feed of last changes';
	}

   const template = '<rss version="0.91">
<channel>
<title>{SITE_TITLE}</title>
<link>{ADR_ACCUEIL}</link>
<description>{WIKI_DESCRIPTION}</description>
<language>{WIKI_LANG}</language>
{CONTENT_RSS}
</channel>
</rss>';

   private $RSS_FOLDER = "rss";

	function writedPage ($file)
	{
		global $PLUGIN_RSS_ACTIVE;
		// If plugi set not to be active, then exit the method
		if ($PLUGIN_RSS_ACTIVE != TRUE) return;
		
		global $DATA_FOLDER, $PAGES_FOLDER, $SITE_TITLE, $WIKI_LANG;
		
		$TIME_FORMAT = "%Y-%m-%d %H:%M";
		$CONTENT_RSS = "";
		
		// Attention, bug si https ou port différent de 80 ?
		$ADR_ACCUEIL = "http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"];
		
		$rss = str_replace('{SITE_TITLE}', $SITE_TITLE, self::template);
		$rss = str_replace('{ADR_ACCUEIL}', $ADR_ACCUEIL , $rss);
		$rss = str_replace('{WIKI_LANG}', $WIKI_LANG, $rss);
		$rss = str_replace('{WIKI_DESCRIPTION}', "RSS feeds from " . $SITE_TITLE, $rss);	// TODO: Text should be localized
		
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
		$filetime = array_slice($filetime, 0, 10);
		foreach ($filetime as $filename => $timestamp)
		{
		 $filename = substr($filename, 0, strlen($filename) - 4);
		 //RSS content
		 $CONTENT_RSS .= "<item>
		    <title>$filename</title>
		    <pubDate>". date("r", $timestamp)."</pubDate>
		    <link>$ADR_ACCUEIL?page=" . urlencode("$filename") . "&amp;lang=$WIKI_LANG</link>
		    <description>$filename " . strftime("$TIME_FORMAT", $timestamp) . "</description>
		    </item>";
		}
		$rss = str_replace("{CONTENT_RSS}", $CONTENT_RSS, $rss); 
		//Write RSS
		$file = fopen("{$this->RSS_FOLDER}/rss_$WIKI_LANG.xml", "w");
		// if (! $file) die (tr('Cannot create RSS feed'));
		if (! $file) die ('Cannot create RSS feed');
		fputs($file, $rss);
		fclose($file);    
	}
   
	function template()
	{
		$template_html = &$this->wikissme->template_html;	// MUST be passed by reference.
		global $WIKI_LANG;
		$template_html = str_replace("{RSS}", "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"RSS\" href=\"{$this->RSS_FOLDER}/rss_{$WIKI_LANG}.xml\" />", $template_html);
		return FALSE;
	}
}

?>
