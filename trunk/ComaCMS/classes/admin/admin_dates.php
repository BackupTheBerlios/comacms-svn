<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: admin_dates.php				#
 # created		: 2005-10-03					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
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
	require_once('./classes/admin/admin.php');
	
	
	/**
	 * @package ComaCMS
	 */
	/* class Admin_Dates extends Admin{
	 	
	 	/**
	 	 * @access public
	 	 * @param string action
	 	 * @param array admin_lang
	 	 * @return string
	 	 */
/*	 	function GetPage($action, $admin_lang) {
			$out = "\t\t\t<h3>" . $admin_lang['dates'] . "</h3><hr />\r\n";
		 	$action = strtolower($action);
		 	switch ($action) {
		 		case 'save':		$out .= $this->_saveDate($admin_lang);
		 					break;
		 		case 'delete':		$out .= $this->_deleteDate($admin_lang);
		 					break;
		 		case 'edit':		$out .= $this->_editDate($admin_lang);
		 					break;
		 		case 'new':		$out .= $this->_newDate($admin_lang);
		 					break;
		 		case 'add':		$out .= $this->_addDate($admin_lang);
		 					break;
		 		default:		$out .= $this->_homePage($admin_lang);
		 	}
			return $out;
	 	}
	 	
	 	/**
	 	 * @access private
	 	 * @param array admin_lang
	 	 * @return string
	 	 */
/*	 	function _homePage($admin_lang) {
	 		$out = '';
	 		$out .= "<a href=\"admin.php?page=dates&amp;action=new\" class=\"button\">" . $admin_lang['add_a_new_date'] . "</a>
				<table class=\"text_table full_width\">
					<thead>
						<tr>
							<th>" . $admin_lang['date'] . "</th>
							<th>" . $admin_lang['location'] . "</th>
							<th>" . $admin_lang['topic'] . "</th>
							<th>" . $admin_lang['creator'] . "</th>
							<th>" . $admin_lang['actions'] . "</th>
						</tr>
					</thead>\r\n";

			// write all news entries
			$sql = "SELECT *
				FROM " . DB_PREFIX . "dates
				ORDER BY date_date ASC";
			$result = db_result($sql);
			while($row = mysql_fetch_object($result)) {
				$out .= "\t\t\t\t\t<tr ID=\"dateid" . $row->date_id . "\">
						<td>
							" . date("d.m.Y H:i", $row->date_date) . "
						</td>
						<td>
							" . $row->date_place . "
						</td>
						<td>
							" . nl2br($row->date_topic) . "
						</td>
						<td>
							" . getUserByID($row->date_creator) . "
						</td>
						<td colspan=\"2\">
							<a href=\"admin.php?page=dates&amp;action=edit&amp;date_id=" . $row->date_id . "\" title=\"" . $admin_lang['edit'] . "\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>
							&nbsp;<a href=\"admin.php?page=dates&amp;action=delete&amp;date_id=" . $row->date_id . "\" title=\"" . $admin_lang['delete'] . "\"><img src=\"./img/del.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>
						</td>
					</tr>\r\n";
				
			}
			$out .= "</table>"; 
			return $out;
	 	}
	 	
	 	/**
	 	 * @access private
	 	 * @param array admin_lang
	 	 * @return string
	 	 */
/*	 	function _editDate($admin_lang) {
	 		$date_id = GetPostOrGet('date_id');
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX ."dates
	 			WHERE date_id = $date_id";
	 		$date_result = db_result($sql);
	 		if($date = mysql_fetch_object($date_result)) {
	 			$out = "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
	 			<input type=\"hidden\" name=\"page\" value=\"dates\"/>
	 			<input type=\"hidden\" name=\"action\" value=\"save\"/>
	 			<input type=\"hidden\" name=\"date_id\" value=\"$date->date_id\"/>
	 			<table>
	 				<tr>
	 					<td>" . $admin_lang['date'] . ": <span class=\"info\">Dies ist das Datum, an dem die Veranstaltung stattfindet</span></td>
	 					<td>" . $this->DateSelecter($date->date_date, mktime()) . "</td></tr>
	 				<tr>
	 					<td>" . $admin_lang['location'] . ": <span class=\"info\">Gemeint ist hier der Ort an welchem die Veranstaltung stattfindet.</td>
	 					<td><input type=\"text\" name=\"date_place\" value=\"$date->date_place\"/></td></tr>
	 				<tr>
	 					<td>" . $admin_lang['topic'] . ": <span class=\"info\">Dies ist die Beschreibung des Termins</span></td>
	 					<td><input type=\"text\" name=\"date_topic\" value=\"$date->date_topic\"/></td></tr>
	 				<tr>
	 					<td></td><td><input type=\"submit\" class=\"button\" value=\"Speichern\" />&nbsp;<input type=\"reset\" class=\"button\" value=\"" . $admin_lang['reset'] . "\" /></td>
	 				</tr>
	 			</table>
	 			</form>";
	 			return $out;
	 		}
	 		return $this->_homePage($admin_lang);
	 	}
	 	
	 	/**
	 	 * @access private
	 	 * @param array admin_lang
	 	 * @return string
	 	 */
/*	 	function _saveDate($admin_lang) {
	 		
	 		
	 		$date_id = GetPostOrGet('date_id');
	 		$date_topic = GetPostOrGet('date_topic');
	 		$date_place = GetPostOrGet('date_place');
	 		if($date_topic !== null && $date_place !== null && is_numeric($date_id)) {
	 			$date_date =  mktime(GetPostOrGet('date_hour'), GetPostOrGet('date_minute'),0, GetPostOrGet('date_month'), GetPostOrGet('date_day'), GetPostOrGet('date_year'));
				$sql = "UPDATE " . DB_PREFIX . "dates
					SET date_topic= '$date_topic',
					date_place= '$date_place',
					date_date='$date_date'
					WHERE date_id=$date_id";
				db_result($sql);
			} 
	 		header("Location: " . $_SERVER['PHP_SELF'] . "?page=dates");
	 	}
	 	
	 	/**
	 	 * @access private
	 	 * @param array admin_lang
	 	 * @return string
	 	 */
/*	 	function _newDate($admin_lang) {
	 		global $user;
	 		$out = "\t\t\t<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
				<input type=\"hidden\" name=\"page\" value=\"dates\" />
				<input type=\"hidden\" name=\"action\" value=\"add\" />
				<table>
					<tr>
						<td>" . $admin_lang['date'] . ": <span class=\"info\">Dies ist das Datum, an dem die Veranstaltung stattfindet</span></td>
						<td>" . $this->DateSelecter(mktime(), mktime()) . "</td>
					</tr>
					<tr>
						<td>" . $admin_lang['location'] . ": <span class=\"info\">Gemeint ist hier der Ort an welchem die Veranstaltung stattfindet.</span></td>
						<td><input type=\"text\" name=\"date_place\" maxlength=\"60\" value=\"\" /></td>
					</tr>
					<tr>
						<td>" . $admin_lang['topic'] . ": <span class=\"info\">Dies ist die Beschreibung des Termins</span></td>
						<td><input type=\"text\" name=\"date_topic\" maxlength=\"150\" /></td>
					</tr>
					<tr>
						<td>Eingelogt als " . $user->Showname . " &nbsp;</td><td><input type=\"submit\" class=\"button\" value=\"Speichern\" />&nbsp;<input type=\"reset\" class=\"button\" value=\"" . $admin_lang['reset'] . "\" /></td>
					</tr>
				</table>
			</form>";
			return $out;
	 	}
	 	
	 	/**
	 	 * @access private
	 	 * @param array admin_lang
	 	 * @return string
	 	 */
/*	 	function _addDate($admin_lang) {
	 		global $user;
	 		$date_topic = GetPostOrGet('date_topic');
	 		$date_place = GetPostOrGet('date_place');
	 		if($date_topic !== null && $date_place !== null) {
	 			$date_date =  mktime(GetPostOrGet('date_hour'), GetPostOrGet('date_minute'),0, GetPostOrGet('date_month'), GetPostOrGet('date_day'), GetPostOrGet('date_year'));
				$sql = "INSERT INTO " . DB_PREFIX . "dates (date_topic, date_place, date_date, date_creator)
					VALUES ('$date_topic', '$date_place', '$date_date', '$user->ID')";
				db_result($sql);
				
			} 
			
			header("Location: " . $_SERVER['PHP_SELF'] . "?page=dates");
	 	}
	 	
	 	/**
	 	 * @access private
	 	 * @param array admin_lang
	 	 * @return string
	 	 */
/*	 	function _deleteDate($admin_lang) {
	 		$sure = GetPostOrGet('sure');
	 		$date_id = GetPostOrGet('date_id');
	 		if($sure == 1 && is_numeric($date_id)) {
	 			$sql = "DELETE FROM " . DB_PREFIX . "dates
	 				WHERE date_id=$date_id";
				db_result($sql);
			}
			elseif(is_numeric($date_id)) {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "dates
					WHERE date_id=$date_id";
				$result = db_result($sql);
				if($date = mysql_fetch_object($result)) {
					$out = "Den Newseintrag &quot;" . $date->date_topic . "&quot; am " . date("d.m.Y", $date->date_date) . " um " . date("H:i", $date->date_date) . " Uhr wirklich l&ouml;schen?<br />
			<a href=\"" . $_SERVER['PHP_SELF'] . "?page=dates&amp;action=delete&amp;date_id=" . $date_id . "&amp;sure=1\" title=\"Wirklich L&ouml;schen\">ja</a> &nbsp;&nbsp;&nbsp;&nbsp;
			<a href=\"" . $_SERVER['PHP_SELF'] . "?page=dates\" title=\"Nicht L&ouml;schen\">Nein</a>";
			
					return $out;
				}
			}
			header("Location: " . $_SERVER['PHP_SELF'] . "?page=dates");
	 	}
	 	
	 	/**
	 	 * DateSelecter
	 	 * 
	 	 * Creates a 'control' to select a date without typing something in by keyboard
	 	 * 
	 	 * @access public
	 	 * @param timestamp selected_date
	 	 * @param timestamp $start_date
	 	 * @param string prefix
	 	 * @param integer years
	 	 * @return string
	 	 */
/*	 	function DateSelecter($selected_date, $startdate, $prefix = 'date', $years = 10) {
	 		$selected_year = date('Y', $selected_date);
	 		$endyear = date('Y', $startdate) + $years;
	 		$selected_day = date('j', $selected_date);
	 		$selected_month = date('n', $selected_date);
	 		$selected_hour = date('G', $selected_date);
	 		$selected_minute = date('i', $selected_date);
	 			 			 		
	 		$out = "<select id=\"" . $prefix . "_day\" name=\"" . $prefix . "_day\">";
	 		for($i = 1; $i <= 31;$i++) {
	 			if($i == $selected_day)
	 				$out .= "<option selected=\"selected\" value=\"$i\">" . substr('0'.$i,-2) . "</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">" . substr('0'.$i,-2) . "</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
				<select id=\"" . $prefix . "_month\" name=\"" . $prefix . "_month\">";
	 		for($i = 1; $i <= 12;$i++) {
	 			if($i == $selected_month)
	 				$out .= "<option selected=\"selected\" value=\"$i\">" . substr('0'.$i,-2) . "</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">" . substr('0'.$i,-2) . "</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
	 			<select id=\"" . $prefix . "_year\" name=\"" . $prefix . "_year\">";
	 		for($i = ($selected_year - $years < 1970) ? 1970 : ($selected_year - $years); $i <= $endyear; $i++) {
	 			if($i == $selected_year)
	 				$out .= "<option selected=\"selected\" value=\"$i\">$i</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">$i</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
				<select id=\"" . $prefix . "_hour\" name=\"" . $prefix . "_hour\">";
	 		for($i = 0; $i <= 23; $i++) {
	 			if($i == $selected_hour)
	 				$out .= "<option selected=\"selected\" value=\"$i\">" . substr('0'.$i,-2) . "</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">" . substr('0'.$i,-2) . "</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
				<select id=\"" . $prefix . "_minute\" name=\"" . $prefix . "_minute\">";
	 		for($i = 0; $i <= 59;$i++) {
	 			if($i == $selected_minute)
	 				$out .= "<option selected=\"selected\" value=\"$i\">" . substr('0'.$i,-2) . "</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">" . substr('0'.$i,-2) . "</option>\r\n";
	 		}
	 		$out .= "</select>";
	 		return $out;
	 	}
	 }*/
?>
