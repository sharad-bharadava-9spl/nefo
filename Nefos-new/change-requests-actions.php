<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();

	// check the approve request

	if(isset($_REQUEST['approve']) && isset($_REQUEST['requestid'])){
		$approve_id =  $_REQUEST['approve'];
		$request_id = $_REQUEST['requestid'];

		// update the approve id

		$updateAprroveRequest = "UPDATE change_requests SET approved = $approve_id WHERE id = $request_id";
		try
		{
			$results = $pdo3->prepare("$updateAprroveRequest");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		if($approve_id == 1){
			$_SESSION['successMessage'] = "Request Approved successfully!";
		}else{
			$_SESSION['errorMessage'] = "Approve request rejected!";
		}
		header("Location: change-requests.php");
		exit();

	}

	// check foe deadline date and update this

	if(isset($_REQUEST['deadlineDate']) && isset($_REQUEST['dead_id'])){
		$deadlineDate = date("Y-m-d H:i:s", strtotime($_REQUEST['deadlineDate']));
		$dead_id = $_REQUEST['dead_id'];

		// update the deadline date 

		$updateDeadline = "UPDATE change_requests SET deadline = '$deadlineDate' WHERE id = $dead_id";
		try
		{
			$results = $pdo3->prepare("$updateDeadline");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = "Deadline updated successfully!";
		header("Location: change-requests.php");
		exit();


	}
	
	// check the complete request

	if(isset($_REQUEST['comment']) && isset($_REQUEST['comment_id']) && isset($_REQUEST['complete_status'])){
		$comment =  $_REQUEST['comment'];
		$comment_id = $_REQUEST['comment_id'];
		$complete_status = $_REQUEST['complete_status'];

		// update the complete status

		$updateCompleteRequest = "UPDATE change_requests SET completed = $complete_status, comment = '$comment' WHERE id = $comment_id";
		try
		{
			$results = $pdo3->prepare("$updateCompleteRequest");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		if($complete_status == 1){
			$_SESSION['successMessage'] = "Request completed successfully!";
		}else{
			$_SESSION['errorMessage'] = "Complete request rejected!";
		}
		header("Location: change-requests.php");
		exit();

	}