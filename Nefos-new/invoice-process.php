<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	$current_username = $_SESSION['first_name'];

	authorizeUser($accessLevel);
	
		$query = "select * from invoices2";
		try
		{
			$inv_result = $pdo->prepare("$query");
			$inv_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while($inv_row = $inv_result->fetch()){
			$inv_number = $inv_row['invno'];

			if(strpos($inv_number, 'S') !== false){
			 	 $inv_arr[] = str_replace("S", "", $inv_number);
			 }else if(strpos($inv_number, 'M') !== false){
			 	$inv_arr[] = str_replace("M", "", $inv_number);
			 }else if(strpos($inv_number, 'CN') !== false){
			 	$inv_arr[] = str_replace("CN", "", $inv_number);
			 }
			 else{
			 	$inv_arr[] = $inv_number;
			 }
		}

		$maxInvoiceNumber = max($inv_arr);  

		 if(strpos($maxInvoiceNumber, 'S') !== false){
		 	 $invoiceNumber = str_replace("S", "", $maxInvoiceNumber);
		 }else{
		 	$invoiceNumber = $maxInvoiceNumber;
		 }
		 $nextInvoiceNumber = $invoiceNumber + 1;
	// Did this page re-submit with a form? If so, check & store details
	$order_arr = [];	 
	if (isset($_POST['save_invoice'])) {
		
	    $invoice_type = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['choose_invoice_type']))); 
	    $member_check = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['member_check']))); 
		$customer = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['customer_number'])));
		$customer_arr = explode("--", $customer);
		$customer_number = $customer_arr[0];
		$customer_name = $customer_arr[1];
		$invoice_date =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['invoice_date'])));
		$invoice_date =  date("Y-m-d", strtotime($invoice_date));
		$invoice_due_date =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['invoice_due_date'])));
		$invoice_due_date =   date("Y-m-d", strtotime($invoice_due_date));
		$base_amount =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['base_amount'])));
		$currency =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['currency'])));
		
		$discount =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['discount'])));
		$vat =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['vat'])));
		$fees =  str_replace("'","\'",str_replace('%', '&#37;', $_POST['fees']));
		$fees =  array_filter($fees);
		$fees_elements = serialize($fees);
		$description =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
		
		

		if($credit_amount == ''){
			 $credit_amount = 0;
		}

		$paid_status = '';
		
		if (abs(($total_amount-$credit_amount)/$credit_amount) < 0.00001) {
			$paid_status = 'Paid';
		}
		if($invoice_type == 'SW'){
			$type = 'sw';
			$total_amount =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['total_amount'])));
			$credit_amount =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['credit_amount'])));
			$debit_amount =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['debit_amount'])));
			$total_credit =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['total_credit'])));
		}else{
			$type = 'hw';
			$total_amount =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['total_amount_order'])));
			$credit_amount =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['credit_amount2'])));
			$debit_amount =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['debit_amount2'])));
			$total_credit =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['total_credit2'])));
		}
		if ($credit_amount > $total_credit || $credit_amount > $total_amount) {
			    $_SESSION['errorMessage'] = "Please enter valid credit amount !";
			    header("Location:new-invoice.php?type=".$type);
			    die;
		    } 

		// check if a customer number is temperarory or permanant

		 $is_temp_customer =  substr($customer_number,0,1);

		 if($is_temp_customer == 9 && $invoice_type == 'SW'){

		 		$customer_number = updatePermanentCustomer($customer_number);
		 	}
			
		if($invoice_type == 'SW'){
			$member_value = 0;
			if($member_check == 'MM'){
				$member_value = $_POST['member_module_val'];
			}else if($member_check = 'MD'){
				$member_value = $_POST['member_dispense_val'];
			} 

			$_SESSION['member_value'] = $member_value;
		}

	    if($invoice_type == 'HW'){
	    	 $order_id = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['order_id']))); 

	    	 // check order id 

	    	 $checkOrder = "SELECT * from sales WHERE saleid =".$order_id;

	    	 	try
				{
					$check_result = $pdo2->prepare("$checkOrder");
					$check_result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			$num_order = $check_result->rowCount();

			if($num_order == 0){
				$_SESSION['errorMessage'] = "Please provide valid order id !";
				header("location: new-invoice.php");
				die;
			}

			while($orderRow = $check_result->fetch()){

				//--=====customer details================--
				$query = "SELECT customer FROM db_access WHERE domain = '$customer'";
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
					$custnumber = $row['customer'];
				
				$query = "SELECT shortName, street, streetnumber, flat, postcode, city, email, phone, shipping, number, vat FROM customers WHERE number = '$custnumber'";
				try
				{
				$result = $pdo2->prepare("$query");
				$result->execute();
				}
				catch (PDOException $e)
				{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
				}
				$row = $result->fetch();
				//--====customer details==================--

				$discount = $orderRow['discount'];
				$shipping = $orderRow['shipping'];
				$vat = $row['vat'];
				$total_amount = $orderRow['amount'];
				$paymentoption = $orderRow['paymentoption'];
			}
			// get product details

			$selectProduct = "SELECT * from salesdetails WHERE saleid =".$order_id;

				try
				{
					$get_result = $pdo2->prepare("$selectProduct");
					$get_result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			$i =0;	
			$subtotal = 0;
			while($getSalesRow = $get_result->fetch()){
				$productid = $getSalesRow['productid'];
				$purchaseid = $getSalesRow['purchaseid'];

				// get name
				 $getName = "SELECT name from products WHERE productid =".$productid;

					try
					{
						$select_result = $pdo2->prepare("$getName");
						$select_result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$nameRow = $select_result->fetch();

					$quantity = $getSalesRow['quantity'];
					//---------discount-----------------
					if ($purchaseid == 12 || $purchaseid == 13 || $purchaseid == 14 || $purchaseid == 15 || $purchaseid == 16) {
						 if ($quantity > 99) {
							 $discountTxt = '20%';
							 $discountOp = 0.8;
						 } else if ($quantity > 49) {
							 $discountTxt = '10%';
							 $discountOp = 0.9;
						 } else if ($quantity > 9) {
							 $discountTxt = '5%';
							 $discountOp = 0.95;
						 } else {					
							 $discountTxt = '';
							 $discountOp = 1;
						 }				
					 } 
					 else if ($purchaseid == 25 || $purchaseid == 26 || $purchaseid == 27) {
						 
						 if ($quantity > 999) {
							 
							 $discountTxt = '5%';
							 $discountOp = 0.95;
							 
						 } else {
							 
							 $discountTxt = '';
							 $discountOp = 1;
							 
						 }
		 
					 } 
					 else {
						 $discountTxt = '';
						 $discountOp = 1;
					 }
					//---------discount-----------------

					$qty = explode(".",$getSalesRow['quantity']);
					$productQty = ($qty[1]>0)?$getSalesRow['quantity']:$qty[0];

					$productName = $nameRow['name'];
					$order_arr[$i]['name'] = $productName;
					$order_arr[$i]['quantity'] = $productQty;
					$order_arr[$i]['price'] = $getSalesRow['amount'] / $productQty;
					$order_arr[$i]['amount'] = $getSalesRow['amount'] * $discountOp;
					$order_arr[$i]['discount'] = $discountTxt;
					$sub_base_amount =  $getSalesRow['amount'] * $discountOp;
					$subtotal += $sub_base_amount;
					$i++;

			}	
			$_SESSION['order_arr'] = $order_arr;
			$base_amount = $subtotal;
	    }
	    

		if($total_amount < 400){
			$invNo = 'S'.$nextInvoiceNumber;
		}else{
			$invNo = $nextInvoiceNumber;
		}
		
	   $_SESSION['invoice_type'] = $invoice_type;
	   $_SESSION['customer_number'] = $customer_number;
	   $_SESSION['customer_name'] = $customer_name;
	   $_SESSION['invoice_date'] = $invoice_date;
	   $_SESSION['invoice_due_date'] = $invoice_due_date;
	   $_SESSION['base_amount'] = $base_amount;
	   $_SESSION['total_amount'] = $total_amount;
	   $_SESSION['paymentoption'] = ($paymentoption)?$paymentoption:'';
	   $_SESSION['shipping'] = $shipping;
	   $_SESSION['discount'] = $discount;
	   $_SESSION['vat'] = $vat;
	   $_SESSION['fees_elements'] = array_filter($_POST['fees']);
	   $_SESSION['description'] = $description;
	   $_SESSION['invNo'] = $invNo;
	   $_SESSION['credit_amount'] = $credit_amount;
	   $_SESSION['debit_amount'] = $debit_amount;

	   if($invoice_due_date == ''){
	   	    $invoice_due_date = "0000-00-00";
	   }

	   $created_date = date("Y-m-d H:i:s");

		// Query to update user - 28 arguments
		 $insertInvoice = sprintf("INSERT INTO invoices2 (invno, paid, invdate, invduedate,invoice_generate_time, currency, base_amount, amount, fees, discount, vat, order_id, customer, brand, description, invoice_created, member_section, client_credit, client_debit) VALUES ('%s', '%s', '%s', '%s','%s', '%s', '%f', '%f', '%s', '%f', '%f', '%d', '%s', '%s', '%s', '%s', '%s', '%f', '%f')",
					$invNo,
					$paid_status,
					$invoice_date,
					$invoice_due_date,
					date('H:i:s'),
					$currency,
					$base_amount,
					$total_amount,
					$fees_elements,
					$discount,
					$vat,
					$order_id,
					$customer_number,
					$invoice_type,
					$description,
					$created_date,
					$member_check,
					$credit_amount,
					$debit_amount
					);
		try
		{
			$result = $pdo->prepare("$insertInvoice")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		if($credit_amount > 0){

				// check last credit amount of customer

				$checkCredit = "SELECT credit FROM customers WHERE number =".$customer_number;

				try
				{
					$credit_result = $pdo3->prepare("$checkCredit");
					$credit_result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$fetch_credit = $credit_result->fetch();
						$client_credit = $fetch_credit['credit'] - $credit_amount;
					

				// update in credit movements for credits

				$insertMovement = "INSERT INTO credit_movements SET invoice_id = '$invNo', customer = '$customer_number', credit_status = 'Used', amount = '$credit_amount', movement_at = '$created_date', credit_reason ='0'";

				try
				{
					$insert_movement = $pdo3->prepare("$insertMovement");
					$insert_movement->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

				

				// update credit in customers table

				$updateCredit = "UPDATE customers SET credit = '$client_credit' WHERE number =".$customer_number;

				try
				{
					$update_credit = $pdo3->prepare("$updateCredit");
					$update_credit->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}


				// insert into credit

				// Query to update user - 28 arguments

				$delta_credit  = -$credit_amount;
				 $insertCredit = "INSERT into credits SET invoice_no = '$invNo', customer = '$customer_number', amount = '$delta_credit', credit_balance = '$client_credit', credit_type = 2, created_at = '$created_date'";  
				try
				{
					$result = $pdo3->prepare("$insertCredit")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
		}		

		if($debit_amount > 0){

				// check last credit amount of customer

				$checkDebit = "SELECT debit FROM customers WHERE number =".$customer_number;

				try
				{
					$debit_result = $pdo3->prepare("$checkDebit");
					$debit_result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$fetch_debit = $debit_result->fetch();
						$client_debit = $fetch_debit['debit'] - $debit_amount;
					

				// update in credit movements for credits

				$insertMovement = "INSERT INTO debit_movements SET invoice_id = '$invNo', customer = '$customer_number', debit_status = 'Used', amount = '$debit_amount', movement_at = '$created_date'";

				try
				{
					$insert_movement = $pdo3->prepare("$insertMovement");
					$insert_movement->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

				

				// update credit in customers table

				$updateDebit = "UPDATE customers SET debit = '$client_debit' WHERE number =".$customer_number;

				try
				{
					$update_debit = $pdo3->prepare("$updateDebit");
					$update_debit->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}


				// insert into credit

				// Query to update user - 28 arguments

				$delta_debit  = -$debit_amount;
				 $insertDebit = "INSERT into customer_debits SET invoice_no = '$invNo', customer = '$customer_number', amount = '$delta_debit', debit_balance = '$client_debit', created_at = '$created_date'";  
				try
				{
					$result = $pdo3->prepare("$insertDebit")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
		}
		// On success: redirect.
		$_SESSION['successMessage'] = "Invoice added successfully!";
		header("Location: create-invoice.php");
	}
	
	/***** FORM SUBMIT END *****/