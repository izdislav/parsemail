<?php
class parsemail
{
	private $_db;
	
	
	public function __construct()
	{
		$this->_db = new Db();
	}
	
	public function parseEmail($emailFileName)
	{
		$header = new header();
		$headerName = new headername();
		$emailmessage = new emailmessage();
		$emailmessage->id = $emailmessage->create();
		
		
		// read email in from stdin
		$fd = fopen($emailFileName, "r");
		//$email = file_get_contents("message_5.eml");
		$email = "";
		while (!feof($fd)) {
			$email .= fread($fd, 1024);
		}
		fclose($fd);
		
		//create the email parser class
		$mime=new mime_parser_class;
		$mime->ignore_syntax_errors = 1;
		$parameters=array(
				'Data'=>$email,
		);
		
		$mime->Decode($parameters, $decoded);
		
		
		//---------------------- Extracted Addresses -----------------------//
		//get the name and email of the sender
		//$fromName = $decoded[0]['ExtractedAddresses']['from:'][0]['name'];
		//$fromEmail = $decoded[0]['ExtractedAddresses']['from:'][0]['address'];
		//Debug::write($decoded[0]['ExtractedAddresses']);
		//get the name and email of the recipient
		foreach ($decoded[0]['ExtractedAddresses']['to:'] as $arrTo)
		{
			$user = new user();
			$useremail = new useremail();
			
			$result = $this->_db->query("SELECT * FROM `user` WHERE `user_email` = :user_email", array("user_email"=>$arrTo['address']));
			
			if (count($result) == 0)
			{
				$user->user_email = $arrTo['address'];
				if (isset($arrTo['name']))
				{
					$user->user_name = $arrTo['name'];
				}
				$user->id = $user->create();
			}
			else
			{
				$user->user_email = $result[0]['user_email'];
				$user->id = $result[0]['id'];
			}
			
			$useremail->emailmessage_id = $emailmessage->id;
			$useremail->user_id = $user->id;
			$useremail->type = 'to';
			$useremail->create();
		}
		
		
		if (isset($decoded[0]['ExtractedAddresses']['cc:']))
		{
			foreach ($decoded[0]['ExtractedAddresses']['cc:'] as $arrTo)
			{
				$user = new user();
				$useremail = new useremail();
					
				$result = $this->_db->query("SELECT * FROM `user` WHERE `user_email` = :user_email", array("user_email"=>$arrTo['address']));
				if (count($result) == 0)
				{
					$user->user_email = $arrTo['address'];
					if (isset($arrTo['name']))
					{
						$user->user_name = $arrTo['name'];
					}
					$user->id = $user->create();
				}
				else
				{
					$user->user_email = $result[0]['user_email'];
					$user->id = $result[0]['id'];
						
				}
				$useremail->emailmessage_id = $emailmessage->id;
				$useremail->user_id = $user->id;
				$useremail->type = 'cc';
				$useremail->create();
			}
		}
		

		//---------------------- GET EMAIL HEADER INFO -----------------------//
		//Debug::write($decoded[0]['Headers']);
		foreach ($decoded[0]['Headers'] as $hName => $hValue)
		{
			$hName = str_replace(":", "", $hName);
			if (is_array($hValue))
			{
				foreach($hValue as $hSubName => $hSubValue)
				{
					$result = $this->_db->query("SELECT * FROM `headername` WHERE `header_name` = :headerName", array("headerName"=>$hName));
		
					if (count($result) == 0)
					{
						$headerName->id = 0;
						$headerName->header_name = $hName;
						$headerName->id = $headerName->create();
					}
					else
					{
						$headerName->header_name = $result[0]['header_name'];
						$headerName->id = $result[0]['id'];
							
					}
					$header->emailmessage_id = $emailmessage->id;
					$header->headername_id = $headerName->id;
					$header->header_value = $hSubValue;
					$header->create();
				}
			}
			else
			{
				$result = $this->_db->query("SELECT * FROM `headername` WHERE `header_name` = :headerName", array("headerName"=>$hName));
					
				if (count($result) == 0)
				{
					$headerName->id = 0;
					$headerName->header_name = $hName;
					$headerName->id = $headerName->create();
				}
				else
				{
					$headerName->header_name = $result[0]['header_name'];
					$headerName->id = $result[0]['id'];
		
				}
				$header->emailmessage_id = $emailmessage->id;
				$header->headername_id = $headerName->id;
				$header->header_value = mb_decode_mimeheader($hValue);
				$header->create();
			}
		}
		
		
		//---------------------- FIND THE BODY -----------------------//
//  		Debug::write($decoded[0]);
// 		Debug::write(substr(strtolower($decoded[0]['Parts'][0]['Headers']['content-type:']),0,strlen('text/plain')));
// 		Debug::write($decoded[0]['Parts'][0]['Body']);
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
		
		//Debug::write($decoded[0]['Parts'][1]);
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
		
		
		
		//Debug::write($emailmessage->body);
		
		//------------------------ ATTACHMENTS ------------------------------------//
		
		//loop through email parts
		foreach($decoded[0]['Parts'] as $part){
		
			//check for attachments
			if(isset($part['FileDisposition']) && $part['FileDisposition'] == 'attachment'){
		
				//format file name (change spaces to underscore then remove anything that isn't a letter, number or underscore)
				$filename = preg_replace('/[^0-9,a-z,\.,_]*/i','',str_replace(' ','_', $part['FileName']));
		
				// 		//write the data to the file
				// 		$fp = fopen('upload/' . $filename, 'w');
				// 		$written = fwrite($fp,$part['Body']);
				// 		fclose($fp);
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
				echo "<br>File $attachment->name uploaded<br>";
				Debug::write("File $attachment->name uploaded");
			}
		}
		
		return true;
		
// 		$result = $this->_db->query("select header_name, header_value from header
// 				left join headername on headername.id = header.headername_id
// 				where emailmessage_id = $emailmessage->id");
	}
	
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
	
	public function getEmailsByHeader($headerName) 
	{
		$emailsWithHeaderNameResult = $this->_db->column("SELECT emailmessage_id FROM `header` left join `headername` on `headername`.id = `header`.headername_id where `header_name` = '$headerName'");
		if (count($emailsWithHeaderNameResult) > 0)
		{
			return $emailsWithHeaderNameResult;
		}
	}
	
	public function getEmailsPerS�nderDomain($emailAddress='')
	{
		if (!empty($emailAddress))
		{
			$query = "select count(*) as cnt, REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(header.header_value, '@', -1), '.', -2),'>','') as email 
				from header left join headername on headername.id = header.headername_id
				left join useremail on useremail.emailmessage_id = header.emailmessage_id
				left join user on user.id = useremail.user_id
				where headername.header_name = 'from'
				and user.user_email = '$emailAddress'
				group by email";
		}
		else
		{
			$query = "select count(*) as cnt, REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(header.header_value, '@', -1), '.', -2),'>','') as email 
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
		
		Debug::write($arrEmail);
	}
	
	public function checkEmailInDatabase($emailAddress)
	{
		
	}

}