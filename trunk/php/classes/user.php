<?php
/*****************************************************************************
 *
 *  file		: user.php
 *  created		: 2005-09-03
 *  copyright		: (C) 2005 The ComaCMS-Team
 *  email		: comacms@williblau.de
 *
 *****************************************************************************/

/*****************************************************************************
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *****************************************************************************/
 
	class User {
	
		var $OnlineId = '';
		
		var $Name = '';
		
		var $Showname = '';
		
		var $PasswordMd5 = '';
		
		var $Id = 0;
		
		var $AccessRghts = '';
		
		var $IsAdmin = false;
		
		var $IsLoggedIn = false;
		
		// FIX ME: get this by default config or by HTTP headers of the client
		var $Language = 'de';
		
		
		
		function User() {
			global $extern_login_name, $extern_login_password, $extern_lang, $_COOKIE;
			$languages = array('de', 'en');
			// Check: has the user changed the language by hand?
			if(!empty($extern_lang)) {
				if(in_array($extern_lang, $languages))
					$this->Language = $extern_lang;
			}
			// Get the language from the cookie if it' s not changed
			elseif(isset($_COOKIE['ComaCMS_user_lang'])) {
				if(in_array($_COOKIE['ComaCMS_user_lang'], $languages))
					$this->Language = $_COOKIE['ComaCMS_user_lang'];
			}
			
			// Set the cookie (for the next 93(= 3x31) days)
			setcookie('ComaCMS_user_lang', $this->Language, time() + 8035200); 
		
			// Tells the cookie: "the user is logged in!"?
			if(isset($_COOKIE['ComaCMS_user'])) {
				$data = explode('|', $_COOKIE['ComaCMS_user']);
				$this->OnlineId = @$data[0];
				$this->Name = @$data[1];
				$this->PasswordMd5 = @$data[2];
			}
			// Tries somebody to log in?
			if(!empty($extern_login_name) && !empty($extern_login_password)) {
				$this->Name = $extern_login_name;
				$this->PasswordMd5 = md5($extern_login_password);
			}
			// Has the user no OnlineId? Generate one!
			if($this->OnlineId == '')
				$this->OnlineId =  md5(uniqid(rand()));
	
			// Check: is the user really logged in? Or had he typed in the right name and password?
			if($this->Name != "" && $this->PasswordMd5 != "") {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "users
					WHERE user_name='$this->Name' AND user_password='$this->PasswordMd5'";
				$original_user_result = db_result($sql);
				$original_user = mysql_fetch_object($original_user_result);
				if(@$original_user->user_name == '') {
					$this->IsAdmin = false;
					$this->IsLoggedIn = false;
					$this->ame = '';
					$this->PasswordMd5 = '';
				}
				else {
					$this->IsLoggedIn = true;
					$this->Showname = $original_user->user_showname;
					$this->Id = $original_user->user_id;
					if($original_user->user_admin == 'y')
						$this->IsAdmin = true;
				}
			}
			
			// Set the cookie (for the next 4 hours) 
			setcookie('ComaCMS_user',$this->OnlineId . '|' . $this->Name . '|' . $this->PasswordMd5, time() + 14400);
			
		}
	}
	
	/*
			global $login_name, $login_password, $lang, $actual_user_is_admin, $actual_user_is_logged_in, $actual_user_id, $actual_user_name, $actual_user_showname, $actual_user_passwd_md5, $actual_user_lang, $actual_user_online_id, $_COOKIE;
	
		$actual_user_online_id = "";
		$actual_user_is_admin = false;
		$actual_user_is_logged_in = false;
		$actual_user_id = 0;
		//
		// FIX ME: get this by default config or by HTTP headers of the client
		//
		$actual_user_lang = 'de'; 
		$actual_user_name = '';
		$actual_user_showname = '';
		$actual_user_passwd_md5 = '';
		$languages = array('de', 'en');
		//
		// Check: has the user changed the language by hand?
		//
		if(isset($lang)) {
			if(in_array($lang, $languages))
				$actual_user_lang = $lang;
		}
		//
		// Get the language from the cookie if it' s not changed
		//
		elseif(isset($_COOKIE['CMS_user_lang'])) {
			if(in_array($_COOKIE['CMS_user_lang'], $languages))
				$actual_user_lang = $_COOKIE['CMS_user_lang'];
		}
		//
		// Set the cookie (for the next 93(= 3x31) Days)
		//
		setcookie('CMS_user_lang', $actual_user_lang, time() + 8035200); 
		//
		// Tells the cookie: "the user is logged in!"?
		//
		if(isset($_COOKIE['CMS_user_cookie'])) {
			$data = explode('|', $_COOKIE['CMS_user_cookie']);
			$actual_user_online_id = @$data[0];
			$actual_user_name = @$data[1];
			$actual_user_passwd_md5 = @$data[2];
		}
		//
		// Tries somebody to log in?
		//
		if(isset($login_name) && isset($login_password)) {
			$actual_user_name = $login_name;
			$actual_user_passwd_md5 = md5($login_password);
		}
		
		if($actual_user_online_id == '')
			$actual_user_online_id =  md5(uniqid(rand()));
		//
		// Check: is the user really logged in?
		//
		if($actual_user_name != "" && $actual_user_passwd_md5 != "") {
			$sql = "SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_name='$actual_user_name' AND user_password='$actual_user_passwd_md5'";
			$original_user_result = db_result($sql);
			$original_user = mysql_fetch_object($original_user_result);
			if(@$original_user->user_name == '') {
				$actual_user_is_admin = false;
				$actual_user_is_logged_in = false;
				$actual_user_name = '';
				$actual_user_passwd_md5 = '';
			}
			else {
				$actual_user_is_logged_in = true;
				$actual_user_showname = $original_user->user_showname;
				$actual_user_id = $original_user->user_id;
				if($original_user->user_admin == 'y')
					$actual_user_is_admin = true;
			}
		}
		
		setcookie('CMS_user_cookie',$actual_user_online_id . '|' . $actual_user_name . '|' . $actual_user_passwd_md5, time() + 14400);
	}
	*/
?>