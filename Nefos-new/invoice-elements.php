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
		$deleteElement = "DELETE FROM invoice_elements where id = $id";
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
			$_SESSION['successMessage'] = "Element deleted successfully!";
			header("location: invoice-elements.php");
			exit();
	}
	// Query to look up users
	 $selectElements= "SELECT * FROM invoice_elements order by id DESC";
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
			    name: "Elements",
			    filename: "Elements" //do not include extension
		
			  });
		
			});
		  
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
			}); 

		});
      function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this element ?')){
      	 	 window.location = "invoice-elements.php?did="+delete_id;
      	 }
      }
		
EOD;


	pageStart("Invoice Elements", NULL, $memberScript, "pmembership", NULL, "Invoice Elements", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center><a href='invoice-section.php' class='cta1'>Invoice Section</a><a href='new-invoice-element.php' class='cta1'>Add New Element</a></center>

         <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center>
      
<br />

	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>Price</th>
	    <th>Other Options</th>
	    <th>Action</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
		while ($element = $results->fetch()) {

	  	
			$custom_options = $element['custom_options'];
			if($custom_options == 1){
				$option = 'Allow Units';
			}else if($custom_options == 2){
				$option = 'Custom Amount';
			}else{
				$option = '';
			}

	echo sprintf("
  	    <tr><td>%s</td>
  	    <td class='right'>%s</td>
  	    <td>%s</td>
		<td><a href='edit-invoice-element.php?id=%d'><img src='images/edit.png' height='15' title='Edit element'></a>&nbsp;&nbsp;<a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete Element'></a></td></tr>",
  	 $element['element_en'], $element['element_price'], $option,  $element['id'], $element['id']
  	);
	  
  }
?>

	 </tbody>
	 </table>
<?php  displayFooter();