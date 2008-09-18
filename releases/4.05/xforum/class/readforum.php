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

class Readforum extends Read 
{
    function Readforum()
    {
        $this->Read("forum");
    }
}

class xforumReadforumHandler extends xforumReadHandler
{
    function xforumReadforumHandler(&$db) {
        $this->xforumReadHandler($db, "forum");
    }
    
    /**
     * clean orphan items from database
     * 
     * @return 	bool	true on success
     */
    function cleanOrphan()
    {
	    parent::cleanOrphan($this->db->prefix("xf_posts"), "post_id");
		return parent::cleanOrphan($this->db->prefix("xf_forums"), "forum_id", "read_item");
    }    
    
    function setRead_items($status = 0, $uid = null)
    {
	    if(empty($this->mode)) return true;
	    
	    if($this->mode == 1) return $this->setRead_items_cookie($status);
	    else return $this->setRead_items_db($status, $uid);
    }
        
    function setRead_items_cookie($status, $items)
    {
	    $cookie_name = "LF";
		$items = array();
		if(!empty($status)):
		$item_handler =& xoops_getmodulehandler('forum', 'xforum');
		$items_id = $item_handler->getIds();
		foreach($items_id as $key){
			$items[$key] = time();
		}
		endif;
		forum_setcookie($cookie_name, $items);
		return true;
    }
    
    function setRead_items_db($status, $uid)
    {
	    if(empty($uid)){
		    if(is_object($GLOBALS["xoopsUser"])){
			    $uid = $GLOBALS["xoopsUser"]->getVar("uid");
		    }else{
			    return false;
		    }
	    }
	    if(empty($status)){
			$this->deleteAll(new Criteria("uid", $uid));
		    return true;
	    }

		$item_handler =& xoops_getmodulehandler('forum', 'xforum');
		$items_obj =& $item_handler->getAll(null, array("forum_last_post_id"));
		foreach(array_keys($items_obj) as $key){
			$this->setRead_db($key, $items_obj[$key]->getVar("forum_last_post_id"), $uid);
		}
		unset($items_obj);
		
		return true;
    }
}
?>