<?php
// $Id: index.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $
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

include "header.php";

	global $xoopsModuleConfig;
	
	if ($xoopsModuleConfig['htaccess'])
	{
		if (strpos(' '.$_SERVER['REQUEST_URI'],"modules/")){
			header( "HTTP/1.1 301 Moved Permanently" ); 
			header( "Location: ".XOOPS_URL."/forums/".str_replace(array("/modules/xforum/","index.php"),"",$_SERVER['REQUEST_URI']));
		}
	}
/* deal with marks */
if (isset($_GET['mark_read'])){
    if(1 == intval($_GET['mark_read'])){ // marked as read
	    $markvalue = 1;
	    $markresult = _MD_MARK_READ;
    }else{ // marked as unread
	    $markvalue = 0;
	    $markresult = _MD_MARK_UNREAD;
    }
	forum_setRead_forum($markvalue);
    $url=XOOPS_URL . '/modules/' . $xoopsModule->getVar("dirname") . '/index.php';
    redirect_header($url, 2, _MD_ALL_FORUM_MARKED.' '.$markresult);
}

$viewcat = @intval($_GET['cat']);
$category_handler =& xoops_getmodulehandler('category', 'xforum');

$categories = array();
if (!$viewcat) {
	$categories = $category_handler->getAllCats('access', true);
    $forum_index_title = "";
	$xoops_pagetitle = $xoopsModule->getVar('name');
}else {
    $category_obj =& $category_handler->get($viewcat);
	if($category_handler->getPermission($category_obj)) {
		$categories[$viewcat] =& $category_obj;
	}
    $forum_index_title = sprintf(_MD_FORUMINDEX, htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES));
	$xoops_pagetitle = $category_obj->getVar('cat_title') . " [" .$xoopsModule->getVar('name')."]";
}
if(count($categories) == 0){
    redirect_header(XOOPS_URL, 2, _MD_NORIGHTTOACCESS);
    exit();
}

/* rss feed */
if(!empty($xoopsModuleConfig['rss_enable'])){
	$xoops_module_header .= '
	<link rel="alternate" type="application/rss+xml" title="'.$xoopsModule->getVar('name').'" href="'.XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/rss.php" />
	';
}

$xoopsOption['template_main']= 'xforum_index.html';
$xoopsOption['xoops_pagetitle']= $xoops_pagetitle;
$xoopsOption['xoops_module_header']= $xoops_module_header;
include XOOPS_ROOT_PATH."/header.php";

$xoopsTpl->assign('xoops_pagetitle', $xoops_pagetitle);
$xoopsTpl->assign('xoops_module_header', $xoops_module_header);
$xoopsTpl->assign('forum_index_title', $forum_index_title);
if ($xoopsModuleConfig['wol_enabled']){
	$online_handler =& xoops_getmodulehandler('online', 'xforum');
	$online_handler->init();
    $xoopsTpl->assign('online', $online_handler->show_online());
}

/* display forum stats */
$xoopsTpl->assign(array(
	"lang_welcomemsg" => sprintf(_MD_WELCOME, htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES)), 
	"total_topics" => get_total_topics(), 
	"total_posts" => get_total_posts(), 
	"lang_lastvisit" => sprintf(_MD_LASTVISIT,formatTimestamp($last_visit)), 
	"lang_currenttime" => sprintf(_MD_TIMENOW,formatTimestamp(time(),"m"))));

$forum_handler =& xoops_getmodulehandler('forum', 'xforum');
$xforums_obj = $forum_handler->getForumsByCategory(array_keys($categories), "access");
$xforums_array = $forum_handler->display($xforums_obj);
unset($xforums_obj);

if(count($xforums_array)>0){
    foreach ($xforums_array[0] as $parent => $xforum) {
        if (isset($xforums_array[$xforum['forum_id']])) {
            $xforum['subforum'] =& $xforums_array[$xforum['forum_id']];
        }
        $xforumsByCat[$xforum['forum_cid']][] = $xforum;
    }
}

$category_array = array();
$cat_order = array();
$toggles = forum_getcookie('G', true);
?><script src="<?php echo XOOPS_URL; ?>/modules/xforum/include/js/xtorrent_toggle.js" language="javascript"></script><?php
foreach(array_keys($categories) as $id){
    $xforums = array();

    $onecat =& $categories[$id];
    
    $catid="cat_".$onecat->getVar('cat_id');
	$catshow = (count($toggles)>0)?((in_array($catid, $toggles)) ? true : false):false;
	$display = ($catshow) ? 'none;'  :  'block;';
	$display_icon  = ($catshow) ? 'images/plus.png' : 'images/minus.png' ;

    if (isset($xforumsByCat[$onecat->getVar('cat_id')])) {
        $xforums =& $xforumsByCat[$onecat->getVar('cat_id')];
    }

	$cat_description = $onecat->getVar('cat_description');
	$cat_description = $myts->undoHtmlSpecialChars($cat_description);
	$cat_sponsor = array();
    @list($url, $title) = array_map("trim",preg_split("/ /", $onecat->getVar('cat_url'), 2));
    if(empty($title)) $title = $url;
    $title = $myts->htmlSpecialChars($title);
	if(!empty($url)) $cat_sponsor = array("title"=>$title, "link"=>formatURL($url));
	if($onecat->getVar('cat_image') &&	$onecat->getVar('cat_image') != "blank.gif"){
		$cat_image = XOOPS_URL."/modules/" . $xoopsModule->getVar("dirname") . "/images/category/" . $onecat->getVar('cat_image');
	}else{
		$cat_image = "";
	}
    $category_array[] = array(
    	'cat_order' => $onecat->getVar('cat_order'),
    	'cat_id' => $onecat->getVar('cat_id'),
	    'cat_title' => $onecat->getVar('cat_title'),
	    'cat_image' => $cat_image,
	    'cat_sponsor' => $cat_sponsor,
	    'cat_description' => $myts->displayTarea($cat_description, 1),
        'cat_display' => $display,
	    'cat_display_icon' => $display_icon,
	    'forums' => $xforums
    	);
    $cat_order[] = $onecat->getVar('cat_order');
}
unset($categories);

$xoopsTpl->assign_by_ref("categories", $category_array);
$xoopsTpl->assign("subforum_display", $xoopsModuleConfig['subforum_display']);
$xoopsTpl->assign('mark_read', "index.php?mark_read=1");
$xoopsTpl->assign('mark_unread', "index.php?mark_read=2");

$xoopsTpl->assign('all_link', "viewall.php");
$xoopsTpl->assign('post_link', "viewpost.php");
$xoopsTpl->assign('newpost_link', "viewpost.php?type=new");
$xoopsTpl->assign('digest_link', "viewall.php?type=digest");
$xoopsTpl->assign('unreplied_link', "viewall.php?type=unreplied");
$xoopsTpl->assign('unread_link', "viewall.php?type=unread");
$xoopsTpl->assign('down',forum_displayImage($xforumImage['doubledown']));
$xoopsTpl->assign('menumode',$menumode);
$xoopsTpl->assign('menumode_other',$menumode_other);

$isadmin = forum_isAdmin();
$xoopsTpl->assign('viewer_level', ($isadmin)?2:(is_object($xoopsUser)?1:0) );
$mode = (!empty($_GET['mode'])) ? intval($_GET['mode']) : 0;
$xoopsTpl->assign('mode', $mode );

$xoopsTpl->assign('viewcat', $viewcat);
$xoopsTpl->assign('version', $xoopsModule->getVar("version"));

/* To be removed */
if ( $isadmin ) {
    $xoopsTpl->assign('forum_index_cpanel',array("link"=>"admin/index.php", "name"=>_MD_ADMINCP));
}

if ($xoopsModuleConfig['rss_enable'] == 1) {
    $xoopsTpl->assign("rss_enable",1);
    $xoopsTpl->assign("rss_button", forum_displayImage($xforumImage['rss'], 'RSS feed'));
}
$xoopsTpl->assign(array(
	"img_hotfolder" => forum_displayImage($xforumImage['newposts_forum']),
	"img_folder" => forum_displayImage($xforumImage['folder_forum']),
	"img_locked_nonewposts" => forum_displayImage($xforumImage['locked_forum']),
	"img_locked_newposts" => forum_displayImage($xforumImage['locked_forum_newposts']),
	'img_subforum' => forum_displayImage($xforumImage['subforum'])));
include_once XOOPS_ROOT_PATH.'/footer.php';
?>