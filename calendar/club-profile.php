<?php
// Begin code by sagar
	require_once '../cOnfig/connection.php';

	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
     
	// Authenticate & authorize
	authorizeUser($accessLevel);

// echo "<pre>";
// print_r ($_SESSION);
// echo "</pre>";
// die;
	if (isset($_SESSION['customer'])) {
		$user_id = $_SESSION['customer'];
	}else {
		handleError($lang['error-nouserid'],"");
	}
    
	// Query to look up customer
		
	
	pageStart("Club profile", NULL, $deleteNoteScript, "pprofilenew", NULL, "Club profile", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$getAccess = "SELECT * FROM `db_access` WHERE domain =  '" . $_SESSION['domain'] . "'";
	try {
		$result = $pdo->prepare("$getAccess");
		$result->execute();
	} catch (PDOException $e) {
		$error = 'Error fetching user: ' . $e->getMessage();
		echo $error;
		exit();
	}
	$row = $result->fetch();
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here 
	curl_setopt_array($curl, [
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'http://localhost/Nefos/customer-api.php?cnumber=' . $row['customer'],
		CURLOPT_USERAGENT => 'Codular Sample cURL Request'
	]);
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	$error = curl_error($curl);
	// Close request to clear up some resources
	curl_close($curl);
	$response = json_decode($resp);

// echo "<pre>";
// print_r ($response);
// echo "</pre>";
// die;
	//  GET contact list 
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'http://localhost/Nefos/customer-api-contact-list.php?cnumber=128' .$row['id'],
		CURLOPT_USERAGENT => 'Codular Sample cURL Request'
	]);
	// Send the request & save response to $contact
	$contact = curl_exec($curl);
	$error = curl_error($curl);
	// Close request to clear up some resources
	curl_close($curl);
	$contactlist = json_decode($contact);

		
	//  GET Club profile 
		$curl = curl_init();
		// Set some options - we are passing in a useragent too here 
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'http://localhost/Nefos/customer-api-club-profile.php?cnumber=' .$row['id'],
			CURLOPT_USERAGENT => 'Codular Sample cURL Request'
		]);
		$res = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);
		$club_profile = json_decode($res);
		if ($club_profile->status == 1) {
			$data2 = $club_profile->data;
		}
		if ($data2) {
			
			$row = $data2[0];
			
			$domain = $row->domain;
			$db_pwd = '';
			$warning = $row->warning;
			$cutoff = date("d-m-Y", strtotime($row->cutoff));
			$db_name = "ccs_" . $domain;
			$db_user = "root";
			try	{
		 		$pdo2 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
		 		$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 		$pdo2->exec('SET NAMES "utf8"');
			}
			catch (PDOException $e)	{
		  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
		
		 		echo $output;
		 		exit();
			}
			
		}
	
?>
<center>

 <span class="secondbuttons">
  <!-- <a href="clients.php" class="cta">&laquo; Customers</a> -->
  <a href="contact-details-update.php" class="cta"><?php echo $lang['global-edit']; ?></a>
  <!-- <a href="customer-notes.php?userid=<?php echo $user_id;?>" class="cta"><?php echo $lang['add-note']; ?></a> -->
  <!-- <a href="contacts.php?userid=<?php echo $user_id;?>" class="cta">Contacts</a> -->
 </span>
</center>

<div class="overview">
 <span class="profilepicholder"><img class="profilepic" src="../images/_<?php echo $_SESSION['domain']; ?>/logo.png" /></span>
 <span class="profilefirst"><?php echo $response->data->longName_shipping;  ?> 
	 <!-- <a href="customer-contract.php?user_id=<?php echo $user_id; ?>" target="_blank"><img src="images/contract.png" style='margin-bottom: -3px; margin-left: 5px;'/></a> -->
</span>
 <br />
 
 <br />
<div id="memberNotifications"> <span class="profilethird">
<?php 
	if ($data2) {

		$selectMembers = "SELECT COUNT(memberno) from users WHERE userGroup <> 8";
		try
		{
			$result = $pdo2->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$currentMembers = $row['COUNT(memberno)'];
			
		$selectMembers = "SELECT COUNT(memberno) from users WHERE (userGroup BETWEEN '1' AND '4') OR (userGroup = 5 AND (DATE(paidUntil) >= DATE(NOW()) OR exento = 1))";
		try
		{
			$result = $pdo2->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$activeMembers = $row['COUNT(memberno)'];
			
			// Real active members last month
			$month_ini = new DateTime("first day of last month");
			$month_end = new DateTime("last day of last month");
			
			$monthBeginLast = $month_ini->format('Y-m-d'); // 2012-02-01
			$monthEndLast = $month_end->format('Y-m-d'); // 2012-02-29
			
			$selectRealActives = "SELECT COUNT( DISTINCT userid ) FROM sales WHERE saletime BETWEEN '$monthBeginLast' AND '$monthEndLast'";
			try
			{
				$result = $pdo2->prepare("$selectRealActives");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$realActiveMembers = $row['COUNT( DISTINCT userid )'];
			
			// Real active member this month
			$month_ini = new DateTime("first day of this month");
			$month_end = new DateTime("last day of this month");
			
			$monthBegin = $month_ini->format('Y-m-d'); // 2012-02-01
			$monthEnd = $month_end->format('Y-m-d'); // 2012-02-29
			
			$selectRealActives = "SELECT COUNT( DISTINCT userid ) FROM sales WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd'";
			try
			{
				$result = $pdo2->prepare("$selectRealActives");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$realActiveMembersNow = $row['COUNT( DISTINCT userid )'];
				
		// Revenue: Member payments + donations + direct dispensing
			// Look up dispensed today cash
			$selectSales = "SELECT SUM(amount) from sales WHERE saletime BETWEEN '$monthBeginLast' AND '$monthEndLast' AND direct < 3";
			try
			{
				$result = $pdo2->prepare("$selectSales");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$salesTodayCash = $row['SUM(amount)'];
		
			// Look up bar sales today cash
			$selectSales = "SELECT SUM(amount) from b_sales WHERE saletime BETWEEN '$monthBeginLast' AND '$monthEndLast' AND direct < 3";
			try
			{
				$result = $pdo2->prepare("$selectSales");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$salesTodayBarCash = $row['SUM(amount)'];
	
		// Look up donations
		$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE donatedTo <> 3 AND donationTime BETWEEN '$monthBeginLast' AND '$monthEndLast'";
		try
		{
			$result = $pdo2->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donations = $row['SUM(amount)'];
				
		// Look up today's membership fees
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paymentdate BETWEEN '$monthBeginLast' AND '$monthEndLast'";
		try
		{
			$result = $pdo2->prepare("$selectMembershipFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$membershipFees = $row['SUM(amountPaid)'];
			
			$revenue = number_format($donations + $membershipFees + $salesTodayCash + $salesTodayBarCash,2);
			
			// Look up dispensed today cash
			$selectSales = "SELECT SUM(amount) from sales WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd' AND direct < 3";
			try
			{
				$result = $pdo2->prepare("$selectSales");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$salesTodayCash = $row['SUM(amount)'];
		
			// Look up bar sales today cash
			$selectSales = "SELECT SUM(amount) from b_sales WHERE saletime BETWEEN '$monthBegin' AND '$monthEnd' AND direct < 3";
			try
			{
				$result = $pdo2->prepare("$selectSales");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$salesTodayBarCash = $row['SUM(amount)'];
	
		// Look up donations
		$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE donatedTo <> 3 AND donationTime BETWEEN '$monthBegin' AND '$monthEnd'";
		try
		{
			$result = $pdo2->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donations = $row['SUM(amount)'];
				
		// Look up today's membership fees
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paymentdate BETWEEN '$monthBegin' AND '$monthEnd'";
		try
		{
			$result = $pdo2->prepare("$selectMembershipFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$membershipFees = $row['SUM(amountPaid)'];
			
			$revenueNow = number_format($donations + $membershipFees + $salesTodayCash + $salesTodayBarCash,2);
			
		}			

	echo <<<EOD
<table style='margin-left: 0; color: white;'>
 <tr>
  <td>Members</td>
  <td class='right'><strong>$currentMembers</strong></td>
 </tr>
 <tr>
  <td>Active members&nbsp;&nbsp;&nbsp;&nbsp;</td>
  <td class='right'><strong>$activeMembers</strong></td>
 </tr>
 <tr>
  <td>Dispensed $lastMonth</td>
  <td class='right'><strong>$realActiveMembers</strong></td>
 </tr>
 <!-- <tr>
  <td>Revenue $lastMonth</td>
  <td class='right'><strong>$revenue &euro;</strong></td>
 </tr>
 <tr>
  <td>Revenue $currentMonth</td>
  <td  class='right'><strong>$revenueNow &euro;</strong></td>
 </tr> -->
</table>
EOD;

	
	

	echo "</span></div><span class='profilefourth'>";
	
	// Contract
	// Users
	// Lawyer
	
	if ($lawyer != 0) {
		
		$query = "SELECT name, telephone, email, street, streetnumber, flat, postcode, city, country FROM lawyers WHERE id = $lawyer";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$lname = $row['name'];
			$ltelephone = $row['telephone'];
			$lemail = $row['email'];
			$lstreet = $row['street'];
			$lstreetnumber = $row['streetnumber'];
			$lflat = $row['flat'];
			$lpostcode = $row['postcode'];
			$lcity = $row['city'];
			$lcountry = $row['country'];
			
	}

?>
<br />
</span>
 </div> <!-- END OVERVIEW -->
 
  <div class="clearfloat"></div><br />
  
  
<div id="profileWrapper">

<div id="leftprofile">
 <div id="detailedinfo">
  <div id="leftpane">
<h4>Club details</h4><br />
<a href="mailto:<?php echo $response->data->email; ?>"><?php echo$response->data->email; ?></a><br />
<?php echo $response->data->phone; ?><br /><br />
<?php echo $response->data->street . " " . $response->data->streetnumber . " " . $response->data->flat; ?><br />
      <?php echo $response->data->postcode; ?> <?php echo $response->data->city; ?><br />
      <?php echo $response->data->country; ?><br /><br /><br />
      
<h4>Lawyer</h4><br />
<strong><?php echo $lname; ?></strong><br />
<a href="mailto:<?php echo $lemail; ?>"><?php echo $lemail; ?></a><br />
<?php echo $ltelephone; ?><br /><br />
<?php echo $lstreet . " " . $lstreetnumber . " " . $lflat; ?><br />
      <?php echo $lpostcode; ?> <?php echo $lcity; ?><br />
      <?php echo $lcountry; ?><br /><br />

  </div> <!-- END LEFTPANE -->
  <div id="rightpane">
<h4>Contacts</h4><br />
<?php echo $contactlist; ?>
<!--<h4>System specifics</h4>
User ID: <?php echo $user_id; ?><br />
Signup source: <?php echo $signupsource; ?><br />
Card ID: <?php echo $cardid; ?><br />-->
  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->

  <div id="userPreferences">
  <div id="leftpane">
<h4><?php echo $lang['member-weeklyavgs']; ?></h4>
<?php echo $lang['global-dispenses']; ?>: <?php echo number_format($totalDispensesPerWeek,0); ?><br />
<?php echo $lang['member-spenditure']; ?>: <?php echo number_format($totalAmountPerWeek,0); ?> &euro;<br /><br />

  </div> <!-- END LEFTPANE -->
  <div id="rightpane">


  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->

  </div> <!-- END LEFTPROFILE -->
 
 
 
 </div> <!-- END PROFILEWRAPPER -->

<script>

	$("#adminComment").hover(function () {
	$("#commentText").css("display", "block");
	},function () {
	$("#commentText").css("display", "none");
	});	
	
	$("#minimizeMemberBox").click(function () {
	$("#hiddenSummary").css("display", "block");
	$("#memberbox").css("display", "none");
	});	
	
	$("#minimizeSummaryBox").click(function () {
	$("#memberbox").css("display", "block");
	$("#hiddenSummary").css("display", "none");
	});	
	
function toggleDiv(divId) {
   $("#"+divId).toggle();
}	
</script>
<?php displayFooter(); ?>
<!-- End code by sagar -->