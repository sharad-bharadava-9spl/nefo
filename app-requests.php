<?php
	// file updated by konstant for CCS app requests on 13-01-2022
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	$domain = $_SESSION['domain'];
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$timeLimit = "";

/*	if(isset($_REQUEST['request_id'])){
		$request_id = $_REQUEST['request_id'];
		$member_id = $_REQUEST['member_id'];
		$previous_request = $_REQUEST['allow'];
		$change_request = 1; 
		if($previous_request == 1){
			$change_request = 0;
		}
		// get the email id of member

		$selectMember = "SELECT email FROM members WHERE id = '".$member_id."'";
		try
		{
			$mem_results = $pdo->prepare("$selectMember");
			$mem_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$mem_row= $mem_results->fetch();
			$member_email = $mem_row['email'];

		$updateAppMember = sprintf("UPDATE users SET app_member = '%d' WHERE email = '%s';", $member_id, $member_email);
		try
		{
			$pdo3->prepare("$updateAppMember")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}




		// update the app request
		$updateAppRequest = sprintf("UPDATE app_requests SET allow_request = '%d' WHERE id = '%d';", $change_request, $request_id);
		try
		{
			$pdo->prepare("$updateAppRequest")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = "Request updated succesfully!";
		header("Location: app-requests.php");
		exit();

	}*/
	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(b.created_at) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";
			
	}
	
	
	// Query to look up log items
	 $selectRequests = "SELECT * FROM members a, app_requests b WHERE a.id = b.member_id AND b.club_name = '".$domain."'  $timeLimit ORDER by b.created_at DESC";
		try
		{
			$results = $pdo->prepare("$selectRequests");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	


	
	$deleteDonationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	    $( "#exceldatepicker" ).datepicker({
				dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	   $( "#exceldatepicker2" ).datepicker({
				dateFormat: "dd-mm-yy"
	    });
	  });	    
	  
	    $(document).ready(function() {
		  
			$('#cloneTable').width($('#mainTable').width());
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "currency"
					},
					6: {
						sorter: "currency"
					}
				}
			}); 
		
			$(window).resize(function() {
				$('#cloneTable').width($('#mainTable').width());
			});

		});
			function search_member(email, allow_request){
				window.location = "search-app-members.php?key=" + btoa(email);
			}
		
EOD;
	pageStart('App members Requests', NULL, $deleteDonationScript, "pexpenses", "admin", 'App members Requests', $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<center>
	<div id="filterbox">
		   <div id="mainboxheader">
 					<?php echo $lang['filter']; ?> </div>
			 <div class="boxcontent">	
		        <form action='' method='POST'>
		<?php
			if (isset($_POST['fromDate'])) {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="off" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="off" class="sixDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
				 <button type="submit" class='cta2' style='display: inline-block; width: 40px;'>OK</button>
		EOD;
				
			} else {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="off" class="sixDigit defaultinput" placeholder="{$lang['from-date']}" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="off" class="sixDigit defaultinput" placeholder="{$lang['to-date']}" onchange='this.form.submit()' />
				 <button type="submit" class='cta2' style='display: inline-block; width: 40px;'>OK</button>
		EOD;
			}
		?>
		        </form>
		        </div>
		      </div>
		   </center>   <br> </br> 
		  <!-- <center> <a href="javascript:void(0);" id="openCOnfirm" onClick=""><img src="images/excel-new.png"/></a></center><br> </br>  -->
	 <table class="default" id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th>Username</th>
	    <th>Email</th>
	    <th>Club code</th>
	    <th>Approve request</th>
	    <th><?php echo $lang['action']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  <?php
		while ($app_request = $results->fetch()) {
	
	
	$id = $app_request['id'];
	$formattedDate = date("Y-m-d H:i:s", strtotime($app_request['created_at'] . "+$offsetSec seconds"));
	$username = $app_request['username'];
	$email = $app_request['email'];
	$club_code = $app_request['club_code'];
	$allow_request = $app_request['allow_request'];
	
	$allow_text = "<span style='color:red;'><strong>Not Allowed</strong></span>";
	if($allow_request == 1){
		$allow_text = "<span style='color:green;'><strong>Allowed</strong></span>";
	}
	$action_button = "<a href='javascript:search_member(\"$email\", $allow_request);' class='cta1' style='width:107px; font-size: 12px;'>Search Member</a>";
	$members_row =	sprintf("
  	  <tr>
  	   <td class='left'>%s</td>
  	   <td style='text-align: right;'>%s</td>
  	   <td style='text-align: right;'>%s</td>
  	   <td style='text-align: right;'>%s</td>
  	   <td class='centered'>%s</td>
  	   <td class='centered'>%s</td>
	  </tr>",
	  $formattedDate, $username, $email, $club_code, $allow_text, $action_button
	  );
	  echo $members_row;
  }
?>
	 </tbody>
	 </table>
	 
<div  class="actionbox-npr" id = "dialog-3" title = "<?php echo $lang['log']; ?>">
	
	<div class='boxcomtemt'>
		<p>Export excel between time ranges</p><br>
		<input type="text" id="exceldatepicker" name="fromDate" autocomplete="off" class="sixDigit defaultinput" placeholder="<?php echo $lang['from-date'] ?>" />
		 <input type="text" id="exceldatepicker2" name="untilDate" autocomplete="off" class="sixDigit defaultinput" placeholder="<?php echo $lang['to-date'] ?>"/>
			<button class='cta1' id="fullList">Ok</button>
		
	</div>
</div> 
<?php  displayFooter(); ?>

<script type="text/javascript">
	$( "#dialog-3" ).dialog({
	    autoOpen: false, 
	    hide: "puff",
	    show : "slide",
	     position: {
	       my: "top top",
	       at: "top top"
	    }      
	 });
	 $( "#openCOnfirm" ).click(function() {
	    $( "#dialog-3" ).dialog( "open" );
	 });

	 $("#fullList").click(function(){
	    $("#load").show();
	    $( "#dialog-3" ).dialog( "close" );

	    var fromDate = $("#exceldatepicker").val();
	    var untilDate = $("#exceldatepicker2").val();
	    var url = 'app-requests-report.php?fromDate='+fromDate+'&untilDate='+untilDate+'&count=0&totalCount=0';
	    window.open(url, "App Request Report","height=300,width=300,modal=yes,alwaysRaised=yes");
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);    
	 });

</script>