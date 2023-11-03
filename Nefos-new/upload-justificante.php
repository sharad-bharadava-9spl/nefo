<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if ($_POST['confirmed'] == 'yes') {
		
		$ticketid = $_POST['ticketid'];
		$number = $_POST['number'];
		$language = $_POST['language'];

		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		
		// Attachments
		$not_Allowed_extention = array("exe","js","php","java","sql","mp3","xml","ogg","css","html","json","msu","msi","graphql","pif","application","gadget","msp","com","scr","hta","cpl","jar","bat","cmd","vb","vbs","vbe","jse","ws","wsf","wsc","wsh","ps1","ps1xml","ps2","ps2xml","psc1","psc2","msh","msh1","msh2","mshxml","msh1xml","msh2xml","scf","lnk","inf","reg");
		
		$not_allowed = array("application/javascript", "application/json", "application/x-www-form-urlencoded", "application/xml", "application/sql", "application/graphql", "application/ld+json", "audio/mpeg", "audio/ogg", "text/css", "text/html", "text/xml", "application/vnd.api+json", "application/octet-stream", "text/javascript", "application/x-msdownload");
	
	   $feedback_upload_dir = "feedback_attach";     // The directory for the video to be saved in

	
		 $maximum_files = 5; 
	$feedback_upload_dir = "/var/www/html/ccsnubev2_com/v6/justificantes"; 	
	$feedback_upload_path = $feedback_upload_dir."/";      
	$feedback_upload_dir2 = "justificantes"; 	
	$feedback_upload_path2 = $feedback_upload_dir2."/";      
	$feedback_prefix = "justificante_";      
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
	    
	    foreach ($_POST['invoices'] as $inv) {
			
			$invno2 = $inv['invno'];
			$query = "UPDATE invoices SET justificante = '$feedback_path2', verified = 1 WHERE invno = '$invno2'";
			try
			{
				$result = $pdo->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}			
			
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = "Justificante uploaded succesfully!";
		header("Location: cutoff.php");
		exit();
		
	}
	/***** FORM SUBMIT END *****/
	
	$client = $_GET['client'];
	$period = $_GET['period'];
	$invno = $_GET['invno'];
	
	pageStart("Nefos tool", NULL, $validationScript, "pprofilenew", "donations fees", $lang['delete-fee-payment'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>
 <form id="registerForm" action="" method="POST" enctype="multipart/form-data">

<div id="mainbox-no-width" style='text-align: left;'>
 <div id="mainboxheader">
  Upload justificante
 </div>
 <div class='boxcontent'>

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
<center><input type="file" id="file" class="inputfile" name="attach_files[]" style='border: 0; margin-left: 10px;' data-multiple-caption="{count} files selected" multiple /><label for="file"><span>Click here to attach files</span></label>
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
<strong>Apply to which invoices?</strong><br /><br />
<table class='default noborder'>
<?php

	$query = "SELECT invno, invdate, action, cutoffdate, promise, paid, amount FROM invoices WHERE customer = '$client' AND paid = ''";
	try
	{
		$results = $pdo->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$i = 0;
	while ($row = $results->fetch()) {
		
		$invno2 = $row['invno'];
		$invdate = date("d-m-Y", strtotime($row['invdate']));
		$amount = $row['amount'];
		$cutoffdate = date("d-m-Y", strtotime($row['cutoffdate']));
		$promise = date("d-m-Y", strtotime($row['promise']));
		
		if ($invno2 == $invno) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		
		echo "<tr><td><input type='checkbox' name='invoices[$i][invno]' value='$invno2' $checked /></td><td>$invno2</td><td>$invdate</td><td class='right'>$amount &euro;</td></tr>";
		
		$i++;
		
	}

?>
</table>
<br /><br />

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