<?php

// $Id$

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
require_once(XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php');
if ( !defined('FORUM_NOTIFY_ITEMINFO') ) {
define('FORUM_NOTIFY_ITEMINFO', 1);

function forum_notify_iteminfo($category, $item_id)
{
	$module_handler =& xoops_gethandler('module');
	$module =& $module_handler->getByDirname('xforum');

	if ($category=='global') {
		$item['name'] = '';
		$item['url'] = '';
		return $item;
	}
	$item_id = intval($item_id);

	global $xoopsDB;
	if ($category=='forum') {
		// Assume we have a valid forum id
		$sql = 'SELECT forum_name FROM ' . $xoopsDB->prefix('xf_forums') . ' WHERE forum_id = '.$item_id;
		if (!$result = $xoopsDB->query($sql)){
			  redirect_header("index.php", 2, _MD_ERRORFORUM);
    		exit();
		}
		$result_array = $xoopsDB->fetchArray($result);
		$item['name'] = $result_array['forum_name'];
		$item['url'] = XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/viewforum.php?forum=' . $item_id;
		return $item;
	}

	if ($category=='thread') {
		// Assume we have a valid topid id
		$sql = 'SELECT t.topic_title,f.forum_id,f.forum_name FROM '.$xoopsDB->prefix('xf_topics') . ' t, ' . $xoopsDB->prefix('xf_forums') . ' f WHERE t.forum_id = f.forum_id AND t.topic_id = '. $item_id . ' limit 1';
		if (!$result = $xoopsDB->query($sql)){
			  redirect_header("index.php", 2, _MD_ERROROCCURED);
    		exit();
		}
		$result_array = $xoopsDB->fetchArray($result);
		$item['name'] = $result_array['topic_title'];
		$item['url'] = XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/viewtopic.php?forum=' . $result_array['forum_id'] . '&topic_id=' . $item_id;
		return $item;
	}

	if ($category=='post') {
		// Assume we have a valid post id
		$sql = 'SELECT subject,topic_id,forum_id FROM ' . $xoopsDB->prefix('xf_posts') . ' WHERE post_id = ' . $item_id . ' LIMIT 1';
		if (!$result = $xoopsDB->query($sql)){
			  redirect_header("index.php", 2, _MD_ERROROCCURED);
    		exit();
		}
		$result_array = $xoopsDB->fetchArray($result);
		$item['name'] = $result_array['subject'];
		$item['url'] = XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/viewtopic.php?forum= ' . $result_array['forum_id'] . '&amp;topic_id=' . $result_array['topic_id'] . '#forumpost' . $item_id;
		return $item;
	}
}
}
?>
