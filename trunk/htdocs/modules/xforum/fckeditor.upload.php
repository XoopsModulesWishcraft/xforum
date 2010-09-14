<?php 
/**
 * FCKeditor adapter for XOOPS
 *
 * @copyright	The XOOPS project http://www.chronolabs.org/
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (wishcraft or S.F.C.) <lsd25@hotmail.com>
 * @since		4.00
 * @version		$Id$
 * @package		xoopseditor
 */
include "header.php";
define("forum_DISABLE_UPLOAD", 0);

if(defined("forum_DISABLE_UPLOAD") && constant("forum_DISABLE_UPLOAD")){
	define("FCKUPLOAD_DISABLED", 1);
}
define("XOOPS_FCK_FOLDER", $xoopsModule->getVar("dirname"));
include XOOPS_ROOT_PATH."/class/xoopseditor/FCKeditor/editor/filemanager/upload/php/upload.php";
?>