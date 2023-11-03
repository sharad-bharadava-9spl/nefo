<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$customer_id = $_REQUEST['id'];
	if(!isset($_REQUEST['id'])){
		header("Location:credits.php");
		die;
	}

	if(isset($_GET['did'])){			
			// FETCH CREDIT DETAILS
			$id= $_GET['did'];
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
				$customer_number = $creditDetails['customer'];
				$credit_reason = $creditDetails['reason_id'];
				$amount = $creditDetails['amount'];
				$comment = $creditDetails['comment'];
				$created_at = date("Y-m-d H:i:s");

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

				$client_credit = $fetch_credit['credit'] - $amount;
				

			// update in credit movements for credits

			$insertMovement = "INSERT INTO credit_movements SET customer = '$customer_number', credit_status = 'Removed', amount = '$amount', movement_at = '$created_at', credit_reason ='$credit_reason', comment = '$comment'";

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
			// delete department
			
			$deleteElement = "DELETE FROM credits where id = $id";
			try
			{
				$results = $pdo3->prepare("$deleteElement");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}



			$_SESSION['successMessage'] = "Credit deleted successfully!";
			header("location: credits.php");
			exit();
	}
	// Query to look up users
	 $selectElements= "SELECT * FROM credits WHERE customer = ".$customer_id." ORDER BY id DESC";
		try
		{
			$results = $pdo3->prepare("$selectElements");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl,.dropdown-filter-dropdown",
			    name: "Credit Details",
			    filename: "Credit Details" //do not include extension
		
			  });
		
			});
		  
			
			
			/*	$('#mainTable').tablesorter({
				usNumberFormat: true,
			}); */

		});
      function delete_element(delete_id,customer_id){
      	 if(confirm('Are you sure to delete this element ?')){
      	 	 window.location = "credit-details.php?did="+delete_id+"&id="+customer_id;
      	 }
      }
		
EOD;


	pageStart("Credit Details", NULL, $memberScript, "pmembership", NULL, "Credit Details", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
		<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
		<center>
			<a href='credits.php' class='cta1'>Credits</a>
			<a href='new-credit.php' class='cta1'>Add Credit</a>
			<a href='credit-reasons.php' class='cta1'>Credit Reasons</a>
		</center>
         <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center>
      
		<br />

			 <table class='default' id='mainTable'>
			  <thead>	
			   <tr style='cursor: pointer;'>
			    <th>invoice #</th>
			    <th>Payment Number</th>
			    <th>Customer</th>
			    <th>Credit Reason</th>
			    <th>Credit Amount (€)</th>
			    <th>Debit Amount (€)</th>
			    <th>Credit Balance (€)</th>
			    <th>Comment</th>
			    <th dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter">Movement TIme</th>
 			    <th dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter">Credit Updated</th>
			    <th>Action</th>
			   </tr>
			  </thead>
			  <tbody>
			  
			  <?php
				while ($credit = $results->fetch()) {

					$credit_id = $credit['id'];
					$invoice = $credit['invoice_no'];
					$customer = $credit['customer'];
					$payment_number = $credit['payment_number'];
					$reason_id = $credit['reason_id'];
					$amount = $credit['amount'];
					$credit_balance = $credit['credit_balance'];
					$comment = $credit['comment'];
					$credit_type = $credit['credit_type'];
					$created_at = $credit['created_at'];
					$updated_at = $credit['updated_at'];
					$created_date = '';
					$updated_date = '';
					if($created_at != ''){
						$created_date = date("d-m-Y H:i:s", strtotime($created_at));
					}
					if($updated_at != ''){
						$updated_date = date("d-m-Y H:i:s", strtotime($updated_at));
					}

					if ($comment != '') {
	
						$commentRead = "
						                <img src='images/comments.png' id='comment$credit_id' /><div id='helpBox$credit_id' class='helpBox'>{$comment}</div>
						                <script>
						                  	$('#comment$credit_id').on({
										 		'mouseover' : function() {
												 	$('#helpBox$credit_id').css('display', 'block');
										  		},
										  		'mouseout' : function() {
												 	$('#helpBox$credit_id').css('display', 'none');
											  	}
										  	});
										</script>
						                ";
						
					} else {
						
						$commentRead = "";
						
					}

					// get the reason name
					$reason = '';
					if($reason_id != ''){
						$selectReason  = "SELECT reason From credit_reasons WHERE id =".$reason_id;

							try
							{
								$reason_results = $pdo3->prepare("$selectReason");
								$reason_results->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}

							$reason_row = $reason_results->fetch();
								$reason = $reason_row['reason'];
						}

					// get the customer name 
					
					$selectCustomer = "SELECT longName FROM customers WHERE number = ".$customer;

						try
						{
							$customer_results = $pdo3->prepare("$selectCustomer");
							$customer_results->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
						$customer_row = $customer_results->fetch();
							$customer_name = $customer_row['longName']; 

					// select invoice brand

					$selectinvoice  = "SELECT brand From invoices2 WHERE invno ='".$invoice."'";

						try
						{
							$invoice_results = $pdo->prepare("$selectinvoice");
							$invoice_results->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching invoice: ' . $e->getMessage();
								echo $error;
								exit();
						}

						$invoice_row = $invoice_results->fetch();
							$brand = $invoice_row['brand'];
					// edit and delete action
					$action = '';		
					if($credit_type == 1){
						$action =  	"<a href='edit-credit.php?id=".$credit_id."'><img src='images/edit.png' height='15' title='Edit Credit'></a>&nbsp;&nbsp;<a href='javascript:void(0);' onClick='delete_element(".$credit_id.",".$customer_id.")'><img src='images/delete.png' height='15' title='Delete Credit'></a>";	
					}
					$invoice_no = $customer."-".$invoice."-".$brand.".pdf";
					$invoice_path = "../invoices/".$invoice_no;
					$inovice_pdf = '';
					if(file_exists($invoice_path)){
						$inovice_pdf = $invoice_path;
					}
					$debit_amount = '';
					$credit_amount = '';
					if($amount < 0){
						$debit_amount = $amount;
					}else {
						$credit_amount = $amount;
					}
					echo sprintf("
				  	    <tr>
				  	    <td><a href='%s' target='_blank'>%s</a></td>
				  	    <td>%s</td>
				  	    <td>%s (%s)</td>
				  	    <td>%s</td>
				  	    <td class='right'>%s</td>
				  	    <td class='right'>%s</td>
				  	    <td class='right'>%s</td>
				  	    <td style='position:relative;'>%s</td>
				  	    <td>%s</td>
				  	    <td>%s</td>
						<td>%s</td>
						</tr>",
				  	 $inovice_pdf, $invoice, $payment_number, $customer_name, $customer, $reason, $credit_amount, $debit_amount, $credit_balance, $commentRead, $created_date, $updated_date, $action
				  	);
				  }
				?>

			 </tbody>
			 </table>
<?php  displayFooter(); ?>

<script src="js/excel-bootstrap-table-filter-bundle.js"></script>
<script src="js/moment.js"></script>
<script type="text/javascript">
	$('#mainTable').excelTableFilter();
</script>