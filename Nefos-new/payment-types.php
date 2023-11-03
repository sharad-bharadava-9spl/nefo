<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	if(isset($_GET['did'])){
			
			$id= $_GET['did'];


			// delete bank id
			
			$deleteElement = "DELETE FROM payment_types where id = $id";
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



			$_SESSION['successMessage'] = "Payment type deleted successfully!";
			header("location: payment-types.php");
			exit();

	}

	// Query to look up users
	 $selectPaymentType = "SELECT * FROM payment_types";
		try
		{
			$results = $pdo->prepare("$selectPaymentType");
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
			    name: "Bank ID",
			    filename: "Bank ID" //do not include extension
		
			  });
		
			});
		  
			
			
			/*	$('#mainTable').tablesorter({
				usNumberFormat: true,
			}); */

		});
      function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this type ?')){
      	 	 window.location = "payment-types.php?did="+delete_id;
      	 }
      }
		
EOD;


	pageStart("Payment Types", NULL, $memberScript, "pmembership", NULL, "Payment Types", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
		<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
		<center>
			<a href='invoice-payments.php' class='cta1'>Invoice Payments</a>
			<a href='new-payment.php' class='cta1'>Add Payment</a>
			<a href='new-payment-type.php' class='cta1'>Add Payment Type</a>
		</center>
         <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center>
      
		<br />

			 <table class='default' id='mainTable'>
			  <thead>	
			   <tr style='cursor: pointer;'>
			    <th>Code</th>
			    <th>Payment Type</th>
			    <th>Action</th>
			   </tr>
			  </thead>
			  <tbody>
			  
			  <?php
				while ($types = $results->fetch()) {

					$id = $types['id'];
					$code = $types['code'];
					$name = $types['name'];
					

					echo sprintf("
				  	    <tr><td>%s</td>
				  	    <td>%s</td>
						<td><a href='edit-payment-type.php?id=%d'><img src='images/edit.png' height='15' title='Edit payment Type'></a>&nbsp;&nbsp;<a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete Payment type'></a></td>
						</tr>",
				  	 $code, $name, $id, $id
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