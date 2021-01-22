<?php

namespace Hcode;

use Rain\Tpl;

class Mailler
{
	const USERNAME = "achadosperdidos2020@gmail.com";
	const PASSWORD = "aprj@#\$2020";
	const MAILER_FROM = 'Achados e perdidos RJ';

	private $email;
	
	function __construct($toAdress,$toName,$subject,$tplName, $data = array())
	{
		

		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] . "/views/email/",
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"         => false
	    );

		Tpl::configure( $config );

		$tpl = new Tpl();

		foreach ($data as $key => $value) {
			$tpl->assign($key, $value);
		}

		$html = $tpl->draw($tplName, ture);

		$this->email = new \PHPMailer();

		$this->email->isSMTP();

		//Enable SMTP debugging
		// SMTP::DEBUG_OFF = off (for production use) 0
		// SMTP::DEBUG_CLIENT = client messages 1
		// SMTP::DEBUG_SERVER = client and server messages 2
		//$this->email->SMTPDebug = SMTP::DEBUG_SERVER;
		$this->email->SMTPDebug = 0;

		//Set the hostname of the mail server
		$this->email->Host = 'smtp.gmail.com';
		// use
		// $this->email->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6

		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$this->email->Port = 587;

		//Set the encryption mechanism to use - STARTTLS or SMTPS
		//$this->email->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$this->email->SMTPSecure = "tls";

		//Whether to use SMTP authentication
		$this->email->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$this->email->Username = Mailer::USERNAME;

		//Password to use for SMTP authentication
		$this->email->Password = Mailer::PASSWORD;

		//Set who the message is to be sent from
		$this->email->setFrom( Mailer::USERNAME, Mailer::MAILER_FROM);

		//Set an alternative reply-to address
		//$this->email->addReplyTo('replyto@example.com', 'First Last');

		//Set who the message is to be sent to
		$this->email->addAddress($toAdress, $toName);

		//Set the subject line
		//***** POSSO COLOCAR VÁRIAS LINHAS P ENVIO **********//
		$this->email->Subject = $subject;

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		//$this->email->msgHTML(file_get_contents('contents.html'), __DIR__);
		$this->email->msgHTML(file_get_contents($html));

		//Replace the plain text body with one created manually
		$this->email->AltBody = 'Texto alternativo caso não funcione';

		//Attach an image file
		$this->email->addAttachment('Capturar.png');	
		}

		public function send()
		{

		return $this->email->send();		


		}
}