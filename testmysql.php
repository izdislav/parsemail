<?php
// $mysqli = new mysqli("localhost", "parsemailuser", "C8A8e6msQnC2vJmF", "parsemail", 3306);
// if ($mysqli->connect_errno) {
//     echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
// }
// echo $mysqli->host_info . "\n";

// /* change character set to utf8 */
// if (!$mysqli->set_charset("utf8")) {
// 	printf("Error loading character set utf8: %s\n", $mysqli->error);
// 	exit();
// } else {
// 	printf("Current character set: %s\n", $mysqli->character_set_name());
// }

// $mysqli->query("INSERT INTO `parsemail`.`emailmessage` (`id`, `body`, `created_on`) VALUES (NULL, 'асдеяверттъ', CURRENT_TIMESTAMP);");






// $settings = parse_ini_file("./db/settings.ini.php");
// $dsn = 'mysql:dbname='.$settings["dbname"].';host='.$settings["host"].';charset=utf8';
// try
// {
// 	# Read settings from INI file, set UTF8
// 	$pdo = new PDO($dsn, $settings["user"], $settings["password"], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

// 	// 				$dbHandle = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass,
// 	// 						array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));

// 	# We can now log any exceptions on Fatal error.
// 	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 	# Disable emulation of prepared statements, use REAL prepared statements instead.
// 	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

// 	# Connection succeeded, set the boolean to true.
// 	$bConnected = true;

// }
// catch (PDOException $e)
// {
// 	# Write into log
// 	echo $ExceptionLog($e->getMessage());
// 	die();
// }

// $sQuery = $pdo->prepare("INSERT INTO `parsemail`.`emailmessage` (`id`, `body`, `created_on`) VALUES (NULL, 'асдеяверттъ', CURRENT_TIMESTAMP);");
// $sQuery->execute();







ini_set ("display_errors", "1");
error_reporting(E_ALL);
mb_internal_encoding('UTF-8');

include './Debug.php';

include './db/Db.class.php';


$_db = new Db();


$qry = $_db->query("INSERT INTO `parsemail`.`emailmessage` (`id`, `body`, `created_on`) VALUES (NULL, 'асдеяверттъ', CURRENT_TIMESTAMP);");