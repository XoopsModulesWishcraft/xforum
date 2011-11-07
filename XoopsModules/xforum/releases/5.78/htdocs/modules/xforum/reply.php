<?php

// $Id$

include 'header.php';
error_reporting(E_ALL);
foreach (array('forum', 'topic_id', 'post_id', 'order', 'pid', 'start') as $getint) {
    ${$getint} = isset($_GET[$getint]) ? intval($_GET[$getint]) : 0;
}
$viewmode = (isset($_GET['viewmode']) && $_GET['viewmode'] != 'flat') ? 'thread' : 'flat';
if ( empty($forum) ) {
    redirect_header(XOOPS_URL."/index.php", 2, _MD_ERRORFORUM);
    exit();
} elseif ( empty($topic_id) ) {
    redirect_header("viewforum.php?forum=$forum", 2, _MD_ERRORTOPIC);
    exit();
} 

$forum_handler =& xoops_getmodulehandler('forum', 'xforum');
$topic_handler =& xoops_getmodulehandler('topic', 'xforum');
$post_handler =& xoops_getmodulehandler('post', 'xforum');

if ( !$topic_id && !$post_id ) {
	$redirect = empty($forum)?"index.php":'viewforum.php?forum='.$forum;
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
}

if ( empty($post_id) ) {
	$post_id = $topic_handler->getTopPostId($topic_id);
}
$forumpost =& $post_handler->get($post_id);
$topic_id = $forumpost->getVar("topic_id");
$forum = $forumpost->getVar("forum_id");

$forum_obj = $forum_handler->get($forum);
if (!$forum_handler->getPermission($forum_obj)){
    redirect_header(XOOPS_URL."/index.php", 2, _MD_NORIGHTTOACCESS);
    exit();
}

$topic_status = $topic_handler->get($topic_id,'topic_status');
if ( !$topic_handler->getPermission($forum_obj, $topic_status, 'reply')){
	redirect_header(XOOPS_URL."/modules/xforum/viewtopic.php?topic_id=$topic_id&amp;order=$order&amp;viewmode=$viewmode&amp;pid=$pid&amp;forum=".$forum_obj->getVar('forum_id'), 2, _MD_NORIGHTTOREPLY);
    exit();
}

if ($GLOBALS['xoopsModuleConfig']['htaccess']) {
	$url = $forum_obj->getREPLYURL($forumpost);
	if (!strpos($url, $_SERVER['REQUEST_URI'])) {
		header( "HTTP/1.1 301 Moved Permanently" ); 
		header('Location: '.$url);
		exit(0);
	}
}
if ($xoopsModuleConfig['wol_enabled']){
	$online_handler =& xoops_getmodulehandler('online', 'xforum');
	$online_handler->init($forum_obj);
}

 
	// Disable cache
	$xoopsConfig["module_cache"][$xoopsModule->getVar("mid")] = 0;
    include XOOPS_ROOT_PATH.'/header.php';

	$myts =& MyTextSanitizer::getInstance();
	$isadmin = forum_isAdmin($forum_obj);
    $forumpostshow =& $post_handler->getByLimit($topic_id,5);

    if($forumpost->getVar('uid')) {
	    $r_name =forum_getUnameFromId( $forumpost->getVar('uid'), $xoopsModuleConfig['show_realname'] );
    }else{
	    $poster_name = $forumpost->getVar('poster_name');
    	$r_name = (empty($poster_name))?$xoopsConfig['anonymous']:$myts->htmlSpecialChars($poster_name);
	}

    $r_subject=$forumpost->getVar('subject', "E");

    if (!preg_match("/^(Re|"._MD_RE."):/i",$r_subject)) {
        $subject = _MD_RE.': '.$r_subject;
    } else {
        $subject = $r_subject;
    }

    $q_message = $forumpost->getVar('post_text',"e");

    if((!$xoopsModuleConfig['enable_karma']||!$forumpost->getVar('post_karma'))
    && (!$xoopsModuleConfig['allow_require_reply'] ||!$forumpost->getVar('require_reply')))
    {
	    if (isset($_GET['quotedac']) && $_GET['quotedac'] == 1) {
	        $message = "[quote]\n";
	        $message .= sprintf(_MD_USERWROTE,$r_name);
	        $message .= "\n".$q_message."[/quote]";
        	$hidden = "";
	    } else {
	        $hidden = "[quote]\n";
	        $hidden .= sprintf(_MD_USERWROTE,$r_name);
	        $hidden .= "\n".$q_message."[/quote]";
	        $message = "";
	    }
    }else{
        $hidden = "";
        $message = "";
    }

    echo "<br />";
    $pid=$post_id;
    unset($post_id);
    $topic_id=$forumpost->getVar('topic_id');
    $isreply =1;
    $istopic = 0;
    $dohtml = 1;
    $dosmiley = 1;
    $doxcode = 1;
    $dobr = 1;
    $subject_pre="";
    $icon = '';
    $attachsig = (is_object($xoopsUser) && $xoopsUser->getVar('attachsig')) ? 1 : 0;
    $topic_id=$forumpost->getVar('topic_id');
    $post_karma = 0;
    $require_reply = 0;

    if ($xoopsModuleConfig['disc_show'] == 2 or $xoopsModuleConfig['disc_show'] == 3 ){
	    echo "<div class=\"confirmMsg\">".$xoopsModuleConfig['disclaimer']."</div><br clear=\"both\">";
    }

    include 'include/forumform.inc.php';

	$karma_handler =& xoops_getmodulehandler('karma', 'xforum');
	$user_karma = $karma_handler->getUserKarma();

    foreach ($forumpostshow as $eachpost) {
    	// Sorry, in order to save queries, we have to hide the non-open post_text even if you have replied or have adequate karma, even an admin.
	    if( $xoopsModuleConfig['enable_karma'] && $eachpost->getVar('post_karma') > 0 ) {
	        $p_message = sprintf(_MD_KARMA_REQUIREMENT, "***", $eachpost->getVar('post_karma'))."</div>";
	    }elseif( $xoopsModuleConfig['allow_require_reply'] && $eachpost->getVar('require_reply') ) {
	        $p_message = _MD_REPLY_REQUIREMENT;
	    }else{
		    $p_message = $eachpost->getVar('post_text');
	    }

    	$isadmin = 0;
    	if($eachpost->getVar('uid')) {
	    	$p_name =forum_getUnameFromId( $eachpost->getVar('uid'), $xoopsModuleConfig['show_realname'] );
			if (forum_isAdmin($forum_obj, $eachpost->getVar('uid'))) $isadmin = 1;
    	}else{
	    	$poster_name = $eachpost->getVar('poster_name');
    		$p_name = (empty($poster_name))?$xoopsConfig['anonymous']:$myts->htmlSpecialChars($poster_name);
		}
		$p_date = formatTimestamp($eachpost->getVar('post_time'));
		/*
	    if( $isadmin && $xoopsModuleConfig['allow_moderator_html']){
	    	$p_subject = $myts->undoHtmlSpecialChars($eachpost->getVar('subject'));
		}else{
	    	$p_subject = $eachpost->getVar('subject');
		}
		*/
	    $p_subject = $eachpost->getVar('subject');
    	$p_content = _MD_BY." <strong> ".$p_name." </strong> "._MD_ON." <strong> ".$p_date."</strong><br /><br />";
    	$p_content .= $p_message;

	    echo "<table cellpadding='4' cellspacing='1' width='98%' class='outer'><tr><td class='head'>".$p_subject."</td></tr>";
	    echo "<tr><td><br />".$p_content."<br /></td></tr></table>";
	}

    include XOOPS_ROOT_PATH.'/footer.php';
?>