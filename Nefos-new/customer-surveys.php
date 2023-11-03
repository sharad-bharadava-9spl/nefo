<?php
	//Created by Konstant for Task-14935056 on 17/09/2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();

	// Check if 'entre fechas' was utilised
	if (!empty($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(created_at) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	}else{
		$timeLimit = '';
	}

	// Query to look up requests
	$selectSurveys = "SELECT * FROM customer_survey WHERE 1 $timeLimit order by id DESC";
		try
		{
			$results = $pdo3->prepare("$selectSurveys");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

	
	$deleteSaleScript = <<<EOD
	    $(document).ready(function() {

	    	$( function() {
			    $( "#datepicker" ).datepicker({
					dateFormat: "dd-mm-yy"
			    });	    
			  
			    $( "#datepicker2" ).datepicker({
					dateFormat: "dd-mm-yy"
			    });	    
			 });

			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Surveys",
			    filename: "Surveys" //do not include extension
		
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
					11: {
						sorter: "dates"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});


EOD;
	pageStart("Customer Surveys", NULL, $deleteSaleScript, "psales", "Customer Surveys", "Customer Surveys", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center>
	<a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
</center>
<br />
<br />
<center>
	<div id='filterbox'>
		 <div id='mainboxheader'>
		 <?php echo $lang['filter']; ?>
		 </div>
	 	<div class='boxcontent'>
	        <form action='' method='POST' style='display: inline-block;'>
				<?php
					if (isset($_POST['fromDate'])) {
						
						echo <<<EOD
						 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
						 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" />
					EOD;
							
						} else {
							
							echo <<<EOD
							 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['from-date']}" />
							 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['to-date']}" />
					EOD;

						}
					?>
				      <br>
					<button type="submit"  class='cta1' style='display: inline-block; width: 50px;'>OK</button>
	        	</form>
	        </div>
	       </td>
	      </tr>
	     </table>
	</div>
</center>
<br />
<br />
<table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Customer number</th>
	    <th>Customer name</th>
	    <th>User (memberno + first_name + last_name)</th>
	    <th>HW & Delivery</th>
	    <th>HW & Delivery Comment</th>
	    <th>Customer Service</th>
	    <th>Customer Service Comment</th>
	    <th>Software user experience</th>
	    <th>Software user experience Comment</th>
	    <th>General Service</th>
	    <th>General Service Comment</th>
	    <th>Entry Date</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	while ($survey = $results->fetch()) {
				
				$time = date("d-m-Y H:i", strtotime($survey['created_at']));
				$id = $survey['id'];
				$customer_id = $survey['customer_id'];
				$user_id = $survey['user_id'];
				$hw_delivery = $survey['hw_delivery'];
				$customer_support = $survey['customer_support'];
				$sft_user_exe = $survey['sft_user_exe'];
				$general_service = $survey['general_service'];
				$hwd_comment = $survey['hwd_comment'];
				$cws_comment = $survey['cws_comment'];
				$sue_comment = $survey['sue_comment'];
				$gs_comment = $survey['gs_comment'];

				// fetch customer details

				$selectCustomer = "SELECT number, longName FROM customers WHERE id=".$customer_id;

				try{
					$customer_result = $pdo3->prepare("$selectCustomer");
					$customer_result->execute();
				}
				catch(PDOException $e)
				{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
				}
				$customer_row = $customer_result->fetch();
					$customer_number = $customer_row['number'];
					$customer_name = $customer_row['longName'];

				// get the user domain details
				
				try
				{
					$result = $pdo->prepare("SELECT db_pwd,domain FROM db_access WHERE customer = :customer");
					$result->bindValue(':customer', $customer_number);
					$result->execute();
					//print_r($results); exit;
				}
				catch (PDOException $e)
				{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
				}

				$row = $result->fetch();
			    $db_name = 'ccs_'.$row['domain'];
			    if(strpos($siteroot, "192.168.0.41/ccs/Nefos-new")){
					$db_user = "root";
					$db_pwd = "";
				}else{
					 $db_user = $db_name . "u";
					 $db_pwd = $row['db_pwd'];
				}
			    try	{
					//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
			 		$pdo_user = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
			 		$pdo_user->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			 		$pdo_user->exec('SET NAMES "utf8"');
				}
				catch (PDOException $e)	{
					
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			 		
				}	
				
				// fetch user details

				$selectUsers = "SELECT memberno, first_name, last_name FROM users WHERE user_id=".$user_id;
				try{
					$user_result = $pdo_user->prepare("$selectUsers");
					$user_result->execute();
				}
				catch(PDOException $e)
				{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
				}
				$user_row = $user_result->fetch();
					$memberno = $user_row['memberno'];
					$first_name = $user_row['first_name'];
					$last_name = $user_row['last_name'];

				if ($hwd_comment != '') {
					
					$hwdRead = "
					                <img src='images/comments.png' id='hwd$id' /><div id='helpBoxhw$id' class='helpBox'>$hwd_comment</div>
					                <script>
					                  	$('#hwd$id').on({
									 		'mouseover' : function() {
											 	$('#helpBoxhw$id').css('display', 'block');
									  		},
									  		'mouseout' : function() {
											 	$('#helpBoxhw$id').css('display', 'none');
										  	}
									  	});
									</script>
					                ";
					
				} else {
					
					$hwdRead = "";
					
				}				

				if ($cws_comment != '') {
					
					$cwsRead = "
					                <img src='images/comments.png' id='cwe$id' /><div id='helpBoxcws$id' class='helpBox'>$cws_comment</div>
					                <script>
					                  	$('#cwe$id').on({
									 		'mouseover' : function() {
											 	$('#helpBoxcws$id').css('display', 'block');
									  		},
									  		'mouseout' : function() {
											 	$('#helpBoxcws$id').css('display', 'none');
										  	}
									  	});
									</script>
					                ";
					
				} else {
					
					$cwsRead = "";
					
				}				

				if ($sue_comment != '') {
					
					$sueRead = "
					                <img src='images/comments.png' id='sue$id' /><div id='helpBoxsue$id' class='helpBox'>$sue_comment</div>
					                <script>
					                  	$('#sue$id').on({
									 		'mouseover' : function() {
											 	$('#helpBoxsue$id').css('display', 'block');
									  		},
									  		'mouseout' : function() {
											 	$('#helpBoxsue$id').css('display', 'none');
										  	}
									  	});
									</script>
					                ";
					
				} else {
					
					$sueRead = "";
					
				}				

				if ($gs_comment != '') {
					
					$gsRead = "
					                <img src='images/comments.png' id='gs$id' /><div id='helpBoxgs$id' class='helpBox'>$gs_comment</div>
					                <script>
					                  	$('#gs$id').on({
									 		'mouseover' : function() {
											 	$('#helpBoxgs$id').css('display', 'block');
									  		},
									  		'mouseout' : function() {
											 	$('#helpBoxgs$id').css('display', 'none');
										  	}
									  	});
									</script>
					                ";
					
				} else {
					
					$gsRead = "";
					
				}


			
					echo "
		  	   <tr>
		  	    <td>$customer_number</td>
		  	    <td>$customer_name</td>
		  	    <td>$memberno - $first_name $last_name</td>
		  	    <td class='centered'>$hw_delivery</td>
		  	    <td class='centered'><span class='relativeitem'>$hwdRead</span></td>
		  	    <td class='centered'>$customer_support</td>
		  	    <td class='centered'><span class='relativeitem'>$cwsRead</span></td>
		  	    <td class='centered'>$sft_user_exe</td>
		  	    <td class='centered'><span class='relativeitem'>$sueRead</span></td>
		  	    <td class='centered'>$general_service</td>
		  	    <td class='centered'><span class='relativeitem'>$gsRead</span></td>
		  	    <td class='centered'>$time</td>
		  	   </tr>";
	  
  	}
?>

	 </tbody>
</table>
	 
<?php



displayFooter(); ?>

