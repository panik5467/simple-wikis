<?php # coding: utf-8

/* WiKiss - http://wikiss.tuxfamily.org/
  * Licence GNU/GPLv2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
  * Copyright (C) 2007 charles-henri d'Adhémar <cdadhemar@gmail.com> 
  */

/** Modifs JJL
 * - no subdir for default language ($LANG)
 * - don't display flag for current language
 */

class Translate
{

	const TRANSLATE_DIR = "./locale";    // The directory where the translations are located
	private $_language = "";    // The current language.
	private $_availableLanguages = array();    // Array of available languages.
   private $_toBeTranslated = false;  // Indicate if the current page needs to be translated
	
   function __toString()
   {
      return tr('Traduction des pages du wiki');
   }
	
	// Set the available languages from the translations directory
	// Set the current language from either the cookie or the GET 'lang' variable.
	// Load the translation corresponding to the current language.
	function __construct()
	{
		global $LANG;
		
		// Retrieve the available langs from the translations directory.
		// The defaullt lang $LANG is the first lang available.
		
		$this->_availableLanguages[] = $LANG;
      
      // english is always available
      //~ if ($LANG != 'en')
         //~ $this->_availableLanguages[] = 'en';
		
		// If the translations directory exists we set the language to the input language.
		// Else we set the language to the defaullt one.
		
		if (is_dir(Translate::TRANSLATE_DIR))
		{
			$files = scandir(Translate::TRANSLATE_DIR);
			foreach($files as $file)
			{
				// Only file like en.php, fr.php ... are valid.
				if (is_file(Translate::TRANSLATE_DIR.'/'.$file) && preg_match("/^([a-z]{2})\.php$/", $file, $matches) && $matches[1] != $LANG)
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
			elseif (isset($_COOKIE['WiKissLang']))
			{
				$this->setLanguage($_COOKIE['WiKissLang']);
			}
			else
			{
				$this->setLanguage($LANG);
			}
			
		}
		else
		{
			$this->setLanguage($LANG);
		}
		
	} // __construct ()
	
	
	// Set the current language.
	// This function makes sure that the language to be set is a valid language and that a related translation exists
	// input : $lang. The language to be set.
	// output : none
	private function setLanguage($lang)
	{
		global $LANG,$START_PAGE;
		global $PAGES_DIR;
        global $BACKUP_DIR;
		
		// Set the language to the default one if no parameter specified or if wrong parameter.
		if (empty($lang) || !array_search($lang, $this->_availableLanguages))
		{
			$lang = $LANG;
		}
		
		$this->_language = $lang;
		
		// Set the language in a cookie if it has changed.
		// If no cookie is set and the language is the default language, no cookie is set.
		
		if ((isset($_COOKIE['WiKissLang']) && $_COOKIE['WiKissLang'] != $this->_language) ||
			(!isset($_COOKIE['WiKissLang']) && $this->_language != $LANG))
		{
			setcookie('WiKissLang', $this->_language, time() + 365*24*3600);
			$_COOKIE['WiKissLang'] = $this->_language;
		}

      // get page title
      if (isset($_GET['page']))
      {
          $PAGE_TITLE = stripslashes($_GET["page"]);
      }
      else
      {
          $PAGE_TITLE = "$START_PAGE";
      }

      // Set directories for the current language
      if ($this->_language != $this->_availableLanguages[0])
      { // Not default language
         $BACKUP_DIR = $BACKUP_DIR . $this->_language . "/";
         if ((isset($_POST['content'])) || (is_readable($PAGES_DIR . $this->_language . "/" . $PAGE_TITLE . ".txt"))
             || (! is_readable($PAGES_DIR . $PAGE_TITLE . ".txt")) )
         { // page creation/modif or page exists in this language
            $PAGES_DIR = $PAGES_DIR . $this->_language . "/";
         }
         else
         { // stay with default language for page
            $this->_toBeTranslated = true;
         }
      }
		

        if (!is_dir($PAGES_DIR))
        {
        //TODO fixme 0700
            mkdir($PAGES_DIR, 0777);
        }
        if (!is_dir($BACKUP_DIR))
        {
        //TODO fixme 0700
            mkdir($BACKUP_DIR, 0777);
        }

		// Set the LANG for wikipedia links
		$LANG = $this->_language;
		
	} // setLanguage ()
	
	
	// Format a language element in html with image and link to the current page with the 'lang' parameter.
	// input : $language. The language to format
	// output : $link. The language html formated
	private function formatLanguageLink($language)
	{
		global $PAGE_TITLE,$LANG;
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
         $img = Translate::TRANSLATE_DIR.'/'.$language.'.png';
			$link = '<a href="?'.$page.'"><img src="'. $img .'" alt="'.$language.'" /> </a>';
		}
		
		return $link;
	} // formatLanguageLink ()
	
	
	// For each supported language we format the link.
	// Finally we substitute the {TRANSLATE} tag in the template by all the language links.
	public function template()
	{
		global $html;
		global $LANG;
		
		$links = "";
		
		// Format the language in html
		foreach($this->_availableLanguages as $language)
		{
			$links = $links . $this->formatLanguageLink($language);
		}

        if ($this->_toBeTranslated)
        {
            $links .= '&nbsp;'.tr('Editez cette page pour la traduire');
        }

		// Insert the language links menu in the template
		$html = str_replace('{TRANSLATE}', $links, $html);
		
	} // template ()
	
}

?>
