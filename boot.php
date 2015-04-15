<?php
// Name of the database server Ex. localhost
define('HOST', 'SERVERNAME'); 
// Name of the database  Ex.DB_IOS 
define('DATABASE', 'DATABASE_NAME');
// Database user name Ex. ROOT
define('DB_USER', 'USER_NAME');
// Passoward for Database Ex. ROOT 
define('DB_PASSWORD', 'PASSWORD');

// Connecting to the database
mysql_connect(HOST,DB_USER,DB_PASSWORD);
mysql_select_db(DATABASE);

/* 
**Details of the Apple itunes connect account
** Username is your login name for the iTunes account
** Password is the passkey for login for the iTunes account
** vndnumber is the vendeor number on the Apple account, to get go to the reports page it will be shown in the dropdown
*/
$accounts = array(
					array('username' => 'abc@pqr.com',
						'password' => 'XXXXXX',
						'vndnumber' => '012365456',
						),
						
						array('username' => 'mnp@pqr.com',
						'password' => 'XXXXXX',
						'vndnumber' => '963852741',
						),
				);
				
