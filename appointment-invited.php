<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	// Pagination
	if (isset($_GET['pageno'])) {
    	$pageno = $_GET['pageno'];
    } else {
    	$pageno = 1;
    }
    if (isset($_SESSION['pagination'])) {
    	$no_of_records_per_page = $_SESSION['pagination'];
	} else {
    	$no_of_records_per_page = 200;
	}
	
    $query = "SELECT COUNT(*) FROM users WHERE citainvited = 1";
	$rowTot = $pdo3->query("$query")->fetchColumn();
	
    $offset = ($pageno-1) * $no_of_records_per_page; 

    $total_pages_sql = "SELECT COUNT(*) FROM users WHERE userGroup <> 8";
	$rowCount = $pdo3->query("$total_pages_sql")->fetchColumn();
    
    $total_pages = ceil($rowCount / $no_of_records_per_page);

	// Query to look up users
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.paidUntil, u.registeredSince, u.userGroup, u.last_name, u.credit, u.email, u.telephone, SUM(s.amount), u.citainvited FROM users u, sales s WHERE u.user_id = s.userid AND u.userGroup <> 8 GROUP BY user_id ORDER by SUM(s.amount) DESC LIMIT $offset, $no_of_records_per_page";
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
	

	pageStart($lang['invite'], NULL, $memberScript, "pmembership", NULL, $lang['invite'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>

<div id='filterbox'>
 <div class='boxcontent'>
 <?php echo $lang['invite-instructions']; ?>
 </div>
</div>
<br /><br />
<div id='productoverview'>
<?php echo $lang['members-invited']; ?>: <?php echo $rowTot; ?>
</div>
</center>

<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th>E-mail</th>
	    <th><?php echo $lang['member-telephone']; ?></th>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
	    <th><?php echo $lang['global-credit']; ?></th>
<?php } ?>
	    <th><?php echo $lang['global-registered']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['member-group']; ?></th>
<?php if ($_SESSION['membershipFees'] == 1) { ?>
	    <th><?php echo $lang['expiry']; ?></th>
<?php } ?>
	    <th><?php echo $lang['last-dispense']; ?></th>
	    <th><?php echo $lang['closeday-dispensed']; ?></th>
	    <th><?php if ($_SESSION['lang'] == 'es') { echo "Invitado"; } else { echo "Invited"; } ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {

			$paidUntil = $user['paidUntil'];
			$telephone = $user['telephone'];
			$email = $user['email'];
			$citainvited = $user['citainvited'];
			
			// Check email validity
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$email = "<span style='color: red; font-weight: 800;'>$email</a>";
				$checkOrNot = "<input type='checkbox' name='giveBaja[%d]' value='%d' style='width: 12px; display: none;'/>";
			} else {
				$email = $email;
				$checkOrNot = "<input type='checkbox' name='giveBaja[%d]' value='%d' style='width: 12px;' />";
			}

	

		$dispQuery = "SELECT groupName FROM usergroups WHERE userGroup = {$user['userGroup']}";
		try
		{
			$resultC = $pdo3->prepare("$dispQuery");
			$resultC->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$rowC = $resultC->fetch();
			$groupName = $rowC['groupName'];
	
	// Look up last dispense date
	$dispQuery = "SELECT saletime FROM sales WHERE userid = {$user['user_id']} ORDER BY saletime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$dispQuery");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if (!$data) {
		
		$lastDispense = "<span class='white'>00-00-0000</span>";
		
	} else {
		
		$rowD = $data[0];
			$lastDispense = date("d-m-Y", strtotime($rowD['saletime']));
			
	}
	
	if ($user['usageType'] == '1') {
		$usageType = "<img src='images/medical.png' width='16' /><span style='display:none'>1</span>";
	} else {
		$usageType = '';
	}
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d-m-Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');

	if ($user['userGroup'] > 4 && $exento == 0) {
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			$membertill = "<span class='mid'>$memberExpReadable</span>";
	  	} else if (strtotime($memberExp) < strtotime($timeNow)) {
		  	$membertill = "<span class='negative'>$memberExpReadable</span>";
		} else if (strtotime($memberExp) > strtotime($timeNow)) {
		  	$membertill = "<span class='positive'>$memberExpReadable</span>";
		}
		
	} else {
		
		$membertill = "<span class='white'>00-00-0000</span>";
		
	}
	
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>",
	  $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], $email, $user['user_id'], $telephone);
	  

if ($_SESSION['creditOrDirect'] == 1) {
	
	echo sprintf("
  	   <td class='clickableRow right' href='profile.php?user_id=%d'>%0.1f &euro;</td>",
  	  $user['user_id'], $user['credit']);
  	  
}

	echo sprintf("
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: center;' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='profile.php?user_id=%d'>%s</td>",
  	  $user['user_id'], date("d-m-Y",strtotime($user['registeredSince'])), $user['user_id'], $usageType, $user['user_id'], $groupName);

if ($_SESSION['membershipFees'] == 1) {
	  
	echo sprintf("<td class='clickableRow %s' href='profile.php?user_id=%d'>%s</td>",
   $paidClass, $user['user_id'], $membertill);
	    
}

	echo sprintf("<td class='clickableRow' href='profile.php?user_id=%d'>%s</td><td class='clickableRow right' href='profile.php?user_id=%d'><strong>%s &euro;</strong></td>",
   $user['user_id'], $lastDispense, $user['user_id'], $user['SUM(s.amount)']);
   
	if ($citainvited == 1) {
		$citainvited = "<img src='images/complete.png' width='16' />";
	} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$citainvited = "";
	} else {
		$citainvited = "<a href='uTil/cita-invite.php?user_id={$user['user_id']}'><span style='color: red;'>{$lang['global-no']}</span></a>";
	} 
	
			


	echo sprintf("<td class='centered'>%s</td>",
  $citainvited);


}
?>

	 </tbody>
	 </table>

<!-- Pagination code BEGIN -->
<style>
a.pagination {
	display: inline-block;
	background-color: #eee;
	border: 1px solid #ccc;
	width: 50px;
	height: 50px;
	line-height: 50px;
	margin: 5px;
	color: #333;
}
a.pagination.disabled {
	background-color: #ccc;
	border: 1px solid #aaa;
}
</style>
<center>
<br />
<a href="?pageno=1<?php echo $sortparam; ?>" class='pagination <?php if ($pageno == 1 || (!isset($_GET['pageno']))) { echo 'disabled'; } ?>'>&laquo;</a>
<a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?pageno=".($pageno - 1); } echo $sortparam; ?>" class='pagination <?php if($pageno <= 1){ echo 'disabled'; } ?>'>Prev</a>
<a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo "?pageno=".($pageno + 1); } echo $sortparam; ?>" class='pagination <?php if ($total_pages == $pageno){ echo 'disabled'; } ?>'>Next</a>
<a href="?pageno=<?php echo $total_pages; echo $sortparam; ?>" class='pagination <?php if ($total_pages == $pageno){ echo 'disabled'; } ?>'>&raquo;</a>
</center>
<!-- Pagination code END -->

<?php  displayFooter(); ?>
