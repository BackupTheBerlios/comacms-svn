<?php
/**
 * @package ComaCMS
 * @subpackage Dates
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : dates_admin.php
 # created              : 2006-03-10
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
  	/**
  	 * 
 	 */
 	require_once('classes/admin/admin_module.php');
 	require_once('modules/dates/dates.class.php');
 	
 	/**
 	 * @package ComaCMS
 	 * @subpackage Dates
 	 */
 	class Admin_Module_Dates extends Admin_Module {
 		
 		/**
		 * @access public
 		 * @param Sql SqlConnection
 		 * @param User User
 		 * @param array Lang
 		 * @param Config Config
 		 * @param ComaLate ComaLate
 		 * @param ComaLib ComaLib
 		 */
 		function Admin_Module_Dates(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_User = &$User;	
 			$this->_Lang = &$Lang;
 			$this->_Config = &$Config;
 			$this->_ComaLate = &$ComaLate;
 			$this->_ComaLib = &$ComaLib;
 		}
 		
 		/**
 		 * @access public
 		 * @param string Action 
 		 * @return string
 		 */
 		function GetPage($Action = '') {
 			$out = '';
 			switch ($Action) {
 				case 'new':	$out .= $this->_newPage();
 						break;
 				case 'add':	$out .= $this->_addPage();
 						break;
 				case 'delete':	$out .= $this->_deletePage();
 						break;
 				case 'edit':	$out .= $this->_editPage();
 						break;
 				case 'save':	$out .= $this->_savePage();
 						break;
 				default:	$out .= $this->_homePage();
 						break;
 			}
 			return $out;
 		}
 		
 		/**
 		 * The title of the actual-module-page
		 * @access public
		 * @return string
		 */
		function GetTitle() {
			return 'Dates-Module';
		}
			
		/**
		 * @access private
		 * @return string
		 */
		function _savePage() {
			
			$dateYear = GetPostOrGet('dateYear');
			$dateMonth = GetPostOrGet('dateMonth');
			$dateDay = GetPostOrGet('dateDay');
			$dateHour = GetPostOrGet('dateHour');
			$dateMinute = GetPostOrGet('dateMinute');
			$dateTopic = GetPostOrGet('dateTopic');
			$dateLocation = GetPostOrGet('dateLocation');
			$dateID = GetPostOrGet('dateID');

			if(is_numeric($dateID) && is_numeric($dateYear) && is_numeric($dateMonth) && is_numeric($dateDay) && is_numeric($dateHour) && is_numeric($dateMinute) && $dateTopic != '' && $dateLocation != '') {
				$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
				$dates->UpdateDate($dateID, $dateYear, $dateMonth, $dateDay, $dateHour, $dateMinute, $dateTopic, $dateLocation);
			}
			return $this->_HomePage();
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _addPage() {
			
			$dateYear = GetPostOrGet('dateYear');
			$dateMonth = GetPostOrGet('dateMonth');
			$dateDay = GetPostOrGet('dateDay');
			$dateHour = GetPostOrGet('dateHour');
			$dateMinute = GetPostOrGet('dateMinute');
			$dateTopic = GetPostOrGet('dateTopic');
			$dateLocation = GetPostOrGet('dateLocation');

			if(is_numeric($dateYear) && is_numeric($dateMonth) && is_numeric($dateDay) && is_numeric($dateHour) && is_numeric($dateMinute) && $dateTopic != '' && $dateLocation != '') {
				$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
				$dates->AddDate($dateYear, $dateMonth, $dateDay, $dateHour, $dateMinute, $dateTopic, $dateLocation);
			}
			return $this->_HomePage();
		}		
		
		/**
		 * The page with a form to create a new date
 		 * @access private
 		 * @return string
 		 */
 		function _newPage() {
 			$out = "\t\t\t<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"module_dates\" />
				<input type=\"hidden\" name=\"action\" value=\"add\" />
				<fieldset>
					<legend>{$this->_Lang['add_a_new_date']}</legend>
					<div class=\"row\">
						<label>
							<strong>{$this->_Lang['date']}:</strong>
							<span class=\"info\">Dies ist das Datum, an dem die Veranstaltung stattfindet</span>
							</label>
						" . Dates::DateSelecter(mktime(), mktime(), 'date', 7) . "
					</div>
					<div class=\"row\">
						<label>
							<strong>{$this->_Lang['location']}:</strong>
							<span class=\"info\">Gemeint ist hier der Ort an welchem die Veranstaltung stattfindet.</span>
						</label>
						<input type=\"text\" name=\"dateLocation\" maxlength=\"60\" value=\"\" />
					</div>
					<div class=\"row\">
						<label>
							<strong>{$this->_Lang['topic']}:</strong>
							<span class=\"info\">Dies ist die Beschreibung des Termins</span>
						</label>
						<textarea name=\"dateTopic\" ></textarea>
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"{$this->_Lang['save']}\" />&nbsp;<input type=\"reset\" class=\"button\" value=\"{$this->_Lang['reset']}\" />
					</div>
				</fieldset>
			</form>";
			return $out;
 		}
		
		/**
	 	 * @access private
	 	 * @param array admin_lang
	 	 * @return string
	 	 */
	 	function _editPage() {
	 		$dateID = GetPostOrGet('dateID');
	 		if(is_numeric($dateID)) {
	 			$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
	 			$dateEntry = $dates->GetDate($dateID);
	 			if(count($dateEntry) > 0) {
	 				$out = "<h2>Termin bearbeiten</h2>
	 			<form method=\"post\" action=\"admin.php\">
	 			<input type=\"hidden\" name=\"page\" value=\"module_dates\"/>
	 			<input type=\"hidden\" name=\"action\" value=\"save\"/>
	 			<input type=\"hidden\" name=\"dateID\" value=\"$dateID\"/>
	 			<fieldset>
	 				<legend>Termin bearbeiten</legend>
	 				<div class=\"row\">
	 					<label><strong>{$this->_Lang['date']}:</strong> <span class=\"info\">Dies ist das Datum, an dem die Veranstaltung stattfindet</span></label>
	 					" . Dates::DateSelecter($dateEntry['DATE_DATE'], mktime()) . "
	 				</div>
	 				<div class=\"row\">
	 					<label><strong>{$this->_Lang['location']}:</strong> <span class=\"info\">Gemeint ist hier der Ort an welchem die Veranstaltung stattfindet.</span></label>
	 					<input type=\"text\" name=\"dateLocation\" value=\"{$dateEntry['DATE_LOCATION']}\"/>
	 				</div>
	 				<div class=\"row\">
	 					<label>
	 						<strong>{$this->_Lang['topic']}:</strong>
	 						<span class=\"info\">Dies ist die Beschreibung des Termins</span>
	 					</label>
	 					<input type=\"text\" name=\"dateTopic\" value=\"{$dateEntry['DATE_TOPIC']}\"/>
	 				</div>
	 				<div class=\"row\">
	 					<input type=\"submit\" class=\"button\" value=\"{$this->_Lang['save']}\" />
	 					<input type=\"reset\" class=\"button\" value=\"{$this->_Lang['reset']}\" />
	 				</div>
	 			</fieldset>
	 			</form>";
	 			return $out;
	 			}
	 		}
	 		return $this->_homePage();
	 	}
		
 		/**
 		 * @access private
 		 * @return string
 		 */
 		function _homePage() {
 			$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
 			$datesArray = $dates->FillArray(-1, false);
 			$out = "<h2>{$this->_Lang['dates']}</h2>
 				<a href=\"admin.php?page=module_dates&amp;action=new\" class=\"button\">{$this->_Lang['add_a_new_date']}</a>
				<table class=\"text_table full_width\">
					<thead>
						<tr>
							<th>{$this->_Lang['date']}</th>
							<th>{$this->_Lang['location']}</th>
							<th>{$this->_Lang['topic']}</th>
							<th>{$this->_Lang['creator']}</th>
							<th>{$this->_Lang['actions']}</th>
						</tr>
					</thead>
					<tbody>\r\n";

			foreach($datesArray as $dateEntry) {
				$out .= "\t\t\t\t\t<tr ID=\"dateid{$dateEntry['DATE_ID']}\">
						<td>
							" . date("d.m.Y H:i", $dateEntry['DATE_DATE']) . "
						</td>
						<td>
							{$dateEntry['DATE_LOCATION']}
						</td>
						<td>
							" . nl2br($dateEntry['DATE_TOPIC']) . "
						</td>
						<td>
							" . getUserByID($dateEntry['DATE_CREATOR']) . "
						</td>
						<td colspan=\"2\">
							<a href=\"admin.php?page=module_dates&amp;action=edit&amp;dateID={$dateEntry['DATE_ID']}\" title=\"{$this->_Lang['edit']}\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"{$this->_Lang['edit']}\" title=\"" . $this->_Lang['edit'] . "\"/></a>
							&nbsp;<a href=\"admin.php?page=module_dates&amp;action=delete&amp;dateID={$dateEntry['DATE_ID']}\" title=\"{$this->_Lang['delete']}\"><img src=\"./img/del.png\" height=\"16\" width=\"16\" alt=\"{ $this->_Lang['delete']}\" title=\"" . $this->_Lang['delete'] . "\"/></a>
						</td>
					</tr>\r\n";
				
			}
			$out .= "</tbody>
				</table>"; 
			return $out;
 		}
 		
 		/**
 		 * @access private
 		 * @return string
 		 */
 		function _deletePage() {
	 		$confirmation = GetPostOrGet('confirmation');
	 		$dateID = GetPostOrGet('dateID');
	 		$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
	 		// has the user confirmed that he is sure to delete the date?
	 		if($confirmation == 1 && is_numeric($dateID))
	 			$dates->DeleteDate($dateID);
			else if(is_numeric($dateID)) {
				$dateEntry = $dates->GetDate($dateID);
				if(count($dateEntry) > 0) {
					$out = "<h2>{$this->_Lang['delete_date']}</h2>\r\n";
					$out .= sprintf($this->_Lang['Do_you_really_want_to_delete_the_date_%date_topic%_for_the_%date%_at_%time%_o_clock'], $dateEntry['DATE_TOPIC'], date("d.m.Y", $dateEntry['DATE_DATE']),  date("H:i", $dateEntry['DATE_DATE']));
					$out .= "<br />
			<a class=\"button\" href=\"admin.php?page=module_dates&amp;action=delete&amp;dateID=$dateID&amp;confirmation=1\" title=\"Wirklich L&ouml;schen\">{$this->_Lang['yes']}</a>
			<a class=\"button\" href=\"admin.php?page=module_dates\" title=\"Nicht L&ouml;schen\">{$this->_Lang['no']}</a>";
					return $out;
				}
			}
			return $this->_homePage();
	 	}
 	}
?>