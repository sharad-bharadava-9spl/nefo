<?php
	
	session_start();
	
	if (isset($_GET['domain'])) {
		$_SESSION['domain'] = $_GET['domain'];
	}
	
	require_once 'cOnfig/connection-tablet.php';
	require_once 'cOnfig/view-nohead.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	unset($_SESSION['newUserId']);
	unset($_SESSSION['newAval']);
	
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
			pageStart($lang['member-newmembercaps'] . " - " . $lang['statutes'], NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - " . $lang['statutes'], $_SESSION['successMessage'], $lang['too-young'] . $minAge . ".");
			exit();
			
		} else {
			
			$_SESSION['consumoPrevio'] = $_POST['consumoPrevio'];
			$_SESSION['memberType'] = $_POST['memberType'];
			$_SESSION['day'] = $_POST['day'];
			$_SESSION['month'] = $_POST['month'];
			$_SESSION['year'] = $_POST['year'];
			header("Location: reg-2.php");
			exit();

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
    	messages: {
	    	memberType: 'Por favor, elige uno'
    	},
		  errorPlacement: function(error, element) {
			if (element.is(".memberType")){
				 error.appendTo("#errorBox4");
			} else if (element.is("#savesig")){
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
	
	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", "", $_SESSION['successMessage'], $_SESSION['errorMessage']);

if ($_SESSION['domain'] == 'choko') {
?>
<style>
body {
	background-color: #0d223f !important;
	color: #fff;
	}
#header {
	background-color: #0d223f !important;
	color: #fff;
	}
</style>
<?php } 

if ($_SESSION['domain'] == 'amagi') {
	
	echo "<a href='reg-sig.php' class='cta' style='float: right;'>Socio existente</a>";
	
}
?>

<br /><br />
<script type="text/javascript" src="js/dd_signature_pad3.js"></script>

	<form id="registerForm" method="post" action="">

<?php

	$file = "_club/_$domain/contract.php";
	
	if (file_exists($file)) {
		include $file;
	}
	
?>
<br />
<center>
<h1><?php echo $lang['birthdate']; ?></h1>
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" placeholder="dd" value="<?php echo $day; ?>" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" placeholder="mm" value="<?php echo $month; ?>" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br /><br />
</center>
<center><strong><a href="#sig">Tú firma:</a></strong><br /><br />
<a name="sig"></a><div id="signatureSet">
		<div id="dd_signaturePadWrapper"></div>
	</div><br />
	
<input type="checkbox" name="accept" id="savesig" style="width: 12px;" /><span id="errorBox1"></span>&nbsp;&nbsp;Estoy de acuerdo y aceptado todo arriba.<br />
   
</center>

<center><span id="errorBox"></span><br />
	 <button name='oneClick' class='oneClick' type="submit">Submit</button><br /><br /><br /></center>
	</form>



<?php displayFooter(); ?>
