<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_GET['userid'])) {
		
		$customer = $_GET['userid'];
		$_SESSION['cid'] = $customer;
		
	}
	
	// Query to look up contacts
	$selectCats = "SELECT id, name, telephone, email, role, comment, active FROM contacts WHERE customer = '{$_SESSION['customer']}' ORDER by name ASC";
	try
	{
		$resultP = $pdo3->prepare("$selectCats");
		$resultP->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
		
	
	$deleteCategoryScript = <<<EOD
function delete_contact(contactid) {
	
		if (confirm("Are you sure you want to delete this contact? You can NOT undo this action!")) {
			window.location = "uTil/delete-contact.php?contactid=" + contactid;
		}
		
}
EOD;
	pageStart("Contacts", NULL, $deleteCategoryScript, "pproducts", "admin", "Contacts", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center><a href="customer.php?user_id=<?php echo $_SESSION['cid']; ?>" class="cta">&laquo; Customer</a> <a href="new-contact.php" class="cta">New contact</a></center>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>Role</th>
	    <th>Telephone</th>
	    <th>Email</th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($category = $resultP->fetch()) {

	$categoryid = $category['id'];
	if ($category['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$categoryid' /><div id='helpBox$categoryid' class='helpBox'>{$category['comment']}</div>
		                <script>
		                  	$('#comment$categoryid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$categoryid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$categoryid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}
	
	if ($category['active'] == 0) {
		$style = "style='background-color: #ccc;'";
	} else {
		$style = "";
	}

	$flower_row = sprintf("
  	  <tr>
  	   <td class='clickableRow' $style href='edit-contact.php?contactid=%d'>%s</td>
  	   <td class='clickableRow' $style href='edit-contact.php?contactid=%d'>%s</td>
  	   <td class='clickableRow' $style href='edit-contact.php?contactid=%d'>%s</td>
  	   <td class='clickableRow' $style href='edit-contact.php?contactid=%d'>%s</td>
	   <td class='relative' $style>$commentRead</td>
  	   <td $style ><a href='javascript:delete_contact(%d)'><img src='images/delete.png' height='15' title='Delete contact' /></a></td>
	  </tr>",
	  $categoryid, $category['name'], $categoryid, $category['role'], $categoryid, $category['telephone'], $categoryid, $category['email'], $categoryid
	  );
	  echo $flower_row;
  }
?>



	 </tbody>
	 </table>

<?php  displayFooter(); ?>
