<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: edit_text_page.php				#
 # created		: 2005-09-08					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#

	/**
	 * @package ComaCMS
	 */
	class Edit_Text_Page {
		/**
		 * @access public
		 * @return void
		 * @param integer page_id
		 * @param integer history_id
		 */
		function NewPage($page_id, $history_id = 0) {
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages_text
				WHERE page_id=$page_id";
			$exists_result = db_result($sql);
			if($exists = mysql_fetch_object($exists_result)) {
				$sql = "INSERT INTO " . DB_PREFIX . "pages_text_history (page_id, text_page_text)
					VALUES ($history_id, '$exists->text_page_text')";
				db_result($sql);
				$sql = "UPDATE " . DB_PREFIX . "pages_text
					SET text_page_text='', text_page_html=''
					WHERE page_id='$page_id'";
				db_result($sql);
			}
			else {
				$sql = "INSERT INTO " . DB_PREFIX . "pages_text (page_id, text_page_text,text_page_html)
					VALUES ($page_id, '', '')";
				db_result($sql);
			}
		}
	
		function Save($page_id) {
			global $user, $admin_lang;
			$page_edit_comment = GetPostOrGet('page_edit_comment');
			$page_title = GetPostOrGet('page_title');
			$page_text = GetPostOrGet('page_text');
			if(GetPostOrGet('page_preview') != '')
				return $this->Edit($page_id, $page_title, $page_text, $page_edit_comment);
			if(GetPostOrGet('page_abort') != '') {
				header('Location: admin.php?page=pagestructure');
				die();
			}
			if($page_title != '' && $page_id != '' && $page_text != '')
			{
				$sql = "SELECT struct.*, text.*
				FROM ( " . DB_PREFIX. "pages struct
				LEFT JOIN " . DB_PREFIX . "pages_text text ON text.page_id = struct.page_id )
				WHERE struct.page_id='$page_id' AND struct.page_type='text'";
				$old_result = db_result($sql);
				if($old = mysql_fetch_object($old_result)) { // exists the page?
					if($old->page_title != $page_title || MakeSecure($old->text_page_text) != $page_text) {
						if(!($page_title == $old->page_title && $old->text_page_text == '')) {					
							$sql = "INSERT INTO " . DB_PREFIX . "pages_history (page_id, page_type, page_name, page_title, page_parent_id, page_lang, page_creator, page_date, page_edit_comment)
								VALUES($old->page_id, '$old->page_type', '$old->page_name', '$old->page_title', $old->page_parent_id, '$old->page_lang', $old->page_creator, $old->page_date, '$old->page_edit_comment')";
							db_result($sql);
							$lastid = mysql_insert_id();
							$sql = "INSERT INTO " . DB_PREFIX . "pages_text_history (page_id, text_page_text)
								VALUES ($lastid, '$old->text_page_text')";
							db_result($sql);
						}
						$html = convertToPreHtml($page_text);
						$sql = "UPDATE " . DB_PREFIX . "pages_text
							SET text_page_text='$page_text', text_page_html='$html'
							WHERE page_id='$old->page_id'";
						db_result($sql);
						$sql = "UPDATE " . DB_PREFIX . "pages
							SET page_creator=$user->ID, page_date=" . mktime() . ", page_title='$page_title', page_edit_comment='$page_edit_comment'
							WHERE page_id=$page_id";
						db_result($sql);
						header("Location: admin.php?page=pagestructure");
						return "Die Seite sollte gespeichert sein!";
					}
					else { // no changes
						// TODO: Show it to the user
						return "keine Veränderungen!!";
					}
				}
				else { // it dosen't
					// TODO: Show it to the user
					return "error2!!";
				}
			}
			else
			{
				//restore the old version if $change is given
				$change = GetPostOrGet('change');
				$sure = GetPostOrGet('sure');
				if(is_numeric($change)) {
					//load old version
					//load actual version
					$sql = "SELECT struct.*, text.*
						FROM ( " . DB_PREFIX. "pages struct
						LEFT JOIN " . DB_PREFIX . "pages_text text ON text.page_id = struct.page_id )
						WHERE struct.page_id='$page_id' AND struct.page_type='text'";
					$actual_result = db_result($sql);
					$sql = "SELECT *
						FROM (" . DB_PREFIX . "pages_history page
						LEFT JOIN " . DB_PREFIX . "pages_text_history text ON text.page_id = page.id ) 
						WHERE page.page_id=$page_id
						ORDER BY  page.page_date ASC
						LIMIT " . ($change - 1) . ",1";
					$old_result = db_result($sql);
					if(($old = mysql_fetch_object($old_result)) && ($actual = mysql_fetch_object($actual_result))) {
						if($sure == 1) {
							$sql = "INSERT INTO " . DB_PREFIX . "pages_history (page_id, page_type, page_name, page_title, page_parent_id, page_lang, page_creator, page_date, page_edit_comment)
								VALUES($actual->page_id, '$actual->page_type', '$actual->page_name', '$actual->page_title', $actual->page_parent_id, '$actual->page_lang', $actual->page_creator, $actual->page_date, '$actual->page_edit_comment')";
							db_result($sql);
							$lastid = mysql_insert_id();
							$sql = "INSERT INTO " . DB_PREFIX . "pages_text_history (page_id, text_page_text)
								VALUES ($lastid, '$actual->text_page_text')";
							db_result($sql);
							$html = convertToPreHtml($old->text_page_text);
							$sql = "UPDATE " . DB_PREFIX . "pages_text
								SET text_page_text='$old->text_page_text', text_page_html='$html'
								WHERE page_id='$page_id'";
							db_result($sql);
							$page_edit_comment = sprintf($admin_lang['restored_from_version'], $change);
							$sql = "UPDATE " . DB_PREFIX . "pages
								SET page_creator=$user->ID, page_date=" . mktime() . ", page_title='$old->page_title', page_edit_comment='$page_edit_comment'
								WHERE page_id=$page_id";
							db_result($sql);
							header("Location: admin.php?page=pagestructure");	
						}
						else {
							$out = '';
							$out .= "M�chten Sie diesen Text:<pre class=\"code\">$actual->text_page_text</pre>wirklich durch diesen Text:<pre class=\"code\">$old->text_page_text</pre>ersetzen?<br />
								<a href=\"admin.php?page=pagestructure&amp;action=save&amp;page_id=$page_id&amp;change=$change&amp;sure=1\" class=\"button\">" . $admin_lang['yes'] . "</a>
		 						<a href=\"admin.php?page=pagestructure&amp;action=info&amp;page_id=$page_id\" class=\"button\">" . $admin_lang['no'] . "</a>";
							return $out;
							
						}
					}
				}
				// TODO: Manage Errors and show them to the user
				return "error!!";
			}
		}
		
		function Edit($page_id, $title = '', $text = '', $edit_comment = '') {
			global $_SERVER, $admin_lang;
			
			$change = GetPostOrGet('change');
			$count = 1;
			$out = '';
			$page_data = null;
			$got_mysql = false;
			if($text == '' && $title == '') {
				if(is_numeric($change) && $text == '' && $title == '') {
					$out .= "<strong>Achtung:</strong> Sie bearbeiten nicht die aktuelle Version, wenn Sie speichern wird ihr Text den aktuellen überschreiben!";
					$sql = "SELECT *
						FROM (" . DB_PREFIX . "pages_history page
						LEFT JOIN " . DB_PREFIX . "pages_text_history text ON text.page_id = page.id ) 
						WHERE page.page_id=$page_id
						ORDER BY  page.page_date ASC
						LIMIT " . ($change - 1) . ",1";
				}
				else if($text == '' && $title == '') {
					$sql = "SELECT *
						FROM " . DB_PREFIX . "pages_history
						WHERE page_id = $page_id
						LIMIT 0,1";
					$count_result = db_result($sql);
					$count = mysql_num_rows($count_result);
					$sql = "SELECT struct.page_id, struct.page_title, text.text_page_text, struct.page_edit_comment
						FROM ( " . DB_PREFIX. "pages struct
						LEFT JOIN " . DB_PREFIX . "pages_text text ON text.page_id = struct.page_id )
						WHERE struct.page_id='$page_id' AND struct.page_type='text'";
				}
				$page_result = db_result($sql);
				if($page_data = mysql_fetch_object($page_result))
					$got_mysql = true;
			}	
			
			
			if($got_mysql || ($text != '' || $title != '')) {
				if($text != '' || $title != '') {
					$page_title = stripslashes($title);
					$page_text = stripslashes($text);
					$page_edit_comment = stripslashes($edit_comment);
					$show_preview = true;
				}
				else {
					$page_title = $page_data->page_title;
					$page_text = $page_data->text_page_text;
					$page_edit_comment = $admin_lang['edited'] . '...';
					$show_preview = false;
				}
				$page_text = str_replace('ä', '&auml;', $page_text);
				$page_text = str_replace('Ä', '&Auml;', $page_text);
				$page_text = str_replace('ü', '&uuml;', $page_text);
				$page_text = str_replace('Ü', '&Uuml;', $page_text);
				$page_text = str_replace('ö', '&ouml;', $page_text);
				$page_text = str_replace('Ö', '&Ouml;', $page_text);
				$page_text = str_replace('<', '&lt;', $page_text);
				$page_text = str_replace('>', '&gt;', $page_text);
				$page_text = str_replace('ß', '&szlig;', $page_text);
				$out .= "\t\t\t<fieldset><legend>Seite Bearbeiten</legend><form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
				<input type=\"hidden\" name=\"action\" value=\"save\" />
				<input type=\"hidden\" name=\"page_id\" value=\"$page_id\" />
				<input type=\"text\" name=\"page_title\" value=\"$page_title\" /><br />
				<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>
				<script type=\"text/javascript\" language=\"javascript\">
					writeButton(\"img/button_fett.png\",\"Formatiert Text Fett\",\"**\",\"**\",\"Fetter Text\",\"f\");
					writeButton(\"img/button_kursiv.png\",\"Formatiert Text kursiv\",\"//\",\"//\",\"Kursiver Text\",\"k\");
					writeButton(\"img/button_unterstrichen.png\",\"Unterstreicht den Text\",\"__\",\"__\",\"Unterstrichener Text\",\"u\");
					writeButton(\"img/button_ueberschrift.png\",\"Markiert den Text als Überschrift\",\"==== \",\" ====\",\"�berschrift\",\"h\");
				</script><br />
				<textarea id=\"editor\" class=\"edit\" name=\"page_text\">$page_text</textarea>
				" . $admin_lang['comment_on_change'] . ": <input name=\"page_edit_comment\" style=\"width:20em;\" value=\"" .  (($count == 0 ) ? $page_data->page_edit_comment : ((is_numeric($change)) ?  sprintf($admin_lang['edited_from_version'], $change) : $page_edit_comment)) . "\" maxlength=\"100\" type=\"text\"/><br />
				<input type=\"submit\" value=\"Speichern\" class=\"button\" />
				<input type=\"submit\" value=\"Vorschau\" name=\"page_preview\" class=\"button\" />
				<input type=\"submit\" value=\"Abbrechen\" name=\"page_abort\" class=\"button\"/>
			</form></fieldset>\r\n";
				if($show_preview)
					$out .= "<fieldset><legend>Vorschau</legend>"
						 . convertToPreHtml($page_text) ."</fieldset>";
				
			}
			return $out;
		}
	}
?>