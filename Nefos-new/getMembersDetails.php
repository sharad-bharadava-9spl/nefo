<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';
	
	

	$customer_num = $_REQUEST['cust_num'];

	// fetch clubs db access details

    $getDBaccess = "SELECT domain, db_pwd from db_access WHERE customer=".$customer_num; 

	try
	{
		$results = $pdo->prepare("$getDBaccess");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$count = $results->rowCount();

	// get vat from customers table

	$getCustomer = "SELECT vat, credit, debit from customers WHERE number =".$customer_num;

	try
	{
		$customer_results = $pdo2->prepare("$getCustomer");
		$customer_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$customner_row = $customer_results->fetch();
		$custom_vat = $customner_row['vat'];
		$customer_credit = $customner_row['credit'];
		$customer_debit = $customner_row['debit'];

		if($customer_credit == '' ){
			$customer_credit = 0;
		}
	// fetch last member selection from invoices of customer
		$getMemberselect = "SELECT member_section from invoices WHERE customer=".$customer_num." order by invoice_created DESC limit 1"; 

		try
		{
			$member_results = $pdo->prepare("$getMemberselect");
			$member_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$memberSelectCount = $member_results->rowCount();
		$member_selection = ''; 
		if($memberSelectCount > 0){
			 $member_fetch = $member_results->fetch();
			 $member_selection = $member_fetch['member_section'];
		}


	if($count > 0){
		$row = $results->fetch();

		$domain = $row['domain'];
		$club_db_pwd = $row['db_pwd'];
		$club_db_name = "ccs_".$domain;
		$club_db_user = "ccs_".$domain."u";

				// create db connection for club db

				try	{
			 		$pdo_club = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$club_db_name, $club_db_user, $club_db_pwd);
			 		$pdo_club->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			 		$pdo_club->exec('SET NAMES "utf8"');
				}
				catch (PDOException $e)	{
			  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

			 		//echo $output;
			 		$response['error'] = "club database is not exist !";
			 		$response['vat'] = $custom_vat;
			 		$response['member_selection'] = $member_selection;
			 		$response['credit'] = $customer_credit;
			 		$response['debit'] = $customer_debit;
			 		header('Content-Type: application/json');
					echo json_encode($response);
			 		exit();
				}

				$previous_month_first_date =   date("Y-m-d", strtotime("first day of previous month"));		
				$previous_month_last_date =   date("Y-m-d", strtotime("last day of previous month"));
				//SELECT COUNT(DISTINCT userid) FROM sales WHERE DATE(saletime) BETWEEN DATE('2020-11-01') AND DATE('2020-11-30')
				// member dispensed last month

				$selectDispenseMembers = "SELECT COUNT(DISTINCT userid) FROM sales WHERE DATE(saletime) BETWEEN DATE('".$previous_month_first_date."') AND DATE('".$previous_month_last_date."')";

					try
					{
						$member_results = $pdo_club->prepare("$selectDispenseMembers");
						$member_results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				$fetch_dispense_members = $member_results->fetch();
					$dispensed_members= $fetch_dispense_members['COUNT(DISTINCT userid)'];

					$member_dispensed_total = $dispensed_members * 0.555;

					// get total users in users table

					$selectUsers = "SELECT COUNT(user_id) from users";
					try
					{
						$user_results = $pdo_club->prepare("$selectUsers");
						$user_results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$user_fetch = $user_results->fetch();
						$userCount = $user_fetch['COUNT(user_id)'];

						if($userCount <= 150 && $userCount >0){
							$member_module_total = 44.50;
						}else if($userCount > 150 && $userCount <= 300){
							$member_module_total = 89;
						}else if($userCount > 300){
							$member_module_total = 133.50;
						}

					// get log operations in last month
					
					$selectLog = "SELECT COUNT(id) FROM log WHERE DATE(logtime) BETWEEN DATE('".$previous_month_first_date."') AND DATE('".$previous_month_last_date."')";

					try
					{
						$log_results = $pdo_club->prepare("$selectLog");
						$log_results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$log_fetch = $log_results->fetch();
						$logs= $log_fetch['COUNT(id)'];

					$response['member_dispensed_total'] = number_format($member_dispensed_total, 2);
					$response['member_module_total'] = number_format($member_module_total, 2);
					$response['member_dispensed'] = $dispensed_members;
					$response['users'] = $userCount;
					$response['logs'] = $logs;
					$response['vat'] = $custom_vat;
					$response['member_selection'] = $member_selection;
					$response['credit'] = $customer_credit;
					$response['debit'] = $customer_debit;



	}else{
		$response['vat'] = $custom_vat;
		$response['member_selection'] = $member_selection;
		$response['credit'] = $customer_credit;
		$response['debit'] = $customer_debit;
		$response['error'] = "Club db access not found or database not exist !";
	}
	
	//$response = array("contact_number" => $contact_number, "contact_email" => $contact_email);
header('Content-Type: application/json');
	echo json_encode($response);

	die;