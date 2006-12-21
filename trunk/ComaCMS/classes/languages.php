<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : languages.php
 # created              : 2005-12-20
 # copyright            : (C) 2005-2006 The ComaCMS-Team
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
 				$this->_Languages[$language->languages_id] = array('Id' => $language->languages_id,
																'ShortName' => $language->languages_short_name, 
																'Name' => $language->languages_name,
																'LanguageFile' => $language->languages_lang_file);
 			}
 		}
 		
 		/**
 		 * Returns the local Languagearray
 		 * @return array Languagesarray
 		 */
 		function GetAllLanguages() {
 			if (!empty($this->_Languages)) {
 				return $this->_Languages;
 			}
 		}
 	}
?>
