<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	$_SESSION['max_crop_height'] = 1200;
	$_SESSION['max_crop_width'] = 1200;
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['day'])) {
		
		// Get minimum age from system settings
		$ageCheck = "SELECT minAge FROM systemsettings";
		try
		{
			$result = $pdo3->prepare("$ageCheck");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$minAge = $row['minAge'];
		
		$bdayraw = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day'];
		
		$age = date_diff(date_create($bdayraw), date_create('today'))->y;
		
		if ($age < $minAge) {
			pageStart($lang['member-newmembercaps'] . " - " . $lang['statutes'], NULL, $validationScript, "pprofile", "statutes dev-align-center", $lang['member-newmembercaps'] . " - " . $lang['statutes'], $_SESSION['successMessage'], $lang['too-young'] . $minAge . ".");
			exit();
			
		} else {
			
		// Check if a webcam DNI photo was submitted
		if (isset($_GET['upload'])) {
			
			$_SESSION['consumoPrevio'] = $_POST['consumoPrevio'];
			$_SESSION['memberType'] = $_POST['memberType'];
			$_SESSION['day'] = $_POST['day'];
			$_SESSION['month'] = $_POST['month'];
			$_SESSION['year'] = $_POST['year'];
			
			$bday = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day'];

			
			$user_id = $_SESSION['tempNo'];
			
			$image_fieldname = "fileToUpload";
			
			// Potential PHP upload errors
			$php_errors = array(1 => $lang['imgError1'],
								2 => $lang['imgError1'],
								3 => $lang['imgError2'],
								4 => $lang['imgError3']);
							
			// Check for any upload errors
			if ($_FILES[$image_fieldname]['error'] != 0) {
				$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
				header("Location: new-member-upload.php");
				exit();
			}
			
			// Check if a real file was uploaded
			if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError4'];
				header("Location: new-member-upload.php");
				exit();
			}
			
			// Is this actually an image?
			if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError5'];
				header("Location: new-member-upload.php");
				exit();
			}
			
			// Save the file
			$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
			$upload_filename = "images/_$domain/sigs/" . $user_id . "." . $extension;
			$_SESSION['sigext'] = $extension;
			
			if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
				
				$_SESSION['sigext'] = $extension;
				
				$_SESSION['image_path'] = $upload_filename;
				$_SESSION['success_url'] = "new-member-1.php";
				$_SESSION['successMessage'] = "Firma subido con éxito!";
				header("Location: crop-image.php");
				exit();
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError6'];
				header("Location: new-member-upload.php");
				exit();
			}
				
		}
					
			$_SESSION['consumoPrevio'] = $_POST['consumoPrevio'];
			$_SESSION['memberType'] = $_POST['memberType'];
			$_SESSION['day'] = $_POST['day'];
			$_SESSION['month'] = $_POST['month'];
			$_SESSION['year'] = $_POST['year'];
			
			$bday = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day'];
			
			function checkmydate($date) {
			  $tempDate = explode('-', $date);
			  // checkdate(month, day, year)
			  return checkdate($tempDate[1], $tempDate[2], $tempDate[0]);
			}
			
			if (checkmydate($bday) == false) {
				$_SESSION['errorMessage'] = $lang['wrong-date-format'];

			} else {
			
				header("Location: new-member-1.php");
				exit();
				
			}
		}
		
	}
		
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
$('#dd_signaturePadWrapper').click(function(e) {  
        $('#savesig').attr('checked', false)
    });
	    	    
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  accept: {
				  required: true
			  },
			  accept2: {
				  required: true
			  },
			  accept3: {
				  required: true
			  },
			  fileToUpload: {
				  required: true
			  },
			  day: {
				  required: true,
				  range:[1,31]
			  },
			  month: {
				  required: true,
				  range:[1,12]
			  },
			  year: {
				  required: true,
				  range:[1900,2000]
			  }

    	}, // end rules
		  errorPlacement: function(error, element) {
			if (element.is("#savesig")){
				 error.appendTo("#errorBox1");
			} else if (element.is("#accept2")){
				 error.appendTo("#errorBox2");
			} else if (element.is("#accept3")){
				 error.appendTo("#errorBox3");
			} else {
				return true;
			}
		},
		 
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
	  $.validator.messages.required = "Tienes que aceptar!";
	  
function demo_postSaveAction(f) {
	var objParent=document.getElementById('testDiv');
	var objDiv=document.createElement('div');
	with(objDiv) {
		setAttribute('id','demo_downloadWrapper');
		with(style) {
			position="relative";
			padding="10px";
			textAlign="left";
			margin="15px 70px 0px 70px";
			border="solid 1px #c0c0c0";
			backgroundColor="#efefef";
			borderRadius="4pt";
		}
		innerHTML="<h4>Demo signature saved to image. Click to download.</h4>";
		innerHTML+="<ul><li><a href=\"dd_signature_process.php?download="+f+"\" target=\"_blank\">"+f+"</a></li></ul>";
	}
	objParent.appendChild(objDiv);
}

  }); // end ready
EOD;

	// Generate random temporary membership number, to use throughout the process.
	$tempNo = "_" . generateRandomString();
	$_SESSION['tempNo'] = $tempNo;
	
	if (isset($_GET['aval'])) {
		$_SESSION['aval'] = $_GET['aval'];
	}
	if (isset($_GET['aval2'])) {
		$_SESSION['aval2'] = $_GET['aval2'];
	}

	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	echo "<div class='actionbox-np2'>";
	 echo "<div class='boxcontent'>";
		echo "<form id='registerForm' action='?upload' method='post' enctype='multipart/form-data'>";


?>


<?php

	$file = "_club/_$domain/contract.php";
	
	if (file_exists($file)) {
		include $file;
	}
	
?>

<br />
<center>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox1"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El socio/a solicitante manifiesta haber leído los estatutos y la presente solicitud de admisión de socio.
	  <input type="checkbox" name="accept" id="savesig"/>
	  <div class="fakebox"></div>
	 </label>
	</div>

   <span id="errorBox1"></span></td>

</center>
<br />
<center>
 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 <table>
  <tr>
   <td><strong><?php echo $lang['step']; ?> 1, elige archivo:</strong></td>

   <td style="padding-left: 5px;">
   	<!-- <input type="file" name="fileToUpload" id="fileToUpload"> -->
   	   		<div class="upload-btn-wrapper">
			  <button class="btn" style="width: auto !important;">Choose file</button>
			  <input type="file" name="fileToUpload" id="fileToUpload">
			</div>
   </td>
  </tr>
  <tr>
   <td style="padding-top: 10px;"><strong><?php echo $lang['step']; ?> 2, fecha de nacimiento:</strong></td>
   <td style="padding-top: 10px; padding-left: 5px;">   <input type="number" lang="nb" name="day" class="twoDigit defaultinput" maxlength="2" placeholder="dd" value="<?php echo $day; ?>" />
   <input type="number" lang="nb" name="month" class="twoDigit defaultinput" maxlength="2" placeholder="mm" value="<?php echo $month; ?>" />
   <input type="number" lang="nb" name="year" class="fourDigit defaultinput" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br /><br />
</td>
  </tr>
  <tr>
   <td style="padding-top: 10px;"><strong><?php echo $lang['step']; ?> 3, enviar:</strong></td>
   <td style="padding-top: 10px; padding-left: 5px;"><input type="submit" value="<?php echo $lang['submit']; ?>" class="cta1" name="submit"></td>
  </tr>
</table>
</center>


	</form>

</div>
</div>

<?php displayFooter(); ?>
