<?php

	session_start();
	
	if (isset($_GET['domain'])) {
		
		if ($_GET['domain'] == 'thegreenhousetenerife') {
			$_SESSION['domain'] = 'greenhousetenerife';
		} else {
			$_SESSION['domain'] = $_GET['domain'];
		}
		
	}
	
	require_once 'cOnfig/connection-tablet.php';
	require_once 'cOnfig/view-nohead.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	$domain = $_SESSION['domain'];
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
$('#dd_signaturePadWrapper').click(function(e) {  
        $('#savesig').attr('checked', false)
    });
	    	    	  
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
		innerHTML+="<ul><li><a href=\"dd_signature_process2.php?download="+f+"\" target=\"_blank\">"+f+"</a></li></ul>";
	}
	objParent.appendChild(objDiv);
}

  }); // end ready
EOD;

	$findTempNo = "SELECT chip FROM newscan WHERE type = 99 ORDER BY scanid DESC LIMIT 1";
	try
	{
		$result = $pdo3->prepare("$findTempNo");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	if (!$data) {
		
		$_SESSION['errorMessage'] = $lang['not-ready-to-sign'];
		header("Location: sign-index.php?domain=$domain");
		exit();
		
	}
		
	$row = $data[0];
		$tempNo = $row['chip'];
		$_SESSION['tempNo'] = $tempNo;
	
	pageStart("Statutes", NULL, $validationScript, "pprofile", "statutes", $lang['member-newmembercaps'] . " - Statutes", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>


<script type="text/javascript" src="js/dd_signature_pad2.js"></script>


<center>
<div id="signatureSet" style="margin-top: -25px;">
		<div id="dd_signaturePadWrapper"></div>
	</div>
	<a class='cta1' href='#' id='savesig' style='margin-left: -10px;' ><?php echo $lang['form-accept'] ?></a>
	
</center>



<?php displayFooter(); ?>
