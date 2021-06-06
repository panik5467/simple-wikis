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
 *  @version $Id: wkp_Menu.php,v 1.4 2009/11/02 20:40:48 neven Exp $
 *  @author Neven Boyanov
 *
 * ORIGINAL COMMENTS:
 *  Plugin for Menu page
 *  Allow a special page to be displayed on every wiki page
 *  For special page used, see $MENU_PAGE below
 *  Edit this page via wikiss. Supported syntax includes links and images
 *  Use the tag {PAGE_MENU} in template.html
 *
 */

require_once "classes/AbstractOldPlugin.php";

class Menu extends AbstractOldPlugin
{
   /* update thoses methods following your needs remove any unused one */
   private $menu_content = '';
   public $isPresent = false;
   
   function createMenu()
   {
	  /* Name of the special page treated as a menu */
	  $MENU_PAGE = 'Menu';
      $menu_name = Page::filepath($MENU_PAGE);
      // print "debug[" . __METHOD__ . "]: {$menu_name}<br />"; // DEBUG
      if (file_exists($menu_name))
      { // load menu
         $this->isPresent = true; // a menu exists
         $file = fopen($menu_name, "r");
         $this->menu_content = fread($file, filesize($menu_name));
         fclose($file);
         // format menu
         $this->menu_content = preg_replace('/\r/','',$this->menu_content);
         $this->menu_content = preg_replace("/&(?!lt;)/","&amp;",$this->menu_content);
         $this->menu_content = str_replace("<","&lt;",$this->menu_content);
         
         $rg_url        = "[0-9a-zA-Z\.\#/~\-_%=\?\&,\+\:@;!\(\)\*\$']*"; // TODO: verif & / &amp;
         $rg_img_local  = "(".$rg_url."\.(jpeg|jpg|gif|png))"; 
         $rg_img_http   = "h(ttps?://".$rg_url."\.(jpeg|jpg|gif|png))";
         $rg_link_local = "(".$rg_url.")";
         $rg_link_http  = "h(ttps?://".$rg_url.")";
         // image
         $this->menu_content = preg_replace('#\['.$rg_img_http.'(\|(right|left))?\]#','<img src="xx$1" alt="xx$1" style="float:$4;"/>',$this->menu_content); // [http.png] / [http.png|right]
         $this->menu_content = preg_replace('#\['.$rg_img_local.'(\|(right|left))?\]#','<img src="$1" alt="$1" style="float:$4"/>',$this->menu_content); // [local.png] / [local.png|left]
         // image link [http://wikiss.tuxfamily.org/img/logo_100.png|http://wikiss.tuxfamily.org/img/logo_100.png]
         
         $this->menu_content = preg_replace('#\['.$rg_img_http.'\|'.$rg_link_http  .'(\|(right|left))?\]#U', '<a href="xx$3" class="url"><img src="xx$1" alt="xx$3" title="xx$3" style="float:$5;"/></a>', $this->menu_content);  // [http|http]
         $this->menu_content = preg_replace('#\['.$rg_img_http.'\|'.$rg_link_local .'(\|(right|left))?\]#U', '<a href="$3" class="url"><img src="xx$1" alt="$3" title="$3" style="float:$5;"/></a>', $this->menu_content); // [http|local]
         $this->menu_content = preg_replace('#\['.$rg_img_local.'\|'.$rg_link_http .'(\|(right|left))?\]#U', '<a href="xx$3" class="url"><img src="$1" alt="xx$3" title="xx$3" style="float:$5;"/></a>', $this->menu_content); // [local|http]
         $this->menu_content = preg_replace('#\['.$rg_img_local.'\|'.$rg_link_local.'(\|(right|left))?\]#U', '<a href="$3" class="url"><img src="$1" alt="$3" title="$3" style="float:$5;"/></a>', $this->menu_content); // [local|local]
         
         $this->menu_content = preg_replace('#\[([^\]]+)\|'.$rg_link_http.'\]#U', '<a href="xx$2" class="url">$1</a>', $this->menu_content);
         $this->menu_content = preg_replace('#\[([^\]]+)\|'.$rg_link_local.'\]#U', '<a href="$2" class="url">$1</a>', $this->menu_content);
         $this->menu_content = preg_replace('#'.$rg_link_http.'#i', '<a href="$0" class="url">xx$1</a>', $this->menu_content);
         $this->menu_content = preg_replace('#xxttp#', 'http', $this->menu_content);
         preg_match_all("/\[([^\/]+)\]/U", $this->menu_content, $matches, PREG_PATTERN_ORDER);
         foreach ($matches[1] as $match)
            if (file_exists(Page::filepath($match)))
               $this->menu_content = str_replace("[$match]", '<a href="' . $this->wikissme->script_basename . '?page=' . urlencode($match) . '">'.$match.'</a>', $this->menu_content);
            else
               $this->menu_content = str_replace("[$match]", '<a href="' . $this->wikissme->script_basename . '?page=' . urlencode($match) . '" class="pending" >'.$match.'</a>', $this->menu_content);
         $this->menu_content = preg_replace(WIKISSME_REGEX_HRLINE, '<hr />', $this->menu_content);
         $this->menu_content = preg_replace(WIKISSME_REGEX_EMAILADDRESS, '<a href="mailto:$0">$0</a>', $this->menu_content);
         // create list
         $this->menu_content = preg_replace('#(.+)$#m','<li>$1</li>',$this->menu_content);
         $this->menu_content = '<ul>'.$this->menu_content.'</ul>';
      }
   } // createMenu
   
   function __construct(&$wikissme)
   {
		parent::AbstractOldPlugin($wikissme);
   } // __construct()
   
   function __destruct()
   {
   } // __destruct()
   
   function __toString()
   {
      // return tr('Affiche un menu sur toutes les pages wiki');
      return 'Displays a menu on all wiki pages';
   }

   function formatBegin()
   {
      // print "debug[" . __METHOD__ . "]: <br />"; // DEBUG
      $this->createMenu();
      return FALSE;
   } // formatBegin ()

	function template ()
	{
		$template_html = &$this->wikissme->template_html;	// MUST be passed by reference.
		// replace tokens in the template
		// NOTE:
		//   In this implementation the RegEx will replace the PAGE_MENU template tag 
		//   along with the surrounding formatting HTML, in this case a DIV.
		//   So if there is no PAGE_MENU template tag then the surrounding formatting
		//   HTML will be removed as well.
		// TODO:
		//   This should be simplified by adding a simple PAGE_MENU template tag and 
		//   replace it with the menu content including surrounding formating
		//   HTML such as a DIV.
		if ($this->isPresent)
		{
			$template_html = preg_replace('/{([^}]*)PAGE_MENU(.*)}/U',"$1".$this->menu_content."$2",$template_html);
		}
		else
		{
			$template_html = preg_replace('/{([^}]*)PAGE_MENU(.*)}/U','',$template_html);
		}
		return FALSE;
	} // template ()
}

?>
