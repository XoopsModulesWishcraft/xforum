<?php
// $Id: header.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License 2.0 as published by //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
//  Author: wishcraft (S.F.C., sales@chronolabs.org.au)                      //
//  URL: http://www.chronolabs.org.au/forums/X-Forum/0,17,0,0,100,0,DESC,0   //
//  Project: X-Forum 4                                                       //
//  ------------------------------------------------------------------------ //


include_once '../../mainfile.php';
error_reporting(E_ALL);
include_once XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar("dirname")."/include/vars.php";
include_once XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar("dirname")."/include/functions.php";
include_once XOOPS_ROOT_PATH."/Frameworks/art/functions.php";

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

$forum_module_header = '';
$forum_module_header .= '<link rel="alternate" type="application/rss+xml" title="'.$xoopsModule->getVar('name').'" href="'.XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/rss.php" />';
if(!empty($xoopsModuleConfig['pngforie_enabled'])){
	$forum_module_header .= '<style type="text/css">img {behavior:url("'.XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/include/pngbehavior.htc");}</style>';
}
$forum_module_header .= '
	<link rel="stylesheet" type="text/css" href="'.XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/templates/xforum.css" />
	<script type="text/javascript">var toggle_cookie="'.$forumCookie['prefix'].'G'.'";</script>
	<script src="'.XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/include/js/xforum_toggle.js" type="text/javascript"></script>
	';
if($menumode==2){
	$forum_module_header .= '
	<link rel="stylesheet" type="text/css" href="'.XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/templates/forum_menu_hover.css" />
	<style type="text/css">body {behavior:url("'.XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/include/xforum.htc");}</style>
	';
}
if($menumode==1){
	$forum_module_header .= '
	<link rel="stylesheet" type="text/css" href="'.XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/templates/forum_menu_click.css" />
	<script src="'.XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/include/js/forum_menu_click.js" type="text/javascript"></script>
	';
}
$xoops_module_header = $forum_module_header; // for cache hack
//$xoopsOption['xoops_module_header'] = $xoops_module_header;
/*
if(!empty($xoopsModuleConfig['pngforie_enabled'])){
	$xTheme->addCSS(null,null,'img {behavior:url("include/pngbehavior.htc");}');
}
$xTheme->addJS(XOOPS_URL."/modules/".$xoopsModule->getVar("dirname")."/include/js/xforum_toggle.js");
$xTheme->addJS(null, null, 'var toggle_cookie="'.$forumCookie['prefix'].'G'.'";');
if($menumode==2){
	$xTheme->addCSS(XOOPS_URL."/modules/".$xoopsModule->getVar("dirname")."/templates/forum_menu_hover.css");
	$xTheme->addCSS(null,null,'body {behavior:url("include/xforum.htc");}');
}
if($menumode==1){
	$xTheme->addCSS(XOOPS_URL."/modules/".$xoopsModule->getVar("dirname")."/templates/forum_menu_click.css");
	$xTheme->addJS(XOOPS_URL."/modules/".$xoopsModule->getVar("dirname")."/include/js/forum_menu_click.js");
}
$xoops_module_header = '<link rel="stylesheet" type="text/css" media="screen" href="'.XOOPS_URL."/modules/".$xoopsModule->getVar("dirname").'/templates/xforum.css" />';
*/

forum_welcome();
?>