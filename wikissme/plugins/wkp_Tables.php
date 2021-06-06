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
 *  @version $Id: wkp_Tables.php,v 1.2 2009/07/10 20:04:31 neven Exp $
 *  @author Neven Boyanov
 *
 * ORIGINAL COMMENTS:
 *  Plugin syntaxe de tableaux pour WiKiss
 *  Code basé sur le système de table de Eomys pour TigerWiki
 *  http://chabel.org/forum/comments.php?DiscussionID=11&page=1#Item_0
 *
 */

class Tables
{
   function __toString()
   {
      // return tr('Add a syntax for tables');
      return 'Add a syntax to show tables';
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
      global $PAGE_CONTENT;
      $PAGE_CONTENT = preg_replace(
      "/((^ *\|[^\n]*\| *$\n)+)/me",
      '"<table class=\"wikitable\">".stripslashes($this->make_table("$1"))."</table>\n"',
      $PAGE_CONTENT);
   }


}

?>
