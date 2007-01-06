<?php
/**
 * @package FormMaker
 * @copyright (C) 2005-2006 The ComaCMS-Team
 * @version FormMaker 0.1
 */
 #----------------------------------------------------------------------
 # file                 : formmaker.class.php
 # created              : 2007-01-01
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 	
 	/**
 	 * Helps the user to generate a formular and checks the inputs if the user wants it to
 	 * @package FormMaker
 	 */
 	class FormMaker {
 		
 		/**
 		 * This array contains all generated formulars in a certain structure that is ready for a ComaLate template
 		 * @var array Generated forms
 		 * @access private
 		 */
 		var $_Forms = array();
 		
 		/**
 		 * The language value for todo. It is needed if anywhere is no information or errorinformation given so that the user can see that there is still no value there
 		 * @var string The TodoValue
 		 * @access private
 		 */
 		var $_TodoValue;
 		
 		/**
 		 * Initializes the FormMaker class
 		 * @access public
 		 * @param string $TodoValue A value for the langstring todo. It is needed if anywhere is no translation set for an information tag
 		 * @return FormMaker A new FormMaker class
 		 */
 		function FormMaker($TodoValue) {
 			$this->_TodoValue = $TodoValue;
 		}
 		
 		/**
 		 * Checks a given value by using a regular expression
 		 * @access private
 		 * @param string $EMail The emailaddress that should be checked
 		 * @return bool Is the value an emailaddress? [true] [false]
 		 */
 		function _IsEMailAddress($EMail){
			return eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,4}$", $EMail);
		}
		
		/**
		 * Checks a given value by using a regular expressionn
		 * @access private
		 * @param string $Icq Contains the value that should be checked
		 * @return bool Is the value an icq number? [true] [false]
		 */
		function _IsIcqNumber($Icq) {
			return eregi("^[0-9]{3}(\-)?[0-9]{3}(\-)?[0-9]{3}$", $Icq);
		}
		
		/**
		 * Checks wether a given value ends witch a specified string
		 * @access private
		 * @param string $String The string that should be tested wether it ends with the string $Search
		 * @param string $Search This is the string witch which the $String should end
		 * @return bool Ends the $String realy with $Search? [true] [false]
		 */
		function _EndsWith($String, $Search) {
			return $Search == substr($String, 0 - (strlen($Search)));
		}
		
		/**
		 * Checks wether a given value starts witch a specified string
		 * @access private
		 * @param string $String The string that should be tested wether it starts with the string $Search
		 * @param string $Search This is the string witch which the $String should start
		 * @return bool Starts the $String realy with $Search? [true] [false]
		 */
		function _StartsWith($String, $Search) {
			return 0 === strpos($String, $Search);
		}
 		
 		/**
 		 * Adds a new formular to the local array
 		 * @access public
 		 * @param string $Name The name of the form
 		 * @param string $Action The action of the form
 		 * @param string $SubmitValue The value of the submit button
 		 * @param string $FieldsetLegend The legend of a fieldset if the form should be displayed in one
 		 * @param string $Method The method of the form. Can be [post] or [get]
 		 * @return void Adds a form
 		 */
 		function AddForm($Name, $Action, $SubmitValue, $FieldsetLegend, $Method = 'post') {
 			
 			if (!empty($Name) && !empty($Action)) {
 				// Set the value of the submitbutton
	 			if (empty($SubmitValue))
	 				$SubmitValue = $this->_TodoValue;
	 			// Set the formmethod either to get or to post
 				if (!empty($Method))
 					$Method = (($Method == 'get') ? 'get' : 'post');
 				
 				// Add the form to the local array
 				$this->_Forms[$Name] = array(
 										'form_name' => $Name,
 										'action' => $Action,
 										'method' => $Method,
 										'submit_value' => $SubmitValue,
 										'fieldset_start' => ((!empty($FieldsetLegend)) ? '<fieldset>' : ''),
 										'fieldset_legend' => ((!empty($FieldsetLegend)) ? '<legend>' . $FieldsetLegend . '</legend>' : ''),
 										'fieldset_end' => ((!empty($FieldsetLegend)) ? '</fieldset>' : ''),
 										'errorinformation_generated' => false,
 										'hidden_inputs' => array(),
 										'inputs' => array());
 			}
 		}
 		
 		/**
 		 * Adds a hidden inputfield to the local array
 		 * @access public
 		 * @param string $FormName The name of the formular to that the hidden input should be added
 		 * @param string $Name The name of the hidden input
 		 * @param string $Value The value of the hidden input
 		 * @return void Adds hidden inputfield
 		 */
 		function AddHiddenInput($FormName, $Name, $Value) {
 			
 			// Check wether the variables are not empty
 			if (!empty($FormName) && array_key_exists($FormName, $this->_Forms) && !empty($Name) && !empty($Value))
 				$this->_Forms[$FormName]['hidden_inputs'][$Name] = array(
 												'name' => $Name,
 												'value' => $Value);
 		}
 		
 		/**
 		 * Adds an input to a formular in the local aray
 		 * @access public
 		 * @param string $FormName The name of the formular the input should be added to
 		 * @param string $Name The name of the input
 		 * @param string $Type The type of the input, possible are [TEXT], [SELECT] and [PASSWORD]
 		 * @param string $NameTranslation The translation that should be shown for that input
 		 * @param string $Information The information about that input that should be shown
 		 * @param string $Value The value of the input if any exist
 		 * @return void Adds an input to a form in the local array
 		 */
 		function AddInput($FormName, $Name, $Type = 'text', $NameTranslation, $Information, $Value = '') {
 			
 			// If a local form can be identified and there is a name for that input specified go on
 			if (!empty($FormName) && array_key_exists($FormName, $this->_Forms) && !empty($Name)){
 				// Initialize variables
	 			if (empty($Information))
	 				$Information = $this->_TodoValue;
	 			if (empty($NameTranslation))
	 				$NameTranslation = $this->_TodoValue;
	 				
	 			// Add the input to the local array
 				$this->_Forms[$FormName]['inputs'][$Name] = array(
							'name' => $Name,
							'start_input' => (($Type == 'password') ? '<input type="{type}"' : (($Type == 'select') ? '<select' : '<input type="{type}"')),
							'end_input' => (($Type == 'password') ? '/>' : (($Type == 'select') ? '>
									<select_entrys:loop><option{selected} value="{select_value}">{display_value}</option>
									</select_entrys>
								</select>' : ' value="{value}" />')),
							'type' => (($Type == 'password') ? 'password' : 'text'),
							'translation' => $NameTranslation,
							'information' => $Information,
							'errorinformation' => array(),
							'value' => (($Type != 'password') ? $Value : ''),
							'password_value' => (($Type == 'password') ? $Value : ''),
							'select_entrys' => array(),
							'checkings' => array());
 			}
 		}
 		
 		/**
 		 * Adds a check to an input in the local array
 		 * @access public
 		 * @param string $FormName The name of the formular in witch the input is
 		 * @param string $InputName The name of the input
 		 * @param string $CheckType The type of the checking
 		 * @param string $ErrorInformation The errorinformation text if the error matches
 		 * @param string $SecondInputName The second input if the check needs a compatison to another field
 		 * @return void Adds a check
 		 */
 		function AddCheck($FormName, $InputName, $CheckType, $ErrorInformation, $SecondInputName = '') {
 			
 			// Initialize the variables
 			if (empty($ErrorInformation))
 				$ErrorInformation = $this->_TodoValue;
 			
 			$this->_Forms[$FormName]['inputs'][$InputName]['checkings'][] = array(
 															'type' => $CheckType,
 															'secondInput' => $SecondInputName,
 															'text' => $ErrorInformation);
 		}
 		
 		/**
 		 * Adds a select entry to an existing select input field in the local array
 		 * @access public
 		 * @param string $FormName The name of the form in the local array
 		 * @param string $InputName The name of the input
 		 * @param bool $Checked Is the entry checked?
 		 * @param string $Value The value of the select entry
 		 * @param string $DisplayValue The value of the entry that should be displayed
 		 * @return void Add a select entry to an input of a form saved in the local array 
 		 */
 		function AddSelectEntry($FormName, $InputName, $Checked, $Value, $DisplayValue) {
 			
 			// If there is no Input specified the FormMaker cannot identify the right one and so returns false, the value and the DisplayValue mustn`t be empty, too
 			if (!empty($FormName) && array_key_exists($FormName, $this->_Forms) && !empty($InputName) && !empty($Value) && !empty($DisplayValue)) {
 				
 				// If $Checked is true add the html code to the variable. so it can be either [selected="selected"] or []
	 			$Checked = (($Checked) ? ' selected="selected"' : '');
	 			
	 			// Add the select entry to the local input
	 			$this->_Forms[$FormName]['inputs'][$InputName]['select_entrys'][] = array(
	 															'selected' => $Checked,
	 															'select_value' => $Value,
	 															'display_value' => $DisplayValue);
 			}
 		}
 		
 		/**
 		 * Checks every input using its checkings
 		 * @access public
 		 * @param string $FormName The name of the form that should be checked
 		 * @param bool $GenerateErrorInformations Shall the function create an output for each error?
 		 * @return bool If there are no errors found it returns true, else false
 		 */
 		function CheckInputs($FormName, $GenerateErrorInformations = false) {
 			
 			// If the function can identify an existing form go on
 			if (!empty($FormName) && array_key_exists($FormName, $this->_Forms)) {
 				
 				// Set the variables of the formular
	 			if ($GenerateErrorInformations)
	 				$this->_Forms[$FormName]['errorinformation_generated'] = true;
	 			
	 			// Hopely there are no errors made... if there are some set $ok then to false
	 			$ok = true;
	 			
	 			// Work through all inputs
	 			foreach($this->_Forms[$FormName]['inputs'] as $input) {
	 				
	 				// Work through all checkings
	 				foreach($input['checkings'] as $check) {
	 					
						// Switch between the checkings and set a new errorinformation if there is an error
						switch ($check['type']) {
							case 'empty':
								// Get the right value to check
								$value = (($input['type'] == 'password') ? $input['password_value'] : $input['value']);
								if (empty($value)) {
									$ok = false;
									if ($GenerateErrorInformations)
										$this->_Forms[$FormName]['inputs'][$input['name']]['errorinformation'][] = array('errortext' => $check['text']);
								} 
								break;
								
							case 'not_email':
							
								// Identify wether value is an emailadress or not 
								if ($this->_IsEMailAddress($input['value'])) {
									$ok = false;
									if ($GenerateErrorInformations)
										$this->_Forms[$FormName]['inputs'][$input['name']]['errorinformation'][] = array('errortext' => $check['text']);
								}
								break;
								
							case 'not_icq':
							
								// Identify wether value is an icq number or not
								if (!$this->_IsIcqNumber($input['value'])) {
									$ok = false;
									if ($GenerateErrorInformations)
										$this->_Forms[$FormName]['inputs'][$input['name']]['errorinformation'][] = array('errortext' => $check['text']);
								}
								break;
							
							case 'not_same_password_value_as':
								// Compare the values of the second input and the input field
								if ($this->_Forms[$FormName]['inputs'][$check['secondInput']]['password_value'] != $input['password_value']) {
									$ok = false;
									if ($GenerateErrorInformations)
										$this->_Forms[$FormName]['inputs'][$input['name']]['errorinformation'][] = array('errortext' => $check['text']);
								}
								break;
						}
					}
	 			}
	 			
	 			// Return wether everything is ok or not
	 			return $ok;
 			}
 		}
 		
 		/**
 		 * Generates the errorinformations for one specific formular
 		 * @access private
 		 * @param string $FormName The name of the form for which errorinformations should be generated
 		 * @return void Generate errorinformations
 		 */
 		function _GenerateErrorInformation($FormName) {
 			
 			// If the function can identify a form then go on
 			if (!empty($FormName) && array_key_exists($FormName, $this->_Forms)) {
 				
 				// Set local variables
				$this->_Forms[$FormName]['errorinformation_generated'] = true;
	 			
	 			// Work through all inputs
	 			foreach($this->_Forms[$FormName]['inputs'] as $input) {
	 				
	 				// Work through all checkings
	 				foreach($input['checkings'] as $check) {
	 					
						// Switch between the checkings and set a new errorinformation if there is an error
						switch ($check['type']) {
							case 'empty':
								$value = (($input['type'] == 'password') ? $input['password_value'] : $input['value']);
								if (empty($value)) {
									$this->_Forms[$FormName]['inputs'][$input['name']]['errorinformation'][] = array('errortext' => $check['text']);
								} 
								break;
								
							case 'not_email':
							
								// Identify wether value is an emailadress or not 
								if (!$this->_IsEMailAddress($input['value']))
									$this->_Forms[$FormName]['inputs'][$input['name']]['errorinformation'][] = array('errortext' => $check['text']);
								break;
								
							case 'not_icq':
							
								// Identify wether value is and icq number or not
								if (!$this->_IsIcqNumber($input['value']))
									$this->_Forms[$FormName]['inputs'][$input['name']]['errorinformation'][] = array('errortext' => $check['text']);
								break;
							
							case 'not_same_password_value_as':
								if ($this->_Forms[$FormName]['inputs'][$check['secondInput']]['password_value'] != $input['password_value'])
									$this->_Forms[$FormName]['inputs'][$input['name']]['errorinformation'][] = array('errortext' => $check['text']);
								break;
						}
					}
	 			}
 			}
 		}
 		
 		/**
 		 * Generates errorinformations for all forms in the local array
 		 * @access private
 		 * @return void Generates errorinformations
 		 */
 		function _GenerateErrorInformations() {
 			
 			// Generate errorinformation for each form
 			foreach ($this->_Forms as $form) {
 				
 				$this->_GenerateErrorInformation($form['form_name']);
 			}
 		}
 		
 		/**
 		 * Generates a template for the inputs and returns it
 		 * @access public
 		 * @param bool $GiveErrorInformation Shall the method give out any errorinformation?
 		 * @return string The ready compiled inputs
 		 */
 		function GenerateOutputs($GiveErrorInformation = false) {
 			
 			// Initialize variables
 			$output = '';
 			
 			// Work through all inputs and generate output
 			foreach($this->_Inputs as $input) {
 				$output .= "\r\n\t\t\t\t\t\t<div class=\"row\">
							<label for=\"{$input['name']}\">
								<strong>{$input['translation']}:</strong>";
				if ($GiveErrorInformation) {
					if (!$this->_ErrorInformationGenerated)
						$this->_GenerateErrorInformations();
					foreach ($input['errorinformation'] as $error => $errorText) {
						$output .= "\r\n\t\t\t\t\t\t\t<span class=\"error\">{$errorText}</span>";
					}
				}
				$output .= "\r\n\t\t\t\t\t\t\t\t<span class=\"info\">{$input['information']}</span>
							</label>
							<input type=\"{$input['type']}\" name=\"{$input['name']}\" id=\"{$input['name']}\" value=\"{$input['value']}\" />
						</div>\r\n";
 			}
 			
 			// Return the template
 			return $output;
 		}
 		
 		/**
 		 * Returns the default template and sets replacements for ComaLate
 		 * @access public
 		 * @param ComaLate &$ComaLate A link to the comalate class to set the replacements for the template
 		 * @param bool $GiveErrorInformation Shall the method give out any errorinformation?
 		 * @return string The default template
 		 */
 		function GenerateTemplate(&$ComaLate, $GiveErrorInformation = false) {
 			
 			$comaLate = &$ComaLate;
 			// If the class shall give out any errorinformation it must be generated
 			if ($GiveErrorInformation)
 				$this->_GenerateErrorInformations();
 			
 			// Set replacements for the template
 			$comaLate->SetReplacement("FORM_MAKER", $this->_Forms);
 			
			// Generate the template
 			$template = "\r\n\t\t\t\t<FORM_MAKER:loop>
 				<form action=\"{action}\" method=\"{method}\">
					{fieldset_start}
						<hidden_inputs:loop><input type=\"hidden\" name=\"{name}\" value=\"{value}\" />\r\n\t\t\t\t\t\t</hidden_inputs>
						{fieldset_legend}
						<inputs:loop>
							<div class=\"row\">
								<label for=\"{name}\">
									<strong>{translation}:</strong>";
				
				// If the method shall give out any errorinformation there must be a template for that
			if ($GiveErrorInformation)
				$template .= "\r\n\t\t\t\t\t\t\t\t\t<errorinformation:loop><span class=\"error\">{errortext}</span></errorinformation>";
				
			$template .= "\r\n\t\t\t\t\t\t\t\t\t<span class=\"info\">{information}</span>
								</label>
								{start_input} name=\"{name}\" id=\"{name}\" {end_input}
							</div>
						</inputs>
						<div class=\"row\">
							<input type=\"submit\" value=\"{submit_value}\" />
						</div>
					{fieldset_end}
				</form>
				</FORM_MAKER>"; 
 			
 			// Return the template
 			return $template;
 		}
 	}
?>