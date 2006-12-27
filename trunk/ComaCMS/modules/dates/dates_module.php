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
 			$this->_ComaLate->SetReplacement('DATES', $datesArray);
 			return "</p><table class=\"text_table full_width\">
				<thead>
					<tr>
						<th class=\"table_date_width\">
							" . $this->_Translation->GetTranslation('date') . "
						</th>
						<th class=\"small_width\">
							" . $this->_Translation->GetTranslation('location') . "
						</th>
						<th>
							" . $this->_Translation->GetTranslation('topic') . "
						</th>
					</tr>
				</thead>
				<tbody>
					<DATES:loop>
					<tr>
						<td>
							{DATE_DATE}
						</td>
						<td>
							{DATE_LOCATION}
						</td>
						<td>
							{DATE_TOPIC}
						</td>
					</tr>
					</DATES>
				</tbody>
 			</table><p>";
 			
 		}
	}
?>