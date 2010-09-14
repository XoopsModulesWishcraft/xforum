<?php
// $Id: print.php,v 4.03 2008/06/05 15:35:59 wishcraft Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.chronolabs.org/>                             //
// ------------------------------------------------------------------------- //
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
//  Author: wishcraft (S.A.R., sales@chronolabs.org.au)                      //
//  URL: http://www.chronolabs.org.au/articles/Xoops-Modules---Hacks/X-Forum-4-03/ 
//  Project: X-Forum 4                                                       //
//  ------------------------------------------------------------------------ //

/* 
 * Print contents of a post or a topic
 * currently only available for post print
 *
 * TODO: topic print; print with page splitting
 * 
 */
 
error_reporting(0);
include 'header.php';
error_reporting(0);

if(empty($_POST["post_data"])){
	
$xforum = isset($_GET['forum']) ? intval($_GET['forum']) : 0;
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
$post_id = !empty($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if ( empty($post_id) && empty($topic_id) ){
	die(_MD_ERRORTOPIC);
}

if(!empty($post_id)){
	$post_handler =& xoops_getmodulehandler('post', 'xforum');
	$post = & $post_handler->get($post_id);
	if(!$approved = $post->getVar('approved')){
		die(_MD_NORIGHTTOVIEW);
	}
	$topic_id = $post->getVar("topic_id");
	$post_data = $post_handler->getPostForPrint($post);
	$isPost = 1;
	$post_data["url"] = XOOPS_URL."/xforum/viewtopic.php?topic_id=".$post->getVar("topic_id")."&amp;post_id=".$post_id;
}

$topic_handler =& xoops_getmodulehandler('topic', 'xforum');
$xforumtopic =& $topic_handler->get($topic_id);
$topic_id = $xforumtopic->getVar('topic_id');
$xforum = $xforumtopic->getVar('forum_id');
if(!$approved = $xforumtopic->getVar('approved'))    {
	die(_MD_NORIGHTTOVIEW);
}

$isadmin = xforum_isAdmin($viewtopic_forum);
if(!$isadmin && $xforumtopic->getVar('approved')<0 ){
    die(_MD_NORIGHTTOVIEW);
}

$xforum_handler =& xoops_getmodulehandler('forum', 'xforum');
$xforum = $xforumtopic->getVar('forum_id');
$viewtopic_forum =& $xforum_handler->get($xforum);
if (!$xforum_handler->getPermission($viewtopic_forum)){
    die(_MD_NORIGHTTOVIEW);
}

if (!$topic_handler->getPermission($viewtopic_forum, $xforumtopic->getVar('topic_status'), "view")){
	die(_MD_NORIGHTTOVIEW);
}

}else{
	$post_data = unserialize(base64_decode($_POST["post_data"]));
	$isPost = 1;
}

header('Content-Type: text/html; charset='._CHARSET); 

if(empty($isPost)){

	echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    echo "<html>\n<head>\n";
    echo "<title>" . htmlspecialchars($xoopsConfig['sitename']) . "</title>\n";
    echo "<meta http-equiv='Content-Type' content='text/html; charset=" . _CHARSET . "' />\n";
    echo "<meta name='AUTHOR' content='" . htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES) . "' />\n";
    echo "<meta name='COPYRIGHT' content='Copyright (c) ".date('Y')." by " . htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES) . "' />\n";
    echo "<meta name='DESCRIPTION' content='" . htmlspecialchars($xoopsConfig['slogan'], ENT_QUOTES) . "' />\n";
    echo "<meta name='GENERATOR' content='" . XOOPS_VERSION . "' />\n\n\n";
    echo "<body bgcolor='#ffffff' text='#000000' onload='window.print()'>
	 	  <div style='width: 750px; border: 1px solid #000; padding: 20px;'>
	 	  <div style='text-align: center; display: block; margin: 0 0 6px 0;'>
		  <img src='" . XOOPS_URL . "/modules/xforum/images/xoopsxf_slogo.png' border='0' alt='' />
		  <br />
		  <br />
		  ";

    $postsArray = $topic_handler->getAllPosts($xforumtopic);
    foreach ($postsArray as $post) {
		if(!$post->getVar('approved'))    continue;
		$post_data = $post_handler->getPostForPrint($post);
		echo "<h2 style='margin: 0;'>".$post_data['subject']."</h2>
 	          <div align='center'>" ._POSTEDBY. "&nbsp;".$post_data['author']."&nbsp;"._ON."&nbsp;".$post_data['date']."</div>
		      <div style='text-align: center; display: block; padding-bottom: 12px; margin: 0 0 6px 0; border-bottom: 2px solid #ccc;'></div>
		      <div style='text-align: left'>".$post_data['text']."</div>
		      <div style='padding-top: 12px; border-top: 2px solid #ccc;'></div><br />";
    }
	echo "<p>"._MD_COMEFROM . "&nbsp;".XOOPS_URL."/xforum/viewtopic.php?forum=".$xforum_id."&amp;topic_id=".$topic_id."</p>";
	echo "</div></div>";
	echo "</body></html>";
	
}else{

	echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    echo "<html>\n<head>\n";
    echo "<title>" . htmlspecialchars($xoopsConfig['sitename']) . "</title>\n";
    echo "<meta http-equiv='Content-Type' content='text/html; charset=" . _CHARSET . "' />\n";
    echo "<meta name='AUTHOR' content='" . htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES) . "' />\n";
    echo "<meta name='COPYRIGHT' content='Copyright (c) ".date('Y')." by " . htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES) . "' />\n";
    echo "<meta name='DESCRIPTION' content='" . htmlspecialchars($xoopsConfig['slogan'], ENT_QUOTES) . "' />\n";
    echo "<meta name='GENERATOR' content='" . XOOPS_VERSION . "' />\n\n\n";
    echo "</head>\n\n\n";
    echo "<body bgcolor='#ffffff' text='#000000' onload='window.print()'>
 		  <div style='width: 750px; border: 1px solid #000; padding: 20px;'>
 		  <div style='text-align: center; display: block; margin: 0 0 6px 0;'>
	      <img src='" . XOOPS_URL . "/modules/xforum/images/xoopsxf_slogo.png' border='0' alt='' />
	      <h2 style='margin: 0;'>".$post_data['subject']."</h2></div>
 	      <div align='center'>" ._POSTEDBY. "&nbsp;".$post_data['author']."&nbsp;"._ON."&nbsp;".$post_data['date']."</div>
		  <div style='text-align: center; display: block; padding-bottom: 12px; margin: 0 0 6px 0; border-bottom: 2px solid #ccc;'></div>
		   	<div style='text-align: left'>".$post_data['text']."</div>
			<div style='padding-top: 12px; border-top: 2px solid #ccc;'></div>
			<p>"._MD_COMEFROM . "&nbsp;".$post_data["url"]."</p>
		    </div>
            <br />";
	echo "<br /></body></html>";
}
?>