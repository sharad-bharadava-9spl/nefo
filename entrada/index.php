<?php 

	session_start();
	
	if (isset($_GET['domain'])) {
		$_SESSION['domain'] = $_GET['domain'];
	}
	
	require_once '../cOnfig/connection-tablet.php';
	require_once '../cOnfig/view-nohead.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	// Check if a chip was scanned
	if (isset($_POST['cardid'])) {
		
		$cardid = $_POST['cardid'];
		
		// Query to look up user
		$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.day, u.month, u.year, u.usageType, u.cardid, u.paidUntil, ug.userGroup, ug.groupName, u.credit, u.banComment, u.photoext, u.bajaDate, u.exento FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.cardid = '$cardid'";
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
			
		if ($data) {
			
			$row = $data[0];
				$user_id = $row['user_id'];
				$memberno = $row['memberno'];
				$registeredSince = $row['registeredSince'];
				$membertime = date("M y", strtotime($registeredSince));
				$userGroup = $row['userGroup'];
				$groupName = $row['groupName'];
				$first_name = $row['first_name'];
				$last_name = $row['last_name'];
				$day = $row['day'];
				$month = $row['month'];
				$year = $row['year'];
				$usageType = $row['usageType'];
				$cardid = $row['cardid'];
				$paidUntil = $row['paidUntil'];
				$userCredit = $row['credit'];
				$banComment = $row['banComment'];
				$photoext = $row['photoext'];
				$dniext1 = $row['dniext1'];
				$dniext2 = $row['dniext2'];
				$exento = $row['exento'];
				$bajaDate = date('d-m-y', strtotime($row['bajaDate']));

			
			// Register visit in or out
			$selectRows = "SELECT COUNT(visitNo) FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
			$rowCount = $pdo3->query("$selectRows")->fetchColumn();
			
			// Lookup user's last visit:
			$lastVisit = "SELECT visitNo, completed, scanin FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
			try
			{
				$result = $pdo3->prepare("$lastVisit");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$completed = $row['completed'];
				$visitNo = $row['visitNo'];
				$scanin = $row['scanin'];
				$scaninCompare = date('d-m-Y', strtotime($scanin));
				
				$visitTime = date('Y-m-d H:i:s');
				$visitTimeCompare = date('d-m-Y', strtotime($visitTime));
				
				tzo();
				$visitTimeReadable = date('H:i');
				
			if ($rowCount == 0) {

				// First ever visit. Sign in user.
				$query = sprintf("INSERT INTO newvisits (userid, scanin) VALUES ('%d', '%s');",
				  $user_id, $visitTime);
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
						
				$_SESSION['successMessage'] = $lang['member-entered'] . " " . $visitTimeReadable . ".";
				
			
			// If user is already signed in, we try to sign him out
			} else if ($completed == 0) {

				// Determine duration
				$minutesOfVisit = round(abs(strtotime($scanin) - strtotime($visitTime)) / 60,2);
				
				// Check if he's stayed for 15 minutes
				if ($minutesOfVisit < 10) {
					
					$_SESSION['errorMessage'] = $lang['global-member'] . " " . $lang['left-at'] . " " . $visitTimeReadable. " (" . number_format($minutesOfVisit,0) . " minutos).<br /><strong>No han pasado 10 minutos desde tu entrada!</strong>";
					$query = "UPDATE newvisits SET scanout = '$visitTime', duration = $minutesOfVisit, completed = 1, warning = 1 WHERE visitNo = $visitNo";
					
				// If his last sign in was yesterday (meaning he forgot to sign out)
				} else if (strtotime($scaninCompare) < strtotime($visitTimeCompare)) {
					
					$_SESSION['errorMessage'] = $lang['member-entered'] . " " . $visitTimeReadable . ".<br />&iexcl;En la última salida, olvidaste escanear el llavero!";
					$query = "UPDATE newvisits SET scanout = '$visitTime', duration = $minutesOfVisit, completed = 1, warning = 2 WHERE visitNo = $visitNo";
					
					// Also sign him in!
					$query2 = sprintf("INSERT INTO newvisits (userid, scanin) VALUES ('%d', '%s');",
				  		$user_id, $visitTime);
		try
		{
			$result = $pdo3->prepare("$query2")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
						
				} else {
			
					$_SESSION['successMessage'] = $lang['global-member'] . " " . $lang['left-at'] . " " . $visitTimeReadable . " (" . number_format($minutesOfVisit,0) . " minutos).";
					$query = "UPDATE newvisits SET scanout = '$visitTime', duration = $minutesOfVisit, completed = 1 WHERE visitNo = $visitNo";
				
				}
				
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
			

			// User has visited before, is signed out, so we sign him in			
			} else {
				
				$query = sprintf("INSERT INTO newvisits (userid, scanin) VALUES ('%d', '%s');",
				  $user_id, $visitTime);
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
					
				$_SESSION['successMessage'] = $lang['member-entered'] . " " . $visitTimeReadable . ".";
				
			}
			
	}
			
			header("Location: index.php");
			exit();		
	} else {
		
?>

<html>
 <head>
  <title>Acceso</title>
  <script src="../scripts/jquery-1.10.2.min.js"></script>
  <link href="../css/styles14.css" rel="stylesheet" type="text/css" />
 </head>  
 <body id="userlookup">
  <center>
   <div id="wrapper" >
    <div id="header">
     <br /><br /><img src="../images/logo.png" />
    </div> <!-- end HEADER -->
    <div id='main'>
     <br /><br /><br />
     <h2 style="font-size: 20px;">Pasa tu llavero para continuar.</h2>
     <br />
     <img src="../images/llavero.png" />
	 <form onsubmit="oneClick.disabled = true; return true;" id="registerForm" action="" autocomplete="off" method="POST">
      <input type="text" name="cardid" id="focus" maxlength="10" autofocus value="" style="border: 0; outline: 0; box-shadow: 0 0 0 0; color: #ffffff" /><br /><br />
      <button name='oneClick' type="submit" style="display: none;" >Accept</button>
     </form>
    </div> <!-- END MAIN -->
   </div> <!-- END WRAPPER-->
 </center>
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
 </body>
</html>

<?php } displayFooter();