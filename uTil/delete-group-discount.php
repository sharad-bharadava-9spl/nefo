<?php
    if (isset($_GET['discountid'])) {
	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
if ($_SESSION['domain'] == 'relax') {
	$accessLevel = '2';
} else {
	$accessLevel = '1';
}
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the purchase ID
	$discountid = $_GET['discountid'];
	// Delete the donation
	$deleteCatDiscount = sprintf("DELETE FROM catdiscounts WHERE usergroup_discount_id = '%d';", $discountid);
	
		try
		{
	            $result = $pdo3->prepare("$deleteCatDiscount")->execute();
		}
		catch (PDOException $e)
		{
	            $error = 'Error fetching user: ' . $e->getMessage();
	            echo $error;
	            exit();
		}	

		$deleteBarCatDiscount = sprintf("DELETE FROM b_catdiscounts WHERE usergroup_discount_id = '%d';", $discountid);
	
		try
		{
	            $result = $pdo3->prepare("$deleteBarCatDiscount")->execute();
		}
		catch (PDOException $e)
		{
	            $error = 'Error fetching user: ' . $e->getMessage();
	            echo $error;
	            exit();
		}

	$deleteIndDiscounts = "DELETE FROM inddiscounts WHERE usergroup_discount_id = $discountid";
        try {
                $result = $pdo3->prepare("$deleteIndDiscounts")->execute();
        } catch (PDOException $e) {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }	

    $deleteBarIndDiscounts = "DELETE FROM b_inddiscounts WHERE usergroup_discount_id = $discountid";
        try {
                $result = $pdo3->prepare("$deleteBarIndDiscounts")->execute();
        } catch (PDOException $e) {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }

	$deleteDiscount = sprintf("DELETE FROM usergroup_discounts WHERE id = '%d';", $discountid);
	
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
        header("Location: ../global-discounts.php?type=ud");
        
    } else {
        header("Location: ../global-discounts.php?type=ud");
    }