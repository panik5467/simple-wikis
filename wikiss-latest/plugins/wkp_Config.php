<?php # coding: utf-8

/** Liste les plugins installés
 * Accès via : ?action=list
 */
class Config
{

   function __toString()
   {
      return tr('Configuration de WiKiss');
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
      global $plugins,$CONTENT,$PAGE_TITLE,$PAGE_TITLE_link,$editable;
      
      if ($a == "config")
      {
         $CONTENT = '<table width="100%"><tr valign="top"><td width="50%">'; // reset du contenu de la page
         $PAGE_TITLE_link = FALSE; // pas de lien sur le titre
         $editable = FALSE; // non editable
         $PAGE_TITLE = tr('Configuration'); // titre de la page
         // plugin list
         $CONTENT .= '<h2>Plugins</h2>';
         $plugins_files = $this->getFiles ('plugins',"/^(_?wkp_.+)\.php$/");
         //~ foreach ($plugins as $p)
            //~ $CONTENT .= get_class($p) . " : ". $p ."<br/>\n";
         foreach ($plugins_files as $p)
         {
            $CONTENT .= '<b>'.$this->display($p).'</b>';
            if ($p[0] == '_')
               require 'plugins/' . $p . '.php';
            $pname = trim(strrchr($p,'_'),'_');
            $templug = new $pname();
            $CONTENT .= '  (<i>'.$templug.'</i>)<br/>';
         }
         
         $CONTENT .= '</td><td>';
         
         // locales list
         $CONTENT .= "<h2>Locales</h2>";
         $locales = $this->getFiles (Translate::TRANSLATE_DIR,"/^(_?[a-z]{2})\.php$/");
         foreach ($locales as $l)
            $CONTENT .= $this->display($l).'<br/>';
         $CONTENT .= '</td></tr></table>';
         return TRUE;
      }
      return FALSE; // action non traitée
   } // action
}

?>
