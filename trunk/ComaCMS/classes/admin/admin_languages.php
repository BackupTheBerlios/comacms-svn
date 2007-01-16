<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin_languages.php
 # created              : 2006-12-20
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
	require_once __ROOT__ . '/classes/languages.php';
	require_once __ROOT__ . '/classes/admin/admin.php';
	
	/**
	 * @package ComaCMS
	 */
	class Admin_Languages extends Admin {
		
		/**
		 * Language functions
		 * @access private
		 * @var class Language functions
		 */
		var $_Languages;
		
		/**
		 * Adds specific initialization actions to the initialization of the Admin_Language class
		 * @param class SqlConnection A link to the SqlConnectionclass
		 * @access private
		 */
		function _Init() {
			$this->_Languages = new Languages(&$this->_SqlConnection);
			$this->_Languages->LoadAllLanguages();
		}
		
 		/**
 		 * Returns the page which is selected by <var>$Action</var>
 		 * @param string Action Contains the name of the subpage
		 * @return string Pagedata
 		 */
		function GetPage($Action = '') {
			$out = '<h2>{LANG_TITLE_LANGUAGES}</h2>';
			$this->_ComaLate->SetReplacement('LANG_TITLE_LANGUAGES', $this->_Translation->GetTranslation('languages'));
			
			// Switch between the subpages
			switch ($Action) {
				case 'newLanguage':		$out .= $this->_NewLanguage();
										break;
				default:				$out .= $this->_HomePage();
										break;
			}
			
			// return the pagedata
			return $out;
		}
		
		/**
		 * Returns the template for the Homepage and sets all needed replacements
		 * @access private
		 * @return string template of homepage
		 */
		function _HomePage() {
			// Set replacements for language
			$this->_ComaLate->SetReplacement('LANG_TITLE_LANGUAGE', $this->_Translation->GetTranslation('language'));
			$this->_ComaLate->SetReplacement('LANG_ADD_LANGUAGE', $this->_Translation->GetTranslation('add_language'));
			$this->_ComaLate->SetReplacement('LANG_TITLE_ABBREVIATION', $this->_Translation->GetTranslation('abbreviation'));
			$this->_ComaLate->SetReplacement('LANG_TITLE_LANGUAGE_FILE', $this->_Translation->GetTranslation('language_file'));
			$this->_ComaLate->SetReplacement('LANG_TITLE_ACTIONS', $this->_Translation->GetTranslation('actions'));
			
			// Set replacement for languages entries
			$languages = array();
			$languages = $this->_Languages->GetAllLanguages();
			$this->_ComaLate->SetReplacement('LANGUAGES_LINE', $languages);
			
			// Set default template
			$template = '
				<a href="admin.php?page=languages&amp;action=newLanguage" class="button">{LANG_ADD_LANGUAGE}</a>';
			// Add templatetable if any languages exist
			if (empty($languages)) {
				$template .= '
				<table class="text_table full_width margin_center">
					<tr>
						<th>{LANG_TITLE_LANGUAGE}</th>
						<th>{LANG_TITLE_ABBREVIATION}</th>
						<th>{LANG_TITLE_LANGUAGE_FILE}</th>
						<th>{LANG_TITLE_ACTIONS}</th>
					</tr>
					<LANGUAGES_LINE:loop>
					<tr>
						<td>{LANG_NAME}</td>
						<td>{LANG_SHOTR_NAME}</td>
						<td>{LANG_LANG_FILE}</td>
						<td>edit delete</td>
					</tr>
					</LANGUAGES_LINE>
				</table>';
			}
			return $template;
		}
		
		/**
		 * Returns a form to add a new language to the system
		 * @access private
		 * @return string template of the form
		 */
		function _NewLanguage() {
			// Set replacements for language
			$this->_ComaLate->SetReplacement('LANG_LANGUAGE', $this->_Translation->GetTranslation('language'));
			$this->_ComaLate->SetReplacement('LANG_ADD_TO_DATABASE', $this->_Translation->GetTranslation('add_to_database'));
			$this->_ComaLate->SetReplacement('LANG_BACK', $this->_Translation->GetTranslation('back'));
			$this->_ComaLate->SetReplacement('LANG_LANGUAGE_NAME', $this->_Translation->GetTranslation('language_name'));
			$this->_ComaLate->SetReplacement('LANG_LANGUAGE_FILE', $this->_Translation->GetTranslation('language_file'));

			// Set replacement for existing languagefiles
			$this->_Languages->FindAllLangFilesInDirectory(__ROOT__ . '/lang/');
			$existingLanguagesFiles = $this->_Languages->GetExistingLangFiles();
			$this->_ComaLate->SetReplacement('LANGUAGE_FILES_LIST', $existingLanguagesFiles);
			
			// Make template
			$template = '
				<form action="admin.php" method="get">
					<fieldset>
						<legend>{LANG_LANGUAGE}</legend>
						<input type="hidden" name="action" value="checkForm" />
						<input type="hidden" name="page" value="languages" />
						<div class="row">
							<label for="LanguageName">
								{LANG_LANGUAGE_NAME}:
								<span class="info">{LANGUAGE_NAME_INFO}</span>
							</label>
							<input type="text" id="LanguageName" name="LanguageName" />
						</div>
						<div class="row">
							<label for="LanguageFile">
								{LANG_LANGUAGE_FILE}:
								<span class="info">{LANGUAGE_FILE_INFO}</span>
							</label>
							<select id="LanguageFile" name="LanguageFile"><LANGUAGE_FILES_LIST:loop>
								<option value="{FilePath}">{FileName}</option></LANGUAGE_FILES_LIST>
							</select>
						</div>
						<div class="row">
							<input type="submit" class="button" value="{LANG_ADD_TO_DATABASE}" />&nbsp;
							<a href="admin.php?page=languages" class="button">{LANG_BACK}</a>
						</div>
					</fieldset>
				</form>
				';
			return $template;
		}
	}
?>