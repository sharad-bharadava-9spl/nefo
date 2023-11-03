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
	if (isset($_POST['credit_reason'])) {
		$credit_reason = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['credit_reason'])));
		$amount = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['amount'])));
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$customer = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['customer'])));
		$customer_arr = explode("--", $customer);
		$customer_number = $customer_arr[0];
		$credit_type =1;
		$created_at = date('Y-m-d H:i:s');
		

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
			//$client_credit = 0;
			//if(!empty($fetch_credit['credit'])){
				$client_credit =  $fetch_credit['credit'] + $amount;
			//}

		// Query to update user - 28 arguments
		 $updateUser = "INSERT into credits SET customer = '$customer_number', reason_id = '$credit_reason', amount = '$amount', credit_balance = '$client_credit', comment = '$comment', credit_type = '$credit_type', created_at = '$created_at'";  
		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}		

		// update in credit movements for credits

		$insertMovement = "INSERT INTO credit_movements SET customer = '$customer_number', credit_status = 'Added', amount = '$amount', movement_at = '$created_at', credit_reason ='$credit_reason', comment = '$comment'";

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

		$_SESSION['successMessage'] = "New Credit Saved!";
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

	pageStart("Add New Credit", NULL, $validationScript, "pprofile", NULL, "Add New Credit", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	?>
	<center>
			<a href='credit-movements.php' class='cta1'>Credit Movements</a>
			<a href='credits.php' class='cta1'>Credits</a>
	</center>
	<center>
		<form id="registerForm" action="" method="POST">
			<div id="mainbox-no-width">
				<div id="mainboxheader"> Add Credit </div>
				<div class='boxcontent'>
					<table>
						<tr>
							<td><strong>Customer</strong></td>
							<td>
								<input type="text" name="customer" class="defaultinput" id="cust_num" required="">
							</td>
						</tr>
						<tr>
							<td><strong>Select Credit Reason</strong></td>
							<td>
								<select name="credit_reason" required="" class="defaultinput">
									<option value="">Select Reason</option>
									<?php while($reason_row = $result->fetch()){ ?>
										<option value="<?php echo $reason_row['id'] ?>"><?php echo $reason_row['reason'] ?></option>
									<?php } ?>	
								</select>
							</td>
						</tr>						
						<tr>
							<td><strong>Amount</strong></td>
							<td>
								<input type="text" name="amount" class="defaultinput" required="">
							</td>
						</tr>						
						<tr>
							<td><strong>Comment</strong> </td>
							<td>
								<textarea name="comment" class="defaultinput"></textarea>
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
	</center>	
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