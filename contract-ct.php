<?php
	
	require_once 'cOnfig/connection-master.php';
	require_once 'cOnfig/view-loggedout.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();


	if(!isset($_REQUEST['auth'])){
		echo "This link is invalid!";
		exit();
	}

	$authToken = $_REQUEST['auth'];

	try
	{
		$result = $pdo2->prepare("SELECT * FROM custom_contract_signatures WHERE authtoken = :authtoken;");
		$result->bindValue(':authtoken', $authToken);
		$result->execute();
		$data = $result->fetchAll(PDO::FETCH_ASSOC);
		$id = $data[0]['contract_id'];

	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	if(empty($data)){
		 echo "This link is invalid !";
		 header("Location: contract-ct.php");
		 exit();
	}
	
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['responsible'])) {
		
		$responsible = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['responsible'])));
		$dni = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['dni'])));
		$club = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['club'])));
		$address = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['address'])));
		$cif = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['cif'])));
		$insertTime = date('Y-m-d H:i:s');
			
		 $query = sprintf("UPDATE custom_contract_signatures SET signature = '%d', cif = '%s', name = '%s', dni = '%s', club = '%s', address = '%s', created_at = '%s'  WHERE contract_id = '%d' ;", 1, 
		  		$cif, $responsible, $dni, $club, $address, $insertTime, $id);
		// echo $query; die;
		try
		{
			 $pdo2->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
			// On success: redirect.
			$_SESSION['successMessage'] = "Contrato firmado. Gracias!";
			header("Location: index.php");
			exit();
	}
			

		
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
$('#dd_signaturePadWrapper').click(function(e) {  
        $('#savesig').attr('checked', false)
    });
	    	    
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  responsible: {
				  required: true
			  },
			  dni: {
				  required: true
			  },
			  address: {
				  required: true
			  },
			  club: {
				  required: true
			  },
			  savesig: {
				  required: true
			  },
			  accept2: {
				  required: true
			  },
			  accept3: {
				  required: true
			  }			  
    	}, // end rules
		  errorPlacement: function(error, element) {
			if (element.is("#savesig")){
				 error.appendTo("#errorBox1");
			} else if (element.is("#accept2")){
				 error.appendTo("#errorBox2");
			} else if (element.is("#accept3")){
				 error.appendTo("#errorBox3");
			} else if (element.is("#accept4")){
				 error.appendTo("#errorBox4");
			} else {
				return true;
			}
		},
		 
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
	  $.validator.messages.required = "Tienes que aceptar!";
	  
function demo_postSaveAction(f) {
	var objParent=document.getElementById('testDiv');
	var objDiv=document.createElement('div');
	with(objDiv) {
		setAttribute('id','demo_downloadWrapper');
		with(style) {
			position="relative";
			padding="10px";
			textAlign="left";
			margin="15px 70px 0px 70px";
			border="solid 1px #c0c0c0";
			backgroundColor="#efefef";
			borderRadius="4pt";
		}
		innerHTML="<h4>Demo signature saved to image. Click to download.</h4>";
		innerHTML+="<ul><li><a href=\"dd_signature_process_custom.php?download="+f+"\" target=\"_blank\">"+f+"</a></li></ul>";
	}
	objParent.appendChild(objDiv);
}

  }); // end ready
EOD;

	// Generate random temporary membership number, to use throughout the process.
	//$tempNo = "_" . generateRandomString();
	$_SESSION['image_token'] = $authToken;
	

	
		pageStart("Contrato", NULL, $validationScript, "pprofile", "statutes dev-align-center", "CCS Software Contract", $_SESSION['successMessage'], $_SESSION['errorMessage']);

		// get the custom contract 

	try
	{
		$selectContract = $pdo2->prepare("SELECT * FROM custom_contracts WHERE id = :id;");
		$selectContract->bindValue(':id', $id);
		$selectContract->execute();

	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row_contract = $selectContract->fetch();
		$custom_contract = $row_contract['contract'];

?>

	
<script type="text/javascript" src="js/dd_signature_pad_custom.js"></script>
<div class="actionbox-np2">
	<div class='boxcontent'>
		<form id="registerForm" method="post" action="">

	<p>In Madrid, on the <?php echo date("d-M-Y"); ?></p>
	<h2>BY AND BETWEEN</h2>

	<p><input type="text" name="responsible" placeholder="Contact person" class="defaultinput sixDigit"/> of legal age, with D.N I. (ID card) number <input type="text" name="dni" placeholder="DNI" class="defaultinput sixDigit"/> and on behalf and in representation of  <input type="text" name="club" placeholder="Club name" class="defaultinput sixDigit"/>, hereinafter the "CLIENT", with registered address at  <input type="text" name="address" placeholder="Full address" class="defaultinput sixDigit"/>, <input type="text" name="cif" placeholder="CIF of club" class="defaultinput sixDigit" />.</p>
	<p>And, Andreas Bjolmer Nilsen of legal age, with D. N. I. number X8075467Y and on behalf and in representation of the company Nefos Solutions LTD, hereinafter, the "SUPPLIER"), with registered address at 20 Harcourt St, Saint Kevin's, Dublin 2, D02 PF99, Ireland, street and company number N0073291G.</p>
	<p>The CLIENT and the SUPPLIER may henceforth be individually referred to as "the party" and, together, as "the parties", recognising each other as having legal capacity and sufficient responsibility to enter into this contract.</p><br><br>

	<?php echo $custom_contract; ?><br><br>	
	<h2>CONSENT</h2>
	<p>The intended purpose and use of both the data itself and its processing is to provide the requested service to you or to deliver the purchased product to you. You may accept the purposes you think are appropriate by checking the applicable box. Note that some purposes may be necessary in order to provide the service. If you do not mark these boxes, it will not be possible to provide/deliver the associated service/product.
		<div class='fakeboxholder'>	
		 <label class="control">
		  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Provision of the contracted service (if you agree to the processing of your data for this purpose please tick this box) 
		  <input type="checkbox" name="accept2" id="accept2" />
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br />
		<br />
		<div class='fakeboxholder'>	
		 <label class="control">
		  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Shipment of the purchased product (if you agree to the processing of your data for this purpose please tick this box) 
		  <input type="checkbox" name="accept3" id="accept3" />
		  <div class="fakebox"></div>
		 </label>
		</div>
		<br />
		<br />

		<div class='fakeboxholder'>	
		 <label class="control">
		  <span id="errorBox4"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sending offers of products and services of interest to you (if you agree to the processing of your data for this purpose please tick this box) 
		  <input type="checkbox" name="accept4" id="accept4" />
		  <div class="fakebox"></div>
		 </label>
		</div>

	<p class='smallerfont'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Identification details of the company responsible:<br />
	Nefos Solutions Limited, N0073291G, C/ Esteban Collantes 22, 5ª - 28017 - Madrid - MADRID, 644441092</p>




	<center><strong>Your signature:</strong><br />
	<div id="signatureSet">
			<div id="dd_signaturePadWrapper"></div>
		</div><br />
	</center>
	<br>

	<center>
		<div class='fakeboxholder'>
		 <label class="control">
		  <span id="errorBox1"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I agree to and accept all of the above. 
		  <input type="checkbox" name="savesig" id="savesig" />
		  <div class="fakebox"></div>
		 </label>
		</div>

	</center><br />
	<center><span id="errorBox"></span></center><br />


		 <button type="submit" class="cta1">Submit</button><br />
	</form>
	</div>
</div>

<?php  displayFooter(); ?>
