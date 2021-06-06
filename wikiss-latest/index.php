<?php # coding: utf-8
   /* WiKiss - http://wikiss.tuxfamily.org/
    * Licence GNU/GPLv2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
    * Copyright (C) JJL 2007
    * Basé sur TigerWiki 2.22 par Chabel.org - http://chabel.org
    */

   /* performances calculation   
   $perf1 = microtime();
   */
   
   $WIKI_VERSION = 'WiKiss 0.4beta1';
   
   ini_set('default_charset','UTF-8'); // Default character set for auto content type header
   header('Content-type: text/html; charset=UTF-8');

   // Fichier de configuration
   include('_config.php');
   
   /** test si l'utilisateur est authentifié
    * pose un cookie si absent
    */
   function authentified()
   {
      global $PASSWORD;
      $pwd=md5($PASSWORD);
      if (((isset($_COOKIE['AutorisationWiKiss'])) && ($_COOKIE['AutorisationWiKiss'] == $pwd)) || ((isset($_POST['sc']) && $_POST['sc'] == $PASSWORD)) || ($PASSWORD == ''))
      {
         if (($PASSWORD != '') && (empty($_COOKIE['AutorisationWiKiss']) || ($_COOKIE['AutorisationWiKiss'] != $pwd)))
         {
            setcookie('AutorisationWiKiss',$pwd, time() + 365*24*3600);
            $_COOKIE['AutorisationWiKiss'] = $pwd;
         }
         return TRUE;
      }
      else
         return FALSE;
   } // authentified()


   /** Call a method for all plugins
    * $mname: method name
    * [...] : method arguments
    * return: TRUE if treated by a plugin
    */
   function plugin_call_method ($mname)
   {
      global $plugins;
      $ret = FALSE;
      foreach ($plugins as $plugin)
         if (method_exists($plugin,$mname))
         {
            $args = func_get_args();
            $ret |= call_user_func_array(array($plugin,$mname),array_slice($args,1));
         }
      return $ret;
   } // plugin_call_method()
   
   /** translate a string if needed
    * $str: the string to be translated
    * return the translated string
    */
   function tr ($str)
   {
      global $strings; // localized strings from locale/xx.php
      if (array_key_exists($str,$strings) and !empty($strings[$str]))
      {
         return $strings[$str];
      }
      else
      {
         return $str;
      }
   } // tr()

   /** load language file
    */
   function loadLang($lang)
   {
      global $strings;
      // include locale file if needed
      $locale_fname = 'locale/'.$lang.'.php';
      if (file_exists($locale_fname))
         include_once($locale_fname);
      else
         $strings = array();
   } // loadLang()

   /** Initialisations */
   $toc = ''; // Table Of Content
   $CONTENT = ''; // contenu de la page
   $HISTORY = ''; // lien vers l'historique
   $plugins_dir = 'plugins/'; // repertoire ou stocker les plugins
   $plugins = array(); // tableau des objets plugin
   $template = 'template.html'; // Fichier template
   $PAGE_TITLE_link = TRUE; // y-a-t-il un lien sur le titre de la page ?
   $editable = TRUE; // la page est editable

   /** Chargement des plugins */
   if (is_dir($plugins_dir) && ($dir = opendir($plugins_dir)))
   {
      while (($file = readdir($dir)) !== false)
      {
         if (preg_match('/^wkp_(.+)\.php$/', $file, $matches)>0)
         {
            require $plugins_dir . $file;
            $plugins[] = new $matches[1]();
         }
      }
      //~ print_r($plugins);
   }

   // Load current language
   loadLang($LANG);

   /** Traitement des variables passées au script */
   // Conversion en UTF-8
   if (extension_loaded('mbstring'))
   {
      ini_set('mbstring.language','Neutral'); // Set default language to Neutral(UTF-8) (default)
      ini_set('mbstring.internal_encoding','UTF-8'); // Set default internal encoding to UTF-8
      ini_set('mbstring.http_output','UTF-8'); // Set HTTP output encoding to UTF-8
      ini_set('mbstring.detect_order','UTF-8,ISO-8859-1'); // Set default character encoding detection order
      ini_set('mbstring.func_overload',MB_OVERLOAD_STRING);
      
      $get_conv = array('page','query');
      $post_conv = array('sc','content','page');
      foreach ($get_conv as $get_key)
      {
         if (isset($_GET[$get_key]))
         {
            // print "$get_key detected:". mb_detect_encoding($_GET[$get_key])."\n"; // DBG
            $_GET[$get_key] = mb_convert_encoding($_GET[$get_key],'UTF-8',mb_detect_encoding($_GET[$get_key]));
         }
      }
      foreach ($post_conv as $post_key)
         if (isset($_POST[$post_key]))
            $_POST[$post_key] = mb_convert_encoding($_POST[$post_key],'UTF-8',mb_detect_encoding($_POST[$post_key]));
   }

   // Effacement du cookie
   if (isset($_GET['erasecookie']))
   {
      setcookie('AutorisationWiKiss');
      $_COOKIE['AutorisationWiKiss'] = ''; // remove cookie without reloading
   }

   // Trouver la page a afficher
   if (isset($_GET['page']))
   {
      $PAGE_TITLE = stripslashes($_GET['page']);
   }
   else
   {
      $_GET['page'] = '';
      if (isset($_GET['action']))
      {
         if ($_GET['action'] == 'search')
            if (isset($_GET['query']) && $_GET['query'] != '')
               $PAGE_TITLE = tr('Résultats de recherche pour').' '. $_GET['query']; // html encoded later
            else
               $PAGE_TITLE = tr('Liste des pages');
         elseif ($_GET['action'] == 'recent')
            $PAGE_TITLE = tr('Changements récents');
         else
            $PAGE_TITLE = "$START_PAGE";
      }
      else
         $PAGE_TITLE = "$START_PAGE";
   }
   if (isset($_GET['action']))
      $action = $_GET['action'];
   else
      $action = '';

   if (isset($_GET['time']))
   {
      $gtime = $_GET['time'];
      if (preg_match('/\//', $gtime))
         $gtime = '';
   }
   @date_default_timezone_set($TIME_ZONE);
   $datetw = date('Y/m/d H:i', mktime(date('H') + $LOCAL_HOUR));
   // Arreter les acces malicieux via repertoire et accents
   if (preg_match('/\//', $PAGE_TITLE))
      $PAGE_TITLE = $START_PAGE;
   // Ecrire les modifications, s'il y a lieu
   if (isset($_POST['content']))
   {
      if ($_SERVER['REQUEST_METHOD'] == 'POST')
      {
         $PAGE_TITLE = str_replace(array('/','\\'),'',stripslashes($_POST['page']));
         if (authentified())
         {
            if ($_POST['content'] == '')
            {
               if (file_exists($PAGES_DIR . $PAGE_TITLE . '.txt'))
                  unlink($PAGES_DIR . $PAGE_TITLE . '.txt');
            }
            else
            {
               if (! $file = @fopen($PAGES_DIR . $PAGE_TITLE . '.txt', 'w'))
                  die('Could not write page!');
               //~ $safe_content = htmlentities($_POST['content'],ENT_COMPAT,"UTF-8");
               $safe_content = str_replace('<','&lt;',$_POST['content']);
               if (get_magic_quotes_gpc())
                  fputs($file, stripslashes($safe_content));
               else
                  fputs($file, $safe_content);
               fclose($file);
               if ($BACKUP_DIR <> '')
               {
                  $complete_dir_s = $BACKUP_DIR . $PAGE_TITLE . '/'; // TODO BUG
                  if (! $dir = @opendir($complete_dir_s))
                  {
                     mkdir($complete_dir_s);
                     chmod($complete_dir_s,0777);
                  }
                  if (! $file = @fopen($complete_dir_s . date('Ymd-Hi', mktime(date('H') + $LOCAL_HOUR)) . '.bak', 'a'))
                     die('Could not write backup of page!');
                  fputs($file, "\n// " . $datetw . ' / ' . ' ' . $_SERVER['REMOTE_ADDR'] . "\n");
                  if (get_magic_quotes_gpc())
                     fputs($file, stripslashes($safe_content));
                  else
                     fputs($file, $safe_content);
                  fclose($file);
               }
            }
            plugin_call_method('writedPage',$file);
            header('location: ./?page=' . urlencode(stripslashes($PAGE_TITLE)));
            exit();
         }
         else
         {
            $_GET['error'] = tr('Mot de passe').tr(' spécifié incorrect.');
            $action = 'edit';
            $CONTENT = str_replace("<","&lt;",$_POST['content']);
         }
      }
   }
   // Lecture et analyse du modèle de page
   if (! $file = @fopen($template, 'r'))
      die("'$template' is missing!");
   $html = fread($file, filesize($template));
   fclose($file);
   // Lecture du contenu et de la date de modification de la page
   if (empty($_GET['error']) && (($file = @fopen($PAGES_DIR . $PAGE_TITLE . '.txt', 'r')) || $action <> ''))
   {
      if (file_exists($PAGES_DIR . $PAGE_TITLE . '.txt'))
         $TIME = date('Y/m/d H:i', @filemtime($PAGES_DIR . $PAGE_TITLE . '.txt') + $LOCAL_HOUR * 3600);
      $CONTENT = @fread($file, @filesize($PAGES_DIR . $PAGE_TITLE . '.txt'));
      // Restaurer une page
      if (isset($_GET['page']) && isset($gtime) && isset($_GET['restore']) && $_GET['restore'] == 1)
         if ($file = @fopen($BACKUP_DIR . $PAGE_TITLE . '/' . $gtime, 'r'))
            $CONTENT = "\n" . @fread($file, @filesize($BACKUP_DIR . $PAGE_TITLE . '/' . $gtime)) . "\n";
      @fclose($file);
      $CONTENT = preg_replace('/\\$/Umsi', '&#036;', $CONTENT);
      $CONTENT = preg_replace('/\\\/Umsi', '&#092;', $CONTENT);
   }
   else
   {
      if (!file_exists($PAGES_DIR . $PAGE_TITLE . '.txt'))
         $CONTENT = "\n" . str_replace('%page%',stripslashes($PAGE_TITLE),tr('La page %page% est vide.')) ."\n";
      else
         $action = 'edit';
   }


   /** Actions spéciales du Wiki */
   if ($action == 'edit')
   { // edition de la page
      $editable = FALSE;
      $HISTORY = '<a href="?page='.urlencode($PAGE_TITLE).'&amp;action=history" accesskey="6" rel="nofollow">'.tr('Historique').'</a><br />';
      $CONTENT = '<form method="post" action="./"><textarea name="content" cols="83" rows="30" style="width: 100%;">'.$CONTENT.'</textarea><input type="hidden" name="page" value="'.$PAGE_TITLE.'" /><br /><p align="right">';
      if (!authentified())
         $CONTENT .= tr('Mot de passe').' : <input type="password" name="sc" />';
      $CONTENT .= ' <input type="submit" value="'.tr('Enregistrer').'" accesskey="s" /></p></form>';
      //Retrait d'un </div> avant le </form>
   }
   elseif ($action == 'history')
   { // historique d'une page
      if (isset($gtime))
      { // Afficaheg d'un fichier d'historique
         $complete_dir = $BACKUP_DIR . $PAGE_TITLE . '/';
         if ($file = @fopen($BACKUP_DIR . $PAGE_TITLE . '/' . $gtime, 'r'))
         {
            $HISTORY = '<a href="?page='.$PAGE_TITLE.'&amp;action=history" rel="nofollow">'.tr('Historique').'</a> <a href="?page='.$PAGE_TITLE.'&amp;action=edit&amp;time='.$gtime.'&amp;restore=1" rel="nofollow">'.tr('Restaurer').'</a>';
            $CONTENT = @fread($file, @filesize($complete_dir . $gtime)) . "\n";
            $CONTENT = str_replace("\n",'<br/>',str_replace("\r",'',$CONTENT));
         }
         else
            $HISTORY = '<a href="?page='.$PAGE_TITLE.'&amp;action=history" rel="nofollow">'.tr('Historique').'</a> -';
      }
      else
      { // Liste des versions historiques d'une page
         $HISTORY = tr('Historique');
         $complete_dir = $BACKUP_DIR . $PAGE_TITLE . '/';
         if ($opening_dir = @opendir($complete_dir))
         {
            while (false !== ($filename = @readdir($opening_dir)))
               if (preg_match('/\.bak$/',$filename))
                  $files[] = $filename;
            rsort ($files);
            $CONTENT = '<form method="GET" action="./">'."\n".'<input type=hidden name=action value=diff><input type=hidden name=page value="'.$PAGE_TITLE.'">'; 
            for ($cptfiles = 0; $cptfiles < sizeof($files); $cptfiles++)
            {
               $CONTENT .= '<input type="radio" name="f1" value="'.$files[$cptfiles].'"><input type="radio" name="f2" value="'.$files[$cptfiles].'" />';
               $CONTENT .= '<a href="?page='.$PAGE_TITLE.'&amp;action=history&amp;time='.$files[$cptfiles].'">'.$files[$cptfiles].'</a><br />';
            }
            $CONTENT .= '<input type="submit" value="diff"></form>';
         }
         else
            $CONTENT = tr('Aucun historique existant.');
      }
   }
   elseif ($action == 'diff')
   { // differences entre deux révisions
      if (isset($_GET['f1']))
      { // diff très simple entre deux pages
         $CONTENT = '';
         function pcolor($color,$txt)
            {return '<font color="'.$color.'">'.$txt.'</font><br/>';}
         $HISTORY = '<a href="?page='.urlencode($PAGE_TITLE).'&amp;action=history">'.tr('Historique').'</a>';
         if (!strpos($_GET['f1'],'/'))
            $fn1 = $BACKUP_DIR . $PAGE_TITLE . '/' . $_GET['f1'];
         if (!strpos($_GET['f2'],'/'))
            $fn2 = $BACKUP_DIR . $PAGE_TITLE . '/' . $_GET['f2'];
         if ($fn2 < $fn1) {$tmp=$fn1;$fn1=$fn2;$fn2=$tmp;}
         $a1 = explode("\n",@file_get_contents($fn1));
         $a2 = explode("\n",@file_get_contents($fn2));
         // fclose ?
         $d1 = array_diff($a1,$a2);
         $d2 = array_diff($a2,$a1);
         for ($i=0;$i<=max(sizeof($a2),sizeof($a1));$i++)
         {
            if (array_key_exists($i,$d1))
               $CONTENT .= pcolor('red',$d1[$i]);
            if (array_key_exists($i,$d2))
               $CONTENT .= pcolor('green',$d2[$i]);
            if (!(array_key_exists($i,$d1) && array_key_exists($i,$d2)) && @$d2[$i] != @$a2[$i])
               $CONTENT .= pcolor ('black',$a2[$i]);
         }
      }
      else
      { // diff auto entre les 2 dernières versions
         $complete_dir = $BACKUP_DIR . $PAGE_TITLE . '/';
         if ($opening_dir = @opendir($complete_dir))
         {
            while (false !== ($filename = @readdir($opening_dir)))
               if (preg_match('/\.bak$/',$filename))
                  $files[] = $filename;
            rsort ($files);
            header('location: ./?page=' . urlencode($PAGE_TITLE) .'&action='.$action.'&f1='.$files[0].'&f2='.$files[1]);
            exit();
         }
      }
   }
   elseif ($action == 'search')
   { // Page de recherche
      $files= '';
      $PAGE_TITLE_link = FALSE;
      $editable = FALSE;
      $dir = opendir (getcwd() . '/'.$PAGES_DIR);
      while ($file = readdir($dir))
      {
         if (preg_match('/\.txt$/',$file))
         {
            $handle = fopen($PAGES_DIR.$file, 'r');
            @$content = fread($handle, filesize($PAGES_DIR.$file));
            fclose($handle);
            if (isset($_GET['query']))
               $query = preg_quote($_GET['query'],'/');
            else
               $query='';
            if (@preg_match("/$query/i", $content) || preg_match("/$query/i", "$file"))
               $files[] = substr($file, 0, strlen($file) - 4);
         }
      }
      if (is_array($files))
      {
         sort($files);
         foreach ($files as $file)
            $CONTENT .= '<a href="./?page='.$file.'">'.$file.'</a><br />';
      }
      $PAGE_TITLE .= ' ('.count($files).')';
   }
   elseif ($action == 'recent')
   { // Changements récents
      $PAGE_TITLE_link = FALSE;
      $editable = FALSE;
      $dir = opendir(getcwd() . "/$PAGES_DIR");
      while ($file = readdir($dir))
         if (preg_match('/\.txt$/', $file))
            $filetime[$file] = filemtime($PAGES_DIR . $file);
      arsort($filetime);
      $filetime = array_slice($filetime, 0, 10);
      foreach ($filetime as $filename => $timestamp)
      {
         $filename = substr($filename, 0, strlen($filename) - 4);
         $CONTENT .= '<a href="./?page='.$filename.'">'.$filename.'</a> (' . strftime($TIME_FORMAT, $timestamp + $LOCAL_HOUR * 3600) . ' - <a href="./?page='.$filename.'&amp;action=diff">diff</a>)<br />';
      }
   }
   elseif ($action != '')
   {
      if (!plugin_call_method('action',$action))
         $action = '';
   }
   
   if ($action == '')
   { // Formatage de page
      $CONTENT = str_replace("\r",'',$CONTENT);
      $CONTENT = preg_replace('/&(?!lt;)/','&amp;',$CONTENT);
      $CONTENT = str_replace('<','&lt;',$CONTENT);
      //~ $CONTENT = htmlentities($CONTENT,ENT_COMPAT,"UTF-8");
      $CONTENT = preg_replace('/&amp;#036;/Umsi', '&#036;', $CONTENT); // ??
      $CONTENT = preg_replace('/&amp;#092;/Umsi', '&#092;', $CONTENT); // ??
      
      $CONTENT = preg_replace('/\^(.)/Umsie', "'&#'.ord('$1').';'", $CONTENT); // escape caractère
      //~ {{CODE}}
      $nbcode = preg_match_all('/{{(.+)}}/Ums',$CONTENT,$matches_code);
      $CONTENT = preg_replace('/{{(.+)}}/Ums','<pre><code>{{CODE}}</code></pre>',$CONTENT);
      //~ {{CODE}}

      plugin_call_method('formatBegin');

      // balises type bloc
      $CONTENT = preg_replace('/----*(\r\n|\r|\n)/m', '<hr />', $CONTENT);

      $CONTENT = preg_replace('/^\*\*\*(.*)(\n)/Um', "<ul><ul><ul><li>$1</li></ul></ul></ul>$2", $CONTENT);
      $CONTENT = preg_replace('/^\*\*(.*)(\n)/Um', "<ul><ul><li>$1</li></ul></ul>$2", $CONTENT);
      $CONTENT = preg_replace('/^\*(.*)(\n)/Um', "<ul><li>$1</li></ul>$2", $CONTENT);
      $CONTENT = preg_replace('/^\#\#\#(.*)(\n)/Um', "<ol><ol><ol><li>$1</li></ol></ol></ol>$2", $CONTENT);
      $CONTENT = preg_replace('/^\#\#(.*)(\n)/Um', "<ol><ol><li>$1</li></ol></ol>$2", $CONTENT);
      $CONTENT = preg_replace('/^\#(.*)(\n)/Um', "<ol><li>$1</li></ol>$2", $CONTENT);

      $CONTENT = preg_replace('/(<\/ol>\n*<ol>|<\/ul>\n*<ul>)/', '', $CONTENT);
      $CONTENT = preg_replace('/(<\/ol>\n*<ol>|<\/ul>\n*<ul>)/', '', $CONTENT);
      $CONTENT = preg_replace('/(<\/ol>\n*<ol>|<\/ul>\n*<ul>)/', '', $CONTENT);

      $CONTENT = preg_replace('#</li><ul><li>*#', '<ul><li>', $CONTENT);
      $CONTENT = preg_replace('#</ul></ul>*#', '</ul></li></ul>', $CONTENT);
      $CONTENT = preg_replace('#</ul></ul>*#', '</ul></li></ul>', $CONTENT);
      $CONTENT = preg_replace('#</li></ul><li>*#', '</li></ul></li><li>', $CONTENT);

      $CONTENT = preg_replace('#</li><ol><li>*#', '<ol><li>', $CONTENT);
      $CONTENT = preg_replace('#</ol></ol>*#', '</ol></li></ol>', $CONTENT);
      $CONTENT = preg_replace('#</ol></ol>*#', '</ol></li></ol>', $CONTENT);
      $CONTENT = preg_replace('#</li></ol><li>*#', '</li></ol></li><li>', $CONTENT);

      function name_title($matches) // replace titles
         {global $titres;
         $titres[]=array(strlen($matches[1]),preg_replace('/[^\da-z]/i','_',$matches[2]),$matches[2]);$i=count($titres)-1;
         return '<h'.$titres[$i][0].'><a name="'.$titres[$i][1].'">'.$titres[$i][2].'</a></h'.$titres[$i][0].'>';}
      $CONTENT = preg_replace_callback('/^(!+?)(.*)$/Um', 'name_title', $CONTENT);

      // Paragraphes
      $CONTENT = preg_replace('/(^$\n)+([^<]+?)^$/ms',"<p>\n$2</p>",$CONTENT); // <p></p> (sans balise)
      
      // balises type en ligne
      $CONTENT = str_replace('%%','<br />',$CONTENT); // %%
      $CONTENT = str_replace('&lt;-->', '&harr;', $CONTENT); // <-->
      $CONTENT = str_replace('-->', '&rarr;', $CONTENT); // -->
      $CONTENT = str_replace('&lt;--', '&larr;', $CONTENT); // <--
      $CONTENT = preg_replace('/\([cC]\)/Umsi', '&copy;', $CONTENT); // (c)
      $CONTENT = preg_replace('/\([rR]\)/Umsi', '&reg;', $CONTENT); // (r)

      $rg_url        = "[0-9a-zA-Z\.\#/~\-_%=\?\&,\+\:@;!\(\)\*\$']*"; // TODO: verif & / &amp;
      $rg_img_local  = '('.$rg_url.'\.(jpeg|jpg|gif|png))'; 
      $rg_img_http   = 'h(ttps?://'.$rg_url.'\.(jpeg|jpg|gif|png))';
      $rg_link_local = '('.$rg_url.')';
      $rg_link_http  = 'h(ttps?://'.$rg_url.')';
      // image
      $CONTENT = preg_replace('#\['.$rg_img_http.'(\|(right|left))?\]#','<img src="xx$1" alt="xx$1" style="float:$4;"/>',$CONTENT); // [http.png] / [http.png|right]
      $CONTENT = preg_replace('#\['.$rg_img_local.'(\|(right|left))?\]#','<img src="$1" alt="$1" style="float:$4"/>',$CONTENT); // [local.png] / [local.png|left]
      // image link [http://wikiss.tuxfamily.org/img/logo_100.png|http://wikiss.tuxfamily.org/img/logo_100.png]
      
      $CONTENT = preg_replace('#\['.$rg_img_http.'\|'.$rg_link_http  .'(\|(right|left))?\]#U', '<a href="xx$3" class="url"><img src="xx$1" alt="xx$3" title="xx$3" style="float:$5;"/></a>', $CONTENT);  // [http|http]
      $CONTENT = preg_replace('#\['.$rg_img_http.'\|'.$rg_link_local .'(\|(right|left))?\]#U', '<a href="$3" class="url"><img src="xx$1" alt="$3" title="$3" style="float:$5;"/></a>', $CONTENT); // [http|local]
      $CONTENT = preg_replace('#\['.$rg_img_local.'\|'.$rg_link_http .'(\|(right|left))?\]#U', '<a href="xx$3" class="url"><img src="$1" alt="xx$3" title="xx$3" style="float:$5;"/></a>', $CONTENT); // [local|http]
      $CONTENT = preg_replace('#\['.$rg_img_local.'\|'.$rg_link_local.'(\|(right|left))?\]#U', '<a href="$3" class="url"><img src="$1" alt="$3" title="$3" style="float:$5;"/></a>', $CONTENT); // [local|local]
      
      $CONTENT = preg_replace('#\[([^\]]+)\|'.$rg_link_http.'\]#U', '<a href="xx$2" class="url">$1</a>', $CONTENT);
      $CONTENT = preg_replace('#\[([^\]]+)\|'.$rg_link_local.'\]#U', '<a href="$2" class="url">$1</a>', $CONTENT);
      $CONTENT = preg_replace('#'.$rg_link_http.'#i', '<a href="$0" class="url">xx$1</a>', $CONTENT);
      $CONTENT = preg_replace('#xxttp#', 'http', $CONTENT);
      $CONTENT = preg_replace('#\[\?(.*)\]#Ui', '<a href="http://'.$LANG.'.wikipedia.org/wiki/$1" class="url" title="Wikipedia">$1</a>', $CONTENT); // Wikipedia
      preg_match_all('/\[([^\/]+)\]/U', $CONTENT, $matches, PREG_PATTERN_ORDER);
      foreach ($matches[1] as $match)
         if (file_exists($PAGES_DIR.$match.'.txt'))
            $CONTENT = str_replace("[$match]", '<a href="./?page='.$match.'">'.$match.'</a>', $CONTENT);
         else
            $CONTENT = str_replace("[$match]", '<a href="./?page='.$match.'" class="pending" >'.$match.'</a>', $CONTENT);
      $CONTENT = preg_replace('#([0-9a-zA-Z\./~\-_]+@[0-9a-z\./~\-_]+)#i', '<a href="mailto:$0">$0</a>', $CONTENT);
      
      while (preg_match('/^  /Um', $CONTENT))
         $CONTENT = preg_replace('/^( +) ([^ ])/Um', '$1&nbsp;&nbsp;&nbsp;&nbsp;$2', $CONTENT);
      $CONTENT = preg_replace('/^ /Um', '&nbsp;&nbsp;&nbsp;&nbsp;', $CONTENT);

      $CONTENT = preg_replace("/'--(.*)--'/Um", '<span style="text-decoration:line-through">$1</span>', $CONTENT); // barré
      $CONTENT = preg_replace("/'__(.*)__'/Um", '<span style="text-decoration:underline">$1</span>', $CONTENT); // souligné
      $CONTENT = preg_replace("/'''''(.*)'''''/Um", '<strong><em>$1</em></strong>', $CONTENT);
      $CONTENT = preg_replace("/'''(.*)'''/Um", '<strong>$1</strong>', $CONTENT);
      $CONTENT = preg_replace("/''(.*)''/Um", '<em>$1</em>', $CONTENT);
               
      // TOC
      if (strpos($CONTENT,'%TOC%') !== FALSE)
      {
         $CONTENT = preg_replace('/%TOC%/Um','',$CONTENT,1);
         $nbAncres = count($titres);
         $toc = '<div id="toc">';
         for ($i=0;$i<$nbAncres;$i++) $toc .= '<h'.$titres[$i][0].'><a href="#'.urlencode($titres[$i][1]).'">'.preg_replace('#[\[\]]#','',$titres[$i][2]).'</a></h'.$titres[$i][0].'> ';
         $toc .= '</div>';
      }
      //-- {CODE}
      function matchcode($m)
      {
         global $matches_code;
         static $idxcode=0;
         return $matches_code[1][$idxcode++];
      }
      if ($nbcode > 0)
         $CONTENT = preg_replace_callback(array_fill(0,$nbcode,'/{{CODE}}/'),'matchcode',$CONTENT);
      //-- {CODE}
      plugin_call_method('formatEnd');
   }

   /** Remplacement dans le template */
   // remplacement selon l'action
   if ($action == 'recent')
      $RECENT = tr('Changements récents');
   else
      $RECENT = '<a href="./?action=recent" accesskey="3">'.tr('Changements récents').'</a>';
   $html = preg_replace('/{([^}]*)RECENT_CHANGES(.*)}/U',"$1".$RECENT."$2",$html);
   
   if ($PAGE_TITLE == $START_PAGE && $action <> 'search')
      $HOME = tr('Accueil');
   else
      $HOME = '<a href="./?page='.$START_PAGE.'" accesskey="1">'.tr('Accueil').'</a>';
   $html = preg_replace('/{([^}]*)HOME(.*)}/U',"$1".$HOME."$2",$html);
   
   if ($action != 'edit')
      $html = preg_replace('/{[^}]*HELP.*}/U', '', $html);
   else
      $html = preg_replace('/{([^}]*)HELP(.*)}/U', "$1<a href=\"./?page=".$HELP_PAGE.'" accesskey="2" rel="nofollow">'.tr('Aide')."</a>$2", $html);
   if (!isset($_GET['query'])) $_GET['query'] = '';
      $html = str_replace('{SEARCH}', '<form method="get" action="./?page='.urlencode($PAGE_TITLE).'"><div><input type="hidden" name="action" value="search" /><input type="text" name="query" value="'.htmlentities($_GET['query'],ENT_COMPAT,'UTF-8').'" tabindex="1" /> <input type="submit" value="'.tr('Rechercher').'" accesskey="q" /></div></form>', $html);
   if ($action != '' && $action != 'edit' || (!file_exists($PAGES_DIR . $PAGE_TITLE . '.txt')))
      $TIME = '-';
   plugin_call_method('template');
   
   // Erreur du mot de passe
   if (isset($_GET['error']))
      $html = str_replace('{ERROR}', $_GET['error'], $html);
   else
      $html = str_replace('{ERROR}', '', $html);
      
   // remplacement selon variables
   if (!empty($HISTORY))
      $html = preg_replace('/{([^}]*)HISTORY(.*)}/U',"$1".$HISTORY."$2",$html);
   else
      $html = preg_replace('/{([^}]*)HISTORY(.*)}/U','',$html);
   if ($PAGE_TITLE_link)
      $html = str_replace('{PAGE_TITLE}', '<a href="./?page='.urlencode($PAGE_TITLE).'">'.htmlentities($PAGE_TITLE,ENT_COMPAT,'UTF-8').'</a>', $html);
   else
      $html = str_replace('{PAGE_TITLE}', htmlentities($PAGE_TITLE,ENT_COMPAT,'UTF-8'), $html);
   if ($editable)
      if (is_writable($PAGES_DIR . $PAGE_TITLE . '.txt') || !file_exists($PAGES_DIR . $PAGE_TITLE . '.txt'))
         $EDIT = '<a href="./?page='.urlencode($PAGE_TITLE).'&amp;action=edit" accesskey="5" rel="nofollow">'.tr('Éditer').'</a>';
      else
         $EDIT = tr('Page verrouillée');
   else
      $EDIT = tr('Éditer');
   $html = preg_replace('/{([^}]*)EDIT(.*)}/U',"$1".$EDIT."$2",$html);
   
   if (!empty($toc))
      $html = preg_replace('/{([^}]*)TOC(.*)}/U',"$1".$toc."$2",$html);
   else
      $html = preg_replace('/{([^}]*)TOC(.*)}/U','',$html);

   //remplacements fixes
   $html = str_replace('{PAGE_TITLE_BRUT}', htmlentities($PAGE_TITLE,ENT_COMPAT,'UTF-8'), $html);
   $html = str_replace('{WIKI_TITLE}', $WIKI_TITLE, $html);
   $html = str_replace('{LAST_CHANGE}', tr('Dernière modification').' :', $html);
   $html = str_replace('{CONTENT}', $CONTENT, $html);
   $html = str_replace('{LANG}', $LANG, $html);
   $html = str_replace('{WIKI_VERSION}', $WIKI_VERSION, $html);
   $html = str_replace('{TIME}', $TIME, $html);
   $html = str_replace('{DATE}', $datetw, $html);
   $html = str_replace('{IP}', $_SERVER['REMOTE_ADDR'], $html);
   if (isset($_COOKIE['AutorisationWiKiss']) && $_COOKIE['AutorisationWiKiss'] != '')
      $html = str_replace('{COOKIE}', '-- <a href="./?erasecookie=1&amp;'.$_SERVER['QUERY_STRING'].'" rel="nofollow">'.tr('Eff. cookie').'</a>', $html);
   else
      $html = str_replace('{COOKIE}', '', $html);
   // Affichage de la page
   echo $html;
   
   /** performances caclculation
   $perf2 = microtime();
   $perf = $perf2 - $perf1;
   $f = fopen('perfs.log','a');
   if ($perf > 0)
      fputs($f, $perf."\n");
   fclose($f);
   */

?>
