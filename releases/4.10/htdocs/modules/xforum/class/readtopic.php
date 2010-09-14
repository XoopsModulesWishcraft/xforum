<?php
// $Id: moderate.php,v 4.03 2008/06/05 16:23:33 wishcraft Exp $
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
//  URL: http://www.chronolabs.org.au/forums/X-Forum/0,17,0,0,100,0,DESC,0   //
//  Project: X-Forum 4                                                       //
//  ------------------------------------------------------------------------ //
include_once dirname(__FILE__).'/read.php';

/**
 * A handler for read/unread handling
 * 
 * @package     xforum/X-Forum
 * 
 * @author	    S.A.R. (wishcraft, http://www.chronolabs.org)
 * @copyright	copyright (c) 2005 XOOPS.org
 */

class Readtopic extends Read 
{
    function Readtopic()
    {
        $this->Read("topic");
        //$this->initVar('forum_id', XOBJ_DTYPE_INT);
    }
}

class xforumReadtopicHandler extends xforumReadHandler
{
    /**
     * maximum records per forum for one user.
     * assigned from $xoopsModuleConfig["read_items"]
     *
     * @var integer
     */
	var $items_per_forum;
	
    function xforumReadtopicHandler(&$db) {
        $this->xforumReadHandler($db, "topic");
	    $xforumConfig = forum_load_config();
        $this->items_per_forum = isset($xforumConfig["read_items"])?intval($xforumConfig["read_items"]):100;
    }
    
    /**
     * clean orphan items from database
     * 
     * @return 	bool	true on success
     */
    function cleanOrphan()
    {
	    parent::cleanOrphan($this->db->prefix("xf_posts"), "post_id");
	    return parent::cleanOrphan($this->db->prefix("xf_topics"), "topic_id", "read_item");
    }    

    /**
     * Clear garbage
     * 
     * Delete all expired and duplicated records
     */
    function clearGarbage(){
	    parent::clearGarbage();
	    
	    // TODO: clearItemsExceedMaximumItemsPerForum
        return true;
    }
    
    function setRead_items($status = 0, $forum_id = 0, $uid = null)
    {
	    if(empty($this->mode)) return true;
	    
	    if($this->mode == 1) return $this->setRead_items_cookie($status, $forum_id);
	    else return $this->setRead_items_db($status, $forum_id, $uid);
    }
        
    function setRead_items_cookie($status, $forum_id)
    {
	    $cookie_name = "LT";
	    $cookie_vars = forum_getcookie($cookie_name, true);
	    
		$item_handler =& xoops_getmodulehandler('topic', 'xforum');
		$criteria =& new CriteriaCompo(new Criteria("forum_id", $forum_id));
		$criteria->setSort("topic_last_post_id");
		$criteria->setOrder("DESC");
		$criteria->setLimit($this->items_per_forum);
		$items = $item_handler->getIds($criteria);
	    
	    foreach($items as $var){
		    if(empty($status)){
			    if(isset($cookie_vars[$var])) unset($cookie_vars[$var]);
		    }else{
			    $cookie_vars[$var] = time() /*$items[$var]*/;
		    }
	    }
		forum_setcookie($cookie_name, $cookie_vars);
		return true;
    }
    
    function setRead_items_db($status, $forum_id, $uid)
    {
	    if(empty($uid)){
		    if(is_object($GLOBALS["xoopsUser"])){
			    $uid = $GLOBALS["xoopsUser"]->getVar("uid");
		    }else{
			    return false;
		    }
	    }
	    
		$item_handler =& xoops_getmodulehandler('topic', 'xforum');
		$criteria_topic =& new CriteriaCompo(new Criteria("forum_id", $forum_id));
		$criteria_topic->setSort("topic_last_post_id");
		$criteria_topic->setOrder("DESC");
		$criteria_topic->setLimit($this->items_per_forum);
		$criteria_sticky =& new CriteriaCompo(new Criteria("forum_id", $forum_id));
		$criteria_sticky->add(new Criteria("topic_sticky", 1));
	
	    if(empty($status)){		    
			$items_id = $item_handler->getIds($criteria_topic);
			$sticky_id = $item_handler->getIds($criteria_sticky);
			$items =  $items_id+$sticky_id;
			$criteria =& new CriteriaCompo(new Criteria("uid", $uid));
			$criteria->add(new Criteria("read_item", "(".implode(", ", $items).")", "IN"));
			$this->deleteAll($criteria, true);
		    return true;
	    }
		
		$items_obj =& $item_handler->getAll($criteria_topic, array("topic_last_post_id"));
		$sticky_obj =& $item_handler->getAll($criteria_sticky, array("topic_last_post_id"));
		$items_obj = $items_obj + $sticky_obj;
		$items = array();
		foreach(array_keys($items_obj) as $key){
			$items[$key] = $items_obj[$key]->getVar("topic_last_post_id");
		}
		unset($items_obj, $sticky_obj);
		foreach(array_keys($items) as $key){
			$this->setRead_db($key, $items[$key], $uid);
		}
		return true;
    }
}
?>