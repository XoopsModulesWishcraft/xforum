<?php
// $Id: moderate.php,v 4.03 2008/06/05 16:23:25 wishcraft Exp $
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
include 'header.php';

$xforum_id = isset($_POST['forum']) ? intval($_POST['forum']) : 0;
$xforum_id = isset($_GET['forum']) ? intval($_GET['forum']) : $xforum_id;

$isadmin = xforum_isAdmin($xforum_id);
if(!$isadmin){
    redirect_header(XOOPS_URL."/index.php", 2, _MD_NORIGHTTOACCESS);
    exit();
}
$is_administrator = xforum_isAdmin();

$moderate_handler =& xoops_getmodulehandler('moderate', 'xforum');

if(!empty($_POST["submit"])&&!empty($_POST["expire"])){
	if( !empty($_POST["ip"]) && !preg_match("/^([0-9]{1,3}\.){0,3}[0-9]{1,3}$/", $_POST["ip"])) $_POST["ip"]="";
	if(
		(!empty($_POST["uid"]) && $moderate_handler->getLatest($_POST["uid"])>(time()+$_POST["expire"]*3600*24))
		||
		(!empty($_POST["ip"]) && $moderate_handler->getLatest($_POST["ip"], false)>(time()+$_POST["expire"]*3600*24))
		||
		(empty($_POST["uid"]) && empty($_POST["ip"]))
	){
	}else{
		$moderate_obj =& $moderate_handler->create();
		$moderate_obj->setVar("uid", @$_POST["uid"]);
		$moderate_obj->setVar("ip", @$_POST["ip"]);
		$moderate_obj->setVar("forum_id", $xforum_id);
		$moderate_obj->setVar("mod_start", time());
		$moderate_obj->setVar("mod_end", time()+$_POST["expire"]*3600*24);
		$moderate_obj->setVar("mod_desc", @$_POST["desc"]);
		if($res = $moderate_handler->insert($moderate_obj) && !empty($xforum_id) && !empty($_POST["uid"]) ){
			$uname = XoopsUser::getUnameFromID($_POST["uid"]);
			$post_handler =& xoops_getmodulehandler("post", "xforum");
			$xforumpost =& $post_handler->create();
		    $xforumpost->setVar("poster_ip", xforum_getIP());
		    $xforumpost->setVar("uid", empty($GLOBALS["xoopsUser"])?0:$GLOBALS["xoopsUser"]->getVar("uid"));
		    $xforumpost->setVar("forum_id", $xforum_id);
			$xforumpost->setVar("subject", sprintf(_MD_SUSPEND_SUBJECT, $uname, $_POST["expire"]));
			$xforumpost->setVar("post_text", sprintf(_MD_SUSPEND_TEXT, '<a href="' . XOOPS_URL . '/userinfo.php?uid='.$_POST["uid"].'">'.$uname.'</a>', $_POST["expire"], @$_POST["desc"], formatTimestamp(time()+$_POST["expire"]*3600*24) ));
		    $xforumpost->setVar("dohtml", 1);
		    $xforumpost->setVar("dosmiley", 1);
		    $xforumpost->setVar("doxcode", 1);
		    $xforumpost->setVar("post_time", time());
			$post_handler->insert($xforumpost);
			unset($xforumpost);
		}
		if($_POST["uid"]>0){
			$online_handler =& xoops_gethandler('online');
			$onlines =& $online_handler->getAll(new Criteria("online_uid", $_POST["uid"]));
			if (false != $onlines) {
    			$online_ip = $onlines[0]["online_ip"];
				$sql = sprintf('DELETE FROM %s WHERE sess_ip = %s', $xoopsDB->prefix('session'), $xoopsDB->quoteString($online_ip));
		        if ( !$result = $xoopsDB->queryF($sql) ) {
		        }
			}
		}
		if(!empty($_POST["ip"])){
			$sql = 'DELETE FROM '.$xoopsDB->prefix('session').' WHERE sess_ip LIKE '.$xoopsDB->quoteString('%'.$_POST["ip"]);
	        if ( !$result = $xoopsDB->queryF($sql) ) {
	        }
		}
		redirect_header("moderate.php?forum=$xforum_id", 2, _MD_DBUPDATED);
		exit();
	}
}elseif(!empty($_GET["del"])){
	$moderate_obj =& $moderate_handler->get($_GET["del"]);
	if($is_administrator || $moderate_obj->getVar("forum_id")==$xforum_id){
		$moderate_handler->delete($moderate_obj, true);
		redirect_header("moderate.php?forum=$xforum_id", 2, _MD_DBUPDATED);
		exit();
	}
}

$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$sortname = isset($_GET['sort']) ? $_GET['sort'] : "";

switch($sortname){
	case "uid":
		$sort = "uid ASC, ip";
		$order = "ASC";
		break;
	case "start":
		$sort = "mod_start";
		$order = "ASC";
		break;
	case "expire":
		$sort = "mod_end";
		$order = "DESC";
		break;
	//case "expire":
	default:
		$sort = "forum_id ASC, uid ASC, ip";
		$order = "ASC";
		break;
}

$criteria = new Criteria("forum_id", "(0, ".$xforum_id.")", "IN");
$criteria->setLimit($xoopsModuleConfig['topics_per_page']);
$criteria->setStart($start);
$criteria->setSort($sort);
$criteria->setOrder($order);
$moderate_objs =& $moderate_handler->getObjects($criteria);
$moderate_count = $moderate_handler->getCount($criteria);

include XOOPS_ROOT_PATH.'/header.php';
if($xforum_id){
	$url = 'viewforum.php?forum='.$xforum_id;
}else{
	$url = 'index.php';
}
echo '<div style="padding: 10px; margin-left:auto; margin-right:auto; text-align:center;"><a href="'.$url.'"><h2>'._MD_SUSPEND_MANAGEMENT.'</h2></a></div>';

if(!empty($moderate_count)){
	$_users = array();
	foreach(array_keys($moderate_objs) as $id){
		$_users[$moderate_objs[$id]->getVar("uid")] = 1;
	}
	$users =& xforum_getUnameFromIds(array_keys($_users), $xoopsModuleConfig['show_realname'], true);
	
	echo '
	<table class="outer" cellpadding="6" cellspacing="1" border="0" width="100%" align="center">
		<tr class="head" align="left">
			<td width="5%" align="center" nowrap="nowrap">
				<strong><a href="moderate.php?forum='.$xforum_id.'&amp;start='.$start.'&amp;sort=uid" title="Sort by uid">'._MD_SUSPEND_UID.'</a></strong>
				</td>
			<td width="10%" align="center" nowrap="nowrap">
				<strong><a href="moderate.php?forum='.$xforum_id.'&amp;start='.$start.'&amp;sort=start" title="Sort by start">'._MD_SUSPEND_START.'</a></strong>
				</td>
			<td width="10%" align="center" nowrap="nowrap">
				<strong><a href="moderate.php?forum='.$xforum_id.'&amp;start='.$start.'&amp;sort=expire" title="Sort by expire">'._MD_SUSPEND_EXPIRE.'</a></strong>
				</td>
			<td width="10%" align="center" nowrap="nowrap">
				<strong><a href="moderate.php?forum='.$xforum_id.'&amp;start='.$start.'&amp;sort=forum" title="Sort by expire">'._MD_SUSPEND_SCOPE.'</a></strong>
				</td>
			<td align="left">
				<strong>'._MD_SUSPEND_DESC.'</strong>
				</td>
			<td width="5%" align="center" nowrap="nowrap">
				<strong>'._DELETE.'</strong>
				</td>
		</tr>
	';
	
	foreach(array_keys($moderate_objs) as $id){
		echo '	
			<tr>
				<td width="5%" align="center" nowrap="nowrap">
					'.(
						$moderate_objs[$id]->getVar("uid")?
							(isset($users[$moderate_objs[$id]->getVar("uid")])?$users[$moderate_objs[$id]->getVar("uid")]:$moderate_objs[$id]->getVar("uid"))
							:$moderate_objs[$id]->getVar("ip")
					).'
					</td>
				<td width="10%" align="center">
					'.(formatTimestamp($moderate_objs[$id]->getVar("mod_start"))).'
					</td>
				<td width="10%" align="center">
					'.(formatTimestamp($moderate_objs[$id]->getVar("mod_end"))).'
					</td>
				<td width="10%" align="center">
					'.($moderate_objs[$id]->getVar("forum_id")?_MD_FORUM:_ALL).'
					</td>
				<td align="left">
					'.($moderate_objs[$id]->getVar("mod_desc")?$moderate_objs[$id]->getVar("mod_desc"):_NONE).'
					</td>
				<td width="5%" align="center" nowrap="nowrap">
					'.
					( ($is_administrator || $moderate_objs[$id]->getVar("forum_id")==$xforum_id)?'<a href="moderate.php?forum='.$xforum_id.'&amp;del='.$moderate_objs[$id]->getVar("mod_id").'">'._DELETE.'</a>':' ').'
					</td>
			</tr>
		';
	}	
	echo '
		<tr class="head" align="left">
			<td width="5%" align="center" nowrap="nowrap">
				<strong><a href="moderate.php?forum='.$xforum_id.'&amp;start='.$start.'&amp;sort=uid" title="Sort by uid">'._MD_SUSPEND_UID.'</a></strong>
				</td>
			<td width="10%" align="center" nowrap="nowrap">
				<strong><a href="moderate.php?forum='.$xforum_id.'&amp;start='.$start.'&amp;sort=start" title="Sort by start">'._MD_SUSPEND_START.'</a></strong>
				</td>
			<td width="10%" align="center" nowrap="nowrap">
				<strong><a href="moderate.php?forum='.$xforum_id.'&amp;start='.$start.'&amp;sort=expire" title="Sort by expire">'._MD_SUSPEND_EXPIRE.'</a></strong>
				</td>
			<td width="10%" align="center" nowrap="nowrap">
				<strong><a href="moderate.php?forum='.$xforum_id.'&amp;start='.$start.'&amp;sort=forum" title="Sort by expire">'._MD_SUSPEND_SCOPE.'</a></strong>
				</td>
			<td align="left">
				<strong>'._MD_SUSPEND_DESC.'</strong>
				</td>
			<td width="5%" align="center" nowrap="nowrap">
				<strong>'._DELETE.'</strong>
				</td>
		</tr>
	';
	if ( $moderate_count > $xoopsModuleConfig['topics_per_page']) {
		include XOOPS_ROOT_PATH.'/class/pagenav.php';
		$nav = new XoopsPageNav($all_topics, $xoopsModuleConfig['topics_per_page'], $start, "start", 'forum='.$xforum_id.'&amp;sort='.$sortname);
		echo '<tr><td colspan="6">'.$nav->renderNav(4).'</td></tr>';
	}
	
	echo '</table><br /><br />';			
}

include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";
$xforum_form = new XoopsThemeForm(_ADD, 'suspend', "moderate.php", 'post');
$xforum_form->addElement(new XoopsFormText(_MD_SUSPEND_UID, 'uid', 20, 25));
$xforum_form->addElement(new XoopsFormText(_MD_SUSPEND_IP, 'ip', 20, 25));
$xforum_form->addElement(new XoopsFormText(_MD_SUSPEND_DURATION, 'expire', 20, 25, ''), true);
$xforum_form->addElement(new XoopsFormText(_MD_SUSPEND_DESC, 'desc', 50, 255));
$xforum_form->addElement(new XoopsFormHidden('forum', $xforum_id));
$xforum_form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, "submit"));
$xforum_form->display();
include XOOPS_ROOT_PATH.'/footer.php';
?>