<?php # coding: utf-8

/** File upload plugin for Wikiss
 * access with : ?action=upload
 */

/** configuration **/
$DATA_DIR = 'data'; /* directory where files are stored */

class Upload
{

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
      return tr('Gestions de fichiers');
   }
      
   function action($action)
   {
      global $_GET,$CONTENT,$PAGE_TITLE,$PAGE_TITLE_link,$editable,$DATA_DIR;
      $current = '';
      
      if ($action == 'upload')
      {
         $CONTENT = ''; // reset du contenu de la page
         $PAGE_TITLE_link = FALSE; // pas de lien sur le titre
         $editable = FALSE; // non editable
         $PAGE_TITLE = tr('Téléchargement de fichiers'); // titre de la page
         // remplissage du contenu
         if (is_dir($DATA_DIR))
         {
            // Set current dir
            if (isset($_REQUEST['current']))
               $current = $this->cleanInput($_REQUEST['current']); // POST ou GET
            if (!preg_match('/^'.trim($DATA_DIR,'/').'/',$current))
               $current = trim($DATA_DIR,'/'); // on considere que $DATA_DIR est clean
            
            // Copie du fichier
            if (authentified())
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
                  // an error occurs during upload
                  $_GET['error'] = tr ('Fichier non téléchargé') . ' ('.$_FILES['fichier']['error'].')';
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
               $_GET['error'] = tr('Mot de passe').tr(' spécifié incorrect.');
            }
            // liste des fichiers
            if ($opening_dir = @opendir($current))
            {
               $CONTENT .= '<h3>'.$current.'</h3>';
               $files = array();
               while (false !== ($filename = @readdir($opening_dir)))
                  if (($filename != '.') and !($filename=='..' and $current==trim($DATA_DIR,'/')))
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
                     $CONTENT = $CONTENT.'<a href="./?action='.$action.'&current='.urlencode($path).'">'.$file.'/</a>';
                  }
                  else
                  {
                     $CONTENT = $CONTENT.'<a href="'.$path.'">'.$file.'</a>';
                  }
                  if ((authentified()) and ($file != '..'))
                     // autendified donc peut effacer
                     $CONTENT .= ' (</font><a title="delete" href="./?action=upload&del='.urlencode($path)."&current=".urlencode($current).'">&times;</a>)';
                  $CONTENT .= '<br />';
               }
            }
            // formulaire d'upload
            $CONTENT .= '<hr/><div style="float:right;"><form method="post" action="./?action='.$action.'" enctype= "multipart/form-data"><p align="right">';
            if (!authentified())
               $CONTENT .= tr('Mot de passe').' : <input type="password" name="sc" /> <br />';
            $CONTENT .= '<input type="hidden" name="current" value="'.$current.'" /> <br/>';
            $CONTENT .= tr('Fichier').' : <input type="file" name="fichier" /> <br/>';
            $CONTENT .= tr('R&eacute;pertoire &agrave; cr&eacute;er').' : <input type="text" name="repertoire" /> <br/>';
            $CONTENT .= ' <input type="submit" value="'.tr('Enregistrer').'" accesskey="s" /></p></form></div>';
            $CONTENT .= '<div style="float:left;" >'.tr('Entrez eventuellement votre mot de passe.<br/>Choississez le fichier &agrave; uploader en cliquant sur <i>Parcourir</i> ou le r&eacute;pertoire &agrave; cr&eacute;er. Cliquez sur <i>Envoyer</i><br/>Si vous &ecirc;tes identifi&eacute;, vous pouvez effacer un fichier en cliquant sur &times;<br/>La taille maximum autorisée est de : ').ini_get('upload_max_filesize').'</div>';
         }
         else
            $CONTENT = tr('Page verrouillée'); // page désactivée
        return TRUE;
      } // action == upload
      return FALSE; // action non traitée
   } // action
} // Upload

?>
