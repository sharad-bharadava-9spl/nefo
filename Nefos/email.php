<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);	    
	    
	// Query to look up users
	$selectUsers = "SELECT u.memberno, u.first_name, u.last_name, ug.groupName, u.email, u.telephone FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND memberno <> '0' ORDER by memberno DESC";
	
	$result = mysql_query($selectUsers)
		or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		
	$tableScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){
			
				  $("#mainTable").table2excel({
				    name: "Donaciones",
				    filename: "Donaciones" //do not include extension
			
				  });
			
				});
					    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$('#mainTable').tablesorter({
				usNumberFormat: true
			}); 
			
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
	});

EOD;

		
	
	pageStart($lang['title-members'], NULL, $tableScript, "pusers", "memberlist", $lang['member-memberemails'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
     
	 <table class="default" id="mainTable">
	  <thead>
	   <tr>
	    <th>#</th>
	    <th><?php echo $lang['member-firstname']; ?></th>
	    <th><?php echo $lang['member-lastname']; ?></th>
	    <th><?php echo $lang['member-group']; ?></th>
	    <th><?php echo $lang['member-email']; ?></th>
	    <th><?php echo $lang['member-telephone']; ?></th>
	   </tr>
	  </thead>
	  
	  <?php

while ($user = mysql_fetch_array($result)) {

	$user_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
	  </tr>",
	  $user['memberno'], $user['first_name'], $user['last_name'], $user['groupName'], $user['email'], $user['telephone']
	  );
	  echo $user_row;
  }
?>

	 </tbody>
	 </table>
	 
<?php  displayFooter(); ?>
