<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$domain = $_SESSION['domain'];
	
	// If contract exists, include it
	// Sig: 0 = mouse, 1 = CCS, 2 = Topaz
	
	if (isset($_GET['aval'])) {
		$_SESSION['aval'] = $_GET['aval'];
	}
	if (isset($_GET['aval2'])) {
		$_SESSION['aval2'] = $_GET['aval2'];
	}

		
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
			
			$bday = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day'];
			
			function checkmydate($date) {
			  $tempDate = explode('-', $date);
			  // checkdate(month, day, year)
			  return checkdate($tempDate[1], $tempDate[2], $tempDate[0]);
			}
			
			if (checkmydate($bday) == false) {
				$_SESSION['errorMessage'] = $lang['wrong-date-format'];

			} else {
				
				if ($_SESSION['sigtablet'] == 0) {
					
					header("Location: new-member-1.php");
					
				} else if ($_SESSION['sigtablet'] == 1) {
					
					header("Location: new-member-0b.php");
					
				} else {
					
					// Topaz
					$tempNo = $_SESSION['tempNo'];
			
					$encoded_data = $_POST['sigImageData'];
					$binary_data = base64_decode( $encoded_data );
					
					$imgname = "images/_$domain/sigs/" . $tempNo . ".png";
					
					// save to server (beware of permissions)
					$result = file_put_contents( $imgname, $binary_data );

	                $cloudPath = $google_root_folder.$imgname;
	                $isSucceed = uploadFile($google_bucket, $result, $cloudPath);
			        if ($isSucceed == true) {
			        	unlink($imgname);
			           }
		
					header("Location: new-member-1.php");
				
				}
				exit();
				
			}
		}
		
	}
		
	if ($_SESSION['fastVisitor'] == 1 && $_GET['normalmember'] != 'true') {
		
		pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>
<script>
	// disable back script
	(function (global) {

	  if(typeof (global) === "undefined")
	  {
	    throw new Error("window is undefined");
	  }

	    var _hash = "!";
	    var noBackPlease = function () {
	        global.location.href += "#";

	    // making sure we have the fruit available for juice....
	    // 50 milliseconds for just once do not cost much (^__^)
	        global.setTimeout(function () {
	            global.location.href += "!";
	        }, 50);
	    };
	  
	  // Earlier we had setInerval here....
	    global.onhashchange = function () {
	        if (global.location.hash !== _hash) {
	            global.location.hash = _hash;
	        }
	    };

	    global.onload = function () {
	        
	    noBackPlease();

	    // disables backspace on page except on input fields and textarea..
	    document.body.onkeydown = function (e) {
	            var elm = e.target.nodeName.toLowerCase();
	            if (e.which === 8 && (elm !== 'input' && elm  !== 'textarea')) {
	                e.preventDefault();
	            }
	            // stopping event bubbling up the DOM tree..
	            e.stopPropagation();
	        };
	    
	    };

	})(window);		
</script>

			<center>
			 <a class="cta1" href="?normalmember=true"><?php echo $lang['normal-member']; ?></a>
			 <a class="cta1" href="new-visitor.php"><?php echo $lang['day-visitor']; ?></a>
			</center>

<?php		
		
	} else {
		
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
				  range:[1900,2001]
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

	pageStart($lang['member-contract'], NULL, $validationScript, "pprofile", "statutes", $lang['member-contract'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
<script>
	// disable back script
	(function (global) {

	  if(typeof (global) === "undefined")
	  {
	    throw new Error("window is undefined");
	  }

	    var _hash = "!";
	    var noBackPlease = function () {
	        global.location.href += "#";

	    // making sure we have the fruit available for juice....
	    // 50 milliseconds for just once do not cost much (^__^)
	        global.setTimeout(function () {
	            global.location.href += "!";
	        }, 50);
	    };
	  
	  // Earlier we had setInerval here....
	    global.onhashchange = function () {
	        if (global.location.hash !== _hash) {
	            global.location.hash = _hash;
	        }
	    };

	    global.onload = function () {
	        
	    noBackPlease();

	    // disables backspace on page except on input fields and textarea..
	    document.body.onkeydown = function (e) {
	            var elm = e.target.nodeName.toLowerCase();
	            if (e.which === 8 && (elm !== 'input' && elm  !== 'textarea')) {
	                e.preventDefault();
	            }
	            // stopping event bubbling up the DOM tree..
	            e.stopPropagation();
	        };
	    
	    };

	})(window);		
</script>

<?php

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


<div id='progress'>
 <div id='progressinside2'>
 </div>
</div>
<br />
 <div id='progresstext1'>
 1. <?php echo $lang['avalista']; ?>
 </div>

 <div id='progresstext2'>
 2. <?php echo $lang['member-contract']; ?>
 </div>
 
 <div id='mainbox'>
  <div id='contractholder'>
<?php

	$file = "_club/_$domain/contract.php";
	
	if (file_exists($file)) {
		include $file;
	}
	
?>
<br /><br /><br />
<center>
<h3><?php echo $lang['birthdate']; ?></h3>
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" placeholder="dd" value="<?php echo $day; ?>" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" placeholder="mm" value="<?php echo $month; ?>" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br /><br />
   
<?php if ($_SESSION['sigtablet'] == 0) { ?>
	
	
<strong>
 <a href="#sig"><h3><?php echo $lang['your-signature']; ?></h3></a>
</strong>
<br />
<a name="sig"></a>
<div id="signatureSet">
 <div id="dd_signaturePadWrapper"></div>
</div>
<br />

	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox1"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['new-member-confirm']; ?>
	  <input type="checkbox" name="accept" id="savesig"/>
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br /><br />
</center>
</div>

<?php } else if ($_SESSION['sigtablet'] == 2) { ?>

<h3><?php echo $lang['your-signature']; ?></h3><br />
<canvas id="cnv" name="cnv" width="500" height="150" onclick="javascript:onSign()" style="border: 2px solid #a80082;"></canvas><br /><br />

<input id="button1" name="ClearBtn" type="button" value="Limpiar" onclick="javascript:onClear()" style="margin-right: 10px; width: 80px;" />

<input id="button2" name="DoneBtn" type="button" value="Finalizar" onclick="javascript:onDone()" style="margin-left: 10px; width: 80px;" /><br />

<br />
<br />

 <table>
  <tr>
   <td><input type="checkbox" name="accept" id="savesig" style="width: 12px;" /></td>
   <td><?php echo $lang['new-member-confirm']; ?><br />
   <span id="errorBox1"></span></td>
  </tr>
 </table>
</center><br />
<INPUT TYPE=HIDDEN NAME="bioSigData">
<INPUT TYPE=HIDDEN NAME="sigImgData">
<INPUT TYPE=HIDDEN NAME="sigStringData" />
<INPUT TYPE=HIDDEN NAME="sigImageData" />

<?php } ?>
</div>
</div>
<center><span id="errorBox"></span><br />
	 <button name='oneClick' class='oneClick' type="submit"><?php echo $lang['submit']; ?></button><br /><br /><br /></center>
	</form>



<?php } displayFooter(); ?>
