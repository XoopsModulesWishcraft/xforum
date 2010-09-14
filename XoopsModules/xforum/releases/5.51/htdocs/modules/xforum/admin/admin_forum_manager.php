<?php
// $Id: admin_forum_manager.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000 XOOPS.org                           //
// <http://www.chronolabs.org/>                             //
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License 2.0 as published by //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
// //
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
// //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
// //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.chronolabs.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
include 'admin_header.php';
include XOOPS_ROOT_PATH . "/class/xoopstree.php";
//include_once XOOPS_ROOT_PATH . "/class/pagenav.php";

$op = '';
$confirm = '';

if (isset($_GET['op'])) $op = $_GET['op'];
if (isset($_POST['op'])) $op = $_POST['op'];
if (isset($_POST['default'])) $op = 'default';
if (isset($_GET['forum'])) $xforum = $_GET['forum'];
if (isset($_POST['forum'])) $xforum = $_POST['forum'];

$forum_handler =& xoops_getmodulehandler('forum', 'xforum');
/**
 * newForum()
 *
 * @param integer $catid
 * @return
 */
function newForum($parent_forum = 0)
{
    editForum(null, $parent_forum);
}

/**
 * editForum()
 *
 * @param integer $catid
 * @return
 */
function editForum($ff, $parent_forum = 0)
{
    global $myts, $xoopsDB, $xoopsModule, $forum_handler;

    //$forum_handler = &xoops_getmodulehandler('forum', 'xforum');
    if (!is_object($ff)) {
        $ff =& $forum_handler->create();
        $new = true;
        $xforum = 0;
    } else {
        $xforum = $ff->getVar('forum_id');
        $new = false;
    }
    if ($parent_forum > 0) {
        $pf =& $forum_handler->get($parent_forum);
    }

    $mytree = new XoopsTree($xoopsDB->prefix("xf_categories"), "cat_id", "0");

	require_once XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar("dirname")."/class/xoopsformloader.php";
    if ($xforum) {
        $sform = new XoopsThemeForm(_AM_XFORUM_EDITTHISFORUM . " " . $ff->getVar('forum_name'), "op", xoops_getenv('PHP_SELF'));
    } else {
        $sform = new XoopsThemeForm(_AM_XFORUM_CREATENEWFORUM, "op", xoops_getenv('PHP_SELF'));

        $ff->setVar('parent_forum', $parent_forum);
        $ff->setVar('forum_order', 0);
        $ff->setVar('forum_name', '');
        $ff->setVar('forum_desc', '');
        $ff->setVar('forum_moderator', array(1));

        $ff->setVar('forum_type', 0);
        $ff->setVar('allow_html', 1);
        $ff->setVar('allow_sig', 1);
        $ff->setVar('allow_polls', 1);
        $ff->setVar('allow_subject_prefix', 1);
        $ff->setVar('hot_threshold', 10);
        //$ff->setVar('allow_attachments', 1);
        $ff->setVar('attach_maxkb', 1000);
        $ff->setVar('attach_ext', 'zip|gif|jpg');
    }

    $sform->addElement(new XoopsFormText(_AM_XFORUM_FORUMNAME, 'forum_name', 50, 80, $ff->getVar('forum_name', 'E')), true);
    $sform->addElement(new XoopsFormDhtmlTextArea(_AM_XFORUM_FORUMDESCRIPTION, 'forum_desc', $ff->getVar('forum_desc', 'E'), 10, 60), false);

    $sform->addElement(new XoopsFormHidden('parent_forum', $ff->getVar('parent_forum')));
    if ($parent_forum == 0) {
        ob_start();
        if ($new) {
        	$mytree->makeMySelBox("cat_title", "cat_id", @$_GET['cat_id']);
        } else {
        	$mytree->makeMySelBox("cat_title", "cat_id", $ff->getVar('cat_id'));
        }
        $sform->addElement(new XoopsFormLabel(_AM_XFORUM_CATEGORY, ob_get_contents()));
        ob_end_clean();
    } else {
        $sform->addElement(new XoopsFormHidden('cat_id', $pf->getVar('cat_id')));
    }

    $sform->addElement(new XoopsFormText(_AM_XFORUM_SET_FORUMORDER, 'forum_order', 5, 10, $ff->getVar('forum_order')), false);
    $status_select = new XoopsFormSelect(_AM_XFORUM_STATE, "forum_type", $ff->getVar('forum_type'));
    $status_select->addOptionArray(array('0' => _AM_XFORUM_ACTIVE, '1' => _AM_XFORUM_INACTIVE));
    $sform->addElement($status_select);

    $allowhtml_radio = new XoopsFormRadioYN(_AM_XFORUM_ALLOWHTML, 'allow_html', $ff->getVar('allow_html'), '' . _YES . '', ' ' . _NO . '');
    $sform->addElement($allowhtml_radio);

    $allowsig_radio = new XoopsFormRadioYN(_AM_XFORUM_ALLOWSIGNATURES, 'allow_sig', $ff->getVar('allow_sig'), '' . _YES . '', ' ' . _NO . '');
    $sform->addElement($allowsig_radio);

    $allowpolls_radio = new XoopsFormRadioYN(_AM_XFORUM_ALLOWPOLLS, 'allow_polls', $ff->getVar('allow_polls'), '' . _YES . '', ' ' . _NO . '');
    $sform->addElement($allowpolls_radio);

    $allowprefix_radio = new XoopsFormRadioYN(_AM_XFORUM_ALLOW_SUBJECT_PREFIX, 'allow_subject_prefix', $ff->getVar('allow_subject_prefix'), '' . _YES . '', ' ' . _NO . '');
    $sform->addElement($allowprefix_radio);

    $sform->addElement(new XoopsFormText(_AM_XFORUM_HOTTOPICTHRESHOLD, 'hot_threshold', 5, 10, $ff->getVar('hot_threshold')), true);

    /*
    $allowattach_radio = new XoopsFormRadioYN(_AM_XFORUM_ALLOW_ATTACHMENTS, 'allow_attachments', $ff->getVar('allow_attachments'), '' . _YES . '', ' ' . _NO . '');
    $sform->addElement($allowattach_radio);
    */
    $sform->addElement(new XoopsFormText(_AM_XFORUM_ATTACHMENT_SIZE, 'attach_maxkb', 5, 10, $ff->getVar('attach_maxkb')), true);
    //$sform->addElement(new XoopsFormText(_AM_XFORUM_ALLOWED_EXTENSIONS, 'attach_ext', 50, 255, $ff->getVar('attach_ext')), true);
    $ext = $ff->getVar('attach_ext');
    $sform->addElement(new XoopsFormText(_AM_XFORUM_ALLOWED_EXTENSIONS, 'attach_ext', 50, 255, $ext), true);
   	$sform->addElement(new XoopsFormSelectUser(_AM_XFORUM_MODERATOR, 'forum_moderator', false, $ff->getVar("forum_moderator"), 5, true));

    $perm_tray = new XoopsFormElementTray(_AM_XFORUM_PERMISSIONS_TO_THIS_FORUM, '');
	$perm_checkbox = new XoopsFormCheckBox('', 'perm_template', $ff->isNew());
	$perm_checkbox->addOption(1, _AM_XFORUM_PERM_TEMPLATEAPP);
	$perm_tray->addElement($perm_checkbox);
	$perm_tray->addElement(new XoopsFormLabel('', '<a href="admin_permissions.php?action=template" target="_blank">'._AM_XFORUM_PERM_TEMPLATE.'</a>'));
    $sform->addElement($perm_tray);
   	
    $sform->addElement(new XoopsFormHidden('forum', $xforum));
    $sform->addElement(new XoopsFormHidden('op', "save"));

    $button_tray = new XoopsFormElementTray('', '');
    $button_tray->addElement(new XoopsFormButton('', '', _SUBMIT, 'submit'));

    $button_tray->addElement(new XoopsFormButton('', '', _AM_XFORUM_CLEAR, 'reset'));

    $butt_cancel = new XoopsFormButton('', '', _CANCEL, 'button');
    $butt_cancel->setExtra('onclick="history.go(-1)"');
    $button_tray->addElement($butt_cancel);

    $sform->addElement($button_tray);
    $sform->display();
}
xoops_cp_header();
switch ($op) {
    case 'moveforum':
        loadModuleAdminMenu(2, "");

        if(!empty($_POST['dest'])) {
            if (!empty($_GET['forum'])) $forum_id = intval($_GET['forum']);
            if (!empty($_POST['forum'])) $forum_id = intval($_POST['forum']);

            $dest = $_POST['dest'];
            if($dest{0}=="f"){
	            $pid = substr($dest, 1);
            	$xforum = &$forum_handler->get(intval($pid));
            	$cid = $xforum->getVar("cat_id");
            	unset($xforum);
            }else{
	            $cid = intval($dest);
	            $pid = 0;
            }
            $bMoved = 0;
            $errString = '';
            $value = "cat_id=" . $cid.", parent_forum=" . $pid;
            $sql_move = "UPDATE " . $xoopsDB->prefix('xf_forums') . " SET " . $value . " WHERE forum_id=$forum_id";
            if ($result = $xoopsDB->queryF($sql_move)){
                $bMoved = 1;
            	$sql = "UPDATE " . $xoopsDB->prefix('xf_forums') . " SET parent_forum = 0 WHERE parent_forum=$forum_id";
            	$result = $xoopsDB->queryF($sql);
            }

            if (!$bMoved) {
                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_XFORUM_MSG_ERR_FORUM_MOVED);
            } else {
                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_XFORUM_MSG_FORUM_MOVED);
            }
            exit();
        } else {
            //loadModuleAdminMenu(2, "");

            if (!empty($_POST['forum'])) $forum_id = intval($_POST['forum']);
            if (!empty($_GET['forum'])) $forum_id = intval($_GET['forum']);
            //$xforum = &$forum_handler->get($forum_id);
           
	        $box = '<select name="dest">';
            $box .= '<option value=0 selected>' . _AM_XFORUM_SELECT . '</option>';
	
			$category_handler =& xoops_getmodulehandler('category', 'xforum');
		    $categories = $category_handler->getAllCats('', true);
		    $xforums = $forum_handler->getForumsByCategory(array_keys($categories), '', false);
		
			if(count($categories)>0 && count($xforums)>0){
				foreach(array_keys($xforums) as $key){
		            $box .= "<option value=".$key."'>".$categories[$key]->getVar('cat_title')."</option>";
		            foreach ($xforums[$key] as $xforumid=>$_forum) {
		                $box .= "<option value='f".$xforumid."'>-- ".$_forum['title']."</option>";
		            }
				}
		    }
	    	unset($xforums, $categories);
            $box .= '</select>';

            echo '<form action="./admin_forum_manager.php" method="post" name="forummove" id="forummove">';
            echo '<input type="hidden" name="op" value="moveforum" />';
            echo '<input type="hidden" name="forum" value=' . $forum_id . ' />';
            echo '<table border="0" cellpadding="1" cellspacing="0" align="center" valign="top" width="95%"><tr>';
            echo '<td class="bg2" align="center"><strong>' . _AM_XFORUM_MOVETHISFORUM . '</strong></td>';
            echo '</tr>';
            echo '<tr><td class="bg1" align="center">' . $box . '</td></tr>';
            echo '<tr><td align="center"><input type="submit" name="save" value=' . _GO . ' class="button" /></td></tr>';
            echo '</table></form>';
        }
        break;

    case 'mergeforum':
        loadModuleAdminMenu(2, "");

        if (!empty($_POST['dest_forum'])) {
            if (isset($_GET['forum'])) $forum_id = intval($_GET['forum']);
            if (isset($_POST['forum'])) $forum_id = intval($_POST['forum']);

            $sql = "UPDATE " . $xoopsDB->prefix('xf_posts') . " SET forum_id=" . $_POST['dest_forum'] . " WHERE forum_id=$xforum";
            $result = $xoopsDB->queryF($sql);
            $sql = "UPDATE " . $xoopsDB->prefix('xf_topics') . " SET forum_id=" . $_POST['dest_forum'] . " WHERE forum_id=$xforum";
            $result = $xoopsDB->queryF($sql);
            $sql = "UPDATE " . $xoopsDB->prefix('xf_forums') . " SET parent_forum = 0 WHERE parent_forum=$forum_id";
            $result = $xoopsDB->queryF($sql);

            $sql = "SELECT COUNT(*) AS count FROM " . $xoopsDB->prefix('xf_posts') . " WHERE WHERE forum_id=$forum_id";
            $result = $xoopsDB->query($sql);
            list($post_count) = $xoopsDB->fetchArray($result);
            $sql = "SELECT COUNT(*) AS count FROM " . $xoopsDB->prefix('xf_topics') . " WHERE WHERE forum_id=$forum_id";
            $result = $xoopsDB->query($sql);
            list($topic_count) = $xoopsDB->fetchArray($result);

            $xforum = &$forum_handler->get($forum_id);
            $forum_handler->delete($xforum);

            if ($post_count || $topic_count) {
                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_XFORUM_MSG_ERR_FORUM_MERGED);
            } else {
                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_XFORUM_MSG_FORUM_MERGED);
            }
            exit();
        } else {
            //loadModuleAdminMenu(2, "");

            if (isset($_GET['forum'])) $forum_id = intval($_GET['forum']);
            if (isset($_POST['forum'])) $forum_id = intval($_POST['forum']);
            //$xforum = &$forum_handler->get($forum_id);
			
	        $box = '<select name="dest_forum">';
            $box .= '<option value=0 selected>' . _AM_XFORUM_SELECT . '</option>';
	
			//$category_handler =& xoops_getmodulehandler('category', 'xforum');
		    $xforums = $forum_handler->getForumsByCategory(0, '', false);
		
			if(count($xforums)>0){
				foreach(array_keys($xforums) as $key){
		            foreach ($xforums[$key] as $f=>$_forum) {
		                $box .= "<option value='".$f."'>-- ".$_forum['title']."</option>";
						if( !isset($_forum["sub"]) || count($_forum["sub"])==0) continue; 
			            foreach (array_keys($_forum["sub"]) as $s) {
			                $box .= "<option value='".$s."'>---- ".$_forum["sub"][$s]['title']."</option>";
		                }
		            }
				}
		    }
	    	unset($xforums);

            echo '<form action="./admin_forum_manager.php" method="post" name="forummove" id="forummove">';
            echo '<input type="hidden" name="op" value="mergeforum" />';
            echo '<input type="hidden" name="forum" value=' . $forum_id . ' />';
            echo '<table border="0" cellpadding="1" cellspacing="0" align="center" valign="top" width="95%"><tr>';
            echo '<td class="bg2" align="center"><strong>' . _AM_XFORUM_MERGETHISFORUM . '</strong></td>';
            echo '</tr>';
            echo '<tr><td class="bg1" align="center">' . _AM_XFORUM_MERGETO_FORUM . '</td></tr>';
            echo '<tr><td class="bg1" align="center">' . $box . '</td></tr>';
            echo '<tr><td align="center"><input type="submit" name="save" value=' . _GO . ' class="button" /></td></tr>';
            echo '</form></table>';
        }
        break;

    case 'sync':
        loadModuleAdminMenu(5, _AM_XFORUM_SYNCFORUM);
        if (isset($_POST['submit'])) {
			forum_synchronization();
			/*
			$topic_handler =& xoops_getmodulehandler('topic', 'xforum');
			$topic_handler->synchronization();
			*/
            redirect_header("./index.php", 1, _AM_XFORUM_SYNCHING);
            exit();
        } else {
            //loadModuleAdminMenu(3, _AM_XFORUM_SYNCFORUM);
            echo '<fieldset><legend style="font-weight: bold; color: #900;">' . _AM_XFORUM_SYNCFORUM . '</legend>';
            echo '<br /><br /><table width="100%" border="0" cellspacing="1" class="outer"><tr><td class="odd">';
            echo '<table border="0" cellpadding="1" cellspacing="1" width="100%">';
            echo '<tr class="bg3" align="left">';
            echo '<td>' . _AM_XFORUM_CLICKBELOWSYNC . '</td>';
            echo '</tr>';
            echo '<tr class="bg1" align="center">';
            echo '<td><form action="admin_forum_manager.php" method="post">';
            echo '<input type="hidden" name="op" value="sync"><input type="submit" name="submit" value=' . _AM_XFORUM_SYNCFORUM . ' /></form></td>';
            echo '</td>';
            echo '</tr>';
            echo '</table></td></tr></table>';
        }

        echo "</fieldset>";
        break;

    case "save":

        if ($xforum) {
            $ff = &$forum_handler->get($xforum);
            $message = _AM_XFORUM_FORUMUPDATE;
        } else {
            $ff = &$forum_handler->create();
            $message = _AM_XFORUM_FORUMCREATED;
        }

        $ff->setVar('forum_name', $_POST['forum_name']);
        $ff->setVar('forum_desc', $_POST['forum_desc']);
        $ff->setVar('forum_order', $_POST['forum_order']);
        $ff->setVar('forum_moderator', isset($_POST['forum_moderator'])?$_POST['forum_moderator']:array());
        $ff->setVar('parent_forum', @$_POST['parent_forum']);
        $ff->setVar('cat_id', $_POST['cat_id']);
        $ff->setVar('forum_type', @$_POST['forum_type']);
        $ff->setVar('allow_html', @$_POST['allow_html']);
        $ff->setVar('allow_sig', @$_POST['allow_sig']);
        $ff->setVar('allow_polls', $_POST['allow_polls']);
        $ff->setVar('allow_subject_prefix', @$_POST['allow_subject_prefix']);
        //$ff->setVar('allow_attachments', $_POST['allow_attachments']);
        $ff->setVar('attach_maxkb', $_POST['attach_maxkb']);
        $ff->setVar('attach_ext', $_POST['attach_ext']);
        $ff->setVar('hot_threshold', $_POST['hot_threshold']);
        if ($forum_handler->insert($ff)) {
	        if(!empty($_POST["perm_template"])){
			    $groupperm_handler = &xoops_getmodulehandler('permission', 'xforum');
			    $perm_template = $groupperm_handler->getTemplate();
				//$groupperm_handler =& xoops_gethandler('groupperm');
			    $member_handler =& xoops_gethandler('member');
			    $glist =& $member_handler->getGroupList();
				$perms = array_map("trim",explode(',', FORUM_PERM_ITEMS));
				foreach(array_keys($glist) as $group){
				    foreach($perms as $perm){
					    $perm = "forum_".$perm;
						$ids = $groupperm_handler->getItemIds($perm, $group, $xoopsModule->getVar("mid"));
						if(!in_array($ff->getVar("forum_id"), $ids)){
							if(empty($perm_template[$group][$perm])){
								$groupperm_handler->deleteRight($perm, $ff->getVar("forum_id"), $group, $xoopsModule->getVar("mid"));
							}else{
								$groupperm_handler->addRight($perm, $ff->getVar("forum_id"), $group, $xoopsModule->getVar("mid"));
							}
						}
				    }
				}
	        }
            redirect_header("admin_forum_manager.php?op=mod&amp;forum=" . $ff->getVar('forum_id') . "", 2, $message);
            exit();
        } else {
            redirect_header("admin_forum_manager.php?op=mod&amp;forum=" . $ff->getVar('forum_id') . "", 2, _AM_XFORUM_FORUM_ERROR);
            exit();
        }

    case "mod":
        $ff =& $forum_handler->get($xforum);
        loadModuleAdminMenu(2, _AM_XFORUM_EDITTHISFORUM . $ff->getVar('forum_name'));
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_EDITTHISFORUM . "</legend>";
        echo"<br /><br /><table width='100%' border='0' cellspacing='1' class='outer'><tr><td class='odd'>";

        editForum($ff);

        echo"</td></tr></table>";
        echo "</fieldset>";
        break;

    case "del":

        if (isset($_POST['confirm']) != 1) {
            xoops_confirm(array('op' => 'del', 'forum' => intval($_GET['forum']), 'confirm' => 1), 'admin_forum_manager.php', _AM_XFORUM_TWDAFAP);
            break;
        } else {
            $ff = &$forum_handler->get($_POST['forum']);
            $forum_handler->delete($ff);
            redirect_header("admin_forum_manager.php?op=manage", 1, _AM_XFORUM_FORUMREMOVED);
            exit();
        }
        break;

    case 'manage':
        loadModuleAdminMenu(2, _AM_XFORUM_FORUM_MANAGER);

        $echo = "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_FORUM_MANAGER . "</legend>";
        $echo .= "<br />";

        $echo .= "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        $echo .= "<tr align='center'>";
        $echo .= "<td class='bg3' colspan='2'>" . _AM_XFORUM_NAME . "</td>";
        $echo .= "<td class='bg3'>" . _AM_XFORUM_EDIT . "</td>";
        $echo .= "<td class='bg3'>" . _AM_XFORUM_DELETE . "</td>";
        $echo .= "<td class='bg3'>" . _AM_XFORUM_ADD . "</td>";
        $echo .= "<td class='bg3'>" . _AM_XFORUM_MOVE . "</td>";
        $echo .= "<td class='bg3'>" . _AM_XFORUM_MERGE . "</td>";
        $echo .= "</tr>";

		$category_handler = &xoops_getmodulehandler('category', 'xforum');
    	$categories = $category_handler->getAllCats('', true);
		$xforums = $forum_handler->getForumsByCategory(array_keys($categories), '', false);
		foreach (array_keys($categories) as $c) {
            $category =& $categories[$c];
            $cat_link = "<a href=\"" . $xforumUrl['root'] . "/index.php?viewcat=" . $category->getVar('cat_id') . "\">" . $category->getVar('cat_title') . "</a>";
            $cat_edit_link = "<a href=\"admin_cat_manager.php?op=mod&amp;cat_id=" . $category->getVar('cat_id') . "\">".forum_displayImage($xforumImage['edit'], _EDIT)."</a>";
            $cat_del_link = "<a href=\"admin_cat_manager.php?op=del&amp;cat_id=" . $category->getVar('cat_id') . "\">".forum_displayImage($xforumImage['delete'], _DELETE)."</a>";
            $forum_add_link = "<a href=\"admin_forum_manager.php?op=addforum&amp;cat_id=" . $category->getVar('cat_id') . "\">".forum_displayImage($xforumImage['new_forum'])."</a>";

            $echo .= "<tr class='even' align='left'>";
            $echo .= "<td width='100%' colspan='2'><strong>" . $cat_link . "</strong></td>";
            $echo .= "<td align='center'>" . $cat_edit_link . "</td>";
            $echo .= "<td align='center'>" . $cat_del_link . "</td>";
            $echo .= "<td align='center'>" . $forum_add_link . "</td>";
            $echo .= "<td></td>";
            $echo .= "<td></td>";
            $echo .= "</tr>";
            if(!isset($xforums[$c])) continue;
			foreach(array_keys($xforums[$c]) as $f){
                $f_link = "&nbsp;<a href=\"" . $xforumUrl['root'] . "/viewforum.php?forum=" . $f . "\">" . $xforums[$c][$f]["title"] . "</a>";
                $f_edit_link = "<a href=\"admin_forum_manager.php?op=mod&amp;forum=" . $f . "\">".forum_displayImage($xforumImage['edit'])."</a>";
                $f_del_link = "<a href=\"admin_forum_manager.php?op=del&amp;forum=" . $f . "\">".forum_displayImage($xforumImage['delete'])."</a>";
                $sf_add_link = "<a href=\"admin_forum_manager.php?op=addsubforum&amp;cat_id=" . $c . "&parent_forum=" . $f . "\">".forum_displayImage($xforumImage['new_subforum'])."</a>";
                $f_move_link = "<a href=\"admin_forum_manager.php?op=moveforum&amp;forum=" . $f . "\">".forum_displayImage($xforumImage['move_topic'])."</a>";
                $f_merge_link = "<a href=\"admin_forum_manager.php?op=mergeforum&amp;forum=" . $f . "\">".forum_displayImage($xforumImage['move_topic'])."</a>";

                $echo .= "<tr class='odd' align='left'><td></td>";
                $echo .= "<td><strong>" . $f_link . "</strong></td>";
                $echo .= "<td align='center'>" . $f_edit_link . "</td>";
                $echo .= "<td align='center'>" . $f_del_link . "</td>";
                $echo .= "<td align='center'>" . $sf_add_link . "</td>";
                $echo .= "<td align='center'>" . $f_move_link . "</td>";
                $echo .= "<td align='center'>" . $f_merge_link . "</td>";
                $echo .= "</tr>";
		        if(!isset($xforums[$c][$f]["sub"])) continue;
				foreach(array_keys($xforums[$c][$f]["sub"]) as $s){
                    $f_link = "&nbsp;<a href=\"" . $xforumUrl['root'] . "/viewforum.php?forum=" . $s . "\">-->" . $xforums[$c][$f]["sub"][$s]["title"] . "</a>";
                    $f_edit_link = "<a href=\"admin_forum_manager.php?op=mod&amp;forum=" . $s . "\">".forum_displayImage($xforumImage['edit'])."</a>";
                    $f_del_link = "<a href=\"admin_forum_manager.php?op=del&amp;forum=" . $s . "\">".forum_displayImage($xforumImage['delete'])."</a>";
                    $sf_add_link = "";
                    $f_move_link = "<a href=\"admin_forum_manager.php?op=moveforum&amp;forum=" . $s . "\">".forum_displayImage($xforumImage['move_topic'])."</a>";
                    $f_merge_link = "<a href=\"admin_forum_manager.php?op=mergeforum&amp;forum=" . $s . "\">".forum_displayImage($xforumImage['move_topic'])."</a>";
                    $echo .= "<tr class='odd' align='left'><td></td>";
                    $echo .= "<td><strong>" . $f_link . "</strong></td>";
                    $echo .= "<td align='center'>" . $f_edit_link . "</td>";
                    $echo .= "<td align='center'>" . $f_del_link . "</td>";
                    $echo .= "<td align='center'>" . $sf_add_link . "</td>";
                    $echo .= "<td align='center'>" . $f_move_link . "</td>";
                    $echo .= "<td align='center'>" . $f_merge_link . "</td>";
                    $echo .= "</tr>";
				}
			}
		}
    	unset($xforums, $categories);

        echo $echo;
        echo "</table>";
        echo "</fieldset>";
        break;

    case "addsubforum":
    /*
        loadModuleAdminMenu(2, _AM_XFORUM_CREATENEWFORUM);
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_CREATENEWFORUM . "</legend>";
        echo "<br />";
        $parent_forum = isset($_GET['parent_forum']) ? intval($_GET['parent_forum']) : null;

        newForum($parent_forum);

        echo "</fieldset>";

        break;
	*/
	
    case "default":
    default:
        loadModuleAdminMenu(2, _AM_XFORUM_CREATENEWFORUM);
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_XFORUM_CREATENEWFORUM . "</legend>";
        echo "<br />";

        //$parent_forum = isset($_GET['parent_forum']) ? intval($_GET['parent_forum']) : null;
        newForum(@intval($_GET['parent_forum']));

        echo "</fieldset>";
        break;
}
echo chronolabs_inline(false); xoops_cp_footer();

?>