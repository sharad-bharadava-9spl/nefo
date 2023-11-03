<?php
	
	require_once 'cOnfig/connection.php';
	// require_once 'cOnfig/view.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['id'])) {
		$id = $_POST['id'];
		$credit_reason = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['credit_reason'])));
		$amount = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['amount'])));
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$customer = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['customer'])));
		$customer_arr = explode("--", $customer);
		$customer_number = $customer_arr[0];
		$updated_at = date('Y-m-d H:i:s');
		$old_credit_arr = [];

		$selectCredit = "SELECT * from credits WHERE id= ".$id;

			try
			{
				$credit_results = $pdo3->prepare("$selectCredit");
				$credit_results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$creditDetails = $credit_results->fetch();
				$old_customer_number = $creditDetails['customer'];
				$old_credit_reason = $creditDetails['reason_id'];
				$old_amount = $creditDetails['amount'];
				$old_comment = $creditDetails['comment'];


				$old_credit_arr['customer_number'] = $old_customer_number;
				$old_credit_arr['reason_id'] = $old_credit_reason;
				$old_credit_arr['amount'] = $old_amount;
				$old_credit_arr['old_comment'] = $old_comment;

				$old_credit_json = json_encode($old_credit_arr);

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

			if(empty($fetch_credit['credit'])){
				$fetch_credit['credit'] = 0;
			}

			// get the diffrerence amount 
			$diff_amount = $amount - $old_amount;
					$client_credit = $fetch_credit['credit'] + $diff_amount;

			// update in credit movements for credits

			$insertMovement = "INSERT INTO credit_movements SET customer = '$customer_number', credit_status = 'Updated', amount = '$amount', movement_at = '$updated_at', credit_reason ='$credit_reason', comment = '$comment', old_credit_data = '$old_credit_json'";

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

		// Query to update user - 28 arguments
		 $updateUser = "UPDATE credits SET customer = '$customer_number', reason_id = '$credit_reason', amount = '$amount', credit_balance = '$client_credit',  comment = '$comment', updated_at = '$updated_at'  WHERE id = $id"; 
		try
		{
			$result1 = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}	


		// On success: redirect.
		$_SESSION['successMessage'] = "Credit updated succesfully!";
		header("Location: credits.php");
		exit();
	}

	// Query to look up users
	 $selectUsers = "SELECT number,longName,shortName,alias FROM customers order by id ASC"; 
		try
		{
			$cuastomer_results = $pdo3->prepare("$selectUsers");
			$cuastomer_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	while($row = $cuastomer_results->fetch()){
		$customer_arr[$row['number']] = $row['longName']." - ".$row['shortName']." - ".$row['alias'];
	}
	
	$validationScript = <<<EOD
    $(document).ready(function() {


    	    
	  $('#registerForm').validate({
		  rules: {
			  name: {
				  required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			if (element.is("#savesig")){
				 error.appendTo("#errorBox1");
			} else if (element.is("#accept2")){
				 error.appendTo("#errorBox2");
			} else if (element.is("#accept3")){
				 error.appendTo("#errorBox3");
			} else if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
		 
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate


  }); // end ready
EOD;
	pageStart("Edit Credit Reason", NULL, $validationScript, "pprofile", NULL, "Edit Credit Reason", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$id = $_GET['id'];
	// Query to look up calls
	$selectUsers = "SELECT * FROM  credits WHERE id = $id";
	try
	{
		$results = $pdo3->prepare("$selectUsers");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $results->fetch();
		
		$reason_id = $row['reason_id'];
		$customer_id = $row['customer'];

		// fetch customer details

		$selectCustomer = "SELECT number,longName,shortName,alias FROM customers WHERE number =".$customer_id; 
		try
		{
			$custom_results = $pdo3->prepare("$selectCustomer");
			$custom_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		while($custom_row = $custom_results->fetch()){
			$number = $custom_row['number'];
			$longName = $custom_row['longName'];
			$shortName = $custom_row['shortName'];
			$alias = $custom_row['alias'];
		}
		$last_customer =  $number."--".$longName." - ".$shortName." - ".$alias;
	// fetch credit reason records

			$selectReasons = "SELECT * from credit_reasons";

			try
			{
				$result = $pdo3->prepare("$selectReasons");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
	?>
<center>
			<a href='credit-movements.php' class='cta1'>Credit Movements</a>
			<a href='credits.php' class='cta1'>Credits</a>
</center>
	<center>
		<form id="registerForm" action="" method="POST">
			<input type="hidden" name="id" value="<?php echo $id; ?>">
			<div id="mainbox-no-width">
				<div id="mainboxheader"> Add Credit </div>
				<div class='boxcontent'>
					<table>
						<tr>
							<td><strong>Customer</strong></td>
							<td>
								<input type="text" name="customer" class="defaultinput" id="cust_num" value="<?php echo $last_customer; ?>" required="">
							</td>
						</tr>
						<tr>
							<td><strong>Select Credit Reason</strong></td>
							<td>
								<select name="credit_reason" required="" class="defaultinput">
									<option value="">Select Reason</option>
									<?php while($reason_row = $result->fetch()){ ?>
										<option value="<?php echo $reason_row['id'] ?>" <?php if($reason_row['id'] == $reason_id){  echo "selected"; } ?>><?php echo $reason_row['reason'] ?></option>
									<?php } ?>	
								</select>
							</td>
						</tr>						
						<tr>
							<td><strong>Amount</strong></td>
							<td>
								<input type="text" name="amount" class="defaultinput" value="<?php echo $row['amount'] ?>" required="">
							</td>
						</tr>						
						<tr>
							<td><strong>Comment</strong> </td>
							<td>
								<textarea name="comment" class="defaultinput"><?php echo $row['comment']; ?></textarea>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<br />
			<button class='oneClick cta1' name='oneClick' type="submit">
				<?php echo $lang['global-savechanges']; ?>
			</button>
			</div>
		</form>	
<?php  displayFooter(); 
foreach($customer_arr as $cust_key => $cust_val){
	$customer_bind_arr[] = strval($cust_key)."--".$cust_val;
}

?>
<script type="text/javascript">
	var customer_bind_arr = <?php echo json_encode($customer_bind_arr); ?>;
	$( "#cust_num" ).autocomplete({
	       	source: customer_bind_arr,
	      	minLength: 0
	    }).focus(function(){
	        if (this.value == ""){
	            $(this).autocomplete("search");
	        }
	});	
</script>
