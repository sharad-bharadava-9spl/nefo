<?php
require_once 'cOnfig/connection.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';
require 'PHPMailerAutoload.php'; 

session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

 $club = $_SESSION['domain'];
$worker_id = $_SESSION['user_id'];

$query = sprintf("SELECT first_name, last_name FROM users WHERE user_id = '%d';",
						$worker_id);
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user1: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	$row = $result->fetch();
	  $worker_first_name = $row['first_name'];
	  $worker_last_name = $row['last_name'];
      $worker_name = $worker_first_name." ".$worker_last_name;
if($worker_first_name != '' || $worker_last_name != ''){
	$worker = $worker_name;
}else{
	$worker = $_SESSION['first_name'];
}
$max_file = 20; // file size
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
    $mail->Username = "info@cannabisclub.systems";

    //Password to use for SMTP authentication
    $mail->Password = "Insjormafon9191";

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
/*    if (!$sucess) {
    echo "Mailer Error: " . $mail -> ErrorInfo;  die;
    } else {
    echo "Message sent!"; die;
    }*/
}


  /*$allowed_file_types = array('image/pjpeg'=>"jpg",'image/jpeg'=>"jpg",'image/jpg'=>"jpg",'image/pjpeg'=>"jpeg",'image/jpeg'=>"jpeg",'image/jpg'=>"jpeg",'image/png'=>"png",'image/x-png'=>"png",'image/gif'=>"gif", 'application/doc', 'application/pdf', 'another/type');*/
/* $allowed_file_types = array('application/doc' => "doc", 'application/pdf'=> 'pdf','image/jpg'=>"jpg", 'image/jpeg'=>"jpeg",'image/jpg'=>"jpeg",'image/png'=>"png",'image/x-png'=>"png",'image/gif'=>"gif", 'application/zip' => "zip", 'application/msword' => 'doc', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx', 'application/vnd.ms-excel'=>'xls', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xlsx', 'application/vnd.ms-powerpoint'=>'ppt', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'pptx','application/vnd.oasis.opendocument.text'=>'odt', 'text/plain'=>'txt', 'application/rtf' =>'rtf', 'application/x-rar-compressed' => 'rar' ,'image/vnd.adobe.photoshop' => 'psd' ,'video/3gpp' => '3gp');*/
$not_Allowed_extention = array("exe","js","php","java","sql","mp3","xml","ogg","css","html","json","msu","msi","graphql","pif","application","gadget","msp","com","scr","hta","cpl","jar","bat","cmd","vb","vbs","vbe","jse","ws","wsf","wsc","wsh","ps1","ps1xml","ps2","ps2xml","psc1","psc2","msh","msh1","msh2","mshxml","msh1xml","msh2xml","scf","lnk","inf","reg");
$not_allowed = array("application/javascript", "application/json", "application/x-www-form-urlencoded", "application/xml", "application/sql", "application/graphql", "application/ld+json", "audio/mpeg", "audio/ogg", "text/css", "text/html", "text/xml", "application/vnd.api+json", "application/octet-stream", "text/javascript", "application/x-msdownload");
   $feedback_upload_dir = "feedback_attach";     // The directory for the video to be saved in

	if(!is_dir($feedback_upload_dir)){
	  mkdir($feedback_upload_dir, 0777);
	}
	$feedback_upload_dir = "feedback_attach"; 	
	$feedback_upload_path = $feedback_upload_dir."/";      
	$feedback_prefix = "feedback_";      
	$feedback_name = $feedback_prefix.strtotime(date('Y-m-d H:i:s'));
	$feedback_location = $feedback_upload_path.$feedback_name; 

	if(isset($_POST['feedback_sub'])){
		$adminmail = new PHPMailer();
		$reason = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['reason']))); 
		$issue = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['issue'])));
		$message = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['message']))); 
		 $maximum_files = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['max_files']))); 
		$insertTime = date("Y-m-d H:i:s");
		$fileNames = array_filter($_FILES['attach_files']['name']);
		$insertValuesSQL = '';
		$count_attach_files = count($fileNames);
		if(!empty($fileNames)){ 
			if(count($fileNames) <= $maximum_files){
		        foreach($_FILES['attach_files']['name'] as $key=>$val){ 
		            // File upload path 
		           
		            $feedback_name = $_FILES['attach_files']['name'][$key];
		            $feedback_tmp = $_FILES['attach_files']['tmp_name'][$key];
		            $feedback_size = $_FILES['attach_files']['size'][$key];
		            $feedback_type = $_FILES['attach_files']['type'][$key];
		            $filename = basename($_FILES['attach_files']['name'][$key]);
		            $file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
		           	              //check if the file size is above the allowed limit
		           // echo $feedback_type."<br>";
		           if(in_array($file_ext, $not_Allowed_extention)){
		              	 $_SESSION['errorMessage']  = "Please upload valid file types only !";
		                 header("Location: help-center.php");
		                 die;
		             }
		            $mimetype = mime_content_type($feedback_tmp); 
		           	if(in_array($mimetype, $not_allowed)){
		              	 $_SESSION['errorMessage']  = "Please upload valid files !";
		                 header("Location: help-center.php");
		                 die;
		              }
		            if ($feedback_size > ($max_file*1048576)) {
		                $_SESSION['errorMessage'] = "file must be under ".$max_file."MB in size";
		                header("Location: help-center.php");
		                die;
		              }

		               
		               $feedback_path = $feedback_location.$key.".".$file_ext;

			                move_uploaded_file($feedback_tmp, $feedback_path); 
			                chmod($feedback_path, 0777);
		             
		            // Check whether file type is valid 
		          
		                    // Image db insert sql 
		                    $insertValuesSQL .= "('feedback_id', '".$feedback_path."', NOW()),"; 
		                    // add attachment
		                   // $adminmail->AddAttachment($feedback_path);
		               
		        }
	    	}else{
	    		$_SESSION['errorMessage']  = "Sorry, you can upload maximum ".$maximum_files." files !";
		        header("Location: help-center.php");
		        die;
	    	}
	    }
	 
	     $insertValuesSQL = trim($insertValuesSQL, ',');   
	   // echo "INSERT INTO feedback_attachments (file_name, uploaded_on) VALUES $insertValuesSQL"; die;
		// Send success emails
	    $body = "<!DOCTYPE html>
			<html>
			   <head>
			      <style>
			        table {
			          font-family: arial, sans-serif;
			          border-collapse: collapse;
			          width: 70%;
			        }
			        
			        td, th {
			          border: 1px solid #dddddd;
			          text-align: left;
			          padding: 8px;
			        }
			        
			        tr:nth-child(even) {
			          background-color: #dddddd;
			        }
			      </style>
			   </head>";
			$body .= "<body>
			      		Dear admin,<br>
						$worker from $club has sent feedback from their software.<br><br><br>
			      <table>
			         <tr>
			            <td>Reason</td>
			            <td>".$reason."</td>
			         </tr><tr>
			            <td>Issue</td>
			            <td>".$issue."</td>
			         </tr><tr>
			            <td>Message</td>
			            <td>".$message."</td>
			         </tr>";
			if($count_attach_files > 0){
				$body .=  "<tr>
								<td>Attachment(s)</td>
								<td>".$count_attach_files." file(s) included</td>
							</tr>";
			}         
			$body .=  "</table>
			   </body>
			</html>";   
            // info@cannabisclub.systems
	        $maiAdmin = "info@cannabisclub.systems";
	        $subject = "New feedback from CCS software - club $club";
	       
		      
		      $adminmail->isSMTP();
         
         
        sendEmail($adminmail, $maiAdmin, $body, $subject);

		 $insertFeedback = sprintf("INSERT INTO feedback (reason, operator_id, club, operator_name, issue, message, created_at) VALUES ('%s', '%d', '%s', '%s', '%s', '%s', '%s')",
		
					$reason,
					$worker_id,
					$club,
					$worker,
					$issue,
					$message,
					$insertTime,
					);   
		try
		{
			$result = $pdo2->prepare("$insertFeedback")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}	
		$feedback_id = $pdo2->lastInsertId();
		if($insertValuesSQL != ''){
		    $insertValuesSQL = str_replace("feedback_id", $feedback_id, $insertValuesSQL);
		     $insertFeedback_attach = "INSERT INTO feedback_attachments (feedback_id, file_name, uploaded_on) VALUES $insertValuesSQL";  
			try
			{
				$attach_result = $pdo2->prepare("$insertFeedback_attach")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		}
		$_SESSION['successMessage'] = "Feedback sent successfully!";
		header("Location: help-center.php");
		die;
		
	}
