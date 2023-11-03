<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$domain = $_SESSION['domain'];

	if (isset($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
		
	// Query to look up customer
	$userDetails = "SELECT c.id, c.registeredSince, c.Brand, c.number, c.longName, c.shortName, c.cif, c.street, c.streetnumber, c.flat, c.postcode, c.city, c.state, c.country, c.website, c.email, c.facebook, c.twitter, c.instagram, c.googleplus, c.status, c.type, c.lawyer, c.URL, c.source, c.billingType, c.phone, s.statusName, c.membermodule, c.contact, c.language, c.clubtype, c.size, c.opened, c.alias, c.directdebit_name, c.directdebit_iban, c.launchdate, c.startdate, c.affiliation, c.location_street_name, c.location_street_number, c.location_local, c.location_postcode, c.location_city, c.location_province, c.location_country, c.phone_whatsapp, c.phone_sms FROM customers c, customerstatus s WHERE c.status = s.id AND c.id = $user_id";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$id = $row['id'];
			$registeredSince = date("M y", strtotime($row['registeredSince']));
			$Brand = $row['Brand'];
			$number = $row['number'];
			$_SESSION['customer'] = $number;
			$longName = $row['longName'];
			$shortName = $row['shortName'];
			$cif = $row['cif'];
			$street = $row['street'];
			$streetnumber = $row['streetnumber'];
			$flat = $row['flat'];
			$postcode = $row['postcode'];
			$city = $row['city'];
			$state = $row['state'];
			$country = $row['country'];
			$website = $row['website'];
			$email = $row['email'];
			$facebook = $row['facebook'];
			$twitter = $row['twitter'];
			$instagram = $row['instagram'];
			$googleplus = $row['googleplus'];
			$status = $row['status'];
			$type = $row['type'];
			$lawyer = $row['lawyer'];
			$URL = $row['URL'];
			$source = $row['source'];
			$billingType = $row['billingType'];
			$dbname = $row['dbname'];
			$dbuser = $row['dbuser'];
			$dbpwd = $row['dbpwd'];
			$signedContract = $row['signedContract'];
			$statusName = $row['statusName'];
			$telephone = $row['phone'];
			$sms = $row['phone_sms'];
			$whatsapp = $row['phone_whatsapp'];
			$membermodule = $row['membermodule'];
			$contact = $row['contact'];
			$language = $row['language'];
			$clubtype = $row['clubtype'];
			$size = $row['size'];
			$alias = $row['alias'];
			$directdebit = $row['directdebit'];
			$launchdate = $row['launchdate'];
			$startdate = $row['startdate'];
			$affiliation = $row['affiliation'];
			$location_street_name = $row['location_street_name'];
			$location_street_number = $row['location_street_number'];
			$location_local = $row['location_local'];
			$location_postcode = $row['location_postcode'];
			$location_city = $row['location_city'];
			$location_province = $row['location_province'];
			$location_city = $row['location_city'];
			$directdebit_name = $row['directdebit_name'];
			$directdebit_iban = $row['directdebit_iban'];

		$query = "SELECT domain, warning FROM db_access WHERE customer = '$number'";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$domain = $row['domain'];		
			$warning = $row['warning'];		
		
		if ($launchdate != NULL) {
			$launch = date("d-m-Y", strtotime($launchdate));
		} else {
			$launch = "";
		}
		if ($startdate != NULL) {
			$start = date("d-m-Y", strtotime($startdate));
		} else {
			$start = "";
		}

	pageStart("Client profile", NULL, $deleteNoteScript, "pprofilenew", NULL, "Client profile", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	// Logo
	$memberPhoto = "/var/www/html/ccsnubev2_com/v6/images/_$domain/logo.png";
	
	$tn = time();
	
	if (!file_exists($memberPhoto)) {
		$memberPhoto = "<img class='profilepic' src='images/ccs-square.png' />";
		$deletePhoto = '';
	} else {
		$memberPhoto = "<img class='profilepic' src='https://ccsnubev2.com/v6/images/_$domain/logo.png?$tn' width='237' />";
	}
	
	// First invoice
	$query = "SELECT invdate FROM invoices WHERE customer = '$number' ORDER BY invdate ASC LIMIT 1";
	try
	{
		$result = $pdo->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$firstInvoice = date("d M Y", strtotime($row['invdate']));
		
	if ($firstInvoice == '01 Jan 1970') {
		
		$firstInvoice = "N/A";
		
	}
	
	// Status
	if ($status == 1 || $status == 2 || $status == 3 || $status == 4 || $status == 9 || $status == 10 || $status == 12 || $status == 14 || $status == 15) {
		
		$custStatus = $statusName;
			
	} else if ($warning == 3) {
		$custStatus = "<span style='color: red;'>CUT OFF</span>";
	} else if ($daysSinceLastLog > 2) {
		$custStatus = 'Stopped using SW';
	} else if ($daysSinceLastLog < 3) {
		if ($membermodule == 1) {
			$custStatus = 'Customer - member module';
		} else {
			$custStatus = 'Customer';
		}
	} else {
		$custStatus = 'Unknown';
	}
	
	// Affiliation
	if ($affiliation > 0) {
		
		$query = "SELECT name FROM affiliations WHERE id = '$affiliation'";
		try
		{
			$result = $pdo2->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$affiliate = "<td><img src='images/affiliate.png' style='margin-bottom: -2px;' /></td><td>{$row['name']}</td>";
			
	} else {
		
		$affiliate = "<td></td><td></td>";
			
	}
	
	
	
	
	// Notes
	try
	{
		$results = $pdo2->prepare("SELECT noteid, notetime, userid, note, worker FROM customernotes WHERE userid = $user_id ORDER by notetime DESC");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user1: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	if ($results->rowCount()) {
		$userNotes = $results->fetchAll();
	} else {
		$userNotes = '';
	}
	
	// Contract signed?
	$query = "SELECT db_pwd, customer, warning, domain FROM db_access WHERE customer = '$number'";
	try
	{
		$result = $pdo->prepare("$query");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user1: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	if ($data) {

		$row = $data[0];
			$db_pwd = $row['db_pwd'];
			$customer = $row['customer'];
			$warning = $row['warning'];
			$domain = $row['domain'];

		$db_name = "ccs_" . $domain;
		$db_user = $db_name . "u";

		try	{
	 		$pdo6 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
	 		$pdo6->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$pdo6->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
			$nodb = 'true';
		}
		
		if ($nodb != 'true') {
		
			$query = "SELECT * FROM contract";
			try
			{
				$result = $pdo6->prepare("$query");
				$result->execute();
				$dataC = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user1: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			if (!$dataC) {
		
				$noContract = 'true';
				
			}
			
		} else {
			
			$noContract = 'true';
			$noDB = 'true';
		
		}
		
	} else {
		
		$noContract = 'true';
		$noDB = 'true';
			
	}
	
	// Unpaid invoices?
	$selectRows = "SELECT COUNT(invno) FROM invoices WHERE customer = '$number' AND paid = ''";
	$unpaidInvoices = $pdo->query("$selectRows")->fetchColumn();
	
		
?>

<center>
 <div id="profilebuttons">
 
<?php
	echo "<a href='bar-new-sale-2.php?user_id=$user_id' class='cta'><img src='images/new-hw-sale.png' height='18' style='margin-right: 8px; margin-bottom: -2px;' />New HW Sale</a><a href='new-call.php?user_id=$user_id' class='cta'><img src='images/new-call.png' height='18' style='margin-right: 6px; margin-bottom: -2px;' />New Call</a>";

?>
<br />
  <span id='rowtwo'>
   <a href="edit-customer.php?user_id=<?php echo $user_id;?>&warning=<?php echo $warning;?>&cutoff=<?php echo $cutoff;?>&domain=<?php echo urlencode($domain);?>" class="cta2"><?php echo $lang['global-edit']; ?></a>
   <a href="customer-notes.php?userid=<?php echo $user_id;?>" class="cta2"><?php echo $lang['add-note']; ?></a>
   <a href="feedback-client.php?userid=<?php echo $user_id;?>" class="cta2">Feedback</a>
   <a href="contacts.php?userid=<?php echo $user_id;?>" class="cta2">Contacts</a>
   <a href="contact-log.php?userid=<?php echo $user_id;?>" class="cta2">Contact Log</a>
   <a href="calls.php?userid=<?php echo $user_id;?>" class="cta2">Call Log</a>
   <a href="invoices.php?customer=<?php echo $number;?>" class="cta2">Invoices</a>
   <!--<a href="email-log.php?userid=<?php echo $user_id;?>" class="cta2">Email Log</a>-->
   <a href="javascript:delete_customer(<?php echo $user_id;?>)" class="cta3">Delete</a>
  </span>
 </div>
</center>

<div id="mainbox">
 <div id="mainleft">
  <span id="profilepicholder"><a href="new-picture.php?user_id=<?php echo $user_id; ?>"><?php echo $memberPhoto; ?></a></span>
<?php

	echo <<<EOD
   <span class='firsttext'>#$number&nbsp;<span style='font-size: 16px; color: #f6ae4a;'>($registeredSince)</span></span> <a href="client-contract.php?user_id=$user_id&number=$number" target="_blank"><img src="images/contract-new.png" style='margin-bottom: -3px; margin-left: 5px;'/></a><br /><span class='nametext'>$shortName<br /><span style='font-size: 14px;'>($longName)</span></span><br /><br /><strong>Alias:</strong> $alias<br />
   <span class='usergrouptext'>Client since $firstInvoice</span><br />
EOD;

	echo "<br /><span class='creditDisplay'>$custStatus</span><br /><br />";

	echo <<<EOD
    <table class='smallinfo'>
$bdayicon $medicalicon
    <tr>
     $affiliate
     <td></td>
     <td></td>
    </tr>
   </table>
EOD;


?>
  
 </div>
 
<?php

	// Check for all warnings, if any warning found set warningflag = 1
	
	if ($userNotes != '') {
		$i = 1;
	    $warningflag = 1;
		
		$warningbox .= <<<EOD
  <a class='smallwarning comment' href="#" id='adminComment' onClick="javascript:toggleDiv('userNotes'); return false;">
  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> Comments
EOD;

	if ($_GET['deleted'] == 'yes' || isset($_GET['openComment'])) {
		$warningbox .= "<div id='userNotes'>";
	} else {
		$warningbox .= "<div id='userNotes' style='display: none;'>";
	}
	
		$warningbox .= <<<EOD
	 <table class="default">
  	  <tr>
  	   <th class="smallerfont" style='width: 120px;'><strong>{$lang['pur-date']}</strong></th>
  	   <th class="smallerfont" style='width: 120px;'><strong>{$lang['responsible']}</strong></th>
  	   <th class="smallerfont" colspan='2'><strong>{$lang['global-comment']}</strong></th>
	  </tr>
EOD;
		foreach ($userNotes as $userNote) {
	
			if ($userNote['notetime'] == NULL) {
				$formattedDate = '';
			} else {
				$formattedDate = date("d-m-y H:i", strtotime($userNote['notetime'] . "+$offsetSec seconds"));
			}
			$noteid = $userNote['noteid'];
			$note = $userNote['note'];
			$responsible = $userNote['worker'];
			$worker = getUser($responsible);
			
		if($i == $noteCount) {
	   		$warningbox .= <<<EOD
 <tr>
  <td style='border-bottom: 0;'>$formattedDate</td>
  <td style='border-bottom: 0;'>$worker</td>
  <td style='border-bottom: 0;'>$note</td>
  <td style='border-bottom: 0;'></td>
 </tr>
EOD;
		} else {
			$warningbox .= <<<EOD
 <tr>
  <td>$formattedDate</td>
  <td>$worker</td>
  <td>$note</td>
  <td><!--<a href="javascript:void(0);" onclick= "delete_note($noteid,$user_id);" style='z-index: 22000;'><img src='images/delete.png' width='15' /></a>--></td>
 </tr>
EOD;
	}
			$warningbox .= <<<EOD
EOD;
	$i++;
		}
$warningbox .= "</table></div></a>";
	}
	
	if ($cif == '') {
		$warningbox .= <<<EOD
  <a href='edit-customer.php?user_id=$user_id' class='smallwarning dni'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> CIF missing
  </a>
EOD;
	    $warningflag = 1;
	}

	if ($noContract == 'true') {
		$warningbox .= <<<EOD
  <a href='#' class='smallwarning signature'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> Contract not signed
  </a>
EOD;
	    $warningflag = 1;
	}
	
	if ($noDB == 'true') {
		$warningbox .= <<<EOD
  <a href='#' class='smallwarning '>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> No active DB
  </a>
EOD;
	    $warningflag = 1;
	}
	
	if ($unpaidInvoices > 0) {
		$warningbox .= <<<EOD
  <a href='invoices.php?customer=$number' class='smallwarning'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> $unpaidInvoices unpaid invoice(s)
  </a>
EOD;
	    $warningflag = 1;
	}
	
	if ($warning == 3) {
		$warningbox .= <<<EOD
  <a href='#' class='smallwarning '>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> Cut off
  </a>
EOD;
	    $warningflag = 1;
	}
	
	if ($warningflag == 1) {
		
		echo <<<EOD
 <div id="mainright">
$warningbox
 </div>
EOD;

	}
?>
 </div>
 </div>
 <div class="mainwrapper">
  <div class="mainbox3">
   <div class='mainboxheader'>
    <img src="images/calendar.png" style='margin-bottom: -5px; margin-right: 5px;' /> Invoicing History
   </div>
   <div class='mainboxcontent'>
  <table class="memberStats" id="monthByMonth">
  <tbody>
   <tr>
    <th>Month</th>
    <th>Software</th>
    <th>Hardware</th>
    <th>Other</th>
    <th>TOTAL</th>
   </tr>
   
<?php

	for ($a = 0; $a < 11; $a++) {
		
		if ($a == 0) {
			
			$dateOperator = "MONTH(invdate) = MONTH(NOW()) AND YEAR(invdate) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
			
		} else {
			
			$dateOperator = "MONTH(invdate) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(invdate) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("M Y", strtotime("-$a months", strtotime("first day of this month") ));
			
		}

		// Look up invoice amounts
		$query = "SELECT SUM(amount) FROM invoices WHERE customer = '$number' AND brand = 'SW' AND $dateOperator";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$amountSW = $row['SUM(amount)'];
			
		// Look up invoice amounts
		$query = "SELECT SUM(amount) FROM invoices WHERE customer = '$number' AND brand = 'HW' AND $dateOperator";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$amountHW = $row['SUM(amount)'];
			
		// Look up invoice amounts
		$query = "SELECT SUM(amount) FROM invoices WHERE customer = '$number' AND brand <> 'SW' AND brand <> 'HW' AND $dateOperator";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$amountOther = $row['SUM(amount)'];
			
		$totalAmount = $amountSW + $amountHW + $amountOther;
			
		$month_row .= <<<EOD
 <tr>
  <td class="first">$timestamp</td>
  <td>{$expr(number_format($amountSW,2))} <span class="smallerfont">&euro;</span></td>
  <td>{$expr(number_format($amountHW,2))} <span class="smallerfont">&euro;</span></td>
  <td>{$expr(number_format($amountOther,2))} <span class="smallerfont">&euro;</span></td>
  <td><strong>{$expr(number_format($totalAmount,2))} <span class="smallerfont">&euro;</span></strong></td>
 </tr>
EOD;

	}
	
		
	$month_row .= <<<EOD
 <tr id="loadMore3">
  <td class="centered" colspan="5"><a href="#" onclick="event.preventDefault(); loadMoreMonths()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
EOD;

	echo $month_row;
?>
<form>
 <input type="hidden" id="monthID" value="11" />
</form>
   </div>
  </div>

  <div class="mainbox2">
   <div class='mainboxheader'>
    <img src="images/invoice.png" style='margin-bottom: -5px; margin-right: 5px;' /> Invoicing & Address details
   </div>
   <div class='mainboxcontent'>
       <div class='infobox'>
     <h4>Official invoicing data</h4>
     <table>
      <tr>
       <td colspan="2"><strong><?php echo $longName; ?></strong></td>
      </tr>
      <tr>
       <td><img src="images/pin-new.png" /></td>
       <td><?php echo $street . " " . $streetnumber . " " . $flat; ?><br />
      <?php echo $postcode; ?> <?php echo $city; ?><br />
      <?php echo $location_province . ", " . $country; ?>
	   </td>
      </tr>
      <tr>
       <td><img src="images/cif.png" /></td>
       <td><?php echo $cif; ?></td>
      </tr>
<?php if ($directdebit_name != '') { ?>
      <tr>
       <td><img src="images/direct-debit.png" /></td>
       <td style='vertical-align: top;'>
        <?php echo $directdebit_name; ?><br />
        <?php echo $directdebit_iban; ?>
       </td>
      </tr>
<?php } ?>
     </table>


    </div>
    <div class='infobox'>
     <h4>Commercial & Shipping</h4>
     <table>
      <tr>
       <td colspan="2"><strong><?php echo $shortName; ?></strong></td>
      </tr>
      <tr>
       <td><img src="images/pin-new.png" /></td>
       <td><?php echo $location_street_name . " " . $location_street_number . " " . $location_local; ?><br />
      <?php echo $location_postcode; ?> <?php echo $location_city; ?><br />
      <?php echo $location_province . ", " . $location_country; ?>
</td>
      </tr>
     </table>

    </div>
   </div>  
  </div>
  <div class="mainbox2">
   <div class='mainboxheader'>
    <img src="images/personal.png" style='margin-bottom: -10px; margin-right: 5px;' /> Contacts
   </div>
   <div class='mainboxcontent'>
   
<?php
			echo <<<EOD
    <div class='infobox' style='width: 98%;'>
     <h4>CLUB CONTACT DETAILS</h4>
     <table>
      <tr>
       <td>E-mail</td>
       <td><a href="mailto:<?php echo $email; ?>">$email</a></td>
      </tr>
      <tr>
       <td>Phone number(s)</td>
       <td>$telephone</td>
      </tr>
      <tr>
       <td>SMS number</td>
       <td>$sms</td>
      </tr>
      <tr>
       <td>Whatsapp number</td>
       <td>$whatsapp</td>
      </tr>
     </table>
    </div><br /><br /><br />
EOD;

		$query = "SELECT name, role, telephone, email, active FROM contacts WHERE customer = '$number' AND active <> 0 ORDER BY name ASC";
		try
		{
			$results = $pdo3->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $results->fetch()) {
			
			$cname = $row['name'];
			$crole = $row['role'];
			$ctelephone = $row['telephone'];
			$cemail = $row['email'];
			$active = $row['active'];

			
			echo <<<EOD
    <div class='infobox'>
     <h4>$cname</h4>
	 $crole<br />
<a href="mailto:<?php echo $cemail; ?>">$cemail</a><br />
$ctelephone<br /><br />&nbsp;
    </div>
EOD;
			
		}
		
		// Look up domain
		$findDomain = "SELECT domain, db_pwd, warning, cutoff FROM db_access WHERE customer = '$number'";
		try
		{
			$result = $pdo->prepare("$findDomain");
			$result->execute();
			$data2 = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		if ($data2) {

			$row = $data2[0];
			$domain = $row['domain'];
			$_SESSION['customerdomain'] = $domain;
			$db_pwd = $row['db_pwd'];
			$warning = $row['warning'];
			$cutoff = date("d-m-Y", strtotime($row['cutoff']));
			$db_name = "ccs_" . $domain;
			$db_user = $db_name . "u";
			
			/* DEBUG
			
			
			echo "domain: $domain<br />";
			echo "db_pwd: $db_pwd<br />";
			echo "db_name: $db_name<br />";
			echo "db_user: $db_user<br />";
			echo "domain: $domain<br />";
			*/
			
			// Look for db name. If it doesn't exist, throw error.

			try	{
		 		$pdoClub = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
		 		$pdoClub->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 		$pdoClub->exec('SET NAMES "utf8"');
			}
			catch (PDOException $e)	{
		  		// $output = 'Unable to connect to the database server: ' . $e->getMessage();
		
		 		// echo $output;
		 		$_SESSION['errorMessage'] = "Customer does not have a database (meaning they haven't been launched - or they have been sunset!)";
		 		$nodb = 'true';
			}
			// Activity queries
		$selectUsersU = "SELECT time FROM logins WHERE domain = '$domain' AND email <> 'super@user.com' ORDER BY time ASC LIMIT 1";
		try
		{
			$result = $pdo->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$firstLogin = date("d-m-Y", strtotime($row['time']));
			
		if ($firstLogin == '01-01-1970') {
			$firstLogin = "";
		} else {
			$firstLogin = $firstLogin;
		}

		$selectUsersU = "SELECT time FROM logins WHERE domain = '$domain' AND email <> 'super@user.com' ORDER BY time DESC LIMIT 1";
		try
		{
			$result = $pdo->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$lastLogin = date("d-m-Y", strtotime($row['time']));
			$lastLogin2 = date("d-m-Y", strtotime($row['time']) + 3600);
			
		if ($lastLogin == '01-01-1970') {
			$lastLogin = "";
		} else {
			$lastLogin = $lastLogin;
		}
		
		// Only run queries if club has a db
		if ($data2) {

		$selectUsersU = "SELECT saletime FROM sales ORDER BY saletime DESC LIMIT 1";
		try
		{
			$result = $pdoClub->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$lastSale = date("d-m-Y", strtotime($row['saletime']));
			$lastSale2 = date("d-m-Y", strtotime($row['saletime']) + 3600);
			
		if ($lastSale == '01-01-1970') {
			$lastSale = "";
		} else {
			$lastSale = $lastSale;
		}
		$selectUsersU = "SELECT logtime FROM log ORDER BY logtime DESC LIMIT 1";
		try
		{
			$result = $pdoClub->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$lastLog = date("d-m-Y", strtotime($row['logtime']));
			
		$selectRows = "SELECT COUNT(id) FROM log";
		$rowCount = $pdoClub->query("$selectRows")->fetchColumn();
		
		$selectUsersU = "SELECT paymentdate FROM memberpayments WHERE userid <> 1 ORDER BY paymentdate DESC LIMIT 1";
		try
		{
			$result = $pdoClub->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$lastMem = date("d-m-Y", strtotime($row['paymentdate']));
			$lastMem2 = date("d-m-Y", strtotime($row['paymentdate']) + 3600);
			
		if ($lastMem == '01-01-1970') {
			$lastMem = "";
		} else {
			$lastMem = $lastMem;
		}
		
		if (strtotime($lastLogin2) > strtotime($lastSale2) && strtotime($lastLogin2) > strtotime($lastMem2)) {
			$lastActivity = $lastLogin2;
		} else if (strtotime($lastSale2) > strtotime($lastMem2)) {
			$lastActivity = $lastSale2;
		} else if (strtotime($lastMem2) > strtotime($lastSale2)) {
			$lastActivity = $lastMem2;
		} else if (strtotime($lastMem2) == strtotime($lastSale2)) {
			$lastActivity = $lastMem2;
		} else {
			$lastActivity = "";
		}
		
		$from = strtotime($lastLog);
		$today = time();
		$difference = $today - $from;
		$daysSinceLog = floor($difference / 86400);

		
		
		if ($lastLog == '01-01-1970') {
			$lastLog = "";
		} else {
			$lastLog = $lastLog;
		}
			
		} else {
			$domain = 'NONE';
		}
		
	$selectMembers = "SELECT COUNT(memberno) from users WHERE userGroup <> 8";
	try
	{
		$result = $pdoClub->prepare("$selectMembers");
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
		$result = $pdoClub->prepare("$selectMembers");
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

	}
?>
  </div>
 </div>
 <br /><br />
 <div class='clearFloat'></div>
   <div id="mainbox">
   <div class='mainboxheader'>
    <img src="images/consumo.png" style='margin-bottom: -8px; margin-right: 5px;' /> ACTIVITY
   </div>
   <div class='mainboxcontent'>
    <div class='infobox'>
     <h4>Software</h4>
  <table class="default memberStats">
   <tr>
    <td class="first">Launched:</td>
    <td><?php echo $launch; ?></td>
   </tr>
   <tr>
    <td class="first">First login:</td>
    <td><?php echo $firstLogin; ?></td>
   </tr>
   <tr>
    <td class="first">Start date:</td>
    <td><?php echo $start; ?></td>
   </tr>
   <tr>
    <td class="first">Last login:</td>
    <td><?php echo $lastLogin; ?></td>
   </tr>
   <tr>
    <td class="first">Last dispense:</td>
    <td><?php echo $lastSale; ?></td>
   </tr>
   <tr>
    <td class="first">Last member activity:</td>
    <td><?php echo $lastMem; ?></td>
   </tr>
   <tr>
    <td class="first">Last activity:</td>
    <td><?php echo $lastActivity; ?></td>
   </tr>
   <tr>
    <td class="first">Last log entry:</td>
    <td><?php echo $lastLog; ?></td>
   </tr>
  </table>       
    </div>
    <div class='infobox'>
     <h4>Members</h4>
     <table style='width: 100%'>
      <tr>     <td><span class='maintext'>Members:</span> <?php echo number_format($currentMembers,0); ?></td>
       <td style='text-align: right;'><span class='maintext'>Active members:</span> <?php echo number_format($activeMembers,0); ?></td>
      </tr>
     </table>
     <br />
     <h4>Members dispensed</h4>
  <table class="default memberStats" id="membersales">
  <tbody>

<?php
	for ($a = 0; $a < 5; $a++) {
		
		if ($a == 0) {
			
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
			
		} else {
			
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("M Y", strtotime("-$a months", strtotime("first day of this month") ));
			
		}

		if ($data2) {
		// Look up member dispenses
		$query = "SELECT COUNT(DISTINCT userid) FROM sales WHERE $dateOperator";
		try
		{
			$result = $pdoClub->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$dispensed = $row['COUNT(DISTINCT userid)'];
			
		}
			
		$dispensed_row .= <<<EOD
 <tr>
  <td class="first">$timestamp</td>
  <td>$dispensed</td>
 </tr>
EOD;

	}
	
		
	/*$dispensed_row .= <<<EOD
 <tr id="loadMore3">
  <td class="centered" colspan="2"><a href="#" onclick="event.preventDefault(); loadMoreMonths()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
EOD;
*/
	echo $dispensed_row;
			if ($data2) {

	$selectRealActives = "SELECT creditOrDirect FROM systemsettings";
	try
	{
		$result = $pdoClub->prepare("$selectRealActives");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$creditOrDirect = $row['creditOrDirect'];
		
	}
		

		
	if ($creditOrDirect == 0) {
		$dispensemode = 'Mode: Direct dispensing';
	} else {
		$dispensemode = 'Mode: Credit';
	}

	
?>
  </table>       
     </div>
      <br /><br /><br />
    <div class='infobox' style='width: 100%;'>
       
     <h4>Revenue (<?php echo $dispensemode; ?>)</h4>
  <table class="memberStats" id="monthByMonth">
  <tbody>
   <tr>
    <th>Month</th>
    <th class='right'>Donations</th>
    <th class='right'>Membership fees</th>
    <th class='right'>Direct dispenses</th>
    <th class='right'>Direct bar sales</th>
    <th class='right'>TOTAL</th>
   </tr>

<?php

	for ($a = 0; $a < 13; $a++) {
		
		if ($a == 0) {
			
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$dateOperator2 = "MONTH(donationTime) = MONTH(NOW()) AND YEAR(donationTime) = YEAR(NOW())";
			$dateOperator3 = "MONTH(paymentdate) = MONTH(NOW()) AND YEAR(paymentdate) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
			
		} else {
			
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$dateOperator2 = "MONTH(donationTime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$dateOperator3 = "MONTH(paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("M Y", strtotime("-$a months", strtotime("first day of this month") ));
			
		}
		if ($data2) {

		// Look up dispensed today cash
		$selectSales = "SELECT SUM(amount) from sales WHERE direct < 3 AND $dateOperator";
		try
		{
			$result = $pdoClub->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesToday = $row['SUM(amount)'];
	
		// Look up bar sales today cash
		$selectSales = "SELECT SUM(amount) from b_sales WHERE direct < 3 AND $dateOperator";
		try
		{
			$result = $pdoClub->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayBar = $row['SUM(amount)'];

	// Look up donations
	$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE donatedTo <> 3 AND $dateOperator2";
	try
	{
		$result = $pdoClub->prepare("$selectDonations");
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
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE $dateOperator3";
	try
	{
		$result = $pdoClub->prepare("$selectMembershipFees");
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
		
	}
		
		$revenueNow = $donations + $membershipFees + $salesTodayCash + $salesTodayBarCash;
		
					
		$revenue_row .= <<<EOD
 <tr>
  <td class="first">$timestamp</td>
  <td>{$expr(number_format($donations,0))} <span class="smallerfont">&euro;</span></td>
  <td>{$expr(number_format($membershipFees,0))} <span class="smallerfont">&euro;</span></td>
  <td>{$expr(number_format($salesToday,0))} <span class="smallerfont">&euro;</span></td>
  <td>{$expr(number_format($salesTodayBar,0))} <span class="smallerfont">&euro;</span></td>
  <td><strong>{$expr(number_format($revenueNow,0))} <span class="smallerfont">&euro;</span></strong></td>
 </tr>
EOD;

	}

	
		
	/*$dispensed_row .= <<<EOD
 <tr id="loadMore3">
  <td class="centered" colspan="2"><a href="#" onclick="event.preventDefault(); loadMoreMonths()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
EOD;
*/
	echo $revenue_row;
	
?>
  </table>       

   </div>
   </div>
   </div>


 <br /><br />
 <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a><br /><br />
 	 <table class="default" id='mainTable' style='width: 1144px;'>
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th>&euro;</th>
	    <th>Tot. u</th>
	    <th>Tot. <?php echo $_SESSION['currencyoperator'] ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  
	  /*
	// Pagination
	if (isset($_GET['pageno'])) {
    	$pageno = $_GET['pageno'];
    } else {
    	$pageno = 1;
    }
    	$no_of_records_per_page = 20;
	
    $offset = ($pageno-1) * $no_of_records_per_page; 

    $total_pages_sql = "SELECT COUNT(saleid) FROM sales WHERE userid = $user_id";
	$rowCount1 = $pdo3->query("$total_pages_sql")->fetchColumn();
    $total_pages_sql = "SELECT COUNT(saleid) FROM b_sales WHERE userid = $user_id";
	$rowCount2 = $pdo3->query("$total_pages_sql")->fetchColumn();
    $total_pages_sql = "SELECT COUNT(donationid) FROM donations WHERE userid = $user_id";
	$rowCount3 = $pdo3->query("$total_pages_sql")->fetchColumn();
    $total_pages_sql = "SELECT COUNT(paymentid) FROM memberpayments WHERE userid = $user_id";
	$rowCount4 = $pdo3->query("$total_pages_sql")->fetchColumn();
	
	$rowCount = $rowCount1 + $rowCount2 + $rowCount3 + $rowCount4;
    
    $total_pages = ceil($rowCount / $no_of_records_per_page);

*/

	// Query to look up individual sales
	$selectSales = "SELECT saleid, saletime, userid, amount, '' AS amountpaid, '' AS quantity, unitsTot AS units, adminComment, creditBefore, creditAfter, 'bar' as Type, '' AS donatedTo FROM b_sales WHERE userid = $user_id ORDER by saletime DESC";
		try
		{
			$resultsS = $pdo3->prepare("$selectSales");
			$resultsS->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}


		while ($sale = $resultsS->fetch()) {
	
		$formattedDate = date("d M Y H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$type = $sale['Type'];
		$donatedTo = $sale['donatedTo'];
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$first_name = $row['first_name'];
		$memberno = $row['memberno'];
		

		
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM b_salesdetails d, b_sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$totResult = $onesaleResult->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	   
		echo "
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			
			// Look up bar category
			$selectBarCat = "SELECT name FROM b_categories WHERE id = {$onesale['category']}";
		try
		{
			$result = $pdo3->prepare("$selectBarCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$barRow = $result->fetch();
		   		$category = $barRow['name'];
			
			
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			
			$productid = $onesale['productid'];
			
		$selectProduct = "SELECT name FROM b_products WHERE productid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$name = $row['name'];


			echo $name . "<br />";
		}
		echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['quantity'],0) . "<br />";
		}
		echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
	  	   	foreach ($totResult as $onesale) {	
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,1);
		echo "
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$units} u</strong></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$amount} <span class='smallerfont'>&euro;</span></strong></td></tr>

		";
		

}

?>
  </tbody>
 </table>
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

function loadMoreMonths(){
	
	// Add 'Loading' text
	$("#loadMore3").remove();
	$("#monthByMonth").append("<tr id='monthLoading'><td colspan='5' class='centered'>Loading. Please wait...</td></tr>");

	// Take dayID, send it to Ajax
	var monthJSID = parseInt($("#monthID").val());
    $.ajax({
      type:"post",
      url:"get-invoice-month.php?month="+monthJSID+"&number=<?php echo $number; ?>",
      datatype:"text",
      success:function(data)
      {
			$("#monthLoading").remove();
	       	$('#monthByMonth tbody').append(data);
      }
    });
	
	$("#monthID").val(monthJSID + 11);
    
};
</script>

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
<a href="?pageno=1<?php echo $sortparam . '&user_id=' . $user_id; ?>" class='pagination <?php if ($pageno == 1 || (!isset($_GET['pageno']))) { echo 'disabled'; } ?>'>&laquo;</a>
<a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?pageno=".($pageno - 1); } echo $sortparam . '&user_id=' . $user_id; ?>" class='pagination <?php if($pageno <= 1){ echo 'disabled'; } ?>'>Prev</a>
<a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo "?pageno=".($pageno + 1); } echo $sortparam . '&user_id=' . $user_id; ?>" class='pagination <?php if ($total_pages == $pageno){ echo 'disabled'; } ?>'>Next</a>
<a href="?pageno=<?php echo $total_pages; echo $sortparam . '&user_id=' . $user_id; ?>" class='pagination <?php if ($total_pages == $pageno){ echo 'disabled'; } ?>'>&raquo;</a>
</center>
<!-- Pagination code END -->

<?php displayFooter();