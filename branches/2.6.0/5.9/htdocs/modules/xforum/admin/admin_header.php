<?php

// $Id: admin_header.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000 XOOPS.org                           //
// <http://www.chronolabs.org/>                             //
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License 2.0 as published by //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
// //
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
// //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
// //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.chronolabs.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
	
	require_once (dirname(dirname(dirname(dirname(__FILE__)))).'/include/cp_header.php');
	
	$GLOBALS['xoops'] = Xoops::getInstance();
	
	if (!defined('_CHARSET'))
		define ("_CHARSET","UTF-8");
	if (!defined('_CHARSET_ISO'))
		define ("_CHARSET_ISO","ISO-8859-1");
		
	$GLOBALS['myts'] = MyTextSanitizer::getInstance();
	
	$module_handler = $GLOBALS['xoops']->getHandler('module');
	$config_handler = $GLOBALS['xoops']->getHandler('config');
	$GLOBALS['xforumModule'] = $module_handler->getByDirname('xforum');
	$GLOBALS['xforumModuleConfig'] = $config_handler->getConfigList($GLOBALS['xforumModule']->getVar('mid')); 
		
	XoopsLoad::load('pagenav');	
	XoopsLoad::load('xoopslists');
	XoopsLoad::load('xoopsformloader');
	
	include_once $GLOBALS['xoops']->path('class'.DS.'xoopsmailer.php');
	include_once $GLOBALS['xoops']->path('class'.DS.'xoopstree.php');
	
	if ( file_exists($GLOBALS['xoops']->path('/Frameworks/moduleclasses/moduleadmin/moduleadmin.php'))){
        include_once $GLOBALS['xoops']->path('/Frameworks/moduleclasses/moduleadmin/moduleadmin.php');
    }else{
        echo $GLOBALS['xoops']->error("Error: You don't use the Frameworks \"admin module\". Please install this Frameworks");
    }
    
	$GLOBALS['xforumImageIcon'] = XOOPS_URL .'/'. $GLOBALS['xforumModule']->getInfo('icons16');
	$GLOBALS['xforumImageAdmin'] = XOOPS_URL .'/'. $GLOBALS['xforumModule']->getInfo('icons32');
	
	if ($GLOBALS['xoopsUser']) {
	    $moduleperm_handler = $GLOBALS['xoops']->getHandler('groupperm');
	    if (!$moduleperm_handler->checkRight('module_admin', $GLOBALS['xforumModule']->getVar( 'mid' ), $GLOBALS['xoopsUser']->getGroups())) {
	        $GLOBALS['xoops']->redirect(XOOPS_URL, 1, _NOPERM);
	        exit();
	    }
	} else {
	    $GLOBALS['xoops']->redirect(XOOPS_URL . "/user.php", 1, _NOPERM);
	    exit();
	}

	$GLOBALS['xoops']->loadLanguage('user');
	
	if (!isset($GLOBALS['xoops']->tpl) || !is_object($GLOBALS['xoops']->tpl)) {
		include_once(XOOPS_ROOT_PATH."/class/template.php");
		$GLOBALS['xoops']->tpl = new XoopsTpl();
	}
	
	$GLOBALS['xoops']->tpl->assign('pathImageIcon', $GLOBALS['xforumImageIcon']);
	$GLOBALS['xoops']->tpl->assign('pathImageAdmin', $GLOBALS['xforumImageAdmin']);

	include_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/vars.php";
	include_once XOOPS_ROOT_PATH."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/admin.functions.php";
	include_once XOOPS_ROOT_PATH."/Frameworks/art/functions.php";
	include_once XOOPS_ROOT_PATH."/Frameworks/art/functions.admin.php";

	$GLOBALS['xoops']->loadLanguage('main', 'xforum');

	$GLOBALS['xforumModule'] = $module_handler->getByDirname('xforum');	
?>