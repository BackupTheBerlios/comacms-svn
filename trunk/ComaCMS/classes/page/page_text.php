<?php
/**
 * @package ComaCMS
 * @subpackage Page
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : page_text.php
 # created              : 2006-12-30
 # copyright            : (C) 2005-2007 The ComaCMS-Team
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
 	require_once __ROOT__ . '/classes/page/page.php';
 	require_once __ROOT__ . '/classes/textactions.php';

 	/**
	 * @package ComaCMS
	 * @subpackage Page
  	 */	
	class Page_Text extends Page {
		
		/**
		 * @access public
		 * @var string
		 */
		var $Text;
		
		/**
		 * @access public
		 * @param integer PageID
		 * @return boolean Is true on success
		 */
		function LoadPage($PageID) {
			/*$sql = 'SELECT text_page_text, text_page_html
					FROM ' . DB_PREFIX . 'pages_text
					WHERE page_id = ' . $PageID;*/
			$sql = 'SELECT page.page_date, page.page_title, text.text_page_text, text.text_page_html
					FROM ( ' . DB_PREFIX. 'pages page
					LEFT JOIN ' . DB_PREFIX . 'pages_text text ON text.page_id = page.page_id )
					WHERE page.page_id=' . $PageID . '
					LIMIT 1';
			$pageDataResult = $this->_SqlConnection->SqlQuery($sql);
			$loaded = false;
			if($pageData = mysql_fetch_object($pageDataResult)) {
				$this->HTML = $pageData->text_page_html;
				$this->Text = $pageData->text_page_text;
					
				$dateDayFormat = $this->_Config->Get('date_day_format', '');
				$dateTimeFormat = $this->_Config->Get('date_time_format', '');
				$dateFormat = $dateDayFormat . ' ' . $dateTimeFormat;
				$this->HTML .= '<div class="last_change">' . $this->_Translation->GetTranslation('last_change') . ': ' . date($dateFormat, $pageData->page_date) . '</div>';
				$this->_ComaLate->SetReplacement('LAST_CHANGE', date($dateFormat, $pageData->page_date));
				$this->_ComaLate->SetReplacement('PAGE_NAME', $pageData->page_title);
				
				//yyyy-mm-ddThh:mm:ss+hh:mm
				$this->_ComaLate->SetMeta('date', date('Y-m-d\TH:i:sO', $pageData->page_date)); 
				return true;
			
							
			}
 			return false;
 		}
 		
 		/**
 		 * @access public
 		 * @param integer PageID
 		 * @param integer Revision 0 = HEAD
 		 * @return boolean Is true on success
 		 */
 		function LoadPageFromRevision($PageID, $Revision) {
 			if(!is_numeric($Revision) || !is_numeric($PageID))
 				return false;
 			// It is the HEAD-revision, we won't find it in the history data	
 			if($Revision == 0)
 				return $this->LoadPage($PageID);
 			
 			$sql = 'SELECT text.text_page_text
					FROM (' . DB_PREFIX . 'pages_history page
					LEFT JOIN ' . DB_PREFIX . 'pages_text_history text ON text.page_id = page.id ) 
					WHERE page.page_id=' . $PageID . '
					ORDER BY  page.page_date ASC
					LIMIT ' . ($Revision - 1) . ',1';
 			
 			$pageDataResult = $this->_SqlConnection->SqlQuery($sql);
			if($pageData = mysql_fetch_object($pageDataResult)) {
				 $this->HTML = TextActions::ConvertToPreHtml($pageData->text_page_text);
				 $this->Text = $pageData->text_page_text;
				return true;
			}
			return false;
 		}
 		
	}
?>
