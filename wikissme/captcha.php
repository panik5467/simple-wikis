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
 *  @package WiKissMe
 *  @version $Id: captcha.php,v 1.1 2009/07/04 15:33:16 neven Exp $
 *  @author Neven Boyanov
 *
 */

require_once "classes/CaptchaSecurityImages.php";

session_start();

$key = $_GET['key'];

$width = (isset($_GET['width']) ? $_GET['width'] : '124');
$height = (isset($_GET['height']) ? $_GET['height'] : '42');
$characters = (isset($_GET['characters']) && $_GET['characters'] > 1 ? $_GET['characters'] : '6');

$captcha = new CaptchaSecurityImages($width,$height,$characters);

$_SESSION['CaptchaSecurityImages_key'] = $key;

?>
