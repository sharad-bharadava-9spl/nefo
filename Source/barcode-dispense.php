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
	
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomemberid'],"");
	}
	
	$domain = $_SESSION['domain'];
	
	// Check if a chip was scanned
	if (isset($_POST['barcode'])) {
		
		$barcode = $_POST['barcode'];
		
		$userDetails = "SELECT purchaseid, barCode, category FROM purchases WHERE barCode = '$barcode'";
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
			
			
			
		if (!$data || $barcode == '') {
			
			$_SESSION['errorMessage'] = "Este codigo de barra no esta registrado!";
			
		} else {
	
			$row = $data[0];
				$purchaseid = $row['purchaseid'];
				$category = $row['category'];
				
			// On success: redirect.
			header("Location: barcode-dispense-2.php?user_id=$user_id&purchaseid=$purchaseid&category=$category&firstscan=yes");
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
	
	echo "<center><div id='profilearea'><img src='images/_$domain/members/$user_id.$photoExt' class='salesPagePic' /><h4>#$memberno - $first_name $last_name</h4><div class='clearfloat'></div><span class='creditDisplay'>{$lang['global-credit']}: <span class='creditAmount'>" . number_format($credit,2) . "</span></span></div></center><br />";
		

		
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
  <input type="text" name="barcode" id="focus" maxlength="80" autofocus placeholder="Codigo de barra" /><br />
  <button name='oneClick' type="submit" style="visibility: hidden;">Submit</button>
 </form>
</center>