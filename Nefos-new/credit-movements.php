<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	// Query to look up users
	 $selectMovements= "SELECT * FROM credit_movements order by id DESC";
		try
		{
			$results = $pdo3->prepare("$selectMovements");
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
			    name: "Credit Movements",
			    filename: "Credit Movements" //do not include extension
		
			  });
		
			});
		  
			
			
			/*$('#mainTable').tablesorter({
				usNumberFormat: true,
			}); */

		});
      function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this element ?')){
      	 	 window.location = "invoice-elements.php?did="+delete_id;
      	 }
      }
		
EOD;


	pageStart("Credits Movements", NULL, $memberScript, "pmembership", NULL, "Credits Movements", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

		<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
		<center>
			<a href='invoice-section.php' class='cta1'>Invoice Section</a>
			<a href='credits.php' class='cta1'>Credits</a>
		</center>
      <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center>
      
		<br />

			 <table class='default' id='mainTable'>
			  <thead>	
			   <tr style='cursor: pointer;'>
			    <th>Invoice #</th>
			    <th>Customer</th>
			    <th>Credit Status</th>
			    <th>Amount (€)</th>
			    <th>Credit Reason</th>
			    <th> Comment </th>
			    <th  dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter">Movement TIme</th>
			    <th>Old Data</th>
			   </tr>
			  </thead>
			  <tbody>
			  
			  <?php
				while ($credit_movement = $results->fetch()) {

					$movement_id = $credit_movement['id'];
					$invoice_id = $credit_movement['invoice_id'];
					$customer = $credit_movement['customer'];
					$credit_status = $credit_movement['credit_status'];
					$amount = $credit_movement['amount'];
					$comment = $credit_movement['comment'];
					$credit_reason = $credit_movement['credit_reason'];
					$movement_at = $credit_movement['movement_at'];
					$movement_at = date("d-m-Y H:i:s", strtotime($movement_at));
					$old_data = '';
					$old_data_arr = [];
					if($credit_movement['old_credit_data'] != ''){
						$old_data = $credit_movement['old_credit_data'];
						$old_data_arr = json_decode($old_data);
					}


					$reason = '';
					$old_customer_text = 'N/A';
					// get the reason
					if($credit_reason != ''){
						 $selectReason = "SELECT reason FROM credit_reasons WHERE id =".$credit_reason;
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

					if ($comment != '') {
	
						$commentRead = "
						                <img src='images/comments.png' id='comment$movement_id' /><div id='helpBox$movement_id' class='helpBox'>{$comment}</div>
						                <script>
						                  	$('#comment$movement_id').on({
										 		'mouseover' : function() {
												 	$('#helpBox$movement_id').css('display', 'block');
										  		},
										  		'mouseout' : function() {
												 	$('#helpBox$movement_id').css('display', 'none');
											  	}
										  	});
										</script>
						                ";
						
					} else {
						
						$commentRead = "";
						
					}

					if(!empty($old_data_arr)){
							
							$old_customer_number = $old_data_arr->customer_number;
							$old_credit_reason = $old_data_arr->reason_id;
							$old_amount = $old_data_arr->amount;
							$old_comment = $old_data_arr->old_comment;


							// get customer details

							 $selectCusromer = "SELECT id,longName FROM customers WHERE number =".$old_customer_number;

								try
								{
									$cuastomer_results = $pdo3->prepare("$selectCusromer");
									$cuastomer_results->execute();
								}
								catch (PDOException $e)
								{
										$error = 'Error fetching user: ' . $e->getMessage();
										echo $error;
										exit();
								}
								$customer_row = $cuastomer_results->fetch();
									$old_customer_name = $customer_row['longName'];
									$old_customer_id = $customer_row['id'];

								// fetch resomn
								
								$selectOldReason = "SELECT reason FROM credit_reasons WHERE id =".$old_credit_reason;
								try
								{
									$oldreason_results = $pdo3->prepare("$selectOldReason");
									$oldreason_results->execute();
								}
								catch (PDOException $e)
								{
										$error = 'Error fetching user: ' . $e->getMessage();
										echo $error;
										exit();
								}
								$oldreason_row = $oldreason_results->fetch();
									$old_reason = $oldreason_row['reason'];

								$old_customer_text = '<strong>Customer Name : </strong><a href="customer.php?user_id='.$old_customer_id.'" >'.$old_customer_name.'(ID-'.$old_customer_number.')</a><br><strong>Credit Reason :</strong>'.$old_reason.'<br><strong>Amount : </strong>'.$old_amount.'<br><strong>Comment : </strong>'.$old_comment.'<br>';	


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

					$selectinvoice  = "SELECT brand From invoices2 WHERE invno ='".$invoice_id."'";

						try
						{
							$invoice_results = $pdo->prepare("$selectinvoice");
							$invoice_results->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}

						$invoice_row = $invoice_results->fetch();
							$brand = $invoice_row['brand'];

					$invoice_no = $customer."-".$invoice_id."-".$brand.".pdf";
					$invoice_path = "../invoices/".$invoice_no;
					$inovice_pdf = '';
					if(file_exists($invoice_path)){
						$inovice_pdf = $invoice_path;
					}
					echo sprintf("
				  	    <tr><td><a href='%s' target='_blank'>%s</a></td>
				  	    <td>%s (%s)</td>
				  	    <td>%s</td>
				  	    <td class='right'>%s</td>
				  	    <td>%s</td>
				  	    <td style='position:relative;'>%s</td>
				  	    <td>%s</td>
				  	    <td>%s</td>
						</tr>",
				  	$inovice_pdf, $invoice_id, $customer_name, $customer, $credit_status, $amount, $reason, $commentRead, $movement_at, $old_customer_text
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