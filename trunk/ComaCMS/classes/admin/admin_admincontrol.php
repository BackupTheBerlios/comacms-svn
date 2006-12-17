<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin_admincontrol.php
 # created              : 2005-10-18
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
	require_once __ROOT__ . '/classes/admin/admin.php';
	
	/**
	 * @package ComaCMS
	 */
	class Admin_AdminControl extends Admin {
 	 
		/**
	 	 * @access public
	 	 * @param string Action This parameter is without a function
		 * @return string
	 	 */
		function GetPage($Action = '') {
			// get some config-values
			$dateDayFormat = $this->_Config->Get('date_day_format', '');
			$dateTimeFormat = $this->_Config->Get('date_time_format', '');
			$dateFormat = $dateDayFormat . ' ' . $dateTimeFormat;
			
			// get the number of pages whicht aren't deleted
			$sql = "SELECT page_id
					FROM " . DB_PREFIX . "pages
					WHERE page_access != 'deleted'";
			
			$pagesResult = $this->_SqlConnection->SqlQuery($sql);
			$pagesCount = mysql_num_rows($pagesResult);
			
			// get the number of all pages saved in the history
			$sql = "SELECT page_id
					FROM " . DB_PREFIX . "pages_history";
			
			$historyPagesResult = $this->_SqlConnection->SqlQuery($sql);
			$historyPagesCount = mysql_num_rows($historyPagesResult);
			
			// get the number of all registered users
			$sql = "SELECT user_id
					FROM " . DB_PREFIX . "users";
			
			$usersResult = $this->_SqlConnection->SqlQuery($sql);
			$usersCount = mysql_num_rows($usersResult);
			
			// get the size of all tables with the prefix DB_PREFIX
			$sql = "SHOW TABLE STATUS";
			$tableInfoResult = $this->_SqlConnection->SqlQuery($sql);
			$dataSize = 0;
			
			while($tableInfo = mysql_fetch_object($tableInfoResult)) {
				if(substr($tableInfo->Name, 0, strlen(DB_PREFIX)) == DB_PREFIX)
					$dataSize += $tableInfo->Data_length + $tableInfo->Index_length;
			}
			
			// get the date of the installation or set one
			$installDate = $this->_Config->Get('install_date');
			if($installDate == '') {
				$this->_Config->Save('install_date', mktime());
				$installDate = mktime();
			}
			// set replacements for translations
			$this->_ComaLate->SetReplacement('ADMIN_CONTROL_TITLE', $this->_Translation->GetTranslation('admincontrol'));
			$this->_ComaLate->SetReplacement('LOG_TITLE_DATE', $this->_Translation->GetTranslation('date'));
			$this->_ComaLate->SetReplacement('LOG_TITLE_PAGE', $this->_Translation->GetTranslation('page'));
			$this->_ComaLate->SetReplacement('LOG_TITLE_USER', $this->_Translation->GetTranslation('user'));
			$this->_ComaLate->SetReplacement('LOG_TITLE_COMMENT', $this->_Translation->GetTranslation('comment'));
			$this->_ComaLate->SetReplacement('ADMIN_CONTROL_LAST_CHANGES', $this->_Translation->GetTranslation('last_changes'));
			$this->_ComaLate->SetReplacement('USER_TITLE_NAME', $this->_Translation->GetTranslation('name'));
			$this->_ComaLate->SetReplacement('USER_TITLE_PAGE', $this->_Translation->GetTranslation('page'));
			$this->_ComaLate->SetReplacement('USER_TITLE_LAST_ACTION', $this->_Translation->GetTranslation('last_action'));
			$this->_ComaLate->SetReplacement('USER_TITLE_LANGUAGE', $this->_Translation->GetTranslation('language'));
			$this->_ComaLate->SetReplacement('USER_TITLE_IP', $this->_Translation->GetTranslation('ip'));
			$this->_ComaLate->SetReplacement('USER_TITLE_HOST', $this->_Translation->GetTranslation('host'));
			$this->_ComaLate->SetReplacement('ADMIN_CONTROL_USERS', $this->_Translation->GetTranslation('visitors'));
			
			// fill the table with some statistical data 
			$adminControlStats = array();
			$adminControlStats[] = array('STATS_NAME' => $this->_Translation->GetTranslation('online_since'),
										 'STATS_VALUE' => date($dateDayFormat, $installDate));
			$adminControlStats[] = array('STATS_NAME' => $this->_Translation->GetTranslation('registered_users'),
										 'STATS_VALUE' => $usersCount);
			$adminControlStats[] = array('STATS_NAME' => $this->_Translation->GetTranslation('created_pages'),
										 'STATS_VALUE' => $pagesCount);
			$adminControlStats[] = array('STATS_NAME' => $this->_Translation->GetTranslation('saved_page_modifications'),
										 'STATS_VALUE' => $historyPagesCount);
			$adminControlStats[] = array('STATS_NAME' => $this->_Translation->GetTranslation('database_size'),
										 'STATS_VALUE' => kbormb($dataSize));
			$this->_ComaLate->SetReplacement('ADMIN_CONTROL_STATS', $adminControlStats);
			
			$pages = array();
			
			// get the last 6 pages
			$sql = "SELECT page_name, page_title, page_creator, page_edit_comment, page_date
					FROM " . DB_PREFIX . "pages
					ORDER BY page_date DESC
					LIMIT 6";
			
			$pagesResult = $this->_SqlConnection->SqlQuery($sql);
						
			while($page = mysql_fetch_object($pagesResult))
				$pages[$page->page_date] = array($page->page_name, $page->page_title, $page->page_creator, $page->page_edit_comment);
			
			// get the last 6 pages of the history
			$sql = "SELECT page_name, page_title, page_creator, page_edit_comment, page_date
					FROM " . DB_PREFIX . "pages_history
					ORDER BY page_date DESC
					LIMIT 6";
			
			$pagesResult = $this->_SqlConnection->SqlQuery($sql);
			
			while($page = mysql_fetch_object($pagesResult))
				$pages[$page->page_date] = array($page->page_name, $page->page_title, $page->page_creator, $page->page_edit_comment);
			
			krsort($pages);
			
			$logData = array();
			$count = 0;
			
			foreach ($pages as $date => $page) {
   				$logData[] = array('LOG_DATE' => date($dateFormat, $date),
						'LOG_PAGE_URL'  =>  $page[0],
						'LOG_PAGE_TITLE' => $page[1],
						'LOG_PAGE_NAME' => rawurldecode($page[0]),
						'LOG_USER' => $this->_ComaLib->GetUserByID($page[2]),
						'LOG_COMMENT' => $page[3]);
				if($count++ == 5)
					break;	
			}
			$this->_ComaLate->SetReplacement('ADMIN_CONTROL_LOG', $logData);
			
			// get all visitors of the page which moved in the last 5 minutes			
			$sql = "SELECT online_userid, online_page, online_lastaction, online_lang, online_ip, online_host
					FROM " . DB_PREFIX . "online
					WHERE online_lastaction >= " . (mktime() - 300)."
					ORDER BY online_userid";
			$usersOnlineResult = $this->_SqlConnection->SqlQuery($sql);
			$usersData = array();

			while($userOnline = mysql_fetch_object($usersOnlineResult)) {
				if($userOnline->online_userid == 0)
					$username  = $this->_Translation->GetTranslation('not_registered');
				else
					$username = $this->_ComaLib->GetUserByID($userOnline->online_userid);

				$usersData[] = array('USER_NAME' => $username,
									'USER_PAGE'  =>  $userOnline->online_page,
									'USER_LAST_ACTION' => date($dateFormat, $userOnline->online_lastaction),
									'USER_LANGUAGE' => $this->_Translation->GetTranslation($userOnline->online_lang),
									'USER_IP' => $userOnline->online_ip,
									'USER_HOST' => $userOnline->online_host);
			}
			$this->_ComaLate->SetReplacement('ADMIN_CONTROL_USERS', $usersData);
	
			// throw out the temlate-data 
			$template = '<h2>{ADMIN_CONTROL_TITLE}</h2>
					<table>
					<ADMIN_CONTROL_STATS:loop>
						<tr>
							<th>{STATS_NAME}:</th>
							<td>{STATS_VALUE}</td>
						</tr>
					</ADMIN_CONTROL_STATS>
					</table>
					<h2>{ADMIN_CONTROL_LAST_CHANGES}</h2>
					<table class="full_width">
						<tr>
							<th>{LOG_TITLE_DATE}</th>
							<th>{LOG_TITLE_PAGE}</th>
							<th>{LOG_TITLE_USER}</th>
							<th>{LOG_TITLE_COMMENT}</th>
						</tr>
					<ADMIN_CONTROL_LOG:loop>
						<tr>
							<td>{LOG_DATE}</td>
							<td><a href="index.php?page={LOG_PAGE_URL}">{LOG_PAGE_TITLE}</a>({LOG_PAGE_NAME})</td>
							<td>{LOG_USER}</td>
							<td>{LOG_COMMENT}</td>
						</tr>
					</ADMIN_CONTROL_LOG>
					</table>
					<h2>{ADMIN_CONTROL_USERS}</h2>
					<table class="full_width">
						<tr>
						<th>{USER_TITLE_NAME}</th>
						<th>{USER_TITLE_PAGE}</th>
						<th>{USER_TITLE_LAST_ACTION}</th>
						<th>{USER_TITLE_LANGUAGE}</th>
						<th>{USER_TITLE_IP}</th>
						<th>{USER_TITLE_HOST}</th>
						</tr>
						<ADMIN_CONTROL_USERS:loop>
						<tr>
						<td>{USER_NAME}</td>
						<td><a href="index.php?page={USER_PAGE}">{USER_PAGE}</a></td>
						<td>{USER_LAST_ACTION}</td>
						<td>{USER_LANGUAGE}</td>
						<td>{USER_IP}</td>
						<td>{USER_HOST}</td>
						</tr>
						</ADMIN_CONTROL_USERS>
					</table>'; 
			return $template;			
		}
	}
?>
