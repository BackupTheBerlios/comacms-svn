<?php
/**
 * @package ComaCMS
 * @subpackage Dates
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : dates_module.php
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
	require_once('classes/module.php');
	require_once('modules/dates/dates.class.php');
	
 	/**
	 * @package ComaCMS
	 * @subpackage Dates 
	 */
	class Module_Dates extends Module{
		
		function UseModule($Identifer, $Parameters) {
 			$Parameters = explode('&', $Parameters);
 			$all= false;
 			$count = 6;
 			$location='%';
 			foreach($Parameters as $parameter){
 				$parameter = explode('=', $parameter, 2);
 				if(empty($parameter[1]))
 					$parameter[1] = true;
 				$$parameter[0] = $parameter[1];
 			}
 			$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
 			if($all)
 				$count = -1;
 			$datesArray = array();
 			if($location != '%')
 				$datesArray = $dates->ExtendedFillArray($location, $count);
 			else
 				$datesArray = $dates->FillArray($count);
 			
 			$name = uniqid('EVENTS_');
 			$this->_ComaLate->SetReplacement($name, $datesArray);
 			$this->_ComaLate->SetReplacement('LANG_DATE', $this->_Translation->GetTranslation('date'));
 			$this->_ComaLate->SetReplacement('LANG_LOCATION', $this->_Translation->GetTranslation('location'));
 			$this->_ComaLate->SetReplacement('LANG_TOPIC', $this->_Translation->GetTranslation('topic'));
 			
 			return '</p><table class="full_width">
				<thead>
					<tr>
						<th class="table_date_width">
							{LANG_DATE}
						</th>
						<th class="small_width">
							{LANG_LOCATION}
						</th>
						<th>
							{LANG_TOPIC}
						</th>
					</tr>
				</thead>
				<tbody>
					<' . $name . ':loop>
					<tr>
						<td>
							{EVENT_DATE}
						</td>
						<td>
							{EVENT_LOCATION}
						</td>
						<td>
							{EVENT_TOPIC}
						</td>
					</tr>
					</' . $name . '>
				</tbody>
 			</table><p>';
 			
 		}
	}
?>