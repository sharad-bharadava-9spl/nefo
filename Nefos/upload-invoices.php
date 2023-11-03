<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	session_start();
		

	pageStart($lang['index-members'], NULL, $memberScript, "pmembership", NULL, $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	  
	// Look up all invoices in folder
	$files = scandir("../../ccsnubev2_com/invoices/");
	
	$i = 0;
	
	foreach ($files as $file) {
		
		if (strpos($file, 'pdf') !== false) {
			$customernumber = substr($file, 0, 5);
			
			$query = "SELECT domain FROM db_access WHERE customer = '$customernumber'";
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
				$domain = $row['domain'];
				
			rename("../../ccsnubev2_com/invoices/" . $file, "../../ccsnubev2_com/_club/_$domain/invoices/$file");
			
			echo "MOVED: " . "../../ccsnubev2_com/invoices/" . $file, "../../ccsnubev2_com/_club/_$domain/invoices/$file" . "<br />";
			$i++;
		}
	}
	
	echo "<br />Finished. $i invoices uploaded.<br /><input type='checkbox' style='width: 15px;' /> Click here to confirm that the above number matches the CCS CMR.<br /><input type='checkbox' style='width: 15px;' /> Click here to confirm that the above number matches the 'CCS invoicing' file.<br /><strong style='color: red;'>REMEMBER TO UPLOAD INVOIES FOR Faded and MariaMaria to The Plug - manually!</strong><br /><br />&nbsp;<strong style='color: red;'>CHECK root 'invoices' folder for un-uploaded invoices! They don't have an 'invoice' folder in their directory! Help mij!</strong><br /><br />&nbsp;";


displayFooter();