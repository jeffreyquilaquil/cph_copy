<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'default';
$active_record = TRUE;

$db['default']['hostname'] = 'sql01.tatepublishing.net';
$db['default']['username'] = 'cph_app01';
$db['default']['password'] = '40n6LtpCmqT7';

$db['default']['database'] = 'careerph_prod';
$db['default']['dbdriver'] = 'mysqli';

$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = FALSE;
$db['default']['db_debug'] = TRUE;
$db['default']['cache_on'] = TRUE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;

$db['defaultdev']['hostname'] = 'localhost';
$db['defaultdev']['username'] = 'root';
$db['defaultdev']['password'] = '945DmB2EazkE';
$db['defaultdev']['database'] = 'tatecareerph_db';
$db['defaultdev']['dbdriver'] = 'mysqli';
$db['defaultdev']['dbprefix'] = '';
$db['defaultdev']['pconnect'] = FALSE;
$db['defaultdev']['db_debug'] = TRUE;
$db['defaultdev']['cache_on'] = TRUE;
$db['defaultdev']['cachedir'] = '';
$db['defaultdev']['char_set'] = 'utf8';
$db['defaultdev']['dbcollat'] = 'utf8_general_ci';
$db['defaultdev']['swap_pre'] = '';
$db['defaultdev']['autoinit'] = TRUE;
$db['defaultdev']['stricton'] = FALSE;


$db['projectTracker']['hostname'] = 'ptracker.clhfapw0bgm7.us-east-1.rds.amazonaws.com';
$db['projectTracker']['username'] = 'pt';
$db['projectTracker']['password'] = 'WD4000FFYZ';
$db['projectTracker']['database'] = 'projectTracker';
$db['projectTracker']['dbdriver'] = 'mysqli';
$db['projectTracker']['dbprefix'] = '';
$db['projectTracker']['pconnect'] = FALSE;
$db['projectTracker']['db_debug'] = TRUE;
$db['projectTracker']['cache_on'] = TRUE;
$db['projectTracker']['cachedir'] = '';
$db['projectTracker']['char_set'] = 'utf8';
$db['projectTracker']['dbcollat'] = 'utf8_general_ci';
$db['projectTracker']['swap_pre'] = '';
$db['projectTracker']['autoinit'] = TRUE;
$db['projectTracker']['stricton'] = FALSE;


/* End of file database.php */
/* Location: ./application/config/database.php */
