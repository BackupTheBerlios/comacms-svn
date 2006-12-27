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
			return $this->_Translation->GetTranslation('dates'). '-' . $this->_Translation->GetTranslation('module');
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
 			$this->_ComaLate->SetReplacement('LANG_EVENTS', $this->_Translation->GetTranslation('events'));
 			$this->_ComaLate->SetReplacement('LANG_ADD_A_NEW_EVENT', $this->_Translation->GetTranslation('add_a_new_event'));
 			$this->_ComaLate->SetReplacement('LANG_DATE', $this->_Translation->GetTranslation('date'));
 			$this->_ComaLate->SetReplacement('LANG_THIS_IS_THE_DATE_OF_THE_EVENT', $this->_Translation->GetTranslation('this_is_the_date_of_the_event'));
 			$this->_ComaLate->SetReplacement('LANG_LOCATION', $this->_Translation->GetTranslation('location'));
 			$this->_ComaLate->SetReplacement('LANG_THIS_IS_THE_LOCATION_WHERE_THE_EVENT_WILL_BE', $this->_Translation->GetTranslation('this_is_the_location_where_the_event_will_be'));
 			$this->_ComaLate->SetReplacement('LANG_TOPIC', $this->_Translation->GetTranslation('topic'));
 			$this->_ComaLate->SetReplacement('LANG_ADD', $this->_Translation->GetTranslation('add'));
 			$this->_ComaLate->SetReplacement('LANG_BACK', $this->_Translation->GetTranslation('back'));
 			$this->_ComaLate->SetReplacement('LANG_THE_TOPIC_DESCRIBES_THIS_EVENT', $this->_Translation->GetTranslation('the_topic_describes_this_event'));
 			/*'date'
 			'location'
 			'this_is_the_date_of_the_event'
 			'this_is_the_location_where_the_event_will_be'
 			Gemeint ist hier der Ort an welchem die Veranstaltung stattfindet.
 			'topic'
 			'the_topic_describes_this_event'
 			Dies ist die Beschreibung des Termins
 			'back'
 			'add'*/
 			
 			$template = '<h2>{LANG_EVENTS}</h2>
 				<form method="post" action="admin.php">
				<input type="hidden" name="page" value="module_dates" />
				<input type="hidden" name="action" value="add" />
				<fieldset>
					<legend>{LANG_ADD_A_NEW_EVENT}</legend>
					<div class="row">
						<label>
							<strong>{LANG_DATE}:</strong>
							<span class="info">{LANG_THIS_IS_THE_DATE_OF_THE_EVENT}</span>
							</label>
						' . Dates::DateSelecter(mktime(), mktime(), 'date', 7) . '
					</div>
					<div class="row">
						<label>
							<strong>{LANG_LOCATION}:</strong>
							<span class="info">{LANG_THIS_IS_THE_LOCATION_WHERE_THE_EVENT_WILL_BE}</span>
						</label>
						<input type="text" name="dateLocation" maxlength="60" value="" />
					</div>
					<div class="row">
						<label>
							<strong>{LANG_TOPIC}:</strong>
							<span class="info">{LANG_THE_TOPIC_DESCRIBES_THIS_EVENT}</span>
						</label>
						<textarea name="dateTopic" ></textarea>
					</div>
					<div class="row">
						<a href="admin.php?page=modules_dates" class="button">{LANG_BACK}</a>
						<input type="submit" class="button" value="{LANG_ADD}" />
					</div>
				</fieldset>
			</form>';
			return $template;
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
 			
 			// get all dates with readable dates and usernames
 			$datesArray = $dates->FillArray(-1, true, true);
 			
 			$this->_ComaLate->SetReplacement('DATE_DATES', $datesArray);
 			
 			$this->_ComaLate->SetReplacement('DATES_MODULE_TITLE', $this->_Translation->GetTranslation('events'));
 			$this->_ComaLate->SetReplacement('ADD_A_NEW_DATE', $this->_Translation->GetTranslation('add_a_new_event'));
 			$this->_ComaLate->SetReplacement('DATE_TITLE_DATE', $this->_Translation->GetTranslation('date'));
 			$this->_ComaLate->SetReplacement('DATE_TITLE_LOCATION', $this->_Translation->GetTranslation('location'));
 			$this->_ComaLate->SetReplacement('DATE_TITLE_TOPIC', $this->_Translation->GetTranslation('topic'));
 			$this->_ComaLate->SetReplacement('DATE_TITLE_CREATOR', $this->_Translation->GetTranslation('creator'));
 			$this->_ComaLate->SetReplacement('DATE_TITLE_ACTIONS', $this->_Translation->GetTranslation('actions'));
 			$this->_ComaLate->SetReplacement('DATE_LANG_EDIT', sprintf($this->_Translation->GetTranslation('edit_the_date_%date%'), '{DATE_TOPIC}'));
			$this->_ComaLate->SetReplacement('DATE_LANG_DELETE', sprintf($this->_Translation->GetTranslation('delete_the_date_%date%'), '{DATE_TOPIC}'));
			 			
 			$template = '<h2>{DATES_MODULE_TITLE}</h2>
						<a href="admin.php?page=module_dates&amp;action=new" class="button">{ADD_A_NEW_DATE}</a>
						<table  class="full_width">
							<tr>
								<th>{DATE_TITLE_DATE}</th>
								<th>{DATE_TITLE_LOCATION}</th>
								<th>{DATE_TITLE_TOPIC}</th>
								<th>{DATE_TITLE_CREATOR}</th>
								<th class="actions">{DATE_TITLE_ACTIONS}</th>
							</tr>
							<DATE_DATES:loop>
							<tr>
								<td>{DATE_DATE}</td>
								<td>{DATE_LOCATION}</td>
								<td>{DATE_TOPIC}</td>
								<td>{DATE_CREATOR}</td>
								<td>
									<a href="admin.php?page=module_dates&amp;action=edit&amp;dateID={DATE_ID}" title="{DATE_LANG_EDIT}"><img src="./img/edit.png" height="16" width="16" alt="{DATE_LANG_EDIT}" title="{DATE_LANG_EDIT}" /></a>
									<a href="admin.php?page=module_dates&amp;action=delete&amp;dateID={DATE_ID}" title="{DATE_LANG_DELETE}"><img src="./img/del.png" height="16" width="16" alt="{DATE_LANG_DELETE}" title="{DATE_LANG_DELETE}" /></a></td>
							</tr>
							</DATE_DATES>
							
						</table>';
 			return $template;
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