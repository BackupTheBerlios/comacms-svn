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
	
		var $OnlineID = '';
		
		var $Name = '';
		
		var $Showname = '';
		
		var $PasswordMd5 = '';
		
		var $ID = 0;
		
		var $AccessRghts = '';
		
		var $IsAdmin = false;
		
		/**
		 * @var bool
		 */
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
				$this->OnlineID = @$data[0];
				$this->Name = @$data[1];
				$this->PasswordMd5 = @$data[2];
			}
			// Tries somebody to log in?
			if(!empty($extern_login_name) && !empty($extern_login_password)) {
				$this->Name = $extern_login_name;
				$this->PasswordMd5 = md5($extern_login_password);
			}
			// Has the user no OnlineId? Generate one!
			if($this->OnlineID == '')
				$this->OnlineID =  md5(uniqid(rand()));
	
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
					$this->Name = '';
					$this->PasswordMd5 = '';
				}
				else {
					$this->IsLoggedIn = true;
					$this->Showname = $original_user->user_showname;
					$this->ID = $original_user->user_id;
					if($original_user->user_admin == 'y')
						$this->IsAdmin = true;
				}
			}
			
			// Set the cookie (for the next 4 hours) 
			setcookie('ComaCMS_user',$this->OnlineID . '|' . $this->Name . '|' . $this->PasswordMd5, time() + 14400);
			
		}
		
		function SetPage($page, $config) {
			global $REMOTE_ADDR;
			
			$counter_start_date = $config->Get('counter_start_date');
			$counter_all = $config->Get('counter_all');
			
			// is the counter counting the first time ever?
			if($counter_start_date == '') {
				$counter_start_date = mktime();
				$config->Save('counter_start_date', $counter_start_date);
			}
			if($counter_all == '') {
				$counter_all = 0;
				$config->Save('counter_all', 1);
			}
			
			// check if the user is new on the page
			$sql = "SELECT *
				FROM " . DB_PREFIX . "online
				WHERE online_id='$this->OnlineID'";
			$result_new = db_result($sql);
			if($row3 = mysql_fetch_object($result_new)) {
				$sql = "UPDATE " . DB_PREFIX . "online
					SET lastaction='" . mktime() . "', userid=$this->ID, lang='$this->Language', page='$page'
					WHERE online_id='$this->OnlineID'";
				db_result($sql);
			}
			else {
				$sql = "INSERT INTO " . DB_PREFIX . "online (online_id, ip, lastaction, page, userid, lang, host)
				VALUES ('$this->OnlineID', '$REMOTE_ADDR', '" . mktime() . "', '$page', $this->ID, '$this->Language', '" . gethostbyaddr($REMOTE_ADDR). "')";
				db_result($sql);
				$counter_all++;
			}

			// set the new counterstatus with the count of all users who visted the site since countig
			if($counter_all != 1)
				$config->Save('counter_all', $counter_all);
			
			// delete all enries with a last action which is more than 20 minutes passed
			$sql = "DELETE FROM " . DB_PREFIX . "online
				WHERE lastaction < '" . (mktime() - 1200) . "'";
			db_result($sql);
			
		}
	}
?>