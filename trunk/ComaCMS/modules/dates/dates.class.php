<?php
/**
 * @package ComaCMS
 * @subpackage Dates
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : dates.class.php
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
	 * @package ComaCMS
 	 * @subpackage Dates
	 */
	class Dates {
		/**
		 * @access private
		 * @var Sql
		 */
		var $_SqlConnection;
		
		/**
		 * @access private
		 * @var ComaLib
		 */
 		var $_ComaLib;
 		
 		/**
 		 * @access private
 		 * @var User
 		 */
 		var $_User;
 		
 		/**
 		 * @access private
 		 * @var Config
 		 */
 		var $_Config;
 		
 		/**
 		 * @access public
 		 * @param Sql SqlConnection
 		 * @param ComaLib ComaLib
 		 * @param User User
 		 * @param Config Config
 		 */
 		function Dates(&$SqlConnection, &$ComaLib, &$User, &$Config) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_ComaLib = &$ComaLib;
 			$this->_User = &$User;
 			$this->_Config = &$Config;
 		}
 		
 		/**
 		 * @access public
 		 * @param integer DateID
 		 * @return void
 		 */
 		function DeleteDate($DateID = -1) {
 			if(is_numeric($DateID)) {
 				$sql = "DELETE FROM " . DB_PREFIX . "dates
	 				WHERE date_id=$DateID
	 				LIMIT 1";
 				$this->_SqlConnection->SqlQuery($sql);
 			}
 		}
 		
 		/**
 		 * @access public
 		 * @return array
 		 * @param integer DateID
 		 */
 		function GetDate($DateID = -1) {
 			if(is_numeric($DateID)) {
 				$sql = "SELECT date_id, date_date, date_topic, date_location, date_creator
					FROM " . DB_PREFIX . "dates
					WHERE date_id=$DateID
					LIMIT 1";
				$dateResult = $this->_SqlConnection->SqlQuery($sql);
				if($dateEntry = mysql_fetch_object($dateResult)) {
					$dateArray =  array('DATE_ID' => $dateEntry->date_id,
 							'DATE_DATE' => $dateEntry->date_date,
 							'DATE_TOPIC' => $dateEntry->date_topic,
 							'DATE_LOCATION' => $dateEntry->date_location,
 							'DATE_CREATOR' => $dateEntry->date_creator
 							);
					return $dateArray;
				}
				else
					return array();
 			}
 		}
 		
 		/**
 		 * @access public
 		 * @param integer MaxCount
 		 * @param boolean ConvertTimestamp
 		 * @return array
 		 */
 		function FillArray($MaxCount = 6, $ConvertTimestamp = true) {
 			$datesArray = array();
 			
 			if(!is_numeric($MaxCount))
 				$MaxCount =  6;
 			$sql = "SELECT date_id, date_date, date_topic, date_creator, date_location
 				FROM " . DB_PREFIX . "dates
 				ORDER BY date_date ASC
 				LIMIT $MaxCount";
 			if($MaxCount < 0)
 				$sql = "SELECT date_id, date_date, date_topic, date_creator, date_location
 				FROM " . DB_PREFIX . "dates
 				ORDER BY date_date ASC";
 			
 			$datesResult = $this->_SqlConnection->SqlQuery($sql);
 			while($dateEntry = mysql_fetch_object($datesResult)) {
 				$datesArray[] = array('DATE_ID' => $dateEntry->date_id,
 							'DATE_DATE' => ($ConvertTimestamp) ? date('d.m.Y H:i', $dateEntry->date_date) : $dateEntry->date_date,
 							'DATE_TOPIC' => nl2br($dateEntry->date_topic),
 							'DATE_LOCATION' => $dateEntry->date_location,
 							'DATE_CREATOR' => $dateEntry->date_creator
 						);
 			}
 			return $datesArray;
 		}
 		/**
 		 * @access public
 		 * @param string Location
 		 * @param integer MaxCount
 		 * @param boolean ConvertTimestamp
 		 * @return array
 		 */
 		function ExtendedFillArray($Location, $MaxCount = 6, $ConvertTimestamp = true) {
 			$datesArray = array();
 			
 			if(!is_numeric($MaxCount))
 				$MaxCount =  6;
 			$sql = "SELECT date_id, date_date, date_topic, date_creator, date_location
 				FROM " . DB_PREFIX . "dates
 				WHERE date_location LIKE '$Location'
 				ORDER BY date_date ASC
 				LIMIT $MaxCount";
 			if($MaxCount < 0)
 				$sql = "SELECT date_id, date_date, date_topic, date_creator, date_location
 				FROM " . DB_PREFIX . "dates
 				WHERE date_location LIKE '$Location'
 				ORDER BY date_date ASC";
 			
 			$datesResult = $this->_SqlConnection->SqlQuery($sql);
 			while($dateEntry = mysql_fetch_object($datesResult)) {
 				$datesArray[] = array('DATE_ID' => $dateEntry->date_id,
 							'DATE_DATE' => ($ConvertTimestamp) ? date('d.m.Y H:i', $dateEntry->date_date) : $dateEntry->date_date,
 							'DATE_TOPIC' => nl2br($dateEntry->date_topic),
 							'DATE_LOCATION' => $dateEntry->date_location,
 							'DATE_CREATOR' => $dateEntry->date_creator
 						);
 			}
 			return $datesArray;
 		}
 		
 		/** AddDate
 		 * Adds a Date into the datelist
 		 * @access public
 		 * @param integer Year
 		 * @param integer Month
 		 * @param integer Day
 		 * @param integer Hour
 		 * @param integer Minute
 		 * @param string Topic
 		 * @param string Location
 		 * @return void
 		 */
 		function AddDate($Year, $Month, $Day, $Hour, $Minute, $Topic, $Location) {
 			if(is_numeric($Year) && is_numeric($Month) && is_numeric($Day) && is_numeric($Hour) && is_numeric($Minute) && $Location != '' && $Topic != '') {
 				
 				$date = mktime($Hour, $Minute, 0, $Month, $Day, $Year);
 				$sql = "INSERT INTO " . DB_PREFIX . "dates (date_topic, date_location, date_date, date_creator)
					VALUES ('$Topic', '$Location', '$date', '{$this->_User->ID}')";
				$this->_SqlConnection->SqlQuery($sql);
 			}
 		}
 		
 		/** UpdateDate
 		 * @access public
 		 * @param integer DateID
 		 * @param integer Year
 		 * @param integer Month
 		 * @param integer Day
 		 * @param integer Hour
 		 * @param integer Minute
 		 * @param string Topic
 		 * @param string Location
 		 * @return void
 		 */
 		function UpdateDate($DateID, $Year, $Month, $Day, $Hour, $Minute, $Topic, $Location) {
 			if(is_numeric($DateID) && is_numeric($Year) && is_numeric($Month) && is_numeric($Day) && is_numeric($Hour) && is_numeric($Minute) && $Location != '' && $Topic != '') {
 				
 				$date = mktime($Hour, $Minute, 0, $Month, $Day, $Year);
				$sql = "UPDATE " . DB_PREFIX . "dates
					SET date_topic= '$Topic',
					date_location= '$Location',
					date_date='$date'
					WHERE date_id=$DateID";
				$this->_SqlConnection->SqlQuery($sql);
 			}
 		}
 		
 		/** DateSelecter
	 	 * 
	 	 * Creates a 'control' to select a date without typing something in by keyboard
	 	 * 
	 	 * @access public
	 	 * @static
	 	 * @param timestamp SelectedDate
	 	 * @param timestamp StartDate
	 	 * @param string Prefix
	 	 * @param integer Years
	 	 * @return string
	 	 */
	 	function DateSelecter($SelectedDate, $StartDate, $Prefix = 'date', $Years = 10) {
	 		$selectedYear = date('Y', $SelectedDate);
	 		$endYear = date('Y', $StartDate) + $Years;
	 		$selectedDay = date('j', $SelectedDate);
	 		$selectedMonth = date('n', $SelectedDate);
	 		$selectedHour = date('G', $SelectedDate);
	 		$selectedMinute = date('i', $SelectedDate);
	 			 			 		
	 		$out = "<select id=\"{$Prefix}Day\" name=\"{$Prefix}Day\">";
	 		for($i = 1; $i <= 31;$i++) {
	 			if($i == $selectedDay)
	 				$out .= "<option selected=\"selected\" value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
				<select id=\"{$Prefix}Month\" name=\"{$Prefix}Month\">";
	 		for($i = 1; $i <= 12;$i++) {
	 			if($i == $selectedMonth)
	 				$out .= "<option selected=\"selected\" value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
	 			<select id=\"{$Prefix}Year\" name=\"{$Prefix}Year\">";
	 		for($i = ($selectedYear - $Years < 1970) ? 1970 : ($selectedYear - $Years); $i <= $endYear; $i++) {
	 			if($i == $selectedYear)
	 				$out .= "<option selected=\"selected\" value=\"$i\">$i</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">$i</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
				<select id=\"{$Prefix}Hour\" name=\"{$Prefix}Hour\">";
	 		for($i = 0; $i <= 23; $i++) {
	 			if($i == $selectedHour)
	 				$out .= "<option selected=\"selected\" value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
				<select id=\"{$Prefix}Minute\" name=\"{$Prefix}Minute\">";
	 		for($i = 0; $i <= 59;$i++) {
	 			if($i == $selectedMinute)
	 				$out .= "<option selected=\"selected\" value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 		}
	 		$out .= "</select>";
	 		return $out;
	 	}
}
?>