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
		header("Location:customer-debits.php");
		die;
	}


	// Query to look up users
	 $selectElements= "SELECT * FROM customer_debits WHERE customer = ".$customer_id." ORDER BY id DESC";
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
			    name: "Debit Details",
			    filename: "Debit Details" //do not include extension
		
			  });
		
			});
		  
			
			
			/*	$('#mainTable').tablesorter({
				usNumberFormat: true,
			}); */

		});
      function delete_element(delete_id,customer_id){
      	 if(confirm('Are you sure to delete this element ?')){
      	 	 window.location = "debit-details.php?did="+delete_id+"&id="+customer_id;
      	 }
      }
		
EOD;


	pageStart("Debit Details", NULL, $memberScript, "pmembership", NULL, "Debit Details", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
		<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
		<center>
			<a href='customer-debits.php' class='cta1'>Debits</a>
		</center>
         <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center>
      
		<br />

			 <table class='default' id='mainTable'>
			  <thead>	
			   <tr style='cursor: pointer;'>
			    <th>invoice #</th>
			    <th>Payment Number</th>
			    <th>Customer</th>
			    <th>Debit Amount (€)</th>
			    <th>Debit Balance (€)</th>
			    <th dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter">Movement TIme</th>
 			    <th dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter">Credit Updated</th>
			   </tr>
			  </thead>
			  <tbody>
			  
			  <?php
				while ($debit = $results->fetch()) {

					$debit_id = $debit['id'];
					$invoice = $debit['invoice_no'];
					$customer = $debit['customer'];
					$payment_number = $debit['payment_number'];
					$amount = $debit['amount'];
					$debit_balance = $debit['debit_balance'];
					$created_at = $debit['created_at'];
					$updated_at = $debit['updated_at'];
					$created_date = '';
					$updated_date = '';
					if($created_at != ''){
						$created_date = date("d-m-Y H:i:s", strtotime($created_at));
					}
					if($updated_at != ''){
						$updated_date = date("d-m-Y H:i:s", strtotime($updated_at));
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

					$selectinvoice  = "SELECT brand From invoices WHERE invno ='".$invoice."'";

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

					$invoice_no = $customer."-".$invoice."-".$brand.".pdf";
					$invoice_path = "invoices/".$invoice_no;
					$inovice_pdf = '';
					if(file_exists($invoice_path)){
						$inovice_pdf = $invoice_path;
					}
					
					$debit_amount = $amount;
					echo sprintf("
				  	    <tr>
				  	    <td><a href='%s' target='_blank'>%s</a></td>
				  	    <td>%s</td>
				  	    <td>%s (%s)</td>
				  	    <td class='right'>%s</td>
				  	    <td class='right'>%s</td>
				  	    <td>%s</td>
				  	    <td>%s</td>
						</tr>",
				  	 $inovice_pdf, $invoice, $payment_number, $customer_name, $customer, $debit_amount, $debit_balance, $created_date, $updated_date
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