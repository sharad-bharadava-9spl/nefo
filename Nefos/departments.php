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
		$deleteDepartment = "DELETE FROM departments where id = $id";
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
			$_SESSION['successMessage'] = "Department deleted successfully!";
			header("location: departments.php");
			exit();
	}
	// Query to look up users
	 $selectUsers = "SELECT * FROM departments order by id DESC";
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
			    filename: "departments" //do not include extension
		
			  });
		
			});
		  
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
			}); 

		});
      function delete_element(delete_id){
      	 if(confirm('Are you sure to delete it ?')){
      	 	 window.location = "departments.php?did="+delete_id;
      	 }
      }
		
EOD;


	pageStart("Departments", NULL, $memberScript, "pmembership", NULL, "Departments", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center><a href='new-department.php' class='cta'>New Department</a></center>

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
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>Action</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($departemt = $results->fetch()) {

	echo sprintf("
  	    <tr><td class='clickableRow'>%s</td>
		<td><a href='edit-department.php?id=%d'><img src='images/edit.png' height='15' title='Edit department'></a>&nbsp;&nbsp;<a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete department'></a></td></tr>",
  	 $departemt['name'], $departemt['id'], $departemt['id']
  	);
	  
  }
?>

	 </tbody>
	 </table>
<?php  displayFooter();