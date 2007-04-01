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
 	require_once __ROOT__ . '/classes/admin/admin_module.php';
 	require_once __ROOT__ . '/modules/dates/dates.class.php';
 	
 	/**
 	 * @package ComaCMS
 	 * @subpackage Dates
 	 */
 	class Admin_Module_Dates extends Admin_Module {
 		
 		/**
 		 * @access private
 		 */
 		function _Init() {
 			$this->_Translation->AddSources(__ROOT__ . '/modules/dates/lang/');
 		}
 		
 		/**
 		 * @access public
 		 * @param string Action 
 		 * @return string
 		 */
 		function GetPage($Action = '') {
 			$out = '';
 			switch ($Action) {
 				case 'new':	$out .= $this->_NewDatePage();
 						break;
 				case 'add':	$out .= $this->_AddDatePage();
 						break;
 				case 'delete':	$out .= $this->_DeleteDatePage();
 						break;
 				case 'edit':	$out .= $this->_EditDatePage();
 						break;
 				case 'save':	$out .= $this->_SaveDatePage();
 						break;
 				default:	$out .= $this->_mainPage();
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
			return $this->_Translation->GetTranslation('events'). '-' . $this->_Translation->GetTranslation('module');
		}
		
		/**
		 * @access private
		 * @return string;
		 */
		function _DateFormular($Day = -1, $Month = -1, $Year = -1, $Hour = -1, $Minute = -1, $Location = '', $Topic = '', $EventID = -1, $LocationError = '', $TopicError = '') {
			$this->_ComaLate->SetReplacement('LANG_EVENTS', $this->_Translation->GetTranslation('events'));
 			$this->_ComaLate->SetReplacement('LANG_DATE', $this->_Translation->GetTranslation('date'));
 			$this->_ComaLate->SetReplacement('DATE_INFO', $this->_Translation->GetTranslation('this_is_the_date_of_the_event'));
 			$this->_ComaLate->SetReplacement('LANG_LOCATION', $this->_Translation->GetTranslation('location'));
 			$this->_ComaLate->SetReplacement('LOCATION_INFO', $this->_Translation->GetTranslation('this_is_the_location_where_the_event_will_be'));
 			$this->_ComaLate->SetReplacement('LANG_TOPIC', $this->_Translation->GetTranslation('topic'));
 			$this->_ComaLate->SetReplacement('LANG_BACK', $this->_Translation->GetTranslation('back'));
 			$this->_ComaLate->SetReplacement('TOPIC_INFO', $this->_Translation->GetTranslation('the_topic_describes_this_event'));
 			
 			$this->_ComaLate->SetReplacement('EVENT_ID', $EventID);
 			$this->_ComaLate->SetReplacement('LOCATION_VALUE', stripslashes($Location));
 			$this->_ComaLate->SetReplacement('TOPIC_VALUE', stripslashes($Topic));
 			
 			if($EventID == -1) {
 				$this->_ComaLate->SetReplacement('EVENT_SUBTITLE', $this->_Translation->GetTranslation('add_a_new_event'));
 				$this->_ComaLate->SetReplacement('LANG_ADD', $this->_Translation->GetTranslation('add'));
 				$this->_ComaLate->SetReplacement('EVENT_ACTION', 'add');
 			}
 			else {
 				$this->_ComaLate->SetReplacement('EVENT_SUBTITLE', $this->_Translation->GetTranslation('edit_event'));
 				$this->_ComaLate->SetReplacement('LANG_ADD', $this->_Translation->GetTranslation('save'));
 				$this->_ComaLate->SetReplacement('EVENT_ACTION', 'save');	
 			}
 		 	
 		 	if($LocationError != '') {
 		 		$this->_ComaLate->SetCondition('event_location_error', true);
 		 		$this->_ComaLate->SetReplacement('LOCATION_ERROR', $LocationError);
 		 	}
 		 	
 		 	if($TopicError != '') {
 		 		$this->_ComaLate->SetCondition('event_topic_error', true);
 		 		$this->_ComaLate->SetReplacement('TOPIC_ERROR', $TopicError);
 		 	}
 		 	
 		 	// The timestamp "selectedDate"
 		 	$selectedDate = 0;
 		 	if($Day == -1 || $Month == -1 || $Year == -1 || $Hour == -1 || $Minute == -1)
 		 		$selectedDate = mktime();
 		 	else
 		 		$selectedDate = mktime($Hour, $Minute, 0, $Month, $Day, $Year);
 		 			
 			$template = '<h2>{LANG_EVENTS}</h2>
 				<form method="post" action="admin.php">
				<input type="hidden" name="page" value="module_dates" />
				<input type="hidden" name="action" value="{EVENT_ACTION}" />
				<input type="hidden" name="eventID" value="{EVENT_ID}" />
				<fieldset>
					<legend>{EVENT_SUBTITLE}</legend>
					<div class="row">
						<label>
							<strong>{LANG_DATE}:</strong>
							<span class="info">{DATE_INFO}</span>
							</label>
						' . Dates::DateSelector($selectedDate, mktime(), 'date', 7) . '
					</div>
					<div class="row">
						<label>
							<strong>{LANG_LOCATION}:</strong>
							<span class="info">{LOCATION_INFO}</span>
							<event_location_error:condition><span class="error">{LOCATION_ERROR}</span></event_location_error>
						</label>
						<input type="text" name="dateLocation" maxlength="60" value="{LOCATION_VALUE}" />
					</div>
					<div class="row">
						<label>
							<strong>{LANG_TOPIC}:</strong>
							<span class="info">{TOPIC_INFO}</span>
							<event_topic_error:condition><span class="error">{TOPIC_ERROR}</span></event_topic_error>
						</label>
						<script type="text/javascript" language="JavaScript" src="system/functions.js"></script>
						<script type="text/javascript" language="javascript">
							//<![CDATA[
							writeButton("img/button_fett.png", "Text fett formatieren", "**", "**", "fetter Text", "f");
							writeButton("img/button_kursiv.png", "Text kursiv formatieren", "//", "//", "{LANG_ITALIC_TEXT", "k");
							writeButton("img/button_unterstrichen.png", "Text unterstreichen", "__", "__", "unterstrichener Text", "u");
							//]]>
						</script><br />
						<textarea name="dateTopic" id="editor">{TOPIC_VALUE}</textarea>
					</div>
					<div class="row">
						<a href="admin.php?page=module_dates" class="button">{LANG_BACK}</a>
						<input type="submit" class="button" value="{LANG_ADD}" />
					</div>
				</fieldset>
			</form>';
			return $template;
		}
		
 		/**
 		 * @access private
 		 */
 		function _addDatePage() {
 			$eventDay = GetPostOrGet('dateDay');
			$eventMonth = GetPostOrGet('dateMonth');
 			$eventYear = GetPostOrGet('dateYear');			
			$eventHour = GetPostOrGet('dateHour');
			$eventMinute = GetPostOrGet('dateMinute');
			$eventLocation = GetPostOrGet('dateLocation');
			$eventTopic = GetPostOrGet('dateTopic');
			
 			if(is_numeric($eventYear) && is_numeric($eventMonth) && is_numeric($eventDay) && is_numeric($eventHour) && is_numeric($eventMinute) && $eventTopic != '' && $eventLocation != '') {
 				$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
				$dates->AddDate($eventYear, $eventMonth, $eventDay, $eventHour, $eventMinute, $eventTopic, $eventLocation);

				return $this->_mainPage();
			}
			$locationError = '';
			$topicError = '';
			if($eventLocation == '')
				$locationError = $this->_Translation->GetTranslation('please_type_in_a_location');
			if($eventTopic == '')
				$topicError = $this->_Translation->GetTranslation('please_type_in_a_topic');	

			return $this->_DateFormular($eventDay, $eventMonth, $eventYear, $eventHour, $eventMinute,$eventLocation, $eventTopic, -1, $locationError, $topicError);
 		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _saveDatePage() {
			
			$dateYear = GetPostOrGet('dateYear');
			$dateMonth = GetPostOrGet('dateMonth');
			$dateDay = GetPostOrGet('dateDay');
			$dateHour = GetPostOrGet('dateHour');
			$dateMinute = GetPostOrGet('dateMinute');
			$dateTopic = GetPostOrGet('dateTopic');
			$dateLocation = GetPostOrGet('dateLocation');
			$eventID = GetPostOrGet('eventID');

			if(is_numeric($eventID) && is_numeric($dateYear) && is_numeric($dateMonth) && is_numeric($dateDay) && is_numeric($dateHour) && is_numeric($dateMinute) && $dateTopic != '' && $dateLocation != '') {
				$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
				$dates->UpdateDate($eventID, $dateYear, $dateMonth, $dateDay, $dateHour, $dateMinute, $dateTopic, $dateLocation);
				return $this->_mainPage();
			}
			if(!is_numeric($eventID))
				return $this->_mainPage();
			
			$locationError = '';
			$topicError = '';
			if($dateLocation == '')
				$locationError = $this->_Translation->GetTranslation('please_type_in_a_location');
			if($dateTopic == '')
				$topicError = $this->_Translation->GetTranslation('please_type_in_a_topic');	

			return $this->_DateFormular($dateDay, $dateMonth, $dateYear, $dateHour, $dateMinute,$dateLocation, $dateTopic, $eventID, $locationError, $topicError);
			
		}
		
		/**
	 	 * @access private
	 	 * @param array admin_lang
	 	 * @return string
	 	 */
	 	function _EditDatePage() {
	 		$evetID = GetPostOrGet('eventID');
	 		if(!is_numeric($evetID))
	 			return $this->_mainPage();
	 		
	 		$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
	 		$dateEntry = $dates->GetDate($evetID);

	 		$year = date('Y', $dateEntry['EVENT_DATE']);
	 		$day = date('j', $dateEntry['EVENT_DATE']);
	 		$month = date('n', $dateEntry['EVENT_DATE']);
	 		$hour = date('G', $dateEntry['EVENT_DATE']);
	 		$minute = date('i', $dateEntry['EVENT_DATE']);
	 		
	 		return $this->_DateFormular($day, $month, $year, $hour, $minute, $dateEntry['EVENT_LOCATION'], $dateEntry['EVENT_TOPIC'], $evetID);
	 	}
		/**
		 * The page with a form to create a new date
 		 * @access private
 		 * @return string
 		 */
 		function _newDatePage() {
 			return $this->_DateFormular();
 		}
 				
 		/**
 		 * @access private
 		 * @return string
 		 */
 		function _mainPage() {
 			$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
 			$showOld = false;
 			if(GetPostOrGet('showOld') == '1')
 				$showOld = true;
 			// get all dates with readable dates and usernames
 			$eventsArray = $dates->FillArray(-1, 0, true, true, !$showOld);
 			
 			$this->_ComaLate->SetReplacement('EVENT_EVENTS', $eventsArray);
 			
 			$this->_ComaLate->SetReplacement('DATES_MODULE_TITLE', $this->_Translation->GetTranslation('events'));
 			$this->_ComaLate->SetReplacement('ADD_A_NEW_EVENT', $this->_Translation->GetTranslation('add_a_new_event'));
 			$this->_ComaLate->SetReplacement('EVENT_TITLE_DATE', $this->_Translation->GetTranslation('date'));
 			$this->_ComaLate->SetReplacement('EVENT_TITLE_LOCATION', $this->_Translation->GetTranslation('location'));
 			$this->_ComaLate->SetReplacement('EVENT_TITLE_TOPIC', $this->_Translation->GetTranslation('topic'));
 			$this->_ComaLate->SetReplacement('EVENT_TITLE_CREATOR', $this->_Translation->GetTranslation('creator'));
 			$this->_ComaLate->SetReplacement('EVENT_TITLE_ACTIONS', $this->_Translation->GetTranslation('actions'));
 			$this->_ComaLate->SetReplacement('EVENT_LANG_EDIT', sprintf($this->_Translation->GetTranslation('edit_the_date_%date%'), '{EVENT_TOPIC}'));
			$this->_ComaLate->SetReplacement('EVENT_LANG_DELETE', sprintf($this->_Translation->GetTranslation('delete_the_date_%date%'), '{EVENT_TOPIC}'));
			$this->_ComaLate->SetReplacement('SHOW_OLD', $this->_Translation->GetTranslation(($showOld) ?  'hide_old_events' : 'show_old_events'));
			$this->_ComaLate->SetReplacement('SHOW_OLD_VAR', ($showOld) ?  0 : 1);
			
			 			
 			$template = '<h2>{DATES_MODULE_TITLE}</h2>
						<a href="admin.php?page=module_dates&amp;action=new" class="button">{ADD_A_NEW_EVENT}</a>
						<a href="admin.php?page=module_dates&amp;showOld={SHOW_OLD_VAR}" class="button">{SHOW_OLD}</a>
						<table  class="full_width">
							<tr>
								<th>{EVENT_TITLE_DATE}</th>
								<th>{EVENT_TITLE_LOCATION}</th>
								<th>{EVENT_TITLE_TOPIC}</th>
								<th>{EVENT_TITLE_CREATOR}</th>
								<th class="actions">{EVENT_TITLE_ACTIONS}</th>
							</tr>
							<EVENT_EVENTS:loop>
							<tr>
								<td>{EVENT_DATE}</td>
								<td>{EVENT_LOCATION}</td>
								<td>{EVENT_TOPIC_HTML}</td>
								<td>{EVENT_CREATOR}</td>
								<td>
									<a href="admin.php?page=module_dates&amp;action=edit&amp;eventID={EVENT_ID}" title="{EVENT_LANG_EDIT}"><img src="./img/edit.png" height="16" width="16" alt="{EVENT_LANG_EDIT}" title="{EVENT_LANG_EDIT}" /></a>
									<a href="admin.php?page=module_dates&amp;action=delete&amp;eventID={EVENT_ID}" title="{EVENT_LANG_DELETE}"><img src="./img/del.png" height="16" width="16" alt="{EVENT_LANG_DELETE}" title="{EVENT_LANG_DELETE}" /></a></td>
							</tr>
							</EVENT_EVENTS>
							
						</table>';
 			return $template;
 		}
 		
 		/**
 		 * @access private
 		 * @return string
 		 */
 		function _DeleteDatePage() {
 			$confirmation = GetPostOrGet('confirmation');
 			$eventID = GetPostOrGet('eventID');
 			$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
 			if($confirmation == 1 && is_numeric($eventID))
 				$dates->DeleteDate($eventID);
 			else if(is_numeric($eventID)) {
 				$dateEntry = $dates->GetDate($eventID);
 				$this->_ComaLate->SetReplacement('LANG_EVENTS', $this->_Translation->GetTranslation('events'));
 				$this->_ComaLate->SetReplacement('LANG_YES', $this->_Translation->GetTranslation('yes'));
 				$this->_ComaLate->SetReplacement('LANG_NO', $this->_Translation->GetTranslation('no'));
 				$this->_ComaLate->SetReplacement('LANG_REMOVE_EVENT', $this->_Translation->GetTranslation('remove_event'));
				$this->_ComaLate->SetReplacement('LANG_DONT_REMOVE_THIS_EVENT', $this->_Translation->GetTranslation('dont_remove_this_event'));
				$this->_ComaLate->SetReplacement('LANG_REMOVE_THIS_EVENT', $this->_Translation->GetTranslation('remove_this_event')); 				
 				$question = sprintf($this->_Translation->GetTranslation('Do_you_really_want_to_delete_the_date_%date_topic%_for_the_%date%_at_%time%_o_clock'), $dateEntry['EVENT_TOPIC'], date("d.m.Y", $dateEntry['EVENT_DATE']),  date("H:i", $dateEntry['EVENT_DATE']));
 				$this->_ComaLate->SetReplacement('REMOVE_QUESTION', $question);
 				$this->_ComaLate->SetReplacement('EVENT_ID', $eventID);
 				$template = '<h2>{LANG_EVENTS}</h2>
 						<fieldset>
 						<legend>{LANG_REMOVE_EVENT}</legend>
 						{REMOVE_QUESTION}
 						<br />
 						<a class="button" href="admin.php?page=module_dates&amp;action=delete&amp;eventID={EVENT_ID}&amp;confirmation=1" title="{LANG_REMOVE_THIS_EVENT}">{LANG_YES}</a>
						<a class="button" href="admin.php?page=module_dates" title="{LANG_DONT_REMOVE_THIS_EVENT}">{LANG_NO}</a>
 						</fieldset>';
 				return $template;
 			}
 			return $this->_mainPage();
 		}
 	}
?>