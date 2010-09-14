<?php
// $Id: functions.php,v 4.04 2008/06/05 15:35:33 wishcraft Exp $
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

if(!defined("forum_FUNCTIONS")):
define("forum_FUNCTIONS", true);

include_once dirname(__FILE__)."/functions.ini.php";

if (!function_exists('xoops_sef'))
{
	function xoops_sef($datab, $char ='-')
	{
		$replacement_chars = array();
		$accepted = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","m","o","p","q",
				 "r","s","t","u","v","w","x","y","z","0","9","8","7","6","5","4","3","2","1");
		for($i=0;$i<256;$i++){
			if (!in_array(strtolower(chr($i)),$accepted))
				$replacement_chars[] = chr($i);
		}
		$return_data = (str_replace($replacement_chars,$char,$datab));
		#print $return_data . "<BR><BR>";
		return($return_data);
	
	}
}
function &forum_getUnameFromIds( $userid, $usereal = 0, $linked = false )
{
	$users = mod_getUnameFromIds( $userid, $usereal, $linked);
	return $users;
}

function forum_getUnameFromId( $userid, $usereal = 0, $linked = false)
{
	return mod_getUnameFromId( $userid, $usereal, $linked);
}

function forum_is_dir($dir){
    $openBasedir = ini_get('open_basedir');
    if (empty($openBasedir)) {
	    return @is_dir($dir);
    }

    return in_array($dir, explode(':', $openBasedir));
}

/*
 * Sorry, we have to use the stupid solution unless there is an option in MyTextSanitizer:: htmlspecialchars();
 */
function forum_htmlSpecialChars($text)
{
	return preg_replace(array("/&amp;/i", "/&nbsp;/i"), array('&', '&amp;nbsp;'), htmlspecialchars($text));
}

function &forum_displayTarea(&$text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1)
{
	global $myts;

	if ($html != 1) {
		// html not allowed
		$text = forum_htmlSpecialChars($text);
	}
	$text = $myts->codePreConv($text, $xcode); // Ryuji_edit(2003-11-18)
	$text = $myts->makeClickable($text);
	if ($smiley != 0) {
		// process smiley
		$text = $myts->smiley($text);
	}
	if ($xcode != 0) {
		// decode xcode
		if ($image != 0) {
			// image allowed
			$text = $myts->xoopsCodeDecode($text);
		} else {
    		// image not allowed
    		$text = $myts->xoopsCodeDecode($text, 0);
		}
	}
	if ($br != 0) {
		$text = $myts->nl2Br($text);
	}
	$text = $myts->codeConv($text, $xcode, $image);	// Ryuji_edit(2003-11-18)
	return $text;
}

/* 
 * Filter out possible malicious text
 * kses project at SF could be a good solution to check
 *
 * package: Article
 *
 * @param string	$text 	text to filter
 * @param bool		$force 	flag indicating to force filtering
 * @return string 	filtered text
 */
function &forum_textFilter($text, $force = false)
{
	global $xoopsUser, $xoopsConfig;
	
	if(empty($force) && is_object($xoopsUser) && $xoopsUser->isAdmin()){
		return $text;
	}
	// For future applications
	$tags=empty($xoopsConfig["filter_tags"])?array():explode(",", $xoopsConfig["filter_tags"]);
	$tags = array_map("trim", $tags);
	
	// Set embedded tags
	$tags[] = "SCRIPT";
	$tags[] = "VBSCRIPT";
	$tags[] = "JAVASCRIPT";
	foreach($tags as $tag){
		$search[] = "/<".$tag."[^>]*?>.*?<\/".$tag.">/si";
		$replace[] = " [!".strtoupper($tag)." FILTERED!] ";
	}
	// Set iframe tag
	$search[]= "/<IFRAME[^>\/]*SRC=(['\"])?([^>\/]*)(\\1)[^>\/]*?\/>/si";
	$replace[]=" [!IFRAME FILTERED!] \\2 ";
	$search[]= "/<IFRAME[^>]*?>([^<]*)<\/IFRAME>/si";
	$replace[]=" [!IFRAME FILTERED!] \\1 ";
	// action
	$text = preg_replace($search, $replace, $text);
	return $text;
}

function forum_html2text($document)
{
	$text = strip_tags($document);
	return $text;
}

/*
 * Currently the xforum session/cookie handlers are limited to:
 * -- one dimension
 * -- "," and "|" are preserved
 *
 */

function forum_setsession($name, $string = '')
{
	if(is_array($string)) {
		$value = array();
		foreach ($string as $key => $val){
			$value[]=$key."|".$val;
		}
		$string = implode(",", $value);
	}
	$_SESSION['xforum_'.$name] = $string;
}

function forum_getsession($name, $isArray = false)
{
	$value = !empty($_SESSION['xforum_'.$name]) ? $_SESSION['xforum_'.$name] : false;
	if($isArray) {
		$_value = ($value)?explode(",", $value):array();
		$value = array();
		if(count($_value)>0) foreach($_value as $string){
			$key = substr($string, 0, strpos($string,"|"));
			$val = substr($string, (strpos($string,"|")+1));
			$value[$key] = $val;
		}
		unset($_value);
	}
	return $value;
}

function forum_setcookie($name, $string = '', $expire = 0)
{
	global $forumCookie;
	if(is_array($string)) {
		$value = array();
		foreach ($string as $key => $val){
			$value[]=$key."|".$val;
		}
		$string = implode(",", $value);
	}
	setcookie($forumCookie['prefix'].$name, $string, intval($expire), $forumCookie['path'], $forumCookie['domain'], $forumCookie['secure']);
}

function forum_getcookie($name, $isArray = false)
{
	global $forumCookie;
	$value = !empty($_COOKIE[$forumCookie['prefix'].$name]) ? $_COOKIE[$forumCookie['prefix'].$name] : null;
	if($isArray) {
		$_value = ($value)?explode(",", $value):array();
		$value = array();
		if(count($_value)>0) foreach($_value as $string){
			$sep = strpos($string,"|");
			if($sep===false){
				$value[]=$string;
			}else{
				$key = substr($string, 0, $sep);
				$val = substr($string, ($sep+1));
				$value[$key] = $val;
			}
		}
		unset($_value);
	}
	return $value;
}

function forum_checkTimelimit($action_last, $action_tag, $inMinute = true)
{
	global $xoopsModuleConfig;
	if(!isset($xoopsModuleConfig[$action_tag]) or $xoopsModuleConfig[$action_tag]==0) return true;
	$timelimit = ($inMinute)?$xoopsModuleConfig[$action_tag]*60:$xoopsModuleConfig[$action_tag];
	return ($action_last > time()-$timelimit)?true:false;
}


function &getModuleAdministrators($mid=0)
{
	static $module_administrators=array();
	if(isset($module_administrators[$mid])) return $module_administrators[$mid];

    $moduleperm_handler =& xoops_gethandler('groupperm');
    $groupsIds = $moduleperm_handler->getGroupIds('module_admin', $mid);

    $administrators = array();
    $member_handler =& xoops_gethandler('member');
    foreach($groupsIds as $groupid){
    	$userIds = $member_handler->getUsersByGroup($groupid);
    	foreach($userIds as $userid){
        	$administrators[$userid] = 1;
    	}
    }
    $module_administrators[$mid] =array_keys($administrators);
    unset($administrators);
    return $module_administrators[$mid];
}

/* use hardcoded DB query to save queries */
function forum_isModuleAdministrator($uid = 0, $mid = 0)
{
	global $xoopsDB;
	static $module_administrators=array();
	if(isset($module_administrators[$mid][$uid])) return $module_administrators[$mid][$uid];

    $sql = "SELECT COUNT(l.groupid) FROM ".$xoopsDB->prefix('groups_users_link')." AS l".
    		" LEFT JOIN ".$xoopsDB->prefix('group_permission')." AS p ON p.gperm_groupid=l.groupid".
    		" WHERE l.uid=".intval($uid).
    		"	AND p.gperm_modid = '1' AND p.gperm_name = 'module_admin' AND p.gperm_itemid = '".intval($mid)."'";
    if(!$result = $xoopsDB->query($sql)){
	    $module_administrators[$mid][$uid] = null;
    }else{
    	list($count) = $xoopsDB->fetchRow($result);
	    $module_administrators[$mid][$uid] = intval($count);    	
    }
    return $module_administrators[$mid][$uid];
}

/* use hardcoded DB query to save queries */
function forum_isModuleAdministrators($uid = array(), $mid = 0)
{
	global $xoopsDB;
	$module_administrators=array();

	if(empty($uid)) return $module_administrators;
    $sql = "SELECT COUNT(l.groupid) AS count, l.uid FROM ".$xoopsDB->prefix('groups_users_link')." AS l".
    		" LEFT JOIN ".$xoopsDB->prefix('group_permission')." AS p ON p.gperm_groupid=l.groupid".
    		" WHERE l.uid IN (".implode(", ", array_map("intval", $uid)).")".
    		"	AND p.gperm_modid = '1' AND p.gperm_name = 'module_admin' AND p.gperm_itemid = '".intval($mid)."'".
    		" GROUP BY l.uid";
    if($result = $xoopsDB->query($sql)){
	    while($myrow = $xoopsDB->fetchArray($result)){
	    	$module_administrators[$myrow["uid"]] = intval($myrow["count"]);
    	}
    }
    return $module_administrators;
}

function forum_isAdministrator($user=-1, $mid=0)
{
	global $xoopsUser, $xoopsModule;
	static $administrators, $forum_mid;

	if(is_numeric($user) && $user == -1) $user =& $xoopsUser;
	if(!is_object($user) && intval($user)<1) return false;
	$uid = (is_object($user))?$user->getVar('uid'):intval($user);

	if(!$mid){
		if (!isset($forum_mid)) {
		    if(is_object($xoopsModule)&& 'xforum' == $xoopsModule->dirname()){
		    	$forum_mid = $xoopsModule->getVar('mid');
		    }else{
		        $modhandler =& xoops_gethandler('module');
		        $xforum =& $modhandler->getByDirname('xforum');
			    $forum_mid = $xforum->getVar('mid');
			    unset($xforum);
		    }
		}
		$mid = $forum_mid;
	}
	
	return forum_isModuleAdministrator($uid, $mid);
}

function forum_isAdmin($xforum = 0, $user=-1)
{
	global $xoopsUser;
	static $_cachedModerators;

	if(is_numeric($user) && $user == -1) $user =& $xoopsUser;
	if(!is_object($user) && intval($user)<1) return false;
	$uid = (is_object($user))?$user->getVar('uid'):intval($user);
	if(forum_isAdministrator($uid)) return true;

	$cache_id = (is_object($xforum))?$xforum->getVar('forum_id'):intval($xforum);
	if(!isset($_cachedModerators[$cache_id])){
		$forum_handler =& xoops_getmodulehandler('forum', 'xforum');
		if(!is_object($xforum)) $xforum = $forum_handler->get(intval($xforum));
		$_cachedModerators[$cache_id] = $forum_handler->getModerators($xforum);
	}
	return in_array($uid,$_cachedModerators[$cache_id]);
}

function forum_isModerator($xforum = 0, $user=-1)
{
	global $xoopsUser;
	static $_cachedModerators;

	if(is_numeric($user) && $user == -1) $user =& $xoopsUser;
	if(!is_object($user) && intval($user)<1) {
		return false;
	}
	$uid = (is_object($user))?$user->getVar('uid'):intval($user);

	$cache_id = (is_object($xforum))?$xforum->getVar('forum_id'):intval($xforum);
	if(!isset($_cachedModerators[$cache_id])){
		$forum_handler =& xoops_getmodulehandler('forum', 'xforum');
		if(!is_object($xforum)) $xforum = $forum_handler->get(intval($xforum));
		$_cachedModerators[$cache_id] = $forum_handler->getModerators($xforum);
	}
	return in_array($uid,$_cachedModerators[$cache_id]);
}

function forum_checkSubjectPrefixPermission($xforum = 0, $user=-1)
{
	global $xoopsUser, $xoopsModuleConfig;

	if($xoopsModuleConfig['subject_prefix_level']<1){
		return false;
	}
	if($xoopsModuleConfig['subject_prefix_level']==1){
		return true;
	}
	if(is_numeric($user) && $user == -1) $user =& $xoopsUser;
	if(!is_object($user) && intval($user)<1) return false;
	$uid = (is_object($user))?$user->getVar('uid'):intval($user);
	if($xoopsModuleConfig['subject_prefix_level']==2){
		return true;
	}
	if($xoopsModuleConfig['subject_prefix_level']==3){
		if(forum_isAdmin($xforum, $user)) return true;
		else return false;
	}
	if($xoopsModuleConfig['subject_prefix_level']==4){
		if(forum_isAdministrator($user)) return true;
	}
	return false;
}
/*
* Gets the total number of topics in a form
*/
function get_total_topics($forum_id="")
{
	$topic_handler =& xoops_getmodulehandler('topic', 'xforum');
	$criteria =& new CriteriaCompo(new Criteria("approved", 0, ">"));
    if ( $forum_id ) {
	    $criteria->add(new Criteria("forum_id", intval($forum_id)));
    }
    return $topic_handler->getCount($criteria);
}

/*
* Returns the total number of posts in the whole system, a forum, or a topic
* Also can return the number of users on the system.
*/
function get_total_posts($id = 0, $type = "all")
{
	$post_handler =& xoops_getmodulehandler('post', 'xforum');
	$criteria =& new CriteriaCompo(new Criteria("approved", 0, ">"));
    switch ( $type ) {
    case 'forum':
        if($id>0) $criteria->add(new Criteria("forum_id", intval($id)));
        break;
    case 'topic':
        if($id>0) $criteria->add(new Criteria("topic_id", intval($id)));
        break;
    case 'all':
    default:
        break;
    }
    return $post_handler->getCount($criteria);
}

function get_total_views()
{
    global $xoopsDB;
    $sql = "SELECT sum(topic_views) FROM ".$xoopsDB->prefix("xf_topics")."";
    if ( !$result = $xoopsDB->query($sql) ) {
        return null;
    }
    list ($total) = $xoopsDB->fetchRow($result);
    return $total;
}

function forum_forumSelectBox($value = null, $permission = "access", $delimitor_category = true)
{
	$category_handler =& xoops_getmodulehandler('category', 'xforum');
	$forum_handler =& xoops_getmodulehandler('forum', 'xforum');
    $categories = $category_handler->getAllCats($permission, true);
    $xforums = $forum_handler->getForumsByCategory(array_keys($categories), $permission, false);

    if(!defined("_MD_SELFORUM")) {
		if ( !( $ret = @include_once( XOOPS_ROOT_PATH."/modules/xforum/language/".$GLOBALS['xoopsConfig']['language']."/main.php" ) ) ) {
			include_once( XOOPS_ROOT_PATH."/modules/xforum/language/english/main.php" );
		}
    }
    $value = is_array($value)?$value:array($value);
    $box ='<option value="-1">-- '._MD_SELFORUM.' --</option>';
	if(count($categories)>0 && count($xforums)>0){
		foreach(array_keys($xforums) as $key){
			if($delimitor_category) {
	            $box .= "<option value='-1'>&nbsp;</option>";
			}
            $box .= "<option value='-1'>[".$categories[$key]->getVar('cat_title')."]</option>";
            foreach ($xforums[$key] as $f=>$xforum) {
                $box .= "<option value='".$f."' ".( (in_array($f, $value))?" selected":"" ).">-- ".$xforum['title']."</option>";
				if( !isset($xforum["sub"]) || count($xforum["sub"]) ==0 ) continue; 
	            foreach (array_keys($xforum["sub"]) as $s) {
	                $box .= "<option value='".$s."' ".( (in_array($s, $value))?" selected":"" ).">---- ".$xforum["sub"][$s]['title']."</option>";
                }
            }
		}
    } else {
        $box .= "<option value='-1'>"._MD_NOFORUMINDB."</option>";
    }
    unset($xforums, $categories);
 
    return $box;   
}
	
function forum_make_jumpbox($forum_id = 0)
{
	$box = '<form name="forum_jumpbox" method="get" action="viewforum.php" onsubmit="javascript: if(document.forum_jumpbox.forum.value &lt; 1){return false;}">';
	$box .= '<select class="select" name="forum" onchange="javascript: if(this.options[this.selectedIndex].value >0 ){ document.forms.forum_jumpbox.submit();}">';
    $box .= forum_forumSelectBox($forum_id);
    $box .= "</select> <input type='submit' class='button' value='"._GO."' /></form>";
    unset($xforums, $categories);
    return $box;
}

function forum_isIE5()
{
	static $user_agent_is_IE5;

	if(isset($user_agent_is_IE5)) return $user_agent_is_IE5;;
    $msie='/msie\s(5\.[5-9]|[6-9]\.[0-9]*).*(win)/i';
    if( !isset($_SERVER['HTTP_USER_AGENT']) ||
        !preg_match($msie,$_SERVER['HTTP_USER_AGENT']) ||
        preg_match('/opera/i',$_SERVER['HTTP_USER_AGENT'])){
	    $user_agent_is_IE5 = false;
    }else{
	    $user_agent_is_IE5 = true;
    }
    return $user_agent_is_IE5;
}

function forum_displayImage($image, $alt = "", $width = 0, $height =0, $style ="margin: 0px;", $sizeMeth='scale')
{
	global $xoopsModuleConfig, $xforumImage;
	static $image_type;

	$user_agent_is_IE5 = forum_isIE5();
	if(!isset($image_type)) $image_type = ($xoopsModuleConfig['image_type'] == 'auto')?(($user_agent_is_IE5)?'gif':'png'):$xoopsModuleConfig['image_type'];
	$image .= '.'.$image_type;
	$imageuri=preg_replace("/^".preg_quote(XOOPS_URL,"/")."/",XOOPS_ROOT_PATH,$image);
	if(!preg_match("/^".preg_quote(XOOPS_ROOT_PATH,"/")."/",$imageuri)){
		$imageuri = XOOPS_ROOT_PATH."/".$image;
	}
	if(file_exists($imageuri)){
	    $size=@getimagesize($imageuri);
	    if(is_array($size)){
		    $width=$size[0];
		    $height=$size[1];
	    }
    }else{
		$image=$xforumImage['blank'].'.gif';
    }
    $width .='px';
    $height .='px';

    $img_style = "width: $width; height:$height; $style";
    $image_url = "<img src=\"".$image."\" style=\"".$img_style."\" alt=\"".$alt."\" align=\"middle\" />";

    return $image_url;
}

/**
 * forum_updaterating()
 *
 * @param $sel_id
 * @return updates rating data in itemtable for a given item
 **/
function forum_updaterating($sel_id)
{
    global $xoopsDB;
    $query = "select rating FROM " . $xoopsDB -> prefix('xf_votedata') . " WHERE topic_id = " . $sel_id . "";
    $voteresult = $xoopsDB -> query($query);
    $votesDB = $xoopsDB -> getRowsNum($voteresult);
    $totalrating = 0;
    while (list($rating) = $xoopsDB -> fetchRow($voteresult))
    {
        $totalrating += $rating;
    }
    $finalrating = $totalrating / $votesDB;
    $finalrating = number_format($finalrating, 4);
    $sql = sprintf("UPDATE %s SET rating = %u, votes = %u WHERE topic_id = %u", $xoopsDB -> prefix('xf_topics'), $finalrating, $votesDB, $sel_id);
    $xoopsDB -> queryF($sql);
}

function forum_sinceSelectBox($selected = 100)
{
	global $xoopsModuleConfig;

	$select_array = explode(',',$xoopsModuleConfig['since_options']);
	$select_array = array_map('trim',$select_array);

	$forum_selection_since = '<select name="since">';
	foreach ($select_array as $since) {
		$forum_selection_since .= '<option value="'.$since.'"'.(($selected == $since) ? ' selected="selected"' : '').'>';
		if($since>0){
			$forum_selection_since .= sprintf(_MD_FROMLASTDAYS, $since);
		}else{
			$forum_selection_since .= sprintf(_MD_FROMLASTHOURS, abs($since));
		}
		$forum_selection_since .= '</option>';
	}
	$forum_selection_since .= '<option value="365"'.(($selected == 365) ? ' selected="selected"' : '').'>'._MD_THELASTYEAR.'</option>';
	$forum_selection_since .= '<option value="0"'.(($selected == 0) ? ' selected="selected"' : '').'>'._MD_BEGINNING.'</option>';
	$forum_selection_since .= '</select>';

	return $forum_selection_since;
}

function forum_getSinceTime($since = 100)
{
	if($since==1000) return 0;
	if($since>0) return intval($since) * 24 * 3600;
	else return intval(abs($since)) * 3600;
}

function forum_welcome( $user = -1 )
{
	global $xoopsModuleConfig, $xoopsUser;

	if(empty($xoopsModuleConfig["welcome_forum"])) return null;
	if(is_numeric($user) && $user == -1) $user =& $xoopsUser;
	if(!is_object($user) || $user->getVar('posts')){
		return false;
	}

	$forum_handler =& xoops_getmodulehandler('forum', 'xforum');
	$xforum =& $forum_handler->get($xoopsModuleConfig["welcome_forum"]);
	if (!$forum_handler->getPermission($xforum)){
		unset($xforum);
		return false;
	}
	unset($xforum);
	include_once dirname(__FILE__)."/functions.welcome.php";
	return forum_welcome_create($user, $xoopsModuleConfig["welcome_forum"]);
}

function forum_synchronization($type = "")
{
	switch($type){
	case "rate":
	case "report":
	case "post":
	case "topic":
	case "forum":
	case "category":
	case "moderate":
	case "read":
		$type = array($type);
		$clean = $type;
		break;
	default:
		$type = null;
		$clean = array("category", "forum", "topic", "post", "report", "rate", "moderate", "readtopic", "readforum");
		break;
	}
	foreach($clean as $item){
		$handler =& xoops_getmodulehandler($item, "xforum");
		$handler->cleanOrphan();
		unset($handler);
	}
    $xforumConfig = forum_load_config();
	if(empty($type) || in_array("post", $type)):
		$post_handler =& xoops_getmodulehandler("post", "xforum");
        $expires = isset($xforumConfig["pending_expire"])?intval($xforumConfig["pending_expire"]):7;
		$post_handler->cleanExpires($expires*24*3600);
	endif;
	if(empty($type) || in_array("topic", $type)):
		$topic_handler =& xoops_getmodulehandler("topic", "xforum");
        $expires = isset($xforumConfig["pending_expire"])?intval($xforumConfig["pending_expire"]):7;
		$topic_handler->cleanExpires($expires*24*3600);
		$topic_handler->synchronization();
	endif;
	if(empty($type) || in_array("forum", $type)):
		$forum_handler =& xoops_getmodulehandler("forum", "xforum");
		$forum_handler->synchronization();
	endif;
	if(empty($type) || in_array("moderate", $type)):
		$moderate_handler =& xoops_getmodulehandler("moderate", "xforum");
		$moderate_handler->clearGarbage();
	endif;
	if(empty($type) || in_array("read", $type)):
		$read_handler =& xoops_getmodulehandler("readforum", "xforum");
		$read_handler->clearGarbage();
		$read_handler->synchronization();
		$read_handler =& xoops_getmodulehandler("readtopic", "xforum");
		$read_handler->clearGarbage();
		$read_handler->synchronization();
	endif;
	return true;
}

function forum_setRead($type, $item_id, $post_id, $uid = null)
{
	$read_handler =& xoops_getmodulehandler("read".$type, "xforum");
	return $read_handler->setRead($item_id, $post_id, $uid);
}

function forum_getRead($type, $item_id, $uid = null)
{
	$read_handler =& xoops_getmodulehandler("read".$type, "xforum");
	return $read_handler->getRead($item_id, $uid);
}

function forum_setRead_forum($status = 0, $uid = null)
{
	$read_handler =& xoops_getmodulehandler("readforum", "xforum");
	return $read_handler->setRead_items($status, $uid);
}

function forum_setRead_topic($status = 0, $forum_id = 0, $uid = null)
{
	$read_handler =& xoops_getmodulehandler("readtopic", "xforum");
	return $read_handler->setRead_items($status, $forum_id, $uid);
}

function forum_isRead($type, &$items, $uid = null)
{
	$read_handler =& xoops_getmodulehandler("read".$type, "xforum");
	return $read_handler->isRead_items($items, $uid);
}

endif;

function chronolabs_inline($flash = false)
{

	$ret = '<div style="clear:both; height 10px;">&nbsp;</div>
<div style="clear:both; height 10px;"><center><img src="http://www.chronolabs.org.au/images/banners/loader/supportimage.php?flash=false" /></center></div>
<div style="clear:both;">Chronolabs offer limited free support should you want some development work done please contact us <a href="http://www.chronolabs.org.au/liaise/">on the question for a quote form.</a> We offer a wide range of XOOPS Professional Solution and have options for Basic SEO and marketing of your site as well as Search Engine Optimization for <a href="http://www.xoops.org/">XOOPS</a>. If you are looking for work done with this module/application or are looking for development on your site please contact us.</div>';
	return $ret;
}
?>