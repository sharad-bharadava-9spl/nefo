<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		if ($filterVar == 100) {
			
			$limitVar = "100";
			$optionList = "<option value='$filterVar'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 250) {
			
			$limitVar = "250";
			$optionList = "<option value='$filterVar'>{$lang['last']} 250</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 500) {
			
			$limitVar = "500";
			$optionList = "<option value='$filterVar'>{$lang['last']} 500</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>";
			
		} else {
			
			// Grab month and year number
			$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
			$year = substr($filterVar, strrpos($filterVar, '-') + 1);
			
			$timeLimit = "AND MONTH(registeredSince) = $month AND YEAR(registeredSince) = $year";
			
			$optionList = "<option value='filterVar'>$filterVar</option>
				<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";		
		}
			
	} else {
		
		$limitVar = "100";
		
		$optionList = "<option value=''>{$lang['filter']}</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	// Pagination
 if (isset($_GET['pageno']) && $_GET['pageno'] != '') {
    	$pageno = $_GET['pageno'];
    } else {
    	$pageno = 1;
    }
    if (isset($limitVar)) {
    	$no_of_records_per_page = $limitVar;
	} else {
    	$no_of_records_per_page = 2;
	}
	
    $offset = ($pageno-1) * $no_of_records_per_page; 

    $total_pages_sql = "SELECT COUNT(*) FROM customers WHERE 1 $timeLimit";
	$rowCount = $pdo3->query("$total_pages_sql")->fetchColumn();
    
    $total_pages = ceil($rowCount / $no_of_records_per_page);
	
	// Query to look up sales
	$selectClubs = "SELECT id, registeredSince, longName, cif, city, state, country, website, email, phone, language, club_status FROM customers WHERE  1 $timeLimit ORDER by registeredSince DESC LIMIT $offset, $no_of_records_per_page";
		try
		{
			$results = $pdo3->prepare("$selectClubs");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	// Create month-by-month split
	$findStartDate = "SELECT registeredSince FROM customers ORDER BY registeredSince ASC LIMIT 1";
	try
	{
		$result = $pdo3->prepare("$findStartDate");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$startDate = date('01-m-Y', strtotime($row['registeredSince']));
		$endDate = date('01-m-Y');
		$endDateShort = date('m-Y', strtotime($endDate));
		
		
		
	if ($endDateShort != $filterVar) {
		$optionList .= "<option value='$endDateShort'>$endDateShort</option>";
	}
	
	$genDateFull = date('01-m-Y', strtotime($endDate));
	$genDate = date('m-Y', strtotime($genDateFull));
	
		
		
	while (strtotime($genDateFull) > strtotime($startDate)) {
	
		
		$genDateFull = date('01-m-Y', strtotime("$genDateFull - 1 month"));
		$genDate = date('m-Y', strtotime($genDateFull));
		
		// Exclude option if already selected
		if ($genDate != $filterVar) {
			$optionList .= "<option value='$genDate'>$genDate</option>";
		}

	}

		
	
	$deleteSaleScript = <<<EOD
	    $(document).ready(function() {
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Retiradas",
			    filename: "Retiradas" //do not include extension

			  });

			});

			
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});


EOD;
	pageStart("CLUBS", NULL, $deleteSaleScript, "pclubs", "clubs admin", "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<style type="text/css">
	    #pclubs a.disabled{
            color: #dcdad8;
            text-decoration: none;
    }
</style>
<center><a href='new-club-db.php' class='cta'>Upload Club DB</a></center>
	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
        <form action='' method='POST'>
	     <select id='filter' name='filter' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
        </form>
       </td>
      </tr>
     </table>
<br />
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th>Club</th>
	    <th>CIF</th>
	    <th>City</th>
	    <th>State</th>
	    <th>Country</th>
	    <th>Website</th>
	    <th>Contact Number</th>
	    <th>Email</th>
	    <th>Request Status</th>
	    <th>Language</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
		while ($club = $results->fetch()) {
	
		$formattedDate = date("d-m-Y H:i", strtotime($club['registeredSince']."+$offsetSec seconds"));
		$registeredSince = $club['registeredSince'];
		$clubid = $club['id'];
		$clubName = $club['longName'];
		$cif = $club['cif'];
		$city = $club['city'];
		$state = $club['state'];
		$country = $club['country'];
		$website = $club['website'];
		$email = $club['email'];
		$phone = $club['phone'];
		$status = $club['club_status'];
		$language = $club['language'];

		if($status == 0){
			$club_request = "Pending";
			$color = "#FF5722";
		}else if($status == 1){
			$club_request = "Approved";
			$color = "#159938";
		}else{
			$club_request = "Rejected";
			$color = "#F44336";
		}

			
			
				echo "<tr>
		  	   		<td class='clickableRow' href='club.php?club_id={$clubid}'>$formattedDate";
					echo "</td>
		  	   <td class='clickableRow' href='club.php?club_id={$clubid}'>$clubName";
					echo "</td>
		  	   <td class='clickableRow' href='club.php?club_id={$clubid}'>$cif";
					echo "</td>
		  	   <td class='clickableRow' href='club.php?club_id={$clubid}'>$city";
				echo "</td><td class='clickableRow' href='club.php?club_id={$clubid}'>$state";
				echo "</td><td class='clickableRow' href='club.php?club_id={$clubid}'>$country";
				echo "</td><td class='clickableRow' href='club.php?club_id={$clubid}'>$website";
				echo "</td>";
				echo "
				<td class='clickableRow' href='club.php?club_id={$clubid}'>{$phone}</td>
				<td class='clickableRow' href='club.php?club_id={$clubid}'>{$email}</td>
				<td class='clickableRow' style='color:{$color}' href='club.php?club_id={$clubid}'>{$club_request}</td>
				<td class='clickableRow' href='club.php?club_id={$clubid}'>{$language}</td>
				</tr>
				";
				
	}

	
?>

	 </tbody>
	 </table>
	<center>
		<br />
		<a href="?pageno=1" class='pagination <?php if ($pageno == 1 || (!isset($_GET['pageno']))) { echo 'disabled'; } ?>'>&laquo;</a>
		<a href="<?php if($pageno <= 1){ echo 'javascript:void(0)'; } else { echo "?pageno=".($pageno - 1); }?>" class='pagination <?php if($pageno <= 1){ echo 'disabled'; } ?>'>Prev</a>
		<a href="<?php if($pageno >= $total_pages){ echo 'javascript:void(0)'; } else { echo "?pageno=".($pageno + 1); } ?>" class='pagination <?php if ($total_pages == $pageno){ echo 'disabled'; } ?>'>Next</a>
		<a href="?pageno=<?php echo $total_pages; ?>" class='pagination <?php if ($total_pages == $pageno){ echo 'disabled'; } ?>'>&raquo;</a>
	</center>
<?php



displayFooter();
