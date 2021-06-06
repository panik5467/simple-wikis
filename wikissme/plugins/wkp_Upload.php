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
 *  @version $Id: wkp_Upload.php,v 1.3 2009/07/18 20:28:29 neven Exp $
 *  @author Neven Boyanov
 *
 * ORIGINAL COMMENTS:
 *  File upload plugin for Wikiss
 *  access with : ?action=upload
 *
 */

require_once "classes/AbstractOldPlugin.php";

class Upload extends AbstractOldPlugin
{
   function __construct(&$wikissme)
   {
		parent::AbstractOldPlugin($wikissme);
   }

   /** Check/corect path validity
    * remove .., ^/, //, /$ and decode path
    * input: path
    * return corrected path
    */
   function cleanInput($input)
   {
      return trim(preg_replace('/(\.\.|^\/|\/\/)/','',urldecode($input)),'/');
   } // cleanInput

   function __toString()
   {
      // return tr('Gestions de fichiers');
      return localize('INFOLABEL_FILEMANAGEMENT');
   }
      
   function action($action)
   {
      global $_GET, $PAGE_CONTENT, $PAGE_TITLE_link, $DATA_FOLDER, $editable;

	  $UPLOAD_FOLDER = $DATA_FOLDER . '/files'; /* directory where files are stored */
      $current = '';
      
      if ($action == 'upload')
      {
         $PAGE_CONTENT = ''; // reset du contenu de la page
         $PAGE_TITLE_link = FALSE; // pas de lien sur le titre
         $editable = FALSE; // non editable
         $this->wikissme->page->name = localize('INFOLABEL_FILEUPLOAD'); // titre de la page
         
         // print "UPLOAD_FOLDER: {$UPLOAD_FOLDER}<br />";	// ---- DEBUG
         // remplissage du contenu
         if (is_dir($UPLOAD_FOLDER))
         {
            // Set current dir
            if (isset($_REQUEST['current']))
               $current = $this->cleanInput($_REQUEST['current']); // POST ou GET
            if (!preg_match('/^' . str_replace("/", "\/", trim($UPLOAD_FOLDER, '/')) . '/', $current))
               $current = trim($UPLOAD_FOLDER,'/'); // on considere que $UPLOAD_FOLDER est clean
            
            // Copie du fichier
            if (authenticated())
            {  // password is ok
               // Creation de repertoire
               if (isset($_POST['repertoire']) and !empty($_POST['repertoire']))
               {
                  @mkdir($current.'/'.$this->cleanInput($_POST['repertoire']));
               }
               // Upload de fichier
               elseif (!empty($_FILES['fichier']['tmp_name']))
               { //Un fichier a ete envoye, nous pouvons le traiter
                  if(is_uploaded_file($_FILES['fichier']['tmp_name']) and !stripos($_FILES['fichier']['name'],'php'))
                  {
                     @move_uploaded_file($_FILES['fichier']['tmp_name'],$current.'/'.$_FILES['fichier']['name']);
                  }
               }
               elseif (isset ($_FILES['fichier']) && $_FILES['fichier']['error'] != UPLOAD_ERR_OK)
               {
                  // an error occurs during upload
                  // $_GET['error'] = tr('Fichier non téléchargé') . ' ('.$_FILES['fichier']['error'].')';	// Replaced by the code below ...
                  $this->wikissme->addError(-1, localize('INFOLABEL_FILENOTUPLOADED') . ' ('.$_FILES['fichier']['error'].')');
               }
               // Effacement de fichier/repertoire
               if (isset($_GET['del']))
               { // Fichier à effacer
                  $dir = $this->cleanInput($_GET['del']);
                  if (is_dir($dir))
                     $ret = @rmdir($dir);
                  else
                     $ret = @unlink($dir);
               }

            }
            elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
            { // pas authentifié, mais clique sur envoyer --> mauvais password
              // EN: "password" + "input incorrect"
               // $_GET['error'] = tr('Mot de passe').tr(' spécifié incorrect.');	// Replaced by the code below ...
               $this->wikissme->addError(-1, localize('INFOLABEL_PASSWORDINCORRECT'));
            }
            // liste des fichiers
            if ($opening_dir = @opendir($current))
            {
               $PAGE_CONTENT .= '<h3>'.$current.'</h3>';
               $files = array();
               while (false !== ($filename = @readdir($opening_dir)))
                  if (($filename != '.') and !($filename=='..' and $current==trim($UPLOAD_FOLDER,'/')))
                     $files[] = $filename;
               if ($files)
                  sort ($files);
               foreach ($files as $file)
               {
                  if ($file == '..')
                     $path = substr($current,0,strrpos($current,'/'));
                  else
                     $path = $current.'/'.$file;
                  if (is_dir($path))
                  { // repertoire
                     $PAGE_CONTENT = $PAGE_CONTENT.'<a href="' . $wikissme->script_basename . '?action='.$action.'&current='.urlencode($path).'">'.$file.'/</a>';
                  }
                  else
                  {
                     $PAGE_CONTENT = $PAGE_CONTENT.'<a href="'.$path.'">'.$file.'</a>';
                  }
                  if ((authenticated()) and ($file != '..'))
                     // autendified donc peut effacer
                     $PAGE_CONTENT .= ' (</font><a title="delete" href="' . $wikissme->script_basename . '?action=upload&del='.urlencode($path)."&current=".urlencode($current).'">&times;</a>)';
                  $PAGE_CONTENT .= '<br />';
               }
            }
            // formulaire d'upload
            $PAGE_CONTENT .= '<hr/><div style="float:right;"><form method="post" action="' . $wikissme->script_basename . '?action='.$action.'" enctype= "multipart/form-data"><p align="right">';
            if (!authenticated())
               // $PAGE_CONTENT .= tr('Mot de passe').' : <input type="password" name="sc" /> <br />';
               $PAGE_CONTENT .= localize('INFOLABEL_PASSWORD') . ': <input type="password" name="sc" /> <br />';
            $PAGE_CONTENT .= '<input type="hidden" name="current" value="'.$current.'" /> <br/>';
            // $PAGE_CONTENT .= tr('Fichier').' : <input type="file" name="fichier" /> <br/>';
            $PAGE_CONTENT .= localize('INFOLABEL_FILE') . ': <input type="file" name="fichier" /> <br/>';
            // $PAGE_CONTENT .= tr('R&eacute;pertoire &agrave; cr&eacute;er').' : <input type="text" name="repertoire" /> <br/>';
            $PAGE_CONTENT .= localize('INFOLABEL_FOLDERTOCREATE') . ' : <input type="text" name="repertoire" /> <br/>';
            // $PAGE_CONTENT .= ' <input type="submit" value="'.tr('Enregistrer').'" accesskey="s" /></p></form></div>';
            $PAGE_CONTENT .= ' <input type="submit" value="' . localize('ACTLABEL_SUBMIT') . '" accesskey="s" /></p></form></div>';
            $PAGE_CONTENT .= 
			  '<div style="float:left;" >' .
			  localize('INFOTEXT_PLUGINUPLOAD_GUIDESHORT') . ini_get('upload_max_filesize') .
			  '</div>';
         }
         else
         {
            // $PAGE_CONTENT = tr('Page verrouillée'); // page désactivée, "page locked"
            $PAGE_CONTENT = localize('INFOLABEL_FOLDERTOCREATE'); // page désactivée, "page locked"
         }
        return TRUE;
      } // action == upload
      return FALSE; // action non traitée
   } // action
} // Upload

?>
