<?php
// $Id: category.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $
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
 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

defined("forum_FUNCTIONS_INI") || include XOOPS_ROOT_PATH.'/modules/xforum/include/functions.ini.php';
forum_load_object();

class Category extends ArtObject {

    function Category()
    {
	    $this->ArtObject("xf_categories");
        $this->initVar('cat_id', XOBJ_DTYPE_INT);
        $this->initVar('pid', XOBJ_DTYPE_INT, 0);
        $this->initVar('cat_title', XOBJ_DTYPE_TXTBOX);
        $this->initVar('cat_image', XOBJ_DTYPE_TXTBOX);
        $this->initVar('cat_description', XOBJ_DTYPE_TXTAREA);
        $this->initVar('cat_order', XOBJ_DTYPE_INT);
        //$this->initVar('cat_state', XOBJ_DTYPE_INT);
        $this->initVar('cat_url', XOBJ_DTYPE_URL);
        //$this->initVar('cat_showdescript', XOBJ_DTYPE_INT);
    }
}

class xforumCategoryHandler extends ArtObjectHandler
{
    function xforumCategoryHandler(&$db) {
        $this->ArtObjectHandler($db, 'xf_categories', 'Category', 'cat_id', 'cat_title');
    }

    function &getAllCats($permission = false, $idAsKey = true, $tags = null)
    {
	    $perm_string = (empty($permission))?'all':'access';
        $_cachedCats[$perm_string]=array();
        $criteria = new Criteria("1", 1);
        $criteria->setSort("cat_order");
        $categories =& $this->getAll($criteria, $tags, $idAsKey);
        foreach(array_keys($categories) as $key){
            if ($permission && !$this->getPermission($categories[$key])) continue;
            if($idAsKey){
            	$_cachedCats[$perm_string][$key] = $categories[$key];
            }else{
            	$_cachedCats[$perm_string][] = $categories[$key];
        	}
        }
        return $_cachedCats[$perm_string];
    }

    function insert(&$category)
    {
        parent::insert($category, true);
        if ($category->isNew()) {
	        $this->applyPermissionTemplate($category);
        }

        return $category->getVar('cat_id');
    }

    function delete(&$category)
    {
        global $xoopsModule;
		$forum_handler = &xoops_getmodulehandler('forum', 'xforum');
		$forum_handler->deleteAll(new Criteria("cat_id", $category->getVar('cat_id')), true, true);
        if ($result = parent::delete($category)) {
            // Delete group permissions
            return $this->deletePermission($category);
        } else {
	        $category->setErrors("delete category error: ".$sql);
            return false;
        }
    }

    /*
     * Check permission for a category
     *
     * TODO: get a list of categories per permission type
     *
     * @param	mixed (object or integer)	category object or ID
     * return	bool
     */
    function getPermission($category)
    {
        global $xoopsUser, $xoopsModule;
        static $_cachedCategoryPerms;

        if (forum_isAdministrator()) return true;

        if(!isset($_cachedCategoryPerms)){
	        $getpermission = &xoops_getmodulehandler('permission', 'xforum');
	        $_cachedCategoryPerms = $getpermission->getPermissions("category");
        }

        $cat_id = is_object($category)? $category->getVar('cat_id'):intval($category);
        $permission = (isset($_cachedCategoryPerms[$cat_id]['category_access'])) ? 1 : 0;

        return $permission;
    }
        
    function deletePermission(&$category)
    {
		$perm_handler =& xoops_getmodulehandler('permission', 'xforum');
		return $perm_handler->deleteByCategory($category->getVar("cat_id"));
	}
    
    function applyPermissionTemplate(&$category)
    {
		$perm_handler =& xoops_getmodulehandler('permission', 'xforum');
		return $perm_handler->setCategoryPermission($category->getVar("cat_id"));
	}
}

?>