<?php
/**
 * Class for parse email and save it into mysql database 
 * 
 * Contains some extra methods for retrieve emails form the database in various ways
 * @author Victor Krastev
 * @version 1.0
 *
 */
class parsemail
{
	private $_db;
	
	/**
	 * Constructor, conects database object
	 */
	public function __construct()
	{
		$this->_db = new Db();
	}
	
	/**
	 * Parses and inserts email to database
	 * @param string $emailFileName path to email file
	 * @return int returns saved email id
	 */
	public function parseEmail($emailFileName)
	{
		if (!file_exists($emailFileName))
		{
			throw new Exception("Email file $emailFileName not found!");
		}
		
		// crud object for email
		$emailmessage = new emailmessage();
		$emailmessage->id = $emailmessage->create();
		
		
		// read email in from stdin
		$fd = fopen($emailFileName, "r");
		$email = "";
		while (!feof($fd)) 
		{
			$email .= fread($fd, 1024);
		}
		fclose($fd);
		
		//create the email parser class
		$mime = new mime_parser_class;
		$mime->ignore_syntax_errors = 1;
		$parameters = array(
				'Data'=>$email,
		);
		
		// parse email into array
		$mime->Decode($parameters, $decoded);
		
		
		//---------------------- Extracted Addresses -----------------------//
		// save email addresses from 'to' header to database
		foreach ($decoded[0]['ExtractedAddresses']['to:'] as $arrTo)
		{
			$this->saveUserEmailRelation ($arrTo, $emailmessage->id);

		}
		
		// save email addresses from 'cc' header to database
		if (isset($decoded[0]['ExtractedAddresses']['cc:']))
		{
			foreach ($decoded[0]['ExtractedAddresses']['cc:'] as $arrCc)
			{
				$this->saveUserEmailRelation ($arrCc, $emailmessage->id);
			}
		}
		

		//---------------------- GET EMAIL HEADER INFO -----------------------//
		foreach ($decoded[0]['Headers'] as $hName => $hValue)
		{
			$hName = str_replace(":", "", $hName);
			if (is_array($hValue))
			{
				foreach($hValue as $hSubName => $hSubValue)
				{
					$this->saveHeader($hName, $hSubValue, $emailmessage->id);
				}
			}
			else
			{
				$this->saveHeader($hName, $hValue, $emailmessage->id);
			}
		}
		
		
		//---------------------- FIND THE BODY -----------------------//
		//get the text message body
		if (substr(strtolower($decoded[0]['Headers']['content-type:']),0,strlen('text/plain')) == 'text/plain' && isset($decoded[0]['Body']))
		{
			$body = $decoded[0]['Body'];
		} 
		elseif (isset($decoded[0]['Parts'][0]) && substr(strtolower($decoded[0]['Parts'][0]['Headers']['content-type:']),0,strlen('text/plain')) == 'text/plain' && isset($decoded[0]['Parts'][0]['Body']))
		{
			$body = $decoded[0]['Parts'][0]['Body'];
		}
		elseif (isset($decoded[0]['Parts'][0]['Parts'][0]) && substr(strtolower($decoded[0]['Parts'][0]['Headers']['content-type:']),0,strlen('text/plain')) == 'text/plain' && isset($decoded[0]['Parts'][0]['Body'])) 
		{
			$body = $decoded[0]['Parts'][0]['Body'];
		} 
		elseif (isset($decoded[0]['Parts'][0]['Parts'][0]) && substr(strtolower($decoded[0]['Parts'][0]['Parts'][0]['Headers']['content-type:']),0,strlen('text/plain')) == 'text/plain' && isset($decoded[0]['Parts'][0]['Parts'][0]['Body'])) 
		{
			$body = $decoded[0]['Parts'][0]['Parts'][0]['Body'];
		}

		if (isset($body))
		{
			$emailmessage->body = trim($body);
		}
		
		//get the html message body
		if (substr(strtolower($decoded[0]['Headers']['content-type:']),0,strlen('text/html')) == 'text/html' && isset($decoded[0]['Body']))
		{
			$body_html = $decoded[0]['Body'];
		} 
		elseif (isset($decoded[0]['Parts'][1]) && substr(strtolower($decoded[0]['Parts'][1]['Headers']['content-type:']),0,strlen('text/html')) == 'text/html' && isset($decoded[0]['Parts'][1]['Body']))
		{
			$body_html = $decoded[0]['Parts'][1]['Body'];
		}
		elseif (isset($decoded[0]['Parts'][0]['Parts'][1]) && substr(strtolower($decoded[0]['Parts'][0]['Headers']['content-type:']),0,strlen('text/html')) == 'text/html' && isset($decoded[0]['Parts'][1]['Body']))
		{
			$body_html = $decoded[0]['Parts'][0]['Body'];
		}
		elseif (isset($decoded[0]['Parts'][0]['Parts'][1]) && substr(strtolower($decoded[0]['Parts'][0]['Parts'][1]['Headers']['content-type:']),0,strlen('text/html')) == 'text/html' && isset($decoded[0]['Parts'][0]['Parts'][1]['Body']))
		{
			$body_html = $decoded[0]['Parts'][0]['Parts'][1]['Body'];
		}
		
		if (isset($body_html))
		{
			$emailmessage->body_html = trim($body_html);
		}
		
		$emailmessage->save();
		
		//------------------------ ATTACHMENTS ------------------------------------//
		
		//loop through email parts
		foreach($decoded[0]['Parts'] as $part){
		
			//check for attachments
			if(isset($part['FileDisposition']) && $part['FileDisposition'] == 'attachment'){
		
				//format file name (change spaces to underscore then remove anything that isn't a letter, number or underscore)
				$filename = preg_replace('/[^0-9,a-z,\.,_]*/i','',str_replace(' ','_', $part['FileName']));
		
				$attachment = new attachment();
				$attachment->emailmessage_id = $emailmessage->id;
				//add file to attachments array
				if(!get_magic_quotes_gpc())
				{
					$attachment->name = addslashes($part['FileName']);
				}
				else
				{
					$attachment->name = $part['FileName'];
				}
				$attachment->size = $part['BodyLength'];
				$attachment->type = explode(";", $part['Headers']['content-type:'])[0];
				$attachment->content = addslashes($part['Body']);
				$attachment->create();
			}
		}
		
		return $emailmessage->id;
	}
	
	/**
	 * Save header name and value into database
	 * @param string $hName header name
	 * @param string $hValue header value
	 * @param int $emailmessageId
	 */
	private function saveHeader($hName, $hValue, $emailmessageId) 
	{
		// crud objects
		$header = new header();
		$headerName = new headername();
		
		$result = $this->_db->query("SELECT * FROM `headername` WHERE `header_name` = '$hName'");
		
		if (count($result) == 0)
		{
			// Insert header name into database
			$headerName->id = 0;
			$headerName->header_name = $hName;
			$headerName->id = $headerName->create();
		}
		else
		{
			// header name exists in database
			$headerName->header_name = $result[0]['header_name'];
			$headerName->id = $result[0]['id'];
				
		}
		$header->emailmessage_id = $emailmessageId;
		$header->headername_id = $headerName->id;
		$header->header_value = $hValue;
		$header->create();
	}

	
	/**
	 * Saves user and user email realation into database
	 * @param array $arrEmailAddress 
	 * @param int $emailmessageId 
	 */
	 private function saveUserEmailRelation($arrEmailAddress, $emailmessageId) 
	 {
		// crud objects
		$user = new user();
		$useremail = new useremail();
		
		$result = $this->_db->query("SELECT * FROM `user` WHERE `user_email` = :user_email", array("user_email"=>$arrEmailAddress['address']));
		
		if (count($result) == 0)
		{
			// Create new user 
			$user->user_email = $arrEmailAddress['address'];
			if (isset($arrEmailAddress['name']))
			{
				$user->user_name = $arrEmailAddress['name'];
			}
			$user->id = $user->create();
		}
		else
		{
			// User exists
			$user->user_email = $result[0]['user_email'];
			$user->id = $result[0]['id'];
		}
		
		// save user email relation
		$useremail->emailmessage_id = $emailmessageId;
		$useremail->user_id = $user->id;
		$useremail->type = 'to';
		$useremail->create();
	}

	
	/**
	 * Returns user or all emails from database
	 * @param string $emailAddress if empty returns all 
	 * @param string $lCount returns array of email Ids if false, otherwise int count of emails 
	 * @throws Exception
	 * @return array|int 
	 */
	public function getEmails($emailAddress="", $lCount=false)
	{
		if (!empty($emailAddress))
		{
			$userResult = $this->_db->query("SELECT id FROM `user` WHERE `user_email` = :user_email", array("user_email"=>$emailAddress));
			if (count($userResult) == 1)
			{
				if (!$lCount)
				{
					$useremailResult = $this->_db->column("SELECT id FROM `useremail` WHERE `user_id` = '".$userResult[0]['id']."'");
					return $useremailResult;
				}
				else 
				{
					$useremailResult = $this->_db->column("SELECT count(*) as cnt FROM `useremail` WHERE `user_id` = '".$userResult[0]['id']."'");
					return $useremailResult[0];
				}
			}
			else 
			{
				throw new Exception("Email address $emailAddress not found"); 
			}
		}
		else
		{
			if (!$lCount)
			{
				$emailResult = $this->_db->column("SELECT id FROM `useremail`");
				return $emailResult;
			}
			else
			{
				$emailResult = $this->_db->column("SELECT count(*) as cnt FROM `useremail`");
				return $emailResult[0];
			}
		}
	}
	
	/**
	 * Returns all emails with given header name
	 * @param $headerName
	 * @return array|bool on success returns array of email ids
	 */
	public function getEmailsByHeader($headerName) 
	{
		$emailsWithHeaderNameResult = $this->_db->column("SELECT emailmessage_id FROM `header` left join `headername` on `headername`.id = `header`.headername_id where `header_name` = '$headerName'");
		if (count($emailsWithHeaderNameResult) > 0)
		{
			return $emailsWithHeaderNameResult;
		}
		else 
		{
			return false;
		}
	}
	
	/**
	 * Returns sender domain array for given user email address or for all emails in database
	 * @param string $emailAddress
	 * @return array 
	 */
	public function getEmailsPerSenderDomain($emailAddress='')
	{
		if (!empty($emailAddress))
		{
			$query = "select count(*) as cnt, REPLACE(SUBSTRING_INDEX(header.header_value, '@', -1),'>','') as email 
				from header left join headername on headername.id = header.headername_id
				left join useremail on useremail.emailmessage_id = header.emailmessage_id
				left join user on user.id = useremail.user_id
				where headername.header_name = 'from'
				and user.user_email = '$emailAddress'
				group by email";
		}
		else
		{
			$query = "select count(*) as cnt, REPLACE(SUBSTRING_INDEX(header.header_value, '@', -1),'>','') as email 
				from header left join headername on headername.id = header.headername_id
				where headername.header_name = 'from'
				group by email";
		}
		
		$userResults = $this->_db->query($query);
		$emailsCount = array();
		foreach ($userResults as $userResult)
		{
			$emailsCount[$userResult['email']] = $userResult['cnt'];
		}
		return $emailsCount;
	}
	
	/**
	 * Returns percent of user emails with 'received-spf' header starts with 'pass'
	 * @param string $emailAddress
	 * @return number 
	 */
	public function getEmailsSpfPass($emailAddress="")
	{
		if (!empty($emailAddress))
		{
			$query = "select count(*) as cnt, user.user_email
				from header left join headername on headername.id = header.headername_id
				left join useremail on useremail.emailmessage_id = header.emailmessage_id
				left join user on user.id = useremail.user_id
				where headername.header_name = 'received-spf'				
				and user.user_email = '$emailAddress'
				and header.header_value like 'pass%'
				group by user.user_email";
			$emailCount = $this->getEmails($emailAddress, true);
		}
		else
		{
			$query = "select count(*) as cnt
				from header left join headername on headername.id = header.headername_id
				where headername.header_name = 'received-spf'		
				and header.header_value like 'pass%'";
			$emailCount = $this->getEmails("", true);
		}
		
		$emailsSpfPassResults = $this->_db->column($query);
		
		return round($emailsSpfPassResults[0]/$emailCount, 2) * 100;
	}
	
	
	/**
	 * Returns all data stored in database for given email id
	 * @param int $emailId email pk from emailmessage table
	 * @return array
	 */
	public function getEmailDetails($emailId)
	{
		$emailmessage = new emailmessage();
		
		$emailmessage->find($emailId);
		
		$arrEmail = $emailmessage->variables;
		
		$headersResult = $this->_db->query("select header_name, header_value 
									from header left join headername on headername.id = header.headername_id
									where header.emailmessage_id = $emailId");
		foreach ($headersResult as $header)
		{
			$arrEmail[$header['header_name']] = $header['header_value'];
		}
		
		$attachmentsResult = $this->_db->query("select * from attachment where attachment.emailmessage_id = $emailId");
		if (count($attachmentsResult) > 0)
		{
			$arrEmail['attachments'] = $attachmentsResult;
		}
		
		return $arrEmail;
	}
	
}