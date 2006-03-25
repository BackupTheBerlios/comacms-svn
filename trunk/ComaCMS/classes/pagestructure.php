<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: pagestructure.php				#
 # created		: 2006-01-27					#
 # copyright		: (C) 2005-2006 The ComaCMS-Team		#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
 
 
	/**
	 * @package ComaCMS
	 */ 
	class PageStructure {
		
		var $_SqlConnection;
		var $_User;
		var $_Pages = array();
		var $_ParentIDPages = array();
		var $_NextSortID = array();
		/**
		 * @param SqlConnection SqlConnection
		 * @param User User
		 */
		function PageStructure($SqlConnection, $User) {
			$this->_SqlConnection = &$SqlConnection;
			$this->_User = &$User;
		}
		
		/**
		 * @param integer PageID
		 * @param string PageAccess
		 * @return void
		 */
		function SetPageDeleted($PageID) {
			$sql = "UPDATE " . DB_PREFIX . "pages
				SET  page_access='deleted', page_creator='" . $this->_User->ID . "', page_date='" . mktime() . "'
				WHERE page_id='$PageID'";
			$this->_SqlConnection->SqlQuery($sql);
		}
		
		function SetSubPagesDeleted($ParentPageID) {
			$this->LoadParentIDs();
			foreach($this->_ParentIDPages[$ParentPageID] as $element) {
				if(!empty($this->_ParentIDPages[$element['id']]))
					$this->SetSubPagesDeleted($element['id']);
				$this->SetPageDeleted($element['id']);
			}
			
		}
		
		function LoadData($PageID) {
			if(empty($this->_Pages[$PageID])) {
				$sql = "SELECT *
		 			FROM " . DB_PREFIX . "pages
		 			WHERE page_id=$PageID
		 			LIMIT 0, 1";
		 		$pageResult = $this->_SqlConnection->SqlQuery($sql);
		 		if($page = mysql_fetch_object($pageResult))
			 		$this->_Pages[$PageID] = array('name' => $page->page_name, 'title' => $page->page_title);
		 	}
		}
		
		function LoadParentIDs() {
			if(count($this->_ParentIDPages) <= 0) {
				// TODO: ORDER BY page_sortid
				$sql = "SELECT *
			 		FROM " . DB_PREFIX . "pages
		 			ORDER BY page_parent_id";
		 		$pageResult = $this->_SqlConnection->SqlQuery($sql);
	 			while($page = mysql_fetch_object($pageResult)) {
		 			$this->_ParentIDPages[$page->page_parent_id][] =
	 						array('id' => $page->page_id,
		 						'name' => $page->page_name,
	 							'type' => $page->page_type,
	 							'lang' => $page->page_lang,
	 							'title' => $page->page_title,
	 							'access' => $page->page_access);
	 				$this->_Pages[$page->page_id] = array('name' => $page->page_name, 'title' => $page->page_title);
	 			}
			}
		}
		
		/**
		 * @param integer PageID
		 * @return boolean
		 */
		function PageExists($PageID) {
			$this->LoadData($PageID);
			return !empty($this->_Pages[$PageID]);
		}
		
		/**
		 * @param integer PageID
		 * @return boolean
		 */
		function PageHasSubPages($PageID, $IgnoreDeleted = true) {
		 	$this->LoadParentIDs();
		 	if(empty($this->_ParentIDPages[$PageID]))
		 		return false;
		 	else if($IgnoreDeleted){
		 		foreach($this->_ParentIDPages[$PageID] as $element) {
		 			if($element['access'] != 'deleted')
		 				return true;
		 		}
		 		return false;
		 	}
		 	else
		 		return true;
		 	
		}
		
		function MoveSubPagesFromTo($ParentPageID, $NewParentPageID) {
			$this->LoadParentIDs();
			foreach($this->_ParentIDPages[$ParentPageID] as $element) {
		 		$this->MoveSubPageTo($element['id'], $NewParentPageID);
		 	}
		}
		
		function MoveSubPageTo($PageID, $NewParentPageID) {
			$sql = "UPDATE " . DB_PREFIX . "pages
	 			SET page_parent_id=$NewParentPageID
 				WHERE page_id = $PageID";
 				$this->_SqlConnection->SqlQuery($sql);	
		}
		
		
		function GetPageData($PageID, $Field) {
			$this->LoadData($PageID);
			if(empty($this->_Pages[$PageID])) {
				return false;
			}
			else
				return $this->_Pages[$PageID][$Field];
		}
		
		/**
		 * @param Array PageIDs
		 * @param integer MenuID
		 */
		function GenerateMenu($PageIDs, $MenuID = 1) {
			$oderID = 0;
			if(count($PageIDs) <= 0)
				return;
			foreach($PageIDs as $pageID) {
				$sql = "SELECT *
		 			FROM " . DB_PREFIX . "pages
		 			WHERE page_id=$pageID";
		 		$pageResult = $this->_SqlConnection->SqlQuery($sql);
		 		if($page = mysql_fetch_object($pageResult)) {
		 			if($page->page_access == 'public') {
			 			$link = "l:" . $page->page_name;
			 			$sql = "INSERT INTO " . DB_PREFIX . "menu
							(menu_text, menu_link, menu_new, menu_orderid, menu_menuid, menu_page_id)
							VALUES ('$page->page_title', '$link', 'no', $oderID, $MenuID, $page->page_id)";
						$this->_SqlConnection->SqlQuery($sql);
						$oderID++;
		 			}
		 		}
			}
		}
		
		/**
		 * @param integer MenuID
		 */
		function ClearMenu($MenuID = 1) {
			$sql = "DELETE
				FROM " . DB_PREFIX . "menu
				WHERE menu_menuid=$MenuID";
			$this->_SqlConnection->SqlQuery($sql);
		}
		
		/** PageStructurePulldown
		 * This function generates all <option>-tags to create a pull-down-list wherec you can select a page 
		 * 
		 * @access public
		 * @param integer Topnode The ID of the root-element
		 * @param integer Deep
		 * @param string Topnumber
		 * @param integer Without The element with this ID and all its child-elemnts will be ignored
		 * @param integer Selected This element will be selected
		 * @return string
		 */
		function PageStructurePulldown($Topnode = 0, $Deep = 0, $Topnumber = '', $Without = -1, $Selected = -1) {
		 	$out = '';
			if(empty($this->_ParentIDPages[$Topnode]))
		 		return '';
		 	$number = 1;
		 	foreach($this->_ParentIDPages[$Topnode] as $page) {
		 		if($page['id'] != $Without) {
		 			$out .= "<option style=\"padding-left:" . ($Deep * 1.5) . "em;\" value=\"" . $page['id'] . "\"" . (($page['id'] == $Selected) ? ' selected="selected"' : '') . ">$Topnumber$number. " . $page['title'] . " (" . $page['name'] . ")</option>\r\n";
		 			$out .= $this->PageStructurePulldown($page['id'], $Deep + 1, $Topnumber . $number. "." ,$Without, $Selected);
		 			$number++;
		 		}
		 		
		 	}
		 	return $out;
		}
		
		function LoadInlineMenuData($PageID) {
			if(empty($this->_InlineMenus[$PageID])) {
				$sql = "SELECT *
		 			FROM " . DB_PREFIX . "inlinemenu
		 			WHERE page_id=$PageID
		 			LIMIT 0, 1";
		 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
		 		if($menu = mysql_fetch_object($menuResult))
			 		$this->_InlineMenus[$PageID] = array('image' => $menu->inlinemenu_image,
									'imageThumb' => $menu->inlinemenu_image_thumb,
									'imageTitle' => $menu->inlinemenu_image_title,
									'html' => $menu->inlinemenu_html);
		 	}
		}
		
		function InlineMenuExists($PageID) {
			$this->LoadInlineMenuData($PageID);
			return !empty($this->_InlineMenus[$PageID]);
		}
		
		function CreateInlineMenu($PageID) {
			$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu (page_id, inlinemenu_image, inlinemenu_image_thumb, inlinemenu_image_title, inlinemenu_html)
				VALUES($PageID, '', '', '', '')";
			$this->_SqlConnection->SqlQuery($sql);
			$this->_InlineMenus[$PageID] = array('image' => '',
								'imageThumb' => '',
								'imageTitle' => '',
								'html' => '');
		}
		
		function GetInlineMenuData($PageID, $Field) {
			$this->LoadInlineMenuData($PageID);
			if(empty($this->_InlineMenus[$PageID])) {
				return false;
			}
			else
				return $this->_InlineMenus[$PageID][$Field];
		}
		
		function SetInlineMenuImage($PageID, $ImageThumb, $Image) {
			//echo $PageID . "<br>" . $ImageThumb. "<br>" . $Image;
			$sql = "UPDATE " . DB_PREFIX . "inlinemenu
				SET inlinemenu_image_thumb='$ImageThumb', inlinemenu_image='$Image'
				WHERE page_id=$PageID";
			//echo "<br>$sql";
			$this->_SqlConnection->SqlQuery($sql);
			$this->_InlineMenus[$PageID]['image'] = $Image;
			$this->_InlineMenus[$PageID]['imageThumb'] = $ImageThumb;
		}
		
		function SetInlineMenuImageTitle($PageID, $Title) {
			$sql = "UPDATE " . DB_PREFIX . "inlinemenu
				SET inlinemenu_image_title='$Title'
				WHERE page_id=$PageID";
			$this->_SqlConnection->SqlQuery($sql);
			$this->_InlineMenus[$PageID]['imageTitle'] = $Title;
		}
		
		function RemoveInlineMenuImage($PageID) {
			$sql = "UPDATE " . DB_PREFIX . "inlinemenu
				SET inlinemenu_image='', inlinemenu_image_thumb=''
				WHERE page_id=$PageID";
			$this->_SqlConnection->SqlQuery($sql);
			$this->_InlineMenus[$PageID]['image'] = '';
			$this->_InlineMenus[$PageID]['imageThumb'] = '';
		}
		
		/**
		 * @param integer PageID
		 * @return integer
		 */
		function LoadNextInlineMenuSortID($PageID) {
			// Check if already has loaded
			if(!isset($this->_NextSortID[$PageID])) {
				// Load it!
				$sql = "SELECT inlineentry_sortid
			 		FROM " . DB_PREFIX . "inlinemenu_entries
			 		WHERE inlineentry_page_id = $PageID
			 		ORDER BY inlineentry_sortid DESC
			 		LIMIT 0, 1";
			 	$lastOrderIDResult = $this->_SqlConnection->SqlQuery($sql);
			 	if($lastOrderID = mysql_fetch_object($lastOrderIDResult)){
					$this->_NextSortID[$PageID] = $lastOrderID->inlineentry_sortid;
				}
				else // No entries! Start with zero
					$this->_NextSortID[$PageID] = 0;
			}
			// Increment the order ID
			$this->_NextSortID[$PageID]++;
			
			return $this->_NextSortID[$PageID];
		}
		
		function SaveInlineMenuLink($Text, $Link, $EntryID, $PageID) {
			$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
				SET inlineentry_text = '$Text',inlineentry_link = '$Link'
				WHERE inlineentry_id=$EntryID";
			$this->_SqlConnection->SqlQuery($sql);
			$this->GenerateInlineMenu($PageID);
		}
		
		function AddInlineMenuLink($Text, $Link, $PageID) {
			$sortID = $this->LoadNextInlineMenuSortID($PageID);
			$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu_entries (inlineentry_sortid, inlineentry_page_id, inlineentry_type, inlineentry_text, inlineentry_link)
				VALUES ($sortID, $PageID, 'link', '$Text','$Link')";
			$this->_SqlConnection->SqlQuery($sql);
			$this->GenerateInlineMenu($PageID);
		}
		
		function SaveInlineMenuInternLink($Text, $Link, $EntryID, $PageID) {
			$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
				SET inlineentry_text = '$Text',inlineentry_link = 'index.php?page=$Link'
				WHERE inlineentry_id=$EntryID";
			$this->_SqlConnection->SqlQuery($sql);
			$this->GenerateInlineMenu($PageID);
		}
		
		function AddInlineMenuInternLink($Text, $Link, $PageID) {
			$sortID = $this->LoadNextInlineMenuSortID($PageID);
			$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu_entries (inlineentry_sortid, inlineentry_page_id, inlineentry_type, inlineentry_text, inlineentry_link)
				VALUES ($sortID, $PageID, 'intern', '$Text','index.php?page=$Link')";
			$this->_SqlConnection->SqlQuery($sql);
			$this->GenerateInlineMenu($PageID);
		}
		
		function SaveInlineMenuText($Text, $EntryID, $PageID) {
			$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
				SET inlineentry_text = '$Text'
				WHERE inlineentry_id=$EntryID";
			$this->_SqlConnection->SqlQuery($sql);
			$this->GenerateInlineMenu($PageID);
		}
		
		function AddInlineMenuText($Text, $PageID) {
			$sortID = $this->LoadNextInlineMenuSortID($PageID);
			$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu_entries (inlineentry_sortid, inlineentry_page_id, inlineentry_type, inlineentry_text)
				VALUES ($sortID, $PageID, 'text', '$Text')";
			$this->_SqlConnection->SqlQuery($sql);
			$this->GenerateInlineMenu($PageID);
		}
		
		function SaveInlineMenuDownload($Text, $Link, $EntryID, $PageID) {
			$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
				SET inlineentry_text = '$Text',inlineentry_link = '$Link'
				WHERE inlineentry_id=$EntryID";
			$this->_SqlConnection->SqlQuery($sql);
			$this->GenerateInlineMenu($PageID);
		}
		
		function AddInlineMenuDownload($Text, $Link, $PageID) {
			$sortID = $this->LoadNextInlineMenuSortID($PageID);
			$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu_entries (inlineentry_sortid, inlineentry_page_id, inlineentry_type, inlineentry_ext, inlineentry_link)
				VALUES ($sortID, $PageID, 'download', '$Text','$Link')";
			$this->_SqlConnection->SqlQuery($sql);
			$this->GenerateInlineMenu($PageID);
		}
		
		function GenerateInlineMenu($PageID) {
			$sql = "SELECT *
				FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentry_page_id=$PageID
				ORDER BY inlineentry_sortid ASC";
			$inlieMenuEntriesResult = $this->_SqlConnection->SqlQuery($sql);
			$inlieMenuHtml = "<ul>\r\n";
			while($inlieMenuEntry = mysql_fetch_object($inlieMenuEntriesResult)) {
				if($inlieMenuEntry->inlineentry_type == 'text')
					$inlieMenuHtml .= "\t<li class=\"inline_text\">" . nl2br($inlieMenuEntry->inlineentry_text) . "</li>\r\n";
				elseif($inlieMenuEntry->inlineentry_type == 'link')
					$inlieMenuHtml .= "\<li class=\"inline_link\"><a href=\"$inlieMenuEntry->inlineentry_link\">$inlieMenuEntry->inlineentry_text</a></li>\r\n";
				elseif($inlieMenuEntry->inlineentry_type == 'intern')
					$inlieMenuHtml .= "\t<li class=\"inline_intern\"><a href=\"$inlieMenuEntry->inlineentry_link\">$inlieMenuEntry->inlineentry_text</a></li>\r\n";
				elseif($inlieMenuEntry->inlineentry_type == 'download') {
						$sql = "SELECT *
						FROM " . DB_PREFIX . "files
						WHERE file_id=$inlieMenuEntry->inlineentry_link
						Limit 0,1";
					$fileResult = $this->_SqlConnection->SqlQuery($sql);
					if($file = mysql_fetch_object($fileResult)) {
						if(file_exists($file->file_path)) {
							$size = kbormb(filesize($file->file_path), false);
							$inlieMenuHtml .= "\t<li class=\"inline_download\"><a href=\"download.php?file_id=$inlieMenuEntry->inlineentry_link\" title=\"Download von &quot;$file->file_name&quot; bei einer Gr&ouml;&szlig;e von $size\">$inlieMenuEntry->inlineentry_text</a> (<span class=\"filesize\">$size</span>)</li>\r\n";
						}
					}
				}
			}
			$inlieMenuHtml .= "</ul>\r\n";
			if($inlieMenuHtml == "<ul>\r\n</ul>\r\n")
				$inlieMenuHtml = '';
			$sql = "UPDATE " . DB_PREFIX . "inlinemenu
				SET inlinemenu_html='$inlieMenuHtml'
				WHERE page_id='$PageID'";
			$this->_SqlConnection->SqlQuery($sql);	
		}
		
		function InlineMenuEntryMoveUp ($InlineMenuEntrySortID, $PageID) {
 			$sql = "SELECT *
				FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentry_sortid <= $InlineMenuEntrySortID AND inlineentry_page_id = $PageID
				ORDER BY inlineentry_sortid DESC
				LIMIT 0 , 2";
			$entriesResult = $this->_SqlConnection->SqlQuery($sql);
			
			$this->_InlineMenuEntriesSwitchSortIDs($entriesResult);
			$this->GenerateInlineMenu($PageID);
 		}
 		
 		function InlineMenuEntryMoveDown ($InlineMenuEntrySortID, $PageID) {
 			$sql = "SELECT *
				FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentry_sortid >= $InlineMenuEntrySortID AND inlineentry_page_id = $PageID
				ORDER BY inlineentry_sortid ASC
				LIMIT 0, 2";
			$entriesResult = $this->_SqlConnection->SqlQuery($sql);
			
			$this->_InlineMenuEntriesSwitchSortIDs($entriesResult);
			$this->GenerateInlineMenu($PageID);
 		}
 		
 		function _InlineMenuEntriesSwitchSortIDs ($InlineMenuEntriesResult) {
 			if ($entry = mysql_fetch_object($InlineMenuEntriesResult)) {
				$inlineMenuEntryID1 = $entry->inlineentry_id;
				$inlineMenuEntryOrderID1 = $entry->inlineentry_sortid;
				
				if ($entry = mysql_fetch_object($InlineMenuEntriesResult)) {
					$inlineMenuEntryID2 = $entry->inlineentry_id;
					$inlineMenuEntryOrderID2 = $entry->inlineentry_sortid;
					
					$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
						SET inlineentry_sortid=$inlineMenuEntryOrderID2
						WHERE inlineentry_id=$inlineMenuEntryID1";
					$this->_SqlConnection->SqlQuery($sql);
						 
					$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
						SET inlineentry_sortid=$inlineMenuEntryOrderID1
						WHERE inlineentry_id=$inlineMenuEntryID2";
					$this->_SqlConnection->SqlQuery($sql);
				}
			}
 		}
 		
 		function RemoveInlineMenuEntry ($EntryID) {
 			$sql = "DELETE FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentry_id=$EntryID";
			$this->_SqlConnection->SqlQuery($sql);
 		}
 		
 		function LoadInlineMenuEntry ($EntryID) {
 			$sql = "SELECT *
				FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentry_id = $EntryID
				LIMIT 0, 1";
			$entryResult = $this->_SqlConnection->SqlQuery($sql);
			if($entry = mysql_fetch_object($entryResult)) {
				return array('type' => $entry->inlineentry_type,
						'text' => $entry->inlineentry_text,
						'link'=> $entry->inlineentry_link );
			}
			return false;
 		}
	}
?>