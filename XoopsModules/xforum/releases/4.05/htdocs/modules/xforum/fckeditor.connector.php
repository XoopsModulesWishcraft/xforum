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

define("XOOPS_FCK_FOLDER", $xoopsModule->getVar("dirname"));
include XOOPS_ROOT_PATH."/class/xoopseditor/FCKeditor/editor/filemanager/browser/default/connectors/php/connector.php";
?>