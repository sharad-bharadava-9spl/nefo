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
		// delete department
		$id= $_GET['did'];
		$deleteElement = "DELETE FROM credit_reasons where id = $id";
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
			$_SESSION['successMessage'] = "Reason deleted successfully!";
			header("location: credit-reasons.php");
			exit();
	}
	// Query to look up users
	 $selectElements= "SELECT * FROM credit_reasons order by id DESC";
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
			    exclude: ".noExl",
			    name: "Reasons",
			    filename: "Reasons" //do not include extension
		
			  });
		
			});
		  
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
			}); 

		});
      function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this element ?')){
      	 	 window.location = "credit-reasons.php?did="+delete_id;
      	 }
      }
		
EOD;


	pageStart("Credits", NULL, $memberScript, "pmembership", NULL, "Credits", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

		<center>
			<a href='credit-movements.php' class='cta1'>Credit Movements</a>
			<a href='credits.php' class='cta1'>Credits</a>
			<a href='new-credit-reason.php' class='cta1'>Add Credit Reason</a>
		</center>
         <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center>
      
		<br />

			 <table class='default' id='mainTable'>
			  <thead>	
			   <tr style='cursor: pointer;'>
			    <th>Credit Reason</th>
			    <th>Action</th>
			   </tr>
			  </thead>
			  <tbody>
			  
			  <?php
				while ($reason = $results->fetch()) {


					echo sprintf("
				  	    <tr><td>%s</td>
						<td><a href='edit-credit-reason.php?id=%d'><img src='images/edit.png' height='15' title='Edit reason'></a>&nbsp;&nbsp;<a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete Element'></a></td></tr>",
				  	 $reason['reason'], $reason['id'], $reason['id']
				  	);
					  
				  }
				?>

			 </tbody>
			 </table>
<?php  displayFooter();