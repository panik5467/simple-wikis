<!-- coding: utf-8 -->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>WiKiss strings</title>
</head>

<body>

<pre>
<?php

$plugins_folder = "../plugins";
$lang_dir = './';

/** find current supported langages */
$locales = array();
if (is_dir($lang_dir) && ($dir = opendir($lang_dir)))
{
   while (($file = readdir($dir)) !== false)
   {
      if (preg_match("/^([a-z]{2})\.php$/", $file, $matches)>0)
      {
         array_push($locales,$matches[1]);
      }
   }
   // print_r($locales);
}

/** find strings to be translated and create locale file structure */
$file_content = file_get_contents("../index.php");

if (is_dir($plugins_folder) && ($dir = opendir($plugins_folder)))
{
   while (($file = readdir($dir)) !== false)
   {
      if (preg_match("/^wkp_(.+)\.php$/", $file, $matches)>0)
      {
         $file_content .= file_get_contents($plugins_folder . '/' .$file);
      }
   }
}

$matches = array();
$n = preg_match_all("/[^a-zA-Z]tr\(['\"](.+?)['\"]\)/",$file_content,$matches);
// print_r(array_unique($matches[1]));
$french_strings = array_unique($matches[1]);
//sort($strings);

/** display strings to be translated */
if (isset($_GET['locale']))
{
   include_once($lang_dir.'/'.$_GET['locale'].'.php');
   echo '/* locale: '.$_GET['locale'].' */<br/>';
}

$maxlen=0;
foreach ($french_strings as $str)
   if (strlen($str) > $maxlen)
      $maxlen = strlen($str);

echo '$strings = array('."\n";
foreach ($french_strings as $str)
{
   if (isset($strings[$str]))
      printf("   %-40s => '%s',\n","'".htmlspecialchars($str)."'",htmlspecialchars($strings[$str]));
   else
      printf("   %-40s => '',\n","'".htmlspecialchars($str)."'");
}
print ");";


?>
</pre>
<hr/>
<form method="get" action="find_strings.php">
<select name="locale">

<?php
foreach ($locales as $loc)
   echo "<option value='$loc'>$loc</option>";
?>

</select>
<input type="submit" value="go" />
</form>

</body>
</html>