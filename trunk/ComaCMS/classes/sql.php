<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : sql.php
 # created              : 2006-01-22
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
 	class Sql {
 		/**
 		 * The username of the MySQL-account
 		 * @access public
 		 * @var string 
 		 */
 		var $UserName = '';
 		
 		/**
 		 * The passwort for the MySQL-account
 		 * @access public
 		 * @var string
 		 */
 		var $UserPassword = '';
 		
 		/**
 		 * The server-address, on which the MySQL-databases are
 		 * @access public
 		 * @var string
 		 */
 		var $Server = '';
 		
 		/**
 		 * The name of the database in the MySQL-server
 		 * @access public
 		 * @var string
 		 */
 		var $Database = '';
 		
 		/**
 		 * The number of all done MySQL-queries in this session
 		 * @access public
 		 * @var integer 
 		 */
 		var $QueriesCount = 0;
 		/**
 		 * @access private
 		 * @var resource
 		 */
 		var $_Connection;
 		
 		/**
 		 * @access public
 		 * @param string UserName
 		 * @param string UserPassword
 		 * @param string Server
 		 */
 		function Sql($UserName, $UserPassword, $Server = 'localhost') {
 			// Set vars
 			$this->UserName = $UserName;
 			$this->UserPassword = $UserPassword;
 			$this->Server = $Server;
 		}
 		
 		/**
 		 * @access public
 		 * @param string Database The name of the database on the MySQL-server
 		 * @return void
 		 */
 		function Connect($Database) {
 			error_reporting(E_ALL);
 			$this->_Connection = mysql_pconnect($this->Server, $this->UserName, $this->UserPassword)
			or die('Mysql-error:' . mysql_error());
			$this->Database = $Database; 
			mysql_select_db($Database, $this->_Connection)
			or die('Mysql-error:' . mysql_error()); 		
 		}
 		
 		/**
 		 * Sends a MySQL-Query to the Server and incements a counter which counts the MySQL-queries 
 		 * @access public
 		 * @param string Query A MySQL-Query 
 		 * @return resource
 		 */
 		function SqlQuery($Query) {
 			global $sqlConnection;
 			$sqlConnection->QueriesCount++;
 			/* helpful to find unnecessary SQL-queries(replace it only with the "++"):
			 * 
			 * .= "\r\n$Query\r\n" . print_r(debug_backtrace(),true);
			 */	
 			$result = mysql_query ($Query, $this->_Connection);
			if(!$result)
				echo 'Error: ' . $Query . ':' . mysql_error () . ';';
			return $result;
 		}
 		
 		/**
 		 * Executes a Sql-File
 		 * @access public
 		 * @param srting Filename
 		 * @return boolean
 		 */
 		function SqlExecFile($Filename) {
 			if(!file_exists($Filename))
 				return false;
 			$fileHandle = fopen($Filename, "r");
			// Read the whole file
	  		$queries = fread($fileHandle, filesize($Filename));
		  	// Close the handle
	  		fclose($fileHandle);
	  		return $this->SqlExecMultipe($queries);
	  		
 		}
 		
 		/**
 		 * Executes many sql-queries at once
 		 * @access public
 		 * @param srting Queries
 		 * @return boolean
 		 */
 		function SqlExecMultiple($Queries) {
 			$Queries = explode(';', $Queries);
			foreach($Queries as $query){
				if($query != ''){
					if(!$this->SqlQuery($query))
						return false;
				}
			}
			return true;
 		}
 	}
?>