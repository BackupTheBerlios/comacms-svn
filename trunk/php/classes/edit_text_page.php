<?php
/*****************************************************************************
 *
 *  file		: edit_text_page.php
 *  created		: 2005-09-08
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
 
	class Edit_Text_Page {
		
		function NewPage($page_id) {
			global $extern_page_text;
			if($extern_page_text != '') {
				$sql = "INSERT INTO " . DB_PREFIX . "pages_text (page_id, text_page_text,text_page_html)
					VALUES ($page_id, '', '')";
				db_result($sql);
			}
		}
	
		function Save($page_id) {
			global $_SERVER, $extern_page_title, $extern_page_text, $user;
			
			if($extern_page_title != '' && $page_id != '' && $extern_page_text != '')
			{
				$sql = "SELECT struct.page_id, struct.page_title, text.text_page_text
				FROM ( " . DB_PREFIX. "pages struct
				LEFT JOIN " . DB_PREFIX . "pages_text text ON text.page_id = struct.page_id )
				WHERE struct.page_id='$page_id' AND struct.page_type='text'";
				$old_result = db_result($sql);
				if($old = mysql_fetch_object($old_result)) { // exists the page?
					if($old->page_title != $extern_page_title || $old->text_page_text != $extern_page_text) {
						//TODO: backup the old into cms_pages_text_history
						
						$html = convertToPreHtml($extern_page_text);
						$sql = "UPDATE " . DB_PREFIX . "pages_text
							SET text_page_text='$extern_page_text', text_page_html='$html'
							WHERE page_id='$old->page_id'";
						db_result($sql);
						$sql = "UPDATE " . DB_PREFIX . "pages
							SET page_creator=$user->Id, page_date=" . mktime() . ", page_title='$extern_page_title'
							WHERE page_id=$page_id";
						db_result($sql);
						return "Die Seite sollte gespeichert sein!";
					}
					else { // no changes
						// TODO: Show it to the user
						return "no changes!!";
					}
				}
				else { // it dosen't
					// TODO: Show it to the user
					return "error!!";
				}
				
			}
			else
			{
				// TODO: Manage Errors and show them to the user
				return "error!!";
			}
		}
		
		function Edit($page_id) {
			global $_SEERVER;
			$sql = "SELECT struct.page_id, struct.page_title, text.text_page_text
				FROM ( " . DB_PREFIX. "pages struct
				LEFT JOIN " . DB_PREFIX . "pages_text text ON text.page_id = struct.page_id )
				WHERE struct.page_id='$page_id' AND struct.page_type='text'";
			$page_result = db_result($sql);
			$page_data = mysql_fetch_object($page_result);
			$out = "\t\t\t<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
				<input type=\"hidden\" name=\"action\" value=\"save\" />
				<input type=\"hidden\" name=\"page_id\" value=\"" . $page_data->page_id . "\" />
				<input type=\"text\" name=\"page_title\" value=\"" . $page_data->page_title . "\" /><br />
				<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>
				<script type=\"text/javascript\" language=\"javascript\">
					writeButton(\"img/button_fett.png\",\"Formatiert Text Fett\",\"**\",\"**\",\"Fetter Text\",\"f\");
					writeButton(\"img/button_kursiv.png\",\"Formatiert Text kursiv\",\"//\",\"//\",\"Kursiver Text\",\"k\");
					writeButton(\"img/button_unterstrichen.png\",\"Unterstreicht den Text\",\"__\",\"__\",\"Unterstrichener Text\",\"u\");
					writeButton(\"img/button_ueberschrift.png\",\"Markiert den Text als Überschrift\",\"=== \",\" ===\",\"Überschrift\",\"h\");
				</script><br />
				<textarea id=\"editor\" class=\"edit\" name=\"page_text\">".$page_data->text_page_text."</textarea>
				<input type=\"reset\" value=\"Zurücksetzten\" />
				<input type=\"submit\" value=\"Speichern\" />
			</form>";
			return $out;
		}
	}
?>