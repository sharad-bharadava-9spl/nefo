<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	$query = "SELECT id, name, telephone, email, club, time, hash FROM contacts WHERE hash <> '' ORDER BY time DESC";
	try
	{
		$results = $pdo2->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($row = $results->fetch()) {
		
		$name = $row['name'];
		$contactid = $row['id'];
		$telephone = $row['telephone'];
		$email = $row['email'];
		$club = $row['club'];
		$registerTime = date("d-m-Y H:i", strtotime($row['time']));
		$hash = $row['hash'];
		
		$viewDetails = '';
		
		// Look up demo behaviour
		$query = "SELECT id, time, action FROM demoviews WHERE hash = '$hash' ORDER BY time ASC";
		try
		{
			$resultsD = $pdo2->prepare("$query");
			$resultsD->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rowD = $resultsD->fetch()) {
			
			// Query, check next ID for THIS hash, if timestamp is the same!
			$id = $rowD['id'];
			$time = $rowD['time'];
			
			$query = "SELECT time FROM demoviews WHERE hash = '$hash' AND id > '$id' ORDER BY time ASC";
			try
			{
				$resultV = $pdo2->prepare("$query");
				$resultV->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$rowV = $resultV->fetch();
				$time2 = $rowV['time'];
				
			if ($time2 == $time) {
				
				$action = 'Forward/rewind';
				
			} else {
				
				$action = $rowD['action'];
				
				if ($action == 'visit') {
					$action = 'Clicked link in e-mail';
				} else if ($action == 'play') {
					$action = 'Played video';
				} else if ($action == 'pause') {
					$action = 'Paused video';
				} else if ($action == 'stop') {
					$action = 'Video ended';
				} else if ($action == 'stop') {
					$action = 'Video ended';
				} else if ($action == 'launch') {
					$action = 'Clicked Launch button';
				}
				
			}
			
				if ($time == $time3) {
					
					if ($action == 'Video ended') {
						
						$time3 = $rowD['time'];
						
						$timeView = date("d-m-Y H:i:s", strtotime($rowD['time']));
						
						$viewDetails .= <<<EOD

 <tr>
  <td>$timeView</td>
  <td>$action</td>
 </tr>

EOD;
					}
					
				} else {
				
				
				$time3 = $rowD['time'];
				
				$timeView = date("d-m-Y H:i:s", strtotime($rowD['time']));
				
				$viewDetails .= <<<EOD

 <tr>
  <td>$timeView</td>
  <td>$action</td>
 </tr>

EOD;
}
			
		}
				
		$details .= <<<EOD

 <tr>
  <td colspan='2' style='font-size: 16px; position: relative; border-bottom: 4px solid #ddd !important;'><a href='https://nefoscloud.com/Nefos/edit-contact.php?demoview&contactid=$contactid'><strong>$name ($club)<br />
  </strong>$email<br />$telephone</a><a href='uTil/re-send.php?hash=$hash' style='position: absolute; top: 15px; right: 15px;'><img src='images/re-send.png' /></a></td>
 </tr>
 <tr>
  <td>$registerTime</td>
  <td>Signed up</td>
 </tr>
 $viewDetails
 <tr>
  <td colspan='2' style='background-color: #eaebe6; border-bottom: 0px;'></td>
 </tr>

EOD;
		
	}
		
	pageStart("Demo views", NULL, $validationScript, "pprofile", NULL, "Demo views", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<table class='default' style='min-width: 500px;'>
 <?php echo $details; ?>
</table>

<?php displayFooter();