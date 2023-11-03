<?php
//Created by Konstant for Task-14935056 on 16/09/2021
require_once 'cOnfig/connection.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';

session_start();
$accessLevel = '3';
	
// Authenticate & authorize
authorizeUser($accessLevel);

// check if form submitted
$response = [];
if(isset($_POST['submitted'])){

	$customer_id = $_POST['customer_id'];
	$user_id = $_POST['user_id'];
	$hwd = $_POST['hwd'];
	$cws = $_POST['cws'];
	$sue = $_POST['sue'];
	$gs = $_POST['gs'];
	$hwd_comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['hwd_comment'])));
	$cws_comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['cws_comment'])));
	$sue_comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['sue_comment'])));
	$gs_comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['gs_comment'])));
	$insertTime = date("Y-m-d H:i:s");

	// insert the surevy form details

	try
    {
        //-----------------------------------------------
        $insertCustomerSurvey = $pdo2->prepare("INSERT INTO customer_survey (customer_id, user_id, hw_delivery, customer_support, sft_user_exe, general_service, hwd_comment, cws_comment, sue_comment, gs_comment, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $insertCustomerSurvey->bindValue(1, $customer_id);
        $insertCustomerSurvey->bindValue(2, $user_id);
        $insertCustomerSurvey->bindValue(3, $hwd);
        $insertCustomerSurvey->bindValue(4, $cws);
        $insertCustomerSurvey->bindValue(5, $sue);
        $insertCustomerSurvey->bindValue(6, $gs);
        $insertCustomerSurvey->bindValue(7, $hwd_comment);
        $insertCustomerSurvey->bindValue(8, $cws_comment);
        $insertCustomerSurvey->bindValue(9, $sue_comment);
        $insertCustomerSurvey->bindValue(10, $gs_comment);
        $insertCustomerSurvey->bindValue(11, $insertTime);
        $insertCustomerSurvey->execute();
        //-----------------------------------------------

       $response['success'] = "yes";

    }
    catch (PDOException $e)
    {
            $error = 'Error fetching user: ' . $e->getMessage();
            $response['error'] = $error;
            exit();
    }

}
echo  json_encode($response);
die;


