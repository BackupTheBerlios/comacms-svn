<?php
/**
 * @package ComaCMS
 * @subpackage Contact
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : contact_settings.php
 # created              : 2006-12-03
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
	global $translation;
	Preferences::SetSetting('contact_mail_to', 'Standard Kontakt Email Empf&auml;nger', 'Das ist die Emailadresse, an die alle Emails aus dem Kontaktformular gehen f&uuml;r die keine Empf&auml;ngeradresse festgelegt ist.', $translation->GetTranslation('email'), '', 'email');
?>