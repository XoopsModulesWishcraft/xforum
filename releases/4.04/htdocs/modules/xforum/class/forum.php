<?php
// $Id: forum.php,v 4.04 2008/06/05 15:35:32 wishcraft Exp $
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
 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

defined("forum_FUNCTIONS_INI") || include XOOPS_ROOT_PATH.'/modules/xforum/include/functions.ini.php';
forum_load_object();

class Forum extends ArtObject {

    function Forum()
    {
	    $this->ArtObject("xf_forums");
        $this->initVar('forum_id', XOBJ_DTYPE_INT);
        $this->initVar('forum_name', XOBJ_DTYPE_TXTBOX);
        $this->initVar('forum_desc', XOBJ_DTYPE_TXTAREA);
        $this->initVar('forum_moderator', XOBJ_DTYPE_ARRAY, serialize(array()));
        $this->initVar('forum_topics', XOBJ_DTYPE_INT);
        $this->initVar('forum_posts', XOBJ_DTYPE_INT);
        $this->initVar('forum_last_post_id', XOBJ_DTYPE_INT);
        $this->initVar('cat_id', XOBJ_DTYPE_INT);
        $this->initVar('forum_type', XOBJ_DTYPE_INT, 0); // 0 - active; 1 - inactive
        $this->initVar('parent_forum', XOBJ_DTYPE_INT);
        $this->initVar('allow_html', XOBJ_DTYPE_INT, 0); // To be added in 3.01: 0 - disabled; 1 - enabled; 2 - checked by default
        $this->initVar('allow_sig', XOBJ_DTYPE_INT, 1);
        $this->initVar('allow_subject_prefix', XOBJ_DTYPE_INT, 1);
        $this->initVar('hot_threshold', XOBJ_DTYPE_INT, 20);
        $this->initVar('allow_polls', XOBJ_DTYPE_INT, 0);
        //$this->initVar('allow_attachments', XOBJ_DTYPE_INT);
        $this->initVar('attach_maxkb', XOBJ_DTYPE_INT, 100);
        $this->initVar('attach_ext', XOBJ_DTYPE_TXTAREA, "zip|jpg|gif");
        $this->initVar('forum_order', XOBJ_DTYPE_INT, 99);
        /*
         * For desc
         *
         */
        $this->initVar("dohtml", XOBJ_DTYPE_INT, 1);
        $this->initVar("dosmiley", XOBJ_DTYPE_INT, 1);
        $this->initVar("doxcode", XOBJ_DTYPE_INT, 1);
        $this->initVar("doimage", XOBJ_DTYPE_INT, 1);
        $this->initVar("dobr", XOBJ_DTYPE_INT, 1);
    }

    // Get moderators in uname or in uid
    function &getModerators($asUname = false)
    {
	    static $_cachedModerators = array();

        $moderators = array_filter($this->getVar('forum_moderator'));
        if(!$asUname) return $moderators;

        $moderators_return = array();
        $moderators_new = array();
        foreach($moderators as $id){
	        if($id ==0) continue;
	        if(isset($_cachedModerators[$id])) $moderators_return[$id] = &$_cachedModerators[$id];
	        else $moderators_new[]=$id;
        }
        if(count($moderators_new)>0){
			include_once XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php';
	        $moderators_new = forum_getUnameFromIds($moderators_new);
	        foreach($moderators_new as $id => $name){
				$_cachedModerators[$id] = $name;
				$moderators_return[$id] = $name;
			}
        }
        return $moderators_return;
    }

    // deprecated
    function isSubForum()
    {
        return ($this->getVar('parent_forum') > 0);
    }

    function disp_forumModerators($valid_moderators = 0)
    {
    	global $xoopsDB, $xoopsModuleConfig, $myts;

        $ret = "";
        if ($valid_moderators === 0) {
            $valid_moderators = $this->getModerators();
        }
        if (empty($valid_moderators) || !is_array($valid_moderators)) {
            return $ret;
        }
		include_once XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php';
        $moderators = forum_getUnameFromIds($valid_moderators, !empty($xoopsModuleConfig['show_realname']), true);
		$ret = implode(", ", $moderators);
		return $ret;
    }
}

class xforumForumHandler extends ArtObjectHandler
{
    function xforumForumHandler(&$db) {
        $this->ArtObjectHandler($db, 'xf_forums', 'Forum', 'forum_id', 'forum_name');
    }

    function insert(&$xforum)
    {
        if(!parent::insert($xforum, true)){
	        xoops_error($xforum->getErrors());
	        return false;
        }
        
        if ($xforum->isNew()) {
        	$this->applyPermissionTemplate($xforum);
    	}

        return $xforum->getVar('forum_id');
    }

    function delete(&$xforum)
    {
        global $xoopsModule;
        // RMV-NOTIFY
        xoops_notification_deletebyitem ($xoopsModule->getVar('mid'), 'forum', $xforum->getVar('forum_id'));
        // Get list of all topics in forum, to delete them too
		$topic_handler =& xoops_getmodulehandler('topic', 'xforum');
		$topic_handler->deleteAll(new Criteria("forum_id", $xforum->getVar('forum_id')), true, true);
        $this->updateAll("parent_forum", $xforum->getVar('parent_forum'), new Criteria("parent_forum", $xforum->getVar('forum_id')));
       	$this->deletePermission($xforum);
        return parent::delete($xforum);
    }

    function &getForums($cat = 0, $permission = "", $tags = null)
    {
	    $_cachedForums=array();
	    $perm_string = (empty($permission))?'all':$permission;
        $forum_handler =& xoops_getmodulehandler('forum', 'xforum');
	    $criteria = new CriteriaCompo(new Criteria("1", 1));
        if (is_numeric($cat) && $cat> 0) {
	        $criteria->add(new Criteria("cat_id", intval($cat)));
        }elseif(is_array($cat) && count($cat) >0){
            $criteria->add(new Criteria("cat_id", "(" . implode(", ", array_map("intval", $cat)).")", "IN"));
        }
        $criteria->setSort("forum_order");
        $criteria->setOrder("ASC");
        $xforums =& $forum_handler->getAll($criteria, $tags);
        $_cachedForums[$perm_string]=array();
        foreach(array_keys($xforums) as $key){
            if ($permission && !$this->getPermission($xforums[$key], $permission, empty($cat))) continue;
            $_cachedForums[$perm_string][$key] =& $xforums[$key];
        }
        // TODO: Retrieve subforums
        return $_cachedForums[$perm_string];
    }
    
    function &getForumsByCategory($categoryid = 0, $permission = "", $asObject = true, $tags = null)
    {
	    //$tags = array("parent_forum", "cat_id", "forum_name");
        $xforums =& $this->getForums($categoryid, $permission, $tags);
        if($asObject) return $xforums;
        
		$xforums_array = array();
		$array_cat=array();
		$array_forum=array();
		if(!is_array($xforums)) return array();
		foreach (array_keys($xforums) as $xforumid) {
			$xforum =& $xforums[$xforumid];
		    $xforums_array[$xforum->getVar('parent_forum')][$xforumid] = array(
			    'cid' => $xforum->getVar('cat_id'),
			    'title' => $xforum->getVar('forum_name')
			);
		}
		if(!isset($xforums_array[0])) {
			$ret = array();
			return $ret;
		}
		foreach ($xforums_array[0] as $key => $xforum) {
		    if (isset($xforums_array[$key])) {
		        $xforum['sub'] = $xforums_array[$key];
		    }
		    $array_forum[$xforum['cid']][$key] = $xforum;
		}
		ksort($array_forum);
		unset($xforums);
		unset($xforums_array);
        return $array_forum;
    }

    // Get moderators of multi-forums
    function &getModerators(&$xforums, $asUname = false)
    {
        if (empty($xforums)) $xforums = $this->getForums();
        $moderators = array();
        if (is_array($xforums)) {
            foreach ($xforums as $xforumid => $xforum) {
                $moderators = array_merge($moderators, $xforum->getModerators($asUname));
            }
        } elseif (is_object($xforums)) {
            $moderators =& $xforums->getModerators($asUname);
        }
        return $moderators;
    }

    function getAllTopics(&$xforum, $startdate, $start, $sortname, $sortorder, $type = '', $excerpt = 0)
    {
        global $xoopsModule, $xoopsConfig, $xoopsModuleConfig, $xforumImage, $xforumUrl, $myts, $xoopsUser, $viewall_forums;
		include_once XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php';
		
        $UserUid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : null;

        $topic_lastread = forum_getcookie('LT', true);

        if (is_object($xforum)) {
            $criteria_forum = ' AND t.forum_id = ' . $xforum->getVar('forum_id');
            $hot_threshold = $xforum->getVar('hot_threshold');
            $allow_subject_prefix = $xforum->getVar('allow_subject_prefix');
        } else {
            $hot_threshold = 10;
            $allow_subject_prefix = 0;
            if (is_array($xforum) && count($xforum) > 0){
                $criteria_forum = ' AND t.forum_id IN (' . implode(',', array_keys($xforum)) . ')';
            }elseif(!empty($xforum)){
                $criteria_forum = ' AND t.forum_id ='.intval($xforum);
            }else{
                $criteria_forum = '';
            }
        }

        $sort = array();
        $criteria_post = ' p.post_time > ' . $startdate;
        $criteria_extra = '';
        $criteria_approve = ' AND t.approved = 1 AND p.approved = 1';
        $post_on = ' p.post_id = t.topic_last_post_id';
        //$post_criteria = '';
        $leftjoin = ' LEFT JOIN ' . $this->db->prefix('xf_posts') . ' p ON p.post_id = t.topic_last_post_id';
        switch ($type) {
            case 'digest':
                $criteria_extra = ' AND t.topic_digest = 1';
                break;
            case 'unreplied':
                $criteria_extra = ' AND t.topic_replies < 1';
                break;
            case 'unread':
				//$time_criterion = max($GLOBALS['last_visit'], $startdate);
                if(empty($xoopsModuleConfig["read_mode"])){
                }elseif($xoopsModuleConfig["read_mode"] ==2){
	        		$leftjoin .= ' LEFT JOIN ' . $this->db->prefix('xf_reads_topic') . ' r ON r.read_item = t.topic_id';
	                $criteria_post .= ' AND (r.read_id IS NULL OR r.post_id < t.topic_last_post_id)';
                }elseif($xoopsModuleConfig["read_mode"] == 1){
	        		$topics = array();
	    			$topic_lastread = forum_getcookie('LT', true);
	        		if(count($topic_lastread)>0) foreach($topic_lastread as $id=>$time){
		        		if($time > $time_criterion) $topics[] = $id;
			        }
			        if(count($topics)>0){
	                	$criteria_extra = ' AND t.topic_id NOT IN ('.implode(",", $topics).')';
                	}
                	$criteria_post = ' p.post_time > ' . max($GLOBALS['last_visit'], $startdate);
                }
                break;
            case 'pending':
		        $post_on = ' p.topic_id = t.topic_id';
        		$criteria_post .= ' AND p.pid=0';
        		$criteria_approve = ' AND t.approved = 0';
                break;
            case 'deleted':
        		$criteria_approve = ' AND t.approved = -1';
                break;
            case 'all': // For viewall.php; do not display sticky topics at first
            case 'active': // same as "all"
                //$criteria_post = ' p.post_time > ' . $startdate;
                break;
            default:
                $criteria_post = ' (p.post_time > ' . $startdate . ' OR t.topic_sticky=1)';
                $sort[] = 't.topic_sticky DESC';
                break;
        }
        
        $select = 	't.*, '.
        			' p.post_time as last_post_time, p.poster_name as last_poster_name, p.icon, p.post_id, p.uid';
        $from = $this->db->prefix("xf_topics") . ' t '.$leftjoin;
        $where = $criteria_post. $criteria_forum . $criteria_extra . $criteria_approve;

        if($excerpt){
        	$select .=', p.post_karma, p.require_reply, pt.post_text';
        	$from .= ' LEFT JOIN ' . $this->db->prefix('xf_posts_text') . ' pt ON pt.post_id = t.topic_last_post_id';
    	}
    	if($sortname == "u.uname"){
        	$sortname = "t.topic_poster";
    	}
    	
        $sort[] = trim($sortname.' '.$sortorder);
        $sort = implode(", ", $sort);
        if(empty($sort)) $sort = 'p.post_time DESC';
        
    	$sql = 	'SELECT '.$select.
    			' FROM '.$from.
    			' WHERE '.$where.
    			' ORDER BY '.$sort;
    			
        if (!$result = $this->db->query($sql, $xoopsModuleConfig['topics_per_page'], $start)) {
            redirect_header('index.php', 2, _MD_ERROROCCURED . '<br />' . $sql);
            exit();
        }

        $subject_array = array();
        if(!empty($allow_subject_prefix) && !empty($xoopsModuleConfig['subject_prefix'])):
        $subjectpres = explode(',', $xoopsModuleConfig['subject_prefix']);
        if (count($subjectpres) > 1) {
            foreach($subjectpres as $subjectpre) {
                $subject_array[] = $subjectpre." ";
            }
        }
        endif;
        $subject_array[0] = null;


        $sticky = 0;
        $topics = array();
        $posters = array();
        $reads = array();
        while ($myrow = $this->db->fetchArray($result)) {
            if ($myrow['topic_sticky']) {
                $sticky++;
            }
            
            // ------------------------------------------------------
            // topic_icon: priority: sticky -> digest -> regular
            
            if ($myrow['topic_haspoll']) {
	            if ($myrow['topic_sticky']) {
	                $topic_icon = forum_displayImage($xforumImage['folder_sticky'], _MD_TOPICSTICKY) . '<br />' . forum_displayImage($xforumImage['poll'], _MD_TOPICHASPOLL);
	            }else{
                	$topic_icon = forum_displayImage($xforumImage['poll'], _MD_TOPICHASPOLL);
	            }
            }elseif($myrow['topic_sticky']) {
                $topic_icon = forum_displayImage($xforumImage['folder_sticky'], _MD_TOPICSTICKY);
            }elseif (!empty($myrow['icon'])) {
                $topic_icon = '<img src="' . XOOPS_URL . '/images/subject/' . htmlspecialchars($myrow['icon']) . '" alt="" />';
            } else {
                $topic_icon = '<img src="' . XOOPS_URL . '/images/icons/no_posticon.gif" alt="" />';
            }
            // ------------------------------------------------------
            // rating_img
            $rating = number_format($myrow['rating'] / 2, 0);
            $rating_img = forum_displayImage($xforumImage[($rating < 1)?'blank':'rate' . $rating]);
            // ------------------------------------------------------
            // topic_page_jump
            $topic_page_jump = '';
            $topic_page_jump_icon = '';
            $totalpages = ceil(($myrow['topic_replies'] + 1) / $xoopsModuleConfig['posts_per_page']);
            if ($totalpages > 1) {
                $topic_page_jump .= '&nbsp;&nbsp;';
                $append = false;
                for ($i = 1; $i <= $totalpages; $i++) {
                    if ($i > 3 && $i < $totalpages) {
	                    if(!$append){
                        	$topic_page_jump .= "...";
                        	$append = true;
                    	}
                    } else {
                        $topic_page_jump .= '[<a href="'.XOOPS_URL.'/modules/xforum/'.'viewtopic.php?topic_id=' . $myrow['topic_id'] . '&amp;start=' . (($i - 1) * $xoopsModuleConfig['posts_per_page']) . '">' . $i . '</a>]';
                        $topic_page_jump_icon = "<a href='" . XOOPS_URL . "/modules/xforum/viewtopic.php?topic_id=" . $myrow['topic_id'] . "&amp;start=" . (($i - 1) * $xoopsModuleConfig['posts_per_page']) . "#forumpost" . $myrow['post_id'] . "'>" . forum_displayImage($xforumImage['docicon']) . "</a>";
                    }
                }
            }
            else {
            	$topic_page_jump_icon = "<a href='" . XOOPS_URL . "/modules/xforum/viewtopic.php?topic_id=" . $myrow['topic_id'] . "#forumpost" . $myrow['post_id'] . "'>" . forum_displayImage($xforumImage['docicon']) . "</a>";
        	}
            // ------------------------------------------------------
            // => topic array
            if (is_object($viewall_forums[$myrow['forum_id']])){
                $forum_link = '<a href="' . XOOPS_URL . '/modules/xforum/viewforum.php?forum=' . $myrow['forum_id'] . '">' . $viewall_forums[$myrow['forum_id']]->getVar('forum_name') . '</a>';
            }else {
	            $forum_link = '';
            }

           	$topic_title = $myts->htmlSpecialChars($myrow['topic_title']);
            if ($myrow['topic_digest']) $topic_title = "<span class='digest'>" . $topic_title . "</span>";

            if( $excerpt == 0 ){
	            $topic_excerpt = "";
            }elseif( ($myrow['post_karma']>0 || $myrow['require_reply']>0) && !forum_isAdmin($xforum) ){
	            $topic_excerpt = "";
            }else{
	            $topic_excerpt = xoops_substr(forum_html2text($myts->displayTarea($myrow['post_text'])), 0, $excerpt);
	            $topic_excerpt = str_replace("[", "&#91;", $myts->htmlSpecialChars($topic_excerpt));
            }

            $topic_subject = ($allow_subject_prefix)?$subject_array[$myrow['topic_subject']]:"";
            $topics[$myrow['topic_id']] = array(
            	'topic_id' => $myrow['topic_id'],
            	'topic_icon' => $topic_icon,
                //'topic_folder' => forum_displayImage($topic_folder),
                'topic_title' => $topic_subject.$topic_title,
                'topic_link' => XOOPS_URL.'/modules/xforum/'.'viewtopic.php?topic_id=' . $myrow['topic_id'] . '&amp;forum=' . $myrow['forum_id'],
                'rating_img' => $rating_img,
                'topic_page_jump' => $topic_page_jump,
                'topic_page_jump_icon' => $topic_page_jump_icon,
                'topic_replies' => $myrow['topic_replies'],
                'topic_poster_uid' => $myrow['topic_poster'],
                'topic_poster_name' => $myts->htmlSpecialChars( ($myrow['poster_name'])?$myrow['poster_name']:$xoopsConfig['anonymous']),
                'topic_views' => $myrow['topic_views'],
                'topic_time' => forum_formatTimestamp($myrow['topic_time']),
                'topic_last_posttime' => forum_formatTimestamp($myrow['last_post_time']),
                'topic_last_poster_uid' => $myrow['uid'],
                'topic_last_poster_name' => $myts->htmlSpecialChars( ($myrow['last_poster_name'])?$myrow['last_poster_name']:$xoopsConfig['anonymous']),
                'topic_forum_link' => $forum_link,
                'topic_excerpt' => $topic_excerpt,
                'stick' => empty($myrow['topic_sticky']),
                "stats" => array($myrow['topic_status'], $myrow['topic_digest'], $myrow['topic_replies']),
                );
                
            /* users */
            $posters[$myrow['topic_poster']] = 1;
            $posters[$myrow['uid']] = 1;
            // reads
            if(!empty($xoopsModuleConfig["read_mode"])){
            	$reads[$myrow['topic_id']] = ($xoopsModuleConfig["read_mode"] == 1)?$myrow['last_post_time']:$myrow["topic_last_post_id"];
        	}
        }
		$posters_name =& forum_getUnameFromIds(array_keys($posters), $xoopsModuleConfig['show_realname'], true);
        $topic_isRead = forum_isRead("topic", $reads);
        
        foreach(array_keys($topics) as $id){
            $topics[$id]["topic_poster"] = !empty($posters_name[$topics[$id]["topic_poster_uid"]])?
                                			$posters_name[$topics[$id]["topic_poster_uid"]]
            								:$topics[$id]["topic_poster_name"];
            $topics[$id]["topic_last_poster"] = !empty($posters_name[$topics[$id]["topic_last_poster_uid"]])?
                                			$posters_name[$topics[$id]["topic_last_poster_uid"]]
            								:$topics[$id]["topic_last_poster_name"];
           	// ------------------------------------------------------
            // topic_folder: priority: newhot -> hot/new -> regular
            list($topic_status, $topic_digest, $topic_replies) = $topics[$id]["stats"];
            if ($topic_status == 1) {
                $topic_folder = $xforumImage['locked_topic'];
            } else {
                if ($topic_digest) $topic_folder = $xforumImage['folder_digest'];
                elseif ($topic_replies >= $hot_threshold) {
	                if(empty($topic_isRead[$id])){
                        $topic_folder = $xforumImage['hot_newposts_topic'];
                    } else {
                        $topic_folder = $xforumImage['hot_folder_topic'];
                    }
                } else {
	                if(empty($topic_isRead[$id])){
                        $topic_folder = $xforumImage['newposts_topic'];
                    } else {
                        $topic_folder = $xforumImage['folder_topic'];
                    }
                }
            }
			$topics[$id]['topic_folder'] = forum_displayImage($topic_folder);
            								
            unset($topics[$id]["topic_poster_name"], $topics[$id]["topic_last_poster_name"], $topics[$id]["stats"]);
        }

        if ( count($topics) > 0) {
	    	$sql = " SELECT DISTINCT topic_id FROM " . $this->db->prefix("xf_posts").
	    	 		" WHERE attachment != ''".
	    	 		" AND topic_id IN (" . implode(',', array_keys($topics)) . ")";
            if($result = $this->db->query($sql)) {
                while (list($topic_id) = $this->db->fetchRow($result)) {
                    $topics[$topic_id]['attachment'] = '&nbsp;' . forum_displayImage($xforumImage['clip'], _MD_TOPICSHASATT);
                }
            }
        }
        return array($topics, $sticky);
    }

    function getTopicCount(&$xforum, $startdate, $type)
    {
	    global $xoopsModuleConfig;
		include_once XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php';
	    
        $criteria_extra = '';
        $criteria_approve = ' AND t.approved = 1'; // any others?
        $leftjoin = ' LEFT JOIN ' . $this->db->prefix('xf_posts') . ' p ON p.post_id = t.topic_last_post_id';
        $criteria_post = ' p.post_time > ' . $startdate;
        switch ($type) {
            case 'digest':
                $criteria_extra = ' AND topic_digest = 1';
                break;
            case 'unreplied':
                $criteria_extra = ' AND topic_replies < 1';
                break;
            case 'unread':
                if(empty($xoopsModuleConfig["read_mode"])){
                }elseif($xoopsModuleConfig["read_mode"] ==2){
	        		$leftjoin .= ' LEFT JOIN ' . $this->db->prefix('xf_reads_topic') . ' r ON r.read_item = t.topic_id';
	                $criteria_post .= ' AND (r.read_id IS NULL OR r.post_id < t.topic_last_post_id)';
                }elseif($xoopsModuleConfig["read_mode"] == 1){
                	$criteria_post = ' p.post_time > ' . max($GLOBALS['last_visit'], $startdate);
	        		$topics = array();
	    			$topic_lastread = forum_getcookie('LT', true);
	        		if(count($topic_lastread)>0) foreach($topic_lastread as $id=>$time){
		        		if($time > $time_criterion) $topics[] = $id;
			        }
			        if(count($topics)>0){
	                	$criteria_extra = ' AND t.topic_id NOT IN ('.implode(",", $topics).')';
                	}
                }
                break;
            case 'pending':
        		$criteria_approve = ' AND t.approved = 0';
                break;
            case 'deleted':
        		$criteria_approve = ' AND t.approved = -1';
                break;
            case 'all':
                break;
            default:
                $criteria_post = ' (p.post_time > ' . $startdate . ' OR t.topic_sticky=1)';
                break;
        }
        if (is_object($xforum)) $criteria_forum = ' AND t.forum_id = ' . $xforum->getVar('forum_id');
        else {
            if (is_array($xforum) && count($xforum) > 0){
                $criteria_forum = ' AND t.forum_id IN (' . implode(',', array_keys($xforum)) . ')';
            }elseif(!empty($xforum)){
                $criteria_forum = ' AND t.forum_id ='.intval($xforum);
            }else{
                $criteria_forum = '';
            }
        }

        $sql = 'SELECT COUNT(*) as count FROM ' . $this->db->prefix("xf_topics") . ' t '.$leftjoin;
        $sql .= ' WHERE '.$criteria_post . $criteria_forum . $criteria_extra . $criteria_approve;
        if (!$result = $this->db->query($sql)) {
	        return null;
        }
        $myrow = $this->db->fetchArray($result);
        $count = $myrow['count'];
        return $count;
    }

    // get permission
    function getPermission($xforum, $type = "access", $checkCategory = true)
    {
        global $xoopsUser, $xoopsModule;
        static $_cachedPerms;
		include_once XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php';

        if($type == "all") return true;
        if (forum_isAdministrator()) return true;
        if (!is_object($xforum)) $xforum =& $this->get($xforum);
        if ($xforum->getVar('forum_type')) return false;// if forum inactive, all has no access except admin

		if(!empty($checkCategory)){
            $category_handler =& xoops_getmodulehandler('category', 'xforum');
            $categoryPerm = $category_handler->getPermission($xforum->getVar('cat_id'));
        	if (!$categoryPerm) return false;
    	}

        $type = strtolower($type);
        if ("moderate" == $type) {
            $permission = (forum_isModerator($xforum))?1:0;
        } else {
			$perms = array_map("trim",explode(',', FORUM_PERM_ITEMS));
           	$perm_type = 'forum';
            $perm_item = (in_array($type, $perms))?'forum_' . $type:"forum_access";
			if (!isset($_cachedPerms[$perm_type])) {
				$getpermission =& xoops_getmodulehandler('permission', 'xforum');
				$_cachedPerms[$perm_type] = $getpermission->getPermissions($perm_type);
			}
        	$permission = (isset($_cachedPerms[$perm_type][$xforum->getVar('forum_id')][$perm_item])) ? 1 : 0;
        }
        return $permission;
    }
    
    function deletePermission(&$xforum)
    {
		$perm_handler =& xoops_getmodulehandler('permission', 'xforum');
		return $perm_handler->deleteByForum($xforum->getVar("forum_id"));
	}
    
    function applyPermissionTemplate(&$xforum)
    {
		$perm_handler =& xoops_getmodulehandler('permission', 'xforum');
		return $perm_handler->applyTemplate($xforum->getVar("forum_id"));
	}
	        
    /**
     * clean orphan items from database
     * 
     * @return 	bool	true on success
     */
    function cleanOrphan()
    {
	    parent::cleanOrphan($this->db->prefix("xf_categories"), "cat_id");
	    
    	if($this->mysql_major_version() >= 4):
    	/*
        $sql = "DELETE FROM ".$this->table.
        		" WHERE (parent_forum >0 AND parent_forum NOT IN ( SELECT DISTINCT forum_id FROM ".$this->table.") )";
        */
        $sql = 	"DELETE ".$this->table." FROM ".$this->table.
        		" LEFT JOIN ".$this->table." AS aa ON ".$this->table.".parent_forum = aa.forum_id ".
        		" WHERE ".$this->table.".parent_forum>0 AND (aa.forum_id IS NULL)";
        if (!$result = $this->db->queryF($sql)):
	        xoops_error("cleanOrphan error:". $sql);
        endif;
        else:
        $this->identifierName = "parent_forum";
        $forum_list = $this->getList(new Criteria("parent_forum", 0, ">"));
        $this->identifierName = "forum_name";
        if($parent_forums = @array_values($forum_list)){
	        $parent_list = $this->getIds(new Criteria("forum_id", "(".implode(", ", $parent_forums).")", "IN"));
	        foreach($forum_list as $forum_id => $parent_forum){
		        if(in_array($parent_forum, $parent_list)) continue;
		        $forum_obj =& $this->get($forum_id);
		        $this->delete($forum_obj);
		        unset($forum_obj);
	        }
        }
		endif;
        
	    return true;
    }
    
    function synchronization($object = null)
    {
	    global $xoopsDB;
	    
	    if(empty($object)) {
	    	/* for MySQL 4.1+ */
	    	if($this->mysql_major_version() >= 4){
	        $sql = "UPDATE ".$this->table.
	        		" SET ".$this->table.".forum_last_post_id = @last_post =(".
	        		"	SELECT MAX(post_id) AS last_post ".
	        		" 	FROM " . $this->db->prefix("xf_posts") . 
	        		" 	WHERE approved=1 AND forum_id = ".$this->table.".forum_id".
	        		" )".
	        		" WHERE ".$this->table.".forum_last_post_id <> @last_post";
			if(!$this->db->queryF($sql)){
				xoops_error("update error: ".$sql);
			}
	        $sql = "UPDATE ".$this->table.
	        		" SET ".$this->table.".forum_posts = @posts =(".
	        		"	SELECT COUNT(*) AS posts ".
	        		" 	FROM " . $this->db->prefix("xf_posts") . 
	        		" 	WHERE approved=1 AND forum_id = ".$this->table.".forum_id".
	        		" )".
	        		" WHERE ".$this->table.".forum_posts <> @posts";
			if(!$this->db->queryF($sql)){
				xoops_error("update error: ".$sql);
			}
	        $sql = "UPDATE ".$this->table.
	        		" SET ".$this->table.".forum_topics = @topics =(".
	        		"	SELECT COUNT(*) AS topics ".
	        		" 	FROM " . $this->db->prefix("xf_topics") . 
	        		" 	WHERE approved=1 AND forum_id = ".$this->table.".forum_id".
	        		" )".
	        		" WHERE ".$this->table.".forum_topics <> @topics";
			if(!$this->db->queryF($sql)){
				xoops_error("update error: ".$sql);
			}
        	}else{
	        // for 4.0+
		    $xforums = $this->getIds();
		    foreach($xforums as $id){
			    if(!$obj =& $this->get($id)) continue;
			    $this->synchronization($obj);
			    unset($obj);
		    }
        	}
			
			return true;
	    }
	    if(!is_object($object)){
		    $object =& $this->get(intval($object));
	    }	    
	    if(!$object->getVar("forum_id")) return false;
	    
        $sql = "SELECT MAX(post_id) AS last_post, COUNT(*) AS total FROM " . $xoopsDB->prefix("xf_posts") . " AS p LEFT JOIN  " . $xoopsDB->prefix("xf_topics") . " AS t ON p.topic_id=t.topic_id WHERE p.approved=1 AND t.approved=1 AND p.forum_id = ".$object->getVar("forum_id");
        if ( $result = $xoopsDB->query($sql)):
        $last_post = 0;
        $posts = 0;
        if( $row = $xoopsDB->fetchArray($result) ) {
	        $last_post = intval($row['last_post']);
	        $posts = intval($row['total']);
        }
        if($object->getVar("forum_last_post_id") != $last_post){
        	$object->setVar("forum_last_post_id", $last_post);
    	}
        if($object->getVar("forum_posts") != $posts){
        	$object->setVar("forum_posts", $posts);
    	}
        endif;
        $sql = "SELECT COUNT(*) AS total FROM ".$xoopsDB->prefix("xf_topics")." WHERE approved=1 AND forum_id = ".$object->getVar("forum_id");
        if ( $result = $xoopsDB->query($sql) ):
        if ( $row = $xoopsDB->fetchArray($result) ) {
	        if($object->getVar("forum_topics") != $row['total']){
            	$object->setVar("forum_topics", $row['total']);
        	}
        }
        endif;

        return $this->insert($object, true);
    }
    
    function &display(&$xforums_obj)
    {
        global $xoopsModule, $xoopsConfig, $xoopsModuleConfig, $xforumImage, $myts;
		include_once XOOPS_ROOT_PATH.'/modules/xforum/include/functions.php';
	    
		$posts = array();
		$posts_obj = array();
		foreach (array_keys($xforums_obj) as $id) {
			$posts[] = $xforums_obj[$id]->getVar("forum_last_post_id");
		}
		if(!empty($posts)){
			$post_handler =& xoops_getmodulehandler('post', 'xforum');
			$posts_obj =& $post_handler->getAll(new Criteria("post_id", "(".implode(", ", $posts).")", "IN"), array("uid", "topic_id", "post_time", "subject", "poster_name", "icon"));
		}
		
		$users = array();
		$reads = array();
		foreach (array_keys($xforums_obj) as $id) {
			$forum_obj =& $xforums_obj[$id];
			if(!$forum_obj->getVar("forum_last_post_id")) continue;
			if(!$post_obj =& $posts_obj[$forum_obj->getVar("forum_last_post_id")]) {
				$forum_obj->assignVar("forum_last_post_id", 0);
				continue;
			}
			$users[] = $post_obj->getVar("uid");
			if($moderators = $forum_obj->getModerators()){
				$users = array_merge($users, $moderators);
			}
		    // reads
		    if(!empty($xoopsModuleConfig["read_mode"])){
		    	$reads[$id] = ($xoopsModuleConfig["read_mode"] == 1)?$post_obj->getVar('post_time'):$post_obj->getVar('post_id');
			}
		}
		$forum_isread = forum_isRead("forum", $reads);
		$users_linked = forum_getUnameFromIds(array_unique($users), !empty($xoopsModuleConfig['show_realname']), true);
		
		$xforums_array = array();
		foreach (array_keys($xforums_obj) as $id) {
			$forum_obj =& $xforums_obj[$id];
			if(!$this->getPermission($forum_obj, "access", false)) continue;
			
			$_forum_data = array();
			$_forum_data["forum_order"]		= $forum_obj->getVar('forum_order');
			$_forum_data["forum_id"] 		= $id;
			$_forum_data["forum_cid"] 		= $forum_obj->getVar('cat_id');
			$_forum_data["forum_name"] 		= $forum_obj->getVar('forum_name');
			$_forum_data["forum_desc"] 		= $forum_obj->getVar('forum_desc');
			$_forum_data["forum_posts"] 	= $forum_obj->getVar("forum_posts");
			$_forum_data["forum_topics"] 	= $forum_obj->getVar("forum_topics");
			$_forum_data["forum_type"] 		= $forum_obj->getVar('forum_type');
			$forum_moderators = array();
			$moderators = $forum_obj->getModerators();
			foreach($moderators as $moderator){
				$forum_moderators[] = @$users_linked[$moderator];
			}
			$_forum_data["forum_moderators"]	= implode(", ", array_filter($forum_moderators));
		        
			if($forum_obj->getVar("forum_last_post_id")):
			$post_obj =& $posts_obj[$forum_obj->getVar("forum_last_post_id")];
			if(!empty($users_linked[$post_obj->getVar("uid")])){
				$_forum_data["forum_lastpost_user"] = $users_linked[$post_obj->getVar("uid")];
			}elseif($post_obj->getVar("poster_name")){
				$_forum_data["forum_lastpost_user"] = $post_obj->getVar("poster_name");
			}else{
				$_forum_data["forum_lastpost_user"] = $myts->htmlSpecialChars($GLOBALS["xoopsConfig"]["anonymous"]);
			}
			
		    $_forum_data['forum_lastpost_time'] = forum_formatTimestamp($post_obj->getVar('post_time'));
		    $_forum_data['forum_lastpost_icon'] = '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar("dirname") . '/viewtopic.php?post_id=' . $post_obj->getVar('post_id') . '&amp;topic_id=' . $post_obj->getVar('topic_id') . '#forumpost' . $post_obj->getVar('post_id') . '">'.
		            '<img src="' . XOOPS_URL . '/images/subject/' . ($post_obj->getVar('icon')?$post_obj->getVar('icon'): 'icon1.gif') . '" alt="" />'.
		        	'</a>';
			endif;
			
		    if (empty($forum_isread[$id])) {
		        $forum_folder = ($forum_obj->getVar('forum_type') == 1) ? $xforumImage['locked_forum_newposts'] : $xforumImage['newposts_forum'];
		    } else {
		        $forum_folder = ($forum_obj->getVar('forum_type') == 1) ? $xforumImage['locked_forum'] : $xforumImage['folder_forum'];
		    }
		    $_forum_data['forum_folder'] = forum_displayImage($forum_folder);
			
		    $xforums_array[$forum_obj->getVar('parent_forum')][] = $_forum_data;
		}
		return $xforums_array;	    
    }
}
?>