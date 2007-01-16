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
			
			// Switch between the subpages
			switch ($Action) {
				default:
					$template .= $this->_LanguagePage($Language);
					break;
				
				case '2':
					$template .= $this->_TemplatePage($Language);
					break;
					
				case '3':
					$template .= $this->_RequirementsPage($Language);
					break;
					
				case '4':			
					$template .= $this->_LicensePage($Language);
					break;
					
				case '5':
					$template .= $this->_DatabaseSettings($Language);
					break;
				
				case '6':
					$template .= $this->_CheckDatabaseSettings($Language);
					break;
					
				case '7':
					$template .= $this->_AddAdministrator($Language);
					break;
					
				case '8': 
					$template .= $this->_CheckAdministrator($Language);
					break;
					
				case '9':
					$template .= $this->_ConfigPage($Language);
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
			
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false);
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
		 * @return string template
		 */
		function _RequirementsPage($Language) {
			
			// Get external Parameters
			$Style = GetPostOrGet('style');
			
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
		 * @return string template
		 */
		function _LicensePage($Language) {
			
			// Get external parameters
			$Style = GetPostOrGet('style');
			$AgreementError = GetPostOrGet('agreement_error');
			if (!is_numeric($AgreementError)) $AgreementError = 0;
			
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
		 * @param string $Language The actual language
		 * @return sring Returns a template for the database settings page
		 */
		function _DatabaseSettings($Language) {
			
			// Get external parameters
			$Style = GetPostOrGet('style');
			$Confirmation = GetPostOrGet('confirmation');
			
			// Has the user agreed with the license?
			if ($Confirmation != 'yes')
				header("Location: install.php?page=4&lang={$Language}&style={$Style}&agreement_error=1");
			
			// Initialize FormMaker class
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'));
			
			// Add a form
			$formMaker->AddForm('database_settings', 'install.php', $this->_Translation->GetTranslation('next'), $this->_Translation->GetTranslation('database_settings'), 'post');
			
			// Add hidden inputs
			$formMaker->AddHiddenInput('database_settings', 'page', '6');
			$formMaker->AddHiddenInput('database_settings', 'lang', $Language);
			$formMaker->AddHiddenInput('database_settings', 'confirmation', 'yes');
			$formMaker->AddHiddenInput('database_settings', 'style', $Style);
			
			// Add the inputs of the form
			$formMaker->AddInput('database_settings', 'database_server', 'text', $this->_Translation->GetTranslation('database_server'), $this->_Translation->GetTranslation('this_is_the_adress_of_the_database_server'), 'localhost');
			$formMaker->AddInput('database_settings', 'database_name', 'text', $this->_Translation->GetTranslation('database_name'), $this->_Translation->GetTranslation('this_is_the_name_of_the_database_at_the_server_used_for_comacms'), 'comacms');
			$formMaker->AddInput('database_settings', 'database_username', 'text', $this->_Translation->GetTranslation('database_username'), $this->_Translation->GetTranslation('this_is_the_username_used_to_connect_to_the_database_server'), 'root');
			$formMaker->AddInput('database_settings', 'database_password', 'password', $this->_Translation->GetTranslation('database_password'), $this->_Translation->GetTranslation('this_is_the_password_that_is_maybee_needed_to_connect_to_the_databaseserver_with_the_username_(can_bee_empty)'));
			$formMaker->AddInput('database_settings', 'database_prefix', 'text', $this->_Translation->GetTranslation('prefix_for_tables'), $this->_Translation->GetTranslation('with_this_prefix_written_before_each_table_you_can_identify_all_tables_belonging_to_comacms'), 'comacms_');
			
			// Generate template
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false); 
			return $template;
		}
		
		/**
		 * Checks all database settings inputs, creates the config.php and initializes the database
		 * @access private
		 * @param string $Language The actual language
		 * @return void Returns the database settingspage with the errorcodes if there are any or sets the user to the next page
		 */
		function _CheckDatabaseSettings($Language) {
			
			// Get external parameters
			$Style = GetPostOrGet('style');
			$DatabaseServer = GetPostOrGet('database_server');
			$DatabaseName = GetPostOrGet('database_name');
			$DatabaseUsername = GetPostOrGet('database_username');
			$DatabasePassword = GetPostOrGet('database_password');
			$DatabasePrefix = GetPostOrGet('database_prefix');
			
			
			// Initialize FormMaker class
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'));
			
			// Add a form
			$formMaker->AddForm('database_settings', 'install.php', $this->_Translation->GetTranslation('next'), $this->_Translation->GetTranslation('database_settings'), 'post');
			
			// Add hidden inputs
			$formMaker->AddHiddenInput('database_settings', 'page', '6');
			$formMaker->AddHiddenInput('database_settings', 'lang', $Language);
			$formMaker->AddHiddenInput('database_settings', 'confirmation', 'yes');
			$formMaker->AddHiddenInput('database_settings', 'style', $Style);
			
			// Add the inputs of the form
			$formMaker->AddInput('database_settings', 'database_server', 'text', $this->_Translation->GetTranslation('database_server'), $this->_Translation->GetTranslation('this_is_the_adress_of_the_database_server'), $DatabaseServer);
			$formMaker->AddInput('database_settings', 'database_name', 'text', $this->_Translation->GetTranslation('database_name'), $this->_Translation->GetTranslation('this_is_the_name_of_the_database_at_the_server_used_for_comacms'), $DatabaseName);
			$formMaker->AddInput('database_settings', 'database_username', 'text', $this->_Translation->GetTranslation('database_username'), $this->_Translation->GetTranslation('this_is_the_username_used_to_connect_to_the_database_server'), $DatabaseUsername);
			$formMaker->AddInput('database_settings', 'database_password', 'password', $this->_Translation->GetTranslation('database_password'), $this->_Translation->GetTranslation('this_is_the_password_that_is_maybee_needed_to_connect_to_the_databaseserver_with_the_username_(can_bee_empty)'));
			$formMaker->AddInput('database_settings', 'database_prefix', 'text', $this->_Translation->GetTranslation('prefix_for_tables'), $this->_Translation->GetTranslation('with_this_prefix_written_before_each_table_you_can_identify_all_tables_belonging_to_comacms'), $DatabasePrefix);
			
			// Add the checkings for the inputs
			$formMaker->AddCheck('database_settings', 'database_server', 'empty', $this->_Translation->GetTranslation('you_have_to_define_a_database_server'));
			$formMaker->AddCheck('database_settings', 'database_name', 'empty', $this->_Translation->GetTranslation('you_have_to_define_a_database'));
			$formMaker->AddCheck('database_settings', 'database_username', 'empty', $this->_Translation->GetTranslation('you_have_to_define_a_user_name_to_connect_to_the_database_server'));
			$formMaker->AddCheck('database_settings', 'database_prefix', 'empty', $this->_Translation->GetTranslation('you_have_to_define_a_prefix'));
			
			$ok = $formMaker->CheckInputs('database_settings', true);
			
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
				
				header("Location: install.php?page=7&lang={$Language}&style={$Style}&confirmation=yes");
				die();
			}
			else {
				return "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, true);
			}
		}
		
		/**
		 * Returns a template for the user to add a new administrator
		 * @access private
		 * @param string $Language The actual language
		 * @return string The template for the add administrator page
		 */
		function _AddAdministrator($Language) {
			
			// Get external parameters
			$Style = GetPostOrGet('style');
			$Confirmation = GetPostOrGet('confirmation');
			$AdminShowName = GetPostOrGet('admin_showname');
			$AdminName = GetPostOrGet('admin_name');
			$AdminPassword = GetPostOrGet('admin_password');
			$AdminPassword2 = GetPostOrGet('admin_password2');
			
			// Is the database realy Initialized or tries someone to skip the databasesettings?
			if ($Confirmation != 'yes')
				header("Location: install.php?page=5&lang={$Language}&style={$Style}&confirmation=yes");
			
			// Initialize the FormMaker class
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'));
			
			// Add a new form for the admin registration
			$formMaker->AddForm('admin_registration', 'install.php', $this->_Translation->GetTranslation('next'), $this->_Translation->GetTranslation('create_administrator'), 'post');
			
			// Add the hidden inputs
			$formMaker->AddHiddenInput('admin_registration', 'page', '8');
			$formMaker->AddHiddenInput('admin_registration', 'lang', $Language);
			$formMaker->AddHiddenInput('admin_registration', 'style', $Style);
			$formMaker->AddHiddenInput('admin_registration', 'confirmation', 'yes');
			
			// Add the inputs
			$formMaker->AddInput('admin_registration', 'admin_showname', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example'), $AdminShowName);
			$formMaker->AddInput('admin_registration', 'admin_name', 'text', $this->_Translation->GetTranslation('loginname'), $this->_Translation->GetTranslation('with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name'), $AdminName);
			$formMaker->AddInput('admin_registration', 'admin_password', 'password', $this->_Translation->GetTranslation('password'), $this->_Translation->GetTranslation('with_this_password_the_user_can_login_to_restricted_areas'), $AdminPassword);
			$formMaker->AddInput('admin_registration', 'admin_password2', 'password', $this->_Translation->GetTranslation('password_repetition'), $this->_Translation->GetTranslation('it_is_guaranteed_by_a_repetition_that_the_user_did_not_mistype_during_the_input'), $AdminPassword2);
			
			// Generate template
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false);
			return $template;
		}
		
		/**
		 * Checks the administrator inputs
		 * @access private
		 * @param string $Language The actual language
		 * @return void Returns the add administrator page or sets the user back to the database settings
		 */
		function _CheckAdministrator($Language) {
			
			// Get external parameters
			$Style = GetPostOrGet('style');
			$Confirmation = GetPostOrGet('confirmation');
			$AdminShowName = GetPostOrGet('admin_showname');
			$AdminName = GetPostOrGet('admin_name');
			$AdminPassword = GetPostOrGet('admin_password');
			$AdminPassword2 = GetPostOrGet('admin_password2');
			
			// Give config variables their default value to prevent PHP Eclipse from warning about a missing variable
			$d_server = 'localhost';
			$d_pre = 'comacms_';
			$d_user = 'root';
			$d_pw = '';
			$d_base = 'comacms';
			
			// Is the database realy Initialized or tries someone to skip the databasesettings?
			if ($Confirmation != 'yes')
				header("Location: install.php?page=5&lang={$Language}&style={$Style}&confirmation=yes");
			
			// Initialize the FormMaker class
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'));
			
			// Add a new form for the admin registration
			$formMaker->AddForm('admin_registration', 'install.php', $this->_Translation->GetTranslation('next'), $this->_Translation->GetTranslation('create_administrator'), 'post');
			
			// Add the hidden inputs
			$formMaker->AddHiddenInput('admin_registration', 'page', '8');
			$formMaker->AddHiddenInput('admin_registration', 'lang', $Language);
			$formMaker->AddHiddenInput('admin_registration', 'style', $Style);
			$formMaker->AddHiddenInput('admin_registration', 'confirmation', 'yes');
			
			// Add the inputs
			$formMaker->AddInput('admin_registration', 'admin_showname', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example'), $AdminShowName);
			$formMaker->AddInput('admin_registration', 'admin_name', 'text', $this->_Translation->GetTranslation('loginname'), $this->_Translation->GetTranslation('with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name'), $AdminName);
			$formMaker->AddInput('admin_registration', 'admin_password', 'password', $this->_Translation->GetTranslation('password'), $this->_Translation->GetTranslation('with_this_password_the_user_can_login_to_restricted_areas'), $AdminPassword);
			$formMaker->AddInput('admin_registration', 'admin_password2', 'password', $this->_Translation->GetTranslation('password_repetition'), $this->_Translation->GetTranslation('it_is_guaranteed_by_a_repetition_that_the_user_did_not_mistype_during_the_input'), $AdminPassword2);
			
			// Add the checks for the formular
			$formMaker->AddCheck('admin_registration', 'admin_showname', 'empty', $this->_Translation->GetTranslation('the_name_must_be_indicated'));
			$formMaker->AddCheck('admin_registration', 'admin_name', 'empty', $this->_Translation->GetTranslation('the_nickname_must_be_indicated'));
			$formMaker->AddCheck('admin_registration', 'admin_password', 'empty', $this->_Translation->GetTranslation('the_password_field_must_not_be_empty'));
			$formMaker->AddCheck('admin_registration', 'admin_password', 'not_same_password_value_as', $this->_Translation->GetTranslation('the_password_and_its_repetition_are_unequal'), 'admin_password2');
			$formMaker->AddCheck('admin_registration', 'admin_password2', 'empty', $this->_Translation->GetTranslation('the_password_field_must_not_be_empty'));
			
			// Check the form and generate errorinformations
			$ok = $formMaker->CheckInputs('admin_registration', true);
			
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
				// Generate template
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, true);
			return $template;
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
		function _ConfigPage($Language) {
			/*
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
			return $template;*/
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
