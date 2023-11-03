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
	
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomemberid'],"");
	}
	
	// Check if a chip was scanned
	if (isset($_POST['barcode'])) {
		
		$barcode = $_POST['barcode'];
		
		$userDetails = "SELECT purchaseid, barCode, category FROM b_purchases WHERE barCode = '$barcode'";
		try
		{
			$result = $pdo3->prepare("$userDetails");
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
			
			$_SESSION['errorMessage'] = "Este codigo de barra no esta registrado!<br /><br /><br />";
			
		} else {

			$row = $data[0];
				$purchaseid = $row['purchaseid'];
				$category = $row['category'];
				
			// On success: redirect.
			header("Location: barcode-bar-2.php?user_id=$user_id&purchaseid=$purchaseid&category=$category&firstscan=yes");
			exit();

		}
		

	}

	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit, photoExt FROM users WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$paidUntil = $row['paidUntil'];
		$userGroup = $row['userGroup'];
		$credit = $row['credit'];
		$photoExt = $row['photoExt'];

		
	pageStart("CCS", NULL, $testinput, "pmembership", NULL, NULL, $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo "<center><div class='actionbox-np2'><div class='boxcontent'><img src='{$google_root}images/members/$userid.$photoExt' class='salesPagePic' /><h5>#$memberno - $first_name $last_name</h5><div class='clearfloat'></div><span class='creditDisplay'>{$lang['global-credit']}: <span class='creditAmount'>" . number_format($credit,2) . "</span></span></div></div></center><br />";
		

		
?>

<script>

$(document).ready(function() {
    $("#focus").focus().bind('blur', function() {
        $(this).focus();
    }); 

    $("html").click(function() {
        $("#focus").val($("#focus").val()).focus();
    });

    //disable the tab key
    $(document).keydown(function(objEvent) {
        if (objEvent.keyCode == 9) {  //tab pressed
            objEvent.preventDefault(); // stops its action
       }
    })      
});
</script>


<center>
 <form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="" autocomplete="off" method="POST">
  <input type="text" name="barcode" class='defaultinput' id="focus" maxlength="80" autofocus placeholder="Codigo de barra" /><br />
  <button name='oneClick' class='cta1' type="submit" style="visibility: hidden;">Submit</button>
 </form>
</center>
