<?php

// $Id$



include_once '../../mainfile.php';
error_reporting(E_ALL);
include_once XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar("dirname")."/include/vars.php";
include_once XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar("dirname")."/include/functions.php";
include_once XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar("dirname")."/include/functions.ini.php";

$myts =& MyTextSanitizer::getInstance();

// menumode cookie
if(isset($_REQUEST['menumode'])){
	$menumode = intval($_REQUEST['menumode']);
	forum_setcookie("M", $menumode, $forumCookie['expire']);
}else{
	$cookie_M = intval(forum_getcookie("M"));
	$menumode = ($cookie_M === null || !isset($valid_menumodes[$cookie_M]))?$xoopsModuleConfig['menu_mode']:$cookie_M;
}

$menumode_other = array();
$menu_url = htmlSpecialChars(preg_replace("/&menumode=[^&]/", "", $_SERVER[ 'REQUEST_URI' ]));
$menu_url .= (false === strpos($menu_url, "?"))?"?menumode=":"&amp;menumode=";
foreach($valid_menumodes as $key=>$val){
	if($key != $menumode) $menumode_other[]=array("title"=>$val, "link"=>$menu_url.$key);
}

forum_welcome();
?>