<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	$current_username = $_SESSION['first_name'];

	authorizeUser($accessLevel);
	
		$query = "select * from invoices";
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
	if (isset($_POST['save_credit_note'])) {
		
	    $invoice_type = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['choose_invoice_type']))); 
	    
		$customer = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['customer_number'])));
		$customer_arr = explode("--", $customer);
		$customer_number = $customer_arr[0];
		$customer_name = $customer_arr[1];
		$invoice_date =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['invoice_date'])));
		$invoice_date =   date("Y-m-d", strtotime($invoice_date));
		$base_amount =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['base_amount'])));
		$currency =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['currency'])));
		$vat =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['vat'])));
		$description =  str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
		$discount =0;
		$paid_status = 'Paid';
		$base_amount = $base_amount;
		$total_amount = $total_amount;
		// check if a customer number is temperarory or permanant

		 $is_temp_customer =  substr($customer_number,0,1);

		 if($is_temp_customer == 9){

			 	// assign permanent number to customer

			 	$selectNumber = "SELECT MAX(number) FROM customers WHERE number LIKE ('1%')";

			 		try
					{
						$check_number = $pdo2->prepare("$selectNumber");
						$check_number->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$numberRow = $check_number->fetch();
						 $max_number = $numberRow['MAX(number)'];
						 $permanent_customer_num = $max_number +1;

						 // update permanent cyustomer number in customer table

						 $updateCustomer = "UPDATE customers SET number = '".$permanent_customer_num."' WHERE number = ".$customer_number;

							try
							{
								$update_number = $pdo2->prepare("$updateCustomer");
								$update_number->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}

							$customer_number = $permanent_customer_num;
		 		}
		 		$order_id = 0;
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

					$i++;

			}	
			$_SESSION['order_arr'] = $order_arr;

	    }
	    

		
			$invNo = "CN".$nextInvoiceNumber;
		
		
	   $_SESSION['invoice_type'] = $invoice_type;
	   $_SESSION['customer_number'] = $customer_number;
	   $_SESSION['customer_name'] = $customer_name;
	   $_SESSION['invoice_date'] = $invoice_date;
	   $_SESSION['base_amount'] = $base_amount;
	   $_SESSION['total_amount'] = $total_amount;
	   $_SESSION['paymentoption'] = ($paymentoption)?$paymentoption:'';
	   $_SESSION['shipping'] = $shipping;
	   $_SESSION['discount'] = $discount;
	   $_SESSION['vat'] = $vat;
	   $_SESSION['description'] = $description;
	   $_SESSION['invNo'] = $invNo;

	   if($invoice_due_date == ''){
	   	    $invoice_due_date = "0000-00-00";
	   }

	   $created_date = date("Y-m-d H:i:s");

		// Query to update user - 28 arguments
		 $insertInvoice = sprintf("INSERT INTO invoices2 (invno, paid, invdate ,invoice_generate_time, currency, base_amount, amount, discount, vat, order_id, customer, brand, description, payment_type, invoice_created) VALUES ('%s', '%s', '%s', '%s','%s', '%f', '%f', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s')",
					$invNo,
					$paid_status,
					$invoice_date,
					date('H:i:s'),
					$currency,
					$base_amount,
					$total_amount,
					$discount,
					$vat,
					$order_id,
					$customer_number,
					$invoice_type,
					$description,
					'CN',
					$created_date
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

		// On success: redirect.
		$_SESSION['successMessage'] = "Credit Note added successfully!";
		header("Location: create-credit-note.php");
	}
	
	/***** FORM SUBMIT END *****/