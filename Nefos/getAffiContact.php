<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);

	$affiliate_id = $_GET['aff_id'];
	$checked = $_GET['checked'];

	// get customer numbers from affiliate
if(isset($checked) && $checked == "new"){
		$selectClub  = "SELECT number,shortName from customers WHERE affiliation=".$affiliate_id;
		try
		{
			$aff_results = $pdo3->prepare("$selectClub");
			$aff_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
				echo "<option value=''>Select Customer/club</option>";
					while($affRow = $aff_results->fetch()){
						echo "<option value='".$affRow['number']."'>".$affRow['shortName']."</option>";
					} 
		
	}
	else{
	$selectClub  = "SELECT number from customers WHERE affiliation=".$affiliate_id;
		try
		{
			$aff_results = $pdo3->prepare("$selectClub");
			$aff_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		while($affRow = $aff_results->fetch()){
			$customer_numbers[] = $affRow['number']; 
		}
		$cust_nums = implode(",", $customer_numbers);
		if(empty($cust_nums)){
			$cust_nums = -1;
		}
	$getContacts = "SELECT a.id, a.name, b.shortName from contacts a,customers b WHERE a.customer IN ($cust_nums) AND a.customer = b.number";

	try
	{
		$results = $pdo3->prepare("$getContacts");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	echo "<option value=''>Select Contact Person</option>";
	while($row = $results->fetch()){
		echo "<option value='".$row['id']."'>".$row['name']." (".$row['shortName'].")</option>";
	}
}