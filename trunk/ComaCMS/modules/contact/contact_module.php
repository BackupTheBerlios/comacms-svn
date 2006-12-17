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
	require_once __ROOT__ . '/functions.php';
	/**
	 * @package ComaCMS
	 * @subpackage News 
	 */
	class Module_Contact extends Module{
		
		function Module_Contact(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib) {
 			$this->_SqlConnection = &$SqlConnection;
 			$this->_User = &$User;
 			$this->_Config = &$Config;
 			$this->_Lang = &$Lang;
 			$this->_ComaLate = &$ComaLate;
 			$this->_ComaLib = &$ComaLib;
 		}
 		
 		function UseModule($Identifer, $Parameters) {
 			
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
 				return $this->_Lang['no_valid_reciever_email_address'];
 			$output = '';
 			$action = GetPostOrGet('action');
 			// if the mail should be sended
 			if($action == 'send')
 				$output .= $this->_sendMail($mailTo);
 			else
 				$output .= $this->_mailForm();
 			return $output;
 		}
 		
 		/**
 		 * this function shows the email-form
 		 */
 		function _mailForm($MailFromName = '', $MailFrom = '', $Message = '', $MailError = '', $NameError = '', $MessageError = '') {
 			$output = "</p><form action=\"#\" method=\"post\">
 						<input type=\"hidden\" name=\"page\" value=\"" . GetPostOrGet('page') . "\" />
 						<input type=\"hidden\" name=\"action\" value=\"send\" />
 					<fieldset><legend>{$this->_Lang['contact']}</legend>";
 			$output .= "<div class=\"row\">
 							<label class=\"row\" for=\"contact_mail_from_name\"><strong>{$this->_Lang['name']}:</strong>
 									" . (($NameError != '') ? '<span class="info error">'.$NameError.'</span>' : '') . "
 								</label>
 								<input id=\"contact_mail_from_name\" name=\"contact_mail_from_name\" value=\"{$MailFromName}\" type=\"text\" /></div>\n";
 			$output .= "<div class=\"row\">
 							<label class=\"row\" for=\"contact_mail_from\"><strong>{$this->_Lang['email']}:</strong>
 									" . (($MailError != '') ? '<span class="info error">'.$MailError.'</span>' : '') . "
 								</label>
 								<input id=\"contact_mail_from\" name=\"contact_mail_from\" type=\"text\" value=\"{$MailFrom}\" /></div>\n";
 			$output .= "<div class=\"row\">
 							<label class=\"row\" for=\"contact_message\"><strong>{$this->_Lang['message']}:</strong>
 									" . (($MessageError != '') ? '<span class="info error">'.$MessageError.'</span>' : '') . "
 								</label>
 								<textarea id=\"contact_message\" name=\"contact_message\">{$Message}</textarea></div>\n";
 			$output .= "<div class=\"row\"><input type=\"submit\" class=\"button\" value=\"{$this->_Lang['send']}\"/></div>";
			$output .= "</fieldset></form><p>";
			return $output;
 		}
 		/**
 		 * @param string MailTo The reciever of the mail
 		 */
 		function _sendMail($MailTo) {
 			$mailFromName = GetPostOrGet('contact_mail_from_name');
 			$mailFrom = GetPostOrGet('contact_mail_from');
 			$message = GetPostOrGet('contact_message');
 			$mailError = '';
 			// no email
 			if($mailFrom == '')
 				$mailError = $this->_Lang['the_email_address_must_be_indicated'];
 			// invalid email
 			else if(!isEMailAddress($mailFrom))
 				$mailError = $this->_Lang['this_is_a_invalid_email_address'];

 			$nameError = '';
 			// empty name
 			if($mailFromName == '')
 				$nameError = $this->_Lang['the_name_must_be_indicated'];
 			$messageError = '';
 			// empty message
 			if($message == '')
 				$messageError = $this->_Lang['please_enter_your_message'];
 			// if no errors occured
 			if($nameError == '' && $mailError == '' && $messageError == ''){
				// who is the 'real' sender
 				$from = $this->_Config->Get('administrator_emailaddress', 'administrator@comacms');	
 				// the information about the sender
 				$fromInfo = $mailFromName . ' <' . $mailFrom . '>';
 				// the title of the message
 				$title = sprintf($this->_Lang['new_email_from_a_visitor_of_%homepage%'], $this->_Config->Get('pagename', 'homepage'));
				//generate the message
				$messageContent = sprintf($this->_Lang['contact_message_%from%_%message'], $fromInfo, $message);
				
				$output = "</p><fieldset><legend>{$this->_Lang['contact']}</legend>";
				// try to send the email
				if(sendmail($MailTo, $from, $title, $messageContent))
 					$output .= $this->_Lang['your_message_was_sent_succesdfully'];
 				else // TODO: try to give some hints what to do 
 					$output .= $this->_Lang['an_error_occured_on_sending_this_message'];
 				$output .= '</fieldset><p>';
 				return $output;
 			}
 			else // otherwise show the mailform to make it possible to correct the input
 				return $this->_mailForm($mailFromName, $mailFrom, $message, $mailError, $nameError, $messageError);
 			
 				
 		}
	}
?>