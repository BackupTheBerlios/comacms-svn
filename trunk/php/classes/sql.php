<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: sql.php					#
 # created		: 2006-01-22					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
 
	/**
	 * @package ComaCMS
	 */
 	class Sql {
 		var $UserName = '';
 		var $UserPassword = '';
 		var $Server = '';
 		var $Database = '';
 		var $QueriesCount = 0;

 		/**
 		 * @access private
 		 */
 		var $_Connection;
 		
 		function Sql($UserName, $UserPassword, $Server = 'localhost') {
 			// Set vars
 			$this->UserName = $UserName;
 			$this->UserPassword = $UserPassword;
 			$this->Server = $Server;
 		}
 		
 		function Connect($Database) {
 			error_reporting(E_ALL);
 			$this->_Connection = mysql_pconnect($this->Server, $this->UserName, $this->UserPassword)
			or die('Mysql-error:' . mysql_error());
			$this->Database = $Database; 
			mysql_select_db($Database, $this->_Connection)
			or die('Mysql-error:' . mysql_error()); 		
 		}
 		
 		function SqlQuery($Query) {
 			global $sqlConnection;
 			$sqlConnection->QueriesCount .= "\r\n$Query\r\n";
 			/* helpful to find unnecessary SQL-queries(replace it only with the "++"):
			 * 
			 * .= "\r\n$Query\r\n" . print_r(debug_backtrace(),true);
			 */	
 			$result = mysql_query ($Query, $this->_Connection);
			if(!$result)
				echo 'Error: ' . $Query . ':' . mysql_error () . ';';
			return $result;
 		}
 	}
?>