<?php
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-print.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the sale ID
	if (isset($_GET['id'])) {
		$donationid = $_GET['id'];
	} else {
		handleError($lang['error-nosaleid'],"");
	}
	
	// Query to look up sale
	$selectSale = "SELECT donationTime, amount, userid FROM donations WHERE donationid = $donationid";
		try
		{
			$result = $pdo3->prepare("$selectSale");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$formattedDate = date("d M H:i", strtotime($sale['donationTime']."+$offsetSec seconds"));
			$saletime = $row['donationTime'];
			$userid = $row['userid'];
			$amount = $row['amount'];
	
	pageStart($lang['print-receipt'], NULL, NULL, "pprint", "Sale", $lang['print-receiptD'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user2: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$first_name = $row['first_name'];
		$memberno = $row['memberno'];
		
		$userLookup = "SELECT operator FROM log WHERE logtime = '$saletime'";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user23: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$operator = $row['operator'];
				
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$operator}";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching usera: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$opname = $row['first_name'];
			$opno = $row['memberno'];
			   
		echo "
<strong>
ASSOCIACIÓ GREEN AGE<br />
G65923930
</strong><br /><br />
<table>
 <tr>
  <td><strong>Document no.:</strong></td>
  <td>$donationid</td>
 </tr>
 <tr>
  <td><strong>Data:</strong></td>
  <td>$formattedDate</td>
 </tr>
 <tr>
  <td><strong>Ates per:</strong></td>
  <td>$opname</td>
 </tr>
 <tr>
  <td><strong>Soci:</strong></td>
  <td>#$memberno - $first_name</td>
 </tr>
</table>

	<br /><br />

	 <table id='' class='default'>
	  <thead>
	   <tr>
	    <th style='text-align: left;'>Aportacion</th>
	   </tr>
	  </thead>
	  <tbody>
<tr><td>";

		

			echo <<<EOD
			$amount {$_SESSION['currencyoperator']}</td></tr>
			</table>
EOD;
