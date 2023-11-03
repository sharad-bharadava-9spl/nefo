<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	if(isset($_GET['did'])){
		// delete department
		$id= $_GET['did'];
		$deleteDepartment = "DELETE FROM issues where id = $id";
			try
			{
				$results = $pdo3->prepare("$deleteDepartment");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$_SESSION['successMessage'] = "Issue deleted successfully!";
			header("location: issues.php");
			exit();
	}
	// Query to look up users
	 $selectUsers = "SELECT * FROM issues order by id DESC";
		try
		{
			$results = $pdo3->prepare("$selectUsers");
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
			    name: "Customers",
			    filename: "Customers" //do not include extension
		
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
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
	function delete_element(delete_id){
      	 if(confirm('Are you sure to delete it ?')){
      	 	 window.location = "issues.php?did="+delete_id;
      	 }
      }
EOD;


	pageStart("Issues", NULL, $memberScript, "pmembership", NULL, "Issues", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center><a href='new-issue.php' class='cta'>New Issue</a></center>

	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />

	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Issue</th>
	    <th>Action</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($issue = $results->fetch()) {

	echo sprintf("
  	    <tr><td class='clickableRow' href='edit-issue.php?id=%d'>%s</td>
		<td><a  href='edit-issue.php?id=%d'><img src='images/edit.png' height='15' title='Edit user'>&nbsp;&nbsp;<a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete Issue'></a></td></tr>",
  	  $issue['id'], $issue['issue'], $issue['id'], $issue['id']
  	);
	  
  }
?>

	 </tbody>
	 </table>
<?php  displayFooter();