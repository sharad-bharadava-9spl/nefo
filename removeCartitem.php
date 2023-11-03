<?php
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';

   $purchase_id = $_POST['purchase_id']; 

if(isset($purchase_id)){
	// DELETE ITEM FROM dispense table 
	$deleteProduct = "DELETE from savesales_details  WHERE purchase_id = $purchase_id";

		try
		{
			$result = $pdo3->prepare("$deleteProduct");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching product: ' . $e->getMessage();
				echo $error;
				exit();
		}

		echo "Deleted";
}else{
	echo "Not deleted";
}

