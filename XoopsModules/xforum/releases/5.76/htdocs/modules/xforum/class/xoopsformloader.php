<?php

// $Id$
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}

include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";

if ( $GLOBALS['xoopsModuleConfig']['tag'] )
	include_once XOOPS_ROOT_PATH . '/modules/tag/include/formtag.php';
	
if ($GLOBALS['xoopsModuleConfig']['multisite']) {
	include_once(XOOPS_ROOT_PATH . '/modules/multisite/class/formcheckboxdomains.php');
	include_once(XOOPS_ROOT_PATH . '/modules/multisite/class/formselectdomains.php');
}	
?>