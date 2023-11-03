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
	 $selectElements= "SELECT customer, SUM(amount) FROM customer_debits GROUP BY customer";
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
			    name: "Debits",
			    filename: "Debits" //do not include extension
		
			  });
		
			});
		  
			
			
			/*	$('#mainTable').tablesorter({
				usNumberFormat: true,
			}); */

		});
      function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this element ?')){
      	 	 window.location = "credit-details.php?did="+delete_id;
      	 }
      }
		
EOD;


	pageStart("Debits", NULL, $memberScript, "pmembership", NULL, "Debits", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
		<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
		<center>
			<a href='invoice-section.php' class='cta1'>Invoice Section</a>
			<a href='debit-movements.php' class='cta1'>Debit Movements</a>
		</center>
         <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center>
      
		<br />

			 <table class='default' id='mainTable'>
			  <thead>	
			   <tr style='cursor: pointer;'>
			    <th>Customer Number</th>
			   	<th>Customer Name</th>
			    <th>Amount (€)</th>
			    <th>History</th>
			   </tr>
			  </thead>
			  <tbody>
			  
			  <?php
				while ($debit = $results->fetch()) {

					$debit_id = $debit['id'];
					$customer = $debit['customer'];
					$amount = $debit['SUM(amount)'];

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

					echo sprintf("
				  	    <tr><td>%s</td>
				  	    <td>%s</td>
				  	    <td class='right'>%s</td>
						<td><a href='debit-details.php?id=%d'><img src='images/notes.png' height='15' title='Edit Debit'></a></td>
						</tr>",
				  	 $customer, $customer_name, $amount, $customer  
				  	);
				  }
				?>

			 </tbody>
			 </table>
<?php  displayFooter(); ?>

<script src="js/excel-bootstrap-table-filter-bundle.js"></script>
<script type="text/javascript">
	$('#mainTable').excelTableFilter();
</script>