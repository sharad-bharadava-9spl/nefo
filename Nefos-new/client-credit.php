<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
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


	pageStart("Client Credits", NULL, $memberScript, "pmembership", NULL, "Client Credits", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

		<center>
			<a href='invoice-section.php' class='cta1'>Invoice Section</a>
			<a href='credits.php' class='cta1'>Credits</a>
		</center>

<?php  displayFooter();