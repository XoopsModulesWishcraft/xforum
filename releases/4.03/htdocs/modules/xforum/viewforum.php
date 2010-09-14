<?php
// $Id: viewforum.php,v 4.03 2008/06/05 15:35:59 wishcraft Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.chronolabs.org/>                             //
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
//  Author: wishcraft (S.A.R., sales@chronolabs.org.au)                      //
//  URL: http://www.chronolabs.org.au/articles/Xoops-Modules---Hacks/X-Forum-4-03/ 
//  Project: X-Forum 4                                                       //
//  ------------------------------------------------------------------------ //

include "header.php";

	if ($xoopsModuleConfig['htaccess'])
	{

		$xforum_name = isset($_GET['forum_name'])?xoops_sef($_GET['forum_name'],'_'):''; // ?
		
		if (strlen($xforum_name)>0){
		
			$sql = "SELECT forum_id FROM ".$xoopsDB->prefix('xf_forums')." WHERE forum_name LIKE '".str_replace(array('\\_',"-"),"_",addslashes($xforum_name))."'";
			$ret = $xoopsDB->queryF($sql);
			$rt = $xoopsDB->fetchArray($ret);
			$xforum_id = $rt['forum_id'];	
		
			
			$xforum_id = intval($_GET['forum']) ? intval($_GET['forum']) : $xforum_id;
			$type = (!empty($_GET['type']) && in_array($_GET['type'], array("active", "pending", "deleted", "digest", "unreplied", "unread")))? $_GET['type'] : "";
			$mode = !empty($_GET['mode']) ? intval($_GET['mode']) : 0;
			$mode = (!empty($type) && in_array($type, array("active", "pending", "deleted")))?2:$mode;
			$since = isset($_GET['since']) ? intval($_GET['since']) : $xoopsModuleConfig["since_default"];
			$startdate = empty($since)?0:(time() - xforum_getSinceTime($since));
			$start = !empty($_GET['start']) ? intval($_GET['start']) : 0;
			$sortorder = (!isset($_GET['sortorder']) || $_GET['sortorder'] != "ASC") ? "DESC" : "ASC";
			$sortname = $_GET['sortname']!=0 ? $_GET['sortname'] : '';
			
			if ( empty($xforum_id) ) {
				redirect_header(XOOPS_URL."/index.php", 2, _MD_ERRORFORUM);
				exit();
			}
			
		} else {
		
			if ( empty($_GET['forum']) ) {
				redirect_header(XOOPS_URL."/index.php", 2, _MD_ERRORFORUM);
				exit();
			}
		
			$xforum_id = intval($_GET['forum']);
			if (!empty($_GET['type'])){
				$type = (!empty($_GET['type']) && in_array($_GET['type'], array("active", "pending", "deleted", "digest", "unreplied", "unread")))? $_GET['type'] : "";
			} else {
				$sql = "SELECT forum_name FROM ".$xoopsDB->prefix('xf_forums')." WHERE forum_id = '$xforum_id'";
				$ret = $xoopsDB->queryF($sql);
				$rt = $xoopsDB->fetchArray($ret);
				$xforum_name = $rt['forum_name'];	
			
				$xforum_id = intval($_GET['forum']) ? intval($_GET['forum']) : $xforum_id;
				$type = (!empty($_GET['type']) && in_array($_GET['type'], array("active", "pending", "deleted", "digest", "unreplied", "unread")))? $_GET['type'] : "0";
				$mode = !empty($_GET['mode']) ? intval($_GET['mode']) : 0;
				$mode = (!empty($type) && in_array($type, array("active", "pending", "deleted")))?2:$mode;
				$since = isset($_GET['since']) ? intval($_GET['since']) : $xoopsModuleConfig["since_default"];
				$start = !empty($_GET['start']) ? intval($_GET['start']) : 0;
				$sortorder = (!isset($_GET['sortorder']) || $_GET['sortorder'] != "ASC") ? "DESC" : "ASC";
				$sortname = $_GET['sortname'] ? $_GET['sortname'] : 0;
					
				header( "HTTP/1.1 301 Moved Permanently" ); 
				header( "Location: ".XOOPS_URL."/forums/".xoops_sef($xforum_name)."/0,$xforum_id,$type,$mode,$since,$start,$sortorder,$sortname");
				exit;
			}
		}
	} else {
		$xforum_id = intval($_GET['forum']) ? intval($_GET['forum']) : $xforum_id;
		$type = (!empty($_GET['type']) && in_array($_GET['type'], array("active", "pending", "deleted", "digest", "unreplied", "unread")))? $_GET['type'] : "";
		$mode = !empty($_GET['mode']) ? intval($_GET['mode']) : 0;
		$mode = (!empty($type) && in_array($type, array("active", "pending", "deleted")))?2:$mode;
		$since = isset($_GET['since']) ? intval($_GET['since']) : $xoopsModuleConfig["since_default"];
		$startdate = empty($since)?0:(time() - xforum_getSinceTime($since));
		$start = !empty($_GET['start']) ? intval($_GET['start']) : 0;
		$sortorder = (!isset($_GET['sortorder']) || $_GET['sortorder'] != "ASC") ? "DESC" : "ASC";
		$sortname = $_GET['sortname']!=0 ? $_GET['sortname'] : '';
	}
	

if (isset($_GET['mark_read'])){
    if(1 == intval($_GET['mark_read'])){ // marked as read
	    $markvalue = 1;
	    $markresult = _MD_MARK_READ;
    }else{ // marked as unread
	    $markvalue = 0;
	    $markresult = _MD_MARK_UNREAD;
    }
	xforum_setRead_topic($markvalue, $xforum_id);
	$url = "viewforum.php?start=".$_GET['start']."&amp;forum=".$xforum_id."&amp;sortname=".$_GET['sortname']."&amp;sortorder=".$_GET['sortorder']."&amp;since=".$_GET['since'];
    redirect_header($url,2, $markresult);
}

$xforum_id = intval($xforum_id);
$type = (!empty($_GET['type']) && in_array($_GET['type'], array("active", "pending", "deleted", "digest", "unreplied", "unread")))? $_GET['type'] : "";
$mode = !empty($_GET['mode']) ? intval($_GET['mode']) : 0;
$mode = (!empty($type) && in_array($type, array("active", "pending", "deleted")))?2:$mode;

$xforum_handler =& xoops_getmodulehandler('forum', 'xforum');
$xforum_obj =& $xforum_handler->get($xforum_id);
if (!$xforum_handler->getPermission($xforum_obj)){
    redirect_header(XOOPS_URL."/index.php", 2, _MD_NORIGHTTOACCESS);
    exit();
}
xforum_setRead("forum", $xforum_id, $xforum_obj->getVar("forum_last_post_id"));


$xoops_pagetitle = $xforum_obj->getVar('forum_name') . " [" .$xoopsModule->getVar('name')."]";
if(!empty($xoopsModuleConfig['rss_enable'])){
	$xoops_module_header .= '<link rel="alternate" type="application/xml+rss" title="'.$xoopsModule->getVar('name').'-'.$xforum_obj->getVar('forum_name').'" href="'.XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/rss.php?f='.$xforum_id.'" />';
}

$xoopsOption['template_main'] = 'xforum_viewforum.html';
$xoopsOption['xoops_pagetitle']= $xoops_pagetitle;
$xoopsOption['xoops_module_header']= $xoops_module_header;
include XOOPS_ROOT_PATH."/header.php";
$xoopsTpl->assign('xoops_module_header', $xoops_module_header);
$xoopsTpl->assign('xoops_pagetitle', $xoops_pagetitle);
$xoopsTpl->assign("forum_id", $xforum_obj->getVar('forum_id'));

$isadmin = xforum_isAdmin($xforum_obj);
$xoopsTpl->assign('viewer_level', ($isadmin)?2:(is_object($xoopsUser)?1:0) );
/* Only admin has access to admin mode */
if(!$isadmin){
	$type = (!empty($type) && in_array($type, array("active", "pending", "deleted")))?"":$type;
	$mode = 0;
}
$xoopsTpl->assign('mode', $mode);
$xoopsTpl->assign('type', $type);

if ($xoopsModuleConfig['wol_enabled']){
	$online_handler =& xoops_getmodulehandler('online', 'xforum');
	$online_handler->init($xforum_obj);
    $xoopsTpl->assign('online', $online_handler->show_online());
}

$getpermission =& xoops_getmodulehandler('permission', 'xforum');
$permission_set = $getpermission->getPermissions("forum", $xforum_obj->getVar('forum_id'));

if ($xforum_handler->getPermission($xforum_obj, "post")){
	$xoopsTpl->assign('forum_newtopic_img', "<a href=\"newtopic.php?forum=".$xforum_obj->getVar('forum_id')."\"><img src='".XOOPS_URL."/modules/xforum/images/imagesets/default/english/t_new-a.png'></a>");
	if ($xforum_handler->getPermission($xforum_obj, "addpoll") && $xforum_obj->getVar('allow_polls') == 1){
		$t_poll = xforum_displayImage($xforumImage['t_poll'],_MD_ADDPOLL);
		$xoopsTpl->assign('forum_addpoll', "<a href=\"newtopic.php?op=add&amp;forum=".$xforum_obj->getVar('forum_id')."\">".$t_poll."</a>&nbsp;");
 	}
} else {
    if ( !empty($GLOBALS["xoopsModuleConfig"]["show_reg"]) && !is_object($xoopsUser)) {
	    $redirect = preg_replace("|(.*)\/modules\/xforum\/(.*)|", "\\1/modules/xforum/newtopic.php?forum=".$xforum_obj->getVar('forum_id'), htmlspecialchars($xoopsRequestUri));
		$xoopsTpl->assign('forum_newtopic', '_inactive');
		$xoopsTpl->assign('forum_post_or_register', '<a href="'.XOOPS_URL.'/user.php?xoops_redirect='.$redirect.'">'._MD_REGTOPOST.'</a>');
		$xoopsTpl->assign('forum_addpoll', "");
	} else {
		$xoopsTpl->assign('forum_newtopic', '_inactive');
		$xoopsTpl->assign('forum_post_or_register', "");
		$xoopsTpl->assign('forum_addpoll', "");
	}
}

if($xforum_obj->getVar('parent_forum')){
	$parent_forum_obj =& $xforum_handler->get($xforum_obj->getVar('parent_forum'), array("forum_name"));
	$parentforum = array("id"=>$xforum_obj->getVar('parent_forum'), "name"=>$parent_forum_obj->getVar("forum_name"));
	unset($parent_forum_obj);
	$xoopsTpl->assign_by_ref("parentforum", $parentforum);
}else{
	$criteria =& new Criteria("parent_forum", $xforum_id);
	$criteria->setSort("forum_order");
	if($xforums_obj =& $xforum_handler->getAll($criteria)){
		$subforum_array = $xforum_handler->display($xforums_obj);
		$subforum = array_values($subforum_array[$xforum_id]);
		unset($xforums_obj, $subforum_array);
		$xoopsTpl->assign_by_ref("subforum", $subforum);
	}
}

$category_handler =& xoops_getmodulehandler("category");
$category_obj =& $category_handler->get($xforum_obj->getVar("cat_id"), array("cat_title"));
$xoopsTpl->assign('category', array("id" => $xforum_obj->getVar("cat_id"), "title" => $category_obj->getVar('cat_title')));

$xoopsTpl->assign('forum_index_title', sprintf(_MD_FORUMINDEX,htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES)));
$xoopsTpl->assign('folder_topic', xforum_displayImage($xforumImage['folder_topic']));
$xoopsTpl->assign('forum_name', $xforum_obj->getVar('forum_name'));
$xoopsTpl->assign('forum_moderators', $xforum_obj->disp_forumModerators());

$sel_sort_array = array("t.topic_title"=>_MD_TOPICTITLE, "u.uname"=>_MD_TOPICPOSTER, "t.topic_time"=>_MD_TOPICTIME, "t.topic_replies"=>_MD_NUMBERREPLIES, "t.topic_views"=>_MD_VIEWS, "p.post_time"=>_MD_LASTPOSTTIME);
if ( !isset($_GET['sortname']) || !in_array($_GET['sortname'], array_keys($sel_sort_array)) ) {
	$sortname = "p.post_time";
} else {
	$sortname = $_GET['sortname'];
}

$xforum_selection_sort = '<select name="sortname">';
foreach ( $sel_sort_array as $sort_k => $sort_v ) {
	$xforum_selection_sort .= '<option value="'.$sort_k.'"'.(($sortname == $sort_k) ? ' selected="selected"' : '').'>'.$sort_v.'</option>';
}
$xforum_selection_sort .= '</select>';

$xoopsTpl->assign_by_ref('forum_selection_sort', $xforum_selection_sort);

$sortorder = (!isset($_GET['sortorder']) || $_GET['sortorder'] != "ASC") ? "DESC" : "ASC";

$xforum_selection_order = '<select name="sortorder">';
$xforum_selection_order .= '<option value="ASC"'.(($sortorder == "ASC") ? ' selected="selected"' : '').'>'._MD_ASCENDING.'</option>';
$xforum_selection_order .= '<option value="DESC"'.(($sortorder == "DESC") ? ' selected="selected"' : '').'>'._MD_DESCENDING.'</option>';
$xforum_selection_order .= '</select>';

$xoopsTpl->assign_by_ref('forum_selection_order', $xforum_selection_order);

$since = isset($_GET['since']) ? intval($_GET['since']) : $xoopsModuleConfig["since_default"];
$xforum_selection_since = xforum_sinceSelectBox($since);

$xoopsTpl->assign_by_ref('forum_selection_since', $xforum_selection_since);
$xoopsTpl->assign('h_topic_link', "viewforum.php?forum=$xforum_id&amp;sortname=t.topic_title&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_title" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$xoopsTpl->assign('h_reply_link', "viewforum.php?forum=$xforum_id&amp;sortname=t.topic_replies&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_replies" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$xoopsTpl->assign('h_poster_link', "viewforum.php?forum=$xforum_id&amp;sortname=u.uname&amp;since=$since&amp;sortorder=". (($sortname == "u.uname" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$xoopsTpl->assign('h_views_link', "viewforum.php?forum=$xforum_id&amp;sortname=t.topic_views&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_views" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$xoopsTpl->assign('h_rating_link', "viewforum.php?forum=$xforum_id&amp;sortname=t.topic_ratings&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_ratings" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$xoopsTpl->assign('h_date_link', "viewforum.php?forum=$xforum_id&amp;sortname=p.post_time&amp;since=$since&amp;sortorder=". (($sortname == "p.post_time" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$xoopsTpl->assign('h_publish_link', "viewforum.php?forum=$xforum_id&amp;sortname=t.topic_time&amp;since=$since&amp;sortorder=". (($sortname == "t.topic_time" && $sortorder == "DESC") ? "ASC" : "DESC"))."&amp;type=$type";
$xoopsTpl->assign('forum_since', $since); // For $since in search.php

$startdate = empty($since)?0:(time() - xforum_getSinceTime($since));
$start = !empty($_GET['start']) ? intval($_GET['start']) : 0;

list($allTopics, $sticky) = $xforum_handler->getAllTopics($xforum_obj,$startdate,$start,$sortname,$sortorder,$type,$xoopsModuleConfig['post_excerpt']);
$xoopsTpl->assign_by_ref('topics', $allTopics);
//unset($allTopics);
$xoopsTpl->assign('sticky', $sticky);
$xoopsTpl->assign('rating_enable', $xoopsModuleConfig['rating_enabled']);
$xoopsTpl->assign('img_newposts', xforum_displayImage($xforumImage['newposts_topic']));
$xoopsTpl->assign('img_hotnewposts', xforum_displayImage($xforumImage['hot_newposts_topic']));
$xoopsTpl->assign('img_folder', xforum_displayImage($xforumImage['folder_topic']));
$xoopsTpl->assign('img_hotfolder', xforum_displayImage($xforumImage['hot_folder_topic']));
$xoopsTpl->assign('img_locked', xforum_displayImage($xforumImage['locked_topic']));

$xoopsTpl->assign('img_sticky', xforum_displayImage($xforumImage['folder_sticky'],_MD_TOPICSTICKY));
$xoopsTpl->assign('img_digest', xforum_displayImage($xforumImage['folder_digest'],_MD_TOPICDIGEST));
$xoopsTpl->assign('img_poll', xforum_displayImage($xforumImage['poll'],_MD_TOPICHASPOLL));

$mark_read_link = "viewforum.php?mark_read=1&amp;start=$start&amp;forum=".$xforum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=$type";
$mark_unread_link = "viewforum.php?mark_read=2&amp;start=$start&amp;forum=".$xforum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=$type";
$xoopsTpl->assign('mark_read', $mark_read_link);
$xoopsTpl->assign('mark_unread', $mark_unread_link);

$xoopsTpl->assign('post_link', "viewpost.php?forum=".$xforum_obj->getVar('forum_id'));
$xoopsTpl->assign('newpost_link', "viewpost.php?type=new&amp;forum=".$xforum_obj->getVar('forum_id'));
$xoopsTpl->assign('all_link', "viewforum.php?start=$start&amp;forum=".$xforum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since");
$xoopsTpl->assign('digest_link', "viewforum.php?start=$start&amp;forum=".$xforum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=digest");
$xoopsTpl->assign('unreplied_link', "viewforum.php?start=$start&amp;forum=".$xforum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=unreplied");
$xoopsTpl->assign('unread_link', "viewforum.php?start=$start&amp;forum=".$xforum_obj->getVar('forum_id')."&amp;sortname=$sortname&amp;sortorder=$sortorder&amp;since=$since&amp;type=unread");
switch($type){
	case 'digest':
		$current_type = '['._MD_DIGEST.']';
		break;
	case 'unreplied':
		$current_type = '['._MD_UNREPLIED.']';
		break;
	case 'unread':
		$current_type = '['._MD_UNREAD.']';
		break;
	case 'active':
		$current_type = '['._MD_TYPE_ADMIN.']';
		break;
	case 'pending':
		$current_type = '['._MD_TYPE_PENDING.']';
		break;
	case 'deleted':
		$current_type = '['._MD_TYPE_DELETED.']';
		break;
	default:
		$current_type = '';
		break;
	}
$xoopsTpl->assign('forum_topictype', $current_type);

$all_topics = $xforum_handler->getTopicCount($xforum_obj,$startdate,$type);
if ( $all_topics > $xoopsModuleConfig['topics_per_page']) {
	include XOOPS_ROOT_PATH.'/class/pagenav.php';
	$nav = new XoopsPageNav($all_topics, $xoopsModuleConfig['topics_per_page'], $start, "start", 'forum='.$xforum_obj->getVar('forum_id').'&amp;sortname='.$sortname.'&amp;sortorder='.$sortorder.'&amp;since='.$since."&amp;type=$type&amp;mode=".$mode);
	$xoopsTpl->assign('forum_pagenav', $nav->renderNav(4));
} else {
	$xoopsTpl->assign('forum_pagenav', '');
}

if(!empty($xoopsModuleConfig['show_jump'])){
	$xoopsTpl->assign('forum_jumpbox', xforum_make_jumpbox($xforum_obj->getVar('forum_id')));
}
$xoopsTpl->assign('down',xforum_displayImage($xforumImage['doubledown']));
$xoopsTpl->assign('menumode',$menumode);
$xoopsTpl->assign('menumode_other',$menumode_other);

if($xoopsModuleConfig['show_permissiontable']){
	$permission_table = & $getpermission->permission_table($permission_set,$xforum_obj->getVar('forum_id'), false, $isadmin);
	$xoopsTpl->assign_by_ref('permission_table', $permission_table);
	unset($permission_table);
}

if ($xoopsModuleConfig['rss_enable'] == 1) {
	$xoopsTpl->assign("rss_button","<div align='right'><a href='".XOOPS_URL . "/modules/" . $xoopsModule->dirname() . "/rss.php?f=".$xforum_obj->getVar('forum_id')."' title='RSS feed' target='_blank'>".xforum_displayImage($xforumImage['rss'], 'RSS feed')."</a></div>");
}

include XOOPS_ROOT_PATH."/footer.php";
?>