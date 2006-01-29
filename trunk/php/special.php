<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: special.php					#
 # created		: 2005-08-10					#
 # copyright		: (C) 2005-2006 The ComaCMS-Team		#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
	
	/**
	 * @ignore
	 */
	define("COMACMS_RUN", true);
 
	include('common.php');
	
	/**
	 * @ignore
	 */
	include('./lang/' . $user->Language  . '/admin_lang.php');
	
	if(!isset($extern_page))
		header('Locaction: index.php');
	$text = '';
	$title = '';
	if($extern_page == 'login') {
		$error = GetPostOrGet('error');
		$text_error = '';
		if($error == '1')
			$text_error = 'Der Login wurde nicht angegeben.';
		if($error == '2')
			$text_error = 'Das Passwort wurde nicht angegeben.';
		if($error == '3')
			$text_error = 'Es wurden keine Eingaben gemacht.';
		if($error == '4')
			$text_error = 'Der Benutzer und(oder) das Passwort sind falsch.';
		if($text_error != '')
			$text_error = "\r\n<strong>" . $text_error . '</strong>';
		$title = "Login";
		$text = "<form method=\"post\" action=\"admin.php\">
			<input type=\"hidden\" name=\"page\" value=\"admincontrol\" />$text_error
			<table>
				<tr><td>Loginname:</td><td><input type=\"text\" name=\"login_name\" /></td></tr>
				<tr><td>Passwort:</td><td><input type=\"password\" name=\"login_password\" /></td></tr>
				<tr><td colspan=\"2\"><input type=\"submit\" value=\"Login\" class=\"button\"/></td></tr>
			</table>
		</form>";
	}
	elseif($extern_page == '404') {	
		$want = GetPostOrGet('want');
		$title = 'Seite nicht gefunden.';
		$text = "Die Seite mit dem Namen &quot;$want&quot; wurde leider nicht gefunden.<br />
			Falls die Seite aber da sein m?sste, melden sie sich bitte beim Seitenbetreiber.";
	}
	elseif($extern_page == '410') {	//Gone/Deleted
		$text = ' '; 
	}
	elseif($extern_page == 'image') {
		
		$imageID = GetPostOrGet('id');
		$imageFile = GetPostOrGet('file');
		if(is_numeric($imageID) || !empty($imageFile)) {
			$title = 'Bild';
			$condition = 'file_id = ' . $imageID;
			if(empty($imageID))
				$condition = "file_name = '$imageFile'";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "files
				WHERE $condition
				LIMIT 0,1";
			$imageResult = $sqlConnection->SqlQuery($sql);
			if($imageData = mysql_fetch_object($imageResult)) {
				$text = "<img src=\"" . generateUrl($imageData->file_path) ."\"/>";
				$exif = exif_read_data($imageData->file_path, 0, true);

				foreach ($exif as $key => $section) {
   					foreach ($section as $name => $val) {
       $text .= "$key.$name: $val<br />\n";
   }
				}
			}
		}
		
	}
	if($text == '') {
		header('Location: index.php');
		die();
	}
	$page->Title = $title;
	$page->SetText($text);
	$page->Template = str_replace("[INLINEMENU]", '', $page->Template);
	$page->Template = str_replace("[POSITION]", "<a href=\"special.php?page=$extern_page\">$title</a>", $page->Template);
	$page->Template = preg_replace("/\<forinlinemenu\>(.+?)\<\/forinlinemenu\>/s", "", $page->Template);
	$page->Template = preg_replace("/\<notinadmin\>(.+?)\<\/notinadmin\>/s", '$1', $page->Template);
	echo $page->OutputHTML();
	echo "\r\n<!-- rendered in " . round(getmicrotime(microtime()) - getmicrotime($starttime), 4) . ' seconds with ' . $sqlConnection->QueriesCount .' SQL queries -->';
?>
