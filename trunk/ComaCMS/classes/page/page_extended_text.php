<?php
/**
 * @package ComaCMS
 * @subpackage Page
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : page_extended_text.php
 # created              : 2007-01-03
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
	require_once __ROOT__ . '/classes/page/page_extended.php';
	require_once __ROOT__ . '/classes/textactions.php';
	
	/**
	 * @package ComaCMS
	 * @subpackage Page
	 */
 	class Page_Extended_Text extends Page_Extended {
 		
 		function NewPage($PageID) {
 			if(!is_numeric($PageID))
 				return false;
			$sql = "INSERT INTO " . DB_PREFIX . "pages_text (page_id, text_page_text,text_page_html)
					VALUES ($PageID, '', '')";
			$this->_SqlConnection->SqlQuery($sql);
					
 		}
 		
 		function RestoreRevision($PageID, $Revision) {
 			if(!is_numeric($Revision) || !is_numeric($PageID))
 				return false;
 			if(!$this->LoadPageData($PageID))
 				return false;
 			$sql = "SELECT text.text_page_text, page.page_title
					FROM (" . DB_PREFIX . "pages_history page
					LEFT JOIN " . DB_PREFIX . "pages_text_history text ON text.page_id = page.id ) 
					WHERE page.page_id=$PageID
					ORDER BY  page.page_date ASC
					LIMIT " . ($Revision - 1) . ",1";
			$oldResult = $this->_SqlConnection->SqlQuery($sql);
			if($oldPage = mysql_fetch_object($oldResult)) {
				
				$logMessage = sprintf($this->_Translation->GetTranslation('restored_from_version'), $Revision);
				
				$this->LogPage($PageID, $logMessage);
				
				$html = addslashes(TextActions::ConvertToPreHTML($oldPage->text_page_text));
				$this->UpdatePage($PageID, addslashes($oldPage->text_page_text), $html);
				$this->UpdateTitle($PageID, addslashes($oldPage->page_title));
				
				return true;	
			}
 		}
 		
 		function GetRestoreRevisionPage($PageID, $Revision) {
 			if(!is_numeric($Revision) || !is_numeric($PageID))
 				return false;
 			if(!$this->LoadPageData($PageID))
 				return false;
 			$sql = "SELECT text.text_page_text
					FROM (" . DB_PREFIX . "pages_history page
					LEFT JOIN " . DB_PREFIX . "pages_text_history text ON text.page_id = page.id ) 
					WHERE page.page_id=$PageID
					ORDER BY  page.page_date ASC
					LIMIT " . ($Revision - 1) . ",1";
			$oldResult = $this->_SqlConnection->SqlQuery($sql);
			if($oldPage = mysql_fetch_object($oldResult)) {
				$this->_ComaLate->SetReplacement('LANG_DO_YOU_WANT_TO_REPLACE', $this->_Translation->GetTranslation('do_you_want_to_replace_the_first_text_with_the_secont_one'));
				$this->_ComaLate->SetReplacement('LANG_NO', $this->_Translation->GetTranslation('no'));
				$this->_ComaLate->SetReplacement('LANG_YES', $this->_Translation->GetTranslation('yes'));
				$this->_ComaLate->SetReplacement('LANG_CURRENT', $this->_Translation->GetTranslation('current'));
				$this->_ComaLate->SetReplacement('LANG_REVISION', $this->_Translation->GetTranslation('revision'));
				//print_r($this->_PagesData);
				$this->_ComaLate->SetReplacement('TEXT_CURRENT', $this->_PagesData[$PageID]['pageText']);
				$this->_ComaLate->SetReplacement('TEXT_OLD', $oldPage->text_page_text);
				
				$this->_ComaLate->SetReplacement('REVISION', $Revision);
				$this->_ComaLate->SetReplacement('PAGE_ID', $PageID);
				$out = '{LANG_DO_YOU_WANT_TO_REPLACE}<br />
						<a href="admin.php?page=pagestructure&amp;action=restorePage&amp;pageID={PAGE_ID}&amp;revision={REVISION}&amp;sure=1" class="button">{LANG_YES}</a>
		 				<a href="admin.php?page=pagestructure&amp;action=pageInfo&amp;pageID={PAGE_ID}" class="button">{LANG_NO}</a>
						<div class="column ctwo">
							<h3>{LANG_CURRENT}</h3>
							<pre class="code"> {TEXT_CURRENT}&nbsp;</pre>
						</div>
						<div class="column ctwo">
							<h3>{LANG_REVISION} {REVISION}</h3>
							<pre class="code"> {TEXT_OLD}&nbsp;</pre>
						</div>
						<p class="after_column" />';
				return $out;
			}
  		}
 		
 		function LoadPageData($PageID) {
 			if(!is_numeric($PageID))
 				return false;
 			$sql = "SELECT struct.page_id, struct.page_title, text.text_page_text, struct.page_edit_comment, struct.page_Type, struct.page_name, struct.page_date, struct.page_creator, struct.page_lang, struct.page_parent_id, struct.page_type
					FROM ( " . DB_PREFIX. "pages struct
					LEFT JOIN " . DB_PREFIX . "pages_text text ON text.page_id = struct.page_id )
					WHERE struct.page_id='$PageID' AND struct.page_type='text'";
			$pageDataResult = $this->_SqlConnection->SqlQuery($sql);
			if($pageData = mysql_fetch_object($pageDataResult)) {
				$this->_PagesData[$PageID] = array('pageTitle' => $pageData->page_title,
												'pageText' => $pageData->text_page_text,
												'pageType' => $pageData->page_type,
												'pageComment' => $pageData->page_edit_comment,
												'pageName' => $pageData->page_name,
												'pageDate' => $pageData->page_date,
												'pageCreator' => $pageData->page_creator,
												'pageLang' => $pageData->page_lang,
												'pageParentID' => $pageData->page_parent_id);
				return true;
			}
			else 
				return false;
			
			
 		}
 		
 		function GetPageData($PageID) {
 			if(!is_numeric($PageID))
 				return false;
 			if($this->LoadPageData($PageID))
 				return $this->_PagesData[$PageID];
 			return array();
 		}
 		
 		/**
 		 * @param integer $PageID
 		 * @return boolean
 		 */	
 		function PageHasHistory($PageID) {
 			if(!is_numeric($PageID))
 				return false;
 			$sql = 'SELECT *
					FROM ' . DB_PREFIX . 'pages_history
					WHERE page_id = ' . $PageID . '
					LIMIT 1';
			$countResult = $this->_SqlConnection->SqlQuery($sql);
			if(mysql_num_rows($countResult) > 0)
				return true;
			return false;
 		}
 		
 		function _EditPageImportOdt() {
 			$template ='<fieldset>
 							<legend>{LANG_IMPORT_ODT}</legend>
 							<h3>{LANG_USE_UPLOADED_FILE}</h3>
 								<form action="admin.php" method="post">
 									<input type="hidden" name="page" value="pagestructure" />
									<input type="hidden" name="action" value="savePage" />
									<input type="hidden" name="pageID" value="{PAGE_ID}" />
									<select></select>
 								</form>
 							<h3>{UPLOAD_NEW_FILE}</h3>
 						</fieldset>';
 			return $template;
 		}
 		
 		function GetEditPage($PageID) {
			if(!is_numeric($PageID))
 				return false;
			$action2 = GetPostOrGet('action2');
			switch($action2) {
				case 'importOdt':
					return $this->_EditPageImportOdt($PageID);
			}
			$preview = GetPostOrGet('pagePreview');

			$pageData = array();
			
			// oh.. somebody called the previewfunction without javascript
			if($preview != '') {
				$pageData['pageTitle'] = stripslashes(GetPostOrGet('pageTitle'));
				$pageData['pageText'] = stripslashes(GetPostOrGet('pageText'));
				$pageData['pageComment'] = stripslashes(GetPostOrGet('pageEditComment'));
				$this->_ComaLate->SetCondition('page_preview');
				$this->_ComaLate->SetReplacement('PREVIEW_CONTENT', urlencode(TextActions::ConvertToPreHTML($pageData['pageText'])));
			}
			else{
				if(!$this->LoadPageData($PageID))
					return false;
				$pageData = &$this->_PagesData[$PageID];
				if($this->PageHasHistory($PageID))
					$pageData['pageComment'] = $this->_Translation->GetTranslation('edited') . '...';
			}
			$pageData['pageText'] = str_replace('<', '&lt;', $pageData['pageText']);
			$pageData['pageText'] = str_replace('>', '&gt;', $pageData['pageText']);
			$this->_ComaLate->SetReplacement('PAGE_ID', $PageID);
			$this->_ComaLate->SetReplacement('PAGE_TITLE', $pageData['pageTitle']);
			$this->_ComaLate->SetReplacement('PAGE_TEXT', $pageData['pageText']);
			
			
			$this->_ComaLate->SetReplacement('COMMENT_VALUE', $pageData['pageComment']);
			/*$count == 0 : => $page_data->page_edit_comment
			is_numeric($change) : =>  sprintf($translation->GetTranslation('edited_from_version'), $change)
			else $page_edit_comment */
						
			$this->_ComaLate->SetReplacement('LANG_EDIT_PAGE', $this->_Translation->GetTranslation('edit_page'));
			$this->_ComaLate->SetReplacement('LANG_TITLE', $this->_Translation->GetTranslation('title'));
			$this->_ComaLate->SetReplacement('LANG_TEXT', $this->_Translation->GetTranslation('text'));
			$this->_ComaLate->SetReplacement('LANG_BOLD_TEXT', $this->_Translation->GetTranslation('bold_text'));
			$this->_ComaLate->SetReplacement('LANG_FORMAT_BOLD', $this->_Translation->GetTranslation('format_text_bold'));
			$this->_ComaLate->SetReplacement('LANG_ITALIC_TEXT', $this->_Translation->GetTranslation('italic_text'));
			$this->_ComaLate->SetReplacement('LANG_FORMAT_ITALIC', $this->_Translation->GetTranslation('format_text_italic'));
			$this->_ComaLate->SetReplacement('LANG_UNDERLINED_TEXT', $this->_Translation->GetTranslation('underlined_text'));
			$this->_ComaLate->SetReplacement('LANG_FORMAT_UNDERLINE', $this->_Translation->GetTranslation('underline_text'));
			$this->_ComaLate->SetReplacement('LANG_FORMAT_HEADLINE', $this->_Translation->GetTranslation('format_text_as_a_headline'));
			$this->_ComaLate->SetReplacement('LANG_HEADLINE', $this->_Translation->GetTranslation('headline'));
			$this->_ComaLate->SetReplacement('LANG_COMMENT', $this->_Translation->GetTranslation('comment_on_change'));
			$this->_ComaLate->SetReplacement('LANG_TITLE_INFO', $this->_Translation->GetTranslation('the_title_is_someting_like_a_headline_of_the_page'));
			$this->_ComaLate->SetReplacement('LANG_COMMENT_INFO', $this->_Translation->GetTranslation('a_short_description_what_you_did_here'));
			$this->_ComaLate->SetReplacement('LANG_MAKE_BIGGER', $this->_Translation->GetTranslation('make_input_box_bigger'));
			$this->_ComaLate->SetReplacement('LANG_MAKE_SMALLER', $this->_Translation->GetTranslation('make_input_box_smaller'));
			$this->_ComaLate->SetReplacement('LANG_SAVE', $this->_Translation->GetTranslation('save'));
			$this->_ComaLate->SetReplacement('LANG_PREVIEW', $this->_Translation->GetTranslation('preview'));
			$this->_ComaLate->SetReplacement('LANG_ABORT', $this->_Translation->GetTranslation('abort'));
			$this->_ComaLate->SetReplacement('LANG_IMPORT', $this->_Translation->GetTranslation('import'));
			$this->_ComaLate->SetReplacement('LANG_IMPORT_ODT', $this->_Translation->GetTranslation('import_odt'));
			//mit der importfunkion ist es möglich Textdokumente im odt format direkt importieren zu lassen
			$this->_ComaLate->SetReplacement('LANG_IMPORT_INFO', $this->_Translation->GetTranslation('the_odt_import_make_it_possible_to_load_documents_saved_in_the_odt_format'));

			$template = '
			<fieldset>
				<legend>{LANG_EDIT_PAGE}</legend>
				<form action="admin.php" method="post">
					<input type="hidden" name="page" value="pagestructure" />
					<input type="hidden" name="action" value="savePage" />
					<input type="hidden" name="pageID" value="{PAGE_ID}" />
					<div class="row">
 						<label for="pageTitle">
 							<strong>{LANG_TITLE}:</strong>
 							<span class="info">{LANG_TITLE_INFO}</span>
 						</label>
 						<input type="text" name="pageTitle" id="pageTitle" value="{PAGE_TITLE}" />
 					</div>
 					<div class="row">
 						<label>
 							<strong>{LANG_IMPORT}:</strong>
 							<span class="info">{LANG_IMPORT_INFO}</span>
 						</label>
 						<a class="button" href="admin.php?page=pagestructure&amp;action=editPage&amp;pageID={PAGE_ID}&amp;action2=importOdt">{LANG_IMPORT_ODT}</a>
 					</div>
 					<div class="row">
 						<label for="editor">
 							<strong>{LANG_TEXT}:</strong>
 						</label>
 						<script type="text/javascript" language="JavaScript" src="system/functions.js"></script>
						<script type="text/javascript" language="javascript">
							//<![CDATA[
							writeButton("img/button_fett.png", "{LANG_FORMAT_BOLD}", "**", "**", "{LANG_BOLD_TEXT}", "f");
							writeButton("img/button_kursiv.png", "{LANG_FORMAT_ITALIC}", "//", "//", "{LANG_ITALIC_TEXT", "k");
							writeButton("img/button_unterstrichen.png", "{LANG_FORMAT_UNDERLINE}", "__", "__", "{LANG_UNDERLINED_TEXT}", "u");
							writeButton("img/button_ueberschrift.png", "{LANG_FORMAT_HEADLINE}", "== ", " ==", "{LANG_HEADLINE}", "h");
							//]]>
						</script><br />
 						<textarea id="editor" class="edit" name="pageText">{PAGE_TEXT}</textarea>
	 					<script type="text/javascript" language="javascript">
	 						//<![CDATA[
							document.write(\'<div style="float:right;">\');
							document.write(\'<img onclick="resizeBox(\\\'editor\\\', -5, 17)" title="{LANG_MAKE_SMALLER}" alt="{LANG_MAKE_SMALLER}" class="resize" src="img/up.png" /> \');
							document.write(\'<img onclick="resizeBox(\\\'editor\\\', 5, 17)" title="{LANG_MAKE_BIGGER}" alt="{LANG_MAKE_BIGGER}" class="resize" src="img/down.png" /><br />\');
							document.write(\'<\' + \'/div>\');
							//]]>
						</script>
					</div>
					<div class="row">
						<label for="pageEditComment">
 							<strong>{LANG_COMMENT}:</strong>
 							<span class="info">{LANG_COMMENT_INFO}</span>
 						</label>
 						<input class="page_comment" name="pageEditComment" id="pageEditComment" value="{COMMENT_VALUE}" maxlength="100" type="text"/>
					</div>
					<div class="row">
						<input type="submit" value="{LANG_SAVE}" class="button" />
						<input type="submit" value="{LANG_PREVIEW}" name="pagePreview" class="button" />
						<a href="admin.php?page=pagestructure" class="button">{LANG_ABORT}</a>
					</div>
				</form>
			</fieldset>
			<page_preview:condition>
			<fieldset>
				<legend>{LANG_PREVIEW}</legend>
				<iframe class="pagepreview" src="index.php?content={PREVIEW_CONTENT}"></iframe>
			</fieldset>
			</page_preview>';
			
			return $template;
		}
		
		function GetSavePage($PageID) {
			if(!is_numeric($PageID))
 				return false;
			$preview = GetPostOrGet('pagePreview');
			// oh.. somebody called the previewfunction without javascript
			if($preview != '')
				return $this->GetEditPage($PageID);
				$pageTitle = stripslashes(GetPostOrGet('pageTitle'));
				$pageText = stripslashes(GetPostOrGet('pageText'));
				$pageComment = stripslashes(GetPostOrGet('pageEditComment'));
				$pageHtml =  TextActions::ConvertToPreHTML($pageText);
				$html = addslashes($pageHtml);
				
				$this->LogPage($PageID, $pageComment);
				$this->UpdatePage($PageID, addslashes($pageText), $html);
				$this->UpdateTitle($PageID, addslashes($pageTitle));
		}
		
		function UpdateTitle($PageID, $PageTitle) {
			if(!is_numeric($PageID))
 				return false;
			$sql = "UPDATE " . DB_PREFIX . "pages
					SET page_title='$PageTitle'
					WHERE page_id='$PageID'
					LIMIT 1";
			$this->_SqlConnection->SqlQuery($sql);
		}
		
		function UpdatePage($PageID, $PageText, $PageHtml) {
			if(!is_numeric($PageID))
 				return false;
			$sql = "UPDATE " . DB_PREFIX . "pages_text
					SET text_page_text='$PageText', text_page_html='$PageHtml'
					WHERE page_id='$PageID'
					LIMIT 1";
			$this->_SqlConnection->SqlQuery($sql);
		}
		
		function LogPage($PageID, $LogMessage) {
			if(!is_numeric($PageID))
				return false;
			 
			if(empty($this->_PagesData[$PageID]))
				$this->GetPageData($PageID);
			$pageData = &$this->_PagesData[$PageID];
			
			$sql = "INSERT INTO " . DB_PREFIX . "pages_history (page_id, page_type, page_name, page_title, page_parent_id, page_lang, page_creator, page_date, page_edit_comment)
					VALUES($PageID, '{$pageData['pageType']}', '" . addslashes($pageData['pageName']) ."', '" . addslashes($pageData['pageTitle']) . "', {$pageData['pageParentID']}, '" . addslashes($pageData['pageLang']) . "', {$pageData['pageCreator']}, {$pageData['pageDate']}, '{$pageData['pageComment']}')";
			$this->_SqlConnection->SqlQuery($sql);
			$lastID = mysql_insert_id();
			
			
			$sql = "SELECT text_page_text
					FROM " . DB_PREFIX . "pages_text
					WHERE page_id=$PageID
					LIMIT 1";
			$pageResult = $this->_SqlConnection->SqlQuery($sql);
			if($page = mysql_fetch_object($pageResult)) {
				$sql = "INSERT INTO " . DB_PREFIX . "pages_text_history (page_id, text_page_text)
						VALUES ($lastID, '" . addslashes($page->text_page_text) . "')";
				$this->_SqlConnection->SqlQuery($sql);	
			}
			
			
			$sql = "UPDATE " . DB_PREFIX . "pages
					SET page_creator='{$this->_User->ID}', page_date='" . mktime() . "',  page_edit_comment = '$LogMessage'
					WHERE page_id='$PageID'
					LIMIT 1";
			$this->_SqlConnection->SqlQuery($sql);
		}
		
 	}
 
?>
