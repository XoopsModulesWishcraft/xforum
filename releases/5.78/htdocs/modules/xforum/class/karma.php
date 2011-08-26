<?php

// $Id: karma.php,v 4.03 2008/06/05 15:35:32 wishcraft Exp $

class xforumKarmaHandler {
    var $user;

    function getUserKarma($user = false)
    {
        global $xoopsUser;

        if (!isset($user) || !$user) {
            if (is_object($xoopsUser)) $this->user = $xoopsUser;
            else $this->user = null;
        } elseif (is_object($user)) {
            $this->user = $user;
        } elseif (is_int ($user) && $user > 0) {
            $member_handler = &xoops_gethandler('member');
            $this->user = $member_handler->get($user);
        } else $this->user = null;

        return $this->calUserkarma();
    } 

    function calUserkarma()
    {
        if (!$this->user) $user_karma = 0;
        else $user_karma = $this->user->getVar('posts') * 50;
        return $user_karma;
    } 

    function updateUserKarma()
    {
    } 

    function writeUserKarma()
    {
    } 

    function readUserKarma()
    {
    } 
} 

?>