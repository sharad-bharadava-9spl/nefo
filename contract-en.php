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
	
	if ($_SESSION['userGroup'] > 1) {
		
		if ($_SESSION['lang'] == 'en') {
			
			pageStart("Contrato", NULL, $validationScript, "pprofile", "statutes dev-align-center", "Contrato de software CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
			echo "<center>Only administrators can sign the contract!</center>";
			
		} else {
			
			pageStart("Contrato", NULL, $validationScript, "pprofile", "statutes dev-align-center", "Contrato de software CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
			echo "<center>Solo administradores pueden firmar el contrato!</center>";
			
		}
		
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
		$imageid = $_SESSION['tempNo'];
			
		  	$query = sprintf("INSERT INTO contract (cif, name, dni, club, address, time, image) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s');",
		  		$cif, $responsible, $dni, $club, $address, $insertTime, $imageid);
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
			$domainCheck = "SELECT domain FROM systemsettings";
		try
		{
			$result = $pdo3->prepare("$domainCheck");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$siteDomain = $row['domain'];
			
			$name = "Admin";
			$email = "info@cannabisclub.systems";
			/*
			// Send e-mail(s)
			require_once 'PHPMailerAutoload.php';
			
			$mail = new PHPMailer(true);
			$mail->SMTPDebug = 0;
			$mail->Debugoutput = 'html';
			$mail->isSMTP();
			$mail->Host = "smtp.serviciodecorreo.es";
			$mail->SMTPAuth = true;
			$mail->Username = "info@ccsnube.com";
			$mail->Password = "Rbt14x74";
			$mail->SMTPSecure = 'ssl'; 
			$mail->Port = 465;
			$mail->setFrom('info@ccsnube.com', 'CCSNube');
			$mail->addAddress("$email", "$name");
			$mail->Subject = "Contract signed by $siteDomain";
			$mail->isHTML(true);
			$mail->Body = "Dear admin.<br /><br />Club $siteDomain has updated their details and signed the CCS contract.";
			$mail->send();
			*/
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
		innerHTML+="<ul><li><a href=\"dd_signature_process.php?download="+f+"\" target=\"_blank\">"+f+"</a></li></ul>";
	}
	objParent.appendChild(objDiv);
}

  }); // end ready
EOD;

	// Generate random temporary membership number, to use throughout the process.
	$tempNo = "_" . generateRandomString();
	$_SESSION['tempNo'] = $tempNo;
	
if ($_SESSION['lang'] == 'en') {
	
		pageStart("Contrato", NULL, $validationScript, "pprofile", "statutes dev-align-center", "CCS Software Contract", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

	
<script type="text/javascript" src="js/dd_signature_pad.js"></script>
<div class="actionbox-np2">
	<div class='boxcontent'>
		<form id="registerForm" method="post" action="">

	<p>In Madrid, on the <?php echo date("d-M-Y"); ?></p>
	<h2>BY AND BETWEEN</h2>

	<p><input type="text" name="responsible" placeholder="Contact person" class="defaultinput sixDigit"/> of legal age, with D.N I. (ID card) number <input type="text" name="dni" placeholder="DNI" class="defaultinput sixDigit"/> and on behalf and in representation of  <input type="text" name="club" placeholder="Club name" class="defaultinput sixDigit"/>, hereinafter the "CLIENT", with registered address at  <input type="text" name="address" placeholder="Full address" class="defaultinput sixDigit"/>, <input type="text" name="cif" placeholder="CIF of club" class="defaultinput sixDigit" />.</p>
	<p>And, Andreas Bjolmer Nilsen of legal age, with D. N. I. number X8075467Y and on behalf and in representation of the company Nefos Solutions LTD, hereinafter, the "SUPPLIER"), with registered address at 20 Harcourt St, Saint Kevin's, Dublin 2, D02 PF99, Ireland, street and company number N0073291G.</p>
	<p>The CLIENT and the SUPPLIER may henceforth be individually referred to as "the party" and, together, as "the parties", recognising each other as having legal capacity and sufficient responsibility to enter into this contract.</p>

	<br /><br />
	<h2>DECLARE</h2>
	<p><strong>ONE:</strong> That the CLIENT is interested in the purchase of a software program license.

	</p>
	<p>The client is interested in these services to use the CCS (Cannabis Club Systems) program on their computers.</p>
	<p><strong>TWO:</strong> that the SUPPLIER is a company specialising in the provision of services for the creation, development, distribution, updating and maintenance of computer systems, cloud computing, web and email hosting.</p>
	<p><strong>THREE:</strong> That the Parties are interested in entering into a license agreement under which the SUPPLIER licenses the CLIENT for the use of the computer program named CCS (Cannabis Club Systems).</p>
	<p>That the Parties gathered in the headquarters of the CLIENT, agree to enter into this LICENSE agreement, hereinafter the "Contract", in accordance with the following clauses.</p>

	<h2>CLAUSES</h2>

	<h3>ONE. - SUBJECT</h3>
	<p>Under the Contract the SUPPLIER undertakes to assign the non-exclusive use of the CCS (Cannabis Club Systems) software program to the CLIENT.</p>
	<p>The license payment covered by this contract does not constitute the purchase of the applicable programs, titles, or copyright.</p>

	<h3>TWO. - GENERAL TERMS AND CONDITIONS AND SPECIFICS ON THE PROVISION OF SERVICES</h3>
	<p>2.1. The services shall be provided under the following general terms and conditions:</p>
	<p>2.1.1.1. The SUPPLIER shall be responsible for the quality of the work carried out with the due diligence of an expert company in the performance of the work covered by this contract.</p>
	<p>2.1.1.2. The SUPPLIER undertakes to manage and obtain, at his/her own expense, all licences, permits and administrative authorisations that may be necessary for the performance of the services.</p>
	<p>2.1.1.3. The PROVIDER shall keep information provided by the CLIENT in or for the performance of the contract confidential, or which by its very nature must be treated as such. All information that is disclosed by the CLIENT in accordance with the law or with a judicial decision or act from a competent authority is excluded from the category of confidential information. This duty shall continue for a period of three years from the end of the service.</p>
	<p>2.1.1.4. In the event that the provision of the Services involves the need to access personal data, the SUPPLIER, as in charge of processing, is liable for compliance with Law 15/1999, of 13 December, on Protection of Personal Data and Royal Decree 1720/2007, of 21 December, which approves the Implementation Regulation of Organic Law 15/1999 and other applicable regulations.</p>
	<p>The SUPPLIER shall therefore be liable for any infringements incurred in the event that he/she uses the personal data for another purpose, communicates them to a third party, or in general uses them irregularly, as well as when he/she fails to take appropriate measures for the storage and custody of said personal data. To that effect, the CLIENT agrees to indemnify for any damages incurred directly, or for any claim, action or proceeding, that bring about a default or defective performance by the SUPPLIER of the provisions in the Contract, as set forth in the rules regulating the protection of personal data.</p>
	<p>For the purposes of Article 12 of law 15/1999, the SUPPLIER shall only process personal data to which he/she has access to in accordance with the CLIENT's instructions and shall not apply or use them for any purpose other than that stated in the contract, nor shall he/she communicate them to other persons even for the purpose of preservation. In the event that the SUPPLIER uses the data for another purpose, communicates it or uses it in breach of the terms of the contract, he/she shall also be held liable for the processing and the infringements that were personally incurred.</p>
	<p>The SUPPLIER shall take the necessary technical and organisational measures to ensure the security of personal data and to prevent its alteration, loss, processing or unauthorised access, taking into account the state of the technology, the nature of the stored data and the risks to which it is exposed, whether from human action or from the physical or natural environment. For these purposes the SUPPLIER shall apply the security levels that are set out in Royal Decree 1720/2007 in accordance with the nature of the data concerned.</p>
	<p>2.1.1.5. The SUPPLIER shall be responsible for the correctness and accuracy of the documents provided to the CLIENT in the execution of the contract and shall promptly notify the CLIENT when an error is found so that such corrective measures and actions as are deemed appropriate may be taken.</p>
	<p>2.1.1.6. The SUPPLIER shall be liable for damages to the CLIENT and for any claims that may be made by a third party, which have been caused directly by the PROVIDER or their staff, in execution of the contract or which result from a lack of diligence, as referred to above. </p>
	<p>2.1.1.7. The obligations established for the SUPPLIER by this clause shall also be binding on its potential employees, external and internal partners and subcontractors, and the PROVIDER shall therefore be liable to the CLIENT if such obligations are breached by such employees.</p>
	<p>2.2. The SUPPLIER shall relinquish the non-exclusive usage licence and provide the services under the following specific terms and conditions: </p>
	<p>2.2.1.1. The SUPPLIER licenses the use of the program described in Clause 1 of this contract to the CLIENT; this is understood as a personal license, of a non-exclusive and non-transferable nature. </p>
	<p>2.2.1.2. The intellectual property for the licensed software, belongs uniquely and exclusively to the SUPPLIER. This intellectual property covers the software, its source code and the structure of its database.</p>
	<p>2.2.1.3. The CLIENT acknowledges that the program is supplied as described in the subject matter of the contract, clause 1 and that this agreement grants he/she rights to other versions, improvements or modifications of the contract. </p>
	<p>2.2.1.4. The usage license includes all the necessary and additional knowledge for the program contents and its technical specifications to make it useful to the client. </p>
	<p>2.2.1.5. The license to use the program is granted to the CLIENT who will be responsible for its safekeeping. The licensee is the owner of the media in which the software has been stored, but recognises the SUPPLIER and its licensors as the owner of the software.</p>
	<p>The SUPPLIER reserves any rights not expressly granted to the licensee.</p>
	<p>2.2.1.6. Following the receipt and installation of the program the CLIENT must report compliance with it and its technical specifications within ten working days. If, on the expiry of this time period, the CLIENT has not indicated their compliance to the SUPPLIER, it will be understood that the program has been accepted. </p>
	<p>2.2.1.7. The SUPPLIER guarantees the good condition of the cloud and accompanying online support.</p>
	<p>2.2.1.8. The SUPPLIER will inform the CLIENT of any new developments, extensions, etc., that occur with the program, in case they are interested in their installation.</p>
	<p>2.2.1.9. The SUPPLIER shall comply with the contract by performing the services in a competent and professional manner, complying with the required quality standards and diligently taking care of the CLIENT's materials which have to be used as a result of the contract. </p>
	<p>2.3 The transfer or termination of this contract in whole or in part shall be prohibited without the prior consent by the SUPPLIER.</p>

	<h3>THREE. - USE POLICY</h3>
	<p>3.1 The CLIENT is solely responsible for the selection of the software program and the services that are the subject of this contract and that these are in accordance with their needs, as well as for their installation and use. 
	<p>3.2 In no case shall the SUPPLIER of the program be liable to the user or third parties for any damage, including loss of profits, loss of savings or any kind of damage arising as a result of its use. The use of the program is at the CLIENT's own risk. 
	<p>3.3 The CLIENT agrees not to disclose the information provided to him by the SUPPLIER and identified as "confidential", with the exception of information that is in the public domain.
	<p>3.4 The SUPPLIER excludes any liability of any kind for access to content provided to its users through its services that are contrary to law, morals and public order, that infringe intellectual or industrial property rights, or that contain any defect or computer virus or similar software routine.
	<p>The SUPPLIER has no knowledge that the websites that may be accessed through their services have a content contrary to the law, morals or public order, that contain any vice, defect, computer virus or similar software routine.
	<p>3.5 The SUPPLIER will remove the service within 24 hours in the event that the facts referred to in clause 3.4 become known. 
	<p>3.6 The SUPPLIER is not responsible for the accuracy, verisimilitude or professional suitability of the CLIENT's published, unpublished or hidden data, or of other users of the software and the use intended for it, with no responsibility assumed for the misuse of this software.

	<h3>FOUR. - PRICING AND INVOICING.</h3>
	<p>4.1 The contract price is EUR 0.555 per month per active member, Irish VAT (23%) excluded. 
	<p>The fees for the software with all modules are calculated based on the number of active members the company has. An active member shall mean those that have been granted a license in the company within the last month.
	<p>The maximum amount invoiced shall be that of 500 active members. If the company consists of over 500 ACTIVE members, we will not charge for ACTIVE members above that number.
	<p>For other modules and services see list of services and prices.
	<p>4.2 After acceptance of the work by the CLIENT the payment for services shall be by bank transfer or deposit in cash within 10 days from the date of receipt of the invoice to the following account owned by the SUPPLIER: ES67 0182 0981 4502 0317 9600 (BBVA) 
	<p>Invoices must have been paid within 30 days of their issue date. If an invoice is not paid after that period, access to the program will be temporarily blocked until the default is resolved.
	<p>4.3 The SUPPLIER undertakes to notify the CLIENT by email of any change in the minimum price at least 30 days in advance. If the CLIENT is in disagreement and wishes to terminate services with the SUPPLIER, he/she should unsubscribe by email.

	<h3>FIVE. - DURATION OF THE CONTRACT</h3>
	<p>The duration of this contract is indefinite. The termination of this usage license may result from the causes provided for in intellectual property law and any violation of the obligations of this contract. 
	<p>The contract may be extended expressly and in writing if neither party provides one month's notice.

	<h3>SIX. - SERVICE LEVEL AGREEMENT</h3>
	<p>6.1 All services provided by the SUPPLIER shall be performed by specialised personnel in each field. The SUPPLIER 's staff will provide all the necessary, appropriate and updated materials to provide the Services.
	<p>6.2 Service failures or malfunctions shall be communicated to the SUPPLIER via email at support@cannabisclub.systems.
	<p>6.3 Problems will be resolved within a maximum period of 24 hours if mild, 48 if severe, and 5 days if critical, from receipt of the notice.
	<p>6.4 Critical impact is defined as incidences which significantly affect the CLIENT, in the context of the provision of services.
	<ul class='normallist'>
	 <li>A serious incident is defined as one which moderately affects the CLIENT, in the context of the provision of services.</li>
	 <li>A mild incident is defined as one limited to hindering the provision of services.</li>
	</ul>
	<p>6.5 The status of the services shall be reviewed on a quarterly basis by the CLIENT and the PROVIDER to verify their proper functioning.
	<p>6.6 OWNERSHIP OF THE PROGRAMS AND COPYRIGHT.
	<p>The programs covered by this contract under license, their original copies, any copy, partial or total, made by the SUPPLIER or the CLIENT or by any other person, the legal rights of copying, patents, trademarks, trade secrets, and any other intellectual right or property, belonging to the SUPPLIER, which have sufficient authorisation to offer licenses for use of such programs.
	<p>The CLIENT accepts and recognises that licensed programs are business secrets belonging to the SUPPLIER, as well as any information or documentation that is provided to him/her that has been identified as confidential.
	<p>The CLIENT must refrain from copying (whether for profit or not) the licensed programs, and must state the necessary internal measures for the protection of the owners.

	<h3>SEVEN. - MODIFICATION</h3>
	<p>The parties may amend the contract by mutual agreement and in writing.
	<h3>EIGHT. - RESOLUTION</h3>
	<p>The parties may terminate the contract, and are entitled to compensation for damages caused, in the event of a breach of the obligations set out in herein.
	<h3>NINE. - NOTIFICATIONS</h3>
	<p>Notifications by the parties shall be made by e-mail. The CLIENT undertakes to give the SUPPLIER their up to date contact details. 
	<h3>TEN. - LEGAL REGIME</h3>
	<p>This contract is of a commercial nature, and there is no employment link between the CLIENT and the SUPPLIER's employees who provide the specific Services. 
	<p>Any dispute arising from or relating to this contract, including any matter involving its existence, validity or termination, shall be settled by arbitration of law, administered by the European Arbitration Association of Madrid (Aeade), in accordance with its arbitration rules in force at the date of the submission of the arbitration request. The Arbitral Tribunal designated for that purpose shall be composed of a single expert arbitrator and the language of the arbitration shall be Spanish. The seat of the arbitration shall be Madrid.
	<p>And in evidence of the foregoing, the parties sign the contract, in duplicate and for one purpose, at the place and date indicated in the heading. 
	<br />
	<br />

	<h2>GRPD clause to collect data from those concerned with their consent</h2>
	<p>In accordance with the provisions of the General Data Protection Regulations, we inform you that we will process your personal data for the purpose of administrative, accounting and fiscal management arising from our commercial relationship, as well as sending you commercial communications about our products and/or services. The data provided shall be kept for as long as the business relationship is maintained or for as long as it is necessary to comply with legal obligations. The data will not be transferred to third parties except in cases where there is a legal obligation and we will process them on the basis of the consent given.
	<p>We also inform you of the ability to exercise the following rights regarding your personal data: rights of access, rectification, deletion or to be forgotten, limitation, opposition, portability and to withdraw the consent given.
	<p>You can send an email to: info@nefosolutions.com or write to Nefos Solutions Limited C / Esteban Collantes 22, 5th-28017-Madrid-MADRID
	<p>In addition, one may contact the competent data protection supervisory authority for additional information or to make a complaint.
	<br />
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
<?php } else {
	
		pageStart("Contrato", NULL, $validationScript, "pprofile", "statutes dev-align-center", "Contrato de software CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	 ?>

<script type="text/javascript" src="js/dd_signature_pad.js"></script>
<div class="actionbox-np2">
	<div class='boxcontent'>
	<form id="registerForm" method="post" action="">

<p>En Madrid, a <?php echo date("d-M-Y"); ?></p>
<h2>REUNIDOS</h2>

<p>DE UNA  PARTE, <input type="text" name="responsible" class="defaultinput sixDigit" placeholder="Nombre de contacto" /> mayor de edad, con D.N.I. n&uacute;mero <input type="text"  class="defaultinput sixDigit"  name="dni" placeholder="DNI" /> y en nombre y representaci&oacute;n de la <input type="text" name="club"  class="defaultinput sixDigit"  placeholder="Nombre de club" />, en adelante, el "CLIENTE", domiciliada en <input type="text" name="address" placeholder="Direcci&oacute;n completo" />, <input type="text"  class="defaultinput sixDigit"  name="cif" placeholder="CIF del club" />.</p>
<p>DE OTRA PARTE, Andreas Bjolmer Nilsen mayor de edad, con D.N.I. número X8075467Y y en nombre y representación de la mercantil Nefos Solutions LTD, en adelante, el "PROVEEDOR", domiciliada en 20 Harcourt St, Saint Kevin's, Dublin 2, D02 PF99, Irlanda, calle y numero de compania N0073291G.</p>
<p>El CLIENTE y el PROVEEDOR, en adelante, podrán ser denominadas, individualmente, "la Parte" y, conjuntamente, "las Partes", reconociéndose mutuamente capacidad jurídica y de obrar suficiente para la celebración del presente Contrato.</p>

<br /><br />
<h2>EXPONEN</h2>
<p><strong>PRIMERO:</strong> Que el CLIENTE está interesado en la compra de licencia de un programa de software.</p>
<p>El CLIENTE está interesado en contratar dichos servicios para utilizar en sus ordenadores el programa CCS (Cannabis Club Systems).</p>
<p><strong>SEGUNDO:</strong> Que el PROVEEDOR es una empresa especializada en la prestación de servicios de creación, desarrollo, distribución, actualización y mantenimiento de sistemas informáticos, Cloud computing, web y mail hosting.</p>
<p><strong>TERCERO:</strong> Que las Partes están interesadas en celebrar un contrato de licencia de uso en virtud del cual el PROVEEDOR licencie al CLIENTE para el uso del programa informático denominado CCS (Cannabis Club Systems).</p>
<p>Que las Partes reunidas en la sede social del CLIENTE, acuerdan celebrar el presente contrato de LICENCIA DE USO, en adelante, el "Contrato", de acuerdo con las siguientes cláusulas.</p>

<h2>CLÁUSULAS</h2>

<h3>PRIMERA.- OBJETO</h3>
<p>En virtud del Contrato el PROVEEDOR se obliga a ceder el uso, sin exclusiva al CLIENTE del programa de software CCS (Cannabis Club Systems).</p>
<p>El pago de la licencia otorgada bajo el presente contrato, no constituye la compra de los programas ni de los títulos, ni derechos de autor correspondientes.</p>

<h3>SEGUNDA.- TÉRMINOS Y CONDICIONES GENERALES Y ESPECÍFICOS DE PRESTACIÓN DE LOS SERVICIOS</h3>
<p>2.1. Los Servicios se prestarán en los siguientes términos y condiciones generales:</p>
<p>2.1.1.1. El PROVEEDOR responderá de la calidad del trabajo desarrollado con la diligencia exigible a una empresa experta en la realización de los trabajos objeto del Contrato.</p>
<p>2.1.1.2. El PROVEEDOR se obliga a gestionar y obtener, a su cargo, todas las licencias, permisos y autorizaciones administrativas que pudieren ser necesarias para la realización de los Servicios. </p>
<p>2.1.1.3. El PROVEEDOR guardará confidencialidad sobre la información que le facilite el CLIENTE en o para la ejecución del Contrato o que por su propia naturaleza deba ser tratada como tal. Se excluye de la categoría de información confidencial toda aquella información que sea divulgada por el CLIENTE, aquella que haya de ser revelada de acuerdo con las leyes o con una resolución judicial o acto de autoridad competente. Este deber se mantendrá durante un plazo de tres años a contar desde la finalización del servicio.</p>
<p>2.1.1.4. En el caso de que la prestación de los Servicios suponga la necesidad de acceder a datos de carácter personal, el PROVEEDOR, como encargado del tratamiento, queda obligado al cumplimiento de la Ley 15/1999, de 13 de diciembre, de Protección de Datos de Carácter Personal y del Real Decreto 1720/2007, de 21 de diciembre, por el que se aprueba el Reglamento de desarrollo de la Ley Orgánica 15/1999 y demás normativa aplicable. </p>
<p>El PROVEEDOR responderá, por tanto, de las infracciones en que pudiera incurrir en el caso de que destine los datos personales a otra finalidad, los comunique a un tercero, o en general, los utilice de forma irregular, así como cuando no adopte las medidas correspondientes para el almacenamiento y custodia de los mismos. A tal efecto, se obliga a indemnizar al CLIENTE, por cualesquiera daños y perjuicios que sufra directamente, o por toda reclamación, acción o procedimiento, que traiga su causa de un incumplimiento o cumplimiento defectuoso por parte del PROVEEDOR de lo dispuesto tanto en el Contrato como lo dispuesto en la normativa reguladora de la protección de datos de carácter personal.</p>
<p>A los efectos del artículo 12 de la Ley 15/1999, el PROVEEDOR únicamente tratará los datos de carácter personal a los que tenga acceso conforme a las instrucciones del CLIENTE y no los aplicará o utilizará con un fin distinto al objeto del Contrato, ni los comunicará, ni siquiera para su conservación, a otras personas. En el caso de que el PROVEEDOR destine los datos a otra finalidad, los comunique o los utilice incumpliendo las estipulaciones del Contrato, será considerado también responsable del tratamiento, respondiendo de las infracciones en que hubiera incurrido personalmente. </p>
<p>El PROVEEDOR deberá adoptar las medidas de índole técnica y organizativas necesarias que garanticen la seguridad de los datos de carácter personal y eviten su alteración, pérdida, tratamiento o acceso no autorizado, habida cuenta del estado de la tecnología, la naturaleza de los datos almacenados y los riesgos a que están expuestos, ya provengan de la acción humana o del medio físico o natural. A estos efectos el PROVEEDOR deberá aplicar los niveles de seguridad que se establecen en el Real Decreto 1720/2007 de acuerdo a la naturaleza de los datos que trate.</p>
<p>2.1.1.5. El PROVEEDOR responderá de la corrección y precisión de los documentos que aporte al CLIENTE en ejecución del Contrato y avisará sin dilación al CLIENTE cuando detecte un error para que pueda adoptar las medidas y acciones correctoras que estime oportunas.</p>
<p>2.1.1.6. El PROVEEDOR responderá de los daños y perjuicios que se deriven para el CLIENTE y de las reclamaciones que pueda realizar un tercero, y que tengan su causa directa en errores del PROVEEDOR, o de su personal, en la ejecución del Contrato o que deriven de la falta de diligencia referida anteriormente. </p>
<p>2.1.1.7. Las obligaciones establecidas para el PROVEEDOR por la presente cláusula serán también de obligado cumplimiento para sus posibles empleados, colaboradores, tanto externos como internos, y subcontratistas, por lo que el PROVEEDOR responderá frente al CLIENTE si tales obligaciones son incumplidas por tales empleados.</p>
<p>2.2. El PROVEEDOR cederá la licencia de uso, no exclusiva y prestará los Servicios en los siguientes términos y condiciones específicos: </p>
<p>2.2.1.1. El PROVEEDOR licencia al CLIENTE el uso del programa descrito en cláusula 1ª de este contrato; Dicha licencia se entiende como Licencia personal, de carácter no exclusivo e intransferible. </p>
<p>2.2.1.2. La Propiedad Intelectual del programa informático licenciado, es única y exclusivamente del PROVEEDOR. Dicha propiedad intelectual abarca el programa informático, su código fuente y la estructura de su base de datos.</p>
<p>2.2.1.3. El CLIENTE reconoce que el programa se suministra tal y como se describe en el Objeto del contrato, cláusula 1ª y que el presente acuerdo le concede derecho sobre otras versiones, mejoras o modificaciones del mismo. </p>
<p>2.2.1.4. La licencia de uso incluye todos los conocimientos necesarios y adicionales al contenido del programa y sus especificaciones técnicas para que éste sea útil al CLIENTE. </p>
<p>2.2.1.5. La licencia de uso del programa se concede al CLENTE que será responsable de su custodia. El licenciatario es el propietario de los medios en los cuales haya almacenado el software, pero reconoce al PROVEEDOR  y sus cedentes de licencia la propiedad del software.</p>
<p>El PROVEEDOR  se reserva cualesquiera derechos no otorgados expresamente al licenciatario.</p>
<p>2.2.1.6. El CLIENTE una vez recibido el programa debe, dentro de los 10 días hábiles siguientes a la instalación de los programas, manifestar por escrito al PROVEEDOR la conformidad al programa y a las especificaciones Técnicas de funcionamiento. Si transcurrido dicho término, El CLIENTE no ha manifestado su conformidad al PROVEEDOR, se entender que los programas han sido aceptados. </p>
<p>2.2.1.7. El PROVEEDOR garantiza el buen estado de los soportes y de la nube en el soporte online.</p>
<p>2.2.1.8. El PROVEEDOR informará al CLIENTE de cualquier novedad, ampliación, etc., que se produzca en el programa, por si le interesase su instalación.</p>
<p>2.2.1.9. El PROVEEDOR ejecutará el Contrato realizando de manera competente y profesional los Servicios, cumpliendo los niveles de calidad exigidos y cuidando diligentemente los materiales del CLIENTE que tuviera que utilizar como consecuencia del Contrato. </p>
<p>2.3 Queda prohibida la transferencia o cesión total o parcial del presente contrato sin mediar el consentimiento previo por parte del PROVEEDOR.</p>

<h3>TERCERA.- POLÍTICA DE USO</h3>
<p>3.1 El CLIENTE es el único responsable de la selección del programa de software y de los servicios que constituyen el objeto de este Contrato y que éstos se ajustan a sus necesidades, así como de la instalación y utilización del programa. </p>
<p>3.2 En ningún caso será el PROVEEDOR del programa responsable, ante el usuario o terceras partes, de cualquier daño, incluida pérdida de beneficios, pérdida de ahorro o cualquier tipo de perjuicio surgido como consecuencia de su utilización, siendo el uso del programa a riesgo y ventura del CLIENTE.</p>
<p>3.3 El CLIENTE se compromete a no divulgar la información que le haya sido proporcionada por el PROVEEDOR y que sea identificada por éste como "Confidencial", a excepción de aquella información que sea de dominio público.</p>
<p>3.4 El PROVEEDOR excluye cualquier responsabilidad de cualquier tipo en el acceso a los contenidos facilitados a sus usuarios a través de sus servicios que sean contrarios a la Ley, la moral y al Orden Público, que infrinjan derechos de propiedad intelectual, industrial o contengan cualquier vicio defecto o virus informático o rutina de software similar.</p>
<p>El PROVEEDOR no tiene conocimiento de que las páginas a las que se pueda acceder a través de sus servicios tengan un contenido contario a la Ley ,a la moral o al orden público , que infrinjan cualquier vicio , defecto , virus informático o rutina de Software similar.</p>
<p>3.5 El PROVEEDOR   eliminará el servicio en un plazo de 24 horas  en el caso de que el hecho al que se refiere la cláusula 3.4 llegare a su conocimiento.</p>
<p>3.6 El PROVEEDOR no se responsabiliza por la exactitud, verosimilitud o idoneidad profesional de los datos publicados, así como de los no publicados u ocultos por El CLIENTE y demás  usuarios de su software  y de la utilización que se les dé a los mismos, no asumiendo responsabilidad alguna en relación a la mala utilización de este software.</p>

<h3>CUARTA.- PRECIO Y FACTURACIÓN.- </h3>
<p>4.1 El precio del Contrato es de 0,555 euros al mes por socio activo IVA IRLANDES (23%) excluido. </p>
<p>Las tarifas para el software con todos los módulos se calculan en función del número de socios activos que tenga la asociación. Por socio activo se entenderá los socios que han sido dispensados en la asociación durante el mes.</p>
<p>La cantidad máxima facturada será la correspondiente a 500 socios ACTIVOS. Si su asociación consta de más de 500 socios ACTIVOS, no le cobraremos ninguna cantidad por los socios ACTIVOS por encima de dicha cantidad.</p>
<p>Para otros módulos y servicios ver lista de servicios y precios.</p>
<p>4.2 El pago de las facturas por los servicios se realizará, tras la aceptación de los trabajos por el CLIENTE, mediante transferencia bancaria o ingreso en efectivo a los 10 días de la fecha de recepción de la factura a la siguiente cuenta corriente titularidad del PROVEEDOR: ES67 0182 0981 4502 0317 9600 (BBVA) </p>
<p>Las facturas DEBERÁN haber sido abonadas en un plazo máximo de 30 días desde su fecha de emisión. En caso de no haber abonado una factura pasado dicho plazo, su acceso al programa quedará temporalmente bloqueado hasta que el impago quede resuelto.</p>
<p>4.3 El PROVEEDOR se compromete a avisar por correo electrónico al CLIENTE del cambio del precio mínimo 30 días con antelación. Si el CLIENTE esta desacuerdo y quiere terminar los servicios con el PROVEEDOR tiene que enviar por correo electrónico su baja de servicios.</p>


<h3>QUINTA.- DURACIÓN DEL CONTRATO</h3>
<p>El plazo de duración del presente Contrato es indefinida. La terminación de esta Licencia de uso se puede producir las causas previstas en la Ley de Propiedad Intelectual y cualquier violación de las obligaciones de este contrato. </p>
<p>El Contrato podrá ser prorrogado expresamente y por escrito si ninguna de las partes realiza preaviso de un mes.</p>

<h3>SEXTA.- ACUERDO DE NIVEL DE SERVICIO</h3>
<p>6.1 Todos los Servicios prestados por el PROVEEDOR se realizarán por personal especializado en cada materia. El personal del PROVEEDOR acudirá previsto de todo el material necesario, adecuado y actualizado, para prestar los Servicios.</p>
<p>6.2 Las averías o el mal funcionamiento de los Servicios se comunicarán al PROVEEDOR a través de correo electrónico soporte@cannabisclub.systems.</p>
<p>6.3 Los problemas se resolverán en un período máximo de 24 horas si es leve, 48 si la incidencia es grave y cinco días si es crítica desde la recepción del aviso.</p>
<p>6.4 Se entiende por incidencia crítica: las incidencias que, en el marco de la prestación de los Servicios, afectan significativamente al CLIENTE.</p>
<ul class='normallist'>
 <li>Se entiende por incidencia grave: las incidencias que, en el marco de la prestación de los Servicios, afectan moderadamente al CLIENTE.</li>
 <li>Se entiende por incidencia leve: las incidencias que se limitan a entorpecer la prestación de los Servicios.</li>
</ul>
<p>6.5 El estado de los Servicios se revisará trimestralmente por el CLIENTE y el PROVEEDOR para comprobar su buen funcionamiento.</p>
<p>6.6 PROPIEDAD DE LOS PROGRAMAS Y DERECHOS DE AUTOR.</p>
<p>Los programas amparados por este contrato bajo licencia, las reproducciones originales de los mismos, cualquier copia parcial o total, realizada por el PROVEEDOR o la CLIENTE o por cualquier otra persona, los derechos legales de copia, las patentes, las marcas, los secretos comerciales, y de cualquier otro derecho intelectual o de propiedad, pertenecen al PROVEEDOR, por lo que cuenta con las autorizaciones suficientes para otorgar a su vez licencias de uso sobre dichos programas.</p>
<p>EL CLIENTE acepta y reconoce que los programas bajo licencia son secretos comerciales del PROVEEDOR, así como toda la información o documentación que le sea proporcionada y que haya sido identificada por este como confidencial.</p>
<p>El CLIENTE deber abstenerse de copiar con o sin fines de lucro los programas bajo licencia, dictando las medidas internas necesarias tendientes a la protección de los de autor de los propietarios de los programas bajo licencia.</p>

<h3>SÉPTIMA.- MODIFICACIÓN</h3>
<p>Las Partes podrán modificar el contrato de mutuo acuerdo y por escrito.</p>

<h3>OCTAVA.- RESOLUCIÓN</h3>
<p>Las Partes podrán resolver el Contrato, con derecho a la indemnización de daños y perjuicios causados, en caso de incumplimiento de las obligaciones establecidas en el mismo.</p>

<h3>NOVENA.- NOTIFICACIONES</h3>
<p>Las notificaciones que se realicen las Partes deberán realizarse por correo electrónico. El CLIENTE se compromete dar al PROVEEDOR los datos de contacto actualizados. </p>

<h3>DÉCIMA.- REGIMEN JURÍDICO</h3>
<p>El presente contrato tiene carácter mercantil, no existiendo en ningún caso vínculo laboral alguno entre el CLIENTE y el personal del PROVEEDOR que preste concretamente los Servicios. </p>
<p>Toda controversia derivada de este contrato o que guarde relación con él -incluida cualquier cuestión relativa a su existencia, validez o terminación- será resuelta mediante arbitraje DE DERECHO, administrado por la Asociación Europea de Arbitraje de Madrid (Aeade), de conformidad con su Reglamento de Arbitraje vigente a la fecha de presentación de la solicitud de arbitraje. El Tribunal Arbitral que se designe a tal efecto estará compuesto por un único árbitro experto y el idioma del arbitraje será el Castellano La sede del arbitraje será Madrid.</p>
<p>Y en prueba de cuanto antecede, las Partes suscriben el Contrato, en dos ejemplares y a un solo efecto, en el lugar y fecha señalados en el encabezamiento
<br />
<br />

<h2>Cláusula RGPD para recabar datos de interesados con consentimiento</h2>
<p>De acuerdo con lo establecido en el Reglamento General de Protección de Datos, le informamos que trataremos sus datos personales con la finalidad de realizar la gestión administrativa, contable y fiscal derivada de nuestra relación comercial, así como enviarle comunicaciones comerciales sobre nuestros productos y/o servicios. Los datos proporcionados se conservarán mientras se mantenga la relación comercial o durante el tiempo necesario para cumplir con las obligaciones legales. Los datos no se cederán a terceros salvo en los casos en que exista una obligación legal y los trataremos en base a su consentimiento.</p>
<p>Asimismo, le informamos de la posibilidad de ejercer los siguientes derechos sobre sus datos personales: derecho de acceso, rectificación, supresión u olvido, limitación, oposición, portabilidad y  a retirar el consentimiento prestado.</p>
<p>Para ello podrá enviar un email a: info@nefosolutions.com o dirigir un escrito a Nefos Solutions Limited C/ Esteban Collantes 22, 5ª - 28017 - Madrid - MADRID</p>
<p>Además, el interesado puede  dirigirse a  la Autoridad de Control en  materia de Protección de Datos competente para obtener información adicional o presentar una reclamación.</p>
<br />
<h2>CONSENTIMIENTO</h2>
<p>La finalidad y uso previsto tanto de los datos en sí mismos como de su tratamiento, es prestarle el servicio solicitado o entregarle el producto adquirido. A continuación podrá aceptar las finalidades que crea convenientes marcando su casilla correspondiente, tenga en cuenta que algunas finalidades pueden ser necesarias para poderle prestar el servicio, en el caso de NO marcar dichas casillas, no se podrá prestar/entregar el servicio/producto asociado.</p>

	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Prestación del servicio contratado (Si acepta el tratamiento de sus datos con esta finalidad marque esta casilla)
	  <input type="checkbox" name="accept2" id="accept2" />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Envío del producto adquirido (Si acepta el tratamiento de sus datos con esta finalidad marque esta casilla)
	  <input type="checkbox" name="accept3" id="accept3" />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />

	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox4"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Envío de ofertas de productos y servicios de su interés (Si acepta el tratamiento de sus datos con esta finalidad marque esta casilla)
	  <input type="checkbox" name="accept4" id="accept4" />
	  <div class="fakebox"></div>
	 </label>
	</div>

<p class='smallerfont'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Datos identificativos del responsable:<br />
Nefos Solutions Limited, N0073291G, C/ Esteban Collantes 22, 5ª - 28017 - Madrid - MADRID, 644441092</p>




<center><strong>T&uacute; firma:</strong><br />
<div id="signatureSet">
		<div id="dd_signaturePadWrapper"></div>
	</div><br />
</center>
<br>
<center>
	<div class='fakeboxholder'>
	 <label class="control">
	  <span id="errorBox1"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Estoy de acuerdo y aceptado todo arriba.
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

<?php } displayFooter(); ?>
