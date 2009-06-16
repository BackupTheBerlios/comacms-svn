<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2009 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : ldap.php
 # created              : 2009-06-09
 # copyright            : (C) 2005-2009 The ComaCMS-Team
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
	class Ldap {
		
		/**
		 * This is the address of the ldapserver
		 * 
		 * @access public
		 * @var string Serveraddress
		 */
		var $Server = 'localhost';
		
		/**
		 * This is the port of the ldapserver
		 * 
		 * This is the port to access the ldapserver. Standartport for ldap is 389, 
		 * using ssl the standart port would be 636.
		 * @access public
		 * @var integer The serverport
		 */
		var $Port = 389;
		
		/**
		 * This is the protokoll version of the server.
		 * 
		 * The protokoll is important! Standart protokoll for php is not 3 but for most of the servers it is!
		 * So it would come to misunderstanding at some point without setting to the right protokollversion!
		 * @access public
		 * @var integer ProtokollVersion
		 */
		var $ProtokollVersion = 3;
		
		/**
		 * This is a user-base-dn to search the ldap server
		 * 
		 * If the server needs an authenticated user to be searched for objects,
		 * this should be his exact id on the ldap server including base-dn;
		 * @access public
		 * @var string The user-base-dn
		 */
		var $UserName = '';
		
		/**
		 * This is the password for the ldap user
		 * 
		 * This is the password for the standart user of the ldap, to allow the ComaCMS System to search
		 * for existing users and groups
		 * @access public
		 * @var string The password for the ldap user
		 */
		var $Password = '';
		
		/**
		 * Is the current user an admin user?
		 * 
		 * If the user is admin enable administrative funktions of the class
		 * @access public
		 * @var boolean Enable Admin functions
		 */
		var $IsAdmin = false;
		
		/**
		 * Counts the querys to the ldap server
		 * 
		 * Normaly only the number of querys is counted but for debugging reasons it can
		 * contain the ldap querys as a string, too.
		 * @access public
		 * @var string|int Querycount
		 */
		var $QueryCount = '';
		
		/**
		 * This is the connectionresource to the ldapserver
		 *
		 * @access private
		 * @var resource The connection socket to the ldapserver 
		 */
		var $_Connection;
		
		/**
		 * This is the binding of the user to the ldap connection resource
		 * 
		 * @access private
		 * @var resource The binding of the ldap user to the connection resource
		 */
		var $_UserBinding;
		
		/**
		 * Initializes a new ldap class
		 * 
		 * @access public
		 * @param string $Server This is the hostname of the ldapserver
		 * @param integer $Port This is the port on which the ldapserver is listening on
		 * @param integer $ProtokollVersion This is the version of the ldap protokoll to communicate in
		 * @param string $UserName This is the uid-basename-dn of the user to search the ldapserver
		 * @param string $Password This is the password of the user at the ldap server
		 * @return void A new ldap connection class
		 */
		function Ldap($Server, $Port, $ProtokollVersion = 3, $UserName = '', $Password = '') {
			
			// Initialize the local variables
			$this->Server = $Server;
			$this->Port = $Port;
			$this->ProtokollVersion = $ProtokollVersion;
			$this->UserName = $UserName;
			$this->Password = $Password;
		}
		
		/**
		 * Connect to the ldap server and try to bind the user
		 * 
		 * Builds a new socket to an ldapserver and after that binds the ldap user
		 * to the local resource for further searches in the ldap database
		 * @access public
		 * @return void
		 */
		function Connect() {
			
			// Enable error reporting for all errors
			error_reporting(E_ALL);
			$this->_Connection = ldap_connect($this->Server, $this->Port) or die('LDAP Fehler: ' . ldap_error($this->_Connection));
			
			// Set any protokoll options
			ldap_set_option($this->_Connection, LDAP_OPT_PROTOKOL_OPTION, $this->ProtokollVersion);
			
			// Try to bind the local user to the serverconnection
			if ($this->UserName != '' && $this->Password != '') {
				$this->_UserBinding = ldap_bind($this->_Connection, $this->UserName, $this->Password) or die('LDAP Fehler: ' . ldap_error($this->_Connection));
			}
			else {
				
				// Try anonymous login
				$this->_UserBinding = ldap_bind($this->_Connection);
			}
		}
		
		/**
		 * Relogin to the ldapserver using an admin account
		 * 
		 * Gives the user the option to relogin to the ldapserver using an admin account.
		 * If the current login is already an admin user the admin functions just get enabled.
		 * @access public
		 * @param string $UserName The name of the admin user
		 * @param string $Password The password of the admin user
		 * @return bool Login correct?
		 */
		function AdminLogin($UserName = '', $Password = '') {
			
			if ($UserName == $this->UserName && $Password == $this->Password) {
				
				// If the admin user is the same user as the one currently logged in just enable admin functions
				$this->IsAdmin = true;
				return true;
			}
			else if ($UserName != '' && $Password != '') {
				
				// We got an admin user... Try to logg him on
				$binding = ldap_bind($this->_Connection, $UserName, $Password) or die('LDAP Fehler: '. ldap_error($this->_Connection));
				$this->IsAdmin = true;
				return true;
			}
			else {
				return false;
			}
		}
		
		/**
		 * Searches the ldapserver for a specific search string
		 * 
		 * Searched the basedn part of the ldap server for a specific search string. Returned get entries
		 * of the subtrees of basedn.
		 * @access public
		 * @param string $BaseDn This is the subtree of the ldapserver to be searched
		 * @param string $Filter This is the ldap filter to find entries on the server
		 * @return ressource The resource of the search return
		 */
		function LdapSearch($BaseDn, $Filter) {
			
			$this->QueryCount++;
			$result = ldap_search($this->_Connection, $BaseDn, $Filter) or die('LDAP Error: ' . ldap_error($this->_Connection));
		}
	}

?>
