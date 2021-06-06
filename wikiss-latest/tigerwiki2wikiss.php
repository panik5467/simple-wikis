<!-- coding: utf-8
 JJL : http://wikiss.tuxfamily.org
 v3:
 - detection de l'encodage en entr�e
 - conversion pages et historique
 - conversion des titres
 -->

<html> 
<head> 
<title>Change Encoding</title> 
<meta http-equiv=content-type content="text/html; charset=UTF-8"> 
</head> 

<body> 
<h1>Convert <a href="http://www.chabel.org/archives/?page=TigerWiki">TigerWiki</a> pages to <a href="http://wikiss.tuxfamily.org/">WiKiss</a> 0.2 pages</h1>

This utility aims to convert :
<ul><li>TigerWiki pages and history to UTF-8
</li><li>Pages names to UTF-8
</li><li>Title to the new  syntax (more ! for smaller titles)
</li></ul>

<p>
Please first <b>BACKUP</b> your pages !<br/>
Save your directories <tt>pages</tt> and <tt>historique</tt> to a safe place.<br/>
When done, click on <i>Do it !</i>
</p><p>
The page display the detected original charset. If yours differs from UTF-8 or ISO-8859-*, you'll need to change the <tt>mb_detect_order</tt>
line at the beginning of the script for the detection to work. See <a href="http://www.php.net/manual/en/function.mb-detect-order.php">PHP documentation</a> for more details.<br/>
You will also need PHP5 for this to work.
</p><p>
<b>Note: </b> a <a href="http://us3.php.net/manual/fr/function.mb-detect-encoding.php#55228">bug in php</a> prevent conversion of string ending by a special char. For exemple if you have a page named <i>Accentué</i>, the page name will
be well converted, but the history directory will have a problem, the ending <i>é</i> will be lost. So you'll have to rename it from <i>Accentu</i> to <i>Accentué</i> but in utf-8.
</p>
<pre> 
<?php

if (!function_exists("mb_detect_encoding"))
   die ("module mbstring not installed");

include("_config.php");

$title_replacement = "@@@";

// if you use another charset, you will have to change that
mb_detect_order(array("UTF-8","ISO-8859-1"));

/** Arg, workaround for this shitty html_entity_decode that does not support UTF-8 before PHP5
 *  from comments : http://fr3.php.net/manual/fr/function.html-entity-decode.php#76408
 */
function code2utf($number)
{
   if ($number < 0)
      return FALSE;

   if ($number < 128)
      return chr($number);

   // Removing / Replacing Windows Illegals Characters
   if ($number < 160)
   {
      if ($number==128) $number=8364;
      elseif ($number==129) $number=160; // (Rayo:) #129 using no relevant sign, thus, mapped to the saved-space #160
      elseif ($number==130) $number=8218;
      elseif ($number==131) $number=402;
      elseif ($number==132) $number=8222;
      elseif ($number==133) $number=8230;
      elseif ($number==134) $number=8224;
      elseif ($number==135) $number=8225;
      elseif ($number==136) $number=710;
      elseif ($number==137) $number=8240;
      elseif ($number==138) $number=352;
      elseif ($number==139) $number=8249;
      elseif ($number==140) $number=338;
      elseif ($number==141) $number=160; // (Rayo:) #129 using no relevant sign, thus, mapped to the saved-space #160
      elseif ($number==142) $number=381;
      elseif ($number==143) $number=160; // (Rayo:) #129 using no relevant sign, thus, mapped to the saved-space #160
      elseif ($number==144) $number=160; // (Rayo:) #129 using no relevant sign, thus, mapped to the saved-space #160
      elseif ($number==145) $number=8216;
      elseif ($number==146) $number=8217;
      elseif ($number==147) $number=8220;
      elseif ($number==148) $number=8221;
      elseif ($number==149) $number=8226;
      elseif ($number==150) $number=8211;
      elseif ($number==151) $number=8212;
      elseif ($number==152) $number=732;
      elseif ($number==153) $number=8482;
      elseif ($number==154) $number=353;
      elseif ($number==155) $number=8250;
      elseif ($number==156) $number=339;
      elseif ($number==157) $number=160; // (Rayo:) #129 using no relevant sign, thus, mapped to the saved-space #160
      elseif ($number==158) $number=382;
      elseif ($number==159) $number=376;
   } //if
   if ($number < 2048)
      return chr(($number >> 6) + 192) . chr(($number & 63) + 128);
   if ($number < 65536)
      return chr(($number >> 12) + 224) . chr((($number >> 6) & 63) + 128) . chr(($number & 63) + 128);
   if ($number < 2097152)
      return chr(($number >> 18) + 240) . chr((($number >> 12) & 63) + 128) . chr((($number >> 6) & 63) + 128) . chr(($number & 63) + 128);
  return FALSE;
} //code2utf()

function html_entity_decode_utf8($string)
{
   static $trans_tbl;

   // replace numeric entities
   $string = preg_replace('~&#x([0-9a-f]+);~ei', 'code2utf(hexdec("\\1"))', $string);
   $string = preg_replace('~&#([0-9]+);~e', 'code2utf(\\1)', $string);

   // replace literal entities
   if (!isset($trans_tbl))
   {
      $trans_tbl = array();
      foreach (get_html_translation_table(HTML_ENTITIES) as $val=>$key)
         $trans_tbl[$key] = utf8_encode($val);
      /* Html special char table miss a lot of characters
       * here's a more complete list
       * http://fr3.php.net/manual/fr/function.get-html-translation-table.php#73410
       */
      $more = array(
         '&apos;'=>'&#39;', '&minus;'=>'&#45;', '&circ;'=>'&#94;', '&tilde;'=>'&#126;', '&Scaron;'=>'&#138;', '&lsaquo;'=>'&#139;', '&OElig;'=>'&#140;',
         '&lsquo;'=>'&#145;', '&rsquo;'=>'&#146;', '&ldquo;'=>'&#147;', '&rdquo;'=>'&#148;', '&bull;'=>'&#149;', '&ndash;'=>'&#150;', '&mdash;'=>'&#151;',
         '&tilde;'=>'&#152;', '&trade;'=>'&#153;', '&scaron;'=>'&#154;', '&rsaquo;'=>'&#155;', '&oelig;'=>'&#156;', '&Yuml;'=>'&#159;', '&yuml;'=>'&#255;',
         '&OElig;'=>'&#338;', '&oelig;'=>'&#339;', '&Scaron;'=>'&#352;', '&scaron;'=>'&#353;', '&Yuml;'=>'&#376;', '&fnof;'=>'&#402;', '&circ;'=>'&#710;',
         '&tilde;'=>'&#732;', '&Alpha;'=>'&#913;', '&Beta;'=>'&#914;', '&Gamma;'=>'&#915;', '&Delta;'=>'&#916;', '&Epsilon;'=>'&#917;', '&Zeta;'=>'&#918;',
         '&Eta;'=>'&#919;', '&Theta;'=>'&#920;', '&Iota;'=>'&#921;', '&Kappa;'=>'&#922;', '&Lambda;'=>'&#923;', '&Mu;'=>'&#924;', '&Nu;'=>'&#925;',
         '&Xi;'=>'&#926;', '&Omicron;'=>'&#927;', '&Pi;'=>'&#928;', '&Rho;'=>'&#929;', '&Sigma;'=>'&#931;', '&Tau;'=>'&#932;', '&Upsilon;'=>'&#933;',
         '&Phi;'=>'&#934;', '&Chi;'=>'&#935;', '&Psi;'=>'&#936;', '&Omega;'=>'&#937;', '&alpha;'=>'&#945;', '&beta;'=>'&#946;', '&gamma;'=>'&#947;',
         '&delta;'=>'&#948;', '&epsilon;'=>'&#949;', '&zeta;'=>'&#950;', '&eta;'=>'&#951;', '&theta;'=>'&#952;', '&iota;'=>'&#953;', '&kappa;'=>'&#954;',
         '&lambda;'=>'&#955;', '&mu;'=>'&#956;', '&nu;'=>'&#957;', '&xi;'=>'&#958;', '&omicron;'=>'&#959;', '&pi;'=>'&#960;', '&rho;'=>'&#961;',
         '&sigmaf;'=>'&#962;', '&sigma;'=>'&#963;', '&tau;'=>'&#964;', '&upsilon;'=>'&#965;', '&phi;'=>'&#966;', '&chi;'=>'&#967;', '&psi;'=>'&#968;',
         '&omega;'=>'&#969;', '&thetasym;'=>'&#977;', '&upsih;'=>'&#978;', '&piv;'=>'&#982;', '&ensp;'=>'&#8194;', '&emsp;'=>'&#8195;', '&thinsp;'=>'&#8201;',
         '&zwnj;'=>'&#8204;', '&zwj;'=>'&#8205;', '&lrm;'=>'&#8206;', '&rlm;'=>'&#8207;', '&ndash;'=>'&#8211;', '&mdash;'=>'&#8212;', '&lsquo;'=>'&#8216;',
         '&rsquo;'=>'&#8217;', '&sbquo;'=>'&#8218;', '&ldquo;'=>'&#8220;', '&rdquo;'=>'&#8221;', '&bdquo;'=>'&#8222;', '&dagger;'=>'&#8224;', '&Dagger;'=>'&#8225;',
         '&bull;'=>'&#8226;', '&hellip;'=>'&#8230;', '&permil;'=>'&#8240;', '&prime;'=>'&#8242;', '&Prime;'=>'&#8243;', '&lsaquo;'=>'&#8249;',
         '&rsaquo;'=>'&#8250;', '&oline;'=>'&#8254;', '&frasl;'=>'&#8260;', '&euro;'=>'&#8364;', '&image;'=>'&#8465;', '&weierp;'=>'&#8472;', '&real;'=>'&#8476;',
         '&trade;'=>'&#8482;', '&alefsym;'=>'&#8501;', '&larr;'=>'&#8592;', '&uarr;'=>'&#8593;', '&rarr;'=>'&#8594;', '&darr;'=>'&#8595;', '&harr;'=>'&#8596;',
         '&crarr;'=>'&#8629;', '&lArr;'=>'&#8656;', '&uArr;'=>'&#8657;', '&rArr;'=>'&#8658;', '&dArr;'=>'&#8659;', '&hArr;'=>'&#8660;', '&forall;'=>'&#8704;',
         '&part;'=>'&#8706;', '&exist;'=>'&#8707;', '&empty;'=>'&#8709;', '&nabla;'=>'&#8711;', '&isin;'=>'&#8712;', '&notin;'=>'&#8713;', '&ni;'=>'&#8715;',
         '&prod;'=>'&#8719;', '&sum;'=>'&#8721;', '&minus;'=>'&#8722;', '&lowast;'=>'&#8727;', '&radic;'=>'&#8730;', '&prop;'=>'&#8733;', '&infin;'=>'&#8734;',
         '&ang;'=>'&#8736;', '&and;'=>'&#8743;', '&or;'=>'&#8744;', '&cap;'=>'&#8745;', '&cup;'=>'&#8746;', '&int;'=>'&#8747;', '&there4;'=>'&#8756;',
         '&sim;'=>'&#8764;', '&cong;'=>'&#8773;', '&asymp;'=>'&#8776;', '&ne;'=>'&#8800;', '&equiv;'=>'&#8801;', '&le;'=>'&#8804;', '&ge;'=>'&#8805;',
         '&sub;'=>'&#8834;', '&sup;'=>'&#8835;', '&nsub;'=>'&#8836;', '&sube;'=>'&#8838;', '&supe;'=>'&#8839;', '&oplus;'=>'&#8853;', '&otimes;'=>'&#8855;',
         '&perp;'=>'&#8869;', '&sdot;'=>'&#8901;', '&lceil;'=>'&#8968;', '&rceil;'=>'&#8969;', '&lfloor;'=>'&#8970;', '&rfloor;'=>'&#8971;', '&lang;'=>'&#9001;',
         '&rang;'=>'&#9002;', '&loz;'=>'&#9674;', '&spades;'=>'&#9824;', '&clubs;'=>'&#9827;', '&hearts;'=>'&#9829;', '&diams;'=>'&#9830;'
         );
      foreach ($more as $key=>$val)
         $trans_tbl[$key] = preg_replace('~&#([0-9]+);~e', 'code2utf(\\1)', $val);

//~ print_r ($trans_tbl);
   }
   return strtr($string, $trans_tbl);
}

// convert utf-8 to html entities
function utf2html ($content,$dryrun=FALSE)
{
   if (!$dryrun)
   {
      $decoded_utf = html_entity_decode($content,ENT_COMPAT,"UTF-8");
      // if you have PHP4, you should try to use this instead :
      // $decoded_utf = html_entity_decode_utf8($content);
      $encoded_utf = htmlentities($decoded_utf,ENT_COMPAT,"UTF-8");
      return $encoded_utf;
   }
   else
      return "";
}//utf2html

// convert whatever to utf-8
function ukn2utf ($content,$dryrun=FALSE)
{
   $current = mb_detect_encoding($content);
   print $current;
   if (!$dryrun)
      return mb_convert_encoding($content,"UTF-8",$current);
   else
      return "";
}//ukn2utf

// read, convert titles and write a file
function convertTitles ($filename,$dryrun=FALSE)
{
   global $title_replacement;
   $strin = file_get_contents($filename);
   if (preg_match("/^$title_replacement/",$strin) != 0)
   {
      print ("error, $title_replacement is present in the page, change the title_replacement variable in this script");
      return;
   }
   $cpt=0;
   $strin = preg_replace("/^!!!/Um",$title_replacement,$strin);
   $strin = preg_replace("/^!([^!])/Um","!!!$1",$strin,-1,$c);
   $cpt += $c;
   $strin = preg_replace("/^$title_replacement/Um","!",$strin,-1,$c);
   $cpt += $c;
   print (" / $cpt title(s)");
   if (!$dryrun)
   {
      // write file
      $f = fopen($filename,"w");
      if ($f)
      {
         fwrite($f,$strin);
         fclose($f);
      }
   }
}//convertTitles

// read, convert and write a file
function convertFile ($filename,$dryrun=FALSE)
{
   print ("content: ");
   $strin = file_get_contents($filename);
   // convert content
   $utf = ukn2utf($strin,$dryrun);
   //~ $strout = utf2html($utf,$dryrun);
   if (!$dryrun)
   {
      // write file
      $f = fopen($filename,"w");
      if ($f)
      {
         //~ fwrite($f,$strout);
         fwrite($f,$utf);
         fclose($f);
      }
   }
   // convert filename
   print (" name: (");
   $utfn = ukn2utf($filename);
   print (") &rarr; ".$utfn);
   if (!$dryrun)
   {
      rename($filename,$utfn);
      $filename = $utfn;
   }
   convertTitles($filename,$dryrun);
}//convertFile

// convert every .txt and .bak files in the dirname
function convertDir($dirname,$dryrun=FALSE)
{
   // convert dirname
   print "<li>$dirname";
   print (" (");
   $utfn = ukn2utf($dirname);
   print (") &rarr; $utfn");
   if (!$dryrun)
   {
      rename($dirname,$utfn);
      $dirname = $utfn;
   }
   if ($opening_dir = @opendir($dirname))
   {
      print "<ul>";
      while (false !== ($filename = @readdir($opening_dir))) 
      {
         $ext = strtolower(strrchr($filename,'.'));
         if ($ext == '.txt' or $ext == '.bak')
         {
            print ("<li><a href=$dirname/$filename>".$filename."</a>...");
            convertFile($dirname."/".$filename,$dryrun);
            if (!$dryrun) print " : done";
            print "</li>";
         }
      }
      print "</ul></li>";
   }
}//convertDir

/** MAIN ***********************************************/

if (isset($_GET['do']))
   $dryrun = FALSE;
else
{
   $dryrun = TRUE;
   print "<a href=\"".$_SERVER['SCRIPT_NAME']."?do=1\">Do it !</a>\n<hr/>";
}
   
   
print "<ul>";
// PAGES
convertDir(trim($PAGES_DIR,"/"),$dryrun);
// HISTORY
$dirname = trim($BACKUP_DIR,"/");
if ($opening_dir = @opendir($dirname))
{
   while (false !== ($filename = @readdir($opening_dir)))
   {
      if (is_dir($dirname ."/". $filename) and $filename != "." and $filename != "..")
         convertDir($dirname ."/". $filename,$dryrun);
   }
}
print "</ul>";

?>

</pre> 
<hr/> 
<div align=right style={font-size:small;}><a href=http://kubuntu.free.fr/blog/>JJL Creation</a></div> 
</body> 
</html>
