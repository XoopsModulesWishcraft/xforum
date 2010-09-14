<?php
// $Id: xoopsformloader.php,v 1.8.22.1.2.4 2005/07/14 16:13:30 wishcraft Exp $
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}

$ret= explode(" ",XOOPS_VERSION);
$ver= explode(".",$ret[1]);

if ($ret[0]>=2&&$ret[1]>=3){
	include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";
} else {
	if(!@include_once XOOPS_ROOT_PATH."/Frameworks/xoops22/class/xoopsformloader.php"){
		include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";
	}
}
?>