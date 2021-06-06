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
 *  @version $Id: wkp_Translate.php,v 1.5 2009/11/02 20:37:15 neven Exp $
 *  @author Neven Boyanov
 *
 * ORIGINAL COMMENTS:
 *  WiKiss - http://wikiss.tuxfamily.org/
 *  Licence GNU/GPLv2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *  Copyright (C) 2007 charles-henri d'Adhémar <cdadhemar@gmail.com> 
 *  Modifs JJL
 *  - no subdir for default language ($WIKI_LANG)
 *  - don't display flag for current language
 *
 */

require_once "classes/AbstractOldPlugin.php";

class Translate extends AbstractOldPlugin
{

	const TRANSLATE_FOLDER = "./locale";    // The directory where the translations are located
	private $_language = "";    // The current language.
	private $_availableLanguages = array();    // Array of available languages.
	private $_toBeTranslated = false;  // Indicate if the current page needs to be translated
	
   function __toString()
   {
      // return tr('Traduction des pages du wiki');
      return 'Translation of wiki pages';
   }
   	
	// Set the available languages from the translations directory
	// Set the current language from either the cookie or the GET 'lang' variable.
	// Load the translation corresponding to the current language.
	function __construct($wikissme)
	{
		parent::AbstractOldPlugin($wikissme);
		
		global $WIKI_LANG;
		
		// Retrieve the available langs from the translations directory.
		// The defaullt lang $WIKI_LANG is the first lang available.
		
		$this->_availableLanguages[] = $WIKI_LANG;
      
      // english is always available
      //~ if ($WIKI_LANG != 'en')
         //~ $this->_availableLanguages[] = 'en';
		
		// If the translations directory exists we set the language to the input language.
		// Else we set the language to the defaullt one.
		
		if (is_dir(Translate::TRANSLATE_FOLDER))
		{
			$files = scandir(Translate::TRANSLATE_FOLDER);
			foreach($files as $file)
			{
				// Only file like en.php, fr.php ... are valid.
				if (is_file(Translate::TRANSLATE_FOLDER.'/'.$file) && preg_match("/^([a-z]{2})\.php$/", $file, $matches) && $matches[1] != $WIKI_LANG)
				{
               // echo 'YES'.$file.'<br/>';
					$this->_availableLanguages[] = $matches[1];
				}
			}
			
			// Set the current language either form the GET 'lang' variable or the cookie.
			// Set the current language to the default if no language is specified.
			
			if (isset($_GET['lang']))
			{
				$this->setLanguage($_GET['lang']);
			}
			elseif (isset($_COOKIE['WiKissMeLang']))
			{
				$this->setLanguage($_COOKIE['WiKissMeLang']);
			}
			else
			{
				$this->setLanguage($WIKI_LANG);
			}
			
		}
		else
		{
			$this->setLanguage($WIKI_LANG);
		}
		
	} // __construct ()
	
	
	// Set the current language.
	// This function makes sure that the language to be set is a valid language and that a related translation exists
	// input : $lang. The language to be set.
	// output : none
	private function setLanguage($lang)
	{
		global $DATA_FOLDER, $PAGES_FOLDER, $WIKI_LANG, $PAGE_START, $HISTORY_FOLDER;
		
		// Set the language to the default one if no parameter specified or if wrong parameter.
		if (empty($lang) || !array_search($lang, $this->_availableLanguages))
		{
			$lang = $WIKI_LANG;
		}
		
		$this->_language = $lang;
		
		// Set the language in a cookie if it has changed.
		// If no cookie is set and the language is the default language, no cookie is set.
		
		if ((isset($_COOKIE['WiKissMeLang']) && $_COOKIE['WiKissMeLang'] != $this->_language) ||
			(!isset($_COOKIE['WiKissMeLang']) && $this->_language != $WIKI_LANG))
		{
			setcookie('WiKissMeLang', $this->_language, time() + 365*24*3600);
			$_COOKIE['WiKissMeLang'] = $this->_language;
		}

      // get page title
      if (isset($_GET['page']))
      {
          $this->wikissme->page->name = stripslashes($_GET["page"]);
      }
      else
      {
          $this->wikissme->page->name = "$PAGE_START";
      }

      // Set directories for the current language
      if ($this->_language != $this->_availableLanguages[0])
      { // Not default language
         $HISTORY_FOLDER = $HISTORY_FOLDER . '/' . $this->_language . "/";
         if ((isset($_POST['content'])) || (is_readable($DATA_FOLDER . '/' . $PAGES_FOLDER . '/' . $this->_language . "/" . $this->wikissme->page->name . ".txt"))
             || (! is_readable($DATA_FOLDER . '/' . $PAGES_FOLDER . '/' . $this->wikissme->page->name . ".txt")) )
         { // page creation/modif or page exists in this language
            $PAGES_FOLDER = $PAGES_FOLDER . '/' . $this->_language . "/";
         }
         else
         { // stay with default language for page
            $this->_toBeTranslated = true;
         }
      }

        if (!is_dir($DATA_FOLDER . '/' . $PAGES_FOLDER))
        {
        //TODO fixme 0700
            mkdir($DATA_FOLDER . '/' . $PAGES_FOLDER, 0777);
        }
        if (!is_dir($DATA_FOLDER . '/' . $HISTORY_FOLDER))
        {
        //TODO fixme 0700
            mkdir($DATA_FOLDER . '/' . $HISTORY_FOLDER, 0777);
        }

		// Set the WIKI_LANG for wikipedia links
		$WIKI_LANG = $this->_language;
		
	} // setLanguage ()
	
	
	// Format a language element in html with image and link to the current page with the 'lang' parameter.
	// input : $language. The language to format
	// output : $link. The language html formated
	private function formatLanguageLink($language)
	{
		global $WIKI_LANG;
		$link = "";
		
		// Display only the image for the current language, no link.
		if ($language != $this->_language)
		{
			$_GET['lang'] = $language;
			$query = array();
			foreach($_GET as $key => $value) 
			{
				if (!empty($value) && $key != 'error')
				{
					$query["$key"] = $value;
				}
			}
			$page = http_build_query($query);
        	$img = Translate::TRANSLATE_FOLDER . '/' . $language . '.png';
			$link = '<a href="?'.$page.'"><img style="locale" src="'. $img .'" alt="'.$language.'" /> </a>';
		}
		
		return $link;
	} // formatLanguageLink ()
	
	
	// For each supported language we format the link.
	// Finally we substitute the {TRANSLATE} tag in the template by all the language links.
	public function template()
	{
		$template_html = &$this->wikissme->template_html;	// MUST be passed by reference.

		global $WIKI_LANG;
		
		$links = "";
		
		// Format the language in html
		foreach($this->_availableLanguages as $language)
		{
			$links = $links . $this->formatLanguageLink($language);
		}

        if ($this->_toBeTranslated)
        {
            // $links .= '&nbsp;'.tr('Editez cette page pour la traduire');
            $links .= '&nbsp;' . 'Edit this page to translate it';
        }

		// Insert the language links in the template
		$template_html = str_replace('{TRANSLATE}', $links, $template_html);
		
	} // template ()
	
}

?>
