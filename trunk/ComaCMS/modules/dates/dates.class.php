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
	 * @ignore
	 */
	require_once __ROOT__ . '/classes/textactions.php';
	
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
 		 * @param Sql &SqlConnection
 		 * @param ComaLib &ComaLib
 		 * @param User &User
 		 * @param Config &Config
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
 				$sql = "SELECT date_id, date_date, date_topic, date_location, date_creator, date_topic_html
					FROM " . DB_PREFIX . "dates
					WHERE date_id=$DateID
					LIMIT 1";
				$dateResult = $this->_SqlConnection->SqlQuery($sql);
				if($dateEntry = mysql_fetch_object($dateResult)) {
					$dateArray =  array('EVENT_ID' => $dateEntry->date_id,
 							'EVENT_DATE' => $dateEntry->date_date,
 							'EVENT_TOPIC' => $dateEntry->date_topic,
 							'EVENT_TOPIC_HTML' => $dateEntry->date_topic_html,
 							'EVENT_LOCATION' => $dateEntry->date_location,
 							'EVENT_CREATOR' => $dateEntry->date_creator
 							);
					return $dateArray;
				}
				else
					return array();
 			}
 		}
 		
 		function _makeLinks($Text) {
 			preg_match_all("#\[\[(.+?)\]\]#s", $Text, $links);
			$link_list = array();
			$linkNr = 1;
			// replace all links with a short uniqe id to replace them later back
			foreach($links[1] as $link) {
				$link_list[$linkNr] = $link;
				$Text = str_replace("[[$link]]", "[[%$linkNr%]]", $Text);
				$linkNr++;
			}
			foreach($link_list as $linkNr => $link) {
				if(preg_match("#^(.+?)\|(.+?)$#i", $link, $link2))				
					$Text = str_replace("[[%$linkNr%]]", "<a href=\"" . TextActions::MakeLink($link2[1]) . "\">" . $link2[2] . "</a>", $Text);
				else
					$Text = str_replace("[[%$linkNr%]]", "<a href=\"" . TextActions::MakeLink($link) . "\">" . $link . "</a>", $Text);
			}
			// convert all **text** to <strong>text</strong> => Bold
			$Text = preg_replace("/\*\*(.+?)\*\*/s", "<strong>$1</strong>", $Text);
			// convert all //text// to <em>text</em> => Italic
			$Text = preg_replace("/\/\/(.+?)\/\//s", "<em>$1</em>", $Text);
			// convert all __text__ to <u>text</u> => Underline
			$Text = preg_replace("/__(.+?)__/s", "<u>$1</u>", $Text);
			
			$Text = nl2br($Text);
			
			return $Text;
 		
 		}
 		
 		function GetCount($HideOlder = true, $Older = -1) {
 			$count = 0;
 			
 			if($HideOlder && $Older == -1)
 				$Older = mktime();
 				
 			$sqlHide = '';
 			if($HideOlder)
 				$sqlHide = " WHERE date_date > $Older ";
 			
 			$sql = "SELECT date_id
 			 		FROM " . DB_PREFIX . "dates
 					$sqlHide";
 			$datesResult = $this->_SqlConnection->SqlQuery($sql);
 			$count = mysql_num_rows($datesResult);
 			return $count;	
 		}
 		
 		
 		function GetExtendedCount($Location, $HideOlder = true, $Older = -1) {
 			$count = 0;
 			
 			if($HideOlder && $Older == -1)
 				$Older = mktime() - 86400;//24*60*60
 				
 			$sqlHide = '';
 			if($HideOlder)
 				$sqlHide = " AND date_date > $Older ";
 			
 			$sql = "SELECT date_id
 			 		FROM " . DB_PREFIX . "dates
 			 		WHERE (date_location LIKE '$Location')
 					$sqlHide";
 			$datesResult = $this->_SqlConnection->SqlQuery($sql);
 			$count = mysql_num_rows($datesResult);
 			return $count;	
 		}
 		
 		
 		/**
 		 * @access public
 		 * @param integer MaxCount
 		 * @param boolean ConvertTimestamp
 		 * @param boolean ConvertUsername
 		 * @param boolean HideOld If this is true, all Events behind <param>Older</param> will be ignored
 		 * @param timestamp Older
 		 * @return array
 		 */
 		function FillArray($MaxCount = 6, $Start = 0, $ConvertTimestamp = true, $ConvertUsername = true, $HideOlder = true, $Older = -1) {
 			
 			// get some config-values
			$dateDayFormat = $this->_Config->Get('dates_day_format', 'd.m.Y');
			$dateTimeFormat = $this->_Config->Get('datex_time_format', 'H:i');
			$dateFormat = $dateDayFormat . ' ' . $dateTimeFormat;
 			
 			$datesArray = array();
 			
 			if($HideOlder && $Older == -1)
 				$Older = mktime() - 86400;//24*60*60
 				
 			$sqlHide = '';
 			if($HideOlder)
 				$sqlHide = " WHERE date_date > $Older ";
 			
 			
 			if(!is_numeric($MaxCount))
 				$MaxCount =  6;
 			$sql = "SELECT date_id, date_date, date_topic, date_creator, date_location, date_topic_html
 				FROM " . DB_PREFIX . "dates
 				$sqlHide
 				ORDER BY date_date ASC
 				LIMIT $Start, $MaxCount";
 			if($MaxCount < 0)
 				$sql = "SELECT date_id, date_date, date_topic, date_creator, date_location, date_topic_html
 				FROM " . DB_PREFIX . "dates
 				$sqlHide
 				ORDER BY date_date ASC";
 			
	 			
 			
 			$datesResult = $this->_SqlConnection->SqlQuery($sql);
 			while($dateEntry = mysql_fetch_object($datesResult)) {
 				
 				// Convert the text from the database to html
	 		//	$Text = $dateEntry->date_topic;
	 			
	 			
 				
 				$datesArray[] = array('EVENT_ID' => $dateEntry->date_id,
 							'EVENT_DATE' => ($ConvertTimestamp) ? date($dateFormat, $dateEntry->date_date) : $dateEntry->date_date,
 							'EVENT_TOPIC' => $dateEntry->date_topic,
 							'EVENT_TOPIC_HTML' => $dateEntry->date_topic_html,
 							'EVENT_LOCATION' => $dateEntry->date_location,
 							'EVENT_CREATOR' =>  ($ConvertTimestamp)? $this->_ComaLib->GetUserByID($dateEntry->date_creator) :$dateEntry->date_creator, 
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
 		function ExtendedFillArray($Location, $MaxCount = 6, $Start = 0, $ConvertTimestamp = true) {
 			$datesArray = array();
 			
 			// get some config-values
			$dateDayFormat = $this->_Config->Get('dates_day_format', 'd.m.Y');
			$dateTimeFormat = $this->_Config->Get('dates_time_format', 'H:i');
			$dateFormat = $dateDayFormat . ' ' . $dateTimeFormat;
 			
 			if(!is_numeric($MaxCount))
 				$MaxCount =  6;
 			$sql = "SELECT date_id, date_date, date_topic, date_topic_html, date_creator, date_location
 				FROM " . DB_PREFIX . "dates
 				WHERE date_location LIKE '$Location'
 				ORDER BY date_date ASC
 				LIMIT $Start, $MaxCount";
 			if($MaxCount < 0)
 				$sql = "SELECT date_id, date_date, date_topic, date_topic_html, date_creator, date_location
 				FROM " . DB_PREFIX . "dates
 				WHERE date_location LIKE '$Location'
 				ORDER BY date_date ASC";
 			
 			$datesResult = $this->_SqlConnection->SqlQuery($sql);
 			while($dateEntry = mysql_fetch_object($datesResult)) {
 				$datesArray[] = array('EVENT_ID' => $dateEntry->date_id,
 							'EVENT_DATE' => ($ConvertTimestamp) ? date($dateFormat, $dateEntry->date_date) : $dateEntry->date_date,
 							'EVENT_TOPIC' => $dateEntry->date_topic,
 							'EVENT_TOPIC_HTML' => $dateEntry->date_topic_html,
 							'EVENT_LOCATION' => $dateEntry->date_location,
 							'EVENT_CREATOR' => $dateEntry->date_creator
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
 				$topicHtml = $this->_makeLinks($Topic);
 				$sql = "INSERT INTO " . DB_PREFIX . "dates (date_topic, date_topic_html, date_location, date_date, date_creator)
					VALUES ('$Topic', '$topicHtml', '$Location', '$date', '{$this->_User->ID}')";
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
 				$topicHtml = $this->_makeLinks($Topic);
				$sql = "UPDATE " . DB_PREFIX . "dates
					SET date_topic= '$Topic', date_topic_html='$topicHtml',
					date_location= '$Location',
					date_date='$date'
					WHERE date_id=$DateID";
				$this->_SqlConnection->SqlQuery($sql);
 			}
 		}
 		
 		/** DateSelector
	 	 * 
	 	 * Creates a 'control' to select a date without typing something in by keyboard
	 	 * 
	 	 * @access public
	 	 * @static
	 	 * @param timestamp SelectedDate This date will be selected after generating the 'control'
	 	 * @param timestamp StartDate
	 	 * @param string Prefix The prefix allows to crate more than one DateSelector in one form
	 	 * @param integer Years 
	 	 * @return string
	 	 */
	 	function DateSelector($SelectedDate, $StartDate, $Prefix = 'date', $Years = 10) {
	 		// The selcted year
	 		$selectedYear = date('Y', $SelectedDate);
	 		// The 'maximum'-year (StartDate->Year + Years)
	 		$endYear = date('Y', $StartDate) + $Years;
	 		// The selected day
	 		$selectedDay = date('j', $SelectedDate);
	 		// The selected month
	 		$selectedMonth = date('n', $SelectedDate);
	 		// The selected hour
	 		$selectedHour = date('G', $SelectedDate);
	 		// The selected minute
	 		$selectedMinute = date('i', $SelectedDate);
	 			 			 		
	 		$out = "<select id=\"{$Prefix}Day\" name=\"{$Prefix}Day\">";
	 		// print all possible days of a month
	 		for($i = 1; $i <= 31; $i++) {
	 			if($i == $selectedDay)
	 				$out .= "<option selected=\"selected\" value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
				<select id=\"{$Prefix}Month\" name=\"{$Prefix}Month\">";
			// print all months
	 		for($i = 1; $i <= 12; $i++) {
	 			if($i == $selectedMonth)
	 				$out .= "<option selected=\"selected\" value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
	 			<select id=\"{$Prefix}Year\" name=\"{$Prefix}Year\">";
	 		// print all selectable years, but make sure that the year is greater than 1970 (lower years will make trouble in windows environments)
	 		for($i = ($selectedYear - $Years < 1970) ? 1970 : ($selectedYear - $Years); $i <= $endYear; $i++) {
	 			if($i == $selectedYear)
	 				$out .= "<option selected=\"selected\" value=\"$i\">$i</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">$i</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
				<select id=\"{$Prefix}Hour\" name=\"{$Prefix}Hour\">";
	 		// print all hours
	 		for($i = 0; $i <= 23; $i++) {
	 			if($i == $selectedHour)
	 				$out .= "<option selected=\"selected\" value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 			else
	 				$out .= "<option value=\"$i\">" . substr('0' . $i, -2) . "</option>\r\n";
	 		}
	 		$out .= "</select>\r\n
				<select id=\"{$Prefix}Minute\" name=\"{$Prefix}Minute\">";
	 		// print all minutes
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