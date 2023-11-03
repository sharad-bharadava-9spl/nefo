<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	
	$user_id = $_GET['userid'];

	$userDetails = "SELECT c.id, c.registeredSince, c.Brand, c.number, c.longName, c.shortName, c.cif, c.street, c.streetnumber, c.flat, c.postcode, c.city, c.state, c.country, c.website, c.email, c.facebook, c.twitter, c.instagram, c.googleplus, c.status, c.type, c.lawyer, c.URL, c.source, c.billingType, c.phone, s.statusName, c.membermodule, c.contact, c.language, c.clubtype, c.size, c.opened, c.alias, c.launchdate, c.startdate FROM customers c, customerstatus s WHERE c.status = s.id AND c.id = $user_id";
	
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
		$registeredSince = $row['registeredSince'];
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
		$membermodule = $row['membermodule'];
		$contact = $row['contact'];
		$language = $row['language'];
		$clubtype = $row['clubtype'];
		$size = $row['size'];
		$alias = $row['alias'];
		$directdebit = $row['directdebit'];
		$launchdate = $row['launchdate'];
		$startdate = $row['startdate'];


	if ($source == '0' || $source == '') {
		
	} else {
		
		// Check if 'source' contains a hyphen, and if so, explode the string and show text input depending on whether it's a club, lawyer or accountant recommendation. What about 'OTHER'?
		
		if (strpos($source, 'Recommendation') !== false) {
			
			$recommended = 'true'; // To use further down to show text input
			
			$array = explode(" - ",$source);
			
			$recommendedID = $array[1];
			
			// Look up club name
			$query = "SELECT number, shortName FROM customers WHERE id = $recommendedID";
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
				$sourceNumber = $row['number'];
				$sourceshortName = $row['shortName'];
			
			$source = "Recommendation: $sourceNumber - $sourceshortName";
			
		} else if (strpos($source, 'Accountant') !== false) {
			
			$accountant = 'true'; // To use further down to show text input
			$array = explode(" - ",$source);
			
			$accountantNo = $array[1];
			
	      	// Query to look up accountant
			$selectGroups = "SELECT id, name FROM accountants WHERE id = '$accountantNo'";
			try
			{
				$result = $pdo3->prepare("$selectGroups");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$accountantName = $row['name'];
				
			$source = "Accountant: $accountantName";
			
		} else if (strpos($source, 'Lawyer') !== false) {
			
			$lawyer = 'true'; // To use further down to show text input
			$array = explode(" - ",$source);
			
			$lawyerNo = $array[1];
			
	      	// Query to look up lawyer
			$selectGroups = "SELECT id, name FROM lawyers WHERE id = '$lawyerNo'";
			try
			{
				$result = $pdo3->prepare("$selectGroups");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$lawyerName = $row['name'];

			$source = "Lawyer: $lawyerName";

		} else if (strpos($source, 'Other') !== false) {
			
			
			$other = 'true';
			
			$array = explode(" - ",$source);
			
			$otherValue = $array[1];
			
			$source = "Other: $source";
			
		}
		
	}		
	
	
	pageStart("Contact log", NULL, $memberScript, "pmembership", NULL, "Contact log", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center>
<div id='productoverview'>
 <table>
  <tr>
   <td>How did they find us?</td>
   <td class='right'><strong><?php echo $source; ?></strong></td>
  </tr>
  <tr>
   <td>How did they contact us?&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td class='right'><strong><?php echo $contact; ?></strong></td>
  </tr>
 </table>
</div>
</center>

<?php exit(); ?>
<br />
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});" ><img src="images/excel-new.png" style='margin-top: -40px;'/></a>
<br />

	 <table class='defaultalternate left' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Status</th>
	    <th>Created</th>
	    <th>Last reply</th>
	    <th>#</th>
	    <th>Club</th>
	    <th>Reason</th>
	    <th style='width: 200px !important;'>Issue</th>
	    <th>Replies</th>
	    <th>Description</th>
	    <th>Attachments</th>
	    <th>Action</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  	$i =0;
	  	
	  	 $main_site = str_replace("Nefos/", "", $siteroot);
		while ($feedback = $results->fetch()) {
				$feedbackid= $feedback['id'];
				$time = date('d-m-Y H:i', strtotime($feedback['created_at']));
				if ($feedback['message'] != '') {
		
						$commentRead = "
						                <span class='relativeitem'><img src='images/description.png' id='comment$feedbackid' /><div id='helpBox$feedbackid' class='helpBox'><strong>Message:</strong><br />{$feedback['message']}</div>
						                <script>
						                  	$('#comment$feedbackid').on({
										 		'mouseover' : function() {
												 	$('#helpBox$feedbackid').css('display', 'block');
										  		},
										  		'mouseout' : function() {
												 	$('#helpBox$feedbackid').css('display', 'none');
											  	}
										  	});
										</script></span>
						                ";
						
					} else {
						
						$commentRead = "";
						
					}


				$getAttachments = "SELECT file_name from feedback_attachments where feedback_id = ".$feedbackid;
				try
				{
					$attach_results = $pdo3->prepare("$getAttachments");
					$attach_results->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching attachment: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$attachCount = $attach_results->rowCount();
				if($attachCount > 0){
					$x =0;
					while($attachRow = $attach_results->fetch()){
						$attach_no = $x+1;
						$attach_arr[$i][$x] = "<a target='_blank' href='https://ccsnubev2.com/v6/".$attachRow['file_name']."' download><img src='images/paperclip.png' width='15' /></a>&nbsp;"; 
					 	$x++;	
					}
					$attachments = implode(" ", $attach_arr[$i]);
				}else{
					$attachments = '';
				}
				
				if ($feedback['status'] == 0) {
					$status = "<span class='negative'><strong>New</strong></span>";
				} else if ($feedback['status'] == 1) {
					$status = "Waiting on client";
				} else if ($feedback['status'] == 2) {
					$status = "<span class='negative'><strong>Action needed</strong></span>";
				} else {
					$status = "<img src='images/checkmark-new.png' width='18' />";
				}
				
				if ($feedback['locked'] == 0) {
					$locked = "<a href='uTil/lock-ticket.php?tid=$feedbackid&set=1'><img src='images/padlock-grey.png' height='15' title='Lock ticket'></a>";
				} else if ($feedback['locked'] == 1) {
					$locked = "<a href='uTil/lock-ticket.php?tid=$feedbackid&set=0'><img src='images/padlock.png' height='15' title='Lock ticket'></a>";
				}
				
			$selectRows = "SELECT COUNT(id) FROM feedback_comments WHERE feedbackid = '$feedbackid'";
			$rowCount = $pdo3->query("$selectRows")->fetchColumn();
			
			$query = "SELECT time FROM feedback_comments WHERE feedbackid = '$feedbackid' ORDER BY time DESC LIMIT 1";
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
				$lastreply = $row['time'];

	echo sprintf("
  	    <tr>
  	    <td>%s</td>
  	    <td>%s</td>
  	    <td>%s</td>
  	    <td>%s</td>
  	    <td style='text-transform: capitalize;'>%s</td>
  	    <td>%s</td>
  	    <td style='width: 300px !important;'>%s</td>
  	    <td class='centered'>%d</td>
  	    <td class='centered'>%s</td>
  	    <td class='centered'>%s</td>
		<td class='centered'><a href='ticket.php?ticketid=$feedbackid'><img src='images/magglass-new.png' width='15' /></a>&nbsp;&nbsp;&nbsp;$locked</td></tr>",
  	 $status, date('d-m-Y H:i', strtotime($feedback['created_at'])), date('d-m-Y H:i', strtotime($lastreply)), $feedback['number'], $feedback['club'], $feedback['reason'], $feedback['issue'], $rowCount, $commentRead, $attachments, $feedback['id']
  	);

	 $i++; 
  }
				
?>

	 </tbody>
	 </table>
<?php  displayFooter();