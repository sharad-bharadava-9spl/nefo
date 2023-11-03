<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
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
		
		if ($_GET['aval'] == 'true') {
			
			header("Location: aval-details.php?user_id=$user_id");
		
		} else {
			
			header("Location: profile.php?user_id=$user_id");
			
		}
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
	
	pageStart($lang['new-signature'], NULL, $validationScript, "pprofile", "statutes", $lang['new-signature'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


if ($_SESSION['sigtablet'] == 0) { ?>

<script type="text/javascript" src="js/dd_signature_pad-v6.js"></script>
<form id="registerForm" method="post" action="">

<?php } else if ($_SESSION['sigtablet'] == 2) { ?>

<script type="text/javascript" src="scripts/SigWebTablet.js"></script>

<script type="text/javascript">
var tmr;

function onSign()
{
   var ctx = document.getElementById('cnv').getContext('2d');         
   SetDisplayXSize( 500 );
   SetDisplayYSize( 100 );
   SetJustifyMode(0);
   ClearTablet();
   tmr = SetTabletState(1, ctx, 50) || tmr;
}

function onClear()
{
   ClearTablet();
}

function onDone()
{
   if(NumberOfTabletPoints() == 0)
   {
      alert("Tienes que firmar!");
   }
   else
   {
      SetTabletState(0, tmr);
      //RETURN TOPAZ-FORMAT SIGSTRING
      SetSigCompressionMode(1);
      document.FORM1.bioSigData.value=GetSigString();
      document.FORM1.sigStringData.value += GetSigString();
      //this returns the signature in Topaz's own format, with biometric information


      //RETURN BMP BYTE ARRAY CONVERTED TO BASE64 STRING
      SetImageXSize(500);
      SetImageYSize(100);
      SetImagePenWidth(5);
      GetSigImageB64(SigImageCallback);
      document.getElementById("button2").style.background='#b6ec98';
   }
}

function SigImageCallback( str )
{
   document.FORM1.sigImageData.value = str;
}


	
</script> 


<script type="text/javascript">
window.onunload = window.onbeforeunload = (function(){
closingSigWeb()
})

function closingSigWeb()
{
   ClearTablet();
   SetTabletState(0, tmr);
}

</script>
<style>
p {
	margin: 20px;
}
ol li {
	margin-top: 10px;
	padding-left: 10px;
	line-height: 1.5em;
	text-align: left !important;
}
</style>

	<form id="registerForm" method="post" name="FORM1" action="">
	
<?php } else { ?>

<form id="registerForm" method="post" action="">

<?php } ?>
 <div id='mainbox'>
  <div id='mainboxheader'>
  Solicitud admisión soci@ libre consumidor@
  </div>
  <div id='contractholder'>

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
   
<?php if ($_SESSION['sigtablet'] == 0) { ?>
	
	
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
	  <span id="errorBox1"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El socio/a solicitante manifiesta haber leído los estatutos y la presente solicitud de admisión de socio.
	  <input type="checkbox" name="accept" id="savesig"/>
	  <div class="fakebox"></div>
	 </label>
	</div>
	
</center>
</div>

<?php } else if ($_SESSION['sigtablet'] == 2) { ?>

<strong><?php echo $lang['your-signature']; ?></strong><br />
<canvas id="cnv" name="cnv" width="500" height="150" onclick="javascript:onSign()" style="border: 2px solid #a80082;"></canvas><br /><br />

<input id="button1" name="ClearBtn" type="button" value="Limpiar" onclick="javascript:onClear()" style="margin-right: 10px; width: 80px;" />

<input id="button2" name="DoneBtn" type="button" value="Finalizar" onclick="javascript:onDone()" style="margin-left: 10px; width: 80px;" /><br />

<br />
<br />

 <table>
  <tr>
   <td><input type="checkbox" name="accept" id="savesig" style="width: 12px;" /></td>
   <td>Estoy de acuerdo y aceptado todo arriba.<br />
   <span id="errorBox1"></span></td>
  </tr>
 </table>
</center><br />
<INPUT TYPE=HIDDEN NAME="bioSigData">
<INPUT TYPE=HIDDEN NAME="sigImgData">
<INPUT TYPE=HIDDEN NAME="sigStringData" />
<INPUT TYPE=HIDDEN NAME="sigImageData" />

<?php } ?>
<input type='hidden' name='user_id' value='<?php echo $user_id; ?>' />
<input type='hidden' name='submitit' value='yes' />
<center><span id="errorBox"></span><br />
	 <button name='oneClick' class='oneClick' type="submit"><?php echo $lang['submit']; ?></button><br /><br /><br /></center>
	</form>



<?php displayFooter(); ?>
