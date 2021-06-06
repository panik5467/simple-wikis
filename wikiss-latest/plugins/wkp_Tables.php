<?php # coding: utf-8

/** Plugin syntaxe de tableaux pour WiKiss
 * Code basé sur le système de table de Eomys pour TigerWiki
 * http://chabel.org/forum/comments.php?DiscussionID=11&page=1#Item_0
 */
class Tables
{
   function __toString()
   {
      return _('Add a syntax for tables');
   }
   
   function table_style($s)
   {
      $r = ''; $st = '';

      if (strpos($s, 'l') !== false)
       $st .= 'text-align: left; ';
      else if (strpos($s, 'r') !== false)
       $st .= 'text-align: right; ';

      if (strpos($s, 't') !== false)
       $st .= 'vertical-align: top; ';
      else if (strpos($s, 'b') !== false)
       $st .= 'vertical-align: bottom; ';
         
      return $r . ($st ? ' style="' . $st . '"' : '');
   }

   function make_table($s)
   {
      global $matches_links;
      // Suppression des espaces en debut et fin de ligne
      //~ $s = trim($s);
      // on enleve les liens contenants |
      $regex = "/\[([^]]+\|.+)\]/Ums";
      $nblinks = preg_match_all($regex,$s,$matches_links);
      $s = preg_replace($regex,"[LINK]",$s);
      // Doublage des |
      $s = str_replace('|', '||', $s);

      // Creation des <tr></tr> en se servant des debuts et fins de ligne
      $s = preg_replace('/^\s*\|(.*)\|\s*$/m', '<tr>$1</tr>', $s);
      $s = str_replace("\n","",$s);

      // Creation des <th></th> et des <td></td> en se servant des |
      $s=preg_replace('/\|(h){0,1}(([lrtb]* ){0,1})(\s*(\d*)\s*,(\d*)\s*){0,1}(.*?)\|/e',
         '"<t".("$1"?"h":"d").("$5"?" colspan=\"$5\"":" ").("$6"?" rowspan=\"$6\"":" ").$this->table_style("$2").">$7</t".("$1"?"h":"d").">"',$s);


      if ($nblinks> 0)
         $s = preg_replace_callback(array_fill(0,$nblinks,"/\[LINK\]/"),
            create_function('$m',
            'global $matches_links;static $idxlink=0;return "[".$matches_links[1][$idxlink++]."]";') ,$s);

      return stripslashes($s);
   }
   
   function formatBegin()
   {
      global $CONTENT;
      $CONTENT = preg_replace(
      "/((^ *\|[^\n]*\| *$\n)+)/me",
      '"<table class=\"wikitable\">".stripslashes($this->make_table("$1"))."</table>\n"',
      $CONTENT);
   }


}

?>
