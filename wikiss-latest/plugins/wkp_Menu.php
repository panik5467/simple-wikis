<?php # coding: utf-8

/** Plugin for Menu page
 * Allow a special page to be displayed on every wiki page
 * ****
 * For special page used, see $MENU_PAGE below
 * Edit this page via wikiss. Supported syntax includes links and images
 * Use the tag {MENU} in template.html
 */

/* Name of the special page treated as a menu */
$MENU_PAGE = 'Menu';

class Menu
{
   /*
    update thoses methods following your needs
    * remove any unused one
    */
   private $menu = '';
   public $isMenu = false;
   
   function createMenu()
   {
      global $PAGES_DIR,$MENU_PAGE;
      $menu_name = $PAGES_DIR . $MENU_PAGE . '.txt';
      if (file_exists($menu_name))
      { // load menu
         $this->isMenu = true; // a menu exists
         $file = fopen($menu_name, "r");
         $this->menu = fread($file, filesize($menu_name));
         fclose($file);
         // format menu
         $this->menu = preg_replace('/\r/','',$this->menu);
         $this->menu = preg_replace("/&(?!lt;)/","&amp;",$this->menu);
         $this->menu = str_replace("<","&lt;",$this->menu);
         
         $this->menu = preg_replace('/----*/m', '<hr />', $this->menu);
         $rg_url        = "[0-9a-zA-Z\.\#/~\-_%=\?\&,\+\:@;!\(\)\*\$']*"; // TODO: verif & / &amp;
         $rg_img_local  = "(".$rg_url."\.(jpeg|jpg|gif|png))"; 
         $rg_img_http   = "h(ttps?://".$rg_url."\.(jpeg|jpg|gif|png))";
         $rg_link_local = "(".$rg_url.")";
         $rg_link_http  = "h(ttps?://".$rg_url.")";
         // image
         $this->menu = preg_replace('#\['.$rg_img_http.'(\|(right|left))?\]#','<img src="xx$1" alt="xx$1" style="float:$4;"/>',$this->menu); // [http.png] / [http.png|right]
         $this->menu = preg_replace('#\['.$rg_img_local.'(\|(right|left))?\]#','<img src="$1" alt="$1" style="float:$4"/>',$this->menu); // [local.png] / [local.png|left]
         // image link [http://wikiss.tuxfamily.org/img/logo_100.png|http://wikiss.tuxfamily.org/img/logo_100.png]
         
         $this->menu = preg_replace('#\['.$rg_img_http.'\|'.$rg_link_http  .'(\|(right|left))?\]#U', '<a href="xx$3" class="url"><img src="xx$1" alt="xx$3" title="xx$3" style="float:$5;"/></a>', $this->menu);  // [http|http]
         $this->menu = preg_replace('#\['.$rg_img_http.'\|'.$rg_link_local .'(\|(right|left))?\]#U', '<a href="$3" class="url"><img src="xx$1" alt="$3" title="$3" style="float:$5;"/></a>', $this->menu); // [http|local]
         $this->menu = preg_replace('#\['.$rg_img_local.'\|'.$rg_link_http .'(\|(right|left))?\]#U', '<a href="xx$3" class="url"><img src="$1" alt="xx$3" title="xx$3" style="float:$5;"/></a>', $this->menu); // [local|http]
         $this->menu = preg_replace('#\['.$rg_img_local.'\|'.$rg_link_local.'(\|(right|left))?\]#U', '<a href="$3" class="url"><img src="$1" alt="$3" title="$3" style="float:$5;"/></a>', $this->menu); // [local|local]
         
         $this->menu = preg_replace('#\[([^\]]+)\|'.$rg_link_http.'\]#U', '<a href="xx$2" class="url">$1</a>', $this->menu);
         $this->menu = preg_replace('#\[([^\]]+)\|'.$rg_link_local.'\]#U', '<a href="$2" class="url">$1</a>', $this->menu);
         $this->menu = preg_replace('#'.$rg_link_http.'#i', '<a href="$0" class="url">xx$1</a>', $this->menu);
         $this->menu = preg_replace('#xxttp#', 'http', $this->menu);
         preg_match_all("/\[([^\/]+)\]/U", $this->menu, $matches, PREG_PATTERN_ORDER);
         foreach ($matches[1] as $match)
            if (file_exists($PAGES_DIR."$match.txt"))
               $this->menu = str_replace("[$match]", '<a href="./?page='.$match.'">'.$match.'</a>', $this->menu);
            else
               $this->menu = str_replace("[$match]", '<a href="./?page='.$match.'" class="pending" >'.$match.'</a>', $this->menu);
         $this->menu = preg_replace('#([0-9a-zA-Z\./~\-_]+@[0-9a-z\./~\-_]+)#i', '<a href="mailto:$0">$0</a>', $this->menu);
         // create list
         $this->menu = preg_replace('#(.+)$#m','<li>$1</li>',$this->menu);
         $this->menu = '<ul>'.$this->menu.'</ul>';
      }
   } // createMenu
   
   function __construct()
   {
      
   } // __construct()
   
   function __destruct()
   {
   } // __destruct()
   
   function __toString()
   {
      return _('Affiche un menu sur toutes les pages wiki');
   } // __toString()

   function writedPage ()
   {
   } // writedPage ()
   
   function action ($action)
   {
   } // action ($action)
   
   function formatBegin ()
   {
      $this->createMenu();
      return FALSE;
   } // formatBegin ()

   function formatEnd ()
   {
      global $CONTENT;
      // do stuff about formating the displayed page
      return FALSE;
   } // formatEnd ()
   
   function template ()
   {
      global $html;
      // replace tokens in the template
      if ($this->isMenu)
      {
         $html = preg_replace('/{([^}]*)MENU(.*)}/U',"$1".$this->menu."$2",$html);
      }
      else
      {
         $html = preg_replace('/{([^}]*)MENU(.*)}/U','',$html);
      }
      return FALSE;
   } // template ()
}

?>
