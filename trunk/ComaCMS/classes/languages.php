<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : languages.php
 # created              : 2006-12-20
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
 	/**
 	 * @package ComaCMS
 	 */
 	class Languages {
 		
 		/**
 		 * @var Sql
 		 * @access private
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * Contains all languages from database
 		 * @var array languages
 		 * @access private
 		 */
 		var $_Languages = array();
 		
 		/**
 		 * Contains all languages files
 		 * @var array Languagefiles
 		 * @access private
 		 */
 		var $_LanguageFiles = array();
 		
 		/**
 		 * Contains all language arrays
 		 * @var array LanguageArrays
 		 * @access private
 		 */
 		var $_LanguageArrays = array();
 		
 		/**
 		 * Contains all possible languagenames by using their short name as index
 		 * @var array Languagenames
 		 * @access private
 		 */
 		var $_LanguageNames = array(
 				'af' => 'Afrikaans',
 				'sq' => 'Albanisch',
 				'am' => 'Amharisch',
 				'ar' => 'Arabisch',
 				'ar-eg' => 'Arabisch/&Auml;gypten',
 				'ar-dz' => 'Arabisch/Algerien',
 				'ar-bh' => 'Arabisch/Bahrain',
 				'ar-iq' => 'Arabisch/Irak',
 				'ar-jo' => 'Arabisch/Jordanien',
 				'ar-kw' => 'Arabisch/Jordanien',
 				'ar-lb' => 'Arabisch/Libanon',
 				'ar-ly' => 'Arabisch/Libyen',
 				'ar-ma' => 'Arabisch/Marokko',
 				'ar-om' => 'Arabisch/Oman',
 				'ar-qa' => 'Arabisch/Quatar',
 				'ar-sa' => 'Arabisch/Saudi-Arabien',
 				'ar-sy' => 'Arabisch/Syrien',
 				'ar-tn' => 'Arabisch/Tunesien',
 				'ar-ae' => 'Arabisch/V.A.E.',
 				'ar-ye' => 'Arabisch/Yemen',
 				'an' => 'Aragonesisch',
 				'hy' => 'Armenisch',
 				'az' => 'Aserbaidshanisch',
 				'as' => 'Assami',
 				'ast' => 'Asturisch',
 				'eu' => 'Baskisch',
 				'bn' => 'Bengalisch',
 				'bs' => 'Bosnisch',
 				'br' => 'Bretonisch',
 				'bg' => 'Bulgarisch',
 				'ch' => 'Chamorro');
 		
 		/**
 		 * Initializes the Language class
 		 * @access public
 		 * @return class Language functions
 		 */
 		function Languages(&$SqlConnection) {
 			$this->_SqlConnection = &$SqlConnection;
 		}
 		
 		/**
 		 * Loads all languages form mysqldatabase to local array
 		 * @access public
 		 */
 		function LoadAllLanguages() {
 			$sql = 'SELECT *
 					FROM ' . DB_PREFIX . 'languages';
 			$languagesResult = $this->_SqlConnection->SqlQuery($sql);
 			while ($language = mysql_fetch_object($languagesResult)) {
 				$this->_Languages[$language->languages_id] = array('LANG_ID' => $language->languages_id,
																'LANG_SHORT_NAME' => $language->languages_short_name, 
																'LANG_NAME' => $language->languages_name,
			/* WHY?? */											'LANG_FILE' => $language->languages_lang_file);
 			}
 		}
 		
 		/**
 		 * Returns the local Languagearray
 		 * @access public
 		 * @return array LanguagesArray
 		 */
 		function GetAllLanguages() {
 			if (!empty($this->_Languages))
 				return $this->_Languages;
 		}
 		
 		/**
 		 * Searches in a directory for languagefiles and adds them to local array
 		 * @access public
 		 * @param string Directory The directory to search in
 		 */
 		function FindAllLangFilesInDirectory($Directory) {
 			$langFiles = dir($Directory);
 			while ($langFile = $langFiles->Read()) {
 				if (startsWith($langFile, 'lang_') && endsWith($langFile, '.php')) {
 					$langString = &$this->_LanguageArrays[$langFile];
 					include($Directory . $langFile);
 					if (is_array($this->_LanguageArrays[$langFile])) {
 						$this->_LanguageFiles[] = array('FileName' => $langFile,
 														'FilePath' => $Directory . $langFile);
 					}
 				}
 			}
 		}
 		
 		/**
 		 * Returns the local languagefiles list for extern use
 		 * @access public
 		 * @return array LoadedLanguageFiles
 		 */
 		function GetExistingLangFiles() {
 			if (!empty($this->_LanguageFiles))
 				return $this->_LanguageFiles;
 		}
 	}
?>
