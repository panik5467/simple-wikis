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
 *  @version $Id: wkp_Config.php,v 1.3 2009/07/18 20:28:29 neven Exp $
 *  @author Neven Boyanov
 *
 * ORIGINAL COMMENTS:
 *  Liste les plugins installés
 *  Accès via : ?action=list
 *
 */

require_once "classes/AbstractOldPlugin.php";

class Config extends AbstractOldPlugin
{
   function __construct(&$wikissme)
   {
		parent::AbstractOldPlugin($wikissme);
   }

   function __toString()
   {
      // return tr('Configuration de WiKiss');
      return localize('INFOLABEL_SYSTEMCONFIGURATION');
   }
   
   /** get list of files
    * $dir: where to search
    * $patern : matching patern
    */
   function getFiles ($dir,$patern)
   {
      $a = array();
      if (is_dir($dir) && ($odir = opendir($dir)))
      {
         while (($file = readdir($odir)) !== false)
         {
            if (preg_match($patern, $file, $matches)>0)
            {
               array_push($a,$matches[1]);
            }
         }
      }
      return $a;
   } // getFiles
   
   function display ($name)
   {
      $str = '';
      if ($name[0] == '_')
         $str .= '<input type="checkbox" disabled="disabled" />  ';
      else
         $str .= '<input type="checkbox" checked="checked" disabled="disabled" />  ';
      $str .= trim($name,'_');
      return $str;
   } // display
      
   function action($a)
   {
      global $PAGE_CONTENT, $PAGE_TITLE_link, $editable;
      
      if ($a == "config")
      {
         $PAGE_CONTENT = '<table width="100%"><tr valign="top"><td width="50%">'; // reset du contenu de la page
         $PAGE_TITLE_link = FALSE; // pas de lien sur le titre
         $editable = FALSE; // non editable
         $this->wikissme->page->name = localize('INFOLABEL_CONFIGURATION'); // titre de la page
         // plugin list
         $PAGE_CONTENT .= '<h2>Plugins</h2>';
         $plugins_files = $this->getFiles('plugins', "/^(_?wkp_.+)\.php$/");
         foreach ($plugins_files as $p)
         {
         	$plugin_rank = FALSE;
            $PAGE_CONTENT .= '<b>'.$this->display($p).'</b>';
            if ($p[0] == '_')
               require 'plugins/' . $p . '.php';
            $pname = trim(strrchr($p,'_'),'_');
            $templug = new $pname($this->wikissme);	// FIXED ---- TODO: This MUST be fixed, should not pass just NULL as parameter ----
            if (method_exists($pname, "getRank")) $plugin_rank = $templug->getRank();
            $PAGE_CONTENT .= 
				' :  <i>'.$templug.'</i>' . 
				($plugin_rank ? " (rank:{$plugin_rank})" : "") . 
				'<br/>';
         }
         
         $PAGE_CONTENT .= '</td><td>';
         
         // locales list
         $PAGE_CONTENT .= "<h2>Locales</h2>";
         $locales = $this->getFiles (Translate::TRANSLATE_FOLDER,"/^(_?[a-z]{2})\.php$/");
         foreach ($locales as $l)
            $PAGE_CONTENT .= $this->display($l).'<br/>';
         $PAGE_CONTENT .= '</td></tr></table>';
         return TRUE;
      }
      return FALSE; // action non traitée
   } // action
}

?>
