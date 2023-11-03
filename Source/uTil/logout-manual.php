<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
	
	// Check sys settings if they use worker tracking or not
	// Show prompt: Is this the end of your shift?
	if ($_SESSION['workertracking'] == 0) {
		
		header('Location: logout.php');
		exit();
		
	}
	
	// Check if user has an open shift, if they don't, just log'em out
	$query = "SELECT user_id FROM logins WHERE DATE(time) = DATE(NOW() - INTERVAL 3 HOUR) AND type = 2 AND user_id = {$_SESSION['user_id']} ORDER BY time DESC";
	try
	{
		$result = $pdo3->prepare("$query");
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
	
		header('Location: logout.php');
		exit();
		
	}
	
	if (isset($_GET['signout'])) {
		
			$query = sprintf("SELECT first_name, user_id, memberno, email, userGroup, workStation, domain FROM users WHERE user_id = '%d';",
			$_SESSION['user_id']);
			try
			{
				$result = $pdo3->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user1: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
				$result = $result->fetch();
						$user_id = $result['user_id'];
						$username = $result['email'];
						$memberno = $result['memberno'];
						$userGroup = $result['userGroup'];
						$first_name = $result['first_name'];
						$domain = $result['domain'];
						$workStationAccess = $result['workStation'];
					
					
					$loginTime = date('Y-m-d H:i:s');
					$workUntil = date('Y-m-d') . ' ' . $_POST['timeFull'];
					
					// Find last scanin for this user
					$query = "SELECT id FROM logins WHERE user_id = $user_id ORDER BY time DESC";
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
						$id = $row['id'];
	
					// $query = "INSERT INTO logins (time, user_id, type, success) VALUES ('$loginTime', '$user_id', 2, 1)";
					$query = "UPDATE logins SET type = 2, success = 1, email = '$loginTime' WHERE id = $id";
					try
					{
						$result = $pdo3->prepare("$query");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user2: ' . $e->getMessage();
							echo $error;
							exit();
					}
					
					header("Location: logout.php");
		
		
	}

	
	$query = "SELECT user_id FROM logins WHERE DATE(time) = DATE(NOW() - INTERVAL 3 HOUR) AND type = 1 AND user_id = {$_SESSION['user_id']}";
	try
	{
		$result = $pdo3->prepare("$query");
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


			$query = sprintf("SELECT first_name, last_name, user_id, memberno, email, userGroup, workStation, domain, photoExt FROM users WHERE user_id = '%d';",
			$_SESSION['user_id']);
			try
			{
				$result = $pdo3->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user1: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
		$row = $result->fetch();
				$user_id = $row['user_id'];
				$username = $row['email'];
				$memberno = $row['memberno'];
				$userGroup = $row['userGroup'];
				$first_name = $row['first_name'];
				$last_name = $row['last_name'];
				$domain = $row['domain'];
				$photoExt = $row['photoExt'];
				$workStationAccess = $row['workStation'];
			
	pageStart($lang['working-hours'], NULL, $timePicker, "changeuser", NULL, $lang['working-hours'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	echo "<center><div id='profilearea' style='font-size: 18px;'><img src='../images/_$domain/members/$user_id.$photoExt' class='salesPagePic' /><br /><h4>$first_name $last_name</h4><br />{$lang['end-shift-or-not']}?</div></center>";
	
	echo "<br /><center><a href='?signout' class='cta'>{$lang['global-yes']}</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='logout.php' class='cta'>{$lang['global-no']}</a></center>";
	
	} else {
		
					header("Location: logout.php");
					
	}

