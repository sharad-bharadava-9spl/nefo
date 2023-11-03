<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	session_start();



	pageStart("Inactivity report", NULL, $memberScript, "pmembership", NULL, "Inactivity report", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	  
	
$sql = $pdo->query('SHOW DATABASES');
$getAllDbs = $sql->fetchALL(PDO::FETCH_ASSOC);

$i = 1;
foreach ($getAllDbs as $DB) {
	
	$database = $DB['Database'];
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_irena' && $database != 'ccs_masterdb' && $database != 'ccs_andyclub1' && $database != 'ccs_andyclub2' && $database != 'ccs_andysclub3' && $database != 'ccs_andyshouse' && $database != 'ccs_berryscorner' && $database != 'ccs_berryshole' && $database != 'ccs_ccstest' && $database != 'ccs_demo' && $database != 'ccs_demo1' && $database != 'ccs_demo2' && $database != 'ccs_demo3' && $database != 'ccs_demo4' && $database != 'ccs_demo5' && $database != 'ccs_demo6' && $database != 'ccs_demo7' && $database != 'ccs_g13viejo' && $database != 'ccs_iuhhfisud' && $database != 'ccs_jazzhuset' && $database != 'ccs_jazzhusetnano' && $database != 'ccs_kjell' && $database != 'ccs_weedbunny' && $database != 'ccs_testtest' && $database != 'ccs_levelclub' && $database != 'ccs_levelclub1' && $database != 'ccs_levelclub2') {
		
		$domain = substr($database,4);
		
		$query = "SELECT db_pwd, customer, warning FROM db_access WHERE domain = '$domain'";
		try
		{
			$result = $pdo->prepare("$query");
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
				$db_pwd = $row['db_pwd'];
				$customer = $row['customer'];
				$warning = $row['warning'];
	
			$db_name = "ccs_" . $domain;
			$db_user = $db_name . "u";
	
			try	{
		 		$pdo6 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
		 		$pdo6->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 		$pdo6->exec('SET NAMES "utf8"');
			}
			catch (PDOException $e)	{
		  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
		
		 		echo $output;
		 		exit();
			}
			
			// Check last log, if more than 3 days then run queries
			$selectUsersU = "SELECT logtime FROM log ORDER BY logtime DESC LIMIT 1";
			try
			{
				$result = $pdo6->prepare("$selectUsersU");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$lastLog = date("Y-m-d", strtotime($row['logtime']));
				$lastLogFull = date("d-m-Y H:i", strtotime($row['logtime']));
				
			$dateNow = date("Y-m-d");
			
			$date1 = new DateTime("$lastLog");
			$date2 = new DateTime("$dateNow");
			$interval = $date1->diff($date2);
			
			$daysSinceLastLog = $interval->days;
			
			if ($daysSinceLastLog == 3) {
				
				$selectUsersU = "SELECT number, shortName FROM customers WHERE number = '$customer'";
				try
				{
					$result = $pdo2->prepare("$selectUsersU");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$rowX = $result->fetch();
					$number = $rowX['number'];
					$shortName = $rowX['shortName'];
					
				// Date today
				$dateNow = date("Y-m-d");
				
				// Date minus 7 days
				$dateFirst = date("Y-m-d", strtotime($dateNow . " -7 days"));
				
				// Loop from that day, run 7 times
				$i = 0;
				$mailHeaderRow = "";
				$mailBodyRow = "";
				
				while ($i < 7) {
					
					$opDate = date("Y-m-d", strtotime($dateFirst . " +$i days"));
					$opDateRead = date("d/m", strtotime($dateFirst . " +$i days"));
					
					$query = "SELECT COUNT(id) FROM log WHERE DATE(logtime) = '$opDate'";
					try
					{
						$result = $pdo6->prepare("$query");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$logsPeriod = $row['COUNT(id)'];
						
					$mailHeaderRow .= "<td style='padding: 5px; border: 1px solid #ccc;'><strong>$opDateRead</strong></td>";
					$mailBodyRow .= "<td style='padding: 5px; border: 1px solid #ccc;'><center>$logsPeriod</center></td>";
					
					$i++;
					
				}
					
				// Prepare mail
				$mailBody = <<<EOD
	
Hi admin,<br /><br />
It has now been 3 days since the last log operation was registered in $shortName!<br /><br />
Log operations the last 7 days:<br /><br />
<table>
 <tr>
  $mailHeaderRow
 </tr>
 <tr>
  $mailBodyRow
 </tr>
</table><br />
Please contact the client to find out what's going on.<br /><br />
All the best,<br />
The Nefos team.

EOD;

				// Send e-mail to admin
				try {
					
					// Send e-mail(s)
					require_once '../PHPMailerAutoload.php';
					
					$mail = new PHPMailer(true);
					$mail->CharSet = 'UTF-8';
					$mail->SMTPDebug = 0;
					$mail->Debugoutput = 'html';
					$mail->isSMTP();
					$mail->Host = "mail.cannabisclub.systems";
					$mail->SMTPAuth = true;
					$mail->Username = "info@cannabisclub.systems";
					$mail->Password = "Insjormafon9191";
					$mail->SMTPSecure = 'ssl'; 
					$mail->Port = 465;
					$mail->setFrom('info@cannabisclub.systems', 'CCSNube');
					$mail->addAddress("andreas@cannabisclub.systems", "CCS");
					$mail->addAddress("info@cannabisclub.systems", "CCS");
					$mail->Subject = "Club inactivity: $shortName";
					$mail->isHTML(true);
					$mail->Body = $mailBody;
					$mail->send();

				}
				catch (Exception $e)
				{
				   echo $e->errorMessage();
				   $_SESSION['errorMessage'] = "Error sending mail!!";
				}

				echo "$mailBody<br />";

	  		} // end 3 days since last log


		} // end 'if data'
	
	}
	
}

echo "FIN";

displayFooter();