<?php
/**
 * @package ComaCMS
 * @subpackage Installation
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : install.class.php
 # created              : 2006-12-23
 # copyright            : (C) 2005-2007 The ComaCMS-Team
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
	require_once __ROOT__ . '/classes/sql.php';
	require_once __ROOT__ . '/classes/comalib.php';
	require_once __ROOT__ . '/classes/pagepreview.php';
	require_once __ROOT__ . '/lib/formmaker/formmaker.class.php';
	
	/**
	 * @package ComaCMS
	 * @subpackage Installation
	 */
	class Installation {
		
		/**
		 * A link to the translation class
		 * @var Language The Translation class
		 * @access private
		 */
		var $_Translation;
		
		/**
		 * A link to the ComaLate class
		 * @var ComaLate The ComaLate class
		 * @access private
		 */
		var $_ComaLate;
		
		/**
		 * A link to the ComaLib class
		 * @var ComaLib The ComaLib class
		 * @access private
		 */
		var $_ComaLib;
		
		/**
		 * A link to the MySqlConnectionClass
		 * @var Sql The SqlConnection class
		 * @access private
		 */
		var $_SqlConnection;
		
		/**
		 * Initializes the Intallation class
		 * @access public
		 * @param Language &$Translation A link to the translation class
		 * @param ComaLate &$ComaLate A link to the ComaLate class
		 * @return Installation The Intallation class
		 */
		function Installation(&$Translation, &$ComaLate) {
			$this->_Translation = &$Translation;
			$this->_ComaLate = &$ComaLate;
			$this->_ComaLib = new ComaLib();
		}
		
		/**
		 * Returns the templates for the Installsubpages and sets replacements for ComaLate
		 * @access public
		 * @param string $Action The name of the subpage
		 * @param string $Language The actual language
		 * @return string A template for the subpage 
		 */
		function GetPage($Action = '', $Language) {
			
			// Generate templateheadline
			$this->_ComaLate->SetReplacement('LANG_INSTALLATION', $this->_Translation->GetTranslation('installation'));
			$this->_ComaLate->SetReplacement('LANG_STEP', $this->_Translation->GetTranslation('step'));
			$template = "<h2>{LANG_INSTALLATION}: {LANG_STEP} {$Action}</h2>";
			
			// Get external parameters
			$Style = GetPostOrGet('style');
			$Confirmation = GetPostOrGet('confirmation');
			$AgreementError = GetPostOrGet('agreement_error');
			if (!is_numeric($AgreementError)) $AgreementError = 0;
			$DatabaseServer = GetPostOrGet('database_server');
			$DatabaseServerError = GetPostOrGet('database_server_error');
			if (!is_numeric($DatabaseServerError)) $DatabaseServerError = 0;
			$DatabaseName = GetPostOrGet('database_name');
			$DatabaseNameError = GetPostOrGet('database_name_error');
			if (!is_numeric($DatabaseNameError)) $DatabaseNameError = 0;
			$DatabaseUsername = GetPostOrGet('database_username');
			$DatabaseUsernameError = GetPostOrGet('database_username_error');
			if (!is_numeric($DatabaseUsernameError)) $DatabaseUsernameError = 0;
			$DatabasePassword = GetPostOrGet('database_password');
			$DatabasePrefix = GetPostOrGet('database_prefix');
			$DatabasePrefixError = GetPostOrGet('database_prefix_error');
			if (!is_numeric($DatabasePrefixError)) $DatabasePrefixError = 0;
			$AdminShowName = GetPostOrGet('admin_showname');
			$AdminName = GetPostOrGet('admin_name');
			$AdminPassword = GetPostOrGet('admin_password');
			$AdminPassword2 = GetPostOrGet('admin_password2');
			$CheckInputs = GetPostOrGet('CheckInputs');
			if ($CheckInputs == 'true')
				$CheckInputs = true;
			else
				$CheckInputs = false;
			
			// Switch between the subpages
			switch ($Action) {
				default:
					$template .= $this->_LanguagePage($Language);
					break;
				
				case '2':
					$template .= $this->_TemplatePage($Language);
					break;
					
				case '3':
					$template .= $this->_RequirementsPage($Language, $Style);
					break;
					
				case '4':			
					$template .= $this->_LicensePage($Language, $Style, $AgreementError);
					break;
					
				case '5':
					if ($Confirmation == 'yes') {
						$template .= $this->_DatabaseSettings($Language, $Style, $Confirmation, $DatabaseServer, $DatabaseServerError, $DatabaseName, $DatabaseNameError, $DatabaseUsername, $DatabaseUsernameError, $DatabasePrefix, $DatabasePrefixError);
					}
					else {
						header("Location: install.php?page=4&lang={$Language}&agreement_error=1&style={$Style}");
						die();
					}
					break;
				
				case '6':
					$this->_CheckDatabaseSettings($Language, $Style, $Confirmation, $DatabaseServer, $DatabaseName, $DatabaseUsername, $DatabasePassword, $DatabasePrefix);
					
				case '7':
					$template .= $this->_AddAdministrator($Language, $Style, 'yes', $AdminShowName, $AdminName, $AdminPassword, $AdminPassword2, $CheckInputs);
					break;
					
				case '8': 
					$template .= $this->_CheckAdministrator($Language, $Style, $Confirmation, $AdminShowName, $AdminName, $AdminPassword, $AdminPassword2);
					break;
					
				case '9':
					$template .= $this->_ConfigPage($Language, $Style);
					break;
				
				case '10':
					$template .= $this->_InstallationComplete();
					break;
			}
			
			// Return the template
			return $template;
		}
		
		/**
		 * Returns the template for the language page
		 * @access private
		 * @param string $Language The actual choosen language
		 * @return string The template for the language page
		 */
		function _LanguagePage($Language) {
			
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'));
			$formMaker->AddForm('installation_language', 'install.php', $this->_Translation->GetTranslation('next'), $this->_Translation->GetTranslation('language'), 'post');
			
			$formMaker->AddHiddenInput('installation_language', 'page', '2');
			
			$formMaker->AddInput('installation_language', 'lang', 'select', $this->_Translation->GetTranslation('language'), $this->_Translation->GetTranslation('please_select_your_language'));
			
			// search for the availible language-files
			$languageItems = array();
			$languageFolder = dir("../lang/");
			while($file = $languageFolder->read()) {
				// check if the language-file really exists
				if($file != "." && $file != ".." && startsWith($file, 'lang_') && endsWith($file, '.php')) {
					$file = str_replace('lang_', '', $file);
					$file = str_replace('.php', '', $file);
					if($Language == $file)
						$selected = true;
					else
						$selected = false;
					$formMaker->AddSelectEntry('installation_language', 'lang', $selected, $file, $this->_Translation->GetTranslation($file));
				}
			}
			
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateTemplate(&$this->_ComaLate, false);
			return $template;
		}
		
		/**
		 * Returns the template for the template page
		 * @access private
		 * @param string $Language The actual language
		 * @return string the template for the template chooser 
		 */
		function _TemplatePage($Language) {
			
			$pagePreview = new PagePreview(&$this->_ComaLib);
			// preselect a style
			$style = "comacms";
			
 		 	$styleSelect = $pagePreview->GetStyles(__ROOT__. '/styles/', $style);
			$this->_ComaLate->SetReplacement('PREVIEW_STYLE_SELECT', $styleSelect);
			
			// Set replacements for the template
 		 	$this->_ComaLate->SetReplacement('ACTUALSTYLE', $style);
 		 	$this->_ComaLate->SetReplacement('ACTUAL_LANGUAGE', $Language);
 		 	$this->_ComaLate->SetReplacement('LANG_SITESTYLE', $this->_Translation->GetTranslation('sitestyle'));
			
 		 	// Throw out the template data
 		 	$template = '<script type="text/javascript" language="JavaScript" src="./functions.js"></script>
						<form action="install.php" method="get">
							<fieldset>
								<iframe id="previewiframe" class="pagepreview" src="install.php?style={ACTUALSTYLE}"></iframe>
								<input type="hidden" name="page" value="3" />
								<input type="hidden" name="confirmation" value="yes" />
								<input type="hidden" name="lang" value="{ACTUAL_LANGUAGE}" />
								<legend>{LANG_SITESTYLE}</legend>
								<label for="stylepreviewselect">{LANG_SITESTYLE}:
									<select id="stylepreviewselect" name="style">
										<PREVIEW_STYLE_SELECT:loop>
											<option value="{ENTRY_VALUE}"{ENTRY_SELECTED}>{ENTRY_LONGNAME}</option>
										</PREVIEW_STYLE_SELECT>
									</select>
								</label>
								<input type="submit" value="Vorschau" onclick="preview_style();return false;" class="button" />
								<input type="submit" value="Speichern" name="save" class="button" />
							</fieldset>
						</form>';
			return $template;
		}
		
		/**
		 * Returns the template for therequirements page
		 * @access private
		 * @param string Language The actual language
		 * @param string Style The actual stye
		 * @return string template
		 */
		function _RequirementsPage($Language, $Style) {
			
			// Set phpminversion
			$phpversion_min = '4.3.0';
			
			// write a clear config if none exists
			if(!file_exists('../config.php')) {
				$writeHandle = @fopen ('../config.php', 'w');
				if($writeHandle !== false) {
					@fwrite($writeHandle, "<?php\r\n//empty config will call the installation-scrip\r\n?>");
					@fclose($writeHandle);
				}
			}
			
			// set requirementsarray for replacement
			$requirements = array();
			$ok = true;
			// check wether the config is writeable
			if(is_writable('../config.php')) {
				$requirements[] = array('requirement' => sprintf($this->_Translation->GetTranslation('is_the_file_%file%_writeable'), '/config.php'), 'answer' => $this->_Translation->GetTranslation('yes'));
			} 
			else {
				$requirements[] = array('requirement' => sprintf($this->_Translation->GetTranslation('is_the_file_%file%_writeable'), '/config.php'), 'answer' => $this->_Translation->GetTranslation('no'));
				$ok = false;
			}	
			
			// check wether the datafolder is writeable
			if(is_writable('../data/'))
				$requirements[] = array('requirement' => sprintf($this->_Translation->GetTranslation('is_the_directory_%directory%_writeable'), '/data/'), 'answer' => $this->_Translation->GetTranslation('yes'));
			else {
				$requirements[] = array('requirement' => sprintf($this->_Translation->GetTranslation('is_the_directory_%directory%_writeable'), '/data/'), 'answer' => $this->_Translation->GetTranslation('no'));
				$ok = false;
			}	
			
			// check wether the smiliesfolder is writeable
			if(is_writable('../data/smilies/'))
				$requirements[] = array('requirement' => sprintf($this->_Translation->GetTranslation('is_the_directory_%directory%_writeable'), '/data/smilies/'), 'answer' => $this->_Translation->GetTranslation('yes'));
			else {
				$requirements[] = array('requirement' => sprintf($this->_Translation->GetTranslation('is_the_directory_%directory%_writeable'), '/data/smilies/'), 'answer' => $this->_Translation->GetTranslation('no'));
				$ok = false;
			}
			
			// check wether the uploadfolder is writeable
			if(is_writable('../data/upload/'))
				$requirements[] = array('requirement' => sprintf($this->_Translation->GetTranslation('is_the_directory_%directory%_writeable'), '/data/upload/'), 'answer' => $this->_Translation->GetTranslation('yes')); 
			else {
				$requirements[] = array('requirement' => sprintf($this->_Translation->GetTranslation('is_the_directory_%directory%_writeable'), '/data/upload/'), 'answer' => $this->_Translation->GetTranslation('no'));
				$ok = false;
			}	
			
			// check wether phpversion is uptodate
			if(version_compare($phpversion_min, phpversion(), '<=') == 1)
				$requirements[] = array('requirement' => $this->_Translation->GetTranslation('php_version') . " > {$phpversion_min}", 'answer' => $this->_Translation->GetTranslation('yes'));
			else {
				$requirements[] = array('requirement' => $this->_Translation->GetTranslation('php_version') . " > {$phpversion_min}", 'answer' => $this->_Translation->GetTranslation('yes'));
				$ok = false;
			}
			$this->_ComaLate->SetReplacement('REQUIREMENTS', $requirements);
			
			// Set replacements for language
			$this->_ComaLate->SetReplacement('ACTUAL_LANGUAGE', $Language);
			$this->_ComaLate->SetReplacement('ACTUAL_STYLE', $Style);
			$this->_ComaLate->SetReplacement('LANG_REQUIREMENTS', $this->_Translation->GetTranslation('requirements'));
			$this->_ComaLate->SetReplacement('REQUIREMENTS_OK', (($ok) ? '<input type="submit" value="' . $this->_Translation->GetTranslation('next') . '" />' : $this->_Translation->GetTranslation('please_try_to_fix_these_problems_to_finish_the_installation')));
			
			// Generate the template
			$template = "\r\n\t\t\t\t"  . '<form action="install.php" method="get">
					<fieldset>
					<input type="hidden" name="page" value="4" />
					<input type="hidden" name="lang" value="{ACTUAL_LANGUAGE}" />
					<input type="hidden" name="style" value="{ACTUAL_STYLE}" />
					<legend>{LANG_REQUIREMENTS}</legend>
					<REQUIREMENTS:loop>
						<div class="row">{requirement}: <strong>{answer}</strong></div>
					</REQUIREMENTS>
					<div class="row">
						{REQUIREMENTS_OK}
					</div>
				</fieldset>
			</form>';
			return $template;
		}
		
		/**
		 * Returns the template for the license page
		 * @access private
		 * @param string Language The actual language
		 * @param string Style The actual style
		 * @param integer AgreementError The user has to agree with the license and so he is asked again
		 * @return string template
		 */
		function _LicensePage($Language, $Style, $AgreementError) {
			
			// Read licensefile and set replacement
			$this->_ComaLate->SetReplacement('LICENSE_FILE_CONTENT', file_get_contents('./license.txt'));
			
			// Set replacements
			$this->_ComaLate->SetReplacement('ACTUAL_LANGUAGE', $Language);
			$this->_ComaLate->SetReplacement('ACTUAL_STYLE', $Style);
			$this->_ComaLate->SetReplacement('LANG_LICENSE', $this->_Translation->GetTranslation('license'));
			$this->_ComaLate->SetReplacement('LANG_I_AGREE', $this->_Translation->GetTranslation('i_agree'));
			$this->_ComaLate->SetReplacement('LANG_DO_YOU_AGREE_WITH_THIS_CONDITIONS', $this->_Translation->GetTranslation('do_you_agee_with_this_conditions'));
			$this->_ComaLate->SetReplacement('LANG_NEXT', $this->_Translation->GetTranslation('next'));
			$this->_ComaLate->SetReplacement('AGREEMENT_ERROR', (($AgreementError == 1) ? '<span class="error">' . $this->_Translation->GetTranslation('you_have_to_agree_to_the_licese_to_install_ComaCMS') . '</span>' : ''));
			
			// Generate template
			$template = "\r\n\t\t\t\t" . '<form action="install.php" method="get">
					<fieldset>
						<input type="hidden" name="page" value="5" />
						<input type="hidden" name="lang" value="{ACTUAL_LANGUAGE}" />
						<input type="hidden" name="style" value="{ACTUAL_STYLE}" />
						<legend>{LANG_LICENSE}</legend>
						<div class="row">
							<textarea readonly="readonly" id="license" rows="17">{LICENSE_FILE_CONTENT}</textarea>
						</div>
						<div class="row">
							<label for="confirmation">
								<strong>{LANG_I_AGREE}:</strong>
								{AGREEMENT_ERROR}
								<span class="info">{LANG_DO_YOU_AGREE_WITH_THIS_CONDITIONS}</span>
							</label>
							<input type="checkbox" value="yes" name="confirmation" id="confirmation" />
						</div>
						<div class="row">
							<input type="submit" value="{LANG_NEXT}"/>
						</div>
					</fieldset>
				</form>';
			return $template;
		}
		
		/**
		 * Returns the template for the database settings page
		 * @access private
		 * @param string Language The actual language
		 * @param string Style The actual style
		 * @param string Confirmation Has the user agreed with the license?
		 * @param string DatabaseServer The typed in database server
		 * @param integer DatabaseServerError Has the user typed in a valid databaseserver?
		 * @param string DatabaseName The typed in database name
		 * @param integer DatabaseNameError Has the user typed in a valid database name?
		 * @param string DatabaseUsername The typed in database username
		 * @param integer DatabaseUsernameError Has the user typed in a valid database username?
		 * @param string DatabasePrefix The typed in database prefix
		 * @param integer DatabasePrefixError Has the user typed in a databaseprefix?
		 * @return sring template
		 */
		function _DatabaseSettings($Language, $Style, $Confirmation, $DatabaseServer = '', $DatabaseServerError = 0, $DatabaseName = '', $DatabaseNameError = 0, $DatabaseUsername = '', $DatabaseUsernameError = 0, $DatabasePrefix = '', $DatabasePrefixError = 0) {
			
			// Has the user agreed with the license?
			if ($Confirmation != 'yes')
				header("Location: install.php?page=3&lang={$Language}&agreement_error=1");
			
			// Generate inputs
			$inputs = array();
			$inputs[] = array(
							'name' => 'database_server',
							'type' => 'text',
							'translation' => $this->_Translation->GetTranslation('database_server'),
							'errorinformation' => (($DatabaseServerError == 1) ? '<span class="error">' . $this->_Translation->GetTranslation('you_have_to_define_a_database_server') . '</span>' : ''),
							'information' => $this->_Translation->GetTranslation('this_is_the_adress_of_the_database_server'),
							'value_preset' => (($DatabaseServer != '') ? $DatabaseServer : 'localhost'));
			$inputs[] = array(
							'name' => 'database_name',
							'type' => 'text',
							'translation' => $this->_Translation->GetTranslation('database_name'),
							'errorinformation' => (($DatabaseNameError == 1) ? '<span class="error">' . $this->_Translation->GetTranslation('you_have_to_define_a_database') . '</span>' : ''),
							'information' => $this->_Translation->GetTranslation('this_is_the_name_of_the_database_at_the_server_used_for_comacms'),
							'value_preset' => (($DatabaseName != '') ? $DatabaseName : 'comacms'));
			$inputs[] = array(
							'name' => 'database_username',
							'type' => 'text',
							'translation' => $this->_Translation->GetTranslation('database_username'),
							'errorinformation' => (($DatabaseUsernameError == 1) ? '<span class="error">' . $this->_Translation->GetTranslation('you_have_to_define_a_user_name_to_connect_to_the_database_server') . '</span>' : ''),
							'information' => $this->_Translation->GetTranslation('this_is_the_username_used_to_connect_to_the_database_server'),
							'value_preset' => (($DatabaseUsername != '') ? $DatabaseUsername : 'root'));
			$inputs[] = array(
							'name' => 'database_password',
							'type' => 'password',
							'translation' => $this->_Translation->GetTranslation('database_password'),
							'errorinformation' => '',
							'information' => $this->_Translation->GetTranslation('this_is_the_password_that_is_maybee_needed_to_connect_to_the_databaseserver_with_the_username_(can_bee_empty)'),
							'value_preset' => '');
			$inputs[] = array(
							'name' => 'database_prefix',
							'type' => 'text',
							'translation' => $this->_Translation->GetTranslation('prefix_for_tables'),
							'errorinformation' => (($DatabasePrefixError == 1) ? '<span class="error">' . $this->_Translation->GetTranslation('you_have_to_define_a_prefix') . '</span>' : ''),
							'information' => $this->_Translation->GetTranslation('with_this_prefix_written_before_each_table_you_can_identify_all_tables_belonging_to_comacms'),
							'value_preset' => (($DatabasePrefix != '') ? $DatabasePrefix : 'comacms_'));
			$this->_ComaLate->SetReplacement('INPUTS', $inputs);
			
			// Set replacements
			$this->_ComaLate->SetReplacement('ACTUAL_LANGUAGE', $Language);
			$this->_ComaLate->SetReplacement('ACTUAL_STYLE', $Style);
			$this->_ComaLate->SetReplacement('CONFIRMATION', $Confirmation);
			$this->_ComaLate->SetReplacement('LANG_DATABASE_SETTINGS', $this->_Translation->GetTranslation('database_settings'));
			$this->_ComaLate->SetReplacement('LANG_NEXT', $this->_Translation->GetTranslation('next'));
			
			// Generate template
			$template = "\r\n\t\t\t\t" . '<form action="install.php" method="get">
					<fieldset>
						<input type="hidden" name="page" value="6" />
						<input type="hidden" name="lang" value="{ACTUAL_LANGUAGE}" />
						<input type="hidden" name="confirmation" value="{CONFIRMATION}" />
						<input type="hidden" name="style" value="{ACTUAL_STYLE}" />
						<legend>{LANG_DATABASE_SETTINGS}</legend>
						<INPUTS:loop>
							<div class="row">
								<label for="{name}">
									<strong>{translation}:</strong>
									{errorinformation}
									<span class="info">{information}</span>
								</label>
								<input type="{type}" name="{name}" id="{name}" value="{value_preset}" />
							</div>
						</INPUTS>
						<div class="row">
							<input type="submit" value="{LANG_NEXT}" />
						</div>
					</fieldset>
				</form>';
			return $template;
		}
		
		/**
		 * Checks all database settings inputs, creates the config.php and initializes the database
		 * @access private
		 * @param string Language The actual language
		 * @param string Style The actual style
		 * @param string Confirmation Has the user agreed with the license?
		 * @param string DatabaseServer The address of the database server
		 * @param string DatabaseName The name of the database used for ComaCMS
		 * @param string DatabaseUsername The username to connect to the database server
		 * @param string DatabasePassword The password for the username to connect to the database server
		 * @param string DatabasePrefix The prefix used for the tables used by ComaCMS
		 * @return void
		 */
		function _CheckDatabaseSettings($Language, $Style, $Confirmation, $DatabaseServer, $DatabaseName, $DatabaseUsername, $DatabasePassword, $DatabasePrefix) {
			$ok = true;
			if (empty($DatabaseServer)) {
				$ok = false;
				$DatabaseServerError = 1;
			}
			else
				$DatabaseServerError = 0;
				
			if (empty($DatabaseUsername)) {
				$ok = false;
				$DatabaseUsernameError = 1;
			}
			else
				$DatabaseUsernameError = 0;
			
			if (empty($DatabaseName)) {
				$ok = false;
				$DatabaseNameError = 1;
			}
			else
				$DatabaseNameError = 0;
			
			if (empty($DatabasePrefix)) {
				$ok = false;
				$DatabasePrefixError = 1;
			}
			else
				$DatabasePrefixError = 0;
			
			if ($Confirmation != 'yes') {
				$ok = false;
				header("Location: install.php?page=4&lang={$Language}&agreement_error=1&style={$Style}");
				die();
			}
			if ($ok) {
				$this->_SqlConnection = new Sql($DatabaseUsername, $DatabasePassword, $DatabaseServer);
				$this->_SqlConnection->Connect($DatabaseName);
				
				// read database installation steps
				$file = 'sql/install.sql';
				$fileHandle = fopen($file, "r");
				// Read the whole file
			  	$queries = str_replace('{DB_PREFIX}', $DatabasePrefix, fread($fileHandle, filesize($file)));
				// Close the handle
			  	fclose($fileHandle);
			  	// do all database initializations
				$this->_SqlConnection->SqlExecMultiple($queries);
				
				// write config file
				$config_data = "<?php\n";
				$config_data .= '$d_server = \'' . $DatabaseServer . '\';' . "\r\n";
				$config_data .= '$d_user   = \'' . $DatabaseUsername . '\';' . "\r\n";
				$config_data .= '$d_pw     = \'' . $DatabasePassword . '\';' . " \r\n";
				$config_data .= '$d_base   = \'' . $DatabaseName . '\';' . "\r\n";
				$config_data .= '$d_pre = \'' . $DatabasePrefix . '\';' . " \r\n\r\n";
				$config_data .= 'define(\'COMACMS_INSTALLED\', true);' . "\r\n";
				$config_data .= '?>';
				$fp = @fopen('../config.php', 'w');
				$result = @fputs($fp, $config_data, strlen($config_data));
				@fclose($fp);
			}
			else {
				header("Location: install.php?page=5&lang={$Language}&style={$Style}&confirmation=yes&" . ((!empty($DatabaseServer)) ? "database_server={$DatabaseServer}&" : '') . "database_server_error={$DatabaseServerError}" . ((!empty($DatabaseUsername)) ? "&database_username={$DatabaseUsername}" : '') . "&database_username_error={$DatabaseUsernameError}" . ((!empty($DatabaseName)) ? "&database_name={$DatabaseName}" : '') . "&database_name_error={$DatabaseNameError}" . ((!empty($DatabasePrefix)) ? "&database_prefix={$DatabasePrefix}" : '') . "&database_prefix_error={$DatabasePrefixError}");
				die();
			}
		}
		
		/**
		 * Generates the formular for the adminregistration
		 * @param string $Language The actual language
		 * @param string $Style The actual style
		 * @param string $AdminShowName The AdminShowName if exists
		 * @param string $AdminName The AdminName if exists
		 * @param string $AdminPassword The AdminPassword if exists
		 * @param string $AdminPassword2 The AdminPasswordRepetition if exists
		 * @return FormMaker The link to the FormMaker class
		 */
		function _GenerateAdminForm($Language, $Style, $AdminShowName = '', $AdminName = '', $AdminPassword = '', $AdminPassword2 = '') {
			// Generate the inputs
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'));
			
			$formMaker->AddForm('admin_registration', 'install.php', $this->_Translation->GetTranslation('next'), $this->_Translation->GetTranslation('create_administrator'), 'post');
			
			$formMaker->AddHiddenInput('admin_registration', 'page', '8');
			$formMaker->AddHiddenInput('admin_registration', 'lang', $Language);
			$formMaker->AddHiddenInput('admin_registration', 'style', $Style);
			$formMaker->AddHiddenInput('admin_registration', 'confirmation', 'yes');
			
			$formMaker->AddInput('admin_registration', 'admin_showname', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example'), $AdminShowName);
			$formMaker->AddCheck('admin_registration', 'admin_showname', 'empty', $this->_Translation->GetTranslation('the_name_must_be_indicated'));
			
			$formMaker->AddInput('admin_registration', 'admin_name', 'text', $this->_Translation->GetTranslation('loginname'), $this->_Translation->GetTranslation('with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name'), $AdminName);
			$formMaker->AddCheck('admin_registration', 'admin_name', 'empty', $this->_Translation->GetTranslation('the_nickname_must_be_indicated'));
			
			$formMaker->AddInput('admin_registration', 'admin_password', 'password', $this->_Translation->GetTranslation('password'), $this->_Translation->GetTranslation('with_this_password_the_user_can_login_to_restricted_areas'), $AdminPassword);
			$formMaker->AddCheck('admin_registration', 'admin_password', 'empty', $this->_Translation->GetTranslation('the_password_field_must_not_be_empty'));
			$formMaker->AddCheck('admin_registration', 'admin_password', 'not_same_password_value_as', $this->_Translation->GetTranslation('the_password_and_its_repetition_are_unequal'), 'admin_password2');
			
			$formMaker->AddInput('admin_registration', 'admin_password2', 'password', $this->_Translation->GetTranslation('password_repetition'), $this->_Translation->GetTranslation('it_is_guaranteed_by_a_repetition_that_the_user_did_not_mistype_during_the_input'), $AdminPassword2);
			$formMaker->AddCheck('admin_registration', 'admin_password2', 'empty', $this->_Translation->GetTranslation('the_password_field_must_not_be_empty'));
			
			return $formMaker;
		}
		
		/**
		 * Returns a template for the user to add a new administrator
		 * @access private
		 * @param string $Language The actual language
		 * @param string $Style The actual style
		 * @param string $DatabaseInitialized Is the database correctly initialized?
		 * @param string $AdminShowName The typed in admin show name
		 * @param string $AdminName The typed in admin name
		 * @param string $AdminPassword The typed in admin password
		 * @param string $AdminPassword2 The typed in admin password repetition
		 * @param bool $CheckInputs Shall the inputs be checked?
		 * @return string template
		 */
		function _AddAdministrator($Language, $Style, $DatabaseInitialized, $AdminShowName, $AdminName, $AdminPassword, $AdminPassword2, $CheckInputs = false) {
			
			// Is the database realy Initialized or tries someone to skip the databasesettings?
			if ($DatabaseInitialized != 'yes')
				header("Location: install.php?page=3&lang={$Language}");
			
			$form = $this->_GenerateAdminForm($Language, $Style, $AdminShowName, $AdminName, $AdminPassword, $AdminPassword2);
			
			// Generate template
			$template = "\r\n\t\t\t\t" . $form->GenerateTemplate(&$this->_ComaLate, $CheckInputs);
			return $template;
		}
		
		/**
		 * Checks the administrator inputs
		 * @access private
		 * @param string Language The actual language
		 * @param string Style The actual style
		 * @param string Confirmation Is everything ok?
		 * @param string AdminShowName The showname of the administrator that should be created
		 * @param string AdminName The nickname of the administrator that should be created
		 * @param string AdminPassword The password for the administrator
		 * @param string AdminPasswordRepetition The repepetition of the password to prevent typing errors
		 * @return void
		 */
		function _CheckAdministrator($Language, $Style, $Confirmation, $AdminShowName, $AdminName, $AdminPassword, $AdminPasswordRepetition) {
			
			// Inititalize variables
			$d_server = 'localhost';
			$d_pre = 'comacms_';
			$d_user = 'root';
			$d_pw = '';
			$d_base = 'comacms';
			
			
			$form = $this->_GenerateAdminForm($Language, $Style, $AdminShowName, $AdminName, $AdminPassword, $AdminPasswordRepetition);
			$ok = $form->CheckInputs('admin_registration', false);
			
			// If everything is ok
			if ($ok && $Confirmation == 'yes') {
				include __ROOT__ . '/config.php';
				$sql = "INSERT INTO {$d_pre}users (user_name, user_showname, user_password, user_registerdate, user_admin, user_icq)
						VALUES ('{$AdminName}', '{$AdminShowName}', '" . md5($AdminPassword) . "', '" . mktime() . "', 'y', '');
						INSERT INTO {$d_pre}config (config_name, config_value)
						VALUES ('install_date', '" . mktime() . "');
						INSERT INTO {$d_pre}config (config_name, config_value)
						VALUES ('style', '{$Style}');
						INSERT INTO {$d_pre}pages (page_lang, page_access, page_name, page_title, page_parent_id, page_creator, page_type, page_date, page_edit_comment)
						VALUES('{$Language}', 'public', 'home', '" . $this->_Translation->GetTranslation('homepage') . "', 0, 1, 'text', " . mktime() . ", 'Installed the Homepage');";
				
				$this->_SqlConnection = new Sql($d_user, $d_pw, $d_server);
				$this->_SqlConnection->Connect($d_base);
				$this->_SqlConnection->SqlExecMultiple($sql);
				$lastid =  mysql_insert_id();
				$sql = "INSERT INTO {$d_pre}pages_text (page_id, text_page_text,text_page_html)
						VALUES ({$lastid}, '" . $this->_Translation->GetTranslation('welcome_to_this_homepage') . "', '" . $this->_Translation->GetTranslation('welcome_to_this_homepage') . "')";
				$this->_SqlConnection->SqlQuery($sql);
				
				// Lead on to the next page
				header("Location: install.php?page=9&lang={$Language}&style={$Style}");
				die();
			}
			else {
				return $this->_AddAdministrator($Language, $Style, $Confirmation, $AdminShowName, $AdminName, $AdminPassword, $AdminPasswordRepetition, true);
			}
		}
		
		/**
		 * Returns a template for a configuration page
		 * @access private
		 * @param string Language The actual language
		 * @param string Style The actual style
		 * @param string ConfigPagename The name of the page if already defined
		 * @param integer ConfigPagenameError The errorcode for the pagename field
		 * @param string ConfigKeywords The keywords of the new page if already defined
		 * @param integer ConfigKeywordsError The errorcode for the keywords field
		 * @param string ConfigThumbnailFolder The path to the thumbailfolder if already defined
		 * @param integer ConfigThumbnailFolderError The errorcode for the thumbnailfolder
		 * @param string ConfigDateDayFormatError This is the date day format if already defined
		 * @param integer ConfigDateDayFormatError The errorcode for the date day format field
		 * @param string ConfigDateTimeFormat This is the date time format if already defined
		 * @param integer ConfigDateTimeFormatError This is the errorcode for the date time field
		 * @param string ConfigAdministratorEmail The emailadress of the administrator if already defined
		 * @param integer ConfigAdministratorEmailError The errorcode for the administrator emailadress field
		 * @return string template
		 */
		function _ConfigPage($Language, $Style, $ConfigPagename = 'ComaCMS', $ConfigPagenameError = '', $ConfigKeywords = '', $ConfigKeywordsError = '', $ConfigThumbnailFolder = 'data/thumbnails/', $ConfigThumbnailFolderError = '', $ConfigDateDayFormat = 'd.m.Y', $ConfigDateDayFormatError = '', $ConfigDateTimeFormat = 'H:i:s', $ConfigDateTimeFormatError = '', $ConfigAdministratorEmail = 'administrator@comacms', $ConfigAdministratorEmailError = '') {
			
			// Generate inputs
			$inputs = array();
			$inputs[] = array(
							'name' => 'config_pagename',
							'type' => 'text',
							'translation' => $this->_Translation->GetTranslation('pagename'),
							'errorinformation' => (($ConfigPagenameError == 1) ? '<span class="error">' . $this->_Translation->GetTranslation('you_have_to_define_a_pagename') . '</span>' : ''),
							'information' => $this->_Translation->GetTranslation('here_you_can_define_the_name_of_the_page'),
							'value_preset' => (($ConfigPagename != '') ? $ConfigPagename : ''));
			
			$inputs[] = array(
							'name' => 'config_keywords',
							'type' => 'text',
							'translation' => $this->_Translation->GetTranslation('keywords'),
							'errorinformation' => (($ConfigKeywordsError == 1) ? '<span class="error">' . $this->_Translation->GetTranslation('you_have_to_define_some_keywords_describing_your_page') . '</span>' : ''),
							'information' => $this->_Translation->GetTranslation('using_the_keyword_a_searchengine_like_google_can_easily_identify_the_content_of_your_page'),
							'value_preset' => (($ConfigKeywords != '') ? $ConfigKeywords : ''));
			
			// make a text for the user out of the errorcode
			$thumbnailError = '';
			if ($ConfigThumbnailFolderError == 1)
				$thumbnailError = '<span class="error">' . $this->_Translation->GetTranslation('you_have_to_define_a_thumbnail_folder_where_comacms_can_put_the_thumbnails_of_an_fotos') . '</span>';
			
			$inputs[] = array(
							'name' => 'config_thumbnailfolder',
							'type' => 'text',
							'translation' => $this->_Translation->GetTranslation('thumbnailfolder'),
							'errorinformation' => $thumbnailError,
							'information' => $this->_Translation->GetTranslation('comacms_puts_in_this_folder_the_thumbnails_of_all_fotos_you_will_upload'),
							'value_preset' => (($ConfigThumbnailFolder != '') ? $ConfigThumbnailFolder : ''));
			
			$inputs[] = array(
							'name' => 'config_date_day_format',
							'type' => 'text',
							'translation' => $this->_Translation->GetTranslation('date_day_format'),
							'errorinformation' => (($ConfigDateDayFormatError == 1) ? '<span class="error">' . $this->_Translation->GetTranslation('you_have_to_define_a_date_day_format') . '</span>' : ''),
							'information' => $this->_Translation->GetTranslation('in_this_format_the_system_shows_dates'),
							'value_preset' => (($ConfigDateDayFormat != '') ? $ConfigDateDayFormat : ''));
			
			$inputs[] = array(
							'name' => 'config_date_time_format',
							'type' => 'text',
							'translation' => $this->_Translation->GetTranslation('date_time_format'),
							'errorinformation' => (($ConfigDateTimeFormatError == 1) ? '<span class="error">' . $this->_Translation->GetTranslation('you_have_to_define_a_date_time_format') . '</span>' : ''),
							'information' => $this->_Translation->GetTranslation('in_this_format_the_system_shows_times'),
							'value_preset' => (($ConfigDateTimeFormat != '') ? $ConfigDateTimeFormat : ''));
			
			// make a text out of the AdministratorEmailError code
			$administratorEmailError = '';
			if ($ConfigAdministratorEmailError == 1)
				$administratorEmailError = '<span class="error">' . $this->_Translation->GetTranslation('you_have_to_define_an_email_adress_for_the_administrator') . '</span>';
			
			$inputs[] = array(
							'name' => 'config_administrator_email',
							'type' => 'text',
							'translation' => $this->_Translation->GetTranslation('administrator_email'),
							'errorinformation' => $administratorEmailError,
							'information' => $this->_Translation->GetTranslation('this_is_the_email_adress_of_the_administrator_used_as_sender_in_emails_from_the_system'),
							'value_preset' => (($ConfigAdministratorEmail != '') ? $ConfigAdministratorEmail : ''));
			$this->_ComaLate->SetReplacement('INPUTS', $inputs);
			
			// Set replacements
			$this->_ComaLate->SetReplacement('ACTUAL_LANGUAGE', $Language);
			$this->_ComaLate->SetReplacement('ACTUAL_STYLE', $Style);
			$this->_ComaLate->SetReplacement('LANG_PREFERENCES', $this->_Translation->GetTranslation('preferences'));
			$this->_ComaLate->SetReplacement('LANG_NEXT', $this->_Translation->GetTranslation('next'));
			
			// Generate template
			$template = "\r\n\t\t\t\t" . '<form action="install.php" method="get">
					<fieldset>
						<input type="hidden" name="page" value="10" />
						<input type="hidden" name="lang" value="{ACTUAL_LANGUAGE}" />
						<input type="hidden" name="style" value="{ACTUAL_STYLE}" />
						<legend>{LANG_PREFERENCES}<legend>
						<INPUTS:loop>
							<div class="row">
								<label for="{name}">
									<strong>{translation}:</strong>
									{errorinformation}
									<span class="info">{information}</span>
								</label>
								<input type="{type}" name="{name}" id="{name}" value="{value_preset}" />
							</div>
						</INPUTS>
						<div class="row">
							<input type="submit" value="{LANG_NEXT}" />
						</div>
					</fieldset>
				</form>';
			
			// return the output
			return $template;
		}
		
		/**
		 * Retruns the "installation ok" page
		 * @access private
		 * @return string pagedata
		 */
		function _InstallationComplete() {
			$template = $this->_Translation->GetTranslation('installation_complete');
			$template .= " \r\n<a href=\"../index.php\">Index</a>";
			return $template;
		}
	}
?>
