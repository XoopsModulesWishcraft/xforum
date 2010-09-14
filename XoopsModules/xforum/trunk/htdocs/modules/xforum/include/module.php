<?php
// $Id: module.php,v 4.04 2008/06/05 16:23:50 wishcraft Exp $
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
if(defined("XOOPS_MODULE_XFORUM_FUCTIONS")) exit();
define("XOOPS_MODULE_XFORUM_FUCTIONS", 1);

@include_once XOOPS_ROOT_PATH.'/modules/xforum/include/plugin.php';
include_once XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php';

forum_load_object();


function xoops_module_update_xforum(&$module, $oldversion = null) 
{
	$xforumConfig = forum_load_config();
	
    //$oldversion = $module->getVar('version');
    //$oldconfig = $module->getVar('hasconfig');
    // xforum 1.0 -- no config
    //if (empty($oldconfig)) {
    if ($oldversion == 100) {
	    include_once dirname(__FILE__)."/module.v100.php";
	    xoops_module_update_XFORUM_v100($module);
    }
    
    // xforum 2.* and X-Forum 1.*
    // change group permission name
    // change forum moderators
    if ($oldversion < 220) {
	    include_once dirname(__FILE__)."/module.v220.php";
	    xoops_module_update_XFORUM_v220($module);
    }
    
    if ($oldversion < 230) {
        $GLOBALS['xoopsDB']->queryFromFile(XOOPS_ROOT_PATH."/modules/xforum/sql/upgrade_230.sql");
		//$module->setErrors("xf_moderates table inserted");
    }

    if ($oldversion < 304) {
        $GLOBALS['xoopsDB']->queryFromFile(XOOPS_ROOT_PATH."/modules/xforum/sql/mysql.304.sql");
    }
    
	if ($oldversion < 411) {
        $GLOBALS['xoopsDB']->queryF("ALTER TABLE " . $GLOBALS['xoopsDB']->prefix('xf_posts') . " ADD COLUMN (`tags` VARCHAR(255) DEFAULT '')");
    }
	
	if ($oldversion < 545) {
        $GLOBALS['xoopsDB']->queryF("CREATE TABLE `" . $GLOBALS['xoopsDB']->prefix('xf_extras') . "` (
  `post_id` int(13) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $GLOBALS['xoopsDB']->queryF("CREATE TABLE `" . $GLOBALS['xoopsDB']->prefix('xf_field') . "` (
  `field_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` mediumtext,
  `field_type` varchar(30) NOT NULL DEFAULT '',
  `field_valuetype` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `field_name` varchar(255) NOT NULL DEFAULT '',
  `field_title` varchar(255) NOT NULL DEFAULT '',
  `field_description` text,
  `field_required` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_maxlength` smallint(6) unsigned NOT NULL DEFAULT '0',
  `field_weight` smallint(6) unsigned NOT NULL DEFAULT '0',
  `field_default` text,
  `field_notnull` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_edit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_show` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_config` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `field_options` text,
  PRIMARY KEY (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $GLOBALS['xoopsDB']->queryF("CREATE TABLE `" . $GLOBALS['xoopsDB']->prefix('xf_visibility') . "` (
  `field_id` int(12) unsigned NOT NULL DEFAULT '0',
  `user_group` smallint(5) unsigned NOT NULL DEFAULT '0',
  `profile_group` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`,`user_group`,`profile_group`),
  KEY `visible` (`user_group`,`profile_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    }
	
	if(!empty($xforumConfig["syncOnUpdate"])){
		forum_synchronization();
	}
	
	return true;
}

function xoops_module_pre_update_xforum(&$module) 
{
	return forum_setModuleConfig($module, true);
}

function xoops_module_pre_install_xforum(&$module)
{
	$mod_tables = $module->getInfo("tables");
	foreach($mod_tables as $table){
		$GLOBALS["xoopsDB"]->queryF("DROP TABLE IF EXISTS ".$GLOBALS["xoopsDB"]->prefix($table).";");
	}
	return forum_setModuleConfig($module);
}

function xoops_module_install_xforum(&$module)
{
	/* Create a test category */
	$category_handler =& xoops_getmodulehandler('category', 'xforum');
	$category =& $category_handler->create();
    $category->setVar('cat_title', _MI_XFORUM_INSTALL_CAT_TITLE, true);
    $category->setVar('cat_image', "", true);
    $category->setVar('cat_order', 1);
    $category->setVar('cat_description', _MI_XFORUM_INSTALL_CAT_DESC, true);
    $category->setVar('cat_url', "http://xoops.org XOOPS", true);
    if (!$cat_id = $category_handler->insert($category)) {
        return true;
    }

    /* Create a forum for test */
	$forum_handler =& xoops_getmodulehandler('forum', 'xforum');
    $xforum =& $forum_handler->create();
    $xforum->setVar('forum_name', _MI_XFORUM_INSTALL_FORUM_NAME, true);
    $xforum->setVar('forum_desc', _MI_XFORUM_INSTALL_FORUM_DESC, true);
    $xforum->setVar('forum_order', 1);
    $xforum->setVar('forum_moderator', array());
    $xforum->setVar('parent_forum', 0);
    $xforum->setVar('cat_id', $cat_id);
    $xforum->setVar('forum_type', 0);
    $xforum->setVar('allow_html', 0);
    $xforum->setVar('allow_sig', 1);
    $xforum->setVar('allow_polls', 0);
    $xforum->setVar('allow_subject_prefix', 1);
    //$xforum->setVar('allow_attachments', 1);
    $xforum->setVar('attach_maxkb', 100);
    $xforum->setVar('attach_ext', "zip|jpg|gif");
    $xforum->setVar('hot_threshold', 20);
    $forum_id = $forum_handler->insert($xforum);
	
    /* Set corresponding permissions for the category and the forum */
    $module_id = $module->getVar("mid") ;
    $gperm_handler =& xoops_gethandler("groupperm");
    $groups_view = array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS);
    $groups_post = array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS);
	$post_items = array('post', 'reply', 'edit', 'delete', 'addpoll', 'vote', 'attach', 'noapprove');
    foreach ($groups_view as $group_id) {
        $gperm_handler->addRight("category_access", $cat_id, $group_id, $module_id);
        $gperm_handler->addRight("forum_access", $forum_id, $group_id, $module_id);
        $gperm_handler->addRight("forum_view", $forum_id, $group_id, $module_id);
    }
    foreach ($groups_post as $group_id) {
	    foreach($post_items as $item){
        	$gperm_handler->addRight("forum_".$item, $forum_id, $group_id, $module_id);
    	}
    }
    
    /* Create a test post */
	$post_handler =& xoops_getmodulehandler('post', 'xforum');
	$xforumpost =& $post_handler->create();
    $xforumpost->setVar('poster_ip', forum_getIP());
    $xforumpost->setVar('uid', $GLOBALS["xoopsUser"]->getVar("uid"));
	$xforumpost->setVar('approved', 1);
    $xforumpost->setVar('forum_id', $forum_id);
    $xforumpost->setVar('subject', _MI_XFORUM_INSTALL_POST_SUBJECT, true);
    $xforumpost->setVar('dohtml', 0);
    $xforumpost->setVar('dosmiley', 1);
    $xforumpost->setVar('doxcode', 1);
    $xforumpost->setVar('dobr', 1);
    $xforumpost->setVar('icon', "", true);
    $xforumpost->setVar('attachsig', 1);
    $xforumpost->setVar('post_time', time());
    $xforumpost->setVar('post_text', _MI_XFORUM_INSTALL_POST_TEXT, true);
    $postid = $post_handler->insert($xforumpost);
        
    return true;
}
 
function forum_setModuleConfig(&$module, $isUpdate = false) 
{
	return true;
}
?>