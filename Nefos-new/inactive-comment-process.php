<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	
	$period = '202104';

	$response = [];
	if ($_POST['confirmed'] == 'yes') {
		
		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		$second = $_POST['second'];
		$client = $_POST['client'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$nowDate = date('Y-m-d H:i');
		
		$registertime = "$year-$month-$day $hour:$minute:$second";

		$query = sprintf("INSERT INTO inactivecomments (time, customer, comment, operator) VALUES ('%s', '%s', '%s', '%d');",
	  	 $registertime, $client, $comment, $_SESSION['user_id']);
		try
		{
			$result = $pdo2->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$comment = "Added from Inactivity dashboard:<br />$comment";
		
		$day2 = $_POST['day2'];
		$month2 = $_POST['month2'];
		$year2 = $_POST['year2'];
			
		if ($day2 != '' && $month2 != '' && $year2 != '') {
			
			$followuptime = "$year2-$month2-$day2";

			$query = "UPDATE customers SET inactive_followup = '$followuptime' WHERE number = '$client'";
			try
			{
				$result = $pdo2->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		}

		// Check if we should add cutoff comment as well
		if ($_POST['addcutoff'] == 1) {
			
			$query = "SELECT invno FROM invoices WHERE customer = '$client' AND period = '$period'";
			try
			{
				$results = $pdo->prepare("$query");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			while ($row = $results->fetch()) {
				
				$invno = $row['invno'];			
			
				$query = sprintf("INSERT INTO cutoffcomments (time, customer, comment, operator, period, invno) VALUES ('%s', '%s', '%s', '%d', '%s', '%s');",
				  	 $nowDate, $client, $comment, $_SESSION['user_id'], $period, $invno);
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
				
			}

			
		}
		// show comments
		$query = "SELECT time, comment, operator FROM inactivecomments WHERE customer = '$client' ORDER BY time DESC";
		try
		{
			$result = $pdo2->prepare("$query");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		

		$comments = '';
			
		foreach ($data as $rowC) {
		
			$commenttime = date("d/m/Y H:i:s", strtotime($rowC['time']));
			$comment = $rowC['comment'];		
			$operator = $rowC['operator'];	
				
			// Look up user
			$query = "SELECT first_name, last_name FROM users WHERE user_id = '$operator'";
			try
			{
				$result = $pdo2->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$first_name = $row['first_name'];
				$last_name = $row['last_name'];
			
			$comments .= "<strong><span style='font-size: 16px;'>$first_name $last_name</span><br />$commenttime</strong><br />$comment<br /><br />";
				
		}
			
		// On success: redirect.
		$response['success'] = "Comment added succesfully!";
		$response['comments'] = $comments;
		echo json_encode($response);
		exit();
		
	}