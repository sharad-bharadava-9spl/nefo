<?php 
 require "../PHPMailerAutoload.php";

 function sendEmail($mail, $to, $body, $subject) {
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;

    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';

    //Set the hostname of the mail server
    $mail->Host = 'mail.cannabisclub.systems';

    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $mail->Port = 465;

    //Set the encryption system to use - ssl (deprecated) or tls
    $mail->SMTPSecure = 'ssl';

    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;

    //Username to use for SMTP authentication - use full email address for gmail
    $mail->Username = 'info@cannabisclub.systems';

    //Password to use for SMTP authentication
    $mail->Password = 'Insjormafon9191';

    //Set who the message is to be sent from
    $mail->setFrom('info@cannabisclub.systems', 'CCSNube');

    //Set who the message is to be sent to
    $mail->addAddress($to, $to);

    //Set the subject line
    $mail->Subject = $subject;

    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);
    $mail->Body = $body;
    //Replace the plain text body with one created manually
    $mail->AltBody = 'This is a plain-text message body';

    $sucess = $mail->send();
    //send the message, check for errors
    if (!$sucess) {
    echo "Mailer Error: " . $mail -> ErrorInfo;
    } else {
    echo "Message sent!";
    }
    die;
}
	   $maiAdmin = "test@yopmail.com";
	   
	   $subject = "CCS Club Status";
		$adminmail = new PHPMailer();
		$adminmail->isSMTP();
		
		$body = "Hello <b>Admin</b><br>
					<p>The club  has been approved !</p>";
		sendEmail($adminmail, $maiAdmin, $body, $subject);
		

		/*// UPDATE CONTRACT FOR NEW CLUB
		if(!empty($clubContract) || $clubContract != ''){
			$clubContractFile = $newDir."/contract.php";
			file_put_contents($clubContractFile, $clubContract);
		}else{
			file_put_contents($clubContractFile, '');
		}