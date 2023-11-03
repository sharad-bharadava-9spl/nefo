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
	 $selectMovements= "SELECT * FROM debit_movements order by id DESC";
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
			    name: "Debit Movements",
			    filename: "Debit Movements" //do not include extension
		
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


	pageStart("Debit Movements", NULL, $memberScript, "pmembership", NULL, "Debit Movements", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

		<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
		<center>
			<a href='invoice-section.php' class='cta1'>Invoice Section</a>
			<a href='customer-debits.php' class='cta1'>Debits</a>
		</center>
      <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center>
      
		<br />

			 <table class='default' id='mainTable'>
			  <thead>	
			   <tr style='cursor: pointer;'>
			    <th>Invoice #</th>
			    <th>Customer</th>
			    <th>Debit Status</th>
			    <th>Amount (€)</th>
			    <th  dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter">Movement TIme</th>
			    <th>Comment</th>
			    <th>Old Data</th>
			   </tr>
			  </thead>
			  <tbody>
			  
			  <?php
				while ($debit_movement = $results->fetch()) {

					$movement_id = $debit_movement['id'];
					$invoice_id = $debit_movement['invoice_id'];
					$customer = $debit_movement['customer'];
					$debit_status = $debit_movement['debit_status'];
					$amount = $debit_movement['amount'];
					$comment = $debit_movement['comment'];
					$movement_at = $debit_movement['movement_at'];
					$movement_at = date("d-m-Y H:i:s", strtotime($movement_at));
					$old_data = '';
					$old_data_arr = [];
					if($debit_movement['old_debit_data'] != ''){
						$old_data = $debit_movement['old_debit_data'];
						$old_data_arr = json_decode($old_data);
					}


					$reason = '';
					$old_customer_text = 'N/A';

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


								$old_customer_text = '<strong>Customer Name : </strong><a href="customer.php?user_id='.$old_customer_id.'" >'.$old_customer_name.'(ID-'.$old_customer_number.')</a><br><strong>Amount : </strong>'.$old_amount.'<br><strong>Comment : </strong>'.$old_comment.'<br>';	


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

					$selectinvoice  = "SELECT brand From invoices WHERE invno ='".$invoice_id."'";

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
					$invoice_path = "invoices/".$invoice_no;
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
				  	    <td><span style='position:relative;'>%s</span></td>
				  	    <td>%s</td>
						</tr>",
				  	$inovice_pdf, $invoice_id, $customer_name, $customer, $debit_status, $amount, $movement_at, $commentRead, $old_customer_text

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