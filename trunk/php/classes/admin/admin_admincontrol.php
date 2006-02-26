<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: admin_admincontrol.php			#
 # created		: 2005-10-18					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
	
	/**
	 * @ignore
	 */
	require_once('./classes/admin/admin.php');
	/**
	 * @package ComaCMS
	 */
	class Admin_AdminControl extends Admin{
		
		/**
		 * @access private
		 * @var array
		 */
		var $admin_lang;
		
		/**
		 * @access private
		 * @var Config
		 */
		var $config;
		
		/**
	 	 * @param array admin_lang
	 	 * @param Config config
	 	 */
	 	function Admin_AdminControl($admin_lang, $config) {
	 		$this->admin_lang = $admin_lang;
	 		$this->config = $config;
	 	}
	 	 
		/**
	 	 * @access public
	 	 * @param string action
	 	 * @param array admin_lang
	 	 * @param Config config
	 	 * @return string
	 	 */
		function GetPage($action) {
		
			// get the coutnt of all pages
			$sql = "SELECT page_id
				FROM " . DB_PREFIX . "pages";
			$sitedata_result = db_result($sql);
			$page_count = mysql_num_rows($sitedata_result);
			$sql = "SELECT page_id
				FROM " . DB_PREFIX . "pages_history";
			$history_sitedata_result = db_result($sql);
			$history_page_count = mysql_num_rows($history_sitedata_result);
			
			// get the count of all registered users
			$sql = "SELECT user_id
				FROM " . DB_PREFIX . "users";
			$users_result = db_result($sql);
			$users_count = mysql_num_rows($users_result);
			// get the size of all tables with the prefix DB_PREFIX
			$table_infos_result = db_result("SHOW TABLE STATUS");
			$data_size = 0;
			while($table_infos = mysql_fetch_object($table_infos_result)) {
				if(substr($table_infos->Name, 0, strlen(DB_PREFIX)) == DB_PREFIX)
					$data_size += $table_infos->Data_length + $table_infos->Index_length;
			}
			$installdate = $this->config->Get('install_date');
			if($installdate == '') {
				$config->Save('install_date', mktime());
				$installdate = mktime();
			}	
			$out = "\t\t\t<h2>AdminControl</h2>
			<table class=\"text_table\">
				<tr><th>" . $this->admin_lang['online since'] . ":</th><td>". date("d.m.Y",$installdate) . "</td></tr>
				<tr><th>" . $this->admin_lang['registered users'] . ":</th><td>$users_count</td></tr>
				<tr><th>" . $this->admin_lang['created pages'] . ":</th><td>$page_count</td></tr>
				<tr><th>" . $this->admin_lang['saved_page_modifications'] . ":</th><td>$history_page_count</td></tr>
				<tr><th>" . $this->admin_lang['database size'] . ":</th><td>" . kbormb($data_size) . "</td></tr>
			</table>
			<h2>Letzte Ver&auml;nderungen</h2>
			<table class=\"text_table full_width\">
				<thead>
					<tr>
						<th>" . $this->admin_lang['date'] . "</th><th>" . $this->admin_lang['page'] . "</th><th>" . $this->admin_lang['user'] . "</th><th>" . $this->admin_lang['comment'] . "</th>
					</tr>
				</thead>";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages
				ORDER BY page_date DESC
				LIMIT 0,6";
			$pages_result = db_result($sql);
			$pages = array();
			while($page = mysql_fetch_object($pages_result)) {
				$pages[$page->page_date] = array($page->page_name, $page->page_title, $page->page_creator, $page->page_edit_comment);
			}
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages_history
				ORDER BY page_date DESC
				LIMIT 0,6";
			$pages_result = db_result($sql);
			while($page = mysql_fetch_object($pages_result)) {
				$pages[$page->page_date] = array($page->page_name, $page->page_title, $page->page_creator, $page->page_edit_comment);
			}
			krsort($pages);
			$count = 0;
			foreach ($pages as $date => $page) {
   				$out .= "<tr>
						<td class=\"table_date_width\">" . date("d.m.Y H:i:s", $date) . "</td>
						<td><a href=\"index.php?page=" . $page[0] . "\">" . $page[1] . "</a> (" . $page[0] . ")</td>
						<td>" . getUserById($page[2]) . "</td>
						<td>" . $page[3] . "</td>
					</tr>\r\n";
   				$count++;
   				if($count > 5)
   					break;
			}
			$out .= "</table>
			<h2>Aktuelle Besucher</h2>
			<table class=\"text_table\">
				<thead>
					<tr>
						<th>".$this->admin_lang['name']."</th>
						<th>".$this->admin_lang['page']."</th>
						<th>".$this->admin_lang['last action']."</th>
						<th>".$this->admin_lang['language']."</th>
						<th>".$this->admin_lang['ip']."</th>
						<th>".$this->admin_lang['host']."</th>
					</tr>
				</thead>\r\n";
			// output all visitors surfing on the site
			$sql = "SELECT userid, page, lastaction, lang, ip, host
				FROM " . DB_PREFIX . "online
				WHERE lastaction >= " . (mktime() - 300);
			$users_online_result = db_result($sql);
			while($users_online = mysql_fetch_object($users_online_result)) {
				if($users_online->userid == 0)
					$username  = $this->admin_lang['not registered'];
				else
					$username = getUserById($users_online->userid);
				$out .= "\t\t\t\t<tr>
					<td>".$username."</td>
					<td><a href=\"index.php?page=" . $users_online->page . "\">" . $users_online->page . "</a></td>
					<td>" . date("d.m.Y H:i:s", $users_online->lastaction) . "</td>
					<td>" . $this->admin_lang[$users_online->lang] . "</td>
					<td>" . $users_online->ip . "</td>
					<td>" . $users_online->host . "</td>
				</tr>\r\n";
			}
			$out .= "\t\t\t</table>";
			return $out;
		}
	}
?>
