<?php
	
	require_once 'cOnfig/connection-tablet.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	
	$domain = $_SESSION['domain'];
	
	if (isset($_POST['submitit'])) {
		$user_id = $_POST['user_id'];
		$usageType = $_POST['memberType'];
		$consumoPrevio = $_POST['consumoPrevio'];
		
		if ($consumoPrevio > 0) {

			$query = "UPDATE users SET mconsumption = $consumoPrevio WHERE user_id = $user_id";
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
			
		}
		
		if ($_SESSION['sigtablet'] == 1) {
			
			if ($_GET['aval'] == 'true') {
				header("Location: new-signature-1.php?user_id=$user_id&aval=true");
			} else {
				header("Location: new-signature-1.php?user_id=$user_id");
			}
			
			exit();
					
		} else if ($_SESSION['sigtablet'] == 2) {
			
			$tempNo = "_" . generateRandomString();
			$_SESSION['tempNo'] = $tempNo;
			$user_id = $_POST['user_id'];
	
			$encoded_data = $_POST['sigImageData'];
			$binary_data = base64_decode( $encoded_data );
			
			$imgname = "images/_$domain/sigs/" . $tempNo . ".png";
			
			// save to server (beware of permissions)
			$result = file_put_contents( $imgname, $binary_data );
		
		}
		
		$_SESSION['successMessage'] = $lang['contract-signed'];
		
			
			header("Location: reg.php");
			
	}
	
	
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
$('#dd_signaturePadWrapper').click(function(e) {  
        $('#savesig').attr('checked', false)
    });
	    	    
	  $('#registerForm').validate({
		  rules: {
			  accept: {
				  required: true
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

	$user_id = $_GET['user_id'];
	$mconsumption = $_GET['mconsumption'];
	$usageType = $_GET['usageType'];
	
	$_SESSION['tempNo'] = $user_id;
	
	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>

<script type="text/javascript" src="js/dd_signature_pad.js"></script>
<form id="registerForm" method="post" action="">

<?php
	$file = "_club/_$domain/contract.php";
	
	if (file_exists($file)) {
		include $file;
	}
	
?>
<br />
<center>
   
	
<strong>
 <a href="#sig"><?php echo $lang['your-signature']; ?></a>
</strong>
<br /><br />
<a name="sig"></a>
<div id="signatureSet">
 <div id="dd_signaturePadWrapper"></div>
</div>
<br />

	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox1"></span>El socio/a solicitante manifiesta haber leído los estatutos y la presente solicitud de admisión de socio.
	  <input type="checkbox" name="accept" id="savesig"/>
	  <div class="fakebox"></div>
	 </label>
	</div>
	
</center>
</div>


<input type='hidden' name='user_id' value='<?php echo $user_id; ?>' />
<input type='hidden' name='submitit' value='yes' />
<center><span id="errorBox"></span><br />
	 <button name='oneClick' class='oneClick' type="submit"><?php echo $lang['submit']; ?></button><br /><br /><br /></center>
	</form>



<?php displayFooter(); ?>
