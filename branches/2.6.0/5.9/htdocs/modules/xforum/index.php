<?php

// $Id: index.php,v 4.04 2008/06/05 15:35:59 wishcraft Exp $


include "header.php";

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
    $url=XOOPS_URL . '/modules/' . $GLOBALS['xforumModule']->getVar("dirname") . '/index.php';
    $GLOBALS['xoops']->redirect($url, 2, _MD_ALL_FORUM_MARKED.' '.$markresult);
}

$viewcat = @intval($_GET['cat']);
$category_handler = $GLOBALS['xoops']->getModuleHandler('category', 'xforum');

$categories = array();
if (!$viewcat) {
	$categories = $category_handler->getAllCats('access', true);
    $forum_index_title = "";
	$xoops_pagetitle = $GLOBALS['xforumModule']->getVar('name');
}else {
    $category_obj = $category_handler->get($viewcat);
	if($category_handler->getPermission($category_obj)) {
		$categories[$viewcat] = $category_obj;
	}
    $forum_index_title = sprintf(_MD_XFORUMINDEX, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES));
	$xoops_pagetitle = $category_obj->getVar('cat_title') . " [" .$GLOBALS['xforumModule']->getVar('name')."]";
}

if(count($categories) == 0){
    $GLOBALS['xoops']->redirect(XOOPS_URL, 2, _MD_NORIGHTTOACCESS);
    exit();
}

if ($GLOBALS['xforumModuleConfig']['htaccess']&&empty($_GET)) {
	if ($viewcat<>0) {
		$category = $category_handler->get($viewcat);
		$url = $category->getURL();
	} else {
		$url = XOOPS_URL.'/'.$GLOBALS['xforumModuleConfig']['baseurl'].'/';
	}
	if (!strpos($url, $_SERVER['REQUEST_URI'])) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header('Location: '.$url);
		exit(0);
	}
}

/* rss feed */

$GLOBALS['xoopsOption']['template_main']= 'xforum_index.html';
$GLOBALS['xoopsOption']['xoops_pagetitle']= $xoops_pagetitle;

$GLOBALS['xoops']->header($GLOBALS['xoopsOption']['template_main']);

if(!empty($GLOBALS['xforumModuleConfig']['rss_enable'])){
	$GLOBALS['xoops']->theme->addLink('alternate', XOOPS_URL.'/modules/'.$GLOBALS['xforumModule']->getVar('dirname').'/rss.php', array('type'=>"application/rss+xml", 'title' => $GLOBALS['xforumModule']->getVar('name')));
}
if(!empty($GLOBALS['xforumModuleConfig']['pngforie_enabled'])){
	$GLOBALS['xoops']->theme->addStylesheet(null,"text/css",'img {behavior:url("'.XOOPS_URL.'/modules/xforum/include/pngbehavior.htc");}');
}
$GLOBALS['xoops']->theme->addScript(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/js/xforum_toggle.js");
$GLOBALS['xoops']->theme->addScript(null, 'text/javascript', 'var toggle_cookie="'.$forumCookie['prefix'].'G'.'";');
if($menumode==2){
	$GLOBALS['xoops']->theme->addStylesheet(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/language/" . $GLOBALS['xoopsConfig']['language'] . "/forum_menu_hover.css", "text/css");
	$GLOBALS['xoops']->theme->addStylesheet(null,"text/css",'body {behavior:url("'.XOOPS_URL.'/modules/xforum/include/xforum.htc");}');
}
if($menumode==1){
	$GLOBALS['xoops']->theme->addStylesheet(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/language/" . $GLOBALS['xoopsConfig']['language'] . "/forum_menu_click.css", "text/css");
	$GLOBALS['xoops']->theme->addScript(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/js/forum_menu_click.js", 'text/javascript');
}
$GLOBALS['xoops']->theme->addStylesheet(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/language/" . $GLOBALS['xoopsConfig']['language'] . "/xforum.css", "text/css");


$GLOBALS['xoops']->tpl->assign('xoops_pagetitle', $xoops_pagetitle);
$GLOBALS['xoops']->tpl->assign('forum_index_title', $forum_index_title);
if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
	$online_handler = $GLOBALS['xoops']->getModuleHandler('online', 'xforum');
	$online_handler->init();
    $GLOBALS['xoops']->tpl->assign('online', $online_handler->show_online());
}

/* display forum stats */
$GLOBALS['xoops']->tpl->assign(array(
	"lang_welcomemsg" => sprintf(_MD_WELCOME, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)), 
	"total_topics" => get_total_topics(), 
	"total_posts" => get_total_posts(), 
	"lang_lastvisit" => sprintf(_MD_LASTVISIT,formatTimestamp($last_visit)), 
	"lang_currenttime" => sprintf(_MD_TIMENOW,formatTimestamp(time(),"m"))));

$GLOBALS['forum_handler'] = $GLOBALS['xoops']->getModuleHandler('forum', 'xforum');
$xforums_obj = $GLOBALS['forum_handler']->getForumsByCategory(array_keys($categories), "access");
$xforums_array = $GLOBALS['forum_handler']->display($xforums_obj);
unset($xforums_obj);

if(count($xforums_array)>0){
    foreach ($xforums_array[0] as $parent => $xforum) {
        if (isset($xforums_array[$xforum['forum_id']])) {
            $xforum['subforum'] = $xforums_array[$xforum['forum_id']];
        }
        $xforumsByCat[$xforum['forum_cid']][] = $xforum;
    }
}

$category_array = array();
$cat_order = array();
$toggles = forum_getcookie('G', true);

foreach(array_keys($categories) as $id){
    $xforums = array();

    $onecat = $categories[$id];
    
    $catid="cat_".$onecat->getVar('cat_id');
	$catshow = (count($toggles)>0)?((in_array($catid, $toggles)) ? true : false):false;
	$display = ($catshow) ? 'none;'  :  'block;';
	$display_icon  = ($catshow) ? 'images/plus.png' : 'images/minus.png' ;

    if (isset($xforumsByCat[$onecat->getVar('cat_id')])) {
        $xforums = $xforumsByCat[$onecat->getVar('cat_id')];
    }

	$cat_description = $onecat->getVar('cat_description');
	$cat_description = $GLOBALS['myts']->undoHtmlSpecialChars($cat_description);
	$cat_sponsor = array();
    @list($url, $title) = array_map("trim",preg_split("/ /", $onecat->getVar('cat_url'), 2));
    if(empty($title)) $title = $url;
    $title = $GLOBALS['myts']->htmlSpecialChars($title);
	if(!empty($url)) $cat_sponsor = array("title"=>$title, "link"=>formatURL($url));
	if($onecat->getVar('cat_image') &&	$onecat->getVar('cat_image') != "blank.gif"){
		$cat_image = XOOPS_URL."/modules/" . $GLOBALS['xforumModule']->getVar("dirname") . "/images/category/" . $onecat->getVar('cat_image');
	}else{
		$cat_image = "";
	}
    $category_array[] = array(
    	'cat_order' => $onecat->getVar('cat_order'),
    	'cat_id' => $onecat->getVar('cat_id'),
	    'cat_title' => $onecat->getVar('cat_title'),
	    'cat_image' => $cat_image,
	    'cat_sponsor' => $cat_sponsor,
	    'cat_description' => $GLOBALS['myts']->displayTarea($cat_description, 1),
        'cat_display' => $display,
	    'cat_display_icon' => $display_icon,
    	'cat_url' => $onecat->getURL(),
	    'forums' => $xforums
    	);
    $cat_order[] = $onecat->getVar('cat_order');
}
unset($categories);

$GLOBALS['xoops']->tpl->assign_by_ref("categories", $category_array);
$GLOBALS['xoops']->tpl->assign("subforum_display", $GLOBALS['xforumModuleConfig']['subforum_display']);
$GLOBALS['xoops']->tpl->assign('mark_read', XOOPS_URL."/modules/xforum/index.php?mark_read=1");
$GLOBALS['xoops']->tpl->assign('mark_unread', XOOPS_URL."/modules/xforum/index.php?mark_read=2");

$GLOBALS['xoops']->tpl->assign('all_link', XOOPS_URL."/modules/xforum/viewall.php");
$GLOBALS['xoops']->tpl->assign('post_link', XOOPS_URL."/modules/xforum/viewpost.php");
$GLOBALS['xoops']->tpl->assign('newpost_link', XOOPS_URL."/modules/xforum/viewpost.php?type=new");
$GLOBALS['xoops']->tpl->assign('digest_link', XOOPS_URL."/modules/xforum/viewall.php?type=digest");
$GLOBALS['xoops']->tpl->assign('unreplied_link', XOOPS_URL."/modules/xforum/viewall.php?type=unreplied");
$GLOBALS['xoops']->tpl->assign('unread_link', XOOPS_URL."/modules/xforum/viewall.php?type=unread");
$GLOBALS['xoops']->tpl->assign('deleted_link', XOOPS_URL."/modules/xforum/viewall.php?type=deleted");
$GLOBALS['xoops']->tpl->assign('pending_link', XOOPS_URL."/modules/xforum/viewall.php?type=pending");
$GLOBALS['xoops']->tpl->assign('down',forum_displayImage($GLOBALS['xforumImage']['doubledown']));
$GLOBALS['xoops']->tpl->assign('menumode',$menumode);
$GLOBALS['xoops']->tpl->assign('menumode_other',$menumode_other);

$GLOBALS['isadmin'] = forum_isAdmin();
$GLOBALS['xoops']->tpl->assign('viewer_level', ($isadmin)?2:(is_object($GLOBALS['xoopsUser'])?1:0) );
$mode = (!empty($_GET['mode'])) ? intval($_GET['mode']) : 0;
$GLOBALS['xoops']->tpl->assign('mode', $mode );

$GLOBALS['xoops']->tpl->assign('viewcat', $viewcat);
$GLOBALS['xoops']->tpl->assign('version', $GLOBALS['xforumModule']->getVar("version"));

/* To be removed */
if ( $GLOBALS['isadmin'] ) {
    $GLOBALS['xoops']->tpl->assign('forum_index_cpanel',array("link"=>"admin/index.php", "name"=>_MD_ADMINCP));
}

if ($GLOBALS['xforumModuleConfig']['rss_enable'] == 1) {
    $GLOBALS['xoops']->tpl->assign("rss_enable",1);
    $GLOBALS['xoops']->tpl->assign("rss_button", forum_displayImage($GLOBALS['xforumImage']['rss'], 'RSS feed'));
}
$GLOBALS['xoops']->tpl->assign(array(
	"img_hotfolder" => forum_displayImage($GLOBALS['xforumImage']['newposts_forum']),
	"img_folder" => forum_displayImage($GLOBALS['xforumImage']['folder_forum']),
	"img_locked_nonewposts" => forum_displayImage($GLOBALS['xforumImage']['locked_forum']),
	"img_locked_newposts" => forum_displayImage($GLOBALS['xforumImage']['locked_forum_newposts']),
	'img_subforum' => forum_displayImage($GLOBALS['xforumImage']['subforum'])));
include_once XOOPS_ROOT_PATH.'/footer.php';
?>