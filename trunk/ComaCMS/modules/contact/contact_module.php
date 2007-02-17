<?php
/**
 * @package ComaCMS
 * @subpackage Contact
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : contact_module.php
 # created              : 2006-12-03
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
	require_once __ROOT__ . '/classes/module.php';
	require_once __ROOT__ . '/lib/formmaker/formmaker.class.php';
	
	/**
	 * @package ComaCMS
	 * @subpackage News 
	 */
	class Module_Contact extends Module {
 		
 		function UseModule($Identifer, $Parameters) {
 			$this->_Translation->AddSources(__ROOT__ . '/modules/contact/lang/');
 			// default parameter
 			$mailTo = $this->_Config->Get('contact_mail_to','');
 			
 			$Parameters = explode('&', $Parameters);
 			foreach($Parameters as $parameter){
 				$parameter = explode('=', $parameter, 2);
 				if(!isset($parameter[1]))
 					$parameter[1] = true;
 				$$parameter[0] = $parameter[1];
 			}
 			if(!isEMailAddress($mailTo))
 				return $this->_Translation->GetTranslation('no_valid_reciever_email_address');
 				$output = $this->_sendMail($mailTo);
 			return $output;
 		}
 		
 		/**
 		 * this function shows the email-form
 		 */
 		function _mailForm($MailFromName, $MailFrom, $Message, $Check = false) {
 			
			
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('contact_formular', '#', $this->_Translation->GetTranslation('send'), $this->_Translation->GetTranslation('contact'), 'post');
			
			$formMaker->AddHiddenInput('contact_formular', 'page', GetPostOrGet('page'));
			$formMaker->AddHiddenInput('contact_formular', 'action', 'send');
			
			$formMaker->AddInput('contact_formular', 'contact_mail_from_name', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('please_enter_your_name_here') . ' '. $this->_Translation->GetTranslation('(required)'), $MailFromName);
			if($Check)
				$formMaker->AddCheck('contact_formular', 'contact_mail_from_name', 'empty', $this->_Translation->GetTranslation('the_name_must_be_indicated'));
			$formMaker->AddInput('contact_formular', 'contact_mail_from', 'text', $this->_Translation->GetTranslation('email'), $this->_Translation->GetTranslation('please_enter_your_email_here') . ' '. $this->_Translation->GetTranslation('(required)'), $MailFrom);
			if($Check)
				$formMaker->AddCheck('contact_formular', 'contact_mail_from', 'empty', $this->_Translation->GetTranslation('the_nickname_must_be_indicated'));			
			if($Check && $MailFrom != '')
				$formMaker->AddCheck('contact_formular', 'contact_mail_from', 'not_email', $this->_Translation->GetTranslation('this_is_an_invalid_email_address'));
			$formMaker->AddInput('contact_formular', 'contact_message', 'textarea', $this->_Translation->GetTranslation('message'), $this->_Translation->GetTranslation('please_enter_here_the_message_you_want_do_send') . ' '. $this->_Translation->GetTranslation('(required)'), $Message);
			if($Check)
				$formMaker->AddCheck('contact_formular', 'contact_message', 'empty', $this->_Translation->GetTranslation('please_enter_your_message'));

			if($formMaker->CheckInputs('contact_formular', true) && $Check)
				return '';
		
			$template = "\r\n\t\t\t\t</p>" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, $Check) . '<p>';
			
			return $template;
 		}
 		/**
 		 * @param string MailTo The reciever of the mail
 		 */
 		function _sendMail($MailTo) {
 			$mailFromName = GetPostOrGet('contact_mail_from_name');
 			$mailFrom = GetPostOrGet('contact_mail_from');
 			$message = GetPostOrGet('contact_message');
 			$action = GetPostOrGet('action');
 			$mailError = '';
 			// no email
 			if($mailFrom == '')
 				$mailError = $this->_Translation->GetTranslation('the_email_address_must_be_indicated');
 			// invalid email
 			else if(!isEMailAddress($mailFrom))
 				$mailError = $this->_Translation->GetTranslation('this_is_a_invalid_email_address');
			$check = false;
			if($action != '')
				$check = true;
 			$template = $this->_mailForm($mailFromName, $mailFrom, $message, $check);
 			if($template == ''){
				// who is the 'real' sender
 				$from = $this->_Config->Get('administrator_emailaddress', 'administrator@comacms');	
 				// the information about the sender
 				$fromInfo = $mailFromName . ' <' . $mailFrom . '>';
 				// the title of the message
 				$title = sprintf($this->_Translation->GetTranslation('new_email_from_a_visitor_of_%homepage%'), $this->_Config->Get('pagename', 'homepage'));
				//generate the message
				$messageContent = sprintf($this->_Translation->GetTranslation('contact_message_%from%_%message'), $fromInfo, $message);
				
				$output = "</p><fieldset><legend>" . $this->_Translation->GetTranslation('contact') . "</legend>";
				// try to send the email
				if(sendmail($MailTo, $from, $title, $messageContent))
 					$output .= $this->_Translation->GetTranslation('your_message_was_sent_succesdfully');
 				else // TODO: try to give some hints what to do 
 					$output .= $this->_Translation->GetTranslation('an_error_occured_on_sending_this_message');
 				$output .= '</fieldset><p>';
 				return $output;
 			}
 			else // otherwise show the mailform to make it possible to correct the input
 				return $template;
 			
 				
 		}
	}
?>