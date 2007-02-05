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
 			// parse all parameters
 			foreach($Parameters as $parameter){
 				$parameter = explode('=', $parameter, 2);
 				if(empty($parameter[1]))
 					$parameter[1] = true;
 				$$parameter[0] = $parameter[1];
 			}
 			$dates = new Dates($this->_SqlConnection, $this->_ComaLib, $this->_User, $this->_Config);
 			// we want to get "all" dates
 			if($all)
 				$count = -1;
 			
 			$datesArray = array();
 			$found = 0;
 			// get the count of all possible matches
 			
 			// if location is set, it is a conditional request
 			if($location != '%')
 				$found = $dates->GetExtendedCount($location);
 			else
 				$found = $dates->GetCount();
 			
 			$start = 0;
 			$linksArray = array();
 			$linksTemplate = '';
 			
 			$links = uniqid('LINKS_');
 			// it is usefull to use "page links"
 			if($found > $count && $count > 1) {
 				$parts = $found/$count;
 		
 				$max = round($parts, 0);
 				$max = ($max >= $parts) ? $max : $max +1;
 					
 				$linksTemplate = '<' . $links . ':loop>
 						<a href="?page={PAGE_ID}&amp;page_nr={LINK_NR}">{LINK_TEXT}</a> {LINK_MINUS} 
 					</' . $links . '>';
 				$pageNr = GetPostOrGet('page_nr');
 				if(!is_numeric($pageNr))
 					$pageNr = 0;
 				if($pageNr > 0)
 					$linksTemplate = '<a href="?page={PAGE_ID}&amp;page_nr=' . ($pageNr - 1) . '">{LANG_PREVIOUS}</a> -' . $linksTemplate ;
 				if($pageNr < $max - 1)
 					$linksTemplate .= ' - <a href="?page={PAGE_ID}&amp;page_nr=' . ($pageNr + 1) . '">{LANG_NEXT}</a>';					
 				for($i=0;$i < $parts; $i++)
 					$linksArray[$i] = array('LINK_NR' => $i, 'LINK_TEXT' => $i+1,'LINK_MINUS' => '-' );
 					
 					
 				$linksArray[$max-1]['LINK_MINUS'] = '';
 					
 				$this->_ComaLate->SetReplacement($links, $linksArray);
 				$this->_ComaLate->SetReplacement('LANG_NEXT', $this->_Translation->GetTranslation('next'));
				$this->_ComaLate->SetReplacement('LANG_PREVIOUS', $this->_Translation->GetTranslation('previous'));
 				$linksTemplate = '<div>' . $linksTemplate . '</div>';
 				$start = $count * $pageNr;
 				if($start > $found)
 					$start = ($max - 1) * $count;
 				
 			}
 			
 			// Get the array with the dates
 			if($location != '%')
 				$datesArray = $dates->ExtendedFillArray($location, $count, $start);
 			else
 				$datesArray = $dates->FillArray($count, $start);
 			
 			
 			$name = uniqid('EVENTS_');
 			$this->_ComaLate->SetReplacement($name, $datesArray);
 			
 			$this->_ComaLate->SetReplacement('PAGE_ID', GetPostOrGet('page'));
 			
 			$this->_ComaLate->SetReplacement('LANG_DATE', $this->_Translation->GetTranslation('date'));
 			$this->_ComaLate->SetReplacement('LANG_LOCATION', $this->_Translation->GetTranslation('location'));
 			$this->_ComaLate->SetReplacement('LANG_TOPIC', $this->_Translation->GetTranslation('topic'));
 			
 			$template = '</p>' . $linksTemplate . '
 					<table class="full_width">
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
 			</table>' . $linksTemplate . '<p>';
 			
 			return $template;
 			
 		}
	}
?>