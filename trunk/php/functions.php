<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: functions.php					#
 # created		: 2005-06-17					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#

/*	function _start() {
		global $db_con, $d_user, $d_pw, $d_base, $d_server, $d_pre;
		
		include_once('config.php');
		$db_con = connect_to_db($d_user, $d_pw, $d_base, $d_server);
		define('DB_PREFIX', $d_pre);
}*/
	
	/**
	 * @return void
	 * @param username string
	 * @param userpw string
	 * @param database string
	 * @param server string
	 */	
	function connect_to_db($username, $userpw, $database, $server = 'localhost') {
		global $db_con;
		error_reporting(E_ALL);
		$db_con = mysql_pconnect($server, $username, $userpw)
		or die('Mysql-error:' . mysql_error());
		mysql_select_db($database, $db_con)
		or die('Mysql-error:' . mysql_error());
		
		//return $db_con;
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

	function generatemenu($style = 'clear', $menue_id = 1, $selected = '', $style_root = '.') {
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
				$menue_str = $menu_link;
			else
				$menue_str = $menu_link2;
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

	function position_to_root($id, $between = " > ", $link = true) {
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
			$page_title = $parent->page_title;
			if($link)
				$page_title = '<a href="' . ( ($parent->page_type == 'gallery') ? 'gallery' : 'index') . '.php?page=' . $parent->page_name . '">' . $page_title . '</a>';
			$way_to_root = $page_title . $between . $way_to_root;
		}
		if($link)
			$actual_page_title = '<a href="' . ( ($actual->page_type == 'gallery') ? 'gallery' : 'index') . '.php?page=' . $actual->page_name . '">' . $actual->page_title . '</a>';
		return $way_to_root . $actual_page_title;
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
	
	function nextDates($count = 5) {
		$sql = "SELECT *
			FROM " . DB_PREFIX . "dates
			WHERE date_date >= " . mktime() . "
			ORDER BY date_date ASC
			LIMIT 0, $count";
		$result = db_result($sql);
		$out = "<table class=\"dates\">
			<tr><td>Datum</td><td>Ort</td><td>Veranstaltung</td></tr>";
		while($date = mysql_fetch_object($result)) {
			$out .= "<tr><td>" . date("d.m.Y",$date->date_date) . "</td><td>$date->date_place</td><td>$date->date_topic</td></tr>";
		}
		
		$out .= "</table>";
		return $out;
	}
	
	function articlesPreview($count = 5) {
		$sql = "SELECT *
			FROM " . DB_PREFIX . "articles
			ORDER BY article_date DESC
			LIMIT 0, $count";
		$result = db_result($sql);
		$out = '<div class="articles-block">';
		while($data = mysql_fetch_object($result)) {
			$out .= "\t\t\t<div class=\"article\">
				<span class=\"article-title\">" . $data->article_title . "
					<span class=\"article-date\">" . date('d.m.Y H:i:s', $data->article_date) . "</span>
				</span>
				" . nl2br($data->article_description) . " <a href=\"article.php?page_id=$data->article_id\">mehr...</a>
				<span class=\"article-author\">" . getUserByID($data->article_creator) . "</span>
			</div>\r\n";	
		}
		$out .= '</div>';
		return $out;
	}
	
	/**
	 * This function make it easier to catch a variable which is send by GET or POST
	 * if the variable dosen't exist this function returns null
	 * @return mixed
	 * @var Name $Name is the name of the variable wthich value is to return
	 */
	function GetPostOrGet($Name) {
		global $_POST, $_GET;
		
		$value = null; // no GET- or POST-variable available
		if(isset($_POST[$Name])) // exists an POST-value?
			$value = $_POST[$Name];
		else if(isset($_GET[$Name])) // exists an GET-value?
			$value = $_GET[$Name];
		$value = MakeSecure($value);
		return $value;
	}
	/**
	 * @return mixed
	 */
	function MakeSecure($handle) {
		if($handle !== null) {
			if(is_array($handle)) {
				while(next($handle))
					$hanlde[key($handle)] = MakeSecure(current($handle));
			}
			else {
				if(get_magic_quotes_gpc())
	   				$handle = stripslashes($handle);
				if(!is_numeric($handle))
					$handle =  mysql_real_escape_string($handle);
			}
		}
		return $handle;
	}
	
	function GetMimeContentType($filename)
	{
   		$mime = array(
       			'.ai' => 'application/postscript',
			'.aif' => 'audio/x-aiff',
			'.aifc' => 'audio/x-aiff',
			'.aiff' => 'audio/x-aiff',
			'.asc' => 'text/plain',
			'.au' => 'audio/basic',
			'.avi' => 'video/x-msvideo',
			'.bcpio' => 'application/x-bcpio',
			'.bin' => 'application/octet-stream',
			'.c' => 'text/plain',
			'.cc' => 'text/plain',
			'.ccad' => 'application/clariscad',
			'.cdf' => 'application/x-netcdf',
			'.class' => 'application/octet-stream',
			'.cpio' => 'application/x-cpio',
			'.cpt' => 'application/mac-compactpro',
			'.csh' => 'application/x-csh',
			'.css' => 'text/css',
			'.dcr' => 'application/x-director',
			'.dir' => 'application/x-director',
			'.dms' => 'application/octet-stream',
			'.doc' => 'application/msword',
			'.drw' => 'application/drafting',
			'.dvi' => 'application/x-dvi',
			'.dwg' => 'application/acad',
			'.dxf' => 'application/dxf',
			'.dxr' => 'application/x-director',
			'.eps' => 'application/postscript',
			'.etx' => 'text/x-setext',
			'.exe' => 'application/octet-stream',
			'.ez' => 'application/andrew-inset',
			'.f' => 'text/plain',
			'.f90' => 'text/plain',
			'.fli' => 'video/x-fli',
			'.gif' => 'image/gif',
			'.gtar' => 'application/x-gtar',
			'.gz' => 'application/x-gzip',
			'.h' => 'text/plain',
			'.hdf' => 'application/x-hdf',
			'.hh' => 'text/plain',
			'.hqx' => 'application/mac-binhex40',
			'.htm' => 'text/html',
			'.html' => 'text/html',
			'.ice' => 'x-conference/x-cooltalk',
			'.ief' => 'image/ief',
			'.iges' => 'model/iges',
			'.igs' => 'model/iges',
			'.ips' => 'application/x-ipscript',
			'.ipx' => 'application/x-ipix',
			'.jpe' => 'image/jpeg',
			'.jpeg' => 'image/jpeg',
			'.jpg' => 'image/jpeg',
			'.js' => 'application/x-javascript',
			'.kar' => 'audio/midi',
			'.latex' => 'application/x-latex',
			'.lha' => 'application/octet-stream',
			'.lsp' => 'application/x-lisp',
			'.lzh' => 'application/octet-stream',
			'.m' => 'text/plain',
			'.man' => 'application/x-troff-man',
			'.me' => 'application/x-troff-me',
			'.mesh' => 'model/mesh',
			'.mid' => 'audio/midi',
			'.midi' => 'audio/midi',
			'.mif' => 'application/vnd.mif',
			'.mime' => 'www/mime',
			'.mov' => 'video/quicktime',
			'.movie' => 'video/x-sgi-movie',
			'.mp2' => 'audio/mpeg',
			'.mp3' => 'audio/mpeg',
			'.mpe' => 'video/mpeg',
			'.mpeg' => 'video/mpeg',
			'.mpg' => 'video/mpeg',
			'.mpga' => 'audio/mpeg',
			'.ms' => 'application/x-troff-ms',
			'.msh' => 'model/mesh',
			'.nc' => 'application/x-netcdf',
			'.oda' => 'application/oda',
			'.pbm' => 'image/x-portable-bitmap',
			'.pdb' => 'chemical/x-pdb',
			'.pdf' => 'application/pdf',
			'.pgm' => 'image/x-portable-graymap',
			'.pgn' => 'application/x-chess-pgn',
			'.png' => 'image/png',
			'.pnm' => 'image/x-portable-anymap',
			'.pot' => 'application/mspowerpoint',
			'.ppm' => 'image/x-portable-pixmap',
			'.pps' => 'application/mspowerpoint',
			'.ppt' => 'application/mspowerpoint',
			'.ppz' => 'application/mspowerpoint',
			'.pre' => 'application/x-freelance',
			'.prt' => 'application/pro_eng',
			'.ps' => 'application/postscript',
			'.qt' => 'video/quicktime',
			'.ra' => 'audio/x-realaudio',
			'.ram' => 'audio/x-pn-realaudio',
			'.ras' => 'image/cmu-raster',
			'.rgb' => 'image/x-rgb',
			'.rm' => 'audio/x-pn-realaudio',
			'.roff' => 'application/x-troff',
			'.rpm' => 'audio/x-pn-realaudio-plugin',
			'.rtf' => 'text/rtf',
			'.rtx' => 'text/richtext',
			'.scm' => 'application/x-lotusscreencam',
			'.set' => 'application/set',
			'.sgm' => 'text/sgml',
			'.sgml' => 'text/sgml',
			'.sh' => 'application/x-sh',
			'.shar' => 'application/x-shar',
			'.silo' => 'model/mesh',
			'.sit' => 'application/x-stuffit',
			'.skd' => 'application/x-koan',
			'.skm' => 'application/x-koan',
			'.skp' => 'application/x-koan',
			'.skt' => 'application/x-koan',
			'.smi' => 'application/smil',
			'.smil' => 'application/smil',
			'.snd' => 'audio/basic',
			'.sol' => 'application/solids',
			'.spl' => 'application/x-futuresplash',
			'.src' => 'application/x-wais-source',
			'.step' => 'application/STEP',
			'.stl' => 'application/SLA',
			'.stp' => 'application/STEP',
			'.sv4cpio' => 'application/x-sv4cpio',
			'.sv4crc' => 'application/x-sv4crc',
			'.swf' => 'application/x-shockwave-flash',
			'.t' => 'application/x-troff',
			'.tar' => 'application/x-tar',
			'.tcl' => 'application/x-tcl',
			'.tex' => 'application/x-tex',
			'.texi' => 'application/x-texinfo',
			'.texinfo' => 'application/x-texinfo',
			'.tif' => 'image/tiff',
			'.tiff' => 'image/tiff',
			'.tr' => 'application/x-troff',
			'.tsi' => 'audio/TSP-audio',
			'.tsp' => 'application/dsptype',
			'.tsv' => 'text/tab-separated-values',
			'.txt' => 'text/plain',
			'.unv' => 'application/i-deas',
			'.ustar' => 'application/x-ustar',
			'.vcd' => 'application/x-cdlink',
			'.vda' => 'application/vda',
			'.viv' => 'video/vnd.vivo',
			'.vivo' => 'video/vnd.vivo',
			'.vrml' => 'model/vrml',
			'.wav' => 'audio/x-wav',
			'.wrl' => 'model/vrml',
			'.xbm' => 'image/x-xbitmap',
			'.xlc' => 'application/vnd.ms-excel',
			'.xll' => 'application/vnd.ms-excel',
			'.xlm' => 'application/vnd.ms-excel',
			'.xls' => 'application/vnd.ms-excel',
			'.xlw' => 'application/vnd.ms-excel',
			'.xml' => 'text/xml',
			'.xpm' => 'image/x-xpixmap',
			'.xwd' => 'image/x-xwindowdump',
			'.xyz' => 'chemical/x-pdb',
			'.zip' => 'application/zip'
  		);
  		$ext = strrchr($filename, '.');
  		if(array_key_exists($ext, $mime))
   			return $mime[$ext];
   		return 'application/unknown';
	} 
	
?>