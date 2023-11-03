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
			
			$deleteElement = "DELETE FROM payment_bank_id where id = $id";
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



			$_SESSION['successMessage'] = "Bnak ID deleted successfully!";
			header("location: bank-ids.php");
			exit();

	}

	// Query to look up users
	 $selectBankID = "SELECT * FROM payment_bank_id";
		try
		{
			$results = $pdo->prepare("$selectBankID");
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
      	 if(confirm('Are you sure to delete this id ?')){
      	 	 window.location = "bank-ids.php?did="+delete_id;
      	 }
      }
		
EOD;


	pageStart("Bank IDs", NULL, $memberScript, "pmembership", NULL, "Bank IDs", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
		<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
		<center>
			<a href='invoice-payments.php' class='cta1'>Invoice Payments</a>
			<a href='new-payment.php' class='cta1'>Add Payment</a>
			<a href='new-bank-id.php' class='cta1'>Add Bank ID</a>
		</center>
         <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center>
      
		<br />

			 <table class='default' id='mainTable'>
			  <thead>	
			   <tr style='cursor: pointer;'>
			    <th>Bank ID</th>
			    <th>Action</th>
			   </tr>
			  </thead>
			  <tbody>
			  
			  <?php
				while ($bank_ids = $results->fetch()) {

					$id = $bank_ids['id'];
					$bank_id = $bank_ids['bank_id'];
					

					echo sprintf("
				  	    <tr><td>%s</td>
						<td><a href='edit-bank-id.php?id=%d'><img src='images/edit.png' height='15' title='Edit Bank ID'></a>&nbsp;&nbsp;<a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete Bank ID'></a></td>
						</tr>",
				  	 $bank_id, $id, $id
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