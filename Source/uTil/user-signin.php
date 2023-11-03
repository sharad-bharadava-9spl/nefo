<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the user ID
	$user_id = $_GET['user_id'];
	
	$visitTime = date('Y-m-d H:i:s');
	tzo();
	$visitTimeReadable = date('H:i');
	
	if ($_SESSION['domain'] == 'summitlounge') {
		
		if (isset($_GET['fee'])) {
			
			$fee = $_GET['fee'];
			
			if ($_GET['bank'] == 'true') {
				$bank = 2;
			} else {
				$bank = 1;
			}
			
			try
			{
				$result = $pdo3->prepare("INSERT INTO newvisits (userid, scanin, amount, paidTo) VALUES ($user_id, '$visitTime', '$fee', $bank)")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			$_SESSION['successMessage'] = $lang['member-entered'] . ": " . $visitTimeReadable . ".";
	 		header("Location: ../profile.php?user_id={$user_id}");
	 		
	 		exit();
			
		}
		
		// Check cuota type and user Group to determine if they should pay
		$query = "SELECT cuota, userGroup FROM users WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$cuota = $row['cuota'];
			$userGroup = $row['userGroup'];
		
		// Should not pay visits	
		if ($cuota == 0 || $cuota == 2 || $userGroup < 4) {
			
			try
			{
				$result = $pdo3->prepare("INSERT INTO newvisits (userid, scanin) VALUES ($user_id, '$visitTime')")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			$_SESSION['successMessage'] = $lang['member-entered'] . ": " . $visitTimeReadable . ".";
			header("Location: ../profile.php?user_id=$user_id");
        	exit();
			
		} else {
			
			pageStart("New visit", NULL, $deleteNoteScript, "newchip", NULL, "New visit", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
	
			echo "<center><span class='ctalinks2'>
	           <a class='cta' href='?user_id=$user_id&fee=5'><br />5&#36;<br /> {$lang['cash']}</a>";
	           
	       	if ($_SESSION['bankPayments'] == 1) {
	           echo "<a class='cta' href='?user_id=$user_id&fee=5&bank=true'><br />5&#36;<br /> {$lang['bank-card']}</a>";
	       	}
			echo "<center><span class='ctalinks2'>
	           <a class='cta' href='?user_id=$user_id&fee=10'><br />10&#36;<br /> {$lang['cash']}</a>";
	           
	       	if ($_SESSION['bankPayments'] == 1) {
	           echo "<a class='cta' href='?user_id=$user_id&fee=10&bank=true'><br />10&#36;<br /> {$lang['bank-card']}</a>";
	       	}
			echo "<center><span class='ctalinks2'>
	           <a class='cta' href='?user_id=$user_id&fee=15'><br />15&#36;<br /> {$lang['cash']}</a>";
	           
	       	if ($_SESSION['bankPayments'] == 1) {
	           echo "<a class='cta' href='?user_id=$user_id&fee=15&bank=true'><br />15&#36;<br /> {$lang['bank-card']}</a>";
	       	}
			echo "<center><span class='ctalinks2'>
	           <a class='cta' href='?user_id=$user_id&fee=20'><br />20&#36;<br /> {$lang['cash']}</a>";
	           
	       	if ($_SESSION['bankPayments'] == 1) {
	           echo "<a class='cta' href='?user_id=$user_id&fee=20&bank=true'><br />20&#36;<br /> {$lang['bank-card']}</a>";
	       	}
	       	echo "
	           
	           </span>
	          </center>";
          
        	exit();
		}
        	exit();
     }

		
	try
	{
		$result = $pdo3->prepare("INSERT INTO newvisits (userid, scanin) VALUES ($user_id, '$visitTime')")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
		$_SESSION['successMessage'] = $lang['member-entered'] . ": " . $visitTimeReadable . ".";
 		header("Location: ../mini-profile.php?user_id={$user_id}");