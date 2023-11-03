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
		$deleteDepartment = "DELETE FROM department_cat where id = $id";
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
			$_SESSION['successMessage'] = "Department category deleted successfully!";
			header("location: department-category.php");
			exit();
	}
	// Query to look up users
	 $selectUsers = "SELECT * FROM department_cat order by id DESC";
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
      	 	 window.location = "department-category.php?did="+delete_id;
      	 }
      }
EOD;


	pageStart("Department Categories", NULL, $memberScript, "pmembership", NULL, "Department Categories", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center><a href='new-department-cat.php' class='cta'>New Department Category</a></center>

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
	    <th>Department Name</th>
	    <th>Category</th>
	    <th>Action</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($departemt_cat = $results->fetch()) {
			$department_id = $departemt_cat['department_id'];
			$getDepartment = "SELECT name from departments where id =".$department_id;
		try
		{
			$result = $pdo3->prepare("$getDepartment");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
    $depRow = $result->fetch();
    $department_name = $depRow['name'];
	echo sprintf("
  	    <tr><td class='clickableRow' href='edit-department-cat.php?id=%d'>%s</td>
  	    <td class='clickableRow' href='edit-department-cat.php?id=%d'>%s</td>
		<td><a href='edit-department-cat.php?id=%d'><img src='images/edit.png' height='15' title='Edit user'></a>&nbsp;&nbsp;<a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete department'></a></td></tr>",
  	  $departemt_cat['id'], $department_name, $departemt_cat['id'], $departemt_cat['category'], $departemt_cat['id'], $departemt_cat['id']
  	);
	  
  }
?>

	 </tbody>
	 </table>
<?php  displayFooter();