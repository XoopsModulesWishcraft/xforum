<?php
// $Id: vars.php,v 4.04 2008/06/05 15:35:33 wishcraft Exp $
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
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
include_once XOOPS_ROOT_PATH.'/modules/xforum/include/functions.ini.php';

$ori_error_level = ini_get('error_reporting');
error_reporting(E_ALL ^ E_NOTICE);

/**#@+
 * xforum constant
 *
 **/
define('forum_CONSTANTS',1);
define('forum_READ', 1);
define('forum_UNREAD', 2);
define('forum_UNREPLIED', 3);
define('forum_DIGEST', 4);
define('forum_DELETEONE', 1);
define('forum_DELETEALL', 2);
if (!defined('FORUM_PERM_ITEMS')) define('FORUM_PERM_ITEMS', 'access,view,post,reply,edit,delete,addpoll,vote,attach,noapprove');

/* some static xoopsModuleConfig */
$GLOBALS["xoopsModuleConfig"]["require_name"] = true; // "name" field is required for anonymous users in edit form

// MENU handler
/* You could remove anyone by commenting out in order to disable it */
$valid_menumodes = array(
	0 => _MD_MENU_SELECT,	// for selectbox
	1 => _MD_MENU_CLICK,	// for "click to expand"
	2 => _MD_MENU_HOVER		// for "mouse hover to expand"
	);

include_once XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php';

// You shouldn't have to change any of these
$xforumUrl['root'] = XOOPS_URL."/modules/" . $xoopsModule->dirname();
$xforumUrl['images_root'] = XOOPS_URL."/modules/".$xoopsModule->dirname()."/"."images";

//$handle = opendir(XOOPS_ROOT_PATH.'/modules/' . $xoopsModule->dirname() . '/images/imagesets/');
$setdir = $xoopsModuleConfig['image_set'];
//if (empty($setdir) || !is_dir(XOOPS_ROOT_PATH.'/modules/'. $xoopsModule->dirname() .'/images/imagesets/'.$setdir.'/')) {
if (empty($setdir)) {
	$setdir = "default";
}

$xforumUrl['images_set']= $xforumUrl['images_root']."/imagesets/".$setdir;
if (is_dir(XOOPS_ROOT_PATH.'/modules/'. $xoopsModule->dirname() .'/images/imagesets/'.$setdir.'/'.$xoopsConfig['language'])) {
	$xforumUrl['images_lang']=$xforumUrl['images_set']."/".$xoopsConfig['language'];
}else{
	$xforumUrl['images_lang']=$xforumUrl['images_set']."/english";
}

/* -- You shouldn't have to change anything after this point */
/* -- Images -- */

$xforumImage['attachment'] = $xforumUrl['images_set']."/attachment-a";
$xforumImage['clip'] = $xforumUrl['images_set']."/clip-a";
$xforumImage['whosonline'] = $xforumUrl['images_set']."/whosonline-a";

$xforumImage['folder_sticky'] = $xforumUrl['images_set']."/folder_sticky-a";
$xforumImage['folder_digest'] = $xforumUrl['images_set']."/folder_digest-a";
$xforumImage['locked_topic'] = $xforumUrl['images_set']."/lock-a";
$xforumImage['poll'] = $xforumUrl['images_set']."/poll-a";

$xforumImage['newposts_forum'] = $xforumUrl['images_set']."/folder_new_big-a";
$xforumImage['folder_forum'] = $xforumUrl['images_set']."/folder_big-a";
$xforumImage['locked_forum'] = $xforumUrl['images_set']."/folder_locked_big-a";
$xforumImage['locked_forum_newposts'] = $xforumUrl['images_set']."/folder_locked_big_newposts-a";
$xforumImage['folder_topic'] = $xforumUrl['images_set']."/folder-a";
$xforumImage['hot_folder_topic'] = $xforumUrl['images_set']."/hot_folder-a";
$xforumImage['newposts_topic'] = $xforumUrl['images_set']."/red_folder-a";
$xforumImage['hot_newposts_topic'] = $xforumUrl['images_set']."/hot_red_folder-a";
$xforumImage['hot_user_folder_topic'] = $xforumUrl['images_set']."/hot_folder_user-a";
$xforumImage['newposts_user_topic'] = $xforumUrl['images_set']."/red_folder_user-a";
$xforumImage['folder_user_topic'] = $xforumUrl['images_set']."/folder_user-a";

$xforumImage['rss'] = $xforumUrl['images_root']."/rss-a";
$xforumImage['subforum'] = $xforumUrl['images_root']."/arrow-a";
$xforumImage['blank'] = $xforumUrl['images_root']."/blank";
$xforumImage['move_topic'] = $xforumUrl['images_root']."/move_topic-a";
$xforumImage['del_topic'] = $xforumUrl['images_root']."/del_topic-a";
$xforumImage['lock_topic'] = $xforumUrl['images_root']."/lock_topic-a";
$xforumImage['unlock_topic'] = $xforumUrl['images_root']."/unlock_topic-a";
$xforumImage['sticky'] = $xforumUrl['images_root']."/sticky-a";
$xforumImage['unsticky'] = $xforumUrl['images_root']."/unsticky-a";
$xforumImage['digest'] = $xforumUrl['images_root']."/digest-a";
$xforumImage['undigest'] = $xforumUrl['images_root']."/undigest-a";

$xforumImage['edit'] = $xforumUrl['images_root']."/edit-a";
$xforumImage['delete'] = $xforumUrl['images_root']."/delete-a";
$xforumImage['restart'] = $xforumUrl['images_root']."/approve-a";
$xforumImage['approve'] = $xforumUrl['images_root']."/approve-a";

$xforumImage['personal'] = $xforumUrl['images_root']."/personal-a";
$xforumImage['pm'] = $xforumUrl['images_root'] . "/pm-a";
$xforumImage['icq'] = $xforumUrl['images_root'] . "/icq-a";
$xforumImage['email'] = $xforumUrl['images_root'] . "/email-a";
$xforumImage['aim'] = $xforumUrl['images_root'] . "/aim-a";
$xforumImage['home'] = $xforumUrl['images_root'] . "/home-a";
$xforumImage['yahoo'] = $xforumUrl['images_root'] . "/yahoo-a";
$xforumImage['msnm'] = $xforumUrl['images_root'] . "/msnm-a";
$xforumImage['pdf'] = $xforumUrl['images_root']."/pdf-a";
$xforumImage['spacer'] = $xforumUrl['images_root']."/spacer-a";
$xforumImage['news'] = $xforumUrl['images_root']."/news-a";
$xforumImage['docicon'] = $xforumUrl['images_root']."/document-a";

$xforumImage['p_delete'] = $xforumUrl['images_lang']."/p_delete-a";
$xforumImage['p_reply'] = $xforumUrl['images_lang']."/p_reply-a";
$xforumImage['p_quote'] = $xforumUrl['images_lang']."/p_quote-a";
$xforumImage['p_edit'] = $xforumUrl['images_lang']."/p_edit-a";
$xforumImage['p_report'] = $xforumUrl['images_lang']."/p_report-a";
$xforumImage['p_up'] = $xforumUrl['images_lang']."/p_up-a";
$xforumImage['t_new'] = $xforumUrl['images_lang']."/t_new-a";
$xforumImage['t_poll'] = $xforumUrl['images_lang']."/t_poll-a";
$xforumImage['t_qr'] = $xforumUrl['images_lang']."/t_qr-a";
$xforumImage['t_reply'] = $xforumUrl['images_lang']."/t_reply-a";

$xforumImage['online'] = $xforumUrl['images_lang']."/online-a";
$xforumImage['offline'] = $xforumUrl['images_lang']."/offline-a";
$xforumImage['new_forum']    = $xforumUrl['images_lang']."/new_forum-a";
$xforumImage['new_subforum'] = $xforumUrl['images_lang']."/new_subforum-a";

$xforumImage['post_content'] = $xforumUrl['images_set']."/post_content-a";

$xforumImage['threaded'] = $xforumUrl['images_set']."/threaded-a";
$xforumImage['flat'] = $xforumUrl['images_set']."/flat-a";
$xforumImage['left'] = $xforumUrl['images_set']."/left-a";
$xforumImage['right'] = $xforumUrl['images_set']."/right-a";
$xforumImage['doubledown'] = $xforumUrl['images_set']."/doubledown-a";
$xforumImage['down'] = $xforumUrl['images_set']."/down-a";
$xforumImage['up'] = $xforumUrl['images_set']."/up-a";
$xforumImage['printer'] = $xforumUrl['images_set']."/printer-a";

$xforumImage['pm'] = XOOPS_URL."/images/icons/pm_small.gif";

$xforumImage['rate1'] = $xforumUrl['images_set'].'/rate1-a';
$xforumImage['rate2'] = $xforumUrl['images_set'].'/rate2-a';
$xforumImage['rate3'] = $xforumUrl['images_set'].'/rate3-a';
$xforumImage['rate4'] = $xforumUrl['images_set'].'/rate4-a';
$xforumImage['rate5'] = $xforumUrl['images_set'].'/rate5-a';

// xforum cookie structure
/* -- Cookie settings -- */
$forumCookie['domain'] = "";
$forumCookie['path'] = "/";
$forumCookie['secure'] = false;
$forumCookie['expire'] = time() + 3600 * 24 * 30; // one month
$forumCookie['prefix'] = '';

// set cookie name to avoid subsites confusion such as: domain.com, sub1.domain.com, sub2.domain.com, domain.com/xoopss, domain.com/xoops2
if(empty($forumCookie['prefix'])){
	$cookie_prefix = preg_replace("/[^a-z_0-9]+/i", "_", preg_replace("/(http(s)?:\/\/)?(www.)?/i","",XOOPS_URL));
	$cookie_userid = (is_object($xoopsUser))?$xoopsUser->getVar('uid'):0;
	$forumCookie['prefix'] = $cookie_prefix."_".$xoopsModule->dirname().'_'.$cookie_userid."_";
}

// set LastVisitTemp cookie, which only gets the time from the LastVisit cookie if it does not exist yet
// otherwise, it gets the time from the LastVisitTemp cookie
//$last_visit = forum_getcookie("LVT");
$last_visit = forum_getsession("LV");
$last_visit = ($last_visit)?$last_visit:forum_getcookie("LV");
$last_visit = ($last_visit)?$last_visit:time();


// update LastVisit cookie.
forum_setcookie("LV", time(), $forumCookie['expire']); // set cookie life time to one month
//forum_setcookie("LVT", $last_visit);
forum_setsession("LV", $last_visit);

/* xforum cookie storage
	Long term cookie: (configurable, generally one month)
		LV - Last Visit
		M - Menu mode
		V - View mode
		G - Toggle
	Short term cookie: (same as session life time)
		ST - Stored Topic IDs for mark
		LP - Last Post
		LF - Forum Last view
		LT - Topic Last read
		LVT - Last Visit Temp
*/

// include customized variables
if( is_object($GLOBALS["xoopsModule"]) && "xforum" == $GLOBALS["xoopsModule"]->getVar("dirname", "n") ) {
	$GLOBALS["xoopsModuleConfig"] = forum_load_config();
}

forum_load_object();

error_reporting($ori_error_level);
?>