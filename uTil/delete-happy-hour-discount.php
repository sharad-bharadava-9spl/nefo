<?php
    if (isset($_GET['discountid'])) {
	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	//$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the purchase ID
	$discountid = $_GET['discountid'];
	// Delete the donation
	$deleteDiscount = sprintf("DELETE FROM global_happy_hour_discounts WHERE id = '%d';", $discountid);
	
	try
	{
            $result = $pdo3->prepare("$deleteDiscount")->execute();
	}
	catch (PDOException $e)
	{
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
	}
	
        $_SESSION['successMessage'] = $lang['discount-deleted'];
        header("Location: ../global-discounts.php?type=hhd");
        
    } else {
        header("Location: ../global-discounts.php?type=hhd");
    }