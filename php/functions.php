<?php
/*****************************************************************************
 *
 *  file		: functions.php
 *  created		: 2005-06-17
 *  copyright		: (C) 2005 The Comasy-Team
 *  email		: comasy@williblau.de
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
	function _start() {
		global $db_con, $d_user, $d_pw, $d_base, $d_server, $d_pre;
		
		include_once('config.php');
		$db_con = connect_to_db($d_user, $d_pw, $d_base, $d_server);
		define('DB_PREFIX', $d_pre);
}

	function connect_to_db($username, $userpw, $database, $server = 'localhost') {
		error_reporting(E_ALL);
		$db = mysql_pconnect($server, $username, $userpw)
		or die('Mysql-error:' . mysql_error());
		mysql_select_db($database, $db)
		or die('Mysql-error:' . mysql_error());
		
		return $db;
	}

	function db_result($command) {
		global $db_con;
		
		$result = mysql_query ($command, $db_con);
		if (!$result)
			echo 'Error: ' . $command . ':' . mysql_error () . ';';
			
		return $result;
	}

	function generate_password($length) {
		$abc = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 'r', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'R', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
		$out = '';
		for($i = 0; $i < $length; $i++) {
			$out .=  $abc[rand(0, count($abc) - 1)];
		}
		
		return $out;
	}
	
	function sendmail($to, $from, $title, $text) {
		$to = strtolower($to);
		$from = strtolower($from);
		$header="From:$from\n";
		$header .= 'Content-Type: text/html';
		
		return mail($to, $title, $text, $header);
}

	function getmicrotime($mic_time) {
		list($usec, $sec) = explode(' ', $mic_time);
		
		return ((float)$usec + (float)$sec);
	}

	function writelog($text) {
		$handle = fopen ('log.log', 'a');
		fwrite($handle, $text . "\n");
		fclose ($handle);
	}

	function getUserIDByName($name) {
		$sql = "SELECT user_id
			FROM " . DB_PREFIX . "users
			WHERE user_name='$name'";
		$result = db_result($sql);
		$row = mysql_fetch_object($result);
		
		return $row->user_id;
}

	function getUserByID($id) {
		$sql = "SELECT user_showname
			FROM " . DB_PREFIX . "users
			WHERE user_id = '$id'";
		$result = db_result($sql);
		$row = mysql_fetch_object($result);
		
		return $row->user_showname;
	}

	function replace_smilies($textdata) {
		$smilies_path = 'data/smilies';
		$textdata = str_replace('??:-)', 	'<img src="' . $smilies_path . '/uneasy.gif" />',	$textdata);
		$textdata = str_replace(':-)',		'<img src="' . $smilies_path . '/icon_smile.gif" />',	$textdata);
		$textdata = str_replace(';-)',		'<img src="' . $smilies_path . '/icon_wink.gif" />',	$textdata);
		$textdata = str_replace(':-&lt;',	'<img src="' . $smilies_path . '/icon_sad.gif" />',	$textdata);
		$textdata = str_replace(':-<',		'<img src="' . $smilies_path . '/icon_sad.gif" />',	$textdata);
		$textdata = str_replace(':-X',		'<img src="' . $smilies_path . '/xx.gif" />',		$textdata);
		$textdata = str_replace('8-)',		'<img src="' . $smilies_path . '/icon_cool.gif" />',	$textdata);
		$textdata = str_replace('=D&gt;',	'<img src="' . $smilies_path . '/clap.gif" />',		$textdata);
		$textdata = str_replace('=D>',		'<img src="' . $smilies_path . '/clap.gif" />',		$textdata);
		$textdata = str_replace(':music:',	'<img src="' . $smilies_path . '/dance.gif" />',	$textdata);
		$textdata = str_replace(':n&ouml;:',	'<img src="' . $smilies_path . '/noe.gif" />',		$textdata);
		$textdata = str_replace('](*,)',	'<img src="' . $smilies_path . '/wall.gif" />',		$textdata);
		$textdata = str_replace(':-~',		'<img src="' . $smilies_path . '/confused.gif" />',	$textdata);
		$textdata = str_replace(':cry:',	'<img src="' . $smilies_path . '/cry.gif" />',		$textdata);
		$textdata = str_replace('lol',		'<img src="' . $smilies_path . '/lol.gif" />',		$textdata);
		$textdata = str_replace('LOL',		'<img src="' . $smilies_path . '/lol.gif" />',		$textdata);
		$textdata = str_replace(':-/',		'<img src="' . $smilies_path . '/neutral.gif" />',	$textdata);
		$textdata = str_replace(':-D',		'<img src="' . $smilies_path . '/razz.gif" />',		$textdata);
		$textdata = str_replace('??:-)',	'<img src="' . $smilies_path . '/neutral.gif" />',	$textdata);
		$textdata = str_replace(':nö:',		'<img src="' . $smilies_path . '/noe.gif" />',		$textdata);
		$textdata = str_replace(':noe:',	'<img src="' . $smilies_path . '/noe.gif" />',		$textdata);
		$textdata = str_replace(':-O',		'<img src="' . $smilies_path . '/oo.gif" />',		$textdata);
		$textdata = str_replace(':devil:',	'<img src="' . $smilies_path . '/devil.gif" />',	$textdata);
		$textdata = str_replace(':love:',	'<img src="' . $smilies_path . '/love.gif" />',		$textdata);
		
		return $textdata;
	}

	function generatemenue($style = 'clear', $menue_id = 1, $selected = '', $style_root = '.') {
		global $internal_page_root;
		
		$menue = " ";
		include($style_root . '/styles/' . $style . '/menue.php');
		$sql = "SELECT *
			FROM " . DB_PREFIX . "menue
			WHERE menue_id='$menue_id'
			ORDER BY orderid ASC";
		$menue_result = db_result($sql);
		while($menue_data = mysql_fetch_object($menue_result)) {
			if($menue_id == 1)
				$menue_str = $menue_link;
			else
				$menue_str = $menue_link2;
			$menue_str = str_replace('[text]', $menue_data->text, $menue_str);
			$link = $menue_data->link;
			if(substr($link, 0, 2) == 'l:')
				$link = @$internal_page_root . 'index.php?page=' . substr($link, 2);
			if(substr($link, 0, 2) == 'g:')
				$link = @$internal_page_root . 'gallery.php?page=' . substr($link, 2);
			if(substr($link, 0, 2) == 'a:')
				$link = @$internal_page_root . 'admin.php?page=' . substr($link, 2);
				
			$menue_str = str_replace('[link]', $link, $menue_str);
			$new = $menue_data->new;
			if($new == 'yes')
				$new = 'target="_blank" ';
			else
				$new = '';
			$menue_str = str_replace('[new]', $new, $menue_str);
			$menue .= $menue_str . "\r\n";
		}
		
		return $menue;
	}

	function position_to_root($id, $between = " > ") {
		$sql = "SELECT *
			FROM " . DB_PREFIX . "pages_content
			WHERE page_id=$id";
		$actual_result = db_result($sql);
		$actual = mysql_fetch_object($actual_result);
		$parent_id = $actual->page_parent_id;
		$way_to_root = '';	
		while($parent_id != 0) {
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages_content
				WHERE page_id=$parent_id";
			$parent_result = db_result($sql);
			$parent = mysql_fetch_object($parent_result);
			$parent_id = $parent->page_parent_id;
			$way_to_root = $parent->page_title . $between . $way_to_root;
		}
		
		return $way_to_root . $actual->page_title;
	}
/*****************************************************************************
 *
 *  set_usercookies()
 *  Saves something for the client in two cookies:
 *  1.:
 *  - Online-id (to differ betwen clients with a same ip)
 *  - User-login-name(if it's abailable)
 *  - Userpassword-MD5-Hash(if it and the username are availble)
 *  2.:
 *  - Userdefined language (is by default $internal_default_language) with a long lifetime (about three months)
 *
 *  TODO: Make a better languagedetection
 *
 *****************************************************************************/

	function set_usercookies() {
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
	
	function isEMailAddress($email){
		return eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,4}$", $email);	
	}
	
	function isIcqNumber($icq) {
		return eregi("^[0-9]{3}(\-)?[0-9]{3}(\-)?[0-9]{3}$", $icq);
	}
	
	function endsWith($string, $search) {
		return $search == substr($string, 0 - (strlen($search)));
	}
	function startsWith($string, $search) {
		return 0 === strpos($string, $search);
	}
?>