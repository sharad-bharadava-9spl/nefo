<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	session_start();
	$accessLevel = '2';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if(isset($_GET['did'])){
			
			$id= $_GET['did'];
			$_SESSION['delete_payment_id'] = $id;

			// get old data of payments

		   	$selectOldInvoices =  "SELECT invoices, payment_type FROM invoice_payments WHERE id = ".$id;

		   	try
			{
				$old_results =  $pdo->prepare("$selectOldInvoices");
				$old_results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$old_invoice_row = $old_results->fetch();
				$old_invoices = $old_invoice_row['invoices'];
				$old_payment_type = $old_invoice_row['payment_type'];
				$old_invoice_arr = explode(",", $old_invoices);
				$_SESSION['remove_old_invoice'] = $old_invoice_arr;
				$_SESSION['remove_old_payment_type'] = $old_payment_type;

				// adjust credits if added or removed

				$selectPaymentCredit  = "SELECT amount,customer,invoice_no FROM credits WHERE payment_number =".$id;

				try
				{
					$pay_credit_res =  $pdo3->prepare("$selectPaymentCredit");
					$pay_credit_res->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
				$pay_count = $pay_credit_res->rowCount();
				$pay_res_row = $pay_credit_res->fetch();
				if($pay_count > 0){
					$allocate_credit = $pay_res_row['amount'];
					$customer_number = $pay_res_row['customer'];
					$old_invoice_id = $pay_res_row['invoice_no'];
					$created_at = date('Y-m-d H:i:s');
					$credit_reason = 5;
					// revert credit for customer

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
								$client_credit =  $fetch_credit['credit'] - $allocate_credit;
							//}

						// update in credit movements for credits

						$insertMovement = "INSERT INTO credit_movements SET customer = '$customer_number', credit_status = 'Removed', amount = '$allocate_credit', invoice_id = '$old_invoice_id', movement_at = '$created_at', credit_reason ='$credit_reason', comment = ''";

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
						$deleteCredit = "DELETE FROM credits WHERE payment_number =".$id;  
						try
						{
							$result = $pdo3->prepare("$deleteCredit")->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
				}			

				$updateOldDelta = "UPDATE invoices SET delta = '0' WHERE invno = '".end($old_invoice_arr)."'"; 

			   	try
				{
					$pdo->prepare("$updateOldDelta")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

			// update old data

			foreach ($old_invoice_arr as $old_invoice) {

					$updateOldInvoice = "UPDATE invoices SET payment = '0' , paid =''  WHERE invno = '".$old_invoice."'";

				try
				{
					$pdo->prepare("$updateOldInvoice")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

			}




			if($old_payment_type == 3){
				header("location: remove-credit-invoice.php");
				exit();
			}else{

				// delete bank id
				
				$deleteElement = "DELETE FROM invoice_payments where id = $id";
					try
					{
						$results = $pdo->prepare("$deleteElement");
						$results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}

				$_SESSION['successMessage'] = "Payment deleted successfully!";
				header("location: invoice-payments.php");
				exit();
			}

	}
	
	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "created_at BETWEEN '$fromDate' AND '$untilDate 23:59:59' AND";
			
	} else {
		
		$current_quarter = ceil(date('n') / 3);
		$fromDate = date('Y-m-d', strtotime(date('Y') . '-' . (($current_quarter * 3) - 2) . '-1'));
		$untilDate = date('Y-m-t', strtotime(date('Y') . '-' . (($current_quarter * 3)) . '-1'));
		
		$timeLimit = "created_at BETWEEN '$fromDate' AND '$untilDate 23:59:59' AND";
	}


	// Query to look up users
		$selectPayment = "SELECT * FROM invoice_payments WHERE $timeLimit invoices <> '' order by online_verified ASC, id DESC";
		try
		{
			$results = $pdo->prepare("$selectPayment");
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
		    
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl,.dropdown-filter-dropdown",
			    name: "Payments",
			    filename: "Payments" //do not include extension
		
			  });
		
			});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			
			/*$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					4: {
						sorter: "dates"
					}
				}
			}); */

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});

	 function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this payment ?')){
      	 	 window.location = "invoice-payments.php?did="+delete_id;
      	 }
      }

		
EOD;
// delete videos



	pageStart("Invoice Payments", NULL, $memberScript, "pmembership", NULL, "Invoice Payments", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
<center>
	<a href='new-payment.php' class='cta1'>Add Payment</a>
	<a href='unallocated.php' class='cta1'>Unallocated</a>
</center>
<br />
<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['filter']; ?>
 </div>
 <div class='boxcontent' style='padding-bottom: 0;'>
  <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <br /><button type="submit" class='cta2'>{$lang['filter']}</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="$fromDate" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="$untilDate" onchange='this.form.submit()' />
		 <br /><button type="submit" class='cta2'>{$lang['filter']}</button>
EOD;

	}
?>
        </form>
 </div>
</div>
</center>

         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
<br />
<br />
		 <table class='default' id='mainTable' style='min-width: 1700px;'>
			  <thead>	
			   <tr style='cursor: pointer;'>
			    <th style='min-width: 95px;'>Region</th>
			    <th style='min-width: 95px;'>Payment #</th>
			    <th>#</th>
			    <th style='min-width: 210px;'>Customer</th>
			    <th style='min-width: 90px;'>Amount</th>
			    <th style='min-width: 90px;'>Delta</th>
			    <th style='min-width: 90px;'>Bank ID</th>
			    <th style='min-width: 105px;' dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter">Settled Date</th>
			    <th style='min-width: 130px;' dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter">Lodgment Date</th>
			    <th style='min-width: 115px;'>Payment Type</th>
			    <th style='min-width: 80px;'>Verified</th>
			    <th style='min-width: 80px;'>Invoices</th>
			    <th style='min-width: 90px;'>Inv. date</th>
			    <th style='min-width: 90px;'>Comment</th>
			    <th dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter" style='min-width: 120px;'>Created</th>
			    <th dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter" style='min-width: 120px;'>Updated</th>
			    <th style='min-width: 75px;'>Action</th>
			   </tr>
			  </thead>
			  <tbody>
			  
			  <?php
				while ($payment = $results->fetch()) {

					$id = $payment['id'];
					$amount = $payment['amount'];
					$bank_id = $payment['bank_id'];
					$settled_date = $payment['settled_date'];
					$settled_date = date("d-m-Y", strtotime($settled_date));
					$bank_lodgement_date = $payment['bank_lodgement_date'];
					$bank_lodgement_date = date("d-m-Y", strtotime($bank_lodgement_date));
					$payment_type = $payment['payment_type'];
					$invoices = $payment['invoices'];
					$oneInvoice = $invoices;
					$invoices = str_replace(",", "<br>", $invoices);
					$allocate_payment = $payment['allocate_payment'];
					$comment = $payment['comment'];
					$customer = $payment['customer'];
					$online_verified = $payment['online_verified'];
					$new_delta = $payment['delta'];
					$created_at = '';
					$updated_at = '';
					
					$invList = explode(',', $oneInvoice);
					
					$invDates = '';
					
					
						$invAmt = 0;
			        foreach ($invList as $thisInvoice)  {
				        
				        $dateQuery = "SELECT invdate, amount FROM invoices WHERE invno = '$thisInvoice'";
						try
						{
							$resultsD = $pdo->prepare("$dateQuery");
							$resultsD->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
						
						while ($rowD = $resultsD->fetch()) {
							
							$invDates .= date("d-m-Y", strtotime($rowD['invdate'])) . "<br />";
							$invAmt = $invAmt + $rowD['amount'];
							
						}
						
			            //$invDates .= $thisInvoice ."<br />";
			        }
				$old_delta =  number_format($amount - $invAmt, 2);

				if($new_delta == ''){
					$remain_delta = $old_delta; 
				}else{
					$remain_delta = $new_delta; 
				}


			        // Select amount of all invoices paid
			        // $query = "SELECT SUM(amount) FROM invoices WHERE invno in ()";
			        
					if($payment['created_at'] != ''){
						$created_at = date("d-m-Y H:i", strtotime($payment['created_at']."+$offsetSec seconds"));
					}
					if($payment['updated_at'] != ''){
						$updated_at = date("d-m-Y H:i", strtotime($payment['updated_at']."+$offsetSec seconds"));
					}
					
					if ($online_verified == 1 || $payment_type != 3) {
						$verified = "<img src='images/complete.png' width='16' />";
						$verifiedOpr = "SI";
					} else {
						$verified = $lang['global-no'];
						$verifiedOpr = $lang['global-no'];
					}
					
			if ($invoiced == 1) {
				
				if ($delivereddate == NULL) {
					$inInvoiced = "<img src='images/complete.png' width='16' />";
				} else {
					$delTime = date('d-m-Y', strtotime($delivereddate));
					$inInvoiced = "<span style='color: #000 !important;'>$delTime</span>";
				}
				$inInvoicedOpr = "SI";
			} else {
				$inInvoiced = $lang['global-no'];
				$inInvoicedOpr = $lang['global-no'];
			}

					
					
					if ($comment != '') {
	
						$commentRead = "
						                <img src='images/comments.png' id='comment$id' /><div id='helpBox$id' class='helpBox'>{$comment}</div>
						                <script>
						                  	$('#comment$id').on({
										 		'mouseover' : function() {
												 	$('#helpBox$id').css('display', 'block');
										  		},
										  		'mouseout' : function() {
												 	$('#helpBox$id').css('display', 'none');
											  	}
										  	});
										</script>
						                ";
						
					} else {
						
						$commentRead = "";
						
					}
					// fetch bank id name 

					$selectBankId = "SELECT bank_id FROM payment_bank_id WHERE id=".$bank_id;
					try
					{
						$bank_results = $pdo->prepare("$selectBankId");
						$bank_results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$bankId_row = $bank_results->fetch();
						$bank_id_name =  $bankId_row['bank_id'];

					// fetch the payment Type name

					$selectPaymentType = "SELECT * FROM payment_types WHERE id=".$payment_type;
					try
					{
						$payment_results = $pdo->prepare("$selectPaymentType");
						$payment_results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$paymentType_roe = $payment_results->fetch();
						$payment_type_name =  $paymentType_roe['name'];
						$payment_type_code =  $paymentType_roe['code'];
						
					if ($customer != 0) {
						
						if (strpos($customer, ',') !== false) {
							
							$multiClient = 'true';
							$customers = explode(",", $customer);
							// $customer = str_replace(",", "<br>", $customer);
							$longName = '';
							
							foreach ($customers as $indCust)  {
								
								$customer = $indCust;
								$query = "SELECT Brand, longName FROM customers WHERE number = '$indCust'";
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
									//$longName .= $row['longName'] . '<br />';
									// After cloudflare deployment, a bug has appeared where many payments are registered with the same customer number several times. Lokesh to investigate, meanwhile here's the "workaround":
									$longName = $row['longName'];
									$Brand = $row['Brand'];
	
							}
							
						} else {
							
							$multiClient = 'false';
							
							$query = "SELECT longName FROM customers WHERE number = '$customer'";
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
								$longName = $row['longName'];
							
						}
							
					} else {
						
						$customer = '';
						$longName = '';
						
					}
					
					if ($Brand == 1) {
						$region = 'Europe';
					} else if ($Brand == 2) {
						$region = 'Nefos';
					} else if ($Brand == 3) {
						$region = 'North America';
					} else if ($Brand == 4) {
						$region = 'South America';
					} else if ($Brand == 5) {
						$region = 'South Africa';
					} else {
						$region = 'N/A';
					}
					

					echo sprintf("
				  	    <tr>
				  	    <td class='right'>%s</td>
				  	    <td class='right'>%d</td>
				  	    <td>%s</td>
				  	    <td>%s</td>
				  	    <td class='right'>%s</td>
				  	    <td class='right'>%s</td>
				  	    <td>%s</td>
				  	    <td>%s</td>
				  	    <td>%s</td>
				  	    <td>%s (%s)</td>
						<td class='centered'><a href='uTil/online-verify.php?paymentid=$id&menu=$verifiedOpr'>$verified</a></td>
				  	    <td>%s</td>
				  	    <td>%s</td>
				  	    <td class='centered'><span style='position:relative;'>%s</span></td>
				  	    <td>%s</td>
				  	    <td>%s</td>
						<td><a href='edit-payment.php?id=%d'><img src='images/edit.png' height='15' title='Edit payment'></a>&nbsp;&nbsp;<a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete Payment'></a></td>
						</tr>",
				  	 $region, $id, $customer, $longName, $amount, $remain_delta, $bank_id_name, $settled_date, $bank_lodgement_date, $payment_type_name, $payment_type_code, $invoices, $invDates, $commentRead, $created_at, $updated_at, $id, $id
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