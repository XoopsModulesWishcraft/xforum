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
    redirect_header($url, 2, _MD_ALL_FORUM_MARKED.' '.$markresult);
}

$viewcat = @intval($_GET['cat']);
$category_handler =& xoops_getmodulehandler('category', 'xforum');

$categories = array();
if (!$viewcat) {
	$categories = $category_handler->getAllCats('access', true);
    $forum_index_title = "";
	$xoops_pagetitle = $GLOBALS['xforumModule']->getVar('name');
}else {
    $category_obj =& $category_handler->get($viewcat);
	if($category_handler->getPermission($category_obj)) {
		$categories[$viewcat] =& $category_obj;
	}
    $forum_index_title = sprintf(_MD_XFORUMINDEX, htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES));
	$xoops_pagetitle = $category_obj->getVar('cat_title') . " [" .$GLOBALS['xforumModule']->getVar('name')."]";
}

if(count($categories) == 0){
    redirect_header(XOOPS_URL, 2, _MD_NORIGHTTOACCESS);
    exit();
}

if ($GLOBALS['xoopsModuleConfig']['htaccess']&&empty($_GET)) {
	if ($viewcat<>0) {
		$category =& $category_handler->get($viewcat);
		$url = $category->getURL();
	} else {
		$url = XOOPS_URL.'/'.$GLOBALS['xoopsModuleConfig']['baseurl'].'/';
	}
	if (!strpos($url, $_SERVER['REQUEST_URI'])) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header('Location: '.$url);
		exit(0);
	}
}

/* rss feed */

$xoopsOption['template_main']= 'xforum_index.html';
$xoopsOption['xoops_pagetitle']= $xoops_pagetitle;

include XOOPS_ROOT_PATH."/header.php";

if(!empty($GLOBALS['xforumModuleConfig']['rss_enable'])){
	$GLOBALS['xoTheme']->addLink('alternate', XOOPS_URL.'/modules/'.$GLOBALS['xforumModule']->getVar('dirname').'/rss.php', array('type'=>"application/rss+xml", 'title' => $GLOBALS['xoopsModule']->getVar('name')));
}
if(!empty($GLOBALS['xforumModuleConfig']['pngforie_enabled'])){
	$GLOBALS['xoTheme']->addStylesheet(null,"text/css",'img {behavior:url("'.XOOPS_URL.'/modules/xforum/include/pngbehavior.htc");}');
}
$GLOBALS['xoTheme']->addScript(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/js/xforum_toggle.js");
$GLOBALS['xoTheme']->addScript(null, 'text/javascript', 'var toggle_cookie="'.$forumCookie['prefix'].'G'.'";');
if($menumode==2){
	$GLOBALS['xoTheme']->addStylesheet(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/templates/forum_menu_hover.css", "text/css");
	$GLOBALS['xoTheme']->addStylesheet(null,"text/css",'body {behavior:url("'.XOOPS_URL.'/modules/xforum/include/xforum.htc");}');
}
if($menumode==1){
	$GLOBALS['xoTheme']->addStylesheet(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/templates/forum_menu_click.css", "text/css");
	$GLOBALS['xoTheme']->addScript(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/include/js/forum_menu_click.js", 'text/javascript');
}
$GLOBALS['xoTheme']->addStylesheet(XOOPS_URL."/modules/".$GLOBALS['xforumModule']->getVar("dirname")."/templates/xforum.css", "text/css");


$xoopsTpl->assign('xoops_pagetitle', $xoops_pagetitle);
$xoopsTpl->assign('forum_index_title', $forum_index_title);
if ($GLOBALS['xforumModuleConfig']['wol_enabled']){
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
	    'cat_description' => $myts->displayTarea($cat_description, 1),
        'cat_display' => $display,
	    'cat_display_icon' => $display_icon,
    	'cat_url' => $onecat->getURL(),
	    'forums' => $xforums
    	);
    $cat_order[] = $onecat->getVar('cat_order');
}
unset($categories);

$xoopsTpl->assign_by_ref("categories", $category_array);
$xoopsTpl->assign("subforum_display", $GLOBALS['xforumModuleConfig']['subforum_display']);
$xoopsTpl->assign('mark_read', XOOPS_URL."/modules/xforum/index.php?mark_read=1");
$xoopsTpl->assign('mark_unread', XOOPS_URL."/modules/xforum/index.php?mark_read=2");

$xoopsTpl->assign('all_link', XOOPS_URL."/modules/xforum/viewall.php");
$xoopsTpl->assign('post_link', XOOPS_URL."/modules/xforum/viewpost.php");
$xoopsTpl->assign('newpost_link', XOOPS_URL."/modules/xforum/viewpost.php?type=new");
$xoopsTpl->assign('digest_link', XOOPS_URL."/modules/xforum/viewall.php?type=digest");
$xoopsTpl->assign('unreplied_link', XOOPS_URL."/modules/xforum/viewall.php?type=unreplied");
$xoopsTpl->assign('unread_link', XOOPS_URL."/modules/xforum/viewall.php?type=unread");
$xoopsTpl->assign('deleted_link', XOOPS_URL."/modules/xforum/viewall.php?type=deleted");
$xoopsTpl->assign('pending_link', XOOPS_URL."/modules/xforum/viewall.php?type=pending");
$xoopsTpl->assign('down',forum_displayImage($xforumImage['doubledown']));
$xoopsTpl->assign('menumode',$menumode);
$xoopsTpl->assign('menumode_other',$menumode_other);

$isadmin = forum_isAdmin();
$xoopsTpl->assign('viewer_level', ($isadmin)?2:(is_object($xoopsUser)?1:0) );
$mode = (!empty($_GET['mode'])) ? intval($_GET['mode']) : 0;
$xoopsTpl->assign('mode', $mode );

$xoopsTpl->assign('viewcat', $viewcat);
$xoopsTpl->assign('version', $GLOBALS['xforumModule']->getVar("version"));

/* To be removed */
if ( $isadmin ) {
    $xoopsTpl->assign('forum_index_cpanel',array("link"=>"admin/index.php", "name"=>_MD_ADMINCP));
}

if ($GLOBALS['xforumModuleConfig']['rss_enable'] == 1) {
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