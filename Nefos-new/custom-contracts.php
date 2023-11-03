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

	if(strpos($siteroot, "ccsnube.com/ttt") !== false){
	    $base_url = "http://ccsnube.com/ttt/";
	}else{
	    $base_url = "http://192.168.0.41/ccs/";
	}

	// resend contract email

	if(isset($_REQUEST['contract_id']) && $_REQUEST['resend'] == 1){

		$contract_id = $_REQUEST['contract_id'];

		// get the mail values

		$selectResendContract = "SELECT * FROM custom_contracts WHERE id =".$contract_id;

		try
		{
			$resend_results = $pdo3->prepare("$selectResendContract");
			$resend_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$contract_row = $resend_results->fetch();
			$email = $contract_row['email'];
			$subject = $contract_row['subject'];

			$authToken = md5($contract_id.",".$email);

		// send contract email
		require_once '../PHPMailerAutoload.php';
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';
        $mail->isSMTP();
        $mail->Host = "mail.cannabisclub.systems";
        $mail->SMTPAuth = true;
        $mail->Username = "info@cannabisclub.systems";
        $mail->Password = "Insjormafon9191";
        $mail->SMTPSecure = 'ssl'; 
        $mail->Port = 465;
        $mail->setFrom('info@cannabisclub.systems', 'CCSNube');
        $mail->addAddress("$email");
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $link = $base_url."contract-ct.php?auth=".$authToken;
        $mail->Body = "<p>Please click on this <a href='".$link."'>contract link</a> to verify contract !</p>";
        $mail->send();

        $_SESSION['successMessage'] = "Contract resent successfully!";
        header("Location: custom-contracts.php");
        exit();
	}

	// Query to look up requests
	$selectContracts = "SELECT *, a.created_at AS created, b.created_at AS sign_updated FROM custom_contracts a, custom_contract_signatures b WHERE a.id = b.contract_id order by a.id DESC";
		try
		{
			$results = $pdo3->prepare("$selectContracts");
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

			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Contracts",
			    filename: "Contracts" //do not include extension
		
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
					0: {
						sorter: "dates"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});

		function resend_email(id){
			if(confirm("Are you sure to resend email ?")){
				window.location = "custom-contracts.php?contract_id=" + id + "&resend=1";
			}
		}

EOD;
	pageStart("Custom Contracts", NULL, $deleteSaleScript, "psales", "Custom Contracts", "Custom Contracts", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center>
 <a href="send-custom-contract.php" class="cta1">Send Contract</a> <br />
<a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
</center>
<br />
<br />
<table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Time</th>
	    <th>Email</th>
	    <th>Subject</th>
	    <th>Contract</th>
	    <th>Signature</th>
	    <th>Verifiy</th>
	    <th>CIF</th>
	    <th>Name</th>
	    <th>DNI</th>
	    <th>Club</th>
	    <th>Address</th>
	    <th>Signed Time</th>
	    <th>Resend Email</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	while ($contract = $results->fetch()) {
				
				$time = date("d-m-Y H:i", strtotime($contract['created']));
				$id = $contract['contract_id'];
				$email = $contract['email'];
				$subject = $contract['subject'];
				$contract_content = $contract['contract'];
				$authtoken = $contract['authtoken'];
				$verify = $contract['signature'];
				$cif = $contract['cif'];
				$name = $contract['name'];
				$dni = $contract['dni'];
				$club = $contract['club'];
				$address = $contract['address'];
				if($contract['sign_updated'] != ''){
					$sign_updated = date("d-m-Y H:i", strtotime($contract['sign_updated']));
				}else{
					$sign_updated = "N/A";
				}
				
				if ($contract_content != '') {
					
					$contractRead = "
					                <img src='images/comments.png' id='contract$id' /><div id='helpBox$id' class='helpBox'>$contract_content</div>
					                <script>
					                  	$('#contract$id').on({
									 		'mouseover' : function() {
											 	$('#helpBox$id').css('display', 'block');
									  		},
									  		'mouseout' : function() {
											 	$('#helpBox$id').css('display', 'none');
										  	}
									  	});
									</script>
					                ";
					
				} else {
					
					$contractRead = "";
					
				}

				if($verify == 0){
					$veriText = "No";
					$resend_btn = "<a href='javascript:void(0);' onClick='resend_email(".$id.")' class='cta4'>Resend</a>";
				}else{
					$veriText = "Yes";
					$resend_btn = '';
				}	

				if($verify == 1){
					$signature_path = $base_url."images/custom_sigs/".$authtoken.".png";
					$sign_image = "<a href='".$signature_path."' target = '_blank'><img src='".$signature_path."' height='50' width='50'/></a>";
				}else{
					$sign_image = 'N/A';
				}		

			
					echo "
		  	   <tr>
		  	    <td>$time</td>
		  	    <td>$email</td>
		  	    <td>$subject</td>
		  	    <td class='centered'><span class='relativeitem'>$contractRead</span></td>
		  	    <td class='centered'>$sign_image</td>
		  	    <td>$veriText</td>
		  	    <td class='centered'>$cif</td>
		  	    <td class='centered'>$name</td>
		  	    <td class='centered'>$dni</td>
		  	    <td class='centered'>$club</td>
		  	    <td class='centered'>$address</td>
		  	    <td class='centered'>$sign_updated</td>
		  	    <td class='centered'>$resend_btn</td>
		  	   </tr>";
	  
  	}
?>

	 </tbody>
</table>
	 
<?php



displayFooter(); ?>

