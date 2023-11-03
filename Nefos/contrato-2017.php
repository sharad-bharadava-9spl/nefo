<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['responsible'])) {
		
			$responsible = $_POST['responsible'];
			$dni = $_POST['dni'];
			$club = $_POST['club'];
			$address = $_POST['address'];
			$cif = $_POST['cif'];
			$insertTime = date('Y-m-d H:i:s');
			$imageid = $_SESSION['tempNo'];
			
		  	$query = sprintf("INSERT INTO contract (cif, name, dni, club, address, time, image) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s');",
		  		$cif, $responsible, $dni, $club, $address, $insertTime, $imageid);
		  			
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
			
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
		  rules: {
			  accept: {
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

	pageStart("Contrato", NULL, $validationScript, "pprofile", "statutes", "Contrato de software CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>


<script type="text/javascript" src="js/dd_signature_pad.js"></script>

	<form id="registerForm" method="post" action="">

<p>En Madrid, a <?php echo date("d-M-Y"); ?></p>
<h2>REUNIDOS</h2>

<p>DE UNA  PARTE, <input type="text" name="responsible" placeholder="Nombre de contacto" /> mayor de edad, con D.N.I. n&uacute;mero <input type="text" name="dni" placeholder="DNI" /> y en nombre y representaci&oacute;n de la <input type="text" name="club" placeholder="Nombre de club" />, en adelante, el "CLIENTE", domiciliada en <input type="text" name="address" placeholder="Direcci&oacute;n completo" />, <input type="text" name="cif" placeholder="CIF del club" />.</p>
<p>DE OTRA  PARTE, Andreas Bjolmer Nilsen mayor de edad, con D.N.I. n&uacute;mero X8075467Y  y en nombre y representaci&oacute;n de la mercantil Nefos Solutions LTD, en adelante, el "PROVEEDOR", domiciliada en Coliemore House, Coliemore Road, Dalkey, Irlanda, calle y numero de compania 575093.</p>
<p>El CLIENTE y el PROVEEDOR, en adelante, podr&aacute;n ser denominadas, individualmente, "la Parte" y, conjuntamente, "las Partes", reconoci&eacute;ndose mutuamente capacidad jur&iacute;dica y de obrar suficiente para la celebraci&oacute;n del presente Contrato.</p>
<br /><br />
<h2>EXPONEN</h2>

<p><strong>PRIMERO:</strong> Que el CLIENTE est&aacute; interesado en la compra de licencia de un programa de software.</p>
<p>El CLIENTE est&aacute; interesado en contratar dichos servicios para utilizar en sus ordenadores el programa CCS (Cannabis Club Systems)</p>
<p><strong>SEGUNDO:</strong> Que el PROVEEDOR es una empresa especializada en la prestaci&oacute;n de servicios de creaci&oacute;n, desarrollo, distribuci&oacute;n, actualizaci&oacute;n y mantenimiento de sistemas inform&aacute;ticos, Cloud computing, web y mail hosting.</p>
<p><strong>TERCERO:</strong> Que las Partes est&aacute;n interesadas en celebrar un contrato de licencia de uso en virtud del cual el PROVEEDOR licencie al CLIENTE para el uso del programa inform&aacute;tico denominado CCS (Cannabis Club Systems).</p>
<p>Que las Partes reunidas en la sede social del CLIENTE, acuerdan celebrar el presente contrato de LICENCIA DE USO, en adelante, el "Contrato", de acuerdo con las siguientes cl&aacute;usulas.</p>

<br /><br />
<h2>CL&Aacute;USULAS</h2>

<br />
<h3>PRIMERA.- OBJETO</h3>
<p>En virtud del Contrato el PROVEEDOR se obliga a ceder el uso, sin exclusiva al CLIENTE del programa de software CCS (Cannabis Club Systems).</p>
<p>El pago de la licencia otorgada bajo el presente contrato, no constituye la compra de los programas ni de los t&iacute;tulos, ni derechos de autor correspondientes.</p>

<br />
<h3>SEGUNDA.- T&Eacute;RMINOS Y CONDICIONES GENERALES Y ESPEC&Iacute;FICOS DE PRESTACI&Oacute;N DE LOS SERVICIOS</h3>
<p>2.1. Los Servicios se prestar&aacute;n en los siguientes t&eacute;rminos y condiciones generales:</p>
<p>2.1.1.1. El PROVEEDOR responder&aacute; de la calidad del trabajo desarrollado con la diligencia exigible a una empresa experta en la realizaci&oacute;n de los trabajos objeto del Contrato.</p>
<p>2.1.1.2. El PROVEEDOR se obliga a gestionar y obtener, a su cargo, todas las licencias, permisos y autorizaciones administrativas que pudieren ser necesarias para la realizaci&oacute;n de los Servicios. </p>
<p>2.1.1.3. El PROVEEDOR guardar&aacute; confidencialidad sobre la informaci&oacute;n que le facilite el CLIENTE en o para la ejecuci&oacute;n del Contrato o que por su propia naturaleza deba ser tratada como tal. Se excluye de la categor&iacute;a de informaci&oacute;n confidencial toda aquella informaci&oacute;n que sea divulgada por el CLIENTE, aquella que haya de ser revelada de acuerdo con las leyes o con una resoluci&oacute;n judicial o acto de autoridad competente. Este deber se mantendr&aacute; durante un plazo de tres a&ntilde;os a contar desde la finalizaci&oacute;n del servicio.</p>
<p>2.1.1.4. En el caso de que la prestaci&oacute;n de los Servicios suponga la necesidad de acceder a datos de car&aacute;cter personal, el PROVEEDOR, como encargado del tratamiento, queda obligado al cumplimiento de la Ley 15/1999, de 13 de diciembre, de Protecci&oacute;n de Datos de Car&aacute;cter Personal y del Real Decreto 1720/2007, de 21 de diciembre, por el que se aprueba el Reglamento de desarrollo de la Ley Org&aacute;nica 15/1999 y dem&aacute;s normativa aplicable. </p>
<p>El PROVEEDOR responder&aacute;, por tanto, de las infracciones en que pudiera incurrir en el caso de que destine los datos personales a otra finalidad, los comunique a un tercero, o en general, los utilice de forma irregular, as&iacute; como cuando no adopte las medidas correspondientes para el almacenamiento y custodia de los mismos. A tal efecto, se obliga a indemnizar al CLIENTE, por cualesquiera da&ntilde;os y perjuicios que sufra directamente, o por toda reclamaci&oacute;n, acci&oacute;n o procedimiento, que traiga su causa de un incumplimiento o cumplimiento defectuoso por parte del PROVEEDOR de lo dispuesto tanto en el Contrato como lo dispuesto en la normativa reguladora de la protecci&oacute;n de datos de car&aacute;cter personal.</p>
<p>A los efectos del art&iacute;culo 12 de la Ley 15/1999, el PROVEEDOR &uacute;nicamente tratar&aacute; los datos de car&aacute;cter personal a los que tenga acceso conforme a las instrucciones del CLIENTE y no los aplicar&aacute; o utilizar&aacute; con un fin distinto al objeto del Contrato, ni los comunicar&aacute;, ni siquiera para su conservaci&oacute;n, a otras personas. En el caso de que el PROVEEDOR destine los datos a otra finalidad, los comunique o los utilice incumpliendo las estipulaciones del Contrato, ser&aacute; considerado tambi&eacute;n responsable del tratamiento, respondiendo de las infracciones en que hubiera incurrido personalmente. </p>
<p>El PROVEEDOR deber&aacute; adoptar las medidas de &iacute;ndole t&eacute;cnica y organizativas necesarias que garanticen la seguridad de los datos de car&aacute;cter personal y eviten su alteraci&oacute;n, p&eacute;rdida, tratamiento o acceso no autorizado, habida cuenta del estado de la tecnolog&iacute;a, la naturaleza de los datos almacenados y los riesgos a que est&aacute;n expuestos, ya provengan de la acci&oacute;n humana o del medio f&iacute;sico o natural. A estos efectos el PROVEEDOR deber&aacute; aplicar los niveles de seguridad que se establecen en el Real Decreto 1720/2007 de acuerdo a la naturaleza de los datos que trate.</p>
<p>2.1.1.5. El PROVEEDOR responder&aacute; de la correcci&oacute;n y precisi&oacute;n de los documentos que aporte al CLIENTE en ejecuci&oacute;n del Contrato y avisar&aacute; sin dilaci&oacute;n al CLIENTE cuando detecte un error para que pueda adoptar las medidas y acciones correctoras que estime oportunas.</p>
<p>2.1.1.6. El PROVEEDOR responder&aacute; de los da&ntilde;os y perjuicios que se deriven para el CLIENTE y de las reclamaciones que pueda realizar un tercero, y que tengan su causa directa en errores del PROVEEDOR, o de su personal, en la ejecuci&oacute;n del Contrato o que deriven de la falta de diligencia referida anteriormente. </p>
<p>2.1.1.7. Las obligaciones establecidas para el PROVEEDOR por la presente cl&aacute;usula ser&aacute;n tambi&eacute;n de obligado cumplimiento para sus posibles empleados, colaboradores, tanto externos como internos, y subcontratistas, por lo que el PROVEEDOR responder&aacute; frente al CLIENTE si tales obligaciones son incumplidas por tales empleados.</p>
<p>2.2. El PROVEEDOR ceder&aacute; la licencia de uso, no exclusiva y prestar&aacute; los Servicios en los siguientes t&eacute;rminos y condiciones espec&iacute;ficos: </p>
<p>2.2.1.1. El PROVEEDOR licencia al CLIENTE el uso del programa descrito en cl&aacute;usula 1&ordf; de este contrato; Dicha licencia se entiende como Licencia personal, de car&aacute;cter no exclusivo e intransferible. </p>
<p>2.2.1.2. La Propiedad Intelectual del programa inform&aacute;tico licenciado, es &uacute;nica y exclusivamente del PROVEEDOR. Dicha propiedad intelectual abarca el programa inform&aacute;tico, su c&oacute;digo fuente y la estructura de su base de datos.</p>
<p>2.2.1.3. Queda expresamente prohibido el uso no permitido por ley en contenido colgado en el espacio. En todo aquello que no se haya regulado expresamente en este contrato, las partes se remiten a lo que establece la legislaci&oacute;n sobre Propiedad Intelectual.</p>
<p>2.2.1.4. El CLIENTE reconoce que el programa se suministra tal y como se describe en el Objeto del contrato, cl&aacute;usula 1&ordf; y que el presente acuerdo le concede derecho sobre otras versiones, mejoras o modificaciones del mismo. </p>
<p>2.2.1.5. La licencia de uso incluye todos los conocimientos necesarios y adicionales al contenido del programa y sus especificaciones t&eacute;cnicas para que &eacute;ste sea &uacute;til al CLIENTE. </p>
<p>2.2.1.6. La licencia de uso del programa se concede al CLENTE que ser&aacute; responsable de su custodia. El licenciatario es el propietario de los medios en los cuales haya almacenado el software, pero reconoce al PROVEEDOR  y sus cedentes de licencia la propiedad del software.</p>
<p>El PROVEEDOR  se reserva cualesquiera derechos no otorgados expresamente al licenciatario.</p>
<p>2.2.1.7. El CLIENTE una vez recibido el programa debe, dentro de los 10 d&iacute;as h&aacute;biles siguientes a la instalaci&oacute;n de los programas, manifestar por escrito al PROVEEDOR la conformidad al programa y a las especificaciones T&eacute;cnicas de funcionamiento. Si transcurrido dicho t&eacute;rmino, El CLIENTE no ha manifestado su conformidad al PROVEEDOR, se entender que los programas han sido aceptados. </p>
<p>2.2.1.8. El PROVEEDOR informar&aacute; al CLIENTE de cualquier novedad, ampliaci&oacute;n, etc., que se produzca en el programa, por si le interesase su instalaci&oacute;n.</p>
<p>2.2.1.9. El PROVEEDOR ejecutar&aacute; el Contrato realizando de manera competente y profesional los Servicios, cumpliendo los niveles de calidad exigidos y cuidando diligentemente los materiales del CLIENTE que tuviera que utilizar como consecuencia del Contrato. </p>
<p>2.3 Queda prohibida la transferencia o cesi&oacute;n total o parcial del presente contrato sin mediar el consentimiento previo por parte del PROVEEDOR.</p>
<h3>TERCERA.- POL&Iacute;TICA DE USO</h3>
<p>3.1 El CLIENTE es el &uacute;nico responsable de la selecci&oacute;n del programa de software y de los servicios que constituyen el objeto de este Contrato y que &eacute;stos se ajustan a sus necesidades, as&iacute; como de la instalaci&oacute;n y utilizaci&oacute;n del programa. </p>
<p>3.2 En ning&uacute;n caso ser&aacute; el PROVEEDOR del programa responsable, ante el usuario o terceras partes, de cualquier da&ntilde;o, incluida p&eacute;rdida de beneficios, p&eacute;rdida de ahorro o cualquier tipo de perjuicio surgido como consecuencia de su utilizaci&oacute;n, siendo el uso del programa a riesgo y ventura del CLIENTE.</p>
<p>3.3 El CLIENTE se compromete a no divulgar la informaci&oacute;n que le haya sido proporcionada por el PROVEEDOR y que sea identificada por &eacute;ste como "Confidencial", a excepci&oacute;n de aquella informaci&oacute;n que sea de dominio p&uacute;blico.</p>
<p>3.4 El PROVEEDOR excluye cualquier responsabilidad de cualquier tipo en el acceso a los contenidos facilitados a sus usuarios a trav&eacute;s de sus servicios que sean contrarios a la Ley, la moral y al Orden P&uacute;blico, que infrinjan derechos de propiedad intelectual, industrial o contengan cualquier vicio defecto o virus inform&aacute;tico o rutina de software similar.</p>
<p>El PROVEEDOR no tiene conocimiento de que las p&aacute;ginas a las que se pueda acceder a trav&eacute;s de sus servicios tengan un contenido contario a la Ley, a la moral o al orden p&uacute;blico, que infrinjan cualquier vicio, defecto, virus inform&aacute;tico o rutina de Software similar.</p>
<p>3.5 El PROVEEDOR   eliminar&aacute; el servicio en un plazo de 24 horas  en el caso de que el hecho al que se refiere la cl&aacute;usula 3.4 llegare a su conocimiento.</p>
<p>3.6 El PROVEEDOR no se responsabiliza por la exactitud, verosimilitud o idoneidad profesional de los datos publicados, as&iacute; como de los no publicados u ocultos por El CLIENTE y dem&aacute;s  usuarios de su software  y de la utilizaci&oacute;n que se les d&eacute; a los mismos, no asumiendo responsabilidad alguna en relaci&oacute;n a la mala utilizaci&oacute;n de este software.</p>

<h3>CUARTA.- PRECIO Y FACTURACI&Oacute;N.- </h3>
<p>4.1 El precio del Contrato es de 0,50 euros al mes por socio activo IVA excluido.</p>
<p>4.2 El pago de las facturas por los servicios se realizar&aacute;, tras la aceptaci&oacute;n de los trabajos por el CLIENTE, mediante transferencia bancaria a los 10 d&iacute;as de la fecha de recepci&oacute;n de la factura a la siguiente cuenta corriente titularidad del PROVEEDOR: ES67 0182 0981 4502 0317 9600 (BBVA) </p>

<br />
<h3>QUINTA.- DURACI&Oacute;N DEL CONTRATO</h3>
<p>El plazo de duraci&oacute;n del presente Contrato es indefinida. La terminaci&oacute;n de esta Licencia de uso se puede producir las causas previstas en la Ley de Propiedad Intelectual y cualquier violaci&oacute;n de las obligaciones de este contrato. </p>
<p>El Contrato podr&aacute; ser prorrogado expresamente y por escrito si ninguna de las partes realiza preaviso de un mes.</p>

<br />
<h3>SEXTA.- ACUERDO DE NIVEL DE SERVICIO</h3>
<p>6.1 Todos los Servicios prestados por el PROVEEDOR se realizar&aacute;n por personal especializado en cada materia. El personal del PROVEEDOR acudir&aacute; previsto de todo el material necesario, adecuado y actualizado, para prestar los Servicios.</p>
<p>6.2 Las aver&iacute;as o el mal funcionamiento de los Servicios se comunicar&aacute;n al PROVEEDOR a trav&eacute;s de correo electr&oacute;nico. </p>
<p>6.3 Los problemas se resolver&aacute;n en un per&iacute;odo m&aacute;ximo de 24 horas si es leve, 48 si la incidencia es grave y cinco d&iacute;as si es cr&iacute;tica desde la recepci&oacute;n del aviso.</p>
<p>6.4 Se entiende por incidencia cr&iacute;tica: las incidencias que, en el marco de la prestaci&oacute;n de los Servicios, afectan significativamente al CLIENTE.<br />
<ul>
 <li>Se entiende por incidencia grave: las incidencias que, en el marco de la prestaci&oacute;n de los Servicios, afectan moderadamente al CLIENTE.</li>
 <li>Se entiende por incidencia leve: las incidencias que se limitan a entorpecer la prestaci&oacute;n de los Servicios.</li>
</ul>
</p>
<p>6.5 El estado de los Servicios se revisar&aacute; mensualmente por el CLIENTE y el PROVEEDOR para comprobar su buen funcionamiento.</p>
<p>6.6 PROPIEDAD DE LOS PROGRAMAS Y DERECHOS DE AUTOR.</p>
<p>Los programas amparados por este contrato bajo licencia, las reproducciones originales de los mismos, cualquier copia parcial o total, realizada por el PROVEEDOR o la CLIENTE o por cualquier otra persona, los derechos legales de copia, las patentes, las marcas, los secretos comerciales, y de cualquier otro derecho intelectual o de propiedad, pertenecen al PROVEEDOR, por lo que cuenta con las autorizaciones suficientes para otorgar a su vez licencias de uso sobre dichos programas.</p>
<p>EL CLIENTE acepta y reconoce que los programas bajo licencia son secretos comerciales del PROVEEDOR, as&iacute; como toda la informaci&oacute;n o documentaci&oacute;n que le sea proporcionada y que haya sido identificada por este como confidencial.</p>
<p>El CLIENTE deber abstenerse de copiar con o sin fines de lucro los programas bajo licencia, dictando las medidas internas necesarias tendientes a la protecci&oacute;n de los de autor de los propietarios de los programas bajo licencia.</p>

<br />
<h3>S&Eacute;PTIMA.- MODIFICACI&Oacute;N</h3>
<p>Las Partes podr&aacute;n modificar el contrato de mutuo acuerdo y por escrito.</p>
<br />
<h3>OCTAVA.- RESOLUCI&Oacute;N</h3>
<p>Las Partes podr&aacute;n resolver el Contrato, con derecho a la indemnizaci&oacute;n de da&ntilde;os y perjuicios causados, en caso de incumplimiento de las obligaciones establecidas en el mismo.</p>
<br />
<h3>NOVENA.- NOTIFICACIONES</h3>
<p>Las notificaciones que se realicen las Partes deber&aacute;n realizarse por correo electr&oacute;nico.</p>
<br />

<h3>D&Eacute;CIMA.- REGIMEN JUR&Iacute;DICO</h3>
<p>El presente contrato tiene car&aacute;cter mercantil, no existiendo en ning&uacute;n caso v&iacute;nculo laboral alguno entre el CLIENTE y el personal del PROVEEDOR que preste concretamente los Servicios. </p>
<p>Toda controversia derivada de este contrato o que guarde relaci&oacute;n con &eacute;l -incluida cualquier cuesti&oacute;n relativa a su existencia, validez o terminaci&oacute;n- ser&aacute; resuelta mediante arbitraje DE DERECHO, administrado por la Asociaci&oacute;n Europea de Arbitraje de Madrid (Aeade), de conformidad con su Reglamento de Arbitraje vigente a la fecha de presentaci&oacute;n de la solicitud de arbitraje. El Tribunal Arbitral que se designe a tal efecto estar&aacute; compuesto por un &uacute;nico &aacute;rbitro experto y el idioma del arbitraje ser&aacute; el Castellano La sede del arbitraje ser&aacute; Madrid.</p>
<p>Y en prueba de cuanto antecede, las Partes suscriben el Contrato, en dos ejemplares y a un solo efecto, en el lugar y fecha se&ntilde;alados en el encabezamiento.</p>



<center><strong>T&uacute; firma:</strong><br />
<div id="signatureSet">
		<div id="dd_signaturePadWrapper"></div>
	</div><br />
</center>

<center>
 <table>
  <tr>
   <td><input type="checkbox" name="accept" id="savesig" style="width: 12px;" /></td>
   <td>Estoy de acuerdo y aceptado todo arriba.<br />
   <span id="errorBox1"></span></td>
  </tr>
 </table>   
</center><br />
<center><span id="errorBox"></span></center><br />

	 <button type="submit">Submit</button><br />
	</form>



<?php displayFooter(); ?>
