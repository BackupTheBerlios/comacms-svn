<?php
/*****************************************************************************
 *
 *  file		: special.php
 *  created		: 2005-08-10
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

	define("COMACMS_RUN", true);
 
	include('common.php');
	
	include('./lang/' . $user->Language  . '/admin_lang.php');
	if(!isset($extern_page))
		header('Locaction: index.php');
	$text = '';
	$title = '';
	if($extern_page == 'login') {
		$title = "Login";
		$text = "<form method=\"post\" action=\"admin.php\">
			<input type=\"hidden\" name=\"page\" value=\"admincontrol\" />
			<table>
				<tr><td>Loginname:</td><td><input type=\"text\" name=\"login_name\" /></td></tr>
				<tr><td>Passwort:</td><td><input type=\"password\" name=\"login_password\" /></td></tr>
				<tr><td colspan=\"2\"><input type=\"submit\" value=\"Login\" /></td></tr>
			</table>
		</form>";
	}
	elseif($extern_page == '404')
	{
		$title = 'Seite nicht gefunden.';
		$text = 'Die seite wurde leider nicht gefunden';
	}
	if($text == '')
		header('Locaction: index.php');
	$page->Title = $title;
	$page->SetText($text);
	//$page = str_replace('[menu]', generatemenu(@$internal_style, 1, $extern_page), $page);
	//$page = str_replace('[menu2]', generatemenu(@$internal_style, 2, $extern_page), $page);
	$page->Template = str_replace("[inlinemenu]", '', $page->Template);
	$page->Template = str_replace("[position]", "<a href=\"special.php?page=$extern_page\">$title</a>", $page->Template);
	$page->Template = preg_replace("/\<forinlinemenu\>(.+?)\<\/forinlinemenu\>/s", "", $page->Template);
	echo $page->OutputHTML();

?>