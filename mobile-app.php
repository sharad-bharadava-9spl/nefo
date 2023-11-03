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
	
	$script = <<<EOD
	
    $(document).ready(function() {
	    
  	$("#prices").on({
 		"mouseover" : function() {
		 	$("#priceshelp").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#priceshelp").css("display", "none");
	  	}
	});

  	$("#mac").on({
 		"mouseover" : function() {
		 	$("#machelp").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#machelp").css("display", "none");
	  	}
	});

  }); // end ready
EOD;

	pageStart("CCS", NULL, $script, "pindex", "notSelected", "Mobile application", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// Club code
	$query = "SELECT club_code FROM db_access WHERE domain = '{$_SESSION['domain']}'";
	try
	{
		$result = $pdo->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$club_code = $row['club_code'];
		
	// Pending requests
	$query = "SELECT COUNT(id) FROM app_requests WHERE club_name = '{$_SESSION['domain']}' AND allow_request = 1";
	try
	{
		$result = $pdo->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$requests_approved = $row['COUNT(id)'];
		
	// Approved requests
	$query = "SELECT COUNT(id) FROM app_requests WHERE club_name = '{$_SESSION['domain']}' AND allow_request = 0";
	try
	{
		$result = $pdo->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$requests_pending = $row['COUNT(id)'];
		
?>
<center>
 <a href="app-settings.php" class='cta1'>App Settings</a>
 <a href="app-requests.php" class='cta1'>All requests</a>
 <a href="app-notifications.php" class='cta1'>Notifications</a>
 <br />

 <div class='actionbox-np2'>
  <div id='mainboxheader'>
<?php echo $lang['general']; ?>
  </div>

   <div class='boxcontent'>
 <table class='default-plain noborder'>
  <tr>
   <td style='position: relative;'><img src='images/questionmark-new.png' height="14" id="mac" /> Club code:<div id='machelp' class='helpBox'>This is the code your members will have to type into their app to add your club.</div></td>
   <td><?php echo $club_code; ?></td>
  </tr>
  <tr>
   <td style='position: relative;'><img src='images/questionmark-new.png' height="14" id="prices" /> Menu settings:<div id='priceshelp' class='helpBox'>You have three options:<br />- Hide menu<br />- Show menu with prices<br />- Show menu without prices.</div></td>
   <td>Hidden</td>
  </tr>
  <tr>
   <td>Pending app requests:</td>
   <td><?php echo $requests_pending; ?></td>
  </tr>
  <tr>
   <td>Approved members:</td>
   <td><?php echo $requests_approved; ?></td>
  </tr>
 </table>
  </div>
</div>
  <br />
  
 <div class='actionbox-np2' style='width: 400px;'>
  <div id='mainboxheader'>
NOTIFICATIONS
  </div>
  
   <div class='boxcontent'>
   <a href='app-notifications.php'>test</a>
   </div>
</div>

 <div class='actionbox-np2' style='width: 400px;'>
  <div id='mainboxheader'>
PENDING REQUESTS
  </div>
  
   <div class='boxcontent'>
   <a href='app-requests.php'>test</a>
   </div>
</div>

</center>


