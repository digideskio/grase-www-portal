<?php

/* Copyright 2008 Timothy White */

/*  This file is part of GRASE Hotspot.

    http://hotspot.purewhite.id.au/

    GRASE Hotspot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    GRASE Hotspot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GRASE Hotspot.  If not, see <http://www.gnu.org/licenses/>.
*/

// TODO: This appears to be obsolete, replaced by Auth Class, remove old code

/*abstract class AdminAuth
{
    abstract protected function addUser($username, $password);
    abstract protected function deleteUser($username);
    abstract protected function changeUserPassword($username, $password);
    abstract protected function validateLogin($username, $password);
    abstract protected function checkAdminUsernameExists($username);
    abstract protected function getUserLogins();    */

class AdminAuth
{    
    const SALT_LENGTH =  9;
    
    function generateSessionAuthToken($username, $password) 
    {
        return crypt($username);
    }
    
    function validateSession() 
    {
    
        return (crypt($_SESSION['username'], $_SESSION['auth']) == $_SESSION['auth']);
    }
    
    function generateHash($plainText, $salt = null) 
    {
    
        if ($salt === null) 
        {
            $salt = substr(md5(uniqid(rand() , true)) , 0, self::SALT_LENGTH);
        }
        else
        {
            $salt = substr($salt, 0, self::SALT_LENGTH);
        }
        
        return $salt . sha1($salt . $plainText);
    }

}

?>