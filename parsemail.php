<?php
ini_set ("display_errors", "1");
error_reporting(E_ALL);
mb_internal_encoding('UTF-8');

include './include/Debug.php';

include './include/db/Db.class.php';
include './include/db/easyCRUD/easyCRUD.class.php';

// Include email parser
require_once('./include/mimeparser/rfc822_addresses.php');
require_once('./include/mimeparser/mime_parser.php');

// Include crud classes for database records presentation
include_once '/parsemail.crud.class.php';

// Include parse email class
include './parsemail.class.php';


try 
{
	$parsemail = new parsemail();
	
	$savedEmailId = $parsemail->parseEmail("./emails/message_1.eml");
	$email = $parsemail->getEmailDetails($savedEmailId);
	Debug::write($email);
	
	$userEmailIds = $parsemail->getEmails("plamen@gmail.com");
	Debug::write($userEmailIds);
	
	$emailsWithHeaderName = $parsemail->getEmailsByHeader("to");
	Debug::write($emailsWithHeaderName);
	
	$userEmailCount = $parsemail->getEmails("plamen@gmail.com", false);
	Debug::write($userEmailCount);
	
	$emailCount = $parsemail->getEmails("", true);
	Debug::write($emailCount);
	
	$userEmailsPerSenderDomain = $parsemail->getEmailsPerSenderDomain("plamen@gmail.com");
	Debug::write('$userEmailIds',$userEmailsPerSenderDomain);
	
	$emailSpfPassPercent = $parsemail->getEmailsSpfPass("plamen@gmail.com");
	//Debug::write('$userEmailIds',$emailSpfPassPercent);
	 

} 
catch (Exception $e) 
{
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
