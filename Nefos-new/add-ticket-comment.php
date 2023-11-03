<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$ticketid = $_GET['ticketid'];
	$client = $_GET['number'];
	
	if ($_POST['confirmed'] == 'yes') {

		$ticketid = $_POST['ticketid'];
		$number = $_POST['number'];
		$language = $_POST['language'];
		$file1 = $_POST['file1'];
		$file2 = $_POST['file2'];
		$file3 = $_POST['file3'];
		$file4 = $_POST['file4'];

		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		
		// Attachments
		$not_Allowed_extention = array("exe","js","php","java","sql","mp3","xml","ogg","css","html","json","msu","msi","graphql","pif","application","gadget","msp","com","scr","hta","cpl","jar","bat","cmd","vb","vbs","vbe","jse","ws","wsf","wsc","wsh","ps1","ps1xml","ps2","ps2xml","psc1","psc2","msh","msh1","msh2","mshxml","msh1xml","msh2xml","scf","lnk","inf","reg");
		
		$not_allowed = array("application/javascript", "application/json", "application/x-www-form-urlencoded", "application/xml", "application/sql", "application/graphql", "application/ld+json", "audio/mpeg", "audio/ogg", "text/css", "text/html", "text/xml", "application/vnd.api+json", "application/octet-stream", "text/javascript", "application/x-msdownload");
	
	   $feedback_upload_dir = "feedback_attach";     // The directory for the video to be saved in

	
		 $maximum_files = 5; 
	$feedback_upload_dir = "/var/www/html/ccsnubev2_com/v6/feedback_attach"; 	
	$feedback_upload_path = $feedback_upload_dir."/";      
	$feedback_upload_dir2 = "feedback_attach"; 	
	$feedback_upload_path2 = $feedback_upload_dir2."/";      
	$feedback_prefix = "feedback_";      
	$feedback_name = $feedback_prefix.strtotime(date('Y-m-d H:i:s'));
	$feedback_location = $feedback_upload_path.$feedback_name; 
	$feedback_location2 = $feedback_upload_path2.$feedback_name; 

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
		              	 echo "Please upload valid file types only !";
		                 //header("Location: help-center.php");
		                 die;
		             }
		             
		            $mimetype = mime_content_type($feedback_tmp); 
		           	if(in_array($mimetype, $not_allowed)){
		              	 echo "Please upload valid files !";
		                 //header("Location: help-center.php");
		                 die;
		              }
		              
		            if ($feedback_size > 10048576) {
		                echo "file must be under ".$max_file."MB in size";
		                //header("Location: help-center.php");
		                die;
		              }

		               
		               $feedback_path = $feedback_location.$key.".".$file_ext;
		               $feedback_path2 = $feedback_location2.$key.".".$file_ext;
		               
			                move_uploaded_file($feedback_tmp, $feedback_path); 
			                //chmod($feedback_path, 0777);
		             
		            // Check whether file type is valid 
		          
		                    // Image db insert sql 
		                    $insertValuesSQL .= "('feedback_id', '".$feedback_path2."', NOW()),"; 
		                    // add attachment
		                   // $adminmail->AddAttachment($feedback_path);
		               
		        }
	    	}
	    	
	    }
	    
	    $insertValuesSQL = trim($insertValuesSQL, ',');
		
		// Query to add new comment
		$query = sprintf("INSERT INTO feedback_comments (feedbackid, operator, type, comment) VALUES ('%d', '%d', '%d', '%s');",
		  $ticketid, $_SESSION['user_id'], 1, $comment);
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$feedback_id = $pdo3->lastInsertId();
		
		if($insertValuesSQL != ''){
		    $insertValuesSQL = str_replace("feedback_id", $feedback_id, $insertValuesSQL);
		     $insertFeedback_attach = "INSERT INTO feedback_comment_attachments (feedback_id, file_name, uploaded_on) VALUES $insertValuesSQL";  
			try
			{
				$attach_result = $pdo3->prepare("$insertFeedback_attach")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		}
		
		if ($file1 == 1) {
			
		     $insertFeedback_attach = "INSERT INTO feedback_comment_attachments (feedback_id, file_name, uploaded_on) VALUES ('$feedback_id', 'feedback_attach/file1.pdf', NOW())";  
			try
			{
				$attach_result = $pdo3->prepare("$insertFeedback_attach")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
		}
		if ($file2 == 1) {
			
		     $insertFeedback_attach = "INSERT INTO feedback_comment_attachments (feedback_id, file_name, uploaded_on) VALUES ('$feedback_id', 'feedback_attach/file2.pdf', NOW())";  
			try
			{
				$attach_result = $pdo3->prepare("$insertFeedback_attach")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
		}
		if ($file3 == 1) {
			
		     $insertFeedback_attach = "INSERT INTO feedback_comment_attachments (feedback_id, file_name, uploaded_on) VALUES ('$feedback_id', 'feedback_attach/file3.pdf', NOW())";  
			try
			{
				$attach_result = $pdo3->prepare("$insertFeedback_attach")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
		}
		if ($file4 == 1) {
			
		     $insertFeedback_attach = "INSERT INTO feedback_comment_attachments (feedback_id, file_name, uploaded_on) VALUES ('$feedback_id', 'feedback_attach/file4.pdf', NOW())";  
			try
			{
				$attach_result = $pdo3->prepare("$insertFeedback_attach")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
		}
		
		$query = "UPDATE feedback SET status = 1 WHERE id = '$ticketid'";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		
		
		// Look up e-mail
        $query = "SELECT email, shortName FROM customers WHERE number = '$number'";
		try
		{
			$result = $pdo2->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$clubmail = $row['email'];
			$shortName = $row['shortName'];
			
		// Check if valid
		if ($clubmail == '') {
			
			$_SESSION['errorMessage'] = "Feedback sent to client - but no e-mail was sent as there's no e-mail registered for this client. Please update the Nefos tool.";
			
		} else if (!filter_var($clubmail, FILTER_VALIDATE_EMAIL)) {
			
			$_SESSION['errorMessage'] = "Feedback sent to client - but no e-mail was sent as their registered e-mail is invalid: $clubmail. Please update the Nefos tool.";
			
		} else {
			
			// Query to look up feedback
			$selectFeedback= "SELECT * FROM feedback WHERE id = '$ticketid'";
			try
			{
				$result = $pdo3->prepare("$selectFeedback");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			$feedback = $result->fetch();
				$status = $feedback['status'];
				$number = $feedback['number'];
				$club = $feedback['club'];
				$reason = $feedback['reason'];
				$issue = $feedback['issue'];
				$message = $feedback['message'];
				$operator_id = $feedback['operator_id'];
				$operator_name = $feedback['operator_name'];
				$time = date('d-m-Y H:i', strtotime($feedback['created_at']));
				
				
			if ($language == 'es') {
				
				$subject = "Centro de soporte: $issue";
				$body = "Hola $shortName, <br /> <br /> CCS ha respondido a tu ticket de soporte. <br /> <br /> Inicie sesión en tu software y haga clic en el icono del Centro de soporte para ver la respuesta. <br /> <br /> Saludos, <br /> El equipo de CCS.";
				
			} else {
				
				$subject = "Help Center reply: $issue";
				$body = "Hi $shortName,<br /><br />CCS has replied to your support ticket!<br /><br />Please login to your software and click the Help Center icon to see the reply.<br /><br />All the best,<br />The CCS team.";
				
			}
				
			// Send e-mail to client
			try {
				
			// Send e-mail(s)
			require_once '../PHPMailerAutoload.php';
			
			$mail = new PHPMailer(true);
			$mail->CharSet = 'UTF-8';
			$mail->SMTPDebug = 0;
			$mail->Debugoutput = 'html';
			$mail->isSMTP();
			$mail->Host = "mail.cannabisclub.systems";
			$mail->SMTPAuth = true;
			$mail->Username = "info@cannabisclub.systems";
			$mail->Password = "Insjormafon9191";
			$mail->SMTPSecure = 'ssl'; 
			$mail->Port = 465;
			$mail->setFrom('info@cannabisclub.systems', 'CCSNube');
			$mail->addAddress("$clubmail", "$shortName");
			$mail->Subject = $subject;
			$mail->isHTML(true);
			$mail->Body = $body;
			$mail->send();

			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   $_SESSION['errorMessage'] = "Error sending mail!!";
			}
			
		}
		
		
			
		// On success: redirect.
		$_SESSION['successMessage'] = "Reply added succesfully!";
		header("Location: ticket.php?ticketid=$ticketid");
		exit();
		
	}
	/***** FORM SUBMIT END *****/
	
	
	
	// Query to look up feedback
	$selectFeedback= "SELECT * FROM feedback WHERE id = '$ticketid'";
	try
	{
		$result = $pdo3->prepare("$selectFeedback");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  ignore:'', //because the radio buttons are hidden, validation ignores them. This way it'll work.
		  rules: {
			  day: {
				  required: true
			  },
			  month: {
				  required: true
			  },
			  year: {
				  required: true
			  },
			  hour: {
				  required: true
			  },
			  minute: {
				  required: true
			  },
			  comment: {
				  required: true,
				  minlength: 2
			  },
			  language: {
				  required: true
			  }			  
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate

      tinymce.init({
        selector: '#contacttext',
        height :'400',
        plugins: "code",
    });

  }); // end ready
EOD;

	
	pageStart("Nefos tool", NULL, $validationScript, "pprofilenew", "donations fees", $lang['delete-fee-payment'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$feedback = $result->fetch();
	
		$status = $feedback['status'];
		$number = $feedback['number'];
		$club = $feedback['club'];
		$reason = $feedback['reason'];
		$issue = $feedback['issue'];
		$message = $feedback['message'];
		$operator_id = $feedback['operator_id'];
		$operator_name = $feedback['operator_name'];
		$time = date('d-m-Y H:i', strtotime($feedback['created_at']));

?>
<center>
 <form id="registerForm" action="" method="POST" enctype="multipart/form-data">

<div id="mainbox-no-width" style='max-width: 500px;'>
 <div class='boxcontent'>
<span style='font-size: 22px; color: #f2b149; font-weight: 600; text-transform: capitalize;'><?php echo $club; ?></span><br />
<span style='font-size: 18px; color: #00a48c; font-weight: 600; text-transform: capitalize;'><?php echo $operator_name; ?></span><br />
<span style='font-size: 15px; color: #777; font-weight: 600; text-transform: capitalize;'><?php echo $time; ?>
</span>
</div>
</div>
<br />
<div id="mainbox-no-width" style='text-align: left;'>
 <div id="mainboxheader">
  <?php echo $reason; ?>
 </div>
 <div class='boxcontent'>

<strong><?php echo $issue; ?></strong><br />
<?php echo $message; ?>
<br />
<br />
<center>  <textarea name="comment" id="contacttext" placeholder="<?php echo $lang['global-comment']; ?>?" style='width: 800px;'></textarea> </center>
<br />
<br />
<strong style='font-size: 18px;'>INCLUDE FILES?</strong><br />
<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Instalacion bascula CCS.pdf
	  <input type="checkbox" name="file1" value="1" />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Scale installation CCS.pdf
	  <input type="checkbox" name="file2" value="1" />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Signature tablet.pdf
	  <input type="checkbox" name="file3" value="1" />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tablet de firmas.pdf
	  <input type="checkbox" name="file4" value="1" />
	  <div class="fakebox"></div>
	 </label>
	</div>

<center>
<style>
.inputfile {
	width: 0.1px;
	height: 0.1px;
	opacity: 0;
	overflow: hidden;
	position: absolute;
	z-index: -1;
}
.inputfile + label {
	display: inline-block;
	width: 170px;
	padding: 5px;
	margin: 20px;
	background-color: #00a48c;
	color: white;
	font-size: 18px;
	border-radius: 4px;
	position: relative;
	text-align: center;
	margin-bottom: 25px;
	text-transform: uppercase;
	border: 0;
	cursor: pointer;
}

.inputfile:focus + label,
.inputfile + label:hover {
	opacity: 0.8;
}
</style>
<input type="file" id="file" class="inputfile" name="attach_files[]" style='border: 0; margin-left: 10px;' data-multiple-caption="{count} files selected" multiple /><label for="file"><span>Click here to attach files</span></label>
<script>
'use strict';

;( function( $, window, document, undefined )
{
	$( '.inputfile' ).each( function()
	{
		var $input	 = $( this ),
			$label	 = $input.next( 'label' ),
			labelVal = $label.html();

		$input.on( 'change', function( e )
		{
			var fileName = '';

			if( this.files && this.files.length > 1 )
				fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
			else if( e.target.value )
				fileName = e.target.value.split( '\\' ).pop();

			if( fileName )
				$label.find( 'span' ).html( fileName );
			else
				$label.html( labelVal );
		});

		// Firefox bug fix
		$input
		.on( 'focus', function(){ $input.addClass( 'has-focus' ); })
		.on( 'blur', function(){ $input.removeClass( 'has-focus' ); });
	});
})( jQuery, window, document );
</script>
<br /><br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;English
	  <input type="radio" name="language" value="en" id="accept2" />
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Spanish
	  <input type="radio" name="language" value="es" id="accept3" />
	  <div class="fakebox"></div>
	 </label>
	</div>
</center>
<br />&nbsp;
  <input type='hidden' name='confirmed' value='yes' />
  <input type='hidden' name='ticketid' value='<?php echo $ticketid; ?>' />
  <input type='hidden' name='number' value='<?php echo $client; ?>' />
</div></div>
<br /><br />
<center>  <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: -2px; width: 286px;'><?php echo $lang['global-confirm']; ?></button></td>
</center>
 </form>
<script src="https://cdn.tiny.cloud/1/9pxfemefuncr8kvf2f5nm34xwdg8su9zxhktrj66loa5mexa/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<?php displayFooter();