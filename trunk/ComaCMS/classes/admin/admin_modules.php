<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin_system.php
 # created              : 2006-02-18
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
 	require_once __ROOT__ . '/classes/files.php';
 	require_once __ROOT__ . '/classes/opendocument/lifo.php';
 	
 	/**
	 * @package ComaCMS
	 */
 	class Admin_Modules extends Admin{
		

		/** GetPage
		 * 
		 * Returns the requestet page and if it isn't there the default page will be returned
		 * 
		 * @param string action
		 * @access public
		 */
		function GetPage($Action) {
			$out = '';
			switch ($Action) {
				case 'activate':
						$out .= $this->_ActivatePage(); 					
						break;
				case 'deactivate':
						$out .= $this->_DeactivatePage(); 					
						break;
				case 'install':
						$out .= $this->_InstallModule();
						break;
				case 'uninstall':
						$out .= $this->_UninstallModule();
				default:
						$out .= $this->_HomePage();
						break;
			}
			return $out;
		}
		var $_ModuleUninstall = array();
		function _UninstallModule() {
			$moduleName = GetPostOrGet('name');
			$out = '';
			if(file_exists("modules/$moduleName/{$moduleName}_uninstall.xml")) {
				$filename = "modules/$moduleName/{$moduleName}_uninstall.xml";
				$handle = fopen ($filename, "r");
				$UnistallXml = fread ($handle, filesize ($filename));
				fclose ($handle);
				
				$uninstallParser = xml_parser_create();
				xml_set_object($uninstallParser, $this);
				xml_set_element_handler($uninstallParser, '_OpenElementUninstall', '_CloseElementUninstall');
				xml_set_character_data_handler($uninstallParser, '_TextElementUninstall');
				
				if (!xml_parse($uninstallParser, $UnistallXml)) {
					return $out . (sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($uninstallParser)), xml_get_current_line_number($uninstallParser)));
				}
				xml_parser_free($uninstallParser);
				if(count($this->_ModuleUninstall) == 1)
					return $this->_HomePage();
				foreach($this->_ModuleUninstall as $file) {
					if(file_exists("modules/$moduleName/" . $file))
						unlink("modules/$moduleName/" . $file);
				}	
				unlink("modules/$moduleName/{$moduleName}_info.php");
				unlink("modules/$moduleName/{$moduleName}_uninstall.xml");
				return;
			}
			else
				$out .= $this->_Translation->GetTranslation('the_module_isnt_removeable');
			return $out;
		}
		
		function _OpenElementUninstall($Parser, $Name, $Attributes) {
			$this->_XmlLast = $Name;
		}
		
		function _CloseElementUninstall($Parser, $Name) {}
		
		function _TextElementUninstall($Parser, $Data) {
			if($this->_XmlLast == 'FILE')
				$this->_ModuleUninstall[] = $Data;
		}
		
		/**
		 * Activates the page which is transmitted in $GET/POST['name']
		 * @access private
		 * @return srting
		 */
		function _ActivatePage() {
			$moduleName = GetPostOrGet('name');
			// is the module existent?
			if(file_exists("modules/$moduleName/{$moduleName}_info.php")) {
				// get the 'other' modules
				$modulesActivated = unserialize ($this->_Config->Get('modules_activated'));
				// no data was saved...
				if(!is_array($modulesActivated))
					// create the array to make arrayfunctions possible
					$modulesActivated = array();
				// is the module already activated?
				if(!in_array($moduleName, $modulesActivated)) {
					// 'activate' it!
					$modulesActivated[] = $moduleName;
					// Save these changes
					$this->_Config->Save('modules_activated', serialize($modulesActivated));
				}	
				// Go back to the default-view
				return $this->_HomePage();
			}
		}
		
		/**
		 * Dectivates the page which is transmitted in $GET/POST['name']
		 * @access private
		 * @return srting
		 */
		function _DeactivatePage() {
			$moduleName = GetPostOrGet('name');
			// is the module existent?
			if(file_exists("modules/$moduleName/{$moduleName}_info.php")) {
				// get the 'other' modules
				$modulesActivated = unserialize ($this->_Config->Get('modules_activated'));
				// no data was saved...
				if(is_array($modulesActivated)) {
					// is the module activated?
					if(in_array($moduleName, $modulesActivated)) {
						// 'deactivate' it!
						unset($modulesActivated[array_search($moduleName, $modulesActivated)]);
						// Save these changes
						$this->_Config->Save('modules_activated', serialize($modulesActivated));
					}
				}
				// Go back to the default-view
				return $this->_HomePage();
			}
		}
		
		function _OpenElement($Parser, $Name, $Attributes) {
			if($Name == 'MODULE')
				$this->_XmlModule = true;
			if($this->_XmlModule) {
				switch ($Name) {
					case 'FILES':
						$this->_XmlFiles = true;
						break;
					case 'INSTALL':
						$this->_XmlInstall = true;
						break;
					case 'METADATA':
						$this->_XmlMetaData = true;
						break;
				}
			}
			//echo $Name . '<br>';
			//$this->_XmlPath->Add($Name);
			$this->_XmlLast = $Name;
		}
		
		function _CloseElement($Parser, $Name) {
			if($this->_XmlModule) {
				switch ($Name) {
					case 'FILES':
						$this->_XmlFiles = false;
						break;
					case 'INSTALL':
						$this->_XmlInstall = false;
						break;
					case 'METADATA':
						$this->_XmlMetaData = false;
						break;
				}
			}
		}
		
		function _TextElement($Parser, $Data) {
			$Data = str_replace("\n",'', $Data);
			$Data = str_replace("\t",'', $Data);
			$Data = str_replace("  ",'', $Data); 
			if(strlen($Data) == 0 || $Data == ' ')
				return;
			switch ($this->_XmlLast) {

				case 'NAME':
				case 'VERSION':
				case 'AUTHOR':
					
					if($this->_XmlMetaData) {
						if($this->_XmlLast == 'NAME')
							$this->_ModuleName = $Data;
						else if($this->_XmlLast == 'VERSION')
							$this->_ModuleVersion = $Data;
						else if($this->_XmlLast == 'AUTHOR')
							$this->_ModuleAuthor = $Data;
					}				
					break;
				case 'FILE':
					if($this->_XmlFiles)
						$this->_ModuleFiles[] = $Data;
					break;
				case 'SQL':
					if($this->_XmlInstall)
						$this->_ModuleInstall[] = $Data;
					break;
			}
			//echo $this->_XmlLast . ': ' . $Data . "<br>\n";
		}
		var $_ModuleName = '';
		var $_ModuleVersion = '';
		var $_ModuleAuthor = '';
		var $_ModuleFiles = array();
		var $_ModuleInstall = array();
		var $_XmlLast = '';
		var $_XmlModule = false;
		var $_XmlFiles = false;
		var $_XmlInstall = false;
		var $_XmlMetaData = false;
		
		function _InstallModule() {
			$files = new Files($this->_SqlConnection, $this->_User);
			$tempDir = './data/tmp/';
			//$moduleDir = './data/tmp/testmodule/';
			$moduleDir = './modules/';
			if(!array_key_exists('uploadModule', $_FILES) || substr($_FILES['uploadModule']['name'],-4) != '.zip') {
				$out = $this->_Translation->GetTranslation('the_uploaded_file_isnt_a_comacms_module');
				return $out . $this->_HomePage();
			}
			$out = '';
			$newFile = $files->UploadFile('uploadModule', $tempDir, false, 0777);
			$out .= $newFile . "<br/>";
			
			$zip = zip_open(realpath($newFile));
			if(!$zip) {
				unlink($newFile);
				return $out . $this->_Translation->GetTranslation('the_uploaded_file_isnt_a_comacms_module');
			}
			$moduleXml = '';
			while(($zipEntry = zip_read($zip)) && $moduleXml == '') {
				$zipEntryName = zip_entry_name($zipEntry);
				if($zipEntryName == 'module.xml') {
						if(zip_entry_open($zip, $zipEntry, 'r')){
							$moduleXml = zip_entry_read($zipEntry, zip_entry_filesize($zipEntry));
							zip_entry_close($zipEntry);
						}
				}
			}
			zip_close($zip);
			//$out .= nl2br(htmlspecialchars($moduleXml)) . "<br/>";
			$moduleParser = xml_parser_create();
			xml_set_object($moduleParser, $this);
			xml_set_element_handler($moduleParser, '_OpenElement', '_CloseElement');
			xml_set_character_data_handler($moduleParser, '_TextElement');
			$this->_XmlPath = new LiFo();
			if (!xml_parse($moduleParser, $moduleXml)) {
				unlink($newFile);
	       		return $out . (sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($moduleParser)), xml_get_current_line_number($moduleParser)));
			}
			xml_parser_free($moduleParser);
			
			if(count($this->_ModuleFiles) > 0) {
				$destination = $moduleDir . $this->_ModuleName . '/';
				$out .= "Destination: $destination<br/>";
				if(!file_exists($destination))
					mkdir($destination, 0777);
				$zip = zip_open(realpath($newFile));
				if(!$zip) {
					unlink($newFile);
					return $out . $this->_Translation->GetTranslation('the_uploaded_file_isnt_a_comacms_module');
				}
				$moduleXml = '';
				$files = array();
				while(($zipEntry = zip_read($zip)) && $moduleXml == '') {
					$zipEntryName = zip_entry_name($zipEntry);
					if(in_array($zipEntryName, $this->_ModuleFiles)) {
						$buf = zip_entry_read($zipEntry, zip_entry_filesize($zipEntry));
						zip_entry_close($zipEntry);
						$writer = fopen($destination . $zipEntryName, 'w');
						$files[] = $zipEntryName;
	           			fwrite($writer, $buf);
	           			fclose($writer);
	           			
					}
					else if(in_array($zipEntryName, $this->_ModuleInstall)) {
						$buf = zip_entry_read($zipEntry, zip_entry_filesize($zipEntry));
						zip_entry_close($zipEntry);
						$this->_SqlConnection->SqlExecMultiple($buf);						
					}
				}
				zip_close($zip);
				$info = '<?php
	$module[\'name\'] = \'' . $this->_ModuleName .'\';
	$module[\'version\'] = \'' . $this->_ModuleVersion . '\';
	$module[\'author\'] = \'' . $this->_ModuleAuthor . '\';
?>';
				$writer = fopen($destination . $this->_ModuleName . '_info.php', 'w');
				fwrite($writer, $info);
	           	fclose($writer);
	           	$uninstall = '<files>' . "\n";
	           	foreach($files as $file)
	           		$uninstall .= '<file>' . $file . '</file>' . "\n";
	           	$uninstall .= '</files>';
	           	$writer = fopen($destination . $this->_ModuleName . '_uninstall.xml', 'w');
				fwrite($writer, $uninstall);
	           	fclose($writer);
			
				
			}
			
			
			unlink($newFile);
			return $this->_HomePage();
		}
		
		/**
		 * Returns a table with all available modules
		 * @access private
		 * @return srting
		 */
		function _HomePage() {
			// load the name of all already activated modules
			$modulesActivated = unserialize ($this->_Config->Get('modules_activated'));
			// if the data was empty
			if(!is_array($modulesActivated))
				// create the array to prevent bugs caused by 'var-is-not-an-array'-exceptions
				$modulesActivated = array();


			$modules = array();
			$modulesFiles = dir(__ROOT__ . '/modules/');
			
			// Get all directories in the modules directory
			while($moduleDirectory = $modulesFiles->read()) {
				// Check if it is a directory and nothing else
				// Check also if could be a real module directory
				if($moduleDirectory != '.' && $moduleDirectory != '..' && file_exists("./modules/$moduleDirectory/{$moduleDirectory}_info.php")) {
					$module =  array();
					// load the info-file for the module
					include("./modules/$moduleDirectory/{$moduleDirectory}_info.php");
					
					// try to get the 'well-formed' name of the module	
					// if it isn't possible display the internal name of the module
					$moduleName =  (array_key_exists('name', $module)) ? $module['name'] : $moduleDirectory;
					
					// try to get the version-information
					// if there is no info, a 'unknown' will be displayed
					$moduleVersion =  (array_key_exists('version', $module)) ? 'v' . $module['version'] : $this->_Translation->GetTranslation('unknown');

					// the module isn't activated
					$moduleAction = 'activate';
					$moduleActivated = $this->_Translation->GetTranslation('not_activated');
					$actionImage = 'add';
					
					// if the module is already activated
					if(in_array($moduleDirectory, $modulesActivated)) {
						$moduleAction = 'deactivate';
						$moduleActivated = $this->_Translation->GetTranslation('activated');
						$actionImage = 'del';
					}
						
					$modules[] = array('MODULE_NAME' => $moduleName,
									'MODULE_VERSION' => $moduleVersion,
									'MODULE_ACTIVATED' => $moduleActivated,
									'MODULE_ACTION' => $moduleAction,
									'ACTION_IMAGE' => $actionImage,
									'MODULE_DIRECTORY' => $moduleDirectory,
									'MODULE_LANG_ACTION' => sprintf($this->_Translation->GetTranslation($moduleAction . '_module_%modulename%'), $moduleName));
				}
			}
			$modulesFiles->close();
			$this->_ComaLate->SetReplacement('MODULE_MANAGER_TITLE', $this->_Translation->GetTranslation('module_manager'));
			$this->_ComaLate->SetReplacement('MODULE_TITLE_NAME', $this->_Translation->GetTranslation('module_name'));
			$this->_ComaLate->SetReplacement('MODULE_TITLE_VERSION', $this->_Translation->GetTranslation('version'));
			$this->_ComaLate->SetReplacement('MODULE_TITLE_ACTIVATED', $this->_Translation->GetTranslation('activated'));
			$this->_ComaLate->SetReplacement('MODULE_TITLE_ACTIONS', $this->_Translation->GetTranslation('actions'));
			$this->_ComaLate->SetReplacement('LANG_INSTALL_NEW_MODULE', $this->_Translation->GetTranslation('install_new_module'));
			$this->_ComaLate->SetReplacement('LANG_FILE', $this->_Translation->GetTranslation('file'));
			$this->_ComaLate->SetReplacement('LANG_FILE_INFO', $this->_Translation->GetTranslation('select_here_a_comacms_module_setup_file_(*.zip)'));
			$this->_ComaLate->SetReplacement('LANG_INSTALL', $this->_Translation->GetTranslation('install'));
			$this->_ComaLate->SetReplacement('LANG_UNINSTALL_MODULE', $this->_Translation->GetTranslation('uninstall_module'));						
			$this->_ComaLate->SetReplacement('MODULES', $modules);	
			
			$template = '<h2>{MODULE_MANAGER_TITLE}</h2>
					<fieldset>
					<legend>{LANG_INSTALL_NEW_MODULE}</legend>
					<form enctype="multipart/form-data" action="admin.php" method="post">
						<input type="hidden" name="MAX_FILE_SIZE" value="1600000" />
						<input type="hidden" name="action" value="install" />
						<input type="hidden" name="page" value="modules" />
					<div class="row">
						<label>
							<strong>{LANG_FILE}:</strong>
							<span class="info">{LANG_FILE_INFO}</span>
						</label>
						<input name="uploadModule" type="file" />
					</div>
					<div class="row">
						<input class="button" type="submit" value="{LANG_INSTALL}" />
					</div>
					</form>
					</fieldset>
							<table class="full_width">
						<tr>
							<th>{MODULE_TITLE_NAME}</th>
							<th>{MODULE_TITLE_VERSION}</th>
							<th>{MODULE_TITLE_ACTIVATED}</th>
							<th class="actions">{MODULE_TITLE_ACTIONS}</th>
						</tr>
						<MODULES:loop>
						<tr>
							<td>{MODULE_NAME}</td>
							<td>{MODULE_VERSION}</td>
							<td>{MODULE_ACTIVATED}</td>
							<td>
								<a href="admin.php?page=modules&amp;action={MODULE_ACTION}&amp;name={MODULE_DIRECTORY}" title="{MODULE_LANG_ACTION}"><img alt="{MODULE_LANG_ACTION}" src="img/{ACTION_IMAGE}.png" /></a>
								<a href="admin.php?page=modules&amp;action=uninstall&amp;name={MODULE_DIRECTORY}" title="{LANG_UNINSTALL_MODULE}"><img alt="{LANG_UNINSTALL_MODULE}" src="img/delete.png" />
							</td>
						</tr>
						</MODULES>
					</table>';
			return $template;
		}
 	}
?>