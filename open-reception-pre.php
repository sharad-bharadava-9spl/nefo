<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-closing.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
		
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	unset($_SESSION['noCompare']);
	unset($_SESSION['firstOpening']);
		
	// "Separate closing" only available for SHIFTS.
	if ($_SESSION['openAndClose'] < 3) {
		
		$_SESSION['errorMessage'] = $lang['open-day-not-using-open'];
		pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-openday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();

	} else {
		
		// Check if last closing was a day or shift!
		$closingLookup = "SELECT closingtime, closingid, dayOpened, 'day' AS type FROM recclosing UNION ALL SELECT closingtime, closingid, shiftOpened AS dayOpened, 'shift' AS type FROM recshiftclose ORDER BY closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$closingLookup");
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
			
			// Check if an Opening exists. If not, this is the first ever opening.
			$openingLookup = "SELECT openingid, openingtime FROM recopening ORDER BY openingtime DESC";
			try
			{
				$result = $pdo3->prepare("$openingLookup");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
				
				
	
		
			// What if no opening exists? Means this is first ever opening.
			if (!$data) {
				
				$_SESSION['errorMessage'] = $lang['no-closing-data-found-continue'];
				$_SESSION['firstOpening'] = 'true';
				$_SESSION['noCompare'] = 'true';

				// Launch manual process using no comparison
				header("Location: open-reception-shift.php?noComp");
				exit();
			
			} else {
				
				// Opening DOES exist, but no closings yet. Throw error.
				$row = $data[0];
					$openingtime = date("d/m/y h:i", strtotime($row['openingtime']));
				
				$_SESSION['errorMessage'] = $lang['rec-opened-not-closed1'] . $openingtime . $lang['rec-opened-not-closed2'];
				pageStart($lang['open-reception'], NULL, $confirmLeave, "pcloseday", "", $lang['open-reception'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				exit();
				
			}

		}
		
		$row = $data[0];
			$openingtime = date("d/m/y h:i", strtotime($row['closingtime']));
			$type = $row['type'];
			$dayOpened = $row['dayOpened'];
		
		if ($dayOpened > 0) {
				$_SESSION['errorMessage'] = $lang['rec-opened-not-closed1'] . $lang['rec-opened-not-closed2'];
				pageStart($lang['open-reception'], NULL, $confirmLeave, "pcloseday", "", $lang['open-reception'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				exit();
		}
			
			
			header("Location: open-reception-shift.php");
			exit();
			
		
	}
