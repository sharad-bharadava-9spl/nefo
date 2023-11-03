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
		$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.day, u.month, u.year, u.usageType, u.cardid, u.paidUntil, ug.userGroup, ug.groupName, u.credit, u.banComment, u.photoext, u.bajaDate FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.cardid = '$cardid'";
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
			
		
		// Does user ID exist?
		if (!$data) {
			
			echo <<<EOD
<html>
 <head>
  <title>Acceso</title>
  <script src="../scripts/jquery-1.10.2.min.js"></script>
  <link href="../css/styles14.css" rel="stylesheet" type="text/css" />
 </head>
 <body id="userlookup">
<script>
window.onload = function(){
	
setTimeout(function() {
	window.location.replace("index.php");
}, 2000);
  
(function(){
  var counter = 3;

  setInterval(function() {
    counter--;
    if (counter >= 0) {
      span = document.getElementById('count');
      span.innerHTML = counter;
    }  
  }, 1000);
    
})();


  
}</script>
  <center>
   <div id="wrapper">
   


    <div id="header">
    <img src="../images/logo.png" />
    </div> <!-- end HEADER -->
     <br /><br />
    <div id='main' style="	display: inline-block;
	color: white;
	font-weight: 600;
	border: 2px solid #ff0000;
	margin-left: auto;
	margin-right: auto;
	min-width: 400px;
	padding: 10px;
	background-color: #ff7d7d;	
	margin: 2px 0 15px 0;
	color: white !important;
	font-size: 16px;
">
     <br />
     Este llavero no esta registrado!!<br />
     (<span id='count'>3</span>)
     <br /><br />
   </div> <!-- END MAIN -->
   </div> <!-- END WRAPPER-->
 </center>
 </body>
</html>
EOD;

exit();	
   		
		} else {
			
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
			
			
$countdownScript = <<<EOD
window.onload = function(){
	
	setTimeout(function() {
		window.location.replace("index.php");
	}, 1000);
	  
	(function(){
	  var counter = 3;
	
	  setInterval(function() {
	    counter--;
	    if (counter >= 0) {
	      span = document.getElementById('count');
	      span.innerHTML = counter;
	    }  
	  }, 1000);
	    
	})();
}
EOD;
	
			pageStart($lang['title-memberprofile'], NULL, $countdownScript, "pprofilenew", NULL, $lang['member-profilecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
			
			?>
			
			<center><span id="count" style="color: #5aa242; font-size: 18px; font-weight: 600;">3</span></center><br />
<div class="overview">
 <span class="profilepicholder"><img class="profilepic" src="../images/_<?php echo $domain; ?>/members/<?php echo $user_id . "." . $photoext; ?>" /></span>
 <span class="profilefirst">#<?php echo $memberno . " - " . $first_name . " " . $last_name; ?> (<?php echo $membertime; ?>)</span>
 <br /><br />
<div id="memberNotifications"> <span class="profilethird">
<?php 

	if ($userCredit < 0) {
		$userCreditDisplay = 0;
		$userClass = 'negative';
	} else {
		$userCreditDisplay = $userCredit;
	}
	

	echo "<span class='creditDisplay'>Credit: <span class='creditAmount $userClass'>" . number_format($userCreditDisplay,2) . " ".$_SESSION['currencyoperator']."</span></span><br /><br />";


	// If member is banned
	if ($userGroup == 7) {
		
		// Banned 
		echo "<span class='banDisplay'><span class='banHeader'>*** {$lang['bannedC']} !! ***</span><br /><strong>{$lang['reason']}:</strong><br />" . $banComment . "</span>";
		
	} else {
	
	if ($userGroup == 5) {  // show Member w/ expiry
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if ($memberExp == $timeNow) {
			echo "<img src='../images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 5px;' /> <span class='yellow'>" . $lang['member-expirestoday'] . "</span>";
	  	} else if ($memberExp > $timeNow) {
		  	echo $lang['member-memberuntil'] . ": $memberExpReadable";
		} else {
		  	echo "<img src='../images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-expiredon'] . ": $memberExpReadable</span>";
		  	
		  	if ($paymentWarning == '1') {
		  	echo "<br /><img src='../images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px;' /> <img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: -15px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-receivedwarning'] . ": $paymentWarningDateReadable</span>";
		  	}
		  	
		}
		
	} else if ($userGroup == 9) {
		
		echo $groupName . "&nbsp;($bajaDate)";
		
	} else {
		
		echo $groupName . "&nbsp;";
		
	}
	

	if ($usageType == 'Medicinal') {
		echo "<br /><img src='../images/medical-22.png' lass='warningIcon' style='margin-bottom: -3px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['medicinal-user']}</span>";
	}
	
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		echo "<br /><img src='../images/cake-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>&iexcl;Feliz cumpleaños!</span>";
	}	
	
}

	echo "</span></div></div> <!-- END OVERVIEW --><div class='clearfloat'></div><br />";
	
	}
	
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